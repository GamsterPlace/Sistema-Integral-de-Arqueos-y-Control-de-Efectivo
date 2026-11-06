<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\Agencia;
use App\Models\AsignacionOperativaReceptor;
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
        $this->auditor($request);
        $ruta = 'auditoria';

        $agenciaId = $request->integer('agencia');
        $receptorId = $request->integer('receptor');
        $agenciaSeleccionada = null;
        $receptorSeleccionado = null;
        $agencias = collect();
        $receptores = collect();
        $arqueos = null;

        if (!$agenciaId) {
            $agencias = Agencia::query()->orderBy('nombre')->get();
            $resumenAgencias = ArqueoAgencia::query()
                ->where('tipo', 'AUDITORIA')
                ->selectRaw('agencia_id, COUNT(*) total, SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END) certificados, SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END) anulados', ['CERTIFICADO', 'ANULADO'])
                ->groupBy('agencia_id')->get()->keyBy('agencia_id');
            $receptoresPorAgencia = AsignacionOperativaReceptor::query()
                ->vigentes()->select('agencia_id','receptor_usuario_id')->distinct()->get()
                ->groupBy('agencia_id')->map(fn ($items) => $items->count());
            return view('agencias.auditoria.index', compact(
                'ruta', 'agencias', 'resumenAgencias', 'receptoresPorAgencia',
                'agenciaSeleccionada', 'receptorSeleccionado', 'receptores', 'arqueos'
            ));
        }

        $agenciaSeleccionada = Agencia::query()->findOrFail($agenciaId);

        if (!$receptorId) {
            $idsActuales = AsignacionOperativaReceptor::query()
                ->where('agencia_id', $agenciaId)->vigentes()
                ->pluck('receptor_usuario_id');
            $idsHistoricos = ArqueoAgencia::query()->where('tipo', 'AUDITORIA')
                ->where('agencia_id', $agenciaId)->pluck('receptor_usuario_id');
            $ids = $idsActuales->merge($idsHistoricos)->unique()->values();
            $receptores = \App\Models\Usuario::query()
                ->whereIn('id', $ids)->get()->sortBy('nombre_completo')->values();
            $resumenReceptores = ArqueoAgencia::query()
                ->where('tipo', 'AUDITORIA')->where('agencia_id', $agenciaId)
                ->selectRaw('receptor_usuario_id, COUNT(*) total, SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END) certificados, SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END) anulados, MAX(fecha_operativa) ultima_fecha', ['CERTIFICADO','ANULADO'])
                ->groupBy('receptor_usuario_id')->get()->keyBy('receptor_usuario_id');
            $asignacionesReceptor = AsignacionOperativaReceptor::query()
                ->with(['ventanilla', 'turno'])->where('agencia_id', $agenciaId)
                ->vigentes()->get()->groupBy('receptor_usuario_id');
            return view('agencias.auditoria.index', compact(
                'ruta', 'agencias', 'agenciaSeleccionada', 'receptorSeleccionado',
                'receptores', 'resumenReceptores', 'asignacionesReceptor', 'arqueos'
            ));
        }

        $receptorSeleccionado = \App\Models\Usuario::query()->findOrFail($receptorId);
        $existe = AsignacionOperativaReceptor::query()->where('agencia_id', $agenciaId)
            ->where('receptor_usuario_id', $receptorId)->exists()
            || ArqueoAgencia::query()->where('tipo', 'AUDITORIA')
                ->where('agencia_id', $agenciaId)->where('receptor_usuario_id', $receptorId)->exists();
        abort_unless($existe, 404);

        $filtros = $request->validate([
            'buscar' => ['nullable','string','max:100'],
            'estado' => ['nullable','in:PENDIENTE_FIRMA_RECEPTOR,PENDIENTE,CERTIFICADO,ANULADO'],
            'desde' => ['nullable','date'],
            'hasta' => ['nullable','date','after_or_equal:desde'],
            'ventanilla' => ['nullable','integer'],
            'diferencia' => ['nullable','in:todos,con,sin'],
        ]);

        $consulta = ArqueoAgencia::query()->where('tipo','AUDITORIA')
            ->where('agencia_id',$agenciaId)->where('receptor_usuario_id',$receptorId);
        if (filled($filtros['buscar'] ?? null)) {
            $buscar = $filtros['buscar'];
            $consulta->where('numero_arqueo','like','%'.$buscar.'%');
        }
        if (filled($filtros['estado'] ?? null)) $consulta->where('estado',$filtros['estado']);
        if (filled($filtros['desde'] ?? null)) $consulta->whereDate('fecha_operativa','>=',$filtros['desde']);
        if (filled($filtros['hasta'] ?? null)) $consulta->whereDate('fecha_operativa','<=',$filtros['hasta']);
        if (filled($filtros['ventanilla'] ?? null)) $consulta->where('ventanilla_id',$filtros['ventanilla']);
        if (($filtros['diferencia'] ?? 'todos') === 'con') $consulta->where('diferencia','!=',0);
        if (($filtros['diferencia'] ?? 'todos') === 'sin') $consulta->where('diferencia',0);
        $arqueos = $consulta->orderByDesc('fecha_operativa')->orderByDesc('id')
            ->paginate(15)->withQueryString();
        $ventanillas = \App\Models\VentanillaAgencia::query()
            ->where('agencia_id',$agenciaId)->orderBy('codigo')->get();

        return view('agencias.auditoria.index', compact(
            'ruta','agencias','agenciaSeleccionada','receptorSeleccionado',
            'receptores','arqueos','ventanillas'
        ));
    }

    public function create(Request $request, AccesoAgenciaService $acceso): View
    {
        $this->auditor($request);

        $agencias = Agencia::query()
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();

        $asignaciones = $this->asignacionesDisponibles()->get();

        return view('agencias.auditoria.create', [
            'ruta' => 'auditoria',
            'agencias' => $agencias,
            'asignaciones' => $asignaciones,
            'billetes' => self::BILLETES,
            'monedas' => self::MONEDAS,
        ]);
    }

    private function asignacionesDisponibles()
    {
        $hoy = today()->toDateString();

        return AsignacionOperativaReceptor::query()
            ->with(['agencia', 'receptor.datosPersonales', 'ventanilla', 'turno', 'asignacionPersonal'])
            ->where('estado', true)
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->whereHas('agencia', fn ($q) => $q->where('estado', true))
            ->whereHas('ventanilla', fn ($q) => $q->where('estado', true))
            ->whereHas('turno', fn ($q) => $q->where('estado', true))
            ->whereHas('asignacionPersonal', function ($q) use ($hoy) {
                $q->where('cargo', 'RECEPTOR')
                    ->where('estado', true)
                    ->whereDate('fecha_inicio', '<=', $hoy)
                    ->where(function ($q) use ($hoy) {
                        $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $hoy);
                    });
            });
    }

    public function store(
        Request $request,
        AccesoAgenciaService $acceso,
        FirmaArqueoService $firmas,
        AuditoriaService $auditoria
    ) {
        $this->auditor($request);

        $d = $request->validate([
            'asignacion_operativa_id' => ['required', 'integer', 'exists:asignaciones_operativas_receptor,id'],
            'billetes' => ['required', 'array'],
            'billetes.*' => ['required', 'integer', 'min:0', 'max:10000000'],
            'monedas' => ['required', 'array'],
            'monedas.*' => ['required', 'integer', 'min:0', 'max:10000000'],
            'total_corte' => ['required', 'numeric', 'min:0', 'max:999999999999.99', 'decimal:0,2'],
            'justificacion_diferencia' => ['nullable', 'string', 'max:2000'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'confirmar' => ['accepted'],
        ]);

        foreach (['billetes' => self::BILLETES, 'monedas' => self::MONEDAS] as $campo => $permitidas) {
            if (array_diff(array_keys($d[$campo]), $permitidas)
                || array_diff($permitidas, array_keys($d[$campo]))) {
                throw ValidationException::withMessages([
                    $campo => 'Las denominaciones enviadas no coinciden con las permitidas.',
                ]);
            }
        }

        $detalles = [];
        $sumBil = 0;
        $sumMon = 0;

        foreach ([['BILLETE', 'billetes', self::BILLETES], ['MONEDA', 'monedas', self::MONEDAS]] as [$tipo, $campo, $valores]) {
            foreach ($valores as $valor) {
                $centavos = (int) round((float) $valor * 100);
                $cantidad = (int) $d[$campo][$valor];
                $subtotal = $centavos * $cantidad;

                if ($tipo === 'BILLETE') {
                    $sumBil += $subtotal;
                } else {
                    $sumMon += $subtotal;
                }

                $detalles[] = [
                    'tipo' => $tipo,
                    'denominacion' => number_format($centavos / 100, 2, '.', ''),
                    'concepto' => null,
                    'cantidad' => $cantidad,
                    'subtotal' => number_format($subtotal / 100, 2, '.', ''),
                ];
            }
        }

        $total = $sumBil + $sumMon;
        $corte = (int) round((float) $d['total_corte'] * 100);
        $dif = $total - $corte;

        if ($total > 99999999999999 || abs($dif) > 99999999999999) {
            throw ValidationException::withMessages(['billetes' => 'Monto fuera del límite permitido.']);
        }

        if ($dif !== 0 && trim((string) ($d['justificacion_diferencia'] ?? '')) === '') {
            throw ValidationException::withMessages(['justificacion_diferencia' => 'Justifique la diferencia.']);
        }

        $arqueo = DB::transaction(function () use (
            $request, $d, $detalles, $sumBil, $sumMon, $total, $corte, $dif, $firmas, $auditoria
        ) {
            $hoy = today()->toDateString();

            $a = $this->asignacionesDisponibles()
                ->lockForUpdate()
                ->find($d['asignacion_operativa_id']);

            if (!$a || !$a->receptor || !$a->asignacionPersonal
                || (int) $a->asignacionPersonal->usuario_id !== (int) $a->receptor_usuario_id
                || (int) $a->asignacionPersonal->agencia_id !== (int) $a->agencia_id
                || (int) $a->ventanilla->agencia_id !== (int) $a->agencia_id) {
                throw ValidationException::withMessages([
                    'asignacion_operativa_id' => 'La asignación operativa ya no es válida.',
                ]);
            }

            $pendiente = ArqueoAgencia::query()
                ->where('tipo', 'AUDITORIA')
                ->where('agencia_id', $a->agencia_id)
                ->where('ventanilla_id', $a->ventanilla_id)
                ->where('receptor_usuario_id', $a->receptor_usuario_id)
                ->whereIn('estado', ['PENDIENTE_FIRMA_RECEPTOR', 'PENDIENTE'])
                ->exists();

            if ($pendiente) {
                throw ValidationException::withMessages([
                    'asignacion_operativa_id' => 'Existe un arqueo de auditoría pendiente para este receptor y ventanilla.',
                ]);
            }

            $u = $request->user();
            $u->loadMissing('rol');

            $registro = ArqueoAgencia::create([
                'numero_arqueo' => 'AU-'.now()->format('YmdHis').'-'.strtoupper(Str::random(12)),
                'agencia_id' => $a->agencia_id,
                'ventanilla_id' => $a->ventanilla_id,
                'jornada_id' => null,
                'receptor_usuario_id' => $a->receptor_usuario_id,
                'creado_por' => $u->id,
                'tipo' => 'AUDITORIA',
                'estado' => 'PENDIENTE_FIRMA_RECEPTOR',
                'fecha_operativa' => $hoy,
                'inicio_at' => now(),
                'finalizado_at' => now(),
                'cierra_turno' => false,
                'fuera_fecha_ordinaria' => false,
                'agencia_codigo_historico' => $a->agencia->codigo,
                'agencia_nombre_historico' => $a->agencia->nombre,
                'ventanilla_codigo_historico' => $a->ventanilla->codigo,
                'receptor_nombre_historico' => $a->receptor->nombre_completo ?: $a->receptor->usuario,
                'turno_nombre_historico' => $a->turno->nombre,
                'total_billetes' => number_format($sumBil / 100, 2, '.', ''),
                'total_monedas' => number_format($sumMon / 100, 2, '.', ''),
                'total_otros_valores' => '0.00',
                'total_arqueado' => number_format($total / 100, 2, '.', ''),
                'total_corte' => number_format($corte / 100, 2, '.', ''),
                'diferencia' => number_format($dif / 100, 2, '.', ''),
                'justificacion_diferencia' => $d['justificacion_diferencia'] ?? null,
                'observaciones' => $d['observaciones'] ?? null,
            ]);

            $registro->detalles()->createMany($detalles);
            $firmas->firmarAuditor($registro, $u);

            $auditoria->registrar(
                $u, 'ARQUEOS_AGENCIA', 'CREAR_ARQUEO_AUDITORIA', 'arqueos_agencia', $registro->id,
                'Conteo de auditoría firmado por Auditoría, pendiente de confirmación del receptor.',
                null,
                [
                    'numero_arqueo' => $registro->numero_arqueo,
                    'asignacion_operativa_id' => $a->id,
                    'diferencia' => $registro->diferencia,
                ]
            );

            return $registro;
        }, 3);

        return redirect()
            ->route('auditoria.auditoria-agencias.show', $arqueo->id)
            ->with('success', 'Arqueo de auditoría registrado. Pendiente de firma del receptor.');
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

    /** Impresión PDF: acceso limitado al ámbito de cada rol. */
    public function imprimir(Request $request, int $arqueo, AccesoAgenciaService $acceso, FirmaArqueoService $firmas)
    {
        $usuario = $request->user();
        $usuario->loadMissing('rol');
        $rol = $usuario->rol?->nombre;
        $consulta = ArqueoAgencia::query()->where('tipo', 'AUDITORIA');

        if ($rol === 'Receptor') {
            $consulta->where('receptor_usuario_id', $usuario->id);
        } elseif (in_array($rol, ['JefeCaja', 'SubjefeCaja'], true)) {
            [$agencias] = $this->caja($request, $acceso);
            $consulta->whereIn('agencia_id', $agencias);
        } elseif ($rol !== 'Auditoria') {
            abort(403);
        }

        $registro = $consulta->with(['detalles', 'firmas'])->findOrFail($arqueo);
        $verificaciones = $this->verificaciones($registro, $firmas);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('agencias.auditoria.imprimir', [
            'registro' => $registro,
            'verificaciones' => $verificaciones,
            'billetes' => self::BILLETES,
            'monedas' => self::MONEDAS,
        ])->setPaper('letter', 'portrait')->stream('arqueo-auditoria-'.$registro->numero_arqueo.'.pdf');
    }
}
