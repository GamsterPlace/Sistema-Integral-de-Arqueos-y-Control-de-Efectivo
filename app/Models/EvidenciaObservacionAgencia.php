<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenciaObservacionAgencia extends Model
{
    protected $table = 'evidencias_observaciones_agencia';

    protected $fillable = [
        'observacion_id',
        'respuesta_id',
        'adjuntado_por',
        'nombre_original',
        'ruta_privada',
        'tipo_mime',
        'tamano_bytes',
        'sha256',
    ];

    protected function casts(): array
    {
        return [
            'tamano_bytes' => 'integer',
        ];
    }

    public function observacion(): BelongsTo
    {
        return $this->belongsTo(ObservacionArqueoAgencia::class, 'observacion_id');
    }

    public function respuesta(): BelongsTo
    {
        return $this->belongsTo(RespuestaObservacionAgencia::class, 'respuesta_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'adjuntado_por');
    }
}
