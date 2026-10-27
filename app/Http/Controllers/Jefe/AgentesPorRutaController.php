<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AgentesPorRutaController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $rutaId = $request->integer('ruta_id');
        $regionId = $request->integer('region_id');
        $buscar = trim((string) $request->string('buscar'));
        $estado = trim((string) $request->string('estado'));

        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('r.region_id', $regionId)
            )
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.estado',
                'r.region_id',
                'reg.nombre as region_nombre',
            ]);

        $resumenRutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->leftJoin(
                'asignaciones_promotor_ruta as apr',
                function ($join): void {
                    $join
                        ->on('apr.ruta_id', '=', 'r.id')
                        ->where('apr.estado', true)
                        ->whereDate('apr.fecha_inicio', '<=', today())
                        ->where(function ($query): void {
                            $query
                                ->whereNull('apr.fecha_fin')
                                ->orWhereDate('apr.fecha_fin', '>=', today());
                        });
                }
            )
            ->leftJoin('usuarios as up', 'up.id', '=', 'apr.promotor_usuario_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'up.id')
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('r.region_id', $regionId)
            )
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where('r.id', $rutaId)
            )
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.estado',
                'reg.nombre as region_nombre',
                'up.usuario as promotor_usuario',
                'dp.nombres as promotor_nombres',
                'dp.apellidos as promotor_apellidos',
            ])
            ->selectSub(
                DB::table('agentes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('agentes.ruta_id', 'r.id'),
                'total_agentes'
            )
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
                'agentes_con_arqueo_hoy'
            )
            ->distinct()
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get();

        $agentes = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->leftJoin(
                'asignaciones_promotor_ruta as apr',
                function ($join): void {
                    $join
                        ->on('apr.ruta_id', '=', 'r.id')
                        ->where('apr.estado', true)
                        ->whereDate('apr.fecha_inicio', '<=', today())
                        ->where(function ($query): void {
                            $query
                                ->whereNull('apr.fecha_fin')
                                ->orWhereDate('apr.fecha_fin', '>=', today());
                        });
                }
            )
            ->leftJoin('usuarios as up', 'up.id', '=', 'apr.promotor_usuario_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'up.id')
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where('r.id', $rutaId)
            )
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('reg.id', $regionId)
            )
            ->when(
                $estado !== '',
                fn ($query) => $query->where('a.estado', $estado)
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where('a.codigo_agente', 'like', '%' . $buscar . '%')
                            ->orWhere('a.nombre_negocio', 'like', '%' . $buscar . '%')
                            ->orWhere('a.nombre_propietario', 'like', '%' . $buscar . '%');
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
                'up.usuario as promotor_usuario',
                'dp.nombres as promotor_nombres',
                'dp.apellidos as promotor_apellidos',
            ])
            ->selectSub(
                DB::table('arqueos')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->where('arqueos.tipo', 'DIARIO_AGENTE')
                    ->whereDate('arqueos.fecha_arqueo', today())
                    ->where('arqueos.estado', '!=', 'ANULADO'),
                'arqueo_hoy'
            )
            ->selectSub(
                DB::table('arqueos')
                    ->select('fecha_arqueo')
                    ->whereColumn('arqueos.agente_id', 'a.id')
                    ->where('arqueos.estado', '!=', 'ANULADO')
                    ->orderByDesc('arqueos.fecha_arqueo')
                    ->orderByDesc('arqueos.id')
                    ->limit(1),
                'ultimo_arqueo'
            )
            ->distinct()
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->orderBy('a.nombre_negocio')
            ->paginate(15)
            ->withQueryString();

        $rutaSeleccionada = null;

        if ($rutaId > 0) {
            $rutaSeleccionada = DB::table('rutas as r')
                ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
                ->where('r.id', $rutaId)
                ->select([
                    'r.id',
                    'r.codigo',
                    'r.nombre',
                    'r.estado',
                    'reg.nombre as region_nombre',
                ])
                ->first();
        }

        return view('jefe.agentes-ruta.index', [
            'agentes' => $agentes,
            'rutas' => $rutas,
            'regiones' => $regiones,
            'resumenRutas' => $resumenRutas,
            'rutaSeleccionada' => $rutaSeleccionada,
            'rutaId' => $rutaId,
            'regionId' => $regionId,
            'buscar' => $buscar,
            'estado' => $estado,
        ]);
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
