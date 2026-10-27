@extends('layouts.jefe')

@section('title', 'Detalle del Agente')

@section('module-title', 'Agentes')

@push('styles')

<style>
    .page-actions{
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        min-height:42px;
        padding:0 16px;
        border:1px solid #d5dfe5;
        border-radius:11px;
        background:#fff;
        color:#31536e;
        font-size:10px;
        font-weight:800;
        text-decoration:none;
        transition:.2s ease;
    }

    .btn:hover{
        transform:translateY(-1px);
        border-color:#bccbd6;
    }

    .btn-primary{
        border-color:#164c96;
        background:linear-gradient(135deg,#164c96,#1c64b5);
        color:#fff;
        box-shadow:0 8px 18px rgba(22,76,150,.16);
    }

    .btn svg{
        width:16px;
        height:16px;
    }

    .agent-hero{
        position:relative;
        overflow:hidden;
        margin-bottom:22px;
        padding:24px 26px;
        border-radius:18px;
        background:linear-gradient(135deg,#0b315f,#164c96 60%,#0e6678);
        color:#fff;
        box-shadow:0 14px 30px rgba(14,55,94,.13);
    }

    .agent-hero::after{
        content:"";
        position:absolute;
        width:240px;
        height:240px;
        right:-90px;
        top:-110px;
        border:40px solid rgba(255,255,255,.05);
        border-radius:50%;
    }

    .agent-hero-content{
        position:relative;
        z-index:1;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:20px;
    }

    .agent-hero-main{
        display:flex;
        align-items:center;
        gap:16px;
        min-width:0;
    }

    .identity-avatar{
        flex:0 0 auto;
        width:72px;
        height:72px;
        display:grid;
        place-items:center;
        border:3px solid rgba(255,255,255,.24);
        border-radius:18px;
        background:rgba(255,255,255,.12);
        font-size:22px;
        font-weight:900;
    }

    .agent-hero h3{
        margin:0;
        font-size:21px;
        line-height:1.2;
    }

    .agent-hero p{
        margin:6px 0 0;
        color:rgba(255,255,255,.68);
        font-size:11px;
        line-height:1.5;
    }

    .hero-badges{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        justify-content:flex-end;
    }

    .hero-badge{
        display:inline-flex;
        align-items:center;
        gap:7px;
        min-height:34px;
        padding:0 11px;
        border:1px solid rgba(255,255,255,.16);
        border-radius:999px;
        background:rgba(255,255,255,.10);
        color:#fff;
        font-size:9px;
        font-weight:800;
        text-transform:uppercase;
    }

    .hero-badge-dot{
        width:7px;
        height:7px;
        border-radius:50%;
        background:#72e1a0;
        box-shadow:0 0 0 4px rgba(114,225,160,.12);
    }

    .hero-badge.inactive .hero-badge-dot{
        background:#ffb1b1;
        box-shadow:0 0 0 4px rgba(255,177,177,.12);
    }

    .profile-grid{
        display:grid;
        grid-template-columns:320px minmax(0,1fr);
        gap:20px;
        align-items:start;
    }

    .card{
        overflow:hidden;
        border:1px solid #e0e8ee;
        border-radius:18px;
        background:#fff;
        box-shadow:0 8px 24px rgba(20,57,83,.05);
    }

    .identity-card{
        position:sticky;
        top:96px;
    }

    .identity-body{
        padding:18px;
    }

    .identity-row{
        display:flex;
        justify-content:space-between;
        gap:14px;
        padding:12px 0;
        border-bottom:1px solid #edf1f4;
    }

    .identity-row:last-child{
        border-bottom:0;
    }

    .identity-row span{
        color:#738493;
        font-size:8px;
        font-weight:850;
        letter-spacing:.35px;
        text-transform:uppercase;
    }

    .identity-row strong{
        max-width:60%;
        color:#173b59;
        font-size:11px;
        text-align:right;
        overflow-wrap:anywhere;
    }

    .content-card + .content-card{
        margin-top:20px;
    }

    .card-header{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:14px;
        padding:17px 19px;
        border-bottom:1px solid #edf1f4;
        background:#fafcfd;
    }

    .card-header h3{
        margin:0;
        color:#0a3158;
        font-size:15px;
    }

    .card-header p{
        margin:5px 0 0;
        color:#82909a;
        font-size:10px;
        line-height:1.45;
    }

    .card-body{
        padding:19px;
    }

    .info-grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:13px;
    }

    .info-item{
        min-width:0;
        padding:13px 14px;
        border:1px solid #e2e9ee;
        border-radius:11px;
        background:#fbfcfd;
    }

    .info-item.full{
        grid-column:1/-1;
    }

    .info-item span{
        display:block;
        color:#758697;
        font-size:8px;
        font-weight:850;
        letter-spacing:.35px;
        text-transform:uppercase;
    }

    .info-item strong{
        display:block;
        margin-top:6px;
        color:#173b59;
        font-size:11px;
        line-height:1.45;
        overflow-wrap:anywhere;
    }

    .stats-grid{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:12px;
    }

    .stat-box{
        position:relative;
        overflow:hidden;
        padding:15px;
        border:1px solid #e1e8ed;
        border-radius:12px;
        background:#fbfcfd;
    }

    .stat-box::after{
        content:"";
        position:absolute;
        right:-16px;
        top:-18px;
        width:58px;
        height:58px;
        border-radius:50%;
        background:rgba(22,76,150,.04);
    }

    .stat-box span{
        display:block;
        color:#748596;
        font-size:8px;
        font-weight:850;
        letter-spacing:.3px;
        text-transform:uppercase;
    }

    .stat-box strong{
        display:block;
        margin-top:7px;
        color:#082d55;
        font-size:22px;
    }

    .history-wrap{
        overflow-x:auto;
    }

    .history-table{
        width:100%;
        min-width:760px;
        border-collapse:collapse;
    }

    .history-table th,
    .history-table td{
        padding:11px 12px;
        border-bottom:1px solid #edf1f4;
        text-align:left;
        vertical-align:middle;
        font-size:9px;
    }

    .history-table th{
        background:#f7f9fb;
        color:#687b8b;
        font-size:8px;
        font-weight:850;
        letter-spacing:.25px;
        text-transform:uppercase;
    }

    .history-table tbody tr:hover{
        background:#fbfdfe;
    }

    .badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:27px;
        padding:0 9px;
        border-radius:999px;
        background:#edf2f6;
        color:#50667a;
        font-size:8px;
        font-weight:850;
        text-transform:uppercase;
        white-space:nowrap;
    }

    .badge.agent{
        background:#eef5ff;
        color:#285b9b;
    }

    .badge.promotor{
        background:#f2effb;
        color:#67529a;
    }

    .badge.certified{
        background:#eaf8ef;
        color:#1d7b4e;
    }

    .badge.pending{
        background:#fff8e6;
        color:#8a6511;
    }

    .badge.cancelled{
        background:#fdecec;
        color:#b13c3c;
    }

    .badge.ext{
        background:#fff4e8;
        color:#a66312;
    }

    .empty-state{
        padding:32px 18px !important;
        color:#7d8c97;
        text-align:center !important;
        font-size:10px !important;
    }

    @media(max-width:1050px){
        .stats-grid{
            grid-template-columns:repeat(2,minmax(0,1fr));
        }
    }

    @media(max-width:950px){
        .profile-grid{
            grid-template-columns:1fr;
        }

        .identity-card{
            position:static;
        }
    }

    @media(max-width:700px){
        .agent-hero-content{
            align-items:flex-start;
            flex-direction:column;
        }

        .hero-badges{
            justify-content:flex-start;
        }

        .page-actions{
            margin-top:15px;
        }
    }

    @media(max-width:650px){
        .info-grid,
        .stats-grid{
            grid-template-columns:1fr;
        }

        .info-item.full{
            grid-column:auto;
        }

        .agent-hero-main{
            align-items:flex-start;
        }

        .identity-avatar{
            width:58px;
            height:58px;
            border-radius:15px;
            font-size:18px;
        }
    }
