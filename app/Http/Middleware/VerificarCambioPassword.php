<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarCambioPassword
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $usuario = $request->user();

        if (! $usuario) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Determinar si debe cambiar la contraseña
        |--------------------------------------------------------------------------
        |
        | Se exige el cambio cuando:
        | - La cuenta está marcada para cambio obligatorio.
        | - Nunca ha cambiado su contraseña.
        | - La contraseña no fue cambiada durante el mes actual.
        |
        */

        $cambioObligatorio =
            $usuario->requiere_cambio_password ||
            ! $usuario->fecha_ultimo_cambio_password ||
            ! $usuario->fecha_ultimo_cambio_password->isSameMonth(now());

        if ($cambioObligatorio) {
            /*
            |--------------------------------------------------------------------------
            | Mantener sincronizada la marca en la base de datos
            |--------------------------------------------------------------------------
            */

            if (! $usuario->requiere_cambio_password) {
                $usuario->forceFill([
                    'requiere_cambio_password' => true,
                ])->save();
            }

            return redirect()
                ->route('password.cambiar')
                ->with(
                    'warning',
                    'Debe actualizar su contraseña para continuar.'
                );
        }

        return $next($request);
    }
}
