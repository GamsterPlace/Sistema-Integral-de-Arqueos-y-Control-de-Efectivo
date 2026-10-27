<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TurnoAgencia extends Model
{
    protected $table = 'turnos_agencia';

    protected $fillable = [
        'nombre',
        'hora_inicio',
        'hora_fin',
        'cruza_medianoche',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'cruza_medianoche' => 'boolean',
            'estado' => 'boolean',
        ];
    }

    public function jornadas(): HasMany
    {
        return $this->hasMany(JornadaReceptor::class, 'turno_id');
    }
}
