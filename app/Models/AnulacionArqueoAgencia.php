<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnulacionArqueoAgencia extends Model
{
    protected $table = 'anulaciones_arqueos_agencia';

    protected $fillable = [
        'arqueo_agencia_id',
        'anulado_por',
        'firma_id',
        'motivo',
        'fecha_anulacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_anulacion' => 'datetime',
        ];
    }

    public function arqueo(): BelongsTo
    {
        return $this->belongsTo(ArqueoAgencia::class, 'arqueo_agencia_id');
    }

    public function anulador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'anulado_por');
    }

    public function firma(): BelongsTo
    {
        return $this->belongsTo(FirmaArqueoAgencia::class, 'firma_id');
    }
}
