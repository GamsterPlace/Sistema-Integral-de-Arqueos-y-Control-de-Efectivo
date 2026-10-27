<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutorizacionCertificacionAgencia extends Model
{
    protected $table = 'autorizaciones_certificacion_agencia';

    protected $fillable = [
        'arqueo_agencia_id',
        'certificador_sustituto_id',
        'autorizado_por',
        'motivo',
        'autorizado_at',
        'utilizado_at',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'autorizado_at' => 'datetime',
            'utilizado_at' => 'datetime',
        ];
    }

    public function arqueo(): BelongsTo
    {
        return $this->belongsTo(ArqueoAgencia::class, 'arqueo_agencia_id');
    }

    public function certificadorSustituto(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'certificador_sustituto_id');
    }

    public function autorizador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por');
    }
}
