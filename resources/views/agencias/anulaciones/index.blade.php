@extends($ruta === 'jefecaja' ? 'layouts.jefe-caja' : 'layouts.subjefe-caja')

@section('title', 'Anulación de Arqueos')
@section('module-title', 'Anulación de Arqueos')

@push('styles')
<style>
    .aa-page{display:grid;gap:22px;color:#19344d}
    .aa-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .aa-header h1{margin:0;color:#06284f;font-size:clamp(23px,3vw,29px);letter-spacing:-.5px}
    .aa-header p{margin:8px 0 0;color:#6d7d8a;font-size:14px;line-height:1.65;max-width:750px}
    .aa-back{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 15px;border-radius:11px;background:#eaf2fc;color:#164c96;font-size:12px;font-weight:800;white-space:nowrap}
    .aa-back:hover{background:#dceafc}
    .aa-note{display:flex;gap:12px;align-items:flex-start;border:1px solid #f0d8a7;border-radius:14px;background:#fffaf0;padding:15px 17px;color:#76531c;font-size:13px;line-height:1.65}
    .aa-note svg{width:20px;height:20px;flex:0 0 20px;margin-top:1px}
    .aa-panel{background:#fff;border:1px solid #dfe7ee;border-radius:17px;overflow:hidden;box-shadow:0 5px 20px rgba(6,40,79,.045)}
    .aa-panel-header{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap;padding:20px 22px;border-bottom:1px solid #e9eef3}
    .aa-panel-header h2{margin:0;color:#06284f;font-size:17px}
    .aa-panel-header p{margin:5px 0 0;color:#708194;font-size:12px;line-height:1.5}
    .aa-counter{background:#edf4fc;color:#164c96;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:800}
    .aa-filters{display:flex;gap:12px;align-items:end;flex-wrap:wrap;padding:17px 22px;background:#fbfcfe;border-bottom:1px solid #e9eef3}
    .aa-filter{display:grid;gap:6px;flex:1 1 180px;max-width:280px}
    .aa-filter label{color:#52687e;font-size:11px;font-weight:800}
    .aa-filter select,.aa-filter input{width:100%;min-height:40px;padding:9px 11px;border:1px solid #d4e0eb;border-radius:9px;background:#fff;color:#19344d;outline:none;font-size:12px}
    .aa-filter select:focus,.aa-filter input:focus{border-color:#164c96;box-shadow:0 0 0 3px rgba(22,76,150,.10)}
    .aa-table-wrap{overflow-x:auto}
    .aa-table{width:100%;border-collapse:collapse;text-align:left;font-size:12px}
    .aa-table th{padding:13px 15px;background:#f5f8fc;color:#61778c;font-size:11px;font-weight:850;white-space:nowrap}
    .aa-table td{padding:15px;border-top:1px solid #edf1f5;vertical-align:middle}
    .aa-table tbody tr:hover{background:#fafcff}
    .aa-strong{font-weight:850;color:#173b61}
    .aa-secondary{display:block;margin-top:4px;color:#7b8d9e;font-size:11px}
    .aa-state{display:inline-block;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:850;letter-spacing:.02em;white-space:nowrap}
    .aa-state.pending{background:#fff3dc;color:#986110}
    .aa-state.certified{background:#e8f7ef;color:#177847}
    .aa-state.void{background:#fdecec;color:#a33838}
    .aa-detail{display:inline-flex;align-items:center;justify-content:center;padding:9px 13px;background:#eaf2fc;color:#164c96;border-radius:9px;font-size:11px;font-weight:850;white-space:nowrap}
    .aa-detail:hover{background:#d9e9fc}
    .aa-empty{padding:35px 18px;text-align:center;color:#708194;font-size:13px;line-height:1.65}
    .aa-footer{padding:17px 22px;border-top:1px solid #e9eef3}
    .aa-footer nav{max-width:100%;overflow-x:auto}
    @media(max-width:650px){.aa-panel-header,.aa-filters{padding:16px}.aa-table th,.aa-table td{padding:12px}.aa-header{align-items:stretch}.aa-back{width:fit-content}}
</style>
@endpush

@section('content')
<div class="aa-page">
    <header class="aa-header">
        <div>
            <h1>Anulación de Arqueos</h1>
            <p>Consulte los arqueos ordinarios de sus agencias asignadas y revise el detalle antes de solicitar una anulación.</p>
        </div>
        <a class="aa-back" href="{{ route($ruta . '.dashboard') }}">← Volver al panel</a>
    </header>

    <div class="aa-note" role="note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 21h20L12 3Z"/><path d="M12 9v5m0 4h.01"/></svg>
        <div><strong>Operación irreversible.</strong> Tanto el Jefe de Caja como el Subjefe de Caja pueden anular arqueos ordinarios pendientes o certificados de sus agencias autorizadas. Toda anulación requiere motivo, firma electrónica y registro de auditoría. Los arqueos anulados permanecen disponibles para consulta.</div>
    </div>

    <section class="aa-panel" aria-labelledby="aa-list-title">
        <div class="aa-panel-header">
            <div>
                <h2 id="aa-list-title">Arqueos ordinarios</h2>
                <p>Listado de registros disponibles para consulta y revisión.</p>
            </div>
            <span class="aa-counter">{{ $arqueos->total() }} {{ $arqueos->total() === 1 ? 'registro' : 'registros' }}</span>
        </div>

        <div class="aa-filters">
            <div class="aa-filter">
                <label for="aa-status">Filtrar por estado (página actual)</label>
                <select id="aa-status">
                    <option value="">Todos los estados</option>
                    <option value="PENDIENTE">Pendientes</option>
                    <option value="CERTIFICADO">Certificados</option>
                    <option value="ANULADO">Anulados</option>
                </select>
            </div>
            <div class="aa-filter">
                <label for="aa-search">Buscar en esta página</label>
                <input type="search" id="aa-search" placeholder="Número, agencia o receptor" autocomplete="off">
            </div>
        </div>

        <div class="aa-table-wrap">
            <table class="aa-table">
                <thead>
                    <tr>
                        <th>Número de arqueo</th>
                        <th>Fecha operativa</th>
                        <th>Agencia</th>
                        <th>Receptor</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="aa-table-body">
                    @forelse($arqueos as $arqueo)
                        <tr data-status="{{ $arqueo->estado }}" data-search="{{ \Illuminate\Support\Str::lower(($arqueo->numero_arqueo ?? '') . ' ' . ($arqueo->agencia_nombre_historico ?? '') . ' ' . ($arqueo->receptor_nombre_historico ?? '')) }}">
                            <td><span class="aa-strong">{{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}</span></td>
                            <td>{{ $arqueo->fecha_operativa?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                            <td>{{ $arqueo->agencia_nombre_historico ?: 'Agencia #'.$arqueo->agencia_id }}</td>
                            <td>{{ $arqueo->receptor_nombre_historico ?: 'No disponible' }}</td>
                            <td>
                                <span class="aa-state {{ $arqueo->estado === 'PENDIENTE' ? 'pending' : ($arqueo->estado === 'CERTIFICADO' ? 'certified' : 'void') }}">
                                    {{ $arqueo->estado }}
                                </span>
                            </td>
                            <td><a class="aa-detail" href="{{ route($ruta . '.anulaciones.show', $arqueo->id) }}">Ver detalle</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="aa-empty">No hay arqueos ordinarios disponibles para consultar en sus agencias asignadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="aa-filter-empty" class="aa-empty" hidden>No hay coincidencias con los filtros de esta página.</div>
        @if($arqueos->hasPages())
            <div class="aa-footer">{{ $arqueos->links() }}</div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const status = document.getElementById('aa-status');
        const search = document.getElementById('aa-search');
        const rows = Array.from(document.querySelectorAll('#aa-table-body tr[data-status]'));
        const empty = document.getElementById('aa-filter-empty');
        if (!status || !search || !empty || !rows.length) return;

        function filterRows() {
            const selected = status.value;
            const term = search.value.trim().toLocaleLowerCase();
            let visible = 0;
            rows.forEach(function (row) {
                const matches = (!selected || row.dataset.status === selected)
                    && (!term || (row.dataset.search || '').includes(term));
                row.hidden = !matches;
                if (matches) visible++;
            });
            empty.hidden = visible > 0;
        }
        status.addEventListener('change', filterRows);
        search.addEventListener('input', filterRows);
    });
</script>
@endpush
