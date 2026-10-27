<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RespuestaObservacionAgencia extends Model
{
    protected $table = 'respuestas_observaciones_agencia';

    protected $fillable = [
        'observacion_id',
        'respondido_por',
        'respuesta',
    ];

    public function observacion(): BelongsTo
    {
        return $this->belongsTo(ObservacionArqueoAgencia::class, 'observacion_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'respondido_por');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(EvidenciaObservacionAgencia::class, 'respuesta_id');
    }
}
