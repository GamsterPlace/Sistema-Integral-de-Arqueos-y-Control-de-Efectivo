<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReporteController extends Controller
{
    private const REPORTES = [
        'resumen' => 'Resumen Ejecutivo',
        'faltantes' => 'Agentes con Faltantes',
        'sobrantes' => 'Agentes con Sobrantes',
        'ranking-faltantes' => 'Ranking de Agentes con Faltantes',
        'ranking-sobrantes' => 'Ranking de Agentes con Sobrantes',
        'exactos' => 'Arqueos Exactos',
        'historial-agente' => 'Historial por Agente',
        'historial-auditoria' => 'Historial de Auditoría',
        'region-faltantes' => 'Regiones con más Faltantes',
        'region-sobrantes' => 'Regiones con más Sobrantes',
        'ruta-faltantes' => 'Rutas con más Faltantes',
        'ruta-sobrantes' => 'Rutas con más Sobrantes',
    ];

    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAuditoria($usuario);

        $filtros = $this->obtenerFiltros($request);
        $filtros['auditor_id'] = (int) $usuario->id;
        $catalogos = $this->catalogos($filtros);

        [$resultados, $columnas, $metricas] =
            $this->generarReporte($filtros, true);

        return view('auditoria.reportes.index', [
            ...$catalogos,
            ...$filtros,
            'tiposReporte' => self::REPORTES,
            'resultados' => $resultados,
            'columnas' => $columnas,
            'metricas' => $metricas,
        ]);
    }

    public function imprimir(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAuditoria($usuario);

        $filtros = $this->obtenerFiltros($request);
        $filtros['auditor_id'] = (int) $usuario->id;

        [$resultados, $columnas, $metricas] =
            $this->generarReporte($filtros, false);

        $pdf = Pdf::loadView('auditoria.reportes.pdf', [
            'titulo' => self::REPORTES[$filtros['reporte']],
            'resultados' => $resultados,
            'columnas' => $columnas,
            'metricas' => $metricas,
            'filtros' => $filtros,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream(
            'reporte-' . $filtros['reporte']
            . '-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    private function generarReporte(
        array $filtros,
        bool $paginar
    ): array {
        return match ($filtros['reporte']) {
            'faltantes' =>
                $this->reporteIncidencias($filtros, 'FALTANTE', $paginar),

            'sobrantes' =>
                $this->reporteIncidencias($filtros, 'SOBRANTE', $paginar),

            'ranking-faltantes' =>
                $this->rankingAgentes($filtros, 'FALTANTE', $paginar),

            'ranking-sobrantes' =>
                $this->rankingAgentes($filtros, 'SOBRANTE', $paginar),

            'exactos' =>
                $this->reporteExactos($filtros, $paginar),

            'historial-agente' =>
                $this->historialAgente($filtros, $paginar),

            'historial-auditoria' =>
                $this->historialAuditoria($filtros, $paginar),

            'region-faltantes' =>
                $this->rankingRegiones($filtros, 'FALTANTE', $paginar),

            'region-sobrantes' =>
                $this->rankingRegiones($filtros, 'SOBRANTE', $paginar),

            'ruta-faltantes' =>
                $this->rankingRutas($filtros, 'FALTANTE', $paginar),

            'ruta-sobrantes' =>
                $this->rankingRutas($filtros, 'SOBRANTE', $paginar),

            default =>
                $this->resumenEjecutivo($filtros, $paginar),
        };
    }

    private function consultaBase(array $filtros): Builder
    {
        return DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->leftJoin('usuarios as uc', 'uc.id', '=', 'arq.creado_por')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'uc.id')
            ->where('arq.estado', '!=', 'ANULADO')
            ->where('arq.tipo', 'VISITA_AUDITORIA')
            ->where('arq.creado_por', $filtros['auditor_id'])
            ->when(
                $filtros['agente_id'] > 0,
                fn ($q) => $q->where('arq.agente_id', $filtros['agente_id'])
            )
            ->when(
                $filtros['region_id'] > 0,
                fn ($q) => $q->where('reg.id', $filtros['region_id'])
            )
            ->when(
                $filtros['ruta_id'] > 0,
                fn ($q) => $q->where('r.id', $filtros['ruta_id'])
            )
            ->when(
                $filtros['desde'],
                fn ($q) => $q->whereDate(
                    'arq.fecha_arqueo',
                    '>=',
                    $filtros['desde']
                )
            )
            ->when(
                $filtros['hasta'],
                fn ($q) => $q->whereDate(
                    'arq.fecha_arqueo',
                    '<=',
                    $filtros['hasta']
                )
            );
    }

    private function reporteIncidencias(
        array $filtros,
        string $tipo,
        bool $paginar
    ): array {
        $query = $this->consultaBase($filtros);

        if ($tipo === 'FALTANTE') {
            $query->where('arq.diferencia', '<', 0);
        } else {
            $query->where('arq.diferencia', '>', 0);
        }

        $query->select([
            'arq.id',
            'arq.numero_arqueo',
            'arq.fecha_arqueo',
            'arq.tipo',
            'a.codigo_agente',
            'a.nombre_negocio',
            'r.nombre as ruta_nombre',
            'reg.nombre as region_nombre',
            'arq.total_arqueado',
            'arq.saldo_sistema',
            'arq.diferencia',
            'uc.usuario as responsable_usuario',
            'dp.nombres as responsable_nombres',
            'dp.apellidos as responsable_apellidos',
        ])
            ->orderByRaw('ABS(arq.diferencia) DESC')
            ->orderByDesc('arq.fecha_arqueo');

        $metricasQuery = clone $query;

        $totalCasos = DB::query()
            ->fromSub($metricasQuery, 'x')
            ->count();

        $monto = DB::query()
            ->fromSub(clone $query, 'x')
            ->sum(DB::raw('ABS(x.diferencia)'));

        $resultados = $this->resolverResultados(
            $query,
            $paginar,
            $filtros['por_pagina']
        );

        return [
            $resultados,
            [
                'numero_arqueo' => 'Arqueo',
                'fecha_arqueo' => 'Fecha',
                'agente' => 'Agente',
                'region_nombre' => 'Región',
                'ruta_nombre' => 'Ruta',
                'responsable' => 'Responsable',
                'saldo_sistema' => 'Saldo Sistema',
                'total_arqueado' => 'Total Arqueado',
                'diferencia' =>
                    $tipo === 'FALTANTE' ? 'Faltante' : 'Sobrante',
            ],
            [
                'Casos encontrados' => $totalCasos,
                $tipo === 'FALTANTE'
                    ? 'Monto total faltante'
                    : 'Monto total sobrante'
                    => 'Q ' . number_format((float) $monto, 2),
            ],
        ];
    }

    private function rankingAgentes(
        array $filtros,
        string $tipo,
        bool $paginar
    ): array {
        $query = $this->consultaBase($filtros);

        $operador = $tipo === 'FALTANTE' ? '<' : '>';
        $query->where('arq.diferencia', $operador, 0);

        $query
            ->select([
                'a.id as agente_id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->selectRaw('COUNT(*) as cantidad_incidencias')
            ->selectRaw('SUM(ABS(arq.diferencia)) as monto_acumulado')
            ->selectRaw('MAX(ABS(arq.diferencia)) as mayor_incidencia')
            ->groupBy([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre',
                'reg.nombre',
            ])
            ->orderByDesc('cantidad_incidencias')
            ->orderByDesc('monto_acumulado');

        $resultados = $this->resolverResultados(
            $query,
            $paginar,
            $filtros['por_pagina']
        );

        return [
            $resultados,
            [
                'posicion' => '#',
                'agente' => 'Agente',
                'region_nombre' => 'Región',
                'ruta_nombre' => 'Ruta',
                'cantidad_incidencias' =>
                    $tipo === 'FALTANTE'
                        ? 'Faltantes'
                        : 'Sobrantes',
                'monto_acumulado' => 'Monto Acumulado',
                'mayor_incidencia' => 'Mayor Incidencia',
            ],
            [],
        ];
    }

    private function reporteExactos(
        array $filtros,
        bool $paginar
    ): array {
        $query = $this->consultaBase($filtros)
            ->where('arq.diferencia', '=', 0)
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'uc.usuario as responsable_usuario',
                'dp.nombres as responsable_nombres',
                'dp.apellidos as responsable_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo');

        $total = (clone $query)->count();

        return [
            $this->resolverResultados(
                $query,
                $paginar,
                $filtros['por_pagina']
            ),
            [
                'numero_arqueo' => 'Arqueo',
                'fecha_arqueo' => 'Fecha',
                'agente' => 'Agente',
                'region_nombre' => 'Región',
                'ruta_nombre' => 'Ruta',
                'responsable' => 'Responsable',
                'tipo' => 'Tipo',
            ],
            ['Arqueos exactos' => $total],
        ];
    }

    private function historialAgente(
        array $filtros,
        bool $paginar
    ): array {
        $query = $this->consultaBase($filtros)
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'arq.estado',
                'arq.diferencia',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'uc.usuario as responsable_usuario',
                'dp.nombres as responsable_nombres',
                'dp.apellidos as responsable_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id');

        return [
            $this->resolverResultados(
                $query,
                $paginar,
                $filtros['por_pagina']
            ),
            [
                'numero_arqueo' => 'Arqueo',
                'fecha_arqueo' => 'Fecha',
                'agente' => 'Agente',
                'tipo' => 'Tipo',
                'estado' => 'Estado',
                'responsable' => 'Responsable',
                'diferencia' => 'Diferencia',
            ],
            [],
        ];
    }

    private function historialAuditoria(
        array $filtros,
        bool $paginar
    ): array {
        $query = $this->consultaBase($filtros)
            ->where('arq.tipo', 'VISITA_AUDITORIA')
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.estado',
                'arq.diferencia',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'uc.usuario as responsable_usuario',
                'dp.nombres as responsable_nombres',
                'dp.apellidos as responsable_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo');

        return [
            $this->resolverResultados(
                $query,
                $paginar,
                $filtros['por_pagina']
            ),
            [
                'numero_arqueo' => 'Arqueo',
                'fecha_arqueo' => 'Fecha',
                'responsable' => 'Auditoría',
                'agente' => 'Agente',
                'region_nombre' => 'Región',
                'ruta_nombre' => 'Ruta',
                'estado' => 'Estado',
                'diferencia' => 'Diferencia',
            ],
            [],
        ];
    }

    private function rankingRegiones(
        array $filtros,
        string $tipo,
        bool $paginar
    ): array {
        $operador = $tipo === 'FALTANTE' ? '<' : '>';

        $query = $this->consultaBase($filtros)
            ->where('arq.diferencia', $operador, 0)
            ->select([
                'reg.id as region_id',
                'reg.nombre as region_nombre',
            ])
            ->selectRaw('COUNT(*) as cantidad_incidencias')
            ->selectRaw('COUNT(DISTINCT a.id) as agentes_afectados')
            ->selectRaw('SUM(ABS(arq.diferencia)) as monto_acumulado')
            ->groupBy('reg.id', 'reg.nombre')
            ->orderByDesc('cantidad_incidencias')
            ->orderByDesc('monto_acumulado');

        return [
            $this->resolverResultados(
                $query,
                $paginar,
                $filtros['por_pagina']
            ),
            [
                'posicion' => '#',
                'region_nombre' => 'Región',
                'cantidad_incidencias' =>
                    $tipo === 'FALTANTE' ? 'Faltantes' : 'Sobrantes',
                'agentes_afectados' => 'Agentes Afectados',
                'monto_acumulado' => 'Monto Acumulado',
            ],
            [],
        ];
    }

    private function rankingRutas(
        array $filtros,
        string $tipo,
        bool $paginar
    ): array {
        $operador = $tipo === 'FALTANTE' ? '<' : '>';

        $query = $this->consultaBase($filtros)
            ->where('arq.diferencia', $operador, 0)
            ->select([
                'r.id as ruta_id',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->selectRaw('COUNT(*) as cantidad_incidencias')
            ->selectRaw('COUNT(DISTINCT a.id) as agentes_afectados')
            ->selectRaw('SUM(ABS(arq.diferencia)) as monto_acumulado')
            ->groupBy([
                'r.id',
                'r.codigo',
                'r.nombre',
                'reg.nombre',
            ])
            ->orderByDesc('cantidad_incidencias')
            ->orderByDesc('monto_acumulado');

        return [
            $this->resolverResultados(
                $query,
                $paginar,
                $filtros['por_pagina']
            ),
            [
                'posicion' => '#',
                'ruta' => 'Ruta',
                'region_nombre' => 'Región',
                'cantidad_incidencias' =>
                    $tipo === 'FALTANTE' ? 'Faltantes' : 'Sobrantes',
                'agentes_afectados' => 'Agentes Afectados',
                'monto_acumulado' => 'Monto Acumulado',
            ],
            [],
        ];
    }

    private function resumenEjecutivo(
        array $filtros,
        bool $paginar
    ): array {
        $base = $this->consultaBase($filtros);

        $metricas = (clone $base)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                "SUM(CASE WHEN arq.diferencia < 0 THEN 1 ELSE 0 END) as faltantes"
            )
            ->selectRaw(
                "SUM(CASE WHEN arq.diferencia > 0 THEN 1 ELSE 0 END) as sobrantes"
            )
            ->selectRaw(
                "SUM(CASE WHEN arq.diferencia = 0 THEN 1 ELSE 0 END) as exactos"
            )
            ->selectRaw(
                "SUM(CASE WHEN arq.diferencia < 0 THEN ABS(arq.diferencia) ELSE 0 END) as monto_faltantes"
            )
            ->selectRaw(
                "SUM(CASE WHEN arq.diferencia > 0 THEN arq.diferencia ELSE 0 END) as monto_sobrantes"
            )
            ->first();

        $query = $base
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'arq.diferencia',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id');

        return [
            $this->resolverResultados(
                $query,
                $paginar,
                $filtros['por_pagina']
            ),
            [
                'numero_arqueo' => 'Arqueo',
                'fecha_arqueo' => 'Fecha',
                'agente' => 'Agente',
                'region_nombre' => 'Región',
                'ruta_nombre' => 'Ruta',
                'tipo' => 'Tipo',
                'diferencia' => 'Diferencia',
            ],
            [
                'Total arqueos' => (int) ($metricas->total ?? 0),
                'Exactos' => (int) ($metricas->exactos ?? 0),
                'Faltantes' => (int) ($metricas->faltantes ?? 0),
                'Sobrantes' => (int) ($metricas->sobrantes ?? 0),
                'Monto faltantes' =>
                    'Q ' . number_format(
                        (float) ($metricas->monto_faltantes ?? 0),
                        2
                    ),
                'Monto sobrantes' =>
                    'Q ' . number_format(
                        (float) ($metricas->monto_sobrantes ?? 0),
                        2
                    ),
            ],
        ];
    }

    private function resolverResultados(
        Builder $query,
        bool $paginar,
        int $porPagina
    ) {
        if ($paginar) {
            return $query
                ->paginate($porPagina)
                ->withQueryString();
        }

        return $query->get();
    }

    private function catalogos(array $filtros): array
    {
        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->when(
                $filtros['region_id'] > 0,
                fn ($q) => $q->where(
                    'r.region_id',
                    $filtros['region_id']
                )
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

        $agentes = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->when(
                $filtros['region_id'] > 0,
                fn ($q) => $q->where(
                    'reg.id',
                    $filtros['region_id']
                )
            )
            ->when(
                $filtros['ruta_id'] > 0,
                fn ($q) => $q->where(
                    'r.id',
                    $filtros['ruta_id']
                )
            )
            ->orderBy('a.nombre_negocio')
            ->get([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
            ]);

        return compact(
            'regiones',
            'rutas',
            'agentes'
        );
    }

    private function obtenerFiltros(Request $request): array
    {
        $reporte = trim(
            (string) $request->string('reporte', 'resumen')
        );

        if (! array_key_exists($reporte, self::REPORTES)) {
            $reporte = 'resumen';
        }

        $porPagina = $request->integer('por_pagina');

        if (! in_array($porPagina, [10, 20, 50, 100], true)) {
            $porPagina = 20;
        }

        return [
            'reporte' => $reporte,
            'agente_id' => $request->integer('agente_id'),
            'region_id' => $request->integer('region_id'),
            'ruta_id' => $request->integer('ruta_id'),
            'auditor_id' => 0,
            'desde' => $request->input('desde'),
            'hasta' => $request->input('hasta'),
            'por_pagina' => $porPagina,
        ];
    }

    private function validarAuditoria(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Auditoria',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
