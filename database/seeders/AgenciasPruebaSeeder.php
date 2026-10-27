<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgenciasPruebaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $ahora = now();
            $adminId = DB::table('usuarios')->where('usuario', 'admin')->value('id');
            if (! $adminId) {
                throw new \RuntimeException('Ejecuta primero UsuariosPruebaSeeder.');
            }

            DB::table('agencias')->updateOrInsert(
                ['codigo' => 'AG-PRUEBA-01'],
                ['nombre' => 'Agencia de Desarrollo', 'direccion' => 'Agencia ficticia para pruebas', 'estado' => true, 'created_at' => $ahora, 'updated_at' => $ahora]
            );
            $agenciaId = DB::table('agencias')->where('codigo', 'AG-PRUEBA-01')->value('id');

            foreach (['V-01', 'V-02'] as $codigo) {
                DB::table('ventanillas_agencia')->updateOrInsert(
                    ['agencia_id' => $agenciaId, 'codigo' => $codigo],
                    ['descripcion' => 'Ventanilla de desarrollo '.$codigo, 'estado' => true, 'created_at' => $ahora, 'updated_at' => $ahora]
                );
            }

            foreach ([['Mañana', '08:00:00', '13:00:00'], ['Tarde', '13:00:00', '18:00:00']] as [$nombre, $inicio, $fin]) {
                DB::table('turnos_agencia')->updateOrInsert(
                    ['nombre' => $nombre],
                    ['hora_inicio' => $inicio, 'hora_fin' => $fin, 'cruza_medianoche' => false, 'estado' => true, 'created_at' => $ahora, 'updated_at' => $ahora]
                );
            }

            foreach (['receptor' => 'RECEPTOR', 'jefecaja' => 'JEFE_CAJA', 'subjefecaja' => 'SUBJEFE_CAJA', 'jefeagencia' => 'JEFE_AGENCIA'] as $nombreUsuario => $cargo) {
                $usuarioId = DB::table('usuarios')->where('usuario', $nombreUsuario)->value('id');
                if (! $usuarioId) {
                    throw new \RuntimeException('No existe el usuario de desarrollo: '.$nombreUsuario);
                }
                DB::table('asignaciones_personal_agencia')->updateOrInsert(
                    ['usuario_id' => $usuarioId, 'agencia_id' => $agenciaId, 'cargo' => $cargo, 'tipo' => 'PERMANENTE', 'fecha_inicio' => '2026-01-01'],
                    ['fecha_fin' => null, 'estado' => true, 'motivo' => 'Datos de desarrollo', 'asignado_por' => $adminId, 'created_at' => $ahora, 'updated_at' => $ahora]
                );
            }
        });
    }
}
