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

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $rolId = $request->integer('rol_id');
        $estado = trim((string) $request->string('estado'));

        $roles = DB::table('roles')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
            ]);

        $usuarios = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where(
                                'u.usuario',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'dp.nombres',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'dp.apellidos',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'rol.nombre',
                                'like',
                                '%' . $buscar . '%'
                            );
                    });
                }
            )
            ->when(
                $rolId > 0,
                fn ($query) => $query->where(
                    'u.rol_id',
                    $rolId
                )
            )
            ->when(
                $estado !== '',
                fn ($query) => $query->where(
                    'u.estado',
                    $estado
                )
            )
            ->select([
                'u.id',
                'u.usuario',
                'u.estado',
                'u.rol_id',
                'rol.nombre as rol_nombre',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->orderBy('u.usuario')
            ->paginate(15)
            ->withQueryString();

        $resumen = (object) [
            'total' =>
                DB::table('usuarios')
                    ->count(),

            'activos' =>
                DB::table('usuarios')
                    ->where('estado', 'ACTIVO')
                    ->count(),

            'inactivos' =>
                DB::table('usuarios')
                    ->where('estado', 'INACTIVO')
                    ->count(),

            'administradores' =>
                DB::table('usuarios as u')
                    ->join(
                        'roles as rol',
                        'rol.id',
                        '=',
                        'u.rol_id'
                    )
                    ->where(
                        'rol.nombre',
                        'Administrador'
                    )
                    ->count(),
        ];

        return view(
            'administrador.usuarios.index',
            compact(
                'usuarios',
                'roles',
                'buscar',
                'rolId',
                'estado',
                'resumen'
            )
        );
    }

    public function create(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $roles = DB::table('roles')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
            ]);

        return view(
            'administrador.usuarios.create',
            compact('roles')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $validated = $request->validate([
            'usuario' => [
                'required',
                'string',
                'max:100',
                Rule::unique('usuarios', 'usuario'),
            ],
            'rol_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
            'estado' => [
                'required',
                Rule::in([
                    'ACTIVO',
                    'INACTIVO',
                ]),
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
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $usuario
        ): void {
            $usuarioId = DB::table('usuarios')
                ->insertGetId([
                    'usuario' => $validated['usuario'],
                    'password' => Hash::make(
                        $validated['password']
                    ),
                    'rol_id' => $validated['rol_id'],
                    'estado' => $validated['estado'],
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

            $nuevos = $this->obtenerValoresAuditoriaUsuario(
                $usuarioId
            );

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Usuarios',
                accion: 'CREAR_USUARIO',
                tablaAfectada: 'usuarios',
                registroId: $usuarioId,
                descripcion:
                    'Se creó la cuenta de usuario '
                    . $validated['usuario']
                    . ' desde el módulo de Administración.',
                valoresAnteriores: null,
                valoresNuevos: $nuevos
            );
        });

        return redirect()
            ->route('administrador.usuarios.index')
            ->with(
                'success',
                'Usuario creado correctamente.'
            );
    }

    public function show(
        Request $request,
        int $usuario
    ): View {
        /** @var Usuario $auth */
        $auth = $request->user();

        $this->validarAdministrador($auth);

        $registro = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->where('u.id', $usuario)
            ->select([
                'u.id',
                'u.usuario',
                'u.estado',
                'u.rol_id',
                'rol.nombre as rol_nombre',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();

        abort_if(
            ! $registro,
            404,
            'El usuario solicitado no existe.'
        );

        return view(
            'administrador.usuarios.show',
            [
                'usuario' => $registro,
            ]
        );
    }

    public function edit(
        Request $request,
        int $usuario
    ): View {
        /** @var Usuario $auth */
        $auth = $request->user();

        $this->validarAdministrador($auth);

        $registro = DB::table('usuarios as u')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->where('u.id', $usuario)
            ->select([
                'u.id',
                'u.usuario',
                'u.estado',
                'u.rol_id',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();

        abort_if(
            ! $registro,
            404,
            'El usuario solicitado no existe.'
        );

        $roles = DB::table('roles')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
            ]);

        return view(
            'administrador.usuarios.edit',
            [
                'usuario' => $registro,
                'roles' => $roles,
            ]
        );
    }

    public function update(
        Request $request,
        int $usuario
    ): RedirectResponse {
        /** @var Usuario $auth */
        $auth = $request->user();

        $this->validarAdministrador($auth);

        abort_unless(
            DB::table('usuarios')
                ->where('id', $usuario)
                ->exists(),
            404,
            'El usuario solicitado no existe.'
        );

        $validated = $request->validate([
            'usuario' => [
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'usuarios',
                    'usuario'
                )->ignore($usuario),
            ],
            'rol_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
            'estado' => [
                'required',
                Rule::in([
                    'ACTIVO',
                    'INACTIVO',
                ]),
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
        ]);

        DB::transaction(function () use (
            $usuario,
            $validated,
            $auth
        ): void {
            $anteriores = $this->obtenerValoresAuditoriaUsuario(
                $usuario
            );

            DB::table('usuarios')
                ->where('id', $usuario)
                ->update([
                    'usuario' => $validated['usuario'],
                    'rol_id' => $validated['rol_id'],
                    'estado' => $validated['estado'],
                    'updated_at' => now(),
                ]);

            DB::table('datos_personales')
                ->updateOrInsert(
                    [
                        'usuario_id' => $usuario,
                    ],
                    [
                        'nombres' => $validated['nombres'],
                        'apellidos' => $validated['apellidos'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

            $nuevos = $this->obtenerValoresAuditoriaUsuario(
                $usuario
            );

            app(AuditoriaService::class)->registrar(
                usuario: $auth,
                modulo: 'Usuarios',
                accion: 'EDITAR_USUARIO',
                tablaAfectada: 'usuarios',
                registroId: $usuario,
                descripcion:
                    'Se actualizaron los datos generales, rol '
                    . 'o estado de la cuenta '
                    . ($nuevos['usuario'] ?? ('#' . $usuario))
                    . '.',
                valoresAnteriores: $anteriores,
                valoresNuevos: $nuevos
            );
        });

        return redirect()
            ->route(
                'administrador.usuarios.show',
                $usuario
            )
            ->with(
                'success',
                'Usuario actualizado correctamente.'
            );
    }

    public function cambiarEstado(
        Request $request,
        int $usuario
    ): RedirectResponse {
        /** @var Usuario $auth */
        $auth = $request->user();

        $this->validarAdministrador($auth);

        $registro = DB::table('usuarios')
            ->where('id', $usuario)
            ->first([
                'id',
                'estado',
            ]);

        abort_if(
            ! $registro,
            404,
            'El usuario solicitado no existe.'
        );

        abort_if(
            (int) $auth->id === (int) $usuario,
            422,
            'No puede desactivar su propia cuenta.'
        );

        $nuevoEstado =
            $registro->estado === 'ACTIVO'
                ? 'INACTIVO'
                : 'ACTIVO';

        DB::transaction(function () use (
            $usuario,
            $nuevoEstado,
            $auth
        ): void {
            $anteriores = $this->obtenerValoresAuditoriaUsuario(
                $usuario
            );

            DB::table('usuarios')
                ->where('id', $usuario)
                ->update([
                    'estado' => $nuevoEstado,
                    'updated_at' => now(),
                ]);

            $nuevos = $this->obtenerValoresAuditoriaUsuario(
                $usuario
            );

            $accion = $nuevoEstado === 'ACTIVO'
                ? 'ACTIVAR_USUARIO'
                : 'DESACTIVAR_USUARIO';

            app(AuditoriaService::class)->registrar(
                usuario: $auth,
                modulo: 'Usuarios',
                accion: $accion,
                tablaAfectada: 'usuarios',
                registroId: $usuario,
                descripcion:
                    'Se '
                    . ($nuevoEstado === 'ACTIVO'
                        ? 'activó'
                        : 'desactivó')
                    . ' la cuenta '
                    . ($nuevos['usuario'] ?? ('#' . $usuario))
                    . '.',
                valoresAnteriores: $anteriores,
                valoresNuevos: $nuevos
            );
        });

        return back()->with(
            'success',
            'Estado del usuario actualizado correctamente.'
        );
    }

    public function actualizarPassword(
        Request $request,
        int $usuario
    ): RedirectResponse {
        /** @var Usuario $auth */
        $auth = $request->user();

        $this->validarAdministrador($auth);

        abort_unless(
            DB::table('usuarios')
                ->where('id', $usuario)
                ->exists(),
            404,
            'El usuario solicitado no existe.'
        );

        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        DB::transaction(function () use (
            $usuario,
            $validated,
            $auth
        ): void {
            $registroObjetivo = DB::table('usuarios')
                ->where('id', $usuario)
                ->first([
                    'id',
                    'usuario',
                ]);

            DB::table('usuarios')
                ->where('id', $usuario)
                ->update([
                    'password' => Hash::make(
                        $validated['password']
                    ),
                    'updated_at' => now(),
                ]);

            app(AuditoriaService::class)->registrar(
                usuario: $auth,
                modulo: 'Usuarios',
                accion: 'RESTABLECER_PASSWORD',
                tablaAfectada: 'usuarios',
                registroId: $usuario,
                descripcion:
                    'El Administrador restableció la contraseña '
                    . 'de la cuenta '
                    . ($registroObjetivo->usuario ?? ('#' . $usuario))
                    . '.',
                valoresAnteriores: null,
                valoresNuevos: [
                    'password_restablecida' => true,
                ]
            );
        });

        return back()->with(
            'success',
            'Contraseña actualizada correctamente.'
        );
    }

    private function obtenerValoresAuditoriaUsuario(
        int $usuarioId
    ): array {
        $registro = DB::table('usuarios as u')
            ->join(
                'roles as rol',
                'rol.id',
                '=',
                'u.rol_id'
            )
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->where('u.id', $usuarioId)
            ->select([
                'u.id',
                'u.usuario',
                'u.rol_id',
                'rol.nombre as rol',
                'u.estado',
                'u.requiere_cambio_password',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();

        if (! $registro) {
            return [];
        }

        return [
            'id' => (int) $registro->id,
            'usuario' => $registro->usuario,
            'rol_id' => (int) $registro->rol_id,
            'rol' => $registro->rol,
            'estado' => $registro->estado,
            'requiere_cambio_password' =>
                (bool) $registro->requiere_cambio_password,
            'nombres' => $registro->nombres,
            'apellidos' => $registro->apellidos,
        ];
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
