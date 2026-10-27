<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Agente;
use App\Models\Arqueo;
use App\Models\ArqueoDetalle;
use App\Models\FirmaArqueo;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ArqueoAgenteController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        $busqueda = trim((string) $request->string('buscar'));

        $agentes = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->join(
                'asignaciones_promotor_ruta as apr',
                'apr.ruta_id',
                '=',
                'r.id'
            )
            ->where('apr.promotor_usuario_id', $usuario->id)
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', today())
            ->where(function ($query): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', today());
            })
            ->where('r.estado', true)
            ->where('a.estado', 'ACTIVO')
            ->when(
                $busqueda !== '',
                function ($query) use ($busqueda): void {
                    $query->where(function ($subquery) use ($busqueda): void {
                        $subquery
                            ->where(
                                'a.codigo_agente',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'a.nombre_negocio',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'a.nombre_propietario',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'r.nombre',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'reg.nombre',
                                'like',
                                '%' . $busqueda . '%'
                            );
                    });
                }
            )
            ->select([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'a.estado',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->selectSub(
                Arqueo::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->where('arqueos.tipo', 'DIARIO_AGENTE')
                    ->where('arqueos.estado', '!=', 'ANULADO'),
                'total_arqueos'
            )
            ->selectSub(
                Arqueo::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->where('arqueos.tipo', 'DIARIO_AGENTE')
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
                                'CERTIFICADOR'
                            )
                            ->where(
                                'firmas_arqueos.valida',
                                true
                            );
                    }),
                'pendientes_certificacion'
            )
            ->distinct()
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->orderBy('a.nombre_negocio')
            ->paginate(10)
            ->withQueryString();

        return view('promotor.arqueos-agentes.index', [
            'agentes' => $agentes,
            'busqueda' => $busqueda,
        ]);
    }

    public function arqueos(
        Request $request,
        Agente $agente
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);
        $this->validarAgenteAsignado($usuario->id, $agente->id);

        $agente->loadMissing('ruta.region');

        $estado = trim((string) $request->string('estado'));

        $arqueos = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'DIARIO_AGENTE')
            ->when(
                $estado !== '',
                fn (Builder $query): Builder => $query->where(
                    'estado',
                    $estado
                )
            )
            ->withCount([
                'firmas as firmado_agente' => function ($query): void {
                    $query
                        ->where('tipo_firma', 'REALIZADOR')
                        ->where('valida', true);
                },
                'firmas as firmado_promotor' => function ($query): void {
                    $query
                        ->where('tipo_firma', 'CERTIFICADOR')
                        ->where('valida', true);
                },
            ])
            ->latest('fecha_arqueo')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('promotor.arqueos-agentes.arqueos', [
            'agente' => $agente,
            'arqueos' => $arqueos,
            'estado' => $estado,
        ]);
    }

    public function show(
        Request $request,
        Arqueo $arqueo
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        abort_if(
            $arqueo->tipo !== 'DIARIO_AGENTE',
            404,
            'El arqueo solicitado no corresponde a un arqueo del agente.'
        );

        $this->validarAgenteAsignado(
            $usuario->id,
            (int) $arqueo->agente_id
        );

        $arqueo->loadMissing([
            'agente.ruta.region',
            'firmas',
        ]);

        $detalles = ArqueoDetalle::query()
            ->where('arqueo_id', $arqueo->id)
            ->orderByRaw(
                "CASE WHEN tipo = 'BILLETE' THEN 1 ELSE 2 END"
            )
            ->orderByDesc('denominacion')
            ->get();

        $firmaAgente = $arqueo->firmas->first(
            fn (FirmaArqueo $firma): bool =>
                $firma->tipo_firma === 'REALIZADOR'
                && $firma->valida
        );

        $firmaPromotor = $arqueo->firmas->first(
            fn (FirmaArqueo $firma): bool =>
                $firma->tipo_firma === 'CERTIFICADOR'
                && $firma->valida
        );

        $puedeCertificar = $arqueo->estado === 'PENDIENTE_CERTIFICACION'
            && $firmaAgente !== null
            && $firmaPromotor === null;

        $puedeAnular = $arqueo->estado === 'PENDIENTE_CERTIFICACION'
            && $firmaPromotor === null;

        return view('promotor.arqueos-agentes.show', [
            'arqueo' => $arqueo,
            'detalles' => $detalles,
            'billetes' => $detalles->where('tipo', 'BILLETE'),
            'monedas' => $detalles->where('tipo', 'MONEDA'),
            'firmaAgente' => $firmaAgente,
            'firmaPromotor' => $firmaPromotor,
            'puedeCertificar' => $puedeCertificar,
            'puedeAnular' => $puedeAnular,
        ]);
    }

    public function imprimir(
        Request $request,
        Arqueo $arqueo
    ): Response {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        abort_if(
            $arqueo->tipo !== 'DIARIO_AGENTE',
            404,
            'El arqueo solicitado no corresponde a un arqueo del agente.'
        );

        $this->validarAgenteAsignado(
            $usuario->id,
            (int) $arqueo->agente_id
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
            'agente.arqueos.pdf',
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

    public function certificar(
        Request $request,
        Arqueo $arqueo
    ) {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        abort_if(
            $arqueo->tipo !== 'DIARIO_AGENTE',
            404,
            'El arqueo solicitado no corresponde a un arqueo del agente.'
        );

        $this->validarAgenteAsignado(
            $usuario->id,
            (int) $arqueo->agente_id
        );

        $usuario->loadMissing('datosPersonales');

        return DB::transaction(function () use (
            $arqueo,
            $usuario
        ) {
            $arqueo = Arqueo::query()
                ->lockForUpdate()
                ->findOrFail($arqueo->id);

            abort_if(
                $arqueo->estado === 'ANULADO',
                422,
                'El arqueo está anulado y no puede certificarse.'
            );

            abort_if(
                $arqueo->estado !== 'PENDIENTE_CERTIFICACION',
                422,
                'El arqueo no está pendiente de certificación.'
            );

            $firmaAgente = FirmaArqueo::query()
                ->where('arqueo_id', $arqueo->id)
                ->where('tipo_firma', 'REALIZADOR')
                ->where('valida', true)
                ->first();

            abort_if(
                ! $firmaAgente,
                422,
                'El Agente todavía no ha firmado electrónicamente.'
            );

            $firmaExistente = FirmaArqueo::query()
                ->where('arqueo_id', $arqueo->id)
                ->where('tipo_firma', 'CERTIFICADOR')
                ->where('valida', true)
                ->exists();

            abort_if(
                $firmaExistente,
                422,
                'Este arqueo ya fue certificado por un Promotor.'
            );

            $datosPersonales = $usuario->datosPersonales;

            $nombres = trim(
                (string) (
                    $datosPersonales?->nombres
                    ?? $datosPersonales?->nombre
                    ?? $usuario->nombre_usuario
                    ?? 'Promotor'
                )
            );

            $apellidos = trim(
                (string) (
                    $datosPersonales?->apellidos
                    ?? $datosPersonales?->apellido
                    ?? ''
                )
            );

            $contenidoDocumento = json_encode(
                [
                    'arqueo_id' => $arqueo->id,
                    'numero_arqueo' => $arqueo->numero_arqueo,
                    'agente_id' => $arqueo->agente_id,
                    'fecha_arqueo' => optional(
                        $arqueo->fecha_arqueo
                    )->format('Y-m-d'),
                    'total_arqueado' => $arqueo->total_arqueado,
                    'saldo_sistema' => $arqueo->saldo_sistema,
                    'diferencia' => $arqueo->diferencia,
                    'firmado_por' => $usuario->id,
                    'fecha_firma' => now()->toIso8601String(),
                ],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );

            $hashDocumento = hash(
                'sha256',
                (string) $contenidoDocumento
            );

            $firmaElectronica = hash_hmac(
                'sha256',
                $hashDocumento,
                config('app.key')
            );

            $fechaFirma = now();

            $firma = FirmaArqueo::create([
                'arqueo_id' => $arqueo->id,
                'usuario_id' => $usuario->id,
                'tipo_firma' => 'CERTIFICADOR',
                'rol_firmante' => 'Promotor',
                'nombres_historicos' => $nombres,
                'apellidos_historicos' => $apellidos,
                'hash_documento' => $hashDocumento,
                'firma_electronica' => $firmaElectronica,
                'algoritmo' => 'HMAC-SHA256',
                'version_firma' => 1,
                'fecha_firma' => $fechaFirma,
                'valida' => true,
            ]);

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Firmas de Arqueos',
                accion: 'FIRMAR_ARQUEO',
                tablaAfectada: 'firmas_arqueos',
                registroId: $firma->id,
                descripcion:
                    'El Promotor registró su firma electrónica como CERTIFICADOR '
                    . 'del arqueo '
                    . $arqueo->numero_arqueo
                    . '.',
                valoresAnteriores: null,
                valoresNuevos: [
                    'id' => (int) $firma->id,
                    'arqueo_id' => (int) $firma->arqueo_id,
                    'usuario_id' => (int) $firma->usuario_id,
                    'tipo_firma' => $firma->tipo_firma,
                    'rol_firmante' => $firma->rol_firmante,
                    'nombres_historicos' => $firma->nombres_historicos,
                    'apellidos_historicos' => $firma->apellidos_historicos,
                    'algoritmo' => $firma->algoritmo,
                    'version_firma' => (int) $firma->version_firma,
                    'fecha_firma' => $fechaFirma,
                    'valida' => (bool) $firma->valida,
                ]
            );

            $valoresAnterioresArqueo = [
                'estado' => $arqueo->estado,
                'certificado_at' => $arqueo->certificado_at,
            ];

            $fechaCertificacion = now();

            $arqueo->update([
                'estado' => 'CERTIFICADO',
                'certificado_at' => $fechaCertificacion,
            ]);

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Arqueos de Agentes',
                accion: 'CERTIFICAR_ARQUEO',
                tablaAfectada: 'arqueos',
                registroId: $arqueo->id,
                descripcion:
                    'El Promotor certificó el arqueo '
                    . $arqueo->numero_arqueo
                    . '.',
                valoresAnteriores: $valoresAnterioresArqueo,
                valoresNuevos: [
                    'estado' => $arqueo->estado,
                    'certificado_at' => $fechaCertificacion,
                    'firma_certificador_id' => (int) $firma->id,
                ]
            );

            return redirect()
                ->route(
                    'promotor.arqueos-agentes.show',
                    $arqueo
                )
                ->with(
                    'success',
                    'El arqueo fue certificado y firmado electrónicamente.'
                );
        });
    }

    public function anular(
        Request $request,
        Arqueo $arqueo
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        abort_if(
            $arqueo->tipo !== 'DIARIO_AGENTE',
            404,
            'El arqueo solicitado no corresponde a un arqueo del agente.'
        );

        $this->validarAgenteAsignado(
            $usuario->id,
            (int) $arqueo->agente_id
        );

        $datosValidados = $request->validate(
            [
                'motivo_anulacion' => [
                    'required',
                    'string',
                    'min:10',
                    'max:500',
                ],
                'password' => [
                    'required',
                    'string',
                ],
            ],
            [
                'motivo_anulacion.required' => 'Debe indicar el motivo de la anulación.',
                'motivo_anulacion.min' => 'El motivo debe contener al menos 10 caracteres.',
                'motivo_anulacion.max' => 'El motivo no puede superar los 500 caracteres.',
                'password.required' => 'Debe ingresar su contraseña para confirmar.',
            ]
        );

        if (! Hash::check(
            $datosValidados['password'],
            $usuario->password
        )) {
            return back()
                ->withErrors([
                    'password' => 'La contraseña ingresada es incorrecta.',
                ])
                ->withInput();
        }

        return DB::transaction(function () use (
            $arqueo,
            $usuario,
            $datosValidados
        ): RedirectResponse {
            $arqueoBloqueado = Arqueo::query()
                ->lockForUpdate()
                ->findOrFail($arqueo->id);

            abort_if(
                $arqueoBloqueado->estado === 'ANULADO',
                422,
                'El arqueo ya se encuentra anulado.'
            );

            abort_if(
                $arqueoBloqueado->estado === 'CERTIFICADO',
                422,
                'Un arqueo certificado no puede ser anulado por el Promotor.'
            );

            abort_if(
                $arqueoBloqueado->estado !== 'PENDIENTE_CERTIFICACION',
                422,
                'El arqueo no se encuentra disponible para anulación.'
            );

            $firmaPromotor = FirmaArqueo::query()
                ->where('arqueo_id', $arqueoBloqueado->id)
                ->where('tipo_firma', 'CERTIFICADOR')
                ->where('valida', true)
                ->exists();

            abort_if(
                $firmaPromotor,
                422,
                'El arqueo ya fue firmado por un Promotor y no puede anularse.'
            );

            $motivo = trim($datosValidados['motivo_anulacion']);

            $observacionAnterior = trim(
                (string) $arqueoBloqueado->observaciones
            );

            $registroAnulacion = sprintf(
                '[ANULACIÓN %s | Promotor: %s | Usuario ID: %d] %s',
                now()->format('d/m/Y H:i:s'),
                $usuario->nombre_completo
                    ?? $usuario->nombre_usuario
                    ?? 'Promotor',
                $usuario->id,
                $motivo
            );

            $valoresAnterioresArqueo = [
                'estado' => $arqueoBloqueado->estado,
                'anulado_at' => $arqueoBloqueado->anulado_at,
                'observaciones' => $arqueoBloqueado->observaciones,
            ];

            $fechaAnulacion = now();

            $nuevasObservaciones = $observacionAnterior !== ''
                ? $observacionAnterior . PHP_EOL . PHP_EOL . $registroAnulacion
                : $registroAnulacion;

            $arqueoBloqueado->update([
                'estado' => 'ANULADO',
                'anulado_at' => $fechaAnulacion,
                'observaciones' => $nuevasObservaciones,
            ]);

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Arqueos de Agentes',
                accion: 'ANULAR_ARQUEO',
                tablaAfectada: 'arqueos',
                registroId: $arqueoBloqueado->id,
                descripcion:
                    'El Promotor anuló el arqueo '
                    . $arqueoBloqueado->numero_arqueo
                    . '. Motivo: '
                    . $motivo,
                valoresAnteriores: $valoresAnterioresArqueo,
                valoresNuevos: [
                    'estado' => $arqueoBloqueado->estado,
                    'anulado_at' => $fechaAnulacion,
                    'motivo_anulacion' => $motivo,
                    'observaciones' => $nuevasObservaciones,
                ]
            );

            return redirect()
                ->route(
                    'promotor.arqueos-agentes.show',
                    $arqueoBloqueado
                )
                ->with(
                    'success',
                    'El arqueo fue anulado correctamente.'
                );
        });
    }

    private function validarPromotor(Usuario $usuario): void
    {
        $usuario->loadMissing([
            'rol',
            'datosPersonales',
        ]);

        abort_if(
            ! $usuario->rol || $usuario->rol->nombre !== 'Promotor',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }

    private function validarAgenteAsignado(
        int $promotorUsuarioId,
        int $agenteId
    ): void {
        $asignado = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join(
                'asignaciones_promotor_ruta as apr',
                'apr.ruta_id',
                '=',
                'r.id'
            )
            ->where('a.id', $agenteId)
            ->where('a.estado', 'ACTIVO')
            ->where('r.estado', true)
            ->where('apr.promotor_usuario_id', $promotorUsuarioId)
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', today())
            ->where(function ($query): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', today());
            })
            ->exists();

        abort_unless(
            $asignado,
            403,
            'El agente no pertenece a una ruta asignada al promotor.'
        );
    }
}
