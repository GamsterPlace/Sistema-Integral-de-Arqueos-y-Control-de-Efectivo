<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsignacionPersonalAgencia extends Model
{
    protected $table = 'asignaciones_personal_agencia';

    protected $fillable = [
        'usuario_id',
        'agencia_id',
        'cargo',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'motivo',
        'asignado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'estado' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class, 'agencia_id');
    }

    public function asignador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'asignado_por');
    }

    public function jornadas(): HasMany
    {
        return $this->hasMany(JornadaReceptor::class, 'asignacion_personal_id');
    }
}
