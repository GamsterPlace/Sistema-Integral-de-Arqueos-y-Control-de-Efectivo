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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    private const CARGOS_AGENCIA = [
        'RECEPTOR' => 'Receptor',
        'JEFE_CAJA' => 'JefeCaja',
        'SUBJEFE_CAJA' => 'SubjefeCaja',
        'JEFE_AGENCIA' => 'JefeAgencia',
    ];

    private const ROLES_AGENTES = [
        'AGENTE', 'PROMOTOR', 'JEFEDEAGENTES',
    ];

    private const ROLES_AGENCIAS = [
        'RECEPTOR', 'JEFECAJA', 'SUBJEFECAJA',
        'JEFEAGENCIA',
    ];

    private const ROLES_INSTITUCIONALES = [
        'ADMINISTRADOR', 'AUDITORIA', 'GERENCIA',
    ];

    private function autorizar(Request $request): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        abort_unless($usuario, 401);

        $usuario->loadMissing('rol');

        abort_unless(
            $usuario->rol?->nombre === 'Administrador',
            403,
            'No tiene autorización para acceder a esta sección.'
        );

        return $usuario;
    }

    private function roles()
    {
        return DB::table('roles')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function agencias()
    {
        return DB::table('agencias')
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre']);
    }

    private function rolNombre(int $rolId): string
    {
        return (string) DB::table('roles')
            ->where('id', $rolId)
            ->value('nombre');
    }

    private function cargoParaRol(int $rolId): ?string
    {
        $nombre = strtoupper(trim($this->rolNombre($rolId)));

        foreach (self::CARGOS_AGENCIA as $cargo => $rol) {
            if (strtoupper($rol) === $nombre) {
                return $cargo;
            }
        }

        return null;
    }

    private function validarDatos(
        Request $request,
        ?int $usuarioId = null
    ): array {
        $reglas = [
            'usuario' => [
                'required', 'string', 'max:50',
                Rule::unique('usuarios', 'usuario')
                    ->ignore($usuarioId),
            ],
            'rol_id' => [
                'required', 'integer',
                Rule::exists('roles', 'id'),
            ],
            'estado' => [
                'required',
                Rule::in(['ACTIVO', 'INACTIVO', 'BLOQUEADO']),
            ],
            'nombres' => [
                'required', 'string', 'max:150',
            ],
            'apellidos' => [
                'required', 'string', 'max:150',
            ],
            'agencia_id' => [
                'nullable', 'integer',
                Rule::exists('agencias', 'id'),
            ],
            'tipo_asignacion' => [
                'nullable',
                Rule::in(['PERMANENTE', 'TEMPORAL']),
            ],
            'fecha_inicio' => [
                'nullable', 'date_format:Y-m-d',
            ],
            'fecha_fin' => [
                'nullable', 'date_format:Y-m-d',
                'after_or_equal:fecha_inicio',
            ],
            'motivo_asignacion' => [
                'nullable', 'string', 'max:2000',
            ],
        ];

        if ($usuarioId === null) {
            $reglas['password'] = [
                'required', 'string', 'min:8', 'confirmed',
            ];
        }

        $datos = $request->validate($reglas);

        $cargo = $this->cargoParaRol((int) $datos['rol_id']);

        if ($cargo !== null && $usuarioId === null) {
            foreach (['agencia_id', 'tipo_asignacion', 'fecha_inicio'] as $campo) {
                if (empty($datos[$campo])) {
                    throw ValidationException::withMessages([
                        $campo => 'Este campo es obligatorio para personal de agencia.',
                    ]);
                }
            }
        }

        if (
            ($datos['tipo_asignacion'] ?? null) === 'TEMPORAL'
            && !empty($datos['agencia_id'])
            && empty($datos['fecha_fin'])
        ) {
            throw ValidationException::withMessages([
                'fecha_fin' => 'La asignación temporal requiere fecha de finalización.',
            ]);
        }

        if (
            $cargo === null
            && !empty($datos['agencia_id'])
        ) {
            throw ValidationException::withMessages([
                'agencia_id' => 'El rol seleccionado no corresponde a personal de agencia.',
            ]);
        }

        return $datos;
    }

    public function index(Request $request): View
    {
        $this->autorizar($request);

        $buscar = trim((string) $request->input('buscar', ''));
        $rolId = $request->integer('rol_id');
        $estado = trim((string) $request->input('estado', ''));
        $modulo = strtolower(trim((string) $request->input('modulo', 'todos')));
        $agenciaId = $request->integer('agencia_id');

        $rolesModulo = match ($modulo) {
            'agentes' => self::ROLES_AGENTES,
            'agencias' => self::ROLES_AGENCIAS,
            'institucional' => self::ROLES_INSTITUCIONALES,
            default => null,
        };

        $usuarios = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('u.usuario', 'like', "%{$buscar}%")
                        ->orWhere('dp.nombres', 'like', "%{$buscar}%")
                        ->orWhere('dp.apellidos', 'like', "%{$buscar}%")
                        ->orWhere('rol.nombre', 'like', "%{$buscar}%");
                });
            })
            ->when($rolId > 0, fn ($q) =>
                $q->where('u.rol_id', $rolId))
            ->when($estado !== '', fn ($q) =>
                $q->where('u.estado', $estado))
            ->when($rolesModulo !== null, fn ($q) =>
                $q->whereIn(
                    DB::raw('UPPER(TRIM(rol.nombre))'),
                    $rolesModulo
                ))
            ->when($agenciaId > 0, function ($q) use ($agenciaId) {
                $q->whereExists(function ($sub) use ($agenciaId) {
                    $sub->selectRaw('1')
                        ->from('asignaciones_personal_agencia as apa')
                        ->whereColumn('apa.usuario_id', 'u.id')
                        ->where('apa.agencia_id', $agenciaId);
                });
            })
            ->select([
                'u.id', 'u.usuario', 'u.estado', 'u.rol_id',
                'rol.nombre as rol_nombre',
                'dp.nombres', 'dp.apellidos',
            ])
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->orderBy('u.usuario')
            ->paginate(15)
            ->withQueryString();

        $resumen = (object) [
            'total' => DB::table('usuarios')->count(),
            'activos' => DB::table('usuarios')
                ->where('estado', 'ACTIVO')->count(),
            'inactivos' => DB::table('usuarios')
                ->where('estado', 'INACTIVO')->count(),
            'administradores' => DB::table('usuarios as u')
                ->join('roles as r', 'r.id', '=', 'u.rol_id')
                ->where('r.nombre', 'Administrador')
                ->count(),
        ];

        $roles = $this->roles();
        $agencias = $this->agencias();

        return view('administrador.usuarios.index', compact(
            'usuarios', 'roles', 'agencias', 'buscar',
            'rolId', 'estado', 'modulo', 'agenciaId', 'resumen'
        ));
    }

    public function create(Request $request): View
    {
        $this->autorizar($request);

        $roles = $this->roles();
        $agencias = $this->agencias();

        return view('administrador.usuarios.create', compact(
            'roles', 'agencias'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $administrador = $this->autorizar($request);
        $datos = $this->validarDatos($request);

        DB::transaction(function () use ($datos, $administrador) {
            $usuarioId = DB::table('usuarios')->insertGetId([
                'usuario' => $datos['usuario'],
                'password' => Hash::make($datos['password']),
                'rol_id' => $datos['rol_id'],
                'estado' => $datos['estado'],
                'requiere_cambio_password' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('datos_personales')->insert([
                'usuario_id' => $usuarioId,
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $cargo = $this->cargoParaRol((int) $datos['rol_id']);

            if ($cargo !== null) {
                $this->crearAsignacion(
                    $usuarioId, $cargo, $datos, $administrador
                );
            }

            $this->auditar(
                $administrador,
                'CREAR_USUARIO',
                $usuarioId,
                'Se creó la cuenta '.$datos['usuario'].'.',
                null,
                $this->valoresAuditoria($usuarioId)
            );
        });

        return redirect()
            ->route('administrador.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(Request $request, int $usuario): View
    {
        $this->autorizar($request);

        $registro = $this->buscarUsuario($usuario);

        $asignaciones = $this->asignaciones($usuario);

        return view('administrador.usuarios.show', [
            'usuario' => $registro,
            'asignaciones' => $asignaciones,
        ]);
    }

    public function edit(Request $request, int $usuario): View
    {
        $this->autorizar($request);

        $registro = $this->buscarUsuario($usuario);
        $roles = $this->roles();
        $agencias = $this->agencias();
        $asignaciones = $this->asignaciones($usuario);

        return view('administrador.usuarios.edit', [
            'usuario' => $registro,
            'roles' => $roles,
            'agencias' => $agencias,
            'asignaciones' => $asignaciones,
        ]);
    }

    public function update(
        Request $request,
        int $usuario
    ): RedirectResponse {
        $administrador = $this->autorizar($request);

        $registro = $this->buscarUsuario($usuario);
        $datos = $this->validarDatos($request, $usuario);

        if (
            (int) $administrador->id === $usuario
            && $datos['estado'] !== 'ACTIVO'
        ) {
            throw ValidationException::withMessages([
                'estado' => 'No puede desactivar o bloquear su propia cuenta.',
            ]);
        }

        $cargoNuevo = $this->cargoParaRol((int) $datos['rol_id']);

        $tieneAsignaciones = DB::table('asignaciones_personal_agencia')
            ->where('usuario_id', $usuario)
            ->exists();

        if (
            $tieneAsignaciones
            && (int) $registro->rol_id !== (int) $datos['rol_id']
        ) {
            throw ValidationException::withMessages([
                'rol_id' => 'El usuario tiene historial de asignaciones a agencias. El cambio de cargo requiere revisar sus asignaciones para preservar la integridad operativa.',
            ]);
        }

        DB::transaction(function () use (
            $usuario, $datos, $administrador, $cargoNuevo
        ) {
            $anteriores = $this->valoresAuditoria($usuario);

            DB::table('usuarios')->where('id', $usuario)->update([
                'usuario' => $datos['usuario'],
                'rol_id' => $datos['rol_id'],
                'estado' => $datos['estado'],
                'updated_at' => now(),
            ]);

            $personales = DB::table('datos_personales')
                ->where('usuario_id', $usuario)
                ->first();

            if ($personales) {
                DB::table('datos_personales')
                    ->where('usuario_id', $usuario)
                    ->update([
                        'nombres' => $datos['nombres'],
                        'apellidos' => $datos['apellidos'],
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('datos_personales')->insert([
                    'usuario_id' => $usuario,
                    'nombres' => $datos['nombres'],
                    'apellidos' => $datos['apellidos'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($cargoNuevo !== null && !empty($datos['agencia_id'])) {
                $this->crearAsignacion(
                    $usuario, $cargoNuevo, $datos, $administrador
                );
            }

            $this->auditar(
                $administrador,
                'EDITAR_USUARIO',
                $usuario,
                'Se actualizaron los datos de '.$datos['usuario'].'.',
                $anteriores,
                $this->valoresAuditoria($usuario)
            );
        });

        return redirect()
            ->route('administrador.usuarios.show', $usuario)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    private function crearAsignacion(
        int $usuarioId,
        string $cargo,
        array $datos,
        Usuario $administrador
    ): void {
        $agenciaId = (int) $datos['agencia_id'];
        $inicio = $datos['fecha_inicio'] ?? null;
        $fin = $datos['fecha_fin'] ?? null;
        $tipo = $datos['tipo_asignacion'] ?? 'PERMANENTE';

        if (!$inicio) {
            throw ValidationException::withMessages([
                'fecha_inicio' => 'Indique la fecha de inicio de la asignación.',
            ]);
        }

        $solapada = DB::table('asignaciones_personal_agencia')
            ->where('usuario_id', $usuarioId)
            ->where('agencia_id', $agenciaId)
            ->where('cargo', $cargo)
            ->where('tipo', $tipo)
            ->where('estado', 1)
            ->where('fecha_inicio', '<=', $fin ?? '9999-12-31')
            ->where(function ($q) use ($inicio) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', $inicio);
            })
            ->exists();

        if ($solapada) {
            throw ValidationException::withMessages([
                'agencia_id' => 'Ya existe una asignación vigente que se superpone con el período indicado.',
            ]);
        }

        DB::table('asignaciones_personal_agencia')->insert([
            'usuario_id' => $usuarioId,
            'agencia_id' => $agenciaId,
            'cargo' => $cargo,
            'tipo' => $tipo,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'estado' => true,
            'motivo' => $datos['motivo_asignacion'] ?? null,
            'asignado_por' => $administrador->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function asignaciones(int $usuarioId)
    {
        return DB::table('asignaciones_personal_agencia as apa')
            ->join('agencias as a', 'a.id', '=', 'apa.agencia_id')
            ->where('apa.usuario_id', $usuarioId)
            ->select([
                'apa.*',
                'a.nombre as agencia_nombre',
                'a.codigo as agencia_codigo',
            ])
            ->orderByDesc('apa.fecha_inicio')
            ->orderByDesc('apa.id')
            ->get();
    }

    private function buscarUsuario(int $usuarioId): object
    {
        $registro = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('u.id', $usuarioId)
            ->select([
                'u.id', 'u.usuario', 'u.estado', 'u.rol_id',
                'rol.nombre as rol_nombre',
                'dp.nombres', 'dp.apellidos',
            ])
            ->first();

        abort_unless($registro, 404, 'El usuario no existe.');

        return $registro;
    }

    public function cambiarEstado(
        Request $request,
        int $usuario
    ): RedirectResponse {
        $administrador = $this->autorizar($request);

        abort_if(
            (int) $administrador->id === $usuario,
            422,
            'No puede modificar el estado de su propia cuenta.'
        );

        $registro = $this->buscarUsuario($usuario);

        $nuevoEstado = $registro->estado === 'ACTIVO'
            ? 'INACTIVO'
            : 'ACTIVO';

        DB::transaction(function () use (
            $usuario, $nuevoEstado, $administrador
        ) {
            $anteriores = $this->valoresAuditoria($usuario);

            DB::table('usuarios')->where('id', $usuario)->update([
                'estado' => $nuevoEstado,
                'updated_at' => now(),
            ]);

            $this->auditar(
                $administrador,
                $nuevoEstado === 'ACTIVO'
                    ? 'ACTIVAR_USUARIO'
                    : 'DESACTIVAR_USUARIO',
                $usuario,
                'Se cambió el estado de la cuenta.',
                $anteriores,
                $this->valoresAuditoria($usuario)
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
        $administrador = $this->autorizar($request);
        $this->buscarUsuario($usuario);

        $datos = $request->validate([
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
            ],
        ]);

        DB::transaction(function () use (
            $usuario, $datos, $administrador
        ) {
            DB::table('usuarios')->where('id', $usuario)->update([
                'password' => Hash::make($datos['password']),
                'requiere_cambio_password' => true,
                'fecha_ultimo_cambio_password' => null,
                'updated_at' => now(),
            ]);

            $this->auditar(
                $administrador,
                'RESTABLECER_PASSWORD',
                $usuario,
                'El Administrador restableció la contraseña.',
                null,
                ['password_restablecida' => true]
            );
        });

        return back()->with(
            'success',
            'Contraseña actualizada correctamente.'
        );
    }

    private function valoresAuditoria(int $usuarioId): array
    {
        $registro = DB::table('usuarios as u')
            ->join('roles as r', 'r.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('u.id', $usuarioId)
            ->select([
                'u.id', 'u.usuario', 'u.rol_id',
                'r.nombre as rol', 'u.estado',
                'u.requiere_cambio_password',
                'dp.nombres', 'dp.apellidos',
            ])
            ->first();

        if (!$registro) {
            return [];
        }

        return [
            'id' => $registro->id,
            'usuario' => $registro->usuario,
            'rol_id' => $registro->rol_id,
            'rol' => $registro->rol,
            'estado' => $registro->estado,
            'requiere_cambio_password' =>
                (bool) $registro->requiere_cambio_password,
            'nombres' => $registro->nombres,
            'apellidos' => $registro->apellidos,
            'asignaciones_agencia' => $this->asignaciones($usuarioId)
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'agencia_id' => $a->agencia_id,
                    'cargo' => $a->cargo,
                    'tipo' => $a->tipo,
                    'fecha_inicio' => $a->fecha_inicio,
                    'fecha_fin' => $a->fecha_fin,
                    'estado' => (bool) $a->estado,
                ])->all(),
        ];
    }

    private function auditar(
        Usuario $administrador,
        string $accion,
        int $usuarioId,
        string $descripcion,
        ?array $anteriores,
        array $nuevos
    ): void {
        app(AuditoriaService::class)->registrar(
            usuario: $administrador,
            modulo: 'Usuarios',
            accion: $accion,
            tablaAfectada: 'usuarios',
            registroId: $usuarioId,
            descripcion: $descripcion,
            valoresAnteriores: $anteriores,
            valoresNuevos: $nuevos
        );
    }
}
