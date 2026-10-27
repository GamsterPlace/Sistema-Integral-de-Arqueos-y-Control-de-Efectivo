<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignaciones_promotor_ruta', function (Blueprint $table) {
            $table->string('tipo_asignacion', 20)
                ->default('PERMANENTE')
                ->after('estado');

            $table->string('motivo', 500)
                ->nullable()
                ->after('tipo_asignacion');

            $table->index(
                ['ruta_id', 'tipo_asignacion', 'estado'],
                'idx_asignacion_ruta_tipo_estado'
            );

            $table->index(
                ['promotor_usuario_id', 'tipo_asignacion', 'estado'],
                'idx_asignacion_promotor_tipo_estado'
            );
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_promotor_ruta', function (Blueprint $table) {
            $table->dropIndex('idx_asignacion_ruta_tipo_estado');
            $table->dropIndex('idx_asignacion_promotor_tipo_estado');

            $table->dropColumn([
                'tipo_asignacion',
                'motivo',
            ]);
        });
    }
};
