@extends('layouts.jefe')

@section('title', 'Gestión de Rutas')
@section('module-title', 'Gestión de Rutas')

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
        background: #fff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, 0.05);
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

    .filters-card {
        margin-bottom: 20px;
        padding: 18px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) 210px 180px;
        gap: 11px;
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #fff;
        color: #30485b;
        outline: none;
    }

    .filters-actions,
    .page-actions,
    .actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filters-actions {
        justify-content: flex-end;
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
        color: #fff;
    }

    .btn-secondary {
        background: #edf2f5;
        color: #3d596f;
    }

    .btn-success {
        background: #16834f;
        color: #fff;
    }

    .table-card {
        overflow: hidden;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: center;
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

    .routes-table {
        width: 100%;
        min-width: 1280px;
        border-collapse: collapse;
    }

    .routes-table th,
    .routes-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        font-size: 10px;
    }

    .routes-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge.active {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .badge.inactive {
        background: #fdecec;
        color: #b13c3c;
    }

    .badge.permanent {
        background: #eaf2ff;
        color: #235fa7;
    }

    .badge.temporary {
        background: #fff4dc;
        color: #966710;
    }

    .promoters-cell {
        min-width: 145px;
    }

    .promoters-summary {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .promoters-total {
        color: #0a3158;
        font-size: 10px;
        font-weight: 800;
    }

    .promoters-types {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: wrap;
    }

    .no-promoter {
        color: #9a6a15;
        font-size: 9px;
        font-weight: 800;
    }

    .action-link {
        min-height: 34px;
        padding: 0 10px;
        border: 1px solid #d5dfe5;
        border-radius: 9px;
        background: #fff;
        color: #31536e;
        font-size: 9px;
        white-space: nowrap;
    }

    .action-link.primary {
        border-color: #cbdceb;
        background: #edf5fb;
        color: #164c96;
    }

    .action-link.promoters {
        border-color: #c8d8ed;
        background: #f1f6fc;
        color: #0f4c8a;
    }

    .action-link.warning {
        border-color: #eed8a6;
        background: #fff9e9;
        color: #8a6511;
    }

    .action-link.success {
        border-color: #bee2cc;
        background: #effaf3;
        color: #176c40;
    }

    .modal-backdrop {
        position: fixed;
        z-index: 100;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(5, 29, 52, 0.55);
        backdrop-filter: blur(3px);
    }

    .modal-backdrop.open {
        display: flex;
    }

    .modal-card {
        width: 100%;
        max-width: 560px;
        overflow: hidden;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.22);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 18px 20px;
        border-bottom: 1px solid #edf1f4;
    }

    .modal-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 16px;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 10px;
        background: #eef3f6;
        color: #4b6578;
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

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 20px 20px;
    }

    .confirm-modal-card {
        max-width: 470px;
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

    .confirm-icon.success {
        background: #eaf8ef;
        color: #1d7b4e;
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
        color: #fff;
    }

    .btn-warning-modal:hover {
        background: #9e6712;
    }

    .btn-success-modal {
        background: #16834f;
        color: #fff;
    }

    .btn-success-modal:hover {
        background: #116f42;
    }

    .pagination {
        padding: 16px 18px;
    }

    @media (max-width: 950px) {
        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }

        .table-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 650px) {
        .summary-grid {
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
        <h2>Gestión de Rutas</h2>
        <p>
            Administre las rutas operativas, su región asociada,
            agentes y promotores asignados.
        </p>
    </div>

    <div class="page-actions">
        <button
            type="button"
            class="btn btn-success"
            onclick="abrirCrearRuta()"
        >
            Nueva Ruta
        </button>
    </div>
</div>

<section class="summary-grid">
    <article class="summary-card">
        <span>Total rutas</span>
        <strong>{{ $totalRutas }}</strong>
    </article>

    <article class="summary-card">
        <span>Rutas activas</span>
        <strong>{{ $rutasActivas }}</strong>
    </article>

    <article class="summary-card">
        <span>Rutas inactivas</span>
        <strong>{{ $rutasInactivas }}</strong>
    </article>

    <article class="summary-card warning">
        <span>Rutas sin Promotor</span>
        <strong>{{ $rutasSinPromotor }}</strong>
    </article>
</section>

<section class="filters-card">
    <form
        method="GET"
        action="{{ route('jefe.rutas.index') }}"
    >
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                value="{{ $buscar }}"
                class="form-control"
                placeholder="Código, ruta o región"
            >

            <select
                name="region_id"
                class="form-control"
            >
                <option value="">
                    Todas las regiones
                </option>

                @foreach($regiones as $region)
                    <option
                        value="{{ $region->id }}"
                        @selected($regionId === (int) $region->id)
                    >
                        {{ $region->nombre }}
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
                    value="ACTIVA"
                    @selected($estado === 'ACTIVA')
                >
                    Activas
                </option>

                <option
                    value="INACTIVA"
                    @selected($estado === 'INACTIVA')
                >
                    Inactivas
                </option>
            </select>
        </div>

        <div class="filters-actions">
            <a
                href="{{ route('jefe.rutas.index') }}"
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
        <div>
            <h3>Rutas registradas</h3>
            <p>
                La desactivación se bloquea si existen agentes activos
                o promotores con asignaciones vigentes.
            </p>
        </div>
    </header>

    <div class="table-responsive">
        <table class="routes-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Ruta</th>
                    <th>Región</th>
                    <th>Promotores asignados</th>
                    <th>Agentes</th>
                    <th>Activos</th>
                    <th>Arqueados hoy</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rutas as $ruta)
                    <tr>
                        <td>
                            <strong>{{ $ruta->codigo }}</strong>
                        </td>

                        <td>
                            {{ $ruta->nombre }}
                        </td>

                        <td>
                            {{ $ruta->region_nombre }}
                        </td>

                        <td class="promoters-cell">
                            @if((int) $ruta->promotores_asignados === 0)
                                <span class="no-promoter">
                                    Sin Promotor
                                </span>
                            @else
                                <div class="promoters-summary">
                                    <span class="promoters-total">
                                        {{ $ruta->promotores_asignados }}
                                        {{
                                            (int) $ruta->promotores_asignados === 1
                                                ? 'Promotor'
                                                : 'Promotores'
                                        }}
                                    </span>

                                    <div class="promoters-types">
                                        @if((int) $ruta->promotores_permanentes > 0)
                                            <span class="badge permanent">
                                                {{ $ruta->promotores_permanentes }}
                                                {{
                                                    (int) $ruta->promotores_permanentes === 1
                                                        ? 'Permanente'
                                                        : 'Permanentes'
                                                }}
                                            </span>
                                        @endif

                                        @if((int) $ruta->promotores_temporales > 0)
                                            <span class="badge temporary">
                                                {{ $ruta->promotores_temporales }}
                                                {{
                                                    (int) $ruta->promotores_temporales === 1
                                                        ? 'Temporal'
                                                        : 'Temporales'
                                                }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </td>

                        <td>
                            {{ $ruta->total_agentes }}
                        </td>

                        <td>
                            {{ $ruta->agentes_activos }}
                        </td>

                        <td>
                            {{ $ruta->arqueados_hoy }}
                        </td>

                        <td>
                            <span
                                class="badge {{ $ruta->estado ? 'active' : 'inactive' }}"
                            >
                                {{ $ruta->estado ? 'ACTIVA' : 'INACTIVA' }}
                            </span>
                        </td>

                        <td>
                            <div class="actions">
                                <button
                                    type="button"
                                    class="action-link primary"
                                    onclick="abrirEditarRuta(
                                        {{ $ruta->id }},
                                        {{ Illuminate\Support\Js::from($ruta->codigo) }},
                                        {{ Illuminate\Support\Js::from($ruta->nombre) }},
                                        {{ $ruta->region_id }}
                                    )"
                                >
                                    Editar
                                </button>

                                <a
                                    href="{{ route('jefe.rutas.agentes', $ruta->id) }}"
                                    class="action-link"
                                >
                                    Administrar agentes
                                </a>

                                <a
                                    href="{{ route('jefe.rutas.promotor', $ruta->id) }}"
                                    class="action-link promoters"
                                >
                                    Administrar promotores
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('jefe.rutas.estado', $ruta->id) }}"
                                    class="form-cambiar-estado-ruta"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="button"
                                        class="action-link {{ $ruta->estado ? 'warning' : 'success' }}"
                                        onclick="abrirModalEstadoRuta(this)"
                                        data-ruta="{{ $ruta->codigo }} — {{ $ruta->nombre }}"
                                        data-accion="{{ $ruta->estado ? 'desactivar' : 'activar' }}"
                                    >
                                        {{ $ruta->estado ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="9"
                            style="padding:35px;text-align:center;"
                        >
                            No se encontraron rutas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rutas->hasPages())
        <div class="pagination">
            {{ $rutas->links() }}
        </div>
    @endif
</section>

<div
    class="modal-backdrop"
    id="modalRuta"
>
    <div class="modal-card">
        <form
            method="POST"
            id="formRuta"
            action="{{ route('jefe.rutas.store') }}"
        >
            @csrf

            <input
                type="hidden"
                name="_method"
                id="rutaMethod"
                value="POST"
            >

            <header class="modal-header">
                <h3 id="modalRutaTitulo">
                    Nueva Ruta
                </h3>

                <button
                    type="button"
                    class="modal-close"
                    onclick="cerrarModalRuta()"
                    aria-label="Cerrar"
                >
                    ×
                </button>
            </header>

            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="rutaCodigo">
                            Código
                        </label>

                        <input
                            type="text"
                            name="codigo"
                            id="rutaCodigo"
                            class="form-control"
                            required
                            maxlength="50"
                        >
                    </div>

                    <div class="form-group">
                        <label for="rutaNombre">
                            Nombre de la Ruta
                        </label>

                        <input
                            type="text"
                            name="nombre"
                            id="rutaNombre"
                            class="form-control"
                            required
                            maxlength="150"
                        >
                    </div>

                    <div class="form-group">
                        <label for="rutaRegion">
                            Región
                        </label>

                        <select
                            name="region_id"
                            id="rutaRegion"
                            class="form-control"
                            required
                        >
                            <option value="">
                                Seleccione una región
                            </option>

                            @foreach($regiones as $region)
                                @if($region->estado)
                                    <option value="{{ $region->id }}">
                                        {{ $region->nombre }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <footer class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="cerrarModalRuta()"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Guardar
                </button>
            </footer>
        </form>
    </div>
</div>

<div
    class="modal-backdrop"
    id="modalEstadoRuta"
    aria-hidden="true"
>
    <div
        class="modal-card confirm-modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="tituloModalEstadoRuta"
    >
        <header class="modal-header">
            <h3 id="tituloModalEstadoRuta">
                Confirmar cambio de estado
            </h3>

            <button
                type="button"
                class="modal-close"
                onclick="cerrarModalEstadoRuta()"
                aria-label="Cerrar"
            >
                ×
            </button>
        </header>

        <div class="confirm-modal-body">
            <div
                class="confirm-icon"
                id="iconoEstadoRuta"
            >
                <svg
                    id="iconoDesactivarRuta"
                    viewBox="0 0 24 24"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                    ></circle>

                    <path d="M8 12h8"></path>
                </svg>

                <svg
                    id="iconoActivarRuta"
                    viewBox="0 0 24 24"
                    style="display:none;"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                    ></circle>

                    <path d="M8 12h8"></path>
                    <path d="M12 8v8"></path>
                </svg>
            </div>

            <h4 id="preguntaEstadoRuta">
                ¿Cambiar estado de la ruta?
            </h4>

            <p id="descripcionEstadoRuta">
                Confirme la operación antes de continuar.
            </p>
        </div>

        <footer class="modal-footer">
            <button
                type="button"
                class="btn btn-secondary"
                onclick="cerrarModalEstadoRuta()"
            >
                Volver
            </button>

            <button
                type="button"
                class="btn btn-warning-modal"
                id="confirmarEstadoRutaBtn"
            >
                Confirmar
            </button>
        </footer>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modalRuta =
        document.getElementById('modalRuta');

    const formRuta =
        document.getElementById('formRuta');

    const modalRutaTitulo =
        document.getElementById('modalRutaTitulo');

    const rutaMethod =
        document.getElementById('rutaMethod');

    const rutaCodigo =
        document.getElementById('rutaCodigo');

    const rutaNombre =
        document.getElementById('rutaNombre');

    const rutaRegion =
        document.getElementById('rutaRegion');

    const storeUrl =
        @json(route('jefe.rutas.store'));

    const updateBaseUrl =
        @json(url('/jefe-agentes/rutas'));

    const modalEstadoRuta =
        document.getElementById('modalEstadoRuta');

    const confirmarEstadoRutaBtn =
        document.getElementById('confirmarEstadoRutaBtn');

    const preguntaEstadoRuta =
        document.getElementById('preguntaEstadoRuta');

    const descripcionEstadoRuta =
        document.getElementById('descripcionEstadoRuta');

    const iconoEstadoRuta =
        document.getElementById('iconoEstadoRuta');

    const iconoDesactivarRuta =
        document.getElementById('iconoDesactivarRuta');

    const iconoActivarRuta =
        document.getElementById('iconoActivarRuta');

    let formularioEstadoRutaActual = null;

    function abrirCrearRuta() {
        formRuta.reset();

        formRuta.action = storeUrl;

        rutaMethod.value = 'POST';

        modalRutaTitulo.textContent =
            'Nueva Ruta';

        modalRuta.classList.add('open');
    }

    function abrirEditarRuta(
        id,
        codigo,
        nombre,
        regionId
    ) {
        formRuta.action =
            updateBaseUrl + '/' + id;

        rutaMethod.value = 'PUT';

        modalRutaTitulo.textContent =
            'Editar Ruta';

        rutaCodigo.value =
            codigo ?? '';

        rutaNombre.value =
            nombre ?? '';

        rutaRegion.value =
            String(regionId ?? '');

        modalRuta.classList.add('open');
    }

    function cerrarModalRuta() {
        modalRuta.classList.remove('open');
    }

    function abrirModalEstadoRuta(button) {
        formularioEstadoRutaActual =
            button.closest(
                '.form-cambiar-estado-ruta'
            );

        const ruta =
            button.dataset.ruta || 'esta ruta';

        const accion =
            button.dataset.accion || 'cambiar';

        const activar =
            accion === 'activar';

        preguntaEstadoRuta.textContent =
            activar
                ? '¿Activar esta ruta?'
                : '¿Desactivar esta ruta?';

        descripcionEstadoRuta.textContent =
            activar
                ? 'La ruta "' +
                    ruta +
                    '" volverá a quedar disponible para la operación del sistema.'
                : 'La ruta "' +
                    ruta +
                    '" quedará inactiva. La operación puede ser bloqueada si existen agentes activos o promotores con asignaciones vigentes.';

        iconoEstadoRuta.classList.toggle(
            'success',
            activar
        );

        iconoActivarRuta.style.display =
            activar
                ? 'block'
                : 'none';

        iconoDesactivarRuta.style.display =
            activar
                ? 'none'
                : 'block';

        confirmarEstadoRutaBtn.className =
            'btn ' +
            (
                activar
                    ? 'btn-success-modal'
                    : 'btn-warning-modal'
            );

        confirmarEstadoRutaBtn.textContent =
            activar
                ? 'Sí, Activar Ruta'
                : 'Sí, Desactivar Ruta';

        confirmarEstadoRutaBtn.disabled = false;

        modalEstadoRuta.classList.add('open');

        modalEstadoRuta.setAttribute(
            'aria-hidden',
            'false'
        );
    }

    function cerrarModalEstadoRuta() {
        modalEstadoRuta.classList.remove('open');

        modalEstadoRuta.setAttribute(
            'aria-hidden',
            'true'
        );

        formularioEstadoRutaActual = null;

        confirmarEstadoRutaBtn.disabled = false;

        confirmarEstadoRutaBtn.textContent =
            'Confirmar';
    }

    confirmarEstadoRutaBtn.addEventListener(
        'click',
        function () {
            if (! formularioEstadoRutaActual) {
                return;
            }

            this.disabled = true;

            this.textContent =
                'Procesando...';

            formularioEstadoRutaActual.submit();
        }
    );

    modalRuta.addEventListener(
        'click',
        function (event) {
            if (event.target === modalRuta) {
                cerrarModalRuta();
            }
        }
    );

    modalEstadoRuta.addEventListener(
        'click',
        function (event) {
            if (event.target === modalEstadoRuta) {
                cerrarModalEstadoRuta();
            }
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            if (
                modalEstadoRuta.classList.contains(
                    'open'
                )
            ) {
                cerrarModalEstadoRuta();
                return;
            }

            if (
                modalRuta.classList.contains(
                    'open'
                )
            ) {
                cerrarModalRuta();
            }
        }
    );
</script>
@endpush