</style>

@endpush

@section('content')

@php

    $nombrePromotor = trim(

        ($agente->promotor_nombres ?? '')

        . ' '

        . ($agente->promotor_apellidos ?? '')

    );

    $iniciales = collect(

        preg_split('/\s+/', $agente->nombre_negocio)

    )

        ->filter()

        ->take(2)

        ->map(

            fn ($parte) => mb_strtoupper(

                mb_substr($parte, 0, 1)

            )

        )

        ->implode('');

@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Detalle del Agente</h2>
        <p>
            Consulte la información institucional, ubicación operativa,
            promotor asignado y actividad reciente del agente.
        </p>
    </div>

    <div class="page-actions">
        <a
            href="{{ route('jefe.agentes.index') }}"
            class="btn"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"/>
                <path d="m11 18-6-6 6-6"/>
            </svg>
            Regresar
        </a>

        <a
            href="{{ route('jefe.agentes.imprimir', $agente->id) }}"
            target="_blank"
            class="btn btn-primary"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 9V2h12v7"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Imprimir PDF
        </a>
    </div>
</div>

<section class="agent-hero">
    <div class="agent-hero-content">
        <div class="agent-hero-main">
            <div class="identity-avatar">
                {{ $iniciales !== '' ? $iniciales : 'AG' }}
            </div>

            <div>
                <h3>{{ $agente->nombre_negocio }}</h3>
                <p>
                    Agente {{ $agente->codigo_agente }}
                    · {{ $agente->ruta_nombre }}
                    · {{ $agente->region_nombre }}
                </p>
            </div>
        </div>

        <div class="hero-badges">
            <span class="hero-badge {{ strtoupper((string)$agente->estado) === 'ACTIVO' ? '' : 'inactive' }}">
                <span class="hero-badge-dot"></span>
                {{ $agente->estado }}
            </span>

            <span class="hero-badge">
                {{ $agente->ruta_codigo }}
            </span>
        </div>
    </div>
