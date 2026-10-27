@extends('layouts.jefe')

@section('title', 'Listado de Promotores')
@section('module-title', 'Promotores')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .stat-card,
    .filters-card,
    .table-card {
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .stat-card {
        padding: 18px;
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

    .filters-card {
        margin-bottom: 20px;
        padding: 18px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) 190px;
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
        font-weight: 800;
        text-decoration: none;
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

    .promoters-table {
        width: 100%;
        min-width: 1050px;
        border-collapse: collapse;
    }

    .promoters-table th,
    .promoters-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        font-size: 10px;
    }

    .promoters-table th {
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

    @media (max-width: 750px) {
        .stats-grid,
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
        <h2>Listado de Promotores</h2>

        <p>
            Consulte los Promotores registrados,
            sus rutas, agentes asignados y actividad de arqueos.
        </p>
    </div>
</div>

<section class="stats-grid">
    <article class="stat-card">
        <span>Promotores activos</span>
        <strong>{{ $totalActivos }}</strong>
    </article>

    <article class="stat-card">
        <span>Promotores inactivos</span>
        <strong>{{ $totalInactivos }}</strong>
    </article>

    <article class="stat-card">
        <span>Arqueos de Promotor hoy</span>
        <strong>{{ $arqueosHoy }}</strong>
    </article>
</section>

<section class="filters-card">
    <form
        method="GET"
        action="{{ route('jefe.promotores.index') }}"
    >
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                class="form-control"
                value="{{ $buscar }}"
                placeholder="Nombre o usuario del Promotor"
            >

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
                href="{{ route('jefe.promotores.index') }}"
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
        <h3>Promotores registrados</h3>

        <p>
            Resumen operativo de rutas, agentes y arqueos.
        </p>
    </header>

    <div class="table-responsive">
        @if ($promotores->isEmpty())
            <div class="empty-state">
                No se encontraron Promotores.
            </div>
        @else
            <table class="promoters-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Rutas asignadas</th>
                        <th>Agentes asignados</th>
                        <th>Arqueos hoy</th>
                        <th>Total arqueos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($promotores as $promotor)
                        @php
                            $nombreCompleto = trim(
                                ($promotor->nombres ?? '')
                                . ' '
                                . ($promotor->apellidos ?? '')
                            );
                        @endphp

                        <tr>
                            <td>
                                <strong>
                                    {{ $nombreCompleto !== ''
                                        ? $nombreCompleto
                                        : 'Sin nombre registrado' }}
                                </strong>
                            </td>

                            <td>
                                {{ $promotor->usuario }}
                            </td>

                            <td>
                                <span
                                    class="badge {{
                                        $promotor->estado === 'ACTIVO'
                                            ? 'active'
                                            : 'inactive'
                                    }}"
                                >
                                    {{ $promotor->estado }}
                                </span>
                            </td>

                            <td>
                                {{ $promotor->rutas_asignadas }}
                            </td>

                            <td>
                                {{ $promotor->agentes_asignados }}
                            </td>

                            <td>
                                {{ $promotor->arqueos_hoy }}
                            </td>

                            <td>
                                {{ $promotor->total_arqueos }}
                            </td>

                            <td>
                                <div class="actions">
                                    <a
                                        href="{{ route(
                                            'jefe.promotores.show',
                                            $promotor->id
                                        ) }}"
                                        class="action-link primary"
                                    >
                                        Ver Promotor
                                    </a>

                                    <a
                                        href="{{ route(
                                            'jefe.arqueos-promotores.index',
                                            [
                                                'promotor_id'
                                                => $promotor->id
                                            ]
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

    @if ($promotores->hasPages())
        <div class="pagination">
            {{ $promotores->links() }}
        </div>
    @endif
</section>
@endsection
