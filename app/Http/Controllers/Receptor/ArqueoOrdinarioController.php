<?php

namespace App\Http\Controllers\Receptor;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\JornadaReceptor;
use App\Services\Agencias\FirmaArqueoService;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArqueoOrdinarioController extends Controller
{
    private const BILLETES = [200, 100, 50, 20, 10, 5, 2, 1];
    private const MONEDAS = ['1.00', '0.50', '0.25', '0.10', '0.05'];

    public function index(Request $request): View
    {
        $usuario = $request->user();
        $jornadas = JornadaReceptor::with(['agencia', 'ventanilla', 'turno'])
            ->where('receptor_usuario_id', $usuario->id)
            ->whereDate('fecha_operativa', now()->toDateString())
            ->whereHas('asignacionPersonal', fn ($q) => $q->where('usuario_id', $usuario->id)->where('cargo', 'RECEPTOR')->where('estado', true))
            ->orderBy('inicio_programado')->get();
        $arqueos = ArqueoAgencia::with(['agencia', 'ventanilla'])
            ->where('receptor_usuario_id', $usuario->id)->where('tipo', 'ORDINARIO')
            ->orderByDesc('id')->paginate(15);
        return view('receptor.arqueos.index', compact('jornadas', 'arqueos'));
    }

    public function create(Request $request, int $jornada): View
    {
        $registro = $this->jornadaAutorizada($request, $jornada);
        if ($registro->fecha_operativa->toDateString() !== now()->toDateString()) {
            abort(403, 'Los arqueos fuera de fecha requieren habilitación especial.');
        }
        $this->asegurarDisponible($registro->id);
        return view('receptor.arqueos.create', [
            'jornada' => $registro,
            'billetes' => self::BILLETES,
            'monedas' => self::MONEDAS,
        ]);
    }

    public function store(Request $request, int $jornada, FirmaArqueoService $firmas, AuditoriaService $auditoria)
    {
        $usuario = $request->user();
        $datos = $request->validate([
            'billetes' => ['required', 'array'],
            'billetes.*' => ['required', 'integer', 'min:0', 'max:10000000'],
            'monedas' => ['required', 'array'],
            'monedas.*' => ['required', 'integer', 'min:0', 'max:10000000'],
            'total_corte' => ['required', 'numeric', 'min:0', 'max:999999999999.99', 'decimal:0,2'],
            'justificacion_diferencia' => ['nullable', 'string', 'max:2000'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'confirmar_firma' => ['accepted'],
        ]);
        if (array_diff(array_keys($datos['billetes']), array_map('strval', self::BILLETES)) ||
            array_diff(array_keys($datos['monedas']), self::MONEDAS)) {
            throw ValidationException::withMessages(['billetes' => 'Se enviaron denominaciones no permitidas.']);
        }
        $detalle = []; $billetesCentavos = 0; $monedasCentavos = 0;
        foreach (self::BILLETES as $valor) {
            $cantidad = (int) ($datos['billetes'][(string) $valor] ?? 0);
            $subtotal = $valor * 100 * $cantidad;
            $billetesCentavos += $subtotal;
            $detalle[] = ['tipo'=>'BILLETE','denominacion'=>number_format($valor,2,'.',''),'concepto'=>null,'cantidad'=>$cantidad,'subtotal'=>number_format($subtotal/100,2,'.','')];
        }
        foreach (self::MONEDAS as $valor) {
            $cantidad = (int) ($datos['monedas'][$valor] ?? 0);
            $subtotal = (int) round(((float) $valor) * 100) * $cantidad;
            $monedasCentavos += $subtotal;
            $detalle[] = ['tipo'=>'MONEDA','denominacion'=>$valor,'concepto'=>null,'cantidad'=>$cantidad,'subtotal'=>number_format($subtotal/100,2,'.','')];
        }
        $totalCentavos = $billetesCentavos + $monedasCentavos;
        $corteCentavos = (int) round(((float) $datos['total_corte']) * 100);
        $diferenciaCentavos = $totalCentavos - $corteCentavos;
        if ($totalCentavos > 99999999999999 || abs($diferenciaCentavos) > 99999999999999) {
            throw ValidationException::withMessages(['billetes' => 'El monto excede la capacidad permitida.']);
        }
        if ($diferenciaCentavos !== 0 && trim((string) ($datos['justificacion_diferencia'] ?? '')) === '') {
            throw ValidationException::withMessages(['justificacion_diferencia' => 'Debe justificar la diferencia encontrada.']);
        }
        $arqueo = DB::transaction(function () use ($request, $jornada, $usuario, $datos, $detalle, $billetesCentavos, $monedasCentavos, $totalCentavos, $corteCentavos, $diferenciaCentavos, $firmas, $auditoria) {
            // Serializa los registros para esta jornada y evita dos arqueos simultáneos.
            $registro = JornadaReceptor::query()->lockForUpdate()->findOrFail($jornada);
            $registro->load(['agencia', 'ventanilla', 'turno', 'asignacionPersonal', 'receptor.datosPersonales']);
            $this->validarJornada($request, $registro);
            if ($registro->fecha_operativa->toDateString() !== now()->toDateString()) {
                throw ValidationException::withMessages(['jornada' => 'La jornada no corresponde a la fecha de hoy.']);
            }
            $this->asegurarDisponible($registro->id);
            $arqueo = ArqueoAgencia::create([
                'numero_arqueo' => 'AG-'.now()->format('YmdHis').'-'.strtoupper(Str::random(12)),
                'agencia_id' => $registro->agencia_id,
                'ventanilla_id' => $registro->ventanilla_id,
                'jornada_id' => $registro->id,
                'receptor_usuario_id' => $usuario->id,
                'creado_por' => $usuario->id,
                'tipo' => 'ORDINARIO',
                'estado' => 'PENDIENTE',
                'fecha_operativa' => $registro->fecha_operativa,
                'inicio_at' => now(),
                'finalizado_at' => now(),
                'fuera_fecha_ordinaria' => false,
                'cierra_turno' => false,
                'agencia_codigo_historico' => $registro->agencia->codigo,
                'agencia_nombre_historico' => $registro->agencia->nombre,
                'ventanilla_codigo_historico' => $registro->ventanilla->codigo,
                'receptor_nombre_historico' => $usuario->nombre_completo,
                'turno_nombre_historico' => $registro->turno->nombre,
                'total_billetes' => number_format($billetesCentavos/100,2,'.',''),
                'total_monedas' => number_format($monedasCentavos/100,2,'.',''),
                'total_otros_valores' => '0.00',
                'total_arqueado' => number_format($totalCentavos/100,2,'.',''),
                'total_corte' => number_format($corteCentavos/100,2,'.',''),
                'diferencia' => number_format($diferenciaCentavos/100,2,'.',''),
                'justificacion_diferencia' => $datos['justificacion_diferencia'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
                'firmado_receptor_at' => now(),
            ]);
            $arqueo->detalles()->createMany($detalle);
            $firmas->firmarReceptor($arqueo, $usuario);
            $auditoria->registrar($usuario, 'ARQUEOS_AGENCIA', 'FIRMA_ARQUEO_ORDINARIO', 'arqueos_agencia', $arqueo->id,
                'Arqueo ordinario firmado por el receptor.', null,
                ['numero_arqueo'=>$arqueo->numero_arqueo,'jornada_id'=>$registro->id,'total_arqueado'=>$arqueo->total_arqueado,'total_corte'=>$arqueo->total_corte,'diferencia'=>$arqueo->diferencia]);
            return $arqueo;
        }, 3);
        return redirect()->route('receptor.arqueos.show', $arqueo->id)->with('success', 'Arqueo firmado correctamente.');
    }

    public function show(Request $request, int $arqueo, FirmaArqueoService $firmas): View
    {
        $registro = ArqueoAgencia::with(['agencia', 'ventanilla', 'detalles', 'firmas'])
            ->where('receptor_usuario_id', $request->user()->id)->where('tipo', 'ORDINARIO')->findOrFail($arqueo);
        $firma = $registro->firmas->firstWhere('tipo_firma', 'RECEPTOR');
        return view('receptor.arqueos.show', [
            'arqueo' => $registro,
            'firmaValida' => $firma ? $firmas->verificar($registro, $firma) : false,
        ]);
    }

    private function jornadaAutorizada(Request $request, int $id): JornadaReceptor
    {
        $registro = JornadaReceptor::with(['agencia','ventanilla','turno','asignacionPersonal'])->findOrFail($id);
        $this->validarJornada($request, $registro);
        return $registro;
    }

    private function validarJornada(Request $request, JornadaReceptor $jornada): void
    {
        $asignacion = $jornada->asignacionPersonal;
        abort_unless((int) $jornada->receptor_usuario_id === (int) $request->user()->id
            && $asignacion && (int) $asignacion->usuario_id === (int) $request->user()->id
            && (int) $asignacion->agencia_id === (int) $jornada->agencia_id
            && $asignacion->cargo === 'RECEPTOR' && $asignacion->estado
            && in_array($asignacion->tipo, ['PERMANENTE','TEMPORAL'], true)
            && $asignacion->fecha_inicio->toDateString() <= $jornada->fecha_operativa->toDateString()
            && (!$asignacion->fecha_fin || $asignacion->fecha_fin->toDateString() >= $jornada->fecha_operativa->toDateString())
            && $jornada->agencia?->estado && $jornada->ventanilla?->estado
            && (int) $jornada->ventanilla->agencia_id === (int) $jornada->agencia_id
            && $jornada->turno?->estado, 403, 'No tiene autorización para este turno y ventanilla.');
    }

    private function asegurarDisponible(int $jornadaId): void
    {
        if (ArqueoAgencia::where('jornada_id', $jornadaId)->where('tipo', 'ORDINARIO')->exists()) {
            throw ValidationException::withMessages(['jornada' => 'Ya existe un arqueo ordinario para esta jornada. Si fue anulado, su reemplazo requiere el procedimiento autorizado.']);
        }
    }
}
