<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AgenteController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarGerencia($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $estado = trim((string) $request->string('estado'));
        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');

        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->when(
                $regionId > 0,
                fn ($q) => $q->where('r.region_id', $regionId)
            )
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.region_id',
                'reg.nombre as region_nombre',
            ]);

        $promotoresPorRuta = DB::table(
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
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', today())
            ->where(function ($q): void {
                $q->whereNull('apr.fecha_fin')
                    ->orWhereDate(
                        'apr.fecha_fin',
                        '>=',
                        today()
                    );
            })
            ->groupBy('apr.ruta_id')
            ->selectRaw('apr.ruta_id')
            ->selectRaw(
                "GROUP_CONCAT(
                    DISTINCT up.id
                    ORDER BY up.id
                    SEPARATOR ','
                ) as promotor_id"
            )
            ->selectRaw(
                "GROUP_CONCAT(
                    DISTINCT up.usuario
                    ORDER BY up.usuario
                    SEPARATOR ', '
                ) as promotor_usuario"
            )
            ->selectRaw(
                "GROUP_CONCAT(
                    DISTINCT COALESCE(
                        NULLIF(
                            TRIM(
                                CONCAT(
                                    COALESCE(dp.nombres, ''),
                                    ' ',
                                    COALESCE(dp.apellidos, '')
                                )
                            ),
                            ''
                        ),
                        up.usuario
                    )
                    ORDER BY COALESCE(
                        NULLIF(
                            TRIM(
                                CONCAT(
                                    COALESCE(dp.nombres, ''),
                                    ' ',
                                    COALESCE(dp.apellidos, '')
                                )
                            ),
                            ''
                        ),
                        up.usuario
                    )
                    SEPARATOR ', '
                ) as promotor_nombres"
            )
            ->selectRaw("'' as promotor_apellidos");

        $agentes = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join(
                'regiones as reg',
                'reg.id',
                '=',
                'r.region_id'
            )
            ->leftJoinSub(
                $promotoresPorRuta,
                'promotores_ruta',
                function ($join): void {
                    $join->on(
                        'promotores_ruta.ruta_id',
                        '=',
                        'r.id'
                    );
                }
            )
            ->when(
                $regionId > 0,
                fn ($q) => $q->where(
                    'reg.id',
                    $regionId
                )
            )
            ->when(
                $rutaId > 0,
                fn ($q) => $q->where(
                    'r.id',
                    $rutaId
                )
            )
            ->when(
                $estado !== '',
                fn ($q) => $q->where(
                    'a.estado',
                    $estado
                )
            )
            ->when(
                $buscar !== '',
                function ($q) use ($buscar): void {
                    $q->where(
                        function ($s) use ($buscar): void {
                            $s->where(
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
                                )
                                ->orWhere(
                                    'a.direccion',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'r.nombre',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'reg.nombre',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'promotores_ruta.promotor_usuario',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'promotores_ruta.promotor_nombres',
                                    'like',
                                    '%' . $buscar . '%'
                                );
                        }
                    );
                }
            )
            ->select([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'a.estado',
                'r.id as ruta_id',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.id as region_id',
                'reg.nombre as region_nombre',
                'promotores_ruta.promotor_id',
                'promotores_ruta.promotor_usuario',
                'promotores_ruta.promotor_nombres',
                'promotores_ruta.promotor_apellidos',
            ])
            ->selectSub(
                DB::table('arqueos')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'arqueos.agente_id',
                        'a.id'
                    )
                    ->where(
                        'arqueos.estado',
                        '!=',
                        'ANULADO'
                    ),
                'total_arqueos'
            )
            ->selectSub(
                DB::table('arqueos')
                    ->select('fecha_arqueo')
                    ->whereColumn(
                        'arqueos.agente_id',
                        'a.id'
                    )
                    ->where(
                        'arqueos.estado',
                        '!=',
                        'ANULADO'
                    )
                    ->orderByDesc(
                        'arqueos.fecha_arqueo'
                    )
                    ->orderByDesc('arqueos.id')
                    ->limit(1),
                'ultimo_arqueo'
            )
            ->selectSub(
                DB::table('arqueos')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'arqueos.agente_id',
                        'a.id'
                    )
                    ->where(
                        'arqueos.tipo',
                        'DIARIO_AGENTE'
                    )
                    ->whereDate(
                        'arqueos.fecha_arqueo',
                        today()
                    )
                    ->where(
                        'arqueos.estado',
                        '!=',
                        'ANULADO'
                    ),
                'arqueo_hoy'
            )
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->orderBy('a.nombre_negocio')
            ->paginate(15)
            ->withQueryString();

        $totalAgentes = DB::table('agentes')
            ->count();

        $totalActivos = DB::table('agentes')
            ->where('estado', 'ACTIVO')
            ->count();

        $totalInactivos = DB::table('agentes')
            ->where('estado', '!=', 'ACTIVO')
            ->count();

        $conArqueoHoy = DB::table('agentes as a')
            ->where('a.estado', 'ACTIVO')
            ->whereExists(function ($q): void {
                $q->selectRaw('1')
                    ->from('arqueos as arq')
                    ->whereColumn(
                        'arq.agente_id',
                        'a.id'
                    )
                    ->where(
                        'arq.tipo',
                        'DIARIO_AGENTE'
                    )
                    ->whereDate(
                        'arq.fecha_arqueo',
                        today()
                    )
                    ->where(
                        'arq.estado',
                        '!=',
                        'ANULADO'
                    );
            })
            ->count();

        return view(
            'gerencia.agentes.index',
            compact(
                'agentes',
                'regiones',
                'rutas',
                'buscar',
                'estado',
                'regionId',
                'rutaId',
                'totalAgentes',
                'totalActivos',
                'totalInactivos',
                'conArqueoHoy'
            )
        );
    }

    public function show(
        Request $request,
        int $agente
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarGerencia($usuario);

        $promotoresPorRuta = DB::table(
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
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', today())
            ->where(function ($q): void {
                $q->whereNull('apr.fecha_fin')
                    ->orWhereDate(
                        'apr.fecha_fin',
                        '>=',
                        today()
                    );
            })
            ->groupBy('apr.ruta_id')
            ->selectRaw('apr.ruta_id')
            ->selectRaw(
                "GROUP_CONCAT(
                    DISTINCT up.id
                    ORDER BY up.id
                    SEPARATOR ','
                ) as promotor_id"
            )
            ->selectRaw(
                "GROUP_CONCAT(
                    DISTINCT up.usuario
                    ORDER BY up.usuario
                    SEPARATOR ', '
                ) as promotor_usuario"
            )
            ->selectRaw(
                "GROUP_CONCAT(
                    DISTINCT COALESCE(
                        NULLIF(
                            TRIM(
                                CONCAT(
                                    COALESCE(dp.nombres, ''),
                                    ' ',
                                    COALESCE(dp.apellidos, '')
                                )
                            ),
                            ''
                        ),
                        up.usuario
                    )
                    ORDER BY COALESCE(
                        NULLIF(
                            TRIM(
                                CONCAT(
                                    COALESCE(dp.nombres, ''),
                                    ' ',
                                    COALESCE(dp.apellidos, '')
                                )
                            ),
                            ''
                        ),
                        up.usuario
                    )
                    SEPARATOR ', '
                ) as promotor_nombres"
            )
            ->selectRaw("'' as promotor_apellidos");

        $registro = DB::table('agentes as a')
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
            ->leftJoinSub(
                $promotoresPorRuta,
                'promotores_ruta',
                function ($join): void {
                    $join->on(
                        'promotores_ruta.ruta_id',
                        '=',
                        'r.id'
                    );
                }
            )
            ->where('a.id', $agente)
            ->select([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'a.estado',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'promotores_ruta.promotor_usuario',
                'promotores_ruta.promotor_nombres',
                'promotores_ruta.promotor_apellidos',
            ])
            ->first();

        abort_if(
            ! $registro,
            404,
            'El Agente solicitado no existe.'
        );

        $arqueos = DB::table('arqueos as arq')
            ->leftJoin(
                'usuarios as uc',
                'uc.id',
                '=',
                'arq.creado_por'
            )
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'uc.id'
            )
            ->where(
                'arq.agente_id',
                $agente
            )
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'arq.estado',
                'arq.diferencia',
                'arq.fuera_fecha_ordinaria',
                'uc.usuario as responsable_usuario',
                'dp.nombres as responsable_nombres',
                'dp.apellidos as responsable_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(12)
            ->withQueryString();

        return view(
            'gerencia.agentes.show',
            [
                'agente' => $registro,
                'arqueos' => $arqueos,
            ]
        );
    }

    private function validarGerencia(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Gerencia',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
