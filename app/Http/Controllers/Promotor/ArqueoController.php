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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ArqueoController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $arqueos = Arqueo::query()
            ->where('creado_por', $usuario->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->latest('fecha_arqueo')
            ->latest('id')
            ->paginate(10);

        $totalArqueos = Arqueo::query()
            ->where('creado_por', $usuario->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $pendientesFirma = Arqueo::query()
            ->where('creado_por', $usuario->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', 'PENDIENTE_CERTIFICACION')
            ->whereNotExists(function ($query): void {
                $query
                    ->selectRaw('1')
                    ->from('firmas_arqueos')
                    ->whereColumn(
                        'firmas_arqueos.arqueo_id',
                        'arqueos.id'
                    )
                    ->where('firmas_arqueos.tipo_firma', 'VALIDADOR')
                    ->where('firmas_arqueos.valida', true);
            })
            ->count();

        $certificados = Arqueo::query()
            ->where('creado_por', $usuario->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', 'CERTIFICADO')
            ->count();

        return view('promotor.arqueos.index', [
            'arqueos' => $arqueos,
            'totalArqueos' => $totalArqueos,
            'pendientesFirma' => $pendientesFirma,
            'certificados' => $certificados,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'rol',
            'datosPersonales',
        ]);

        $agentes = $this->agentesAsignados($usuario->id)
            ->with([
                'ruta.region',
            ])
            ->orderBy('nombre_negocio')
            ->get();

        if ($agentes->isEmpty()) {
            return redirect()
                ->route('promotor.dashboard')
                ->with(
                    'warning',
                    'No tiene agentes activos asignados.'
                );
        }

        $agenteSeleccionado = null;

        if ($request->filled('agente_id')) {
            $agenteSeleccionado = $this->obtenerAgenteAsignado(
                $usuario->id,
                (int) $request->integer('agente_id')
            );

            $horaInicio = now();

            $request->session()->put(
                'arqueo_promotor_hora_inicio',
                $horaInicio->toDateTimeString()
            );

            $request->session()->put(
                'arqueo_promotor_agente_id',
                $agenteSeleccionado->id
            );
        }

        return view('promotor.arqueos.create', [
            'agentes' => $agentes,
            'agenteSeleccionado' => $agenteSeleccionado,
            'horaInicio' => isset($horaInicio)
                ? $horaInicio
                : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datosValidados = $request->validate([
            'agente_id' => [
                'required',
                'integer',
            ],
            'nombre_propietario' => [
                'required',
                'string',
                'max:150',
            ],
            'saldo_sistema' => [
                'required',
                'numeric',
                'min:0',
            ],
            'detalle' => [
                'required',
                'json',
            ],
            'certificacion' => [
                'required',
                'string',
                'max:2000',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'rol',
            'datosPersonales',
        ]);

        abort_if(
            ! $usuario->rol || $usuario->rol->nombre !== 'Promotor',
            403,
            'No tiene autorización para realizar este arqueo.'
        );

        abort_if(
            ! $usuario->datosPersonales,
            422,
            'El usuario no tiene datos personales registrados.'
        );

        $agente = $this->obtenerAgenteAsignado(
            $usuario->id,
            (int) $datosValidados['agente_id']
        );

        $agente->loadMissing([
            'ruta.region',
        ]);

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

        $agenteSesion = (int) $request->session()->get(
            'arqueo_promotor_agente_id'
        );

        $horaInicioSesion = $request->session()->get(
            'arqueo_promotor_hora_inicio'
        );

        if (
            $agenteSesion !== (int) $agente->id
            || ! $horaInicioSesion
        ) {
            return redirect()
                ->route('promotor.arqueos.create')
                ->withErrors([
                    'arqueo' => 'La sesión del arqueo expiró. Inicie nuevamente.',
                ]);
        }

        $detalle = json_decode(
            $datosValidados['detalle'],
            true
        );

        if (! is_array($detalle) || empty($detalle)) {
            return back()
                ->withErrors([
                    'detalle' => 'El detalle del arqueo es inválido.',
                ])
                ->withInput();
        }

        $detalleValidado = $this->validarDetalle(
            $detalle
        );

        $horaInicio = \Carbon\Carbon::parse(
            $horaInicioSesion
        );

        $horaFin = now();

        try {
            DB::transaction(function () use (
                $datosValidados,
                $detalleValidado,
                $usuario,
                $agente,
                $horaInicio,
                $horaFin
            ): void {
                $totalBilletes = 0.00;
                $totalMonedas = 0.00;

                foreach ($detalleValidado as $item) {
                    $subtotal = round(
                        $item['cantidad'] * $item['denominacion'],
                        2
                    );

                    if ($item['tipo'] === 'BILLETE') {
                        $totalBilletes += $subtotal;
                    } else {
                        $totalMonedas += $subtotal;
                    }
                }

                $totalBilletes = round($totalBilletes, 2);
                $totalMonedas = round($totalMonedas, 2);

                $totalArqueado = round(
                    $totalBilletes + $totalMonedas,
                    2
                );

                $saldoSistema = round(
                    (float) $datosValidados['saldo_sistema'],
                    2
                );

                $diferencia = round(
                    $totalArqueado - $saldoSistema,
                    2
                );

                $arqueo = Arqueo::create([
                    'numero_arqueo' => $this->generarNumeroArqueo(),
                    'agente_id' => $agente->id,
                    'creado_por' => $usuario->id,
                    'habilitacion_atrasada_id' => null,
                    'tipo' => 'VISITA_PROMOTOR',
                    'estado' => 'PENDIENTE_CERTIFICACION',
                    'fecha_arqueo' => today(),
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'fuera_fecha_ordinaria' => false,
                    'codigo_agente_historico' => $agente->codigo_agente,
                    'nombre_negocio_historico' => $agente->nombre_negocio,
                    'nombre_propietario_historico' => trim(
                        $datosValidados['nombre_propietario']
                    ),
                    'direccion_historica' => $agente->direccion,
                    'ruta_historica' => $agente->ruta->nombre,
                    'region_historica' => $agente->ruta->region->nombre,
                    'total_billetes' => $totalBilletes,
                    'total_monedas' => $totalMonedas,
                    'total_arqueado' => $totalArqueado,
                    'saldo_sistema' => $saldoSistema,
                    'diferencia' => $diferencia,
                    'certificacion' => trim(
                        $datosValidados['certificacion']
                    ),
                    'observaciones' => filled(
                        $datosValidados['observaciones'] ?? null
                    )
                        ? trim($datosValidados['observaciones'])
                        : null,
                    'pendiente_certificacion_at' => $horaFin,
                ]);

                foreach ($detalleValidado as $item) {
                    if ($item['cantidad'] <= 0) {
                        continue;
                    }

                    ArqueoDetalle::create([
                        'arqueo_id' => $arqueo->id,
                        'tipo' => $item['tipo'],
                        'denominacion' => $item['denominacion'],
                        'cantidad' => $item['cantidad'],
                        'subtotal' => round(
                            $item['cantidad'] * $item['denominacion'],
                            2
                        ),
                    ]);
                }

                $firmaRealizador = $this->crearFirmaRealizador(
                    $arqueo,
                    $usuario,
                    $detalleValidado
                );

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Arqueos de Promotor',
                    accion: 'CREAR_ARQUEO',
                    tablaAfectada: 'arqueos',
                    registroId: $arqueo->id,
                    descripcion:
                        'El Promotor creó el arqueo '
                        . $arqueo->numero_arqueo
                        . ' para el Agente '
                        . $agente->codigo_agente
                        . ' — '
                        . $agente->nombre_negocio
                        . '.',
                    valoresAnteriores: null,
                    valoresNuevos: [
                        'id' => (int) $arqueo->id,
                        'numero_arqueo' => $arqueo->numero_arqueo,
                        'agente_id' => (int) $arqueo->agente_id,
                        'creado_por' => (int) $arqueo->creado_por,
                        'tipo' => $arqueo->tipo,
                        'estado' => $arqueo->estado,
                        'fecha_arqueo' => $arqueo->fecha_arqueo,
                        'hora_inicio' => $arqueo->hora_inicio,
                        'hora_fin' => $arqueo->hora_fin,
                        'fuera_fecha_ordinaria' =>
                            (bool) $arqueo->fuera_fecha_ordinaria,
                        'codigo_agente_historico' =>
                            $arqueo->codigo_agente_historico,
                        'nombre_negocio_historico' =>
                            $arqueo->nombre_negocio_historico,
                        'nombre_propietario_historico' =>
                            $arqueo->nombre_propietario_historico,
                        'direccion_historica' =>
                            $arqueo->direccion_historica,
                        'ruta_historica' =>
                            $arqueo->ruta_historica,
                        'region_historica' =>
                            $arqueo->region_historica,
                        'total_billetes' =>
                            (float) $arqueo->total_billetes,
                        'total_monedas' =>
                            (float) $arqueo->total_monedas,
                        'total_arqueado' =>
                            (float) $arqueo->total_arqueado,
                        'saldo_sistema' =>
                            (float) $arqueo->saldo_sistema,
                        'diferencia' =>
                            (float) $arqueo->diferencia,
                        'certificacion' =>
                            $arqueo->certificacion,
                        'observaciones' =>
                            $arqueo->observaciones,
                        'pendiente_certificacion_at' =>
                            $arqueo->pendiente_certificacion_at,
                        'detalle' => collect($detalleValidado)
                            ->filter(
                                fn (array $item): bool =>
                                    $item['cantidad'] > 0
                            )
                            ->map(
                                fn (array $item): array => [
                                    'tipo' => $item['tipo'],
                                    'denominacion' =>
                                        (float) $item['denominacion'],
                                    'cantidad' =>
                                        (int) $item['cantidad'],
                                    'subtotal' => round(
                                        $item['cantidad']
                                        * $item['denominacion'],
                                        2
                                    ),
                                ]
                            )
                            ->values()
                            ->all(),
                    ]
                );

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Firmas de Arqueos',
                    accion: 'FIRMAR_ARQUEO',
                    tablaAfectada: 'firmas_arqueos',
                    registroId: $firmaRealizador->id,
                    descripcion:
                        'El Promotor registró su firma electrónica como REALIZADOR '
                        . 'del arqueo '
                        . $arqueo->numero_arqueo
                        . '.',
                    valoresAnteriores: null,
                    valoresNuevos: [
                        'id' => (int) $firmaRealizador->id,
                        'arqueo_id' =>
                            (int) $firmaRealizador->arqueo_id,
                        'usuario_id' =>
                            (int) $firmaRealizador->usuario_id,
                        'tipo_firma' =>
                            $firmaRealizador->tipo_firma,
                        'rol_firmante' =>
                            $firmaRealizador->rol_firmante,
                        'nombres_historicos' =>
                            $firmaRealizador->nombres_historicos,
                        'apellidos_historicos' =>
                            $firmaRealizador->apellidos_historicos,
                        'algoritmo' =>
                            $firmaRealizador->algoritmo,
                        'version_firma' =>
                            (int) $firmaRealizador->version_firma,
                        'fecha_firma' =>
                            $firmaRealizador->fecha_firma,
                        'valida' =>
                            (bool) $firmaRealizador->valida,
                    ]
                );
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors([
                    'error' => 'Ocurrió un error al registrar el arqueo.',
                ])
                ->withInput();
        }

        $request->session()->forget([
            'arqueo_promotor_hora_inicio',
            'arqueo_promotor_agente_id',
        ]);

        return redirect()
            ->route('promotor.arqueos.index')
            ->with(
                'success',
                'El arqueo fue enviado para firma del agente.'
            );
    }

    public function show(
        Request $request,
        Arqueo $arqueo
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        abort_if(
            (int) $arqueo->creado_por !== (int) $usuario->id,
            403,
            'No tiene autorización para consultar este arqueo.'
        );

        abort_if(
            $arqueo->tipo !== 'VISITA_PROMOTOR',
            404,
            'El arqueo solicitado no corresponde a una visita de promotor.'
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

        $firmaPromotor = $arqueo->firmas->first(
            fn (FirmaArqueo $firma): bool =>
                $firma->tipo_firma === 'REALIZADOR'
                && $firma->valida
        );

        $firmaAgente = $arqueo->firmas->first(
            fn (FirmaArqueo $firma): bool =>
                $firma->tipo_firma === 'VALIDADOR'
                && $firma->valida
        );

        $firmaJefe = $arqueo->firmas->first(
            fn (FirmaArqueo $firma): bool =>
                $firma->tipo_firma === 'CERTIFICADOR'
                && $firma->valida
        );

        return view('promotor.arqueos.show', [
            'arqueo' => $arqueo,
            'billetes' => $billetes,
            'monedas' => $monedas,
            'firmaPromotor' => $firmaPromotor,
            'firmaAgente' => $firmaAgente,
            'firmaJefe' => $firmaJefe,
        ]);
    }

    public function imprimir(
        Request $request,
        Arqueo $arqueo
    ): Response {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        abort_if(
            (int) $arqueo->creado_por !== (int) $usuario->id,
            403,
            'No tiene autorización para imprimir este arqueo.'
        );

        abort_if(
            $arqueo->tipo !== 'VISITA_PROMOTOR',
            404,
            'El arqueo solicitado no corresponde a una visita de promotor.'
        );

        $arqueo->loadMissing([
            'detalles',
            'firmas',
        ]);

        $billetesRegistrados = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->keyBy(fn (ArqueoDetalle $detalle): string => number_format(
                (float) $detalle->denominacion,
                2,
                '.',
                ''
            ));

        $monedasRegistradas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->keyBy(fn (ArqueoDetalle $detalle): string => number_format(
                (float) $detalle->denominacion,
                2,
                '.',
                ''
            ));

        $billetes = collect([
            200.00,
            100.00,
            50.00,
            20.00,
            10.00,
            5.00,
            1.00,
        ])->map(function (
            float $denominacion
        ) use ($billetesRegistrados): array {
            $clave = number_format(
                $denominacion,
                2,
                '.',
                ''
            );

            $detalle = $billetesRegistrados->get($clave);

            return [
                'denominacion' => $denominacion,
                'cantidad' => $detalle?->cantidad ?? 0,
                'subtotal' => $detalle?->subtotal ?? 0,
            ];
        });

        $monedas = collect([
            1.00,
            0.50,
            0.25,
            0.10,
            0.05,
        ])->map(function (
            float $denominacion
        ) use ($monedasRegistradas): array {
            $clave = number_format(
                $denominacion,
                2,
                '.',
                ''
            );

            $detalle = $monedasRegistradas->get($clave);

            return [
                'denominacion' => $denominacion,
                'cantidad' => $detalle?->cantidad ?? 0,
                'subtotal' => $detalle?->subtotal ?? 0,
            ];
        });

        $firmaPromotor = $arqueo->firmas->first(
            fn (FirmaArqueo $firma): bool =>
                $firma->tipo_firma === 'REALIZADOR'
                && $firma->valida
        );

        $firmaAgente = $arqueo->firmas->first(
            fn (FirmaArqueo $firma): bool =>
                $firma->tipo_firma === 'VALIDADOR'
                && $firma->valida
        );

        $firmaJefe = $arqueo->firmas->first(
            fn (FirmaArqueo $firma): bool =>
                $firma->tipo_firma === 'CERTIFICADOR'
                && $firma->valida
        );

        $pdf = Pdf::loadView(
            'promotor.arqueos.pdf',
            [
                'arqueo' => $arqueo,
                'billetes' => $billetes,
                'monedas' => $monedas,
                'firmaPromotor' => $firmaPromotor,
                'firmaAgente' => $firmaAgente,
                'firmaJefe' => $firmaJefe,
            ]
        )->setPaper('letter', 'portrait');

        return $pdf->stream(
            $arqueo->numero_arqueo . '.pdf'
        );
    }

    private function agentesAsignados(
        int $promotorUsuarioId
    ): Builder {
        $hoy = today();

        return Agente::query()
            ->where('estado', 'ACTIVO')
            ->whereHas('ruta', function (Builder $query) use (
                $promotorUsuarioId,
                $hoy
            ): void {
                $query->whereExists(function ($subquery) use (
                    $promotorUsuarioId,
                    $hoy
                ): void {
                    $subquery
                        ->selectRaw('1')
                        ->from('asignaciones_promotor_ruta')
                        ->whereColumn(
                            'asignaciones_promotor_ruta.ruta_id',
                            'rutas.id'
                        )
                        ->where(
                            'asignaciones_promotor_ruta.promotor_usuario_id',
                            $promotorUsuarioId
                        )
                        ->where(
                            'asignaciones_promotor_ruta.estado',
                            true
                        )
                        ->whereDate(
                            'asignaciones_promotor_ruta.fecha_inicio',
                            '<=',
                            $hoy
                        )
                        ->where(function ($query) use ($hoy): void {
                            $query
                                ->whereNull(
                                    'asignaciones_promotor_ruta.fecha_fin'
                                )
                                ->orWhereDate(
                                    'asignaciones_promotor_ruta.fecha_fin',
                                    '>=',
                                    $hoy
                                );
                        });
                });
            });
    }

    private function obtenerAgenteAsignado(
        int $promotorUsuarioId,
        int $agenteId
    ): Agente {
        $agente = $this->agentesAsignados(
            $promotorUsuarioId
        )->find($agenteId);

        abort_if(
            ! $agente,
            403,
            'El agente no pertenece a una ruta asignada al promotor.'
        );

        return $agente;
    }

    private function validarDetalle(array $detalle): array
    {
        $billetesPermitidos = [
            200.00,
            100.00,
            50.00,
            20.00,
            10.00,
            5.00,
            1.00,
        ];

        $monedasPermitidas = [
            1.00,
            0.50,
            0.25,
            0.10,
            0.05,
        ];

        $resultado = [];
        $claves = [];

        foreach ($detalle as $item) {
            if (
                ! is_array($item)
                || ! isset(
                    $item['tipo'],
                    $item['denominacion'],
                    $item['cantidad']
                )
            ) {
                throw ValidationException::withMessages([
                    'detalle' => 'El detalle contiene datos incompletos.',
                ]);
            }

            $tipo = strtoupper(
                trim((string) $item['tipo'])
            );

            $denominacion = round(
                (float) $item['denominacion'],
                2
            );

            if (
                filter_var(
                    $item['cantidad'],
                    FILTER_VALIDATE_INT
                ) === false
                || (int) $item['cantidad'] < 0
            ) {
                throw ValidationException::withMessages([
                    'detalle' => 'Existe una cantidad inválida.',
                ]);
            }

            $permitida = match ($tipo) {
                'BILLETE' => in_array(
                    $denominacion,
                    $billetesPermitidos,
                    true
                ),
                'MONEDA' => in_array(
                    $denominacion,
                    $monedasPermitidas,
                    true
                ),
                default => false,
            };

            if (! $permitida) {
                throw ValidationException::withMessages([
                    'detalle' => 'Existe una denominación no permitida.',
                ]);
            }

            $clave = $tipo . '|'
                . number_format(
                    $denominacion,
                    2,
                    '.',
                    ''
                );

            if (in_array($clave, $claves, true)) {
                throw ValidationException::withMessages([
                    'detalle' => 'Existen denominaciones duplicadas.',
                ]);
            }

            $claves[] = $clave;

            $resultado[] = [
                'tipo' => $tipo,
                'denominacion' => $denominacion,
                'cantidad' => (int) $item['cantidad'],
            ];
        }

        return $resultado;
    }

    private function crearFirmaRealizador(
        Arqueo $arqueo,
        Usuario $usuario,
        array $detalle
    ): FirmaArqueo {
        $detalleHash = collect($detalle)
            ->map(function (array $item): array {
                return [
                    'tipo' => $item['tipo'],
                    'denominacion' => number_format(
                        (float) $item['denominacion'],
                        2,
                        '.',
                        ''
                    ),
                    'cantidad' => (int) $item['cantidad'],
                    'subtotal' => number_format(
                        $item['cantidad'] * $item['denominacion'],
                        2,
                        '.',
                        ''
                    ),
                ];
            })
            ->values()
            ->all();

        $documento = [
            'numero_arqueo' => $arqueo->numero_arqueo,
            'agente_id' => (int) $arqueo->agente_id,
            'creado_por' => (int) $arqueo->creado_por,
            'tipo' => $arqueo->tipo,
            'estado' => $arqueo->estado,
            'fecha_arqueo' => $arqueo->fecha_arqueo->format('Y-m-d'),
            'hora_inicio' => $arqueo->hora_inicio->format(
                'Y-m-d H:i:s'
            ),
            'hora_fin' => $arqueo->hora_fin?->format(
                'Y-m-d H:i:s'
            ),
            'total_arqueado' => number_format(
                (float) $arqueo->total_arqueado,
                2,
                '.',
                ''
            ),
            'saldo_sistema' => number_format(
                (float) $arqueo->saldo_sistema,
                2,
                '.',
                ''
            ),
            'diferencia' => number_format(
                (float) $arqueo->diferencia,
                2,
                '.',
                ''
            ),
            'certificacion' => trim(
                (string) $arqueo->certificacion
            ),
            'detalle' => $detalleHash,
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
            'REALIZADOR',
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

        return FirmaArqueo::create([
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $usuario->id,
            'tipo_firma' => 'REALIZADOR',
            'rol_firmante' => $usuario->rol->nombre,
            'nombres_historicos' => trim(
                (string) $usuario->datosPersonales->nombres
            ),
            'apellidos_historicos' => trim(
                (string) $usuario->datosPersonales->apellidos
            ),
            'hash_documento' => $hashDocumento,
            'firma_electronica' => hash_hmac(
                'sha256',
                $contenidoFirma,
                $claveFirma
            ),
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'fecha_firma' => $fechaFirma,
            'valida' => true,
        ]);
    }

    private function generarNumeroArqueo(): string
    {
        do {
            $numero = 'ARQ-'
                . now()->format('YmdHis')
                . '-'
                . Str::upper(Str::random(4));
        } while (
            Arqueo::query()
                ->where('numero_arqueo', $numero)
                ->exists()
        );

        return $numero;
    }
}
