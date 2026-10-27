<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->unique()
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->foreignId('ruta_id')
                ->constrained('rutas')
                ->restrictOnDelete();

            $table->string('codigo_agente', 30)->unique();
            $table->string('nombre_negocio', 150);
            $table->string('nombre_propietario', 150);
            $table->string('direccion', 255);
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'SUSPENDIDO'])
                ->default('ACTIVO');
            $table->timestamps();

            $table->index(['ruta_id', 'estado']);
            $table->index('nombre_negocio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agentes');
    }
};
