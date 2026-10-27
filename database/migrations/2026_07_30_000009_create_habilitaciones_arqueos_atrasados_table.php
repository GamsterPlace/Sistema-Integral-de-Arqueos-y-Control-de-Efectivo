<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habilitaciones_arqueos_atrasados', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agente_id')
                ->constrained('agentes')
                ->restrictOnDelete();

            $table->date('fecha_autorizada');
            $table->text('motivo');

            $table->foreignId('autorizado_por')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->timestamp('autorizado_at');
            $table->enum('estado', ['PENDIENTE', 'UTILIZADA', 'CANCELADA'])
                ->default('PENDIENTE');
            $table->timestamp('utilizado_at')->nullable();
            $table->timestamps();

            $table->index(
                ['agente_id', 'fecha_autorizada', 'estado'],
                'idx_habilitacion_agente_fecha'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habilitaciones_arqueos_atrasados');
    }
};
