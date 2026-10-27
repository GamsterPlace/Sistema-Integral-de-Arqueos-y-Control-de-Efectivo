<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CumplimientoArqueoController extends Controller
{
    private const ESTADOS_ARQUEO_FINALIZADO = [
        'PENDIENTE_CERTIFICACION',
        'CERTIFICADO',
    ];

    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->autorizar($usuario);

        $mes = $this->resolverMes($request);

        $inicioMes = $mes->copy()->startOfMonth();
        $finMes = $mes->copy()->endOfMonth();

        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');
        $promotorId = $request->integer('promotor_id');
        $buscar = trim((string) $request->string('buscar'));

        $regiones = DB::table('regiones')
            ->where('estado', true)
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
            ]);

        $rutas = DB::table('rutas')
            ->where('estado', true)
            ->when(
                $regionId > 0,
                fn ($query) => $query->where(
                    'region_id',
                    $regionId
                )
            )
            ->orderBy('nombre')
            ->get([
                'id',
                'codigo',
                'nombre',
                'region_id',
            ]);

        $promotores = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->where('rol.nombre', 'Promotor')
            ->where('u.estado', 'ACTIVO')
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->get([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ]);

        $datosCalendario = $this->construirCalendario(
            $inicioMes,
            $finMes,
            $regionId,
            $rutaId,
            $promotorId,
            $buscar
        );

        $visitasAuditoria = DB::table('arqueos as arq')
            ->where('arq.tipo', 'VISITA_AUDITORIA')
            ->where('arq.creado_por', $usuario->id)
            ->whereBetween('arq.fecha_arqueo', [
                $inicioMes->toDateString(),
                $finMes->toDateString(),
            ])
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where(
                                'arq.numero_arqueo',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'arq.codigo_agente_historico',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'arq.nombre_negocio_historico',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'arq.nombre_propietario_historico',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'arq.ruta_historica',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'arq.region_historica',
                                'like',
                                '%' . $buscar . '%'
                            );
                    });
                }
            )
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.estado',
                'arq.codigo_agente_historico',
                'arq.nombre_negocio_historico',
                'arq.nombre_propietario_historico',
                'arq.ruta_historica',
                'arq.region_historica',
                'arq.total_arqueado',
                'arq.saldo_sistema',
                'arq.diferencia',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(
                10,
                ['*'],
                'visitas_page'
            )
            ->withQueryString();

        $totalVisitasMes = DB::table('arqueos')
            ->where('tipo', 'VISITA_AUDITORIA')
            ->where('creado_por', $usuario->id)
            ->whereBetween('fecha_arqueo', [
                $inicioMes->toDateString(),
                $finMes->toDateString(),
            ])
            ->count();

        return view(
            'auditoria.cumplimientos.index',
            [
                'mes' => $mes->format('Y-m'),
                'mesActual' => today()->format('Y-m'),
                'mesAnterior' => $mes
                    ->copy()
                    ->subMonthNoOverflow()
                    ->format('Y-m'),
                'mesSiguiente' => $mes
                    ->copy()
                    ->addMonthNoOverflow()
                    ->format('Y-m'),
                'inicioMes' => $inicioMes,
                'finMes' => $finMes,

                'regionId' => $regionId,
                'rutaId' => $rutaId,
                'promotorId' => $promotorId,
                'buscar' => $buscar,

                'regiones' => $regiones,
                'rutas' => $rutas,
                'promotores' => $promotores,

                'calendario' => $datosCalendario['dias'],
                'resumenMes' => $datosCalendario['resumen'],

                'visitasAuditoria' => $visitasAuditoria,
                'totalVisitasMes' => $totalVisitasMes,
            ]
        );
    }

    public function detalle(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->autorizar($usuario);

        $validated = $request->validate([
            'fecha' => [
                'required',
                'date_format:Y-m-d',
            ],
            'categoria' => [
                'required',
                'in:ARQUEADO,SIN_ARQUEO,NO_ATENDIO,EXTEMPORANEO,ANULADO',
            ],
            'region_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'ruta_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'promotor_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'buscar' => [
                'nullable',
                'string',
                'max:150',
            ],
        ]);

        $fecha = Carbon::createFromFormat(
            'Y-m-d',
            $validated['fecha']
        )->startOfDay();

        $categoria = $validated['categoria'];
        $regionId = (int) ($validated['region_id'] ?? 0);
        $rutaId = (int) ($validated['ruta_id'] ?? 0);
        $promotorId = (int) ($validated['promotor_id'] ?? 0);
        $buscar = trim(
            (string) ($validated['buscar'] ?? '')
        );

        if ($fecha->isFuture()) {
            return response()->json([
                'fecha' => $fecha->toDateString(),
                'fecha_formateada' => $fecha
                    ->locale('es')
                    ->translatedFormat(
                        'd \d\e F \d\e Y'
                    ),
                'categoria' => $categoria,
                'categoria_texto' =>
                    $this->textoCategoria($categoria),
                'total' => 0,
                'agentes' => [],
            ]);
        }

        $datos = $this->obtenerDatosPeriodo(
            $fecha->copy(),
            $fecha->copy(),
            $regionId,
            $rutaId,
            $promotorId,
            $buscar
        );

        $agentesDetalle = [];

        foreach ($datos['agentes'] as $agente) {
            if (! $this->agenteCorrespondeAPromotorEnFecha(
                (int) $agente->ruta_id,
                $fecha,
                $promotorId,
                $datos['asignaciones']
            )) {
                continue;
            }

            $clasificacion = $this->clasificarAgenteEnFecha(
                (int) $agente->id,
                $fecha,
                $datos['arqueos'],
                $datos['controles']
            );

            if (
                $clasificacion['categoria']
                !== $categoria
            ) {
                continue;
            }

            $asignacion = $this->obtenerAsignacionRutaEnFecha(
                (int) $agente->ruta_id,
                $fecha,
                $datos['asignaciones']
            );

            $arqueo = $clasificacion['arqueo'];

            $agentesDetalle[] = [
                'id' => (int) $agente->id,
                'codigo_agente' =>
                    $agente->codigo_agente,
                'nombre_negocio' =>
                    $agente->nombre_negocio,
                'nombre_propietario' =>
                    $agente->nombre_propietario,
                'region' =>
                    $agente->region_nombre,
                'ruta' => trim(
                    $agente->ruta_codigo
                    . ' — '
                    . $agente->ruta_nombre
                ),
                'promotor' =>
                    $this->nombrePromotor($asignacion),
                'categoria' => $categoria,
                'estado_texto' =>
                    $this->textoCategoria($categoria),
                'numero_arqueo' =>
                    $arqueo?->numero_arqueo,
                'arqueo_id' =>
                    $arqueo?->id,
            ];
        }

        usort(
            $agentesDetalle,
            fn (array $a, array $b) => [
                $a['region'],
                $a['ruta'],
                $a['nombre_negocio'],
            ] <=> [
                $b['region'],
                $b['ruta'],
                $b['nombre_negocio'],
            ]
        );

        return response()->json([
            'fecha' => $fecha->toDateString(),
            'fecha_formateada' => $fecha
                ->locale('es')
                ->translatedFormat(
                    'd \d\e F \d\e Y'
                ),
            'categoria' => $categoria,
            'categoria_texto' =>
                $this->textoCategoria($categoria),
            'total' => count($agentesDetalle),
            'agentes' => $agentesDetalle,
        ]);
    }

    private function autorizar(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Auditoria',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }

    private function resolverMes(Request $request): Carbon
    {
        $mes = trim(
            (string) $request->string('mes')
        );

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
                // Se utiliza el mes actual.
            }
        }

        return today()->startOfMonth();
    }

    private function construirCalendario(
        Carbon $inicioMes,
        Carbon $finMes,
        int $regionId,
        int $rutaId,
        int $promotorId,
        string $buscar
    ): array {
        $datos = $this->obtenerDatosPeriodo(
            $inicioMes,
            $finMes,
            $regionId,
            $rutaId,
            $promotorId,
            $buscar
        );

        $dias = [];

        $resumen = [
            'total_agentes_dia' => 0,
            'arqueados' => 0,
            'sin_arqueo' => 0,
            'no_atendieron' => 0,
            'extemporaneos' => 0,
            'anulados' => 0,
        ];

        $cursor = $inicioMes->copy();
        $hoy = today();

        while ($cursor->lte($finMes)) {
            $fecha = $cursor->copy();
            $esFuturo = $fecha->gt($hoy);

            $conteos = [
                'total' => 0,
                'ARQUEADO' => 0,
                'SIN_ARQUEO' => 0,
                'NO_ATENDIO' => 0,
                'EXTEMPORANEO' => 0,
                'ANULADO' => 0,
            ];

            if (! $esFuturo) {
                foreach ($datos['agentes'] as $agente) {
                    if (! $this->agenteCorrespondeAPromotorEnFecha(
                        (int) $agente->ruta_id,
                        $fecha,
                        $promotorId,
                        $datos['asignaciones']
                    )) {
                        continue;
                    }

                    $conteos['total']++;

                    $clasificacion =
                        $this->clasificarAgenteEnFecha(
                            (int) $agente->id,
                            $fecha,
                            $datos['arqueos'],
                            $datos['controles']
                        );

                    $conteos[
                        $clasificacion['categoria']
                    ]++;
                }
            }

            $dias[$fecha->toDateString()] = [
                'fecha' =>
                    $fecha->toDateString(),
                'dia' =>
                    $fecha->day,
                'dia_semana' =>
                    $fecha->dayOfWeekIso,
                'es_hoy' =>
                    $fecha->isSameDay($hoy),
                'es_futuro' =>
                    $esFuturo,
                'total_agentes' =>
                    $conteos['total'],
                'arqueados' =>
                    $conteos['ARQUEADO'],
                'sin_arqueo' =>
                    $conteos['SIN_ARQUEO'],
                'no_atendieron' =>
                    $conteos['NO_ATENDIO'],
                'extemporaneos' =>
                    $conteos['EXTEMPORANEO'],
                'anulados' =>
                    $conteos['ANULADO'],
            ];

            if (! $esFuturo) {
                $resumen['total_agentes_dia']
                    += $conteos['total'];

                $resumen['arqueados']
                    += $conteos['ARQUEADO'];

                $resumen['sin_arqueo']
                    += $conteos['SIN_ARQUEO'];

                $resumen['no_atendieron']
                    += $conteos['NO_ATENDIO'];

                $resumen['extemporaneos']
                    += $conteos['EXTEMPORANEO'];

                $resumen['anulados']
                    += $conteos['ANULADO'];
            }

            $cursor->addDay();
        }

        return [
            'dias' => $dias,
            'resumen' => $resumen,
        ];
    }

    private function obtenerDatosPeriodo(
        Carbon $inicio,
        Carbon $fin,
        int $regionId,
        int $rutaId,
        int $promotorId,
        string $buscar
    ): array {
        $agentes = DB::table('agentes as a')
            ->join(
                'rutas as r',
                'r.id',
                '=',
                'a.ruta_id'
            )
            ->join(
                'regiones as reg',
                'reg.id',
                '=',
                'r.region_id'
            )
            ->where('a.estado', 'ACTIVO')
            ->where('r.estado', true)
            ->when(
                $regionId > 0,
                fn ($query) => $query->where(
                    'reg.id',
                    $regionId
                )
            )
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where(
                    'r.id',
                    $rutaId
                )
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(
                        function ($subquery) use ($buscar): void {
                            $subquery
                                ->where(
                                    'a.codigo_agente',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'a.nombre_negocio',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'a.nombre_propietario',
                                    'like',
                                    '%' . $buscar . '%'
                                );
                        }
                    );
                }
            )
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->orderBy('a.nombre_negocio')
            ->get([
                'a.id',
                'a.ruta_id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ]);

        if ($agentes->isEmpty()) {
            return [
                'agentes' => collect(),
                'asignaciones' => collect(),
                'arqueos' => collect(),
                'controles' => collect(),
            ];
        }

        $agenteIds = $agentes
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $rutaIds = $agentes
            ->pluck('ruta_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $asignaciones = DB::table(
            'asignaciones_promotor_ruta as apr'
        )
            ->join(
                'usuarios as up',
                'up.id',
                '=',
                'apr.promotor_usuario_id'
            )
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'up.id'
            )
            ->whereIn('apr.ruta_id', $rutaIds)
            ->where('apr.estado', true)
            ->whereDate(
                'apr.fecha_inicio',
                '<=',
                $fin->toDateString()
            )
            ->where(
                function ($query) use ($inicio): void {
                    $query
                        ->whereNull('apr.fecha_fin')
                        ->orWhereDate(
                            'apr.fecha_fin',
                            '>=',
                            $inicio->toDateString()
                        );
                }
            )
            ->when(
                $promotorId > 0,
                fn ($query) => $query->where(
                    'apr.promotor_usuario_id',
                    $promotorId
                )
            )
            ->orderBy('apr.fecha_inicio')
            ->orderBy('apr.id')
            ->get([
                'apr.id',
                'apr.ruta_id',
                'apr.promotor_usuario_id',
                'apr.fecha_inicio',
                'apr.fecha_fin',
                'up.usuario as promotor_usuario',
                'dp.nombres as promotor_nombres',
                'dp.apellidos as promotor_apellidos',
            ])
            ->groupBy(
                fn ($item) =>
                    (int) $item->ruta_id
            );

        $arqueos = DB::table('arqueos')
            ->whereIn('agente_id', $agenteIds)
            ->where('tipo', 'DIARIO_AGENTE')
            ->whereBetween(
                'fecha_arqueo',
                [
                    $inicio->toDateString(),
                    $fin->toDateString(),
                ]
            )
            ->orderBy('id')
            ->get([
                'id',
                'agente_id',
                'numero_arqueo',
                'estado',
                'fecha_arqueo',
                'fuera_fecha_ordinaria',
            ])
            ->groupBy(
                fn ($arqueo) =>
                    $this->claveAgenteFecha(
                        (int) $arqueo->agente_id,
                        Carbon::parse(
                            $arqueo->fecha_arqueo
                        )
                    )
            );

        $controles = DB::table(
            'controles_diarios'
        )
            ->whereIn('agente_id', $agenteIds)
            ->where('tipo', 'NO_ATENDIO')
            ->where('vigente', true)
            ->whereBetween(
                'fecha',
                [
                    $inicio->toDateString(),
                    $fin->toDateString(),
                ]
            )
            ->get([
                'id',
                'agente_id',
                'fecha',
                'tipo',
            ])
            ->keyBy(
                fn ($control) =>
                    $this->claveAgenteFecha(
                        (int) $control->agente_id,
                        Carbon::parse(
                            $control->fecha
                        )
                    )
            );

        return [
            'agentes' => $agentes,
            'asignaciones' => $asignaciones,
            'arqueos' => $arqueos,
            'controles' => $controles,
        ];
    }

    private function agenteCorrespondeAPromotorEnFecha(
        int $rutaId,
        Carbon $fecha,
        int $promotorId,
        Collection $asignaciones
    ): bool {
        if ($promotorId <= 0) {
            return true;
        }

        return $this->obtenerAsignacionRutaEnFecha(
            $rutaId,
            $fecha,
            $asignaciones
        ) !== null;
    }

    private function obtenerAsignacionRutaEnFecha(
        int $rutaId,
        Carbon $fecha,
        Collection $asignaciones
    ): ?object {
        /** @var Collection<int, object> $asignacionesRuta */
        $asignacionesRuta = $asignaciones->get(
            $rutaId,
            collect()
        );

        return $asignacionesRuta
            ->filter(
                function ($asignacion) use ($fecha): bool {
                    $inicio = Carbon::parse(
                        $asignacion->fecha_inicio
                    )->startOfDay();

                    $fin = $asignacion->fecha_fin
                        ? Carbon::parse(
                            $asignacion->fecha_fin
                        )->endOfDay()
                        : null;

                    return $fecha->gte($inicio)
                        && (
                            $fin === null
                            || $fecha->lte($fin)
                        );
                }
            )
            ->sortByDesc('id')
            ->first();
    }

    private function clasificarAgenteEnFecha(
        int $agenteId,
        Carbon $fecha,
        Collection $arqueos,
        Collection $controles
    ): array {
        $clave = $this->claveAgenteFecha(
            $agenteId,
            $fecha
        );

        /** @var Collection<int, object> $arqueosDia */
        $arqueosDia = $arqueos->get(
            $clave,
            collect()
        );

        $arqueoValido = $arqueosDia
            ->filter(
                fn ($arqueo) => in_array(
                    $arqueo->estado,
                    self::ESTADOS_ARQUEO_FINALIZADO,
                    true
                )
            )
            ->sortByDesc('id')
            ->first();

        if ($arqueoValido) {
            return [
                'categoria' =>
                    (bool) $arqueoValido
                        ->fuera_fecha_ordinaria
                        ? 'EXTEMPORANEO'
                        : 'ARQUEADO',
                'arqueo' => $arqueoValido,
            ];
        }

        if ($controles->has($clave)) {
            return [
                'categoria' => 'NO_ATENDIO',
                'arqueo' => null,
            ];
        }

        $arqueoAnulado = $arqueosDia
            ->filter(
                fn ($arqueo) =>
                    $arqueo->estado === 'ANULADO'
            )
            ->sortByDesc('id')
            ->first();

        if ($arqueoAnulado) {
            return [
                'categoria' => 'ANULADO',
                'arqueo' => $arqueoAnulado,
            ];
        }

        return [
            'categoria' => 'SIN_ARQUEO',
            'arqueo' => null,
        ];
    }

    private function claveAgenteFecha(
        int $agenteId,
        Carbon $fecha
    ): string {
        return $agenteId
            . '|'
            . $fecha->toDateString();
    }

    private function nombrePromotor(
        ?object $asignacion
    ): string {
        if (! $asignacion) {
            return 'Sin promotor asignado';
        }

        $nombre = trim(
            ($asignacion->promotor_nombres ?? '')
            . ' '
            . ($asignacion->promotor_apellidos ?? '')
        );

        return $nombre !== ''
            ? $nombre
            : (
                $asignacion->promotor_usuario
                ?? 'Sin promotor asignado'
            );
    }

    private function textoCategoria(
        string $categoria
    ): string {
        return match ($categoria) {
            'ARQUEADO' =>
                'Agentes con arqueo',

            'SIN_ARQUEO' =>
                'Agentes sin arqueo',

            'NO_ATENDIO' =>
                'Agentes que no atendieron',

            'EXTEMPORANEO' =>
                'Arqueos extemporáneos',

            'ANULADO' =>
                'Arqueos anulados',

            default =>
                'Cumplimiento de arqueos',
        };
    }
}
