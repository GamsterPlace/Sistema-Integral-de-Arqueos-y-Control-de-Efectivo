<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            ConfiguracionesSeeder::class,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call([
                UsuariosPruebaSeeder::class,
                AgenciasPruebaSeeder::class,
            ]);
        }
    }
}
