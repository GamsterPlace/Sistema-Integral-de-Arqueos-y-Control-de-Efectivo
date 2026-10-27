@extends('layouts.administrador')

@section('title', 'Agentes')
@section('module-title', 'Agentes')

@push('styles')
<style>
    .agents-page {
        display: grid;
        gap: 20px;
    }

    .agents-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 22px;
    }

    .agents-title {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .agents-title-icon {
        flex: 0 0 auto;
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #fff;
        box-shadow: 0 9px 20px rgba(22,76,150,.17);
    }

    .agents-title-icon svg,
    .create-button svg,
    .filter-title-icon svg,
    .filter-button svg,
    .clear-button svg,
    .summary-icon svg,
    .icon-button svg,
    .empty-icon svg {
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .agents-title-icon svg {
        width: 23px;
        height: 23px;
    }

    .agents-title h2 {
        margin: 0;
        color: #06284f;
        font-size: 27px;
        letter-spacing: -.6px;
    }

    .agents-title p {
        max-width: 720px;
        margin: 7px 0 0;
        color: #718391;
        font-size: 12px;
        line-height: 1.55;
    }

    .create-button {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 0 0 auto;
        padding: 0 17px;
        border: 1px solid #164c96;
        border-radius: 11px;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #fff;
        font-size: 10px;
        font-weight: 850;
        box-shadow: 0 8px 18px rgba(22,76,150,.17);
        transition: .2s ease;
    }

    .create-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 11px 22px rgba(22,76,150,.22);
    }

    .create-button svg {
        width: 16px;
        height: 16px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0,1fr));
        gap: 14px;
    }

    .summary-card {
        position: relative;
        overflow: hidden;
        min-height: 118px;
        padding: 17px 18px;
        border: 1px solid #dfe7ed;
        border-radius: 17px;
        background: #fff;
        box-shadow: 0 9px 24px rgba(20,57,83,.04);
        transition: .2s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 13px 28px rgba(20,57,83,.075);
    }

    .summary-card::after {
        content: "";
        position: absolute;
        width: 75px;
        height: 75px;
        right: -26px;
        bottom: -31px;
        border-radius: 50%;
        background: rgba(22,76,150,.035);
    }

    .summary-icon {
        width: 35px;
        height: 35px;
        display: grid;
        place-items: center;
        margin-bottom: 12px;
        border-radius: 10px;
        background: #edf4fb;
        color: #164c96;
    }

    .summary-card:nth-child(2) .summary-icon {
        background: #eff9f3;
        color: #008640;
    }

    .summary-card:nth-child(3) .summary-icon {
        background: #fff3f2;
        color: #b14b43;
    }

    .summary-card:nth-child(4) .summary-icon {
        background: #fff8e8;
        color: #9a7115;
    }

    .summary-icon svg {
        width: 18px;
        height: 18px;
    }

    .summary-label {
        display: block;
        color: #758697;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .55px;
        text-transform: uppercase;
    }

    .summary-value {
        position: relative;
        z-index: 1;
        display: block;
        margin-top: 5px;
        color: #082d55;
        font-size: 23px;
        font-weight: 850;
        letter-spacing: -.35px;
    }

    .filters-card {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 9px 25px rgba(20,57,83,.04);
    }

    .filters-header {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 15px 18px;
        border-bottom: 1px solid #e7edf1;
        background: #fbfcfd;
    }

    .filter-title-icon {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 10px;
        background: #edf4fb;
        color: #164c96;
    }

    .filter-title-icon svg {
        width: 17px;
        height: 17px;
    }

    .filters-header h3 {
        margin: 0;
        color: #0b315f;
        font-size: 13px;
        font-weight: 850;
    }

    .filters-header p {
        margin: 3px 0 0;
        color: #8795a0;
        font-size: 9px;
    }

    .filters-body {
        padding: 17px 18px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: minmax(250px,1.35fr) repeat(3,minmax(170px,.8fr));
        gap: 11px;
    }

    .filter-field {
        min-width: 0;
    }

    .filter-label {
        display: block;
        margin-bottom: 6px;
        color: #617586;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .filter-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        outline: none;
        background: #fff;
        color: #30485b;
        font-size: 10px;
        transition: .18s ease;
    }

    .filter-control:focus {
        border-color: #5d8fc6;
        box-shadow: 0 0 0 3px rgba(22,76,150,.08);
    }

    .filters-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 13px;
    }

    .filter-button,
    .clear-button {
        min-height: 39px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 14px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: 850;
        cursor: pointer;
        transition: .2s ease;
    }

    .clear-button {
        border: 1px solid #d5e0e7;
        background: #fff;
        color: #526a7c;
    }

    .clear-button:hover {
        background: #f5f8fa;
        border-color: #b4c4cf;
    }

    .filter-button {
        border: 1px solid #164c96;
        background: #164c96;
        color: #fff;
    }

    .filter-button:hover {
        background: #123f7d;
    }

    .filter-button svg,
    .clear-button svg {
        width: 14px;
        height: 14px;
    }

    .table-card {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(20,57,83,.045);
    }

    .table-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 16px 18px;
        border-bottom: 1px solid #e7edf1;
        background: #fbfcfd;
    }

    .table-card-header h3 {
        margin: 0;
        color: #0b315f;
        font-size: 13px;
        font-weight: 850;
    }

    .table-card-header p {
        margin: 4px 0 0;
        color: #84929d;
        font-size: 9px;
    }

    .records-count {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border: 1px solid #dbe5eb;
        border-radius: 999px;
        background: #fff;
        color: #607687;
        font-size: 9px;
        font-weight: 800;
    }

    .records-count-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #00a651;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .agents-table {
        width: 100%;
        min-width: 1180px;
        border-collapse: collapse;
    }

    .agents-table th {
        padding: 12px 14px;
        border-bottom: 1px solid #e2e9ee;
        background: #f7f9fb;
        color: #687d8d;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .55px;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .agents-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #edf1f4;
        color: #415b6e;
        font-size: 10px;
        vertical-align: middle;
    }

    .agents-table tbody tr {
        transition: background .16s ease;
    }

    .agents-table tbody tr:hover {
        background: #fafcfd;
    }

    .agents-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .agent-code {
        color: #0b315f;
        font-size: 10px;
        font-weight: 850;
    }

    .business-name {
        display: block;
        max-width: 220px;
        color: #173b59;
        font-weight: 800;
    }

    .route-copy {
        color: #607586;
        white-space: nowrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border: 1px solid #c9e5d4;
        border-radius: 999px;
        background: #effaf3;
        color: #197245;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .3px;
        text-transform: uppercase;
    }

    .status-badge.inactive {
        border-color: #e6d1d1;
        background: #fff4f4;
        color: #a33c3c;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .actions-cell {
        white-space: nowrap;
    }

    .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .icon-button {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid #d5dfe5;
        border-radius: 9px;
        background: #fff;
        color: #31536e;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .icon-button:hover {
        transform: translateY(-1px);
        border-color: #9db6c9;
        background: #f4f8fb;
        color: #164c96;
        box-shadow: 0 6px 14px rgba(24,66,99,.10);
    }

    .icon-button.edit {
        border-color: #c8d7e3;
        background: #edf5fb;
        color: #164c96;
    }

    .icon-button.state {
        border-color: #d9e1e6;
        background: #f7f9fa;
        color: #607586;
    }

    .icon-button.state.activate {
        border-color: #c9e5d4;
        background: #effaf3;
        color: #197245;
    }

    .icon-button svg {
        width: 17px;
        height: 17px;
    }

    .state-form {
        display: inline-flex;
        margin: 0;
    }

    .empty-state {
        padding: 48px 20px !important;
        text-align: center;
    }

    .empty-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        margin: 0 auto 12px;
        border-radius: 14px;
        background: #edf4fb;
        color: #164c96;
    }

    .empty-icon svg {
        width: 23px;
        height: 23px;
    }

    .empty-state strong {
        display: block;
        color: #173b59;
        font-size: 12px;
    }

    .empty-state span {
        display: block;
        margin-top: 5px;
        color: #8795a0;
        font-size: 10px;
    }

    .pagination-wrap {
        padding: 15px 18px;
        border-top: 1px solid #e7edf1;
        background: #fbfcfd;
    }

    @media (max-width: 1050px) {
        .summary-grid {
            grid-template-columns: repeat(2,minmax(0,1fr));
        }

        .filters-grid {
            grid-template-columns: repeat(2,minmax(0,1fr));
        }
    }

    @media (max-width: 700px) {
        .agents-header {
            flex-direction: column;
        }

        .create-button {
            width: 100%;
        }

        .summary-grid,
        .filters-grid {
            grid-template-columns: 1fr;
        }

        .filters-actions {
            flex-direction: column-reverse;
        }

        .filter-button,
        .clear-button {
            width: 100%;
        }

        .table-card-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="agents-page">
    <header class="agents-header">
        <div class="agents-title">
            <div class="agents-title-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M3 21h18"/>
                    <path d="M5 21V7l7-4 7 4v14"/>
                    <path d="M9 21v-6h6v6"/>
                    <path d="M19 4v6"/>
                    <path d="M22 7h-6"/>
                </svg>
            </div>

            <div>
                <h2>Gestión de Agentes</h2>
                <p>
                    Administre los Agentes MICOOPE, sus cuentas de acceso y
                    la asignación operativa de rutas.
                </p>
            </div>
        </div>

        <a
            href="{{ route('administrador.agentes.create') }}"
            class="create-button"
        >
            <svg viewBox="0 0 24 24">
                <path d="M12 5v14"/>
                <path d="M5 12h14"/>
            </svg>
            Crear Agente
        </a>
    </header>

    <section class="summary-grid">
        <article class="summary-card">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M3 21h18"/>
                    <path d="M5 21V7l7-4 7 4v14"/>
                    <path d="M9 21v-6h6v6"/>
                </svg>
            </div>
            <span class="summary-label">Total</span>
            <strong class="summary-value">{{ $resumen->total }}</strong>
        </article>

        <article class="summary-card">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m8 12 2.5 2.5L16 9"/>
                </svg>
            </div>
            <span class="summary-label">Activos</span>
            <strong class="summary-value">{{ $resumen->activos }}</strong>
        </article>

        <article class="summary-card">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m7 7 10 10"/>
                </svg>
            </div>
            <span class="summary-label">Inactivos</span>
            <strong class="summary-value">{{ $resumen->inactivos }}</strong>
        </article>

        <article class="summary-card">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="9" cy="8" r="4"/>
                    <path d="M3 21a6 6 0 0 1 12 0"/>
                    <path d="M16 16h6"/>
                </svg>
            </div>
            <span class="summary-label">Cuenta Inactiva</span>
            <strong class="summary-value">{{ $resumen->sin_usuario_activo }}</strong>
        </article>
    </section>

    <form
        method="GET"
        action="{{ route('administrador.agentes.index') }}"
        class="filters-card"
    >
        <header class="filters-header">
            <div class="filter-title-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M4 6h16"/>
                    <path d="M7 12h10"/>
                    <path d="M10 18h4"/>
                </svg>
            </div>

            <div>
                <h3>Filtros de búsqueda</h3>
                <p>Localice agentes por datos generales, estado, región o ruta.</p>
            </div>
        </header>

        <div class="filters-body">
            <div class="filters-grid">
                <div class="filter-field">
                    <label class="filter-label" for="buscar">Buscar agente</label>
                    <input
                        id="buscar"
                        type="text"
                        name="buscar"
                        value="{{ $buscar }}"
                        placeholder="Código, negocio, propietario o usuario"
                        class="filter-control"
                    >
                </div>

                <div class="filter-field">
                    <label class="filter-label" for="estado">Estado</label>
                    <select id="estado" name="estado" class="filter-control">
                        <option value="">Todos los estados</option>
                        <option value="ACTIVO" @selected($estado === 'ACTIVO')>ACTIVO</option>
                        <option value="INACTIVO" @selected($estado === 'INACTIVO')>INACTIVO</option>
                    </select>
                </div>

                <div class="filter-field">
                    <label class="filter-label" for="region_id">Región</label>
                    <select id="region_id" name="region_id" class="filter-control">
                        <option value="">Todas las regiones</option>
                        @foreach ($regiones as $region)
                            <option
                                value="{{ $region->id }}"
                                @selected($regionId === (int) $region->id)
                            >
                                {{ $region->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-field">
                    <label class="filter-label" for="ruta_id">Ruta</label>
                    <select id="ruta_id" name="ruta_id" class="filter-control">
                        <option value="">Todas las rutas</option>
                        @foreach ($rutas as $ruta)
                            <option
                                value="{{ $ruta->id }}"
                                @selected($rutaId === (int) $ruta->id)
                            >
                                {{ $ruta->codigo }} — {{ $ruta->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filters-actions">
                <a
                    href="{{ route('administrador.agentes.index') }}"
                    class="clear-button"
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M4 4l16 16"/>
                        <path d="M20 4 4 20"/>
                    </svg>
                    Limpiar
                </a>

                <button type="submit" class="filter-button">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 6h16"/>
                        <path d="M7 12h10"/>
                        <path d="M10 18h4"/>
                    </svg>
                    Aplicar Filtros
                </button>
            </div>
        </div>
    </form>

    <section class="table-card">
        <header class="table-card-header">
            <div>
                <h3>Listado de Agentes</h3>
                <p>Agentes registrados y su organización operativa actual.</p>
            </div>

            <div class="records-count">
                <span class="records-count-dot"></span>
                {{ $agentes->total() }} registros
            </div>
        </header>

        <div class="table-responsive">
            <table class="agents-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Negocio</th>
                        <th>Propietario</th>
                        <th>Usuario</th>
                        <th>Región</th>
                        <th>Ruta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($agentes as $agente)
                        @php
                            $activo = strtoupper((string) $agente->estado) === 'ACTIVO';
                        @endphp

                        <tr>
                            <td>
                                <span class="agent-code">
                                    {{ $agente->codigo_agente }}
                                </span>
                            </td>

                            <td>
                                <span class="business-name">
                                    {{ $agente->nombre_negocio }}
                                </span>
                            </td>

                            <td>{{ $agente->nombre_propietario }}</td>
                            <td>{{ $agente->usuario }}</td>
                            <td>{{ $agente->region_nombre }}</td>

                            <td>
                                <span class="route-copy">
                                    {{ $agente->ruta_codigo }} — {{ $agente->ruta_nombre }}
                                </span>
                            </td>

                            <td>
                                <span class="status-badge {{ $activo ? '' : 'inactive' }}">
                                    <span class="status-dot"></span>
                                    {{ $agente->estado }}
                                </span>
                            </td>

                            <td class="actions-cell">
                                <div class="table-actions">
                                    <a
                                        href="{{ route('administrador.agentes.show', $agente->id) }}"
                                        class="icon-button"
                                        title="Ver detalle"
                                        aria-label="Ver detalle"
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="2.8"/>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('administrador.agentes.edit', $agente->id) }}"
                                        class="icon-button edit"
                                        title="Editar agente"
                                        aria-label="Editar agente"
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('administrador.agentes.estado', $agente->id) }}"
                                        class="state-form"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="icon-button state {{ $activo ? '' : 'activate' }}"
                                            title="{{ $activo ? 'Desactivar agente' : 'Activar agente' }}"
                                            aria-label="{{ $activo ? 'Desactivar agente' : 'Activar agente' }}"
                                        >
                                            @if ($activo)
                                                <svg viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="9"/>
                                                    <path d="M12 7v5"/>
                                                    <path d="M12 16h.01"/>
                                                </svg>
                                            @else
                                                <svg viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="9"/>
                                                    <path d="m8 12 2.5 2.5L16 9"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <div class="empty-icon">
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="7"/>
                                        <path d="m20 20-4-4"/>
                                    </svg>
                                </div>

                                <strong>No se encontraron Agentes</strong>
                                <span>
                                    Modifique los filtros de búsqueda o registre un nuevo agente.
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($agentes->hasPages())
            <div class="pagination-wrap">
                {{ $agentes->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
