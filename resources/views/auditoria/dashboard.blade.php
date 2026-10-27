@extends('layouts.auditoria')

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
        background: linear-gradient(135deg, #07345f, #164c96 62%, #128257);
        color: #fff;
        box-shadow: 0 17px 36px rgba(10,61,109,.16);
    }
    .welcome-panel::before {
        content:"";
        position:absolute;
        width:230px;height:230px;
        top:-145px;right:-75px;
        border:43px solid rgba(255,255,255,.055);
        border-radius:50%;
    }
    .welcome-content,.welcome-status{position:relative;z-index:2}
    .welcome-content span {
        display:block;margin-bottom:6px;color:rgba(255,255,255,.68);
        font-size:11px;font-weight:750;letter-spacing:.8px;text-transform:uppercase;
    }
    .welcome-content h2{margin:0;font-size:27px;letter-spacing:-.65px}
    .welcome-content p {
        max-width:700px;margin:10px 0 0;color:rgba(255,255,255,.75);
        font-size:13px;line-height:1.6;
    }
    .welcome-status {
        flex:0 0 auto;display:flex;align-items:center;gap:10px;padding:11px 15px;
        border:1px solid rgba(255,255,255,.15);border-radius:13px;
        background:rgba(255,255,255,.10);font-size:12px;font-weight:750;
    }
    .status-dot {
        width:9px;height:9px;border-radius:50%;background:#73e1a0;
        box-shadow:0 0 0 5px rgba(115,225,160,.13);
    }

    .summary-grid {
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:18px;margin-bottom:24px;
    }
    .summary-card {
        min-height:142px;padding:19px;border:1px solid #e0e8ee;
        border-radius:17px;background:#fff;
        box-shadow:0 8px 24px rgba(20,57,83,.055);
    }
    .summary-card.success{border-color:#c5e5d1;background:#f7fcf9}
    .summary-card.warning{border-color:#efd99f;background:#fffdf6}
    .summary-card.danger{border-color:#efc5c5;background:#fffafa}
    .summary-card.info{border-color:#c9dceb;background:#f7fbff}
    .summary-card-header {
        display:flex;align-items:center;justify-content:space-between;gap:12px;
    }
    .summary-label {
        color:#71818e;font-size:11px;font-weight:750;letter-spacing:.55px;
        text-transform:uppercase;
    }
    .summary-icon {
        flex:0 0 auto;width:43px;height:43px;display:grid;place-items:center;
        border-radius:13px;background:rgba(22,76,150,.09);color:#164c96;
    }
    .summary-icon.green{background:rgba(0,166,81,.10);color:#009349}
    .summary-icon.orange{background:rgba(224,151,14,.11);color:#b47c0e}
    .summary-icon.red{background:rgba(194,57,52,.10);color:#b83b36}
    .summary-icon.gray{background:#eef2f5;color:#71808c}
    .summary-icon svg{width:22px;height:22px}
    .summary-value {
        display:block;margin-top:16px;color:#082d55;font-size:23px;
        font-weight:800;letter-spacing:-.5px;
    }
    .summary-description {
        display:block;margin-top:5px;color:#87949d;font-size:11px;line-height:1.45;
    }

    .dashboard-grid {
        display:grid;
        grid-template-columns:minmax(0,1.6fr) minmax(300px,.65fr);
        gap:20px;
    }
    .panel {
        overflow:hidden;border:1px solid #e0e8ee;border-radius:18px;
        background:#fff;box-shadow:0 8px 25px rgba(20,57,83,.05);
    }
    .panel-header {
        display:flex;align-items:center;justify-content:space-between;gap:15px;
        padding:18px 20px;border-bottom:1px solid #edf1f4;
    }
    .panel-title h3{margin:0;color:#0a3158;font-size:16px}
    .panel-title p{margin:5px 0 0;color:#82909a;font-size:11px}
    .panel-link{color:#164c96;font-size:11px;font-weight:750;white-space:nowrap}
    .panel-link:hover{color:#0b315f}

    .table-responsive{overflow-x:auto}
    .recent-table{width:100%;min-width:920px;border-collapse:collapse}
    .recent-table th,.recent-table td {
        padding:13px 14px;border-bottom:1px solid #edf1f4;
        text-align:left;vertical-align:middle;font-size:11px;
    }
    .recent-table th {
        background:#f8fafb;color:#687b8b;font-size:9px;font-weight:800;
        letter-spacing:.4px;text-transform:uppercase;
    }
    .recent-table tbody tr{transition:background .18s ease}
    .recent-table tbody tr:hover{background:#fafcfd}
    .recent-table tbody tr:last-child td{border-bottom:0}
    .table-primary{color:#19344d;font-weight:800}
    .table-secondary{color:#7d8c97}

    .badge {
        display:inline-flex;align-items:center;padding:7px 10px;border-radius:999px;
        font-size:8px;font-weight:800;letter-spacing:.35px;text-transform:uppercase;
        white-space:nowrap;
    }
    .badge.success{background:#eaf8ef;color:#1d7b4e}
    .badge.warning{background:#fff5d9;color:#8b6500}
    .badge.danger{background:#fdecec;color:#b13c3c}
    .badge.neutral{background:#edf2f6;color:#50667a}

    .diff-negative{color:#b33a34;font-weight:800}
    .diff-positive{color:#16834f;font-weight:800}
    .diff-zero{color:#607586;font-weight:800}

    .audit-summary{padding:20px}
    .audit-highlight {
        padding:18px;border:1px solid #dfe8ef;border-radius:15px;
        background:#f8fbfd;margin-bottom:15px;
    }
    .audit-highlight span {
        display:block;color:#718392;font-size:9px;font-weight:800;
        text-transform:uppercase;letter-spacing:.45px;
    }
    .audit-highlight strong {
        display:block;margin-top:8px;color:#0a3158;font-size:30px;
    }
    .audit-highlight p {
        margin:5px 0 0;color:#82909a;font-size:10px;line-height:1.5;
    }
    .audit-mini-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:11px}
    .audit-mini {
        padding:14px;border:1px solid #e3eaef;border-radius:13px;background:#fbfcfd;
    }
    .audit-mini span {
        display:block;color:#758697;font-size:8px;font-weight:800;
        text-transform:uppercase;
    }
    .audit-mini strong {
        display:block;margin-top:6px;color:#173b59;font-size:18px;
    }
    .audit-note {
        margin-top:15px;padding:14px;border:1px solid #dfe8ef;border-radius:13px;
        background:#f8fbfd;color:#647786;font-size:10px;line-height:1.55;
    }
    .audit-note strong{color:#173b59}

    .empty-record{padding:32px 20px;text-align:center}
    .empty-record-icon {
        width:58px;height:58px;display:grid;place-items:center;margin:0 auto 14px;
        border-radius:17px;background:#edf3f8;color:#164c96;
    }
    .empty-record-icon svg{width:29px;height:29px}
    .empty-record h4{margin:0;color:#17364f;font-size:14px}
    .empty-record p {
        max-width:360px;margin:7px auto 0;color:#83919b;font-size:11px;line-height:1.55;
    }

    @media(max-width:1200px){
        .summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
        .dashboard-grid{grid-template-columns:1fr}
    }
    @media(max-width:700px){
        .welcome-panel{display:block;padding:23px 20px}
        .welcome-content h2{font-size:23px}
        .welcome-status{width:fit-content;margin-top:18px}
        .summary-grid,.audit-mini-grid{grid-template-columns:1fr}
    }
</style>
@endpush

@section('content')
@php
    $authUser = auth()->user();
    $nombreAuditor = $authUser?->nombre_completo ?? $authUser?->usuario ?? 'Auditoría';
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Panel de Auditoría</h2>
        <p>
            Consulte sus arqueos de auditoría, resultados encontrados
            y actividad reciente dentro del sistema.
        </p>
    </div>

    <div class="page-badge">
        <span class="page-badge-dot"></span>
        Auditoría activa
    </div>
</div>

<section class="welcome-panel">
    <div class="welcome-content">
        <span>Control y verificación</span>
        <h2>{{ $nombreAuditor }}</h2>
        <p>
            Desde este panel puede supervisar los arqueos realizados por Auditoría,
            identificar faltantes, sobrantes y resultados exactos, y dar seguimiento
            al historial de sus verificaciones.
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
            <span class="summary-label">Total Auditorías</span>
            <span class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </span>
        </div>
        <strong class="summary-value">{{ $resumen->total }}</strong>
        <span class="summary-description">Arqueos realizados por su usuario.</span>
    </article>

    <article class="summary-card success">
        <div class="summary-card-header">
            <span class="summary-label">Realizados Hoy</span>
            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M8 3v4M16 3v4M3 10h18"/>
                </svg>
            </span>
        </div>
        <strong class="summary-value">{{ $resumen->hoy }}</strong>
        <span class="summary-description">Auditorías válidas registradas hoy.</span>
    </article>

    <article class="summary-card success">
        <div class="summary-card-header">
            <span class="summary-label">Certificados</span>
            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m8 12 3 3 5-6"/>
                </svg>
            </span>
        </div>
        <strong class="summary-value">{{ $resumen->certificados }}</strong>
        <span class="summary-description">Arqueos de auditoría certificados.</span>
    </article>

    <article class="summary-card warning">
        <div class="summary-card-header">
            <span class="summary-label">Pendientes</span>
            <span class="summary-icon orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 2"/>
                </svg>
            </span>
        </div>
        <strong class="summary-value">{{ $resumen->pendientes }}</strong>
        <span class="summary-description">Pendientes de certificación.</span>
    </article>

    <article class="summary-card danger">
        <div class="summary-card-header">
            <span class="summary-label">Faltantes</span>
            <span class="summary-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <path d="M12 3v18"/>
                    <path d="m7 16 5 5 5-5"/>
                </svg>
            </span>
        </div>
        <strong class="summary-value">{{ $resumen->faltantes }}</strong>
        <span class="summary-description">Auditorías con diferencia negativa.</span>
    </article>

    <article class="summary-card success">
        <div class="summary-card-header">
            <span class="summary-label">Sobrantes</span>
            <span class="summary-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <path d="M12 21V3"/>
                    <path d="m7 8 5-5 5 5"/>
                </svg>
            </span>
        </div>
        <strong class="summary-value">{{ $resumen->sobrantes }}</strong>
        <span class="summary-description">Auditorías con diferencia positiva.</span>
    </article>

    <article class="summary-card">
        <div class="summary-card-header">
            <span class="summary-label">Exactos</span>
            <span class="summary-icon gray">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M8 12h8"/>
                </svg>
            </span>
        </div>
        <strong class="summary-value">{{ $resumen->exactos }}</strong>
        <span class="summary-description">Auditorías sin diferencia.</span>
    </article>

    <article class="summary-card danger">
        <div class="summary-card-header">
            <span class="summary-label">Anulados</span>
            <span class="summary-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m9 9 6 6M15 9l-6 6"/>
                </svg>
            </span>
        </div>
        <strong class="summary-value">{{ $resumen->anulados }}</strong>
        <span class="summary-description">Auditorías anuladas registradas.</span>
    </article>
</section>

<section class="dashboard-grid">
    <article class="panel">
        <header class="panel-header">
            <div class="panel-title">
                <h3>Auditorías Recientes</h3>
                <p>Últimos arqueos realizados por su usuario de Auditoría.</p>
            </div>

            @if(\Illuminate\Support\Facades\Route::has('auditoria.arqueos.index'))
                <a href="{{ route('auditoria.arqueos.index') }}" class="panel-link">
                    Ver historial
                </a>
            @endif
        </header>

        @if($arqueosRecientes->isEmpty())
            <div class="empty-record">
                <div class="empty-record-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
                <h4>No existen auditorías registradas</h4>
                <p>Sus arqueos de auditoría aparecerán aquí cuando existan registros.</p>
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
                            <th>Estado</th>
                            <th>Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($arqueosRecientes as $arqueo)
                            @php
                                $diferencia = (float) $arqueo->diferencia;

                                $badgeEstado =
                                    $arqueo->estado === 'CERTIFICADO'
                                        ? 'success'
                                        : ($arqueo->estado === 'ANULADO' ? 'danger' : 'warning');

                                $claseDiferencia =
                                    $diferencia < 0
                                        ? 'diff-negative'
                                        : ($diferencia > 0 ? 'diff-positive' : 'diff-zero');
                            @endphp

                            <tr>
                                <td><span class="table-primary">{{ $arqueo->numero_arqueo }}</span></td>
                                <td class="table-secondary">
                                    {{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}
                                </td>
                                <td>
                                    <span class="table-primary">{{ $arqueo->codigo_agente_historico ?? '—' }}</span>
                                    <span class="table-secondary">
                                        — {{ $arqueo->nombre_negocio_historico ?? 'Sin nombre' }}
                                    </span>
                                </td>
                                <td class="table-secondary">{{ $arqueo->region_historica ?? '—' }}</td>
                                <td class="table-secondary">{{ $arqueo->ruta_historica ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $badgeEstado }}">
                                        {{ str_replace('_', ' ', $arqueo->estado) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ $claseDiferencia }}">
                                        Q {{ number_format(abs($diferencia), 2) }}
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
                <h3>Resumen de Cobertura</h3>
                <p>Alcance acumulado de sus verificaciones.</p>
            </div>
        </header>

        <div class="audit-summary">
            <div class="audit-highlight">
                <span>Agentes auditados</span>
                <strong>{{ $agentesAuditados }}</strong>
                <p>
                    Cantidad de agentes distintos que cuentan con al menos
                    un arqueo válido realizado por su usuario.
                </p>
            </div>

            <div class="audit-mini-grid">
                <div class="audit-mini">
                    <span>Total arqueos</span>
                    <strong>{{ $resumen->total }}</strong>
                </div>

                <div class="audit-mini">
                    <span>Hoy</span>
                    <strong>{{ $resumen->hoy }}</strong>
                </div>

                <div class="audit-mini">
                    <span>Con diferencia</span>
                    <strong>{{ $resumen->faltantes + $resumen->sobrantes }}</strong>
                </div>

                <div class="audit-mini">
                    <span>Exactos</span>
                    <strong>{{ $resumen->exactos }}</strong>
                </div>
            </div>

            <div class="audit-note">
                <strong>Control de Auditoría:</strong>
                este panel muestra exclusivamente los arqueos
                <strong>VISITA_AUDITORIA</strong> realizados por el usuario
                de Auditoría autenticado.
            </div>
        </div>
    </aside>
</section>
@endsection
