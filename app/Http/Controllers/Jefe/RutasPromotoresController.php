<?php

namespace App\Http\Controllers\Jefe;

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
        $this->validarJefe($usuario);

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
            ->when($promotorId > 0, fn ($q) => $q->where('apr.promotor_usuario_id', $promotorId))
            ->when($regionId > 0, fn ($q) => $q->where('reg.id', $regionId))
            ->when($buscar !== '', function ($q) use ($buscar): void {
                $q->where(function ($s) use ($buscar): void {
                    $s->where('r.codigo', 'like', '%' . $buscar . '%')
                      ->orWhere('r.nombre', 'like', '%' . $buscar . '%')
                      ->orWhere('reg.nombre', 'like', '%' . $buscar . '%')
                      ->orWhere('u.usuario', 'like', '%' . $buscar . '%')
                      ->orWhere('dp.nombres', 'like', '%' . $buscar . '%')
                      ->orWhere('dp.apellidos', 'like', '%' . $buscar . '%');
                });
            })
            ->when($estado !== '', function ($q) use ($estado): void {
                if ($estado === 'ACTIVA') {
                    $q->where('apr.estado', true)
                      ->whereDate('apr.fecha_inicio', '<=', today())
                      ->where(function ($s): void {
                          $s->whereNull('apr.fecha_fin')
                            ->orWhereDate('apr.fecha_fin', '>=', today());
                      });
                }

                if ($estado === 'PROGRAMADA') {
                    $q->where('apr.estado', true)
                      ->whereDate('apr.fecha_inicio', '>', today());
                }

                if ($estado === 'FINALIZADA') {
                    $q->where(function ($s): void {
                        $s->where('apr.estado', false)
                          ->orWhereDate('apr.fecha_fin', '<', today());
                    });
                }
            })
            ->select([
                'apr.id',
                'apr.promotor_usuario_id',
                'apr.ruta_id',
                'apr.fecha_inicio',
                'apr.fecha_fin',
                'apr.estado as asignacion_estado',
                'u.usuario as promotor_usuario',
                'dp.nombres as promotor_nombres',
                'dp.apellidos as promotor_apellidos',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
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

        $totalAsignacionesActivas = DB::table('asignaciones_promotor_ruta as apr')
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', today())
            ->where(function ($q): void {
                $q->whereNull('apr.fecha_fin')
                  ->orWhereDate('apr.fecha_fin', '>=', today());
            })
            ->count();

        $rutasSinPromotor = DB::table('rutas as r')
            ->where('r.estado', true)
            ->whereNotExists(function ($q): void {
                $q->selectRaw('1')
                  ->from('asignaciones_promotor_ruta as apr')
                  ->whereColumn('apr.ruta_id', 'r.id')
                  ->where('apr.estado', true)
                  ->whereDate('apr.fecha_inicio', '<=', today())
                  ->where(function ($s): void {
                      $s->whereNull('apr.fecha_fin')
                        ->orWhereDate('apr.fecha_fin', '>=', today());
                  });
            })
            ->count();

        $promotoresConRuta = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->where('rol.nombre', 'Promotor')
            ->whereExists(function ($q): void {
                $q->selectRaw('1')
                  ->from('asignaciones_promotor_ruta as apr')
                  ->whereColumn('apr.promotor_usuario_id', 'u.id')
                  ->where('apr.estado', true)
                  ->whereDate('apr.fecha_inicio', '<=', today())
                  ->where(function ($s): void {
                      $s->whereNull('apr.fecha_fin')
                        ->orWhereDate('apr.fecha_fin', '>=', today());
                  });
            })
            ->count();

        $totalPromotoresActivos = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->where('rol.nombre', 'Promotor')
            ->where('u.estado', 'ACTIVO')
            ->count();

        $promotoresSinRuta = max(0, $totalPromotoresActivos - $promotoresConRuta);

        return view('jefe.rutas-promotores.index', compact(
            'asignaciones',
            'promotores',
            'regiones',
            'promotorId',
            'regionId',
            'estado',
            'buscar',
            'totalAsignacionesActivas',
            'rutasSinPromotor',
            'promotoresConRuta',
            'promotoresSinRuta'
        ));
    }

    private function validarJefe(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'jefedeAgentes',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
