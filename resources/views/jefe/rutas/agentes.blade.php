@extends('layouts.jefe')

@section('title', 'Administrar Agentes de Ruta')
@section('module-title', 'Gestión de Rutas')

@push('styles')
<style>
.management-card,.table-card{border:1px solid #e0e8ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(20,57,83,.05)}
.management-card{padding:18px;margin-bottom:20px}
.route-info{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.info-box{padding:14px;border:1px solid #e3e9ee;border-radius:12px;background:#f9fbfc}
.info-box span{display:block;font-size:8px;font-weight:800;text-transform:uppercase;color:#748596}
.info-box strong{display:block;margin-top:6px;color:#082d55;font-size:14px}
.toolbar{display:flex;gap:10px;align-items:center;justify-content:space-between;margin-top:16px}
.search-form{display:flex;gap:9px;flex:1}
.form-control{width:100%;min-height:42px;padding:0 12px;border:1px solid #ced9e1;border-radius:10px;background:#fff;color:#30485b;box-sizing:border-box;outline:none}
.form-control:focus{border-color:#8eabc0;box-shadow:0 0 0 3px rgba(22,76,150,.08)}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 15px;border:0;border-radius:10px;font-size:10px;font-weight:800;text-decoration:none;cursor:pointer}
.btn-primary{background:#164c96;color:#fff}.btn-secondary{background:#edf2f5;color:#3d596f}.btn-success{background:#16834f;color:#fff}
.table-card{overflow:hidden}.table-header{padding:18px 20px;border-bottom:1px solid #edf1f4;background:#fafcfd}
.table-header h3{margin:0;color:#0a3158;font-size:15px}.table-header p{margin:5px 0 0;color:#82909a;font-size:10px}
.table-responsive{overflow-x:auto}.data-table{width:100%;min-width:900px;border-collapse:collapse}
.data-table th,.data-table td{padding:12px 13px;border-bottom:1px solid #edf1f4;text-align:left;font-size:10px}
.data-table th{background:#f7f9fb;color:#687b8b;font-size:8px;font-weight:800;text-transform:uppercase}
.badge{display:inline-flex;padding:6px 9px;border-radius:999px;font-size:8px;font-weight:800}.badge.active{background:#eaf8ef;color:#1d7b4e}.badge.inactive{background:#fdecec;color:#b13c3c}
.bulk-bar{display:flex;align-items:end;gap:10px;padding:16px 18px;border-top:1px solid #edf1f4;background:#fafcfd}.bulk-field{flex:1}
.bulk-field label{display:block;margin-bottom:6px;color:#617586;font-size:9px;font-weight:800;text-transform:uppercase}.pagination{padding:16px 18px}

/* Modales institucionales */
.system-modal-backdrop{position:fixed;inset:0;z-index:2500;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(10,24,37,.62);backdrop-filter:blur(3px)}
.system-modal-backdrop.is-open{display:flex}
.system-modal{width:min(500px,100%);overflow:hidden;border:1px solid #dce5eb;border-radius:18px;background:#fff;box-shadow:0 24px 70px rgba(0,0,0,.25)}
.system-modal-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px 17px;border-bottom:1px solid #e7edf1}
.system-modal-title{display:flex;align-items:flex-start;gap:12px}
.system-modal-icon{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;flex:0 0 40px;border-radius:11px;background:#edf5fb;color:#164c96}
.system-modal-icon.warning{background:#fff6df;color:#9a6c08}
.system-modal-icon svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.system-modal-header h3{margin:0;color:#0a3158;font-size:17px;font-weight:850}.system-modal-header p{margin:5px 0 0;color:#6c7f90;font-size:10px;line-height:1.45}
.system-modal-close{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border:0;border-radius:9px;background:#eef3f7;color:#415d72;font-size:21px;cursor:pointer}
.system-modal-body{padding:20px 22px}
.modal-summary{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.modal-summary-box{padding:12px 14px;border:1px solid #dce6ed;border-radius:11px;background:#f8fafb}
.modal-summary-box span{display:block;color:#7a8b98;font-size:8px;font-weight:850;text-transform:uppercase}
.modal-summary-box strong{display:block;margin-top:4px;color:#173b59;font-size:11px;overflow-wrap:anywhere}
.modal-warning{display:flex;gap:10px;margin-top:14px;padding:12px 14px;border:1px solid #ead9ad;border-radius:11px;background:#fffaf0;color:#755a18;font-size:10px;line-height:1.5}
.system-modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:16px 22px 20px;border-top:1px solid #e7edf1;background:#fbfcfd}
.modal-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 15px;border:1px solid #d5dfe5;border-radius:10px;background:#fff;color:#3d596f;font-size:10px;font-weight:850;cursor:pointer}
.modal-btn.primary{border-color:#164c96;background:#164c96;color:#fff}.modal-btn.primary:hover{background:#103d7c}
body.modal-open{overflow:hidden}

@media(max-width:760px){
.route-info{grid-template-columns:1fr}.toolbar,.search-form,.bulk-bar{flex-direction:column;align-items:stretch}
.modal-summary{grid-template-columns:1fr}.system-modal-footer{flex-direction:column-reverse}.modal-btn{width:100%}
}
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h2>Administrar Agentes de Ruta</h2>
        <p>Reasigne agentes a otra ruta activa. El historial de arqueos no se modifica.</p>
    </div>
    <a href="{{ route('jefe.rutas.index') }}" class="btn btn-secondary">Regresar a rutas</a>
</div>

<section class="management-card">
    <div class="route-info">
        <div class="info-box"><span>Código</span><strong>{{ $rutaActual->codigo }}</strong></div>
        <div class="info-box"><span>Ruta actual</span><strong>{{ $rutaActual->nombre }}</strong></div>
        <div class="info-box"><span>Región</span><strong>{{ $rutaActual->region_nombre }}</strong></div>
    </div>

    <div class="toolbar">
        <form method="GET" action="{{ route('jefe.rutas.agentes', $rutaActual->id) }}" class="search-form">
            <input class="form-control" type="text" name="buscar" value="{{ $buscar }}" placeholder="Código, negocio, propietario o usuario">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a class="btn btn-secondary" href="{{ route('jefe.rutas.agentes', $rutaActual->id) }}">Limpiar</a>
        </form>
    </div>
</section>

<form method="POST" action="{{ route('jefe.rutas.agentes.reasignar', $rutaActual->id) }}" id="reasignacion-form">
    @csrf
    @method('PATCH')

    <section class="table-card">
        <header class="table-header">
            <h3>Agentes asignados</h3>
            <p>Seleccione los agentes que desea trasladar y luego indique la nueva ruta.</p>
        </header>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="seleccionarTodos"></th>
                        <th>Código</th><th>Negocio</th><th>Propietario</th><th>Usuario</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($agentes as $agente)
                    <tr>
                        <td><input type="checkbox" name="agentes[]" value="{{ $agente->id }}" class="agente-check"></td>
                        <td><strong>{{ $agente->codigo_agente }}</strong></td>
                        <td>{{ $agente->nombre_negocio }}</td>
                        <td>{{ $agente->nombre_propietario }}</td>
                        <td>{{ $agente->usuario ?? '—' }}</td>
                        <td><span class="badge {{ $agente->estado === 'ACTIVO' ? 'active' : 'inactive' }}">{{ $agente->estado }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="padding:35px;text-align:center;color:#7d8b95">No hay agentes asignados a esta ruta.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($agentes->hasPages())<div class="pagination">{{ $agentes->links() }}</div>@endif

        <div class="bulk-bar">
            <div class="bulk-field">
                <label for="ruta_destino_id">Mover seleccionados a</label>
                <select name="ruta_destino_id" id="ruta_destino_id" class="form-control" required>
                    <option value="">Seleccione la ruta de destino</option>
                    @foreach($rutasDestino as $destino)
                        <option value="{{ $destino->id }}"
                                data-ruta="{{ $destino->codigo }} / {{ $destino->nombre }}"
                                data-region="{{ $destino->region_nombre }}">
                            {{ $destino->region_nombre }} — {{ $destino->codigo }} / {{ $destino->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-success" type="submit">Reasignar agentes</button>
        </div>
    </section>
</form>

<div class="system-modal-backdrop" id="selection-modal" aria-hidden="true">
    <div class="system-modal" role="dialog" aria-modal="true">
        <header class="system-modal-header">
            <div class="system-modal-title">
                <div class="system-modal-icon warning">
                    <svg viewBox="0 0 24 24"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.7 2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"/></svg>
                </div>
                <div><h3>Seleccione agentes</h3><p>No se ha seleccionado ningún agente para trasladar.</p></div>
            </div>
            <button type="button" class="system-modal-close" data-close="selection-modal">×</button>
        </header>
        <div class="system-modal-body">
            <div class="modal-warning">Seleccione al menos un agente de la tabla antes de continuar con la reasignación.</div>
        </div>
        <footer class="system-modal-footer">
            <button type="button" class="modal-btn primary" data-close="selection-modal">Entendido</button>
        </footer>
    </div>
</div>

<div class="system-modal-backdrop" id="confirmation-modal" aria-hidden="true">
    <div class="system-modal" role="dialog" aria-modal="true">
        <header class="system-modal-header">
            <div class="system-modal-title">
                <div class="system-modal-icon">
                    <svg viewBox="0 0 24 24"><path d="M8 7h11"/><path d="m15 3 4 4-4 4"/><path d="M16 17H5"/><path d="m9 13-4 4 4 4"/></svg>
                </div>
                <div><h3>Confirmar reasignación</h3><p>Revise la operación antes de trasladar los agentes seleccionados.</p></div>
            </div>
            <button type="button" class="system-modal-close" data-close="confirmation-modal">×</button>
        </header>

        <div class="system-modal-body">
            <div class="modal-summary">
                <div class="modal-summary-box"><span>Agentes seleccionados</span><strong id="modal-total">0</strong></div>
                <div class="modal-summary-box"><span>Ruta de origen</span><strong>{{ $rutaActual->codigo }} / {{ $rutaActual->nombre }}</strong></div>
                <div class="modal-summary-box"><span>Ruta de destino</span><strong id="modal-ruta">—</strong></div>
                <div class="modal-summary-box"><span>Región de destino</span><strong id="modal-region">—</strong></div>
            </div>
            <div class="modal-warning">Esta operación actualizará la ruta operativa de los agentes seleccionados. Los arqueos históricos conservarán sus datos originales.</div>
        </div>

        <footer class="system-modal-footer">
            <button type="button" class="modal-btn" data-close="confirmation-modal">Cancelar</button>
            <button type="button" class="modal-btn primary" id="confirmar-reasignacion">Confirmar reasignación</button>
        </footer>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('reasignacion-form');
    const selectAll = document.getElementById('seleccionarTodos');
    const checks = Array.from(document.querySelectorAll('.agente-check'));
    const destino = document.getElementById('ruta_destino_id');
    const selectionModal = document.getElementById('selection-modal');
    const confirmationModal = document.getElementById('confirmation-modal');
    let envioConfirmado = false;

    function abrir(modal) {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }

    function cerrar(modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.system-modal-backdrop.is-open')) {
            document.body.classList.remove('modal-open');
        }
    }

    selectAll?.addEventListener('change', function () {
        checks.forEach(check => check.checked = this.checked);
    });

    checks.forEach(check => check.addEventListener('change', function () {
        const marcados = checks.filter(item => item.checked).length;
        if (selectAll) {
            selectAll.checked = checks.length > 0 && marcados === checks.length;
            selectAll.indeterminate = marcados > 0 && marcados < checks.length;
        }
    }));

    form.addEventListener('submit', function (event) {
        if (envioConfirmado) return;

        event.preventDefault();

        const seleccionados = checks.filter(check => check.checked);

        if (seleccionados.length === 0) {
            abrir(selectionModal);
            return;
        }

        if (!destino.value) {
            destino.focus();
            return;
        }

        const opcion = destino.options[destino.selectedIndex];

        document.getElementById('modal-total').textContent = seleccionados.length;
        document.getElementById('modal-ruta').textContent = opcion.dataset.ruta || opcion.textContent.trim();
        document.getElementById('modal-region').textContent = opcion.dataset.region || '—';

        abrir(confirmationModal);
    });

    document.getElementById('confirmar-reasignacion').addEventListener('click', function () {
        envioConfirmado = true;
        cerrar(confirmationModal);
        form.requestSubmit();
    });

    document.querySelectorAll('[data-close]').forEach(button => {
        button.addEventListener('click', function () {
            const modal = document.getElementById(this.dataset.close);
            if (modal) cerrar(modal);
        });
    });

    document.querySelectorAll('.system-modal-backdrop').forEach(modal => {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) cerrar(modal);
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.system-modal-backdrop.is-open').forEach(cerrar);
        }
    });
});
</script>
@endpush
