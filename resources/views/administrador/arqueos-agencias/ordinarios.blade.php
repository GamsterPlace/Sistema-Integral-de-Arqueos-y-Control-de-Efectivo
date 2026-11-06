@extends('layouts.administrador')
@section('title', 'Arqueos Ordinarios por Agencia')
@section('module-title', 'Arqueos Ordinarios por Agencia')

@push('styles')
<style>
.oa-wrap{max-width:1350px;margin:0 auto;color:#263e52}.oa-wrap *{box-sizing:border-box}
.oa-top{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;margin-bottom:24px}
.oa-top h2{margin:0 0 7px;color:var(--azul-profundo,#06284f);font-size:25px}.oa-top p{margin:0;color:#6a8093;font-size:13px;line-height:1.6}
.oa-btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 15px;border:1px solid #cbdde9;border-radius:10px;background:#fff;color:#155384;font-weight:800;font-size:12px;text-decoration:none;min-height:40px}.oa-btn:hover{background:#edf5fc}.oa-btn.primary{background:#164c96;border-color:#164c96;color:#fff}
.oa-crumb{display:flex;align-items:center;gap:9px;flex-wrap:wrap;margin-bottom:18px;font-size:12px;color:#698095}.oa-crumb a{color:#155b98;text-decoration:none;font-weight:800}.oa-crumb span{color:#9aacba}
.oa-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:24px}
.oa-stat{padding:18px;border:1px solid #dce7ef;border-radius:14px;background:#fff;box-shadow:0 6px 20px rgba(20,58,89,.04)}.oa-stat small{display:block;color:#657e91;font-size:11px;font-weight:800;margin-bottom:8px}.oa-stat strong{font-size:25px;color:#124b7a}
.oa-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.oa-card{display:flex;flex-direction:column;min-width:0;border:1px solid #dce7ef;border-radius:16px;background:#fff;overflow:hidden;box-shadow:0 9px 26px rgba(18,56,90,.05)}
.oa-card-head{padding:20px 20px 15px;border-bottom:1px solid #e9f0f5;background:linear-gradient(120deg,#f5faff,#fff)}.oa-eyebrow{font-size:10px;font-weight:900;color:#59809f;letter-spacing:.06em;text-transform:uppercase}.oa-card h3{margin:8px 0 5px;font-size:18px;color:#123f67;overflow-wrap:anywhere}.oa-card-head p{margin:0;font-size:12px;color:#698095}
.oa-card-body{padding:17px 20px;flex:1}.oa-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.oa-metric{background:#f6f9fc;border:1px solid #e8eef4;border-radius:10px;padding:11px}.oa-metric small{display:block;font-size:10px;color:#73899a;margin-bottom:6px}.oa-metric strong{font-size:18px;color:#244c6c}.oa-card-footer{padding:0 20px 20px}.oa-card-footer .oa-btn{width:100%}
.oa-panel{border:1px solid #dce7ef;background:#fff;border-radius:15px;overflow:hidden;box-shadow:0 8px 25px rgba(20,58,89,.04)}.oa-panel-title{padding:17px 20px;border-bottom:1px solid #e5edf3;font-size:16px;font-weight:800;color:#16446d}
.oa-filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;padding:18px 20px}.oa-field label{display:block;font-size:11px;font-weight:800;color:#647c90;margin-bottom:7px}.oa-field input,.oa-field select{width:100%;min-height:40px;border:1px solid #d2e0e9;border-radius:9px;padding:9px 10px;background:#fff;color:#2d4b63;font-size:12px}.oa-filter-actions{display:flex;align-items:end;gap:8px}
.oa-scroll{overflow-x:auto}.oa-table{width:100%;border-collapse:collapse;min-width:790px}.oa-table th,.oa-table td{padding:14px 16px;border-bottom:1px solid #eaf0f4;text-align:left;font-size:12px}.oa-table th{background:#f5f9fc;color:#647e91;font-size:11px}.oa-table td{color:#34546c}.oa-table tr:last-child td{border-bottom:0}.oa-table td strong{color:#16446d}.oa-table .right{text-align:right}.oa-actions{display:flex;gap:7px;flex-wrap:wrap}
.oa-tag{display:inline-block;padding:5px 9px;border-radius:7px;background:#edf2f6;color:#5a6f80;font-weight:800;font-size:10px}.oa-tag.certificado{background:#e8f7ee;color:#197144}.oa-tag.anulado{background:#fff0f0;color:#a53b3b}.oa-tag.pendiente,.oa-tag.en_proceso{background:#fff5df;color:#916215}
.oa-empty{padding:35px;text-align:center;color:#788c9d;font-size:13px}.oa-pagination{padding:16px 20px;border-top:1px solid #e8eff4}.oa-note{font-size:12px;color:#72889a;margin-top:16px;line-height:1.6}
@media(max-width:1100px){.oa-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.oa-filters{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:700px){.oa-grid,.oa-summary,.oa-filters{grid-template-columns:1fr}.oa-top h2{font-size:21px}}
</style>
@endpush

@section('content')
@php
    $ruta = 'administrador.arqueos-agencias.ordinarios';
    $nombreReceptor = isset($receptor) ? (trim(($receptor->nombres ?? '').' '.($receptor->apellidos ?? '')) ?: $receptor->usuario) : '';
@endphp
<div class="oa-wrap">
    <div class="oa-crumb">
        <a href="{{ route($ruta) }}">Agencias</a>
        @if($nivel !== 'agencias')
            <span>/</span>
            @if($nivel === 'receptores')<strong>{{ $agencia->nombre }}</strong>
            @else<a href="{{ route($ruta, ['agencia_id' => $agencia->id]) }}">{{ $agencia->nombre }}</a><span>/</span><strong>{{ $nombreReceptor }}</strong>@endif
        @endif
    </div>

    <div class="oa-top">
        <div>
            @if($nivel === 'agencias')
                <h2>Arqueos ordinarios por agencia</h2>
                <p>Seleccione una agencia para consultar sus receptores y los arqueos realizados.</p>
            @elseif($nivel === 'receptores')
                <h2>{{ $agencia->nombre }}</h2>
                <p>Fichas de receptores y resumen de sus arqueos ordinarios en esta agencia.</p>
            @else
                <h2>Historial de {{ $nombreReceptor }}</h2>
                <p>{{ $agencia->nombre }} · Historial de arqueos ordinarios realizados por este receptor en la agencia.</p>
            @endif
        </div>
        <div>
            @if($nivel === 'historial')
                <a class="oa-btn" href="{{ route($ruta, ['agencia_id' => $agencia->id]) }}">Volver a receptores</a>
            @elseif($nivel === 'receptores')
                <a class="oa-btn" href="{{ route($ruta) }}">Volver a agencias</a>
            @else
                <a class="oa-btn" href="{{ route('administrador.arqueos-agencias.index') }}">Todos los arqueos</a>
            @endif
        </div>
    </div>

    @if($nivel === 'agencias')
        <div class="oa-summary">
            <div class="oa-stat"><small>Agencias registradas</small><strong>{{ number_format($totales->agencias) }}</strong></div>
            <div class="oa-stat"><small>Arqueos ordinarios</small><strong>{{ number_format($totales->arqueos) }}</strong></div>
            <div class="oa-stat"><small>Certificados</small><strong>{{ number_format($totales->certificados) }}</strong></div>
            <div class="oa-stat"><small>Pendientes</small><strong>{{ number_format($totales->pendientes) }}</strong></div>
        </div>
        <div class="oa-grid">
            @forelse($agencias as $item)
                <article class="oa-card">
                    <div class="oa-card-head"><div class="oa-eyebrow">Agencia · {{ $item->codigo }}</div><h3>{{ $item->nombre }}</h3><p>{{ $item->estado ?? 'Estado no especificado' }}</p></div>
                    <div class="oa-card-body"><div class="oa-metrics">
                        <div class="oa-metric"><small>Receptores con arqueos</small><strong>{{ number_format($item->receptores_con_arqueos) }}</strong></div>
                        <div class="oa-metric"><small>Total de arqueos</small><strong>{{ number_format($item->total_arqueos) }}</strong></div>
                        <div class="oa-metric"><small>Certificados</small><strong>{{ number_format($item->certificados) }}</strong></div>
                        <div class="oa-metric"><small>Pendientes</small><strong>{{ number_format($item->pendientes) }}</strong></div>
                        <div class="oa-metric"><small>Anulados</small><strong>{{ number_format($item->anulados) }}</strong></div>
                        <div class="oa-metric"><small>Con diferencia</small><strong>{{ number_format($item->diferencias) }}</strong></div>
                    </div></div>
                    <div class="oa-card-footer"><a class="oa-btn primary" href="{{ route($ruta, ['agencia_id' => $item->id]) }}">Ver receptores de la agencia</a></div>
                </article>
            @empty
                <div class="oa-empty">No hay agencias registradas.</div>
            @endforelse
        </div>
    @elseif($nivel === 'receptores')
        <div class="oa-summary">
            <div class="oa-stat"><small>Receptores registrados / históricos</small><strong>{{ number_format($resumen->receptores) }}</strong></div>
            <div class="oa-stat"><small>Arqueos ordinarios</small><strong>{{ number_format($resumen->arqueos) }}</strong></div>
            <div class="oa-stat"><small>Certificados</small><strong>{{ number_format($resumen->certificados) }}</strong></div>
            <div class="oa-stat"><small>Pendientes</small><strong>{{ number_format($resumen->pendientes) }}</strong></div>
        </div>
        <div class="oa-grid">
            @forelse($receptores as $item)
                @php $nombre = trim(($item->nombres ?? '').' '.($item->apellidos ?? '')) ?: $item->usuario; @endphp
                <article class="oa-card">
                    <div class="oa-card-head"><div class="oa-eyebrow">Receptor · {{ $item->usuario }}</div><h3>{{ $nombre }}</h3><p>Último arqueo: {{ $item->ultimo_arqueo ? \Carbon\Carbon::parse($item->ultimo_arqueo)->format('d/m/Y') : 'Sin registros' }}</p></div>
                    <div class="oa-card-body"><div class="oa-metrics">
                        <div class="oa-metric"><small>Arqueos realizados</small><strong>{{ number_format($item->total_arqueos) }}</strong></div>
                        <div class="oa-metric"><small>Certificados</small><strong>{{ number_format($item->certificados) }}</strong></div>
                        <div class="oa-metric"><small>Pendientes</small><strong>{{ number_format($item->pendientes) }}</strong></div>
                        <div class="oa-metric"><small>Anulados</small><strong>{{ number_format($item->anulados) }}</strong></div>
                        <div class="oa-metric"><small>Con diferencia</small><strong>{{ number_format($item->diferencias) }}</strong></div>
                    </div></div>
                    <div class="oa-card-footer"><a class="oa-btn primary" href="{{ route($ruta, ['agencia_id' => $agencia->id, 'receptor_id' => $item->id]) }}">Ver historial completo</a></div>
                </article>
            @empty
                <div class="oa-empty">Esta agencia no tiene receptores asignados ni arqueos ordinarios históricos.</div>
            @endforelse
        </div>
        <p class="oa-note">Se incluyen receptores con asignaciones registradas y aquellos que realizaron arqueos en esta agencia, aunque posteriormente hayan sido trasladados.</p>
    @else
        <div class="oa-summary">
            <div class="oa-stat"><small>Total de arqueos</small><strong>{{ number_format($resumen->total_arqueos) }}</strong></div>
            <div class="oa-stat"><small>Certificados</small><strong>{{ number_format($resumen->certificados) }}</strong></div>
            <div class="oa-stat"><small>Pendientes</small><strong>{{ number_format($resumen->pendientes) }}</strong></div>
            <div class="oa-stat"><small>Anulados</small><strong>{{ number_format($resumen->anulados) }}</strong></div>
        </div>
        <section class="oa-panel">
            <div class="oa-panel-title">Historial de arqueos ordinarios</div>
            <form method="GET" action="{{ route($ruta) }}" class="oa-filters">
                <input type="hidden" name="agencia_id" value="{{ $agencia->id }}">
                <input type="hidden" name="receptor_id" value="{{ $receptor->id }}">
                <div class="oa-field"><label>Desde</label><input type="date" name="desde" value="{{ $desde }}"></div>
                <div class="oa-field"><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta }}"></div>
                <div class="oa-field"><label>Estado</label><select name="estado"><option value="">Todos los estados</option>@foreach($estados as $opcion)<option value="{{ $opcion }}" @selected($estado === $opcion)>{{ str_replace('_',' ',$opcion) }}</option>@endforeach</select></div>
                <div class="oa-field"><label>Buscar número, ventanilla o turno</label><input type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar..."></div>
                <div class="oa-filter-actions"><button class="oa-btn primary" type="submit">Filtrar</button><a class="oa-btn" href="{{ route($ruta, ['agencia_id' => $agencia->id, 'receptor_id' => $receptor->id]) }}">Limpiar</a></div>
            </form>
            <div class="oa-scroll"><table class="oa-table"><thead><tr><th>Número</th><th>Fecha</th><th>Ventanilla / Turno</th><th>Estado</th><th class="right">Arqueado</th><th class="right">Corte</th><th class="right">Diferencia</th><th>Acciones</th></tr></thead><tbody>
                @forelse($arqueos as $item)
                    <tr>
                        <td><strong>{{ $item->numero_arqueo }}</strong>@if($item->fuera_fecha_ordinaria)<br><small>Extemporáneo</small>@endif</td>
                        <td>{{ $item->fecha_operativa ? \Carbon\Carbon::parse($item->fecha_operativa)->format('d/m/Y') : '—' }}</td>
                        <td>{{ $item->ventanilla_codigo_historico ?? '—' }}<br><small>{{ $item->turno_nombre_historico ?? '—' }}</small></td>
                        <td><span class="oa-tag {{ strtolower($item->estado) }}">{{ str_replace('_',' ',$item->estado) }}</span></td>
                        <td class="right">Q {{ number_format((float)$item->total_arqueado,2) }}</td>
                        <td class="right">Q {{ number_format((float)$item->total_corte,2) }}</td>
                        <td class="right"><strong>Q {{ number_format((float)$item->diferencia,2) }}</strong></td>
                        <td><div class="oa-actions"><a class="oa-btn" href="{{ route('administrador.arqueos-agencias.show', $item->id) }}">Detalle</a><a class="oa-btn" target="_blank" rel="noopener" href="{{ route('administrador.arqueos-agencias.imprimir', $item->id) }}">PDF</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="oa-empty">No se encontraron arqueos con los filtros seleccionados.</td></tr>
                @endforelse
            </tbody></table></div>
            @if($arqueos->hasPages())<div class="oa-pagination">{{ $arqueos->links() }}</div>@endif
        </section>
        <p class="oa-note">El historial conserva los arqueos ordinarios anulados y los realizados durante asignaciones anteriores en esta agencia. Los indicadores superiores muestran el historial total, sin aplicar los filtros de búsqueda.</p>
    @endif
</div>
@endsection