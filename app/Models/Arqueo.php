<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arqueo extends Model
{
    use HasFactory;

    protected $table = 'arqueos';

    protected $fillable = [
        'numero_arqueo',
        'agente_id',
        'creado_por',
        'habilitacion_atrasada_id',
        'tipo',
        'estado',
        'fecha_arqueo',
        'hora_inicio',
        'hora_fin',
        'fuera_fecha_ordinaria',
        'codigo_agente_historico',
        'nombre_negocio_historico',
        'nombre_propietario_historico',
        'direccion_historica',
        'ruta_historica',
        'region_historica',
        'total_billetes',
        'total_monedas',
        'total_arqueado',
        'saldo_sistema',
        'diferencia',
        'certificacion',
        'observaciones',
        'pendiente_certificacion_at',
        'certificado_at',
        'anulado_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_arqueo' => 'date',
            'hora_inicio' => 'datetime',
            'hora_fin' => 'datetime',
            'fuera_fecha_ordinaria' => 'boolean',
            'total_billetes' => 'decimal:2',
            'total_monedas' => 'decimal:2',
            'total_arqueado' => 'decimal:2',
            'saldo_sistema' => 'decimal:2',
            'diferencia' => 'decimal:2',
            'pendiente_certificacion_at' => 'datetime',
            'certificado_at' => 'datetime',
            'anulado_at' => 'datetime',
        ];
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(
            Agente::class,
            'agente_id'
        );
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'creado_por'
        );
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(
            ArqueoDetalle::class,
            'arqueo_id'
        );
    }

    public function firmas(): HasMany
    {
        return $this->hasMany(
            FirmaArqueo::class,
            'arqueo_id'
        );
    }

    public function estaEnBorrador(): bool
    {
        return $this->estado === 'BORRADOR';
    }

    public function estaPendienteCertificacion(): bool
    {
        return $this->estado === 'PENDIENTE_CERTIFICACION';
    }

    public function estaCertificado(): bool
    {
        return $this->estado === 'CERTIFICADO';
    }

    public function estaAnulado(): bool
    {
        return $this->estado === 'ANULADO';
    }
}
