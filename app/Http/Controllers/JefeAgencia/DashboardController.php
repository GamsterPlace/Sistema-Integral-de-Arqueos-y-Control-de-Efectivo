<?php

namespace App\Http\Controllers\JefeAgencia;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\AsignacionOperativaReceptor;
use App\Models\JornadaReceptor;
use App\Models\VentanillaAgencia;
use App\Services\Agencias\AccesoAgenciaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        AccesoAgenciaService $acceso
    ): View {
        $usuario = $request->user();

        $usuario->loadMissing('rol');

        abort_unless(
            $usuario->rol?->nombre === 'JefeAgencia',
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Agencias autorizadas
        |--------------------------------------------------------------------------
        */

        $asignaciones = $acceso
            ->asignacionesVigentes($usuario)
            ->filter(
                fn ($asignacion) =>
                    (bool) $asignacion->agencia?->estado
            )
            ->values();

        $agenciaIds = $asignaciones
            ->pluck('agencia_id')
            ->unique()
            ->values()
            ->all();

        $fecha = now()->toDateString();

        $agencias = $asignaciones
            ->pluck('agencia')
            ->filter()
            ->unique('id')
            ->values();

        $totalAgencias = $agencias->count();

        $totalVentanillas = VentanillaAgencia::query()
            ->whereIn('agencia_id', $agenciaIds)
            ->where('estado', true)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Asignaciones operativas vigentes
        |--------------------------------------------------------------------------
        |
        | Se consultan las asignaciones del día sin
        | depender de que el receptor haya iniciado sesión.
        |
        */

        $asignacionesOperativas = AsignacionOperativaReceptor::query()
            ->whereIn('agencia_id', $agenciaIds)
            ->vigentes()
            ->whereHas(
                'asignacionPersonal',
                function (Builder $query) use ($fecha): void {
                    $query
                        ->where('cargo', 'RECEPTOR')
                        ->where('estado', true)
                        ->whereDate('fecha_inicio', '<=', $fecha)
                        ->where(function (Builder $subquery) use ($fecha): void {
                            $subquery
                                ->whereNull('fecha_fin')
                                ->orWhereDate('fecha_fin', '>=', $fecha);
                        });
                }
            )
            ->whereHas(
                'ventanilla',
                fn (Builder $query) =>
                    $query->where('estado', true)
            )
            ->whereHas(
                'turno',
                fn (Builder $query) =>
                    $query->where('estado', true)
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Jornadas registradas hoy
        |--------------------------------------------------------------------------
        */

        $jornadasHoy = JornadaReceptor::query()
            ->whereIn('agencia_id', $agenciaIds)
            ->whereDate('fecha_operativa', $fecha)
            ->get([
                'id',
                'agencia_id',
                'ventanilla_id',
                'turno_id',
                'receptor_usuario_id',
                'estado',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Jornadas esperadas
        |--------------------------------------------------------------------------
        |
        | Se consideran:
        | 1. Jornadas ya registradas.
        | 2. Asignaciones vigentes que aún no tienen jornada.
        |
        | Se evita contar dos veces una jornada generada.
        |
        */

        $jornadasEsperadas = $jornadasHoy->map(
            fn ($jornada) => [
                'jornada_id' => (int) $jornada->id,
                'agencia_id' => (int) $jornada->agencia_id,
                'ventanilla_id' => (int) $jornada->ventanilla_id,
                'turno_id' => (int) $jornada->turno_id,
                'receptor_usuario_id' => (int) $jornada->receptor_usuario_id,
            ]
        );

        foreach ($asignacionesOperativas as $operativa) {
            $yaExiste = $jornadasEsperadas->contains(
                fn (array $jornada) =>
                    $jornada['agencia_id'] === (int) $operativa->agencia_id
                    && $jornada['ventanilla_id'] === (int) $operativa->ventanilla_id
                    && $jornada['turno_id'] === (int) $operativa->turno_id
                    && $jornada['receptor_usuario_id'] === (int) $operativa->receptor_usuario_id
            );

            if (!$yaExiste) {
                $jornadasEsperadas->push([
                    'jornada_id' => null,
                    'agencia_id' => (int) $operativa->agencia_id,
                    'ventanilla_id' => (int) $operativa->ventanilla_id,
                    'turno_id' => (int) $operativa->turno_id,
                    'receptor_usuario_id' => (int) $operativa->receptor_usuario_id,
                ]);
            }
        }

        $totalJornadas = $jornadasEsperadas->count();

        /*
        |--------------------------------------------------------------------------
        | Arqueos del día
        |--------------------------------------------------------------------------
        */

        $arqueosHoy = ArqueoAgencia::query()
            ->whereIn('agencia_id', $agenciaIds)
            ->whereDate('fecha_operativa', $fecha)
            ->get([
                'id',
                'jornada_id',
                'receptor_usuario_id',
                'tipo',
                'estado',
                'firmado_receptor_at',
                'anulado_at',
                'diferencia',
            ]);

        $arqueosOrdinariosValidos = $arqueosHoy
            ->filter(
                fn ($arqueo) =>
                    $arqueo->tipo === 'ORDINARIO'
                    && $arqueo->anulado_at === null
                    && $arqueo->estado !== 'ANULADO'
                    && $arqueo->firmado_receptor_at !== null
            );

        /*
        |--------------------------------------------------------------------------
        | Cumplimiento de jornadas
        |--------------------------------------------------------------------------
        */

        $jornadasCumplidas = $jornadasEsperadas
            ->filter(function (array $jornada) use (
                $arqueosOrdinariosValidos
            ): bool {
                if ($jornada['jornada_id'] === null) {
                    return false;
                }

                return $arqueosOrdinariosValidos->contains(
                    fn ($arqueo) =>
                        (int) $arqueo->jornada_id ===
                            $jornada['jornada_id']
                        && (int) $arqueo->receptor_usuario_id ===
                            $jornada['receptor_usuario_id']
                );
            })
            ->count();

        $jornadasPendientes = max(
            0,
            $totalJornadas - $jornadasCumplidas
        );

        $porcentajeCumplimiento = $totalJornadas > 0
            ? round(
                ($jornadasCumplidas / $totalJornadas) * 100,
                1
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Indicadores de arqueos
        |--------------------------------------------------------------------------
        */

        $arqueosPendientesCertificacion =
            $arqueosOrdinariosValidos
                ->where('estado', 'PENDIENTE')
                ->count();

        $arqueosCertificados =
            $arqueosOrdinariosValidos
                ->where('estado', 'CERTIFICADO')
                ->count();

        $arqueosAnulados = $arqueosHoy
            ->filter(
                fn ($arqueo) =>
                    $arqueo->estado === 'ANULADO'
                    || $arqueo->anulado_at !== null
            )
            ->count();

        $arqueosConDiferencia =
            $arqueosOrdinariosValidos
                ->filter(
                    fn ($arqueo) =>
                        abs((float) $arqueo->diferencia) > 0.00001
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Últimos arqueos
        |--------------------------------------------------------------------------
        */

        $ultimosArqueos = ArqueoAgencia::query()
            ->whereIn('agencia_id', $agenciaIds)
            ->whereDate('fecha_operativa', $fecha)
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'id',
                'numero_arqueo',
                'fecha_operativa',
                'agencia_nombre_historico',
                'ventanilla_codigo_historico',
                'receptor_nombre_historico',
                'turno_nombre_historico',
                'tipo',
                'estado',
                'total_arqueado',
                'diferencia',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        return view('dashboards.jefe-agencia', [
            'fecha' => $fecha,
            'agencias' => $agencias,
            'totalAgencias' => $totalAgencias,
            'totalVentanillas' => $totalVentanillas,
            'totalJornadas' => $totalJornadas,
            'jornadasCumplidas' => $jornadasCumplidas,
            'jornadasPendientes' => $jornadasPendientes,
            'porcentajeCumplimiento' => $porcentajeCumplimiento,
            'arqueosPendientesCertificacion' =>
                $arqueosPendientesCertificacion,
            'arqueosCertificados' => $arqueosCertificados,
            'arqueosAnulados' => $arqueosAnulados,
            'arqueosConDiferencia' => $arqueosConDiferencia,
            'ultimosArqueos' => $ultimosArqueos,
        ]);
    }
}
