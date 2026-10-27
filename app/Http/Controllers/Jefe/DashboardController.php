<?php

namespace App\Http\Controllers\Jefe;

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

        $usuario->loadMissing(['rol', 'datosPersonales']);

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'jefedeAgentes',
            403,
            'No tiene autorización para acceder a esta sección.'
        );

        $totalAgentes = DB::table('agentes')
            ->where('estado', 'ACTIVO')
            ->count();

        $totalRutas = DB::table('rutas')
            ->where('estado', true)
            ->count();

        $totalRegiones = DB::table('regiones')
            ->where('estado', true)
            ->count();

        $totalPromotores = DB::table('usuarios as u')
            ->join('roles as r', 'r.id', '=', 'u.rol_id')
            ->where('r.nombre', 'Promotor')
            ->where('u.estado', true)
            ->count();

        $arqueosHoy = DB::table('arqueos')
            ->whereDate('fecha_arqueo', today())
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $arqueosAgentesHoy = DB::table('arqueos')
            ->whereDate('fecha_arqueo', today())
            ->where('tipo', 'DIARIO_AGENTE')
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $arqueosPromotorHoy = DB::table('arqueos')
            ->whereDate('fecha_arqueo', today())
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $pendientesCertificacion = DB::table('arqueos')
            ->where('estado', 'PENDIENTE_CERTIFICACION')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->count();

        $agentesSinArqueoHoy = DB::table('agentes as a')
            ->where('a.estado', 'ACTIVO')
            ->whereNotExists(function ($query): void {
                $query
                    ->selectRaw('1')
                    ->from('arqueos as arq')
                    ->whereColumn('arq.agente_id', 'a.id')
                    ->where('arq.tipo', 'DIARIO_AGENTE')
                    ->whereDate('arq.fecha_arqueo', today())
                    ->where('arq.estado', '!=', 'ANULADO');
            })
            ->count();

        $ultimosArqueos = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'arq.estado',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
            ])
            ->orderByDesc('arq.created_at')
            ->limit(8)
            ->get();

        return view('jefe.dashboard', [
            'usuario' => $usuario,
            'totalAgentes' => $totalAgentes,
            'totalRutas' => $totalRutas,
            'totalRegiones' => $totalRegiones,
            'totalPromotores' => $totalPromotores,
            'arqueosHoy' => $arqueosHoy,
            'arqueosAgentesHoy' => $arqueosAgentesHoy,
            'arqueosPromotorHoy' => $arqueosPromotorHoy,
            'pendientesCertificacion' => $pendientesCertificacion,
            'agentesSinArqueoHoy' => $agentesSinArqueoHoy,
            'ultimosArqueos' => $ultimosArqueos,
        ]);
    }
}
