
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_operativas_receptor', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asignacion_personal_id')
                ->constrained('asignaciones_personal_agencia')
                ->restrictOnDelete();

            $table->foreignId('receptor_usuario_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->foreignId('agencia_id')
                ->constrained('agencias')
                ->restrictOnDelete();

            $table->foreignId('ventanilla_id')
                ->constrained('ventanillas_agencia')
                ->restrictOnDelete();

            $table->foreignId('turno_id')
                ->constrained('turnos_agencia')
                ->restrictOnDelete();

            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            $table->boolean('estado')->default(true);

            $table->text('motivo_cambio')->nullable();

            $table->foreignId('asignado_por')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(
                ['receptor_usuario_id', 'estado', 'fecha_inicio', 'fecha_fin'],
                'aor_receptor_vigencia_idx'
            );

            $table->index(
                ['agencia_id', 'ventanilla_id', 'estado'],
                'aor_ventanilla_estado_idx'
            );

            $table->index(
                ['asignacion_personal_id', 'estado'],
                'aor_personal_estado_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_operativas_receptor');
    }
};
