<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AgenteController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');
        $estado = trim((string) $request->string('estado'));

        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $rutas = DB::table('rutas')
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('region_id', $regionId)
            )
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre', 'region_id']);

        /*
        |--------------------------------------------------------------------------
        | Promotores activos agrupados por Ruta
        |--------------------------------------------------------------------------
        |
        | Una Ruta puede tener más de un Promotor activo.
        |
        | La consulta anterior hacía JOIN directamente contra
        | asignaciones_promotor_ruta, provocando una fila del Agente por cada
        | Promotor asignado a la Ruta.
        |
        | Aquí agrupamos primero los Promotores por Ruta para mantener una sola
        | fila por Agente.
        |
        */

        $promotoresPorRuta = DB::table('asignaciones_promotor_ruta as apr')
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
            ->where(function ($query): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', today());
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
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
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
                                )
                                ->orWhere(
                                    'a.direccion',
                                    'like',
                                    '%' . $buscar . '%'
                                );
                        }
                    );
                }
            )
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('reg.id', $regionId)
            )
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where('r.id', $rutaId)
            )
            ->when(
                $estado !== '',
                fn ($query) => $query->where('a.estado', $estado)
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
                    ->orderByDesc('fecha_arqueo')
                    ->orderByDesc('id')
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

        $totalActivos = DB::table('agentes')
            ->where('estado', 'ACTIVO')
            ->count();

        $totalInactivos = DB::table('agentes')
            ->where('estado', '!=', 'ACTIVO')
            ->count();

        $conArqueoHoy = DB::table('agentes as a')
            ->where('a.estado', 'ACTIVO')
            ->whereExists(function ($query): void {
                $query
                    ->selectRaw('1')
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

        return view('jefe.agentes.index', compact(
            'agentes',
            'regiones',
            'rutas',
            'buscar',
            'regionId',
            'rutaId',
            'estado',
            'totalActivos',
            'totalInactivos',
            'conArqueoHoy'
        ));
    }

    public function show(Request $request, int $agente): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $registro = $this->obtenerAgente($agente);

        abort_if(
            ! $registro,
            404,
            'El agente solicitado no existe.'
        );

        $resumen = $this->obtenerResumen($agente);
        $ultimosArqueos = $this->obtenerUltimosArqueos($agente);

        return view('jefe.agentes.show', [
            'agente' => $registro,
            'resumen' => $resumen,
            'ultimosArqueos' => $ultimosArqueos,
        ]);
    }

    public function imprimirFicha(
        Request $request,
        int $agente
    ): Response {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $registro = $this->obtenerAgente($agente);

        abort_if(
            ! $registro,
            404,
            'El agente solicitado no existe.'
        );

        $resumen = $this->obtenerResumen($agente);
        $ultimosArqueos = $this->obtenerUltimosArqueos($agente);

        $pdf = Pdf::loadView(
            'jefe.agentes.ficha-pdf',
            [
                'agente' => $registro,
                'resumen' => $resumen,
                'ultimosArqueos' => $ultimosArqueos,
            ]
        )->setPaper('letter', 'portrait');

        return $pdf->stream(
            'agente-'
            . $registro->codigo_agente
            . '-'
            . now()->format('Ymd-His')
            . '.pdf'
        );
    }

    public function imprimir(
        Request $request,
        int $agente,
        Arqueo $arqueo
    ): Response {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        abort_if(
            (int) $arqueo->agente_id !== $agente,
            404,
            'El arqueo no pertenece al agente seleccionado.'
        );

        abort_if(
            ! in_array(
                $arqueo->tipo,
                ['DIARIO_AGENTE', 'VISITA_PROMOTOR'],
                true
            ),
            404,
            'El arqueo solicitado no es válido.'
        );

        $arqueo->loadMissing(['detalles', 'firmas']);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        $pdf = Pdf::loadView(
            'jefe.agentes.pdf',
            compact(
                'arqueo',
                'billetes',
                'monedas'
            )
        )->setPaper('letter', 'portrait');

        return $pdf->stream(
            $arqueo->numero_arqueo . '.pdf'
        );
    }

    private function obtenerAgente(int $agente): ?object
    {
        /*
        |--------------------------------------------------------------------------
        | Promotores activos agrupados por Ruta
        |--------------------------------------------------------------------------
        |
        | El detalle del Agente utiliza la misma regla del listado:
        | una Ruta puede tener múltiples Promotores activos sin provocar
        | múltiples filas para el mismo Agente.
        |
        */

        $promotoresPorRuta = DB::table('asignaciones_promotor_ruta as apr')
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
            ->where(function ($query): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', today());
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

        return DB::table('agentes as a')
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
            ->leftJoin(
                'usuarios as ua',
                'ua.id',
                '=',
                'a.usuario_id'
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
                'a.usuario_id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'a.estado',
                'ua.usuario as agente_usuario',
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
            ->first();
    }

    private function obtenerResumen(int $agente): object
    {
        return DB::table('arqueos')
            ->where('agente_id', $agente)
            ->selectRaw('COUNT(*) as total_arqueos')
            ->selectRaw(
                "SUM(CASE
                    WHEN tipo = 'DIARIO_AGENTE'
                    AND estado != 'ANULADO'
                    THEN 1 ELSE 0 END
                ) as arqueos_agente"
            )
            ->selectRaw(
                "SUM(CASE
                    WHEN tipo = 'VISITA_PROMOTOR'
                    AND estado != 'ANULADO'
                    THEN 1 ELSE 0 END
                ) as arqueos_promotor"
            )
            ->selectRaw(
                "SUM(CASE
                    WHEN estado = 'ANULADO'
                    THEN 1 ELSE 0 END
                ) as anulados"
            )
            ->first();
    }

    private function obtenerUltimosArqueos(int $agente)
    {
        return DB::table('arqueos')
            ->where('agente_id', $agente)
            ->select([
                'id',
                'numero_arqueo',
                'fecha_arqueo',
                'tipo',
                'estado',
                'fuera_fecha_ordinaria',
                'total_arqueado',
                'diferencia',
            ])
            ->orderByDesc('fecha_arqueo')
            ->orderByDesc('id')
            ->limit(10)
            ->get();
    }

    private function validarJefe(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'jefedeAgentes',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
