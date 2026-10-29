<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\AsignacionPersonalAgencia;
use App\Services\Agencias\FirmaArqueoService;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $arqueos = ArqueoAgencia::query()
            ->with([
                'agencia',
                'ventanilla',
                'receptor',
            ])
            ->whereIn('agencia_id', $agencias)
            ->where('tipo', 'ORDINARIO')
            ->whereIn('estado', [
                'PENDIENTE',
                'CERTIFICADO',
            ])
            ->orderByRaw(
                "CASE WHEN estado = 'PENDIENTE' THEN 0 ELSE 1 END"
            )
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('agencias.certificaciones.index', [
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
