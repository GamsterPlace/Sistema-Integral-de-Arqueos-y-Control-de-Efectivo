@extends('layouts.promotor')

@section('title', 'Cumplimientos')
@section('module-title', 'Cumplimientos')

@push('styles')
<style>
    .compliance-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        padding: 18px;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .month-navigation {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .month-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid #d7e0e7;
        border-radius: 10px;
        background: #ffffff;
        color: #31536e;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        transition: .2s ease;
    }

    .month-button:hover {
        border-color: #9db6c9;
        background: #f4f8fb;
        color: #164c96;
    }

    .month-button.today {
        border-color: #cfe9d9;
        background: #effaf3;
        color: #197245;
    }

    .month-title {
        text-align: right;
    }

    .month-title strong {
        display: block;
        color: #082d55;
        font-size: 18px;
        text-transform: capitalize;
    }

    .month-title span {
        display: block;
        margin-top: 4px;
        color: #7b8a95;
        font-size: 11px;
    }

    .filters-panel {
        margin-bottom: 20px;
        padding: 18px;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr) auto;
        gap: 14px;
        align-items: end;
    }

    .filter-group label {
        display: block;
        margin-bottom: 7px;
        color: #677884;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .filter-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #d7e0e7;
        border-radius: 10px;
        background: #ffffff;
        color: #28465e;
        outline: none;
    }

    .filter-control:focus {
        border-color: #7da8cf;
        box-shadow: 0 0 0 3px rgba(22, 76, 150, .08);
    }

    .filter-actions {
        display: flex;
        gap: 9px;
    }

    .filter-button {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 16px;
        border: 0;
        border-radius: 10px;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
    }

    .filter-button.clear {
        border: 1px solid #d7e0e7;
        background: #ffffff;
        color: #536b7c;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .summary-card {
        padding: 17px;
        border: 1px solid #e0e8ee;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .045);
    }

    .summary-card span {
        display: block;
        color: #778893;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .summary-card strong {
        display: block;
        margin-top: 8px;
        color: #082d55;
        font-size: 23px;
    }

    .summary-card.success {
        border-top: 3px solid #28a567;
    }

    .summary-card.warning {
        border-top: 3px solid #e7a93f;
    }

    .summary-card.danger {
        border-top: 3px solid #d95b5b;
    }

    .summary-card.purple {
        border-top: 3px solid #8b67c7;
    }

    .summary-card.muted {
        border-top: 3px solid #8b99a4;
    }

    .summary-card.blue {
        border-top: 3px solid #2d74ba;
    }

    .calendar-panel {
        margin-bottom: 24px;
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid #edf1f4;
    }

    .panel-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 16px;
    }

    .panel-header p {
        margin: 5px 0 0;
        color: #82909a;
        font-size: 11px;
    }

    .calendar-wrapper {
        overflow-x: auto;
    }

    .calendar-grid {
        min-width: 980px;
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
    }

    .calendar-weekday {
        padding: 12px;
        border-right: 1px solid #edf1f4;
        border-bottom: 1px solid #edf1f4;
        background: #f7f9fb;
        color: #6c7c88;
        font-size: 10px;
        font-weight: 800;
        text-align: center;
        letter-spacing: .4px;
        text-transform: uppercase;
    }

    .calendar-weekday:nth-child(7n) {
        border-right: 0;
    }

    .calendar-empty,
    .calendar-day {
        min-height: 150px;
        padding: 11px;
        border-right: 1px solid #edf1f4;
        border-bottom: 1px solid #edf1f4;
    }

    .calendar-empty {
        background: #fafbfc;
    }

    .calendar-day:nth-child(7n) {
        border-right: 0;
    }

    .calendar-day.future {
        background: #fafbfc;
    }

    .calendar-day.today {
        box-shadow: inset 0 0 0 2px rgba(22, 76, 150, .28);
    }

    .calendar-day-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }

    .calendar-day-number {
        color: #173d5d;
        font-size: 14px;
        font-weight: 800;
    }

    .today-badge {
        padding: 4px 7px;
        border-radius: 999px;
        background: #eaf2fb;
        color: #164c96;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .future-label {
        color: #9aa7b0;
        font-size: 10px;
        font-weight: 700;
    }

    .day-stats {
        display: grid;
        gap: 5px;
    }

    .day-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 6px 8px;
        border-radius: 8px;
        font-size: 9px;
        font-weight: 750;
    }

    .day-stat.success {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .day-stat.warning {
        background: #fff5dc;
        color: #9c7014;
    }

    .day-stat.danger {
        background: #fdecec;
        color: #b13c3c;
    }

    .day-stat.purple {
        background: #f2edfb;
        color: #714ba8;
    }

    .day-stat.muted {
        background: #eef1f3;
        color: #63717c;
    }

    .agent-day-status {
        padding: 10px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 800;
    }

    .agent-day-status.success {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .agent-day-status.warning {
        background: #fff5dc;
        color: #9c7014;
    }

    .agent-day-status.danger {
        background: #fdecec;
        color: #b13c3c;
    }

    .agent-day-status.purple {
        background: #f2edfb;
        color: #714ba8;
    }

    .agent-day-status.muted {
        background: #eef1f3;
        color: #63717c;
    }

    .agent-day-status small {
        display: block;
        margin-top: 5px;
        font-size: 8px;
        font-weight: 700;
        opacity: .82;
    }

    .mini-eye {
        width: 30px;
        height: 30px;
        margin-top: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #c8d7e3;
        border-radius: 8px;
        background: #edf5fb;
        color: #164c96;
        text-decoration: none;
    }

    .mini-eye svg {
        width: 15px;
        height: 15px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
    }

    .table-panel {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .table-responsive {
        overflow-x: auto;
    }

    .visits-table {
        width: 100%;
        min-width: 1100px;
        border-collapse: collapse;
    }

    .visits-table th {
        padding: 13px 15px;
        background: #f7f9fb;
        color: #6c7c88;
        font-size: 9px;
        font-weight: 800;
        text-align: left;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .visits-table td {
        padding: 14px 15px;
        border-top: 1px solid #edf1f4;
        color: #30485b;
        font-size: 11px;
        vertical-align: middle;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-badge.pendiente {
        background: #fff5dc;
        color: #9c7014;
    }

    .status-badge.certificado {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .status-badge.anulado {
        background: #fdecec;
        color: #b13c3c;
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
        border: 1px solid #d5dfe5;
        border-radius: 9px;
        background: #ffffff;
        color: #31536e;
        text-decoration: none;
        transition: .2s ease;
    }

    .icon-button.print {
        border-color: #c8d7e3;
        background: #edf5fb;
        color: #164c96;
    }

    .icon-button:hover {
        transform: translateY(-1px);
    }

    .icon-button svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .empty-record {
        padding: 42px 20px;
        color: #7d8b95;
        text-align: center;
        font-size: 12px;
    }

    .pagination-container {
        padding: 16px 18px;
        border-top: 1px solid #edf1f4;
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .filters-grid {
            grid-template-columns: 1fr 1fr;
        }

        .filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .compliance-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .month-title {
            text-align: left;
        }

        .filters-grid,
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            grid-column: auto;
            flex-direction: column;
        }

        .filter-button {
            width: 100%;
        }

        .month-navigation {
            flex-wrap: wrap;
        }
    }
</style>
@endpush

@section('content')

@php
    $tituloMes = $inicioMes
        ->copy()
        ->locale('es')
        ->translatedFormat('F Y');

    $queryAnterior = array_filter([
        'mes' => $mesAnterior,
        'ruta_id' => $rutaId > 0 ? $rutaId : null,
        'agente_id' => $agenteId > 0 ? $agenteId : null,
    ]);

    $querySiguiente = array_filter([
        'mes' => $mesSiguiente,
        'ruta_id' => $rutaId > 0 ? $rutaId : null,
        'agente_id' => $agenteId > 0 ? $agenteId : null,
    ]);

    $queryActual = array_filter([
        'mes' => $mesActual,
        'ruta_id' => $rutaId > 0 ? $rutaId : null,
        'agente_id' => $agenteId > 0 ? $agenteId : null,
    ]);

    $agenteSeleccionado = $agenteId > 0
        ? $agentesDisponibles->first(
            fn ($agente) => (int) $agente->id === (int) $agenteId
        )
        : null;
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Cumplimientos</h2>

        <p>
            Consulte el cumplimiento de los agentes de sus rutas asignadas
            y los arqueos de visita realizados por usted.
        </p>
    </div>

    <span class="page-badge">
        <span class="page-badge-dot"></span>
        Control del Promotor
    </span>
</div>

<section class="compliance-toolbar">
    <div class="month-navigation">
        <a
            href="{{ route('promotor.cumplimientos.index', $queryAnterior) }}"
            class="month-button"
        >
            ← Anterior
        </a>

        <a
            href="{{ route('promotor.cumplimientos.index', $queryActual) }}"
            class="month-button today"
        >
            Hoy
        </a>

        <a
            href="{{ route('promotor.cumplimientos.index', $querySiguiente) }}"
            class="month-button"
        >
            Siguiente →
        </a>
    </div>

    <div class="month-title">
        <strong>{{ $tituloMes }}</strong>
        <span>Período consultado</span>
    </div>
</section>

<section class="filters-panel">
    <form
        method="GET"
        action="{{ route('promotor.cumplimientos.index') }}"
    >
        <input
            type="hidden"
            name="mes"
            value="{{ $mes }}"
        >

        <div class="filters-grid">
            <div class="filter-group">
                <label for="ruta_id">
                    Ruta
                </label>

                <select
                    name="ruta_id"
                    id="ruta_id"
                    class="filter-control"
                >
                    <option value="">
                        Todas mis rutas
                    </option>

                    @foreach ($rutasAsignadas as $ruta)
                        <option
                            value="{{ $ruta->id }}"
                            @selected((int) $rutaId === (int) $ruta->id)
                        >
                            {{ $ruta->codigo }}
                            —
                            {{ $ruta->nombre }}
                            ·
                            {{ $ruta->region_nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="agente_id">
                    Agente
                </label>

                <select
                    name="agente_id"
                    id="agente_id"
                    class="filter-control"
                >
                    <option value="">
                        Todos los agentes
                    </option>

                    @foreach ($agentesDisponibles as $agente)
                        <option
                            value="{{ $agente->id }}"
                            @selected((int) $agenteId === (int) $agente->id)
                        >
                            {{ $agente->codigo_agente }}
                            —
                            {{ $agente->nombre_negocio }}
                            ·
                            {{ $agente->ruta_nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions">
                <button
                    type="submit"
                    class="filter-button"
                >
                    Aplicar filtros
                </button>

                <a
                    href="{{ route('promotor.cumplimientos.index', ['mes' => $mes]) }}"
                    class="filter-button clear"
                >
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</section>

<section class="summary-grid">
    <article class="summary-card success">
        <span>Cumplidos</span>
        <strong>{{ $resumenMes['cumplidos'] }}</strong>
    </article>

    <article class="summary-card warning">
        <span>Sin arqueo</span>
        <strong>{{ $resumenMes['sin_arqueo'] }}</strong>
    </article>

    <article class="summary-card danger">
        <span>No atendió</span>
        <strong>{{ $resumenMes['no_atendio'] }}</strong>
    </article>

    <article class="summary-card purple">
        <span>Extemporáneos</span>
        <strong>{{ $resumenMes['extemporaneos'] }}</strong>
    </article>

    <article class="summary-card muted">
        <span>Anulados</span>
        <strong>{{ $resumenMes['anulados'] }}</strong>
    </article>

    <article class="summary-card blue">
        <span>Visitas realizadas</span>
        <strong>{{ $totalVisitasMes }}</strong>
    </article>
</section>

<section class="calendar-panel">
    <header class="panel-header">
        <div>
            <h3>
                @if ($agenteSeleccionado)
                    Cumplimiento de {{ $agenteSeleccionado->nombre_negocio }}
                @else
                    Cumplimiento de mis agentes
                @endif
            </h3>

            <p>
                @if ($agenteSeleccionado)
                    {{ $agenteSeleccionado->codigo_agente }}
                    ·
                    {{ $agenteSeleccionado->ruta_nombre }}
                    ·
                    {{ $agenteSeleccionado->region_nombre }}
                @else
                    Resumen diario de agentes correspondientes a sus rutas asignadas.
                @endif
            </p>
        </div>
    </header>

    <div class="calendar-wrapper">
        <div class="calendar-grid">
            @foreach ([
                'Lunes',
                'Martes',
                'Miércoles',
                'Jueves',
                'Viernes',
                'Sábado',
                'Domingo'
            ] as $diaSemana)
                <div class="calendar-weekday">
                    {{ $diaSemana }}
                </div>
            @endforeach

            @for ($i = 1; $i < $inicioMes->dayOfWeekIso; $i++)
                <div class="calendar-empty"></div>
            @endfor

            @foreach ($calendario as $dia)
                <div
                    class="calendar-day
                        {{ $dia['es_hoy'] ? 'today' : '' }}
                        {{ $dia['es_futuro'] ? 'future' : '' }}"
                >
                    <div class="calendar-day-header">
                        <span class="calendar-day-number">
                            {{ $dia['dia'] }}
                        </span>

                        @if ($dia['es_hoy'])
                            <span class="today-badge">
                                Hoy
                            </span>
                        @endif
                    </div>

                    @if ($dia['es_futuro'])
                        <div class="future-label">
                            Fecha futura
                        </div>
                    @elseif ($agenteSeleccionado)
                        @php
                            $detalle = $dia['agente'];

                            $clase = match ($detalle['categoria'] ?? 'SIN_ARQUEO') {
                                'CUMPLIDO' => 'success',
                                'NO_ATENDIO' => 'danger',
                                'EXTEMPORANEO' => 'purple',
                                'ANULADO' => 'muted',
                                default => 'warning',
                            };
                        @endphp

                        @if ($detalle)
                            <div class="agent-day-status {{ $clase }}">
                                {{ $detalle['texto'] }}

                                @if ($detalle['numero_arqueo'])
                                    <small>
                                        {{ $detalle['numero_arqueo'] }}
                                    </small>
                                @endif
                            </div>

                            @if ($detalle['url_arqueo'])
                                <a
                                    href="{{ $detalle['url_arqueo'] }}"
                                    class="mini-eye"
                                    title="Ver arqueo"
                                    aria-label="Ver arqueo"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                        <circle cx="12" cy="12" r="2.8"></circle>
                                    </svg>
                                </a>
                            @endif
                        @else
                            <div class="future-label">
                                Sin asignación para este día
                            </div>
                        @endif
                    @else
                        <div class="day-stats">
                            <div class="day-stat success">
                                <span>Cumplidos</span>
                                <strong>{{ $dia['totales']['cumplidos'] }}</strong>
                            </div>

                            <div class="day-stat warning">
                                <span>Sin arqueo</span>
                                <strong>{{ $dia['totales']['sin_arqueo'] }}</strong>
                            </div>

                            <div class="day-stat danger">
                                <span>No atendió</span>
                                <strong>{{ $dia['totales']['no_atendio'] }}</strong>
                            </div>

                            <div class="day-stat purple">
                                <span>Extemporáneos</span>
                                <strong>{{ $dia['totales']['extemporaneos'] }}</strong>
                            </div>

                            <div class="day-stat muted">
                                <span>Anulados</span>
                                <strong>{{ $dia['totales']['anulados'] }}</strong>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="table-panel">
    <header class="panel-header">
        <div>
            <h3>Mis arqueos realizados</h3>

            <p>
                Arqueos de visita realizados por usted durante {{ $tituloMes }}.
            </p>
        </div>
    </header>

    <div class="table-responsive">
        @if ($visitasPromotor->isEmpty())
            <div class="empty-record">
                No realizó arqueos de visita durante este período.
            </div>
        @else
            <table class="visits-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Agente</th>
                        <th>Propietario</th>
                        <th>Ruta</th>
                        <th>Región</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Diferencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($visitasPromotor as $visita)
                        @php
                            $claseEstado = match ($visita->estado) {
                                'CERTIFICADO' => 'certificado',
                                'ANULADO' => 'anulado',
                                default => 'pendiente',
                            };

                            $textoEstado = match ($visita->estado) {
                                'PENDIENTE_CERTIFICACION' => 'Pendiente',
                                'CERTIFICADO' => 'Certificado',
                                'ANULADO' => 'Anulado',
                                default => $visita->estado,
                            };
                        @endphp

                        <tr>
                            <td>
                                <strong>
                                    {{ $visita->numero_arqueo }}
                                </strong>
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($visita->fecha_arqueo)->format('d/m/Y') }}
                            </td>

                            <td>
                                {{ $visita->codigo_agente_historico }}
                                <br>
                                <strong>
                                    {{ $visita->nombre_negocio_historico }}
                                </strong>
                            </td>

                            <td>
                                {{ $visita->nombre_propietario_historico }}
                            </td>

                            <td>
                                {{ $visita->ruta_historica }}
                            </td>

                            <td>
                                {{ $visita->region_historica }}
                            </td>

                            <td>
                                <span class="status-badge {{ $claseEstado }}">
                                    {{ $textoEstado }}
                                </span>
                            </td>

                            <td>
                                Q {{ number_format((float) $visita->total_arqueado, 2) }}
                            </td>

                            <td>
                                @if ((float) $visita->diferencia > 0)
                                    <strong style="color:#1d7b4e;">
                                        +Q {{ number_format((float) $visita->diferencia, 2) }}
                                    </strong>
                                @elseif ((float) $visita->diferencia < 0)
                                    <strong style="color:#c0392b;">
                                        Q {{ number_format((float) $visita->diferencia, 2) }}
                                    </strong>
                                @else
                                    <strong style="color:#0a3158;">
                                        Q 0.00
                                    </strong>
                                @endif
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="{{ route('promotor.arqueos.show', $visita->id) }}"
                                        class="icon-button"
                                        title="Ver detalle"
                                        aria-label="Ver detalle del arqueo"
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="2.8"></circle>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('promotor.arqueos.imprimir', $visita->id) }}"
                                        target="_blank"
                                        class="icon-button print"
                                        title="Imprimir PDF"
                                        aria-label="Imprimir arqueo en PDF"
                                    >
                                        <svg viewBox="0 0 24 24">
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

    @if ($visitasPromotor->hasPages())
        <div class="pagination-container">
            {{ $visitasPromotor->links() }}
        </div>
    @endif
</section>

@endsection
