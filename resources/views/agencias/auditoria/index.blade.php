@extends('layouts.auditoria')
@section('title','Historial de arqueos de auditoría')
@section('module-title','Historial de arqueos de auditoría')
@push('styles')
<style>
.ha{max-width:1240px;margin:auto;color:#173a5b}.ha *{box-sizing:border-box}
.ha-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin:0 0 24px}
.ha h1{font-size:27px;color:#123e67;margin:0 0 7px}.ha-muted{font-size:13px;color:#708497}
.ha-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.ha-card{background:#fff;border:1px solid #dce6ef;border-radius:15px;padding:22px;box-shadow:0 5px 20px rgba(19,60,100,.055);display:flex;flex-direction:column;gap:16px}
.ha-card h2{margin:0;color:#153f68;font-size:19px}.ha-top{display:flex;align-items:flex-start;gap:13px}
.ha-icon{display:grid;place-items:center;background:#eaf3fc;color:#145a95;border-radius:12px;width:49px;height:49px;font-size:23px;flex-shrink:0}
.ha-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
.ha-stat{background:#f3f7fb;padding:11px 7px;text-align:center;border-radius:9px}
.ha-stat strong{display:block;color:#123e67;font-size:20px}.ha-stat span{font-size:10px;color:#71849a}
.ha-btn{display:inline-flex;justify-content:center;align-items:center;gap:7px;background:#155c98;color:white!important;border:0;border-radius:9px;text-decoration:none;font-weight:750;font-size:13px;padding:12px 15px;cursor:pointer}
.ha-btn.alt{background:#edf4fb;color:#16558b!important}.ha-card .ha-btn{margin-top:auto}
.ha-crumbs{display:flex;gap:9px;align-items:center;flex-wrap:wrap;font-size:13px;margin-bottom:17px}
.ha-crumbs a{color:#155b98;text-decoration:none;font-weight:700}
.ha-panel{background:white;border:1px solid #dce6ef;border-radius:14px;padding:20px;margin-bottom:20px}
.ha-filters{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}
.ha-filters label{display:block;color:#4d6880;font-size:12px;font-weight:750}
.ha-filters input,.ha-filters select{display:block;width:100%;margin-top:7px;padding:11px;border:1px solid #c9d6e3;border-radius:8px;background:#fff;color:#193d5d}
.ha-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:15px}
.ha-table-wrap{overflow:auto}.ha-table{border-collapse:collapse;width:100%;min-width:830px}
.ha-table th,.ha-table td{text-align:left;padding:13px 10px;border-bottom:1px solid #e6edf3;font-size:12px}
.ha-table th{color:#667e94;background:#f5f8fc;font-size:11px}.ha-table td{color:#24455f}
.ha-pill{display:inline-block;padding:6px 9px;border-radius:50px;background:#eaf2fa;color:#19578a;font-size:11px;font-weight:750}
.ha-empty{padding:35px;text-align:center;color:#71849a;background:#fff;border:1px dashed #cbd9e5;border-radius:12px}
@media(max-width:1050px){.ha-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:700px){.ha-grid,.ha-filters{grid-template-columns:1fr}.ha h1{font-size:23px}}
</style>
@endpush
@section('content')
<div class="ha">
    <div class="ha-head">
        <div>
            <h1>Historial de arqueos de auditoría</h1>
            <div class="ha-muted">Consulta institucional por agencia, receptor y arqueo registrado.</div>
        </div>
        <a class="ha-btn alt" href="{{ route('auditoria.auditoria-agencias.create') }}">+ Realizar arqueo</a>
    </div>

    @if($agenciaSeleccionada)
    <nav class="ha-crumbs" aria-label="Navegación">
        <a href="{{ route('auditoria.auditoria-agencias.index') }}">Agencias</a>
        <span>›</span>
        @if($receptorSeleccionado)
            <a href="{{ route('auditoria.auditoria-agencias.index',['agencia'=>$agenciaSeleccionada->id]) }}">{{ $agenciaSeleccionada->nombre }}</a>
            <span>›</span><strong>{{ $receptorSeleccionado->nombre_completo ?? $receptorSeleccionado->usuario }}</strong>
        @else
            <strong>{{ $agenciaSeleccionada->nombre }}</strong>
        @endif
    </nav>
    @endif

    @if(!$agenciaSeleccionada)
        <div class="ha-grid">
        @forelse($agencias as $agencia)
            @php $r=$resumenAgencias->get($agencia->id); $total=(int)($r->total??0); $cert=(int)($r->certificados??0); $anul=(int)($r->anulados??0); @endphp
            <article class="ha-card">
                <div class="ha-top"><div class="ha-icon">⌂</div><div>
                    <h2>{{ $agencia->nombre }}</h2>
                    <div class="ha-muted">Código: {{ $agencia->codigo }} · {{ $agencia->estado ? 'Activa' : 'Inactiva' }}</div>
                    <div class="ha-muted">{{ (int)($receptoresPorAgencia->get($agencia->id)??0) }} receptores asignados actualmente</div>
                </div></div>
                <div class="ha-stats">
                    <div class="ha-stat"><strong>{{ $total }}</strong><span>Arqueos</span></div>
                    <div class="ha-stat"><strong>{{ $cert }}</strong><span>Certificados</span></div>
                    <div class="ha-stat"><strong>{{ $total-$cert-$anul }}</strong><span>Pendientes</span></div>
                </div>
                <div class="ha-muted">Anulados: {{ $anul }}</div>
                <a class="ha-btn" href="{{ route('auditoria.auditoria-agencias.index',['agencia'=>$agencia->id]) }}">Visualizar receptores →</a>
            </article>
        @empty
            <div class="ha-empty">No existen agencias registradas.</div>
        @endforelse
        </div>
    @elseif(!$receptorSeleccionado)
        <div class="ha-head"><div><h1>{{ $agenciaSeleccionada->nombre }}</h1><div class="ha-muted">Receptores actuales y receptores con historial de auditoría en esta agencia.</div></div>
            <a class="ha-btn alt" href="{{ route('auditoria.auditoria-agencias.index') }}">← Cambiar agencia</a>
        </div>
        <div class="ha-grid">
        @forelse($receptores as $receptor)
            @php $r=$resumenReceptores->get($receptor->id); $total=(int)($r->total??0); $cert=(int)($r->certificados??0); $anul=(int)($r->anulados??0); $operativas=$asignacionesReceptor->get($receptor->id,collect()); @endphp
            <article class="ha-card">
                <div class="ha-top"><div class="ha-icon">♙</div><div>
                    <h2>{{ $receptor->nombre_completo ?? $receptor->usuario }}</h2>
                    <div class="ha-muted">
                    @forelse($operativas as $op)
                        {{ $op->ventanilla?->codigo ?? '—' }} / {{ $op->turno?->nombre ?? '—' }}{{ !$loop->last ? ' · ' : '' }}
                    @empty
                        Sin asignación operativa vigente
                    @endforelse
                    </div>
                    <div class="ha-muted">Último arqueo: {{ $r?->ultima_fecha ? \Illuminate\Support\Carbon::parse($r->ultima_fecha)->format('d/m/Y') : 'Sin registros' }}</div>
                </div></div>
                <div class="ha-stats">
                    <div class="ha-stat"><strong>{{ $total }}</strong><span>Arqueos</span></div>
                    <div class="ha-stat"><strong>{{ $cert }}</strong><span>Certificados</span></div>
                    <div class="ha-stat"><strong>{{ $total-$cert-$anul }}</strong><span>Pendientes</span></div>
                </div>
                <div class="ha-muted">Anulados: {{ $anul }}</div>
                <a class="ha-btn" href="{{ route('auditoria.auditoria-agencias.index',['agencia'=>$agenciaSeleccionada->id,'receptor'=>$receptor->id]) }}">Historial de arqueos →</a>
            </article>
        @empty
            <div class="ha-empty">No hay receptores asignados ni registros históricos de auditoría para esta agencia.</div>
        @endforelse
        </div>
    @else
        <div class="ha-head"><div><h1>{{ $receptorSeleccionado->nombre_completo ?? $receptorSeleccionado->usuario }}</h1>
            <div class="ha-muted">Historial de auditoría · {{ $agenciaSeleccionada->nombre }}</div></div>
            <a class="ha-btn alt" href="{{ route('auditoria.auditoria-agencias.index',['agencia'=>$agenciaSeleccionada->id]) }}">← Volver a receptores</a>
        </div>
        <form method="GET" action="{{ route('auditoria.auditoria-agencias.index') }}" class="ha-panel">
            <input type="hidden" name="agencia" value="{{ $agenciaSeleccionada->id }}">
            <input type="hidden" name="receptor" value="{{ $receptorSeleccionado->id }}">
            <div class="ha-filters">
                <label>Número de arqueo<input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar arqueo..."></label>
                <label>Estado<select name="estado">
                    <option value="">Todos los estados</option>
                    @foreach(['PENDIENTE_FIRMA_RECEPTOR'=>'Pendiente firma receptor','PENDIENTE'=>'Pendiente certificación','CERTIFICADO'=>'Certificado','ANULADO'=>'Anulado'] as $clave=>$nombre)
                        <option value="{{ $clave }}" @selected(request('estado')===$clave)>{{ $nombre }}</option>
                    @endforeach
                </select></label>
                <label>Ventanilla<select name="ventanilla"><option value="">Todas</option>
                    @foreach($ventanillas as $v)<option value="{{ $v->id }}" @selected((string)request('ventanilla')===(string)$v->id)>{{ $v->codigo }}</option>@endforeach
                </select></label>
                <label>Desde<input type="date" name="desde" value="{{ request('desde') }}"></label>
                <label>Hasta<input type="date" name="hasta" value="{{ request('hasta') }}"></label>
                <label>Diferencias<select name="diferencia">
                    <option value="todos">Todas</option><option value="con" @selected(request('diferencia')==='con')>Con diferencia</option><option value="sin" @selected(request('diferencia')==='sin')>Sin diferencia</option>
                </select></label>
            </div>
            <div class="ha-actions"><button class="ha-btn" type="submit">Aplicar filtros</button>
                <a class="ha-btn alt" href="{{ route('auditoria.auditoria-agencias.index',['agencia'=>$agenciaSeleccionada->id,'receptor'=>$receptorSeleccionado->id]) }}">Limpiar filtros</a></div>
        </form>
        <div class="ha-panel">
            <div class="ha-muted" style="margin-bottom:13px">{{ $arqueos->total() }} arqueos encontrados</div>
            <div class="ha-table-wrap"><table class="ha-table">
                <thead><tr><th>Número</th><th>Fecha</th><th>Ventanilla / Turno</th><th>Total arqueado</th><th>Diferencia</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                @forelse($arqueos as $arqueo)
                    <tr>
                        <td><strong>{{ $arqueo->numero_arqueo }}</strong></td>
                        <td>{{ $arqueo->fecha_operativa?->format('d/m/Y') ?? $arqueo->fecha_operativa }}</td>
                        <td>{{ $arqueo->ventanilla_codigo_historico }} / {{ $arqueo->turno_nombre_historico }}</td>
                        <td>Q {{ number_format((float)$arqueo->total_arqueado,2) }}</td>
                        <td>Q {{ number_format((float)$arqueo->diferencia,2) }}</td>
                        <td><span class="ha-pill">{{ str_replace('_',' ',$arqueo->estado) }}</span></td>
                        <td><div class="ha-actions" style="margin:0">
                            <a class="ha-btn alt" href="{{ route('auditoria.auditoria-agencias.show',$arqueo->id) }}">Visualizar</a>
                            <a class="ha-btn alt" href="{{ route('auditoria.auditoria-agencias.imprimir',$arqueo->id) }}" target="_blank" rel="noopener">Imprimir</a>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;padding:28px">No se encontraron arqueos con los filtros seleccionados.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            <div style="margin-top:20px">{{ $arqueos->links() }}</div>
        </div>
    @endif
</div>
@endsection
