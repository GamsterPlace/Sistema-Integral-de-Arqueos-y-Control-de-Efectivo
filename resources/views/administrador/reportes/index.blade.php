@extends('layouts.administrador')

@section('title', 'Reportes')
@section('module-title', 'Reportes')

@push('styles')
<style>
    .report-page{max-width:1550px;margin:0 auto}
    .report-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px}
    .report-card{display:block;padding:17px;border:1px solid #dfe7ee;border-radius:16px;background:#fff;text-decoration:none;transition:.2s ease}
    .report-card:hover{transform:translateY(-2px);border-color:#b9cfe4;box-shadow:0 10px 24px rgba(21,68,112,.08)}
    .report-card.active{border-color:var(--azul-principal);background:#f2f7fd;box-shadow:inset 0 0 0 1px var(--azul-principal)}
    .report-card strong{display:block;color:var(--azul-profundo);font-size:12px}
    .report-card span{display:block;margin-top:6px;color:#7b8b98;font-size:9px;line-height:1.45}
    .filters-card,.results-card{border:1px solid #e0e8ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(20,57,83,.05)}
    .filters-card{padding:18px;margin-bottom:20px}
    .filters-title{display:flex;align-items:center;gap:11px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #edf1f4}
    .filters-icon,.heading-icon{display:flex;align-items:center;justify-content:center;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo))}
    .filters-icon{width:38px;height:38px;border-radius:11px;color:var(--azul-principal);background:#edf4fb}
    .heading-icon{width:54px;height:54px;flex:0 0 54px;border-radius:16px;box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .filters-icon svg,.heading-icon svg,.btn svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .filters-icon svg{width:18px;height:18px}.heading-icon svg{width:26px;height:26px}.btn svg{width:16px;height:16px}
    .filters-title h3{margin:0;color:var(--azul-profundo);font-size:15px}.filters-title p{margin:3px 0 0;color:var(--texto-secundario);font-size:11px}
    .filters-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px}.filters-grid + .filters-grid{margin-top:11px}
    .form-control{width:100%;min-height:42px;padding:0 12px;border:1px solid #ced9e1;border-radius:10px;background:#fff;color:#30485b;box-sizing:border-box}
    .filters-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:14px;flex-wrap:wrap}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:0 15px;border:0;border-radius:10px;font-size:10px;font-weight:800;text-decoration:none;cursor:pointer}
    .btn-primary{background:var(--azul-principal);color:#fff}.btn-secondary{background:#edf2f5;color:#3d596f}.btn-success{background:var(--verde-oscuro);color:#fff}
    .metrics-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;margin-bottom:20px}
    .metric{padding:15px;border:1px solid #e1e8ed;border-radius:14px;background:#fff}.metric span{display:block;color:#758697;font-size:8px;font-weight:800;text-transform:uppercase}.metric strong{display:block;margin-top:7px;color:#082d55;font-size:19px}
    .results-header{display:flex;justify-content:space-between;gap:20px;align-items:center;padding:18px 20px;border-bottom:1px solid #edf1f4;background:#fafcfd}
    .results-header h3{margin:0;color:#0a3158;font-size:15px}.results-header p{margin:5px 0 0;color:#82909a;font-size:10px}
    .table-responsive{overflow-x:auto}.report-table{width:100%;min-width:1050px;border-collapse:collapse}
    .report-table th,.report-table td{padding:12px 13px;border-bottom:1px solid #edf1f4;text-align:left;vertical-align:middle;font-size:10px}
    .report-table th{background:#f7f9fb;color:#687b8b;font-size:8px;text-transform:uppercase}
    .money-negative{color:#b33a34;font-weight:800}.money-positive{color:#16834f;font-weight:800}.money-zero{color:#607586;font-weight:800}.pagination{padding:16px 18px}
    .module-heading{display:flex;align-items:center;gap:15px}
    @media(max-width:1200px){.report-grid{grid-template-columns:repeat(3,1fr)}.metrics-grid{grid-template-columns:repeat(3,1fr)}.filters-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:700px){.report-grid,.metrics-grid,.filters-grid{grid-template-columns:1fr}.filters-actions{flex-direction:column}.btn{width:100%}}
</style>
@endpush

@section('content')
<div class="report-page">
<div class="page-header">
    <div class="page-title module-heading">
        <div class="heading-icon">
            <svg viewBox="0 0 24 24"><path d="M4 19V5"></path><path d="M4 19h16"></path><path d="m7 15 4-4 3 2 5-6"></path></svg>
        </div>
        <div>
            <h2>Centro de Reportes</h2>
            <p>Analice faltantes, sobrantes, exactitud, historiales y comportamiento operativo por Agente, Promotor, Ruta y Región.</p>
        </div>
    </div>
</div>

<div class="report-grid">
    @php
        $descripciones = [
            'resumen' => 'Vista ejecutiva del período seleccionado.',
            'faltantes' => 'Detalle de arqueos con diferencia negativa.',
            'sobrantes' => 'Detalle de arqueos con diferencia positiva.',
            'ranking-faltantes' => 'Agentes con mayor recurrencia de faltantes.',
            'ranking-sobrantes' => 'Agentes con mayor recurrencia de sobrantes.',
            'exactos' => 'Arqueos donde la diferencia fue exactamente cero.',
            'historial-agente' => 'Historial completo filtrable por Agente.',
            'historial-promotor' => 'Visitas y arqueos realizados por Promotor.',
            'region-faltantes' => 'Regiones con mayor incidencia de faltantes.',
            'region-sobrantes' => 'Regiones con mayor incidencia de sobrantes.',
            'ruta-faltantes' => 'Rutas con mayor incidencia de faltantes.',
            'ruta-sobrantes' => 'Rutas con mayor incidencia de sobrantes.',
        ];
    @endphp

    @foreach($tiposReporte as $clave => $nombre)
        <a href="{{ route('administrador.reportes.index', array_merge(request()->except('page'), ['reporte' => $clave])) }}" class="report-card {{ $reporte === $clave ? 'active' : '' }}">
            <strong>{{ $nombre }}</strong>
            <span>{{ $descripciones[$clave] ?? 'Reporte administrativo del sistema.' }}</span>
        </a>
    @endforeach
</div>

<section class="filters-card">
    <div class="filters-title">
        <div class="filters-icon"><svg viewBox="0 0 24 24"><path d="M4 5h16l-6 7v5l-4 2v-7L4 5Z"></path></svg></div>
        <div><h3>Filtros del Reporte</h3><p>Seleccione los criterios para generar la información requerida.</p></div>
    </div>

    <form method="GET" action="{{ route('administrador.reportes.index') }}">
        <input type="hidden" name="reporte" value="{{ $reporte }}">
        <div class="filters-grid">
            <select name="agente_id" class="form-control">
                <option value="">Todos los Agentes</option>
                @foreach($agentes as $agente)
                    <option value="{{ $agente->id }}" @selected($agente_id === (int)$agente->id)>{{ $agente->codigo_agente }} — {{ $agente->nombre_negocio }}</option>
                @endforeach
            </select>
            <select name="promotor_id" class="form-control">
                <option value="">Todos los Promotores</option>
                @foreach($promotores as $promotor)
                    @php $nombrePromotor=trim(($promotor->nombres??'').' '.($promotor->apellidos??'')); @endphp
                    <option value="{{ $promotor->id }}" @selected($promotor_id === (int)$promotor->id)>{{ $nombrePromotor!==''?$nombrePromotor:$promotor->usuario }}</option>
                @endforeach
            </select>
            <select name="region_id" class="form-control">
                <option value="">Todas las Regiones</option>
                @foreach($regiones as $region)<option value="{{ $region->id }}" @selected($region_id === (int)$region->id)>{{ $region->nombre }}</option>@endforeach
            </select>
            <select name="ruta_id" class="form-control">
                <option value="">Todas las Rutas</option>
                @foreach($rutas as $ruta)<option value="{{ $ruta->id }}" @selected($ruta_id === (int)$ruta->id)>{{ $ruta->codigo }} — {{ $ruta->nombre }}</option>@endforeach
            </select>
        </div>
        <div class="filters-grid">
            <select name="tipo" class="form-control">
                <option value="">Todos los tipos de arqueo</option>
                <option value="DIARIO_AGENTE" @selected($tipo==='DIARIO_AGENTE')>Arqueo del Agente</option>
                <option value="VISITA_PROMOTOR" @selected($tipo==='VISITA_PROMOTOR')>Arqueo del Promotor</option>
                <option value="VISITA_AUDITORIA" @selected($tipo==='VISITA_AUDITORIA')>Arqueo de Auditoría</option>
            </select>
            <input type="date" name="desde" class="form-control" value="{{ $desde }}">
            <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
            <select name="por_pagina" class="form-control">
                @foreach([10,20,50,100] as $cantidad)<option value="{{ $cantidad }}" @selected($por_pagina===$cantidad)>{{ $cantidad }} resultados</option>@endforeach
            </select>
        </div>
        <div class="filters-actions">
            <a href="{{ route('administrador.reportes.index',['reporte'=>$reporte]) }}" class="btn btn-secondary">Limpiar Filtros</a>
            <a href="{{ route('administrador.reportes.imprimir',request()->query()) }}" target="_blank" class="btn btn-success">
                <svg viewBox="0 0 24 24"><path d="M6 9V3h12v6"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="7"></rect></svg>
                Imprimir PDF
            </a>
            <button type="submit" class="btn btn-primary">Generar Reporte</button>
        </div>
    </form>
</section>

@if(count($metricas))
<section class="metrics-grid">
    @foreach($metricas as $etiqueta=>$valor)<article class="metric"><span>{{ $etiqueta }}</span><strong>{{ $valor }}</strong></article>@endforeach
</section>
@endif

<section class="results-card">
    <header class="results-header"><div><h3>{{ $tiposReporte[$reporte] }}</h3><p>Resultados correspondientes a los filtros aplicados.</p></div></header>
    <div class="table-responsive">
        <table class="report-table">
            <thead><tr>@foreach($columnas as $columna=>$titulo)<th>{{ $titulo }}</th>@endforeach</tr></thead>
            <tbody>
            @forelse($resultados as $indice=>$fila)
                <tr>
                @foreach($columnas as $columna=>$titulo)
                    <td>
                    @switch($columna)
                        @case('posicion') {{ method_exists($resultados,'firstItem') ? ($resultados->firstItem()+$indice) : ($indice+1) }} @break
                        @case('agente') <strong>{{ $fila->codigo_agente }} — {{ $fila->nombre_negocio }}</strong> @break
                        @case('ruta') {{ $fila->ruta_codigo }} — {{ $fila->ruta_nombre }} @break
                        @case('responsable')
                            @php $responsable=trim(($fila->responsable_nombres??'').' '.($fila->responsable_apellidos??'')); @endphp
                            {{ $responsable!==''?$responsable:($fila->responsable_usuario??'—') }}
                            @break
                        @case('fecha_arqueo') {{ \Carbon\Carbon::parse($fila->fecha_arqueo)->format('d/m/Y') }} @break
                        @case('tipo') {{ $fila->tipo==='DIARIO_AGENTE'?'Agente':($fila->tipo==='VISITA_PROMOTOR'?'Promotor':($fila->tipo==='VISITA_AUDITORIA'?'Auditoría':str_replace('_',' ',$fila->tipo))) }} @break
                        @case('estado') {{ str_replace('_',' ',$fila->estado) }} @break
                        @case('diferencia')
                            @php $dif=(float)$fila->diferencia;$clase=$dif<0?'money-negative':($dif>0?'money-positive':'money-zero'); @endphp
                            <span class="{{ $clase }}">Q {{ number_format(abs($dif),2) }} {{ $dif<0?'Faltante':($dif>0?'Sobrante':'Exacto') }}</span>
                            @break
                        @case('saldo_sistema')
                        @case('total_arqueado')
                        @case('monto_acumulado')
                        @case('mayor_incidencia')
                            Q {{ number_format((float)$fila->{$columna},2) }}
                            @break
                        @default {{ $fila->{$columna}??'—' }}
                    @endswitch
                    </td>
                @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columnas) }}" style="padding:35px;text-align:center;">No existen resultados para los filtros seleccionados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($resultados,'hasPages') && $resultados->hasPages())<div class="pagination">{{ $resultados->links() }}</div>@endif
</section>
</div>
@endsection
