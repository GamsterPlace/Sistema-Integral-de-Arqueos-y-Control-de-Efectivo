<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ArqueoAgenteController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $agenteId = $request->integer('agente_id');
        $buscar = trim((string) $request->string('buscar'));
        $tipo = trim((string) $request->string('tipo'));
        $estado = trim((string) $request->string('estado'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $agentes = DB::table('agentes')
            ->orderBy('nombre_negocio')
            ->get([
                'id',
                'codigo_agente',
                'nombre_negocio',
            ]);

        $query = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->when(
                $agenteId > 0,
                fn ($query) => $query->where(
                    'arq.agente_id',
                    $agenteId
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
                            );
                    });
                }
            )
            ->when(
                $tipo !== '',
                fn ($query) => $query->where(
                    'arq.tipo',
                    $tipo
                )
            )
            ->when(
                $estado !== '',
                fn ($query) => $query->where(
                    'arq.estado',
                    $estado
                )
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
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id');

        $arqueos = $query
            ->paginate(15)
            ->withQueryString();

        return view('jefe.arqueos-agentes.index', [
            'arqueos' => $arqueos,
            'agentes' => $agentes,
            'agenteId' => $agenteId,
            'buscar' => $buscar,
            'tipo' => $tipo,
            'estado' => $estado,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    public function show(
        Request $request,
        Arqueo $arqueo
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $arqueo->loadMissing([
            'detalles',
            'firmas',
        ]);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        return view('jefe.arqueos-agentes.show', [
            'arqueo' => $arqueo,
            'billetes' => $billetes,
            'monedas' => $monedas,
        ]);
    }

    public function imprimir(
        Request $request,
        Arqueo $arqueo
    ): Response {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $arqueo->loadMissing([
            'detalles',
            'firmas',
        ]);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        $pdf = Pdf::loadView(
            'jefe.arqueos-agentes.pdf',
            [
                'arqueo' => $arqueo,
                'billetes' => $billetes,
                'monedas' => $monedas,
            ]
        )->setPaper('letter', 'portrait');

        return $pdf->stream(
            $arqueo->numero_arqueo . '.pdf'
        );
    }

    private function validarJefe(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'jefedeAgentes',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
