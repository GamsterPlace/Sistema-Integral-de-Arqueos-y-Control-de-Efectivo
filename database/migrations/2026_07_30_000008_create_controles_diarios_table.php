<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controles_diarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agente_id')
                ->constrained('agentes')
                ->restrictOnDelete();

            $table->date('fecha');
            $table->enum('tipo', ['NO_ATENDIO']);
            $table->text('anotacion');

            $table->foreignId('registrado_por')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->timestamp('registrado_at');
            $table->boolean('vigente')->default(true);

            $table->foreignId('cancelado_por')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->timestamp('cancelado_at')->nullable();
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamps();

            $table->unique(['agente_id', 'fecha']);
            $table->index(['fecha', 'tipo', 'vigente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controles_diarios');
    }
};
