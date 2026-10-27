@extends('layouts.gerencia')

@section('title', 'Dashboard')
@section('module-title', 'Dashboard')

@push('styles')
<style>
    .welcome-panel {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
        margin-bottom: 24px;
        padding: 27px 30px;
        overflow: hidden;
        border-radius: 20px;
        background:
            linear-gradient(
                135deg,
                #07345f,
                #164c96 62%,
                #128257
            );
        color: #ffffff;
        box-shadow:
            0 17px 36px rgba(10, 61, 109, .16);
    }

    .welcome-panel::before {
        content: "";
        position: absolute;
        width: 230px;
        height: 230px;
        top: -145px;
        right: -75px;
        border: 43px solid rgba(255, 255, 255, .055);
        border-radius: 50%;
    }

    .welcome-content,
    .welcome-status {
        position: relative;
        z-index: 2;
    }

    .welcome-content span {
        display: block;
        margin-bottom: 6px;
        color: rgba(255, 255, 255, .68);
        font-size: 11px;
        font-weight: 750;
        letter-spacing: .8px;
        text-transform: uppercase;
    }

    .welcome-content h2 {
        margin: 0;
        font-size: 27px;
        letter-spacing: -.65px;
    }

    .welcome-content p {
        max-width: 690px;
        margin: 10px 0 0;
        color: rgba(255, 255, 255, .75);
        font-size: 13px;
        line-height: 1.6;
    }

    .welcome-status {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 15px;
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 13px;
        background: rgba(255, 255, 255, .10);
        font-size: 12px;
        font-weight: 750;
    }

    .status-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #73e1a0;
        box-shadow:
            0 0 0 5px rgba(115, 225, 160, .13);
    }

    .summary-grid {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .summary-card {
        position: relative;
        min-height: 142px;
        padding: 19px;
        border: 1px solid #e0e8ee;
        border-radius: 17px;
        background: #ffffff;
        box-shadow:
            0 8px 24px rgba(20, 57, 83, .055);
    }

    .summary-card.success {
        border-color: #c5e5d1;
        background: #f7fcf9;
    }

    .summary-card.warning {
        border-color: #efd99f;
        background: #fffdf6;
    }

    .summary-card.danger {
        border-color: #efc5c5;
        background: #fffafa;
    }

    .summary-card.info {
        border-color: #c9dceb;
        background: #f7fbff;
    }

    .summary-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .summary-label {
        color: #71818e;
        font-size: 11px;
        font-weight: 750;
        letter-spacing: .55px;
        text-transform: uppercase;
    }

    .summary-icon {
        flex: 0 0 auto;
        width: 43px;
        height: 43px;
        display: grid;
        place-items: center;
        border-radius: 13px;
        background: rgba(22, 76, 150, .09);
        color: #164c96;
    }

    .summary-icon.green {
        background: rgba(0, 166, 81, .10);
        color: #009349;
    }

    .summary-icon.orange {
        background: rgba(224, 151, 14, .11);
        color: #b47c0e;
    }

    .summary-icon.red {
        background: rgba(194, 57, 52, .10);
        color: #b83b36;
    }

    .summary-icon.gray {
        background: #eef2f5;
        color: #71808c;
    }

    .summary-icon svg {
        width: 22px;
        height: 22px;
    }

    .summary-value {
        display: block;
        margin-top: 16px;
        color: #082d55;
        font-size: 23px;
        font-weight: 800;
        letter-spacing: -.5px;
    }

    .summary-description {
        display: block;
        margin-top: 5px;
        color: #87949d;
        font-size: 11px;
        line-height: 1.45;
    }

    .content-grid {
        display: grid;
        grid-template-columns:
            minmax(330px, .78fr)
            minmax(0, 1.72fr);
        gap: 20px;
    }

    .panel {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow:
            0 8px 25px rgba(20, 57, 83, .05);
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 18px 20px;
        border-bottom: 1px solid #edf1f4;
    }

    .panel-title h3 {
        margin: 0;
        color: #0a3158;
        font-size: 16px;
    }

    .panel-title p {
        margin: 5px 0 0;
        color: #82909a;
        font-size: 11px;
    }

    .panel-link {
        color: #164c96;
        font-size: 11px;
        font-weight: 750;
        white-space: nowrap;
    }

    .panel-link:hover {
        color: #0b315f;
    }

    .panel-body {
        padding: 20px;
    }

    .compliance-card {
        padding: 2px 0 4px;
    }

    .progress-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
        color: #52697b;
        font-size: 11px;
        font-weight: 800;
    }

    .progress-title strong {
        color: #0a3158;
        font-size: 18px;
    }

    .progress-bar {
        height: 14px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf1f4;
        box-shadow:
            inset 0 1px 2px rgba(26, 54, 76, .05);
    }

    .progress-value {
        height: 100%;
        border-radius: inherit;
        background:
            linear-gradient(
                90deg,
                #164c96,
                #1c64b5,
                #00a651
            );
    }

    .compliance-description {
        margin-top: 10px;
        color: #83919b;
        font-size: 11px;
        line-height: 1.55;
    }

    .compliance-info {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 18px;
    }

    .mini-stat {
        padding: 14px;
        border: 1px solid #e3eaef;
        border-radius: 13px;
        background: #fbfcfd;
    }

    .mini-stat span {
        display: block;
        color: #758697;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .4px;
        text-transform: uppercase;
    }

    .mini-stat strong {
        display: block;
        margin-top: 6px;
        color: #173b59;
        font-size: 18px;
    }

    .executive-note {
        margin-top: 16px;
        padding: 14px;
        border: 1px solid #dfe8ef;
        border-radius: 13px;
        background: #f8fbfd;
        color: #647786;
        font-size: 10px;
        line-height: 1.55;
    }

    .executive-note strong {
        color: #173b59;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .recent-table {
        width: 100%;
        min-width: 930px;
        border-collapse: collapse;
    }

    .recent-table th,
    .recent-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        font-size: 11px;
    }

    .recent-table th {
        background: #f8fafb;
        color: #687b8b;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .4px;
        text-transform: uppercase;
    }

    .recent-table tbody tr {
        transition: background .18s ease;
    }

    .recent-table tbody tr:hover {
        background: #fafcfd;
    }

    .recent-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .table-primary {
        color: #19344d;
        font-weight: 800;
    }

    .table-secondary {
        color: #7d8c97;
    }

    .type-badge {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border-radius: 999px;
        background: #edf2f6;
        color: #50667a;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .type-badge.agent {
        background: #eaf2fb;
        color: #195b9b;
    }

    .type-badge.promoter {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .type-badge.audit {
        background: #f1ecfb;
        color: #7052a8;
    }

    .diff-negative {
        color: #b33a34;
        font-weight: 800;
    }

    .diff-positive {
        color: #16834f;
        font-weight: 800;
    }

    .diff-zero {
        color: #607586;
        font-weight: 800;
    }

    .empty-record {
        padding: 32px 20px;
        text-align: center;
    }

    .empty-record-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        margin: 0 auto 14px;
        border-radius: 17px;
        background: #edf3f8;
        color: #164c96;
    }

    .empty-record-icon svg {
        width: 29px;
        height: 29px;
    }

    .empty-record h4 {
        margin: 0;
        color: #17364f;
        font-size: 14px;
    }

    .empty-record p {
        max-width: 360px;
        margin: 7px auto 0;
        color: #83919b;
        font-size: 11px;
        line-height: 1.55;
    }

    @media (max-width: 1250px) {
        .summary-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .welcome-panel {
            display: block;
            padding: 23px 20px;
        }

        .welcome-content h2 {
            font-size: 23px;
        }

        .welcome-status {
            width: fit-content;
            margin-top: 18px;
        }

        .summary-grid,
        .compliance-info {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $authUser = auth()->user();

    $nombreGerencia =
        $authUser?->nombre_completo
        ?? $authUser?->usuario
        ?? 'Gerencia';
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Dashboard de Gerencia</h2>

        <p>
            Vista ejecutiva del estado operativo, cumplimiento diario
            e incidencias registradas en los arqueos.
        </p>
    </div>

    <div class="page-badge">
        <span class="page-badge-dot"></span>
        Supervisión ejecutiva
    </div>
</div>

<section class="welcome-panel">
    <div class="welcome-content">
        <span>Vista ejecutiva</span>

        <h2>{{ $nombreGerencia }}</h2>

        <p>
            Consulte el comportamiento diario de los arqueos,
            el nivel de cumplimiento y las principales incidencias
            operativas para apoyar la toma de decisiones.
        </p>
    </div>

    <div class="welcome-status">
        <span class="status-dot"></span>
        Información actualizada
    </div>
</section>

<section class="summary-grid">
    <article class="summary-card info">
        <div class="summary-card-header">
            <span class="summary-label">Agentes activos</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M19 8v6"/>
                    <path d="M16 11h6"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $agentesActivos }}</strong>
        <span class="summary-description">Agentes habilitados en operación.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Arqueos hoy</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="M7 9h10"/>
                    <path d="M7 13h5"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $arqueosHoy }}</strong>
        <span class="summary-description">Total de arqueos registrados hoy.</span>
    </article>

    <article class="summary-card success">
        <div class="summary-card-header">
            <span class="summary-label">Arqueos exactos hoy</span>

            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m8 12 3 3 5-6"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $exactosHoy }}</strong>
        <span class="summary-description">Arqueos sin diferencia registrada.</span>
    </article>

    <article class="summary-card warning">
        <div class="summary-card-header">
            <span class="summary-label">Pendientes hoy</span>

            <span class="summary-icon orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 2"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $agentesPendientesHoy }}</strong>
        <span class="summary-description">Agentes activos pendientes de arqueo.</span>
    </article>

    <article class="summary-card danger">
        <div class="summary-card-header">
            <span class="summary-label">Faltantes hoy</span>

            <span class="summary-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v18"/>
                    <path d="m7 16 5 5 5-5"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $faltantesHoy }}</strong>
        <span class="summary-description">Arqueos con diferencia negativa.</span>
    </article>

    <article class="summary-card success">
        <div class="summary-card-header">
            <span class="summary-label">Sobrantes hoy</span>

            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 21V3"/>
                    <path d="m7 8 5-5 5 5"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $sobrantesHoy }}</strong>
        <span class="summary-description">Arqueos con diferencia positiva.</span>
    </article>

    <article class="summary-card warning">
        <div class="summary-card-header">
            <span class="summary-label">Extemporáneos hoy</span>

            <span class="summary-icon orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M16 3v4"/>
                    <path d="M8 3v4"/>
                    <path d="M3 10h18"/>
                    <path d="M12 14v3"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $extemporaneosHoy }}</strong>
        <span class="summary-description">Arqueos realizados fuera de fecha ordinaria.</span>
    </article>

    <article class="summary-card danger">
        <div class="summary-card-header">
            <span class="summary-label">Anulados hoy</span>

            <span class="summary-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m9 9 6 6"/>
                    <path d="m15 9-6 6"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $anuladosHoy }}</strong>
        <span class="summary-description">Arqueos anulados durante el día.</span>
    </article>
