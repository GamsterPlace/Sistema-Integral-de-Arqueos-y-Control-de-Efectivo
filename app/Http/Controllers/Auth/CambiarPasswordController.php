<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\HistorialPassword;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CambiarPasswordController extends Controller
{
    /**
     * Mostrar formulario.
     */
    public function index()
    {
        return view('auth.cambiar-password');
    }

    /**
     * Actualizar contraseña.
     */
    public function actualizar(Request $request)
    {
        $request->validate([
            'password_actual' => [
                'required'
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'password_confirmation' => [
                'required'
            ],
        ], [

            'password.required' => 'Debe ingresar una nueva contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',

        ]);

        $usuario = Auth::user();
        /** @var \App\Models\Usuario $usuario */
        /*
        |--------------------------------------------------------------------------
        | Verificar contraseña actual
        |--------------------------------------------------------------------------
        */

        if (! Hash::check(
            $request->password_actual,
            $usuario->password
        )) {

            return back()
                ->withErrors([
                    'password_actual' => 'La contraseña actual es incorrecta.'
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | No permitir la misma contraseña
        |--------------------------------------------------------------------------
        */

        if (Hash::check(
            $request->password,
            $usuario->password
        )) {

            return back()
                ->withErrors([
                    'password' => 'La nueva contraseña debe ser diferente a la actual.'
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Revisar últimas 5 contraseñas
        |--------------------------------------------------------------------------
        */

        $historial = HistorialPassword::where(
                'usuario_id',
                $usuario->id
            )
            ->latest()
            ->take(5)
            ->get();

        foreach ($historial as $item) {

            if (Hash::check(
                $request->password,
                $item->password
            )) {

                return back()
                    ->withErrors([
                        'password' => 'No puede reutilizar ninguna de sus últimas cinco contraseñas.'
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Guardar contraseña anterior
        |--------------------------------------------------------------------------
        */

        HistorialPassword::create([

            'usuario_id' => $usuario->id,

            'password' => $usuario->password,

        ]);

        /*
        |--------------------------------------------------------------------------
        | Actualizar usuario
        |--------------------------------------------------------------------------
        */

        $usuario->password = Hash::make(
            $request->password
        );

        $usuario->requiere_cambio_password = false;

        $usuario->fecha_ultimo_cambio_password = now();

        $usuario->save();

        app(AuditoriaService::class)->registrar(
            $usuario,
            'SEGURIDAD',
            'CAMBIAR_PASSWORD',
            'usuarios',
            $usuario->id,
            'El usuario actualizó correctamente su contraseña.'
        );

        /*
        |--------------------------------------------------------------------------
        | Cerrar otras sesiones
        |--------------------------------------------------------------------------
        */

        Auth::logoutOtherDevices(
            $request->password
        );

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'La contraseña fue actualizada correctamente.'
            );
    }
}
