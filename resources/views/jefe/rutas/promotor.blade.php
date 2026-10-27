@extends('layouts.jefe')

@section('title', 'Administrar Promotores de Ruta')
@section('module-title', 'Administrar Promotores de Ruta')

@push('styles')
<style>
    .promotor-page {
        display: grid;
        gap: 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
    }

    .page-header h2 {
        margin: 0;
        color: #06284f;
        font-size: 28px;
    }

    .page-header p {
        margin: 7px 0 0;
        color: #64748b;
    }

    .btn {
        border: 0;
        border-radius: 8px;
        padding: 10px 16px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        font-size: 14px;
    }

    .btn-primary {
        background: #0b4f8a;
        color: #fff;
    }

    .btn-success {
        background: #15803d;
        color: #fff;
    }

    .btn-warning {
        background: #d97706;
        color: #fff;
    }

    .btn-danger {
        background: #b91c1c;
        color: #fff;
    }

    .btn-secondary {
        background: #e2e8f0;
        color: #334155;
    }

    .route-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .summary-box {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 17px 18px;
    }

    .summary-box span {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .summary-box strong {
        color: #0f172a;
        font-size: 17px;
    }

    .alert {
        padding: 14px 16px;
        border-radius: 10px;
        font-weight: 600;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }

    .panel-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .panel-header h3 {
        margin: 0;
        color: #0f2f53;
        font-size: 18px;
    }

    .panel-header p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .panel-body {
        padding: 20px;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        text-align: left;
        padding: 12px 14px;
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        border-bottom: 1px solid #e2e8f0;
    }

    td {
        padding: 14px;
        border-bottom: 1px solid #edf2f7;
        color: #334155;
        vertical-align: middle;
    }

    tr:last-child td {
        border-bottom: 0;
    }

    .promotor-name {
        font-weight: 800;
        color: #0f172a;
    }

    .promotor-user {
        display: block;
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge-permanent {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge-temporary {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-active {
        background: #dcfce7;
        color: #166534;
    }

    .badge-finished {
        background: #e2e8f0;
        color: #475569;
    }

    .operations {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .operation-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }

    .operation-card-header {
        padding: 17px 18px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .operation-card-header h3 {
        margin: 0;
        color: #0f2f53;
        font-size: 17px;
    }

    .operation-card-header p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.45;
    }

    .operation-card-body {
        padding: 18px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
    }

    .form-control {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 11px;
        background: #fff;
        color: #0f172a;
        font: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: #0b4f8a;
        box-shadow: 0 0 0 3px rgba(11, 79, 138, .1);
    }

    .form-control[readonly] {
        background: #f8fafc;
        color: #475569;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 90px;
    }

    .empty-state {
        padding: 30px;
        text-align: center;
        color: #64748b;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .small-btn {
        padding: 7px 11px;
        font-size: 12px;
    }

    .motivo-text {
        max-width: 300px;
        white-space: normal;
        line-height: 1.4;
    }

    /* Modal */
    .app-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .app-modal.show {
        display: flex;
    }

    .modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .62);
    }

    .modal-dialog {
        position: relative;
        width: min(520px, 100%);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 25px 60px rgba(15, 23, 42, .3);
        overflow: hidden;
        z-index: 1;
    }

    .modal-header {
        padding: 20px 22px 14px;
    }

    .modal-header h3 {
        margin: 0;
        color: #0f2f53;
    }

    .modal-body {
        padding: 0 22px 20px;
        color: #475569;
        line-height: 1.6;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 15px 22px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    @media (max-width: 1100px) {
        .operations {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .page-header {
            flex-direction: column;
        }

        .route-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="promotor-page">

    <div class="page-header">
        <div>
            <h2>Administrar Promotores de Ruta</h2>
            <p>
                Gestione asignaciones permanentes, reasignaciones y coberturas
                temporales de promotores.
            </p>
        </div>

        <a
            href="{{ route('jefe.rutas.index') }}"
            class="btn btn-secondary"
        >
            Regresar a rutas
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Revise la información ingresada:</strong>
            <ul style="margin: 8px 0 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="route-summary">
        <div class="summary-box">
            <span>Código</span>
            <strong>{{ $rutaActual->codigo }}</strong>
        </div>

        <div class="summary-box">
            <span>Ruta actual</span>
            <strong>{{ $rutaActual->nombre }}</strong>
        </div>

        <div class="summary-box">
            <span>Región</span>
            <strong>{{ $rutaActual->region_nombre }}</strong>
        </div>
    </div>

    <section class="panel">
        <div class="panel-header">
            <h3>Promotores actualmente asignados</h3>
            <p>
                Se muestran las asignaciones vigentes para la fecha actual.
            </p>
        </div>

        @if ($promotoresAsignados->isEmpty())
            <div class="empty-state">
                Esta ruta no tiene promotores asignados actualmente.
            </div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Promotor</th>
                            <th>Tipo</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Motivo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($promotoresAsignados as $asignacion)
                            @php
                                $nombrePromotor = trim(
                                    ($asignacion->nombres ?? '')
                                    . ' '
                                    . ($asignacion->apellidos ?? '')
                                );

                                $nombrePromotor = $nombrePromotor !== ''
                                    ? $nombrePromotor
                                    : $asignacion->usuario;
                            @endphp

                            <tr>
                                <td>
                                    <span class="promotor-name">
                                        {{ $nombrePromotor }}
                                    </span>

                                    <span class="promotor-user">
                                        {{ $asignacion->usuario }}
                                    </span>
                                </td>

                                <td>
                                    @if ($asignacion->tipo_asignacion === 'TEMPORAL')
                                        <span class="badge badge-temporary">
                                            Temporal
                                        </span>
                                    @else
                                        <span class="badge badge-permanent">
                                            Permanente
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($asignacion->fecha_inicio)->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $asignacion->fecha_fin
                                        ? \Carbon\Carbon::parse($asignacion->fecha_fin)->format('d/m/Y')
                                        : 'Sin fecha fin'
                                    }}
                                </td>

                                <td class="motivo-text">
                                    {{ $asignacion->motivo ?: '—' }}
                                </td>

                                <td>
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'jefe.rutas.promotor.finalizar',
                                            [
                                                $rutaActual->id,
                                                $asignacion->asignacion_id
                                            ]
                                        ) }}"
                                        class="confirmation-form"
                                        data-title="Finalizar asignación"
                                        data-message="¿Confirma que desea finalizar la asignación de {{ $nombrePromotor }} en esta ruta?"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-danger small-btn"
                                        >
                                            Finalizar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="operations">

        {{-- ASIGNACIÓN PERMANENTE --}}
        <section class="operation-card">
            <div class="operation-card-header">
                <h3>Asignación</h3>
                <p>
                    Agrega un Promotor a esta ruta sin modificar sus demás
                    rutas asignadas.
                </p>
            </div>

            <div class="operation-card-body">
                <form
                    method="POST"
                    action="{{ route(
                        'jefe.rutas.promotor.asignar',
                        $rutaActual->id
                    ) }}"
                    class="confirmation-form"
                    data-title="Confirmar asignación"
                    data-message="¿Confirma que desea asignar permanentemente este Promotor a la ruta?"
                >
                    @csrf

                    <div class="form-group">
                        <label for="promotor_asignar">
                            Promotor
                        </label>

                        <select
                            name="promotor_usuario_id"
                            id="promotor_asignar"
                            class="form-control"
                            required
                        >
                            <option value="">
                                Seleccione un Promotor
                            </option>

                            @foreach ($promotores as $promotor)
                                @php
                                    $nombre = trim(
                                        ($promotor->nombres ?? '')
                                        . ' '
                                        . ($promotor->apellidos ?? '')
                                    );

                                    $nombre = $nombre !== ''
                                        ? $nombre
                                        : $promotor->usuario;
                                @endphp

                                <option
                                    value="{{ $promotor->id }}"
                                    @selected(
                                        old('promotor_usuario_id')
                                        == $promotor->id
                                    )
                                >
                                    {{ $nombre }} — {{ $promotor->usuario }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ruta</label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $rutaActual->codigo }} — {{ $rutaActual->nombre }}"
                            readonly
                        >
                    </div>

                    <div class="form-actions">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Asignar Promotor
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- REASIGNACIÓN --}}
        <section class="operation-card">
            <div class="operation-card-header">
                <h3>Reasignación</h3>
                <p>
                    Mueve únicamente una asignación permanente de esta ruta
                    hacia otra ruta.
                </p>
            </div>

            <div class="operation-card-body">
                <form
                    method="POST"
                    action="{{ route(
                        'jefe.rutas.promotor.reasignar',
                        $rutaActual->id
                    ) }}"
                    class="confirmation-form"
                    data-title="Confirmar reasignación"
                    data-message="¿Confirma que desea mover esta asignación permanente hacia la ruta seleccionada?"
                >
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label for="asignacion_id">
                            Promotor asignado
                        </label>

                        <select
                            name="asignacion_id"
                            id="asignacion_id"
                            class="form-control"
                            required
                        >
                            <option value="">
                                Seleccione una asignación
                            </option>

                            @foreach (
                                $promotoresAsignados
                                    ->where('tipo_asignacion', 'PERMANENTE')
                                as $asignacion
                            )
                                @php
                                    $nombre = trim(
                                        ($asignacion->nombres ?? '')
                                        . ' '
                                        . ($asignacion->apellidos ?? '')
                                    );

                                    $nombre = $nombre !== ''
                                        ? $nombre
                                        : $asignacion->usuario;
                                @endphp

                                <option
                                    value="{{ $asignacion->asignacion_id }}"
                                >
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ruta de origen</label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $rutaActual->codigo }} — {{ $rutaActual->nombre }}"
                            readonly
                        >
                    </div>

                    <div class="form-group">
                        <label for="ruta_destino_id">
                            Ruta de destino
                        </label>

                        <select
                            name="ruta_destino_id"
                            id="ruta_destino_id"
                            class="form-control"
                            required
                        >
                            <option value="">
                                Seleccione una ruta
                            </option>

                            @foreach ($rutasDestino as $rutaDestino)
                                <option
                                    value="{{ $rutaDestino->id }}"
                                >
                                    {{ $rutaDestino->codigo }}
                                    — {{ $rutaDestino->nombre }}
                                    — {{ $rutaDestino->region_nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-actions">
                        <button
                            type="submit"
                            class="btn btn-warning"
                        >
                            Reasignar Promotor
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- ASIGNACIÓN TEMPORAL --}}
        <section class="operation-card">
            <div class="operation-card-header">
                <h3>Asignación temporal</h3>
                <p>
                    Habilita temporalmente a otro Promotor para cubrir esta
                    ruta sin modificar las asignaciones permanentes.
                </p>
            </div>

            <div class="operation-card-body">
                <form
                    method="POST"
                    action="{{ route(
                        'jefe.rutas.promotor.temporal',
                        $rutaActual->id
                    ) }}"
                    class="confirmation-form"
                    data-title="Confirmar asignación temporal"
                    data-message="¿Confirma que desea crear esta cobertura temporal?"
                >
                    @csrf

                    <div class="form-group">
                        <label for="promotor_temporal">
                            Promotor
                        </label>

                        <select
                            name="promotor_usuario_id"
                            id="promotor_temporal"
                            class="form-control"
                            required
                        >
                            <option value="">
                                Seleccione un Promotor
                            </option>

                            @foreach ($promotores as $promotor)
                                @php
                                    $nombre = trim(
                                        ($promotor->nombres ?? '')
                                        . ' '
                                        . ($promotor->apellidos ?? '')
                                    );

                                    $nombre = $nombre !== ''
                                        ? $nombre
                                        : $promotor->usuario;
                                @endphp

                                <option value="{{ $promotor->id }}">
                                    {{ $nombre }} — {{ $promotor->usuario }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ruta</label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $rutaActual->codigo }} — {{ $rutaActual->nombre }}"
                            readonly
                        >
                    </div>

                    <div class="form-group">
                        <label for="fecha_inicio">
                            Fecha de inicio
                        </label>

                        <input
                            type="date"
                            name="fecha_inicio"
                            id="fecha_inicio"
                            class="form-control"
                            value="{{ old('fecha_inicio', today()->toDateString()) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="fecha_fin">
                            Fecha de finalización
                        </label>

                        <input
                            type="date"
                            name="fecha_fin"
                            id="fecha_fin"
                            class="form-control"
                            value="{{ old('fecha_fin', today()->toDateString()) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="motivo">
                            Motivo de la cobertura
                        </label>

                        <textarea
                            name="motivo"
                            id="motivo"
                            class="form-control"
                            maxlength="500"
                            required
                            placeholder="Indique el motivo de la asignación temporal."
                        >{{ old('motivo') }}</textarea>
                    </div>

                    <div class="form-actions">
                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Asignar temporalmente
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </div>

    {{-- HISTORIAL --}}
    <section class="panel">
        <div class="panel-header">
            <h3>Historial de asignaciones</h3>
            <p>
                Últimos movimientos registrados para esta ruta.
            </p>
        </div>

        @if ($historial->isEmpty())
            <div class="empty-state">
                No existen asignaciones registradas para esta ruta.
            </div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Promotor</th>
                            <th>Tipo</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Estado</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($historial as $registro)
                            @php
                                $nombre = trim(
                                    ($registro->nombres ?? '')
                                    . ' '
                                    . ($registro->apellidos ?? '')
                                );

                                $nombre = $nombre !== ''
                                    ? $nombre
                                    : $registro->usuario;
                            @endphp

                            <tr>
                                <td>
                                    <span class="promotor-name">
                                        {{ $nombre }}
                                    </span>

                                    <span class="promotor-user">
                                        {{ $registro->usuario }}
                                    </span>
                                </td>

                                <td>
                                    @if ($registro->tipo_asignacion === 'TEMPORAL')
                                        <span class="badge badge-temporary">
                                            Temporal
                                        </span>
                                    @else
                                        <span class="badge badge-permanent">
                                            Permanente
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($registro->fecha_inicio)->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $registro->fecha_fin
                                        ? \Carbon\Carbon::parse($registro->fecha_fin)->format('d/m/Y')
                                        : '—'
                                    }}
                                </td>

                                <td>
                                    @if ($registro->estado)
                                        <span class="badge badge-active">
                                            Activa
                                        </span>
                                    @else
                                        <span class="badge badge-finished">
                                            Finalizada
                                        </span>
                                    @endif
                                </td>

                                <td class="motivo-text">
                                    {{ $registro->motivo ?: '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

</div>

{{-- MODAL DE CONFIRMACIÓN --}}
<div
    class="app-modal"
    id="confirmation-modal"
    aria-hidden="true"
>
    <div
        class="modal-backdrop"
        data-close-modal
    ></div>

    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="confirmation-title">
                Confirmar operación
            </h3>
        </div>

        <div class="modal-body">
            <p id="confirmation-message">
                ¿Desea continuar con esta operación?
            </p>
        </div>

        <div class="modal-footer">
            <button
                type="button"
                class="btn btn-secondary"
                data-close-modal
            >
                Cancelar
            </button>

            <button
                type="button"
                class="btn btn-primary"
                id="confirmation-submit"
            >
                Confirmar
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('confirmation-modal');
    const title = document.getElementById('confirmation-title');
    const message = document.getElementById('confirmation-message');
    const confirmButton = document.getElementById('confirmation-submit');

    let pendingForm = null;

    document.querySelectorAll('.confirmation-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            pendingForm = form;

            title.textContent =
                form.dataset.title || 'Confirmar operación';

            message.textContent =
                form.dataset.message
                || '¿Desea continuar con esta operación?';

            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            pendingForm = null;
        });
    });

    confirmButton.addEventListener('click', function () {
        if (! pendingForm) {
            return;
        }

        const form = pendingForm;
        pendingForm = null;

        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');

        form.submit();
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape'
            && modal.classList.contains('show')
        ) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            pendingForm = null;
        }
    });

    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');

    if (fechaInicio && fechaFin) {
        fechaInicio.addEventListener('change', function () {
            fechaFin.min = fechaInicio.value;

            if (
                fechaFin.value
                && fechaFin.value < fechaInicio.value
            ) {
                fechaFin.value = fechaInicio.value;
            }
        });

        fechaFin.min = fechaInicio.value;
    }
});
</script>
@endpush
