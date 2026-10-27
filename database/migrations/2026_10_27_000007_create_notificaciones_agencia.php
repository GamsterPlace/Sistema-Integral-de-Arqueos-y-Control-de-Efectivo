<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_arqueos_agencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destinatario_usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('agencia_id')->nullable()->constrained('agencias')->restrictOnDelete();
            $table->foreignId('arqueo_agencia_id')->nullable()->constrained('arqueos_agencia')->restrictOnDelete();
            $table->string('tipo', 60);
            $table->string('titulo', 180);
            $table->text('mensaje');
            $table->timestamp('leida_at')->nullable();
            $table->timestamp('resuelta_at')->nullable();
            $table->timestamps();
            $table->index(['destinatario_usuario_id', 'leida_at'], 'idx_notificacion_agencia_lectura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_arqueos_agencia');
    }
};
