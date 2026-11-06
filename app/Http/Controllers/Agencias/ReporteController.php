<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\Agencias\ReporteriaAgenciasService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

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

    public function index(Request $request): View
    {
        $usuario = $this->usuarioAutorizado($request);
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
            'layoutReportes' => $this->resolverLayout($usuario),
        ]);
    }

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
            'catalogos' => $this->reporteria->catalogos(
                $usuario,
                $filtros
            ),
        ])->setPaper('letter', 'landscape');

        return $pdf->stream(
            $this->nombreArchivo($filtros, 'pdf')
        );
    }

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
                    throw new RuntimeException(
                        'No fue posible generar el archivo CSV.'
                    );
                }

                fwrite($salida, "\xEF\xBB\xBF");

                fputcsv(
                    $salida,
                    array_values($datos['columnas']),
                    ';'
                );

                foreach ($datos['resultados'] as $indice => $fila) {
                    $valores = [];

                    foreach ($datos['columnas'] as $campo => $titulo) {
                        $valor = $campo === 'posicion'
                            ? $indice + 1
                            : data_get($fila, $campo, '');

                        $valores[] = $this->valorCsv($valor);
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

    public function excel(Request $request): Response
    {
        $usuario = $this->usuarioAutorizado($request);
        $filtros = $this->reporteria->filtros($request);

        $datos = $this->reporteria->generar(
            $usuario,
            $filtros,
            false
        );

        abort_unless(
            class_exists(ZipArchive::class),
            500,
            'La extensión PHP ZIP es necesaria para exportar Excel.'
        );

        $archivo = tempnam(
            sys_get_temp_dir(),
            'arqueos_agencias_'
        );

        if ($archivo === false) {
            throw new RuntimeException(
                'No fue posible crear el archivo temporal Excel.'
            );
        }

        $zip = new ZipArchive();

        if ($zip->open($archivo, ZipArchive::OVERWRITE) !== true) {
            @unlink($archivo);

            throw new RuntimeException(
                'No fue posible preparar el archivo Excel.'
            );
        }

        try {
            $zip->addFromString(
                '[Content_Types].xml',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '</Types>'
            );

            $zip->addFromString(
                '_rels/.rels',
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>'
            );

            $zip->addFromString(
                'xl/workbook.xml',
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
                . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="Reporte de agencias" sheetId="1" r:id="rId1"/></sheets>'
                . '</workbook>'
            );

            $zip->addFromString(
                'xl/_rels/workbook.xml.rels',
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '</Relationships>'
            );

            $xml = fopen('php://temp/maxmemory:5242880', 'w+');

            if ($xml === false) {
                throw new RuntimeException(
                    'No fue posible generar la hoja Excel.'
                );
            }

            fwrite(
                $xml,
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<sheetData>'
            );

            $numeroFila = 1;

            $escribirFila = function (array $valores) use (
                $xml,
                &$numeroFila
            ): void {
                fwrite($xml, '<row r="' . $numeroFila . '">');

                foreach (array_values($valores) as $indice => $valor) {
                    $numero = $indice + 1;
                    $columna = '';

                    while ($numero > 0) {
                        $numero--;

                        $columna = chr(
                            65 + ($numero % 26)
                        ) . $columna;

                        $numero = intdiv($numero, 26);
                    }

                    $referencia = $columna . $numeroFila;

                    $texto = (string) ($valor ?? '');

                    $texto = preg_replace(
                        '/[\x00-\x08\x0B\x0C\x0E-\x1F]/u',
                        '',
                        $texto
                    ) ?? '';

                    // Impide que los textos controlados por usuarios
                    // se interpreten como fórmulas al abrir el XLSX.
                    $texto = $this->valorCsv($texto);

                    $texto = htmlspecialchars(
                        $texto,
                        ENT_XML1 | ENT_QUOTES,
                        'UTF-8'
                    );

                    fwrite(
                        $xml,
                        '<c r="' . $referencia . '" t="inlineStr">'
                        . '<is><t xml:space="preserve">'
                        . $texto
                        . '</t></is></c>'
                    );
                }

                fwrite($xml, '</row>');

                $numeroFila++;
            };

            $escribirFila(
                array_values($datos['columnas'])
            );

            foreach ($datos['resultados'] as $indice => $fila) {
                $valores = [];

                foreach ($datos['columnas'] as $campo => $titulo) {
                    $valores[] = $campo === 'posicion'
                        ? $indice + 1
                        : data_get($fila, $campo, '');
                }

                $escribirFila($valores);
            }

            fwrite($xml, '</sheetData></worksheet>');

            rewind($xml);

            $contenido = stream_get_contents($xml);

            fclose($xml);

            if (
                $contenido === false
                || !$zip->addFromString(
                    'xl/worksheets/sheet1.xml',
                    $contenido
                )
            ) {
                throw new RuntimeException(
                    'No fue posible escribir la hoja Excel.'
                );
            }
        } catch (\Throwable $e) {
            $zip->close();
            @unlink($archivo);

            throw $e;
        }

        if (!$zip->close()) {
            @unlink($archivo);

            throw new RuntimeException(
                'No fue posible finalizar el archivo Excel.'
            );
        }

        return response()->download(
            $archivo,
            $this->nombreArchivo($filtros, 'xlsx'),
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }

    private function usuarioAutorizado(Request $request): Usuario
    {
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
            && (bool) $usuario->rol->estado
            && in_array(
                $usuario->rol->nombre,
                self::ROLES,
                true
            ),
            403,
            'No tiene autorización para consultar reportes de agencias.'
        );

        return $usuario;
    }

    private function resolverLayout(Usuario $usuario): string
    {
        $rol = $usuario->rol?->nombre;

        $layout = self::LAYOUTS[$rol] ?? null;

        abort_unless(
            $layout !== null,
            403,
            'Su rol no tiene un layout autorizado.'
        );

        abort_unless(
            view()->exists($layout),
            500,
            'No se encontró el layout institucional: ' . $layout
        );

        return $layout;
    }

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

    private function valorCsv(mixed $valor): string
    {
        $texto = (string) ($valor ?? '');

        if (preg_match('/^\s*[=+\-@\t\r\n]/u', $texto)) {
            return "'" . $texto;
        }

        return $texto;
    }
}
