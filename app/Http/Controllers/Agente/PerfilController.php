<?php

namespace App\Http\Controllers\Agente;

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

        $usuario->loadMissing([
            'rol',
            'datosPersonales',
            'agente.ruta.region',
        ]);

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        return view('agente.perfil.index', [
            'usuario' => $usuario,
            'datosPersonales' => $usuario->datosPersonales,
            'agente' => $agente,
            'ruta' => $agente->ruta,
            'region' => $agente->ruta?->region,
        ]);
    }

    public function actualizar(
        Request $request
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'rol',
            'datosPersonales',
            'agente',
        ]);

        abort_if(
            ! $usuario->agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        $datosValidados = $request->validate(
            [
                'nombre_usuario' => [
                    'required',
                    'string',
                    'max:80',
                    Rule::unique(
                        'usuarios',
                        'usuario'
                    )->ignore($usuario->id),
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
                'nombre_usuario.required' =>
                    'El nombre de usuario es obligatorio.',

                'nombre_usuario.unique' =>
                    'El nombre de usuario ya está en uso.',

                'nombres.required' =>
                    'Los nombres son obligatorios.',

                'apellidos.required' =>
                    'Los apellidos son obligatorios.',
            ]
        );

        DB::transaction(function () use (
            $usuario,
            $datosValidados
        ): void {
            /*
            |--------------------------------------------------------------------------
            | VALORES ANTERIORES
            |--------------------------------------------------------------------------
            */

            $usuarioAnterior = [
                'id' => (int) $usuario->id,
                'usuario' => $usuario->usuario,
            ];

            $datosPersonalesAnteriores = $usuario->datosPersonales
                ? [
                    'id' => (int) $usuario->datosPersonales->id,
                    'usuario_id' =>
                        (int) $usuario->datosPersonales->usuario_id,
                    'nombres' =>
                        $usuario->datosPersonales->nombres,
                    'apellidos' =>
                        $usuario->datosPersonales->apellidos,
                ]
                : null;

            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR USUARIO
            |--------------------------------------------------------------------------
            */

            $usuario->update([
                'usuario' =>
                    trim($datosValidados['nombre_usuario']),
            ]);

            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR DATOS PERSONALES
            |--------------------------------------------------------------------------
            */

            $datosPersonales = $usuario
                ->datosPersonales()
                ->updateOrCreate(
                    [
                        'usuario_id' => $usuario->id,
                    ],
                    [
                        'nombres' =>
                            trim($datosValidados['nombres']),

                        'apellidos' =>
                            trim($datosValidados['apellidos']),
                    ]
                );

            /*
            |--------------------------------------------------------------------------
            | REFRESCAR DATOS
            |--------------------------------------------------------------------------
            */

            $usuario->refresh();

            /*
            |--------------------------------------------------------------------------
            | AUDITORÍA - USUARIO
            |--------------------------------------------------------------------------
            */

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Perfil Agente',
                accion: 'ACTUALIZAR_PERFIL',
                tablaAfectada: 'usuarios',
                registroId: $usuario->id,
                descripcion:
                    'El Agente actualizó la información de su perfil.',
                valoresAnteriores: $usuarioAnterior,
                valoresNuevos: [
                    'id' => (int) $usuario->id,
                    'usuario' => $usuario->usuario,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | AUDITORÍA - DATOS PERSONALES
            |--------------------------------------------------------------------------
            */

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Perfil Agente',
                accion: $datosPersonalesAnteriores === null
                    ? 'CREAR_DATOS_PERSONALES'
                    : 'ACTUALIZAR_DATOS_PERSONALES',
                tablaAfectada: 'datos_personales',
                registroId: $datosPersonales->id,
                descripcion:
                    $datosPersonalesAnteriores === null
                        ? 'El Agente registró sus datos personales desde su perfil.'
                        : 'El Agente actualizó sus datos personales desde su perfil.',
                valoresAnteriores:
                    $datosPersonalesAnteriores,
                valoresNuevos: [
                    'id' =>
                        (int) $datosPersonales->id,
                    'usuario_id' =>
                        (int) $datosPersonales->usuario_id,
                    'nombres' =>
                        $datosPersonales->nombres,
                    'apellidos' =>
                        $datosPersonales->apellidos,
                ]
            );
        });

        return redirect()
            ->route('agente.perfil.index')
            ->with(
                'success',
                'El perfil fue actualizado correctamente.'
            );
    }
}
