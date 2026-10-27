@extends('layouts.agente')

@section('title', 'Mis Cumplimientos')
@section('module-title', 'Mis Cumplimientos')

@push('styles')
<style>
    .cumplimientos-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .cumplimientos-toolbar-left,
    .cumplimientos-toolbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .calendar-button {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 9px 13px;
        border: 1px solid #d8e2ea;
        border-radius: 11px;
        background: #fff;
        color: #164c96;
        font-size: 12px;
        font-weight: 750;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .calendar-button:hover {
        border-color: #b8c9d8;
        background: #f7fafc;
    }

    .calendar-button.primary {
        border-color: #164c96;
        background: #164c96;
        color: #fff;
    }

    .calendar-button svg {
        width: 17px;
        height: 17px;
    }

    .calendar-month-title {
        margin: 0;
        color: #06284f;
        font-size: 22px;
        font-weight: 800;
        text-transform: capitalize;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .summary-card {
        padding: 17px 18px;
        border: 1px solid #dfe7ee;
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(22, 58, 85, 0.035);
    }

    .summary-card span {
        display: block;
        margin-bottom: 7px;
        color: #6d7d8a;
        font-size: 11px;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .summary-card strong {
        color: #06284f;
        font-size: 25px;
        line-height: 1;
    }

    .calendar-wrapper {
        overflow-x: auto;
        border: 1px solid #dfe7ee;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 9px 24px rgba(22, 58, 85, 0.04);
    }

    .calendar-grid {
        min-width: 900px;
        display: grid;
        grid-template-columns: repeat(7, minmax(120px, 1fr));
    }

    .calendar-weekday {
        padding: 13px 10px;
        border-bottom: 1px solid #dfe7ee;
        background: #f8fafc;
        color: #53697d;
        font-size: 11px;
        font-weight: 800;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.45px;
    }

    .calendar-cell {
        min-height: 145px;
        padding: 12px;
        border-right: 1px solid #edf1f5;
        border-bottom: 1px solid #edf1f5;
        background: #fff;
    }

    .calendar-cell:nth-child(7n) {
        border-right: 0;
    }

    .calendar-cell.empty {
        background: #fafcfd;
    }

    .calendar-cell.today {
        box-shadow: inset 0 0 0 2px rgba(22, 76, 150, 0.22);
        background: #fbfdff;
    }

    .calendar-cell.future {
        background: #fbfcfd;
        color: #98a5af;
    }

    .calendar-day-number {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 13px;
        color: #19344d;
        font-size: 14px;
        font-weight: 800;
    }

    .today-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 7px;
        border-radius: 999px;
        background: #e9f2ff;
        color: #164c96;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .status-box {
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .status-pill {
        width: 100%;
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px 10px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
        text-align: center;
    }

    .status-cumplido {
        background: #ebf8f0;
        color: #177245;
        border: 1px solid #c7e9d4;
    }

    .status-sin-arqueo {
        background: #fff4e8;
        color: #a45d10;
        border: 1px solid #f1d1ab;
    }

    .status-no-atendio {
        background: #fdeeee;
        color: #a63737;
        border: 1px solid #efcaca;
    }

    .status-extemporaneo {
        background: #eef0ff;
        color: #5852a7;
        border: 1px solid #d6d8f8;
    }

    .status-anulado {
        background: #f1f3f5;
        color: #59646f;
        border: 1px solid #d9dee3;
    }

    .status-futuro {
        background: #f5f7f9;
        color: #9aa6af;
        border: 1px solid #e4e8eb;
    }

    .status-actions {
        display: flex;
        justify-content: center;
        gap: 8px;
    }

    .action-button {
        width: 36px;
        height: 36px;
        display: inline-grid;
        place-items: center;
        border-radius: 9px;
        border: 1px solid #d7e1e9;
        background: #fff;
        color: #164c96;
        transition: 0.2s ease;
    }

    .action-button:hover {
        background: #f4f8fc;
        border-color: #bacada;
    }

    .action-button svg {
        width: 18px;
        height: 18px;
    }

    .agent-info-card {
        margin-bottom: 22px;
        padding: 18px 20px;
        border: 1px solid #dfe7ee;
        border-radius: 15px;
        background: #fff;
    }

    .agent-info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .agent-info-item span {
        display: block;
        margin-bottom: 5px;
        color: #7a8995;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.45px;
    }

    .agent-info-item strong {
        color: #19344d;
        font-size: 13px;
    }

    .calendar-legend {
        display: flex;
        align-items: center;
        gap: 10px 14px;
        flex-wrap: wrap;
        margin-top: 16px;
        color: #617383;
        font-size: 11px;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .legend-dot.cumplido { background: #35a86c; }
    .legend-dot.sin { background: #e6973a; }
    .legend-dot.no-atendio { background: #d76060; }
    .legend-dot.extemporaneo { background: #756ec0; }
    .legend-dot.anulado { background: #8b969f; }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .agent-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .summary-grid,
        .agent-info-grid {
            grid-template-columns: 1fr;
        }

        .cumplimientos-toolbar {
            align-items: stretch;
        }

        .cumplimientos-toolbar-left,
        .cumplimientos-toolbar-right {
            width: 100%;
        }

        .calendar-button {
            flex: 1;
        }

        .calendar-month-title {
            font-size: 19px;
        }
    }
</style>
@endpush

@section('content')
    <div class="page-header">
        <div class="page-title">
            <h2>Mis Cumplimientos</h2>
            <p>
                Consulte su cumplimiento diario de arqueos y el estado registrado para cada fecha.
            </p>
        </div>

        <div class="page-badge">
            <span class="page-badge-dot"></span>
            Consulta personal
        </div>
    </div>

    <div class="agent-info-card">
        <div class="agent-info-grid">
            <div class="agent-info-item">
                <span>Código de agente</span>
                <strong>{{ $agente->codigo_agente }}</strong>
            </div>

            <div class="agent-info-item">
                <span>Negocio</span>
                <strong>{{ $agente->nombre_negocio }}</strong>
            </div>

            <div class="agent-info-item">
                <span>Ruta</span>
                <strong>{{ $agente->ruta?->nombre ?? 'Sin ruta asignada' }}</strong>
            </div>

            <div class="agent-info-item">
                <span>Región</span>
                <strong>{{ $agente->ruta?->region?->nombre ?? 'Sin región asignada' }}</strong>
            </div>
        </div>
    </div>

    <div class="cumplimientos-toolbar">
        <div class="cumplimientos-toolbar-left">
            <a
                href="{{ route('agente.cumplimientos.index', ['mes' => $mesAnterior]) }}"
                class="calendar-button"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Mes anterior
            </a>

            <a
                href="{{ route('agente.cumplimientos.index', ['mes' => $mesActual]) }}"
                class="calendar-button primary"
            >
                Hoy
            </a>

            <a
                href="{{ route('agente.cumplimientos.index', ['mes' => $mesSiguiente]) }}"
                class="calendar-button"
            >
                Mes siguiente
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>

        <div class="cumplimientos-toolbar-right">
            <h3 class="calendar-month-title">
                {{ $inicioMes->locale('es')->translatedFormat('F Y') }}
            </h3>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <span>Cumplidos</span>
            <strong>{{ $resumenMes['cumplidos'] }}</strong>
        </div>

        <div class="summary-card">
            <span>Sin arqueo</span>
            <strong>{{ $resumenMes['sin_arqueo'] }}</strong>
        </div>

        <div class="summary-card">
            <span>No atendió</span>
            <strong>{{ $resumenMes['no_atendio'] }}</strong>
        </div>

        <div class="summary-card">
            <span>Extemporáneos</span>
            <strong>{{ $resumenMes['extemporaneos'] }}</strong>
        </div>

        <div class="summary-card">
            <span>Anulados</span>
            <strong>{{ $resumenMes['anulados'] }}</strong>
        </div>
    </div>

    @php
        $inicioSemana = $inicioMes->dayOfWeekIso;
        $celdasIniciales = $inicioSemana - 1;
    @endphp

    <div class="calendar-wrapper">
        <div class="calendar-grid">
            @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $diaSemana)
                <div class="calendar-weekday">
                    {{ $diaSemana }}
                </div>
            @endforeach

            @for ($i = 0; $i < $celdasIniciales; $i++)
                <div class="calendar-cell empty"></div>
            @endfor

            @foreach ($calendario as $dia)
                @php
                    $claseEstado = match ($dia['categoria']) {
                        'CUMPLIDO' => 'status-cumplido',
                        'SIN_ARQUEO' => 'status-sin-arqueo',
                        'NO_ATENDIO' => 'status-no-atendio',
                        'EXTEMPORANEO' => 'status-extemporaneo',
                        'ANULADO' => 'status-anulado',
                        default => 'status-futuro',
                    };
                @endphp

                <div
                    class="calendar-cell
                        {{ $dia['es_hoy'] ? 'today' : '' }}
                        {{ $dia['es_futuro'] ? 'future' : '' }}"
                >
                    <div class="calendar-day-number">
                        <span>{{ $dia['dia'] }}</span>

                        @if ($dia['es_hoy'])
                            <span class="today-badge">
                                Hoy
                            </span>
                        @endif
                    </div>

                    <div class="status-box">
                        <div class="status-pill {{ $claseEstado }}">
                            {{ $dia['estado_texto'] }}
                        </div>

                        @if ($dia['numero_arqueo'])
                            <div class="status-pill status-futuro">
                                {{ $dia['numero_arqueo'] }}
                            </div>
                        @endif

                        @if ($dia['url_arqueo'])
                            <div class="status-actions">
                                <a
                                    href="{{ $dia['url_arqueo'] }}"
                                    class="action-button"
                                    title="Ver arqueo"
                                    aria-label="Ver arqueo"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.9"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="calendar-legend">
        <span class="legend-item">
            <span class="legend-dot cumplido"></span>
            Cumplido
        </span>

        <span class="legend-item">
            <span class="legend-dot sin"></span>
            Sin arqueo
        </span>

        <span class="legend-item">
            <span class="legend-dot no-atendio"></span>
            No atendió
        </span>

        <span class="legend-item">
            <span class="legend-dot extemporaneo"></span>
            Extemporáneo
        </span>

        <span class="legend-item">
            <span class="legend-dot anulado"></span>
            Anulado
        </span>
    </div>
@endsection
