<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arqueos_agencia', function (Blueprint $table) {
            $table->id();
            $table->string('numero_arqueo', 40)->unique();
            $table->foreignId('agencia_id')->constrained('agencias')->restrictOnDelete();
            $table->foreignId('ventanilla_id')->constrained('ventanillas_agencia')->restrictOnDelete();
            $table->foreignId('jornada_id')->nullable()->constrained('jornadas_receptor')->restrictOnDelete();
            $table->foreignId('receptor_usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('receptor_entrante_usuario_id')->nullable()->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('jornada_entrante_id')->nullable()->constrained('jornadas_receptor')->restrictOnDelete();
            $table->foreignId('creado_por')->constrained('usuarios')->restrictOnDelete();
            // Responsables históricos de la certificación (no dependen del traslado posterior).
            $table->foreignId('jefe_caja_responsable_id')->nullable()->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('subjefe_caja_responsable_id')->nullable()->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('habilitacion_atrasada_id')->nullable()->constrained('habilitaciones_arqueos_agencia')->restrictOnDelete();
            $table->foreignId('arqueo_origen_id')->nullable()->constrained('arqueos_agencia')->restrictOnDelete();
            $table->string('tipo', 30); // ORDINARIO, ENTREGA, SORPRESIVO, AUDITORIA
            $table->string('estado', 35)->default('EN_PROCESO');
            $table->date('fecha_operativa');
            $table->dateTime('inicio_at');
            $table->dateTime('finalizado_at')->nullable();
            $table->boolean('fuera_fecha_ordinaria')->default(false);
            $table->boolean('cierra_turno')->default(false);
            $table->string('agencia_codigo_historico', 30);
            $table->string('agencia_nombre_historico', 150);
            $table->string('ventanilla_codigo_historico', 30);
            $table->string('receptor_nombre_historico', 210);
            $table->string('receptor_entrante_nombre_historico', 210)->nullable();
            $table->string('turno_nombre_historico', 80)->nullable();
            $table->decimal('total_billetes', 14, 2)->default(0);
            $table->decimal('total_monedas', 14, 2)->default(0);
            $table->decimal('total_otros_valores', 14, 2)->default(0);
            $table->decimal('total_arqueado', 14, 2)->default(0);
            $table->decimal('total_corte', 14, 2)->default(0);
            $table->decimal('diferencia', 14, 2)->default(0);
            $table->text('justificacion_diferencia')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('firmado_receptor_at')->nullable();
            $table->timestamp('certificado_at')->nullable();
            $table->timestamp('anulado_at')->nullable();
            $table->timestamps();
            $table->index(['agencia_id', 'fecha_operativa']);
            $table->index(['jornada_id', 'tipo', 'estado']);
            $table->index(['receptor_usuario_id', 'fecha_operativa'], 'idx_arqueo_agencia_receptor_fecha');
            $table->index(['tipo', 'estado']);
            $table->index(['receptor_usuario_id', 'tipo', 'estado'], 'idx_receptor_tipo_estado');
            $table->index(['jefe_caja_responsable_id', 'estado'], 'idx_jefe_historico_estado');
            $table->index(['subjefe_caja_responsable_id', 'estado'], 'idx_subjefe_historico_estado');
        });

        Schema::create('arqueo_agencia_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arqueo_agencia_id')->constrained('arqueos_agencia')->restrictOnDelete();
            $table->string('tipo', 20); // BILLETE, MONEDA, OTRO
            $table->decimal('denominacion', 10, 2)->nullable();
            $table->string('concepto', 120)->nullable();
            $table->unsignedInteger('cantidad')->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();
            $table->index(['arqueo_agencia_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arqueo_agencia_detalles');
        Schema::dropIfExists('arqueos_agencia');
    }
};
