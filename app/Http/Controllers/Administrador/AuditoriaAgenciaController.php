<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditoriaAgenciaController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();

        abort_unless($usuario, 403);

        $usuario->loadMissing('rol');

        abort_unless(
            $usuario->rol?->nombre === 'Administrador',
            403
        );

        $filtros = $request->validate([
            'agencia_id' => ['nullable', 'integer', 'exists:agencias,id'],
            'receptor_id' => ['nullable', 'integer', 'exists:usuarios,id'],
            'grupo' => ['nullable', 'in:agencia'],
            'estado' => ['nullable', 'string', 'max:40'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);

        $agenciaId = (int) ($filtros['agencia_id'] ?? 0);
        $receptorId = (int) ($filtros['receptor_id'] ?? 0);
        $grupo = (string) ($filtros['grupo'] ?? '');
        $estado = (string) ($filtros['estado'] ?? '');
        $desde = (string) ($filtros['desde'] ?? '');
        $hasta = (string) ($filtros['hasta'] ?? '');
        $buscar = trim((string) ($filtros['buscar'] ?? ''));

        $ruta = 'administrador.auditoria-agencias.index';

        if (!$agenciaId) {
            $agencias = DB::table('agencias as a')
                ->leftJoin('arqueos_agencia as ar', function ($join) {
                    $join->on('ar.agencia_id', '=', 'a.id')
                        ->where('ar.tipo', '=', 'AUDITORIA');
                })
                ->select('a.id', 'a.codigo', 'a.nombre', 'a.estado')
                ->selectRaw('COUNT(ar.id) as total')
                ->selectRaw('COUNT(DISTINCT ar.receptor_usuario_id) as receptores')
                ->selectRaw("SUM(CASE WHEN ar.estado = 'CERTIFICADO' THEN 1 ELSE 0 END) as certificados")
                ->selectRaw("SUM(CASE WHEN ar.estado = 'ANULADO' THEN 1 ELSE 0 END) as anulados")
                ->selectRaw("SUM(CASE WHEN ar.estado NOT IN ('CERTIFICADO','ANULADO') THEN 1 ELSE 0 END) as pendientes")
                ->selectRaw('SUM(CASE WHEN ar.diferencia IS NOT NULL AND ar.diferencia <> 0 THEN 1 ELSE 0 END) as diferencias')
                ->groupBy('a.id', 'a.codigo', 'a.nombre', 'a.estado')
                ->orderBy('a.nombre')
                ->get();

            $totales = (object) [
                'agencias' => $agencias->count(),
                'arqueos' => $agencias->sum('total'),
                'receptores' => $agencias->sum('receptores'),
                'diferencias' => $agencias->sum('diferencias'),
            ];

            return view('administrador.auditoria-agencias.index', [
                'nivel' => 'agencias',
                'agencias' => $agencias,
                'totales' => $totales,
                'ruta' => $ruta,
            ]);
        }

        $agencia = DB::table('agencias')
            ->where('id', $agenciaId)
            ->first();

        abort_unless($agencia, 404);

        if (!$receptorId && $grupo !== 'agencia') {
            $receptores = DB::table('arqueos_agencia as ar')
                ->leftJoin('usuarios as u', 'u.id', '=', 'ar.receptor_usuario_id')
                ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
                ->where('ar.agencia_id', $agenciaId)
                ->where('ar.tipo', 'AUDITORIA')
                ->whereNotNull('ar.receptor_usuario_id')
                ->select(
                    'ar.receptor_usuario_id as id',
                    'u.usuario',
                    'dp.nombres',
                    'dp.apellidos'
                )
                ->selectRaw('MAX(ar.receptor_nombre_historico) as nombre_historico')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN ar.estado = 'CERTIFICADO' THEN 1 ELSE 0 END) as certificados")
                ->selectRaw("SUM(CASE WHEN ar.estado = 'ANULADO' THEN 1 ELSE 0 END) as anulados")
                ->selectRaw('SUM(CASE WHEN ar.diferencia IS NOT NULL AND ar.diferencia <> 0 THEN 1 ELSE 0 END) as diferencias')
                ->selectRaw('MAX(ar.fecha_operativa) as ultima_fecha')
                ->groupBy(
                    'ar.receptor_usuario_id',
                    'u.usuario',
                    'dp.nombres',
                    'dp.apellidos'
                )
                ->orderBy('dp.nombres')
                ->orderBy('dp.apellidos')
                ->get();

            $generales = DB::table('arqueos_agencia')
                ->where('agencia_id', $agenciaId)
                ->where('tipo', 'AUDITORIA')
                ->whereNull('receptor_usuario_id')
                ->count();

            $resumen = (object) [
                'receptores' => $receptores->count(),
                'arqueos' => $receptores->sum('total') + $generales,
                'generales' => $generales,
                'diferencias' => $receptores->sum('diferencias'),
            ];

            return view('administrador.auditoria-agencias.index', [
                'nivel' => 'receptores',
                'agencia' => $agencia,
                'receptores' => $receptores,
                'resumen' => $resumen,
                'ruta' => $ruta,
            ]);
        }

        $receptor = null;

        if ($grupo !== 'agencia') {
            abort_unless($receptorId, 404);

            $receptor = DB::table('usuarios as u')
                ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
                ->where('u.id', $receptorId)
                ->first([
                    'u.id',
                    'u.usuario',
                    'dp.nombres',
                    'dp.apellidos',
                ]);

            abort_unless($receptor, 404);
        }

        $base = DB::table('arqueos_agencia as ar')
            ->where('ar.agencia_id', $agenciaId)
            ->where('ar.tipo', 'AUDITORIA');

        if ($grupo === 'agencia') {
            $base->whereNull('ar.receptor_usuario_id');
        } else {
            $base->where('ar.receptor_usuario_id', $receptorId);
        }

        abort_unless((clone $base)->exists(), 404);

        $resumen = (clone $base)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN ar.estado = 'CERTIFICADO' THEN 1 ELSE 0 END) as certificados")
            ->selectRaw("SUM(CASE WHEN ar.estado = 'ANULADO' THEN 1 ELSE 0 END) as anulados")
            ->selectRaw('SUM(CASE WHEN ar.diferencia IS NOT NULL AND ar.diferencia <> 0 THEN 1 ELSE 0 END) as diferencias')
            ->first();

        $estados = (clone $base)
            ->distinct()
            ->orderBy('ar.estado')
            ->pluck('ar.estado');

        abort_if(
            $estado !== '' && !$estados->contains($estado),
            422,
            'Estado no válido para este historial.'
        );

        $auditor = DB::table('firmas_arqueos_agencia as fa')
            ->select('fa.arqueo_agencia_id')
            ->selectRaw(
                "MAX(CONCAT(COALESCE(fa.nombres_historicos, ''), ' ', COALESCE(fa.apellidos_historicos, ''))) as auditor_nombre"
            )
            ->where('fa.tipo_firma', 'AUDITOR')
            ->groupBy('fa.arqueo_agencia_id');

        $hallazgos = DB::table('observaciones_arqueos_agencia')
            ->select('arqueo_agencia_id')
            ->selectRaw('COUNT(*) as total_hallazgos')
            ->groupBy('arqueo_agencia_id');

        $arqueos = (clone $base)
            ->leftJoinSub(
                $auditor,
                'aud',
                'aud.arqueo_agencia_id',
                '=',
                'ar.id'
            )
            ->leftJoinSub(
                $hallazgos,
                'hal',
                'hal.arqueo_agencia_id',
                '=',
                'ar.id'
            )
            ->when(
                $estado !== '',
                fn ($q) => $q->where('ar.estado', $estado)
            )
            ->when(
                $desde !== '',
                fn ($q) => $q->whereDate('ar.fecha_operativa', '>=', $desde)
            )
            ->when(
                $hasta !== '',
                fn ($q) => $q->whereDate('ar.fecha_operativa', '<=', $hasta)
            )
            ->when(
                $buscar !== '',
                fn ($q) => $q->where(function ($sub) use ($buscar) {
                    $sub->where('ar.numero_arqueo', 'like', '%'.$buscar.'%')
                        ->orWhere('ar.ventanilla_codigo_historico', 'like', '%'.$buscar.'%')
                        ->orWhere('aud.auditor_nombre', 'like', '%'.$buscar.'%');
                })
            )
            ->select(
                'ar.id',
                'ar.numero_arqueo',
                'ar.fecha_operativa',
                'ar.estado',
                'ar.ventanilla_codigo_historico',
                'ar.turno_nombre_historico',
                'ar.total_arqueado',
                'ar.total_corte',
                'ar.diferencia',
                'ar.certificado_at',
                'ar.anulado_at',
                'aud.auditor_nombre'
            )
            ->selectRaw('COALESCE(hal.total_hallazgos, 0) as total_hallazgos')
            ->orderByDesc('ar.fecha_operativa')
            ->orderByDesc('ar.id')
            ->paginate(15)
            ->withQueryString();

        return view('administrador.auditoria-agencias.index', [
            'nivel' => 'historial',
            'agencia' => $agencia,
            'receptor' => $receptor,
            'resumen' => $resumen,
            'estados' => $estados,
            'arqueos' => $arqueos,
            'estado' => $estado,
            'desde' => $desde,
            'hasta' => $hasta,
            'buscar' => $buscar,
            'ruta' => $ruta,
            'grupo' => $grupo,
        ]);
    }
}
