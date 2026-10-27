@extends('layouts.gerencia')

@section('title', 'Reportes')
@section('module-title', 'Reportes')

@push('styles')
<style>
    .report-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .report-option {
        display: block;
        padding: 17px;
        border: 1px solid #dfe7ee;
        border-radius: 16px;
        background: #ffffff;
        text-decoration: none;
        transition: .2s ease;
    }

    .report-option:hover {
        transform: translateY(-2px);
        border-color: #b9cfe4;
        box-shadow: 0 10px 24px rgba(21, 68, 112, .08);
    }

    .report-option.active {
        border-color: #164c96;
        background: #f2f7fd;
        box-shadow: inset 0 0 0 1px #164c96;
    }

    .report-option strong {
        display: block;
        color: #0a3158;
        font-size: 12px;
    }

    .report-option.active strong {
        color: #164c96;
    }

    .report-option span {
        display: block;
        margin-top: 6px;
        color: #7b8b98;
        font-size: 9px;
        line-height: 1.45;
    }

    .filters-card,
    .table-card {
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .filters-card {
        padding: 18px;
        margin-bottom: 20px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 11px;
    }

    .filters-grid + .filters-grid {
        margin-top: 11px;
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #ffffff;
        color: #30485b;
        box-sizing: border-box;
        outline: none;
    }

    .form-control:focus {
        border-color: #8eb0cb;
        box-shadow: 0 0 0 3px rgba(22, 76, 150, .08);
    }

    .filters-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 14px;
        flex-wrap: wrap;
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
        transition: .2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        background: #164c96;
        color: #ffffff;
    }

    .btn-secondary {
        background: #edf2f5;
        color: #3d596f;
    }

    .btn-print {
        background: #16834f;
        color: #ffffff;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .metric-card {
        padding: 15px;
        border: 1px solid #e1e8ed;
        border-radius: 14px;
        background: #ffffff;
    }

    .metric-card span {
        display: block;
        color: #758697;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .metric-card strong {
        display: block;
        margin-top: 7px;
        color: #082d55;
        font-size: 21px;
    }

    .metric-card small {
        display: block;
        margin-top: 5px;
        color: #718493;
        font-size: 9px;
        font-weight: 700;
    }

    .metric-card.danger {
        border-color: #efc5c5;
        background: #fffafa;
    }

    .metric-card.success {
        border-color: #c5e5d1;
        background: #f7fcf9;
    }

    .metric-card.warning {
        border-color: #efd99f;
        background: #fffdf6;
    }

    .table-card {
        overflow: hidden;
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
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

    .report-table {
        width: 100%;
        min-width: 1050px;
        border-collapse: collapse;
    }

    .report-table th,
    .report-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        font-size: 10px;
    }

    .report-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .report-table tbody tr:hover {
        background: #fbfdfe;
    }

    .money-negative {
        color: #b33a34;
        font-weight: 800;
    }

    .money-positive {
        color: #16834f;
        font-weight: 800;
    }

    .money-neutral {
        color: #607586;
        font-weight: 800;
    }

    @media (max-width: 1200px) {
        .report-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .metrics-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .filters-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .report-grid,
        .metrics-grid,
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
        <h2>Centro de Reportes de Gerencia</h2>

        <p>
            Analice comportamiento, incidencias, diferencias y desempeño operativo
            con una vista ejecutiva y filtros por período, Región, Ruta, Agente y Promotor.
        </p>
    </div>
</div>

<section class="report-grid">
    @foreach($tiposReporte as $clave => $nombre)
        <a
            href="{{ route('gerencia.reportes.index', array_merge(request()->except('page'), ['tipo_reporte' => $clave])) }}"
            class="report-option {{ $tipoReporte === $clave ? 'active' : '' }}"
        >
            <strong>{{ $nombre }}</strong>

            <span>
                Consulte la información correspondiente a este reporte.
            </span>
        </a>
    @endforeach
</section>

<section class="filters-card">
    <form method="GET" action="{{ route('gerencia.reportes.index') }}">
        <input type="hidden" name="tipo_reporte" value="{{ $tipoReporte }}">

        <div class="filters-grid">
            <input
                type="date"
                name="desde"
                value="{{ $desde }}"
                class="form-control"
            >

            <input
                type="date"
                name="hasta"
                value="{{ $hasta }}"
                class="form-control"
            >

            <select name="region_id" class="form-control">
                <option value="">Todas las Regiones</option>

                @foreach($regiones as $region)
                    <option
                        value="{{ $region->id }}"
                        @selected($regionId === (int) $region->id)
                    >
                        {{ $region->nombre }}
                    </option>
                @endforeach
            </select>

            <select name="ruta_id" class="form-control">
                <option value="">Todas las Rutas</option>

                @foreach($rutas as $ruta)
                    <option
                        value="{{ $ruta->id }}"
                        @selected($rutaId === (int) $ruta->id)
                    >
                        {{ $ruta->codigo }} — {{ $ruta->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filters-grid">
            <select name="agente_id" class="form-control">
                <option value="">Todos los Agentes</option>

                @foreach($agentes as $agente)
                    <option
                        value="{{ $agente->id }}"
                        @selected($agenteId === (int) $agente->id)
                    >
                        {{ $agente->codigo_agente }} — {{ $agente->nombre_negocio }}
                    </option>
                @endforeach
            </select>

            <select name="promotor_id" class="form-control">
                <option value="">Todos los Promotores</option>

                @foreach($promotores as $promotor)
                    @php
                        $nombrePromotor = trim(($promotor->nombres ?? '') . ' ' . ($promotor->apellidos ?? ''));
                    @endphp

                    <option
                        value="{{ $promotor->id }}"
                        @selected($promotorId === (int) $promotor->id)
                    >
                        {{ $nombrePromotor !== '' ? $nombrePromotor : $promotor->usuario }}
                    </option>
                @endforeach
            </select>

            <select name="tipo_arqueo" class="form-control">
                <option value="">Todos los tipos de arqueo</option>

                <option
                    value="DIARIO_AGENTE"
                    @selected($tipoArqueo === 'DIARIO_AGENTE')
                >
                    Agente
                </option>

                <option
                    value="VISITA_PROMOTOR"
                    @selected($tipoArqueo === 'VISITA_PROMOTOR')
                >
                    Promotor
                </option>
            </select>

            <div></div>
        </div>

        <div class="filters-actions">
            <a
                href="{{ route('gerencia.reportes.index', ['tipo_reporte' => $tipoReporte]) }}"
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

            <a
                href="{{ route('gerencia.reportes.imprimir', request()->query()) }}"
                target="_blank"
                class="btn btn-print"
            >
                Imprimir Reporte
            </a>
        </div>
    </form>
</section>

<section class="metrics-grid">
    <article class="metric-card">
        <span>Total Arqueos</span>
        <strong>{{ $metricas->total }}</strong>
    </article>

    <article class="metric-card danger">
        <span>Faltantes</span>
        <strong>{{ $metricas->faltantes }}</strong>
        <small>Q {{ number_format($metricas->monto_faltantes, 2) }}</small>
    </article>

    <article class="metric-card success">
        <span>Sobrantes</span>
        <strong>{{ $metricas->sobrantes }}</strong>
        <small>Q {{ number_format($metricas->monto_sobrantes, 2) }}</small>
    </article>

    <article class="metric-card success">
        <span>Exactos</span>
        <strong>{{ $metricas->exactos }}</strong>
    </article>

    <article class="metric-card warning">
        <span>Extemporáneos</span>
        <strong>{{ $metricas->extemporaneos }}</strong>
    </article>
</section>

<section class="table-card">
    <header class="table-header">
        <div>
            <h3>{{ $tiposReporte[$tipoReporte] }}</h3>

            <p>
                Resultados según los filtros seleccionados. Para consultas específicas,
                use los filtros superiores antes de imprimir.
            </p>
        </div>
    </header>

    <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    @foreach($columnas as $titulo)
                        <th>{{ $titulo }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse($resultados as $fila)
                    <tr>
                        @foreach($columnas as $campo => $titulo)
                            @php
                                $valor = $fila->{$campo} ?? null;
                            @endphp

                            <td>
                                @if(in_array($campo, ['diferencia', 'monto'], true))
                                    @php
                                        $numero = (float) $valor;

                                        $clase = $numero < 0
                                            ? 'money-negative'
                                            : ($numero > 0
                                                ? 'money-positive'
                                                : 'money-neutral');
                                    @endphp

                                    <span class="{{ $clase }}">
                                        Q {{ number_format(abs($numero), 2) }}
                                    </span>
                                @elseif($campo === 'fecha_arqueo' && $valor)
                                    {{ \Carbon\Carbon::parse($valor)->format('d/m/Y') }}
                                @elseif($campo === 'tipo' && $valor)
                                    {{ $valor === 'DIARIO_AGENTE'
                                        ? 'Agente'
                                        : ($valor === 'VISITA_PROMOTOR'
                                            ? 'Promotor'
                                            : str_replace('_', ' ', $valor)) }}
                                @else
                                    {{ $valor ?? '—' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="{{ count($columnas) }}"
                            style="padding:35px;text-align:center;"
                        >
                            No se encontraron resultados para este reporte.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
