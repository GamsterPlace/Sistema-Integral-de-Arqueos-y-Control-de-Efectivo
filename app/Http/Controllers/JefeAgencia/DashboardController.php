<?php

namespace App\Http\Controllers\JefeAgencia;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\JornadaReceptor;
use App\Models\VentanillaAgencia;
use App\Services\Agencias\AccesoAgenciaService;
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

        $jornadasHoy = JornadaReceptor::query()
            ->whereIn('agencia_id', $agenciaIds)
            ->whereDate('fecha_operativa', $fecha)
            ->get([
                'id',
                'agencia_id',
                'ventanilla_id',
                'receptor_usuario_id',
                'estado',
            ]);

        $totalJornadas = $jornadasHoy->count();

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

        $jornadasCumplidas = $jornadasHoy
            ->filter(function ($jornada) use (
                $arqueosOrdinariosValidos
            ) {
                return $arqueosOrdinariosValidos->contains(
                    fn ($arqueo) =>
                        (int) $arqueo->jornada_id ===
                            (int) $jornada->id
                        && (int) $arqueo->receptor_usuario_id ===
                            (int) $jornada->receptor_usuario_id
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
