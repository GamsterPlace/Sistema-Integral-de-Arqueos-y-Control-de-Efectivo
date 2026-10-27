<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firmas_arqueos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arqueo_id')
                ->constrained('arqueos')
                ->restrictOnDelete();

            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->enum('tipo_firma', [
                'REALIZADOR',
                'CERTIFICADOR',
                'VALIDADOR',
                'ANULADOR',
            ]);

            /*
             * Copia histórica de la identidad y rol del firmante.
             */
            $table->string('rol_firmante', 50);
            $table->string('nombres_historicos', 100);
            $table->string('apellidos_historicos', 100);

            /*
             * Firma electrónica interna.
             * No se almacena imagen ni firma manuscrita.
             */
            $table->char('hash_documento', 64);
            $table->char('firma_electronica', 64);
            $table->string('algoritmo', 30)->default('HMAC-SHA256');
            $table->unsignedSmallInteger('version_firma')->default(1);

            $table->timestamp('fecha_firma');
            $table->boolean('valida')->default(true);
            $table->timestamps();

            $table->unique(
                ['arqueo_id', 'tipo_firma'],
                'uk_arqueo_tipo_firma'
            );

            $table->index(['usuario_id', 'fecha_firma']);
            $table->index('hash_documento');
            $table->index('firma_electronica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firmas_arqueos');
    }
};
