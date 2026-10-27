<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FirmaArqueoAgencia extends Model
{
    protected $table = 'firmas_arqueos_agencia';

    protected $fillable = [
        'arqueo_agencia_id',
        'usuario_id',
        'tipo_firma',
        'rol_firmante',
        'nombres_historicos',
        'apellidos_historicos',
        'hash_documento',
        'firma_electronica',
        'algoritmo',
        'version_firma',
        'fecha_firma',
        'valida',
    ];

    protected function casts(): array
    {
        return [
            'version_firma' => 'integer',
            'fecha_firma' => 'datetime',
            'valida' => 'boolean',
        ];
    }

    public function arqueo(): BelongsTo
    {
        return $this->belongsTo(ArqueoAgencia::class, 'arqueo_agencia_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function anulacion(): HasOne
    {
        return $this->hasOne(AnulacionArqueoAgencia::class, 'firma_id');
    }
}
