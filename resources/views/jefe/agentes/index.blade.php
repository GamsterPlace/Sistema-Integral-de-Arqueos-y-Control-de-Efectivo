@extends('layouts.jefe')

@section('title', 'Listado de Agentes')
@section('module-title', 'Agentes')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .stat-card {
        padding: 18px;
        border: 1px solid #e0e8ee;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .stat-card span {
        display: block;
        color: #768692;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .stat-card strong {
        display: block;
        margin-top: 8px;
        color: #082d55;
        font-size: 25px;
    }

    .filters-card,
    .table-card {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .filters-card {
        margin-bottom: 20px;
        padding: 18px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns:
            minmax(240px, 1fr)
            190px
            210px
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
    }

    .filters-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 13px;
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

    .btn-primary {
        background: #164c96;
        color: #ffffff;
    }

    .btn-secondary {
        background: #edf2f5;
        color: #3d596f;
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
        min-width: 1200px;
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

    .status-badge {
        display: inline-flex;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .status-active {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .status-inactive {
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

    .actions-cell { white-space: nowrap; }
    .table-actions { display:inline-flex; align-items:center; gap:8px; }
    .icon-button { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border:1px solid #d5dfe5; border-radius:9px; background:#fff; color:#31536e; text-decoration:none; transition:.2s ease; }
    .icon-button:hover { transform:translateY(-1px); border-color:#9db6c9; background:#f4f8fb; color:#164c96; box-shadow:0 6px 14px rgba(24,66,99,.10); }
    .icon-button.print { border-color:#c8d7e3; background:#edf5fb; color:#164c96; }
    .icon-button svg { width:17px; height:17px; stroke:currentColor; stroke-width:1.9; fill:none; stroke-linecap:round; stroke-linejoin:round; }

    .pagination {
        padding: 16px 18px;
    }

    .empty-state {
        padding: 44px 20px;
        color: #7d8b95;
        text-align: center;
        font-size: 11px;
    }

    @media (max-width: 1000px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .filters-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 650px) {
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
        <h2>Listado de Agentes</h2>

        <p>
            Consulte los agentes registrados, su ubicación operativa,
            promotor asignado y estado actual.
        </p>
    </div>
</div>

<section class="stats-grid">
    <article class="stat-card">
        <span>Agentes activos</span>
        <strong>{{ $totalActivos }}</strong>
    </article>

    <article class="stat-card">
        <span>Agentes inactivos</span>
        <strong>{{ $totalInactivos }}</strong>
    </article>

    <article class="stat-card">
        <span>Con arqueo diario hoy</span>
        <strong>{{ $conArqueoHoy }}</strong>
    </article>
</section>

<section class="filters-card">
    <form
        method="GET"
        action="{{ route('jefe.agentes.index') }}"
    >
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                class="form-control"
                value="{{ $buscar }}"
                placeholder="Código, negocio, propietario o dirección"
            >

            <select
                name="region_id"
                class="form-control"
            >
                <option value="">Todas las regiones</option>

                @foreach ($regiones as $region)
                    <option
                        value="{{ $region->id }}"
                        @selected(
                            $regionId === (int) $region->id
                        )
                    >
                        {{ $region->nombre }}
                    </option>
                @endforeach
            </select>

            <select
                name="ruta_id"
                class="form-control"
            >
                <option value="">Todas las rutas</option>

                @foreach ($rutas as $ruta)
                    <option
                        value="{{ $ruta->id }}"
                        @selected(
                            $rutaId === (int) $ruta->id
                        )
                    >
                        {{ $ruta->codigo }}
                        — {{ $ruta->nombre }}
                    </option>
                @endforeach
            </select>

            <select
                name="estado"
                class="form-control"
            >
                <option value="">Todos los estados</option>

                <option
                    value="ACTIVO"
                    @selected($estado === 'ACTIVO')
                >
                    Activos
                </option>

                <option
                    value="INACTIVO"
                    @selected($estado === 'INACTIVO')
                >
                    Inactivos
                </option>
            </select>
        </div>

        <div class="filters-actions">
            <a
                href="{{ route('jefe.agentes.index') }}"
                class="btn btn-secondary"
            >
                Limpiar
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Buscar
            </button>
        </div>
    </form>
</section>

<section class="table-card">
    <header class="table-header">
        <h3>Agentes registrados</h3>

        <p>
            El Jefe de Agentes puede consultar la información,
            pero la creación de agentes corresponde al Administrador.
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
                        <th>Total arqueos</th>
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
                            <td>
                                <strong>
                                    {{ $agente->codigo_agente }}
                                </strong>
                            </td>

                            <td>{{ $agente->nombre_negocio }}</td>

                            <td>{{ $agente->nombre_propietario }}</td>

                            <td>{{ $agente->region_nombre }}</td>

                            <td>
                                {{ $agente->ruta_codigo }}
                                — {{ $agente->ruta_nombre }}
                            </td>

                            <td>
                                {{ $nombrePromotor !== ''
                                    ? $nombrePromotor
                                    : (
                                        $agente->promotor_usuario
                                        ?: 'Sin asignar'
                                    ) }}
                            </td>

                            <td>
                                <span class="status-badge {{
                                    $agente->estado === 'ACTIVO'
                                        ? 'status-active'
                                        : 'status-inactive'
                                }}">
                                    {{ $agente->estado }}
                                </span>
                            </td>

                            <td>
                                @if ((int) $agente->arqueo_hoy > 0)
                                    <span class="today-ok">
                                        Realizado
                                    </span>
                                @else
                                    <span class="today-pending">
                                        Pendiente
                                    </span>
                                @endif
                            </td>

                            <td>{{ $agente->total_arqueos }}</td>

                            <td>
                                {{ $agente->ultimo_arqueo
                                    ? \Carbon\Carbon::parse(
                                        $agente->ultimo_arqueo
                                    )->format('d/m/Y')
                                    : 'Sin arqueos' }}
                            </td>

                            <td class="actions-cell">
                                <div class="table-actions">
                                    <a href="{{ route('jefe.agentes.show', $agente->id) }}"
                                       class="icon-button" title="Ver agente" aria-label="Ver detalle del agente">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="2.8"></circle>
                                        </svg>
                                    </a>

                                    <a href="{{ route('jefe.agentes.imprimir', $agente->id) }}"
                                       target="_blank" class="icon-button print"
                                       title="Imprimir PDF" aria-label="Imprimir ficha del agente en PDF">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M6 9V3h12v6"></path>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                            <rect x="6" y="14" width="12" height="7"></rect>
                                            <path d="M18 12h.01"></path>
                                        </svg>
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
