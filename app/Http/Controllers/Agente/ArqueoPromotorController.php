<?php

namespace App\Http\Controllers\Agente;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\FirmaArqueo;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ArqueoPromotorController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'agente',
        ]);

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        $arqueos = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->latest('fecha_arqueo')
            ->latest('id')
            ->paginate(10);

        $totalArqueos = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $pendientesFirma = Arqueo::query()
            ->where('arqueos.agente_id', $agente->id)
            ->where('arqueos.tipo', 'VISITA_PROMOTOR')
            ->where('arqueos.estado', 'PENDIENTE_CERTIFICACION')
            ->whereNotExists(function ($query) use ($usuario): void {
                $query
                    ->selectRaw('1')
                    ->from('firmas_arqueos')
                    ->whereColumn(
                        'firmas_arqueos.arqueo_id',
                        'arqueos.id'
                    )
                    ->where(
                        'firmas_arqueos.usuario_id',
                        $usuario->id
                    )
                    ->where(
                        'firmas_arqueos.tipo_firma',
                        'VALIDADOR'
                    )
                    ->where(
                        'firmas_arqueos.valida',
                        true
                    );
            })
            ->count();

        $firmados = FirmaArqueo::query()
            ->where('usuario_id', $usuario->id)
            ->where('tipo_firma', 'VALIDADOR')
            ->where('valida', true)
            ->whereHas('arqueo', function ($query) use ($agente): void {
                $query
                    ->where('agente_id', $agente->id)
                    ->where('tipo', 'VISITA_PROMOTOR');
            })
            ->count();

        return view('agente.arqueos-promotor.index', [
            'agente' => $agente,
            'arqueos' => $arqueos,
            'totalArqueos' => $totalArqueos,
            'pendientesFirma' => $pendientesFirma,
            'firmados' => $firmados,
        ]);
    }

    public function show(
        Request $request,
        Arqueo $arqueo
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'agente',
        ]);

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        $this->validarArqueoPromotor(
            $arqueo,
            $agente->id
        );

        $arqueo->loadMissing([
            'detalles',
            'firmas',
        ]);

        $firmaValidador = $arqueo->firmas
            ->first(function (FirmaArqueo $firma) use ($usuario): bool {
                return $firma->tipo_firma === 'VALIDADOR'
                    && (int) $firma->usuario_id === (int) $usuario->id
                    && $firma->valida;
            });

        return view('agente.arqueos-promotor.show', [
            'arqueo' => $arqueo,
            'billetes' => $arqueo->detalles
                ->where('tipo', 'BILLETE')
                ->sortByDesc('denominacion'),
            'monedas' => $arqueo->detalles
                ->where('tipo', 'MONEDA')
                ->sortByDesc('denominacion'),
            'firmaValidador' => $firmaValidador,
        ]);
    }


    public function imprimir(
        Request $request,
        Arqueo $arqueo
    ): Response {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'agente',
        ]);

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        $this->validarArqueoPromotor(
            $arqueo,
            $agente->id
        );

        $arqueo->loadMissing([
            'detalles',
            'firmas',
        ]);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        $pdf = Pdf::loadView(
            'agente.arqueos-promotor.pdf',
            [
                'arqueo' => $arqueo,
                'billetes' => $billetes,
                'monedas' => $monedas,
            ]
        )->setPaper('letter', 'portrait');

        return $pdf->stream(
            $arqueo->numero_arqueo . '.pdf'
        );
    }

    public function firmar(
        Request $request,
        Arqueo $arqueo
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'agente',
            'datosPersonales',
            'rol',
        ]);

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        abort_if(
            ! $usuario->datosPersonales,
            422,
            'El usuario no tiene datos personales registrados.'
        );

        abort_if(
            ! $usuario->rol,
            422,
            'El usuario no tiene un rol asignado.'
        );

        $this->validarArqueoPromotor(
            $arqueo,
            $agente->id
        );

        if ($arqueo->estado !== 'PENDIENTE_CERTIFICACION') {
            return back()->withErrors([
                'firma' => 'El arqueo no se encuentra pendiente de firma.',
            ]);
        }

        $yaFirmado = FirmaArqueo::query()
            ->where('arqueo_id', $arqueo->id)
            ->where('usuario_id', $usuario->id)
            ->where('tipo_firma', 'VALIDADOR')
            ->where('valida', true)
            ->exists();

        if ($yaFirmado) {
            return back()->with(
                'warning',
                'Este arqueo ya fue firmado electrónicamente.'
            );
        }

        try {
            DB::transaction(function () use (
                $arqueo,
                $usuario
            ): void {
                $arqueoBloqueado = Arqueo::query()
                    ->whereKey($arqueo->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $arqueoBloqueado->estado
                    !== 'PENDIENTE_CERTIFICACION'
                ) {
                    throw ValidationException::withMessages([
                        'firma' => 'El arqueo ya no está disponible para firma.',
                    ]);
                }

                $firmaExistente = FirmaArqueo::query()
                    ->where('arqueo_id', $arqueoBloqueado->id)
                    ->where('tipo_firma', 'VALIDADOR')
                    ->where('usuario_id', $usuario->id)
                    ->where('valida', true)
                    ->lockForUpdate()
                    ->exists();

                if ($firmaExistente) {
                    throw ValidationException::withMessages([
                        'firma' => 'El arqueo ya fue firmado electrónicamente.',
                    ]);
                }

                $detalle = DB::table('arqueo_detalles')
                    ->where('arqueo_id', $arqueoBloqueado->id)
                    ->orderBy('tipo')
                    ->orderByDesc('denominacion')
                    ->get()
                    ->map(function ($item): array {
                        return [
                            'tipo' => $item->tipo,
                            'denominacion' => number_format(
                                (float) $item->denominacion,
                                2,
                                '.',
                                ''
                            ),
                            'cantidad' => (int) $item->cantidad,
                            'subtotal' => number_format(
                                (float) $item->subtotal,
                                2,
                                '.',
                                ''
                            ),
                        ];
                    })
                    ->values()
                    ->all();

                $documento = [
                    'numero_arqueo' => $arqueoBloqueado->numero_arqueo,
                    'agente_id' => (int) $arqueoBloqueado->agente_id,
                    'creado_por' => (int) $arqueoBloqueado->creado_por,
                    'tipo' => $arqueoBloqueado->tipo,
                    'estado' => $arqueoBloqueado->estado,
                    'fecha_arqueo' => $arqueoBloqueado
                        ->fecha_arqueo
                        ->format('Y-m-d'),
                    'hora_inicio' => $arqueoBloqueado
                        ->hora_inicio
                        ->format('Y-m-d H:i:s'),
                    'hora_fin' => $arqueoBloqueado
                        ->hora_fin
                        ?->format('Y-m-d H:i:s'),
                    'codigo_agente_historico' =>
                        $arqueoBloqueado->codigo_agente_historico,
                    'nombre_negocio_historico' =>
                        $arqueoBloqueado->nombre_negocio_historico,
                    'nombre_propietario_historico' =>
                        $arqueoBloqueado->nombre_propietario_historico,
                    'direccion_historica' =>
                        $arqueoBloqueado->direccion_historica,
                    'ruta_historica' =>
                        $arqueoBloqueado->ruta_historica,
                    'region_historica' =>
                        $arqueoBloqueado->region_historica,
                    'total_billetes' => number_format(
                        (float) $arqueoBloqueado->total_billetes,
                        2,
                        '.',
                        ''
                    ),
                    'total_monedas' => number_format(
                        (float) $arqueoBloqueado->total_monedas,
                        2,
                        '.',
                        ''
                    ),
                    'total_arqueado' => number_format(
                        (float) $arqueoBloqueado->total_arqueado,
                        2,
                        '.',
                        ''
                    ),
                    'saldo_sistema' => number_format(
                        (float) $arqueoBloqueado->saldo_sistema,
                        2,
                        '.',
                        ''
                    ),
                    'diferencia' => number_format(
                        (float) $arqueoBloqueado->diferencia,
                        2,
                        '.',
                        ''
                    ),
                    'certificacion' => trim(
                        (string) $arqueoBloqueado->certificacion
                    ),
                    'observaciones' =>
                        $arqueoBloqueado->observaciones,
                    'detalle' => $detalle,
                ];

                $documentoJson = json_encode(
                    $documento,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_PRESERVE_ZERO_FRACTION
                    | JSON_THROW_ON_ERROR
                );

                $hashDocumento = hash(
                    'sha256',
                    $documentoJson
                );

                $fechaFirma = now();

                $contenidoFirma = implode('|', [
                    $hashDocumento,
                    (string) $usuario->id,
                    $usuario->rol->nombre,
                    'VALIDADOR',
                    $fechaFirma->format('Y-m-d H:i:s.u'),
                    '1',
                ]);

                $claveFirma = (string) config('app.key');

                if (str_starts_with($claveFirma, 'base64:')) {
                    $claveDecodificada = base64_decode(
                        substr($claveFirma, 7),
                        true
                    );

                    if ($claveDecodificada !== false) {
                        $claveFirma = $claveDecodificada;
                    }
                }

                $firmaElectronica = hash_hmac(
                    'sha256',
                    $contenidoFirma,
                    $claveFirma
                );

                $firmaArqueo = FirmaArqueo::create([
                    'arqueo_id' => $arqueoBloqueado->id,
                    'usuario_id' => $usuario->id,
                    'tipo_firma' => 'VALIDADOR',
                    'rol_firmante' => $usuario->rol->nombre,
                    'nombres_historicos' => trim(
                        (string) $usuario
                            ->datosPersonales
                            ->nombres
                    ),
                    'apellidos_historicos' => trim(
                        (string) $usuario
                            ->datosPersonales
                            ->apellidos
                    ),
                    'hash_documento' => $hashDocumento,
                    'firma_electronica' => $firmaElectronica,
                    'algoritmo' => 'HMAC-SHA256',
                    'version_firma' => 1,
                    'fecha_firma' => $fechaFirma,
                    'valida' => true,
                ]);

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Firmas Arqueos',
                    accion: 'FIRMAR_ARQUEO',
                    tablaAfectada: 'firmas_arqueos',
                    registroId: $firmaArqueo->id,
                    descripcion:
                        'El Agente registró su firma electrónica como VALIDADOR '
                        . 'del arqueo '
                        . $arqueoBloqueado->numero_arqueo
                        . '.',
                    valoresAnteriores: null,
                    valoresNuevos: [
                        'id' => (int) $firmaArqueo->id,
                        'arqueo_id' => (int) $firmaArqueo->arqueo_id,
                        'usuario_id' => (int) $firmaArqueo->usuario_id,
                        'tipo_firma' => $firmaArqueo->tipo_firma,
                        'rol_firmante' => $firmaArqueo->rol_firmante,
                        'nombres_historicos' => $firmaArqueo->nombres_historicos,
                        'apellidos_historicos' => $firmaArqueo->apellidos_historicos,
                        'hash_documento' => $firmaArqueo->hash_documento,
                        'firma_electronica' => $firmaArqueo->firma_electronica,
                        'algoritmo' => $firmaArqueo->algoritmo,
                        'version_firma' => (int) $firmaArqueo->version_firma,
                        'fecha_firma' => $fechaFirma->format('Y-m-d H:i:s.u'),
                        'valida' => (bool) $firmaArqueo->valida,
                    ]
                );
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'firma' => 'Ocurrió un error al firmar el arqueo.',
            ]);
        }

        return redirect()
            ->route(
                'agente.arqueos-promotor.show',
                $arqueo
            )
            ->with(
                'success',
                'El arqueo fue firmado electrónicamente.'
            );
    }

    private function validarArqueoPromotor(
        Arqueo $arqueo,
        int $agenteId
    ): void {
        abort_if(
            (int) $arqueo->agente_id !== $agenteId,
            403,
            'No tiene autorización para consultar este arqueo.'
        );

        abort_if(
            $arqueo->tipo !== 'VISITA_PROMOTOR',
            404,
            'El arqueo solicitado no corresponde a una visita de promotor.'
        );
    }
}
