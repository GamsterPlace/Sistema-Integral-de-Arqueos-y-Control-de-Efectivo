<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        $usuario->loadMissing([
            'rol',
            'datosPersonales',
        ]);

        $rutasAsignadas = DB::table('rutas as r')
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
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
            ])
            ->distinct()
            ->orderBy('r.nombre')
            ->get();

        $totalAgentes = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
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
            ->where('a.estado', 'ACTIVO')
            ->distinct('a.id')
            ->count('a.id');

        return view('promotor.perfil.index', [
            'usuario' => $usuario,
            'datosPersonales' => $usuario->datosPersonales,
            'rutasAsignadas' => $rutasAsignadas,
            'totalAgentes' => $totalAgentes,
        ]);
    }

    public function actualizar(
        Request $request
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        $usuario->loadMissing([
            'rol',
            'datosPersonales',
        ]);

        $datosValidados = $request->validate(
            [
                'nombre_usuario' => [
                    'required',
                    'string',
                    'max:80',
                    Rule::unique('usuarios', 'nombre_usuario')
                        ->ignore($usuario->id),
                ],
                'nombres' => [
                    'required',
                    'string',
                    'max:120',
                ],
                'apellidos' => [
                    'required',
                    'string',
                    'max:120',
                ],
            ],
            [
                'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
                'nombre_usuario.unique' => 'El nombre de usuario ya está en uso.',
                'nombres.required' => 'Los nombres son obligatorios.',
                'apellidos.required' => 'Los apellidos son obligatorios.',
            ]
        );

        DB::transaction(function () use (
            $usuario,
            $datosValidados
        ): void {
            $usuarioAnterior = [
                'id' => (int) $usuario->id,
                'nombre_usuario' => $usuario->nombre_usuario,
            ];

            $datosPersonalesAnteriores = $usuario->datosPersonales
                ? [
                    'id' => (int) $usuario->datosPersonales->id,
                    'usuario_id' => (int) $usuario->datosPersonales->usuario_id,
                    'nombres' => $usuario->datosPersonales->nombres,
                    'apellidos' => $usuario->datosPersonales->apellidos,
                ]
                : null;

            $usuario->update([
                'nombre_usuario' => trim($datosValidados['nombre_usuario']),
            ]);

            $datosPersonales = $usuario->datosPersonales()->updateOrCreate(
                [
                    'usuario_id' => $usuario->id,
                ],
                [
                    'nombres' => trim($datosValidados['nombres']),
                    'apellidos' => trim($datosValidados['apellidos']),
                ]
            );

            $usuario->refresh();

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Perfil Promotor',
                accion: 'ACTUALIZAR_PERFIL',
                tablaAfectada: 'usuarios',
                registroId: $usuario->id,
                descripcion:
                    'El Promotor actualizó la información de su perfil.',
                valoresAnteriores: $usuarioAnterior,
                valoresNuevos: [
                    'id' => (int) $usuario->id,
                    'nombre_usuario' => $usuario->nombre_usuario,
                ]
            );

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Perfil Promotor',
                accion: $datosPersonalesAnteriores === null
                    ? 'CREAR_DATOS_PERSONALES'
                    : 'ACTUALIZAR_DATOS_PERSONALES',
                tablaAfectada: 'datos_personales',
                registroId: $datosPersonales->id,
                descripcion:
                    $datosPersonalesAnteriores === null
                        ? 'El Promotor registró sus datos personales desde su perfil.'
                        : 'El Promotor actualizó sus datos personales desde su perfil.',
                valoresAnteriores: $datosPersonalesAnteriores,
                valoresNuevos: [
                    'id' => (int) $datosPersonales->id,
                    'usuario_id' => (int) $datosPersonales->usuario_id,
                    'nombres' => $datosPersonales->nombres,
                    'apellidos' => $datosPersonales->apellidos,
                ]
            );
        });

        return redirect()
            ->route('promotor.perfil.index')
            ->with(
                'success',
                'El perfil fue actualizado correctamente.'
            );
    }

    private function validarPromotor(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol || $usuario->rol->nombre !== 'Promotor',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
