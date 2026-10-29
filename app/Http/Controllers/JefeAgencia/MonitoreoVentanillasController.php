<?php

namespace App\Http\Controllers\JefeAgencia;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\JornadaReceptor;
use App\Models\VentanillaAgencia;
use App\Services\Agencias\AccesoAgenciaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MonitoreoVentanillasController extends Controller
{
    /**
     * Monitoreo de ventanillas para Jefe de Agencia.
     *
     * El módulo es exclusivamente de consulta.
     */
    public function index(
        Request $request,
        AccesoAgenciaService $acceso
    ): View {
        $usuario = $request->user();

        abort_unless($usuario !== null, 401);

        $usuario->loadMissing('rol');

        abort_unless(
            $usuario->estado === 'ACTIVO'
            && $usuario->rol?->nombre === 'JefeAgencia'
            && (bool) $usuario->rol?->estado,
            403,
            'No tiene autorización para consultar este módulo.'
        );

        /*
        |--------------------------------------------------------------------------
        | Fecha operativa
        |--------------------------------------------------------------------------
        */

        $datos = $request->validate([
            'fecha' => ['nullable', 'date_format:Y-m-d'],
            'agencia_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $fecha = Carbon::parse(
            $datos['fecha'] ?? now()->toDateString()
        )->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Agencias autorizadas
        |--------------------------------------------------------------------------
        |
        | Se consulta el servicio oficial de asignaciones.
        | No se permite visualizar agencias ajenas al usuario.
        |
        */

        $asignaciones = $acceso->asignacionesVigentes(
            $usuario,
            $fecha
        );

        $agencias = $asignaciones
            ->filter(
                fn ($asignacion) =>
                    $asignacion->agencia !== null
                    && (bool) $asignacion->agencia->estado
            )
            ->pluck('agencia')
            ->unique('id')
            ->sortBy('nombre')
            ->values();

        $agenciaIds = $agencias
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $agenciaSeleccionadaId = isset($datos['agencia_id'])
            ? (int) $datos['agencia_id']
            : null;

        if ($agenciaSeleccionadaId !== null) {
            abort_unless(
                in_array(
                    $agenciaSeleccionadaId,
                    $agenciaIds,
                    true
                ),
                403,
                'No tiene acceso a la agencia seleccionada.'
            );

            $agenciaIds = [$agenciaSeleccionadaId];
        }

        /*
        |--------------------------------------------------------------------------
        | Ventanillas activas
        |--------------------------------------------------------------------------
        */

        $ventanillas = VentanillaAgencia::query()
            ->with('agencia')
            ->whereIn('agencia_id', $agenciaIds)
            ->where('estado', true)
            ->orderBy('agencia_id')
            ->orderBy('codigo')
            ->get();

        $ventanillaIds = $ventanillas
            ->pluck('id')
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Jornadas de la fecha seleccionada
        |--------------------------------------------------------------------------
        |
        | Cada jornada se evalúa independientemente.
        | Una ventanilla puede tener varios receptores o turnos.
        |
        */

        $jornadas = JornadaReceptor::query()
            ->with([
                'receptor',
                'turno',
            ])
            ->whereIn('agencia_id', $agenciaIds)
            ->whereIn('ventanilla_id', $ventanillaIds)
            ->whereDate(
                'fecha_operativa',
                $fecha->toDateString()
            )
            ->orderBy('inicio_programado')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Arqueos asociados a las jornadas
        |--------------------------------------------------------------------------
        |
        | Los registros anulados no cumplen la jornada.
        | Se considera realizado cuando existe firma del receptor
        | y el arqueo permanece vigente.
        |
        */

        $jornadaIds = $jornadas
            ->pluck('id')
            ->all();

        $arqueos = ArqueoAgencia::query()
            ->whereIn('agencia_id', $agenciaIds)
            ->whereIn('jornada_id', $jornadaIds)
            ->whereDate(
                'fecha_operativa',
                $fecha->toDateString()
            )
            ->where('tipo', 'ORDINARIO')
            ->whereIn('estado', [
                'PENDIENTE',
                'CERTIFICADO',
            ])
            ->whereNull('anulado_at')
            ->whereNotNull('firmado_receptor_at')
            ->orderByDesc('id')
            ->get();

        $arqueosPorJornada = $arqueos
            ->groupBy('jornada_id');

        $jornadasPorVentanilla = $jornadas
            ->groupBy('ventanilla_id');

        /*
        |--------------------------------------------------------------------------
        | Construcción de la información del monitoreo
        |--------------------------------------------------------------------------
        */

        $monitoreo = $ventanillas->map(
            function ($ventanilla) use (
                $jornadasPorVentanilla,
                $arqueosPorJornada
            ) {
                $jornadasVentanilla =
                    $jornadasPorVentanilla->get(
                        $ventanilla->id,
                        collect()
                    );

                $detalleJornadas = $jornadasVentanilla
                    ->map(function ($jornada) use (
                        $arqueosPorJornada
                    ) {
                        $arqueo = $arqueosPorJornada
                            ->get(
                                $jornada->id,
                                collect()
                            )
                            ->first(
                                fn ($registro) =>
                                    (int) $registro->receptor_usuario_id
                                    === (int) $jornada->receptor_usuario_id
                            );

                        $realizado = $arqueo !== null;

                        $nombreReceptor =
                            $jornada->receptor?->nombre_completo
                            ?: (
                                $jornada->receptor?->usuario
                                ?? 'Receptor no disponible'
                            );

                        return [
                            'id' => $jornada->id,
                            'receptor' => $nombreReceptor,
                            'receptor_usuario_id' =>
                                $jornada->receptor_usuario_id,
                            'turno' =>
                                $jornada->turno?->nombre
                                ?? 'Sin turno',
                            'estado_jornada' => $jornada->estado,
                            'inicio_programado' =>
                                $jornada->inicio_programado,
                            'fin_programado' =>
                                $jornada->fin_programado,
                            'inicio_real' =>
                                $jornada->inicio_real,
                            'fin_real' =>
                                $jornada->fin_real,
                            'arqueo_realizado' => $realizado,
                            'arqueo_id' => $arqueo?->id,
                            'numero_arqueo' =>
                                $arqueo?->numero_arqueo,
                            'estado_arqueo' =>
                                $arqueo?->estado,
                            'total_arqueado' =>
                                $arqueo?->total_arqueado,
                            'diferencia' =>
                                $arqueo?->diferencia,
                            'estado_visual' =>
                                $realizado
                                    ? 'VERDE'
                                    : 'ROJO',
                        ];
                    })
                    ->values();

                $totalJornadas = $detalleJornadas->count();

                $jornadasRealizadas = $detalleJornadas
                    ->where('arqueo_realizado', true)
                    ->count();

                $jornadasPendientes =
                    $totalJornadas - $jornadasRealizadas;

                /*
                | GRIS: sin jornadas asignadas.
                | VERDE: todas las jornadas tienen arqueo.
                | ROJO: existe al menos una jornada pendiente.
                */

                $estadoVisual = match (true) {
                    $totalJornadas === 0 => 'GRIS',
                    $jornadasPendientes > 0 => 'ROJO',
                    default => 'VERDE',
                };

                return [
                    'id' => $ventanilla->id,
                    'codigo' => $ventanilla->codigo,
                    'descripcion' =>
                        $ventanilla->descripcion,
                    'agencia_id' =>
                        $ventanilla->agencia_id,
                    'agencia' =>
                        $ventanilla->agencia?->nombre
                        ?? 'Agencia',
                    'estado_visual' => $estadoVisual,
                    'total_jornadas' => $totalJornadas,
                    'jornadas_realizadas' =>
                        $jornadasRealizadas,
                    'jornadas_pendientes' =>
                        $jornadasPendientes,
                    'jornadas' => $detalleJornadas,
                ];
            }
        )->values();

        /*
        |--------------------------------------------------------------------------
        | Indicadores generales
        |--------------------------------------------------------------------------
        */

        $totalVentanillas = $monitoreo->count();

        $ventanillasVerdes = $monitoreo
            ->where('estado_visual', 'VERDE')
            ->count();

        $ventanillasRojas = $monitoreo
            ->where('estado_visual', 'ROJO')
            ->count();

        $ventanillasGrises = $monitoreo
            ->where('estado_visual', 'GRIS')
            ->count();

        $totalJornadas = $monitoreo
            ->sum('total_jornadas');

        $jornadasRealizadas = $monitoreo
            ->sum('jornadas_realizadas');

        $jornadasPendientes = $monitoreo
            ->sum('jornadas_pendientes');

        $porcentajeCumplimiento = $totalJornadas > 0
            ? round(
                ($jornadasRealizadas / $totalJornadas) * 100,
                1
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Vista independiente
        |--------------------------------------------------------------------------
        */

        return view(
            'jefe-agencia.monitoreo-ventanillas.index',
            [
                'fecha' => $fecha,
                'agencias' => $agencias,
                'agenciaSeleccionadaId' =>
                    $agenciaSeleccionadaId,
                'monitoreo' => $monitoreo,
                'totalVentanillas' => $totalVentanillas,
                'ventanillasVerdes' => $ventanillasVerdes,
                'ventanillasRojas' => $ventanillasRojas,
                'ventanillasGrises' => $ventanillasGrises,
                'totalJornadas' => $totalJornadas,
                'jornadasRealizadas' =>
                    $jornadasRealizadas,
                'jornadasPendientes' =>
                    $jornadasPendientes,
                'porcentajeCumplimiento' =>
                    $porcentajeCumplimiento,
            ]
        );
    }
}
