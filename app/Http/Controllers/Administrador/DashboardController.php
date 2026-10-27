<?php

namespace App\Http\Controllers\Administrador;

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

        $this->validarAdministrador($usuario);

        $resumen = (object) [
            'total_usuarios' =>
                DB::table('usuarios')
                    ->count(),

            'usuarios_activos' =>
                DB::table('usuarios')
                    ->where('estado', 'ACTIVO')
                    ->count(),

            'usuarios_inactivos' =>
                DB::table('usuarios')
                    ->where('estado', 'INACTIVO')
                    ->count(),

            'total_agentes' =>
                DB::table('agentes')
                    ->count(),

            'agentes_activos' =>
                DB::table('agentes')
                    ->where('estado', 'ACTIVO')
                    ->count(),

            'total_promotores' =>
                DB::table('usuarios as u')
                    ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
                    ->where('rol.nombre', 'Promotor')
                    ->count(),

            'rutas_activas' =>
                DB::table('rutas')
                    ->where('estado', true)
                    ->count(),

            'regiones_activas' =>
                DB::table('regiones')
                    ->where('estado', true)
                    ->count(),

            'arqueos_hoy' =>
                DB::table('arqueos')
                    ->whereDate('fecha_arqueo', today())
                    ->where('estado', '!=', 'ANULADO')
                    ->count(),

            'pendientes_certificacion' =>
                DB::table('arqueos')
                    ->where('estado', 'PENDIENTE_CERTIFICACION')
                    ->count(),

            'arqueos_anulados' =>
                DB::table('arqueos')
                    ->where('estado', 'ANULADO')
                    ->count(),

            'extemporaneos' =>
                DB::table('arqueos')
                    ->where('fuera_fecha_ordinaria', true)
                    ->where('estado', '!=', 'ANULADO')
                    ->count(),
        ];

        $arqueosRecientes = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->leftJoin('usuarios as uc', 'uc.id', '=', 'arq.creado_por')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'uc.id')
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'arq.estado',
                'arq.diferencia',
                'arq.fuera_fecha_ordinaria',
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
            ->limit(10)
            ->get();

        $usuariosPorRol = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->select([
                'rol.nombre',
            ])
            ->selectRaw('COUNT(u.id) as total')
            ->groupBy('rol.id', 'rol.nombre')
            ->orderByDesc('total')
            ->get();

        return view(
            'administrador.dashboard',
            compact(
                'resumen',
                'arqueosRecientes',
                'usuariosPorRol'
            )
        );
    }

    private function validarAdministrador(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Administrador',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
