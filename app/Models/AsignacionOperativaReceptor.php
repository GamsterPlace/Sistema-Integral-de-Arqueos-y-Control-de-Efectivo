<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionOperativaReceptor extends Model
{
    protected $table = 'asignaciones_operativas_receptor';

    protected $fillable = [
        'asignacion_personal_id',
        'receptor_usuario_id',
        'agencia_id',
        'ventanilla_id',
        'turno_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'motivo_cambio',
        'asignado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function asignacionPersonal(): BelongsTo
    {
        return $this->belongsTo(
            AsignacionPersonalAgencia::class,
            'asignacion_personal_id'
        );
    }

    public function receptor(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'receptor_usuario_id'
        );
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(
            Agencia::class,
            'agencia_id'
        );
    }

    public function ventanilla(): BelongsTo
    {
        return $this->belongsTo(
            VentanillaAgencia::class,
            'ventanilla_id'
        );
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(
            TurnoAgencia::class,
            'turno_id'
        );
    }

    public function asignador(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'asignado_por'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Consultas de vigencia
    |--------------------------------------------------------------------------
    */

    public function scopeVigentes(
        Builder $query,
        ?CarbonInterface $fecha = null
    ): Builder {
        $fecha ??= today();

        $dia = $fecha->toDateString();

        return $query
            ->where('estado', true)
            ->whereDate('fecha_inicio', '<=', $dia)
            ->where(function (Builder $consulta) use ($dia): void {
                $consulta
                    ->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $dia);
            });
    }

    public function scopeDeReceptor(
        Builder $query,
        int $usuarioId
    ): Builder {
        return $query->where(
            'receptor_usuario_id',
            $usuarioId
        );
    }

    public function scopeDeAgencia(
        Builder $query,
        int $agenciaId
    ): Builder {
        return $query->where(
            'agencia_id',
            $agenciaId
        );
    }

    public function scopeDeVentanilla(
        Builder $query,
        int $ventanillaId
    ): Builder {
        return $query->where(
            'ventanilla_id',
            $ventanillaId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos auxiliares
    |--------------------------------------------------------------------------
    */

    public function estaVigente(
        ?CarbonInterface $fecha = null
    ): bool {
        $fecha ??= today();

        $dia = $fecha->toDateString();

        if (!$this->estado || !$this->fecha_inicio) {
            return false;
        }

        if ($this->fecha_inicio->toDateString() > $dia) {
            return false;
        }

        if (
            $this->fecha_fin !== null
            && $this->fecha_fin->toDateString() < $dia
        ) {
            return false;
        }

        return true;
    }

    public function esIndefinida(): bool
    {
        return $this->fecha_fin === null;
    }
}
