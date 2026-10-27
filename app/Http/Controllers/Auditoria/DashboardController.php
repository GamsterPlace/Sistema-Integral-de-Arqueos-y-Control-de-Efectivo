<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAuditoria($usuario);

        $base = DB::table('arqueos')
            ->where('tipo','VISITA_AUDITORIA')
            ->where('creado_por',$usuario->id);

        $resumen = (object)[
            'total' => (clone $base)->count(),
            'hoy' => (clone $base)->whereDate('fecha_arqueo',today())->where('estado','!=','ANULADO')->count(),
            'certificados' => (clone $base)->where('estado','CERTIFICADO')->count(),
            'pendientes' => (clone $base)->where('estado','PENDIENTE_CERTIFICACION')->count(),
            'anulados' => (clone $base)->where('estado','ANULADO')->count(),
            'faltantes' => (clone $base)->where('estado','!=','ANULADO')->where('diferencia','<',0)->count(),
            'sobrantes' => (clone $base)->where('estado','!=','ANULADO')->where('diferencia','>',0)->count(),
            'exactos' => (clone $base)->where('estado','!=','ANULADO')->where('diferencia',0)->count(),
        ];

        $arqueosRecientes = DB::table('arqueos as arq')
            ->where('arq.tipo','VISITA_AUDITORIA')
            ->where('arq.creado_por',$usuario->id)
            ->select([
                'arq.id','arq.numero_arqueo','arq.fecha_arqueo','arq.estado',
                'arq.codigo_agente_historico','arq.nombre_negocio_historico',
                'arq.ruta_historica','arq.region_historica','arq.diferencia'
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->limit(8)
            ->get();

        $agentesAuditados = DB::table('arqueos')
            ->where('tipo','VISITA_AUDITORIA')
            ->where('creado_por',$usuario->id)
            ->where('estado','!=','ANULADO')
            ->distinct('agente_id')
            ->count('agente_id');

        return view('auditoria.dashboard',compact(
            'resumen','arqueosRecientes','agentesAuditados'
        ));
    }

    private function validarAuditoria(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            !$usuario->rol || $usuario->rol->nombre !== 'Auditoria',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
