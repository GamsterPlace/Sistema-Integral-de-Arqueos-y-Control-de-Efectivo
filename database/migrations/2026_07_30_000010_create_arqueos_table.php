<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arqueos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_arqueo', 40)->unique();

            $table->foreignId('agente_id')
                ->constrained('agentes')
                ->restrictOnDelete();

            $table->foreignId('creado_por')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->foreignId('habilitacion_atrasada_id')
                ->nullable()
                ->constrained('habilitaciones_arqueos_atrasados')
                ->nullOnDelete();

            $table->enum('tipo', [
                'DIARIO_AGENTE',
                'VISITA_PROMOTOR',
                'VISITA_AUDITORIA',
            ]);

            $table->enum('estado', [
                'BORRADOR',
                'PENDIENTE_CERTIFICACION',
                'CERTIFICADO',
                'ANULADO',
            ])->default('BORRADOR');

            $table->date('fecha_arqueo');
            $table->timestamp('hora_inicio');
            $table->timestamp('hora_fin')->nullable();
            $table->boolean('fuera_fecha_ordinaria')->default(false);

            /*
             * Copia histórica del agente, su ruta y región.
             * Estos valores no cambian aunque posteriormente se editen
             * los catálogos o los datos del agente.
             */
            $table->string('codigo_agente_historico', 30);
            $table->string('nombre_negocio_historico', 150);
            $table->string('nombre_propietario_historico', 150);
            $table->string('direccion_historica', 255);
            $table->string('ruta_historica', 130);
            $table->string('region_historica', 100);

            $table->decimal('total_billetes', 14, 2)->default(0);
            $table->decimal('total_monedas', 14, 2)->default(0);
            $table->decimal('total_arqueado', 14, 2)->default(0);
            $table->decimal('saldo_sistema', 14, 2)->default(0);
            $table->decimal('diferencia', 14, 2)->default(0);

            $table->text('certificacion')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamp('pendiente_certificacion_at')->nullable();
            $table->timestamp('certificado_at')->nullable();
            $table->timestamp('anulado_at')->nullable();
            $table->timestamps();

            $table->index(['agente_id', 'fecha_arqueo']);
            $table->index(['tipo', 'estado']);
            $table->index(['fecha_arqueo', 'estado']);
            $table->index('creado_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arqueos');
    }
};
