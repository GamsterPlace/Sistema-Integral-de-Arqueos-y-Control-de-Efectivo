@extends('layouts.promotor')

@section('title', 'Dashboard')
@section('module-title', 'Dashboard')

@push('styles')
<style>
    .welcome-panel {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 24px;
        padding: 28px 30px;
        overflow: hidden;
        border-radius: 20px;
        background: linear-gradient(135deg, #07345f, #164c96 62%, #128257);
        color: #ffffff;
        box-shadow: 0 17px 36px rgba(10, 61, 109, 0.16);
    }

    .welcome-panel::before {
        content: "";
        position: absolute;
        top: -135px;
        right: -70px;
        width: 230px;
        height: 230px;
        border: 43px solid rgba(255, 255, 255, 0.055);
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
        color: rgba(255, 255, 255, 0.68);
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
        max-width: 650px;
        margin: 10px 0 0;
        color: rgba(255, 255, 255, .76);
        font-size: 13px;
        line-height: 1.6;
    }

    .welcome-status {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 0 0 auto;
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

    .summary-header {
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
        display: grid;
        place-items: center;
        width: 43px;
        height: 43px;
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
    }

    .summary-description {
        display: block;
        margin-top: 5px;
        color: #87949d;
        font-size: 11px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(340px, .9fr);
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
        text-decoration: none;
    }

    .panel-link:hover {
        color: #0b315f;
    }

    .panel-body {
        padding: 20px;
    }

    .route-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 13px;
    }

    .route-card,
    .record-item {
        padding: 14px;
        border: 1px solid #e8edf1;
        border-radius: 13px;
        background: #fafcfd;
        transition:
            transform .18s ease,
            border-color .18s ease,
            box-shadow .18s ease;
    }

    .route-card:hover,
    .record-item:hover {
        transform: translateY(-1px);
        border-color: #cfdce6;
        box-shadow:
            0 7px 16px rgba(20, 57, 83, 0.05);
    }

    .route-card span,
    .record-item span {
        color: #87949d;
        font-size: 10px;
    }

    .route-card strong,
    .record-item strong {
        display: block;
        margin-bottom: 5px;
        color: #19344d;
        font-size: 13px;
    }

    .record-list {
        display: grid;
        gap: 12px;
    }

    .record-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        text-decoration: none;
    }

    .record-main {
        min-width: 0;
    }

    .record-status {
        flex: 0 0 auto;
        padding: 7px 10px;
        border-radius: 999px;
        background: #fff5dc;
        color: #9c7014 !important;
        font-size: 9px !important;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .record-status.success {
        background: #eaf8ef;
        color: #1d7b4e !important;
    }

    .quick-actions {
        display: grid;
        gap: 12px;
    }

    .quick-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px;
        border: 1px solid #e8edf1;
        border-radius: 13px;
        background: #fafcfd;
        transition:
            transform .18s ease,
            border-color .18s ease,
            box-shadow .18s ease;
    }

    .quick-action:hover {
        transform: translateY(-1px);
        border-color: #cfdce6;
        box-shadow:
            0 7px 16px rgba(20, 57, 83, 0.05);
    }

    .quick-action-main {
        min-width: 0;
    }

    .quick-action-main strong {
        display: block;
        color: #19344d;
        font-size: 13px;
    }

    .quick-action-main span {
        display: block;
        margin-top: 5px;
        color: #87949d;
        font-size: 10px;
    }

    .quick-action-icon {
        flex: 0 0 auto;
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        border-radius: 10px;
        background: rgba(22, 76, 150, .09);
        color: #164c96;
    }

    .quick-action-icon svg {
        width: 18px;
        height: 18px;
    }

    .empty-record {
        padding: 28px 18px;
        color: #83919b;
        text-align: center;
        font-size: 11px;
        line-height: 1.55;
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
        margin: 7px auto 0;
        max-width: 340px;
        color: #83919b;
        font-size: 11px;
        line-height: 1.55;
    }

    @media (max-width: 1200px) {
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

        .welcome-status {
            width: fit-content;
            margin-top: 18px;
        }

        .summary-grid,
        .route-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">
        <h2>Panel del Promotor</h2>
        <p>Consulte sus rutas, agentes y arqueos pendientes.</p>
    </div>

    <div class="page-badge">
        <span class="page-badge-dot"></span>
        Cuenta activa
    </div>
</div>

<section class="welcome-panel">
    <div class="welcome-content">
        <span>Bienvenido al sistema</span>

        <h2>{{ $usuario->nombre_completo }}</h2>

        <p>
            Desde este panel podrá consultar sus agentes asignados,
            certificar arqueos diarios y realizar arqueos de visita.
        </p>
    </div>

    <div class="welcome-status">
        <span class="status-dot"></span>
        Sesión institucional segura
    </div>
</section>

<section class="summary-grid">

    <article class="summary-card">
        <div class="summary-header">
            <span class="summary-label">Rutas asignadas</span>

            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <path d="M4 19c4-8 12-8 16-14"></path>
                    <circle cx="5" cy="19" r="2"></circle>
                    <circle cx="19" cy="5" r="2"></circle>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $totalRutas }}</strong>
        <span class="summary-description">Rutas activas bajo su responsabilidad.</span>
    </article>

    <article class="summary-card">
        <div class="summary-header">
            <span class="summary-label">Agentes asignados</span>

            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $totalAgentes }}</strong>
        <span class="summary-description">Agentes activos en sus rutas.</span>
    </article>

    <article class="summary-card">
        <div class="summary-header">
            <span class="summary-label">Pendientes de certificación</span>

            <span class="summary-icon orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                    <path d="M10.3 3.7 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"></path>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $pendientesCertificacion }}</strong>
        <span class="summary-description">Arqueos diarios pendientes de revisión.</span>
    </article>

    <article class="summary-card">
        <div class="summary-header">
            <span class="summary-label">Agentes pendientes hoy</span>

            <span class="summary-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 7v5"></path>
                    <path d="M12 16h.01"></path>
                </svg>
            </span>
        </div>

        <strong class="summary-value">{{ $agentesPendientesHoy }}</strong>
        <span class="summary-description">
            {{ $agentesConArqueoHoy }} de {{ $totalAgentes }} realizaron arqueo.
        </span>
    </article>

