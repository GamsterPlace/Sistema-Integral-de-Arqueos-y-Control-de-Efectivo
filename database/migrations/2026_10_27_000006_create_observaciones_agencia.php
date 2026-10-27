<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observaciones_arqueos_agencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arqueo_agencia_id')->constrained('arqueos_agencia')->restrictOnDelete();
            $table->foreignId('registrado_por')->constrained('usuarios')->restrictOnDelete();
            $table->text('descripcion');
            $table->string('estado', 20)->default('ABIERTA');
            $table->foreignId('cerrado_por')->nullable()->constrained('usuarios')->restrictOnDelete();
            $table->text('resolucion')->nullable();
            $table->timestamp('cerrado_at')->nullable();
            $table->timestamps();
            $table->index(['arqueo_agencia_id', 'estado']);
        });

        Schema::create('respuestas_observaciones_agencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observacion_id')->constrained('observaciones_arqueos_agencia')->restrictOnDelete();
            $table->foreignId('respondido_por')->constrained('usuarios')->restrictOnDelete();
            $table->text('respuesta');
            $table->timestamps();
        });

        Schema::create('evidencias_observaciones_agencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observacion_id')->constrained('observaciones_arqueos_agencia')->restrictOnDelete();
            $table->foreignId('respuesta_id')->nullable()->constrained('respuestas_observaciones_agencia')->restrictOnDelete();
            $table->foreignId('adjuntado_por')->constrained('usuarios')->restrictOnDelete();
            $table->string('nombre_original', 255);
            $table->string('ruta_privada', 500);
            $table->string('tipo_mime', 100);
            $table->unsignedBigInteger('tamano_bytes');
            $table->char('sha256', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias_observaciones_agencia');
        Schema::dropIfExists('respuestas_observaciones_agencia');
        Schema::dropIfExists('observaciones_arqueos_agencia');
    }
};
