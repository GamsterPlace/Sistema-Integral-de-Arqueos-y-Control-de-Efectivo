<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\Agencias\ReporteriaAgenciasService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    private const ROLES = [
        'JefeCaja',
        'SubjefeCaja',
        'JefeAgencia',
        'Auditoria',
        'Gerencia',
        'Administrador',
    ];

    /**
     * Layout correspondiente a cada rol autorizado.
     *
     * Los nombres deben coincidir con los archivos
     * existentes en resources/views/layouts/.
     */
    private const LAYOUTS = [
        'JefeCaja' => 'layouts.jefe-caja',
        'SubjefeCaja' => 'layouts.subjefe-caja',
        'JefeAgencia' => 'layouts.jefe-agencia',
        'Auditoria' => 'layouts.auditoria',
        'Gerencia' => 'layouts.gerencia',
        'Administrador' => 'layouts.administrador',
    ];

    public function __construct(
        private readonly ReporteriaAgenciasService $reporteria
    ) {
    }

    /**
     * Centro de Reportes.
     *
     * Vista compartida para todos los roles autorizados,
     * con layout institucional según el usuario autenticado.
     */
    public function index(Request $request): View
    {
        $usuario = $this->usuarioAutorizado($request);

        $layoutReportes = $this->resolverLayoutReportes($usuario);

        $filtros = $this->reporteria->filtros($request);

        $datos = $this->reporteria->generar(
            $usuario,
            $filtros,
            true
        );

        return view('agencias.reportes.index', [
            ...$datos,
            ...$filtros,

            'filtros' => $filtros,

            'tiposReporte' => $this->reporteria->tiposReporte(),

            'catalogos' => $this->reporteria->catalogos(
                $usuario,
                $filtros
            ),

            // Layout correspondiente al rol autenticado.
            'layoutReportes' => $layoutReportes,
        ]);
    }

    /**
     * Exportar el reporte a PDF.
     *
     * Se conserva la vista independiente para impresión,
     * sin utilizar los layouts del sistema.
     */
    public function imprimir(Request $request): Response
    {
        $usuario = $this->usuarioAutorizado($request);

        $filtros = $this->reporteria->filtros($request);

        $datos = $this->reporteria->generar(
            $usuario,
            $filtros,
            false
        );

        $pdf = Pdf::loadView('agencias.reportes.pdf', [
            ...$datos,

            'titulo' => $this->reporteria
                ->tiposReporte()[$filtros['reporte']],

            'filtros' => $filtros,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream(
            $this->nombreArchivo($filtros, 'pdf')
        );
    }

    /**
     * Exportar el reporte a CSV.
     *
     * Se conserva la codificación UTF-8 con BOM
     * y el separador de punto y coma.
     */
    public function csv(Request $request): StreamedResponse
    {
        $usuario = $this->usuarioAutorizado($request);

        $filtros = $this->reporteria->filtros($request);

        $datos = $this->reporteria->generar(
            $usuario,
            $filtros,
            false
        );

        return response()->streamDownload(
            function () use ($datos): void {
                $salida = fopen('php://output', 'w');

                if ($salida === false) {
                    return;
                }

                // BOM para compatibilidad con Excel.
                fwrite($salida, "\xEF\xBB\xBF");

                fputcsv(
                    $salida,
                    array_values($datos['columnas']),
                    ';'
                );

                foreach ($datos['resultados'] as $indice => $fila) {
                    $valores = [];

                    foreach ($datos['columnas'] as $campo => $titulo) {
                        if ($campo === 'posicion') {
                            $valores[] = $indice + 1;
                            continue;
                        }

                        $valores[] = data_get(
                            $fila,
                            $campo,
                            ''
                        );
                    }

                    fputcsv($salida, $valores, ';');
                }

                fclose($salida);
            },
            $this->nombreArchivo($filtros, 'csv'),
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }

    /**
     * Verificar que el usuario tenga autorización
     * para acceder al Centro de Reportes.
     */
    private function usuarioAutorizado(Request $request): Usuario
    {
        /** @var Usuario|null $usuario */
        $usuario = $request->user();

        abort_unless(
            $usuario instanceof Usuario,
            401,
            'Debe iniciar sesión.'
        );

        $usuario->loadMissing('rol');

        abort_unless(
            $usuario->estado === 'ACTIVO'
                && $usuario->rol
                && $usuario->rol->estado
                && in_array(
                    $usuario->rol->nombre,
                    self::ROLES,
                    true
                ),
            403,
            'No tiene autorización para consultar los reportes de agencias.'
        );

        return $usuario;
    }

    /**
     * Determinar el layout institucional
     * correspondiente al rol autenticado.
     */
    private function resolverLayoutReportes(Usuario $usuario): string
    {
        $nombreRol = $usuario->rol?->nombre;

        abort_unless(
            is_string($nombreRol)
                && array_key_exists($nombreRol, self::LAYOUTS),
            403,
            'Su rol no tiene un layout autorizado para reportes.'
        );

        $layout = self::LAYOUTS[$nombreRol];

        abort_unless(
            view()->exists($layout),
            500,
            'No se encontró el layout institucional: ' . $layout
        );

        return $layout;
    }

    /**
     * Generar nombre del archivo exportado.
     */
    private function nombreArchivo(
        array $filtros,
        string $extension
    ): string {
        return 'reporte-agencias-'
            . $filtros['reporte']
            . '-'
            . now()->format('Ymd-His')
            . '.'
            . $extension;
    }
}
