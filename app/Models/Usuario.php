<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'rol_id',
        'usuario',
        'password',
        'estado',
        'requiere_cambio_password',
        'fecha_ultimo_cambio_password',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'requiere_cambio_password' => 'boolean',
            'fecha_ultimo_cambio_password' => 'datetime',
            'ultimo_acceso' => 'datetime',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'rol_id'
        );
    }

    public function datosPersonales(): HasOne
    {
        return $this->hasOne(
            DatosPersonales::class,
            'usuario_id'
        );
    }

    public function agente(): HasOne
    {
        return $this->hasOne(
            Agente::class,
            'usuario_id'
        );
    }

    public function historialPasswords(): HasMany
    {
        return $this->hasMany(
            HistorialPassword::class,
            'usuario_id'
        );
    }

    public function getNombreCompletoAttribute(): string
    {
        $this->loadMissing('datosPersonales');

        if (! $this->datosPersonales) {
            return $this->usuario;
        }

        return trim(
            $this->datosPersonales->nombres.' '.
            $this->datosPersonales->apellidos
        );
    }

    public function arqueosCreados(): HasMany
    {
        return $this->hasMany(
            Arqueo::class,
            'creado_por'
        );
    }

    public function firmasArqueos(): HasMany
    {
        return $this->hasMany(
            FirmaArqueo::class,
            'usuario_id'
        );
    }

    // Relaciones del módulo de Agencias ECOSABA.
    public function asignacionesAgencia(): HasMany
    {
        return $this->hasMany(AsignacionPersonalAgencia::class, 'usuario_id');
    }

    public function jornadasReceptor(): HasMany
    {
        return $this->hasMany(JornadaReceptor::class, 'receptor_usuario_id');
    }

    public function arqueosAgenciaComoReceptor(): HasMany
    {
        return $this->hasMany(ArqueoAgencia::class, 'receptor_usuario_id');
    }

    public function arqueosAgenciaComoEntrante(): HasMany
    {
        return $this->hasMany(ArqueoAgencia::class, 'receptor_entrante_usuario_id');
    }

    public function arqueosAgenciaCreados(): HasMany
    {
        return $this->hasMany(ArqueoAgencia::class, 'creado_por');
    }

    public function firmasArqueosAgencia(): HasMany
    {
        return $this->hasMany(FirmaArqueoAgencia::class, 'usuario_id');
    }

    public function observacionesAuditoriaAgencia(): HasMany
    {
        return $this->hasMany(ObservacionArqueoAgencia::class, 'registrado_por');
    }

    public function notificacionesArqueosAgencia(): HasMany
    {
        return $this->hasMany(NotificacionArqueoAgencia::class, 'destinatario_usuario_id');
    }
}
