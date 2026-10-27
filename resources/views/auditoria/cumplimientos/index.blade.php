@extends('layouts.auditoria')

@section('title', 'Cumplimiento de Arqueos')
@section('module-title', 'Cumplimiento de Arqueos')

@push('styles')
<style>
    .compliance-page {
        display: grid;
        gap: 20px;
    }

    .compliance-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }

    .compliance-title h2 {
        margin: 0;
        color: #06284f;
        font-size: 28px;
    }

    .compliance-title p {
        margin: 8px 0 0;
        max-width: 820px;
        color: #6d7d8a;
        font-size: 13px;
        line-height: 1.6;
    }

    .filters-card,
    .calendar-card,
    .summary-card,
    .visits-card {
        border: 1px solid #e0e8ee;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .filters-card {
        padding: 18px;
        border-radius: 16px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: minmax(190px, 1.2fr) 180px 180px 200px;
        gap: 12px;
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #ffffff;
        color: #30485b;
        font-size: 11px;
        outline: none;
        transition: .2s ease;
    }

    .form-control:focus {
        border-color: #8eb0cb;
        box-shadow: 0 0 0 3px rgba(22, 76, 150, .08);
    }

    .filters-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 13px;
    }

    .btn {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
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

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
    }

    .summary-card {
        padding: 16px;
        border-radius: 14px;
    }

    .summary-card span {
        display: block;
        color: #778895;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .summary-card strong {
        display: block;
        margin-top: 7px;
        color: #082d55;
        font-size: 24px;
    }

    .summary-card.done { border-color: #cce7d6; background: #f8fdf9; }
    .summary-card.pending { border-color: #efd99f; background: #fffdf6; }
    .summary-card.no-attention { border-color: #efc5c5; background: #fffafa; }
    .summary-card.extra { border-color: #d9d0f4; background: #fbf9ff; }
    .summary-card.cancelled { border-color: #d7dde2; background: #fafbfc; }
    .summary-card.audit { border-color: #c9ddf0; background: #f7fbff; }

    .calendar-card {
        overflow: hidden;
        border-radius: 18px;
    }

    .calendar-toolbar,
    .visits-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid #e9eff3;
        background: #fbfcfd;
    }

    .calendar-toolbar h3,
    .visits-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 18px;
        text-transform: capitalize;
    }

    .calendar-toolbar p,
    .visits-header p {
        margin: 4px 0 0;
        color: #82909a;
        font-size: 10px;
    }

    .calendar-nav {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .calendar-nav a {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        border: 1px solid #d8e2e9;
        border-radius: 9px;
        background: #ffffff;
        color: #31536e;
        font-size: 10px;
        font-weight: 800;
        transition: .2s ease;
    }

    .calendar-nav a:hover {
        border-color: #9db6c9;
        color: #164c96;
        background: #f5f9fc;
    }

    .calendar-scroll {
        overflow-x: auto;
    }

    .calendar-grid {
        min-width: 1050px;
        display: grid;
        grid-template-columns: repeat(7, minmax(145px, 1fr));
    }

    .calendar-weekday {
        padding: 11px 12px;
        border-right: 1px solid #e8eef2;
        border-bottom: 1px solid #e8eef2;
        background: #f6f9fb;
        color: #657988;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .6px;
        text-align: center;
    }

    .calendar-blank,
    .calendar-day {
        min-height: 185px;
        border-right: 1px solid #edf1f4;
        border-bottom: 1px solid #edf1f4;
    }

    .calendar-blank {
        background: #fafcfd;
    }

    .calendar-day {
        position: relative;
        padding: 12px;
        background: #ffffff;
    }

    .calendar-day.today {
        background: #f7fbff;
        box-shadow: inset 0 0 0 2px rgba(22, 76, 150, .18);
    }

    .calendar-day.future {
        background: #fbfcfd;
    }

    .day-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }

    .day-number {
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        border-radius: 9px;
        background: #eef3f7;
        color: #173b59;
        font-size: 12px;
        font-weight: 900;
    }

    .calendar-day.today .day-number {
        background: #164c96;
        color: #ffffff;
    }

    .day-total {
        color: #8a99a5;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .day-events {
        display: grid;
        gap: 6px;
    }

    .calendar-event {
        width: 100%;
        min-height: 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 5px 8px;
        border: 0;
        border-radius: 7px;
        font-size: 8px;
        font-weight: 800;
        cursor: pointer;
        text-align: left;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .calendar-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 9px rgba(20, 57, 83, .10);
    }

    .calendar-event strong {
        font-size: 10px;
    }

    .calendar-event.done { background: #eaf8ef; color: #1d7b4e; }
    .calendar-event.pending { background: #fff5dc; color: #9c7014; }
    .calendar-event.no-attention { background: #fdecec; color: #b13c3c; }
    .calendar-event.extra { background: #eee9ff; color: #6345a2; }
    .calendar-event.cancelled { background: #edf0f2; color: #596a78; }

    .future-message {
        margin-top: 18px;
        color: #9aa7b0;
        font-size: 9px;
        line-height: 1.5;
        text-align: center;
    }

    .legend {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 18px;
        padding: 14px 20px;
        border-top: 1px solid #edf1f4;
        background: #fbfcfd;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #627786;
        font-size: 9px;
        font-weight: 750;
    }

    .legend-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }

    .legend-dot.done { background: #56b47c; }
    .legend-dot.pending { background: #d5a837; }
    .legend-dot.no-attention { background: #cf6262; }
    .legend-dot.extra { background: #8165c5; }
    .legend-dot.cancelled { background: #8898a5; }

    .visits-card {
        overflow: hidden;
        border-radius: 18px;
    }

    .table-scroll {
        overflow-x: auto;
    }

    .visits-table {
        width: 100%;
        min-width: 1150px;
        border-collapse: collapse;
    }

    .visits-table th {
        padding: 12px 14px;
        background: #f7f9fb;
        color: #5d7182;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .25px;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .visits-table td {
        padding: 13px 14px;
        border-top: 1px solid #edf1f4;
        color: #334f64;
        font-size: 10px;
        vertical-align: middle;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 25px;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 850;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-certified {
        background: #eaf8f0;
        color: #167746;
    }

    .status-pending {
        background: #fff6df;
        color: #9a6913;
    }

    .status-cancelled {
        background: #fff0ef;
        color: #a83c38;
    }

    .actions-cell {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dce5eb;
        border-radius: 10px;
        text-decoration: none;
        transition: .18s;
    }

    .action-btn svg {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
    }

    .action-view {
        background: #fff;
        color: #31536e;
    }

    .action-print {
        background: #eaf4fb;
        border-color: #cce0ef;
        color: #176398;
    }

    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 12px rgba(20, 57, 83, .09);
    }

    .empty-state {
        padding: 42px 20px;
        color: #7a8c99;
        text-align: center;
        font-size: 11px;
    }

    .pagination-wrapper {
        padding: 16px 18px;
        border-top: 1px solid #edf1f4;
    }

    .modal-backdrop {
        position: fixed;
        z-index: 200;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(4, 27, 49, .58);
        backdrop-filter: blur(4px);
    }

    .modal-backdrop.open {
        display: flex;
    }

    .modal-panel {
        width: min(1100px, 96vw);
        max-height: 88vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 28px 70px rgba(2, 26, 48, .28);
    }

    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding: 19px 20px;
        border-bottom: 1px solid #e8eef2;
        background: #fbfcfd;
    }

    .modal-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 17px;
    }

    .modal-header p {
        margin: 5px 0 0;
        color: #7d8d99;
        font-size: 10px;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        border: 1px solid #d9e2e8;
        border-radius: 9px;
        background: #ffffff;
        color: #5a7080;
        font-size: 20px;
        cursor: pointer;
    }

    .modal-body {
        overflow: auto;
    }

    .modal-loading,
    .modal-empty,
    .modal-error {
        padding: 45px 22px;
        color: #768894;
        font-size: 11px;
        text-align: center;
    }

    .modal-error {
        color: #a84040;
    }

    .detail-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .detail-table th,
    .detail-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        font-size: 10px;
    }

    .detail-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
    }

    @media (max-width: 1150px) {
        .filters-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .compliance-header,
        .calendar-toolbar,
        .visits-header {
            flex-direction: column;
            align-items: stretch;
        }

        .filters-grid,
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .filters-actions,
        .calendar-nav {
            flex-wrap: wrap;
        }

        .filters-actions .btn,
        .calendar-nav a {
            flex: 1;
        }

        .modal-backdrop {
            padding: 10px;
        }

        .modal-panel {
            width: 100%;
            max-height: 94vh;
        }
    }
</style>
@endpush

@section('content')
@php
    $mesCarbon = \Carbon\Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
    $tituloMes = $mesCarbon->locale('es')->translatedFormat('F Y');
    $espaciosIniciales = $inicioMes->dayOfWeekIso - 1;

    $queryBase = array_filter([
        'region_id' => $regionId ?: null,
        'ruta_id' => $rutaId ?: null,
        'promotor_id' => $promotorId ?: null,
        'buscar' => $buscar !== '' ? $buscar : null,
    ], fn ($valor) => $valor !== null && $valor !== '');
@endphp

<div class="compliance-page">
    <header class="compliance-header">
        <div class="compliance-title">
            <h2>Cumplimiento de Arqueos</h2>

            <p>
                Consulte el cumplimiento diario de los arqueos de agentes
                a nivel general, con filtros por región, ruta y promotor.
                También puede revisar los arqueos de Auditoría realizados
                por su propio usuario durante el período seleccionado.
            </p>
        </div>
    </header>

    <section class="filters-card">
        <form method="GET" action="{{ route('auditoria.cumplimientos.index') }}">
            <input type="hidden" name="mes" value="{{ $mes }}">

            <div class="filters-grid">
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    value="{{ $buscar }}"
                    placeholder="Buscar agente, negocio o propietario"
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

                <select name="promotor_id" class="form-control">
                    <option value="">Todos los promotores</option>

                    @foreach ($promotores as $promotor)
                        @php
                            $nombrePromotor = trim(
                                ($promotor->nombres ?? '')
                                . ' '
                                . ($promotor->apellidos ?? '')
                            );
                        @endphp

                        <option
                            value="{{ $promotor->id }}"
                            @selected($promotorId === (int) $promotor->id)
                        >
                            {{ $nombrePromotor !== '' ? $nombrePromotor : $promotor->usuario }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filters-actions">
                <a
                    href="{{ route('auditoria.cumplimientos.index', ['mes' => $mes]) }}"
                    class="btn btn-secondary"
                >
                    Limpiar filtros
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Aplicar filtros
                </button>
            </div>
        </form>
    </section>

    <section class="summary-grid">
        <article class="summary-card done">
            <span>Con arqueo</span>
            <strong>{{ $resumenMes['arqueados'] }}</strong>
        </article>

        <article class="summary-card pending">
            <span>Sin arqueo</span>
            <strong>{{ $resumenMes['sin_arqueo'] }}</strong>
        </article>

        <article class="summary-card no-attention">
            <span>No atendieron</span>
            <strong>{{ $resumenMes['no_atendieron'] }}</strong>
        </article>

        <article class="summary-card extra">
            <span>Extemporáneos</span>
            <strong>{{ $resumenMes['extemporaneos'] }}</strong>
        </article>

        <article class="summary-card cancelled">
            <span>Anulados</span>
            <strong>{{ $resumenMes['anulados'] }}</strong>
        </article>

        <article class="summary-card audit">
            <span>Mis auditorías</span>
            <strong>{{ $totalVisitasMes }}</strong>
        </article>
    </section>

    <section class="calendar-card">
        <header class="calendar-toolbar">
            <div>
                <h3>{{ $tituloMes }}</h3>
                <p>
                    Los días futuros no se contabilizan como incumplimiento.
                </p>
            </div>

            <nav class="calendar-nav" aria-label="Navegación del calendario">
                <a
                    href="{{ route(
                        'auditoria.cumplimientos.index',
                        array_merge($queryBase, ['mes' => $mesAnterior])
                    ) }}"
                >
                    ← Mes anterior
                </a>

                <a
                    href="{{ route(
                        'auditoria.cumplimientos.index',
                        array_merge($queryBase, ['mes' => $mesActual])
                    ) }}"
                >
                    Hoy
                </a>

                <a
                    href="{{ route(
                        'auditoria.cumplimientos.index',
                        array_merge($queryBase, ['mes' => $mesSiguiente])
                    ) }}"
                >
                    Mes siguiente →
                </a>
            </nav>
        </header>

        <div class="calendar-scroll">
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

                @for ($i = 0; $i < $espaciosIniciales; $i++)
                    <div class="calendar-blank"></div>
                @endfor

                @foreach ($calendario as $dia)
                    <article
                        class="calendar-day
                            {{ $dia['es_hoy'] ? 'today' : '' }}
                            {{ $dia['es_futuro'] ? 'future' : '' }}"
                    >
                        <div class="day-header">
                            <span class="day-number">
                                {{ $dia['dia'] }}
                            </span>

                            @if (! $dia['es_futuro'])
                                <span class="day-total">
                                    {{ $dia['total_agentes'] }} agentes
                                </span>
                            @endif
                        </div>

                        @if ($dia['es_futuro'])
                            <div class="future-message">
                                Fecha futura
                            </div>
                        @else
                            <div class="day-events">
                                <button
                                    type="button"
                                    class="calendar-event done js-open-detail"
                                    data-fecha="{{ $dia['fecha'] }}"
                                    data-categoria="ARQUEADO"
                                    data-label="Agentes con arqueo"
                                >
                                    <span>Con arqueo</span>
                                    <strong>{{ $dia['arqueados'] }}</strong>
                                </button>

                                <button
                                    type="button"
                                    class="calendar-event pending js-open-detail"
                                    data-fecha="{{ $dia['fecha'] }}"
                                    data-categoria="SIN_ARQUEO"
                                    data-label="Agentes sin arqueo"
                                >
                                    <span>Sin arqueo</span>
                                    <strong>{{ $dia['sin_arqueo'] }}</strong>
                                </button>

                                <button
                                    type="button"
                                    class="calendar-event no-attention js-open-detail"
                                    data-fecha="{{ $dia['fecha'] }}"
                                    data-categoria="NO_ATENDIO"
                                    data-label="Agentes que no atendieron"
                                >
                                    <span>No atendieron</span>
                                    <strong>{{ $dia['no_atendieron'] }}</strong>
                                </button>

                                <button
                                    type="button"
                                    class="calendar-event extra js-open-detail"
                                    data-fecha="{{ $dia['fecha'] }}"
                                    data-categoria="EXTEMPORANEO"
                                    data-label="Arqueos extemporáneos"
                                >
                                    <span>Extemporáneos</span>
                                    <strong>{{ $dia['extemporaneos'] }}</strong>
                                </button>

                                <button
                                    type="button"
                                    class="calendar-event cancelled js-open-detail"
                                    data-fecha="{{ $dia['fecha'] }}"
                                    data-categoria="ANULADO"
                                    data-label="Arqueos anulados"
                                >
                                    <span>Anulados</span>
                                    <strong>{{ $dia['anulados'] }}</strong>
                                </button>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>

        <footer class="legend">
            <span class="legend-item">
                <i class="legend-dot done"></i>
                Con arqueo
            </span>

            <span class="legend-item">
                <i class="legend-dot pending"></i>
                Sin arqueo
            </span>

            <span class="legend-item">
                <i class="legend-dot no-attention"></i>
                No atendieron
            </span>

            <span class="legend-item">
                <i class="legend-dot extra"></i>
                Extemporáneos
            </span>

            <span class="legend-item">
                <i class="legend-dot cancelled"></i>
                Anulados
            </span>
        </footer>
    </section>

    <section class="visits-card">
        <header class="visits-header">
            <div>
                <h3>Mis arqueos realizados</h3>

                <p>
                    Arqueos VISITA_AUDITORIA registrados por su usuario durante {{ $tituloMes }}.
                </p>
            </div>
        </header>

        <div class="table-scroll">
            @if ($visitasAuditoria->isEmpty())
                <div class="empty-state">
                    No realizó arqueos de Auditoría durante este período.
                </div>
            @else
                <table class="visits-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Agente</th>
                            <th>Propietario</th>
                            <th>Región</th>
                            <th>Ruta</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Diferencia</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($visitasAuditoria as $arqueo)
                            @php
                                $estadoClase = match ($arqueo->estado) {
                                    'CERTIFICADO' => 'status-certified',
                                    'ANULADO' => 'status-cancelled',
                                    default => 'status-pending',
                                };

                                $estadoTexto = match ($arqueo->estado) {
                                    'PENDIENTE_CERTIFICACION' => 'Pendiente',
                                    'CERTIFICADO' => 'Certificado',
                                    'ANULADO' => 'Anulado',
                                    default => str_replace('_', ' ', $arqueo->estado),
                                };
                            @endphp

                            <tr>
                                <td>
                                    <strong>{{ $arqueo->numero_arqueo }}</strong>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}
                                </td>

                                <td>
                                    <strong>{{ $arqueo->codigo_agente_historico }}</strong>
                                    <br>
                                    {{ $arqueo->nombre_negocio_historico }}
                                </td>

                                <td>
                                    {{ $arqueo->nombre_propietario_historico }}
                                </td>

                                <td>
                                    {{ $arqueo->region_historica }}
                                </td>

                                <td>
                                    {{ $arqueo->ruta_historica }}
                                </td>

                                <td>
                                    <span class="status-badge {{ $estadoClase }}">
                                        {{ $estadoTexto }}
                                    </span>
                                </td>

                                <td>
                                    Q {{ number_format((float) $arqueo->total_arqueado, 2) }}
                                </td>

                                <td>
                                    Q {{ number_format((float) $arqueo->diferencia, 2) }}
                                </td>

                                <td>
                                    <div class="actions-cell">
                                        <a
                                            href="{{ route('auditoria.arqueos.show', $arqueo->id) }}"
                                            class="action-btn action-view"
                                            title="Ver arqueo"
                                            aria-label="Ver arqueo"
                                        >
                                            <svg viewBox="0 0 24 24">
                                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                                                <circle cx="12" cy="12" r="2.8"/>
                                            </svg>
                                        </a>

                                        <a
                                            target="_blank"
                                            href="{{ route('auditoria.arqueos.imprimir', $arqueo->id) }}"
                                            class="action-btn action-print"
                                            title="Imprimir PDF"
                                            aria-label="Imprimir PDF"
                                        >
                                            <svg viewBox="0 0 24 24">
                                                <path d="M6 9V3h12v6"/>
                                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                                <path d="M6 14h12v7H6z"/>
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

        @if ($visitasAuditoria->hasPages())
            <div class="pagination-wrapper">
                {{ $visitasAuditoria->links() }}
            </div>
        @endif
    </section>
