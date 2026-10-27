<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $fecha = now();

            /*
            |--------------------------------------------------------------------------
            | REGIÓN Y RUTA DE PRUEBA
            |--------------------------------------------------------------------------
            */

            $regionId = DB::table('regiones')
                ->where('nombre', 'Región Central')
                ->value('id');

            if (! $regionId) {
                $regionId = DB::table('regiones')->insertGetId([
                    'nombre' => 'Región Central',
                    'estado' => true,
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]);
            }

            $rutaId = DB::table('rutas')
                ->where('codigo', 'RUTA-001')
                ->value('id');

            if (! $rutaId) {
                $rutaId = DB::table('rutas')->insertGetId([
                    'region_id' => $regionId,
                    'codigo' => 'RUTA-001',
                    'nombre' => 'Ruta Central',
                    'estado' => true,
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | ADMINISTRADOR
            |--------------------------------------------------------------------------
            */

            $adminId = $this->crearUsuario(
                rol: 'Administrador',
                usuario: 'admin',
                nombres: 'Administrador',
                apellidos: 'General'
            );

            /*
            |--------------------------------------------------------------------------
            | JEFE DE AGENTES
            |--------------------------------------------------------------------------
            */

            $jefeId = $this->crearUsuario(
                rol: 'jefedeAgentes',
                usuario: 'jefe',
                nombres: 'Carlos',
                apellidos: 'Hernández'
            );

            /*
            |--------------------------------------------------------------------------
            | PROMOTOR
            |--------------------------------------------------------------------------
            */

            $promotorId = $this->crearUsuario(
                rol: 'Promotor',
                usuario: 'promotor',
                nombres: 'Luis',
                apellidos: 'Martínez'
            );

            /*
            |--------------------------------------------------------------------------
            | AUDITORÍA
            |--------------------------------------------------------------------------
            */

            $auditoriaId = $this->crearUsuario(
                rol: 'Auditoria',
                usuario: 'auditoria',
                nombres: 'Ana',
                apellidos: 'López'
            );

            /*
            |--------------------------------------------------------------------------
            | GERENCIA
            |--------------------------------------------------------------------------
            */

            $gerenciaId = $this->crearUsuario(
                rol: 'Gerencia',
                usuario: 'gerencia',
                nombres: 'María',
                apellidos: 'Rodríguez'
            );

            // USUARIOS DE AGENCIAS PARA DESARROLLO
            $receptorId = $this->crearUsuario(rol: 'Receptor', usuario: 'receptor', nombres: 'Receptor', apellidos: 'Prueba');
            $jefeCajaId = $this->crearUsuario(rol: 'JefeCaja', usuario: 'jefecaja', nombres: 'Jefe', apellidos: 'Caja');
            $subjefeCajaId = $this->crearUsuario(rol: 'SubjefeCaja', usuario: 'subjefecaja', nombres: 'Subjefe', apellidos: 'Caja');
            $jefeAgenciaId = $this->crearUsuario(rol: 'JefeAgencia', usuario: 'jefeagencia', nombres: 'Jefe', apellidos: 'Agencia');

            /*
            |--------------------------------------------------------------------------
            | AGENTE
            |--------------------------------------------------------------------------
            */

            $agenteUsuarioId = $this->crearUsuario(
                rol: 'agente',
                usuario: 'agente',
                nombres: 'José',
                apellidos: 'Pérez'
            );

            DB::table('agentes')->updateOrInsert(
                [
                    'usuario_id' => $agenteUsuarioId,
                ],
                [
                    'ruta_id' => $rutaId,
                    'codigo_agente' => 'AG0001',
                    'nombre_negocio' => 'Almacenes El Centro',
                    'nombre_propietario' => 'José Pérez',
                    'direccion' => 'San Juan Sacatepequez',
                    'estado' => 'ACTIVO',
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | ASIGNAR PROMOTOR A LA RUTA
            |--------------------------------------------------------------------------
            */

            DB::table('asignaciones_promotor_ruta')->updateOrInsert(
                [
                    'promotor_usuario_id' => $promotorId,
                    'ruta_id' => $rutaId,
                    'fecha_inicio' => now()->toDateString(),
                ],
                [
                    'fecha_fin' => null,
                    'estado' => true,
                    'asignado_por' => $jefeId,
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]
            );
        });
    }

    private function crearUsuario(
        string $rol,
        string $usuario,
        string $nombres,
        string $apellidos
    ): int {
        $rolId = DB::table('roles')
            ->where('nombre', $rol)
            ->value('id');

        if (! $rolId) {
            throw new \RuntimeException(
                "No existe el rol {$rol}. Ejecuta primero RolesSeeder."
            );
        }

        $usuarioId = DB::table('usuarios')
            ->where('usuario', $usuario)
            ->value('id');

        if (! $usuarioId) {
            $password = Str::password(18);
            $usuarioId = DB::table('usuarios')->insertGetId([
                'rol_id' => $rolId,
                'usuario' => $usuario,
                'password' => Hash::make($password),
                'estado' => 'ACTIVO',
                'requiere_cambio_password' => false,
                'fecha_ultimo_cambio_password' => now(),
                'ultimo_acceso' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command?->line("Usuario: {$usuario} | Contraseña temporal: {$password}");
        } else {
            DB::table('usuarios')
                ->where('id', $usuarioId)
                ->update([
                    'rol_id' => $rolId,
                    'updated_at' => now(),
                ]);
        }

        DB::table('datos_personales')->updateOrInsert(
            [
                'usuario_id' => $usuarioId,
            ],
            [
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return $usuarioId;
    }
}
