<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditoriaSistemaController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $usuarioId = $request->integer('usuario_id');
        $modulo = trim((string) $request->string('modulo'));
        $accion = trim((string) $request->string('accion'));
        $tabla = trim((string) $request->string('tabla'));
        $buscar = trim((string) $request->string('buscar'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $usuarios = DB::table('usuarios as u')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->get([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ]);

        $modulos = DB::table('auditoria')
            ->whereNotNull('modulo')
            ->where('modulo', '!=', '')
            ->distinct()
            ->orderBy('modulo')
            ->pluck('modulo');

        $acciones = DB::table('auditoria')
            ->whereNotNull('accion')
            ->where('accion', '!=', '')
            ->distinct()
            ->orderBy('accion')
            ->pluck('accion');

        $tablas = DB::table('auditoria')
            ->whereNotNull('tabla_afectada')
            ->where('tabla_afectada', '!=', '')
            ->distinct()
            ->orderBy('tabla_afectada')
            ->pluck('tabla_afectada');

        $registros = DB::table('auditoria as aud')
            ->leftJoin('usuarios as u', 'u.id', '=', 'aud.usuario_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->leftJoin('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->when(
                $usuarioId > 0,
                fn ($query) => $query->where('aud.usuario_id', $usuarioId)
            )
            ->when(
                $modulo !== '',
                fn ($query) => $query->where('aud.modulo', $modulo)
            )
            ->when(
                $accion !== '',
                fn ($query) => $query->where('aud.accion', $accion)
            )
            ->when(
                $tabla !== '',
                fn ($query) => $query->where('aud.tabla_afectada', $tabla)
            )
            ->when(
                $desde,
                fn ($query) => $query->whereDate('aud.created_at', '>=', $desde)
            )
            ->when(
                $hasta,
                fn ($query) => $query->whereDate('aud.created_at', '<=', $hasta)
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where('aud.descripcion', 'like', '%' . $buscar . '%')
                            ->orWhere('aud.modulo', 'like', '%' . $buscar . '%')
                            ->orWhere('aud.accion', 'like', '%' . $buscar . '%')
                            ->orWhere('aud.tabla_afectada', 'like', '%' . $buscar . '%')
                            ->orWhere('u.usuario', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.nombres', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.apellidos', 'like', '%' . $buscar . '%');
                    });
                }
            )
            ->select([
                'aud.id',
                'aud.usuario_id',
                'aud.modulo',
                'aud.accion',
                'aud.tabla_afectada',
                'aud.registro_id',
                'aud.descripcion',
                'aud.created_at',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
                'rol.nombre as rol_nombre',
            ])
            ->orderByDesc('aud.created_at')
            ->orderByDesc('aud.id')
            ->paginate(25)
            ->withQueryString();

        $resumen = (object) [
            'total' => DB::table('auditoria')->count(),

            'hoy' => DB::table('auditoria')
                ->whereDate('created_at', today())
                ->count(),

            'usuarios' => DB::table('auditoria')
                ->whereNotNull('usuario_id')
                ->distinct('usuario_id')
                ->count('usuario_id'),

            'modulos' => DB::table('auditoria')
                ->distinct('modulo')
                ->count('modulo'),
        ];

        return view(
            'administrador.auditoria-sistema.index',
            compact(
                'registros',
                'usuarios',
                'modulos',
                'acciones',
                'tablas',
                'usuarioId',
                'modulo',
                'accion',
                'tabla',
                'buscar',
                'desde',
                'hasta',
                'resumen'
            )
        );
    }

    public function show(
        Request $request,
        int $auditoria
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $registro = DB::table('auditoria as aud')
            ->leftJoin('usuarios as u', 'u.id', '=', 'aud.usuario_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->leftJoin('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->where('aud.id', $auditoria)
            ->select([
                'aud.id',
                'aud.usuario_id',
                'aud.modulo',
                'aud.accion',
                'aud.tabla_afectada',
                'aud.registro_id',
                'aud.descripcion',
                'aud.valores_anteriores',
                'aud.valores_nuevos',
                'aud.created_at',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
                'rol.nombre as rol_nombre',
            ])
            ->first();

        abort_if(
            ! $registro,
            404,
            'El registro de auditoría solicitado no existe.'
        );

        $anteriores = $this->decodificarJson(
            $registro->valores_anteriores
        );

        $nuevos = $this->decodificarJson(
            $registro->valores_nuevos
        );

        return view(
            'administrador.auditoria-sistema.show',
            compact(
                'registro',
                'anteriores',
                'nuevos'
            )
        );
    }

    private function decodificarJson(
        ?string $valor
    ): array {
        if (! $valor) {
            return [];
        }

        $datos = json_decode(
            $valor,
            true
        );

        return is_array($datos)
            ? $datos
            : [];
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
