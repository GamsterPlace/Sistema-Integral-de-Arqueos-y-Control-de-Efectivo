<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReporteController extends Controller
{
    /**
     * Tipos de reportes disponibles para el Administrador.
     */
    private array $tiposReporte = [
        'resumen' => 'Resumen General',
        'faltantes' => 'Detalle de Faltantes',
        'sobrantes' => 'Detalle de Sobrantes',
        'ranking-faltantes' => 'Ranking de Faltantes',
        'ranking-sobrantes' => 'Ranking de Sobrantes',
        'exactos' => 'Arqueos Exactos',
        'historial-agente' => 'Historial por Agente',
        'historial-promotor' => 'Historial por Promotor',
        'region-faltantes' => 'Faltantes por Región',
        'region-sobrantes' => 'Sobrantes por Región',
        'ruta-faltantes' => 'Faltantes por Ruta',
        'ruta-sobrantes' => 'Sobrantes por Ruta',
    ];

    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

        $filtros = $this->obtenerFiltros($request);
        $catalogos = $this->obtenerCatalogos($filtros['region_id']);

        [$resultados, $columnas, $metricas] = $this->generarReporte(
            $filtros,
            true
        );

        return view('administrador.reportes.index', [
            'tiposReporte' => $this->tiposReporte,
            'reporte' => $filtros['reporte'],
            'resultados' => $resultados,
            'columnas' => $columnas,
            'metricas' => $metricas,

            'agentes' => $catalogos['agentes'],
            'promotores' => $catalogos['promotores'],
            'regiones' => $catalogos['regiones'],
            'rutas' => $catalogos['rutas'],

            'agente_id' => $filtros['agente_id'],
            'promotor_id' => $filtros['promotor_id'],
            'region_id' => $filtros['region_id'],
            'ruta_id' => $filtros['ruta_id'],
            'tipo' => $filtros['tipo'],
            'desde' => $filtros['desde'],
            'hasta' => $filtros['hasta'],
            'por_pagina' => $filtros['por_pagina'],
        ]);
    }

    public function imprimir(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

        $filtros = $this->obtenerFiltros($request);

        [$resultados, $columnas, $metricas] = $this->generarReporte(
            $filtros,
            false
        );

        $titulo = $this->tiposReporte[$filtros['reporte']]
            ?? 'Reporte Administrativo';

        $pdf = Pdf::loadView('administrador.reportes.pdf', [
            'titulo' => $titulo,
            'resultados' => $resultados,
            'columnas' => $columnas,
            'metricas' => $metricas,
        ])->setPaper('letter', 'landscape');

        $nombreArchivo = 'reporte-administrativo-'
            . $filtros['reporte']
            . '-'
            . now()->format('Ymd-His')
            . '.pdf';

        return $pdf->stream($nombreArchivo);
    }

    /**
     * @return array{
     *     reporte:string,
     *     agente_id:int,
     *     promotor_id:int,
     *     region_id:int,
     *     ruta_id:int,
     *     tipo:string,
     *     desde:?string,
     *     hasta:?string,
     *     por_pagina:int
     * }
     */
    private function obtenerFiltros(Request $request): array
    {
        $reporte = trim((string) $request->input('reporte', 'resumen'));

        if (!array_key_exists($reporte, $this->tiposReporte)) {
            $reporte = 'resumen';
        }

        $tipo = trim((string) $request->input('tipo', ''));

        $tiposPermitidos = [
            '',
            'DIARIO_AGENTE',
            'VISITA_PROMOTOR',
            'VISITA_AUDITORIA',
        ];

        if (!in_array($tipo, $tiposPermitidos, true)) {
            $tipo = '';
        }

        $porPagina = (int) $request->input('por_pagina', 20);

        if (!in_array($porPagina, [10, 20, 50, 100], true)) {
            $porPagina = 20;
        }

        return [
            'reporte' => $reporte,
            'agente_id' => $request->integer('agente_id'),
            'promotor_id' => $request->integer('promotor_id'),
            'region_id' => $request->integer('region_id'),
            'ruta_id' => $request->integer('ruta_id'),
            'tipo' => $tipo,
            'desde' => $request->filled('desde')
                ? (string) $request->input('desde')
                : null,
            'hasta' => $request->filled('hasta')
                ? (string) $request->input('hasta')
                : null,
            'por_pagina' => $porPagina,
        ];
    }

    /**
     * @return array{
     *     agentes:Collection,
     *     promotores:Collection,
     *     regiones:Collection,
     *     rutas:Collection
     * }
     */
    private function obtenerCatalogos(int $regionId): array
    {
        $agentes = DB::table('agentes')
            ->orderBy('codigo_agente')
            ->get([
                'id',
                'codigo_agente',
                'nombre_negocio',
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
            ->orderByRaw(
                "COALESCE(dp.nombres, u.usuario), "
                . "COALESCE(dp.apellidos, '')"
            )
            ->get([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ]);

        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
            ]);

        $rutas = DB::table('rutas as r')
            ->join(
                'regiones as reg',
                'reg.id',
                '=',
                'r.region_id'
            )
            ->when(
                $regionId > 0,
                fn (Builder $q) => $q->where(
                    'r.region_id',
                    $regionId
                )
            )
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.region_id',
            ]);

        return compact(
            'agentes',
            'promotores',
            'regiones',
            'rutas'
        );
    }

    /**
     * @return array{0:mixed,1:array,2:array}
     */
    private function generarReporte(
        array $filtros,
        bool $paginar
    ): array {
        $base = $this->consultaBase($filtros);
        $metricas = $this->metricasGenerales($base);

        return match ($filtros['reporte']) {
            'faltantes' => $this->reporteDetalleDiferencia(
                $base,
                '<',
                0,
                $metricas,
                $filtros,
                $paginar
            ),

            'sobrantes' => $this->reporteDetalleDiferencia(
                $base,
                '>',
                0,
                $metricas,
                $filtros,
                $paginar
            ),

            'exactos' => $this->reporteExactos(
                $base,
                $metricas,
                $filtros,
                $paginar
            ),

            'ranking-faltantes' => $this->reporteRankingAgentes(
                $base,
                '<',
                0,
                $metricas,
                $filtros,
                $paginar
            ),

            'ranking-sobrantes' => $this->reporteRankingAgentes(
                $base,
                '>',
                0,
                $metricas,
                $filtros,
                $paginar
            ),

            'historial-agente' => $this->reporteHistorialAgente(
                $base,
                $metricas,
                $filtros,
                $paginar
            ),

            'historial-promotor' => $this->reporteHistorialPromotor(
                $base,
                $metricas,
                $filtros,
                $paginar
            ),

            'region-faltantes' => $this->reporteRegion(
                $base,
                '<',
                0,
                $metricas,
                $filtros,
                $paginar
            ),

            'region-sobrantes' => $this->reporteRegion(
                $base,
                '>',
                0,
                $metricas,
                $filtros,
                $paginar
            ),

            'ruta-faltantes' => $this->reporteRuta(
                $base,
                '<',
                0,
                $metricas,
                $filtros,
                $paginar
            ),

            'ruta-sobrantes' => $this->reporteRuta(
                $base,
                '>',
                0,
                $metricas,
                $filtros,
                $paginar
            ),

            default => $this->reporteResumen(
                $base,
                $metricas,
                $filtros,
                $paginar
            ),
        };
    }

    private function consultaBase(array $filtros): Builder
    {
        return DB::table('arqueos as arq')
            ->join(
                'agentes as a',
                'a.id',
                '=',
                'arq.agente_id'
            )
            ->leftJoin(
                'rutas as r',
                'r.id',
                '=',
                'a.ruta_id'
            )
            ->leftJoin(
                'regiones as reg',
                'reg.id',
                '=',
                'r.region_id'
            )
            ->leftJoin(
                'usuarios as responsable',
                'responsable.id',
                '=',
                'arq.creado_por'
            )
            ->leftJoin(
                'datos_personales as dp_responsable',
                'dp_responsable.usuario_id',
                '=',
                'responsable.id'
            )
            ->where('arq.estado', '!=', 'ANULADO')
            ->when(
                $filtros['desde'],
                fn (Builder $q, string $desde) => $q->whereDate(
                    'arq.fecha_arqueo',
                    '>=',
                    $desde
                )
            )
            ->when(
                $filtros['hasta'],
                fn (Builder $q, string $hasta) => $q->whereDate(
                    'arq.fecha_arqueo',
                    '<=',
                    $hasta
                )
            )
            ->when(
                $filtros['agente_id'] > 0,
                fn (Builder $q) => $q->where(
                    'a.id',
                    $filtros['agente_id']
                )
            )
            ->when(
                $filtros['promotor_id'] > 0,
                fn (Builder $q) => $q
                    ->where(
                        'arq.tipo',
                        'VISITA_PROMOTOR'
                    )
                    ->where(
                        'arq.creado_por',
                        $filtros['promotor_id']
                    )
            )
            ->when(
                $filtros['region_id'] > 0,
                fn (Builder $q) => $q->where(
                    'reg.id',
                    $filtros['region_id']
                )
            )
            ->when(
                $filtros['ruta_id'] > 0,
                fn (Builder $q) => $q->where(
                    'r.id',
                    $filtros['ruta_id']
                )
            )
            ->when(
                $filtros['tipo'] !== '',
                fn (Builder $q) => $q->where(
                    'arq.tipo',
                    $filtros['tipo']
                )
            );
    }

    private function metricasGenerales(Builder $base): array
    {
        $datos = (clone $base)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'SUM(CASE WHEN arq.diferencia < 0 '
                . 'THEN 1 ELSE 0 END) as faltantes'
            )
            ->selectRaw(
                'SUM(CASE WHEN arq.diferencia > 0 '
                . 'THEN 1 ELSE 0 END) as sobrantes'
            )
            ->selectRaw(
                'SUM(CASE WHEN arq.diferencia = 0 '
                . 'THEN 1 ELSE 0 END) as exactos'
            )
            ->selectRaw(
                'SUM(CASE WHEN arq.diferencia < 0 '
                . 'THEN ABS(arq.diferencia) ELSE 0 END) '
                . 'as monto_faltantes'
            )
            ->selectRaw(
                'SUM(CASE WHEN arq.diferencia > 0 '
                . 'THEN arq.diferencia ELSE 0 END) '
                . 'as monto_sobrantes'
            )
            ->first();

        return [
            'Total' => (int) ($datos->total ?? 0),
            'Faltantes' => (int) ($datos->faltantes ?? 0),
            'Sobrantes' => (int) ($datos->sobrantes ?? 0),
            'Exactos' => (int) ($datos->exactos ?? 0),
            'Monto Faltantes' => 'Q '
                . number_format(
                    (float) ($datos->monto_faltantes ?? 0),
                    2
                ),
            'Monto Sobrantes' => 'Q '
                . number_format(
                    (float) ($datos->monto_sobrantes ?? 0),
                    2
                ),
        ];
    }

    private function reporteResumen(
        Builder $base,
        array $metricas,
        array $filtros,
        bool $paginar
    ): array {
        $query = $this->seleccionarDetalle($base)
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id');

        $columnas = [
            'fecha_arqueo' => 'Fecha',
            'tipo' => 'Tipo',
            'agente' => 'Agente',
            'ruta' => 'Ruta',
            'region' => 'Región',
            'responsable' => 'Responsable',
            'estado' => 'Estado',
            'diferencia' => 'Diferencia',
        ];

        return [
            $this->obtenerResultados(
                $query,
                $filtros,
                $paginar
            ),
            $columnas,
            $metricas,
        ];
    }

    private function reporteDetalleDiferencia(
        Builder $base,
        string $operador,
        float $valor,
        array $metricas,
        array $filtros,
        bool $paginar
    ): array {
        $query = $this->seleccionarDetalle($base)
            ->where('arq.diferencia', $operador, $valor)
            ->orderByRaw('ABS(arq.diferencia) DESC')
            ->orderByDesc('arq.fecha_arqueo');

        $columnas = [
            'fecha_arqueo' => 'Fecha',
            'tipo' => 'Tipo',
            'agente' => 'Agente',
            'region' => 'Región',
            'ruta' => 'Ruta',
            'saldo_sistema' => 'Saldo Sistema',
            'total_arqueado' => 'Total Arqueado',
            'diferencia' => 'Diferencia',
        ];

        return [
            $this->obtenerResultados(
                $query,
                $filtros,
                $paginar
            ),
            $columnas,
            $metricas,
        ];
    }

    private function reporteExactos(
        Builder $base,
        array $metricas,
        array $filtros,
        bool $paginar
    ): array {
        $query = $this->seleccionarDetalle($base)
            ->where('arq.diferencia', '=', 0)
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id');

        $columnas = [
            'fecha_arqueo' => 'Fecha',
            'tipo' => 'Tipo',
            'agente' => 'Agente',
            'region' => 'Región',
            'ruta' => 'Ruta',
            'responsable' => 'Responsable',
            'saldo_sistema' => 'Saldo Sistema',
            'total_arqueado' => 'Total Arqueado',
            'diferencia' => 'Diferencia',
        ];

        return [
            $this->obtenerResultados(
                $query,
                $filtros,
                $paginar
            ),
            $columnas,
            $metricas,
        ];
    }

    private function reporteRankingAgentes(
        Builder $base,
        string $operador,
        float $valor,
        array $metricas,
        array $filtros,
        bool $paginar
    ): array {
        $query = (clone $base)
            ->where('arq.diferencia', $operador, $valor)
            ->groupBy(
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio'
            )
            ->select([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
            ])
            ->selectRaw('COUNT(*) as incidencias')
            ->selectRaw(
                'SUM(ABS(arq.diferencia)) as monto_acumulado'
            )
            ->selectRaw(
                'MAX(ABS(arq.diferencia)) as mayor_incidencia'
            )
            ->orderByDesc('monto_acumulado');

        $columnas = [
            'posicion' => 'Posición',
            'agente' => 'Agente',
            'incidencias' => 'Incidencias',
            'monto_acumulado' => 'Monto Acumulado',
            'mayor_incidencia' => 'Mayor Incidencia',
        ];

        return [
            $this->obtenerResultados(
                $query,
                $filtros,
                $paginar
            ),
            $columnas,
            $metricas,
        ];
    }

    private function reporteHistorialAgente(
        Builder $base,
        array $metricas,
        array $filtros,
        bool $paginar
    ): array {
        $query = $this->seleccionarDetalle($base)
            ->orderBy('a.codigo_agente')
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id');

        $columnas = [
            'fecha_arqueo' => 'Fecha',
            'agente' => 'Agente',
            'tipo' => 'Tipo',
            'region' => 'Región',
            'ruta' => 'Ruta',
            'responsable' => 'Responsable',
            'estado' => 'Estado',
            'diferencia' => 'Diferencia',
        ];

        return [
            $this->obtenerResultados(
                $query,
                $filtros,
                $paginar
            ),
            $columnas,
            $metricas,
        ];
    }

    private function reporteHistorialPromotor(
        Builder $base,
        array $metricas,
        array $filtros,
        bool $paginar
    ): array {
        $query = $this->seleccionarDetalle($base)
            ->where('arq.tipo', 'VISITA_PROMOTOR')
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id');

        $columnas = [
            'fecha_arqueo' => 'Fecha',
            'responsable' => 'Promotor',
            'agente' => 'Agente',
            'region' => 'Región',
            'ruta' => 'Ruta',
            'estado' => 'Estado',
            'diferencia' => 'Diferencia',
        ];

        return [
            $this->obtenerResultados(
                $query,
                $filtros,
                $paginar
            ),
            $columnas,
            $metricas,
        ];
    }

    private function reporteRegion(
        Builder $base,
        string $operador,
        float $valor,
        array $metricas,
        array $filtros,
        bool $paginar
    ): array {
        $query = (clone $base)
            ->where('arq.diferencia', $operador, $valor)
            ->whereNotNull('reg.id')
            ->groupBy('reg.id', 'reg.nombre')
            ->select([
                'reg.id',
                'reg.nombre as region',
            ])
            ->selectRaw('COUNT(*) as incidencias')
            ->selectRaw(
                'SUM(ABS(arq.diferencia)) as monto_acumulado'
            )
            ->selectRaw(
                'MAX(ABS(arq.diferencia)) as mayor_incidencia'
            )
            ->orderByDesc('monto_acumulado');

        $columnas = [
            'posicion' => 'Posición',
            'region' => 'Región',
            'incidencias' => 'Incidencias',
            'monto_acumulado' => 'Monto Acumulado',
            'mayor_incidencia' => 'Mayor Incidencia',
        ];

        return [
            $this->obtenerResultados(
                $query,
                $filtros,
                $paginar
            ),
            $columnas,
            $metricas,
        ];
    }

    private function reporteRuta(
        Builder $base,
        string $operador,
        float $valor,
        array $metricas,
        array $filtros,
        bool $paginar
    ): array {
        $query = (clone $base)
            ->where('arq.diferencia', $operador, $valor)
            ->whereNotNull('r.id')
            ->groupBy(
                'r.id',
                'r.codigo',
                'r.nombre',
                'reg.nombre'
            )
            ->select([
                'r.id',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region',
            ])
            ->selectRaw('COUNT(*) as incidencias')
            ->selectRaw(
                'SUM(ABS(arq.diferencia)) as monto_acumulado'
            )
            ->selectRaw(
                'MAX(ABS(arq.diferencia)) as mayor_incidencia'
            )
            ->orderByDesc('monto_acumulado');

        $columnas = [
            'posicion' => 'Posición',
            'ruta' => 'Ruta',
            'region' => 'Región',
            'incidencias' => 'Incidencias',
            'monto_acumulado' => 'Monto Acumulado',
            'mayor_incidencia' => 'Mayor Incidencia',
        ];

        return [
            $this->obtenerResultados(
                $query,
                $filtros,
                $paginar
            ),
            $columnas,
            $metricas,
        ];
    }

    private function seleccionarDetalle(Builder $base): Builder
    {
        return (clone $base)->select([
            'arq.id',
            'arq.numero_arqueo',
            'arq.fecha_arqueo',
            'arq.tipo',
            'arq.estado',
            'arq.saldo_sistema',
            'arq.total_arqueado',
            'arq.diferencia',

            'a.id as agente_id',
            'a.codigo_agente',
            'a.nombre_negocio',

            'r.id as ruta_id',
            'r.codigo as ruta_codigo',
            'r.nombre as ruta_nombre',

            'reg.id as region_id',
            'reg.nombre as region',

            'responsable.id as responsable_id',
            'responsable.usuario as responsable_usuario',
            'dp_responsable.nombres as responsable_nombres',
            'dp_responsable.apellidos as responsable_apellidos',
        ]);
    }

    private function obtenerResultados(
        Builder $query,
        array $filtros,
        bool $paginar
    ): mixed {
        if (!$paginar) {
            return $query->get();
        }

        return $query
            ->paginate($filtros['por_pagina'])
            ->withQueryString();
    }

    private function validarAdministrador(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            !$usuario->rol
                || $usuario->rol->nombre !== 'Administrador',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
