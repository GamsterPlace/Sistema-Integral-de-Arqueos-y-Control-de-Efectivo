@extends('layouts.administrador')

@section('title', 'Detalle de Agente')
@section('module-title', 'Agentes')

@push('styles')
<style>
    .agent-detail {
        display: grid;
        gap: 22px;
    }

    .agent-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 26px 28px;
        border: 1px solid #dce6ed;
        border-radius: 20px;
        background:
            linear-gradient(135deg, rgba(22,76,150,.055), rgba(0,166,81,.035)),
            #fff;
        box-shadow: 0 14px 34px rgba(20,57,83,.06);
    }

    .agent-hero::after {
        content: "";
        position: absolute;
        width: 210px;
        height: 210px;
        right: -75px;
        top: -100px;
        border: 34px solid rgba(22,76,150,.045);
        border-radius: 50%;
        pointer-events: none;
    }

    .agent-identity {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }

    .agent-avatar {
        flex: 0 0 auto;
        width: 62px;
        height: 62px;
        display: grid;
        place-items: center;
        border-radius: 17px;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #fff;
        box-shadow: 0 10px 22px rgba(22,76,150,.18);
    }

    .agent-avatar svg {
        width: 30px;
        height: 30px;
    }

    .agent-heading {
        min-width: 0;
    }

    .agent-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
        color: #6d7d8a;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .75px;
        text-transform: uppercase;
    }

    .agent-eyebrow-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #00a651;
        box-shadow: 0 0 0 4px rgba(0,166,81,.10);
    }

    .agent-heading h2 {
        margin: 0;
        overflow: hidden;
        color: #06284f;
        font-size: 26px;
        letter-spacing: -.55px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .agent-heading p {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin: 8px 0 0;
        color: #6d7d8a;
        font-size: 12px;
        font-weight: 650;
    }

    .hero-separator {
        color: #b5c1ca;
    }

    .hero-actions {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 9px;
        flex: 0 0 auto;
    }

    .btn {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 16px;
        border: 1px solid #d6e0e7;
        border-radius: 11px;
        background: #fff;
        color: #496579;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        transition: .2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        border-color: #a8bdcc;
        box-shadow: 0 7px 16px rgba(20,57,83,.08);
    }

    .btn svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .btn-primary {
        border-color: #164c96;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #fff;
        box-shadow: 0 8px 18px rgba(22,76,150,.16);
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 18px;
    }

    .detail-panel {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(20,57,83,.045);
    }

    .panel-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #e7edf1;
        background: #fbfcfd;
    }

    .panel-icon {
        width: 39px;
        height: 39px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 11px;
        background: #edf4fb;
        color: #164c96;
    }

    .panel-icon.account {
        background: #eff9f3;
        color: #008640;
    }

    .panel-icon svg {
        width: 20px;
        height: 20px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .panel-title h3 {
        margin: 0;
        color: #0b315f;
        font-size: 14px;
        font-weight: 850;
    }

    .panel-title p {
        margin: 4px 0 0;
        color: #83919c;
        font-size: 10px;
    }

    .info-list {
        padding: 8px 20px 14px;
    }

    .info-row {
        display: grid;
        grid-template-columns: minmax(120px,.7fr) minmax(0,1.3fr);
        gap: 16px;
        align-items: center;
        min-height: 51px;
        border-bottom: 1px solid #edf1f4;
    }

    .info-row:last-child {
        border-bottom: 0;
    }

    .info-label {
        color: #758697;
        font-size: 10px;
        font-weight: 750;
    }

    .info-value {
        min-width: 0;
        color: #19344d;
        font-size: 12px;
        font-weight: 750;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border: 1px solid #c9e5d4;
        border-radius: 999px;
        background: #effaf3;
        color: #197245;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .3px;
        text-transform: uppercase;
    }

    .status-badge.inactive {
        border-color: #e6d1d1;
        background: #fff4f4;
        color: #a33c3c;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .summary-section {
        display: grid;
        gap: 13px;
    }

    .summary-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 15px;
    }

    .summary-heading h3 {
        margin: 0;
        color: #0b315f;
        font-size: 17px;
    }

    .summary-heading p {
        margin: 4px 0 0;
        color: #7a8b98;
        font-size: 11px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0,1fr));
        gap: 14px;
    }

    .summary-card {
        position: relative;
        overflow: hidden;
        min-height: 126px;
        padding: 18px;
        border: 1px solid #dfe7ed;
        border-radius: 17px;
        background: #fff;
        box-shadow: 0 9px 24px rgba(20,57,83,.04);
        transition: .2s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 13px 28px rgba(20,57,83,.075);
    }

    .summary-card::after {
        content: "";
        position: absolute;
        width: 75px;
        height: 75px;
        right: -25px;
        bottom: -30px;
        border-radius: 50%;
        background: rgba(22,76,150,.035);
    }

    .summary-icon {
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        margin-bottom: 14px;
        border-radius: 10px;
        background: #edf4fb;
        color: #164c96;
    }

    .summary-card:nth-child(2) .summary-icon {
        background: #eff9f3;
        color: #008640;
    }

    .summary-card:nth-child(3) .summary-icon {
        background: #fff3f2;
        color: #b14b43;
    }

    .summary-card:nth-child(4) .summary-icon {
        background: #fff8e8;
        color: #9a7115;
    }

    .summary-icon svg {
        width: 18px;
        height: 18px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .summary-label {
        display: block;
        color: #758697;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .55px;
        text-transform: uppercase;
    }

    .summary-value {
        position: relative;
        z-index: 1;
        display: block;
        margin-top: 7px;
        color: #082d55;
        font-size: 22px;
        font-weight: 850;
        letter-spacing: -.35px;
    }

    .summary-value.date {
        font-size: 18px;
    }

    @media (max-width: 1000px) {
        .summary-grid {
            grid-template-columns: repeat(2,minmax(0,1fr));
        }
    }

    @media (max-width: 820px) {
        .agent-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .agent-hero {
            padding: 20px;
        }

        .agent-identity {
            align-items: flex-start;
        }

        .agent-avatar {
            width: 52px;
            height: 52px;
        }

        .agent-heading h2 {
            font-size: 21px;
            white-space: normal;
        }

        .hero-actions,
        .hero-actions .btn {
            width: 100%;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .info-row {
            grid-template-columns: 1fr;
            gap: 5px;
            padding: 10px 0;
        }

        .info-value {
            text-align: left;
        }
    }
</style>
@endpush

@section('content')
@php
    $estadoAgenteActivo = in_array(
        strtoupper((string) $agente->estado),
        ['ACTIVO', '1', 'TRUE'],
        true
    );

    $estadoUsuarioActivo = in_array(
        strtoupper((string) $agente->usuario_estado),
        ['ACTIVO', '1', 'TRUE'],
        true
    );
@endphp

<div class="agent-detail">
    <section class="agent-hero">
        <div class="agent-identity">
            <div class="agent-avatar">
                <svg viewBox="0 0 24 24">
                    <path d="M3 21h18"/>
                    <path d="M5 21V7l7-4 7 4v14"/>
                    <path d="M9 21v-6h6v6"/>
                    <path d="M8 10h.01"/>
                    <path d="M12 10h.01"/>
                    <path d="M16 10h.01"/>
                </svg>
            </div>

            <div class="agent-heading">
                <div class="agent-eyebrow">
                    <span class="agent-eyebrow-dot"></span>
                    Ficha del Agente MICOOPE
                </div>

                <h2>{{ $agente->nombre_negocio }}</h2>

                <p>
                    <span>{{ $agente->codigo_agente }}</span>
                    <span class="hero-separator">•</span>
                    <span>{{ $agente->region_nombre ?: 'Sin región' }}</span>
                    <span class="hero-separator">•</span>
                    <span>{{ $agente->ruta_nombre ?: 'Sin ruta' }}</span>
                </p>
            </div>
        </div>

        <div class="hero-actions">
            <a
                href="{{ route('administrador.agentes.index') }}"
                class="btn"
            >
                <svg viewBox="0 0 24 24">
                    <path d="M19 12H5"/>
                    <path d="m11 18-6-6 6-6"/>
                </svg>
                Regresar
            </a>

            <a
                href="{{ route('administrador.agentes.edit', $agente->id) }}"
                class="btn btn-primary"
            >
                <svg viewBox="0 0 24 24">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                </svg>
                Editar Agente
            </a>
        </div>
    </section>

    <div class="detail-grid">
        <section class="detail-panel">
            <header class="panel-header">
                <div class="panel-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 21h18"/>
                        <path d="M5 21V7l7-4 7 4v14"/>
                        <path d="M9 21v-6h6v6"/>
                    </svg>
                </div>

                <div class="panel-title">
                    <h3>Información del Agente</h3>
                    <p>Datos operativos y ubicación asignada.</p>
                </div>
            </header>

            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">Código de agente</span>
                    <span class="info-value">{{ $agente->codigo_agente }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Nombre del negocio</span>
                    <span class="info-value">{{ $agente->nombre_negocio }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Propietario</span>
                    <span class="info-value">{{ $agente->nombre_propietario ?: '—' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Dirección</span>
                    <span class="info-value">{{ $agente->direccion ?: '—' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Región</span>
                    <span class="info-value">{{ $agente->region_nombre ?: '—' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Ruta asignada</span>
                    <span class="info-value">
                        {{ $agente->ruta_codigo ?: '—' }}
                        @if ($agente->ruta_nombre)
                            — {{ $agente->ruta_nombre }}
                        @endif
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Estado</span>
                    <span class="info-value">
                        <span class="status-badge {{ $estadoAgenteActivo ? '' : 'inactive' }}">
                            <span class="status-dot"></span>
                            {{ $agente->estado }}
                        </span>
                    </span>
                </div>
            </div>
        </section>

        <section class="detail-panel">
            <header class="panel-header">
                <div class="panel-icon account">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                        <path d="M18 8h3"/>
                        <path d="M19.5 6.5v3"/>
                    </svg>
                </div>

                <div class="panel-title">
                    <h3>Cuenta de Acceso</h3>
                    <p>Información vinculada al acceso institucional.</p>
                </div>
            </header>

            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">Usuario</span>
                    <span class="info-value">{{ $agente->usuario }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Nombres</span>
                    <span class="info-value">{{ $agente->nombres ?: '—' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Apellidos</span>
                    <span class="info-value">{{ $agente->apellidos ?: '—' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Estado de cuenta</span>
                    <span class="info-value">
                        <span class="status-badge {{ $estadoUsuarioActivo ? '' : 'inactive' }}">
                            <span class="status-dot"></span>
                            {{ $agente->usuario_estado }}
                        </span>
                    </span>
                </div>
            </div>
        </section>
    </div>

    <section class="summary-section">
        <div class="summary-heading">
            <div>
                <h3>Resumen de Arqueos</h3>
                <p>Indicadores generales asociados al historial del agente.</p>
            </div>
        </div>

        <div class="summary-grid">
            <article class="summary-card">
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="M7 9h10"/>
                        <path d="M7 13h5"/>
                    </svg>
                </div>
                <span class="summary-label">Total Arqueos</span>
                <strong class="summary-value">{{ $resumen->total_arqueos }}</strong>
            </article>

            <article class="summary-card">
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="m8 12 2.5 2.5L16 9"/>
                    </svg>
                </div>
                <span class="summary-label">Arqueos Válidos</span>
                <strong class="summary-value">{{ $resumen->arqueos_validos }}</strong>
            </article>

            <article class="summary-card">
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="m7 7 10 10"/>
                    </svg>
                </div>
                <span class="summary-label">Anulados</span>
                <strong class="summary-value">{{ $resumen->anulados }}</strong>
            </article>

            <article class="summary-card">
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M16 3v4"/>
                        <path d="M8 3v4"/>
                        <path d="M3 10h18"/>
                    </svg>
                </div>
                <span class="summary-label">Último Arqueo</span>
                <strong class="summary-value date">
                    {{ $resumen->ultimo_arqueo
                        ? \Carbon\Carbon::parse($resumen->ultimo_arqueo)->format('d/m/Y')
                        : 'Sin registro' }}
                </strong>
            </article>
        </div>
    </section>
</div>
@endsection
