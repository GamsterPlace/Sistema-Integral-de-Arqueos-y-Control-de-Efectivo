<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\Agencias\AccesoAgenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerfilController extends Controller
{
    private const ROLES = [
        'Receptor' => ['layout' => 'layouts.receptor', 'titulo' => 'Receptor'],
        'JefeCaja' => ['layout' => 'layouts.jefe-caja', 'titulo' => 'Jefe de Caja'],
        'SubjefeCaja' => ['layout' => 'layouts.subjefe-caja', 'titulo' => 'Subjefe de Caja'],
        'JefeAgencia' => ['layout' => 'layouts.jefe-agencia', 'titulo' => 'Jefe de Agencia'],
        'Auditoria' => ['layout' => 'layouts.auditoria', 'titulo' => 'Auditoría'],
        'Gerencia' => ['layout' => 'layouts.gerencia', 'titulo' => 'Gerencia'],
        'Administrador' => ['layout' => 'layouts.administrador', 'titulo' => 'Administrador'],
    ];

    private function usuarioAutorizado(Request $request): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $usuario->loadMissing(['rol', 'datosPersonales']);
        abort_unless(isset(self::ROLES[$usuario->rol?->nombre ?? '']), 403);
        abort_unless($usuario->estado === 'ACTIVO' && (bool) $usuario->rol?->estado, 403);
        return $usuario;
    }

    public function index(Request $request, AccesoAgenciaService $acceso): View
    {
        $usuario = $this->usuarioAutorizado($request);
        $rol = $usuario->rol->nombre;
        $layout = self::ROLES[$rol]['layout'];
        $cargo = self::ROLES[$rol]['titulo'];
        $asignaciones = in_array($rol, ['Receptor', 'JefeCaja', 'SubjefeCaja', 'JefeAgencia'], true)
            ? $acceso->asignacionesVigentes($usuario)
            : collect();

        return view('agencias.perfil.index', compact('usuario', 'rol', 'layout', 'cargo', 'asignaciones'));
    }

    public function actualizar(Request $request): RedirectResponse
    {
        $usuario = $this->usuarioAutorizado($request);

        $datos = $request->validate([
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'nombre_usuario' => [
                'required', 'string', 'max:80',
                Rule::unique('usuarios', 'usuario')->ignore($usuario->getKey()),
            ],
        ]);

        DB::transaction(function () use ($usuario, $datos): void {
            $usuario->update(['usuario' => $datos['nombre_usuario']]);
            $usuario->datosPersonales()->updateOrCreate(
                ['usuario_id' => $usuario->getKey()],
                [
                    'nombres' => trim($datos['nombres']),
                    'apellidos' => trim($datos['apellidos']),
                    'telefono' => isset($datos['telefono']) && trim($datos['telefono']) !== ''
                        ? trim($datos['telefono']) : null,
                ]
            );
        });

        return redirect()->route('agencias.perfil.index')
            ->with('success', 'Los datos de su perfil se actualizaron correctamente.');
    }
}
