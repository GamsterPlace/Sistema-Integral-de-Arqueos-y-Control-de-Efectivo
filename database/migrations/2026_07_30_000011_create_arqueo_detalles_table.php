<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arqueo_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arqueo_id')
                ->constrained('arqueos')
                ->cascadeOnDelete();

            $table->enum('tipo', ['BILLETE', 'MONEDA']);
            $table->decimal('denominacion', 8, 2);
            $table->unsignedInteger('cantidad')->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['arqueo_id', 'tipo', 'denominacion'],
                'uk_arqueo_tipo_denominacion'
            );

            $table->index(['arqueo_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arqueo_detalles');
    }
};
