@extends('layouts.jefe')

@section('title', 'Arqueos Fuera de Tiempo')
@section('module-title', 'Arqueos Fuera de Tiempo')

@push('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 22px;
    }

    .summary-card,
    .filters-card,
    .table-card {
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20,57,83,.05);
    }

    .summary-card {
        padding: 18px;
    }

    .summary-card span {
        display: block;
        color: #768692;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .summary-card strong {
        display: block;
        margin-top: 8px;
        color: #082d55;
        font-size: 25px;
    }

    .summary-card.warning {
        border-color: #efd99f;
        background: #fffdf6;
    }

    .summary-card.success {
        border-color: #c4e4d1;
        background: #f7fcf9;
    }

    .summary-card.danger {
        border-color: #efc5c5;
        background: #fffafa;
    }

    .page-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filters-card {
        padding: 18px;
        margin-bottom: 20px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns:
            minmax(230px, 1fr)
            250px
            180px
            160px
            160px;
        gap: 11px;
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #ffffff;
        color: #30485b;
        outline: none;
        box-sizing: border-box;
    }

    .filters-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 13px;
    }

    .btn,
    .action-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .btn {
        min-height: 40px;
        padding: 0 15px;
        border: 0;
        border-radius: 10px;
        font-size: 10px;
    }

    .btn-primary {
        background: #164c96;
        color: #ffffff;
    }

    .btn-secondary {
        background: #edf2f5;
        color: #3d596f;
    }

    .btn-success {
        background: #16834f;
        color: #ffffff;
    }

    .alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 800;
    }

    .alert-success {
        border: 1px solid #a9d8bc;
        background: #ebf8f0;
        color: #216b45;
    }

    .alert-warning {
        border: 1px solid #ead99b;
        background: #fff9e8;
        color: #856411;
    }

    .table-card {
        overflow: hidden;
    }

    .table-header {
        padding: 18px 20px;
        border-bottom: 1px solid #edf1f4;
        background: #fafcfd;
    }

    .table-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 15px;
    }

    .table-header p {
        margin: 5px 0 0;
        color: #82909a;
        font-size: 10px;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .habilitations-table {
        width: 100%;
        min-width: 1320px;
        border-collapse: collapse;
    }

    .habilitations-table th,
    .habilitations-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        font-size: 10px;
    }

    .habilitations-table th {
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
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge.pending {
        background: #fff5dc;
        color: #9c7014;
    }

    .badge.used {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .badge.cancelled {
        background: #fdecec;
        color: #b13c3c;
    }

    .action-link {
        min-height: 34px;
        padding: 0 10px;
        border: 1px solid #d5dfe5;
        border-radius: 9px;
        background: #ffffff;
        color: #31536e;
        font-size: 9px;
        white-space: nowrap;
    }

    .action-link.warning {
        border-color: #eed8a6;
        background: #fff9e9;
        color: #8a6511;
    }

    .pagination {
        padding: 16px 18px;
    }

    .modal-backdrop {
        position: fixed;
        z-index: 2200;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(5,29,52,.58);
        backdrop-filter: blur(3px);
    }

    .modal-backdrop.open {
        display: flex;
    }

    .modal-card {
        width: min(620px, 100%);
        overflow: hidden;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(0,0,0,.25);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        padding: 18px 20px;
        border-bottom: 1px solid #edf1f4;
    }

    .modal-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 17px;
    }

    .modal-header p {
        margin: 5px 0 0;
        color: #718493;
        font-size: 10px;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 10px;
        background: #eef3f6;
        color: #4b6578;
        font-size: 22px;
        cursor: pointer;
    }

    .modal-body {
        padding: 20px;
    }

    .form-grid {
        display: grid;
        gap: 14px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        color: #617586;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    textarea.form-control {
        min-height: 110px;
        padding-top: 11px;
        resize: vertical;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 20px 20px;
        border-top: 1px solid #edf1f4;
        background: #fbfcfd;
    }

    .confirm-modal-card {
        width: min(470px, 100%);
    }

    .confirm-modal-body {
        padding: 24px 22px;
        text-align: center;
    }

    .confirm-icon {
        display: grid;
        place-items: center;
        width: 62px;
        height: 62px;
        margin: 0 auto 15px;
        border-radius: 18px;
        background: #fff5dc;
        color: #9c7014;
    }

    .confirm-icon svg {
        width: 28px;
        height: 28px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .confirm-modal-body h4 {
        margin: 0;
        color: #0a3158;
        font-size: 16px;
    }

    .confirm-modal-body p {
        margin: 9px auto 0;
        max-width: 360px;
        color: #718493;
        font-size: 10px;
        line-height: 1.6;
    }

    .btn-warning-modal {
        background: #b87917;
        color: #ffffff;
    }

    .btn-warning-modal:hover {
        background: #9e6712;
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filters-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 700px) {
        .summary-grid,
        .filters-grid {
            grid-template-columns: 1fr;
        }

        .filters-actions,
        .page-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h2>Arqueos Fuera de Tiempo</h2>

        <p>
            Autorice a un Agente para realizar un arqueo diario
            correspondiente a una fecha anterior.
        </p>
    </div>

    <div class="page-actions">
        <button
            type="button"
            class="btn btn-success"
            onclick="abrirModalHabilitacion()"
        >
            Nueva Habilitación
        </button>
    </div>
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
    <div class="alert alert-warning">
        {{ $errors->first() }}
    </div>
@endif

<section class="summary-grid">
    <article class="summary-card">
        <span>Total</span>
        <strong>{{ $total }}</strong>
    </article>

    <article class="summary-card warning">
        <span>Pendientes</span>
        <strong>{{ $pendientes }}</strong>
    </article>

    <article class="summary-card success">
        <span>Utilizadas</span>
        <strong>{{ $utilizadas }}</strong>
    </article>

    <article class="summary-card danger">
        <span>Canceladas</span>
        <strong>{{ $canceladas }}</strong>
    </article>
</section>

<section class="filters-card">
    <form
        method="GET"
        action="{{ route(
            'jefe.habilitaciones-atrasadas.index'
        ) }}"
    >
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                class="form-control"
                value="{{ $buscar }}"
                placeholder="Agente, negocio, ruta, región o motivo"
            >

            <select
                name="agente_id"
                class="form-control"
            >
                <option value="">
                    Todos los Agentes
                </option>

                @foreach ($agentes as $agente)
                    <option
                        value="{{ $agente->id }}"
                        @selected(
                            $agenteId === (int) $agente->id
                        )
                    >
                        {{ $agente->codigo_agente }}
                        — {{ $agente->nombre_negocio }}
                    </option>
                @endforeach
            </select>

            <select
                name="estado"
                class="form-control"
            >
                <option value="">
                    Todos los estados
                </option>

                <option
                    value="PENDIENTE"
                    @selected($estado === 'PENDIENTE')
                >
                    Pendientes
                </option>

                <option
                    value="UTILIZADA"
                    @selected($estado === 'UTILIZADA')
                >
                    Utilizadas
                </option>

                <option
                    value="CANCELADA"
                    @selected($estado === 'CANCELADA')
                >
                    Canceladas
                </option>
            </select>

            <input
                type="date"
                name="desde"
                class="form-control"
                value="{{ $desde }}"
            >

            <input
                type="date"
                name="hasta"
                class="form-control"
                value="{{ $hasta }}"
            >
        </div>

        <div class="filters-actions">
            <a
                href="{{ route(
                    'jefe.habilitaciones-atrasadas.index'
                ) }}"
                class="btn btn-secondary"
            >
                Limpiar
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Aplicar filtros
            </button>
        </div>
    </form>
