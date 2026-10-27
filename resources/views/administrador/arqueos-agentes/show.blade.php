@extends('layouts.administrador')

@section('title', 'Detalle Arqueo Agente')
@section('module-title', 'Arqueos de Agentes')

@push('styles')
<style>
    .agent-detail-page {
        max-width: 1280px;
        margin: 0 auto;
    }

    .detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }

    .detail-heading {
        display: flex;
        align-items: center;
        gap: 15px;
        min-width: 0;
    }

    .detail-heading-icon {
        width: 54px;
        height: 54px;
        flex: 0 0 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        color: #fff;
        background: linear-gradient(145deg, var(--azul-principal), var(--azul-profundo));
        box-shadow: 0 10px 24px rgba(22, 76, 150, .18);
    }

    .detail-heading-icon svg {
        width: 26px;
        height: 26px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .detail-heading h2 {
        margin: 0 0 4px;
        color: var(--azul-profundo);
        font-size: 25px;
        line-height: 1.15;
        overflow-wrap: anywhere;
    }

    .detail-heading p {
        margin: 0;
        color: var(--texto-secundario);
        font-size: 14px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .btn-detail {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 15px;
        border: 1px solid #d6e1e9;
        border-radius: 11px;
        background: #fff;
        color: #31536e;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
    }

    .btn-detail:hover {
        transform: translateY(-1px);
        border-color: #a9becd;
        color: var(--azul-principal);
        box-shadow: 0 7px 18px rgba(24, 66, 99, .09);
    }

    .btn-detail.primary {
        border-color: transparent;
        color: #fff;
        background: linear-gradient(135deg, var(--azul-principal), var(--azul-secundario));
        box-shadow: 0 8px 18px rgba(22, 76, 150, .16);
    }

    .btn-detail.primary:hover {
        color: #fff;
        box-shadow: 0 10px 22px rgba(22, 76, 150, .23);
    }

    .btn-detail svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .document-banner {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 18px;
        padding: 20px 22px;
        border: 1px solid #dce6ed;
        border-radius: 18px;
        background:
            linear-gradient(105deg, rgba(22, 76, 150, .055), rgba(0, 166, 81, .025)),
            #fff;
        box-shadow: 0 8px 24px rgba(28, 64, 92, .055);
    }

    .document-banner::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: var(--verde-principal);
    }

    .document-eyebrow {
        margin-bottom: 5px;
        color: var(--texto-secundario);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .document-business {
        margin: 0 0 6px;
        color: var(--azul-profundo);
        font-size: 20px;
        line-height: 1.25;
    }

    .document-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 7px 16px;
        color: var(--texto-secundario);
        font-size: 13px;
    }

    .document-meta strong {
        color: var(--texto);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 32px;
        padding: 0 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .025em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-badge::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .status-certified {
        color: #08783d;
        background: #e9f8f0;
        border: 1px solid #c7ecd8;
    }

    .status-pending {
        color: #986b00;
        background: #fff8e3;
        border: 1px solid #f2e2a8;
    }

    .status-cancelled {
        color: #b33a3a;
        background: #fff0f0;
        border: 1px solid #f2cccc;
    }

    .status-neutral {
        color: #526d82;
        background: #f1f5f8;
        border: 1px solid #dce5eb;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(340px, .9fr);
        gap: 18px;
    }

    .detail-panel {
        overflow: hidden;
        border: 1px solid #dfe7ee;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(28, 64, 92, .05);
    }

    .panel-heading {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 17px 19px;
        border-bottom: 1px solid #e6edf2;
        background: #fbfcfd;
    }

    .panel-icon {
        width: 37px;
        height: 37px;
        flex: 0 0 37px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        color: var(--azul-principal);
        background: #edf4fb;
    }

    .panel-icon.result {
        color: var(--verde-oscuro);
        background: #eaf8f1;
    }

    .panel-icon svg {
        width: 19px;
        height: 19px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .panel-heading h3 {
        margin: 0;
        color: var(--azul-profundo);
        font-size: 15px;
    }

    .panel-heading p {
        margin: 2px 0 0;
        color: var(--texto-secundario);
        font-size: 12px;
    }

    .info-list {
        padding: 5px 19px 8px;
    }

    .info-row {
        display: grid;
        grid-template-columns: 150px minmax(0, 1fr);
        gap: 15px;
        align-items: center;
        min-height: 52px;
        border-bottom: 1px solid #edf1f4;
    }

    .info-row:last-child {
        border-bottom: 0;
    }

    .info-label {
        color: var(--texto-secundario);
        font-size: 12px;
        font-weight: 700;
    }

    .info-value {
        color: var(--texto);
        font-size: 13px;
        font-weight: 700;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .info-value.primary {
        color: var(--azul-principal);
    }

    .money-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        padding: 18px 18px 5px;
    }

    .money-card {
        padding: 14px;
        border: 1px solid #e2eaf0;
        border-radius: 13px;
        background: var(--fondo-suave);
    }

    .money-card .label {
        display: block;
        margin-bottom: 5px;
        color: var(--texto-secundario);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .money-card .value {
        display: block;
        color: var(--azul-profundo);
        font-size: 18px;
        font-weight: 800;
        line-height: 1.2;
    }

    .difference-card {
        margin: 7px 18px 18px;
        padding: 15px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border: 1px solid #cfe0eb;
        border-radius: 13px;
        background: #f2f7fb;
    }

    .difference-card .label {
        color: #527087;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .difference-card .value {
        color: var(--azul-principal);
        font-size: 20px;
        font-weight: 800;
        white-space: nowrap;
    }

    .observations {
        margin: 0 18px 18px;
        padding: 15px 16px;
        border: 1px solid #e3eaf0;
        border-radius: 13px;
        background: #fff;
    }

    .observations-title {
        margin-bottom: 7px;
        color: var(--texto-secundario);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .observations p {
        margin: 0;
        color: var(--texto);
        font-size: 13px;
        line-height: 1.6;
        white-space: pre-line;
    }

    @media (max-width: 950px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .detail-header,
        .document-banner {
            align-items: flex-start;
            flex-direction: column;
        }

        .header-actions {
            width: 100%;
        }

        .btn-detail {
            flex: 1;
        }

        .money-summary {
            grid-template-columns: 1fr;
        }

        .info-row {
            grid-template-columns: 1fr;
            gap: 4px;
            padding: 12px 0;
        }

        .info-value {
            text-align: left;
        }

        .difference-card {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
@php
    $a = $arqueo->agente;
    $r = $a?->ruta;
    $reg = $r?->region;

    $estadoClase = match ($arqueo->estado) {
        'CERTIFICADO' => 'status-certified',
        'PENDIENTE_CERTIFICACION' => 'status-pending',
        'ANULADO' => 'status-cancelled',
        default => 'status-neutral',
    };

    $fechaArqueo = $arqueo->fecha_arqueo
        ? \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y')
        : '—';

    $horaInicio = $arqueo->hora_inicio
        ? \Carbon\Carbon::parse($arqueo->hora_inicio)->format('H:i')
        : '—';

    $horaFin = $arqueo->hora_fin
        ? \Carbon\Carbon::parse($arqueo->hora_fin)->format('H:i')
        : '—';
@endphp

<div class="agent-detail-page">
    <div class="detail-header">
        <div class="detail-heading">
            <div class="detail-heading-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 3h12a2 2 0 0 1 2 2v16H4V5a2 2 0 0 1 2-2Z"></path>
                    <path d="M8 7h8M8 11h8M8 15h5"></path>
                </svg>
            </div>

            <div>
                <h2>{{ $arqueo->numero_arqueo }}</h2>
                <p>Detalle completo del arqueo realizado por el Agente MICOOPE.</p>
            </div>
        </div>

        <div class="header-actions">
            <a
                href="{{ route('administrador.arqueos-agentes.index') }}"
                class="btn-detail"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"></path>
                </svg>
                Regresar
            </a>

            <a
                target="_blank"
                href="{{ route('administrador.arqueos-agentes.imprimir', $arqueo->id) }}"
                class="btn-detail primary"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 9V3h12v6"></path>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="7"></rect>
                    <path d="M18 12h.01"></path>
                </svg>
                Imprimir PDF
            </a>
        </div>
    </div>

    <section class="document-banner">
        <div>
            <div class="document-eyebrow">Arqueo diario de Agente</div>

            <h3 class="document-business">
                {{ $a?->nombre_negocio ?? $arqueo->nombre_negocio_historico ?? 'Agente MICOOPE' }}
            </h3>

            <div class="document-meta">
                <span>
                    Código:
                    <strong>{{ $a?->codigo_agente ?? $arqueo->codigo_agente_historico ?? '—' }}</strong>
                </span>

                <span>
                    Fecha:
                    <strong>{{ $fechaArqueo }}</strong>
                </span>

                <span>
                    Región:
                    <strong>{{ $reg?->nombre ?? $arqueo->region_historica ?? '—' }}</strong>
                </span>

                <span>
                    Ruta:
                    <strong>{{ $r?->nombre ?? $arqueo->ruta_historica ?? '—' }}</strong>
                </span>
            </div>
        </div>

        <span class="status-badge {{ $estadoClase }}">
            {{ str_replace('_', ' ', $arqueo->estado) }}
        </span>
    </section>

    <div class="detail-grid">
        <section class="detail-panel">
            <div class="panel-heading">
                <div class="panel-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="3"></circle>
                        <path d="M5 21v-2a7 7 0 0 1 14 0v2"></path>
                    </svg>
                </div>

                <div>
                    <h3>Información del Arqueo</h3>
                    <p>Datos del agente y registro histórico del documento.</p>
                </div>
            </div>

            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">Código de Agente</span>
                    <span class="info-value primary">
                        {{ $a?->codigo_agente ?? $arqueo->codigo_agente_historico ?? '—' }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Nombre del negocio</span>
                    <span class="info-value">
                        {{ $a?->nombre_negocio ?? $arqueo->nombre_negocio_historico ?? '—' }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Propietario</span>
                    <span class="info-value">
                        {{ $arqueo->nombre_propietario_historico ?? $a?->nombre_propietario ?? '—' }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Región</span>
                    <span class="info-value">
                        {{ $reg?->nombre ?? $arqueo->region_historica ?? '—' }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Ruta</span>
                    <span class="info-value">
                        {{ $r?->nombre ?? $arqueo->ruta_historica ?? '—' }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Fecha</span>
                    <span class="info-value">{{ $fechaArqueo }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Hora de inicio</span>
                    <span class="info-value">{{ $horaInicio }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Hora de finalización</span>
                    <span class="info-value">{{ $horaFin }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Estado</span>
                    <span class="info-value">
                        <span class="status-badge {{ $estadoClase }}">
                            {{ str_replace('_', ' ', $arqueo->estado) }}
                        </span>
                    </span>
                </div>
            </div>
        </section>

        <section class="detail-panel">
            <div class="panel-heading">
                <div class="panel-icon result">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 19V5"></path>
                        <path d="M4 19h16"></path>
                        <path d="m7 15 4-4 3 2 5-6"></path>
                    </svg>
                </div>

                <div>
                    <h3>Resultado del Arqueo</h3>
                    <p>Resumen de los valores registrados.</p>
                </div>
            </div>

            <div class="money-summary">
                <div class="money-card">
                    <span class="label">Saldo del Sistema</span>
                    <span class="value">
                        Q {{ number_format((float) $arqueo->saldo_sistema, 2) }}
                    </span>
                </div>

                <div class="money-card">
                    <span class="label">Total Arqueado</span>
                    <span class="value">
                        Q {{ number_format((float) $arqueo->total_arqueado, 2) }}
                    </span>
                </div>
            </div>

            <div class="difference-card">
                <span class="label">Diferencia</span>
                <span class="value">
                    Q {{ number_format(abs((float) $arqueo->diferencia), 2) }}
                </span>
            </div>

            <div class="observations">
                <div class="observations-title">Observaciones</div>
                <p>{{ $arqueo->observaciones ?: 'Sin observaciones registradas.' }}</p>
            </div>
        </section>
    </div>
</div>
@endsection
