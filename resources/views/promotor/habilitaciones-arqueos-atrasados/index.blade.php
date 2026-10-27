@extends('layouts.promotor')

@section('title', 'Arqueos Fuera de Tiempo')
@section('module-title', 'Arqueos Fuera de Tiempo')

@section('content')
<style>
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .page-title h2 {
        margin: 0;
        color: #123d69;
        font-size: 28px;
        font-weight: 800;
    }

    .page-title p {
        margin: 7px 0 0;
        color: #6b7c8d;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 390px minmax(0, 1fr);
        gap: 20px;
    }

    .card {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
    }

    .card-header {
        padding: 16px 18px;
        border-bottom: 1px solid #e7edf1;
        background: #f8fafb;
    }

    .card-header h3 {
        margin: 0;
        color: #173f66;
        font-size: 16px;
        font-weight: 800;
    }

    .card-body {
        padding: 18px;
    }

    .form-group + .form-group {
        margin-top: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 7px;
        color: #65788a;
        font-size: 12px;
        font-weight: 800;
    }

    .form-control {
        width: 100%;
        min-height: 44px;
        padding: 0 13px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #ffffff;
        color: #2f4659;
        outline: none;
    }

    textarea.form-control {
        min-height: 115px;
        padding-top: 11px;
        resize: vertical;
    }

    .form-control:focus {
        border-color: #2b72b8;
        box-shadow: 0 0 0 3px rgba(43, 114, 184, .12);
    }

    .field-error {
        display: block;
        margin-top: 6px;
        color: #a93b35;
        font-size: 12px;
        font-weight: 700;
    }

    .form-help {
        display: block;
        margin-top: 6px;
        color: #7a8b99;
        font-size: 11px;
        line-height: 1.45;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 17px;
        border: 0;
        border-radius: 10px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary {
        width: 100%;
        margin-top: 18px;
        background: #174f8a;
        color: #ffffff;
    }

    .btn-secondary {
        background: #eef3f7;
        color: #38556d;
    }

    .btn-danger {
        min-height: 35px;
        background: #fff0ef;
        color: #9a302a;
    }

    .filters {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) 190px auto auto;
        gap: 10px;
        padding: 16px 18px;
        border-bottom: 1px solid #e7edf1;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
    }

    .history-table th,
    .history-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
    }

    .history-table th {
        color: #627588;
        background: #f7f9fb;
        font-size: 11px;
        text-transform: uppercase;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        min-height: 25px;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge-pending {
        background: #fff3d5;
        color: #8a6100;
    }

    .badge-used {
        background: #e8f7ee;
        color: #247048;
    }

    .badge-cancelled {
        background: #ffe8e6;
        color: #a93b35;
    }

    .reason {
        max-width: 310px;
        color: #50667a;
        line-height: 1.45;
    }

    .empty-state {
        padding: 42px;
        color: #758697;
        text-align: center;
    }

    .pagination-wrapper {
        padding: 16px;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(10, 24, 37, .65);
        backdrop-filter: blur(3px);
    }

    .modal-backdrop.is-open {
        display: flex;
    }

    .modal-dialog {
        width: min(500px, 100%);
        overflow: hidden;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
    }

    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px 16px;
        border-bottom: 1px solid #e7edf1;
    }

    .modal-header h3 {
        margin: 0;
        color: #8f2f2a;
        font-size: 19px;
        font-weight: 800;
    }

    .modal-header p {
        margin: 6px 0 0;
        color: #6c7f90;
        font-size: 11px;
        line-height: 1.45;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 10px;
        background: #eef3f7;
        color: #415d72;
        font-size: 22px;
        cursor: pointer;
    }

    .modal-body {
        padding: 24px 22px;
        text-align: center;
    }

    .modal-icon {
        display: grid;
        place-items: center;
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 18px;
        background: #fff0ef;
        color: #9a302a;
    }

    .modal-icon svg {
        width: 30px;
        height: 30px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .modal-body h4 {
        margin: 0;
        color: #173f66;
        font-size: 17px;
        font-weight: 800;
    }

    .modal-body p {
        max-width: 390px;
        margin: 9px auto 0;
        color: #6c7f90;
        font-size: 11px;
        line-height: 1.6;
    }

    .modal-summary {
        margin-top: 17px;
        padding: 13px 14px;
        border: 1px solid #ead7d4;
        border-radius: 11px;
        background: #fff8f7;
        text-align: left;
    }

    .modal-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        padding: 6px 0;
        color: #765a57;
        font-size: 10px;
    }

    .modal-summary-row strong {
        color: #7f302b;
        text-align: right;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 22px 20px;
        border-top: 1px solid #e7edf1;
        background: #fbfcfd;
    }

    .btn-danger-solid {
        background: #b33a34;
        color: #ffffff;
    }

    .btn-danger-solid:hover {
        background: #982f2a;
    }

    body.modal-open {
        overflow: hidden;
    }

    @media (max-width: 1050px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .filters {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div class="page-title">
        <h2>Arqueos Fuera de Tiempo</h2>

        <p>
            Autorice a un agente de sus rutas asignadas para realizar
            el arqueo correspondiente a una fecha anterior.
        </p>
    </div>
</div>

<div class="content-grid">
    <section class="card">
        <div class="card-header">
            <h3>Nueva habilitación</h3>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route(
                    'promotor.habilitaciones-atrasadas.store'
                ) }}"
            >
                @csrf

                <div class="form-group">
                    <label for="agente_id">
                        Agente
                    </label>

                    <select
                        id="agente_id"
                        name="agente_id"
                        class="form-control"
                        required
                    >
                        <option value="">
                            Seleccione un agente
                        </option>

                        @foreach ($agentesAsignados as $agente)
                            <option
                                value="{{ $agente->id }}"
                                @selected(
                                    old('agente_id') == $agente->id
                                )
                            >
                                {{ $agente->codigo_agente }}
                                — {{ $agente->nombre_negocio }}
                                — {{ $agente->ruta_nombre }}
                            </option>
                        @endforeach
                    </select>

                    @error('agente_id')
                        <span class="field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="fecha_autorizada">
                        Fecha que se habilitará
                    </label>

                    <input
                        type="date"
                        id="fecha_autorizada"
                        name="fecha_autorizada"
                        class="form-control"
                        value="{{ old('fecha_autorizada') }}"
                        max="{{ today()->subDay()->format('Y-m-d') }}"
                        required
                    >

                    <span class="form-help">
                        Solo puede seleccionar una fecha anterior al día de hoy.
                    </span>

                    @error('fecha_autorizada')
                        <span class="field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="motivo">
                        Motivo de la habilitación
                    </label>

                    <textarea
                        id="motivo"
                        name="motivo"
                        class="form-control"
                        minlength="10"
                        maxlength="500"
                        required
                        placeholder="Explique por qué el agente debe realizar el arqueo fuera de tiempo."
                    >{{ old('motivo') }}</textarea>

                    @error('motivo')
                        <span class="field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Habilitar arqueo
                </button>
            </form>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h3>Historial de habilitaciones</h3>
        </div>

        <form
            method="GET"
            action="{{ route(
                'promotor.habilitaciones-atrasadas.index'
            ) }}"
            class="filters"
        >
            <input
                type="text"
                name="buscar"
                class="form-control"
                value="{{ $busqueda }}"
                placeholder="Buscar agente, ruta o región"
            >

            <select
                name="estado"
                class="form-control"
            >
                <option value="">Todos los estados</option>

                @foreach ([
                    'PENDIENTE' => 'Pendiente',
                    'UTILIZADA' => 'Utilizada',
                    'CANCELADA' => 'Cancelada',
                ] as $valor => $texto)
                    <option
                        value="{{ $valor }}"
                        @selected($estado === $valor)
                    >
                        {{ $texto }}
                    </option>
                @endforeach
            </select>

            <button
                type="submit"
                class="btn btn-primary"
                style="width: auto; margin-top: 0;"
            >
                Filtrar
            </button>

            @if ($busqueda !== '' || $estado !== '')
                <a
                    href="{{ route(
                        'promotor.habilitaciones-atrasadas.index'
                    ) }}"
                    class="btn btn-secondary"
                >
                    Limpiar
                </a>
            @endif
        </form>

        <div class="table-wrapper">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Agente</th>
                        <th>Fecha habilitada</th>
                        <th>Motivo</th>
                        <th>Autorizada</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($habilitaciones as $habilitacion)
                        @php
                            $badgeClase = match (
                                $habilitacion->estado
                            ) {
                                'PENDIENTE' => 'badge-pending',
                                'UTILIZADA' => 'badge-used',
                                'CANCELADA' => 'badge-cancelled',
                                default => 'badge-pending',
                            };
                        @endphp

                        <tr>
                            <td>
                                <strong>
                                    {{ $habilitacion->codigo_agente }}
                                </strong>
                                <br>
                                {{ $habilitacion->nombre_negocio }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse(
                                    $habilitacion->fecha_autorizada
                                )->format('d/m/Y') }}
                            </td>

                            <td class="reason">
                                {{ $habilitacion->motivo }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse(
                                    $habilitacion->autorizado_at
                                )->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                <span class="badge {{ $badgeClase }}">
                                    {{ $habilitacion->estado }}
                                </span>
                            </td>

                            <td>
                                @if (
                                    $habilitacion->estado === 'PENDIENTE'
                                )
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'promotor.habilitaciones-atrasadas.cancelar',
                                            $habilitacion->id
                                        ) }}"
                                        class="form-cancelar-habilitacion"
                                    >
                                        @csrf

                                        <button
                                            type="button"
                                            class="btn btn-danger"
                                            onclick="abrirModalCancelacion(this)"
                                            data-agente="{{ $habilitacion->codigo_agente }} — {{ $habilitacion->nombre_negocio }}"
                                            data-fecha="{{ \Carbon\Carbon::parse($habilitacion->fecha_autorizada)->format('d/m/Y') }}"
                                        >
                                            Cancelar
                                        </button>
                                    </form>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    No existen habilitaciones registradas.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($habilitaciones->hasPages())
            <div class="pagination-wrapper">
                {{ $habilitaciones->links() }}
            </div>
        @endif
    </section>