</section>

<div class="profile-grid">

    <aside>

        <section class="card identity-card">
            <div class="identity-body">

                <div class="identity-row">

                    <span>Propietario</span>

                    <strong>

                        {{ $agente->nombre_propietario }}

                    </strong>

                </div>

                <div class="identity-row">

                    <span>Estado</span>

                    <strong>{{ $agente->estado }}</strong>

                </div>

                <div class="identity-row">

                    <span>Usuario</span>

                    <strong>

                        {{ $agente->agente_usuario

                            ?: 'Sin usuario asociado' }}

                    </strong>

                </div>

                <div class="identity-row">

                    <span>Ruta</span>

                    <strong>

                        {{ $agente->ruta_codigo }}

                        — {{ $agente->ruta_nombre }}

                    </strong>

                </div>

                <div class="identity-row">

                    <span>Región</span>

                    <strong>

                        {{ $agente->region_nombre }}

                    </strong>

                </div>

            </div>

        </section>

    </aside>

    <main>

        <section class="card content-card">

            <header class="card-header">

                <h3>Información institucional</h3>

            </header>

            <div class="card-body">

                <div class="info-grid">

                    <div class="info-item">

                        <span>Código de agente</span>

                        <strong>{{ $agente->codigo_agente }}</strong>

                    </div>

                    <div class="info-item">

                        <span>Nombre del negocio</span>

                        <strong>{{ $agente->nombre_negocio }}</strong>

                    </div>

                    <div class="info-item">

                        <span>Propietario / receptor</span>

                        <strong>

                            {{ $agente->nombre_propietario }}

                        </strong>

                    </div>

                    <div class="info-item">

                        <span>Promotor asignado</span>

                        <strong>

                            {{ $nombrePromotor !== ''

                                ? $nombrePromotor

                                : (

                                    $agente->promotor_usuario

                                    ?: 'Sin promotor asignado'

                                ) }}

                        </strong>

                    </div>

                    <div class="info-item">

                        <span>Ruta</span>

                        <strong>

                            {{ $agente->ruta_codigo }}

                            — {{ $agente->ruta_nombre }}

                        </strong>

                    </div>

                    <div class="info-item">

                        <span>Región</span>

                        <strong>

                            {{ $agente->region_nombre }}

                        </strong>

                    </div>

                    <div class="info-item full">

                        <span>Dirección</span>

                        <strong>

                            {{ $agente->direccion ?: 'No registrada' }}

                        </strong>

                    </div>

                </div>

            </div>

        </section>

        <section class="card content-card">

            <header class="card-header">

                <h3>Resumen de arqueos</h3>

            </header>

            <div class="card-body">

                <div class="stats-grid">

                    <div class="stat-box">

                        <span>Total registros</span>

                        <strong>

                            {{ (int) $resumen->total_arqueos }}

                        </strong>

                    </div>

                    <div class="stat-box">

                        <span>Arqueos del agente</span>

                        <strong>

                            {{ (int) $resumen->arqueos_agente }}

                        </strong>

                    </div>

                    <div class="stat-box">

                        <span>Arqueos de promotor</span>

                        <strong>

                            {{ (int) $resumen->arqueos_promotor }}

                        </strong>

                    </div>

                    <div class="stat-box">

                        <span>Anulados</span>

                        <strong>

                            {{ (int) $resumen->anulados }}

                        </strong>

                    </div>

                </div>

            </div>

        </section>

        <section class="card content-card">

            <header class="card-header">

                <h3>Últimos arqueos</h3>

            </header>

            <div class="history-wrap">

                <table class="history-table">

                    <thead>

                        <tr>

                            <th>Número</th>

                            <th>Fecha</th>

                            <th>Tipo</th>

                            <th>Estado</th>

                            <th>Extemporáneo</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($ultimosArqueos as $arqueo)

                            <tr>

                                <td>

                                    <strong>

                                        {{ $arqueo->numero_arqueo }}

                                    </strong>

                                </td>

                                <td>

                                    {{ \Carbon\Carbon::parse(

                                        $arqueo->fecha_arqueo

                                    )->format('d/m/Y') }}

                                </td>

                                <td>

                                    <span class="badge">

                                        {{ $arqueo->tipo === 'DIARIO_AGENTE'

                                            ? 'Agente'

                                            : 'Promotor' }}

                                    </span>

                                </td>

                                <td>

                                    <span class="badge">

                                        {{ str_replace(

                                            '_',

                                            ' ',

                                            $arqueo->estado

                                        ) }}

                                    </span>

                                </td>

                                <td>

                                    {{ $arqueo->fuera_fecha_ordinaria

                                        ? 'Sí'

                                        : 'No' }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5">

                                    Sin arqueos registrados.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

@endsection
