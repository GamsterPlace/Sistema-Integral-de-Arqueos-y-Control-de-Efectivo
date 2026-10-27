<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AgentesPorRegionController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');
        $buscar = trim((string) $request->string('buscar'));
        $estado = trim((string) $request->string('estado'));

        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
                'estado',
            ]);

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
                'r.region_id',
                'r.estado',
                'reg.nombre as region_nombre',
            ]);

        $resumenRegiones = DB::table('regiones as reg')
            ->select([
                'reg.id',
                'reg.nombre',
                'reg.estado',
            ])
            ->selectSub(
                DB::table('rutas')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('rutas.region_id', 'reg.id')
                    ->where('rutas.estado', true),
                'rutas_activas'
            )
            ->selectSub(
                DB::table('agentes as a')
                    ->join('rutas as r2', 'r2.id', '=', 'a.ruta_id')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('r2.region_id', 'reg.id')
                    ->where('a.estado', 'ACTIVO'),
                'agentes_activos'
            )
            ->selectSub(
                DB::table('agentes as a2')
                    ->join('rutas as r3', 'r3.id', '=', 'a2.ruta_id')
                    ->join('arqueos as arq', 'arq.agente_id', '=', 'a2.id')
                    ->selectRaw('COUNT(DISTINCT a2.id)')
                    ->whereColumn('r3.region_id', 'reg.id')
                    ->where('a2.estado', 'ACTIVO')
                    ->where('arq.tipo', 'DIARIO_AGENTE')
                    ->whereDate('arq.fecha_arqueo', today())
                    ->where('arq.estado', '!=', 'ANULADO'),
                'agentes_con_arqueo_hoy'
            )
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('reg.id', $regionId)
            )
            ->orderBy('reg.nombre')
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
                $regionId > 0,
                fn ($query) => $query->where('reg.id', $regionId)
            )
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where('r.id', $rutaId)
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
                'reg.id as region_id',
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

        $regionSeleccionada = null;

        if ($regionId > 0) {
            $regionSeleccionada = DB::table('regiones')
                ->where('id', $regionId)
                ->first([
                    'id',
                    'nombre',
                    'estado',
                ]);
        }

        return view('jefe.agentes-region.index', [
            'agentes' => $agentes,
            'regiones' => $regiones,
            'rutas' => $rutas,
            'resumenRegiones' => $resumenRegiones,
            'regionSeleccionada' => $regionSeleccionada,
            'regionId' => $regionId,
            'rutaId' => $rutaId,
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