</div>

<div
    class="modal-backdrop"
    id="complianceModal"
    aria-hidden="true"
>
    <section
        class="modal-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalTitle"
    >
        <header class="modal-header">
            <div>
                <h3 id="modalTitle">
                    Detalle de cumplimiento
                </h3>

                <p id="modalSubtitle">
                    Seleccione una categoría del calendario.
                </p>
            </div>

            <button
                type="button"
                class="modal-close"
                id="modalClose"
                aria-label="Cerrar modal"
            >
                ×
            </button>
        </header>

        <div
            class="modal-body"
            id="modalBody"
        >
            <div class="modal-loading">
                Cargando información...
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal =
            document.getElementById('complianceModal');

        const modalClose =
            document.getElementById('modalClose');

        const modalTitle =
            document.getElementById('modalTitle');

        const modalSubtitle =
            document.getElementById('modalSubtitle');

        const modalBody =
            document.getElementById('modalBody');

        const detailUrl =
            @json(route('auditoria.cumplimientos.detalle'));

        let lastFocusedElement = null;

        const filtros = {
            region_id: @json($regionId ?: null),
            ruta_id: @json($rutaId ?: null),
            promotor_id: @json($promotorId ?: null),
            buscar: @json($buscar !== '' ? $buscar : null),
        };

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function openModal() {
            lastFocusedElement = document.activeElement;

            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {
                modalClose?.focus();
            });
        }

        function closeModal() {
            if (!modal.classList.contains('open')) {
                return;
            }

            if (
                lastFocusedElement
                && document.contains(lastFocusedElement)
                && typeof lastFocusedElement.focus === 'function'
            ) {
                lastFocusedElement.focus();
            } else if (
                document.activeElement
                && modal.contains(document.activeElement)
            ) {
                document.activeElement.blur();
            }

            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';

            lastFocusedElement = null;
        }

        function renderTable(agentes) {
            if (!agentes.length) {
                return `
                    <div class="modal-empty">
                        No hay agentes en esta categoría para la fecha seleccionada.
                    </div>
                `;
            }

            const rows = agentes.map((agente) => `
                <tr>
                    <td>
                        <strong>
                            ${escapeHtml(agente.codigo_agente)}
                        </strong>
                    </td>
                    <td>
                        ${escapeHtml(agente.nombre_negocio)}
                    </td>
                    <td>
                        ${escapeHtml(agente.nombre_propietario)}
                    </td>
                    <td>
                        ${escapeHtml(agente.region)}
                    </td>
                    <td>
                        ${escapeHtml(agente.ruta)}
                    </td>
                    <td>
                        ${escapeHtml(agente.promotor)}
                    </td>
                    <td>
                        ${escapeHtml(agente.numero_arqueo || '—')}
                    </td>
                </tr>
            `).join('');

            return `
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Negocio</th>
                            <th>Propietario</th>
                            <th>Región</th>
                            <th>Ruta</th>
                            <th>Promotor</th>
                            <th>No. arqueo</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
            `;
        }

        async function loadDetail(button) {
            const fecha =
                button.dataset.fecha;

            const categoria =
                button.dataset.categoria;

            const label =
                button.dataset.label;

            modalTitle.textContent = label;
            modalSubtitle.textContent = fecha;

            modalBody.innerHTML = `
                <div class="modal-loading">
                    Cargando información...
                </div>
            `;

            openModal();

            const params =
                new URLSearchParams({
                    fecha,
                    categoria,
                });

            Object.entries(filtros).forEach(
                ([key, value]) => {
                    if (
                        value !== null
                        && value !== ''
                    ) {
                        params.set(
                            key,
                            value
                        );
                    }
                }
            );

            try {
                const response = await fetch(
                    `${detailUrl}?${params.toString()}`,
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        'No fue posible consultar el detalle.'
                    );
                }

                const data =
                    await response.json();

                modalTitle.textContent =
                    `${data.categoria_texto} (${data.total})`;

                modalSubtitle.textContent =
                    data.fecha_formateada
                    || data.fecha;

                modalBody.innerHTML =
                    renderTable(
                        data.agentes || []
                    );
            } catch (error) {
                modalBody.innerHTML = `
                    <div class="modal-error">
                        No fue posible cargar el detalle.
                        Intente nuevamente.
                    </div>
                `;
            }
        }

        document
            .querySelectorAll('.js-open-detail')
            .forEach((button) => {
                button.addEventListener(
                    'click',
                    function () {
                        loadDetail(button);
                    }
                );
            });

        modalClose?.addEventListener(
            'click',
            closeModal
        );

        modal?.addEventListener(
            'click',
            function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            }
        );

        document.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Escape'
                    && modal.classList.contains('open')
                ) {
                    closeModal();
                }
            }
        );
    });
</script>
@endpush
