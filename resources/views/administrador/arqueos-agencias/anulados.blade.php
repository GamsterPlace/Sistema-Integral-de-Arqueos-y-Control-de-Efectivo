@extends('layouts.administrador')
@section('title', 'Arqueos Anulados de Agencias')
@section('module-title', 'Arqueos Anulados de Agencias')

@push('styles')
<style>
.aa-wrap{max-width:1350px;margin:0 auto;color:#263e52}.aa-wrap *{box-sizing:border-box}
.aa-top{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;margin-bottom:24px}.aa-top h2{margin:0 0 7px;color:#06284f;font-size:25px}.aa-top p{margin:0;color:#6a8093;font-size:13px;line-height:1.6}
.aa-btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 15px;border:1px solid #cbdde9;border-radius:10px;background:#fff;color:#155384;font-weight:800;font-size:12px;text-decoration:none;min-height:40px;cursor:pointer}.aa-btn:hover{background:#edf5fc}.aa-btn.primary{background:#164c96;border-color:#164c96;color:#fff}
.aa-crumb{display:flex;align-items:center;gap:9px;flex-wrap:wrap;margin-bottom:18px;font-size:12px;color:#698095}.aa-crumb a{color:#155b98;text-decoration:none;font-weight:800}.aa-crumb span{color:#9aacba}
.aa-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:24px}.aa-stat{padding:18px;border:1px solid #dce7ef;border-radius:14px;background:#fff;box-shadow:0 6px 20px rgba(20,58,89,.04)}.aa-stat small{display:block;color:#657e91;font-size:11px;font-weight:800;margin-bottom:8px}.aa-stat strong{font-size:25px;color:#124b7a}
.aa-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.aa-card{display:flex;flex-direction:column;min-width:0;border:1px solid #dce7ef;border-radius:16px;background:#fff;overflow:hidden;box-shadow:0 9px 26px rgba(18,56,90,.05)}.aa-card-head{padding:20px 20px 15px;border-bottom:1px solid #e9f0f5;background:linear-gradient(120deg,#f5faff,#fff)}.aa-eyebrow{font-size:10px;font-weight:900;color:#59809f;letter-spacing:.06em;text-transform:uppercase}.aa-card h3{margin:8px 0 5px;font-size:18px;color:#123f67;overflow-wrap:anywhere}.aa-card-head p{margin:0;font-size:12px;color:#698095}.aa-card-body{padding:17px 20px;flex:1}.aa-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.aa-metric{background:#f6f9fc;border:1px solid #e8eef4;border-radius:10px;padding:11px}.aa-metric small{display:block;font-size:10px;color:#73899a;margin-bottom:6px}.aa-metric strong{font-size:18px;color:#244c6c}.aa-card-footer{padding:0 20px 20px}.aa-card-footer .aa-btn{width:100%}
.aa-panel{border:1px solid #dce7ef;background:#fff;border-radius:15px;overflow:hidden;box-shadow:0 8px 25px rgba(20,58,89,.04)}.aa-panel-title{padding:17px 20px;border-bottom:1px solid #e5edf3;font-size:16px;font-weight:800;color:#16446d}.aa-filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;padding:18px 20px}.aa-field label{display:block;font-size:11px;font-weight:800;color:#647c90;margin-bottom:7px}.aa-field input,.aa-field select{width:100%;min-height:40px;border:1px solid #d2e0e9;border-radius:9px;padding:9px 10px;background:#fff;color:#2d4b63;font-size:12px}.aa-filter-actions{display:flex;align-items:end;gap:8px;flex-wrap:wrap}.aa-scroll{overflow-x:auto}.aa-table{width:100%;border-collapse:collapse;min-width:1050px}.aa-table th,.aa-table td{padding:14px 13px;border-bottom:1px solid #eaf0f4;text-align:left;font-size:12px;vertical-align:top}.aa-table th{background:#f5f9fc;color:#647e91;font-size:11px}.aa-table td{color:#34546c}.aa-table tr:last-child td{border-bottom:0}.aa-table td strong{color:#16446d}.aa-table .right{text-align:right}.aa-actions{display:flex;gap:7px;flex-wrap:wrap}.aa-tag{display:inline-block;padding:5px 9px;border-radius:7px;background:#fff0f0;color:#a53b3b;font-weight:800;font-size:10px}.aa-type{display:inline-block;padding:5px 8px;border-radius:7px;background:#eef5fc;color:#215c8d;font-weight:750;font-size:10px}.aa-motivo{max-width:280px;overflow-wrap:anywhere;line-height:1.5}.aa-empty{padding:35px;text-align:center;color:#788c9d;font-size:13px}.aa-pagination{padding:16px 20px;border-top:1px solid #e8eff4}.aa-note{font-size:12px;color:#72889a;margin-top:16px;line-height:1.6}
@media(max-width:1100px){.aa-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.aa-filters{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:700px){.aa-grid,.aa-summary,.aa-filters{grid-template-columns:1fr}.aa-top h2{font-size:21px}}
</style>
@endpush

@section('content')
@php
    $ruta = 'administrador.arqueos-agencias.anulados';
    $nombreReceptor = isset($receptor) ? (trim(($receptor->nombres ?? '').' '.($receptor->apellidos ?? '')) ?: $receptor->usuario) : '';
@endphp
<div class="aa-wrap">
    <div class="aa-crumb">
        <a href="{{ route($ruta) }}">Agencias</a>
        @if($nivel !== 'agencias')
            <span>/</span>
            @if($nivel === 'receptores')
                <strong>{{ $agencia->nombre }}</strong>
            @else
                <a href="{{ route($ruta, ['agencia_id' => $agencia->id]) }}">{{ $agencia->nombre }}</a><span>/</span><strong>{{ $nombreReceptor }}</strong>
            @endif
        @endif
    </div>
    <div class="aa-top">
        <div>
            @if($nivel === 'agencias')
                <h2>Arqueos Anulados de Agencias</h2>
                <p>Seleccione una agencia para revisar los arqueos anulados y su trazabilidad.</p>
            @elseif($nivel === 'receptores')
                <h2>{{ $agencia->nombre }}</h2>
                <p>Receptores con arqueos anulados en esta agencia, incluidos los registros históricos.</p>
            @else
                <h2>Historial de anulaciones: {{ $nombreReceptor }}</h2>
                <p>{{ $agencia->nombre }} · Motivos, responsables y documentos de arqueos anulados.</p>
            @endif
        </div>
        <div>
            @if($nivel === 'historial')
                <a class="aa-btn" href="{{ route($ruta, ['agencia_id' => $agencia->id]) }}">Volver a receptores</a>
            @elseif($nivel === 'receptores')
                <a class="aa-btn" href="{{ route($ruta) }}">Volver a agencias</a>
            @else
                <a class="aa-btn" href="{{ route('administrador.arqueos-agencias.index') }}">Todos los arqueos</a>
            @endif
        </div>
    </div>

    @if($nivel === 'agencias')
        <div class="aa-summary">
            <div class="aa-stat"><small>Agencias registradas</small><strong>{{ number_format($totales->agencias) }}</strong></div>
            <div class="aa-stat"><small>Arqueos anulados</small><strong>{{ number_format($totales->anulados) }}</strong></div>
            <div class="aa-stat"><small>Ordinarios anulados</small><strong>{{ number_format($totales->ordinarios) }}</strong></div>
            <div class="aa-stat"><small>Sorpresivos anulados</small><strong>{{ number_format($totales->sorpresivos) }}</strong></div>
        </div>
        <div class="aa-grid">
            @forelse($agencias as $item)
                <article class="aa-card">
                    <div class="aa-card-head"><div class="aa-eyebrow">Agencia · {{ $item->codigo }}</div><h3>{{ $item->nombre }}</h3><p>{{ $item->estado ?? 'Estado no especificado' }}</p></div>
                    <div class="aa-card-body"><div class="aa-metrics">
                        <div class="aa-metric"><small>Total anulados</small><strong>{{ number_format($item->total_anulados) }}</strong></div>
                        <div class="aa-metric"><small>Receptores afectados</small><strong>{{ number_format($item->receptores_afectados) }}</strong></div>
                        <div class="aa-metric"><small>Ordinarios</small><strong>{{ number_format($item->ordinarios) }}</strong></div>
                        <div class="aa-metric"><small>Sorpresivos</small><strong>{{ number_format($item->sorpresivos) }}</strong></div>
                        <div class="aa-metric"><small>Entregas</small><strong>{{ number_format($item->entregas) }}</strong></div>
                        <div class="aa-metric"><small>Auditoría</small><strong>{{ number_format($item->auditoria) }}</strong></div>
                    </div></div>
                    <div class="aa-card-footer"><a class="aa-btn primary" href="{{ route($ruta, ['agencia_id' => $item->id]) }}">Ver receptores</a></div>
                </article>
            @empty
                <div class="aa-empty">No hay agencias registradas.</div>
            @endforelse
        </div>
    @elseif($nivel === 'receptores')
        <div class="aa-summary">
            <div class="aa-stat"><small>Receptores con anulaciones</small><strong>{{ number_format($resumen->receptores) }}</strong></div>
            <div class="aa-stat"><small>Arqueos anulados</small><strong>{{ number_format($resumen->anulados) }}</strong></div>
            <div class="aa-stat"><small>Ordinarios anulados</small><strong>{{ number_format($resumen->ordinarios) }}</strong></div>
            <div class="aa-stat"><small>Sorpresivos anulados</small><strong>{{ number_format($resumen->sorpresivos) }}</strong></div>
        </div>
        <div class="aa-grid">
            @forelse($receptores as $item)
                @php
                    $nombre = trim(($item->nombres ?? '').' '.($item->apellidos ?? '')) ?: ($item->nombre_historico ?: ($item->usuario ?? 'Receptor'));
                @endphp
                <article class="aa-card">
                    <div class="aa-card-head"><div class="aa-eyebrow">Receptor · {{ $item->usuario ?? 'Sin usuario' }}</div><h3>{{ $nombre }}</h3><p>Último arqueo anulado: {{ $item->ultimo_arqueo ? \Carbon\Carbon::parse($item->ultimo_arqueo)->format('d/m/Y') : 'Sin fecha' }}</p></div>
                    <div class="aa-card-body"><div class="aa-metrics">
                        <div class="aa-metric"><small>Total anulados</small><strong>{{ number_format($item->total_anulados) }}</strong></div>
                        <div class="aa-metric"><small>Ordinarios</small><strong>{{ number_format($item->ordinarios) }}</strong></div>
                        <div class="aa-metric"><small>Sorpresivos</small><strong>{{ number_format($item->sorpresivos) }}</strong></div>
                        <div class="aa-metric"><small>Entregas</small><strong>{{ number_format($item->entregas) }}</strong></div>
                        <div class="aa-metric"><small>Auditoría</small><strong>{{ number_format($item->auditoria) }}</strong></div>
                    </div></div>
                    <div class="aa-card-footer"><a class="aa-btn primary" href="{{ route($ruta, ['agencia_id' => $agencia->id, 'receptor_id' => $item->id]) }}">Ver historial completo</a></div>
                </article>
            @empty
                <div class="aa-empty">Esta agencia no tiene arqueos anulados asociados a receptores.</div>
            @endforelse
        </div>
        <p class="aa-note">Los registros se agrupan por la agencia donde se realizó el arqueo, aunque el receptor haya sido trasladado posteriormente.</p>
    @else
        <div class="aa-summary">
            <div class="aa-stat"><small>Total anulados</small><strong>{{ number_format($resumen->total_anulados) }}</strong></div>
            <div class="aa-stat"><small>Ordinarios</small><strong>{{ number_format($resumen->ordinarios) }}</strong></div>
            <div class="aa-stat"><small>Sorpresivos</small><strong>{{ number_format($resumen->sorpresivos) }}</strong></div>
            <div class="aa-stat"><small>Entregas / Auditoría</small><strong>{{ number_format($resumen->entregas + $resumen->auditoria) }}</strong></div>
        </div>
        <section class="aa-panel">
            <div class="aa-panel-title">Historial completo de arqueos anulados</div>
            <form method="GET" action="{{ route($ruta) }}" class="aa-filters">
                <input type="hidden" name="agencia_id" value="{{ $agencia->id }}">
                <input type="hidden" name="receptor_id" value="{{ $receptor->id }}">
                <div class="aa-field"><label>Desde (fecha operativa)</label><input type="date" name="desde" value="{{ $desde }}"></div>
                <div class="aa-field"><label>Hasta (fecha operativa)</label><input type="date" name="hasta" value="{{ $hasta }}"></div>
                <div class="aa-field"><label>Tipo de arqueo</label><select name="tipo"><option value="">Todos los tipos</option>@foreach($tipos as $opcion)<option value="{{ $opcion }}" @selected($tipo === $opcion)>{{ str_replace('_', ' ', $opcion) }}</option>@endforeach</select></div>
                <div class="aa-field"><label>Buscar número, motivo o ventanilla</label><input type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar..."></div>
                <div class="aa-filter-actions"><button class="aa-btn primary" type="submit">Filtrar</button><a class="aa-btn" href="{{ route($ruta, ['agencia_id' => $agencia->id, 'receptor_id' => $receptor->id]) }}">Limpiar</a></div>
            </form>
            <div class="aa-scroll"><table class="aa-table"><thead><tr><th>Número / Estado</th><th>Tipo</th><th>Fecha operativa</th><th>Fecha anulación</th><th>Ventanilla / Turno</th><th>Responsable</th><th>Motivo</th><th class="right">Arqueado</th><th>Acciones</th></tr></thead><tbody>
                @forelse($arqueos as $item)
                    @php
                        $nombreAnulador = trim(($item->anulado_por_nombres ?? '').' '.($item->anulado_por_apellidos ?? '')) ?: ($item->anulado_por_usuario ?? 'No registrado');
                    @endphp
                    <tr>
                        <td><strong>{{ $item->numero_arqueo }}</strong><br><span class="aa-tag">ANULADO</span></td>
                        <td><span class="aa-type">{{ str_replace('_', ' ', $item->tipo) }}</span></td>
                        <td>{{ $item->fecha_operativa ? \Carbon\Carbon::parse($item->fecha_operativa)->format('d/m/Y') : '—' }}</td>
                        <td>{{ ($item->fecha_anulacion ?? $item->anulado_at) ? \Carbon\Carbon::parse($item->fecha_anulacion ?? $item->anulado_at)->format('d/m/Y H:i') : '—' }}</td>
                        <td>{{ $item->ventanilla_codigo_historico ?? '—' }}<br><small>{{ $item->turno_nombre_historico ?? '—' }}</small></td>
                        <td>{{ $nombreAnulador }}</td>
                        <td><div class="aa-motivo">{{ $item->motivo ?: 'Motivo no registrado' }}</div></td>
                        <td class="right">Q {{ number_format((float)$item->total_arqueado, 2) }}</td>
                        <td><div class="aa-actions"><a class="aa-btn" href="{{ route('administrador.arqueos-agencias.show', $item->id) }}">Detalle</a><a class="aa-btn" target="_blank" rel="noopener" href="{{ route('administrador.arqueos-agencias.imprimir', $item->id) }}">PDF</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="aa-empty">No se encontraron anulaciones con los filtros seleccionados.</td></tr>
                @endforelse
            </tbody></table></div>
            @if($arqueos->hasPages())<div class="aa-pagination">{{ $arqueos->links() }}</div>@endif
        </section>
        <p class="aa-note">Las anulaciones son registros históricos irreversibles. Los indicadores muestran el historial completo del receptor en esta agencia, independientemente de los filtros aplicados.</p>
    @endif
</div>
@endsection
