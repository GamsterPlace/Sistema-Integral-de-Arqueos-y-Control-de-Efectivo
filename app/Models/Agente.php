<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agente extends Model
{
    use HasFactory;

    protected $table = 'agentes';

    protected $fillable = [
        'usuario_id',
        'promotor_id',
        'ruta_id',
        'codigo_agente',
        'nombre_negocio',
        'direccion',
        'propietario',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }

    public function promotor(): BelongsTo
    {
        return $this->belongsTo(
            Promotor::class,
            'promotor_id'
        );
    }

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(
            Ruta::class,
            'ruta_id'
        );
    }

    public function arqueos(): HasMany
    {
        return $this->hasMany(
            Arqueo::class,
            'agente_id'
        );
    }
}
