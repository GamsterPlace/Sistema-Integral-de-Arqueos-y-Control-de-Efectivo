@extends('layouts.administrador')



@section('title', 'Dashboard')

@section('module-title', 'Dashboard de Administración')



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



/* Selector institucional de módulos */
.dashboard-selector {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin: 0 0 27px;
    padding: 8px;
    background: #edf3f8;
    border: 1px solid #dce7ef;
    border-radius: 19px;
    box-shadow: 0 5px 18px rgba(10, 49, 88, .05);
}
.dashboard-selector a {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
    padding: 15px 18px;
    border: 1px solid transparent;
    border-radius: 13px;
    color: #526b80;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    transition: background .18s ease, color .18s ease, box-shadow .18s ease;
}
.dashboard-selector a:hover { background: #fff; color: #164c96; }
.dashboard-selector a.active {
    background: linear-gradient(135deg, #164c96, #0b3769);
    color: #fff;
    box-shadow: 0 7px 18px rgba(22, 76, 150, .19);
}
.dashboard-selector .selector-icon {
    display: grid;
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    place-items: center;
    border-radius: 11px;
    background: rgba(22,76,150,.09);
}
.dashboard-selector a.active .selector-icon { background: rgba(255,255,255,.17); }
.dashboard-selector .selector-icon svg { width: 20px; height: 20px; }
.dashboard-selector .selector-text { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.dashboard-selector .selector-text small { font-size: 10px; font-weight: 500; opacity: .78; }
.dashboard-section-heading { margin: 7px 0 16px; color: #0a3158; font-size: 17px; font-weight: 800; }
.dashboard-section-subtitle { margin: -9px 0 18px; color: #7c8d9a; font-size: 12px; }
.dashboard-empty-value { font-size: 20px; color: #8a98a5; }
@media (max-width: 900px) {
    .dashboard-selector { grid-template-columns: 1fr; }
    .dashboard-selector a { padding: 11px 14px; }
}
</style>

@endpush



@section('content')

@php

    $authUser = auth()->user();

    $nombreAdministrador = $authUser?->nombre_completo ?? $authUser?->usuario ?? 'Administrador';

@endphp



<div class="page-header">

    <div class="page-title">

        <h2>Panel de Administración</h2>

        <p>Supervisión institucional de los arqueos de Agentes MICOOPE y Agencias ECOSABA.</p>

    </div>

    <div class="page-badge"><span class="page-badge-dot"></span> Administración activa</div>

</div>



<section class="welcome-panel">

    <div class="welcome-content">

        <span>Sistema Integral de Arqueos y Control de Efectivo</span>

        <h2>{{ $nombreAdministrador }}</h2>

        <p>Consulte la situación general del sistema o seleccione un módulo para examinar sus indicadores y arqueos recientes.</p>

    </div>

    <div class="welcome-status"><span class="status-dot"></span> Sesión institucional segura</div>

</section>



<nav class="dashboard-selector" aria-label="Seleccionar vista del dashboard">
    <a href="{{ route('administrador.dashboard', ['vista' => 'general']) }}" class="{{ $vista === 'general' ? 'active' : '' }}" @if($vista === 'general') aria-current="page" @endif>
        <span class="selector-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span>
        <span class="selector-text">Vista General<small>Resumen de todo el sistema</small></span>
    </a>
    <a href="{{ route('administrador.dashboard', ['vista' => 'agentes']) }}" class="{{ $vista === 'agentes' ? 'active' : '' }}" @if($vista === 'agentes') aria-current="page" @endif>
        <span class="selector-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M17 7h4M19 5v4M17 14h4M17 18h4"/></svg></span>
        <span class="selector-text">Agentes MICOOPE<small>Supervisión de agentes y promotores</small></span>
    </a>
    <a href="{{ route('administrador.dashboard', ['vista' => 'agencias']) }}" class="{{ $vista === 'agencias' ? 'active' : '' }}" @if($vista === 'agencias') aria-current="page" @endif>
        <span class="selector-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h1M14 10h1M9 14h1M14 14h1M10 21v-4h4v4"/></svg></span>
        <span class="selector-text">Agencias ECOSABA<small>Control de agencias y receptores</small></span>
    </a>
</nav>



@if($vista === 'general')

    <h3 class="dashboard-section-heading">Resumen institucional</h3>

    <section class="summary-grid">

        @foreach([

            ['Total de usuarios', $resumen->total_usuarios, 'Cuentas registradas en el sistema.'],

            ['Usuarios activos', $resumen->usuarios_activos, 'Cuentas actualmente habilitadas.'],

            ['Agentes MICOOPE', $agentes->total_agentes, 'Agentes registrados.'],

            ['Agencias ECOSABA', $agencias->total_agencias, 'Agencias registradas.'],

            ['Arqueos de agentes hoy', $agentes->arqueos_hoy, 'Arqueos no anulados del día.'],

            ['Arqueos de agencias hoy', $agencias->arqueos_hoy, 'Arqueos no anulados del día.'],

            ['Pendientes de agentes', $agentes->pendientes_certificacion, 'Pendientes de certificación.'],

            ['Pendientes de agencias', $agencias->pendientes_certificacion, 'Estados pendientes de certificación.'],

        ] as $indicador)

            <article class="summary-card">

                <div class="summary-card-header"><span class="summary-label">{{ $indicador[0] }}</span></div>

                <strong class="summary-value">{{ number_format($indicador[1]) }}</strong>

                <span class="summary-description">{{ $indicador[2] }}</span>

            </article>

        @endforeach

    </section>

    <h3 class="dashboard-section-heading">Seguimiento institucional</h3>

    <section class="summary-grid">

        @foreach([

            ['Agentes activos', $agentes->agentes_activos],

            ['Agencias activas', $agencias->agencias_activas],

            ['Arqueos anulados MICOOPE', $agentes->arqueos_anulados],

            ['Arqueos anulados ECOSABA', $agencias->arqueos_anulados],

        ] as $indicador)

            <article class="summary-card info">

                <span class="summary-label">{{ $indicador[0] }}</span>

                <strong class="summary-value">{{ $indicador[1] === null ? '—' : number_format($indicador[1]) }}</strong>

                <span class="summary-description">{{ $indicador[1] === null ? 'Estado no disponible en el esquema actual.' : 'Información registrada en el sistema.' }}</span>

            </article>

        @endforeach

    </section>

@endif



@if($vista === 'agentes')

    <h3 class="dashboard-section-heading">Operación de Agentes MICOOPE</h3>

    <section class="summary-grid">

        @foreach([

            ['Total agentes', $agentes->total_agentes, 'Agentes registrados.'],

            ['Agentes activos', $agentes->agentes_activos, 'Agentes habilitados.'],

            ['Promotores', $agentes->total_promotores, 'Promotores registrados.'],

            ['Rutas activas', $agentes->rutas_activas, 'Rutas operativas.'],

            ['Regiones activas', $agentes->regiones_activas, 'Regiones operativas.'],

            ['Arqueos hoy', $agentes->arqueos_hoy, 'Arqueos del día no anulados.'],

            ['Pendientes certificación', $agentes->pendientes_certificacion, 'Pendientes de certificación.'],

            ['Arqueos anulados', $agentes->arqueos_anulados, 'Total histórico anulado.'],

            ['Extemporáneos', $agentes->extemporaneos, 'Fuera de fecha ordinaria y no anulados.'],

        ] as $indicador)

            <article class="summary-card">

                <span class="summary-label">{{ $indicador[0] }}</span>

                <strong class="summary-value">{{ number_format($indicador[1]) }}</strong>

                <span class="summary-description">{{ $indicador[2] }}</span>

            </article>

        @endforeach

    </section>

@endif



@if($vista === 'agencias')

    <h3 class="dashboard-section-heading">Operación de Agencias ECOSABA</h3>

    <section class="summary-grid">

        @foreach([

            ['Total agencias', $agencias->total_agencias, 'Agencias registradas.'],

            ['Agencias activas', $agencias->agencias_activas, 'Agencias habilitadas.'],

            ['Total arqueos', $agencias->total_arqueos, 'Todos los registros, incluidos anulados.'],

            ['Arqueos hoy', $agencias->arqueos_hoy, 'Arqueos no anulados del día.'],

            ['Pendientes certificación', $agencias->pendientes_certificacion, 'Estados pendientes de certificación.'],

            ['Arqueos anulados', $agencias->arqueos_anulados, 'Total histórico anulado.'],

            ['Con diferencia', $agencias->arqueos_con_diferencia, 'Arqueos no anulados con diferencia distinta de cero.'],

        ] as $indicador)

            <article class="summary-card">

                <span class="summary-label">{{ $indicador[0] }}</span>

                <strong class="summary-value">{{ $indicador[1] === null ? '—' : number_format($indicador[1]) }}</strong>

                <span class="summary-description">{{ $indicador[2] }}</span>

            </article>

        @endforeach

    </section>

@endif



<section class="dashboard-grid">

    @if($vista !== 'agencias')

        <article class="panel">

            <header class="panel-header">

                <div class="panel-title"><h3>Arqueos recientes — Agentes MICOOPE</h3><p>Últimos 10 registros de agentes.</p></div>

                <a href="{{ route('administrador.arqueos.index') }}" class="panel-link">Ver todos</a>

            </header>

            @if($arqueosRecientes->isEmpty())

                <div class="empty-record"><h4>No existen arqueos registrados</h4><p>Los registros aparecerán aquí cuando estén disponibles.</p></div>

            @else

                <div class="table-responsive"><table class="recent-table"><thead><tr><th>Número</th><th>Fecha</th><th>Agente</th><th>Tipo</th><th>Estado</th><th>Diferencia</th></tr></thead><tbody>

                @foreach($arqueosRecientes as $arqueo)

                    <tr>

                        <td class="table-primary">{{ $arqueo->numero_arqueo }}</td>

                        <td>{{ $arqueo->fecha_arqueo ? \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') : '—' }}</td>

                        <td>{{ $arqueo->codigo_agente }} — {{ $arqueo->nombre_negocio }}</td>

                        <td><span class="type-badge">{{ str_replace('_', ' ', $arqueo->tipo) }}</span></td>

                        <td><span class="badge {{ $arqueo->estado === 'CERTIFICADO' ? 'success' : ($arqueo->estado === 'ANULADO' ? 'danger' : 'warning') }}">{{ str_replace('_', ' ', $arqueo->estado) }}</span></td>

                        <td class="{{ $arqueo->diferencia < 0 ? 'diff-negative' : ($arqueo->diferencia > 0 ? 'diff-positive' : 'diff-zero') }}">Q {{ number_format(abs((float)$arqueo->diferencia), 2) }}</td>

                    </tr>

                @endforeach

                </tbody></table></div>

            @endif

        </article>

    @endif

    @if($vista !== 'agentes')

        <article class="panel">

            <header class="panel-header">

                <div class="panel-title"><h3>Arqueos recientes — Agencias ECOSABA</h3><p>Últimos 10 registros de agencias.</p></div>

                @if(\Illuminate\Support\Facades\Route::has('agencias.reportes.index'))

                    <a href="{{ route('agencias.reportes.index') }}" class="panel-link">Reportes de agencias</a>

                @endif

            </header>

            @if($arqueosAgenciaRecientes->isEmpty())

                <div class="empty-record"><h4>No existen arqueos de agencias registrados</h4><p>Los registros aparecerán aquí cuando estén disponibles.</p></div>

            @else

                <div class="table-responsive"><table class="recent-table"><thead><tr><th>Número</th><th>Fecha operativa</th><th>Agencia</th><th>Tipo</th><th>Estado</th><th>Diferencia</th></tr></thead><tbody>

                @foreach($arqueosAgenciaRecientes as $arqueo)

                    <tr>

                        <td class="table-primary">{{ $arqueo->numero_arqueo }}</td>

                        <td>{{ $arqueo->fecha ? \Carbon\Carbon::parse($arqueo->fecha)->format('d/m/Y') : '—' }}</td>

                        <td>{{ $arqueo->agencia_nombre ?? '—' }}</td>

                        <td><span class="type-badge">{{ str_replace('_', ' ', $arqueo->tipo) }}</span></td>

                        <td><span class="badge {{ $arqueo->estado === 'CERTIFICADO' ? 'success' : ($arqueo->estado === 'ANULADO' ? 'danger' : 'warning') }}">{{ str_replace('_', ' ', $arqueo->estado) }}</span></td>

                        <td>{{ $arqueo->diferencia === null ? '—' : 'Q '.number_format(abs((float)$arqueo->diferencia), 2) }}</td>

                    </tr>

                @endforeach

                </tbody></table></div>

            @endif

        </article>

    @endif

    <aside class="panel">

        <header class="panel-header"><div class="panel-title"><h3>Usuarios por rol</h3><p>{{ $vista === 'general' ? 'Todos los roles institucionales.' : ($vista === 'agentes' ? 'Roles exclusivos de Agentes MICOOPE.' : 'Roles exclusivos de Agencias ECOSABA.') }}</p></div></header>

        @if($usuariosPorRol->isEmpty())

            <div class="empty-record"><h4>No existen usuarios para esta vista</h4></div>

        @else

            <div class="role-list">

                @foreach($usuariosPorRol as $rol)

                    <div class="role-row"><div class="role-info"><span>{{ $rol->nombre }}</span></div><strong class="role-total">{{ $rol->total }}</strong></div>

                @endforeach

            </div>

        @endif

    </aside>

</section>

@endsection
