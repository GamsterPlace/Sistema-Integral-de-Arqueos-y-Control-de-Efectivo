<?php
namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\JornadaReceptor;
use App\Services\Agencias\AccesoAgenciaService;
use App\Services\Agencias\FirmaArqueoService;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EntregaEfectivoController extends Controller
{
    private const BILLETES = ['200','100','50','20','10','5','2','1'];
    private const MONEDAS = ['1.00','0.50','0.25','0.10','0.05'];

    private function rol(Request $request): string
    {
        $request->user()->loadMissing('rol');
        $rol = $request->user()->rol?->nombre;
        abort_unless(in_array($rol,['Receptor','JefeCaja','SubjefeCaja'],true),403);
        return $rol === 'Receptor' ? 'receptor' : ($rol === 'JefeCaja' ? 'jefecaja' : 'subjefecaja');
    }
    private function agenciasCaja(Request $request, AccesoAgenciaService $acceso): array
    {
        return $acceso->asignacionesVigentes($request->user())
            ->filter(fn($a)=>(bool)$a->agencia?->estado)
            ->pluck('agencia_id')->map(fn($v)=>(int)$v)->all();
    }
    private function autorizada(Request $request, ArqueoAgencia $a, AccesoAgenciaService $acceso): bool
    {
        $rol=$this->rol($request);
        if($rol==='receptor') return in_array((int)$request->user()->id,[(int)$a->receptor_usuario_id,(int)$a->receptor_entrante_usuario_id],true);
        return in_array((int)$a->agencia_id,$this->agenciasCaja($request,$acceso),true);
    }
    public function index(Request $request, AccesoAgenciaService $acceso): View
    {
        $ruta=$this->rol($request);
        $q=ArqueoAgencia::query()->where('tipo','ENTREGA');
        if($ruta==='receptor') $q->where(fn($x)=>$x->where('receptor_usuario_id',$request->user()->id)->orWhere('receptor_entrante_usuario_id',$request->user()->id));
        else $q->whereIn('agencia_id',$this->agenciasCaja($request,$acceso));
        $arqueos=$q->latest('id')->paginate(20);
        return view('agencias.entregas.index',compact('ruta','arqueos'));
    }
    public function create(Request $request, AccesoAgenciaService $acceso): View
    {
        abort_unless($this->rol($request)==='receptor',403);
        $jornadas=JornadaReceptor::with(['agencia','ventanilla','receptor','turno','asignacionPersonal'])
            ->whereDate('fecha_operativa',today()->toDateString())
            ->whereHas('asignacionPersonal',fn($q)=>$q->where('cargo','RECEPTOR')->where('estado',true))
            ->whereHas('agencia',fn($q)=>$q->where('estado',true))
            ->whereHas('ventanilla',fn($q)=>$q->where('estado',true))
            ->whereHas('turno',fn($q)=>$q->where('estado',true))
            ->orderBy('inicio_programado')->get();
        $salientes=$jornadas->where('receptor_usuario_id',$request->user()->id)->values();
        return view('agencias.entregas.create',['ruta'=>'receptor','salientes'=>$salientes,'jornadas'=>$jornadas,'billetes'=>self::BILLETES,'monedas'=>self::MONEDAS]);
    }
    public function store(Request $request, FirmaArqueoService $firmas, AuditoriaService $auditoria)
    {
        abort_unless($this->rol($request)==='receptor',403);
        $d=$request->validate([
            'jornada_id'=>['required','integer','exists:jornadas_receptor,id'],
            'jornada_entrante_id'=>['required','integer','different:jornada_id','exists:jornadas_receptor,id'],
            'billetes'=>['required','array'],'billetes.*'=>['required','integer','min:0','max:10000000'],
            'monedas'=>['required','array'],'monedas.*'=>['required','integer','min:0','max:10000000'],
            'total_corte'=>['required','numeric','min:0','max:999999999999.99','decimal:0,2'],
            'justificacion_diferencia'=>['nullable','string','max:2000'],
            'observaciones'=>['nullable','string','max:5000'],
            'confirmar'=>['accepted'],
        ]);
        if(array_diff(array_keys($d['billetes']),self::BILLETES) || array_diff(array_keys($d['monedas']),self::MONEDAS))
            throw ValidationException::withMessages(['billetes'=>'Se enviaron denominaciones no permitidas.']);
        $detalles=[];$bil=0;$mon=0;
        foreach([['BILLETE',self::BILLETES],['MONEDA',self::MONEDAS]] as [$tipo,$valores]){
            foreach($valores as $v){
                $cantidad=(int)($d[$tipo==='BILLETE'?'billetes':'monedas'][$v]??0);
                $centavos=(int)round((float)$v*100);
                $subtotal=$centavos*$cantidad;
                if($tipo==='BILLETE')$bil+=$subtotal;else $mon+=$subtotal;
                $detalles[]=['tipo'=>$tipo,'denominacion'=>number_format($centavos/100,2,'.',''),'cantidad'=>$cantidad,'subtotal'=>number_format($subtotal/100,2,'.','')];
            }
        }
        $total=$bil+$mon;$corte=(int)round((float)$d['total_corte']*100);$dif=$total-$corte;
        if($total>99999999999999 || abs($dif)>99999999999999)
            throw ValidationException::withMessages(['billetes'=>'El total excede el límite permitido.']);
        if($dif!==0 && trim((string)($d['justificacion_diferencia']??''))==='')
            throw ValidationException::withMessages(['justificacion_diferencia'=>'Debe justificar el faltante o sobrante.']);
        $arqueo=DB::transaction(function()use($request,$d,$detalles,$bil,$mon,$total,$corte,$dif,$firmas,$auditoria){
            // Bloqueo determinista de ambas jornadas para evitar entregas concurrentes.
            $ids=[(int)$d['jornada_id'],(int)$d['jornada_entrante_id']];sort($ids);
            $bloqueadas=JornadaReceptor::whereIn('id',$ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $sal=$bloqueadas->get((int)$d['jornada_id']);$ent=$bloqueadas->get((int)$d['jornada_entrante_id']);
            abort_unless($sal && $ent,404);
            $sal->load(['agencia','ventanilla','receptor','turno','asignacionPersonal']);
            $ent->load(['agencia','ventanilla','receptor','turno','asignacionPersonal']);
            $fecha=today()->toDateString();
            foreach([$sal,$ent] as $j){
                $a=$j->asignacionPersonal;
                abort_unless($j->fecha_operativa?->toDateString()===$fecha && $a && $a->estado && $a->cargo==='RECEPTOR'
                    && (int)$a->usuario_id===(int)$j->receptor_usuario_id
                    && (int)$a->agencia_id===(int)$j->agencia_id
                    && $a->fecha_inicio->toDateString()<=$fecha
                    && (!$a->fecha_fin || $a->fecha_fin->toDateString()>=$fecha)
                    && $j->agencia?->estado && $j->ventanilla?->estado && $j->turno?->estado
                    && (int)$j->ventanilla->agencia_id===(int)$j->agencia_id,403,'Turno no autorizado.');
            }
            abort_unless((int)$sal->receptor_usuario_id===(int)$request->user()->id,403);
            abort_unless((int)$sal->agencia_id===(int)$ent->agencia_id && (int)$sal->ventanilla_id===(int)$ent->ventanilla_id
                && (int)$sal->receptor_usuario_id!==(int)$ent->receptor_usuario_id,422,'La entrega requiere dos receptores diferentes de la misma ventanilla y agencia.');
            if(ArqueoAgencia::where('jornada_id',$sal->id)->where('tipo','ENTREGA')->where('estado','!=','ANULADO')->exists())
                throw ValidationException::withMessages(['jornada_id'=>'Ya existe una entrega no anulada para este turno.']);
            $a=ArqueoAgencia::create([
                'numero_arqueo'=>'EN-'.now()->format('YmdHis').'-'.strtoupper(Str::random(12)),
                'agencia_id'=>$sal->agencia_id,'ventanilla_id'=>$sal->ventanilla_id,'jornada_id'=>$sal->id,
                'receptor_usuario_id'=>$sal->receptor_usuario_id,'receptor_entrante_usuario_id'=>$ent->receptor_usuario_id,
                'jornada_entrante_id'=>$ent->id,'creado_por'=>$request->user()->id,
                'tipo'=>'ENTREGA','estado'=>'PENDIENTE_FIRMA_ENTRANTE','fecha_operativa'=>$sal->fecha_operativa,
                'inicio_at'=>now(),'finalizado_at'=>now(),'fuera_fecha_ordinaria'=>false,'cierra_turno'=>false,
                'agencia_codigo_historico'=>$sal->agencia->codigo,'agencia_nombre_historico'=>$sal->agencia->nombre,
                'ventanilla_codigo_historico'=>$sal->ventanilla->codigo,
                'receptor_nombre_historico'=>$sal->receptor->nombre_completo,
                'receptor_entrante_nombre_historico'=>$ent->receptor->nombre_completo,
                'turno_nombre_historico'=>$sal->turno->nombre,
                'total_billetes'=>number_format($bil/100,2,'.',''),'total_monedas'=>number_format($mon/100,2,'.',''),
                'total_otros_valores'=>'0.00','total_arqueado'=>number_format($total/100,2,'.',''),
                'total_corte'=>number_format($corte/100,2,'.',''),'diferencia'=>number_format($dif/100,2,'.',''),
                'justificacion_diferencia'=>$d['justificacion_diferencia']??null,'observaciones'=>$d['observaciones']??null,
                'firmado_receptor_at'=>now(),
            ]);
            $a->detalles()->createMany($detalles);
            $firmas->firmarEntrega($a,$request->user(),'RECEPTOR');
            $auditoria->registrar($request->user(),'ARQUEOS_AGENCIA','CREAR_ENTREGA','arqueos_agencia',$a->id,
                'Entrega de efectivo registrada y firmada por el receptor saliente.',null,
                ['numero_arqueo'=>$a->numero_arqueo,'jornada_id'=>$sal->id,'jornada_entrante_id'=>$ent->id,'diferencia'=>$a->diferencia]);
            return $a;
        },3);
        return redirect()->route('receptor.entregas.show',$arqueo->id)->with('success','Entrega firmada. Pendiente de firma del receptor entrante.');
    }
    public function show(Request $request,int $arqueo,AccesoAgenciaService $acceso,FirmaArqueoService $firmas):View
    {
        $ruta=$this->rol($request);
        $a=ArqueoAgencia::where('tipo','ENTREGA')->with(['detalles','firmas'])->findOrFail($arqueo);
        abort_unless($this->autorizada($request,$a,$acceso),403);
        $verificaciones=[];foreach($a->firmas as $f)$verificaciones[$f->tipo_firma]=$firmas->verificar($a,$f);
        return view('agencias.entregas.show',['registro'=>$a,'ruta'=>$ruta,'verificaciones'=>$verificaciones,
            'esEntrante'=>$ruta==='receptor' && (int)$a->receptor_entrante_usuario_id===(int)$request->user()->id,
            'esCaja'=>$ruta!=='receptor']);
    }
    public function firmarEntrante(Request $request,int $arqueo,FirmaArqueoService $firmas,AuditoriaService $auditoria)
    {
        abort_unless($this->rol($request)==='receptor',403);
        $request->validate(['confirmar_firma'=>['accepted']]);
        DB::transaction(function()use($request,$arqueo,$firmas,$auditoria){
            $a=ArqueoAgencia::where('tipo','ENTREGA')->where('receptor_entrante_usuario_id',$request->user()->id)->lockForUpdate()->findOrFail($arqueo);
            if($a->estado!=='PENDIENTE_FIRMA_ENTRANTE' || $a->firmas()->where('tipo_firma','ENTRANTE')->exists())
                throw ValidationException::withMessages(['arqueo'=>'Esta entrega no está disponible para firma.']);
            $saliente=$a->firmas()->where('tipo_firma','RECEPTOR')->first();
            if(!$saliente || (int)$saliente->usuario_id!==(int)$a->receptor_usuario_id || !$firmas->verificar($a,$saliente))
                throw ValidationException::withMessages(['arqueo'=>'La firma del receptor saliente no es válida.']);
            $firmas->firmarEntrega($a,$request->user(),'ENTRANTE');
            $a->estado='PENDIENTE';$a->save();
            $auditoria->registrar($request->user(),'ARQUEOS_AGENCIA','FIRMAR_ENTREGA_ENTRANTE','arqueos_agencia',$a->id,
                'Receptor entrante firmó la entrega de efectivo.',null,['estado'=>'PENDIENTE']);
        },3);
        return redirect()->route('receptor.entregas.show',$arqueo)->with('success','Firma del receptor entrante registrada.');
    }
    public function certificar(Request $request,int $arqueo,AccesoAgenciaService $acceso,FirmaArqueoService $firmas,AuditoriaService $auditoria)
    {
        $ruta=$this->rol($request);abort_unless($ruta!=='receptor',403);
        $request->validate(['confirmar_certificacion'=>['accepted']]);
        DB::transaction(function()use($request,$arqueo,$acceso,$firmas,$auditoria){
            $a=ArqueoAgencia::where('tipo','ENTREGA')->lockForUpdate()->findOrFail($arqueo);
            abort_unless($this->autorizada($request,$a,$acceso),403);
            if($a->estado!=='PENDIENTE' || $a->certificado_at || $a->anulado_at)
                throw ValidationException::withMessages(['arqueo'=>'La entrega no está pendiente de certificación.']);
            foreach(['RECEPTOR'=>'receptor_usuario_id','ENTRANTE'=>'receptor_entrante_usuario_id'] as $tipo=>$campo){
                $f=$a->firmas()->where('tipo_firma',$tipo)->first();
                if(!$f || (int)$f->usuario_id!==(int)$a->$campo || !$firmas->verificar($a,$f))
                    throw ValidationException::withMessages(['arqueo'=>'Firma '.$tipo.' inválida.']);
            }
            if($a->firmas()->where('tipo_firma','CERTIFICADOR')->exists())
                throw ValidationException::withMessages(['arqueo'=>'La entrega ya tiene firma certificadora.']);
            if(abs((float)$a->diferencia)>0.00001 && trim((string)$a->justificacion_diferencia)==='')
                throw ValidationException::withMessages(['arqueo'=>'La diferencia necesita justificación.']);
            $firmas->firmarEntrega($a,$request->user(),'CERTIFICADOR');
            $a->estado='CERTIFICADO';$a->certificado_at=now();
            if($request->user()->rol?->nombre==='JefeCaja')$a->jefe_caja_responsable_id=$request->user()->id;
            else $a->subjefe_caja_responsable_id=$request->user()->id;
            $a->save();
            $auditoria->registrar($request->user(),'ARQUEOS_AGENCIA','CERTIFICAR_ENTREGA','arqueos_agencia',$a->id,
                'Entrega certificada con las tres firmas.', ['estado'=>'PENDIENTE'],['estado'=>'CERTIFICADO']);
        },3);
        return redirect()->route($ruta.'.entregas.show',$arqueo)->with('success','Entrega certificada con las tres firmas.');
    }
}
