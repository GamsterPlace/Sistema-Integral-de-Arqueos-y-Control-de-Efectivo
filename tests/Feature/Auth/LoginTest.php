<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_mostrar_la_pantalla_de_login(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertViewIs('auth.login');
    }

    public function test_usuario_puede_iniciar_sesion_con_credenciales_correctas(): void
    {
        $rol = Role::factory()->create([
            'nombre' => 'Administrador',
            'estado' => true,
        ]);

        $usuario = Usuario::factory()->create([
            'rol_id' => $rol->id,
            'usuario' => 'administrador.prueba',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        $response = $this->post(route('login.iniciar'), [
            'usuario' => 'administrador.prueba',
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticatedAs($usuario);

        $response->assertRedirect(
            route('administrador.dashboard')
        );

        $usuario->refresh();

        $this->assertNotNull($usuario->ultimo_acceso);
    }

    public function test_no_permite_login_con_password_incorrecta(): void
    {
        $rol = Role::factory()->create([
            'nombre' => 'Administrador',
            'estado' => true,
        ]);

        Usuario::factory()->create([
            'rol_id' => $rol->id,
            'usuario' => 'administrador.prueba',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
        ]);

        $response = $this
            ->from(route('login'))
            ->post(route('login.iniciar'), [
                'usuario' => 'administrador.prueba',
                'password' => 'PasswordIncorrecta!',
            ]);

        $response->assertRedirect(route('login'));

        $response->assertSessionHasErrors([
            'usuario',
        ]);

        $this->assertGuest();
    }

    public function test_no_permite_login_con_usuario_inexistente(): void
    {
        $response = $this
            ->from(route('login'))
            ->post(route('login.iniciar'), [
                'usuario' => 'usuario.inexistente',
                'password' => 'Password123!',
            ]);

        $response->assertRedirect(route('login'));

        $response->assertSessionHasErrors([
            'usuario',
        ]);

        $this->assertGuest();
    }

    public function test_no_permite_login_a_usuario_inactivo(): void
    {
        $rol = Role::factory()->create([
            'nombre' => 'Administrador',
            'estado' => true,
        ]);

        Usuario::factory()
            ->inactivo()
            ->create([
                'rol_id' => $rol->id,
                'usuario' => 'usuario.inactivo',
                'password' => 'Password123!',
            ]);

        $response = $this
            ->from(route('login'))
            ->post(route('login.iniciar'), [
                'usuario' => 'usuario.inactivo',
                'password' => 'Password123!',
            ]);

        $response->assertRedirect(route('login'));

        $response->assertSessionHasErrors([
            'usuario',
        ]);

        $this->assertGuest();
    }

    public function test_no_permite_login_a_usuario_bloqueado(): void
    {
        $rol = Role::factory()->create([
            'nombre' => 'Administrador',
            'estado' => true,
        ]);

        Usuario::factory()
            ->bloqueado()
            ->create([
                'rol_id' => $rol->id,
                'usuario' => 'usuario.bloqueado',
                'password' => 'Password123!',
            ]);

        $response = $this
            ->from(route('login'))
            ->post(route('login.iniciar'), [
                'usuario' => 'usuario.bloqueado',
                'password' => 'Password123!',
            ]);

        $response->assertRedirect(route('login'));

        $response->assertSessionHasErrors([
            'usuario',
        ]);

        $this->assertGuest();
    }

    public function test_no_permite_login_si_el_rol_esta_inactivo(): void
    {
        $rol = Role::factory()
            ->inactivo()
            ->create([
                'nombre' => 'Administrador',
            ]);

        Usuario::factory()->create([
            'rol_id' => $rol->id,
            'usuario' => 'rol.inactivo',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
        ]);

        $response = $this
            ->from(route('login'))
            ->post(route('login.iniciar'), [
                'usuario' => 'rol.inactivo',
                'password' => 'Password123!',
            ]);

        $response->assertRedirect(route('login'));

        $response->assertSessionHasErrors([
            'usuario',
        ]);

        $this->assertGuest();
    }

    public function test_usuario_con_cambio_password_obligatorio_es_redirigido(): void
    {
        $rol = Role::factory()->create([
            'nombre' => 'Administrador',
            'estado' => true,
        ]);

        $usuario = Usuario::factory()
            ->requiereCambioPassword()
            ->create([
                'rol_id' => $rol->id,
                'usuario' => 'cambio.password',
                'password' => 'Password123!',
            ]);

        $response = $this->post(
            route('login.iniciar'),
            [
                'usuario' => 'cambio.password',
                'password' => 'Password123!',
            ]
        );

        $this->assertAuthenticatedAs($usuario);

        $response->assertRedirect(
            route('password.cambiar')
        );
    }

    public function test_usuario_con_password_de_mes_anterior_debe_cambiarla(): void
    {
        $rol = Role::factory()->create([
            'nombre' => 'Administrador',
            'estado' => true,
        ]);

        $usuario = Usuario::factory()
            ->passwordVencida()
            ->create([
                'rol_id' => $rol->id,
                'usuario' => 'password.vencida',
                'password' => 'Password123!',
            ]);

        $response = $this->post(
            route('login.iniciar'),
            [
                'usuario' => 'password.vencida',
                'password' => 'Password123!',
            ]
        );

        $this->assertAuthenticatedAs($usuario);

        $response->assertRedirect(
            route('password.cambiar')
        );

        $this->assertDatabaseHas('usuarios', [
            'id' => $usuario->id,
            'requiere_cambio_password' => true,
        ]);
    }

    public function test_usuario_autenticado_puede_cerrar_sesion(): void
    {
        $usuario = Usuario::factory()
            ->conRol('Administrador')
            ->create();

        $response = $this
            ->actingAs($usuario)
            ->post(route('logout'));

        $this->assertGuest();

        $response->assertRedirect(
            route('login')
        );
    }
}
