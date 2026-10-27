<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habilitaciones_arqueos_agencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jornada_id')->constrained('jornadas_receptor')->restrictOnDelete();
            $table->foreignId('autorizado_por')->constrained('usuarios')->restrictOnDelete();
            $table->text('motivo');
            $table->string('estado', 20)->default('PENDIENTE');
            $table->timestamp('autorizado_at');
            $table->timestamp('utilizado_at')->nullable();
            $table->timestamps();
            $table->index(['jornada_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habilitaciones_arqueos_agencia');
    }
};
