@extends('layouts.promotor')

@section('title', 'Arqueos del Agente')
@section('module-title', 'Arqueos de Agentes')

@section('content')
<style>
    .back-link {
        display: inline-flex;
        margin-bottom: 18px;
        color: #174f8a;
        font-weight: 700;
        text-decoration: none;
    }

    .agent-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 22px;
        padding: 18px;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
    }

    .summary-item span {
        display: block;
        color: #738495;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .summary-item strong {
        display: block;
        margin-top: 5px;
        color: #173f66;
    }

    .filter-form {
        display: flex;
        gap: 12px;
        margin-bottom: 18px;
    }

    .filter-select {
        min-width: 250px;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #ffffff;
    }

    .btn-filter,
    .btn-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 15px;
        border: 0;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
    }

    .btn-filter {
        background: #174f8a;
        color: #ffffff;
    }

    .btn-view {
        min-height: 35px;
        background: #e9f2fb;
        color: #174f8a;
    }

    .table-card {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 13px 14px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
    }

    th {
        color: #627588;
        background: #f7f9fb;
        font-size: 12px;
        text-transform: uppercase;
    }

    .badge {
        display: inline-flex;
        min-height: 25px;
        padding: 0 9px;
        align-items: center;
        border-radius: 999px;
        background: #edf2f6;
        color: #50667a;
        font-size: 12px;
        font-weight: 800;
    }

    .signature-ok {
        color: #247048;
        font-weight: 800;
    }

    .signature-pending {
        color: #986b00;
        font-weight: 800;
    }

    .empty-state {
        padding: 40px;
        color: #758697;
        text-align: center;
    }

    .pagination-wrapper {
        padding: 16px;
    }

    @media (max-width: 900px) {
        .agent-summary {
            grid-template-columns: 1fr 1fr;
        }

        .table-card {
            overflow-x: auto;
        }

        table {
            min-width: 900px;
        }
    }
</style>

<a
    href="{{ route('promotor.arqueos-agentes.index') }}"
    class="back-link"
>
    ← Regresar a agentes
</a>

<div class="agent-summary">
    <div class="summary-item">
        <span>Código</span>
        <strong>{{ $agente->codigo_agente }}</strong>
    </div>

    <div class="summary-item">
        <span>Negocio</span>
        <strong>{{ $agente->nombre_negocio }}</strong>
    </div>

    <div class="summary-item">
        <span>Ruta</span>
        <strong>{{ $agente->ruta?->nombre }}</strong>
    </div>

    <div class="summary-item">
        <span>Región</span>
        <strong>{{ $agente->ruta?->region?->nombre }}</strong>
    </div>
</div>

<form method="GET" class="filter-form">
    <select name="estado" class="filter-select">
        <option value="">Todos los estados</option>

        @foreach ([
            'PENDIENTE_CERTIFICACION' => 'Pendiente de certificación',
            'CERTIFICADO' => 'Certificado',
            'ANULADO' => 'Anulado',
        ] as $valor => $texto)
            <option
                value="{{ $valor }}"
                @selected($estado === $valor)
            >
                {{ $texto }}
            </option>
        @endforeach
    </select>

    <button type="submit" class="btn-filter">
        Filtrar
    </button>
</form>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Número</th>
                <th>Fecha</th>
                <th>Total arqueado</th>
                <th>Diferencia</th>
                <th>Estado</th>
                <th>Firma agente</th>
                <th>Firma promotor</th>
                <th>Acción</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($arqueos as $arqueo)
                <tr>
                    <td>{{ $arqueo->numero_arqueo }}</td>

                    <td>
                        {{ $arqueo->fecha_arqueo->format('d/m/Y') }}
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

                    <td>
                        <span class="badge">
                            {{ str_replace('_', ' ', $arqueo->estado) }}
                        </span>
                    </td>

                    <td>
                        <span class="{{ $arqueo->firmado_agente
                            ? 'signature-ok'
                            : 'signature-pending' }}"
                        >
                            {{ $arqueo->firmado_agente
                                ? 'Firmado'
                                : 'Pendiente' }}
                        </span>
                    </td>

                    <td>
                        <span class="{{ $arqueo->firmado_promotor
                            ? 'signature-ok'
                            : 'signature-pending' }}"
                        >
                            {{ $arqueo->firmado_promotor
                                ? 'Firmado'
                                : 'Pendiente' }}
                        </span>
                    </td>

                    <td>
                        <a
                            href="{{ route(
                                'promotor.arqueos-agentes.show',
                                $arqueo
                            ) }}"
                            class="btn-view"
                        >
                            Visualizar
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            Este agente no tiene arqueos registrados.
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($arqueos->hasPages())
        <div class="pagination-wrapper">
            {{ $arqueos->links() }}
        </div>
    @endif
</div>
@endsection
