<?php

namespace App\Services\Agencias;

use App\Models\Agencia;
use App\Models\AsignacionOperativaReceptor;
use App\Models\JornadaReceptor;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GeneracionJornadasService
{
    /**
     * Obtiene o genera las jornadas de un receptor
     * para una fecha operativa.
     *
     * No genera jornadas futuras ni históricas.
     */
    public function jornadasDelReceptor(
        int $usuarioId,
        ?CarbonInterface $fecha = null
    ): Collection {
        $fecha ??= today();

        $dia = $fecha->toDateString();

        if ($dia !== today()->toDateString()) {
            return JornadaReceptor::query()
                ->with(['agencia', 'ventanilla', 'turno'])
                ->where('receptor_usuario_id', $usuarioId)
                ->whereDate('fecha_operativa', $dia)
                ->orderBy('inicio_programado')
                ->get();
        }

        $asignaciones = AsignacionOperativaReceptor::query()
            ->vigentes($fecha)
            ->deReceptor($usuarioId)
            ->with([
                'asignacionPersonal',
                'agencia',
                'ventanilla',
                'turno',
            ])
            ->orderBy('agencia_id')
            ->orderBy('turno_id')
            ->get();

        foreach ($asignaciones as $asignacion) {
            $this->obtenerOCrear($asignacion, $fecha);
        }

        return JornadaReceptor::query()
            ->with(['agencia', 'ventanilla', 'turno'])
            ->where('receptor_usuario_id', $usuarioId)
            ->whereDate('fecha_operativa', $dia)
            ->orderBy('inicio_programado')
            ->get();
    }

    /**
     * Obtiene o crea una jornada diaria para una
     * asignación operativa vigente.
     */
    public function obtenerOCrear(
        AsignacionOperativaReceptor $asignacion,
        ?CarbonInterface $fecha = null
    ): JornadaReceptor {
        $fecha ??= today();

        $dia = $fecha->toDateString();

        if ($dia !== today()->toDateString()) {
            throw ValidationException::withMessages([
                'jornada' => 'La generación automática solamente está permitida para la fecha actual.',
            ]);
        }

        return DB::transaction(function () use ($asignacion, $fecha, $dia): JornadaReceptor {
            /*
             * Bloqueo por agencia para evitar que dos
             * solicitudes generen jornadas simultáneas.
             */
            Agencia::query()
                ->whereKey($asignacion->agencia_id)
                ->lockForUpdate()
                ->firstOrFail();

            $asignacion = AsignacionOperativaReceptor::query()
                ->with([
                    'asignacionPersonal',
                    'agencia',
                    'ventanilla',
                    'turno',
                ])
                ->findOrFail($asignacion->getKey());

            if (!$asignacion->estaVigente($fecha)) {
                throw ValidationException::withMessages([
                    'jornada' => 'La asignación operativa ya no está vigente.',
                ]);
            }

            $personal = $asignacion->asignacionPersonal;

            if (
                !$personal
                || !$personal->estado
                || $personal->cargo !== 'RECEPTOR'
                || !in_array($personal->tipo, ['PERMANENTE', 'TEMPORAL'], true)
                || (int) $personal->usuario_id !== (int) $asignacion->receptor_usuario_id
                || (int) $personal->agencia_id !== (int) $asignacion->agencia_id
                || !$personal->fecha_inicio
                || $personal->fecha_inicio->toDateString() > $dia
                || (
                    $personal->fecha_fin
                    && $personal->fecha_fin->toDateString() < $dia
                )
            ) {
                throw ValidationException::withMessages([
                    'jornada' => 'El receptor no tiene una asignación de personal vigente en esta agencia.',
                ]);
            }

            if (
                !$asignacion->agencia?->estado
                || !$asignacion->ventanilla?->estado
                || (int) $asignacion->ventanilla->agencia_id !== (int) $asignacion->agencia_id
                || !$asignacion->turno?->estado
            ) {
                throw ValidationException::withMessages([
                    'jornada' => 'La agencia, ventanilla o turno no se encuentra disponible.',
                ]);
            }

            $turno = $asignacion->turno;

            $inicio = Carbon::parse(
                $dia . ' ' . substr((string) $turno->hora_inicio, 0, 8)
            );

            $fin = Carbon::parse(
                $dia . ' ' . substr((string) $turno->hora_fin, 0, 8)
            );

            if ($turno->cruza_medianoche) {
                $fin->addDay();
            }

            if ($fin->lessThanOrEqualTo($inicio)) {
                throw ValidationException::withMessages([
                    'turno' => 'El horario configurado para este turno es inválido.',
                ]);
            }

            /*
             * Si ya existe la jornada para esta
             * combinación, se reutiliza.
             */
            $existente = JornadaReceptor::query()
                ->where('asignacion_personal_id', $personal->getKey())
                ->where('receptor_usuario_id', $asignacion->receptor_usuario_id)
                ->where('agencia_id', $asignacion->agencia_id)
                ->where('ventanilla_id', $asignacion->ventanilla_id)
                ->where('turno_id', $asignacion->turno_id)
                ->whereDate('fecha_operativa', $dia)
                ->first();

            if ($existente) {
                return $existente;
            }

            /*
             * Se impide superposición de horarios
             * del receptor o de la ventanilla.
             */
            $conflicto = JornadaReceptor::query()
                ->where('inicio_programado', '<', $fin->toDateTimeString())
                ->where('fin_programado', '>', $inicio->toDateTimeString())
                ->where(function (Builder $query) use ($asignacion): void {
                    $query
                        ->where(
                            'receptor_usuario_id',
                            $asignacion->receptor_usuario_id
                        )
                        ->orWhere(function (Builder $subquery) use ($asignacion): void {
                            $subquery
                                ->where('agencia_id', $asignacion->agencia_id)
                                ->where('ventanilla_id', $asignacion->ventanilla_id);
                        });
                })
                ->exists();

            if ($conflicto) {
                throw ValidationException::withMessages([
                    'jornada' => 'Existe otra jornada con horario superpuesto para este receptor o ventanilla.',
                ]);
            }

            return JornadaReceptor::query()->create([
                'asignacion_personal_id' => $personal->getKey(),
                'receptor_usuario_id' => $asignacion->receptor_usuario_id,
                'agencia_id' => $asignacion->agencia_id,
                'ventanilla_id' => $asignacion->ventanilla_id,
                'turno_id' => $asignacion->turno_id,
                'fecha_operativa' => $dia,
                'inicio_programado' => $inicio->toDateTimeString(),
                'fin_programado' => $fin->toDateTimeString(),
                'estado' => 'PROGRAMADA',
                'asignado_por' => $asignacion->asignado_por,
            ]);
        }, 3);
    }
}
