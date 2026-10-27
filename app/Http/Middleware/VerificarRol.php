<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class VerificarRol
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Verificar autenticación
        |--------------------------------------------------------------------------
        */

        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar estado de la cuenta
        |--------------------------------------------------------------------------
        |
        | Si el administrador desactiva una cuenta mientras
        | mantiene una sesión iniciada, se impedirá que
        | continúe utilizando los módulos protegidos.
        |
        */

        $usuario->refresh();

        if ($usuario->estado !== 'ACTIVO') {
            return $this->cerrarSesion(
                $request,
                'Su cuenta se encuentra inactiva o bloqueada.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar rol asignado
        |--------------------------------------------------------------------------
        */

        $usuario->load('rol');

        if (! $usuario->rol) {
            abort(
                403,
                'El usuario no tiene un rol asignado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar estado del rol
        |--------------------------------------------------------------------------
        */

        if (! $usuario->rol->estado) {
            return $this->cerrarSesion(
                $request,
                'El rol asociado a su cuenta está deshabilitado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar permisos del rol
        |--------------------------------------------------------------------------
        |
        | Admite uno o varios roles autorizados.
        |
        | Ejemplo:
        | rol:JefeCaja,SubjefeCaja
        |
        */

        if (! in_array(
            $usuario->rol->nombre,
            $roles,
            true
        )) {
            abort(
                403,
                'No tiene permiso para acceder a esta sección.'
            );
        }

        return $next($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Cerrar sesión por restricción de seguridad
    |--------------------------------------------------------------------------
    */

    private function cerrarSesion(
        Request $request,
        string $mensaje
    ): Response {
        auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('error', $mensaje);
    }
}
