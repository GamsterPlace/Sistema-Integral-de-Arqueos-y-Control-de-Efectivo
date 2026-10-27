<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function mostrarLogin(): View
    {
        return view('auth.login');
    }

    /**
     * @throws ValidationException
     */
    public function iniciarSesion(
        Request $request
    ): RedirectResponse {
        $credenciales = $request->validate([
            'usuario' => [
                'required',
                'string',
                'max:50',
            ],
            'password' => [
                'required',
                'string',
            ],
        ], [
            'usuario.required' => 'Ingrese su usuario.',
            'usuario.string' => 'El usuario ingresado no es válido.',
            'usuario.max' => 'El usuario no puede superar los 50 caracteres.',
            'password.required' => 'Ingrese su contraseña.',
            'password.string' => 'La contraseña ingresada no es válida.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Buscar la cuenta
        |--------------------------------------------------------------------------
        */

        $usuario = Usuario::query()
            ->with('rol')
            ->where('usuario', $credenciales['usuario'])
            ->first();

        if (! $usuario) {
            throw ValidationException::withMessages([
                'usuario' => 'El usuario o la contraseña no son correctos.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar estado de la cuenta
        |--------------------------------------------------------------------------
        */

        if ($usuario->estado !== 'ACTIVO') {
            throw ValidationException::withMessages([
                'usuario' => 'La cuenta se encuentra inactiva o bloqueada.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar estado del rol
        |--------------------------------------------------------------------------
        */

        if (! $usuario->rol || ! $usuario->rol->estado) {
            throw ValidationException::withMessages([
                'usuario' => 'El rol asociado a esta cuenta no está disponible.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Autenticar
        |--------------------------------------------------------------------------
        */

        $autenticado = Auth::attempt([
            'usuario' => $credenciales['usuario'],
            'password' => $credenciales['password'],
            'estado' => 'ACTIVO',
        ]);

        if (! $autenticado) {
            throw ValidationException::withMessages([
                'usuario' => 'El usuario o la contraseña no son correctos.',
            ]);
        }

        $request->session()->regenerate();

        /** @var Usuario $usuarioAutenticado */
        $usuarioAutenticado = Auth::user();

        $usuarioAutenticado->loadMissing('rol');

        /*
        |--------------------------------------------------------------------------
        | Registrar último acceso
        |--------------------------------------------------------------------------
        */

        $usuarioAutenticado->forceFill([
            'ultimo_acceso' => now(),
        ])->save();

        app(AuditoriaService::class)->registrar(
            $usuarioAutenticado,
            'AUTENTICACION',
            'INICIAR_SESION',
            'usuarios',
            $usuarioAutenticado->id,
            'Inicio de sesión exitoso.'
        );

        /*
        |--------------------------------------------------------------------------
        | Verificar cambio obligatorio de contraseña
        |--------------------------------------------------------------------------
        */

        if ($this->requiereCambioPassword($usuarioAutenticado)) {
            if (! $usuarioAutenticado->requiere_cambio_password) {
                $usuarioAutenticado->forceFill([
                    'requiere_cambio_password' => true,
                ])->save();
            }

            return redirect()->route('password.cambiar');
        }

        /*
        |--------------------------------------------------------------------------
        | Redireccionar según el rol
        |--------------------------------------------------------------------------
        |
        | Se utiliza la ruta central del sistema para evitar
        | duplicar la lógica de redirección entre el controlador
        | de autenticación y routes/web.php.
        |
        */

        return redirect()->route('dashboard');
    }

    public function cerrarSesion(
        Request $request
    ): RedirectResponse {
        $usuarioAutenticado = Auth::user();

        if ($usuarioAutenticado !== null) {
            app(AuditoriaService::class)->registrar(
                $usuarioAutenticado,
                'AUTENTICACION',
                'CERRAR_SESION',
                'usuarios',
                $usuarioAutenticado->id,
                'Cierre de sesión realizado por el usuario.'
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Sesión cerrada correctamente.'
            );
    }

    private function requiereCambioPassword(
        Usuario $usuario
    ): bool {
        if ($usuario->requiere_cambio_password) {
            return true;
        }

        if (! $usuario->fecha_ultimo_cambio_password) {
            return true;
        }

        return ! $usuario
            ->fecha_ultimo_cambio_password
            ->isSameMonth(now());
    }
}
