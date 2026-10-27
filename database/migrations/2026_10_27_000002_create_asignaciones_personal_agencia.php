<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_personal_agencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('agencia_id')->constrained('agencias')->restrictOnDelete();
            $table->string('cargo', 30); // RECEPTOR, JEFE_CAJA, SUBJEFE_CAJA, JEFE_AGENCIA
            $table->string('tipo', 20)->default('PERMANENTE'); // PERMANENTE, TEMPORAL
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('estado')->default(true);
            $table->text('motivo')->nullable();
            $table->foreignId('asignado_por')->constrained('usuarios')->restrictOnDelete();
            $table->timestamps();
            $table->index(['usuario_id', 'fecha_inicio', 'fecha_fin'], 'idx_personal_periodo');
            $table->index(['agencia_id', 'cargo', 'estado'], 'idx_agencia_cargo_estado');
        });

        Schema::create('jornadas_receptor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_personal_id')->constrained('asignaciones_personal_agencia')->restrictOnDelete();
            $table->foreignId('receptor_usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('agencia_id')->constrained('agencias')->restrictOnDelete();
            $table->foreignId('ventanilla_id')->constrained('ventanillas_agencia')->restrictOnDelete();
            $table->foreignId('turno_id')->constrained('turnos_agencia')->restrictOnDelete();
            $table->date('fecha_operativa');
            $table->dateTime('inicio_programado');
            $table->dateTime('fin_programado');
            $table->dateTime('inicio_real')->nullable();
            $table->dateTime('fin_real')->nullable();
            $table->string('estado', 25)->default('PROGRAMADA');
            $table->foreignId('asignado_por')->constrained('usuarios')->restrictOnDelete();
            $table->timestamps();
            $table->index(['agencia_id', 'fecha_operativa']);
            $table->index(['receptor_usuario_id', 'fecha_operativa'], 'idx_jornada_receptor_fecha');
            $table->unique(['receptor_usuario_id', 'inicio_programado', 'turno_id'], 'uk_receptor_inicio_turno');
            $table->index(['agencia_id', 'ventanilla_id', 'fecha_operativa'], 'idx_ventanilla_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jornadas_receptor');
        Schema::dropIfExists('asignaciones_personal_agencia');
    }
};
