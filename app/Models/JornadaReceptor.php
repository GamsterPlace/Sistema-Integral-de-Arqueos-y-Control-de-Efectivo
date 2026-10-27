<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JornadaReceptor extends Model
{
    protected $table = 'jornadas_receptor';

    protected $fillable = [
        'asignacion_personal_id',
        'receptor_usuario_id',
        'agencia_id',
        'ventanilla_id',
        'turno_id',
        'fecha_operativa',
        'inicio_programado',
        'fin_programado',
        'inicio_real',
        'fin_real',
        'estado',
        'asignado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_operativa' => 'date',
            'inicio_programado' => 'datetime',
            'fin_programado' => 'datetime',
            'inicio_real' => 'datetime',
            'fin_real' => 'datetime',
        ];
    }

    public function asignacionPersonal(): BelongsTo
    {
        return $this->belongsTo(AsignacionPersonalAgencia::class, 'asignacion_personal_id');
    }

    public function receptor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'receptor_usuario_id');
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class, 'agencia_id');
    }

    public function ventanilla(): BelongsTo
    {
        return $this->belongsTo(VentanillaAgencia::class, 'ventanilla_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoAgencia::class, 'turno_id');
    }

    public function asignador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'asignado_por');
    }

    public function arqueos(): HasMany
    {
        return $this->hasMany(ArqueoAgencia::class, 'jornada_id');
    }

    public function arqueosEntrantes(): HasMany
    {
        return $this->hasMany(ArqueoAgencia::class, 'jornada_entrante_id');
    }

    public function habilitaciones(): HasMany
    {
        return $this->hasMany(HabilitacionArqueoAgencia::class, 'jornada_id');
    }
}
