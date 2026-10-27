<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'rol',
            'datosPersonales',
        ]);

        abort_if(
            ! $usuario->rol || $usuario->rol->nombre !== 'Promotor',
            403,
            'No tiene autorización para acceder al panel del promotor.'
        );

        $hoy = today();

        $rutasAsignadas = DB::table('asignaciones_promotor_ruta as apr')
            ->join('rutas as r', 'r.id', '=', 'apr.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('apr.promotor_usuario_id', $usuario->id)
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', $hoy)
            ->where(function ($query) use ($hoy): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', $hoy);
            })
            ->where('r.estado', true)
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'reg.nombre as region_nombre',
            ])
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get();

        $rutaIds = $rutasAsignadas
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $agentesAsignados = $this->obtenerAgentesAsignados(
            $rutaIds
        );

        $agenteIds = $agentesAsignados
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $totalAgentes = $agentesAsignados->count();

        $agentesConArqueoHoy = $agenteIds->isEmpty()
            ? 0
            : Arqueo::query()
                ->whereIn('agente_id', $agenteIds)
                ->where('tipo', 'DIARIO_AGENTE')
                ->whereDate('fecha_arqueo', $hoy)
                ->where('estado', '!=', 'ANULADO')
                ->distinct('agente_id')
                ->count('agente_id');

        $agentesPendientesHoy = max(
            $totalAgentes - $agentesConArqueoHoy,
            0
        );

        $pendientesCertificacion = $agenteIds->isEmpty()
            ? 0
            : Arqueo::query()
                ->whereIn('agente_id', $agenteIds)
                ->where('tipo', 'DIARIO_AGENTE')
                ->where('estado', 'PENDIENTE_CERTIFICACION')
                ->count();

        $arqueosPromotorHoy = Arqueo::query()
            ->where('creado_por', $usuario->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->whereDate('fecha_arqueo', $hoy)
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $pendientesFirmaAgente = Arqueo::query()
            ->where('creado_por', $usuario->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', 'PENDIENTE_CERTIFICACION')
            ->whereNotExists(function ($query): void {
                $query
                    ->selectRaw('1')
                    ->from('firmas_arqueos')
                    ->whereColumn(
                        'firmas_arqueos.arqueo_id',
                        'arqueos.id'
                    )
                    ->where(
                        'firmas_arqueos.tipo_firma',
                        'VALIDADOR'
                    )
                    ->where(
                        'firmas_arqueos.valida',
                        true
                    );
            })
            ->count();

        $ultimosArqueosAgentes = $agenteIds->isEmpty()
            ? collect()
            : Arqueo::query()
                ->whereIn('agente_id', $agenteIds)
                ->where('tipo', 'DIARIO_AGENTE')
                ->where('estado', '!=', 'ANULADO')
                ->latest('fecha_arqueo')
                ->latest('id')
                ->limit(5)
                ->get();

        $ultimosArqueosPromotor = Arqueo::query()
            ->where('creado_por', $usuario->id)
            ->where('tipo', 'VISITA_PROMOTOR')
            ->latest('fecha_arqueo')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('dashboards.promotor', [
            'usuario' => $usuario,
            'rutasAsignadas' => $rutasAsignadas,
            'totalRutas' => $rutasAsignadas->count(),
            'agentesAsignados' => $agentesAsignados,
            'totalAgentes' => $totalAgentes,
            'agentesConArqueoHoy' => $agentesConArqueoHoy,
            'agentesPendientesHoy' => $agentesPendientesHoy,
            'pendientesCertificacion' => $pendientesCertificacion,
            'arqueosPromotorHoy' => $arqueosPromotorHoy,
            'pendientesFirmaAgente' => $pendientesFirmaAgente,
            'ultimosArqueosAgentes' => $ultimosArqueosAgentes,
            'ultimosArqueosPromotor' => $ultimosArqueosPromotor,
        ]);
    }

    private function obtenerAgentesAsignados(
        Collection $rutaIds
    ): Collection {
        if ($rutaIds->isEmpty()) {
            return collect();
        }

        return DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->whereIn('a.ruta_id', $rutaIds)
            ->where('a.estado', 'ACTIVO')
            ->select([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'r.id as ruta_id',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->orderBy('a.nombre_negocio')
            ->get();
    }
}
