
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('firmas_arqueos_agencia', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arqueo_agencia_id');
            $table->foreignId('usuario_id');

            $table->string('tipo_firma', 30);
            // RECEPTOR, ENTRANTE, CERTIFICADOR, AUDITOR, ANULADOR

            $table->string('rol_firmante', 50);

            $table->string('nombres_historicos', 100);
            $table->string('apellidos_historicos', 100);

            $table->char('hash_documento', 64);
            $table->char('firma_electronica', 64);

            $table->string('algoritmo', 30)
                ->default('HMAC-SHA256');

            $table->unsignedSmallInteger('version_firma')
                ->default(1);

            $table->timestamp('fecha_firma');

            $table->boolean('valida')
                ->default(true);

            $table->timestamps();

            // Relaciones
            $table->foreign(
                'arqueo_agencia_id',
                'fk_firma_ag_arqueo'
            )
                ->references('id')
                ->on('arqueos_agencia')
                ->restrictOnDelete();

            $table->foreign(
                'usuario_id',
                'fk_firma_ag_usuario'
            )
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();

            // Una firma por tipo dentro del arqueo
            $table->unique(
                ['arqueo_agencia_id', 'tipo_firma'],
                'uk_agencia_arqueo_tipo_firma'
            );

            $table->index(
                ['usuario_id', 'fecha_firma'],
                'idx_firma_ag_usuario_fecha'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | ANULACIONES DE ARQUEOS DE AGENCIAS
        |--------------------------------------------------------------------------
        */

        Schema::create('anulaciones_arqueos_agencia', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arqueo_agencia_id');
            $table->foreignId('anulado_por');
            $table->foreignId('firma_id')->nullable();

            $table->text('motivo');
            $table->timestamp('fecha_anulacion');

            $table->timestamps();

            // Relaciones
            $table->foreign(
                'arqueo_agencia_id',
                'fk_anul_ag_arqueo'
            )
                ->references('id')
                ->on('arqueos_agencia')
                ->restrictOnDelete();

            $table->foreign(
                'anulado_por',
                'fk_anul_ag_usuario'
            )
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();

            $table->foreign(
                'firma_id',
                'fk_anul_ag_firma'
            )
                ->references('id')
                ->on('firmas_arqueos_agencia')
                ->restrictOnDelete();

            // Un arqueo solo puede anularse una vez
            $table->unique(
                'arqueo_agencia_id',
                'uk_anul_ag_arqueo'
            );

            // Una firma no puede asociarse a varias anulaciones
            $table->unique(
                'firma_id',
                'uk_anul_ag_firma'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | AUTORIZACIONES EXCEPCIONALES DE CERTIFICACIÓN
        |--------------------------------------------------------------------------
        */

        Schema::create('autorizaciones_certificacion_agencia', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arqueo_agencia_id');
            $table->foreignId('certificador_sustituto_id');
            $table->foreignId('autorizado_por');

            $table->text('motivo');

            $table->timestamp('autorizado_at');
            $table->timestamp('utilizado_at')->nullable();

            $table->string('estado', 20)
                ->default('AUTORIZADA');

            $table->timestamps();

            // Relaciones
            $table->foreign(
                'arqueo_agencia_id',
                'fk_aut_cert_arqueo'
            )
                ->references('id')
                ->on('arqueos_agencia')
                ->restrictOnDelete();

            $table->foreign(
                'certificador_sustituto_id',
                'fk_aut_cert_sustituto'
            )
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();

            $table->foreign(
                'autorizado_por',
                'fk_aut_cert_admin'
            )
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();

            // Consultar autorizaciones según estado
            $table->index(
                ['arqueo_agencia_id', 'estado'],
                'idx_aut_cert_arqueo_estado'
            );

            // Evitar autorizaciones duplicadas
            $table->unique(
                [
                    'arqueo_agencia_id',
                    'certificador_sustituto_id'
                ],
                'uk_sustituto_arqueo'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autorizaciones_certificacion_agencia');
        Schema::dropIfExists('anulaciones_arqueos_agencia');
        Schema::dropIfExists('firmas_arqueos_agencia');
    }
};