</section>

<section class="dashboard-grid">
    <div>
        <article class="panel">
            <header class="panel-header">
                <div class="panel-title">
                    <h3>Rutas asignadas</h3>
                    <p>Rutas activas asignadas al promotor.</p>
                </div>
            </header>

            <div class="panel-body">
                @if ($rutasAsignadas->isEmpty())
                    <div class="empty-record">
                        <div class="empty-record-icon">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.9"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M4 19c4-8 12-8 16-14"/>
                                <circle cx="5" cy="19" r="2"/>
                                <circle cx="19" cy="5" r="2"/>
                            </svg>
                        </div>

                        <h4>Sin rutas asignadas</h4>

                        <p>
                            Cuando tenga rutas activas asignadas,
                            aparecerán en esta sección.
                        </p>
                    </div>
                @else
                    <div class="route-grid">
                        @foreach ($rutasAsignadas as $ruta)
                            <div class="route-card">
                                <strong>{{ $ruta->nombre }}</strong>
                                <span>
                                    {{ $ruta->codigo }} · {{ $ruta->region_nombre }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>

        <article class="panel">
            <header class="panel-header">
                <div class="panel-title">
                    <h3>Últimos arqueos de agentes</h3>
                    <p>Registros diarios más recientes de sus agentes.</p>
                </div>
            </header>

            <div class="panel-body">
                @if ($ultimosArqueosAgentes->isEmpty())
                    <div class="empty-record">
                        <div class="empty-record-icon">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.9"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="M7 9h10"/>
                                <path d="M7 13h5"/>
                            </svg>
                        </div>

                        <h4>Sin arqueos diarios</h4>

                        <p>
                            Los arqueos diarios recientes de sus agentes
                            aparecerán aquí.
                        </p>
                    </div>
                @else
                    <div class="record-list">
                        @foreach ($ultimosArqueosAgentes as $arqueo)
                            <div class="record-item">
                                <div class="record-main">
                                    <strong>{{ $arqueo->nombre_negocio_historico }}</strong>
                                    <span>
                                        {{ $arqueo->numero_arqueo }}
                                        · {{ $arqueo->fecha_arqueo->format('d/m/Y') }}
                                        · Q {{ number_format((float) $arqueo->total_arqueado, 2) }}
                                    </span>
                                </div>

                                <span class="record-status">
                                    {{ $arqueo->estado === 'CERTIFICADO' ? 'Certificado' : 'Pendiente' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>
    </div>

    <div>
        <article class="panel">
            <header class="panel-header">
                <div class="panel-title">
                    <h3>Actividad del promotor</h3>
                    <p>Resumen de sus arqueos de visita.</p>
                </div>
            </header>

            <div class="panel-body">
                <div class="record-list">
                    <div class="record-item">
                        <div class="record-main">
                            <strong>Arqueos realizados hoy</strong>
                            <span>Visitas registradas durante el día.</span>
                        </div>

                        <span class="record-status success">
                            {{ $arqueosPromotorHoy }}
                        </span>
                    </div>

                    <div class="record-item">
                        <div class="record-main">
                            <strong>Pendientes de firma del agente</strong>
                            <span>Arqueos de visita aún no validados.</span>
                        </div>

                        <span class="record-status">
                            {{ $pendientesFirmaAgente }}
                        </span>
                    </div>
                </div>
            </div>
        </article>

        <article class="panel">
            <header class="panel-header">
                <div class="panel-title">
                    <h3>Últimos arqueos realizados</h3>
                    <p>Visitas más recientes registradas por usted.</p>
                </div>
            </header>

            <div class="panel-body">
                @if ($ultimosArqueosPromotor->isEmpty())
                    <div class="empty-record">
                        <div class="empty-record-icon">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.9"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M6 2h9l4 4v16H6z"/>
                                <path d="M14 2v5h5"/>
                                <path d="M9 13h6"/>
                                <path d="M9 17h6"/>
                            </svg>
                        </div>

                        <h4>Sin arqueos de visita</h4>

                        <p>
                            Cuando realice su primer arqueo de visita,
                            aparecerá en esta sección.
                        </p>
                    </div>
                @else
                    <div class="record-list">
                        @foreach ($ultimosArqueosPromotor as $arqueo)
                            <div class="record-item">
                                <div class="record-main">
                                    <strong>{{ $arqueo->nombre_negocio_historico }}</strong>
                                    <span>
                                        {{ $arqueo->numero_arqueo }}
                                        · {{ $arqueo->fecha_arqueo->format('d/m/Y') }}
                                    </span>
                                </div>

                                <span class="record-status">
                                    {{ $arqueo->estado === 'CERTIFICADO' ? 'Certificado' : 'Pendiente' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>
    </div>
</section>

@endsection
