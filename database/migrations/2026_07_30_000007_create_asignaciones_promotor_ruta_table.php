<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_promotor_ruta', function (Blueprint $table) {
            $table->id();

            $table->foreignId('promotor_usuario_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->foreignId('ruta_id')
                ->constrained('rutas')
                ->restrictOnDelete();

            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('estado')->default(true);

            $table->foreignId('asignado_por')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(['ruta_id', 'estado']);
            $table->index(['promotor_usuario_id', 'estado']);
            $table->index(
                ['ruta_id', 'fecha_inicio', 'fecha_fin'],
                'idx_asignacion_ruta_periodo'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_promotor_ruta');
    }
};
