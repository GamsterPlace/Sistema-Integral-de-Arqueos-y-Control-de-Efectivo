@extends('layouts.jefe')

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
        box-shadow: 0 17px 36px rgba(10, 61, 109, .16);
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
        max-width: 680px;
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
        box-shadow: 0 0 0 5px rgba(115, 225, 160, .13);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
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
        box-shadow: 0 8px 24px rgba(20, 57, 83, .055);
    }

    .summary-card.warning {
        border-color: #efd99f;
        background: #fffdf6;
    }

    .summary-card.danger {
        border-color: #efc5c5;
        background: #fffafa;
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

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(320px, .65fr);
        gap: 20px;
    }

    .panel {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 25px rgba(20, 57, 83, .05);
    }

    .panel + .panel {
        margin-top: 20px;
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

    .table-wrapper {
        overflow-x: auto;
    }

    .recent-table {
        width: 100%;
        min-width: 760px;
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
        color: #6f808d;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .45px;
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

    .badge {
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

    .badge.agent {
        background: #eaf2fb;
        color: #195b9b;
    }

    .badge.promoter {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .badge.certified {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .badge.pending {
        background: #fff5dc;
        color: #9c7014;
    }

    .badge.cancelled {
        background: #fff0f0;
        color: #ad3b3b;
    }

    .quick-grid {
        display: grid;
        gap: 12px;
    }

    .quick-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px;
        border: 1px solid #e8edf1;
        border-radius: 13px;
        background: #fafcfd;
        color: #173b59;
        transition:
            transform .18s ease,
            border-color .18s ease,
            box-shadow .18s ease;
    }

    .quick-link:hover {
        transform: translateY(-1px);
        border-color: #cfdce6;
        box-shadow: 0 7px 16px rgba(20, 57, 83, .05);
    }

    .quick-link-content {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .quick-link-icon {
        flex: 0 0 auto;
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 11px;
        background: rgba(22, 76, 150, .09);
        color: #164c96;
    }

    .quick-link-icon.green {
        background: rgba(0, 166, 81, .10);
        color: #009349;
    }

    .quick-link-icon.orange {
        background: rgba(224, 151, 14, .11);
        color: #b47c0e;
    }

    .quick-link-icon.red {
        background: rgba(194, 57, 52, .10);
        color: #b83b36;
    }

    .quick-link-icon svg {
        width: 19px;
        height: 19px;
    }

    .quick-link-copy {
        min-width: 0;
    }

    .quick-link-copy strong {
        display: block;
        color: #19344d;
        font-size: 12px;
    }

    .quick-link-copy span {
        display: block;
        margin-top: 4px;
        color: #87949d;
        font-size: 10px;
        font-weight: 500;
        line-height: 1.4;
    }

    .quick-arrow {
        flex: 0 0 auto;
        color: #8da0ae;
    }

    .quick-arrow svg {
        width: 17px;
        height: 17px;
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
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 1050px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
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

        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $authUser = auth()->user();

    $nombreJefe =
        $authUser?->nombre_completo
        ?? $authUser?->usuario
        ?? 'Jefe de Agentes';
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Panel de Supervisión</h2>

        <p>
            Supervise el estado operativo de agentes, promotores,
            rutas, regiones y arqueos desde un único punto de control.
        </p>
    </div>

    <div class="page-badge">
        <span class="page-badge-dot"></span>
        Supervisión activa
    </div>
</div>

<section class="welcome-panel">
    <div class="welcome-content">
        <span>Bienvenido al sistema</span>

        <h2>{{ $nombreJefe }}</h2>

        <p>
            Desde este panel puede supervisar la operación diaria,
            consultar el avance de los arqueos, gestionar excepciones
            y acceder rápidamente a las funciones principales del área.
        </p>
    </div>

    <div class="welcome-status">
        <span class="status-dot"></span>
        Sesión institucional segura
    </div>
</section>

<section class="summary-grid">
    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Agentes activos</span>

            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M19 8v6"/>
                    <path d="M16 11h6"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $totalAgentes }}</strong>
        <span class="summary-description">Agentes activos registrados.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Promotores activos</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M17 11l2 2 3-4"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $totalPromotores }}</strong>
        <span class="summary-description">Promotores habilitados en el sistema.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Rutas activas</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19c4-8 12-8 16-14"/>
                    <circle cx="5" cy="19" r="2"/>
                    <circle cx="19" cy="5" r="2"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $totalRutas }}</strong>
        <span class="summary-description">Rutas disponibles para operación.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Regiones activas</span>

            <span class="summary-icon gray">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 21s7-5.3 7-12a7 7 0 1 0-14 0c0 6.7 7 12 7 12Z"/>
                    <circle cx="12" cy="9" r="2.5"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $totalRegiones }}</strong>
        <span class="summary-description">Regiones activas configuradas.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Arqueos realizados hoy</span>

            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="M7 9h10"/>
                    <path d="M7 13h5"/>
                    <path d="m16 15 2 2 3-4"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $arqueosHoy }}</strong>
        <span class="summary-description">Total registrado durante el día.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Arqueos de agentes hoy</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2h9l4 4v16H6z"/>
                    <path d="M14 2v5h5"/>
                    <path d="M9 13h6"/>
                    <path d="M9 17h4"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $arqueosAgentesHoy }}</strong>
        <span class="summary-description">Arqueos diarios realizados por agentes.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Arqueos de promotor hoy</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2h9l4 4v16H6z"/>
                    <path d="M14 2v5h5"/>
                    <circle cx="12" cy="14" r="2.5"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $arqueosPromotorHoy }}</strong>
        <span class="summary-description">Arqueos de visita registrados hoy.</span>
    </article>

    <article class="summary-card warning">
        <div class="summary-card-header">
            <span class="summary-label">Pendientes de certificación</span>

            <span class="summary-icon orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                    <path d="M10.3 3.7 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $pendientesCertificacion }}</strong>
        <span class="summary-description">Arqueos pendientes de revisión o certificación.</span>
    </article>

    <article class="summary-card danger">
        <div class="summary-card-header">
            <span class="summary-label">Agentes sin arqueo hoy</span>

            <span class="summary-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5"/>
                    <path d="M12 16h.01"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $agentesSinArqueoHoy }}</strong>
        <span class="summary-description">Agentes activos sin arqueo registrado hoy.</span>
    </article>
