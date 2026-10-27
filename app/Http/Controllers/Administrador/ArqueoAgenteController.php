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

class ArqueoAgenteController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

        $agenteId = $request->integer('agente_id');
        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');
        $estado = trim((string)$request->string('estado'));
        $buscar = trim((string)$request->string('buscar'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $agentes = DB::table('agentes')->orderBy('nombre_negocio')
            ->get(['id','codigo_agente','nombre_negocio']);

        $regiones = DB::table('regiones')->orderBy('nombre')->get(['id','nombre']);

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg','reg.id','=','r.region_id')
            ->when($regionId > 0, fn($q) => $q->where('r.region_id',$regionId))
            ->orderBy('reg.nombre')->orderBy('r.nombre')
            ->get(['r.id','r.codigo','r.nombre','r.region_id']);

        $arqueos = DB::table('arqueos as arq')
            ->join('agentes as a','a.id','=','arq.agente_id')
            ->leftJoin('rutas as r','r.id','=','a.ruta_id')
            ->leftJoin('regiones as reg','reg.id','=','r.region_id')
            ->where('arq.tipo','DIARIO_AGENTE')
            ->when($agenteId > 0, fn($q) => $q->where('arq.agente_id',$agenteId))
            ->when($regionId > 0, fn($q) => $q->where('reg.id',$regionId))
            ->when($rutaId > 0, fn($q) => $q->where('r.id',$rutaId))
            ->when($estado !== '', fn($q) => $q->where('arq.estado',$estado))
            ->when($desde, fn($q) => $q->whereDate('arq.fecha_arqueo','>=',$desde))
            ->when($hasta, fn($q) => $q->whereDate('arq.fecha_arqueo','<=',$hasta))
            ->when($buscar !== '', function($q) use ($buscar) {
                $q->where(function($s) use ($buscar) {
                    $s->where('arq.numero_arqueo','like','%'.$buscar.'%')
                      ->orWhere('a.codigo_agente','like','%'.$buscar.'%')
                      ->orWhere('a.nombre_negocio','like','%'.$buscar.'%');
                });
            })
            ->select([
                'arq.id','arq.numero_arqueo','arq.fecha_arqueo','arq.estado',
                'arq.fuera_fecha_ordinaria','arq.total_arqueado',
                'arq.saldo_sistema','arq.diferencia','a.codigo_agente',
                'a.nombre_negocio','r.nombre as ruta_nombre',
                'reg.nombre as region_nombre'
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(15)
            ->withQueryString();

        $resumen = (object)[
            'total' => DB::table('arqueos')->where('tipo','DIARIO_AGENTE')->count(),
            'certificados' => DB::table('arqueos')->where('tipo','DIARIO_AGENTE')->where('estado','CERTIFICADO')->count(),
            'pendientes' => DB::table('arqueos')->where('tipo','DIARIO_AGENTE')->where('estado','PENDIENTE_CERTIFICACION')->count(),
            'anulados' => DB::table('arqueos')->where('tipo','DIARIO_AGENTE')->where('estado','ANULADO')->count(),
        ];

        return view('administrador.arqueos-agentes.index', compact(
            'arqueos','agentes','regiones','rutas','agenteId','regionId',
            'rutaId','estado','buscar','desde','hasta','resumen'
        ));
    }

    public function show(Request $request, Arqueo $arqueo): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

        abort_if($arqueo->tipo !== 'DIARIO_AGENTE',404,'El arqueo solicitado no corresponde a un arqueo de Agente.');

        $arqueo->loadMissing(['detalles','firmas','agente.ruta.region']);

        $billetes = $arqueo->detalles->where('tipo','BILLETE')->sortByDesc('denominacion')->values();
        $monedas = $arqueo->detalles->where('tipo','MONEDA')->sortByDesc('denominacion')->values();

        return view('administrador.arqueos-agentes.show',compact('arqueo','billetes','monedas'));
    }

    public function imprimir(Request $request, Arqueo $arqueo): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

        abort_if($arqueo->tipo !== 'DIARIO_AGENTE',404,'El arqueo solicitado no corresponde a un arqueo de Agente.');

        $arqueo->loadMissing(['detalles','firmas','agente.ruta.region']);

        $billetes = $arqueo->detalles->where('tipo','BILLETE')->sortByDesc('denominacion')->values();
        $monedas = $arqueo->detalles->where('tipo','MONEDA')->sortByDesc('denominacion')->values();

        $pdf = Pdf::loadView('administrador.arqueos-agentes.pdf',compact('arqueo','billetes','monedas'))
            ->setPaper('letter','portrait');

        return $pdf->stream($arqueo->numero_arqueo.'.pdf');
    }

    private function validarAdministrador(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');
        abort_if(!$usuario->rol || $usuario->rol->nombre !== 'Administrador',403,'No tiene autorización para acceder a esta sección.');
    }
}
