<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Agencia;
use App\Models\AsignacionOperativaReceptor;
use App\Models\JornadaReceptor;
use App\Models\Usuario;
use App\Services\Agencias\CumplimientoJornadaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CumplimientoArqueoAgenciaController extends Controller
{
    private const CATEGORIAS = [
        'CON_ARQUEO',
        'PENDIENTE_HOY',
        'SIN_ARQUEO',
        'ANULADO_SIN_REEMPLAZO',
        'PROGRAMADO',
        'SIN_JORNADA_GENERADA',
    ];

    private function autorizar(Request $request): void
    {
        /** @var Usuario|null $usuario */
        $usuario = $request->user();

        abort_unless($usuario, 401);

        $usuario->loadMissing('rol');

        abort_unless(
            $usuario->estado === 'ACTIVO'
            && $usuario->rol?->nombre === 'Administrador'
            && (bool) $usuario->rol?->estado,
            403
        );
    }

    private function consulta(?int $agenciaId, ?int $receptorId)
    {
        return JornadaReceptor::query()
            ->with([
                'agencia',
                'ventanilla',
                'turno',
                'receptor',
                'arqueos' => fn ($query) => $query
                    ->where('tipo', 'ORDINARIO')
                    ->orderByDesc('id'),
            ])
            ->when(
                $agenciaId !== null,
                fn ($query) => $query->where('agencia_id', $agenciaId)
            )
            ->when(
                $receptorId !== null,
                fn ($query) => $query->where('receptor_usuario_id', $receptorId)
            );
    }

    private function filtros(Request $request): array
    {
        return $request->validate([
            'mes' => ['nullable', 'date_format:Y-m'],
            'agencia_id' => [
                'nullable',
                'integer',
                'min:1',
                'exists:agencias,id',
            ],
            'receptor_id' => [
                'nullable',
                'integer',
                'min:1',
                'exists:usuarios,id',
            ],
        ]);
    }

    /**
     * Cruza las asignaciones operativas con las jornadas efectivamente
     * generadas.
     *
     * Una asignación sin jornada se presenta como SIN_JORNADA_GENERADA.
     * No se clasifica automáticamente como incumplimiento porque
     * los días inactivos y las excepciones corresponden a la
     * lógica operativa existente.
     *
     * Esta consulta es de solo lectura.
     * No genera jornadas históricas.
     */
    private function registrosDelPeriodo(
        Carbon $inicio,
        Carbon $fin,
        ?int $agenciaId,
        ?int $receptorId,
        CumplimientoJornadaService $cumplimiento
    ) {
        $jornadas = $this->consulta($agenciaId, $receptorId)
            ->whereBetween('fecha_operativa', [
                $inicio->toDateString(),
                $fin->toDateString(),
            ])
            ->orderBy('fecha_operativa')
            ->orderBy('inicio_programado')
            ->orderBy('id')
            ->get()
            ->map(
                fn (JornadaReceptor $j) => $cumplimiento->clasificar($j)
            );

        $existentes = [];

        foreach ($jornadas as $j) {
            $clave = implode(':', [
                $j->fecha_operativa->toDateString(),
                $j->asignacion_personal_id,
                $j->receptor_usuario_id,
                $j->agencia_id,
                $j->ventanilla_id,
                $j->turno_id,
            ]);

            $existentes[$clave] = true;
        }

        $asignaciones = AsignacionOperativaReceptor::query()
            ->with([
                'agencia',
                'ventanilla',
                'turno',
                'receptor',
            ])
            ->whereDate(
                'fecha_inicio',
                '<=',
                $fin->toDateString()
            )
            ->where(function ($q) use ($inicio) {
                $q->whereNull('fecha_fin')
                  ->orWhereDate(
                      'fecha_fin',
                      '>=',
                      $inicio->toDateString()
                  );
            })
            ->when(
                $agenciaId !== null,
                fn ($q) => $q->where('agencia_id', $agenciaId)
            )
            ->when(
                $receptorId !== null,
                fn ($q) => $q->where('receptor_usuario_id', $receptorId)
            )
            ->get();

        foreach ($asignaciones as $asignacion) {
            $desde = $inicio->copy()->max(
                $asignacion->fecha_inicio
            );

            $hasta = $asignacion->fecha_fin
                ? $fin->copy()->min($asignacion->fecha_fin)
                : $fin->copy();

            for (
                $dia = $desde->copy();
                $dia->lte($hasta);
                $dia->addDay()
            ) {
                $fecha = $dia->toDateString();

                $clave = implode(':', [
                    $fecha,
                    $asignacion->asignacion_personal_id,
                    $asignacion->receptor_usuario_id,
                    $asignacion->agencia_id,
                    $asignacion->ventanilla_id,
                    $asignacion->turno_id,
                ]);

                if (isset($existentes[$clave])) {
                    continue;
                }

                $existentes[$clave] = true;

                $j = new JornadaReceptor();

                $j->fecha_operativa = $fecha;
                $j->asignacion_personal_id =
                    $asignacion->asignacion_personal_id;
                $j->receptor_usuario_id =
                    $asignacion->receptor_usuario_id;
                $j->agencia_id = $asignacion->agencia_id;
                $j->ventanilla_id = $asignacion->ventanilla_id;
                $j->turno_id = $asignacion->turno_id;

                $j->setRelation(
                    'agencia',
                    $asignacion->agencia
                );

                $j->setRelation(
                    'ventanilla',
                    $asignacion->ventanilla
                );

                $j->setRelation(
                    'turno',
                    $asignacion->turno
                );

                $j->setRelation(
                    'receptor',
                    $asignacion->receptor
                );

                $hoy = now()->toDateString();

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

        return $jornadas
            ->sortBy(
                fn ($j) => $j->fecha_operativa->toDateString()
            )
            ->values();
    }

    public function index(
        Request $request,
        CumplimientoJornadaService $cumplimiento
    ): View {
        $this->autorizar($request);

        $filtros = $this->filtros($request);

        $mes = $filtros['mes'] ?? now()->format('Y-m');

        $inicio = Carbon::createFromFormat(
            '!Y-m',
            $mes
        )->startOfMonth();

        $fin = $inicio->copy()->endOfMonth();

        $agenciaId = isset($filtros['agencia_id'])
            ? (int) $filtros['agencia_id']
            : null;

        $receptorId = isset($filtros['receptor_id'])
            ? (int) $filtros['receptor_id']
            : null;

        $agencias = Agencia::query()
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre']);

        $registros = $this->registrosDelPeriodo(
            $inicio,
            $fin,
            $agenciaId,
            $receptorId,
            $cumplimiento
        );

        $receptorIds = $this->registrosDelPeriodo(
            $inicio,
            $fin,
            $agenciaId,
            null,
            $cumplimiento
        )
            ->pluck('receptor_usuario_id')
            ->unique();

        $receptores = Usuario::query()
            ->whereIn('id', $receptorIds)
            ->orderBy('usuario')
            ->get(['id', 'usuario']);

        $resumen = array_fill_keys(
            self::CATEGORIAS,
            0
        );

        $resumen['CERTIFICADO'] = 0;
        $resumen['PENDIENTE_CERTIFICACION'] = 0;

        $resumen['TOTAL_JORNADAS'] = $registros
            ->filter(fn ($j) => $j->exists)
            ->count();

        $resumen['TOTAL_ASIGNACIONES_SIN_JORNADA'] = $registros
            ->where(
                'cumplimiento_arqueo',
                'SIN_JORNADA_GENERADA'
            )
            ->count();

        $porFecha = $registros->groupBy(
            fn (JornadaReceptor $j) =>
                $j->fecha_operativa->toDateString()
        );

        $calendario = [];

        $hoy = now()->toDateString();

        for (
            $dia = $inicio->copy();
            $dia->lte($fin);
            $dia->addDay()
        ) {
            $fecha = $dia->toDateString();

            $grupo = $porFecha->get(
                $fecha,
                collect()
            );

            $cuentas = array_fill_keys(
                self::CATEGORIAS,
                0
            );

            foreach ($grupo as $jornada) {
                $estado = $jornada->cumplimiento_arqueo;

                if (array_key_exists($estado, $cuentas)) {
                    $cuentas[$estado]++;
                    $resumen[$estado]++;
                }

                $certificacion =
                    $jornada->certificacion_arqueo;

                if (
                    in_array(
                        $certificacion,
                        [
                            'CERTIFICADO',
                            'PENDIENTE_CERTIFICACION',
                        ],
                        true
                    )
                ) {
                    $resumen[$certificacion]++;
                }
            }

            $calendario[] = [
                'fecha' => $fecha,
                'dia' => $dia->day,
                'es_hoy' => $fecha === $hoy,
                'es_futuro' => $fecha > $hoy,
                'total' => $grupo->count(),
                'estados' => $cuentas,
            ];
        }

        $mesAnterior = $inicio
            ->copy()
            ->subMonthNoOverflow()
            ->format('Y-m');

        $mesSiguiente = $inicio
            ->copy()
            ->addMonthNoOverflow()
            ->format('Y-m');

        $mesActual = now()->format('Y-m');

        return view(
            'administrador.cumplimientos-agencias.index',
            compact(
                'agencias',
                'receptores',
                'mes',
                'inicio',
                'mesAnterior',
                'mesSiguiente',
                'mesActual',
                'agenciaId',
                'receptorId',
                'calendario',
                'resumen'
            )
        );
    }

    public function detalle(
        Request $request,
        CumplimientoJornadaService $cumplimiento
    ): JsonResponse {
        $this->autorizar($request);

        $filtros = $request->validate([
            'fecha' => [
                'required',
                'date_format:Y-m-d',
            ],
            'categoria' => [
                'required',
                'in:'.implode(',', self::CATEGORIAS),
            ],
            'agencia_id' => [
                'nullable',
                'integer',
                'min:1',
                'exists:agencias,id',
            ],
            'receptor_id' => [
                'nullable',
                'integer',
                'min:1',
                'exists:usuarios,id',
            ],
        ]);

        $agenciaId = isset($filtros['agencia_id'])
            ? (int) $filtros['agencia_id']
            : null;

        $receptorId = isset($filtros['receptor_id'])
            ? (int) $filtros['receptor_id']
            : null;

        $fecha = Carbon::createFromFormat(
            '!Y-m-d',
            $filtros['fecha']
        );

        $jornadas = $this->registrosDelPeriodo(
            $fecha->copy(),
            $fecha->copy(),
            $agenciaId,
            $receptorId,
            $cumplimiento
        )
            ->filter(
                fn (JornadaReceptor $j) =>
                    $j->cumplimiento_arqueo
                    === $filtros['categoria']
            )
            ->values();

        return response()->json([
            'fecha' => $filtros['fecha'],
            'categoria' => $filtros['categoria'],
            'total' => $jornadas->count(),

            'jornadas' => $jornadas
                ->map(
                    fn (JornadaReceptor $j) => [
                        'id' => $j->id,

                        'fecha' => $j->fecha_operativa
                            ->format('d/m/Y'),

                        'agencia' => $j->agencia?->nombre
                            ?? '—',

                        'ventanilla' => $j->ventanilla?->codigo
                            ?? '—',

                        'receptor' =>
                            $j->receptor?->nombre_completo
                            ?? $j->receptor?->usuario
                            ?? '—',

                        'turno' => $j->turno?->nombre
                            ?? '—',

                        'arqueo' =>
                            $j->arqueo_ordinario_vigente
                            ?->numero_arqueo
                            ?? '—',

                        'certificacion' =>
                            $j->certificacion_arqueo,
                    ]
                )
                ->all(),
        ]);
    }
}
