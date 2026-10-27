<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')
                ->constrained('roles')
                ->restrictOnDelete();

            $table->string('usuario', 50)->unique();
            $table->string('password');
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'BLOQUEADO'])
                ->default('ACTIVO');

            $table->boolean('requiere_cambio_password')->default(true);
            $table->timestamp('fecha_ultimo_cambio_password')->nullable();
            $table->timestamp('ultimo_acceso')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['rol_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
