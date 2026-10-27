<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'rol_id' => Role::factory(),
            'usuario' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
            'ultimo_acceso' => null,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'INACTIVO',
        ]);
    }

    public function bloqueado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'BLOQUEADO',
        ]);
    }

    public function requiereCambioPassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'requiere_cambio_password' => true,
        ]);
    }

    public function passwordVencida(): static
    {
        return $this->state(fn (array $attributes) => [
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now()->subMonth(),
        ]);
    }

    public function conRol(
        string $nombreRol,
        bool $estadoRol = true
    ): static {
        return $this->state(function () use (
            $nombreRol,
            $estadoRol
        ): array {
            return [
                'rol_id' => Role::factory()->create([
                    'nombre' => $nombreRol,
                    'estado' => $estadoRol,
                ])->id,
            ];
        });
    }
}
