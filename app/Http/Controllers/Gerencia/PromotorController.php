<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PromotorController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarGerencia($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $estado = trim((string) $request->string('estado'));

        $promotores = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('rol.nombre', 'Promotor')
            ->when(
                $estado !== '',
                fn ($query) => $query->where('u.estado', $estado)
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where('u.usuario', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.nombres', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.apellidos', 'like', '%' . $buscar . '%');
                    });
                }
            )
            ->select([
                'u.id',
                'u.usuario',
                'u.estado',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->selectSub(
                DB::table('asignaciones_promotor_ruta as apr')
                    ->selectRaw('COUNT(DISTINCT apr.ruta_id)')
                    ->whereColumn('apr.promotor_usuario_id', 'u.id')
                    ->where('apr.estado', true)
                    ->whereDate('apr.fecha_inicio', '<=', today())
                    ->where(function ($query): void {
                        $query
                            ->whereNull('apr.fecha_fin')
                            ->orWhereDate('apr.fecha_fin', '>=', today());
                    }),
                'rutas_asignadas'
            )
            ->selectSub(
                DB::table('agentes as a')
                    ->join(
                        'asignaciones_promotor_ruta as apr2',
                        'apr2.ruta_id',
                        '=',
                        'a.ruta_id'
                    )
                    ->selectRaw('COUNT(DISTINCT a.id)')
                    ->whereColumn('apr2.promotor_usuario_id', 'u.id')
                    ->where('apr2.estado', true)
                    ->whereDate('apr2.fecha_inicio', '<=', today())
                    ->where(function ($query): void {
                        $query
                            ->whereNull('apr2.fecha_fin')
                            ->orWhereDate('apr2.fecha_fin', '>=', today());
                    })
                    ->where('a.estado', 'ACTIVO'),
                'agentes_asignados'
            )
            ->selectSub(
                DB::table('arqueos')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('arqueos.creado_por', 'u.id')
                    ->where('arqueos.tipo', 'VISITA_PROMOTOR')
                    ->where('arqueos.estado', '!=', 'ANULADO'),
                'total_arqueos'
            )
            ->selectSub(
                DB::table('arqueos')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('arqueos.creado_por', 'u.id')
                    ->where('arqueos.tipo', 'VISITA_PROMOTOR')
                    ->whereDate('arqueos.fecha_arqueo', today())
                    ->where('arqueos.estado', '!=', 'ANULADO'),
                'arqueos_hoy'
            )
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->paginate(15)
            ->withQueryString();

        $totalPromotores = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->where('rol.nombre', 'Promotor')
            ->count();

        $totalActivos = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->where('rol.nombre', 'Promotor')
            ->where('u.estado', 'ACTIVO')
            ->count();

        $totalInactivos = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->where('rol.nombre', 'Promotor')
            ->where('u.estado', 'INACTIVO')
            ->count();

        $arqueosPromotorHoy = DB::table('arqueos')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->whereDate('fecha_arqueo', today())
            ->where('estado', '!=', 'ANULADO')
            ->count();

        return view('gerencia.promotores.index', compact(
            'promotores',
            'buscar',
            'estado',
            'totalPromotores',
            'totalActivos',
            'totalInactivos',
            'arqueosPromotorHoy'
        ));
    }

    public function show(
        Request $request,
        int $promotor
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarGerencia($usuario);

        $registro = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('u.id', $promotor)
            ->where('rol.nombre', 'Promotor')
            ->select([
                'u.id',
                'u.usuario',
                'u.estado',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();

        abort_if(
            ! $registro,
            404,
            'El Promotor solicitado no existe.'
        );

        $rutas = DB::table('asignaciones_promotor_ruta as apr')
            ->join('rutas as r', 'r.id', '=', 'apr.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('apr.promotor_usuario_id', $promotor)
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', today())
            ->where(function ($query): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', today());
            })
            ->select([
                'apr.id',
                'apr.fecha_inicio',
                'apr.fecha_fin',
                'r.id as ruta_id',
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
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get();

        $arqueos = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('arq.creado_por', $promotor)
            ->where('arq.tipo', 'VISITA_PROMOTOR')
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.estado',
                'arq.diferencia',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(12)
            ->withQueryString();

        return view('gerencia.promotores.show', [
            'promotor' => $registro,
            'rutas' => $rutas,
            'arqueos' => $arqueos,
        ]);
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
