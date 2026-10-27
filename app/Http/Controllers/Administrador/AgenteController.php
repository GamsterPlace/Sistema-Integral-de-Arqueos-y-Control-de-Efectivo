<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgenteController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $estado = trim((string) $request->string('estado'));
        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');

        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
            ]);

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('r.region_id', $regionId)
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

        $agentes = DB::table('agentes as a')
            ->join('usuarios as u', 'u.id', '=', 'a.usuario_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where('a.codigo_agente', 'like', '%' . $buscar . '%')
                            ->orWhere('a.nombre_negocio', 'like', '%' . $buscar . '%')
                            ->orWhere('a.nombre_propietario', 'like', '%' . $buscar . '%')
                            ->orWhere('a.direccion', 'like', '%' . $buscar . '%')
                            ->orWhere('u.usuario', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.nombres', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.apellidos', 'like', '%' . $buscar . '%');
                    });
                }
            )
            ->when(
                $estado !== '',
                fn ($query) => $query->where('a.estado', $estado)
            )
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('reg.id', $regionId)
            )
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where('r.id', $rutaId)
            )
            ->select([
                'a.id',
                'a.usuario_id',
                'a.ruta_id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'a.estado',
                'u.usuario',
                'u.estado as usuario_estado',
                'dp.nombres',
                'dp.apellidos',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->orderBy('a.nombre_negocio')
            ->paginate(15)
            ->withQueryString();

        $resumen = (object) [
            'total' => DB::table('agentes')->count(),

            'activos' => DB::table('agentes')
                ->where('estado', 'ACTIVO')
                ->count(),

            'inactivos' => DB::table('agentes')
                ->where('estado', 'INACTIVO')
                ->count(),

            'sin_usuario_activo' => DB::table('agentes as a')
                ->join('usuarios as u', 'u.id', '=', 'a.usuario_id')
                ->where('u.estado', '!=', 'ACTIVO')
                ->count(),
        ];

        return view(
            'administrador.agentes.index',
            compact(
                'agentes',
                'regiones',
                'rutas',
                'buscar',
                'estado',
                'regionId',
                'rutaId',
                'resumen'
            )
        );
    }

    public function create(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('r.estado', true)
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get([
                'r.id',
                'r.codigo',
                'r.nombre',
                'reg.nombre as region_nombre',
            ]);

        return view(
            'administrador.agentes.create',
            compact('rutas')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $validated = $request->validate([
            'codigo_agente' => [
                'required',
                'string',
                'max:50',
                Rule::unique('agentes', 'codigo_agente'),
            ],
            'nombre_negocio' => [
                'required',
                'string',
                'max:180',
            ],
            'nombre_propietario' => [
                'required',
                'string',
                'max:180',
            ],
            'direccion' => [
                'required',
                'string',
                'max:255',
            ],
            'ruta_id' => [
                'required',
                'integer',
                Rule::exists('rutas', 'id'),
            ],
            'nombres' => [
                'required',
                'string',
                'max:150',
            ],
            'apellidos' => [
                'required',
                'string',
                'max:150',
            ],
            'usuario' => [
                'required',
                'string',
                'max:100',
                Rule::unique('usuarios', 'usuario'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $rolAgente = DB::table('roles')
            ->where('nombre', 'agente')
            ->first([
                'id',
            ]);

        abort_if(
            ! $rolAgente,
            422,
            'No existe el rol agente en la tabla roles.'
        );

        $agenteId = DB::transaction(
            function () use (
                $validated,
                $rolAgente,
                $usuario
            ): int {
                $usuarioId = DB::table('usuarios')
                    ->insertGetId([
                        'usuario' => $validated['usuario'],
                        'password' => Hash::make($validated['password']),
                        'rol_id' => $rolAgente->id,
                        'estado' => 'ACTIVO',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                DB::table('datos_personales')
                    ->insert([
                        'usuario_id' => $usuarioId,
                        'nombres' => $validated['nombres'],
                        'apellidos' => $validated['apellidos'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                $agenteId = DB::table('agentes')
                    ->insertGetId([
                        'usuario_id' => $usuarioId,
                        'ruta_id' => $validated['ruta_id'],
                        'codigo_agente' => $validated['codigo_agente'],
                        'nombre_negocio' => $validated['nombre_negocio'],
                        'nombre_propietario' => $validated['nombre_propietario'],
                        'direccion' => $validated['direccion'],
                        'estado' => 'ACTIVO',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                $nuevos = $this->obtenerValoresAuditoriaAgente(
                    $agenteId
                );

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Agentes',
                    accion: 'CREAR_AGENTE',
                    tablaAfectada: 'agentes',
                    registroId: $agenteId,
                    descripcion:
                        'Se creó el Agente '
                        . $validated['codigo_agente']
                        . ' — '
                        . $validated['nombre_negocio']
                        . ' y su cuenta de acceso.',
                    valoresAnteriores: null,
                    valoresNuevos: $nuevos
                );

                return $agenteId;
            }
        );

        return redirect()
            ->route(
                'administrador.agentes.show',
                $agenteId
            )
            ->with(
                'success',
                'Agente y cuenta de acceso creados correctamente.'
            );
    }

    public function show(
        Request $request,
        int $agente
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $registro = $this->buscarAgente($agente);

        abort_if(
            ! $registro,
            404,
            'El agente solicitado no existe.'
        );

        $resumen = (object) [
            'total_arqueos' => DB::table('arqueos')
                ->where('agente_id', $agente)
                ->count(),

            'arqueos_validos' => DB::table('arqueos')
                ->where('agente_id', $agente)
                ->where('estado', '!=', 'ANULADO')
                ->count(),

            'anulados' => DB::table('arqueos')
                ->where('agente_id', $agente)
                ->where('estado', 'ANULADO')
                ->count(),

            'ultimo_arqueo' => DB::table('arqueos')
                ->where('agente_id', $agente)
                ->where('estado', '!=', 'ANULADO')
                ->orderByDesc('fecha_arqueo')
                ->orderByDesc('id')
                ->value('fecha_arqueo'),
        ];

        return view(
            'administrador.agentes.show',
            [
                'agente' => $registro,
                'resumen' => $resumen,
            ]
        );
    }

    public function edit(
        Request $request,
        int $agente
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $registro = $this->buscarAgente($agente);

        abort_if(
            ! $registro,
            404,
            'El agente solicitado no existe.'
        );

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.estado',
                'reg.nombre as region_nombre',
            ]);

        return view(
            'administrador.agentes.edit',
            [
                'agente' => $registro,
                'rutas' => $rutas,
            ]
        );
    }

    public function update(
        Request $request,
        int $agente
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $registro = $this->buscarAgente($agente);

        abort_if(
            ! $registro,
            404,
            'El agente solicitado no existe.'
        );

        $validated = $request->validate([
            'codigo_agente' => [
                'required',
                'string',
                'max:50',
                Rule::unique(
                    'agentes',
                    'codigo_agente'
                )->ignore($agente),
            ],
            'nombre_negocio' => [
                'required',
                'string',
                'max:180',
            ],
            'nombre_propietario' => [
                'required',
                'string',
                'max:180',
            ],
            'direccion' => [
                'required',
                'string',
                'max:255',
            ],
            'ruta_id' => [
                'required',
                'integer',
                Rule::exists('rutas', 'id'),
            ],
            'nombres' => [
                'required',
                'string',
                'max:150',
            ],
            'apellidos' => [
                'required',
                'string',
                'max:150',
            ],
            'usuario' => [
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'usuarios',
                    'usuario'
                )->ignore($registro->usuario_id),
            ],
        ]);

        DB::transaction(
            function () use (
                $registro,
                $agente,
                $validated,
                $usuario
            ): void {
                $anteriores = $this->obtenerValoresAuditoriaAgente(
                    $agente
                );

                DB::table('usuarios')
                    ->where('id', $registro->usuario_id)
                    ->update([
                        'usuario' => $validated['usuario'],
                        'updated_at' => now(),
                    ]);

                DB::table('datos_personales')
                    ->updateOrInsert(
                        [
                            'usuario_id' => $registro->usuario_id,
                        ],
                        [
                            'nombres' => $validated['nombres'],
                            'apellidos' => $validated['apellidos'],
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                DB::table('agentes')
                    ->where('id', $agente)
                    ->update([
                        'ruta_id' => $validated['ruta_id'],
                        'codigo_agente' => $validated['codigo_agente'],
                        'nombre_negocio' => $validated['nombre_negocio'],
                        'nombre_propietario' => $validated['nombre_propietario'],
                        'direccion' => $validated['direccion'],
                        'updated_at' => now(),
                    ]);

                $nuevos = $this->obtenerValoresAuditoriaAgente(
                    $agente
                );

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Agentes',
                    accion: 'EDITAR_AGENTE',
                    tablaAfectada: 'agentes',
                    registroId: $agente,
                    descripcion:
                        'Se actualizaron los datos del Agente '
                        . ($nuevos['codigo_agente'] ?? ('#' . $agente))
                        . ' — '
                        . ($nuevos['nombre_negocio'] ?? 'Sin nombre')
                        . '.',
                    valoresAnteriores: $anteriores,
                    valoresNuevos: $nuevos
                );

                if (
                    (int) ($anteriores['ruta_id'] ?? 0)
                    !== (int) ($nuevos['ruta_id'] ?? 0)
                ) {
                    app(AuditoriaService::class)->registrar(
                        usuario: $usuario,
                        modulo: 'Agentes',
                        accion: 'CAMBIAR_RUTA_AGENTE',
                        tablaAfectada: 'agentes',
                        registroId: $agente,
                        descripcion:
                            'Se cambió la ruta asignada al Agente '
                            . ($nuevos['codigo_agente'] ?? ('#' . $agente))
                            . ' de '
                            . ($anteriores['ruta_codigo'] ?? 'Sin ruta')
                            . ' — '
                            . ($anteriores['ruta_nombre'] ?? 'Sin nombre')
                            . ' a '
                            . ($nuevos['ruta_codigo'] ?? 'Sin ruta')
                            . ' — '
                            . ($nuevos['ruta_nombre'] ?? 'Sin nombre')
                            . '.',
                        valoresAnteriores: [
                            'ruta_id' => $anteriores['ruta_id'] ?? null,
                            'ruta_codigo' => $anteriores['ruta_codigo'] ?? null,
                            'ruta_nombre' => $anteriores['ruta_nombre'] ?? null,
                            'region_id' => $anteriores['region_id'] ?? null,
                            'region_nombre' => $anteriores['region_nombre'] ?? null,
                        ],
                        valoresNuevos: [
                            'ruta_id' => $nuevos['ruta_id'] ?? null,
                            'ruta_codigo' => $nuevos['ruta_codigo'] ?? null,
                            'ruta_nombre' => $nuevos['ruta_nombre'] ?? null,
                            'region_id' => $nuevos['region_id'] ?? null,
                            'region_nombre' => $nuevos['region_nombre'] ?? null,
                        ]
                    );
                }
            }
        );

        return redirect()
            ->route(
                'administrador.agentes.show',
                $agente
            )
            ->with(
                'success',
                'Información del agente actualizada correctamente.'
            );
    }

    public function cambiarEstado(
        Request $request,
        int $agente
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $registro = DB::table('agentes')
            ->where('id', $agente)
            ->first([
                'id',
                'usuario_id',
                'estado',
            ]);

        abort_if(
            ! $registro,
            404,
            'El agente solicitado no existe.'
        );

        $nuevoEstado =
            $registro->estado === 'ACTIVO'
                ? 'INACTIVO'
                : 'ACTIVO';

        DB::transaction(
            function () use (
                $registro,
                $agente,
                $nuevoEstado,
                $usuario
            ): void {
                $anteriores = $this->obtenerValoresAuditoriaAgente(
                    $agente
                );

                DB::table('agentes')
                    ->where('id', $agente)
                    ->update([
                        'estado' => $nuevoEstado,
                        'updated_at' => now(),
                    ]);

                DB::table('usuarios')
                    ->where('id', $registro->usuario_id)
                    ->update([
                        'estado' => $nuevoEstado,
                        'updated_at' => now(),
                    ]);

                $nuevos = $this->obtenerValoresAuditoriaAgente(
                    $agente
                );

                $accion = $nuevoEstado === 'ACTIVO'
                    ? 'ACTIVAR_AGENTE'
                    : 'DESACTIVAR_AGENTE';

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Agentes',
                    accion: $accion,
                    tablaAfectada: 'agentes',
                    registroId: $agente,
                    descripcion:
                        'Se '
                        . ($nuevoEstado === 'ACTIVO'
                            ? 'activó'
                            : 'desactivó')
                        . ' el Agente '
                        . ($nuevos['codigo_agente'] ?? ('#' . $agente))
                        . ' y su cuenta de acceso.',
                    valoresAnteriores: [
                        'estado_agente' =>
                            $anteriores['estado_agente'] ?? null,
                        'estado_usuario' =>
                            $anteriores['estado_usuario'] ?? null,
                    ],
                    valoresNuevos: [
                        'estado_agente' =>
                            $nuevos['estado_agente'] ?? null,
                        'estado_usuario' =>
                            $nuevos['estado_usuario'] ?? null,
                    ]
                );
            }
        );

        return back()->with(
            'success',
            'Estado del agente y su cuenta actualizado correctamente.'
        );
    }

    private function obtenerValoresAuditoriaAgente(
        int $agenteId
    ): array {
        $registro = DB::table('agentes as a')
            ->join(
                'usuarios as u',
                'u.id',
                '=',
                'a.usuario_id'
            )
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->join(
                'rutas as r',
                'r.id',
                '=',
                'a.ruta_id'
            )
            ->join(
                'regiones as reg',
                'reg.id',
                '=',
                'r.region_id'
            )
            ->where('a.id', $agenteId)
            ->select([
                'a.id',
                'a.usuario_id',
                'a.ruta_id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'a.estado as estado_agente',
                'u.usuario',
                'u.estado as estado_usuario',
                'dp.nombres',
                'dp.apellidos',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.id as region_id',
                'reg.nombre as region_nombre',
            ])
            ->first();

        if (! $registro) {
            return [];
        }

        return [
            'id' => (int) $registro->id,
            'usuario_id' => (int) $registro->usuario_id,
            'usuario' => $registro->usuario,
            'nombres' => $registro->nombres,
            'apellidos' => $registro->apellidos,
            'codigo_agente' => $registro->codigo_agente,
            'nombre_negocio' => $registro->nombre_negocio,
            'nombre_propietario' => $registro->nombre_propietario,
            'direccion' => $registro->direccion,
            'estado_agente' => $registro->estado_agente,
            'estado_usuario' => $registro->estado_usuario,
            'ruta_id' => (int) $registro->ruta_id,
            'ruta_codigo' => $registro->ruta_codigo,
            'ruta_nombre' => $registro->ruta_nombre,
            'region_id' => (int) $registro->region_id,
            'region_nombre' => $registro->region_nombre,
        ];
    }

    private function buscarAgente(int $agente): ?object
    {
        return DB::table('agentes as a')
            ->join('usuarios as u', 'u.id', '=', 'a.usuario_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('a.id', $agente)
            ->select([
                'a.id',
                'a.usuario_id',
                'a.ruta_id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'a.estado',
                'u.usuario',
                'u.estado as usuario_estado',
                'dp.nombres',
                'dp.apellidos',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.id as region_id',
                'reg.nombre as region_nombre',
            ])
            ->first();
    }

    private function validarAdministrador(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Administrador',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
