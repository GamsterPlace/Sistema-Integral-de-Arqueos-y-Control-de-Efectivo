<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HabilitacionArqueoAgencia extends Model
{
    protected $table = 'habilitaciones_arqueos_agencia';

    protected $fillable = [
        'jornada_id',
        'autorizado_por',
        'motivo',
        'estado',
        'autorizado_at',
        'utilizado_at',
    ];

    protected function casts(): array
    {
        return [
            'autorizado_at' => 'datetime',
            'utilizado_at' => 'datetime',
        ];
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(JornadaReceptor::class, 'jornada_id');
    }

    public function autorizador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por');
    }

    public function arqueos(): HasMany
    {
        return $this->hasMany(ArqueoAgencia::class, 'habilitacion_atrasada_id');
    }
}
