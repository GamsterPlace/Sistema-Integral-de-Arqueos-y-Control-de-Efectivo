<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CumplimientoArqueoController extends Controller
{
    private const ESTADOS_FINALIZADOS = [
        'PENDIENTE_CERTIFICACION',
        'CERTIFICADO',
    ];

    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        $mes = $this->resolverMes(
            trim((string) $request->string('mes'))
        );

        $inicioMes = $mes->copy()->startOfMonth();
        $finMes = $mes->copy()->endOfMonth();

        $rutaId = $request->integer('ruta_id');
        $agenteId = $request->integer('agente_id');

        $rutasAsignadas = $this->rutasAsignadasEnPeriodo(
            $usuario->id,
            $inicioMes,
            $finMes
        );

        if (
            $rutaId > 0
            && ! $rutasAsignadas->contains(
                fn ($ruta): bool => (int) $ruta->id === $rutaId
            )
        ) {
            abort(
                403,
                'La ruta seleccionada no pertenece al Promotor en el período consultado.'
            );
        }

        $agentesDisponibles = $this->agentesDisponiblesEnPeriodo(
            $usuario->id,
            $inicioMes,
            $finMes,
            $rutaId
        );

        if (
            $agenteId > 0
            && ! $agentesDisponibles->contains(
                fn ($agente): bool => (int) $agente->id === $agenteId
            )
        ) {
            abort(
                403,
                'El agente seleccionado no pertenece a una ruta asignada al Promotor en el período consultado.'
            );
        }

        $agentesCalendario = $agenteId > 0
            ? $agentesDisponibles
                ->where('id', $agenteId)
                ->values()
            : $agentesDisponibles;

        $arqueos = DB::table('arqueos')
            ->whereIn(
                'agente_id',
                $agentesCalendario->pluck('id')->all()
            )
            ->where('tipo', 'DIARIO_AGENTE')
            ->whereBetween('fecha_arqueo', [
                $inicioMes->toDateString(),
                $finMes->toDateString(),
            ])
            ->select([
                'id',
                'numero_arqueo',
                'agente_id',
                'estado',
                'fecha_arqueo',
                'fuera_fecha_ordinaria',
            ])
            ->orderBy('id')
            ->get()
            ->groupBy(
                fn ($arqueo): string =>
                    (int) $arqueo->agente_id
                    . '|'
                    . $arqueo->fecha_arqueo
            );

        $controles = DB::table('controles_diarios')
            ->whereIn(
                'agente_id',
                $agentesCalendario->pluck('id')->all()
            )
            ->where('tipo', 'NO_ATENDIO')
            ->where('vigente', true)
            ->whereBetween('fecha', [
                $inicioMes->toDateString(),
                $finMes->toDateString(),
            ])
            ->select([
                'id',
                'agente_id',
                'fecha',
                'tipo',
                'anotacion',
            ])
            ->get()
            ->keyBy(
                fn ($control): string =>
                    (int) $control->agente_id
                    . '|'
                    . $control->fecha
            );

        $calendario = [];
        $resumenMes = [
            'cumplidos' => 0,
            'sin_arqueo' => 0,
            'no_atendio' => 0,
            'extemporaneos' => 0,
            'anulados' => 0,
        ];

        $fecha = $inicioMes->copy();

        while ($fecha->lte($finMes)) {
            $fechaTexto = $fecha->toDateString();

            if ($fecha->isFuture()) {
                $calendario[] = [
                    'fecha' => $fechaTexto,
                    'dia' => $fecha->day,
                    'es_hoy' => $fecha->isToday(),
                    'es_futuro' => true,
                    'totales' => [
                        'cumplidos' => 0,
                        'sin_arqueo' => 0,
                        'no_atendio' => 0,
                        'extemporaneos' => 0,
                        'anulados' => 0,
                    ],
                    'agente' => null,
                ];

                $fecha->addDay();

                continue;
            }

            $agentesDelDia = $agentesCalendario
                ->filter(
                    fn ($agente): bool =>
                        $this->agentePerteneceAlPromotorEnFecha(
                            $agente,
                            $fecha
                        )
                )
                ->values();

            $totalesDia = [
                'cumplidos' => 0,
                'sin_arqueo' => 0,
                'no_atendio' => 0,
                'extemporaneos' => 0,
                'anulados' => 0,
            ];

            $detalleAgente = null;

            foreach ($agentesDelDia as $agente) {
                $clave = (int) $agente->id . '|' . $fechaTexto;


                $arqueosDia = $arqueos->get(
                    $clave,
                    collect()
                );

                $control = $controles->get($clave);

                $clasificacion = $this->clasificarDia(
                    $arqueosDia,
                    $control !== null
                );

                $claveResumen = match ($clasificacion['categoria']) {
                    'CUMPLIDO' => 'cumplidos',
                    'NO_ATENDIO' => 'no_atendio',
                    'EXTEMPORANEO' => 'extemporaneos',
                    'ANULADO' => 'anulados',
                    default => 'sin_arqueo',
                };

                $totalesDia[$claveResumen]++;
                $resumenMes[$claveResumen]++;

                if ($agenteId > 0) {
                    $detalleAgente = [
                        'id' => (int) $agente->id,
                        'codigo_agente' => $agente->codigo_agente,
                        'nombre_negocio' => $agente->nombre_negocio,
                        'categoria' => $clasificacion['categoria'],
                        'texto' => $clasificacion['texto'],
                        'arqueo_id' => $clasificacion['arqueo_id'],
                        'numero_arqueo' => $clasificacion['numero_arqueo'],
                        'url_arqueo' => $clasificacion['arqueo_id']
                            ? route(
                                'promotor.arqueos-agentes.show',
                                $clasificacion['arqueo_id']
                            )
                            : null,
                    ];
                }
            }

            $calendario[] = [
                'fecha' => $fechaTexto,
                'dia' => $fecha->day,
                'es_hoy' => $fecha->isToday(),
                'es_futuro' => false,
                'totales' => $totalesDia,
                'agente' => $detalleAgente,
            ];

            $fecha->addDay();
        }

        $visitasPromotor = DB::table('arqueos')
            ->where('creado_por', $usuario->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->whereBetween('fecha_arqueo', [
                $inicioMes->toDateString(),
                $finMes->toDateString(),
            ])
            ->when(
                $rutaId > 0,
                function ($query) use ($rutaId): void {
                    $query->whereExists(function ($subquery) use ($rutaId): void {
                        $subquery
                            ->selectRaw('1')
                            ->from('agentes as a')
                            ->whereColumn('a.id', 'arqueos.agente_id')
                            ->where('a.ruta_id', $rutaId);
                    });
                }
            )
            ->when(
                $agenteId > 0,
                fn ($query) => $query->where(
                    'agente_id',
                    $agenteId
                )
            )
            ->select([
                'id',
                'numero_arqueo',
                'agente_id',
                'estado',
                'fecha_arqueo',
                'codigo_agente_historico',
                'nombre_negocio_historico',
                'nombre_propietario_historico',
                'ruta_historica',
                'region_historica',
                'total_arqueado',
                'saldo_sistema',
                'diferencia',
            ])
            ->orderByDesc('fecha_arqueo')
            ->orderByDesc('id')
            ->paginate(
                10,
                ['*'],
                'visitas_page'
            )
            ->withQueryString();

        $totalVisitasMes = DB::table('arqueos')
            ->where('creado_por', $usuario->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->whereBetween('fecha_arqueo', [
                $inicioMes->toDateString(),
                $finMes->toDateString(),
            ])
            ->where('estado', '!=', 'ANULADO')
            ->count();

        return view('promotor.cumplimientos.index', [
            'mes' => $mes->format('Y-m'),
            'mesActual' => now()->startOfMonth()->format('Y-m'),
            'mesAnterior' => $mes->copy()->subMonth()->format('Y-m'),
            'mesSiguiente' => $mes->copy()->addMonth()->format('Y-m'),
            'inicioMes' => $inicioMes,
            'finMes' => $finMes,

            'rutaId' => $rutaId,
            'agenteId' => $agenteId,

            'rutasAsignadas' => $rutasAsignadas,
            'agentesDisponibles' => $agentesDisponibles,

            'calendario' => $calendario,
            'resumenMes' => $resumenMes,

            'visitasPromotor' => $visitasPromotor,
            'totalVisitasMes' => $totalVisitasMes,
        ]);
    }

    private function validarPromotor(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Promotor',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }

    private function resolverMes(string $mes): Carbon
    {
        if (
            $mes !== ''
            && preg_match('/^\d{4}-\d{2}$/', $mes) === 1
        ) {
            try {
                return Carbon::createFromFormat(
                    'Y-m',
                    $mes
                )->startOfMonth();
            } catch (\Throwable) {
                // Se usa el mes actual.
            }
        }

        return now()->startOfMonth();
    }

    private function rutasAsignadasEnPeriodo(
        int $promotorUsuarioId,
        Carbon $inicio,
        Carbon $fin
    ): Collection {
        return DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->join(
                'asignaciones_promotor_ruta as apr',
                'apr.ruta_id',
                '=',
                'r.id'
            )
            ->where(
                'apr.promotor_usuario_id',
                $promotorUsuarioId
            )
            ->where('apr.estado', true)
            ->whereDate(
                'apr.fecha_inicio',
                '<=',
                $fin->toDateString()
            )
            ->where(function ($query) use ($inicio): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate(
                        'apr.fecha_fin',
                        '>=',
                        $inicio->toDateString()
                    );
            })
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'reg.nombre as region_nombre',
            ])
            ->distinct()
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get();
    }

    private function agentesDisponiblesEnPeriodo(
        int $promotorUsuarioId,
        Carbon $inicio,
        Carbon $fin,
        int $rutaId
    ): Collection {
        $filas = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->join(
                'asignaciones_promotor_ruta as apr',
                'apr.ruta_id',
                '=',
                'r.id'
            )
            ->where(
                'apr.promotor_usuario_id',
                $promotorUsuarioId
            )
            ->where('apr.estado', true)
            ->whereDate(
                'apr.fecha_inicio',
                '<=',
                $fin->toDateString()
            )
            ->where(function ($query) use ($inicio): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate(
                        'apr.fecha_fin',
                        '>=',
                        $inicio->toDateString()
                    );
            })
            ->where('a.estado', 'ACTIVO')
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where(
                    'r.id',
                    $rutaId
                )
            )
            ->select([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.ruta_id',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'apr.fecha_inicio as asignacion_fecha_inicio',
                'apr.fecha_fin as asignacion_fecha_fin',
            ])
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->orderBy('a.nombre_negocio')
            ->orderBy('apr.fecha_inicio')
            ->get();

        return $filas
            ->groupBy('id')
            ->map(function (Collection $grupo): object {
                $agente = clone $grupo->first();

                $agente->periodos_asignacion = $grupo
                    ->map(
                        fn ($fila): array => [
                            'fecha_inicio' =>
                                $fila->asignacion_fecha_inicio,
                            'fecha_fin' =>
                                $fila->asignacion_fecha_fin,
                        ]
                    )
                    ->values()
                    ->all();

                unset(
                    $agente->asignacion_fecha_inicio,
                    $agente->asignacion_fecha_fin
                );

                return $agente;
            })
            ->values();
    }

    private function agentePerteneceAlPromotorEnFecha(
        object $agente,
        Carbon $fecha
    ): bool {
        foreach ($agente->periodos_asignacion ?? [] as $periodo) {
            $inicio = Carbon::parse(
                $periodo['fecha_inicio']
            )->startOfDay();

            $fin = filled($periodo['fecha_fin'] ?? null)
                ? Carbon::parse(
                    $periodo['fecha_fin']
                )->endOfDay()
                : null;

            if (
                $fecha->gte($inicio)
                && ($fin === null || $fecha->lte($fin))
            ) {
                return true;
            }
        }

        return false;
    }

    private function clasificarDia(
        Collection $arqueos,
        bool $tieneNoAtendio
    ): array {
        $finalizado = $arqueos->first(
            fn ($arqueo): bool =>
                in_array(
                    $arqueo->estado,
                    self::ESTADOS_FINALIZADOS,
                    true
                )
        );

        if ($finalizado) {
            if ((bool) $finalizado->fuera_fecha_ordinaria) {
                return [
                    'categoria' => 'EXTEMPORANEO',
                    'texto' => 'Extemporáneo',
                    'arqueo_id' => (int) $finalizado->id,
                    'numero_arqueo' => $finalizado->numero_arqueo,
                ];
            }

            return [
                'categoria' => 'CUMPLIDO',
                'texto' => 'Cumplido',
                'arqueo_id' => (int) $finalizado->id,
                'numero_arqueo' => $finalizado->numero_arqueo,
            ];
        }

        if ($tieneNoAtendio) {
            return [
                'categoria' => 'NO_ATENDIO',
                'texto' => 'No atendió',
                'arqueo_id' => null,
                'numero_arqueo' => null,
            ];
        }

        $anulado = $arqueos->first(
            fn ($arqueo): bool =>
                $arqueo->estado === 'ANULADO'
        );

        if ($anulado) {
            return [
                'categoria' => 'ANULADO',
                'texto' => 'Anulado',
                'arqueo_id' => (int) $anulado->id,
                'numero_arqueo' => $anulado->numero_arqueo,
            ];
        }

        return [
            'categoria' => 'SIN_ARQUEO',
            'texto' => 'Sin arqueo',
            'arqueo_id' => null,
            'numero_arqueo' => null,
        ];
    }
}
