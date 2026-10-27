<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionArqueoAgencia extends Model
{
    protected $table = 'notificaciones_arqueos_agencia';

    protected $fillable = [
        'destinatario_usuario_id',
        'agencia_id',
        'arqueo_agencia_id',
        'tipo',
        'titulo',
        'mensaje',
        'leida_at',
        'resuelta_at',
    ];

    protected function casts(): array
    {
        return [
            'leida_at' => 'datetime',
            'resuelta_at' => 'datetime',
        ];
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'destinatario_usuario_id');
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class, 'agencia_id');
    }

    public function arqueo(): BelongsTo
    {
        return $this->belongsTo(ArqueoAgencia::class, 'arqueo_agencia_id');
    }
}
