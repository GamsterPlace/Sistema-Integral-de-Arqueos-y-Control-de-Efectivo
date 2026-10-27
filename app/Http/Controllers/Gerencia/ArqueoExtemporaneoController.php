<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArqueoExtemporaneoController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarGerencia($usuario);

        $agenteId = $request->integer('agente_id');
        $promotorId = $request->integer('promotor_id');
        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');

        $tipo = trim((string) $request->string('tipo'));
        $estado = trim((string) $request->string('estado'));
        $buscar = trim((string) $request->string('buscar'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $agentes = DB::table('agentes')
            ->orderBy('nombre_negocio')
            ->get([
                'id',
                'codigo_agente',
                'nombre_negocio',
            ]);

        $promotores = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('rol.nombre', 'Promotor')
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->get([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ]);

        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
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
                'reg.nombre as region_nombre',
            ]);

        $arqueos = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->leftJoin('usuarios as uc', 'uc.id', '=', 'arq.creado_por')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'uc.id')
            ->where('arq.fuera_fecha_ordinaria', true)
            ->when(
                $agenteId > 0,
                fn ($query) => $query->where('arq.agente_id', $agenteId)
            )
            ->when(
                $promotorId > 0,
                fn ($query) => $query->where('arq.creado_por', $promotorId)
            )
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('reg.id', $regionId)
            )
            ->when(
                $rutaId > 0,
                fn ($query) => $query->where('r.id', $rutaId)
            )
            ->when(
                $tipo !== '',
                fn ($query) => $query->where('arq.tipo', $tipo)
            )
            ->when(
                $estado !== '',
                fn ($query) => $query->where('arq.estado', $estado)
            )
            ->when(
                $desde,
                fn ($query) => $query->whereDate(
                    'arq.fecha_arqueo',
                    '>=',
                    $desde
                )
            )
            ->when(
                $hasta,
                fn ($query) => $query->whereDate(
                    'arq.fecha_arqueo',
                    '<=',
                    $hasta
                )
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where(
                                'arq.numero_arqueo',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'a.codigo_agente',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'a.nombre_negocio',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'a.nombre_propietario',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'r.codigo',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'r.nombre',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'reg.nombre',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'uc.usuario',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'dp.nombres',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'dp.apellidos',
                                'like',
                                '%' . $buscar . '%'
                            );
                    });
                }
            )
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'arq.estado',
                'arq.fuera_fecha_ordinaria',
                'arq.total_arqueado',
                'arq.saldo_sistema',
                'arq.diferencia',
                'a.id as agente_id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'uc.usuario as responsable_usuario',
                'dp.nombres as responsable_nombres',
                'dp.apellidos as responsable_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(20)
            ->withQueryString();

        $total = DB::table('arqueos')
            ->where('fuera_fecha_ordinaria', true)
            ->count();

        $deAgentes = DB::table('arqueos')
            ->where('fuera_fecha_ordinaria', true)
            ->where('tipo', 'DIARIO_AGENTE')
            ->count();

        $dePromotores = DB::table('arqueos')
            ->where('fuera_fecha_ordinaria', true)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->count();

        $certificados = DB::table('arqueos')
            ->where('fuera_fecha_ordinaria', true)
            ->where('estado', 'CERTIFICADO')
            ->count();

        $pendientes = DB::table('arqueos')
            ->where('fuera_fecha_ordinaria', true)
            ->where('estado', 'PENDIENTE_CERTIFICACION')
            ->count();

        $anulados = DB::table('arqueos')
            ->where('fuera_fecha_ordinaria', true)
            ->where('estado', 'ANULADO')
            ->count();

        return view(
            'gerencia.arqueos-extemporaneos.index',
            compact(
                'arqueos',
                'agentes',
                'promotores',
                'regiones',
                'rutas',
                'agenteId',
                'promotorId',
                'regionId',
                'rutaId',
                'tipo',
                'estado',
                'buscar',
                'desde',
                'hasta',
                'total',
                'deAgentes',
                'dePromotores',
                'certificados',
                'pendientes',
                'anulados'
            )
        );
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
