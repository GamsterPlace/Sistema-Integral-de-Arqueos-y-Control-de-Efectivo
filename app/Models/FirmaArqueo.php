<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirmaArqueo extends Model
{
    use HasFactory;

    protected $table = 'firmas_arqueos';

    protected $fillable = [
        'arqueo_id',
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
        return $this->belongsTo(
            Arqueo::class,
            'arqueo_id'
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }
}
