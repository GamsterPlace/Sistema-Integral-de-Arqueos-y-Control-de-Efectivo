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

class ArqueoAuditoriaAgenciaController extends Controller
{
    private const BILLETES = ['200','100','50','20','10','5','2','1'];
    private const MONEDAS = ['1.00','0.50','0.25','0.10','0.05'];

    private function auditor(Request $request): void
    {
        $request->user()->loadMissing('rol');
        abort_unless($request->user()->rol?->nombre === 'Auditoria', 403);
    }
    public function index(Request $request, AccesoAgenciaService $acceso): View
    {
        $this->auditor($request); $ruta='auditoria';
        $arqueos=ArqueoAgencia::where('tipo','AUDITORIA')->latest('id')->paginate(20);
        return view('agencias.auditoria.index',compact('arqueos','ruta'));
    }
    public function create(Request $request, AccesoAgenciaService $acceso): View
    {
        $this->auditor($request); $ruta='auditoria';
        $jornadas=JornadaReceptor::with(['agencia','ventanilla','receptor','turno','asignacionPersonal'])
            ->whereDate('fecha_operativa',today()->toDateString())
            ->whereHas('asignacionPersonal',fn($q)=>$q->where('cargo','RECEPTOR')->where('estado',true))
            ->orderBy('agencia_id')->orderBy('inicio_programado')->get()
            ->filter(fn($j)=>(bool)$j->agencia?->estado && (bool)$j->ventanilla?->estado);
        return view('agencias.auditoria.create',['jornadas'=>$jornadas,'ruta'=>$ruta,'billetes'=>self::BILLETES,'monedas'=>self::MONEDAS]);
    }
    public function store(Request $request, AccesoAgenciaService $acceso, FirmaArqueoService $firmas, AuditoriaService $auditoria)
    {
        $this->auditor($request); $ruta='auditoria';
        $d=$request->validate([
            'jornada_id'=>['required','integer','exists:jornadas_receptor,id'],
            'billetes'=>['required','array'],'billetes.*'=>['required','integer','min:0','max:10000000'],
            'monedas'=>['required','array'],'monedas.*'=>['required','integer','min:0','max:10000000'],
            'total_corte'=>['required','numeric','min:0','max:999999999999.99','decimal:0,2'],
            'justificacion_diferencia'=>['nullable','string','max:2000'],
            'observaciones'=>['nullable','string','max:5000'],
            'confirmar'=>['accepted'],
        ]);
        if(array_diff(array_keys($d['billetes']),self::BILLETES) || array_diff(array_keys($d['monedas']),self::MONEDAS))
            throw ValidationException::withMessages(['billetes'=>'Denominaciones no permitidas.']);
        $detalles=[];$sumBil=0;$sumMon=0;
        foreach([['BILLETE',self::BILLETES],['MONEDA',self::MONEDAS]] as [$tipo,$valores]){
            foreach($valores as $v){
                $centavos=(int)round((float)$v*100);$cantidad=(int)($d[$tipo==='BILLETE'?'billetes':'monedas'][$v]??0);
                $subtotal=$centavos*$cantidad;
                if($tipo==='BILLETE')$sumBil+=$subtotal;else $sumMon+=$subtotal;
                $detalles[]=['tipo'=>$tipo,'denominacion'=>number_format($centavos/100,2,'.',''),'concepto'=>null,'cantidad'=>$cantidad,'subtotal'=>number_format($subtotal/100,2,'.','')];
            }
        }
        $total=$sumBil+$sumMon;
        $corte=(int)round((float)$d['total_corte']*100);
        $dif=$total-$corte;
        if($total>99999999999999 || abs($dif)>99999999999999)
            throw ValidationException::withMessages(['billetes'=>'Monto fuera del límite permitido.']);
        if($dif!==0 && trim((string)($d['justificacion_diferencia']??''))==='')
            throw ValidationException::withMessages(['justificacion_diferencia'=>'Justifique la diferencia.']);
        $arqueo=DB::transaction(function()use($request,$d,$detalles,$sumBil,$sumMon,$total,$corte,$dif,$firmas,$auditoria){
            $j=JornadaReceptor::with(['agencia','ventanilla','turno','receptor','asignacionPersonal'])->lockForUpdate()->findOrFail($d['jornada_id']);
            $a=$j->asignacionPersonal;
            abort_unless($j->fecha_operativa->toDateString()===today()->toDateString()
                && $a && $a->estado && $a->cargo==='RECEPTOR' && (int)$a->usuario_id===(int)$j->receptor_usuario_id
                && (int)$a->agencia_id===(int)$j->agencia_id && $a->fecha_inicio->toDateString()<=$j->fecha_operativa->toDateString()
                && (!$a->fecha_fin || $a->fecha_fin->toDateString()>=$j->fecha_operativa->toDateString())
                && $j->agencia?->estado && $j->ventanilla?->estado && (int)$j->ventanilla->agencia_id===(int)$j->agencia_id
                && $j->turno?->estado,403,'Jornada no autorizada.');
            // Un arqueo de auditoría pendiente por jornada: evita iniciar otro mientras falta la firma del receptor.
            if(ArqueoAgencia::where('jornada_id',$j->id)->where('tipo','AUDITORIA')->where('estado','PENDIENTE_FIRMA_RECEPTOR')->exists())
                throw ValidationException::withMessages(['jornada_id'=>'Ya existe un arqueo de auditoría pendiente de firma para este turno.']);
            $u=$request->user();$u->loadMissing('rol');
            $registro=ArqueoAgencia::create([
                'numero_arqueo'=>'AU-'.now()->format('YmdHis').'-'.strtoupper(Str::random(12)),
                'agencia_id'=>$j->agencia_id,'ventanilla_id'=>$j->ventanilla_id,'jornada_id'=>$j->id,
                'receptor_usuario_id'=>$j->receptor_usuario_id,'creado_por'=>$u->id,
                
                
                'tipo'=>'AUDITORIA','estado'=>'PENDIENTE_FIRMA_RECEPTOR','fecha_operativa'=>$j->fecha_operativa,
                'inicio_at'=>now(),'finalizado_at'=>now(),'cierra_turno'=>false,'fuera_fecha_ordinaria'=>false,
                'agencia_codigo_historico'=>$j->agencia->codigo,'agencia_nombre_historico'=>$j->agencia->nombre,
                'ventanilla_codigo_historico'=>$j->ventanilla->codigo,
                'receptor_nombre_historico'=>$j->receptor->nombre_completo,'turno_nombre_historico'=>$j->turno->nombre,
                'total_billetes'=>number_format($sumBil/100,2,'.',''),'total_monedas'=>number_format($sumMon/100,2,'.',''),
                'total_otros_valores'=>'0.00','total_arqueado'=>number_format($total/100,2,'.',''),
                'total_corte'=>number_format($corte/100,2,'.',''),'diferencia'=>number_format($dif/100,2,'.',''),
                'justificacion_diferencia'=>$d['justificacion_diferencia']??null,'observaciones'=>$d['observaciones']??null,
                
            ]);
            $registro->detalles()->createMany($detalles);
            $firmas->firmarAuditor($registro,$u);
            $auditoria->registrar($u,'ARQUEOS_AGENCIA','CREAR_ARQUEO_AUDITORIA','arqueos_agencia',$registro->id,
                'Conteo de auditoría firmado por Auditoría, pendiente de confirmación del receptor.',null,
                ['numero_arqueo'=>$registro->numero_arqueo,'jornada_id'=>$j->id,'diferencia'=>$registro->diferencia]);
            return $registro;
        },3);
        return redirect()->route($ruta.'.auditoria-agencias.show',$arqueo->id)->with('success','Arqueo de auditoría registrado. Pendiente de firma del receptor.');
    }
    public function show(Request $request,int $arqueo,AccesoAgenciaService $acceso,FirmaArqueoService $firmas):View
    {
        $this->auditor($request); $ruta='auditoria';
        $registro=ArqueoAgencia::where('tipo','AUDITORIA')->with(['detalles','firmas'])->findOrFail($arqueo);
        return view('agencias.auditoria.show',['registro'=>$registro,'ruta'=>$ruta,'verificaciones'=>$this->verificaciones($registro,$firmas),'esReceptor'=>false,'esCaja'=>false]);
    }
    public function receptorIndex(Request $request):View
    {
        $arqueos=ArqueoAgencia::where('receptor_usuario_id',$request->user()->id)->where('tipo','AUDITORIA')->latest('id')->paginate(20);
        return view('agencias.auditoria.index',['arqueos'=>$arqueos,'ruta'=>'receptor']);
    }
    public function receptorShow(Request $request,int $arqueo,FirmaArqueoService $firmas):View
    {
        $registro=ArqueoAgencia::where('receptor_usuario_id',$request->user()->id)->where('tipo','AUDITORIA')->with(['detalles','firmas'])->findOrFail($arqueo);
        return view('agencias.auditoria.show',['registro'=>$registro,'ruta'=>'receptor','verificaciones'=>$this->verificaciones($registro,$firmas),'esReceptor'=>true,'esCaja'=>false]);
    }
    public function firmarReceptor(Request $request,int $arqueo,FirmaArqueoService $firmas,AuditoriaService $auditoria)
    {
        $request->validate(['confirmar_firma'=>['accepted']]);
        DB::transaction(function()use($request,$arqueo,$firmas,$auditoria){
            $registro=ArqueoAgencia::where('receptor_usuario_id',$request->user()->id)->where('tipo','AUDITORIA')->lockForUpdate()->findOrFail($arqueo);
            if($registro->estado!=='PENDIENTE_FIRMA_RECEPTOR' || $registro->firmas()->where('tipo_firma','RECEPTOR')->exists())
                throw ValidationException::withMessages(['arqueo'=>'Este arqueo ya fue firmado o no está disponible.']);
            $cert=$registro->firmas()->where('tipo_firma','AUDITOR')->first();
            if(!$cert || !$firmas->verificar($registro,$cert))
                throw ValidationException::withMessages(['arqueo'=>'No se pudo verificar la firma del auditor.']);
            $firmas->firmarReceptor($registro,$request->user());
            $registro->estado='PENDIENTE';$registro->firmado_receptor_at=now();$registro->save();
            $auditoria->registrar($request->user(),'ARQUEOS_AGENCIA','FIRMAR_ARQUEO_AUDITORIA','arqueos_agencia',$registro->id,
                'Receptor confirmó el arqueo de auditoría.',null,['numero_arqueo'=>$registro->numero_arqueo,'estado'=>'PENDIENTE']);
        },3);
        return redirect()->route('receptor.auditoria-agencias.show',$arqueo)->with('success','Firma registrada correctamente.');
    }
    private function caja(Request $request, AccesoAgenciaService $acceso): array
    {
        $u=$request->user(); $u->loadMissing('rol');
        abort_unless(in_array($u->rol?->nombre,['JefeCaja','SubjefeCaja'],true),403);
        $ids=$acceso->asignacionesVigentes($u)->filter(fn($a)=>(bool)$a->agencia?->estado)
            ->pluck('agencia_id')->map(fn($v)=>(int)$v)->all();
        return [$ids,$u->rol->nombre==='JefeCaja'?'jefecaja':'subjefecaja'];
    }
    public function cajaIndex(Request $request,AccesoAgenciaService $acceso):View
    {
        [$ids,$ruta]=$this->caja($request,$acceso);
        $arqueos=ArqueoAgencia::whereIn('agencia_id',$ids)->where('tipo','AUDITORIA')->latest('id')->paginate(20);
        return view('agencias.auditoria.index',compact('arqueos','ruta'));
    }
    public function cajaShow(Request $request,int $arqueo,AccesoAgenciaService $acceso,FirmaArqueoService $firmas):View
    {
        [$ids,$ruta]=$this->caja($request,$acceso);
        $registro=ArqueoAgencia::whereIn('agencia_id',$ids)->where('tipo','AUDITORIA')->with(['detalles','firmas'])->findOrFail($arqueo);
        return view('agencias.auditoria.show',['registro'=>$registro,'ruta'=>$ruta,'verificaciones'=>$this->verificaciones($registro,$firmas),'esReceptor'=>false,'esCaja'=>true]);
    }
    public function certificar(Request $request,int $arqueo,AccesoAgenciaService $acceso,FirmaArqueoService $firmas,AuditoriaService $auditoria)
    {
        $request->validate(['confirmar_certificacion'=>['accepted']]);
        [$ids,$ruta]=$this->caja($request,$acceso);
        DB::transaction(function()use($request,$arqueo,$ids,$firmas,$auditoria){
            $registro=ArqueoAgencia::whereIn('agencia_id',$ids)->where('tipo','AUDITORIA')->lockForUpdate()->findOrFail($arqueo);
            if($registro->estado!=='PENDIENTE' || $registro->anulado_at || $registro->certificado_at || !$registro->firmado_receptor_at)
                throw ValidationException::withMessages(['arqueo'=>'El arqueo no está pendiente de certificación.']);
            $registro->load(['firmas','detalles']);
            foreach(['AUDITOR','RECEPTOR'] as $tipo){
                $f=$registro->firmas->firstWhere('tipo_firma',$tipo);
                if(!$f || !$firmas->verificar($registro,$f))
                    throw ValidationException::withMessages(['arqueo'=>'No se pudo verificar la firma '.$tipo.'.']);
                if($tipo==='RECEPTOR' && (int)$f->usuario_id !== (int)$registro->receptor_usuario_id)
                    throw ValidationException::withMessages(['arqueo'=>'La firma del receptor no corresponde.']);
            }
            if($registro->firmas->contains('tipo_firma','CERTIFICADOR'))
                throw ValidationException::withMessages(['arqueo'=>'Este arqueo ya fue certificado.']);
            if(abs((float)$registro->diferencia)>0.00001 && trim((string)$registro->justificacion_diferencia)==='')
                throw ValidationException::withMessages(['arqueo'=>'La diferencia requiere justificación.']);
            $firmas->firmarCertificador($registro,$request->user());
            $registro->estado='CERTIFICADO'; $registro->certificado_at=now();
            $request->user()->loadMissing('rol');
            if($request->user()->rol->nombre==='JefeCaja') $registro->jefe_caja_responsable_id=$request->user()->id;
            else $registro->subjefe_caja_responsable_id=$request->user()->id;
            $registro->save();
            $auditoria->registrar($request->user(),'ARQUEOS_AGENCIA','CERTIFICAR_ARQUEO_AUDITORIA','arqueos_agencia',$registro->id,
                'Certificación de arqueo de auditoría.', ['estado'=>'PENDIENTE'],['estado'=>'CERTIFICADO']);
        },3);
        return redirect()->route($ruta.'.auditoria-agencias.show',$arqueo)->with('success','Arqueo de auditoría certificado.');
    }
    private function verificaciones(ArqueoAgencia $registro,FirmaArqueoService $firmas):array
    {
        $r=[];foreach($registro->firmas as $f)$r[$f->tipo_firma]=$firmas->verificar($registro,$f);return $r;
    }
}
