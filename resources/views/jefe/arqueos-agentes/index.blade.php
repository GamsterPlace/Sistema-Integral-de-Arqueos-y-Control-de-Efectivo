@extends('layouts.jefe')

@section('title', 'Arqueos por Agente')
@section('module-title', 'Arqueos por Agente')

@push('styles')
<style>
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
            minmax(220px, 1fr)
            220px
            170px
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

    .arqueos-table {
        width: 100%;
        min-width: 1250px;
        border-collapse: collapse;
    }

    .arqueos-table th,
    .arqueos-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        font-size: 10px;
    }

    .arqueos-table th {
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
        background: #edf2f6;
        color: #50667a;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: 1px solid #d5dfe5;
        border-radius: 9px;
        background: #ffffff;
        color: #31536e;
        text-decoration: none;
        transition: .2s ease;
    }

    .icon-button:hover {
        transform: translateY(-1px);
        border-color: #9db6c9;
        background: #f4f8fb;
        color: #164c96;
        box-shadow: 0 6px 14px rgba(24, 66, 99, .10);
    }

    .icon-button.print {
        border-color: #c8d7e3;
        background: #edf5fb;
        color: #164c96;
    }

    .icon-button svg {
        width: 17px;
        height: 17px;
        stroke: currentColor;
        stroke-width: 1.9;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
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

    @media (max-width: 1200px) {
        .filters-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 700px) {
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
        <h2>Ver Arqueos por Agente</h2>

        <p>
            Consulte el historial de arqueos de cualquier agente,
            incluyendo arqueos propios y visitas realizadas por Promotores.
        </p>
    </div>
</div>

<section class="filters-card">
    <form method="GET" action="{{ route('jefe.arqueos-agentes.index') }}">
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                class="form-control"
                value="{{ $buscar }}"
                placeholder="Número, código o negocio"
            >

            <select name="agente_id" class="form-control">
                <option value="">Todos los agentes</option>

                @foreach ($agentes as $agente)
                    <option
                        value="{{ $agente->id }}"
                        @selected($agenteId === (int) $agente->id)
                    >
                        {{ $agente->codigo_agente }}
                        — {{ $agente->nombre_negocio }}
                    </option>
                @endforeach
            </select>

            <select name="tipo" class="form-control">
                <option value="">Todos los tipos</option>
                <option
                    value="DIARIO_AGENTE"
                    @selected($tipo === 'DIARIO_AGENTE')
                >
                    Arqueo del Agente
                </option>
                <option
                    value="VISITA_PROMOTOR"
                    @selected($tipo === 'VISITA_PROMOTOR')
                >
                    Arqueo del Promotor
                </option>
            </select>

            <select name="estado" class="form-control">
                <option value="">Todos los estados</option>
                <option
                    value="PENDIENTE_CERTIFICACION"
                    @selected($estado === 'PENDIENTE_CERTIFICACION')
                >
                    Pendiente certificación
                </option>
                <option
                    value="CERTIFICADO"
                    @selected($estado === 'CERTIFICADO')
                >
                    Certificado
                </option>
                <option
                    value="ANULADO"
                    @selected($estado === 'ANULADO')
                >
                    Anulado
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
                href="{{ route('jefe.arqueos-agentes.index') }}"
                class="btn btn-secondary"
            >
                Limpiar
            </a>

            <button type="submit" class="btn btn-primary">
                Aplicar filtros
            </button>
        </div>
    </form>
</section>

<section class="table-card">
    <header class="table-header">
        <h3>Historial de arqueos</h3>

        <p>
            Resultados ordenados del más reciente al más antiguo.
        </p>
    </header>

    <div class="table-responsive">
        @if ($arqueos->isEmpty())
            <div class="empty-state">
                No se encontraron arqueos para los filtros seleccionados.
            </div>
        @else
            <table class="arqueos-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Agente</th>
                        <th>Región</th>
                        <th>Ruta</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Extemporáneo</th>
                        <th>Total</th>
                        <th>Diferencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($arqueos as $arqueo)
                        <tr>
                            <td>
                                <strong>
                                    {{ $arqueo->numero_arqueo }}
                                </strong>
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse(
                                    $arqueo->fecha_arqueo
                                )->format('d/m/Y') }}
                            </td>

                            <td>
                                {{ $arqueo->codigo_agente }}
                                — {{ $arqueo->nombre_negocio }}
                            </td>

                            <td>
                                {{ $arqueo->region_nombre ?? '—' }}
                            </td>

                            <td>
                                {{ $arqueo->ruta_nombre ?? '—' }}
                            </td>

                            <td>
                                <span class="badge">
                                    {{ $arqueo->tipo === 'DIARIO_AGENTE'
                                        ? 'Agente'
                                        : 'Promotor' }}
                                </span>
                            </td>

                            <td>
                                <span class="badge">
                                    {{ str_replace(
                                        '_',
                                        ' ',
                                        $arqueo->estado
                                    ) }}
                                </span>
                            </td>

                            <td>
                                {{ $arqueo->fuera_fecha_ordinaria
                                    ? 'Sí'
                                    : 'No' }}
                            </td>

                            <td>
                                Q {{ number_format(
                                    (float) $arqueo->total_arqueado,
                                    2
                                ) }}
                            </td>

                            <td>
                                Q {{ number_format(
                                    (float) $arqueo->diferencia,
                                    2
                                ) }}
                            </td>

                            <td class="actions-cell">
                                <div class="table-actions">

                                    <a
                                        href="{{ route(
                                            'jefe.arqueos-agentes.show',
                                            $arqueo->id
                                        ) }}"
                                        class="icon-button"
                                        title="Ver detalle"
                                        aria-label="Ver detalle del arqueo"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="2.8"></circle>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route(
                                            'jefe.arqueos-agentes.imprimir',
                                            $arqueo->id
                                        ) }}"
                                        target="_blank"
                                        class="icon-button print"
                                        title="Imprimir PDF"
                                        aria-label="Imprimir arqueo en PDF"
                                    >
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

    @if ($arqueos->hasPages())
        <div class="pagination">
            {{ $arqueos->links() }}
        </div>
    @endif
</section>
@endsection
