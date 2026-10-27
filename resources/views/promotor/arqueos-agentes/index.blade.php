@extends('layouts.promotor')

@section('title', 'Arqueos de Agentes')
@section('module-title', 'Arqueos de Agentes')

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

    .search-card {
        margin-bottom: 22px;
        padding: 18px;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
    }

    .search-form {
        display: flex;
        gap: 12px;
    }

    .search-input {
        width: 100%;
        min-height: 44px;
        padding: 0 14px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        outline: none;
    }

    .search-input:focus {
        border-color: #2b72b8;
        box-shadow: 0 0 0 3px rgba(43, 114, 184, .12);
    }

    .btn-primary,
    .btn-secondary,
    .btn-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 17px;
        border: 0;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary {
        background: #174f8a;
        color: #ffffff;
    }

    .btn-secondary {
        background: #eef3f7;
        color: #38556d;
    }

    .btn-view {
        min-height: 36px;
        background: #e9f2fb;
        color: #174f8a;
    }

    .table-card {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
    }

    .agents-table {
        width: 100%;
        border-collapse: collapse;
    }

    .agents-table th,
    .agents-table td {
        padding: 14px 15px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
    }

    .agents-table th {
        color: #627588;
        background: #f7f9fb;
        font-size: 12px;
        text-transform: uppercase;
    }

    .agents-table tbody tr:hover {
        background: #fafcfd;
    }

    .agent-name {
        color: #173f66;
        font-weight: 800;
    }

    .agent-code {
        color: #6b7c8d;
        font-size: 12px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
    }

    .badge-neutral {
        background: #edf2f6;
        color: #50667a;
    }

    .badge-warning {
        background: #fff3d5;
        color: #8a6100;
    }

    .badge-success {
        background: #e8f7ee;
        color: #247048;
    }

    .empty-state {
        padding: 45px 20px;
        color: #758697;
        text-align: center;
    }

    .pagination-wrapper {
        padding: 16px;
    }

    @media (max-width: 900px) {
        .table-card {
            overflow-x: auto;
        }

        .agents-table {
            min-width: 900px;
        }

        .search-form {
            flex-direction: column;
        }
    }
</style>

<div class="page-header">
    <div class="page-title">
        <h2>Arqueos de Agentes</h2>
        <p>
            Consulte los agentes de sus rutas asignadas y revise sus
            arqueos diarios.
        </p>
    </div>
</div>

<div class="search-card">
    <form
        method="GET"
        action="{{ route('promotor.arqueos-agentes.index') }}"
        class="search-form"
    >
        <input
            type="text"
            name="buscar"
            class="search-input"
            value="{{ $busqueda }}"
            placeholder="Buscar por código, negocio, propietario, ruta o región"
        >

        <button type="submit" class="btn-primary">
            Buscar
        </button>

        @if ($busqueda !== '')
            <a
                href="{{ route('promotor.arqueos-agentes.index') }}"
                class="btn-secondary"
            >
                Limpiar
            </a>
        @endif
    </form>
</div>

<div class="table-card">
    <table class="agents-table">
        <thead>
            <tr>
                <th>Agente</th>
                <th>Propietario</th>
                <th>Ruta</th>
                <th>Región</th>
                <th>Arqueos</th>
                <th>Pendientes</th>
                <th>Acción</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($agentes as $agente)
                <tr>
                    <td>
                        <div class="agent-name">
                            {{ $agente->nombre_negocio }}
                        </div>

                        <div class="agent-code">
                            {{ $agente->codigo_agente }}
                        </div>
                    </td>

                    <td>
                        {{ $agente->nombre_propietario }}
                    </td>

                    <td>
                        {{ $agente->ruta_codigo }}
                        — {{ $agente->ruta_nombre }}
                    </td>

                    <td>
                        {{ $agente->region_nombre }}
                    </td>

                    <td>
                        <span class="badge badge-neutral">
                            {{ $agente->total_arqueos }}
                        </span>
                    </td>

                    <td>
                        <span class="badge {{ $agente->pendientes_certificacion > 0
                            ? 'badge-warning'
                            : 'badge-success' }}"
                        >
                            {{ $agente->pendientes_certificacion }}
                        </span>
                    </td>

                    <td>
                        <a
                            href="{{ route(
                                'promotor.arqueos-agentes.arqueos',
                                $agente->id
                            ) }}"
                            class="btn-view"
                        >
                            Ver arqueos
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            No se encontraron agentes asignados.
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($agentes->hasPages())
        <div class="pagination-wrapper">
            {{ $agentes->links() }}
        </div>
    @endif
</div>
@endsection
