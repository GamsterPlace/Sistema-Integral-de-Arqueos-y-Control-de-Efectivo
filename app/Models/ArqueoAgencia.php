<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ArqueoAgencia extends Model
{
    protected $table = 'arqueos_agencia';

    protected $fillable = [
        'numero_arqueo',
        'agencia_id',
        'ventanilla_id',
        'jornada_id',
        'receptor_usuario_id',
        'receptor_entrante_usuario_id',
        'jornada_entrante_id',
        'creado_por',
        'jefe_caja_responsable_id',
        'subjefe_caja_responsable_id',
        'habilitacion_atrasada_id',
        'arqueo_origen_id',
        'tipo',
        'estado',
        'fecha_operativa',
        'inicio_at',
        'finalizado_at',
        'fuera_fecha_ordinaria',
        'cierra_turno',
        'agencia_codigo_historico',
        'agencia_nombre_historico',
        'ventanilla_codigo_historico',
        'receptor_nombre_historico',
        'receptor_entrante_nombre_historico',
        'turno_nombre_historico',
        'total_billetes',
        'total_monedas',
        'total_otros_valores',
        'total_arqueado',
        'total_corte',
        'diferencia',
        'justificacion_diferencia',
        'observaciones',
        'firmado_receptor_at',
        'certificado_at',
        'anulado_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_operativa' => 'date',
            'inicio_at' => 'datetime',
            'finalizado_at' => 'datetime',
            'fuera_fecha_ordinaria' => 'boolean',
            'cierra_turno' => 'boolean',
            'total_billetes' => 'decimal:2',
            'total_monedas' => 'decimal:2',
            'total_otros_valores' => 'decimal:2',
            'total_arqueado' => 'decimal:2',
            'total_corte' => 'decimal:2',
            'diferencia' => 'decimal:2',
            'firmado_receptor_at' => 'datetime',
            'certificado_at' => 'datetime',
            'anulado_at' => 'datetime',
        ];
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class, 'agencia_id');
    }

    public function ventanilla(): BelongsTo
    {
        return $this->belongsTo(VentanillaAgencia::class, 'ventanilla_id');
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(JornadaReceptor::class, 'jornada_id');
    }

    public function receptor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'receptor_usuario_id');
    }

    public function receptorEntrante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'receptor_entrante_usuario_id');
    }

    public function jornadaEntrante(): BelongsTo
    {
        return $this->belongsTo(JornadaReceptor::class, 'jornada_entrante_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function jefeCajaResponsable(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'jefe_caja_responsable_id');
    }

    public function subjefeCajaResponsable(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'subjefe_caja_responsable_id');
    }

    public function habilitacionAtrasada(): BelongsTo
    {
        return $this->belongsTo(HabilitacionArqueoAgencia::class, 'habilitacion_atrasada_id');
    }

    public function arqueoOrigen(): BelongsTo
    {
        return $this->belongsTo(ArqueoAgencia::class, 'arqueo_origen_id');
    }

    public function repeticiones(): HasMany
    {
        return $this->hasMany(ArqueoAgencia::class, 'arqueo_origen_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ArqueoAgenciaDetalle::class, 'arqueo_agencia_id');
    }

    public function firmas(): HasMany
    {
        return $this->hasMany(FirmaArqueoAgencia::class, 'arqueo_agencia_id');
    }

    public function anulacion(): HasOne
    {
        return $this->hasOne(AnulacionArqueoAgencia::class, 'arqueo_agencia_id');
    }

    public function autorizacionesCertificacion(): HasMany
    {
        return $this->hasMany(AutorizacionCertificacionAgencia::class, 'arqueo_agencia_id');
    }

    public function observacionesAuditoria(): HasMany
    {
        return $this->hasMany(ObservacionArqueoAgencia::class, 'arqueo_agencia_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionArqueoAgencia::class, 'arqueo_agencia_id');
    }
}
