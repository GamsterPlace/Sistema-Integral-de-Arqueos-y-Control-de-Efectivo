<?php

namespace App\Http\Controllers\Agente;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\FirmaArqueo;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'agente.ruta.region',
            'datosPersonales',
        ]);

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        abort_if(
            ! $agente->ruta,
            422,
            'El agente no tiene una ruta asignada.'
        );

        abort_if(
            ! $agente->ruta->region,
            422,
            'La ruta del agente no tiene una región asignada.'
        );

        /*
        |--------------------------------------------------------------------------
        | ARQUEO DEL DÍA
        |--------------------------------------------------------------------------
        */

        $arqueoHoy = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'DIARIO_AGENTE')
            ->whereDate('fecha_arqueo', today())
            ->where('estado', '!=', 'ANULADO')
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | CONTROL "NO ATENDIÓ" DEL DÍA
        |--------------------------------------------------------------------------
        |
        | Un control solamente se considera válido cuando continúa vigente.
        | La restricción unique de la tabla evita más de un control por
        | agente y fecha.
        |
        */

        $noAtendioHoy = DB::table('controles_diarios')
            ->where('agente_id', $agente->id)
            ->whereDate('fecha', today())
            ->where('tipo', 'NO_ATENDIO')
            ->where('vigente', true)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | ESTADÍSTICAS
        |--------------------------------------------------------------------------
        */

        $totalArqueos = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'DIARIO_AGENTE')
            ->where('estado', '!=', 'ANULADO')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | ÚLTIMO ARQUEO DEL AGENTE
        |--------------------------------------------------------------------------
        */

        $ultimoArqueoAgente = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'DIARIO_AGENTE')
            ->latest('fecha_arqueo')
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | ÚLTIMO ARQUEO DEL PROMOTOR
        |--------------------------------------------------------------------------
        */

        $ultimoArqueoPromotor = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', '!=', 'ANULADO')
            ->latest('fecha_arqueo')
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | ARQUEOS DE PROMOTOR PENDIENTES DE FIRMA
        |--------------------------------------------------------------------------
        */

        $pendientesFirmaPromotor = Arqueo::query()
            ->where('arqueos.agente_id', $agente->id)
            ->where('arqueos.tipo', 'VISITA_PROMOTOR')
            ->where(
                'arqueos.estado',
                'PENDIENTE_CERTIFICACION'
            )
            ->whereNotExists(function ($query): void {
                $query
                    ->selectRaw('1')
                    ->from('firmas_arqueos')
                    ->whereColumn(
                        'firmas_arqueos.arqueo_id',
                        'arqueos.id'
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

        /*
        |--------------------------------------------------------------------------
        | ESTADO DE FIRMA DEL ÚLTIMO ARQUEO DEL PROMOTOR
        |--------------------------------------------------------------------------
        */

        $ultimoArqueoPromotorPendienteFirma = false;

        if ($ultimoArqueoPromotor) {
            $ultimoArqueoPromotorPendienteFirma =
                ! FirmaArqueo::query()
                    ->where(
                        'arqueo_id',
                        $ultimoArqueoPromotor->id
                    )
                    ->where(
                        'tipo_firma',
                        'VALIDADOR'
                    )
                    ->where(
                        'valida',
                        true
                    )
                    ->exists();
        }

        /*
        |--------------------------------------------------------------------------
        | ESTADO DE LA ACTIVIDAD DEL DÍA
        |--------------------------------------------------------------------------
        */

        if ($noAtendioHoy) {
            $estadoArqueoHoy = 'No atendió';

            $descripcionArqueoHoy =
                'El día de hoy fue registrado como no atendido.';
        } else {
            $estadoArqueoHoy = $this->textoEstado(
                $arqueoHoy?->estado
            );

            $descripcionArqueoHoy = match (
                $arqueoHoy?->estado
            ) {
                'PENDIENTE_CERTIFICACION' =>
                    'El arqueo fue enviado y está pendiente de certificación.',

                'CERTIFICADO' =>
                    'El arqueo diario ya fue certificado.',

                default =>
                    'Aún no ha realizado el arqueo diario.',
            };
        }

        /*
        |--------------------------------------------------------------------------
        | PERMISOS DE ACCIONES DEL DÍA
        |--------------------------------------------------------------------------
        |
        | Si existe un arqueo válido, ya no puede marcar "No atendió".
        | Si existe un "No atendió" vigente, ya no puede iniciar el arqueo
        | ordinario de hoy.
        |
        */

        $puedeRealizarArqueoHoy =
            ! $arqueoHoy
            && ! $noAtendioHoy;

        $puedeMarcarNoAtendio =
            ! $arqueoHoy
            && ! $noAtendioHoy;

        return view('dashboards.agente', [
            'agente' => $agente,

            'arqueoHoy' => $arqueoHoy,
            'noAtendioHoy' => $noAtendioHoy,

            'estadoArqueoHoy' => $estadoArqueoHoy,
            'descripcionArqueoHoy' =>
                $descripcionArqueoHoy,

            'puedeRealizarArqueoHoy' =>
                $puedeRealizarArqueoHoy,

            'puedeMarcarNoAtendio' =>
                $puedeMarcarNoAtendio,

            'totalArqueos' => $totalArqueos,

            'pendientesFirmaPromotor' =>
                $pendientesFirmaPromotor,

            'ultimoArqueoAgente' =>
                $ultimoArqueoAgente,

            'estadoUltimoArqueoAgente' =>
                $this->textoEstado(
                    $ultimoArqueoAgente?->estado
                ),

            'ultimoArqueoPromotor' =>
                $ultimoArqueoPromotor,

            'estadoUltimoArqueoPromotor' =>
                $this->textoEstado(
                    $ultimoArqueoPromotor?->estado
                ),

            'ultimoArqueoPromotorPendienteFirma' =>
                $ultimoArqueoPromotorPendienteFirma,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MARCAR EL DÍA COMO "NO ATENDIÓ"
    |--------------------------------------------------------------------------
    */

    public function marcarNoAtendio(
        Request $request
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'agente',
            'rol',
        ]);

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDAR ANOTACIÓN
        |--------------------------------------------------------------------------
        */

        $datosValidados = $request->validate(
            [
                'anotacion' => [
                    'required',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'anotacion.required' =>
                    'Debe indicar el motivo por el cual no atendió.',

                'anotacion.string' =>
                    'La anotación ingresada no es válida.',

                'anotacion.max' =>
                    'La anotación no puede superar los 1000 caracteres.',
            ]
        );

        try {
            DB::transaction(function () use (
                $usuario,
                $agente,
                $datosValidados
            ): void {
                /*
                |--------------------------------------------------------------------------
                | VERIFICAR ARQUEO DEL DÍA
                |--------------------------------------------------------------------------
                |
                | Se vuelve a comprobar dentro de la transacción para impedir
                | que un arqueo y un NO_ATENDIO se registren para el mismo día.
                |
                */

                $arqueoHoy = Arqueo::query()
                    ->where(
                        'agente_id',
                        $agente->id
                    )
                    ->where(
                        'tipo',
                        'DIARIO_AGENTE'
                    )
                    ->whereDate(
                        'fecha_arqueo',
                        today()
                    )
                    ->where(
                        'estado',
                        '!=',
                        'ANULADO'
                    )
                    ->lockForUpdate()
                    ->exists();

                if ($arqueoHoy) {
                    throw ValidationException::withMessages([
                        'no_atendio' =>
                            'No puede marcar el día como "No atendió" porque ya existe un arqueo registrado para hoy.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | VERIFICAR CONTROL EXISTENTE
                |--------------------------------------------------------------------------
                */

                $controlExistente = DB::table(
                    'controles_diarios'
                )
                    ->where(
                        'agente_id',
                        $agente->id
                    )
                    ->whereDate(
                        'fecha',
                        today()
                    )
                    ->lockForUpdate()
                    ->first();

                if ($controlExistente) {
                    if (
                        $controlExistente->tipo
                        === 'NO_ATENDIO'
                        && (bool) $controlExistente->vigente
                    ) {
                        throw ValidationException::withMessages([
                            'no_atendio' =>
                                'El día de hoy ya fue marcado como "No atendió".',
                        ]);
                    }

                    throw ValidationException::withMessages([
                        'no_atendio' =>
                            'Ya existe un control diario registrado para el día de hoy.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | REGISTRAR "NO ATENDIÓ"
                |--------------------------------------------------------------------------
                */

                $registradoAt = now();

                $controlId = DB::table(
                    'controles_diarios'
                )->insertGetId([
                    'agente_id' =>
                        $agente->id,

                    'fecha' =>
                        today()->format('Y-m-d'),

                    'tipo' =>
                        'NO_ATENDIO',

                    'anotacion' =>
                        trim(
                            $datosValidados['anotacion']
                        ),

                    'registrado_por' =>
                        $usuario->id,

                    'registrado_at' =>
                        $registradoAt,

                    'vigente' =>
                        true,

                    'cancelado_por' =>
                        null,

                    'cancelado_at' =>
                        null,

                    'motivo_cancelacion' =>
                        null,

                    'created_at' =>
                        $registradoAt,

                    'updated_at' =>
                        $registradoAt,
                ]);

                /*
                |--------------------------------------------------------------------------
                | OBTENER REGISTRO CREADO
                |--------------------------------------------------------------------------
                */

                $controlCreado = DB::table(
                    'controles_diarios'
                )
                    ->where(
                        'id',
                        $controlId
                    )
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | AUDITORÍA
                |--------------------------------------------------------------------------
                */

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Control Diario Agente',
                    accion: 'MARCAR_NO_ATENDIO',
                    tablaAfectada: 'controles_diarios',
                    registroId: $controlId,
                    descripcion:
                        'El Agente marcó el día '
                        . today()->format('d/m/Y')
                        . ' como "No atendió".',
                    valoresAnteriores: null,
                    valoresNuevos: $controlCreado
                        ? (array) $controlCreado
                        : [
                            'id' => (int) $controlId,
                            'agente_id' =>
                                (int) $agente->id,
                            'fecha' =>
                                today()->format('Y-m-d'),
                            'tipo' =>
                                'NO_ATENDIO',
                            'anotacion' =>
                                trim(
                                    $datosValidados[
                                        'anotacion'
                                    ]
                                ),
                            'registrado_por' =>
                                (int) $usuario->id,
                            'vigente' =>
                                true,
                        ]
                );
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('agente.dashboard')
                ->withErrors([
                    'no_atendio' =>
                        'Ocurrió un error al registrar el día como "No atendió".',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ELIMINAR POSIBLE CONTEXTO DE ARQUEO ABIERTO
        |--------------------------------------------------------------------------
        |
        | Como el sistema no maneja borradores, si existía un formulario de
        | arqueo iniciado en la sesión, se descarta su contexto.
        |
        */

        $request->session()->forget(
            'arqueo_agente_contexto'
        );

        return redirect()
            ->route('agente.dashboard')
            ->with(
                'success',
                'El día de hoy fue marcado correctamente como "No atendió".'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | TEXTO DEL ESTADO
    |--------------------------------------------------------------------------
    */

    private function textoEstado(
        ?string $estado
    ): string {
        return match ($estado) {
            'PENDIENTE_CERTIFICACION' =>
                'Pendiente',

            'CERTIFICADO' =>
                'Certificado',

            'ANULADO' =>
                'Anulado',

            default =>
                'Pendiente',
        };
    }
}
