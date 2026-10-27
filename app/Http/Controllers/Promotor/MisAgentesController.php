<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MisAgentesController extends Controller
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
        $rutaId = $request->integer('ruta_id');

        $rutasAsignadas = DB::table('rutas as r')
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
            ->where('r.estado', true)
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
            ])
            ->distinct()
            ->orderBy('r.nombre')
            ->get();

        $agentes = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
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
            ->where('r.estado', true)
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where('r.id', $rutaId)
            )
            ->when(
                $busqueda !== '',
                function ($query) use ($busqueda): void {
                    $query->where(function ($subquery) use ($busqueda): void {
                        $subquery
                            ->where(
                                'a.codigo_agente',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'a.nombre_negocio',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'a.nombre_propietario',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'a.direccion',
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
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'a.estado',
                'r.id as ruta_id',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->selectSub(
                DB::table('arqueos')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->where('arqueos.estado', '!=', 'ANULADO'),
                'total_arqueos'
            )
            ->selectSub(
                DB::table('arqueos')
                    ->selectRaw('MAX(fecha_arqueo)')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->where('arqueos.estado', '!=', 'ANULADO'),
                'ultimo_arqueo'
            )
            ->selectSub(
                DB::table('arqueos')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->whereDate('arqueos.fecha_arqueo', today())
                    ->where('arqueos.tipo', 'DIARIO_AGENTE')
                    ->where('arqueos.estado', '!=', 'ANULADO'),
                'arqueo_hoy'
            )
            ->distinct()
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->orderBy('a.nombre_negocio')
            ->paginate(12)
            ->withQueryString();

        return view('promotor.mis-agentes.index', [
            'agentes' => $agentes,
            'rutasAsignadas' => $rutasAsignadas,
            'busqueda' => $busqueda,
            'rutaId' => $rutaId,
        ]);
    }
}
