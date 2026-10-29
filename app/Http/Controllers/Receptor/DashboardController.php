<?php

namespace App\Http\Controllers\Receptor;

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
        $hoy = now()->toDateString();

        // Se conserva el servicio institucional de acceso para consultar asignaciones vigentes.
        $asignaciones = $acceso->asignacionesVigentes($usuario);

        // Se limita todo al usuario autenticado, incluso si cubre varias agencias.
        $jornadasHoy = JornadaReceptor::query()
            ->with(['agencia', 'ventanilla', 'turno'])
            ->where('receptor_usuario_id', $usuario->getKey())
            ->whereDate('fecha_operativa', $hoy)
            ->orderBy('inicio_programado')
            ->get();

        $idsJornadas = $jornadasHoy->modelKeys();

        // No se presupone ningún valor textual de los campos tipo o estado.
        // Las fechas de firma/certificación/anulación provienen del modelo entregado.
        $arqueosHoy = ArqueoAgencia::query()
            ->with(['jornada', 'agencia', 'ventanilla'])
            ->where('receptor_usuario_id', $usuario->getKey())
            ->whereDate('fecha_operativa', $hoy)
            ->orderByDesc('id')
            ->get();

        $arqueosDeJornadasHoy = $arqueosHoy->filter(
            fn (ArqueoAgencia $arqueo) => $arqueo->jornada_id !== null
                && in_array((int) $arqueo->jornada_id, $idsJornadas, true)
        );

        $vigentes = $arqueosDeJornadasHoy->filter(
            fn (ArqueoAgencia $arqueo) => $arqueo->anulado_at === null
        );

        $pendientesCertificacion = $vigentes->filter(
            fn (ArqueoAgencia $arqueo) => $arqueo->firmado_receptor_at !== null
                && $arqueo->certificado_at === null
        )->count();

        $certificadosHoy = $vigentes->filter(
            fn (ArqueoAgencia $arqueo) => $arqueo->certificado_at !== null
        )->count();

        // El monto solo incluye arqueos certificados y no anulados de jornadas de hoy.
        // No se suma efectivo entre modalidades para evitar duplicar movimientos.
        $diferencias = $vigentes->filter(
            fn (ArqueoAgencia $arqueo) => $arqueo->certificado_at !== null
                && $arqueo->diferencia !== null
                && (float) $arqueo->diferencia !== 0.0
        );

        $indicadores = [
            'jornadas_hoy' => $jornadasHoy->count(),
            'arqueos_hoy' => $arqueosDeJornadasHoy->count(),
            'pendientes_certificacion' => $pendientesCertificacion,
            'certificados_hoy' => $certificadosHoy,
            'con_diferencia' => $diferencias->count(),
        ];

        return view('dashboards.receptor', [
            'usuario' => $usuario,
            'fecha' => now(),
            'asignaciones' => $asignaciones,
            'jornadasHoy' => $jornadasHoy,
            'arqueosRecientes' => $arqueosHoy->take(6),
            'indicadores' => $indicadores,
        ]);
    }
}
