<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Agencia;
use App\Models\ArqueoAgencia;
use App\Models\AsignacionPersonalAgencia;
use App\Models\Usuario;
use App\Services\Agencias\FirmaArqueoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultaArqueosAgenciaController extends Controller
{
    private function autorizar(Request $request): void
    {
        $usuario = $request->user();

        abort_unless($usuario, 401);

        $usuario->loadMissing('rol');

        abort_unless(
            $usuario->estado === 'ACTIVO'
            && $usuario->rol?->nombre === 'Gerencia'
            && (bool) $usuario->rol?->estado,
            403
        );
    }

    public function historial(Request $request): View
    {
        $this->autorizar($request);

        $filtros = $this->filtros($request);
        $query = $this->consulta($filtros);

        $agencias = Agencia::query()
            ->orderBy('nombre')
            ->get();

        $estados = ArqueoAgencia::query()
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');

        $arqueos = $query
            ->with(['agencia', 'ventanilla', 'receptor'])
            ->orderByDesc('fecha_operativa')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $titulo = 'Historial institucional de arqueos de agencias';

        return view(
            'gerencia.agencias.arqueos.index',
            compact(
                'arqueos',
                'agencias',
                'filtros',
                'titulo',
                'estados'
            )
        );
    }

    public function receptores(Request $request): View
    {
        $this->autorizar($request);

        $datos = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);

        $busqueda = trim((string) ($datos['buscar'] ?? ''));

        // Incluye receptores con asignaciones históricas.
        $ids = AsignacionPersonalAgencia::query()
            ->where('cargo', 'RECEPTOR')
            ->select('usuario_id')
            ->union(
                ArqueoAgencia::query()
                    ->whereNotNull('receptor_usuario_id')
                    ->select('receptor_usuario_id')
            );

        $query = Usuario::query()
            ->with('datosPersonales')
            ->whereIn('id', $ids);

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where(
                    'usuario',
                    'like',
                    '%'.$busqueda.'%'
                );
            });
        }

        $receptores = $query
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        $ultimos = ArqueoAgencia::query()
            ->with('agencia')
            ->whereIn(
                'receptor_usuario_id',
                $receptores->getCollection()->pluck('id')
            )
            ->orderByDesc('fecha_operativa')
            ->orderByDesc('id')
            ->get()
            ->groupBy('receptor_usuario_id')
            ->map(fn ($items) => $items->take(3));

        return view(
            'gerencia.agencias.arqueos.historial',
            compact('receptores', 'ultimos', 'busqueda')
        );
    }

    public function receptor(Request $request, int $receptor): View
    {
        $this->autorizar($request);

        $usuarioReceptor = Usuario::query()
            ->with('datosPersonales')
            ->findOrFail($receptor);

        $existe = AsignacionPersonalAgencia::query()
            ->where('usuario_id', $receptor)
            ->where('cargo', 'RECEPTOR')
            ->exists()
            ||
            ArqueoAgencia::query()
                ->where('receptor_usuario_id', $receptor)
                ->exists();

        abort_unless($existe, 404);

        $arqueos = ArqueoAgencia::query()
            ->where('receptor_usuario_id', $receptor)
            ->with(['agencia', 'ventanilla'])
            ->orderByDesc('fecha_operativa')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'gerencia.agencias.arqueos.receptor',
            compact('arqueos', 'usuarioReceptor')
        );
    }

    public function show(Request $request, int $arqueo): View
    {
        $this->autorizar($request);

        $registro = ArqueoAgencia::query()
            ->with([
                'agencia',
                'ventanilla',
                'receptor',
                'receptorEntrante',
                'jornada.turno',
                'detalles',
                'firmas',
                'anulacion',
                'observacionesAuditoria',
            ])
            ->findOrFail($arqueo);

        return view(
            'gerencia.agencias.arqueos.show',
            ['arqueo' => $registro]
        );
    }

    /**
     * Impresión institucional de arqueos de agencias.
     *
     * Aplica a:
     * - Ordinarios
     * - Sorpresivos
     * - Auditoría
     * - Entrega de efectivo
     *
     * Gerencia únicamente consulta e imprime.
     */
    public function imprimir(
        Request $request,
        FirmaArqueoService $firmas,
        int $arqueo
    ) {
        $this->autorizar($request);

        // Obtener arqueo y sus relaciones.
        $registro = ArqueoAgencia::query()
            ->with([
                'agencia',
                'ventanilla',
                'receptor',
                'receptorEntrante',
                'jornada.turno',
                'detalles',
                'firmas',
                'anulacion',
                'observacionesAuditoria',
            ])
            ->findOrFail($arqueo);

        /*
        |--------------------------------------------------------------------------
        | LOGOTIPO INSTITUCIONAL
        |--------------------------------------------------------------------------
        */

// Logotipo institucional ECOSABA.
// Se utiliza una ruta local para evitar errores
// de interpretación de imágenes Base64 en Dompdf.

        $logoData = null;

        $logoPath = public_path('images/logos/ecosaba.png');

        if (is_file($logoPath) && is_readable($logoPath)) {
            $imagen = @getimagesize($logoPath);

            if (
                $imagen !== false
                && in_array(
                    $imagen['mime'] ?? '',
                    ['image/png', 'image/jpeg'],
                    true
                )
            ) {
                $logoData = $logoPath;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFICACIÓN DE FIRMAS ELECTRÓNICAS
        |--------------------------------------------------------------------------
        */

        $verificaciones = [];

        foreach ($registro->firmas as $firma) {
            $verificaciones[$firma->id] = $firmas->verificar(
                $registro,
                $firma
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IDENTIFICACIÓN DE FIRMANTES
        |--------------------------------------------------------------------------
        */

        $firmaReceptor = $registro->firmas
            ->firstWhere('tipo_firma', 'RECEPTOR');

        $firmaCertificador = $registro->firmas
            ->firstWhere('tipo_firma', 'CERTIFICADOR');

        $firmaEntrante = $registro->firmas
            ->firstWhere('tipo_firma', 'ENTRANTE');

        $firmaAuditor = $registro->firmas
            ->firstWhere('tipo_firma', 'AUDITOR');

        /*
        |--------------------------------------------------------------------------
        | RESULTADO DE VERIFICACIÓN DEL RECEPTOR
        |--------------------------------------------------------------------------
        |
        | true  = firma verificada correctamente.
        | false = la comprobación falló.
        | null  = no existe firma del receptor.
        |
        */

        $firmaValida = $firmaReceptor
            ? ($verificaciones[$firmaReceptor->id] ?? null)
            : null;

        /*
        |--------------------------------------------------------------------------
        | DATOS PARA LA VISTA PDF
        |--------------------------------------------------------------------------
        */

        $datos = [
            'arqueo' => $registro,
            'registro' => $registro,

            'detalles' => $registro->detalles,

            'logoData' => $logoData,

            'verificaciones' => $verificaciones,

            'firmaValida' => $firmaValida,

            'billetes' => [
                200,
                100,
                50,
                20,
                10,
                5,
                2,
                1,
            ],

            'monedas' => [
                '1.00',
                '0.50',
                '0.25',
                '0.10',
                '0.05',
            ],

            'firmaReceptor' => $firmaReceptor,

            'firmaCertificador' => $firmaCertificador,

            'firmaEntrante' => $firmaEntrante,

            'firmaAuditor' => $firmaAuditor,
        ];

        /*
        |--------------------------------------------------------------------------
        | PLANTILLA DE IMPRESIÓN DE GERENCIA
        |--------------------------------------------------------------------------
        |
        | Todos los tipos de arqueo utilizan la misma
        | plantilla institucional de Gerencia.
        |
        | No se modifica la plantilla del Receptor.
        |
        */

        $vista = 'gerencia.agencias.arqueos.imprimir';

        /*
        |--------------------------------------------------------------------------
        | NOMBRE DEL DOCUMENTO
        |--------------------------------------------------------------------------
        */

        $nombre = preg_replace(
            '/[^a-zA-Z0-9_-]/',
            '-',
            (string) $registro->numero_arqueo
        );

        /*
        |--------------------------------------------------------------------------
        | GENERAR PDF
        |--------------------------------------------------------------------------
        */

        return Pdf::loadView($vista, $datos)
            ->setPaper('letter', 'portrait')
            ->stream(
                'arqueo-'.($nombre ?: $registro->id).'.pdf'
            );
    }

    private function filtros(Request $request): array
    {
        return $request->validate([
            'agencia_id' => [
                'nullable',
                'integer',
                'exists:agencias,id',
            ],

            'receptor_id' => [
                'nullable',
                'integer',
                'exists:usuarios,id',
            ],

            'desde' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'hasta' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:desde',
            ],

            'tipo' => [
                'nullable',
                'in:ORDINARIO,SORPRESIVO,AUDITORIA,ENTREGA',
            ],

            'estado' => [
                'nullable',
                'string',
                'max:35',
            ],

            'numero_arqueo' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);
    }

    private function consulta(array $filtros)
    {
        $query = ArqueoAgencia::query();

        foreach (
            ['agencia_id', 'receptor_id', 'tipo', 'estado']
            as $campo
        ) {
            if (!empty($filtros[$campo])) {
                $columna = $campo === 'receptor_id'
                    ? 'receptor_usuario_id'
                    : $campo;

                $query->where(
                    $columna,
                    $filtros[$campo]
                );
            }
        }

        if (!empty($filtros['desde'])) {
            $query->whereDate(
                'fecha_operativa',
                '>=',
                $filtros['desde']
            );
        }

        if (!empty($filtros['hasta'])) {
            $query->whereDate(
                'fecha_operativa',
                '<=',
                $filtros['hasta']
            );
        }

        if (!empty($filtros['numero_arqueo'])) {
            $query->where(
                'numero_arqueo',
                'like',
                '%'.addcslashes(
                    $filtros['numero_arqueo'],
                    '%_\\'
                ).'%'
            );
        }

        return $query;
    }
}
