<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EstadoArqueosController extends Controller
{
    private const ESTADOS_ARQUEO_FINALIZADO = [
        'PENDIENTE_CERTIFICACION',
        'CERTIFICADO',
    ];

    public function index(Request $request): View
    {
        $this->autorizar($request);

        /*
         * -----------------------------------------------------------------
         * BLOQUE LEGACY
         * -----------------------------------------------------------------
         * Se conserva la lógica diaria y todas las variables que utiliza
         * actualmente resources/views/jefe/estado-arqueos/index.blade.php.
         * Esto permite reemplazar primero el controlador sin romper la vista
         * existente. Luego la vista podrá consumir las nuevas variables del
         * calendario mensual.
         */
        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->input('fecha'))->toDateString()
            : today()->toDateString();

        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');
        $promotorId = $request->integer('promotor_id');
        $estado = trim((string) $request->string('estado'));
        $buscar = trim((string) $request->string('buscar'));

        $regiones = DB::table('regiones')
            ->where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $rutas = DB::table('rutas')
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('region_id', $regionId)
            )
            ->where('estado', true)
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

        $query = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->leftJoin(
                'asignaciones_promotor_ruta as apr',
                function ($join) use ($fecha): void {
                    $join
                        ->on('apr.ruta_id', '=', 'r.id')
                        ->where('apr.estado', true)
                        ->whereDate('apr.fecha_inicio', '<=', $fecha)
                        ->where(function ($query) use ($fecha): void {
                            $query
                                ->whereNull('apr.fecha_fin')
                                ->orWhereDate('apr.fecha_fin', '>=', $fecha);
                        });
                }
            )
            ->leftJoin(
                'usuarios as up',
                'up.id',
                '=',
                'apr.promotor_usuario_id'
            )
            ->leftJoin(
                'datos_personales as dpp',
                'dpp.usuario_id',
                '=',
                'up.id'
            )
            ->where('a.estado', 'ACTIVO')
            ->where('r.estado', true)
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('reg.id', $regionId)
            )
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where('r.id', $rutaId)
            )
            ->when(
                $promotorId > 0,
                fn ($query) => $query->where(
                    'apr.promotor_usuario_id',
                    $promotorId
                )
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
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
                    });
                }
            )
            ->select([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'r.id as ruta_id',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.id as region_id',
                'reg.nombre as region_nombre',
                'apr.promotor_usuario_id',
                'up.usuario as promotor_usuario',
                'dpp.nombres as promotor_nombres',
                'dpp.apellidos as promotor_apellidos',
            ])
            ->selectSub(
                DB::table('arqueos')
                    ->select('id')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->whereDate('arqueos.fecha_arqueo', $fecha)
                    ->where('arqueos.tipo', 'DIARIO_AGENTE')
                    ->where('arqueos.estado', '!=', 'ANULADO')
                    ->orderByDesc('arqueos.id')
                    ->limit(1),
                'arqueo_id'
            )
            ->selectSub(
                DB::table('arqueos')
                    ->select('numero_arqueo')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->whereDate('arqueos.fecha_arqueo', $fecha)
                    ->where('arqueos.tipo', 'DIARIO_AGENTE')
                    ->where('arqueos.estado', '!=', 'ANULADO')
                    ->orderByDesc('arqueos.id')
                    ->limit(1),
                'numero_arqueo'
            )
            ->selectSub(
                DB::table('arqueos')
                    ->select('estado')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->whereDate('arqueos.fecha_arqueo', $fecha)
                    ->where('arqueos.tipo', 'DIARIO_AGENTE')
                    ->orderByDesc('arqueos.id')
                    ->limit(1),
                'estado_arqueo'
            )
            ->selectSub(
                DB::table('arqueos')
                    ->select('fuera_fecha_ordinaria')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->whereDate('arqueos.fecha_arqueo', $fecha)
                    ->where('arqueos.tipo', 'DIARIO_AGENTE')
                    ->where('arqueos.estado', '!=', 'ANULADO')
                    ->orderByDesc('arqueos.id')
                    ->limit(1),
                'fuera_fecha_ordinaria'
            )
            ->selectSub(
                DB::table('controles_diarios')
                    ->select('tipo')
                    ->whereColumn('controles_diarios.agente_id', 'a.id')
                    ->whereDate('controles_diarios.fecha', $fecha)
                    ->where('controles_diarios.vigente', true)
                    ->orderByDesc('controles_diarios.id')
                    ->limit(1),
                'estado_control'
            )
            ->distinct();

        if ($estado === 'ARQUEADO') {
            $query->whereExists(function ($subquery) use ($fecha): void {
                $subquery
                    ->selectRaw('1')
                    ->from('arqueos as arq_f')
                    ->whereColumn('arq_f.agente_id', 'a.id')
                    ->whereDate('arq_f.fecha_arqueo', $fecha)
                    ->where('arq_f.tipo', 'DIARIO_AGENTE')
                    ->where('arq_f.estado', '!=', 'ANULADO')
                    ->where(function ($inner): void {
                        $inner
                            ->whereNull('arq_f.fuera_fecha_ordinaria')
                            ->orWhere('arq_f.fuera_fecha_ordinaria', false);
                    });
            });
        } elseif ($estado === 'EXTEMPORANEO') {
            $query->whereExists(function ($subquery) use ($fecha): void {
                $subquery
                    ->selectRaw('1')
                    ->from('arqueos as arq_f')
                    ->whereColumn('arq_f.agente_id', 'a.id')
                    ->whereDate('arq_f.fecha_arqueo', $fecha)
                    ->where('arq_f.tipo', 'DIARIO_AGENTE')
                    ->where('arq_f.estado', '!=', 'ANULADO')
                    ->where('arq_f.fuera_fecha_ordinaria', true);
            });
        } elseif ($estado === 'ANULADO') {
            $query->whereExists(function ($subquery) use ($fecha): void {
                $subquery
                    ->selectRaw('1')
                    ->from('arqueos as arq_f')
                    ->whereColumn('arq_f.agente_id', 'a.id')
                    ->whereDate('arq_f.fecha_arqueo', $fecha)
                    ->where('arq_f.tipo', 'DIARIO_AGENTE')
                    ->where('arq_f.estado', 'ANULADO');
            });
        } elseif ($estado === 'NO_ATENDIO') {
            $query->whereExists(function ($subquery) use ($fecha): void {
                $subquery
                    ->selectRaw('1')
                    ->from('controles_diarios as cd_f')
                    ->whereColumn('cd_f.agente_id', 'a.id')
                    ->whereDate('cd_f.fecha', $fecha)
                    ->where('cd_f.tipo', 'NO_ATENDIO')
                    ->where('cd_f.vigente', true);
            });
        } elseif ($estado === 'PENDIENTE') {
            $query
                ->whereNotExists(function ($subquery) use ($fecha): void {
                    $subquery
                        ->selectRaw('1')
                        ->from('arqueos as arq_f')
                        ->whereColumn('arq_f.agente_id', 'a.id')
                        ->whereDate('arq_f.fecha_arqueo', $fecha)
                        ->where('arq_f.tipo', 'DIARIO_AGENTE')
                        ->where('arq_f.estado', '!=', 'ANULADO');
                })
                ->whereNotExists(function ($subquery) use ($fecha): void {
                    $subquery
                        ->selectRaw('1')
                        ->from('controles_diarios as cd_f')
                        ->whereColumn('cd_f.agente_id', 'a.id')
                        ->whereDate('cd_f.fecha', $fecha)
                        ->where('cd_f.tipo', 'NO_ATENDIO')
                        ->where('cd_f.vigente', true);
                });
        }

        $agentes = $query
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->orderBy('a.nombre_negocio')
            ->paginate(15)
            ->withQueryString();

        $baseAgentes = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('a.estado', 'ACTIVO')
            ->where('r.estado', true)
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('reg.id', $regionId)
            )
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where('r.id', $rutaId)
            );

        $totalAgentes = (clone $baseAgentes)->count();

        $arqueadosHoy = (clone $baseAgentes)
            ->whereExists(function ($query) use ($fecha): void {
                $query
                    ->selectRaw('1')
                    ->from('arqueos as arq')
                    ->whereColumn('arq.agente_id', 'a.id')
                    ->whereDate('arq.fecha_arqueo', $fecha)
                    ->where('arq.tipo', 'DIARIO_AGENTE')
                    ->where('arq.estado', '!=', 'ANULADO');
            })
            ->count();

        $extemporaneos = (clone $baseAgentes)
            ->whereExists(function ($query) use ($fecha): void {
                $query
                    ->selectRaw('1')
                    ->from('arqueos as arq')
                    ->whereColumn('arq.agente_id', 'a.id')
                    ->whereDate('arq.fecha_arqueo', $fecha)
                    ->where('arq.tipo', 'DIARIO_AGENTE')
                    ->where('arq.estado', '!=', 'ANULADO')
                    ->where('arq.fuera_fecha_ordinaria', true);
            })
            ->count();

        $noAtendieron = (clone $baseAgentes)
            ->whereExists(function ($query) use ($fecha): void {
                $query
                    ->selectRaw('1')
                    ->from('controles_diarios as cd')
                    ->whereColumn('cd.agente_id', 'a.id')
                    ->whereDate('cd.fecha', $fecha)
                    ->where('cd.tipo', 'NO_ATENDIO')
                    ->where('cd.vigente', true);
            })
            ->count();

        $anulados = (clone $baseAgentes)
            ->whereExists(function ($query) use ($fecha): void {
                $query
                    ->selectRaw('1')
                    ->from('arqueos as arq')
                    ->whereColumn('arq.agente_id', 'a.id')
                    ->whereDate('arq.fecha_arqueo', $fecha)
                    ->where('arq.tipo', 'DIARIO_AGENTE')
                    ->where('arq.estado', 'ANULADO');
            })
            ->count();

        $pendientes = max(
            0,
            $totalAgentes - $arqueadosHoy - $noAtendieron
        );

        /*
         * -----------------------------------------------------------------
         * NUEVO BLOQUE: CALENDARIO DE CUMPLIMIENTO
         * -----------------------------------------------------------------
         */
        $mes = $this->resolverMes($request, $fecha);
        $inicioMes = $mes->copy()->startOfMonth();
        $finMes = $mes->copy()->endOfMonth();

        $datosCalendario = $this->construirCalendario(
            $inicioMes,
            $finMes,
            $regionId,
            $rutaId,
            $promotorId,
            $buscar
        );

        return view('jefe.estado-arqueos.index', [
            // Variables existentes: se mantienen.
            'agentes' => $agentes,
            'regiones' => $regiones,
            'rutas' => $rutas,
            'promotores' => $promotores,
            'fecha' => $fecha,
            'regionId' => $regionId,
            'rutaId' => $rutaId,
            'promotorId' => $promotorId,
            'estado' => $estado,
            'buscar' => $buscar,
            'totalAgentes' => $totalAgentes,
            'arqueadosHoy' => $arqueadosHoy,
            'pendientes' => $pendientes,
            'noAtendieron' => $noAtendieron,
            'extemporaneos' => $extemporaneos,
            'anulados' => $anulados,

            // Nuevas variables para la futura vista de calendario.
            'mes' => $mes->format('Y-m'),
            'mesActual' => today()->format('Y-m'),
            'mesAnterior' => $mes->copy()->subMonthNoOverflow()->format('Y-m'),
            'mesSiguiente' => $mes->copy()->addMonthNoOverflow()->format('Y-m'),
            'inicioMes' => $inicioMes,
            'finMes' => $finMes,
            'calendario' => $datosCalendario['dias'],
            'resumenMes' => $datosCalendario['resumen'],
        ]);
    }

    /**
     * Devuelve los agentes pertenecientes a una categoría de cumplimiento
     * para una fecha específica. Este método alimentará el modal del calendario.
     */
    public function detalle(Request $request): JsonResponse
    {
        $this->autorizar($request);

        $validated = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d'],
            'categoria' => [
                'required',
                'in:ARQUEADO,SIN_ARQUEO,NO_ATENDIO,EXTEMPORANEO,ANULADO',
            ],
            'region_id' => ['nullable', 'integer', 'min:1'],
            'ruta_id' => ['nullable', 'integer', 'min:1'],
            'promotor_id' => ['nullable', 'integer', 'min:1'],
            'buscar' => ['nullable', 'string', 'max:150'],
        ]);

        $fecha = Carbon::createFromFormat('Y-m-d', $validated['fecha'])
            ->startOfDay();

        $categoria = $validated['categoria'];
        $regionId = (int) ($validated['region_id'] ?? 0);
        $rutaId = (int) ($validated['ruta_id'] ?? 0);
        $promotorId = (int) ($validated['promotor_id'] ?? 0);
        $buscar = trim((string) ($validated['buscar'] ?? ''));

        if ($fecha->isFuture()) {
            return response()->json([
                'fecha' => $fecha->toDateString(),
                'categoria' => $categoria,
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

            if ($clasificacion['categoria'] !== $categoria) {
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
                'codigo_agente' => $agente->codigo_agente,
                'nombre_negocio' => $agente->nombre_negocio,
                'nombre_propietario' => $agente->nombre_propietario,
                'direccion' => $agente->direccion,
                'region' => $agente->region_nombre,
                'ruta' => trim(
                    $agente->ruta_codigo . ' — ' . $agente->ruta_nombre
                ),
                'promotor' => $this->nombrePromotor($asignacion),
                'categoria' => $categoria,
                'estado_texto' => $this->textoCategoria($categoria),
                'numero_arqueo' => $arqueo?->numero_arqueo,
                'arqueo_id' => $arqueo?->id,
                'url_arqueo' => $arqueo
                    ? route('jefe.arqueos-agentes.show', $arqueo->id)
                    : null,
                'url_agente' => route('jefe.agentes.show', $agente->id),
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
                ->translatedFormat('d \d\e F \d\e Y'),
            'categoria' => $categoria,
            'categoria_texto' => $this->textoCategoria($categoria),
            'total' => count($agentesDetalle),
            'agentes' => $agentesDetalle,
        ]);
    }

    private function autorizar(Request $request): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'jefedeAgentes',
            403,
            'No tiene autorización para acceder a esta sección.'
        );

        return $usuario;
    }

    private function resolverMes(Request $request, string $fecha): Carbon
    {
        if ($request->filled('mes')) {
            $mesSolicitado = trim((string) $request->input('mes'));

            if (preg_match('/^\d{4}-\d{2}$/', $mesSolicitado) === 1) {
                try {
                    return Carbon::createFromFormat('Y-m', $mesSolicitado)
                        ->startOfMonth();
                } catch (\Throwable) {
                    // Si llega un mes inválido, se conserva una fecha segura.
                }
            }
        }

        return Carbon::parse($fecha)->startOfMonth();
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

                    $clasificacion = $this->clasificarAgenteEnFecha(
                        (int) $agente->id,
                        $fecha,
                        $datos['arqueos'],
                        $datos['controles']
                    );

                    $conteos[$clasificacion['categoria']]++;
                }
            }

            $dias[$fecha->toDateString()] = [
                'fecha' => $fecha->toDateString(),
                'dia' => $fecha->day,
                'dia_semana' => $fecha->dayOfWeekIso,
                'es_hoy' => $fecha->isSameDay($hoy),
                'es_futuro' => $esFuturo,
                'total_agentes' => $conteos['total'],
                'arqueados' => $conteos['ARQUEADO'],
                'sin_arqueo' => $conteos['SIN_ARQUEO'],
                'no_atendieron' => $conteos['NO_ATENDIO'],
                'extemporaneos' => $conteos['EXTEMPORANEO'],
                'anulados' => $conteos['ANULADO'],
            ];

            if (! $esFuturo) {
                $resumen['total_agentes_dia'] += $conteos['total'];
                $resumen['arqueados'] += $conteos['ARQUEADO'];
                $resumen['sin_arqueo'] += $conteos['SIN_ARQUEO'];
                $resumen['no_atendieron'] += $conteos['NO_ATENDIO'];
                $resumen['extemporaneos'] += $conteos['EXTEMPORANEO'];
                $resumen['anulados'] += $conteos['ANULADO'];
            }

            $cursor->addDay();
        }

        return [
            'dias' => $dias,
            'resumen' => $resumen,
        ];
    }

    /**
     * Carga la información del periodo en pocas consultas y luego permite
     * clasificar en memoria cada agente/día. Evita ejecutar cinco consultas
     * de conteo por cada día del calendario.
     */
    private function obtenerDatosPeriodo(
        Carbon $inicio,
        Carbon $fin,
        int $regionId,
        int $rutaId,
        int $promotorId,
        string $buscar
    ): array {
        $agentes = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('a.estado', 'ACTIVO')
            ->where('r.estado', true)
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('reg.id', $regionId)
            )
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where('r.id', $rutaId)
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where('a.codigo_agente', 'like', '%' . $buscar . '%')
                            ->orWhere('a.nombre_negocio', 'like', '%' . $buscar . '%')
                            ->orWhere(
                                'a.nombre_propietario',
                                'like',
                                '%' . $buscar . '%'
                            );
                    });
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
                'a.direccion',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ]);

        $agenteIds = $agentes->pluck('id')->map(fn ($id) => (int) $id)->all();
        $rutaIds = $agentes->pluck('ruta_id')->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($agentes->isEmpty()) {
            return [
                'agentes' => collect(),
                'asignaciones' => collect(),
                'arqueos' => collect(),
                'controles' => collect(),
            ];
        }

        $asignaciones = DB::table('asignaciones_promotor_ruta as apr')
            ->join('usuarios as up', 'up.id', '=', 'apr.promotor_usuario_id')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'up.id'
            )
            ->whereIn('apr.ruta_id', $rutaIds)
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', $fin->toDateString())
            ->where(function ($query) use ($inicio): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', $inicio->toDateString());
            })
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
            ->groupBy(fn ($item) => (int) $item->ruta_id);

        $arqueos = DB::table('arqueos')
            ->whereIn('agente_id', $agenteIds)
            ->where('tipo', 'DIARIO_AGENTE')
            ->whereBetween('fecha_arqueo', [
                $inicio->toDateString(),
                $fin->toDateString(),
            ])
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
                fn ($arqueo) => $this->claveAgenteFecha(
                    (int) $arqueo->agente_id,
                    Carbon::parse($arqueo->fecha_arqueo)
                )
            );

        $controles = DB::table('controles_diarios')
            ->whereIn('agente_id', $agenteIds)
            ->where('tipo', 'NO_ATENDIO')
            ->where('vigente', true)
            ->whereBetween('fecha', [
                $inicio->toDateString(),
                $fin->toDateString(),
            ])
            ->get([
                'id',
                'agente_id',
                'fecha',
                'tipo',
            ])
            ->keyBy(
                fn ($control) => $this->claveAgenteFecha(
                    (int) $control->agente_id,
                    Carbon::parse($control->fecha)
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
        $asignacionesRuta = $asignaciones->get($rutaId, collect());

        return $asignacionesRuta
            ->filter(function ($asignacion) use ($fecha): bool {
                $inicio = Carbon::parse($asignacion->fecha_inicio)->startOfDay();
                $fin = $asignacion->fecha_fin
                    ? Carbon::parse($asignacion->fecha_fin)->endOfDay()
                    : null;

                return $fecha->gte($inicio)
                    && ($fin === null || $fecha->lte($fin));
            })
            ->sortByDesc('id')
            ->first();
    }

    private function clasificarAgenteEnFecha(
        int $agenteId,
        Carbon $fecha,
        Collection $arqueos,
        Collection $controles
    ): array {
        $clave = $this->claveAgenteFecha($agenteId, $fecha);

        /** @var Collection<int, object> $arqueosDia */
        $arqueosDia = $arqueos->get($clave, collect());

        /*
         * Un arqueo cuenta como realizado únicamente cuando alcanzó un estado
         * funcionalmente finalizado. BORRADOR no se considera cumplimiento.
         */
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
                'categoria' => (bool) $arqueoValido->fuera_fecha_ordinaria
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
            ->filter(fn ($arqueo) => $arqueo->estado === 'ANULADO')
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

    private function claveAgenteFecha(int $agenteId, Carbon $fecha): string
    {
        return $agenteId . '|' . $fecha->toDateString();
    }

    private function nombrePromotor(?object $asignacion): string
    {
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
            : ($asignacion->promotor_usuario ?? 'Sin promotor asignado');
    }

    private function textoCategoria(string $categoria): string
    {
        return match ($categoria) {
            'ARQUEADO' => 'Agentes con arqueo',
            'SIN_ARQUEO' => 'Agentes sin arqueo',
            'NO_ATENDIO' => 'Agentes que no atendieron',
            'EXTEMPORANEO' => 'Arqueos extemporáneos',
            'ANULADO' => 'Arqueos anulados',
            default => 'Cumplimiento de arqueos',
        };
    }
}
