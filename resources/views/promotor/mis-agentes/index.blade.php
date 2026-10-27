@extends('layouts.promotor')

@section('title', 'Mis Agentes')
@section('module-title', 'Mis Agentes')

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

    .filter-card {
        margin-bottom: 22px;
        padding: 18px;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
    }

    .filter-form {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) minmax(220px, 320px) auto auto;
        gap: 12px;
        align-items: center;
    }

    .form-control {
        width: 100%;
        min-height: 44px;
        padding: 0 14px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #ffffff;
        color: #2f4659;
        outline: none;
    }

    .form-control:focus {
        border-color: #2b72b8;
        box-shadow: 0 0 0 3px rgba(43, 114, 184, .12);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 0 17px;
        border: 0;
        border-radius: 10px;
        font-weight: 800;
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

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .summary-card {
        padding: 17px;
        border: 1px solid #dfe7ed;
        border-radius: 14px;
        background: #ffffff;
    }

    .summary-card span {
        display: block;
        color: #718394;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .summary-card strong {
        display: block;
        margin-top: 7px;
        color: #173f66;
        font-size: 23px;
        font-weight: 800;
    }

    .agents-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .agent-card {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .agent-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(28, 55, 78, .09);
    }

    .agent-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 17px;
        border-bottom: 1px solid #e8edf1;
        background: #f8fafb;
    }

    .agent-card-title {
        min-width: 0;
    }

    .agent-card-title h3 {
        margin: 0;
        overflow: hidden;
        color: #173f66;
        font-size: 17px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .agent-code {
        display: block;
        margin-top: 5px;
        color: #6e8091;
        font-size: 12px;
        font-weight: 700;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-active {
        border: 1px solid #b9e1c8;
        background: #e8f7ee;
        color: #247048;
    }

    .status-warning {
        border: 1px solid #ffd76a;
        background: #fff4cc;
        color: #9a6a00;
    }

    .status-inactive {
        background: #ffe8e6;
        color: #a93b35;
    }

    .agent-card-body {
        padding: 17px;
    }

    .detail-row {
        display: grid;
        grid-template-columns: 104px minmax(0, 1fr);
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #edf1f4;
    }

    .detail-row:last-child {
        border-bottom: 0;
    }

    .detail-label {
        color: #718394;
        font-size: 12px;
        font-weight: 800;
    }

    .detail-value {
        min-width: 0;
        color: #334d61;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .agent-card-footer {
        display: flex;
        justify-content: flex-end;
        padding: 14px 17px;
        border-top: 1px solid #e8edf1;
        background: #fbfcfd;
    }

    .btn-arqueos {
        min-height: 37px;
        background: #e9f2fb;
        color: #174f8a;
    }

    .empty-state {
        grid-column: 1 / -1;
        padding: 50px 20px;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
        color: #758697;
        text-align: center;
    }

    .pagination-wrapper {
        margin-top: 20px;
    }

    @media (max-width: 1150px) {
        .agents-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 850px) {
        .filter-form {
            grid-template-columns: 1fr;
        }

        .summary-grid,
        .agents-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div class="page-title">
        <h2>Mis Agentes</h2>

        <p>
            Visualice los agentes que pertenecen a las rutas que tiene
            asignadas actualmente.
        </p>
    </div>
</div>

<div class="filter-card">
    <form
        method="GET"
        action="{{ route('promotor.mis-agentes.index') }}"
        class="filter-form"
    >
        <input
            type="text"
            name="buscar"
            class="form-control"
            value="{{ $busqueda }}"
            placeholder="Buscar por código, negocio, propietario o dirección"
        >

        <select name="ruta_id" class="form-control">
            <option value="">Todas mis rutas</option>

            @foreach ($rutasAsignadas as $ruta)
                <option
                    value="{{ $ruta->id }}"
                    @selected($rutaId === (int) $ruta->id)
                >
                    {{ $ruta->codigo }} — {{ $ruta->nombre }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary">
            Filtrar
        </button>

        @if ($busqueda !== '' || $rutaId > 0)
            <a
                href="{{ route('promotor.mis-agentes.index') }}"
                class="btn btn-secondary"
            >
                Limpiar
            </a>
        @endif
    </form>
</div>

<div class="summary-grid">
    <div class="summary-card">
        <span>Agentes encontrados</span>
        <strong>{{ $agentes->total() }}</strong>
    </div>

    <div class="summary-card">
        <span>Rutas asignadas</span>
        <strong>{{ $rutasAsignadas->count() }}</strong>
    </div>

    <div class="summary-card">
        <span>Página actual</span>
        <strong>
            {{ $agentes->currentPage() }} / {{ $agentes->lastPage() }}
        </strong>
    </div>
</div>

<div class="agents-grid">
    @forelse ($agentes as $agente)
        <article class="agent-card">
            <div class="agent-card-header">
                <div class="agent-card-title">
                    <h3>{{ $agente->nombre_negocio }}</h3>

                    <span class="agent-code">
                        {{ $agente->codigo_agente }}
                    </span>
                </div>

                @if ($agente->estado !== 'ACTIVO')
                    <span class="status-badge status-inactive">
                        Inactivo
                    </span>
                @elseif ((int) $agente->arqueo_hoy > 0)
                    <span class="status-badge status-active">
                        Arqueo realizado
                    </span>
                @else
                    <span class="status-badge status-warning">
                        Pendiente de arqueo
                    </span>
                @endif
            </div>

            <div class="agent-card-body">
                <div class="detail-row">
                    <span class="detail-label">Propietario</span>

                    <span class="detail-value">
                        {{ $agente->nombre_propietario }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Ruta</span>

                    <span class="detail-value">
                        {{ $agente->ruta_codigo }}
                        — {{ $agente->ruta_nombre }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Región</span>

                    <span class="detail-value">
                        {{ $agente->region_nombre }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Dirección</span>

                    <span class="detail-value">
                        {{ $agente->direccion }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Arqueos</span>

                    <span class="detail-value">
                        {{ $agente->total_arqueos }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Último arqueo</span>

                    <span class="detail-value">
                        {{ $agente->ultimo_arqueo
                            ? \Carbon\Carbon::parse(
                                $agente->ultimo_arqueo
                            )->format('d/m/Y')
                            : 'Sin arqueos' }}
                    </span>
                </div>
            </div>

            <div class="agent-card-footer">
                <a
                    href="{{ route(
                        'promotor.arqueos-agentes.arqueos',
                        $agente->id
                    ) }}"
                    class="btn btn-arqueos"
                >
                    Ver arqueos
                </a>
            </div>
        </article>
    @empty
        <div class="empty-state">
            No se encontraron agentes en las rutas asignadas.
        </div>
    @endforelse
</div>

@if ($agentes->hasPages())
    <div class="pagination-wrapper">
        {{ $agentes->links() }}
    </div>
@endif
@endsection
