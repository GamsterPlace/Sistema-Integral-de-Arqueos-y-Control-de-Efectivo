<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $response = $this->get(
            route('administrador.dashboard')
        );

        $response->assertRedirect(
            route('login')
        );

        $this->assertGuest();
    }

    public function test_cada_rol_es_redirigido_a_su_dashboard_correcto(): void
    {
        $casos = [
            'Administrador' => 'administrador.dashboard',
            'Promotor' => 'promotor.dashboard',
            'jefedeAgentes' => 'jefe.dashboard',
            'Auditoria' => 'auditoria.dashboard',
            'Gerencia' => 'gerencia.dashboard',
            'agente' => 'agente.dashboard',
        ];

        foreach ($casos as $nombreRol => $rutaEsperada) {
            /** @var Role $rol */
            $rol = Role::factory()->create([
                'nombre' => $nombreRol,
                'estado' => true,
            ]);

            /** @var Usuario $usuario */
            $usuario = Usuario::factory()->create([
                'rol_id' => $rol->id,
                'estado' => 'ACTIVO',
                'requiere_cambio_password' => false,
                'fecha_ultimo_cambio_password' => now(),
            ]);

            $response = $this
                ->actingAs($usuario)
                ->get(route('dashboard'));

            $response->assertRedirect(
                route($rutaEsperada)
            );
        }
    }

    public function test_promotor_no_puede_acceder_al_modulo_administrador(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Promotor'
        );

        $response = $this
            ->actingAs($usuario)
            ->get(route('administrador.dashboard'));

        $response->assertForbidden();
    }

    public function test_jefe_no_puede_acceder_al_modulo_administrador(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'jefedeAgentes'
        );

        $response = $this
            ->actingAs($usuario)
            ->get(route('administrador.dashboard'));

        $response->assertForbidden();
    }

    public function test_auditoria_no_puede_acceder_al_modulo_administrador(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Auditoria'
        );

        $response = $this
            ->actingAs($usuario)
            ->get(route('administrador.dashboard'));

        $response->assertForbidden();
    }

    public function test_gerencia_no_puede_acceder_al_modulo_administrador(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Gerencia'
        );

        $response = $this
            ->actingAs($usuario)
            ->get(route('administrador.dashboard'));

        $response->assertForbidden();
    }

    public function test_agente_no_puede_acceder_al_modulo_administrador(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'agente'
        );

        $response = $this
            ->actingAs($usuario)
            ->get(route('administrador.dashboard'));

        $response->assertForbidden();
    }

    public function test_administrador_no_puede_acceder_al_modulo_promotor(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Administrador'
        );

        $response = $this
            ->actingAs($usuario)
            ->get(route('promotor.dashboard'));

        $response->assertForbidden();
    }

    public function test_administrador_no_puede_acceder_al_modulo_jefe(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Administrador'
        );

        $response = $this
            ->actingAs($usuario)
            ->get(route('jefe.dashboard'));

        $response->assertForbidden();
    }

    public function test_administrador_no_puede_acceder_al_modulo_auditoria(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Administrador'
        );

        $response = $this
            ->actingAs($usuario)
            ->get(route('auditoria.dashboard'));

        $response->assertForbidden();
    }

    public function test_administrador_no_puede_acceder_al_modulo_gerencia(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Administrador'
        );

        $response = $this
            ->actingAs($usuario)
            ->get(route('gerencia.dashboard'));

        $response->assertForbidden();
    }

    public function test_administrador_no_puede_acceder_al_modulo_agente(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Administrador'
        );

        $response = $this
            ->actingAs($usuario)
            ->get(route('agente.dashboard'));

        $response->assertForbidden();
    }

    public function test_usuario_con_password_pendiente_no_puede_entrar_a_modulos(): void
    {
        /** @var Role $rol */
        $rol = Role::factory()->create([
            'nombre' => 'Administrador',
            'estado' => true,
        ]);

        /** @var Usuario $usuario */
        $usuario = Usuario::factory()
            ->requiereCambioPassword()
            ->create([
                'rol_id' => $rol->id,
            ]);

        $response = $this
            ->actingAs($usuario)
            ->get(route('administrador.dashboard'));

        $response->assertRedirect(
            route('password.cambiar')
        );
    }

    private function crearUsuarioConRol(
        string $nombreRol
    ): Usuario {
        /** @var Role $rol */
        $rol = Role::factory()->create([
            'nombre' => $nombreRol,
            'estado' => true,
        ]);

        /** @var Usuario $usuario */
        $usuario = Usuario::factory()->create([
            'rol_id' => $rol->id,
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        return $usuario;
    }
}
