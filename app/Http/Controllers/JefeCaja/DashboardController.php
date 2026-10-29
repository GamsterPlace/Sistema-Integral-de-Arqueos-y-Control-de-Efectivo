<?php

namespace App\Http\Controllers\JefeCaja;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\JornadaReceptor;
use App\Services\Agencias\AccesoAgenciaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, AccesoAgenciaService $acceso): View
    {
        $usuario = $request->user();
        $asignaciones = $acceso->asignacionesVigentes($usuario);
        $agenciaIds = $asignaciones->pluck('agencia_id')->unique()->values();
        $fecha = now()->toDateString();

        $arqueosHoy = ArqueoAgencia::query()
            ->whereIn('agencia_id', $agenciaIds)
            ->whereDate('fecha_operativa', $fecha)
            ->whereNull('anulado_at');

        $totalArqueos = (clone $arqueosHoy)->count();
        $pendientesCertificacion = (clone $arqueosHoy)
            ->whereNotNull('firmado_receptor_at')
            ->whereNull('certificado_at')
            ->count();
        $certificados = (clone $arqueosHoy)
            ->whereNotNull('certificado_at')
            ->count();
        $conDiferencia = (clone $arqueosHoy)
            ->whereNotNull('diferencia')
            ->where('diferencia', '<>', 0)
            ->count();

        $jornadasHoy = JornadaReceptor::query()
            ->whereIn('agencia_id', $agenciaIds)
            ->whereDate('fecha_operativa', $fecha);

        $totalJornadas = (clone $jornadasHoy)->count();
        $receptoresProgramados = (clone $jornadasHoy)
            ->distinct()
            ->count('receptor_usuario_id');

        $arqueosPendientes = (clone $arqueosHoy)
            ->whereNotNull('firmado_receptor_at')
            ->whereNull('certificado_at')
            ->with(['agencia', 'ventanilla', 'receptor'])
            ->orderByDesc('firmado_receptor_at')
            ->limit(8)
            ->get();

        $ultimosArqueos = (clone $arqueosHoy)
            ->with(['agencia', 'ventanilla', 'receptor'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('dashboards.jefecaja', compact(
            'asignaciones',
            'fecha',
            'totalArqueos',
            'pendientesCertificacion',
            'certificados',
            'conDiferencia',
            'totalJornadas',
            'receptoresProgramados',
            'arqueosPendientes',
            'ultimosArqueos'
        ));
    }
}
