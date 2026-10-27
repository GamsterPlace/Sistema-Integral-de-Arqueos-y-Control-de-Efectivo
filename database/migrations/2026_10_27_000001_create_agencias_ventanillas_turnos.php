<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agencias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 150);
            $table->string('direccion', 255)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        Schema::create('ventanillas_agencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agencia_id')->constrained('agencias')->restrictOnDelete();
            $table->string('codigo', 30);
            $table->string('descripcion', 150)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->unique(['agencia_id', 'codigo']);
        });

        Schema::create('turnos_agencia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('cruza_medianoche')->default(false);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos_agencia');
        Schema::dropIfExists('ventanillas_agencia');
        Schema::dropIfExists('agencias');
    }
};
