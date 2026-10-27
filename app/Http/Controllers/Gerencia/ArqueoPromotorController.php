<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ArqueoPromotorController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarGerencia($usuario);

        $promotorId = $request->integer('promotor_id');
        $agenteId = $request->integer('agente_id');
        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');
        $estado = trim((string) $request->string('estado'));
        $buscar = trim((string) $request->string('buscar'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

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

        $agentes = DB::table('agentes')
            ->orderBy('nombre_negocio')
            ->get([
                'id',
                'codigo_agente',
                'nombre_negocio',
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
            ->join('usuarios as up', 'up.id', '=', 'arq.creado_por')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'up.id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('arq.tipo', 'VISITA_PROMOTOR')
            ->when(
                $promotorId > 0,
                fn ($query) => $query->where('arq.creado_por', $promotorId)
            )
            ->when(
                $agenteId > 0,
                fn ($query) => $query->where('arq.agente_id', $agenteId)
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
                                'up.usuario',
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
                            );
                    });
                }
            )
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.estado',
                'arq.fuera_fecha_ordinaria',
                'arq.total_arqueado',
                'arq.saldo_sistema',
                'arq.diferencia',
                'arq.creado_por as promotor_id',
                'a.id as agente_id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'up.usuario as promotor_usuario',
                'dp.nombres as promotor_nombres',
                'dp.apellidos as promotor_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(15)
            ->withQueryString();

        $total = DB::table('arqueos')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->count();

        $certificados = DB::table('arqueos')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', 'CERTIFICADO')
            ->count();

        $pendientes = DB::table('arqueos')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', 'PENDIENTE_CERTIFICACION')
            ->count();

        $anulados = DB::table('arqueos')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', 'ANULADO')
            ->count();

        return view('gerencia.arqueos-promotores.index', compact(
            'promotores',
            'agentes',
            'regiones',
            'rutas',
            'arqueos',
            'promotorId',
            'agenteId',
            'regionId',
            'rutaId',
            'estado',
            'buscar',
            'desde',
            'hasta',
            'total',
            'certificados',
            'pendientes',
            'anulados'
        ));
    }

    public function show(
        Request $request,
        Arqueo $arqueo
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarGerencia($usuario);

        abort_if(
            $arqueo->tipo !== 'VISITA_PROMOTOR',
            404,
            'El arqueo solicitado no corresponde a un arqueo de Promotor.'
        );

        $arqueo->loadMissing([
            'detalles',
            'firmas',
            'agente.ruta.region',
        ]);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        $promotor = DB::table('usuarios as u')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->where('u.id', $arqueo->creado_por)
            ->select([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();

        return view(
            'gerencia.arqueos-promotores.show',
            compact(
                'arqueo',
                'billetes',
                'monedas',
                'promotor'
            )
        );
    }

    public function imprimir(
        Request $request,
        Arqueo $arqueo
    ): Response {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarGerencia($usuario);

        abort_if(
            $arqueo->tipo !== 'VISITA_PROMOTOR',
            404,
            'El arqueo solicitado no corresponde a un arqueo de Promotor.'
        );

        $arqueo->loadMissing([
            'detalles',
            'firmas',
            'agente.ruta.region',
        ]);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        $promotor = DB::table('usuarios as u')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->where('u.id', $arqueo->creado_por)
            ->select([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();

        $pdf = Pdf::loadView(
            'gerencia.arqueos-promotores.pdf',
            compact(
                'arqueo',
                'billetes',
                'monedas',
                'promotor'
            )
        )->setPaper('letter', 'portrait');

        return $pdf->stream(
            $arqueo->numero_arqueo . '.pdf'
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
