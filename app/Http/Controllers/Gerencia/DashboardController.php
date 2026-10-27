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
            'ultimosArqueos'
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
