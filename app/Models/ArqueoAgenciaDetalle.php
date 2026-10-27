<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArqueoAgenciaDetalle extends Model
{
    protected $table = 'arqueo_agencia_detalles';

    protected $fillable = [
        'arqueo_agencia_id',
        'tipo',
        'denominacion',
        'concepto',
        'cantidad',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'denominacion' => 'decimal:2',
            'cantidad' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    public function arqueo(): BelongsTo
    {
        return $this->belongsTo(ArqueoAgencia::class, 'arqueo_agencia_id');
    }
}
