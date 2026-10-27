@extends('layouts.jefe')

@section('title', 'Administrar Rutas de Región')
@section('module-title', 'Gestión de Regiones')

@push('styles')
<style>
    .management-card,
    .table-card {
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .management-card { padding: 18px; margin-bottom: 20px; }

    .region-info {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 12px;
    }

    .info-box {
        padding: 14px;
        border: 1px solid #e3e9ee;
        border-radius: 12px;
        background: #f9fbfc;
    }

    .info-box span {
        display: block;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        color: #748596;
    }

    .info-box strong {
        display: block;
        margin-top: 6px;
        color: #082d55;
        font-size: 14px;
    }

    .toolbar { display: flex; gap: 10px; margin-top: 16px; }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #fff;
        color: #30485b;
        box-sizing: border-box;
        outline: none;
        transition: .2s ease;
    }

    .form-control:focus {
        border-color: #8eabc0;
        box-shadow: 0 0 0 3px rgba(22, 76, 150, .08);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 15px;
        border: 0;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary { background: #164c96; color: #fff; }
    .btn-secondary { background: #edf2f5; color: #3d596f; }
    .btn-success { background: #16834f; color: #fff; }

    .table-card { overflow: hidden; }

    .table-header {
        padding: 18px 20px;
        border-bottom: 1px solid #edf1f4;
        background: #fafcfd;
    }

    .table-header h3 { margin: 0; color: #0a3158; font-size: 15px; }
    .table-header p { margin: 5px 0 0; color: #82909a; font-size: 10px; }

    .table-responsive { overflow-x: auto; }

    .data-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        font-size: 10px;
    }

    .data-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge {
        display: inline-flex;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
    }

    .badge.active { background: #eaf8ef; color: #1d7b4e; }
    .badge.inactive { background: #fdecec; color: #b13c3c; }

    .bulk-bar {
        display: flex;
        align-items: end;
        gap: 10px;
        padding: 16px 18px;
        border-top: 1px solid #edf1f4;
        background: #fafcfd;
    }

    .bulk-field { flex: 1; }

    .bulk-field label {
        display: block;
        margin-bottom: 6px;
        color: #617586;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .pagination { padding: 16px 18px; }

    /* Modales institucionales */
    .system-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 2500;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(10, 24, 37, .62);
        backdrop-filter: blur(3px);
    }

    .system-modal-backdrop.is-open { display: flex; }

    .system-modal {
        width: min(510px, 100%);
        overflow: hidden;
        border: 1px solid #dce5eb;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(0,0,0,.25);
    }

    .system-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px 17px;
        border-bottom: 1px solid #e7edf1;
    }

    .system-modal-title {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .system-modal-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 11px;
        background: #edf5fb;
        color: #164c96;
    }

    .system-modal-icon.warning {
        background: #fff6df;
        color: #9a6c08;
    }

    .system-modal-icon svg {
        width: 19px;
        height: 19px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .system-modal-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 17px;
        font-weight: 850;
    }

    .system-modal-header p {
        margin: 5px 0 0;
        color: #6c7f90;
        font-size: 10px;
        line-height: 1.45;
    }

    .system-modal-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 9px;
        background: #eef3f7;
        color: #415d72;
        font-size: 21px;
        cursor: pointer;
    }

    .system-modal-body { padding: 20px 22px; }

    .modal-summary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .modal-summary-box {
        padding: 12px 14px;
        border: 1px solid #dce6ed;
        border-radius: 11px;
        background: #f8fafb;
    }

    .modal-summary-box span {
        display: block;
        color: #7a8b98;
        font-size: 8px;
        font-weight: 850;
        text-transform: uppercase;
    }

    .modal-summary-box strong {
        display: block;
        margin-top: 4px;
        color: #173b59;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .modal-warning {
        margin-top: 14px;
        padding: 12px 14px;
        border: 1px solid #ead9ad;
        border-radius: 11px;
        background: #fffaf0;
        color: #755a18;
        font-size: 10px;
        line-height: 1.5;
    }

    .system-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 22px 20px;
        border-top: 1px solid #e7edf1;
        background: #fbfcfd;
    }

    .modal-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 15px;
        border: 1px solid #d5dfe5;
        border-radius: 10px;
        background: #fff;
        color: #3d596f;
        font-size: 10px;
        font-weight: 850;
        cursor: pointer;
    }

    .modal-btn.primary {
        border-color: #164c96;
        background: #164c96;
        color: #fff;
    }

    .modal-btn.primary:hover { background: #103d7c; }

    body.modal-open { overflow: hidden; }

    @media (max-width: 760px) {
        .region-info { grid-template-columns: 1fr; }

        .toolbar,
        .bulk-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .modal-summary { grid-template-columns: 1fr; }

        .system-modal-footer {
            flex-direction: column-reverse;
        }

        .modal-btn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h2>Administrar Rutas de Región</h2>
        <p>Traslade rutas completas a otra región activa. Los agentes permanecen en sus rutas.</p>
    </div>

    <a href="{{ route('jefe.regiones.index') }}" class="btn btn-secondary">
        Regresar a regiones
    </a>
</div>

<section class="management-card">
    <div class="region-info">
        <div class="info-box">
            <span>Región actual</span>
            <strong>{{ $regionActual->nombre }}</strong>
        </div>

        <div class="info-box">
            <span>Estado</span>
            <strong>{{ $regionActual->estado ? 'ACTIVA' : 'INACTIVA' }}</strong>
        </div>
    </div>

    <form
        method="GET"
        action="{{ route('jefe.regiones.rutas', $regionActual->id) }}"
        class="toolbar"
    >
        <input
            class="form-control"
            type="text"
            name="buscar"
            value="{{ $buscar }}"
            placeholder="Código o nombre de ruta"
        >

        <button class="btn btn-primary" type="submit">Buscar</button>

        <a
            class="btn btn-secondary"
            href="{{ route('jefe.regiones.rutas', $regionActual->id) }}"
        >
            Limpiar
        </a>
    </form>
</section>

<form
    method="POST"
    action="{{ route('jefe.regiones.rutas.reasignar', $regionActual->id) }}"
    id="traslado-rutas-form"
>
    @csrf
    @method('PATCH')

    <section class="table-card">
        <header class="table-header">
            <h3>Rutas de {{ $regionActual->nombre }}</h3>
            <p>Seleccione una o varias rutas para moverlas a otra región.</p>
        </header>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="seleccionarTodas"></th>
                        <th>Código</th>
                        <th>Ruta</th>
                        <th>Agentes</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rutas as $ruta)
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    name="rutas[]"
                                    value="{{ $ruta->id }}"
                                    class="ruta-check"
                                    data-codigo="{{ $ruta->codigo }}"
                                    data-nombre="{{ $ruta->nombre }}"
                                    data-agentes="{{ $ruta->total_agentes }}"
                                >
                            </td>

                            <td><strong>{{ $ruta->codigo }}</strong></td>
                            <td>{{ $ruta->nombre }}</td>
                            <td>{{ $ruta->total_agentes }}</td>

                            <td>
                                <span class="badge {{ $ruta->estado ? 'active' : 'inactive' }}">
                                    {{ $ruta->estado ? 'ACTIVA' : 'INACTIVA' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                style="padding:35px;text-align:center;color:#7d8b95"
                            >
                                No hay rutas registradas en esta región.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rutas->hasPages())
            <div class="pagination">{{ $rutas->links() }}</div>
        @endif

        <div class="bulk-bar">
            <div class="bulk-field">
                <label for="region_destino_id">Mover seleccionadas a</label>

                <select
                    name="region_destino_id"
                    id="region_destino_id"
                    class="form-control"
                    required
                >
                    <option value="">Seleccione la región de destino</option>

                    @foreach($regionesDestino as $destino)
                        <option value="{{ $destino->id }}" data-region="{{ $destino->nombre }}">
                            {{ $destino->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-success" type="submit">
                Trasladar rutas
            </button>
        </div>
    </section>
</form>

{{-- Advertencia: no se seleccionaron rutas --}}
<div class="system-modal-backdrop" id="selection-modal" aria-hidden="true">
    <div class="system-modal" role="dialog" aria-modal="true">
        <header class="system-modal-header">
            <div class="system-modal-title">
                <div class="system-modal-icon warning">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                        <path d="M10.3 3.7 2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"></path>
                    </svg>
                </div>

                <div>
                    <h3>Seleccione rutas</h3>
                    <p>No se ha seleccionado ninguna ruta para trasladar.</p>
                </div>
            </div>

            <button
                type="button"
                class="system-modal-close"
                data-close="selection-modal"
                aria-label="Cerrar"
            >×</button>
        </header>

        <div class="system-modal-body">
            <div class="modal-warning">
                Seleccione al menos una ruta de la tabla antes de continuar con el traslado.
            </div>
        </div>

        <footer class="system-modal-footer">
            <button type="button" class="modal-btn primary" data-close="selection-modal">
                Entendido
            </button>
        </footer>
    </div>
</div>

{{-- Confirmación del traslado --}}
<div class="system-modal-backdrop" id="confirmation-modal" aria-hidden="true">
    <div class="system-modal" role="dialog" aria-modal="true">
        <header class="system-modal-header">
            <div class="system-modal-title">
                <div class="system-modal-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M8 7h11"></path>
                        <path d="m15 3 4 4-4 4"></path>
                        <path d="M16 17H5"></path>
                        <path d="m9 13-4 4 4 4"></path>
                    </svg>
                </div>

                <div>
                    <h3>Confirmar traslado de rutas</h3>
                    <p>Revise la operación antes de cambiar las rutas de región.</p>
                </div>
            </div>

            <button
                type="button"
                class="system-modal-close"
                data-close="confirmation-modal"
                aria-label="Cerrar"
            >×</button>
        </header>

        <div class="system-modal-body">
            <div class="modal-summary">
                <div class="modal-summary-box">
                    <span>Rutas seleccionadas</span>
                    <strong id="modal-total-rutas">0</strong>
                </div>

                <div class="modal-summary-box">
                    <span>Agentes involucrados</span>
                    <strong id="modal-total-agentes">0</strong>
                </div>

                <div class="modal-summary-box">
                    <span>Región de origen</span>
                    <strong>{{ $regionActual->nombre }}</strong>
                </div>

                <div class="modal-summary-box">
                    <span>Región de destino</span>
                    <strong id="modal-region-destino">—</strong>
                </div>
            </div>

            <div class="modal-warning">
                Las rutas seleccionadas serán trasladadas a la nueva región.
                Los agentes permanecerán asignados a sus rutas y pasarán
                operativamente a formar parte de la región de destino.
                Los arqueos históricos no serán modificados.
            </div>
        </div>

        <footer class="system-modal-footer">
            <button type="button" class="modal-btn" data-close="confirmation-modal">
                Cancelar
            </button>

            <button type="button" class="modal-btn primary" id="confirmar-traslado">
                Confirmar traslado
            </button>
        </footer>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('traslado-rutas-form');
    const selectAll = document.getElementById('seleccionarTodas');
    const checks = Array.from(document.querySelectorAll('.ruta-check'));
    const destination = document.getElementById('region_destino_id');

    const selectionModal = document.getElementById('selection-modal');
    const confirmationModal = document.getElementById('confirmation-modal');

    let envioConfirmado = false;

    function abrirModal(modal) {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }

    function cerrarModal(modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('.system-modal-backdrop.is-open')) {
            document.body.classList.remove('modal-open');
        }
    }

    selectAll?.addEventListener('change', function () {
        checks.forEach((check) => {
            check.checked = this.checked;
        });
    });

    checks.forEach((check) => {
        check.addEventListener('change', function () {
            if (!selectAll) return;

            const marcadas = checks.filter((item) => item.checked).length;

            selectAll.checked =
                checks.length > 0 && marcadas === checks.length;

            selectAll.indeterminate =
                marcadas > 0 && marcadas < checks.length;
        });
    });

    form.addEventListener('submit', function (event) {
        if (envioConfirmado) {
            return;
        }

        event.preventDefault();

        const seleccionadas = checks.filter((check) => check.checked);

        if (seleccionadas.length === 0) {
            abrirModal(selectionModal);
            return;
        }

        if (!destination.value) {
            destination.focus();
            return;
        }

        const selectedOption =
            destination.options[destination.selectedIndex];

        const totalAgentes = seleccionadas.reduce(
            (total, check) =>
                total + (parseInt(check.dataset.agentes || '0', 10) || 0),
            0
        );

        document.getElementById('modal-total-rutas').textContent =
            seleccionadas.length;

        document.getElementById('modal-total-agentes').textContent =
            totalAgentes;

        document.getElementById('modal-region-destino').textContent =
            selectedOption.dataset.region || selectedOption.textContent.trim();

        abrirModal(confirmationModal);
    });

    document.getElementById('confirmar-traslado')
        .addEventListener('click', function () {
            envioConfirmado = true;
            cerrarModal(confirmationModal);
            form.requestSubmit();
        });

    document.querySelectorAll('[data-close]').forEach((button) => {
        button.addEventListener('click', function () {
            const modal = document.getElementById(this.dataset.close);

            if (modal) {
                cerrarModal(modal);
            }
        });
    });

    document.querySelectorAll('.system-modal-backdrop').forEach((modal) => {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                cerrarModal(modal);
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        document
            .querySelectorAll('.system-modal-backdrop.is-open')
            .forEach(cerrarModal);
    });
});
</script>
@endpush
