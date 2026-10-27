@extends('layouts.promotor')

@section('title', 'Rutas Asignadas')
@section('module-title', 'Rutas Asignadas')

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
        display: grid;
        grid-template-columns: minmax(260px, 1fr) auto auto;
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

    .routes-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .route-card {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
    }

    .route-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        padding: 17px 18px;
        border-bottom: 1px solid #e8edf1;
        background: #f8fafb;
    }

    .route-card-title h3 {
        margin: 0;
        color: #173f66;
        font-size: 18px;
        font-weight: 800;
    }

    .route-code {
        display: block;
        margin-top: 5px;
        color: #6d7f90;
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
    }

    .status-active {
        border: 1px solid #b9e1c8;
        background: #e8f7ee;
        color: #247048;
    }

    .status-inactive {
        border: 1px solid #e3aaa5;
        background: #ffe8e6;
        color: #a93b35;
    }

    .route-card-body {
        padding: 18px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .info-item {
        padding: 13px;
        border: 1px solid #e4ebf0;
        border-radius: 11px;
        background: #fbfcfd;
    }

    .info-item span {
        display: block;
        color: #718394;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .info-item strong {
        display: block;
        margin-top: 5px;
        color: #173f66;
        overflow-wrap: anywhere;
    }

    .progress-section {
        margin-top: 17px;
        padding-top: 16px;
        border-top: 1px solid #edf1f4;
    }

    .progress-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .progress-header span {
        color: #65788a;
        font-size: 12px;
        font-weight: 800;
    }

    .progress-header strong {
        color: #173f66;
    }

    .progress-track {
        height: 10px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf2f6;
    }

    .progress-bar {
        height: 100%;
        border-radius: 999px;
        background: #00a651;
    }

    .route-card-footer {
        display: flex;
        justify-content: flex-end;
        padding: 14px 18px;
        border-top: 1px solid #e8edf1;
        background: #fbfcfd;
    }

    .btn-agents {
        min-height: 38px;
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

    @media (max-width: 900px) {
        .routes-grid {
            grid-template-columns: 1fr;
        }

        .search-form {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 560px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div class="page-title">
        <h2>Rutas Asignadas</h2>

        <p>
            Consulte las rutas que tiene asignadas actualmente y el avance
            diario de los agentes que pertenecen a cada una.
        </p>
    </div>
</div>

<div class="search-card">
    <form
        method="GET"
        action="{{ route('promotor.rutas-asignadas.index') }}"
        class="search-form"
    >
        <input
            type="text"
            name="buscar"
            class="search-input"
            value="{{ $busqueda }}"
            placeholder="Buscar por código, nombre de ruta o región"
        >

        <button type="submit" class="btn btn-primary">
            Buscar
        </button>

        @if ($busqueda !== '')
            <a
                href="{{ route('promotor.rutas-asignadas.index') }}"
                class="btn btn-secondary"
            >
                Limpiar
            </a>
        @endif
    </form>
</div>

<div class="routes-grid">
    @forelse ($rutas as $ruta)
        @php
            $porcentaje = (int) $ruta->agentes_activos > 0
                ? round(
                    ((int) $ruta->agentes_con_arqueo_hoy
                    / (int) $ruta->agentes_activos) * 100
                )
                : 0;
        @endphp

        <article class="route-card">
            <div class="route-card-header">
                <div class="route-card-title">
                    <h3>{{ $ruta->nombre }}</h3>

                    <span class="route-code">
                        {{ $ruta->codigo }}
                    </span>
                </div>

                <span class="status-badge {{ $ruta->estado
                    ? 'status-active'
                    : 'status-inactive' }}"
                >
                    {{ $ruta->estado ? 'Activa' : 'Inactiva' }}
                </span>
            </div>

            <div class="route-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span>Región</span>
                        <strong>{{ $ruta->region_nombre }}</strong>
                    </div>

                    <div class="info-item">
                        <span>Agentes activos</span>
                        <strong>{{ $ruta->agentes_activos }}</strong>
                    </div>

                    <div class="info-item">
                        <span>Total de agentes</span>
                        <strong>{{ $ruta->total_agentes }}</strong>
                    </div>

                    <div class="info-item">
                        <span>Asignada desde</span>

                        <strong>
                            {{ \Carbon\Carbon::parse(
                                $ruta->fecha_inicio
                            )->format('d/m/Y') }}
                        </strong>
                    </div>

                    <div class="info-item">
                        <span>Fecha de finalización</span>

                        <strong>
                            {{ $ruta->fecha_fin
                                ? \Carbon\Carbon::parse(
                                    $ruta->fecha_fin
                                )->format('d/m/Y')
                                : 'Sin fecha definida' }}
                        </strong>
                    </div>

                    <div class="info-item">
                        <span>Arqueos realizados hoy</span>

                        <strong>
                            {{ $ruta->agentes_con_arqueo_hoy }}
                            de {{ $ruta->agentes_activos }}
                        </strong>
                    </div>
                </div>

                <div class="progress-section">
                    <div class="progress-header">
                        <span>Avance de arqueos diarios</span>
                        <strong>{{ $porcentaje }}%</strong>
                    </div>

                    <div class="progress-track">
                        <div
                            class="progress-bar"
                            style="width: {{ min($porcentaje, 100) }}%;"
                        ></div>
                    </div>
                </div>
            </div>

            <div class="route-card-footer">
                <a
                    href="{{ route(
                        'promotor.mis-agentes.index',
                        ['ruta_id' => $ruta->id]
                    ) }}"
                    class="btn btn-agents"
                >
                    Ver agentes de la ruta
                </a>
            </div>
        </article>
    @empty
        <div class="empty-state">
            No se encontraron rutas asignadas actualmente.
        </div>
    @endforelse
</div>

@if ($rutas->hasPages())
    <div class="pagination-wrapper">
        {{ $rutas->links() }}
    </div>
@endif
@endsection
