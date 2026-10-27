@extends('layouts.administrador')

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

    .dashboard-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.55fr)
            minmax(320px, .65fr);
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

    .table-responsive {
        overflow-x: auto;
    }

    .recent-table {
        width: 100%;
        min-width: 960px;
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

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge.success {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .badge.warning {
        background: #fff5d9;
        color: #8b6500;
    }

    .badge.danger {
        background: #fdecec;
        color: #b13c3c;
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

    .role-list {
        padding: 8px 20px 18px;
    }

    .role-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 13px 0;
        border-bottom: 1px solid #edf1f4;
    }

    .role-row:last-child {
        border-bottom: 0;
    }

    .role-info {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .role-icon {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 10px;
        background: rgba(22, 76, 150, .08);
        color: #164c96;
    }

    .role-icon svg {
        width: 17px;
        height: 17px;
    }

    .role-row span {
        color: #556b7c;
        font-size: 11px;
        font-weight: 700;
    }

    .role-row strong {
        color: #0a3158;
        font-size: 14px;
    }

    .role-total {
        min-width: 36px;
        padding: 6px 9px;
        border-radius: 999px;
        background: #edf3f8;
        color: #164c96;
        text-align: center;
        font-size: 10px;
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
                repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid {
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

        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $authUser = auth()->user();

    $nombreAdministrador =
        $authUser?->nombre_completo
        ?? $authUser?->usuario
        ?? 'Administrador';
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Panel de Administración</h2>

        <p>
            Supervise usuarios, agentes, arqueos y la estructura
            operativa general del sistema.
        </p>
    </div>

    <div class="page-badge">
        <span class="page-badge-dot"></span>
        Administración activa
    </div>
</div>

<section class="welcome-panel">
    <div class="welcome-content">
        <span>Administración del sistema</span>

        <h2>{{ $nombreAdministrador }}</h2>

        <p>
            Desde este panel puede consultar el estado general del sistema,
            supervisar usuarios y agentes, revisar la actividad reciente
            y acceder a los módulos administrativos principales.
        </p>
    </div>

    <div class="welcome-status">
        <span class="status-dot"></span>
        Sesión institucional segura
    </div>
</section>

<section class="summary-grid">
    <article class="summary-card info">
        <div class="summary-card-header">
            <span class="summary-label">Total Usuarios</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M19 8v6"/>
                    <path d="M16 11h6"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->total_usuarios }}</strong>
        <span class="summary-description">Usuarios registrados en el sistema.</span>
    </article>

    <article class="summary-card success">
        <div class="summary-card-header">
            <span class="summary-label">Usuarios Activos</span>

            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m8 12 3 3 5-6"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->usuarios_activos }}</strong>
        <span class="summary-description">Cuentas habilitadas actualmente.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Total Agentes</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M2 21a7 7 0 0 1 14 0"/>
                    <path d="M17 11h5"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->total_agentes }}</strong>
        <span class="summary-description">Agentes registrados en la plataforma.</span>
    </article>

    <article class="summary-card success">
        <div class="summary-card-header">
            <span class="summary-label">Agentes Activos</span>

            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M2 21a7 7 0 0 1 14 0"/>
                    <path d="m17 14 2 2 3-4"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->agentes_activos }}</strong>
        <span class="summary-description">Agentes disponibles para operación.</span>
    </article>

    <article class="summary-card info">
        <div class="summary-card-header">
            <span class="summary-label">Promotores</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="m17 11 2 2 3-4"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->total_promotores }}</strong>
        <span class="summary-description">Promotores registrados en el sistema.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Rutas Activas</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19c4-8 12-8 16-14"/>
                    <circle cx="5" cy="19" r="2"/>
                    <circle cx="19" cy="5" r="2"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->rutas_activas }}</strong>
        <span class="summary-description">Rutas habilitadas para operación.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Regiones Activas</span>

            <span class="summary-icon gray">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 21s7-5.3 7-12a7 7 0 1 0-14 0c0 6.7 7 12 7 12Z"/>
                    <circle cx="12" cy="9" r="2.5"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->regiones_activas }}</strong>
        <span class="summary-description">Regiones operativas configuradas.</span>
    </article>

    <article class="summary-card success">
        <div class="summary-card-header">
            <span class="summary-label">Arqueos Hoy</span>

            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="M7 9h10"/>
                    <path d="M7 13h5"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->arqueos_hoy }}</strong>
        <span class="summary-description">Arqueos registrados durante el día.</span>
    </article>

    <article class="summary-card warning">
        <div class="summary-card-header">
            <span class="summary-label">Pendientes Certificación</span>

            <span class="summary-icon orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                    <path d="M10.3 3.7 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->pendientes_certificacion }}</strong>
        <span class="summary-description">Arqueos pendientes de certificación.</span>
    </article>

    <article class="summary-card danger">
        <div class="summary-card-header">
            <span class="summary-label">Arqueos Anulados</span>

            <span class="summary-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m9 9 6 6"/>
                    <path d="m15 9-6 6"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->arqueos_anulados }}</strong>
        <span class="summary-description">Arqueos anulados registrados.</span>
    </article>

    <article class="summary-card warning">
        <div class="summary-card-header">
            <span class="summary-label">Extemporáneos</span>

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

        <strong class="summary-value">{{ $resumen->extemporaneos }}</strong>
        <span class="summary-description">Arqueos fuera de fecha ordinaria.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Usuarios Inactivos</span>

            <span class="summary-icon gray">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M2 21a7 7 0 0 1 14 0"/>
                    <path d="M17 11h5"/>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $resumen->usuarios_inactivos }}</strong>
        <span class="summary-description">Cuentas actualmente deshabilitadas.</span>
    </article>
