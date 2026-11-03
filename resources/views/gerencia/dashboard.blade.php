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
<style>

    /* Agencias: mismo lenguaje visual de los paneles ejecutivos MICOOPE */
    .agency-panels .panel{overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;box-shadow:0 9px 26px rgba(20,57,83,.055);background:#fff}
    .agency-panels .panel-header{padding:20px 23px;background:linear-gradient(100deg,#f8fbff,#fff);border-bottom:1px solid #e7edf3}
    .agency-panels .panel-title h3{font-size:17px;font-weight:800;color:#092f59}
    .agency-panels .panel-title p{font-size:12px;color:#718496}
    .agency-panels .recent-table th{padding:15px 17px;background:#f1f6fa;color:#506a82;letter-spacing:.65px}
    .agency-panels .recent-table td{padding:17px;line-height:1.55}
    .agency-panels .recent-table tbody tr:nth-child(even){background:#fbfdff}
    .agency-panels .recent-table tbody tr:hover{background:#f2f8ff}
    .agency-panels .table-primary{color:#0a3158;font-weight:800}
    .agency-panels .table-secondary{color:#728598}
    .agency-compliance{min-width:165px;display:flex;align-items:center;gap:11px}
    .agency-compliance-track{height:9px;flex:1;min-width:70px;border-radius:30px;background:#e6edf3;overflow:hidden}
    .agency-compliance-fill{height:100%;border-radius:30px;background:linear-gradient(90deg,#1681c4,#00a651)}
    .agency-compliance-fill.low{background:linear-gradient(90deg,#e6a431,#efbd57)}
    .agency-compliance-fill.none{background:#dce5ec}
    .agency-compliance strong{font-size:12px;color:#0a3158;white-space:nowrap}
    .agency-compliance-detail{display:block;margin-top:5px;font-size:10px;color:#778a9a}
    .agency-compliance-note{margin:0;padding:12px 22px 17px;font-size:11px;line-height:1.6;color:#748699;border-top:1px solid #edf1f4}
    .agency-section-marker{display:inline-flex;align-items:center;gap:9px;margin-bottom:8px;color:#1760a4;font-size:11px;font-weight:800;letter-spacing:.7px;text-transform:uppercase}
    .agency-section-marker:before{content:'';width:8px;height:8px;background:#00a651;border-radius:50%}
    .agency-table .type-badge{white-space:nowrap}
    @media(max-width:800px){.agency-panels .panel-header{padding:16px}.agency-panels .recent-table td{padding:13px}}

    .agency-heading {display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;margin:32px 0 20px}
    .agency-heading h2 {margin:0;color:#06284f;font-size:24px;font-weight:800}
    .agency-heading p {margin:7px 0 0;color:#71818e;font-size:12px;line-height:1.6}
    .agency-report-link {display:inline-flex;align-items:center;gap:9px;padding:12px 17px;background:#164c96;color:white;border-radius:11px;font-size:12px;font-weight:750;box-shadow:0 7px 18px rgba(22,76,150,.15)}
    .agency-report-link:hover {background:#0b315f;color:white}
    .agency-report-link svg {width:17px;height:17px}
    .agency-summary {margin-bottom:20px}
    .agency-panels {display:grid;gap:20px}
    .agency-table {min-width:850px}
    .agency-table .table-primary {display:block}
    .agency-table .table-secondary {display:block;margin-top:4px}
    .agency-state {display:inline-block;padding:5px 9px;border-radius:8px;font-size:10px;font-weight:800;background:#edf3fa;color:#164c96}
    .agency-state.certified,.agency-state.active {background:#e9f7ef;color:#16854d}
    .agency-state.pending {background:#fff5df;color:#a46a0a}
    .agency-state.annulled,.agency-state.inactive {background:#fcebec;color:#b33a3a}
    .agency-empty {padding:28px 20px;text-align:center;color:#82909a;font-size:12px}
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

<section id="supervision-agencias" aria-labelledby="agency-title">
    <header class="agency-heading">
        <div>
            <span class="agency-section-marker">Agencias ECOSABA</span>
            <h2 id="agency-title">Supervisión institucional de agencias</h2>
            <p>Indicadores operativos del {{ \Illuminate\Support\Carbon::parse($fechaAgencias)->locale('es')->translatedFormat('d \d\e F \d\e Y') }} · Consulta institucional de todas las agencias.</p>
        </div>
        <a class="agency-report-link" href="{{ route('agencias.reportes.index') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16M6 16V9M12 16V5M18 16v-4"/></svg>
            Centro de Reportes
        </a>
    </header>
    <div class="summary-grid agency-summary">
        <article class="summary-card info">
            <div class="summary-card-header">
                <span class="summary-label">Agencias activas</span>
                <span class="summary-icon "><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 21V9h8v12M7 7h.01M17 7h.01"/></svg></span>
            </div>
            <strong class="summary-value">{{ number_format($indicadoresAgencias['agencias']) }}</strong>
            <span class="summary-description">Edificios habilitados para operar.</span>
        </article>
        <article class="summary-card ">
            <div class="summary-card-header">
                <span class="summary-label">Ventanillas activas</span>
                <span class="summary-icon "><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 11h18M8 15h3"/></svg></span>
            </div>
            <strong class="summary-value">{{ number_format($indicadoresAgencias['ventanillas']) }}</strong>
            <span class="summary-description">Ventanillas habilitadas.</span>
        </article>
        <article class="summary-card ">
            <div class="summary-card-header">
                <span class="summary-label">Arqueos hoy</span>
                <span class="summary-icon "><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span>
            </div>
            <strong class="summary-value">{{ number_format($indicadoresAgencias['arqueos_hoy']) }}</strong>
            <span class="summary-description">Registros de agencias durante el día.</span>
        </article>
        <article class="summary-card success">
            <div class="summary-card-header">
                <span class="summary-label">Certificados hoy</span>
                <span class="summary-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg></span>
            </div>
            <strong class="summary-value">{{ number_format($indicadoresAgencias['certificados_hoy']) }}</strong>
            <span class="summary-description">Arqueos certificados en agencias.</span>
        </article>
        <article class="summary-card warning">
            <div class="summary-card-header">
                <span class="summary-label">Pendientes de certificar</span>
                <span class="summary-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            </div>
            <strong class="summary-value">{{ number_format($indicadoresAgencias['pendientes_hoy']) }}</strong>
            <span class="summary-description">Arqueos en espera de certificación.</span>
        </article>
        <article class="summary-card danger">
            <div class="summary-card-header">
                <span class="summary-label">Con faltante</span>
                <span class="summary-icon red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18m-5-5 5 5 5-5"/></svg></span>
            </div>
            <strong class="summary-value">{{ number_format($indicadoresAgencias['faltantes_hoy']) }}</strong>
            <span class="summary-description">Arqueos con diferencia negativa.</span>
        </article>
        <article class="summary-card success">
            <div class="summary-card-header">
                <span class="summary-label">Con sobrante</span>
                <span class="summary-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V3m-5 5 5-5 5 5"/></svg></span>
            </div>
            <strong class="summary-value">{{ number_format($indicadoresAgencias['sobrantes_hoy']) }}</strong>
            <span class="summary-description">Arqueos con diferencia positiva.</span>
        </article>
        <article class="summary-card danger">
            <div class="summary-card-header">
                <span class="summary-label">Anulados hoy</span>
                <span class="summary-icon red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6m0-6-6 6"/></svg></span>
            </div>
            <strong class="summary-value">{{ number_format($indicadoresAgencias['anulados_hoy']) }}</strong>
            <span class="summary-description">Arqueos anulados durante el día.</span>
        </article>
    </div>
    @php
        $turnosExigiblesInstitucional = $resumenAgencias->sum(fn ($agencia) => (int) ($agencia->arqueos_esperados ?? 0));
        $turnosCumplidosInstitucional = $resumenAgencias->sum(fn ($agencia) => (int) ($agencia->arqueos_cumplidos ?? 0));
        $turnosPendientesInstitucional = max(0, $turnosExigiblesInstitucional - $turnosCumplidosInstitucional);
        $porcentajeInstitucional = $turnosExigiblesInstitucional > 0
            ? min(100, round($turnosCumplidosInstitucional * 100 / $turnosExigiblesInstitucional, 1))
            : null;
    @endphp
    <section class="content-grid" aria-label="Cumplimiento y últimos arqueos de agencias" style="margin-bottom:22px">
        <article class="panel">
            <header class="panel-header">
                <div class="panel-title">
                    <h3>Cumplimiento diario de agencias</h3>
                    <p>Turnos finalizados con arqueo ordinario registrado.</p>
                </div>
            </header>
            <div class="panel-body">
                <div class="compliance-card">
                    <div class="progress-title">
                        <span>Nivel de cumplimiento</span>
                        <strong>{{ $porcentajeInstitucional === null ? 'N/A' : number_format($porcentajeInstitucional, 1) . '%' }}</strong>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-value" style="width:{{ $porcentajeInstitucional ?? 0 }}%;"></div>
                    </div>
                    <p class="compliance-description">
                        @if($porcentajeInstitucional === null)
                            Aún no hay turnos exigibles para calcular el porcentaje.
                        @else
                            Porcentaje de turnos exigibles con arqueo ordinario vigente.
                        @endif
                    </p>
                </div>
                <div class="compliance-info">
                    <div class="mini-stat"><span>Con arqueo</span><strong>{{ $turnosCumplidosInstitucional }}</strong></div>
                    <div class="mini-stat"><span>Pendientes</span><strong>{{ $turnosPendientesInstitucional }}</strong></div>
                    <div class="mini-stat"><span>Turnos exigibles</span><strong>{{ $turnosExigiblesInstitucional }}</strong></div>
                    <div class="mini-stat"><span>Agencias activas</span><strong>{{ $indicadoresAgencias['agencias'] }}</strong></div>
                </div>
                <div class="executive-note">
                    <strong>Lectura ejecutiva:</strong> el indicador considera únicamente los turnos cuyo cierre ya transcurrió. Los turnos futuros no se contabilizan como incumplidos.
                </div>
            </div>
        </article>
        <article class="panel">
            <header class="panel-header">
                <div class="panel-title">
                    <h3>Últimos Arqueos de Agencias</h3>
                    <p>Actividad reciente registrada en las agencias.</p>
                </div>
                <a class="panel-link" href="{{ route('agencias.reportes.index') }}">Ver todos</a>
            </header>
            <div class="table-responsive">
                <table class="recent-table agency-table">
                    <thead><tr><th>Número</th><th>Fecha</th><th>Agencia</th><th>Receptor</th><th>Tipo</th><th>Estado</th><th>Diferencia</th></tr></thead>
                    <tbody>
                    @forelse($ultimosArqueosAgencias as $arqueo)
                        <tr>
                            <td><span class="table-primary">{{ $arqueo->numero_arqueo }}</span></td>
                            <td class="table-secondary">{{ \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y') }}</td>
                            <td><span class="table-primary">{{ $arqueo->agencia_nombre_historico }}</span></td>
                            <td class="table-secondary">{{ $arqueo->receptor_nombre_historico }}</td>
                            <td><span class="type-badge agent">{{ str_replace('_', ' ', $arqueo->tipo) }}</span></td>
                            <td><span class="agency-state {{ $arqueo->estado === 'CERTIFICADO' ? 'certified' : ($arqueo->estado === 'ANULADO' ? 'annulled' : 'pending') }}">{{ $arqueo->estado }}</span></td>
                            <td><span class="{{ $arqueo->diferencia < 0 ? 'diff-negative' : ($arqueo->diferencia > 0 ? 'diff-positive' : 'diff-zero') }}">Q {{ number_format(abs((float) $arqueo->diferencia), 2) }} {{ $arqueo->diferencia < 0 ? 'Faltante' : ($arqueo->diferencia > 0 ? 'Sobrante' : 'Exacto') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="agency-empty">No hay arqueos registrados en agencias.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
    <div class="agency-panels">
        <article class="panel">
            <header class="panel-header">
                <div class="panel-title"><h3>Resumen por agencia — hoy</h3><p>Seguimiento de arqueos, certificaciones y cumplimiento de turnos por agencia.</p></div>
                <a class="panel-link" href="{{ route('agencias.reportes.index') }}">Ver reportes &rarr;</a>
            </header>
            <div class="table-responsive">
                <table class="recent-table agency-table">
                    <thead><tr><th>Agencia</th><th>Estado</th><th>Cumplimiento</th><th>Arqueos</th><th>Certificados</th><th>Pendientes</th><th>Faltantes</th><th>Sobrantes</th><th>Reportes</th></tr></thead>
                    <tbody>
                        @forelse($resumenAgencias as $agencia)
                            @php
                                $esperados = (int) ($agencia->arqueos_esperados ?? 0);
                                $cumplidos = (int) ($agencia->arqueos_cumplidos ?? 0);
                                $porcentajeAgencia = $esperados > 0 ? round(min(100, ($cumplidos / $esperados) * 100), 1) : null;
                            @endphp
                            <tr>
                                <td><span class="table-primary">{{ $agencia->nombre }}</span><span class="table-secondary">{{ $agencia->codigo }}</span></td>
                                <td><span class="agency-state {{ $agencia->estado ? 'active' : 'inactive' }}">{{ $agencia->estado ? 'Activa' : 'Inactiva' }}</span></td>
                                <td>
                                    <div class="agency-compliance" aria-label="{{ $porcentajeAgencia === null ? 'Sin turnos exigibles' : 'Cumplimiento de ' . $porcentajeAgencia . ' por ciento' }}">
                                        <div class="agency-compliance-track"><div class="agency-compliance-fill {{ $porcentajeAgencia === null ? 'none' : ($porcentajeAgencia < 60 ? 'low' : '') }}" style="width:{{ $porcentajeAgencia ?? 0 }}%"></div></div>
                                        <strong>{{ $porcentajeAgencia === null ? 'N/A' : number_format($porcentajeAgencia, 1) . '%' }}</strong>
                                    </div>
                                    <span class="agency-compliance-detail">{{ $cumplidos }} de {{ $esperados }} turnos exigibles</span>
                                </td>
                                <td class="table-primary">{{ number_format($agencia->total_hoy) }}</td>
                                <td><span class="agency-state certified">{{ number_format($agencia->certificados_hoy) }}</span></td>
                                <td><span class="agency-state pending">{{ number_format($agencia->pendientes_hoy) }}</span></td>
                                <td class="diff-negative">{{ number_format($agencia->faltantes_hoy) }}</td>
                                <td class="diff-positive">{{ number_format($agencia->sobrantes_hoy) }}</td>
                                <td><a class="panel-link" href="{{ route('agencias.reportes.index', ['agencia_id' => $agencia->id]) }}">Consultar &rarr;</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="agency-empty">No hay agencias registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="agency-compliance-note">El porcentaje compara turnos programados cuyo horario de cierre ya transcurrió contra turnos con arqueo ordinario vigente registrado. Los turnos aún no finalizados no se consideran incumplidos.</p>
        </article>

    </div>
</section>
@endsection
