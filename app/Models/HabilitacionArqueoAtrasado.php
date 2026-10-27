<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabilitacionArqueoAtrasado extends Model
{
    use HasFactory;

    protected $table = 'habilitaciones_arqueos_atrasados';

    protected $fillable = [
        'agente_id',
        'fecha_autorizada',
        'motivo',
        'autorizado_por',
        'autorizado_at',
        'estado',
        'utilizado_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_autorizada' => 'date',
            'autorizado_at' => 'datetime',
            'utilizado_at' => 'datetime',
        ];
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(
            Agente::class,
            'agente_id'
        );
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'autorizado_por'
        );
    }
}
