<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArqueoJefaturaCajaController extends Controller
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
            'estado' => ['nullable', 'string', 'max:35'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);

        $agenciaId = (int) ($datos['agencia_id'] ?? 0);
        $receptorId = (int) ($datos['receptor_id'] ?? 0);
        $estado = (string) ($datos['estado'] ?? '');
        $desde = (string) ($datos['desde'] ?? '');
        $hasta = (string) ($datos['hasta'] ?? '');
        $buscar = trim((string) ($datos['buscar'] ?? ''));

        // Nivel 1: todas las agencias, incluso las que todavía no registran arqueos.
        if (!$agenciaId) {
            $agencias = DB::table('agencias as a')
                ->leftJoin('arqueos_agencia as ar', function ($join) {
                    $join->on('ar.agencia_id', '=', 'a.id')->where('ar.tipo', '=', 'SORPRESIVO');
                })
                ->select('a.id', 'a.codigo', 'a.nombre', 'a.estado')
                ->selectRaw('COUNT(ar.id) as total_arqueos')
                ->selectRaw('COUNT(DISTINCT ar.receptor_usuario_id) as receptores_con_arqueos')
                ->selectRaw("SUM(CASE WHEN ar.estado = 'CERTIFICADO' THEN 1 ELSE 0 END) as certificados")
                ->selectRaw("SUM(CASE WHEN ar.estado = 'ANULADO' THEN 1 ELSE 0 END) as anulados")
                ->selectRaw("SUM(CASE WHEN ar.id IS NOT NULL AND ar.estado NOT IN ('CERTIFICADO','ANULADO') THEN 1 ELSE 0 END) as pendientes")
                ->selectRaw('SUM(CASE WHEN ar.diferencia <> 0 THEN 1 ELSE 0 END) as diferencias')
                ->groupBy('a.id', 'a.codigo', 'a.nombre', 'a.estado')
                ->orderBy('a.nombre')->get();

            $totales = (object) [
                'agencias' => $agencias->count(),
                'arqueos' => $agencias->sum('total_arqueos'),
                'certificados' => $agencias->sum('certificados'),
                'pendientes' => $agencias->sum('pendientes'),
            ];

            return view('administrador.arqueos-agencias.jefatura', [
                'nivel' => 'agencias', 'agencias' => $agencias, 'totales' => $totales,
            ]);
        }

        $agencia = DB::table('agencias')->where('id', $agenciaId)->first();
        abort_unless($agencia, 404);

        // Nivel 2: fichas de receptores a quienes jefatura practicó arqueos sorpresivos.
        // Se incluyen también receptores actualmente asignados aunque aún no tengan arqueos.
        if (!$receptorId) {
            $idsHistoricos = DB::table('arqueos_agencia')
                ->where('agencia_id', $agenciaId)->where('tipo', 'SORPRESIVO')
                ->whereNotNull('receptor_usuario_id')->select('receptor_usuario_id');
            $idsAsignados = DB::table('asignaciones_personal_agencia')
                ->where('agencia_id', $agenciaId)->where('cargo', 'RECEPTOR')
                ->select('usuario_id');

            $ids = DB::query()->fromSub($idsHistoricos->union($idsAsignados), 'personas')
                ->selectRaw('receptor_usuario_id as id')->pluck('id');

            // El UNION conserva la primera columna; los nombres de columna provienen de la primera consulta.
            $receptores = DB::table('usuarios as u')
                ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
                ->leftJoin('arqueos_agencia as ar', function ($join) use ($agenciaId) {
                    $join->on('ar.receptor_usuario_id', '=', 'u.id')
                        ->where('ar.agencia_id', '=', $agenciaId)
                        ->where('ar.tipo', '=', 'SORPRESIVO');
                })
                ->whereIn('u.id', $ids)
                ->select('u.id', 'u.usuario', 'dp.nombres', 'dp.apellidos')
                ->selectRaw('COUNT(ar.id) as total_arqueos')
                ->selectRaw("SUM(CASE WHEN ar.estado = 'CERTIFICADO' THEN 1 ELSE 0 END) as certificados")
                ->selectRaw("SUM(CASE WHEN ar.estado = 'ANULADO' THEN 1 ELSE 0 END) as anulados")
                ->selectRaw("SUM(CASE WHEN ar.id IS NOT NULL AND ar.estado NOT IN ('CERTIFICADO','ANULADO') THEN 1 ELSE 0 END) as pendientes")
                ->selectRaw('SUM(CASE WHEN ar.diferencia <> 0 THEN 1 ELSE 0 END) as diferencias')
                ->selectRaw('MAX(ar.fecha_operativa) as ultimo_arqueo')
                ->groupBy('u.id', 'u.usuario', 'dp.nombres', 'dp.apellidos')
                ->orderBy('dp.nombres')->orderBy('dp.apellidos')->get();

            $resumen = (object) [
                'receptores' => $receptores->count(),
                'arqueos' => $receptores->sum('total_arqueos'),
                'certificados' => $receptores->sum('certificados'),
                'pendientes' => $receptores->sum('pendientes'),
            ];

            return view('administrador.arqueos-agencias.jefatura', compact('agencia', 'receptores', 'resumen') + ['nivel' => 'receptores']);
        }

        // Nivel 3: historial de arqueos sorpresivos practicados a ese receptor EN ESA agencia.
        $receptor = DB::table('usuarios as u')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('u.id', $receptorId)
            ->first(['u.id', 'u.usuario', 'dp.nombres', 'dp.apellidos']);
        abort_unless($receptor, 404);

        $pertenece = DB::table('arqueos_agencia')->where('agencia_id', $agenciaId)
            ->where('tipo', 'SORPRESIVO')->where('receptor_usuario_id', $receptorId)->exists();
        $asignado = DB::table('asignaciones_personal_agencia')->where('agencia_id', $agenciaId)
            ->where('cargo', 'RECEPTOR')->where('usuario_id', $receptorId)->exists();
        abort_unless($pertenece || $asignado, 404);

        $base = DB::table('arqueos_agencia as ar')
            ->where('ar.tipo', 'SORPRESIVO')
            ->where('ar.agencia_id', $agenciaId)
            ->where('ar.receptor_usuario_id', $receptorId);

        $resumen = (clone $base)
            ->selectRaw('COUNT(*) as total_arqueos')
            ->selectRaw("SUM(CASE WHEN ar.estado = 'CERTIFICADO' THEN 1 ELSE 0 END) as certificados")
            ->selectRaw("SUM(CASE WHEN ar.estado = 'ANULADO' THEN 1 ELSE 0 END) as anulados")
            ->selectRaw("SUM(CASE WHEN ar.estado NOT IN ('CERTIFICADO','ANULADO') THEN 1 ELSE 0 END) as pendientes")
            ->selectRaw('SUM(CASE WHEN ar.diferencia <> 0 THEN 1 ELSE 0 END) as diferencias')
            ->first();

        $estados = (clone $base)->distinct()->orderBy('ar.estado')->pluck('ar.estado');
        $arqueos = (clone $base)
            ->when($estado !== '', fn ($q) => $q->where('ar.estado', $estado))
            ->when($desde !== '', fn ($q) => $q->whereDate('ar.fecha_operativa', '>=', $desde))
            ->when($hasta !== '', fn ($q) => $q->whereDate('ar.fecha_operativa', '<=', $hasta))
            ->when($buscar !== '', fn ($q) => $q->where(function ($sub) use ($buscar) {
                $sub->where('ar.numero_arqueo', 'like', '%'.$buscar.'%')
                    ->orWhere('ar.ventanilla_codigo_historico', 'like', '%'.$buscar.'%')
                    ->orWhere('ar.turno_nombre_historico', 'like', '%'.$buscar.'%');
            }))
            ->select('ar.id', 'ar.numero_arqueo', 'ar.fecha_operativa', 'ar.estado',
                'ar.ventanilla_codigo_historico', 'ar.turno_nombre_historico',
                'ar.total_arqueado', 'ar.total_corte', 'ar.diferencia', 'ar.fuera_fecha_ordinaria')
            ->orderByDesc('ar.fecha_operativa')->orderByDesc('ar.id')
            ->paginate(15)->withQueryString();

        return view('administrador.arqueos-agencias.jefatura', compact(
            'agencia', 'receptor', 'resumen', 'estados', 'arqueos', 'estado', 'desde', 'hasta', 'buscar'
        ) + ['nivel' => 'historial']);
    }
}