</div>

<div
    class="modal-backdrop"
    id="cancelar-habilitacion-modal"
    aria-hidden="true"
>
    <div
        class="modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cancelar-habilitacion-title"
    >
        <div class="modal-header">
            <div>
                <h3 id="cancelar-habilitacion-title">
                    Cancelar Habilitación
                </h3>

                <p>
                    Confirme la cancelación antes de continuar.
                </p>
            </div>

            <button
                type="button"
                class="modal-close"
                id="cerrar-cancelacion-modal"
                aria-label="Cerrar"
            >
                ×
            </button>
        </div>

        <div class="modal-body">
            <div class="modal-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M8 8l8 8"></path>
                    <path d="M16 8l-8 8"></path>
                </svg>
            </div>

            <h4>¿Cancelar esta habilitación?</h4>

            <p>
                El Agente dejará de tener autorización para realizar
                el arqueo correspondiente a la fecha habilitada.
            </p>

            <div class="modal-summary">
                <div class="modal-summary-row">
                    <span>Agente</span>
                    <strong id="cancelacion-agente">—</strong>
                </div>

                <div class="modal-summary-row">
                    <span>Fecha habilitada</span>
                    <strong id="cancelacion-fecha">—</strong>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button
                type="button"
                class="btn btn-secondary"
                id="volver-cancelacion-modal"
            >
                Volver
            </button>

            <button
                type="button"
                class="btn btn-danger-solid"
                id="confirmar-cancelacion"
            >
                Sí, Cancelar Habilitación
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById(
            'cancelar-habilitacion-modal'
        );

        const closeButton = document.getElementById(
            'cerrar-cancelacion-modal'
        );

        const backButton = document.getElementById(
            'volver-cancelacion-modal'
        );

        const confirmButton = document.getElementById(
            'confirmar-cancelacion'
        );

        const agentText = document.getElementById(
            'cancelacion-agente'
        );

        const dateText = document.getElementById(
            'cancelacion-fecha'
        );

        let currentForm = null;

        window.abrirModalCancelacion = function (button) {
            currentForm = button.closest(
                '.form-cancelar-habilitacion'
            );

            agentText.textContent =
                button.dataset.agente || '—';

            dateText.textContent =
                button.dataset.fecha || '—';

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        };

        const closeModal = function () {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            currentForm = null;
        };

        closeButton?.addEventListener(
            'click',
            closeModal
        );

        backButton?.addEventListener(
            'click',
            closeModal
        );

        confirmButton?.addEventListener(
            'click',
            function () {
                if (! currentForm) {
                    return;
                }

                this.disabled = true;
                this.textContent = 'Cancelando...';

                currentForm.submit();
            }
        );

        modal?.addEventListener(
            'click',
            function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            }
        );

        document.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Escape'
                    && modal.classList.contains('is-open')
                ) {
                    closeModal();
                }
            }
        );
    });
</script>
@endsection
