<?php
namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\JornadaReceptor;
use App\Services\Agencias\AccesoAgenciaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CumplimientoArqueosController extends Controller
{
    public function index(Request $request, AccesoAgenciaService $acceso): View
    {
        $usuario = $request->user();
        $usuario->loadMissing('rol');
        $rol = $usuario->rol?->nombre;
        abort_unless(in_array($rol, ['Receptor','JefeCaja','SubjefeCaja','JefeAgencia'], true), 403);

        $filtros = $request->validate([
            'desde' => ['nullable','date_format:Y-m-d'],
            'hasta' => ['nullable','date_format:Y-m-d','after_or_equal:desde'],
            'agencia_id' => ['nullable','integer'],
        ]);
        $desde = $filtros['desde'] ?? now()->startOfMonth()->toDateString();
        $hasta = $filtros['hasta'] ?? now()->toDateString();
        abort_if(\Carbon\Carbon::parse($desde)->diffInDays(\Carbon\Carbon::parse($hasta)) > 366, 422, 'El período máximo es de 366 días.');

        $ruta = match ($rol) {
            'Receptor' => 'receptor', 'JefeCaja' => 'jefecaja',
            'SubjefeCaja' => 'subjefecaja', 'JefeAgencia' => 'jefeagencia',
        };
        $agencias = collect();
        $query = JornadaReceptor::query()
            ->with(['agencia','ventanilla','turno','receptor',
                'arqueos' => fn($q) => $q->where('tipo','ORDINARIO')->orderByDesc('id')])
            ->whereBetween('fecha_operativa', [$desde,$hasta]);

        if ($rol === 'Receptor') {
            $query->where('receptor_usuario_id', $usuario->id)
                ->whereHas('asignacionPersonal', fn($q) => $q->where('usuario_id',$usuario->id)->where('cargo','RECEPTOR'));
        } else {
            $agencias = $acceso->asignacionesVigentes($usuario)
                ->pluck('agencia')->filter()->unique('id')->sortBy('nombre')->values();
            $ids = $agencias->pluck('id')->map(fn($id)=>(int)$id)->all();
            $query->whereIn('agencia_id',$ids);
            if (isset($filtros['agencia_id'])) {
                abort_unless(in_array((int)$filtros['agencia_id'],$ids,true),403,'Agencia no autorizada.');
                $query->where('agencia_id',(int)$filtros['agencia_id']);
            }
        }

        $jornadas = $query->orderByDesc('fecha_operativa')->orderByDesc('inicio_programado')
            ->orderByDesc('id')->paginate(25)->withQueryString();
        $hoy = now()->toDateString();
        $jornadas->getCollection()->transform(function($j) use ($hoy) {
            $vigente = $j->arqueos->first(fn($a) => $a->estado !== 'ANULADO' && $a->firmado_receptor_at !== null);
            $anulado = $j->arqueos->contains(fn($a) => $a->estado === 'ANULADO');
            $j->cumplimiento_arqueo = $vigente ? 'CON_ARQUEO' : (
                $j->fecha_operativa->toDateString() > $hoy ? 'PROGRAMADO' : (
                $j->fecha_operativa->toDateString() === $hoy ? 'PENDIENTE_HOY' : ($anulado ? 'ANULADO_SIN_REEMPLAZO' : 'SIN_ARQUEO')));
            $j->certificacion_arqueo = $vigente ? ($vigente->estado === 'CERTIFICADO' ? 'CERTIFICADO' : 'PENDIENTE_CERTIFICACION') : 'NO_APLICA';
            $j->arqueo_ordinario_vigente = $vigente;
            return $j;
        });
        return view('agencias.cumplimientos.index',compact('jornadas','agencias','ruta','desde','hasta','rol'));
    }
}
