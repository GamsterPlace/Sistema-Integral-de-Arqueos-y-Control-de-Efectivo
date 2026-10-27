<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RutasPromotoresController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarGerencia($usuario);

        $promotorId = $request->integer('promotor_id');
        $regionId = $request->integer('region_id');
        $estado = trim((string) $request->string('estado'));
        $buscar = trim((string) $request->string('buscar'));

        $promotores = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('rol.nombre', 'Promotor')
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->get([
                'u.id',
                'u.usuario',
                'u.estado',
                'dp.nombres',
                'dp.apellidos',
            ]);

        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
                'estado',
            ]);

        $asignaciones = DB::table('asignaciones_promotor_ruta as apr')
            ->join('usuarios as u', 'u.id', '=', 'apr.promotor_usuario_id')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->join('rutas as r', 'r.id', '=', 'apr.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('rol.nombre', 'Promotor')
            ->when(
                $promotorId > 0,
                fn ($query) => $query->where('apr.promotor_usuario_id', $promotorId)
            )
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('reg.id', $regionId)
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where('r.codigo', 'like', '%' . $buscar . '%')
                            ->orWhere('r.nombre', 'like', '%' . $buscar . '%')
                            ->orWhere('reg.nombre', 'like', '%' . $buscar . '%')
                            ->orWhere('u.usuario', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.nombres', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.apellidos', 'like', '%' . $buscar . '%');
                    });
                }
            )
            ->when(
                $estado !== '',
                function ($query) use ($estado): void {
                    if ($estado === 'ACTIVA') {
                        $query
                            ->where('apr.estado', true)
                            ->whereDate('apr.fecha_inicio', '<=', today())
                            ->where(function ($subquery): void {
                                $subquery
                                    ->whereNull('apr.fecha_fin')
                                    ->orWhereDate('apr.fecha_fin', '>=', today());
                            });
                    }

                    if ($estado === 'PROGRAMADA') {
                        $query
                            ->where('apr.estado', true)
                            ->whereDate('apr.fecha_inicio', '>', today());
                    }

                    if ($estado === 'FINALIZADA') {
                        $query->where(function ($subquery): void {
                            $subquery
                                ->where('apr.estado', false)
                                ->orWhereDate('apr.fecha_fin', '<', today());
                        });
                    }
                }
            )
            ->select([
                'apr.id',
                'apr.promotor_usuario_id',
                'apr.ruta_id',
                'apr.fecha_inicio',
                'apr.fecha_fin',
                'apr.estado as asignacion_estado',
                'u.usuario as promotor_usuario',
                'u.estado as promotor_estado',
                'dp.nombres as promotor_nombres',
                'dp.apellidos as promotor_apellidos',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'r.estado as ruta_estado',
                'reg.nombre as region_nombre',
            ])
            ->selectSub(
                DB::table('agentes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('agentes.ruta_id', 'r.id')
                    ->where('agentes.estado', 'ACTIVO'),
                'agentes_activos'
            )
            ->selectSub(
                DB::table('agentes as a2')
                    ->join('arqueos as arq2', 'arq2.agente_id', '=', 'a2.id')
                    ->selectRaw('COUNT(DISTINCT a2.id)')
                    ->whereColumn('a2.ruta_id', 'r.id')
                    ->where('a2.estado', 'ACTIVO')
                    ->where('arq2.tipo', 'DIARIO_AGENTE')
                    ->whereDate('arq2.fecha_arqueo', today())
                    ->where('arq2.estado', '!=', 'ANULADO'),
                'agentes_arqueados_hoy'
            )
            ->orderByDesc('apr.estado')
            ->orderBy('dp.nombres')
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->paginate(15)
            ->withQueryString();

        $totalAsignaciones = DB::table('asignaciones_promotor_ruta')->count();

        $totalAsignacionesActivas = DB::table('asignaciones_promotor_ruta as apr')
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', today())
            ->where(function ($query): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', today());
            })
            ->count();

        $rutasSinPromotor = DB::table('rutas as r')
            ->where('r.estado', true)
            ->whereNotExists(function ($query): void {
                $query
                    ->selectRaw('1')
                    ->from('asignaciones_promotor_ruta as apr')
                    ->whereColumn('apr.ruta_id', 'r.id')
                    ->where('apr.estado', true)
                    ->whereDate('apr.fecha_inicio', '<=', today())
                    ->where(function ($subquery): void {
                        $subquery
                            ->whereNull('apr.fecha_fin')
                            ->orWhereDate('apr.fecha_fin', '>=', today());
                    });
            })
            ->count();

        $promotoresConRuta = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->where('rol.nombre', 'Promotor')
            ->where('u.estado', 'ACTIVO')
            ->whereExists(function ($query): void {
                $query
                    ->selectRaw('1')
                    ->from('asignaciones_promotor_ruta as apr')
                    ->whereColumn('apr.promotor_usuario_id', 'u.id')
                    ->where('apr.estado', true)
                    ->whereDate('apr.fecha_inicio', '<=', today())
                    ->where(function ($subquery): void {
                        $subquery
                            ->whereNull('apr.fecha_fin')
                            ->orWhereDate('apr.fecha_fin', '>=', today());
                    });
            })
            ->count();

        $totalPromotoresActivos = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->where('rol.nombre', 'Promotor')
            ->where('u.estado', 'ACTIVO')
            ->count();

        $promotoresSinRuta = max(
            0,
            $totalPromotoresActivos - $promotoresConRuta
        );

        return view('gerencia.rutas-promotores.index', compact(
            'asignaciones',
            'promotores',
            'regiones',
            'promotorId',
            'regionId',
            'estado',
            'buscar',
            'totalAsignaciones',
            'totalAsignacionesActivas',
            'rutasSinPromotor',
            'promotoresConRuta',
            'promotoresSinRuta'
        ));
    }

    private function validarGerencia(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Gerencia',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
