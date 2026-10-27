<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObservacionArqueoAgencia extends Model
{
    protected $table = 'observaciones_arqueos_agencia';

    protected $fillable = [
        'arqueo_agencia_id',
        'registrado_por',
        'descripcion',
        'estado',
        'cerrado_por',
        'resolucion',
        'cerrado_at',
    ];

    protected function casts(): array
    {
        return [
            'cerrado_at' => 'datetime',
        ];
    }

    public function arqueo(): BelongsTo
    {
        return $this->belongsTo(ArqueoAgencia::class, 'arqueo_agencia_id');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }

    public function cerrador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cerrado_por');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaObservacionAgencia::class, 'observacion_id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(EvidenciaObservacionAgencia::class, 'observacion_id');
    }
}