</section>

<section class="dashboard-grid">
    <div>
        <article class="panel">
            <header class="panel-header">
                <div class="panel-title">
                    <h3>Últimos arqueos registrados</h3>
                    <p>Movimientos recientes dentro del sistema.</p>
                </div>

                <a
                    href="{{ url('/jefe-agentes/estado-arqueos') }}"
                    class="panel-link"
                >
                    Ver estado
                </a>
            </header>

            @if ($ultimosArqueos->isEmpty())
                <div class="empty-record">
                    <div class="empty-record-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M7 9h10"/>
                            <path d="M7 13h5"/>
                        </svg>
                    </div>

                    <h4>No existen arqueos registrados</h4>

                    <p>
                        Los arqueos recientes de agentes y promotores
                        aparecerán en esta sección.
                    </p>
                </div>
            @else
                <div class="table-wrapper">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Agente</th>
                                <th>Ruta</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($ultimosArqueos as $arqueo)
                                @php
                                    $estado = strtoupper(
                                        (string) $arqueo->estado
                                    );

                                    $estadoClass =
                                        $estado === 'CERTIFICADO'
                                            ? 'certified'
                                            : (
                                                $estado === 'ANULADO'
                                                    ? 'cancelled'
                                                    : 'pending'
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
                                        {{ $arqueo->ruta_nombre ?? 'Sin ruta' }}
                                    </td>

                                    <td>
                                        <span class="badge {{
                                            $arqueo->tipo === 'DIARIO_AGENTE'
                                                ? 'agent'
                                                : 'promoter'
                                        }}">
                                            {{
                                                $arqueo->tipo === 'DIARIO_AGENTE'
                                                    ? 'Agente'
                                                    : 'Promotor'
                                            }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge {{ $estadoClass }}">
                                            {{ str_replace(
                                                '_',
                                                ' ',
                                                $estado
                                            ) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
    </div>

    <aside>
        <article class="panel">
            <header class="panel-header">
                <div class="panel-title">
                    <h3>Accesos rápidos</h3>
                    <p>Funciones principales de supervisión.</p>
                </div>
            </header>

            <div class="panel-body">
                <div class="quick-grid">
                    <a
                        href="{{ url('/jefe-agentes/estado-arqueos') }}"
                        class="quick-link"
                    >
                        <div class="quick-link-content">
                            <span class="quick-link-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19h16"/>
                                    <path d="M6 16V9"/>
                                    <path d="M12 16V5"/>
                                    <path d="M18 16v-4"/>
                                </svg>
                            </span>

                            <div class="quick-link-copy">
                                <strong>Estado de Arqueos</strong>
                                <span>Supervisar el avance operativo diario.</span>
                            </div>
                        </div>

                        <span class="quick-arrow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </span>
                    </a>

                    <a
                        href="{{ url('/jefe-agentes/arqueos-agentes') }}"
                        class="quick-link"
                    >
                        <div class="quick-link-content">
                            <span class="quick-link-icon green">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M2 21a7 7 0 0 1 14 0"/>
                                    <path d="M17 11h5"/>
                                </svg>
                            </span>

                            <div class="quick-link-copy">
                                <strong>Arqueos por Agente</strong>
                                <span>Consultar registros individuales.</span>
                            </div>
                        </div>

                        <span class="quick-arrow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </span>
                    </a>

                    <a
                        href="{{ url('/jefe-agentes/arqueos-promotores') }}"
                        class="quick-link"
                    >
                        <div class="quick-link-content">
                            <span class="quick-link-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="m17 11 2 2 3-4"/>
                                </svg>
                            </span>

                            <div class="quick-link-copy">
                                <strong>Arqueos por Promotor</strong>
                                <span>Revisar arqueos de visita realizados.</span>
                            </div>
                        </div>

                        <span class="quick-arrow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </span>
                    </a>

                    <a
                        href="{{ url('/jefe-agentes/certificaciones') }}"
                        class="quick-link"
                    >
                        <div class="quick-link-content">
                            <span class="quick-link-icon orange">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2h9l4 4v16H6z"/>
                                    <path d="M14 2v5h5"/>
                                    <path d="m9 15 2 2 4-5"/>
                                </svg>
                            </span>

                            <div class="quick-link-copy">
                                <strong>Certificaciones Pendientes</strong>
                                <span>Atender arqueos pendientes de revisión.</span>
                            </div>
                        </div>

                        <span class="quick-arrow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </span>
                    </a>

                    <a
                        href="{{ url('/jefe-agentes/habilitaciones-atrasadas') }}"
                        class="quick-link"
                    >
                        <div class="quick-link-content">
                            <span class="quick-link-icon red">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 7v5l3 2"/>
                                    <path d="M8 3 5 6"/>
                                </svg>
                            </span>

                            <div class="quick-link-copy">
                                <strong>Habilitar Arqueo Atrasado</strong>
                                <span>Gestionar excepciones de fechas anteriores.</span>
                            </div>
                        </div>

                        <span class="quick-arrow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </article>
    </aside>
</section>
@endsection
