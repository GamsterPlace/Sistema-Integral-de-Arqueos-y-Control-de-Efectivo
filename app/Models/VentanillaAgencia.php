<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VentanillaAgencia extends Model
{
    protected $table = 'ventanillas_agencia';

    protected $fillable = [
        'agencia_id',
        'codigo',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class, 'agencia_id');
    }

    public function jornadas(): HasMany
    {
        return $this->hasMany(JornadaReceptor::class, 'ventanilla_id');
    }

    public function arqueos(): HasMany
    {
        return $this->hasMany(ArqueoAgencia::class, 'ventanilla_id');
    }
}
