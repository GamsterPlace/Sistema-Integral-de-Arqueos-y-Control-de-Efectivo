<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $table = 'regiones';

    protected $fillable = [
        'nombre',
        'estado',
    ];

    public function rutas(): HasMany
    {
        return $this->hasMany(Ruta::class);
    }
}