</section>

<section class="table-card">
    <header class="table-header">
        <h3>Historial de habilitaciones</h3>

        <p>
            Solo las habilitaciones en estado PENDIENTE
            pueden ser canceladas.
        </p>
    </header>

    <div class="table-responsive">
        <table class="habilitations-table">
            <thead>
                <tr>
                    <th>Agente</th>
                    <th>Región</th>
                    <th>Ruta</th>
                    <th>Fecha autorizada</th>
                    <th>Motivo</th>
                    <th>Autorizado por</th>
                    <th>Fecha autorización</th>
                    <th>Uso</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($habilitaciones as $habilitacion)
                    @php
                        $nombreAutorizador = trim(
                            ($habilitacion->autorizador_nombres ?? '')
                            . ' '
                            . ($habilitacion->autorizador_apellidos ?? '')
                        );

                        $estadoClase = match(
                            $habilitacion->estado
                        ) {
                            'UTILIZADA' => 'used',
                            'CANCELADA' => 'cancelled',
                            default => 'pending',
                        };
                    @endphp

                    <tr>
                        <td>
                            <strong>
                                {{ $habilitacion->codigo_agente }}
                                — {{ $habilitacion->nombre_negocio }}
                            </strong>
                        </td>

                        <td>
                            {{ $habilitacion->region_nombre }}
                        </td>

                        <td>
                            {{ $habilitacion->ruta_codigo }}
                            — {{ $habilitacion->ruta_nombre }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse(
                                $habilitacion->fecha_autorizada
                            )->format('d/m/Y') }}
                        </td>

                        <td>
                            {{ \Illuminate\Support\Str::limit(
                                $habilitacion->motivo,
                                70
                            ) }}
                        </td>

                        <td>
                            {{ $nombreAutorizador !== ''
                                ? $nombreAutorizador
                                : $habilitacion->autorizador_usuario }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse(
                                $habilitacion->autorizado_at
                            )->format('d/m/Y H:i') }}
                        </td>

                        <td>
                            @if (
                                $habilitacion->estado === 'UTILIZADA'
                                || (int) $habilitacion->arqueos_generados > 0
                            )
                                Utilizada
                            @else
                                Pendiente
                            @endif
                        </td>

                        <td>
                            <span class="badge {{ $estadoClase }}">
                                {{ $habilitacion->estado }}
                            </span>
                        </td>

                        <td>
                            @if (
                                $habilitacion->estado === 'PENDIENTE'
                                && (int) $habilitacion->arqueos_generados === 0
                            )
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'jefe.habilitaciones-atrasadas.cancelar',
                                        $habilitacion->id
                                    ) }}"
                                    class="form-cancelar-habilitacion"
                                >
                                    @csrf

                                    <button
                                        type="button"
                                        class="action-link warning"
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
                        <td
                            colspan="10"
                            style="padding:35px;text-align:center;"
                        >
                            No se encontraron habilitaciones.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($habilitaciones->hasPages())
        <div class="pagination">
            {{ $habilitaciones->links() }}
        </div>
    @endif
