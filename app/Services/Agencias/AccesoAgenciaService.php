<?php

namespace App\Services\Agencias;

use App\Models\Agencia;
use App\Models\AsignacionPersonalAgencia;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AccesoAgenciaService
{
    private const CARGOS_POR_ROL = [
        'Receptor' => 'RECEPTOR',
        'JefeCaja' => 'JEFE_CAJA',
        'SubjefeCaja' => 'SUBJEFE_CAJA',
        'JefeAgencia' => 'JEFE_AGENCIA',
    ];

    /**
     * Obtiene las asignaciones vigentes del usuario, limitadas al cargo de su rol.
     *
     * @return Collection<int, AsignacionPersonalAgencia>
     */
    public function asignacionesVigentes(Usuario $usuario, ?Carbon $fecha = null): Collection
    {
        $usuario->loadMissing('rol');
        $cargo = self::CARGOS_POR_ROL[$usuario->rol?->nombre ?? ''] ?? null;

        if ($usuario->estado !== 'ACTIVO' || ! $usuario->rol?->estado || $cargo === null) {
            return new Collection();
        }

        $fechaOperativa = ($fecha ?? now())->toDateString();

        return AsignacionPersonalAgencia::query()
            ->with('agencia')
            ->where('usuario_id', $usuario->getKey())
            ->where('cargo', $cargo)
            ->whereIn('tipo', ['PERMANENTE', 'TEMPORAL'])
            ->where('estado', true)
            ->whereDate('fecha_inicio', '<=', $fechaOperativa)
            ->where(function ($query) use ($fechaOperativa) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $fechaOperativa);
            })
            ->orderBy('agencia_id')
            ->get();
    }

    public function tieneAcceso(Usuario $usuario, int $agenciaId, ?Carbon $fecha = null): bool
    {
        return $this->asignacionesVigentes($usuario, $fecha)
            ->contains(fn (AsignacionPersonalAgencia $asignacion) =>
                (int) $asignacion->agencia_id === $agenciaId
            );
    }

    public function autorizar(Usuario $usuario, int $agenciaId, ?Carbon $fecha = null): void
    {
        abort_unless(
            $this->tieneAcceso($usuario, $agenciaId, $fecha),
            403,
            'No tiene una asignación vigente que autorice el acceso a esta agencia.'
        );
    }
}
