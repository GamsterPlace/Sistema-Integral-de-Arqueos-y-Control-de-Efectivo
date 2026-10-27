<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialPassword extends Model
{
    protected $table = 'historial_passwords';

    protected $fillable = [
        'usuario_id',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }
}