</section>

<div
    class="modal-backdrop"
    id="modalHabilitacion"
>
    <div class="modal-card">
        <form
            method="POST"
            action="{{ route(
                'jefe.habilitaciones-atrasadas.store'
            ) }}"
        >
            @csrf

            <header class="modal-header">
                <div>
                    <h3>Nueva Habilitación</h3>

                    <p>
                        Autorice a un Agente para realizar
                        un arqueo correspondiente a una fecha anterior.
                    </p>
                </div>

                <button
                    type="button"
                    class="modal-close"
                    onclick="cerrarModalHabilitacion()"
                >
                    ×
                </button>
            </header>

            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="agente_id">
                            Agente
                        </label>

                        <select
                            name="agente_id"
                            id="agente_id"
                            class="form-control"
                            required
                        >
                            <option value="">
                                Seleccione un Agente
                            </option>

                            @foreach ($agentes as $agente)
                                <option
                                    value="{{ $agente->id }}"
                                    @selected(
                                        old('agente_id')
                                        == $agente->id
                                    )
                                >
                                    {{ $agente->codigo_agente }}
                                    — {{ $agente->nombre_negocio }}
                                    | {{ $agente->ruta_nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha_autorizada">
                            Fecha que se habilitará
                        </label>

                        <input
                            type="date"
                            name="fecha_autorizada"
                            id="fecha_autorizada"
                            class="form-control"
                            max="{{ now()->subDay()->format('Y-m-d') }}"
                            value="{{ old('fecha_autorizada') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="motivo">
                            Motivo de la habilitación
                        </label>

                        <textarea
                            name="motivo"
                            id="motivo"
                            class="form-control"
                            minlength="10"
                            maxlength="500"
                            required
                            placeholder="Indique por qué se autoriza el arqueo fuera de tiempo..."
                        >{{ old('motivo') }}</textarea>
                    </div>
                </div>
            </div>

            <footer class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="cerrarModalHabilitacion()"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Habilitar Arqueo
                </button>
            </footer>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modalCancelacion" aria-hidden="true">
    <div class="modal-card confirm-modal-card" role="dialog" aria-modal="true" aria-labelledby="tituloModalCancelacion">
        <header class="modal-header">
            <div>
                <h3 id="tituloModalCancelacion">Cancelar Habilitación</h3>
                <p>Confirme la cancelación de esta autorización.</p>
            </div>

            <button type="button" class="modal-close" onclick="cerrarModalCancelacion()" aria-label="Cerrar">×</button>
        </header>

        <div class="confirm-modal-body">
            <div class="confirm-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3 2.8 19h18.4L12 3Z"></path>
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                </svg>
            </div>

            <h4>¿Cancelar esta habilitación?</h4>

            <p>
                Se cancelará la autorización para que
                <strong id="cancelacionAgente">este Agente</strong>
                realice el arqueo correspondiente al
                <strong id="cancelacionFecha">día autorizado</strong>.
                Esta acción dejará la habilitación en estado CANCELADA.
            </p>
        </div>

        <footer class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalCancelacion()">Volver</button>

            <button type="button" class="btn btn-warning-modal" id="confirmarCancelacionBtn">
                Sí, Cancelar Habilitación
            </button>
        </footer>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modalHabilitacion = document.getElementById('modalHabilitacion');
    const modalCancelacion = document.getElementById('modalCancelacion');
    const confirmarCancelacionBtn = document.getElementById('confirmarCancelacionBtn');

    let formularioCancelacionActual = null;

    function abrirModalHabilitacion() {
        modalHabilitacion.classList.add('open');
        modalHabilitacion.setAttribute('aria-hidden', 'false');
    }

    function cerrarModalHabilitacion() {
        modalHabilitacion.classList.remove('open');
        modalHabilitacion.setAttribute('aria-hidden', 'true');
    }

    function abrirModalCancelacion(button) {
        formularioCancelacionActual = button.closest('.form-cancelar-habilitacion');

        document.getElementById('cancelacionAgente').textContent =
            button.dataset.agente || 'este Agente';

        document.getElementById('cancelacionFecha').textContent =
            button.dataset.fecha || 'la fecha autorizada';

        modalCancelacion.classList.add('open');
        modalCancelacion.setAttribute('aria-hidden', 'false');
    }

    function cerrarModalCancelacion() {
        modalCancelacion.classList.remove('open');
        modalCancelacion.setAttribute('aria-hidden', 'true');
        formularioCancelacionActual = null;
    }

    confirmarCancelacionBtn.addEventListener('click', function () {
        if (! formularioCancelacionActual) {
            return;
        }

        this.disabled = true;
        this.textContent = 'Cancelando...';

        formularioCancelacionActual.submit();
    });

    modalHabilitacion.addEventListener('click', function (event) {
        if (event.target === modalHabilitacion) {
            cerrarModalHabilitacion();
        }
    });

    modalCancelacion.addEventListener('click', function (event) {
        if (event.target === modalCancelacion) {
            cerrarModalCancelacion();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        if (modalCancelacion.classList.contains('open')) {
            cerrarModalCancelacion();
            return;
        }

        if (modalHabilitacion.classList.contains('open')) {
            cerrarModalHabilitacion();
        }
    });

    @if ($errors->any())
        abrirModalHabilitacion();
    @endif
</script>
@endpush
