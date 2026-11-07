<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Agencia;
use App\Models\AsignacionOperativaReceptor;
use App\Models\JornadaReceptor;
use App\Models\Usuario;
use App\Services\Agencias\CumplimientoJornadaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CumplimientoAgenciaController extends Controller
{
    private const CATEGORIAS = [
        'CON_ARQUEO', 'PENDIENTE_HOY', 'SIN_ARQUEO',
        'ANULADO_SIN_REEMPLAZO', 'PROGRAMADO', 'SIN_JORNADA_GENERADA',
    ];

    private function autorizar(Request $request): void
    {
        /** @var Usuario|null $usuario */
        $usuario = $request->user();
        abort_unless($usuario, 401);
        $usuario->loadMissing('rol');
        abort_unless(
            $usuario->estado === 'ACTIVO'
            && $usuario->rol?->nombre === 'Gerencia'
            && (bool) $usuario->rol?->estado,
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }

    private function filtros(Request $request, bool $detalle = false): array
    {
        $reglas = [
            'agencia_id' => ['nullable', 'integer', 'min:1', 'exists:agencias,id'],
            'receptor_id' => ['nullable', 'integer', 'min:1', 'exists:usuarios,id'],
        ];
        if ($detalle) {
            $reglas['fecha'] = ['required', 'date_format:Y-m-d'];
            $reglas['categoria'] = ['required', 'in:'.implode(',', self::CATEGORIAS)];
        } else {
            $reglas['mes'] = ['nullable', 'date_format:Y-m'];
        }
        return $request->validate($reglas);
    }

    /**
     * Consulta de solo lectura: no crea jornadas ni altera registros históricos.
     * Se respetan las clasificaciones ya definidas por el servicio compartido.
     */
    private function registrosDelPeriodo(
        Carbon $inicio,
        Carbon $fin,
        ?int $agenciaId,
        ?int $receptorId,
        CumplimientoJornadaService $cumplimiento
    ): Collection {
        $jornadas = JornadaReceptor::query()
            ->with([
                'agencia', 'ventanilla', 'turno', 'receptor',
                'arqueos' => fn ($q) => $q->where('tipo', 'ORDINARIO')->orderByDesc('id'),
            ])
            ->whereBetween('fecha_operativa', [$inicio->toDateString(), $fin->toDateString()])
            ->when($agenciaId !== null, fn ($q) => $q->where('agencia_id', $agenciaId))
            ->when($receptorId !== null, fn ($q) => $q->where('receptor_usuario_id', $receptorId))
            ->orderBy('fecha_operativa')
            ->orderBy('inicio_programado')
            ->orderBy('id')
            ->get()
            ->map(fn (JornadaReceptor $j) => $cumplimiento->clasificar($j));

        $existentes = [];
        foreach ($jornadas as $j) {
            $clave = implode(':', [
                $j->fecha_operativa->toDateString(), $j->asignacion_personal_id,
                $j->receptor_usuario_id, $j->agencia_id, $j->ventanilla_id, $j->turno_id,
            ]);
            $existentes[$clave] = true;
        }

        $asignaciones = AsignacionOperativaReceptor::query()
            ->with(['agencia', 'ventanilla', 'turno', 'receptor'])
            ->whereDate('fecha_inicio', '<=', $fin->toDateString())
            ->where(function ($q) use ($inicio) {
                $q->whereNull('fecha_fin')
                  ->orWhereDate('fecha_fin', '>=', $inicio->toDateString());
            })
            ->when($agenciaId !== null, fn ($q) => $q->where('agencia_id', $agenciaId))
            ->when($receptorId !== null, fn ($q) => $q->where('receptor_usuario_id', $receptorId))
            ->get();

        $hoy = today()->toDateString();
        foreach ($asignaciones as $asignacion) {
            $desde = $inicio->copy()->max($asignacion->fecha_inicio);
            $hasta = $asignacion->fecha_fin
                ? $fin->copy()->min($asignacion->fecha_fin)
                : $fin->copy();

            for ($dia = $desde->copy(); $dia->lte($hasta); $dia->addDay()) {
                $fecha = $dia->toDateString();
                $clave = implode(':', [
                    $fecha, $asignacion->asignacion_personal_id,
                    $asignacion->receptor_usuario_id, $asignacion->agencia_id,
                    $asignacion->ventanilla_id, $asignacion->turno_id,
                ]);
                if (isset($existentes[$clave])) {
                    continue;
                }
                $existentes[$clave] = true;

                $j = new JornadaReceptor();
                $j->fecha_operativa = $fecha;
                $j->asignacion_personal_id = $asignacion->asignacion_personal_id;
                $j->receptor_usuario_id = $asignacion->receptor_usuario_id;
                $j->agencia_id = $asignacion->agencia_id;
                $j->ventanilla_id = $asignacion->ventanilla_id;
                $j->turno_id = $asignacion->turno_id;
                foreach (['agencia', 'ventanilla', 'turno', 'receptor'] as $relacion) {
                    $j->setRelation($relacion, $asignacion->{$relacion});
                }
                // Coincide con el controlador de Administración: la ausencia de
                // jornada no implica automáticamente incumplimiento definitivo.
                $j->cumplimiento_arqueo = match (true) {
                    $fecha > $hoy => 'PROGRAMADO',
                    $fecha === $hoy => 'PENDIENTE_HOY',
                    default => 'SIN_ARQUEO',
                };
                $j->certificacion_arqueo = 'NO_APLICA';
                $j->arqueo_ordinario_vigente = null;
                $jornadas->push($j);
            }
        }

        return $jornadas->sortBy(fn ($j) => $j->fecha_operativa->toDateString())->values();
    }

    public function index(Request $request, CumplimientoJornadaService $cumplimiento): View
    {
        $this->autorizar($request);
        $f = $this->filtros($request);
        $mes = $f['mes'] ?? now()->format('Y-m');
        $inicio = Carbon::createFromFormat('!Y-m', $mes)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();
        $agenciaId = isset($f['agencia_id']) ? (int) $f['agencia_id'] : null;
        $receptorId = isset($f['receptor_id']) ? (int) $f['receptor_id'] : null;

        $agencias = Agencia::query()->orderBy('nombre')->get(['id', 'codigo', 'nombre']);
        $registros = $this->registrosDelPeriodo($inicio, $fin, $agenciaId, null, $cumplimiento);
        $receptores = Usuario::query()
            ->whereIn('id', $registros->pluck('receptor_usuario_id')->filter()->unique())
            ->orderBy('usuario')->get(['id', 'usuario']);
        if ($receptorId !== null) {
            $registros = $registros->where('receptor_usuario_id', $receptorId)->values();
        }

        $resumen = array_fill_keys(self::CATEGORIAS, 0);
        $resumen['CERTIFICADO'] = 0;
        $resumen['PENDIENTE_CERTIFICACION'] = 0;
        $resumen['TOTAL_JORNADAS'] = $registros->filter(fn ($j) => $j->exists)->count();
        $resumen['TOTAL_ASIGNACIONES_SIN_JORNADA'] = $registros
            ->where('cumplimiento_arqueo', 'SIN_JORNADA_GENERADA')->count();

        $porFecha = $registros->groupBy(fn (JornadaReceptor $j) => $j->fecha_operativa->toDateString());
        $calendario = [];
        $hoy = today()->toDateString();
        for ($dia = $inicio->copy(); $dia->lte($fin); $dia->addDay()) {
            $fecha = $dia->toDateString();
            $grupo = $porFecha->get($fecha, collect());
            $cuentas = array_fill_keys(self::CATEGORIAS, 0);
            foreach ($grupo as $j) {
                if (array_key_exists($j->cumplimiento_arqueo, $cuentas)) {
                    $cuentas[$j->cumplimiento_arqueo]++;
                    $resumen[$j->cumplimiento_arqueo]++;
                }
                if (in_array($j->certificacion_arqueo, ['CERTIFICADO', 'PENDIENTE_CERTIFICACION'], true)) {
                    $resumen[$j->certificacion_arqueo]++;
                }
            }
            $calendario[] = [
                'fecha' => $fecha, 'dia' => $dia->day,
                'es_hoy' => $fecha === $hoy, 'es_futuro' => $fecha > $hoy,
                'total' => $grupo->count(), 'estados' => $cuentas,
            ];
        }

        $mesAnterior = $inicio->copy()->subMonthNoOverflow()->format('Y-m');
        $mesSiguiente = $inicio->copy()->addMonthNoOverflow()->format('Y-m');
        $mesActual = now()->format('Y-m');
        return view('gerencia.cumplimiento-agencias.index', compact(
            'agencias', 'receptores', 'mes', 'inicio', 'mesAnterior', 'mesSiguiente',
            'mesActual', 'agenciaId', 'receptorId', 'calendario', 'resumen'
        ));
    }

    public function detalle(Request $request, CumplimientoJornadaService $cumplimiento): JsonResponse
    {
        $this->autorizar($request);
        $f = $this->filtros($request, true);
        $fecha = Carbon::createFromFormat('!Y-m-d', $f['fecha']);
        $registros = $this->registrosDelPeriodo(
            $fecha->copy(), $fecha->copy(),
            isset($f['agencia_id']) ? (int) $f['agencia_id'] : null,
            isset($f['receptor_id']) ? (int) $f['receptor_id'] : null,
            $cumplimiento
        )->filter(fn (JornadaReceptor $j) => $j->cumplimiento_arqueo === $f['categoria'])->values();

        return response()->json([
            'fecha' => $f['fecha'],
            'categoria' => $f['categoria'],
            'total' => $registros->count(),
            'jornadas' => $registros->map(fn (JornadaReceptor $j) => [
                'id' => $j->id,
                'fecha' => $j->fecha_operativa->format('d/m/Y'),
                'agencia' => $j->agencia?->nombre ?? '—',
                'ventanilla' => $j->ventanilla?->codigo ?? '—',
                'receptor' => $j->receptor?->nombre_completo ?? $j->receptor?->usuario ?? '—',
                'turno' => $j->turno?->nombre ?? '—',
                'arqueo' => $j->arqueo_ordinario_vigente?->numero_arqueo ?? '—',
                'certificacion' => $j->certificacion_arqueo,
            ])->all(),
        ]);
    }
}
