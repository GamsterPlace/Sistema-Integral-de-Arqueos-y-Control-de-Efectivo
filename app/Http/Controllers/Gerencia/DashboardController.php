<?php

namespace App\Http\Controllers\Gerencia;

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

        $this->validarGerencia($usuario);

        $hoy = today();

        $totalAgentes = DB::table('agentes')->count();

        $agentesActivos = DB::table('agentes')
            ->where('estado', 'ACTIVO')
            ->count();

        $arqueosHoy = DB::table('arqueos')
            ->whereDate('fecha_arqueo', $hoy)
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $arqueosAgenteHoy = DB::table('arqueos')
            ->where('tipo', 'DIARIO_AGENTE')
            ->whereDate('fecha_arqueo', $hoy)
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $arqueosPromotorHoy = DB::table('arqueos')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->whereDate('fecha_arqueo', $hoy)
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $agentesConArqueoHoy = DB::table('agentes as a')
            ->where('a.estado', 'ACTIVO')
            ->whereExists(function ($query) use ($hoy): void {
                $query
                    ->selectRaw('1')
                    ->from('arqueos as arq')
                    ->whereColumn('arq.agente_id', 'a.id')
                    ->where('arq.tipo', 'DIARIO_AGENTE')
                    ->whereDate('arq.fecha_arqueo', $hoy)
                    ->where('arq.estado', '!=', 'ANULADO');
            })
            ->count();

        $agentesPendientesHoy = max(
            0,
            $agentesActivos - $agentesConArqueoHoy
        );

        $faltantesHoy = DB::table('arqueos')
            ->whereDate('fecha_arqueo', $hoy)
            ->where('estado', '!=', 'ANULADO')
            ->where('diferencia', '<', 0)
            ->count();

        $sobrantesHoy = DB::table('arqueos')
            ->whereDate('fecha_arqueo', $hoy)
            ->where('estado', '!=', 'ANULADO')
            ->where('diferencia', '>', 0)
            ->count();

        $exactosHoy = DB::table('arqueos')
            ->whereDate('fecha_arqueo', $hoy)
            ->where('estado', '!=', 'ANULADO')
            ->where('diferencia', '=', 0)
            ->count();

        $anuladosHoy = DB::table('arqueos')
            ->whereDate('fecha_arqueo', $hoy)
            ->where('estado', 'ANULADO')
            ->count();

        $extemporaneosHoy = DB::table('arqueos')
            ->whereDate('fecha_arqueo', $hoy)
            ->where('estado', '!=', 'ANULADO')
            ->where('fuera_fecha_ordinaria', true)
            ->count();

        $cumplimiento = $agentesActivos > 0
            ? round(
                ($agentesConArqueoHoy / $agentesActivos) * 100,
                1
            )
            : 0;

        $ultimosArqueos = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->leftJoin('usuarios as uc', 'uc.id', '=', 'arq.creado_por')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'uc.id'
            )
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'arq.estado',
                'arq.diferencia',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'uc.usuario as responsable_usuario',
                'dp.nombres as responsable_nombres',
                'dp.apellidos as responsable_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->limit(8)
            ->get();

        $fechaAgencias = today()->toDateString();
        $arqueosVigentes = DB::table('arqueos_agencia')
            ->where('estado', '!=', 'ANULADO');
        $arqueosAgenciasHoy = (clone $arqueosVigentes)
            ->whereDate('fecha_operativa', $fechaAgencias);

        $indicadoresAgencias = [
            'agencias' => DB::table('agencias')->where('estado', true)->count(),
            'ventanillas' => DB::table('ventanillas_agencia')->where('estado', true)->count(),
            'arqueos_hoy' => (clone $arqueosAgenciasHoy)->count(),
            'certificados_hoy' => (clone $arqueosAgenciasHoy)->whereNotNull('certificado_at')->count(),
            'pendientes_hoy' => (clone $arqueosAgenciasHoy)->whereNull('certificado_at')->count(),
            'faltantes_hoy' => (clone $arqueosAgenciasHoy)->whereNotNull('certificado_at')->where('diferencia', '<', 0)->count(),
            'sobrantes_hoy' => (clone $arqueosAgenciasHoy)->whereNotNull('certificado_at')->where('diferencia', '>', 0)->count(),
            'anulados_hoy' => DB::table('arqueos_agencia')->whereDate('fecha_operativa', $fechaAgencias)->where('estado', 'ANULADO')->count(),
        ];

        $resumenAgencias = DB::table('agencias as a')
            ->leftJoin('arqueos_agencia as arq', function ($join) use ($fechaAgencias): void {
                $join->on('arq.agencia_id', '=', 'a.id')
                    ->whereDate('arq.fecha_operativa', '=', $fechaAgencias);
            })
            ->select('a.id', 'a.codigo', 'a.nombre', 'a.estado')
            ->selectRaw('COUNT(arq.id) as total_hoy')
            ->selectRaw("SUM(CASE WHEN arq.estado != 'ANULADO' AND arq.certificado_at IS NOT NULL THEN 1 ELSE 0 END) as certificados_hoy")
            ->selectRaw("SUM(CASE WHEN arq.estado != 'ANULADO' AND arq.certificado_at IS NULL THEN 1 ELSE 0 END) as pendientes_hoy")
            ->selectRaw("SUM(CASE WHEN arq.estado != 'ANULADO' AND arq.certificado_at IS NOT NULL AND arq.diferencia < 0 THEN 1 ELSE 0 END) as faltantes_hoy")
            ->selectRaw("SUM(CASE WHEN arq.estado != 'ANULADO' AND arq.certificado_at IS NOT NULL AND arq.diferencia > 0 THEN 1 ELSE 0 END) as sobrantes_hoy")
            ->groupBy('a.id', 'a.codigo', 'a.nombre', 'a.estado')
            ->orderBy('a.nombre')
            ->get();

        // Cumplimiento por turno exigible: asignaciones operativas vigentes cuyo turno
        // ya finalizó en la fecha consultada. No confundir arqueos con agencias.
        foreach ($resumenAgencias as $agencia) {
            $turnosExigibles = DB::table('asignaciones_operativas_receptor as ao')
                ->join('turnos_agencia as t', 't.id', '=', 'ao.turno_id')
                ->where('ao.agencia_id', $agencia->id)
                ->where('ao.estado', true)
                ->where('t.estado', true)
                ->whereDate('ao.fecha_inicio', '<=', $fechaAgencias)
                ->where(function ($query) use ($fechaAgencias) {
                    $query->whereNull('ao.fecha_fin')
                        ->orWhereDate('ao.fecha_fin', '>=', $fechaAgencias);
                })
                ->where('t.cruza_medianoche', false)
                ->where('t.hora_fin', '<=', now()->format('H:i:s'));

            $agencia->arqueos_esperados = (clone $turnosExigibles)->count();
            $agencia->arqueos_cumplidos = (clone $turnosExigibles)
                ->whereExists(function ($query) use ($fechaAgencias) {
                    $query->selectRaw('1')
                        ->from('jornadas_receptor as j')
                        ->join('arqueos_agencia as arq', 'arq.jornada_id', '=', 'j.id')
                        ->whereColumn('j.receptor_usuario_id', 'ao.receptor_usuario_id')
                        ->whereColumn('j.agencia_id', 'ao.agencia_id')
                        ->whereColumn('j.ventanilla_id', 'ao.ventanilla_id')
                        ->whereColumn('j.turno_id', 'ao.turno_id')
                        ->whereDate('j.fecha_operativa', $fechaAgencias)
                        ->whereDate('arq.fecha_operativa', $fechaAgencias)
                        ->where('arq.tipo', 'ORDINARIO')
                        ->where('arq.estado', '!=', 'ANULADO');
                })->count();
        }

        $ultimosArqueosAgencias = DB::table('arqueos_agencia')
            ->select('id', 'numero_arqueo', 'fecha_operativa', 'agencia_nombre_historico',
                'receptor_nombre_historico', 'tipo', 'estado', 'diferencia', 'certificado_at')
            ->orderByDesc('fecha_operativa')
            ->orderByDesc('id')
            ->limit(10)
            ->get();


        return view('gerencia.dashboard', compact(
            'totalAgentes',
            'agentesActivos',
            'arqueosHoy',
            'arqueosAgenteHoy',
            'arqueosPromotorHoy',
            'agentesConArqueoHoy',
            'agentesPendientesHoy',
            'faltantesHoy',
            'sobrantesHoy',
            'exactosHoy',
            'anuladosHoy',
            'extemporaneosHoy',
            'cumplimiento',
            'ultimosArqueos',
            'fechaAgencias',
            'indicadoresAgencias',
            'resumenAgencias',
            'ultimosArqueosAgencias'
        ));
    }

    private function validarGerencia(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Gerencia',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