</section>

<section class="dashboard-grid">
    <article class="panel">
        <header class="panel-header">
            <div class="panel-title">
                <h3>Arqueos Recientes</h3>

                <p>
                    Últimos movimientos registrados en el sistema.
                </p>
            </div>

            @if(\Illuminate\Support\Facades\Route::has('administrador.arqueos.index'))
                <a
                    href="{{ route('administrador.arqueos.index') }}"
                    class="panel-link"
                >
                    Ver todos
                </a>
            @endif
        </header>

        @if($arqueosRecientes->isEmpty())
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
                            <th>Tipo</th>
                            <th>Responsable</th>
                            <th>Estado</th>
                            <th>Diferencia</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($arqueosRecientes as $arqueo)
                            @php
                                $responsable = trim(
                                    ($arqueo->responsable_nombres ?? '')
                                    . ' '
                                    . ($arqueo->responsable_apellidos ?? '')
                                );

                                $diferencia =
                                    (float) $arqueo->diferencia;

                                $badgeEstado =
                                    $arqueo->estado === 'CERTIFICADO'
                                        ? 'success'
                                        : (
                                            $arqueo->estado === 'ANULADO'
                                                ? 'danger'
                                                : 'warning'
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

                                $claseDiferencia =
                                    $diferencia < 0
                                        ? 'diff-negative'
                                        : (
                                            $diferencia > 0
                                                ? 'diff-positive'
                                                : 'diff-zero'
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

                                <td>
                                    <span class="type-badge {{ $tipoClass }}">
                                        {{ $tipoTexto }}
                                    </span>
                                </td>

                                <td class="table-secondary">
                                    {{ $responsable !== ''
                                        ? $responsable
                                        : (
                                            $arqueo->responsable_usuario
                                            ?? '—'
                                        ) }}
                                </td>

                                <td>
                                    <span class="badge {{ $badgeEstado }}">
                                        {{ str_replace(
                                            '_',
                                            ' ',
                                            $arqueo->estado
                                        ) }}
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

    <aside class="panel">
        <header class="panel-header">
            <div class="panel-title">
                <h3>Usuarios por Rol</h3>

                <p>
                    Distribución actual de usuarios del sistema.
                </p>
            </div>
        </header>

        @if($usuariosPorRol->isEmpty())
            <div class="empty-record">
                <div class="empty-record-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M2 21a7 7 0 0 1 14 0"/>
                    </svg>
                </div>

                <h4>No existen usuarios registrados</h4>

                <p>
                    La distribución por roles aparecerá
                    cuando existan usuarios disponibles.
                </p>
            </div>
        @else
            <div class="role-list">
                @foreach($usuariosPorRol as $rol)
                    <div class="role-row">
                        <div class="role-info">
                            <span class="role-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 21a8 8 0 0 1 16 0"/>
                                </svg>
                            </span>

                            <span>
                                {{ $rol->nombre }}
                            </span>
                        </div>

                        <strong class="role-total">
                            {{ $rol->total }}
                        </strong>
                    </div>
                @endforeach
            </div>
        @endif
    </aside>
</section>
@endsection
