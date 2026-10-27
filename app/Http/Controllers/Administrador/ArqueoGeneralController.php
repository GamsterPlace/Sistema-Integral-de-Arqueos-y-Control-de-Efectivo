<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ArqueoGeneralController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

        $agenteId = $request->integer('agente_id');
        $promotorId = $request->integer('promotor_id');
        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');
        $tipo = trim((string) $request->string('tipo'));
        $estado = trim((string) $request->string('estado'));
        $buscar = trim((string) $request->string('buscar'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $extemporaneo = trim((string) $request->string('extemporaneo'));

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
                fn ($q) => $q->where('r.region_id', $regionId)
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
            ->when(
                $agenteId > 0,
                fn ($q) => $q->where('arq.agente_id', $agenteId)
            )
            ->when(
                $promotorId > 0,
                fn ($q) => $q->where('arq.creado_por', $promotorId)
            )
            ->when(
                $regionId > 0,
                fn ($q) => $q->where('reg.id', $regionId)
            )
            ->when(
                $rutaId > 0,
                fn ($q) => $q->where('r.id', $rutaId)
            )
            ->when(
                $tipo !== '',
                fn ($q) => $q->where('arq.tipo', $tipo)
            )
            ->when(
                $estado !== '',
                fn ($q) => $q->where('arq.estado', $estado)
            )
            ->when(
                $desde,
                fn ($q) => $q->whereDate('arq.fecha_arqueo', '>=', $desde)
            )
            ->when(
                $hasta,
                fn ($q) => $q->whereDate('arq.fecha_arqueo', '<=', $hasta)
            )
            ->when(
                $extemporaneo !== '',
                function ($q) use ($extemporaneo): void {
                    if ($extemporaneo === 'SI') {
                        $q->where('arq.fuera_fecha_ordinaria', true);
                    }

                    if ($extemporaneo === 'NO') {
                        $q->where('arq.fuera_fecha_ordinaria', false);
                    }
                }
            )
            ->when(
                $buscar !== '',
                function ($q) use ($buscar): void {
                    $q->where(function ($s) use ($buscar): void {
                        $s->where(
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

        $resumen = (object) [
            'total' => DB::table('arqueos')->count(),
            'agente' => DB::table('arqueos')
                ->where('tipo', 'DIARIO_AGENTE')
                ->count(),
            'promotor' => DB::table('arqueos')
                ->where('tipo', 'VISITA_PROMOTOR')
                ->count(),
            'certificados' => DB::table('arqueos')
                ->where('estado', 'CERTIFICADO')
                ->count(),
            'pendientes' => DB::table('arqueos')
                ->where('estado', 'PENDIENTE_CERTIFICACION')
                ->count(),
            'anulados' => DB::table('arqueos')
                ->where('estado', 'ANULADO')
                ->count(),
            'extemporaneos' => DB::table('arqueos')
                ->where('fuera_fecha_ordinaria', true)
                ->count(),
        ];

        return view(
            'administrador.arqueos.index',
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
                'extemporaneo',
                'resumen'
            )
        );
    }

    public function show(Request $request, Arqueo $arqueo): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

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

        $responsable = DB::table('usuarios as u')
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
            'administrador.arqueos.show',
            compact(
                'arqueo',
                'billetes',
                'monedas',
                'responsable'
            )
        );
    }

    public function imprimir(Request $request, Arqueo $arqueo): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

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

        $responsable = DB::table('usuarios as u')
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

        /*
        |--------------------------------------------------------------------------
        | PDF SEGÚN EL TIPO DE ARQUEO
        |--------------------------------------------------------------------------
        |
        | DIARIO_AGENTE:
        | resources/views/administrador/arqueos-agentes/pdf.blade.php
        |
        | VISITA_PROMOTOR:
        | resources/views/administrador/arqueos-promotores/pdf.blade.php
        |
        */

        $vistaPdf = match ($arqueo->tipo) {
            'DIARIO_AGENTE' => 'administrador.arqueos-agentes.pdf',
            'VISITA_PROMOTOR' => 'administrador.arqueos-promotores.pdf',
            default => abort(
                422,
                'El tipo de arqueo no tiene un formato PDF configurado.'
            ),
        };

        $pdf = Pdf::loadView(
            $vistaPdf,
            compact(
                'arqueo',
                'billetes',
                'monedas',
                'responsable'
            )
        )->setPaper('letter', 'portrait');

        return $pdf->stream(
            $arqueo->numero_arqueo . '.pdf'
        );
    }

    private function validarAdministrador(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Administrador',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
