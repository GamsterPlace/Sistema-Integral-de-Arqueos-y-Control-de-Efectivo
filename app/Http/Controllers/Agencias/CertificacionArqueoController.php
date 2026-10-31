<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\VentanillaAgencia;
use App\Models\Usuario;
use App\Models\AsignacionPersonalAgencia;
use App\Services\Agencias\FirmaArqueoService;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CertificacionArqueoController extends Controller
{
    private const BILLETES = [
        200, 100, 50, 20, 10, 5, 2, 1,
    ];

    private const MONEDAS = [
        '1.00', '0.50', '0.25', '0.10', '0.05',
    ];

    private function cargo(Request $request): string
    {
        $request->user()->loadMissing('rol');

        return match ($request->user()->rol?->nombre) {
            'JefeCaja' => 'JEFE_CAJA',
            'SubjefeCaja' => 'SUBJEFE_CAJA',
            default => abort(403),
        };
    }

    private function agenciasAutorizadas(Request $request): array
    {
        return AsignacionPersonalAgencia::query()
            ->where('usuario_id', $request->user()->id)
            ->where('cargo', $this->cargo($request))
            ->where('estado', true)
            ->whereIn('tipo', ['PERMANENTE', 'TEMPORAL'])
            ->whereDate(
                'fecha_inicio',
                '<=',
                today()->toDateString()
            )
            ->where(function ($query) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate(
                        'fecha_fin',
                        '>=',
                        today()->toDateString()
                    );
            })
            ->whereHas('agencia', function ($query) {
                $query->where('estado', true);
            })
            ->pluck('agencia_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();
    }

    private function rutaBase(Request $request): string
    {
        return $this->cargo($request) === 'JEFE_CAJA'
            ? 'jefecaja'
            : 'subjefecaja';
    }

    private function obtenerArqueo(
        Request $request,
        int $arqueo
    ): ArqueoAgencia {
        return ArqueoAgencia::query()
            ->with([
                'detalles',
                'firmas',
                'agencia',
                'ventanilla',
                'receptor',
            ])
            ->whereIn(
                'agencia_id',
                $this->agenciasAutorizadas($request)
            )
            ->where('tipo', 'ORDINARIO')
            ->findOrFail($arqueo);
    }

    public function index(Request $request): View
    {
        $agencias = $this->agenciasAutorizadas($request);

        $pendientes = ArqueoAgencia::query()
            ->with(['agencia', 'ventanilla', 'receptor'])
            ->whereIn('agencia_id', $agencias)
            ->where('tipo', 'ORDINARIO')
            ->where('estado', 'PENDIENTE')
            ->orderBy('fecha_operativa')
            ->orderBy('id')
            ->paginate(20, ['*'], 'pendientes_page')
            ->withQueryString();

        $ventanillas = VentanillaAgencia::query()
            ->with('agencia')
            ->whereIn('agencia_id', $agencias)
            ->orderBy('agencia_id')
            ->orderBy('codigo')
            ->get();

        // Se recuperan los últimos tres arqueos de cada ventanilla,
        // sin confundir el receptor histórico con el actualmente asignado.
        foreach ($ventanillas as $ventanilla) {
            $ventanilla->setRelation('ultimosArqueos', ArqueoAgencia::query()
                ->where('agencia_id', $ventanilla->agencia_id)
                ->where('ventanilla_id', $ventanilla->id)
                ->where('tipo', 'ORDINARIO')
                ->orderByDesc('fecha_operativa')
                ->orderByDesc('id')
                ->limit(3)
                ->get());

            $ventanilla->setRelation(
                'receptorAsignado',
                $this->receptorActualVentanilla($ventanilla)
            );
        }

        return view('agencias.certificaciones.index', [
            'pendientes' => $pendientes,
            'ventanillas' => $ventanillas,
            'rutaBase' => $this->rutaBase($request),
        ]);
    }

    /**
     * Resuelve el receptor asignado por una jornada de la fecha actual.
     * Si no existe jornada vigente, no atribuye la asignación a un receptor
     * basándose en arqueos antiguos.
     */
    private function receptorActualVentanilla(VentanillaAgencia $ventanilla): ?Usuario
    {
        if (!Schema::hasTable('jornadas_receptor')) {
            return null;
        }

        $tabla = 'jornadas_receptor';
        if (!Schema::hasColumn($tabla, 'ventanilla_id')
            || !Schema::hasColumn($tabla, 'fecha_operativa')) {
            return null;
        }

        $campoUsuario = null;
        foreach (['receptor_usuario_id', 'usuario_id'] as $campo) {
            if (Schema::hasColumn($tabla, $campo)) {
                $campoUsuario = $campo;
                break;
            }
        }
        if (!$campoUsuario) {
            return null;
        }

        $idUsuario = DB::table($tabla)
            ->where('ventanilla_id', $ventanilla->id)
            ->whereDate('fecha_operativa', today()->toDateString())
            ->orderByDesc('id')
            ->value($campoUsuario);

        return $idUsuario ? Usuario::query()->find($idUsuario) : null;
    }

    public function historial(Request $request, int $ventanilla): View
    {
        $datos = $request->validate([
            'fecha' => ['nullable', 'date'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $registroVentanilla = VentanillaAgencia::query()
            ->with('agencia')
            ->whereIn('agencia_id', $this->agenciasAutorizadas($request))
            ->findOrFail($ventanilla);

        $consulta = ArqueoAgencia::query()
            ->where('agencia_id', $registroVentanilla->agencia_id)
            ->where('ventanilla_id', $registroVentanilla->id)
            ->where('tipo', 'ORDINARIO');

        if (!empty($datos['fecha'])) {
            $consulta->whereDate('fecha_operativa', $datos['fecha']);
        } else {
            if (!empty($datos['desde'])) {
                $consulta->whereDate('fecha_operativa', '>=', $datos['desde']);
            }
            if (!empty($datos['hasta'])) {
                $consulta->whereDate('fecha_operativa', '<=', $datos['hasta']);
            }
        }

        $arqueos = $consulta->orderByDesc('fecha_operativa')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('agencias.certificaciones.historial', [
            'ventanilla' => $registroVentanilla,
            'arqueos' => $arqueos,
            'rutaBase' => $this->rutaBase($request),
        ]);
    }

    public function show(
        Request $request,
        int $arqueo,
        FirmaArqueoService $firmas
    ): View {
        $registro = $this->obtenerArqueo(
            $request,
            $arqueo
        );

        $firmaReceptor = $registro->firmas
            ->firstWhere('tipo_firma', 'RECEPTOR');

        $firmaCertificador = $registro->firmas
            ->firstWhere('tipo_firma', 'CERTIFICADOR');

        $firmaReceptorValida = $firmaReceptor !== null
            && $firmas->verificar(
                $registro,
                $firmaReceptor
            );

        $firmaCertificadorValida = $firmaCertificador !== null
            && $firmas->verificar(
                $registro,
                $firmaCertificador
            );

        return view('agencias.certificaciones.show', [
            'arqueo' => $registro,
            'firmaReceptorValida' => $firmaReceptorValida,
            'firmaCertificadorValida' => $firmaCertificadorValida,
            'rutaBase' => $this->rutaBase($request),
        ]);
    }

    public function imprimir(
        Request $request,
        int $arqueo,
        FirmaArqueoService $firmas
    ) {
        $registro = $this->obtenerArqueo(
            $request,
            $arqueo
        );

        if (!class_exists(Pdf::class)) {
            abort(
                503,
                'El generador de documentos PDF no está disponible.'
            );
        }

        $firmaReceptor = $registro->firmas
            ->firstWhere('tipo_firma', 'RECEPTOR');

        $firmaCertificador = $registro->firmas
            ->firstWhere('tipo_firma', 'CERTIFICADOR');

        $verificaciones = [
            'RECEPTOR' => $firmaReceptor !== null
                && $firmas->verificar(
                    $registro,
                    $firmaReceptor
                ),

            'CERTIFICADOR' => $firmaCertificador !== null
                && $firmas->verificar(
                    $registro,
                    $firmaCertificador
                ),
        ];

        $logoData = null;

        $logoPath = public_path(
            'images/logos/ecosaba.png'
        );

        if (
            is_file($logoPath)
            && is_readable($logoPath)
        ) {
            $contenido = file_get_contents($logoPath);

            if ($contenido !== false) {
                $logoData = 'data:image/png;base64,'
                    .base64_encode($contenido);
            }
        }

        $nombreSeguro = preg_replace(
            '/[^a-zA-Z0-9_-]/',
            '-',
            (string) $registro->numero_arqueo
        );

        $nombreArchivo = 'arqueo-'
            .($nombreSeguro ?: $registro->id)
            .'.pdf';

        return Pdf::loadView(
            'receptor.arqueos.imprimir',
            [
                'registro' => $registro,
                'logoData' => $logoData,
                'verificaciones' => $verificaciones,
                'billetes' => self::BILLETES,
                'monedas' => self::MONEDAS,
            ]
        )
            ->setPaper('letter', 'portrait')
            ->stream($nombreArchivo);
    }

    public function store(
        Request $request,
        int $arqueo,
        FirmaArqueoService $firmas,
        AuditoriaService $auditoria
    ) {
        $request->validate([
            'confirmar_certificacion' => [
                'accepted',
            ],
        ]);

        $agencias = $this->agenciasAutorizadas(
            $request
        );

        $cargo = $this->cargo($request);

        $ruta = $this->rutaBase($request);

        DB::transaction(function () use (
            $request,
            $arqueo,
            $firmas,
            $auditoria,
            $agencias,
            $cargo
        ) {
            $registro = ArqueoAgencia::query()
                ->whereIn('agencia_id', $agencias)
                ->where('tipo', 'ORDINARIO')
                ->lockForUpdate()
                ->findOrFail($arqueo);

            if (
                $registro->estado !== 'PENDIENTE'
                || $registro->certificado_at !== null
                || $registro->anulado_at !== null
            ) {
                throw ValidationException::withMessages([
                    'arqueo' =>
                        'El arqueo ya no está pendiente de certificación.',
                ]);
            }

            $registro->load([
                'firmas',
                'detalles',
            ]);

            $firmaReceptor = $registro->firmas
                ->firstWhere(
                    'tipo_firma',
                    'RECEPTOR'
                );

            if (
                !$firmaReceptor
                || (int) $firmaReceptor->usuario_id
                    !== (int) $registro->receptor_usuario_id
                || !$firmas->verificar(
                    $registro,
                    $firmaReceptor
                )
                || !$registro->firmado_receptor_at
            ) {
                throw ValidationException::withMessages([
                    'arqueo' =>
                        'La firma del receptor no es válida o el arqueo fue modificado.',
                ]);
            }

            if (
                $registro->firmas->contains(
                    'tipo_firma',
                    'CERTIFICADOR'
                )
            ) {
                throw ValidationException::withMessages([
                    'arqueo' =>
                        'Este arqueo ya tiene una firma de certificación.',
                ]);
            }

            if (
                abs((float) $registro->diferencia) > 0.00001
                && trim(
                    (string) $registro->justificacion_diferencia
                ) === ''
            ) {
                throw ValidationException::withMessages([
                    'arqueo' =>
                        'El arqueo presenta una diferencia sin justificación.',
                ]);
            }

            $firmas->firmarCertificador(
                $registro,
                $request->user()
            );

            $registro->estado = 'CERTIFICADO';

            $registro->certificado_at = now();

            if ($cargo === 'JEFE_CAJA') {
                $registro->jefe_caja_responsable_id =
                    $request->user()->id;
            } else {
                $registro->subjefe_caja_responsable_id =
                    $request->user()->id;
            }

            $registro->save();

            $auditoria->registrar(
                $request->user(),
                'ARQUEOS_AGENCIA',
                'CERTIFICAR_ARQUEO_ORDINARIO',
                'arqueos_agencia',
                $registro->id,
                'Certificación del arqueo ordinario por responsable de caja.',
                [
                    'estado' => 'PENDIENTE',
                ],
                [
                    'estado' => 'CERTIFICADO',
                    'numero_arqueo' => $registro->numero_arqueo,
                    'diferencia' => $registro->diferencia,
                    'certificador_id' => $request->user()->id,
                ]
            );
        }, 3);

        return redirect()
            ->route(
                $ruta.'.certificaciones.show',
                $arqueo
            )
            ->with(
                'success',
                'Arqueo certificado correctamente.'
            );
    }
}
