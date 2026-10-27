<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')
                ->constrained('regiones')
                ->restrictOnDelete();

            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->boolean('estado')->default(true);
            $table->timestamps();

            $table->unique(['region_id', 'nombre']);
            $table->index(['region_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
