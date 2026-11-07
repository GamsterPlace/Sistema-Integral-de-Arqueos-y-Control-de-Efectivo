<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAuditoria($usuario);

        $fecha = today()->toDateString();
        $base = DB::table('arqueos')->where('tipo', 'VISITA_AUDITORIA')->where('creado_por', $usuario->id);

        // Actividad personal de auditoría de agentes: se conserva íntegramente.
        $resumen = (object) [
            'total' => (clone $base)->count(),
            'hoy' => (clone $base)->whereDate('fecha_arqueo', $fecha)->where('estado', '!=', 'ANULADO')->count(),
            'certificados' => (clone $base)->where('estado', 'CERTIFICADO')->count(),
            'pendientes' => (clone $base)->where('estado', 'PENDIENTE_CERTIFICACION')->count(),
            'anulados' => (clone $base)->where('estado', 'ANULADO')->count(),
            'faltantes' => (clone $base)->where('estado', '!=', 'ANULADO')->where('diferencia', '<', 0)->count(),
            'sobrantes' => (clone $base)->where('estado', '!=', 'ANULADO')->where('diferencia', '>', 0)->count(),
            'exactos' => (clone $base)->where('estado', '!=', 'ANULADO')->where('diferencia', 0)->count(),
        ];

        $arqueosRecientes = DB::table('arqueos as arq')
            ->where('arq.tipo', 'VISITA_AUDITORIA')->where('arq.creado_por', $usuario->id)
            ->select('arq.id', 'arq.numero_arqueo', 'arq.fecha_arqueo', 'arq.estado',
                'arq.codigo_agente_historico', 'arq.nombre_negocio_historico',
                'arq.ruta_historica', 'arq.region_historica', 'arq.diferencia')
            ->orderByDesc('arq.fecha_arqueo')->orderByDesc('arq.id')->limit(8)->get();

        $agentesAuditados = (clone $base)->where('estado', '!=', 'ANULADO')->distinct()->count('agente_id');

        // Supervisión institucional: todos los arqueos, no solo los creados por el auditor.
        $agentesActivos = DB::table('agentes')->where('estado', 'ACTIVO')->count();
        $arqueosDiariosHoy = DB::table('arqueos')->where('tipo', 'DIARIO_AGENTE')
            ->whereDate('fecha_arqueo', $fecha)->where('estado', '!=', 'ANULADO');
        $agentesConArqueoHoy = (clone $arqueosDiariosHoy)->distinct()->count('agente_id');
        $indicadoresAgentes = [
            'activos' => $agentesActivos,
            'arqueos_hoy' => (clone $arqueosDiariosHoy)->count(),
            'con_arqueo_hoy' => $agentesConArqueoHoy,
            'sin_registro_hoy' => max(0, $agentesActivos - $agentesConArqueoHoy),
            'faltantes_hoy' => (clone $arqueosDiariosHoy)->where('diferencia', '<', 0)->count(),
            'sobrantes_hoy' => (clone $arqueosDiariosHoy)->where('diferencia', '>', 0)->count(),
        ];

        $arqueosAgenciaHoy = DB::table('arqueos_agencia')->whereDate('fecha_operativa', $fecha)
            ->where('estado', '!=', 'ANULADO');
        $indicadoresAgencias = [
            'agencias' => DB::table('agencias')->where('estado', true)->count(),
            'ventanillas' => DB::table('ventanillas_agencia')->where('estado', true)->count(),
            'arqueos_hoy' => (clone $arqueosAgenciaHoy)->count(),
            'certificados_hoy' => (clone $arqueosAgenciaHoy)->whereNotNull('certificado_at')->count(),
            'pendientes_hoy' => (clone $arqueosAgenciaHoy)->whereNull('certificado_at')->count(),
            'faltantes_hoy' => (clone $arqueosAgenciaHoy)->whereNotNull('certificado_at')->where('diferencia', '<', 0)->count(),
            'sobrantes_hoy' => (clone $arqueosAgenciaHoy)->whereNotNull('certificado_at')->where('diferencia', '>', 0)->count(),
            'anulados_hoy' => DB::table('arqueos_agencia')->whereDate('fecha_operativa', $fecha)->where('estado', 'ANULADO')->count(),
        ];

        $resumenAgencias = DB::table('agencias as a')
            ->leftJoin('arqueos_agencia as arq', function ($join) use ($fecha): void {
                $join->on('arq.agencia_id', '=', 'a.id')->whereDate('arq.fecha_operativa', $fecha);
            })
            ->select('a.id', 'a.codigo', 'a.nombre', 'a.estado')
            ->selectRaw('COUNT(arq.id) as total_hoy')
            ->selectRaw("SUM(CASE WHEN arq.estado != 'ANULADO' AND arq.certificado_at IS NOT NULL THEN 1 ELSE 0 END) as certificados_hoy")
            ->selectRaw("SUM(CASE WHEN arq.estado != 'ANULADO' AND arq.certificado_at IS NULL THEN 1 ELSE 0 END) as pendientes_hoy")
            ->selectRaw("SUM(CASE WHEN arq.estado != 'ANULADO' AND arq.certificado_at IS NOT NULL AND arq.diferencia < 0 THEN 1 ELSE 0 END) as faltantes_hoy")
            ->selectRaw("SUM(CASE WHEN arq.estado != 'ANULADO' AND arq.certificado_at IS NOT NULL AND arq.diferencia > 0 THEN 1 ELSE 0 END) as sobrantes_hoy")
            ->groupBy('a.id', 'a.codigo', 'a.nombre', 'a.estado')->orderBy('a.nombre')->get();

        // Turnos pendientes de finalizar no se cuentan como incumplidos.
        // Esta métrica se limita a turnos que no cruzan medianoche; los turnos nocturnos
        // se consultan en el módulo especializado de cumplimiento.
        foreach ($resumenAgencias as $agencia) {
            $exigibles = DB::table('asignaciones_operativas_receptor as ao')
                ->join('turnos_agencia as t', 't.id', '=', 'ao.turno_id')
                ->where('ao.agencia_id', $agencia->id)->where('ao.estado', true)
                ->where('t.estado', true)->where('t.cruza_medianoche', false)
                ->whereDate('ao.fecha_inicio', '<=', $fecha)
                ->where(function ($q) use ($fecha) {
                    $q->whereNull('ao.fecha_fin')->orWhereDate('ao.fecha_fin', '>=', $fecha);
                })->where('t.hora_fin', '<=', now()->format('H:i:s'));

            $agencia->turnos_exigibles = (clone $exigibles)->count();
            $agencia->turnos_cumplidos = (clone $exigibles)->whereExists(function ($q) use ($fecha) {
                $q->selectRaw('1')->from('jornadas_receptor as j')
                    ->join('arqueos_agencia as arq', 'arq.jornada_id', '=', 'j.id')
                    ->whereColumn('j.receptor_usuario_id', 'ao.receptor_usuario_id')
                    ->whereColumn('j.agencia_id', 'ao.agencia_id')
                    ->whereColumn('j.ventanilla_id', 'ao.ventanilla_id')
                    ->whereColumn('j.turno_id', 'ao.turno_id')
                    ->whereDate('j.fecha_operativa', $fecha)
                    ->where('arq.tipo', 'ORDINARIO')->where('arq.estado', '!=', 'ANULADO')
                    ->whereNotNull('arq.firmado_receptor_at');
            })->count();
        }

        $ultimosArqueosAgencias = DB::table('arqueos_agencia')
            ->select('id', 'numero_arqueo', 'fecha_operativa', 'agencia_nombre_historico',
                'receptor_nombre_historico', 'tipo', 'estado', 'diferencia', 'certificado_at')
            ->orderByDesc('fecha_operativa')->orderByDesc('id')->limit(8)->get();

        return view('auditoria.dashboard', compact('resumen', 'arqueosRecientes', 'agentesAuditados',
            'indicadoresAgentes', 'indicadoresAgencias', 'resumenAgencias', 'ultimosArqueosAgencias', 'fecha'));
    }

    private function validarAuditoria(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');
        abort_if(!$usuario->rol || $usuario->rol->nombre !== 'Auditoria', 403,
            'No tiene autorización para acceder a esta sección.');
    }
}
