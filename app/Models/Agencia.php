<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agencia extends Model
{
    protected $table = 'agencias';

    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function ventanillas(): HasMany
    {
        return $this->hasMany(VentanillaAgencia::class, 'agencia_id');
    }

    public function asignacionesPersonal(): HasMany
    {
        return $this->hasMany(AsignacionPersonalAgencia::class, 'agencia_id');
    }

    public function jornadas(): HasMany
    {
        return $this->hasMany(JornadaReceptor::class, 'agencia_id');
    }

    public function arqueos(): HasMany
    {
        return $this->hasMany(ArqueoAgencia::class, 'agencia_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionArqueoAgencia::class, 'agencia_id');
    }
}
