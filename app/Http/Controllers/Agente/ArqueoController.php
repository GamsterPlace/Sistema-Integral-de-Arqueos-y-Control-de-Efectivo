<?php

namespace App\Http\Controllers\Agente;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\ArqueoDetalle;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FirmaArqueo;
use Throwable;

class ArqueoController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing('agente');

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        $arqueoHoy = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'DIARIO_AGENTE')
            ->whereDate('fecha_arqueo', today())
            ->where('estado', '!=', 'ANULADO')
            ->latest('id')
            ->first();

        $arqueos = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'DIARIO_AGENTE')
            ->latest('fecha_arqueo')
            ->latest('id')
            ->paginate(10);

        $totalArqueos = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'DIARIO_AGENTE')
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $totalCertificados = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'DIARIO_AGENTE')
            ->where('estado', 'CERTIFICADO')
            ->count();

        return view('agente.arqueos.index', [
            'agente' => $agente,
            'arqueoHoy' => $arqueoHoy,
            'arqueos' => $arqueos,
            'totalArqueos' => $totalArqueos,
            'totalCertificados' => $totalCertificados,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'agente.ruta.region',
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
        | CONTEXTO DEL ARQUEO
        |--------------------------------------------------------------------------
        |
        | Si viene el parámetro "habilitacion", el arqueo NO corresponde a hoy.
        | Corresponde exactamente a la fecha autorizada en
        | habilitaciones_arqueos_atrasados.
        |
        */
        $habilitacionId = $request->integer('habilitacion');

        $habilitacion = null;
        $esExtemporaneo = false;
        $fechaArqueo = today();

        if ($habilitacionId > 0) {
            $habilitacion = DB::table(
                'habilitaciones_arqueos_atrasados'
            )
                ->where('id', $habilitacionId)
                ->where('agente_id', $agente->id)
                ->first();

            if (! $habilitacion) {
                return redirect()
                    ->route('agente.arqueos-extemporaneos.index')
                    ->withErrors([
                        'habilitacion' => 'La habilitación seleccionada no existe o no pertenece a este agente.',
                    ]);
            }

            if ($habilitacion->estado !== 'PENDIENTE') {
                return redirect()
                    ->route('agente.arqueos-extemporaneos.index')
                    ->withErrors([
                        'habilitacion' => 'La habilitación seleccionada ya no está disponible.',
                    ]);
            }

            $esExtemporaneo = true;

            $fechaArqueo = \Carbon\Carbon::parse(
                $habilitacion->fecha_autorizada
            )->startOfDay();
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN DE DUPLICADO
        |--------------------------------------------------------------------------
        |
        | Normal: valida contra hoy.
        | Extemporáneo: valida contra la fecha autorizada.
        |
        */
        $existe = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'DIARIO_AGENTE')
            ->whereDate('fecha_arqueo', $fechaArqueo)
            ->where('estado', '!=', 'ANULADO')
            ->exists();

        if ($existe) {
            if ($esExtemporaneo) {
                return redirect()
                    ->route('agente.arqueos-extemporaneos.index')
                    ->with(
                        'warning',
                        'Ya existe un arqueo válido para la fecha habilitada '
                        . $fechaArqueo->format('d/m/Y')
                        . '.'
                    );
            }

            return redirect()
                ->route('agente.arqueos.index')
                ->with(
                    'warning',
                    'Ya existe un arqueo para el día de hoy.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN DE "NO ATENDIÓ"
        |--------------------------------------------------------------------------
        |
        | Esta validación aplica únicamente al arqueo ordinario del día.
        | Una habilitación extemporánea constituye una autorización explícita
        | para registrar el arqueo correspondiente a una fecha anterior.
        |
        */

        if (! $esExtemporaneo) {
            $noAtendioHoy = DB::table('controles_diarios')
                ->where('agente_id', $agente->id)
                ->whereDate('fecha', today())
                ->where('tipo', 'NO_ATENDIO')
                ->where('vigente', true)
                ->exists();

            if ($noAtendioHoy) {
                $request->session()->forget(
                    'arqueo_agente_contexto'
                );

                return redirect()
                    ->route('agente.dashboard')
                    ->with(
                        'warning',
                        'No puede realizar el arqueo de hoy porque el día fue marcado como "No atendió".'
                    );
            }
        }

        $horaInicio = now();

        /*
        |--------------------------------------------------------------------------
        | GUARDAR CONTEXTO EN SESIÓN
        |--------------------------------------------------------------------------
        |
        | No confiamos únicamente en un hidden del formulario para decidir
        | qué fecha se guardará.
        |
        */
        $request->session()->put(
            'arqueo_agente_contexto',
            [
                'hora_inicio' => $horaInicio->toDateTimeString(),
                'es_extemporaneo' => $esExtemporaneo,
                'habilitacion_id' => $habilitacion?->id,
                'fecha_arqueo' => $fechaArqueo->format('Y-m-d'),
            ]
        );

        return view(
            'agente.arqueos.create',
            [
                'agente' => $agente,
                'horaInicio' => $horaInicio,
                'esExtemporaneo' => $esExtemporaneo,
                'habilitacion' => $habilitacion,
                'fechaArqueo' => $fechaArqueo,
            ]
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $datosValidados = $request->validate([
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
            'habilitacion_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'agente.ruta.region',
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
            ! $agente->ruta,
            422,
            'El agente no tiene una ruta asignada.'
        );

        abort_if(
            ! $agente->ruta->region,
            422,
            'La ruta del agente no tiene una región asignada.'
        );

        abort_if(
            ! $usuario->rol,
            422,
            'El usuario no tiene un rol asignado.'
        );

        abort_if(
            ! $usuario->datosPersonales,
            422,
            'El usuario no tiene datos personales registrados.'
        );

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

        $detalleValidado = [];

        foreach ($detalle as $item) {
            if (
                ! is_array($item)
                || ! isset(
                    $item['tipo'],
                    $item['denominacion'],
                    $item['cantidad']
                )
            ) {
                return back()
                    ->withErrors([
                        'detalle' => 'El detalle del arqueo contiene datos incompletos.',
                    ])
                    ->withInput();
            }

            $tipo = strtoupper(
                trim((string) $item['tipo'])
            );

            $denominacion = round(
                (float) $item['denominacion'],
                2
            );

            $cantidadOriginal = $item['cantidad'];

            if (
                filter_var(
                    $cantidadOriginal,
                    FILTER_VALIDATE_INT
                ) === false
                || (int) $cantidadOriginal < 0
            ) {
                return back()
                    ->withErrors([
                        'detalle' => 'La cantidad de una denominación no es válida.',
                    ])
                    ->withInput();
            }

            $cantidad = (int) $cantidadOriginal;

            if ($tipo === 'BILLETE') {
                $denominacionValida = in_array(
                    $denominacion,
                    $billetesPermitidos,
                    true
                );
            } elseif ($tipo === 'MONEDA') {
                $denominacionValida = in_array(
                    $denominacion,
                    $monedasPermitidas,
                    true
                );
            } else {
                $denominacionValida = false;
            }

            if (! $denominacionValida) {
                return back()
                    ->withErrors([
                        'detalle' => 'Se encontró una denominación no permitida.',
                    ])
                    ->withInput();
            }

            $detalleValidado[] = [
                'tipo' => $tipo,
                'denominacion' => $denominacion,
                'cantidad' => $cantidad,
            ];
        }

        $contextoArqueo = $request->session()->get(
            'arqueo_agente_contexto'
        );

        if (
            ! is_array($contextoArqueo)
            || empty($contextoArqueo['hora_inicio'])
            || empty($contextoArqueo['fecha_arqueo'])
        ) {
            return redirect()
                ->route('agente.arqueos.create')
                ->withErrors([
                    'arqueo' => 'La sesión del arqueo expiró. Debe iniciar el arqueo nuevamente.',
                ]);
        }

        $horaInicio = \Carbon\Carbon::parse(
            $contextoArqueo['hora_inicio']
        );

        $fechaArqueo = \Carbon\Carbon::parse(
            $contextoArqueo['fecha_arqueo']
        )->startOfDay();

        $esExtemporaneo = (bool) (
            $contextoArqueo['es_extemporaneo']
            ?? false
        );

        $habilitacionId = $esExtemporaneo
            ? (int) (
                $contextoArqueo['habilitacion_id']
                ?? 0
            )
            : null;

        /*
        | Evita que se altere manualmente el ID enviado por el formulario.
        */
        $habilitacionFormulario = isset(
            $datosValidados['habilitacion_id']
        )
            ? (int) $datosValidados['habilitacion_id']
            : null;

        if (
            $esExtemporaneo
            && (
                ! $habilitacionId
                || $habilitacionFormulario !== $habilitacionId
            )
        ) {
            return redirect()
                ->route('agente.arqueos-extemporaneos.index')
                ->withErrors([
                    'habilitacion' => 'La habilitación del arqueo no es válida. Inicie nuevamente el proceso.',
                ]);
        }

        $horaFin = now();

        try {
            DB::transaction(function () use (
                $datosValidados,
                $detalleValidado,
                $usuario,
                $agente,
                $horaInicio,
                $horaFin,
                $fechaArqueo,
                $esExtemporaneo,
                $habilitacionId
            ): void {
                /*
                |--------------------------------------------------------------------------
                | BLOQUEO Y VALIDACIÓN DE HABILITACIÓN EXTEMPORÁNEA
                |--------------------------------------------------------------------------
                */
                if ($esExtemporaneo) {
                    $habilitacion = DB::table(
                        'habilitaciones_arqueos_atrasados'
                    )
                        ->where('id', $habilitacionId)
                        ->where('agente_id', $agente->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $habilitacion) {
                        throw ValidationException::withMessages([
                            'habilitacion' => 'La habilitación seleccionada no existe.',
                        ]);
                    }

                    if ($habilitacion->estado !== 'PENDIENTE') {
                        throw ValidationException::withMessages([
                            'habilitacion' => 'La habilitación ya fue utilizada o cancelada.',
                        ]);
                    }

                    $fechaAutorizada = \Carbon\Carbon::parse(
                        $habilitacion->fecha_autorizada
                    )->format('Y-m-d');

                    if (
                        $fechaAutorizada
                        !== $fechaArqueo->format('Y-m-d')
                    ) {
                        throw ValidationException::withMessages([
                            'habilitacion' => 'La fecha autorizada no coincide con el arqueo iniciado.',
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | DUPLICADO POR FECHA REAL DEL ARQUEO
                |--------------------------------------------------------------------------
                */
                $existe = Arqueo::query()
                    ->where('agente_id', $agente->id)
                    ->where('tipo', 'DIARIO_AGENTE')
                    ->whereDate('fecha_arqueo', $fechaArqueo)
                    ->where('estado', '!=', 'ANULADO')
                    ->lockForUpdate()
                    ->exists();

                if ($existe) {
                    throw ValidationException::withMessages([
                        'arqueo' => $esExtemporaneo
                            ? 'Ya existe un arqueo registrado para la fecha habilitada '
                                . $fechaArqueo->format('d/m/Y')
                                . '.'
                            : 'Ya existe un arqueo registrado para el día de hoy.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | BLOQUEO POR "NO ATENDIÓ"
                |--------------------------------------------------------------------------
                |
                | Se vuelve a comprobar dentro de la transacción para impedir que
                | un formulario abierto previamente pueda crear un arqueo ordinario
                | después de que el día haya sido marcado como NO_ATENDIO.
                |
                */

                if (! $esExtemporaneo) {
                    $noAtendioHoy = DB::table('controles_diarios')
                        ->where('agente_id', $agente->id)
                        ->whereDate('fecha', today())
                        ->where('tipo', 'NO_ATENDIO')
                        ->where('vigente', true)
                        ->lockForUpdate()
                        ->exists();

                    if ($noAtendioHoy) {
                        throw ValidationException::withMessages([
                            'arqueo' =>
                                'No puede registrar el arqueo porque el día de hoy fue marcado como "No atendió".',
                        ]);
                    }
                }

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

                    'tipo' => 'DIARIO_AGENTE',
                    'estado' => 'PENDIENTE_CERTIFICACION',

                    'fecha_arqueo' => $fechaArqueo,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'pendiente_certificacion_at' => $horaFin,

                    'fuera_fecha_ordinaria' => $esExtemporaneo,

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


                $nombresHistoricos = trim(
                    (string) $usuario->datosPersonales->nombres
                );

                $apellidosHistoricos = trim(
                    (string) $usuario->datosPersonales->apellidos
                );

                $detalleParaHash = collect($detalleValidado)
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
                                (float) (
                                    $item['cantidad']
                                    * $item['denominacion']
                                ),
                                2,
                                '.',
                                ''
                            ),
                        ];
                    })
                    ->values()
                    ->all();

                $documentoFirmado = [
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
                    'codigo_agente_historico' => $arqueo->codigo_agente_historico,
                    'nombre_negocio_historico' => $arqueo->nombre_negocio_historico,
                    'nombre_propietario_historico' => $arqueo->nombre_propietario_historico,
                    'direccion_historica' => $arqueo->direccion_historica,
                    'ruta_historica' => $arqueo->ruta_historica,
                    'region_historica' => $arqueo->region_historica,
                    'total_billetes' => number_format(
                        (float) $arqueo->total_billetes,
                        2,
                        '.',
                        ''
                    ),
                    'total_monedas' => number_format(
                        (float) $arqueo->total_monedas,
                        2,
                        '.',
                        ''
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
                    'observaciones' => $arqueo->observaciones,
                    'detalle' => $detalleParaHash,
                ];

                $documentoJson = json_encode(
                    $documentoFirmado,
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

                $firmaElectronica = hash_hmac(
                    'sha256',
                    $contenidoFirma,
                    $claveFirma
                );

                $firmaArqueo = FirmaArqueo::create([
                    'arqueo_id' => $arqueo->id,
                    'usuario_id' => $usuario->id,
                    'tipo_firma' => 'REALIZADOR',
                    'rol_firmante' => $usuario->rol->nombre,
                    'nombres_historicos' => $nombresHistoricos,
                    'apellidos_historicos' => $apellidosHistoricos,
                    'hash_documento' => $hashDocumento,
                    'firma_electronica' => $firmaElectronica,
                    'algoritmo' => 'HMAC-SHA256',
                    'version_firma' => 1,
                    'fecha_firma' => $fechaFirma,
                    'valida' => true,
                ]);

                /*
                |--------------------------------------------------------------------------
                | AUDITORÍA AUTOMÁTICA
                |--------------------------------------------------------------------------
                */
                $auditoria = app(AuditoriaService::class);

                $auditoria->registrar(
                    usuario: $usuario,
                    modulo: 'Arqueos Agente',
                    accion: 'CREAR_ARQUEO',
                    tablaAfectada: 'arqueos',
                    registroId: $arqueo->id,
                    descripcion: 'El Agente creó el arqueo ' . $arqueo->numero_arqueo
                        . ($esExtemporaneo ? ' correspondiente a una fecha habilitada de forma extemporánea.' : '.'),
                    valoresAnteriores: null,
                    valoresNuevos: [
                        'id' => (int) $arqueo->id,
                        'numero_arqueo' => $arqueo->numero_arqueo,
                        'agente_id' => (int) $arqueo->agente_id,
                        'creado_por' => (int) $arqueo->creado_por,
                        'tipo' => $arqueo->tipo,
                        'estado' => $arqueo->estado,
                        'fecha_arqueo' => $arqueo->fecha_arqueo->format('Y-m-d'),
                        'hora_inicio' => $arqueo->hora_inicio?->format('Y-m-d H:i:s'),
                        'hora_fin' => $arqueo->hora_fin?->format('Y-m-d H:i:s'),
                        'fuera_fecha_ordinaria' => (bool) $arqueo->fuera_fecha_ordinaria,
                        'habilitacion_id' => $habilitacionId,
                        'codigo_agente_historico' => $arqueo->codigo_agente_historico,
                        'nombre_negocio_historico' => $arqueo->nombre_negocio_historico,
                        'nombre_propietario_historico' => $arqueo->nombre_propietario_historico,
                        'direccion_historica' => $arqueo->direccion_historica,
                        'ruta_historica' => $arqueo->ruta_historica,
                        'region_historica' => $arqueo->region_historica,
                        'total_billetes' => (float) $arqueo->total_billetes,
                        'total_monedas' => (float) $arqueo->total_monedas,
                        'total_arqueado' => (float) $arqueo->total_arqueado,
                        'saldo_sistema' => (float) $arqueo->saldo_sistema,
                        'diferencia' => (float) $arqueo->diferencia,
                        'certificacion' => $arqueo->certificacion,
                        'observaciones' => $arqueo->observaciones,
                        'detalle' => $detalleParaHash,
                    ]
                );

                $auditoria->registrar(
                    usuario: $usuario,
                    modulo: 'Firmas Arqueos',
                    accion: 'FIRMAR_ARQUEO',
                    tablaAfectada: 'firmas_arqueos',
                    registroId: $firmaArqueo->id,
                    descripcion: 'El Agente registró su firma electrónica como REALIZADOR del arqueo '
                        . $arqueo->numero_arqueo . '.',
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
                        'fecha_firma' => (string) $firmaArqueo->fecha_firma,
                        'valida' => (bool) $firmaArqueo->valida,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | CONSUMIR HABILITACIÓN
                |--------------------------------------------------------------------------
                |
                | Solo se marca UTILIZADA después de que el arqueo y su firma
                | fueron creados correctamente dentro de la misma transacción.
                |
                */
                if ($esExtemporaneo) {
                    $habilitacionAnterior = DB::table(
                        'habilitaciones_arqueos_atrasados'
                    )
                        ->where('id', $habilitacionId)
                        ->where('agente_id', $agente->id)
                        ->first();

                    $utilizadoAt = now();

                    $actualizados = DB::table(
                        'habilitaciones_arqueos_atrasados'
                    )
                        ->where('id', $habilitacionId)
                        ->where('agente_id', $agente->id)
                        ->where('estado', 'PENDIENTE')
                        ->update([
                            'estado' => 'UTILIZADA',
                            'utilizado_at' => $utilizadoAt,
                        ]);

                    if ($actualizados !== 1) {
                        throw ValidationException::withMessages([
                            'habilitacion' => 'La habilitación ya no se encuentra disponible.',
                        ]);
                    }

                    $habilitacionNueva = DB::table(
                        'habilitaciones_arqueos_atrasados'
                    )
                        ->where('id', $habilitacionId)
                        ->first();

                    $auditoria->registrar(
                        usuario: $usuario,
                        modulo: 'Arqueos Extemporáneos',
                        accion: 'UTILIZAR_HABILITACION_ARQUEO_ATRASADO',
                        tablaAfectada: 'habilitaciones_arqueos_atrasados',
                        registroId: $habilitacionId,
                        descripcion: 'La habilitación de arqueo atrasado #'
                            . $habilitacionId
                            . ' fue utilizada para registrar el arqueo '
                            . $arqueo->numero_arqueo . '.',
                        valoresAnteriores: $habilitacionAnterior
                            ? (array) $habilitacionAnterior
                            : null,
                        valoresNuevos: $habilitacionNueva
                            ? (array) $habilitacionNueva
                            : null
                    );
                }
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

        $request->session()->forget(
            'arqueo_agente_contexto'
        );

        if ($esExtemporaneo) {
            return redirect()
                ->route('agente.arqueos-extemporaneos.index')
                ->with(
                    'success',
                    'El arqueo extemporáneo correspondiente al '
                    . $fechaArqueo->format('d/m/Y')
                    . ' fue registrado correctamente y enviado para certificación.'
                );
        }

        return redirect()
            ->route('agente.arqueos.index')
            ->with(
                'success',
                'El arqueo fue enviado correctamente para certificación.'
            );
    }

    public function show(Request $request, Arqueo $arqueo): View
    {
        $arqueo = $this->obtenerArqueoDelAgente($request, $arqueo);

        $detalles = ArqueoDetalle::query()
            ->where('arqueo_id', $arqueo->id)
            ->orderByRaw("CASE WHEN tipo = 'BILLETE' THEN 1 ELSE 2 END")
            ->orderByDesc('denominacion')
            ->get();

        $firmas = FirmaArqueo::query()
            ->where('arqueo_id', $arqueo->id)
            ->where('valida', true)
            ->orderBy('fecha_firma')
            ->get();

        return view('agente.arqueos.show', [
            'arqueo' => $arqueo,
            'detalles' => $detalles,
            'billetes' => $detalles->where('tipo', 'BILLETE'),
            'monedas' => $detalles->where('tipo', 'MONEDA'),
            'firmas' => $firmas,
        ]);
    }

    public function imprimir(Request $request, Arqueo $arqueo): Response
    {
        $arqueo = $this->obtenerArqueoDelAgente($request, $arqueo);

        $detalles = ArqueoDetalle::query()
            ->where('arqueo_id', $arqueo->id)
            ->get();

        $billetesRegistrados = $detalles
            ->where('tipo', 'BILLETE')
            ->keyBy(fn (ArqueoDetalle $detalle) => number_format(
                (float) $detalle->denominacion,
                2,
                '.',
                ''
            ));

        $monedasRegistradas = $detalles
            ->where('tipo', 'MONEDA')
            ->keyBy(fn (ArqueoDetalle $detalle) => number_format(
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
        ])->map(function (float $denominacion) use ($billetesRegistrados): array {
            $clave = number_format($denominacion, 2, '.', '');
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
        ])->map(function (float $denominacion) use ($monedasRegistradas): array {
            $clave = number_format($denominacion, 2, '.', '');
            $detalle = $monedasRegistradas->get($clave);

            return [
                'denominacion' => $denominacion,
                'cantidad' => $detalle?->cantidad ?? 0,
                'subtotal' => $detalle?->subtotal ?? 0,
            ];
        });

        $firmas = FirmaArqueo::query()
            ->where('arqueo_id', $arqueo->id)
            ->where('valida', true)
            ->orderBy('fecha_firma')
            ->get();

        $pdf = Pdf::loadView('agente.arqueos.pdf', [
            'arqueo' => $arqueo,
            'billetes' => $billetes,
            'monedas' => $monedas,
            'firmas' => $firmas,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream($arqueo->numero_arqueo . '.pdf');
    }

    private function obtenerArqueoDelAgente(
        Request $request,
        Arqueo $arqueo
    ): Arqueo {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing('agente');

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        abort_if(
            (int) $arqueo->agente_id !== (int) $agente->id,
            403,
            'No tiene autorización para consultar este arqueo.'
        );

        return $arqueo;
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
