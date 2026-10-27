<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Query\Builder;

class ReporteController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarGerencia($usuario);

        $tipoReporte = trim((string) $request->string('tipo_reporte', 'RESUMEN_EJECUTIVO'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');
        $agenteId = $request->integer('agente_id');
        $promotorId = $request->integer('promotor_id');
        $tipoArqueo = trim((string) $request->string('tipo_arqueo'));

        $tiposReporte = $this->tiposReporte();
        if (! array_key_exists($tipoReporte, $tiposReporte)) {
            $tipoReporte = 'RESUMEN_EJECUTIVO';
        }

        $regiones = DB::table('regiones')->orderBy('nombre')->get(['id', 'nombre']);

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->when($regionId > 0, fn ($q) => $q->where('r.region_id', $regionId))
            ->orderBy('reg.nombre')->orderBy('r.nombre')
            ->get(['r.id', 'r.codigo', 'r.nombre', 'r.region_id', 'reg.nombre as region_nombre']);

        $agentes = DB::table('agentes')
            ->orderBy('nombre_negocio')
            ->get(['id', 'codigo_agente', 'nombre_negocio']);

        $promotores = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('rol.nombre', 'Promotor')
            ->orderBy('dp.nombres')->orderBy('dp.apellidos')
            ->get(['u.id', 'u.usuario', 'dp.nombres', 'dp.apellidos']);

        $base = $this->queryBase($desde, $hasta, $regionId, $rutaId, $agenteId, $promotorId, $tipoArqueo);
        $metricas = $this->metricas(clone $base);
        [$resultados, $columnas] = $this->generarReporte($tipoReporte, $base);

        return view('gerencia.reportes.index', compact(
            'tipoReporte','tiposReporte','desde','hasta','regionId','rutaId','agenteId','promotorId','tipoArqueo',
            'regiones','rutas','agentes','promotores','metricas','resultados','columnas'
        ));
    }

    public function imprimir(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarGerencia($usuario);

        $tipoReporte = trim((string) $request->string('tipo_reporte', 'RESUMEN_EJECUTIVO'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $regionId = $request->integer('region_id');
        $rutaId = $request->integer('ruta_id');
        $agenteId = $request->integer('agente_id');
        $promotorId = $request->integer('promotor_id');
        $tipoArqueo = trim((string) $request->string('tipo_arqueo'));

        $tiposReporte = $this->tiposReporte();
        if (! array_key_exists($tipoReporte, $tiposReporte)) {
            $tipoReporte = 'RESUMEN_EJECUTIVO';
        }

        $base = $this->queryBase($desde, $hasta, $regionId, $rutaId, $agenteId, $promotorId, $tipoArqueo);
        $metricas = $this->metricas(clone $base);
        [$resultados, $columnas] = $this->generarReporte($tipoReporte, $base, true);
        $tituloReporte = $tiposReporte[$tipoReporte];

        $pdf = Pdf::loadView('gerencia.reportes.pdf', compact(
            'tituloReporte','tipoReporte','desde','hasta','metricas','resultados','columnas'
        ))->setPaper('letter', count($columnas) > 6 ? 'landscape' : 'portrait');

        return $pdf->stream('reporte-gerencia-' . strtolower(str_replace('_', '-', $tipoReporte)) . '.pdf');
    }

    private function queryBase(?string $desde, ?string $hasta, int $regionId, int $rutaId, int $agenteId, int $promotorId, string $tipoArqueo)
    {
        return DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->leftJoin('usuarios as uc', 'uc.id', '=', 'arq.creado_por')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'uc.id')
            ->where('arq.estado', '!=', 'ANULADO')
            ->when($desde, fn ($q) => $q->whereDate('arq.fecha_arqueo', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('arq.fecha_arqueo', '<=', $hasta))
            ->when($regionId > 0, fn ($q) => $q->where('reg.id', $regionId))
            ->when($rutaId > 0, fn ($q) => $q->where('r.id', $rutaId))
            ->when($agenteId > 0, fn ($q) => $q->where('a.id', $agenteId))
            ->when($promotorId > 0, fn ($q) => $q->where('arq.creado_por', $promotorId))
            ->when($tipoArqueo !== '', fn ($q) => $q->where('arq.tipo', $tipoArqueo));
    }

    private function metricas(Builder $query): object
    {
        $m = $query
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN arq.tipo = ? THEN 1 ELSE 0 END) as agentes', ['DIARIO_AGENTE'])
            ->selectRaw('SUM(CASE WHEN arq.tipo = ? THEN 1 ELSE 0 END) as promotores', ['VISITA_PROMOTOR'])
            ->selectRaw('SUM(CASE WHEN arq.diferencia < 0 THEN 1 ELSE 0 END) as faltantes')
            ->selectRaw('SUM(CASE WHEN arq.diferencia > 0 THEN 1 ELSE 0 END) as sobrantes')
            ->selectRaw('SUM(CASE WHEN arq.diferencia = 0 THEN 1 ELSE 0 END) as exactos')
            ->selectRaw('SUM(CASE WHEN arq.diferencia < 0 THEN ABS(arq.diferencia) ELSE 0 END) as monto_faltantes')
            ->selectRaw('SUM(CASE WHEN arq.diferencia > 0 THEN arq.diferencia ELSE 0 END) as monto_sobrantes')
            ->selectRaw('SUM(CASE WHEN arq.fuera_fecha_ordinaria = 1 THEN 1 ELSE 0 END) as extemporaneos')
            ->first();

        return (object) [
            'total' => (int) ($m->total ?? 0),
            'agentes' => (int) ($m->agentes ?? 0),
            'promotores' => (int) ($m->promotores ?? 0),
            'faltantes' => (int) ($m->faltantes ?? 0),
            'sobrantes' => (int) ($m->sobrantes ?? 0),
            'exactos' => (int) ($m->exactos ?? 0),
            'monto_faltantes' => (float) ($m->monto_faltantes ?? 0),
            'monto_sobrantes' => (float) ($m->monto_sobrantes ?? 0),
            'extemporaneos' => (int) ($m->extemporaneos ?? 0),
        ];
    }

    private function generarReporte(string $tipoReporte, Builder $base, bool $paraPdf = false): array
    {
        $limit = $paraPdf ? 500 : 100;

        switch ($tipoReporte) {
            case 'FALTANTES':
                $resultados = (clone $base)->where('arq.diferencia', '<', 0)
                    ->select(['arq.numero_arqueo','arq.fecha_arqueo','a.codigo_agente','a.nombre_negocio','reg.nombre as region_nombre','r.nombre as ruta_nombre','arq.tipo','arq.diferencia'])
                    ->orderBy('arq.diferencia')->limit($limit)->get();
                $columnas = ['numero_arqueo'=>'Arqueo','fecha_arqueo'=>'Fecha','codigo_agente'=>'Código','nombre_negocio'=>'Agente','region_nombre'=>'Región','ruta_nombre'=>'Ruta','tipo'=>'Tipo','diferencia'=>'Faltante'];
                break;

            case 'SOBRANTES':
                $resultados = (clone $base)->where('arq.diferencia', '>', 0)
                    ->select(['arq.numero_arqueo','arq.fecha_arqueo','a.codigo_agente','a.nombre_negocio','reg.nombre as region_nombre','r.nombre as ruta_nombre','arq.tipo','arq.diferencia'])
                    ->orderByDesc('arq.diferencia')->limit($limit)->get();
                $columnas = ['numero_arqueo'=>'Arqueo','fecha_arqueo'=>'Fecha','codigo_agente'=>'Código','nombre_negocio'=>'Agente','region_nombre'=>'Región','ruta_nombre'=>'Ruta','tipo'=>'Tipo','diferencia'=>'Sobrante'];
                break;

            case 'RANKING_FALTANTES':
                $resultados = (clone $base)->where('arq.diferencia', '<', 0)
                    ->groupBy('a.id','a.codigo_agente','a.nombre_negocio','reg.nombre','r.nombre')
                    ->select(['a.id','a.codigo_agente','a.nombre_negocio','reg.nombre as region_nombre','r.nombre as ruta_nombre'])
                    ->selectRaw('COUNT(*) as incidencias')
                    ->selectRaw('SUM(ABS(arq.diferencia)) as monto')
                    ->orderByDesc('monto')->limit($limit)->get();
                $columnas = ['codigo_agente'=>'Código','nombre_negocio'=>'Agente','region_nombre'=>'Región','ruta_nombre'=>'Ruta','incidencias'=>'Incidencias','monto'=>'Monto Faltante'];
                break;

            case 'RANKING_SOBRANTES':
                $resultados = (clone $base)->where('arq.diferencia', '>', 0)
                    ->groupBy('a.id','a.codigo_agente','a.nombre_negocio','reg.nombre','r.nombre')
                    ->select(['a.id','a.codigo_agente','a.nombre_negocio','reg.nombre as region_nombre','r.nombre as ruta_nombre'])
                    ->selectRaw('COUNT(*) as incidencias')
                    ->selectRaw('SUM(arq.diferencia) as monto')
                    ->orderByDesc('monto')->limit($limit)->get();
                $columnas = ['codigo_agente'=>'Código','nombre_negocio'=>'Agente','region_nombre'=>'Región','ruta_nombre'=>'Ruta','incidencias'=>'Incidencias','monto'=>'Monto Sobrante'];
                break;

            case 'REGIONES_FALTANTES':
                $resultados = (clone $base)->where('arq.diferencia', '<', 0)
                    ->groupBy('reg.id','reg.nombre')
                    ->select(['reg.id','reg.nombre as region_nombre'])
                    ->selectRaw('COUNT(*) as incidencias')
                    ->selectRaw('SUM(ABS(arq.diferencia)) as monto')
                    ->orderByDesc('monto')->limit($limit)->get();
                $columnas = ['region_nombre'=>'Región','incidencias'=>'Incidencias','monto'=>'Monto Faltante'];
                break;

            case 'REGIONES_SOBRANTES':
                $resultados = (clone $base)->where('arq.diferencia', '>', 0)
                    ->groupBy('reg.id','reg.nombre')
                    ->select(['reg.id','reg.nombre as region_nombre'])
                    ->selectRaw('COUNT(*) as incidencias')
                    ->selectRaw('SUM(arq.diferencia) as monto')
                    ->orderByDesc('monto')->limit($limit)->get();
                $columnas = ['region_nombre'=>'Región','incidencias'=>'Incidencias','monto'=>'Monto Sobrante'];
                break;

            case 'PROMOTORES':
                $resultados = (clone $base)->where('arq.tipo', 'VISITA_PROMOTOR')
                    ->groupBy('uc.id','uc.usuario','dp.nombres','dp.apellidos')
                    ->select(['uc.id','uc.usuario','dp.nombres','dp.apellidos'])
                    ->selectRaw('COUNT(*) as total_arqueos')
                    ->selectRaw('SUM(CASE WHEN arq.diferencia < 0 THEN 1 ELSE 0 END) as faltantes')
                    ->selectRaw('SUM(CASE WHEN arq.diferencia > 0 THEN 1 ELSE 0 END) as sobrantes')
                    ->selectRaw('SUM(CASE WHEN arq.diferencia = 0 THEN 1 ELSE 0 END) as exactos')
                    ->orderByDesc('total_arqueos')->limit($limit)->get();
                $columnas = ['usuario'=>'Usuario','nombres'=>'Nombres','apellidos'=>'Apellidos','total_arqueos'=>'Arqueos','faltantes'=>'Faltantes','sobrantes'=>'Sobrantes','exactos'=>'Exactos'];
                break;

            default:
                $resultados = (clone $base)
                    ->select(['arq.numero_arqueo','arq.fecha_arqueo','arq.tipo','a.codigo_agente','a.nombre_negocio','reg.nombre as region_nombre','r.nombre as ruta_nombre','uc.usuario as responsable_usuario','arq.diferencia'])
                    ->orderByDesc('arq.fecha_arqueo')->orderByDesc('arq.id')->limit($limit)->get();
                $columnas = ['numero_arqueo'=>'Arqueo','fecha_arqueo'=>'Fecha','tipo'=>'Tipo','codigo_agente'=>'Código','nombre_negocio'=>'Agente','region_nombre'=>'Región','ruta_nombre'=>'Ruta','responsable_usuario'=>'Responsable','diferencia'=>'Diferencia'];
                break;
        }

        return [$resultados, $columnas];
    }

    private function tiposReporte(): array
    {
        return [
            'RESUMEN_EJECUTIVO' => 'Resumen Ejecutivo',
            'FALTANTES' => 'Agentes con Faltantes',
            'SOBRANTES' => 'Agentes con Sobrantes',
            'RANKING_FALTANTES' => 'Ranking de Agentes con más Faltantes',
            'RANKING_SOBRANTES' => 'Ranking de Agentes con más Sobrantes',
            'REGIONES_FALTANTES' => 'Regiones con más Faltantes',
            'REGIONES_SOBRANTES' => 'Regiones con más Sobrantes',
            'HISTORIAL_AGENTE' => 'Historial por Agente',
            'HISTORIAL_PROMOTOR' => 'Historial por Promotor',
            'PROMOTORES' => 'Rendimiento de Promotores',
        ];
    }

    private function validarGerencia(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol || $usuario->rol->nombre !== 'Gerencia',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
