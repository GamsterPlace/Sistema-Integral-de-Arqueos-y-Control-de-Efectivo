<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArqueoAnuladoAgenciaController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        abort_unless($usuario, 403);
        $usuario->loadMissing('rol');
        abort_unless($usuario->rol?->nombre === 'Administrador', 403);

        $datos = $request->validate([
            'agencia_id' => ['nullable', 'integer', 'exists:agencias,id'],
            'receptor_id' => ['nullable', 'integer', 'exists:usuarios,id'],
            'tipo' => ['nullable', 'in:ORDINARIO,SORPRESIVO,ENTREGA,AUDITORIA'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);

        $agenciaId = (int) ($datos['agencia_id'] ?? 0);
        $receptorId = (int) ($datos['receptor_id'] ?? 0);
        $tipo = (string) ($datos['tipo'] ?? '');
        $desde = (string) ($datos['desde'] ?? '');
        $hasta = (string) ($datos['hasta'] ?? '');
        $buscar = trim((string) ($datos['buscar'] ?? ''));

        // Nivel 1: agencias, incluso las que no registran anulaciones.
        if (!$agenciaId) {
            $agencias = DB::table('agencias as a')
                ->leftJoin('arqueos_agencia as ar', function ($join) {
                    $join->on('ar.agencia_id', '=', 'a.id')
                        ->where('ar.estado', '=', 'ANULADO');
                })
                ->select('a.id', 'a.codigo', 'a.nombre', 'a.estado')
                ->selectRaw('COUNT(ar.id) as total_anulados')
                ->selectRaw('COUNT(DISTINCT ar.receptor_usuario_id) as receptores_afectados')
                ->selectRaw("SUM(CASE WHEN ar.tipo = 'ORDINARIO' THEN 1 ELSE 0 END) as ordinarios")
                ->selectRaw("SUM(CASE WHEN ar.tipo = 'SORPRESIVO' THEN 1 ELSE 0 END) as sorpresivos")
                ->selectRaw("SUM(CASE WHEN ar.tipo = 'ENTREGA' THEN 1 ELSE 0 END) as entregas")
                ->selectRaw("SUM(CASE WHEN ar.tipo = 'AUDITORIA' THEN 1 ELSE 0 END) as auditoria")
                ->groupBy('a.id', 'a.codigo', 'a.nombre', 'a.estado')
                ->orderBy('a.nombre')->get();

            $totales = (object) [
                'agencias' => $agencias->count(),
                'anulados' => $agencias->sum('total_anulados'),
                'ordinarios' => $agencias->sum('ordinarios'),
                'sorpresivos' => $agencias->sum('sorpresivos'),
            ];

            return view('administrador.arqueos-agencias.anulados', [
                'nivel' => 'agencias', 'agencias' => $agencias, 'totales' => $totales,
            ]);
        }

        $agencia = DB::table('agencias')->where('id', $agenciaId)->first();
        abort_unless($agencia, 404);

        // Nivel 2: solamente receptores con arqueos anulados en esta agencia.
        // No se exige una asignación actual: el historial permanece tras un traslado.
        if (!$receptorId) {
            $receptores = DB::table('arqueos_agencia as ar')
                ->leftJoin('usuarios as u', 'u.id', '=', 'ar.receptor_usuario_id')
                ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
                ->where('ar.agencia_id', $agenciaId)
                ->where('ar.estado', 'ANULADO')
                ->whereNotNull('ar.receptor_usuario_id')
                ->select('u.id', 'u.usuario', 'dp.nombres', 'dp.apellidos')
                ->selectRaw('MAX(ar.receptor_nombre_historico) as nombre_historico')
                ->selectRaw('COUNT(ar.id) as total_anulados')
                ->selectRaw("SUM(CASE WHEN ar.tipo = 'ORDINARIO' THEN 1 ELSE 0 END) as ordinarios")
                ->selectRaw("SUM(CASE WHEN ar.tipo = 'SORPRESIVO' THEN 1 ELSE 0 END) as sorpresivos")
                ->selectRaw("SUM(CASE WHEN ar.tipo = 'ENTREGA' THEN 1 ELSE 0 END) as entregas")
                ->selectRaw("SUM(CASE WHEN ar.tipo = 'AUDITORIA' THEN 1 ELSE 0 END) as auditoria")
                ->selectRaw('MAX(ar.fecha_operativa) as ultimo_arqueo')
                ->groupBy('u.id', 'u.usuario', 'dp.nombres', 'dp.apellidos')
                ->orderBy('dp.nombres')->orderBy('dp.apellidos')->get();

            $resumen = (object) [
                'receptores' => $receptores->count(),
                'anulados' => $receptores->sum('total_anulados'),
                'ordinarios' => $receptores->sum('ordinarios'),
                'sorpresivos' => $receptores->sum('sorpresivos'),
            ];

            return view('administrador.arqueos-agencias.anulados', compact('agencia', 'receptores', 'resumen') + ['nivel' => 'receptores']);
        }

        // Nivel 3: historial de anulaciones de un receptor en la agencia seleccionada.
        $receptor = DB::table('usuarios as u')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('u.id', $receptorId)
            ->first(['u.id', 'u.usuario', 'dp.nombres', 'dp.apellidos']);
        abort_unless($receptor, 404);

        $base = DB::table('arqueos_agencia as ar')
            ->where('ar.agencia_id', $agenciaId)
            ->where('ar.receptor_usuario_id', $receptorId)
            ->where('ar.estado', 'ANULADO');
        abort_unless((clone $base)->exists(), 404);

        $resumen = (clone $base)
            ->selectRaw('COUNT(*) as total_anulados')
            ->selectRaw("SUM(CASE WHEN ar.tipo = 'ORDINARIO' THEN 1 ELSE 0 END) as ordinarios")
            ->selectRaw("SUM(CASE WHEN ar.tipo = 'SORPRESIVO' THEN 1 ELSE 0 END) as sorpresivos")
            ->selectRaw("SUM(CASE WHEN ar.tipo = 'ENTREGA' THEN 1 ELSE 0 END) as entregas")
            ->selectRaw("SUM(CASE WHEN ar.tipo = 'AUDITORIA' THEN 1 ELSE 0 END) as auditoria")
            ->first();

        $tipos = (clone $base)->distinct()->orderBy('ar.tipo')->pluck('ar.tipo');
        $arqueos = (clone $base)
            ->leftJoin('anulaciones_arqueos_agencia as an', 'an.arqueo_agencia_id', '=', 'ar.id')
            ->leftJoin('usuarios as ua', 'ua.id', '=', 'an.anulado_por')
            ->leftJoin('datos_personales as da', 'da.usuario_id', '=', 'ua.id')
            ->when($tipo !== '', fn ($q) => $q->where('ar.tipo', $tipo))
            ->when($desde !== '', fn ($q) => $q->whereDate('ar.fecha_operativa', '>=', $desde))
            ->when($hasta !== '', fn ($q) => $q->whereDate('ar.fecha_operativa', '<=', $hasta))
            ->when($buscar !== '', fn ($q) => $q->where(function ($sub) use ($buscar) {
                $sub->where('ar.numero_arqueo', 'like', '%'.$buscar.'%')
                    ->orWhere('an.motivo', 'like', '%'.$buscar.'%')
                    ->orWhere('ar.ventanilla_codigo_historico', 'like', '%'.$buscar.'%');
            }))
            ->select('ar.id', 'ar.numero_arqueo', 'ar.fecha_operativa', 'ar.tipo',
                'ar.ventanilla_codigo_historico', 'ar.turno_nombre_historico',
                'ar.total_arqueado', 'ar.diferencia', 'ar.anulado_at',
                'an.motivo', 'an.fecha_anulacion', 'ua.usuario as anulado_por_usuario',
                'da.nombres as anulado_por_nombres', 'da.apellidos as anulado_por_apellidos')
            ->orderByDesc('an.fecha_anulacion')->orderByDesc('ar.id')
            ->paginate(15)->withQueryString();

        return view('administrador.arqueos-agencias.anulados', compact(
            'agencia', 'receptor', 'resumen', 'tipos', 'arqueos', 'tipo', 'desde', 'hasta', 'buscar'
        ) + ['nivel' => 'historial']);
    }
}
