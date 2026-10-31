<?php
namespace App\Http\Controllers\Receptor;
use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\JornadaReceptor;
use App\Services\Agencias\FirmaArqueoService;
use App\Services\Agencias\GeneracionJornadasService;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class ArqueoOrdinarioController extends Controller
{
    private const BILLETES = [
        200, 100, 50, 20, 10, 5, 1
    ];
    private const MONEDAS = [
        '1.00',
        '0.50',
        '0.25',
        '0.10',
        '0.05',
        '0.01'
    ];
    private const OTROS_CONCEPTOS = [
        'CHEQUES',
        'VALES',
        'OTROS_VALORES'
   ];
    private const LIMITE_CENTAVOS = 99999999999999;
    public function index(Request $request, GeneracionJornadasService $generacionJornadas): View
    {
        $usuario = $request->user();
        // Crear las jornadas del día a partir de asignaciones operativas vigentes.
       $generacionJornadas->jornadasDelReceptor((int) $usuario->getKey());
        $jornadas = JornadaReceptor::with([
            'agencia',
            'ventanilla',
            'turno'
        ])
            ->where('receptor_usuario_id', $usuario->id)
            ->whereDate(
                'fecha_operativa',
                now()->toDateString()
            )
            ->whereHas(
                'asignacionPersonal',
                fn ($q) => $q
                    ->where('usuario_id', $usuario->id)
                    ->where('cargo', 'RECEPTOR')
                    ->where('estado', true)
            )
            ->with([
                'arqueos' => fn ($q) => $q
                    ->where('tipo', 'ORDINARIO')
                    ->orderByDesc('id')
            ])
            ->orderBy('inicio_programado')
            ->get();
        $arqueos = ArqueoAgencia::with([
            'agencia',
            'ventanilla'
        ])
            ->where('receptor_usuario_id', $usuario->id)
            ->where('tipo', 'ORDINARIO')
            ->orderByDesc('id')
            ->paginate(15);
        return view(
           'receptor.arqueos.index',
            compact('jornadas', 'arqueos')
        );
    }
    public function create(
        Request $request,
        int $jornada
    ): View {
        $registro = $this->jornadaAutorizada(
            $request,
            $jornada
        );
        if (
            $registro->fecha_operativa->toDateString()
            !== now()->toDateString()
        ) {
            abort(
                403,
                'Los arqueos fuera de fecha requieren habilitación especial.'
            );
        }
        $this->asegurarDisponible($registro->id);
        /*
         * Se registra el inicio en la sesión del servidor.
         *
         * La clave está vinculada al usuario y la jornada.
         * Si el formulario se recarga por un error, se conserva
         * el inicio original mientras corresponda al mismo día.
         */
        $claveInicio = $this->claveInicio(
            $request->user()->id,
            $registro->id
        );
        $inicioGuardado = $request->session()->get(
            $claveInicio
        );
        $inicioValido = false;
        if (is_string($inicioGuardado)) {
            try {
                $fechaInicio = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $inicioGuardado,
                    config('app.timezone')
                );
                $inicioValido =
                    $fechaInicio !== false
                    && $fechaInicio->format('Y-m-d H:i:s')
                        === $inicioGuardado
                    && $fechaInicio->toDateString()
                        === now()->toDateString()
                    && !$fechaInicio->isFuture();
            } catch (\Throwable $e) {
                $inicioValido = false;
            }
        }
        if (!$inicioValido) {
            $inicioGuardado = now()->format(
                'Y-m-d H:i:s'
            );
            $request->session()->put(
                $claveInicio,
                $inicioGuardado
            );
        }
        /*
         * El formulario utiliza este valor exclusivamente
         * para mostrar la hora de inicio.
         *
         * El almacenamiento definitivo utilizará el valor
         * protegido en la sesión del servidor.
         */
        $inicioFormularioAt = $inicioGuardado;
        return view('receptor.arqueos.create', [
            'jornada' => $registro,
            'billetes' => self::BILLETES,
            'monedas' => self::MONEDAS,
            'inicioFormularioAt' => $inicioFormularioAt,
        ]);
    }
    public function store(
        Request $request,
        int $jornada,
        FirmaArqueoService $firmas,
        AuditoriaService $auditoria
    ) {
        $usuario = $request->user();
        /*
         * La hora de inicio no se obtiene del formulario.
         * Se recupera exclusivamente de la sesión del servidor.
         */
        $claveInicio = $this->claveInicio(
            $usuario->id,
            $jornada
        );
        $inicioGuardado = $request->session()->get(
            $claveInicio
        );
        if (!is_string($inicioGuardado)) {
            throw ValidationException::withMessages([
                'jornada' =>
                    'No se encontró un inicio de arqueo válido. '
                    .'Vuelva a abrir el formulario para comenzar.'
            ]);
        }
        try {
            $inicioAt = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $inicioGuardado,
                config('app.timezone')
            );
            if (
                $inicioAt === false
                || $inicioAt->format('Y-m-d H:i:s')
                    !== $inicioGuardado
                || $inicioAt->toDateString()
                    !== now()->toDateString()
                || $inicioAt->isFuture()
            ) {
                throw new \RuntimeException(
                    'Inicio fuera del período permitido.'
                );
            }
        } catch (\Throwable $e) {
            $request->session()->forget($claveInicio);
            throw ValidationException::withMessages([
                'jornada' =>
                    'La sesión de inicio del arqueo no es válida. '
                    .'Vuelva a abrir el formulario.'
            ]);
        }
        $datos = $request->validate([
            'billetes' => [
                'required',
                'array'
            ],
            'billetes.*' => [
                'required',

                'integer',

                'min:0',

                'max:10000000'

            ],

            'monedas' => [

                'required',

                'array'

            ],

            'monedas.*' => [

                'required',

                'integer',

                'min:0',

                'max:10000000'

            ],

            'otros_valores' => [

                'sometimes',

                'array'

            ],

            'otros_valores.*' => [

                'required',

                'numeric',

                'min:0',

                'max:999999999999.99',

                'decimal:0,2'

            ],

            'total_corte' => [

                'required',

                'numeric',

                'min:0',

                'max:999999999999.99',

                'decimal:0,2'

            ],

            'justificacion_diferencia' => [

                'nullable',

                'string',

                'max:2000'

            ],

            'observaciones' => [

                'nullable',

                'string',

                'max:5000'

            ],

            'confirmar_firma' => [

                'accepted'

            ],

        ]);

        /*

         * Verificación estricta de denominaciones y conceptos.

         */

        if (

            array_diff(

                array_keys($datos['billetes']),

                array_map('strval', self::BILLETES)

            )

            ||

            array_diff(

                array_keys($datos['monedas']),

                self::MONEDAS

            )

        ) {

            throw ValidationException::withMessages([

                'billetes' =>

                    'Se enviaron denominaciones no permitidas.'

            ]);

        }

        if (

            array_diff(

                array_keys($datos['otros_valores'] ?? []),

                self::OTROS_CONCEPTOS

            )

        ) {

            throw ValidationException::withMessages([

                'otros_valores' =>

                    'Se enviaron conceptos no permitidos.'

            ]);

        }

        $detalle = [];

        $billetesCentavos = 0;

        $monedasCentavos = 0;

        $otrosCentavos = 0;

        /*

         * BILLETES

         */

        foreach (self::BILLETES as $valor) {

            $cantidad = (int) (

                $datos['billetes'][(string) $valor] ?? 0

            );

            $subtotal = $valor * 100 * $cantidad;

            $billetesCentavos += $subtotal;

            $detalle[] = [

                'tipo' => 'BILLETE',

                'denominacion' => number_format(

                    $valor,

                    2,

                    '.',

                    ''

                ),

                'concepto' => null,

                'cantidad' => $cantidad,

                'subtotal' => number_format(

                    $subtotal / 100,

                    2,

                    '.',

                    ''

                ),

            ];

        }

        /*

         * MONEDAS

         */

        foreach (self::MONEDAS as $valor) {

            $cantidad = (int) (

                $datos['monedas'][$valor] ?? 0

            );

            $denominacionCentavos = (int) round(

                ((float) $valor) * 100

            );

            $subtotal =

                $denominacionCentavos * $cantidad;

            $monedasCentavos += $subtotal;

            $detalle[] = [

                'tipo' => 'MONEDA',

                'denominacion' => $valor,

                'concepto' => null,

                'cantidad' => $cantidad,

                'subtotal' => number_format(

                    $subtotal / 100,

                    2,

                    '.',

                    ''

                ),

            ];

        }

        /*

         * CHEQUES, VALES Y OTROS VALORES

         */

        foreach (self::OTROS_CONCEPTOS as $concepto) {

            $importe = (string) (

                $datos['otros_valores'][$concepto] ?? '0'

            );

            $subtotal = $this->aCentavos($importe);

            $otrosCentavos += $subtotal;

            $detalle[] = [

                'tipo' => 'OTRO',

                'denominacion' => null,

                'concepto' => $concepto,

                'cantidad' => $subtotal > 0 ? 1 : 0,

                'subtotal' => number_format(

                    $subtotal / 100,

                    2,

                    '.',

                    ''

                ),

            ];

        }

        /*

         * COMPROBACIÓN DEL ARQUEO

         */

        $totalCentavos =

            $billetesCentavos

            + $monedasCentavos

            + $otrosCentavos;

        $corteCentavos = $this->aCentavos(

            (string) $datos['total_corte']

        );

        $diferenciaCentavos =

            $totalCentavos - $corteCentavos;

        if (

            $totalCentavos > self::LIMITE_CENTAVOS

            || abs($diferenciaCentavos) > self::LIMITE_CENTAVOS

        ) {

            throw ValidationException::withMessages([

                'billetes' =>

                    'El monto excede la capacidad permitida.'

            ]);

        }

        if (

            $diferenciaCentavos !== 0

            && trim((string) (

                $datos['justificacion_diferencia'] ?? ''

            )) === ''

        ) {

            throw ValidationException::withMessages([

                'justificacion_diferencia' =>

                    'Debe justificar la diferencia encontrada.'

            ]);

        }

        /*

         * REGISTRO DEFINITIVO

         */

        $arqueo = DB::transaction(function () use (

            $request,

            $jornada,

            $usuario,

            $datos,

            $detalle,

            $billetesCentavos,

            $monedasCentavos,

            $otrosCentavos,

            $totalCentavos,

            $corteCentavos,

            $diferenciaCentavos,

            $inicioAt,

            $firmas,

            $auditoria

        ) {

            /*

             * Bloqueo de jornada para evitar dos arqueos

             * simultáneos sobre la misma asignación.

             */

            $registro = JornadaReceptor::query()

                ->lockForUpdate()

                ->findOrFail($jornada);

            $registro->load([

                'agencia',

                'ventanilla',

                'turno',

                'asignacionPersonal',

                'receptor.datosPersonales'

            ]);

            $this->validarJornada(

                $request,

                $registro

            );

            if (

                $registro->fecha_operativa->toDateString()

                !== now()->toDateString()

            ) {

                throw ValidationException::withMessages([

                    'jornada' =>

                        'La jornada no corresponde a la fecha de hoy.'

                ]);

            }

            $this->asegurarDisponible($registro->id);

            /*

             * La finalización se registra en el servidor,

             * inmediatamente antes de guardar el arqueo.

             */

            $finalizadoAt = now();

            if ($inicioAt->greaterThan($finalizadoAt)) {

                throw ValidationException::withMessages([

                    'jornada' =>

                        'La hora de inicio no puede ser posterior '

                        .'a la hora de finalización.'

                ]);

            }

            $arqueo = ArqueoAgencia::create([

                'numero_arqueo' =>

                    'AG-'

                    .now()->format('YmdHis')

                    .'-'

                    .strtoupper(Str::random(12)),

                'agencia_id' =>

                    $registro->agencia_id,

                'ventanilla_id' =>

                    $registro->ventanilla_id,

                'jornada_id' =>

                    $registro->id,

                'receptor_usuario_id' =>

                    $usuario->id,

                'creado_por' =>

                    $usuario->id,

                'tipo' =>

                    'ORDINARIO',

                'estado' =>

                    'PENDIENTE',

                'fecha_operativa' =>

                    $registro->fecha_operativa,

                'inicio_at' =>

                    $inicioAt,

                'finalizado_at' =>

                    $finalizadoAt,

                'fuera_fecha_ordinaria' =>

                    false,

                'cierra_turno' =>

                    false,

                'agencia_codigo_historico' =>

                    $registro->agencia->codigo,

                'agencia_nombre_historico' =>

                    $registro->agencia->nombre,

                'ventanilla_codigo_historico' =>

                    $registro->ventanilla->codigo,

                'receptor_nombre_historico' =>

                    $usuario->nombre_completo,

                'turno_nombre_historico' =>

                    $registro->turno->nombre,

                'total_billetes' => number_format(

                    $billetesCentavos / 100,

                    2,

                    '.',

                    ''

                ),

                'total_monedas' => number_format(

                    $monedasCentavos / 100,

                    2,

                    '.',

                    ''

                ),

                'total_otros_valores' => number_format(

                    $otrosCentavos / 100,

                    2,

                    '.',

                    ''

                ),

                'total_arqueado' => number_format(

                    $totalCentavos / 100,

                    2,

                    '.',

                    ''

                ),

                'total_corte' => number_format(

                    $corteCentavos / 100,

                    2,

                    '.',

                    ''

                ),

                'diferencia' => number_format(

                    $diferenciaCentavos / 100,

                    2,

                    '.',

                    ''

                ),

                'justificacion_diferencia' =>

                    $datos['justificacion_diferencia'] ?? null,

                'observaciones' =>

                    $datos['observaciones'] ?? null,

                'firmado_receptor_at' =>

                    $finalizadoAt,

            ]);

            /*

             * Los detalles forman parte del documento

             * protegido por la firma electrónica.

             */

            $arqueo->detalles()->createMany($detalle);

            /*

             * Firma electrónica del receptor.

             */

            $firmas->firmarReceptor(

                $arqueo,

                $usuario

            );

            /*

             * Registro de auditoría.

             */

            $auditoria->registrar(

                $usuario,

                'ARQUEOS_AGENCIA',

                'FIRMA_ARQUEO_ORDINARIO',

                'arqueos_agencia',

                $arqueo->id,

                'Arqueo ordinario firmado por el receptor.',

                null,

                [

                    'numero_arqueo' =>

                        $arqueo->numero_arqueo,

                    'jornada_id' =>

                        $registro->id,

                    'inicio_at' =>

                        $inicioAt->format('Y-m-d H:i:s'),

                    'finalizado_at' =>

                        $finalizadoAt->format('Y-m-d H:i:s'),

                    'total_billetes' =>

                        $arqueo->total_billetes,

                    'total_monedas' =>

                        $arqueo->total_monedas,

                    'total_otros_valores' =>

                        $arqueo->total_otros_valores,

                    'total_arqueado' =>

                        $arqueo->total_arqueado,

                    'total_corte' =>

                        $arqueo->total_corte,

                    'diferencia' =>

                        $arqueo->diferencia,

                ]

            );

            return $arqueo;

        }, 3);

        /*

         * Se limpia la sesión solamente después de

         * guardar correctamente el arqueo.

         */

        $request->session()->forget($claveInicio);

        return redirect()

            ->route(

                'receptor.arqueos.show',

                $arqueo->id

            )

            ->with(

                'success',

                'Arqueo firmado correctamente.'

            );

    }

    public function show(

        Request $request,

        int $arqueo,

        FirmaArqueoService $firmas

    ): View {

        $registro = ArqueoAgencia::with([

            'agencia',

            'ventanilla',

            'detalles',

            'firmas'

        ])

            ->where(

                'receptor_usuario_id',

                $request->user()->id

            )

            ->where('tipo', 'ORDINARIO')

            ->findOrFail($arqueo);

        $firma = $registro->firmas

            ->firstWhere(

                'tipo_firma',

                'RECEPTOR'

            );

        return view('receptor.arqueos.show', [

            'arqueo' => $registro,

            'firmaValida' => $firma

                ? $firmas->verificar(

                    $registro,

                    $firma

                )

                : false,

        ]);

    }

    public function imprimir(

        Request $request,

        int $arqueo,

        FirmaArqueoService $firmas

    ) {

        $registro = ArqueoAgencia::with([

            'detalles',

            'firmas'

        ])

            ->where(

                'receptor_usuario_id',

                $request->user()->id

            )

            ->where('tipo', 'ORDINARIO')

            ->findOrFail($arqueo);

        $firma = $registro->firmas

            ->firstWhere(

                'tipo_firma',

                'RECEPTOR'

            );

        $firmaValida = $firma

            ? $firmas->verificar(

                $registro,

                $firma

            )

            : false;

        if (

            !class_exists(

                \Barryvdh\DomPDF\Facade\Pdf::class

            )

        ) {

            abort(

                503,

                'Para generar el PDF debe instalarse '

                .'barryvdh/laravel-dompdf.'

            );

        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView(

            'receptor.arqueos.imprimir',

            [

                'arqueo' => $registro,

                'firmaValida' => $firmaValida,

                'billetes' => self::BILLETES,

                'monedas' => self::MONEDAS,

            ]

        )

            ->setPaper('letter', 'portrait')

            ->stream(

                'arqueo-'

                .preg_replace(

                    '/[^a-zA-Z0-9_-]/',

                    '-',

                    $registro->numero_arqueo

                )

                .'.pdf'

            );

    }

    private function jornadaAutorizada(

        Request $request,

        int $id

    ): JornadaReceptor {

        $registro = JornadaReceptor::with([

            'agencia',

            'ventanilla',

            'turno',

            'asignacionPersonal'

        ])->findOrFail($id);

        $this->validarJornada(

            $request,

            $registro

        );

        return $registro;

    }

    private function validarJornada(

        Request $request,

        JornadaReceptor $jornada

    ): void {

        $asignacion = $jornada->asignacionPersonal;

        abort_unless(

            (int) $jornada->receptor_usuario_id

                === (int) $request->user()->id

            && $asignacion

            && (int) $asignacion->usuario_id

                === (int) $request->user()->id

            && (int) $asignacion->agencia_id

                === (int) $jornada->agencia_id

            && $asignacion->cargo === 'RECEPTOR'

            && $asignacion->estado

            && in_array(

                $asignacion->tipo,

                [

                    'PERMANENTE',

                    'TEMPORAL'

                ],

                true

            )

            && $asignacion->fecha_inicio

                ->toDateString()

                <= $jornada->fecha_operativa

                    ->toDateString()

            && (

                !$asignacion->fecha_fin

                || $asignacion->fecha_fin

                    ->toDateString()

                    >= $jornada->fecha_operativa

                        ->toDateString()

            )

            && $jornada->agencia?->estado

            && $jornada->ventanilla?->estado

            && (int) $jornada->ventanilla->agencia_id

                === (int) $jornada->agencia_id

            && $jornada->turno?->estado,

            403,

            'No tiene autorización para este turno y ventanilla.'

        );

    }

    private function asegurarDisponible(

        int $jornadaId

    ): void {

        if (

            ArqueoAgencia::where(

                'jornada_id',

                $jornadaId

            )

                ->where('tipo', 'ORDINARIO')

                ->exists()

        ) {

            throw ValidationException::withMessages([

                'jornada' =>

                    'Ya existe un arqueo ordinario para esta jornada. '

                    .'Si fue anulado, su reemplazo requiere '

                    .'el procedimiento autorizado.'

            ]);

        }

    }

    /**

     * Convierte un importe decimal a centavos.

     *

     * Evita utilizar números flotantes para sumar

     * cheques, vales y otros importes monetarios.

     */

    private function aCentavos(string $importe): int

    {

        $importe = trim($importe);

        if (

            !preg_match(

                '/^\d+(?:\.\d{1,2})?$/',

                $importe

            )

        ) {

            throw ValidationException::withMessages([

                'otros_valores' =>

                    'Se encontró un importe monetario inválido.'

            ]);

        }

        $partes = explode('.', $importe, 2);

        $enteros = (int) $partes[0];

        $decimales = (int) str_pad(

            $partes[1] ?? '',

            2,

            '0'

        );

        return ($enteros * 100) + $decimales;

    }

    /**

     * Identifica de forma independiente el inicio

     * del arqueo para cada usuario y jornada.

     */

    private function claveInicio(

        int $usuarioId,

        int $jornadaId

    ): string {

        return 'arqueo_ordinario_inicio_'

            .$usuarioId

            .'_'

            .$jornadaId;

    }

}
