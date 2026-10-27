@extends('layouts.agente')



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

            0 17px 36px rgba(10, 61, 109, 0.16);

    }



    .welcome-panel::before {

        content: "";

        position: absolute;

        width: 230px;

        height: 230px;

        top: -145px;

        right: -75px;

        border: 43px solid rgba(255, 255, 255, 0.055);

        border-radius: 50%;

    }



    .welcome-content {

        position: relative;

        z-index: 2;

    }



    .welcome-content span {

        display: block;

        margin-bottom: 6px;

        color: rgba(255, 255, 255, 0.68);

        font-size: 11px;

        font-weight: 750;

        letter-spacing: 0.8px;

        text-transform: uppercase;

    }



    .welcome-content h2 {

        margin: 0;

        font-size: 27px;

        letter-spacing: -0.65px;

    }



    .welcome-content p {

        max-width: 610px;

        margin: 10px 0 0;

        color: rgba(255, 255, 255, 0.75);

        font-size: 13px;

        line-height: 1.6;

    }



    .welcome-status {

        position: relative;

        z-index: 2;

        flex: 0 0 auto;

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 11px 15px;

        border: 1px solid rgba(255, 255, 255, 0.15);

        border-radius: 13px;

        background: rgba(255, 255, 255, 0.10);

        font-size: 12px;

        font-weight: 750;

    }



    .status-dot {

        width: 9px;

        height: 9px;

        border-radius: 50%;

        background: #73e1a0;

        box-shadow:

            0 0 0 5px rgba(115, 225, 160, 0.13);

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

        overflow: hidden;

        border: 1px solid #e0e8ee;

        border-radius: 17px;

        background: #ffffff;

        box-shadow:

            0 8px 24px rgba(20, 57, 83, 0.055);

    }



    .summary-card-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

    }



    .summary-icon {

        width: 43px;

        height: 43px;

        display: grid;

        place-items: center;

        border-radius: 13px;

        background: rgba(22, 76, 150, 0.09);

        color: #164c96;

    }



    .summary-icon.green {

        background: rgba(0, 166, 81, 0.10);

        color: #009349;

    }



    .summary-icon.orange {

        background: rgba(224, 151, 14, 0.11);

        color: #b47c0e;

    }



    .summary-icon.gray {

        background: #eef2f5;

        color: #71808c;

    }



    .summary-icon svg {

        width: 22px;

        height: 22px;

    }



    .summary-label {

        color: #71818e;

        font-size: 11px;

        font-weight: 750;

        text-transform: uppercase;

        letter-spacing: 0.55px;

    }



    .summary-value {

        display: block;

        margin-top: 16px;

        color: #082d55;

        font-size: 23px;

        font-weight: 800;

        letter-spacing: -0.5px;

    }



    .summary-description {

        display: block;

        margin-top: 5px;

        color: #87949d;

        font-size: 11px;

    }



    .dashboard-grid {

        display: grid;

        grid-template-columns:

            minmax(0, 1.12fr)

            minmax(340px, 0.88fr);

        gap: 20px;

    }



    .panel {

        border: 1px solid #e0e8ee;

        border-radius: 18px;

        background: #ffffff;

        box-shadow:

            0 8px 25px rgba(20, 57, 83, 0.05);

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

    }



    .panel-link:hover {

        color: #0b315f;

    }



    .panel-body {

        padding: 20px;

    }



    .agent-information {

        display: grid;

        grid-template-columns:

            repeat(2, minmax(0, 1fr));

        gap: 15px;

    }



    .information-item {

        padding: 14px;

        border: 1px solid #e8edf1;

        border-radius: 13px;

        background: #fafcfd;

    }



    .information-item span {

        display: block;

        color: #82909a;

        font-size: 10px;

        font-weight: 750;

        letter-spacing: 0.55px;

        text-transform: uppercase;

    }



    .information-item strong {

        display: block;

        margin-top: 6px;

        color: #19344d;

        font-size: 13px;

        line-height: 1.4;

    }



    .empty-record {

        padding: 26px 18px;

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

        margin: 7px auto 0;

        max-width: 330px;

        color: #83919b;

        font-size: 11px;

        line-height: 1.55;

    }



    .primary-action {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        min-height: 42px;

        margin-top: 16px;

        padding: 0 17px;

        border-radius: 11px;

        background:

            linear-gradient(

                135deg,

                #164c96,

                #1c64b5

            );

        color: #ffffff;

        font-size: 11px;

        font-weight: 800;

        box-shadow:

            0 9px 18px rgba(22, 76, 150, 0.18);

    }



    .primary-action:hover {

        color: #ffffff;

        filter: brightness(1.03);

    }



    .primary-action svg {

        width: 16px;

        height: 16px;

    }



    .record-list {

        display: grid;

        gap: 13px;

    }



    .record-item {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        padding: 14px;

        border: 1px solid #e8edf1;

        border-radius: 13px;

        background: #fafcfd;

        transition:

            transform .18s ease,

            border-color .18s ease,

            box-shadow .18s ease;

    }



    .record-item:hover {

        transform: translateY(-1px);

        border-color: #cfdce6;

        box-shadow:

            0 7px 16px rgba(20, 57, 83, 0.05);

    }



    .record-main {

        min-width: 0;

    }



    .record-main strong {

        display: block;

        color: #19344d;

        font-size: 13px;

    }



    .record-main span {

        display: block;

        margin-top: 5px;

        color: #87949d;

        font-size: 10px;

    }



    .record-status {

        flex: 0 0 auto;

        padding: 7px 10px;

        border-radius: 999px;

        background: #fff5dc;

        color: #9c7014;

        font-size: 9px;

        font-weight: 800;

        letter-spacing: 0.45px;

        text-transform: uppercase;

    }



    @media (max-width: 1200px) {

        .summary-grid {

            grid-template-columns:

                repeat(2, minmax(0, 1fr));

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



        .agent-information {

            grid-template-columns: 1fr;

        }

    }


    .dashboard-alert { display:flex; align-items:flex-start; gap:12px; margin-bottom:18px; padding:14px 16px; border:1px solid transparent; border-radius:14px; font-size:14px; line-height:1.5; }
    .dashboard-alert strong { display:block; margin-bottom:2px; }
    .dashboard-alert-success { color:#166534; background:#f0fdf4; border-color:#bbf7d0; }
    .dashboard-alert-warning { color:#92400e; background:#fffbeb; border-color:#fde68a; }
    .dashboard-alert-error { color:#991b1b; background:#fef2f2; border-color:#fecaca; }
    .record-item-no-atendio { width:100%; appearance:none; border:1px solid #fecaca; background:#fff7f7; text-align:left; font:inherit; cursor:pointer; }
    .record-item-no-atendio:hover { border-color:#ef4444; background:#fef2f2; }
    .record-item-no-atendio .record-main strong { color:#991b1b; }
    .record-status-danger { color:#991b1b; background:#fee2e2; border:1px solid #fecaca; }
    .record-item-disabled { cursor:default; border-color:#e5e7eb; background:#f8fafc; }
    .record-item-disabled:hover { transform:none; box-shadow:none; border-color:#e5e7eb; }
    .no-atendio-detail { margin-top:8px; padding:10px 12px; border-radius:10px; color:#7f1d1d; background:#fff1f2; font-size:13px; line-height:1.45; }
    .no-atendio-modal { position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; padding:20px; }
    .no-atendio-modal.is-open { display:flex; }
    .no-atendio-modal-backdrop { position:absolute; inset:0; background:rgba(2,16,35,.62); backdrop-filter:blur(3px); }
    .no-atendio-modal-dialog { position:relative; z-index:1; width:min(100%,560px); overflow:hidden; border-radius:18px; background:#fff; box-shadow:0 24px 70px rgba(2,16,35,.28); }
    .no-atendio-modal-header { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; padding:22px 24px; border-bottom:1px solid #e5e7eb; }
    .no-atendio-modal-header h3 { margin:0; color:#06284f; font-size:20px; }
    .no-atendio-modal-header p { margin:6px 0 0; color:#64748b; font-size:14px; line-height:1.5; }
    .no-atendio-modal-close { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; flex:0 0 36px; border:0; border-radius:10px; color:#475569; background:#f1f5f9; cursor:pointer; font-size:22px; line-height:1; }
    .no-atendio-modal-body { padding:22px 24px; }
    .no-atendio-warning-box { margin-bottom:18px; padding:13px 14px; border:1px solid #fde68a; border-radius:12px; color:#92400e; background:#fffbeb; font-size:13px; line-height:1.5; }
    .no-atendio-field label { display:block; margin-bottom:8px; color:#0f172a; font-size:14px; font-weight:700; }
    .no-atendio-field textarea { width:100%; min-height:125px; resize:vertical; padding:12px 14px; border:1px solid #cbd5e1; border-radius:12px; outline:none; color:#0f172a; background:#fff; font:inherit; line-height:1.5; box-sizing:border-box; }
    .no-atendio-field textarea:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
    .no-atendio-field-help { display:flex; justify-content:space-between; gap:12px; margin-top:7px; color:#64748b; font-size:12px; }
    .no-atendio-modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:18px 24px; border-top:1px solid #e5e7eb; background:#f8fafc; }
    .no-atendio-btn { display:inline-flex; align-items:center; justify-content:center; min-height:42px; padding:0 17px; border-radius:11px; font:inherit; font-size:14px; font-weight:700; cursor:pointer; }
    .no-atendio-btn-secondary { border:1px solid #cbd5e1; color:#334155; background:#fff; }
    .no-atendio-btn-danger { border:1px solid #b91c1c; color:#fff; background:#b91c1c; }
    .no-atendio-btn-danger:hover { background:#991b1b; }
    body.no-atendio-modal-open { overflow:hidden; }
    @media (max-width:640px) {
        .no-atendio-modal { padding:12px; }
        .no-atendio-modal-header,.no-atendio-modal-body,.no-atendio-modal-footer { padding-left:18px; padding-right:18px; }
        .no-atendio-modal-footer { flex-direction:column-reverse; }
        .no-atendio-btn { width:100%; }
    }

</style>

@endpush



@section('content')

    @if (session('success'))
        <div class="dashboard-alert dashboard-alert-success" role="alert">
            <div><strong>Operación realizada correctamente</strong>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('warning'))
        <div class="dashboard-alert dashboard-alert-warning" role="alert">
            <div><strong>Atención</strong>{{ session('warning') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="dashboard-alert dashboard-alert-error" role="alert">
            <div>
                <strong>No fue posible completar la operación</strong>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif


    <div class="page-header">

        <div class="page-title">

            <h2>

                Panel del Agente

            </h2>



            <p>

                Consulte su información, arqueos y actividades recientes.

            </p>

        </div>



        <div class="page-badge">

            <span class="page-badge-dot"></span>

            Cuenta activa

        </div>

    </div>



    <section class="welcome-panel">

        <div class="welcome-content">

            <span>

                Bienvenido al sistema

            </span>



            <h2>

                {{ auth()->user()->nombre_completo }}

            </h2>



            <p>

                Desde este panel podrá realizar su arqueo diario,

                consultar sus registros y firmar los arqueos realizados

                por el promotor.

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

                <span class="summary-label">

                    Arqueo de hoy

                </span>



                <span class="summary-icon orange">

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

                </span>

            </div>



            <strong class="summary-value">

                {{ $arqueoHoy ? $estadoArqueoHoy : 'Pendiente' }}

            </strong>



            <span class="summary-description">

                {{ $descripcionArqueoHoy }}

            </span>

        </article>



        <article class="summary-card">

            <div class="summary-card-header">

                <span class="summary-label">

                    Arqueos realizados

                </span>



                <span class="summary-icon">

                    <svg

                        viewBox="0 0 24 24"

                        fill="none"

                        stroke="currentColor"

                        stroke-width="1.9"

                        stroke-linecap="round"

                        stroke-linejoin="round"

                    >

                        <path d="M4 19h16"/>

                        <path d="M6 16V9"/>

                        <path d="M12 16V5"/>

                        <path d="M18 16v-4"/>

                    </svg>

                </span>

            </div>



            <strong class="summary-value">

                {{ $totalArqueos }}

            </strong>



            <span class="summary-description">

                Total de arqueos diarios registrados.

            </span>

        </article>



        <article class="summary-card">

            <div class="summary-card-header">

                <span class="summary-label">

                    Pendientes de firma

                </span>



                <span class="summary-icon green">

                    <svg

                        viewBox="0 0 24 24"

                        fill="none"

                        stroke="currentColor"

                        stroke-width="1.9"

                        stroke-linecap="round"

                        stroke-linejoin="round"

                    >

                        <path d="M12 20h9"/>

                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>

                    </svg>

                </span>

            </div>



            <strong class="summary-value">

                {{ $pendientesFirmaPromotor }}

            </strong>



            <span class="summary-description">

                Arqueos de promotor pendientes de firma.

            </span>

        </article>



        <article class="summary-card">

            <div class="summary-card-header">

                <span class="summary-label">

                    Último acceso

                </span>



                <span class="summary-icon gray">

                    <svg

                        viewBox="0 0 24 24"

                        fill="none"

                        stroke="currentColor"

                        stroke-width="1.9"

                        stroke-linecap="round"

                        stroke-linejoin="round"

                    >

                        <circle cx="12" cy="12" r="9"/>

                        <path d="M12 7v5l3 2"/>

                    </svg>

                </span>

            </div>



            <strong

                class="summary-value"

                style="font-size: 16px;"

            >

                {{ auth()->user()->ultimo_acceso?->format('d/m/Y H:i') ?? 'Primer acceso' }}

            </strong>



            <span class="summary-description">

                Fecha y hora de ingreso al sistema.

            </span>

        </article>

    </section>



    <section class="dashboard-grid">

        <div>

            <article class="panel">

                <header class="panel-header">

                    <div class="panel-title">

                        <h3>

                            Información del agente

                        </h3>



                        <p>

                            Datos generales asociados a su cuenta.

                        </p>

                    </div>



                    <a

                        href="{{ route('agente.perfil.index') }}"

                        class="panel-link"

                    >

                        Ver perfil

                    </a>

                </header>



                <div class="panel-body">

                    <div class="agent-information">

                        <div class="information-item">

                            <span>

                                Nombre completo

                            </span>



                            <strong>

                                {{ auth()->user()->nombre_completo }}

                            </strong>

                        </div>



                        <div class="information-item">

                            <span>

                                Usuario

                            </span>



                            <strong>

                                {{ auth()->user()->usuario }}

                            </strong>

                        </div>



                        <div class="information-item">

                            <span>

                                Código de agente

                            </span>



                            <strong>

                                {{ $agente->codigo_agente }}

                            </strong>

                        </div>



                        <div class="information-item">

                            <span>

                                Estado de cuenta

                            </span>



                            <strong>

                                {{ auth()->user()->estado }}

                            </strong>

                        </div>



                        <div class="information-item">

                            <span>

                                Región

                            </span>



                            <strong>

                                {{ $agente->ruta?->region?->nombre ?? 'Sin región asignada' }}

                            </strong>

                        </div>



                        <div class="information-item">

                            <span>

                                Ruta

                            </span>



                            <strong>

                                {{ $agente->ruta?->nombre ?? 'Sin ruta asignada' }}

                            </strong>

                        </div>

                    </div>

                </div>

            </article>



            <article

                class="panel"

                style="margin-top: 20px;"

            >

                <header class="panel-header">

                    <div class="panel-title">

                        <h3>

                            Último arqueo realizado por el agente

                        </h3>



                        <p>

                            Registro más reciente ingresado por usted.

                        </p>

                    </div>



                    <a

                        href="{{ route('agente.arqueos.index') }}"

                        class="panel-link"

                    >

                        Ver historial

                    </a>

                </header>



                <div class="panel-body">

                    @if ($ultimoArqueoAgente)

                        <div class="record-list">

                            <a

                                href="{{ route('agente.arqueos.show', $ultimoArqueoAgente) }}"

                                class="record-item"

                            >

                                <div class="record-main">

                                    <strong>

                                        {{ $ultimoArqueoAgente->numero_arqueo }}

                                    </strong>



                                    <span>

                                        {{ $ultimoArqueoAgente->fecha_arqueo->format('d/m/Y') }}

                                        · Q {{ number_format((float) $ultimoArqueoAgente->total_arqueado, 2) }}

                                        · Diferencia Q {{ number_format((float) $ultimoArqueoAgente->diferencia, 2) }}

                                    </span>

                                </div>



                                <span class="record-status">

                                    {{ $estadoUltimoArqueoAgente }}

                                </span>

                            </a>

                        </div>

                    @else

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



                            <h4>

                                No existen arqueos registrados

                            </h4>



                            <p>

                                Cuando realice su primer arqueo, la información

                                más reciente aparecerá en esta sección.

                            </p>



                            <a

                                href="{{ route('agente.arqueos.index') }}"

                                class="primary-action"

                            >

                                Crear arqueo



                                <svg

                                    viewBox="0 0 24 24"

                                    fill="none"

                                    stroke="currentColor"

                                    stroke-width="2"

                                    stroke-linecap="round"

                                >

                                    <path d="M12 5v14"/>

                                    <path d="M5 12h14"/>

                                </svg>

                            </a>

                        </div>

                    @endif

                </div>

            </article>

        </div>



        <div>

            <article class="panel">

                <header class="panel-header">

                    <div class="panel-title">

                        <h3>

                            Último arqueo del promotor

                        </h3>



                        <p>

                            Arqueo más reciente realizado por un promotor.

                        </p>

                    </div>



                    <a

                        href="{{ route('agente.arqueos-promotor.index') }}"

                        class="panel-link"

                    >

                        Ver todos

                    </a>

                </header>



                <div class="panel-body">

                    @if ($ultimoArqueoPromotor)

                        <div class="record-list">

                            <a

                                href="{{ route('agente.arqueos-promotor.index') }}"

                                class="record-item"

                            >

                                <div class="record-main">

                                    <strong>

                                        {{ $ultimoArqueoPromotor->numero_arqueo }}

                                    </strong>



                                    <span>

                                        {{ $ultimoArqueoPromotor->fecha_arqueo->format('d/m/Y') }}

                                        · Q {{ number_format((float) $ultimoArqueoPromotor->total_arqueado, 2) }}

                                        · {{ $estadoUltimoArqueoPromotor }}

                                    </span>

                                </div>



                                @if ($ultimoArqueoPromotorPendienteFirma)

                                    <span class="record-status">

                                        Pendiente de firma

                                    </span>

                                @endif

                            </a>

                        </div>

                    @else

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

                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>

                                    <circle cx="9" cy="7" r="4"/>

                                    <path d="m16 11 2 2 4-4"/>

                                </svg>

                            </div>



                            <h4>

                                Sin arqueos del promotor

                            </h4>



                            <p>

                                En esta sección aparecerán los arqueos realizados

                                por un promotor y los que requieran su firma.

                            </p>

                        </div>

                    @endif

                </div>

            </article>



            <article

                class="panel"

                style="margin-top: 20px;"

            >

                <header class="panel-header">

                    <div class="panel-title">

                        <h3>

                            Acciones rápidas

                        </h3>



                        <p>

                            Accesos principales del agente.

                        </p>

                    </div>

                </header>



                <div class="panel-body">

                    <div class="record-list">

                        @if ($noAtendioHoy)
                            <div class="record-item record-item-disabled">
                                <div class="record-main">
                                    <strong>Hoy fue registrado como No atendió</strong>
                                    <span>No corresponde realizar el arqueo diario ordinario de hoy.</span>
                                    @if (! empty($noAtendioHoy->anotacion))
                                        <div class="no-atendio-detail">
                                            <strong>Motivo:</strong> {{ $noAtendioHoy->anotacion }}
                                        </div>
                                    @endif
                                </div>
                                <span class="record-status record-status-danger">No atendió</span>
                            </div>
                        @else
                            <a href="{{ route('agente.arqueos.index') }}" class="record-item">
                                <div class="record-main">
                                    <strong>Realizar arqueo diario</strong>
                                    <span>Registrar efectivo y saldo del sistema.</span>
                                </div>
                                <span class="record-status">
                                    {{ $arqueoHoy ? $estadoArqueoHoy : 'Pendiente' }}
                                </span>
                            </a>

                            @if ($puedeMarcarNoAtendio)
                                <button type="button" class="record-item record-item-no-atendio" id="abrirNoAtendioModal">
                                    <div class="record-main">
                                        <strong>Marcar como No atendió</strong>
                                        <span>Registrar que el agente no atendió durante el día de hoy.</span>
                                    </div>
                                    <span class="record-status record-status-danger">Registrar</span>
                                </button>
                            @endif
                        @endif



                        <a

                            href="{{ route('agente.arqueos-promotor.index') }}"

                            class="record-item"

                        >

                            <div class="record-main">

                                <strong>

                                    Firmar arqueo de promotor

                                </strong>



                                <span>

                                    Consultar arqueos pendientes de firma.

                                </span>

                            </div>

                        </a>



                        <a

                            href="{{ route('agente.perfil.index') }}"

                            class="record-item"

                        >

                            <div class="record-main">

                                <strong>

                                    Consultar perfil

                                </strong>



                                <span>

                                    Ver información personal y de la cuenta.

                                </span>

                            </div>

                        </a>

                    </div>

                </div>

            </article>

        </div>

    </section>


    @if ($puedeMarcarNoAtendio)
        <div class="no-atendio-modal" id="noAtendioModal" aria-hidden="true">
            <div class="no-atendio-modal-backdrop" data-cerrar-no-atendio></div>

            <div class="no-atendio-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="noAtendioModalTitulo">
                <form method="POST" action="{{ route('agente.no-atendio.store') }}" id="noAtendioForm">
                    @csrf

                    <div class="no-atendio-modal-header">
                        <div>
                            <h3 id="noAtendioModalTitulo">Marcar como No atendió</h3>
                            <p>Registre el motivo correspondiente al día de hoy.</p>
                        </div>
                        <button type="button" class="no-atendio-modal-close" data-cerrar-no-atendio aria-label="Cerrar">&times;</button>
                    </div>

                    <div class="no-atendio-modal-body">
                        <div class="no-atendio-warning-box">
                            Al confirmar esta acción, el día de hoy quedará registrado como
                            <strong>No atendió</strong> y ya no podrá realizarse el arqueo
                            diario ordinario correspondiente a hoy.
                        </div>

                        <div class="no-atendio-field">
                            <label for="anotacionNoAtendio">Motivo o anotación</label>
                            <textarea
                                name="anotacion"
                                id="anotacionNoAtendio"
                                maxlength="1000"
                                required
                                placeholder="Describa el motivo por el cual no atendió..."
                            >{{ old('anotacion') }}</textarea>
                            <div class="no-atendio-field-help">
                                <span>Este campo es obligatorio.</span>
                                <span id="contadorNoAtendio">0 / 1000</span>
                            </div>
                        </div>
                    </div>

                    <div class="no-atendio-modal-footer">
                        <button type="button" class="no-atendio-btn no-atendio-btn-secondary" data-cerrar-no-atendio>
                            Cancelar
                        </button>
                        <button type="submit" class="no-atendio-btn no-atendio-btn-danger" id="confirmarNoAtendio">
                            Confirmar No atendió
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('noAtendioModal');
                const abrir = document.getElementById('abrirNoAtendioModal');
                const textarea = document.getElementById('anotacionNoAtendio');
                const contador = document.getElementById('contadorNoAtendio');
                const formulario = document.getElementById('noAtendioForm');
                const confirmar = document.getElementById('confirmarNoAtendio');
                const cierres = document.querySelectorAll('[data-cerrar-no-atendio]');

                if (!modal || !abrir || !textarea || !formulario) return;

                function actualizarContador() {
                    if (contador) contador.textContent = textarea.value.length + ' / 1000';
                }

                function abrirModal() {
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('no-atendio-modal-open');
                    setTimeout(function () { textarea.focus(); }, 50);
                }

                function cerrarModal() {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('no-atendio-modal-open');
                    abrir.focus();
                }

                abrir.addEventListener('click', abrirModal);
                cierres.forEach(function (elemento) {
                    elemento.addEventListener('click', cerrarModal);
                });
                textarea.addEventListener('input', actualizarContador);

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && modal.classList.contains('is-open')) cerrarModal();
                });

                formulario.addEventListener('submit', function () {
                    if (confirmar) {
                        confirmar.disabled = true;
                        confirmar.textContent = 'Registrando...';
                    }
                });

                actualizarContador();

                @if ($errors->has('anotacion') || $errors->has('no_atendio'))
                    abrirModal();
                @endif
            });
        </script>
    @endif

@endsection
