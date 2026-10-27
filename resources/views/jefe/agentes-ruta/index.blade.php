@extends('layouts.jefe')

@section('title', 'Agentes por Ruta')
@section('module-title', 'Agentes por Ruta')

@push('styles')
<style>
    .route-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 22px;
    }

    .route-card,
    .filters-card,
    .table-card {
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .route-card {
        padding: 17px;
    }

    .route-card.active {
        border-color: #b8d1e8;
        background: #f7fbff;
    }

    .route-name {
        margin-top: 5px;
        color: #173b59;
        font-size: 13px;
        font-weight: 800;
    }

    .route-region {
        margin-top: 4px;
        color: #7b8993;
        font-size: 10px;
    }

    .route-promoter {
        margin-top: 12px;
        padding-top: 11px;
        border-top: 1px solid #edf1f4;
        color: #607487;
        font-size: 10px;
    }

    .route-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 13px;
    }

    .route-stat {
        padding: 10px;
        border-radius: 10px;
        background: #f5f8fa;
        text-align: center;
    }

    .route-stat span {
        display: block;
        color: #7a8994;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .route-stat strong {
        display: block;
        margin-top: 5px;
        color: #082d55;
        font-size: 18px;
    }

    .filters-card {
        margin-bottom: 20px;
        padding: 18px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: minmax(230px, 1fr) 190px 220px 160px;
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
        text-decoration: none;
        font-weight: 800;
    }

    .btn {
        min-height: 40px;
        padding: 0 15px;
        border: 0;
        border-radius: 10px;
        font-size: 10px;
        cursor: pointer;
    }

    .btn-primary {
        background: #164c96;
        color: #ffffff;
    }

    .btn-secondary {
        background: #edf2f5;
        color: #3d596f;
    }

    .selected-route {
        margin-bottom: 18px;
        padding: 16px 18px;
        border: 1px solid #cbdff1;
        border-radius: 14px;
        background: #f7fbff;
    }

    .selected-route strong {
        color: #0a3158;
        font-size: 13px;
    }

    .selected-route span {
        display: block;
        margin-top: 4px;
        color: #6f808d;
        font-size: 10px;
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

    .agents-table {
        width: 100%;
        min-width: 1180px;
        border-collapse: collapse;
    }

    .agents-table th,
    .agents-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        font-size: 10px;
    }

    .agents-table th {
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
    }

    .badge.active {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .badge.inactive {
        background: #fdecec;
        color: #b13c3c;
    }

    .today-ok {
        color: #1d7b4e;
        font-weight: 800;
    }

    .today-pending {
        color: #9c7014;
        font-weight: 800;
    }

    .actions {
        display: flex;
        gap: 7px;
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

    .action-link.primary {
        border-color: #cbdceb;
        background: #edf5fb;
        color: #164c96;
    }

    .empty-state {
        padding: 44px 20px;
        color: #7d8b95;
        text-align: center;
        font-size: 11px;
    }

    .pagination {
        padding: 16px 18px;
    }

    @media (max-width: 1100px) {
        .route-grid,
        .filters-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 700px) {
        .route-grid,
        .filters-grid {
            grid-template-columns: 1fr;
        }

        .filters-actions {
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
        <h2>Agentes por Ruta</h2>
        <p>
            Consulte la distribución de agentes por ruta,
            su Promotor asignado y el cumplimiento del arqueo diario.
        </p>
    </div>
</div>

@if ($resumenRutas->isNotEmpty())
    <section class="route-grid">
        @foreach ($resumenRutas as $ruta)
            @php
                $nombrePromotor = trim(
                    ($ruta->promotor_nombres ?? '')
                    . ' '
                    . ($ruta->promotor_apellidos ?? '')
                );

                $pendientesRuta = max(
                    0,
                    (int) $ruta->agentes_activos
                    - (int) $ruta->agentes_con_arqueo_hoy
                );
            @endphp

            <article class="route-card {{ $rutaId === (int) $ruta->id ? 'active' : '' }}">
                <div class="route-name">
                    {{ $ruta->codigo }} — {{ $ruta->nombre }}
                </div>

                <div class="route-region">
                    {{ $ruta->region_nombre }}
                </div>

                <div class="route-promoter">
                    Promotor:
                    <strong>
                        {{ $nombrePromotor !== ''
                            ? $nombrePromotor
                            : ($ruta->promotor_usuario ?: 'Sin promotor asignado') }}
                    </strong>
                </div>

                <div class="route-stats">
                    <div class="route-stat">
                        <span>Activos</span>
                        <strong>{{ $ruta->agentes_activos }}</strong>
                    </div>

                    <div class="route-stat">
                        <span>Arqueados hoy</span>
                        <strong>{{ $ruta->agentes_con_arqueo_hoy }}</strong>
                    </div>

                    <div class="route-stat">
                        <span>Pendientes</span>
                        <strong>{{ $pendientesRuta }}</strong>
                    </div>
                </div>

                <div style="margin-top:13px;">
                    <a
                        href="{{ route(
                            'jefe.agentes-ruta.index',
                            ['ruta_id' => $ruta->id]
                        ) }}"
                        class="action-link primary"
                    >
                        Ver agentes
                    </a>
                </div>
            </article>
        @endforeach
    </section>
@endif

<section class="filters-card">
    <form method="GET" action="{{ route('jefe.agentes-ruta.index') }}">
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                class="form-control"
                value="{{ $buscar }}"
                placeholder="Código, negocio o propietario"
            >

            <select name="region_id" class="form-control">
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

            <select name="ruta_id" class="form-control">
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

            <select name="estado" class="form-control">
                <option value="">Todos los estados</option>
                <option value="ACTIVO" @selected($estado === 'ACTIVO')>Activos</option>
                <option value="INACTIVO" @selected($estado === 'INACTIVO')>Inactivos</option>
            </select>
        </div>

        <div class="filters-actions">
            <a href="{{ route('jefe.agentes-ruta.index') }}" class="btn btn-secondary">
                Limpiar
            </a>

            <button type="submit" class="btn btn-primary">
                Aplicar filtros
            </button>
        </div>
    </form>
</section>

@if ($rutaSeleccionada)
    <div class="selected-route">
        <strong>
            {{ $rutaSeleccionada->codigo }}
            — {{ $rutaSeleccionada->nombre }}
        </strong>

        <span>
            Región: {{ $rutaSeleccionada->region_nombre }}
        </span>
    </div>
@endif

<section class="table-card">
    <header class="table-header">
        <h3>Agentes de las rutas</h3>
        <p>
            Listado operativo de agentes según los filtros seleccionados.
        </p>
    </header>

    <div class="table-responsive">
        @if ($agentes->isEmpty())
            <div class="empty-state">
                No se encontraron agentes.
            </div>
        @else
            <table class="agents-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Negocio</th>
                        <th>Propietario</th>
                        <th>Región</th>
                        <th>Ruta</th>
                        <th>Promotor</th>
                        <th>Estado</th>
                        <th>Arqueo hoy</th>
                        <th>Último arqueo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($agentes as $agente)
                        @php
                            $nombrePromotor = trim(
                                ($agente->promotor_nombres ?? '')
                                . ' '
                                . ($agente->promotor_apellidos ?? '')
                            );
                        @endphp

                        <tr>
                            <td><strong>{{ $agente->codigo_agente }}</strong></td>
                            <td>{{ $agente->nombre_negocio }}</td>
                            <td>{{ $agente->nombre_propietario }}</td>
                            <td>{{ $agente->region_nombre }}</td>
                            <td>
                                {{ $agente->ruta_codigo }} — {{ $agente->ruta_nombre }}
                            </td>
                            <td>
                                {{ $nombrePromotor !== ''
                                    ? $nombrePromotor
                                    : ($agente->promotor_usuario ?: 'Sin asignar') }}
                            </td>
                            <td>
                                <span class="badge {{ $agente->estado === 'ACTIVO' ? 'active' : 'inactive' }}">
                                    {{ $agente->estado }}
                                </span>
                            </td>
                            <td>
                                @if ((int) $agente->arqueo_hoy > 0)
                                    <span class="today-ok">Realizado</span>
                                @else
                                    <span class="today-pending">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                {{ $agente->ultimo_arqueo
                                    ? \Carbon\Carbon::parse(
                                        $agente->ultimo_arqueo
                                    )->format('d/m/Y')
                                    : 'Sin arqueos' }}
                            </td>
                            <td>
                                <div class="actions">
                                    <a
                                        href="{{ route(
                                            'jefe.agentes.show',
                                            $agente->id
                                        ) }}"
                                        class="action-link primary"
                                    >
                                        Ver agente
                                    </a>

                                    <a
                                        href="{{ route(
                                            'jefe.arqueos-agentes.index',
                                            ['agente_id' => $agente->id]
                                        ) }}"
                                        class="action-link"
                                    >
                                        Ver arqueos
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if ($agentes->hasPages())
        <div class="pagination">
            {{ $agentes->links() }}
        </div>
    @endif
</section>
@endsection