</section>

<section class="content-grid">
    <article class="panel">
        <header class="panel-header">
            <div class="panel-title">
                <h3>Cumplimiento diario</h3>

                <p>
                    Agentes activos que realizaron su arqueo diario.
                </p>
            </div>
        </header>

        <div class="panel-body">
            <div class="compliance-card">
                <div class="progress-title">
                    <span>Nivel de cumplimiento</span>

                    <strong>
                        {{ number_format($cumplimiento, 1) }}%
                    </strong>
                </div>

                <div class="progress-bar">
                    <div
                        class="progress-value"
                        style="width:{{ min(100, max(0, $cumplimiento)) }}%;"
                    ></div>
                </div>

                <p class="compliance-description">
                    Porcentaje de agentes activos que ya cuentan
                    con arqueo diario registrado.
                </p>
            </div>

            <div class="compliance-info">
                <div class="mini-stat">
                    <span>Con arqueo</span>
                    <strong>{{ $agentesConArqueoHoy }}</strong>
                </div>

                <div class="mini-stat">
                    <span>Pendientes</span>
                    <strong>{{ $agentesPendientesHoy }}</strong>
                </div>

                <div class="mini-stat">
                    <span>Arqueos Agente</span>
                    <strong>{{ $arqueosAgenteHoy }}</strong>
                </div>

                <div class="mini-stat">
                    <span>Arqueos Promotor</span>
                    <strong>{{ $arqueosPromotorHoy }}</strong>
                </div>
            </div>

            <div class="executive-note">
                <strong>Lectura ejecutiva:</strong>
                el cumplimiento diario permite identificar rápidamente
                el nivel de avance de la operación y los agentes que aún
                requieren seguimiento.
            </div>
        </div>
    </article>

    <article class="panel">
        <header class="panel-header">
            <div class="panel-title">
                <h3>Últimos Arqueos</h3>

                <p>
                    Actividad reciente registrada en el sistema.
                </p>
            </div>

            <a
                href="{{ url('/gerencia/arqueos') }}"
                class="panel-link"
            >
                Ver todos
            </a>
        </header>

        @if($ultimosArqueos->isEmpty())
            <div class="empty-record">
                <div class="empty-record-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="M7 9h10"/>
                        <path d="M7 13h5"/>
                    </svg>
                </div>

                <h4>No hay arqueos registrados</h4>

                <p>
                    Los movimientos recientes aparecerán aquí
                    cuando existan registros disponibles.
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Agente</th>
                            <th>Región</th>
                            <th>Ruta</th>
                            <th>Tipo</th>
                            <th>Diferencia</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($ultimosArqueos as $arqueo)
                            @php
                                $diferencia =
                                    (float) $arqueo->diferencia;

                                $claseDiferencia =
                                    $diferencia < 0
                                        ? 'diff-negative'
                                        : (
                                            $diferencia > 0
                                                ? 'diff-positive'
                                                : 'diff-zero'
                                        );

                                $tipoClass =
                                    $arqueo->tipo === 'DIARIO_AGENTE'
                                        ? 'agent'
                                        : (
                                            $arqueo->tipo === 'VISITA_PROMOTOR'
                                                ? 'promoter'
                                                : 'audit'
                                        );

                                $tipoTexto =
                                    $arqueo->tipo === 'DIARIO_AGENTE'
                                        ? 'Agente'
                                        : (
                                            $arqueo->tipo === 'VISITA_PROMOTOR'
                                                ? 'Promotor'
                                                : (
                                                    $arqueo->tipo === 'VISITA_AUDITORIA'
                                                        ? 'Auditoría'
                                                        : str_replace(
                                                            '_',
                                                            ' ',
                                                            $arqueo->tipo
                                                        )
                                                )
                                        );
                            @endphp

                            <tr>
                                <td>
                                    <span class="table-primary">
                                        {{ $arqueo->numero_arqueo }}
                                    </span>
                                </td>

                                <td class="table-secondary">
                                    {{ \Carbon\Carbon::parse(
                                        $arqueo->fecha_arqueo
                                    )->format('d/m/Y') }}
                                </td>

                                <td>
                                    <span class="table-primary">
                                        {{ $arqueo->codigo_agente }}
                                    </span>

                                    <span class="table-secondary">
                                        — {{ $arqueo->nombre_negocio }}
                                    </span>
                                </td>

                                <td class="table-secondary">
                                    {{ $arqueo->region_nombre ?? '—' }}
                                </td>

                                <td class="table-secondary">
                                    {{ $arqueo->ruta_nombre ?? '—' }}
                                </td>

                                <td>
                                    <span class="type-badge {{ $tipoClass }}">
                                        {{ $tipoTexto }}
                                    </span>
                                </td>

                                <td>
                                    <span class="{{ $claseDiferencia }}">
                                        Q {{ number_format(
                                            abs($diferencia),
                                            2
                                        ) }}

                                        @if($diferencia < 0)
                                            Faltante
                                        @elseif($diferencia > 0)
                                            Sobrante
                                        @else
                                            Exacto
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </article>
</section>
@endsection
