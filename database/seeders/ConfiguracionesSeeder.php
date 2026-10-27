<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('configuraciones')->upsert([
            [
                'clave' => 'minutos_inactividad',
                'valor' => '20',
                'tipo' => 'NUMERO',
                'descripcion' => 'Minutos de inactividad antes de cerrar la sesión',
                'editable' => true,
            ],
            [
                'clave' => 'cambio_password_mensual',
                'valor' => 'true',
                'tipo' => 'BOOLEANO',
                'descripcion' => 'Obliga el cambio de contraseña al iniciar cada mes',
                'editable' => true,
            ],
            [
                'clave' => 'texto_certificacion',
                'valor' => null,
                'tipo' => 'TEXTO',
                'descripcion' => 'Texto oficial de certificación de los arqueos',
                'editable' => true,
            ],
            [
                'clave' => 'formato_correlativo',
                'valor' => 'ARQ-{FECHA}-{CORRELATIVO}',
                'tipo' => 'TEXTO',
                'descripcion' => 'Formato del número de arqueo',
                'editable' => true,
            ],
            [
                'clave' => 'algoritmo_firma_electronica',
                'valor' => 'HMAC-SHA256',
                'tipo' => 'TEXTO',
                'descripcion' => 'Algoritmo de firma electrónica interna',
                'editable' => false,
            ],
            ['clave' => 'limite_diferencia_agencias', 'valor' => null, 'tipo' => 'NUMERO', 'descripcion' => 'Límite de diferencia de agencias; configurar antes de activar alertas gerenciales', 'editable' => true],
        ], ['clave'], ['valor', 'tipo', 'descripcion', 'editable']);
    }
}
