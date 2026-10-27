<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RutasAsignadasController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol || $usuario->rol->nombre !== 'Promotor',
            403,
            'No tiene autorización para acceder a esta sección.'
        );

        $busqueda = trim((string) $request->string('buscar'));

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->join(
                'asignaciones_promotor_ruta as apr',
                'apr.ruta_id',
                '=',
                'r.id'
            )
            ->where('apr.promotor_usuario_id', $usuario->id)
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', today())
            ->where(function ($query): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', today());
            })
            ->when(
                $busqueda !== '',
                function ($query) use ($busqueda): void {
                    $query->where(function ($subquery) use ($busqueda): void {
                        $subquery
                            ->where(
                                'r.codigo',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'r.nombre',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'reg.nombre',
                                'like',
                                '%' . $busqueda . '%'
                            );
                    });
                }
            )
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.estado',
                'reg.nombre as region_nombre',
                'apr.fecha_inicio',
                'apr.fecha_fin',
            ])
            ->selectSub(
                DB::table('agentes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('agentes.ruta_id', 'r.id')
                    ->where('agentes.estado', 'ACTIVO'),
                'agentes_activos'
            )
            ->selectSub(
                DB::table('agentes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('agentes.ruta_id', 'r.id'),
                'total_agentes'
            )
            ->selectSub(
                DB::table('agentes as a')
                    ->join(
                        'arqueos as arq',
                        'arq.agente_id',
                        '=',
                        'a.id'
                    )
                    ->selectRaw('COUNT(DISTINCT a.id)')
                    ->whereColumn('a.ruta_id', 'r.id')
                    ->where('a.estado', 'ACTIVO')
                    ->where('arq.tipo', 'DIARIO_AGENTE')
                    ->whereDate('arq.fecha_arqueo', today())
                    ->where('arq.estado', '!=', 'ANULADO'),
                'agentes_con_arqueo_hoy'
            )
            ->distinct()
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->paginate(10)
            ->withQueryString();

        return view('promotor.rutas-asignadas.index', [
            'rutas' => $rutas,
            'busqueda' => $busqueda,
        ]);
    }
}
