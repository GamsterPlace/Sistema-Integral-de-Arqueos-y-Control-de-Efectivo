<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArqueoDetalle extends Model
{
    use HasFactory;

    protected $table = 'arqueo_detalles';

    protected $fillable = [
        'arqueo_id',
        'tipo',
        'denominacion',
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
        return $this->belongsTo(
            Arqueo::class,
            'arqueo_id'
        );
    }
}
