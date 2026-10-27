<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anulaciones_arqueos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arqueo_id')
                ->unique()
                ->constrained('arqueos')
                ->restrictOnDelete();

            $table->foreignId('anulado_por')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->foreignId('firma_id')
                ->unique()
                ->constrained('firmas_arqueos')
                ->restrictOnDelete();

            $table->string('rol_anulador', 50);
            $table->string('nombres_anulador_historicos', 100);
            $table->string('apellidos_anulador_historicos', 100);
            $table->text('motivo');
            $table->timestamp('fecha_anulacion');
            $table->timestamps();

            $table->index(['anulado_por', 'fecha_anulacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anulaciones_arqueos');
    }
};
