@extends($ruta === 'jefecaja' ? 'layouts.jefe-caja' : 'layouts.subjefe-caja')
@section('title', 'Anulación de Arqueos')
@section('module-title', 'Anulación de Arqueos')

@push('styles')
<style>
.aa-page{max-width:1250px;margin:auto;display:grid;gap:20px;color:#19344d}.aa-page *{box-sizing:border-box}
.aa-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:15px}.aa-header h1{margin:0 0 7px;color:#06284f;font-size:27px}.aa-muted{margin:0;color:#718498;font-size:13px;line-height:1.6}
.aa-btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 15px;border-radius:10px;background:#145a99;color:#fff!important;text-decoration:none;font-size:12px;font-weight:800;border:0;cursor:pointer;white-space:nowrap}.aa-btn.light{background:#eaf2fc;color:#145a99!important}.aa-btn:hover{filter:brightness(.96)}
.aa-warning{padding:15px 18px;border:1px solid #f0d8a7;border-radius:12px;background:#fffaf0;color:#76531c;font-size:13px;line-height:1.7}
.aa-toolbar{background:#fff;border:1px solid #dce7f1;border-radius:14px;padding:18px;display:flex;gap:12px;flex-wrap:wrap;align-items:end}.aa-field{display:grid;gap:6px;flex:1 1 220px}.aa-field label{font-size:11px;color:#61788c;font-weight:800}.aa-field input,.aa-field select{min-height:41px;width:100%;border:1px solid #cfdeea;border-radius:9px;padding:9px 11px;background:#fff;color:#19344d}
.aa-cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.aa-card{background:#fff;border:1px solid #dce7f1;border-radius:15px;box-shadow:0 6px 24px #12345a0b;overflow:hidden;min-width:0}.aa-card-head{padding:19px 20px;background:#f7faff;border-bottom:1px solid #e5edf5;display:flex;gap:12px;align-items:center}.aa-avatar{height:45px;width:45px;flex:0 0 45px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#e2eefb;color:#155b98;font-size:19px;font-weight:850}.aa-person{min-width:0;flex:1}.aa-person h2{font-size:15px;color:#123e68;margin:0 0 5px;overflow-wrap:anywhere}.aa-person p{font-size:12px;color:#6c8296;margin:0;overflow-wrap:anywhere}.aa-card-body{padding:17px 20px}.aa-card-title{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:12px}.aa-card-title strong{font-size:12px;color:#395b79}.aa-count{background:#edf4fc;color:#164c96;padding:5px 9px;border-radius:30px;font-size:10px;font-weight:800}
.aa-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:center;border-top:1px solid #e8eef4;padding:12px 0}.aa-item:first-of-type{border-top:0}.aa-number{font-weight:850;font-size:12px;color:#173b61;overflow-wrap:anywhere}.aa-date{font-size:11px;color:#7b8d9e;margin-top:5px}.aa-item-right{display:flex;align-items:flex-end;flex-direction:column;gap:7px}.aa-state{display:inline-block;border-radius:25px;padding:5px 9px;font-size:10px;font-weight:850;background:#edf3fa;color:#27547c}.aa-state.pending{background:#fff3dc;color:#986110}.aa-state.certified{background:#e8f7ef;color:#177847}.aa-state.void{background:#fdecec;color:#a33838}.aa-link{font-size:11px;font-weight:800;color:#145a99;text-decoration:none}.aa-link:hover{text-decoration:underline}.aa-empty{padding:19px 0;color:#718498;font-size:12px;line-height:1.6}.aa-card-footer{border-top:1px solid #e5edf5;padding:14px 20px;background:#fbfdff;display:flex;justify-content:flex-end}
.aa-panel{background:#fff;border:1px solid #dce7f1;border-radius:15px;overflow:hidden}.aa-panel-head{padding:20px;border-bottom:1px solid #e5edf5;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}.aa-panel-head h2{font-size:18px;color:#123e68;margin:0 0 5px}.aa-table-wrap{overflow-x:auto}.aa-table{width:100%;border-collapse:collapse;min-width:720px}.aa-table th{background:#f3f8fd;color:#5c748b;font-size:11px;text-align:left;padding:13px}.aa-table td{font-size:12px;border-top:1px solid #e9eff5;padding:13px;color:#284860}.aa-pagination{padding:18px;border-top:1px solid #e5edf5}.aa-no-results{text-align:center;padding:26px;color:#718498;font-size:13px}
@media(max-width:900px){.aa-cards{grid-template-columns:1fr}}@media(max-width:550px){.aa-header h1{font-size:23px}.aa-card-head,.aa-card-body,.aa-card-footer{padding:15px}.aa-item{gap:7px}}
</style>
@endpush

@section('content')
<div class="aa-page">
    <header class="aa-header">
        <div>
            <h1>{{ $historial ? 'Historial completo de arqueos' : 'Anulación de Arqueos' }}</h1>
            <p class="aa-muted">Consulte los arqueos ordinarios de sus receptores y revise sus detalles antes de anular.</p>
        </div>
        <a class="aa-btn light" href="{{ $historial ? route($ruta.'.anulaciones.index') : route($ruta.'.dashboard') }}">
            {{ $historial ? '← Volver a receptores' : '← Volver al panel' }}
        </a>
    </header>

    <div class="aa-warning"><strong>Operación irreversible.</strong> El Jefe y Subjefe de Caja pueden anular arqueos ordinarios pendientes o certificados de sus agencias autorizadas. Se exige motivo, firma electrónica y registro de auditoría. Los arqueos anulados permanecen disponibles para consulta.</div>

    @if($historial !== null)
        <section class="aa-panel">
            <div class="aa-panel-head">
                <div>
                    <h2>{{ $receptorSeleccionado->nombre }}</h2>
                    <p class="aa-muted">Receptor · {{ $receptorSeleccionado->agencia_nombre }} · {{ $historial->total() }} arqueos registrados</p>
                </div>
                <a class="aa-btn light" href="{{ route($ruta.'.anulaciones.index') }}">Ver todas las fichas</a>
            </div>
            <div class="aa-table-wrap">
                <table class="aa-table">
                    <thead><tr><th>Número de arqueo</th><th>Fecha operativa</th><th>Total arqueado</th><th>Estado</th><th>Acción</th></tr></thead>
                    <tbody>
                        @forelse($historial as $arqueo)
                            @php
                                $clase = match($arqueo->estado) {'PENDIENTE'=>'pending','CERTIFICADO'=>'certified','ANULADO'=>'void',default=>''};
                            @endphp
                            <tr>
                                <td><strong>{{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}</strong></td>
                                <td>{{ $arqueo->fecha_operativa ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y') : '—' }}</td>
                                <td>Q {{ number_format((float)$arqueo->total_arqueado,2) }}</td>
                                <td><span class="aa-state {{ $clase }}">{{ $arqueo->estado }}</span></td>
                                <td><a class="aa-btn light" href="{{ route($ruta.'.anulaciones.show',$arqueo->id) }}">Ver detalle</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="aa-no-results">Este receptor no tiene arqueos ordinarios registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($historial->hasPages())<div class="aa-pagination">{{ $historial->links() }}</div>@endif
        </section>
    @else
        <div class="aa-toolbar">
            <div class="aa-field"><label for="aa-search">Buscar receptor o agencia</label><input id="aa-search" type="search" placeholder="Nombre del receptor o agencia" autocomplete="off"></div>
            <div class="aa-field"><label for="aa-status">Estado entre los tres arqueos recientes</label><select id="aa-status"><option value="">Todos los estados</option><option value="PENDIENTE">Pendientes</option><option value="CERTIFICADO">Certificados</option><option value="ANULADO">Anulados</option></select></div>
        </div>
        <div class="aa-cards" id="aa-cards">
            @forelse($receptores as $receptor)
                <article class="aa-card" data-name="{{ \Illuminate\Support\Str::lower($receptor->nombre.' '.$receptor->agencia_nombre) }}" data-states="{{ $receptor->arqueos->pluck('estado')->implode(' ') }}">
                    <div class="aa-card-head">
                        <div class="aa-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($receptor->nombre,0,1)) }}</div>
                        <div class="aa-person"><h2>{{ $receptor->nombre }}</h2><p>Receptor · {{ $receptor->agencia_nombre }}</p></div>
                    </div>
                    <div class="aa-card-body">
                        <div class="aa-card-title"><strong>Últimos 3 arqueos ordinarios</strong><span class="aa-count">{{ $receptor->total_arqueos }} en historial</span></div>
                        @forelse($receptor->arqueos as $arqueo)
                            @php
                                $clase = match($arqueo->estado) {'PENDIENTE'=>'pending','CERTIFICADO'=>'certified','ANULADO'=>'void',default=>''};
                            @endphp
                            <div class="aa-item">
                                <div><div class="aa-number">{{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}</div><div class="aa-date">{{ $arqueo->fecha_operativa ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y') : 'Sin fecha' }} · Q {{ number_format((float)$arqueo->total_arqueado,2) }}</div></div>
                                <div class="aa-item-right"><span class="aa-state {{ $clase }}">{{ $arqueo->estado }}</span><a class="aa-link" href="{{ route($ruta.'.anulaciones.show',$arqueo->id) }}">Ver detalle</a></div>
                            </div>
                        @empty
                            <p class="aa-empty">Este receptor todavía no tiene arqueos ordinarios registrados.</p>
                        @endforelse
                    </div>
                    <div class="aa-card-footer">
                        <a class="aa-btn light" href="{{ route($ruta.'.anulaciones.index',['receptor'=>$receptor->id,'agencia'=>$receptor->agencia_id]) }}">Historial completo →</a>
                    </div>
                </article>
            @empty
                <div class="aa-no-results">No se encontraron receptores con asignación vigente en sus agencias autorizadas.</div>
            @endforelse
        </div>
        <p id="aa-no-results" class="aa-no-results" hidden>No hay receptores que coincidan con los filtros seleccionados.</p>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('aa-search');
    const status = document.getElementById('aa-status');
    if (!search || !status) return;
    const cards = Array.from(document.querySelectorAll('#aa-cards .aa-card'));
    const empty = document.getElementById('aa-no-results');
    function filterCards() {
        const term = search.value.trim().toLocaleLowerCase();
        const selected = status.value;
        let visible = 0;
        cards.forEach(card => {
            const matches = (!term || (card.dataset.name || '').includes(term)) && (!selected || (card.dataset.states || '').split(' ').includes(selected));
            card.hidden = !matches;
            if (matches) visible++;
        });
        if (empty) empty.hidden = visible > 0 || cards.length === 0;
    }
    search.addEventListener('input', filterCards);
    status.addEventListener('change', filterCards);
});
</script>
@endpush
