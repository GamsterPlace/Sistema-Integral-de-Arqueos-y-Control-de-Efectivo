<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ArqueoGeneralController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $agenteId = $request->integer('agente_id');
        $promotorId = $request->integer('promotor_id');
        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');
        $tipo = trim((string) $request->string('tipo'));
        $estado = trim((string) $request->string('estado'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $agentes = DB::table('agentes')
            ->orderBy('nombre_negocio')
            ->get(['id', 'codigo_agente', 'nombre_negocio']);

        $promotores = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('rol.nombre', 'Promotor')
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->get([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ]);

        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->when(
                $regionId > 0,
                fn ($q) => $q->where('r.region_id', $regionId)
            )
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.region_id',
                'reg.nombre as region_nombre',
            ]);

        $arqueos = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->leftJoin('usuarios as uc', 'uc.id', '=', 'arq.creado_por')
            ->leftJoin(
                'datos_personales as dpc',
                'dpc.usuario_id',
                '=',
                'uc.id'
            )
            ->when(
                $buscar !== '',
                function ($q) use ($buscar): void {
                    $q->where(
                        function ($s) use ($buscar): void {
                            $s->where(
                                'arq.numero_arqueo',
                                'like',
                                '%' . $buscar . '%'
                            )
                                ->orWhere(
                                    'a.codigo_agente',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'a.nombre_negocio',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'a.nombre_propietario',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'uc.usuario',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'dpc.nombres',
                                    'like',
                                    '%' . $buscar . '%'
                                )
                                ->orWhere(
                                    'dpc.apellidos',
                                    'like',
                                    '%' . $buscar . '%'
                                );
                        }
                    );
                }
            )
            ->when(
                $agenteId > 0,
                fn ($q) => $q->where(
                    'arq.agente_id',
                    $agenteId
                )
            )
            ->when(
                $promotorId > 0,
                fn ($q) => $q->where(
                    'arq.creado_por',
                    $promotorId
                )
            )
            ->when(
                $regionId > 0,
                fn ($q) => $q->where(
                    'reg.id',
                    $regionId
                )
            )
            ->when(
                $rutaId > 0,
                fn ($q) => $q->where(
                    'r.id',
                    $rutaId
                )
            )
            ->when(
                $tipo !== '',
                fn ($q) => $q->where(
                    'arq.tipo',
                    $tipo
                )
            )
            ->when(
                $estado !== '',
                fn ($q) => $q->where(
                    'arq.estado',
                    $estado
                )
            )
            ->when(
                $desde,
                fn ($q) => $q->whereDate(
                    'arq.fecha_arqueo',
                    '>=',
                    $desde
                )
            )
            ->when(
                $hasta,
                fn ($q) => $q->whereDate(
                    'arq.fecha_arqueo',
                    '<=',
                    $hasta
                )
            )
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'arq.estado',
                'arq.fuera_fecha_ordinaria',
                'arq.total_arqueado',
                'arq.saldo_sistema',
                'arq.diferencia',
                'arq.creado_por',
                'a.id as agente_id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'r.id as ruta_id',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.id as region_id',
                'reg.nombre as region_nombre',
                'uc.usuario as creador_usuario',
                'dpc.nombres as creador_nombres',
                'dpc.apellidos as creador_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(20)
            ->withQueryString();

        $totalArqueos = DB::table('arqueos')
            ->count();

        $arqueosHoy = DB::table('arqueos')
            ->whereDate('fecha_arqueo', today())
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $pendientes = DB::table('arqueos')
            ->where('estado', 'PENDIENTE_CERTIFICACION')
            ->count();

        $certificados = DB::table('arqueos')
            ->where('estado', 'CERTIFICADO')
            ->count();

        $anulados = DB::table('arqueos')
            ->where('estado', 'ANULADO')
            ->count();

        $extemporaneos = DB::table('arqueos')
            ->where('fuera_fecha_ordinaria', true)
            ->where('estado', '!=', 'ANULADO')
            ->count();

        return view(
            'jefe.arqueos.index',
            compact(
                'arqueos',
                'agentes',
                'promotores',
                'regiones',
                'rutas',
                'buscar',
                'agenteId',
                'promotorId',
                'regionId',
                'rutaId',
                'tipo',
                'estado',
                'desde',
                'hasta',
                'totalArqueos',
                'arqueosHoy',
                'pendientes',
                'certificados',
                'anulados',
                'extemporaneos'
            )
        );
    }

    public function show(
        Request $request,
        Arqueo $arqueo
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

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

        $creador = DB::table('usuarios as u')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->leftJoin(
                'roles as rol',
                'rol.id',
                '=',
                'u.rol_id'
            )
            ->where(
                'u.id',
                $arqueo->creado_por
            )
            ->select([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
                'rol.nombre as rol_nombre',
            ])
            ->first();

        $puedeAnular = in_array(
            $arqueo->estado,
            [
                'PENDIENTE_CERTIFICACION',
                'CERTIFICADO',
            ],
            true
        );

        return view(
            'jefe.arqueos.show',
            compact(
                'arqueo',
                'billetes',
                'monedas',
                'creador',
                'puedeAnular'
            )
        );
    }

    public function imprimir(
        Request $request,
        Arqueo $arqueo
    ): Response {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

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
            'jefe.arqueos.pdf',
            compact(
                'arqueo',
                'billetes',
                'monedas'
            )
        )->setPaper(
            'letter',
            'portrait'
        );

        return $pdf->stream(
            $arqueo->numero_arqueo . '.pdf'
        );
    }

    public function anular(
        Request $request,
        Arqueo $arqueo
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

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
                'motivo_anulacion.required' =>
                    'Debe indicar el motivo de la anulación.',
                'motivo_anulacion.min' =>
                    'El motivo debe contener al menos 10 caracteres.',
                'motivo_anulacion.max' =>
                    'El motivo no puede superar los 500 caracteres.',
                'password.required' =>
                    'Debe ingresar su contraseña para confirmar.',
            ]
        );

        if (! Hash::check(
            $datosValidados['password'],
            $usuario->password
        )) {
            return back()
                ->withErrors([
                    'password' =>
                        'La contraseña ingresada es incorrecta.',
                ])
                ->withInput();
        }

        return DB::transaction(
            function () use (
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
                    $arqueoBloqueado->estado === 'BORRADOR',
                    422,
                    'Un arqueo en borrador no puede ser anulado por el Jefe de Agentes.'
                );

                abort_unless(
                    in_array(
                        $arqueoBloqueado->estado,
                        [
                            'PENDIENTE_CERTIFICACION',
                            'CERTIFICADO',
                        ],
                        true
                    ),
                    422,
                    'El arqueo no se encuentra disponible para anulación.'
                );

                /*
                |--------------------------------------------------------------------------
                | Estado anterior
                |--------------------------------------------------------------------------
                */

                $valoresAnteriores = [
                    'id' =>
                        (int) $arqueoBloqueado->id,

                    'numero_arqueo' =>
                        $arqueoBloqueado->numero_arqueo,

                    'agente_id' =>
                        (int) $arqueoBloqueado->agente_id,

                    'tipo' =>
                        $arqueoBloqueado->tipo,

                    'estado' =>
                        $arqueoBloqueado->estado,

                    'fecha_arqueo' =>
                        $arqueoBloqueado->fecha_arqueo,

                    'total_arqueado' =>
                        $arqueoBloqueado->total_arqueado,

                    'saldo_sistema' =>
                        $arqueoBloqueado->saldo_sistema,

                    'diferencia' =>
                        $arqueoBloqueado->diferencia,

                    'observaciones' =>
                        $arqueoBloqueado->observaciones,

                    'anulado_at' =>
                        $arqueoBloqueado->anulado_at,
                ];

                $usuario->loadMissing(
                    'datosPersonales'
                );

                $datosPersonales =
                    $usuario->datosPersonales;

                $nombreJefe = trim(
                    (string) (
                        $datosPersonales?->nombres
                        ?? $datosPersonales?->nombre
                        ?? ''
                    )
                    . ' '
                    . (string) (
                        $datosPersonales?->apellidos
                        ?? $datosPersonales?->apellido
                        ?? ''
                    )
                );

                if ($nombreJefe === '') {
                    $nombreJefe =
                        $usuario->usuario
                        ?? 'Jefe de Agentes';
                }

                $motivo = trim(
                    $datosValidados[
                        'motivo_anulacion'
                    ]
                );

                $observacionAnterior = trim(
                    (string)
                    $arqueoBloqueado->observaciones
                );

                $registroAnulacion = sprintf(
                    '[ANULACIÓN %s | Jefe de Agentes: %s | Usuario ID: %d] %s',
                    now()->format(
                        'd/m/Y H:i:s'
                    ),
                    $nombreJefe,
                    $usuario->id,
                    $motivo
                );

                /*
                |--------------------------------------------------------------------------
                | Anulación
                |--------------------------------------------------------------------------
                */

                $arqueoBloqueado->update([
                    'estado' =>
                        'ANULADO',

                    'anulado_at' =>
                        now(),

                    'observaciones' =>
                        $observacionAnterior !== ''
                            ? $observacionAnterior
                                . PHP_EOL
                                . PHP_EOL
                                . $registroAnulacion
                            : $registroAnulacion,
                ]);

                $arqueoBloqueado->refresh();

                /*
                |--------------------------------------------------------------------------
                | Estado nuevo
                |--------------------------------------------------------------------------
                */

                $valoresNuevos = [
                    'id' =>
                        (int) $arqueoBloqueado->id,

                    'numero_arqueo' =>
                        $arqueoBloqueado->numero_arqueo,

                    'agente_id' =>
                        (int) $arqueoBloqueado->agente_id,

                    'tipo' =>
                        $arqueoBloqueado->tipo,

                    'estado' =>
                        $arqueoBloqueado->estado,

                    'fecha_arqueo' =>
                        $arqueoBloqueado->fecha_arqueo,

                    'total_arqueado' =>
                        $arqueoBloqueado->total_arqueado,

                    'saldo_sistema' =>
                        $arqueoBloqueado->saldo_sistema,

                    'diferencia' =>
                        $arqueoBloqueado->diferencia,

                    'observaciones' =>
                        $arqueoBloqueado->observaciones,

                    'anulado_at' =>
                        $arqueoBloqueado->anulado_at,

                    'motivo_anulacion' =>
                        $motivo,

                    'anulado_por_usuario_id' =>
                        (int) $usuario->id,

                    'anulado_por' =>
                        $nombreJefe,
                ];

                /*
                |--------------------------------------------------------------------------
                | Auditoría automática
                |--------------------------------------------------------------------------
                */

                app(AuditoriaService::class)
                    ->registrar(
                        usuario: $usuario,
                        modulo: 'Arqueos',
                        accion: 'ANULAR_ARQUEO',
                        tablaAfectada: 'arqueos',
                        registroId:
                            $arqueoBloqueado->id,
                        descripcion:
                            'El Jefe de Agentes '
                            . $nombreJefe
                            . ' anuló el arqueo '
                            . $arqueoBloqueado
                                ->numero_arqueo
                            . '. Motivo: '
                            . $motivo,
                        valoresAnteriores:
                            $valoresAnteriores,
                        valoresNuevos:
                            $valoresNuevos
                    );

                return redirect()
                    ->route(
                        'jefe.arqueos.show',
                        $arqueoBloqueado
                    )
                    ->with(
                        'success',
                        'El arqueo fue anulado correctamente.'
                    );
            }
        );
    }

    private function validarJefe(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre
                !== 'jefedeAgentes',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
