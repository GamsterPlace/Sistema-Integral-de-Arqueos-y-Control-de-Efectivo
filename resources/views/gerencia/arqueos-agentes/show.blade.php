@extends('layouts.gerencia')

@section('title', 'Detalle de Arqueo')
@section('module-title', 'Arqueos por Agente')

@push('styles')
<style>
    .detail-wrapper {
        width: calc(100% - 32px);
        max-width: 1080px;
        margin: 0 auto;
        padding-bottom: 26px;
    }

    .detail-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 16px;
    }

    .detail-toolbar-copy h2 {
        margin: 0;
        color: #0a3158;
        font-size: 24px;
        letter-spacing: -.45px;
    }

    .detail-toolbar-copy p {
        margin: 6px 0 0;
        color: #748592;
        font-size: 12px;
        line-height: 1.5;
    }

    .detail-card {
        position: relative;
        overflow: hidden;
        border: 1px solid #d5dce1;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 18px 48px rgba(12, 49, 82, .10);
    }

    .detail-card::after {
        content: "";
        position: absolute;
        z-index: 0;
        top: 305px;
        left: 50%;
        width: 390px;
        height: 430px;
        opacity: .045;
        pointer-events: none;
        transform: translateX(-50%);
        background: url("{{ asset('images/logos/agentes-micoope.png') }}") center/contain no-repeat;
    }

    .detail-header,
    .detail-body {
        position: relative;
        z-index: 2;
    }

    .detail-header {
        position: relative;
        display: grid;
        grid-template-columns: 180px minmax(350px, 1fr) 260px;
        align-items: start;
        gap: 18px;
        padding: 20px 28px 24px;
        border-bottom: 1px solid #e5eaee;
    }

    .detail-header::after {
        content: "";
        position: absolute;
        top: 66px;
        right: 28px;
        width: calc(100% - 210px);
        height: 3px;
        background: linear-gradient(
            to right,
            #4d693f 0%,
            #4d693f 66.666%,
            #d8b427 66.666%,
            #d8b427 100%
        );
    }

    .detail-brand {
        width: 170px;
        height: 58px;
        overflow: hidden;
        color: transparent;
        font-size: 0;
        background: url("{{ asset('images/logos/ecosaba.png') }}") left center/contain no-repeat;
    }

    .detail-brand span {
        display: none;
    }

    .detail-title {
        min-width: 0;
        padding-top: 64px;
        text-align: center;
    }

    .detail-title h2 {
        margin: 0;
        color: #111;
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(21px, 1.85vw, 28px);
        font-weight: 700;
        line-height: 1.08;
        letter-spacing: -.35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .detail-title p {
        margin: 8px 0 0;
        color: #111;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .detail-reference {
        align-self: start;
        min-width: 0;
        padding-top: 47px;
        color: #7d8a94;
        font-size: 9px;
        font-weight: 800;
        text-align: right;
        letter-spacing: .55px;
        text-transform: uppercase;
    }

    .detail-reference strong {
        display: block;
        margin-top: 5px;
        color: #a31d17;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 14px;
        line-height: 1.2;
        letter-spacing: .25px;
        white-space: nowrap;
        overflow: visible;
        word-break: normal;
    }

    .detail-body {
        padding: 24px 28px;
    }

    .section {
        margin-bottom: 23px;
    }

    .section:last-child {
        margin-bottom: 0;
    }

    .section-title {
        margin: 0 0 15px;
        padding-bottom: 6px;
        border-bottom: 1px solid #dce4e9;
        color: #111;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 16px;
        font-weight: 700;
    }

    .information-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 18px 22px;
    }

    .field {
        min-width: 0;
    }

    .field-3 {
        grid-column: span 3;
    }

    .field-5 {
        grid-column: span 5;
    }

    .field-6 {
        grid-column: span 6;
    }

    .field-7 {
        grid-column: span 7;
    }

    .field-label {
        display: block;
        margin-bottom: 3px;
        color: #333;
        font-size: 10px;
        font-weight: 800;
    }

    .field-value {
        display: block;
        width: 100%;
        min-width: 0;
        min-height: 36px;
        padding: 7px 3px 4px;
        overflow: hidden;
        border-bottom: 1px solid #4f5961;
        color: #111;
        font-size: 12px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .count-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(300px, .65fr);
        gap: 26px;
        align-items: start;
    }

    .denominations-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        min-width: 0;
    }

    .count-card {
        width: 100%;
        min-width: 0;
        overflow: hidden;
    }

    .card-heading {
        padding: 6px 0 9px;
        color: #111;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 17px;
        font-weight: 700;
    }

    .count-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }

    .count-table th:nth-child(1),
    .count-table td:nth-child(1) {
        width: 31%;
    }

    .count-table th:nth-child(2),
    .count-table td:nth-child(2) {
        width: 28%;
        text-align: center;
    }

    .count-table th:nth-child(3),
    .count-table td:nth-child(3) {
        width: 41%;
        text-align: right;
    }

    .count-table th {
        padding: 7px 8px;
        border: 0;
        color: #333;
        font-size: 9px;
        font-weight: 800;
        text-align: left;
    }

    .count-table td {
        padding: 7px 8px;
        border: 0;
        color: #111;
        font-size: 12px;
    }

    .denomination-label {
        font-weight: 800;
        white-space: nowrap;
    }

    .quantity-value {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        min-height: 34px;
        padding: 5px 8px;
        border: 1px solid #cfd8de;
        border-bottom-color: #687782;
        border-radius: 6px;
        background: #fff;
        color: #111;
        font-weight: 800;
    }

    .subtotal-value {
        display: block;
        min-height: 28px;
        padding: 6px 3px 3px;
        border-bottom: 1px solid #4f5961;
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
    }

    .totals-card {
        position: sticky;
        top: 96px;
        width: 100%;
        min-width: 0;
        overflow: hidden;
        border: 1px solid #d3dce2;
        border-radius: 14px;
        background: rgba(250, 252, 253, .95);
        box-shadow: 0 10px 26px rgba(20, 57, 83, .06);
    }

    .totals-card .card-heading {
        padding: 14px 16px;
        border-bottom: 1px solid #dce4e9;
        background: #f7f9fa;
        color: #0a3158;
        font-family: inherit;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .totals-content {
        padding: 17px;
    }

    .total-line {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 135px;
        align-items: center;
        gap: 10px;
        min-height: 48px;
        border-bottom: 1px solid #e2e8ec;
    }

    .total-line:last-of-type {
        border-bottom: 0;
    }

    .total-line span:first-child {
        color: #313c45;
        font-size: 11px;
        font-weight: 800;
    }

    .money-value {
        display: block;
        min-height: 35px;
        padding: 7px 3px 4px;
        border-bottom: 1px solid #4f5961;
        color: #111;
        font-size: 12px;
        font-weight: 850;
        text-align: right;
    }

    .total-arqueado {
        color: #082d55;
        font-size: 14px;
    }

    .difference-card {
        margin-top: 17px;
        padding: 15px 14px;
        border: 2px solid #9eb6c9;
        border-radius: 10px;
        background: #eff5f9;
        text-align: center;
    }

    .difference-card span {
        display: block;
        color: #4f6574;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .6px;
        text-transform: uppercase;
    }

    .difference-card strong {
        display: block;
        margin-top: 7px;
        color: #174f80;
        font-size: 22px;
    }

    .result-information {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px 22px;
    }

    .text-box {
        min-height: 102px;
        padding: 8px 4px;
        background: repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent 27px,
            #5f6971 28px
        );
        color: #111;
        font-size: 12px;
        line-height: 28px;
        white-space: pre-wrap;
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid #dfe5e9;
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 43px;
        padding: 0 20px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
    }

    .print-button {
        border: 0;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #fff;
        box-shadow: 0 8px 18px rgba(22, 76, 150, .20);
    }

    @media (max-width: 1050px) {
        .count-layout {
            grid-template-columns: 1fr;
        }

        .totals-card {
            position: static;
        }

        .detail-header {
            grid-template-columns: 155px minmax(280px, 1fr) 220px;
        }

        .detail-title h2 {
            font-size: 22px;
        }
    }

    @media (max-width: 850px) {
        .detail-header {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .detail-header::after {
            position: static;
            display: block;
            grid-column: 1;
            width: 100%;
            margin-top: 8px;
        }

        .detail-brand {
            margin: 0 auto;
        }

        .detail-title,
        .detail-reference {
            padding-top: 0;
        }

        .detail-title h2 {
            white-space: normal;
        }

        .detail-reference {
            text-align: center;
        }

        .detail-reference strong {
            white-space: normal;
            font-size: 14px;
        }

        .information-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .field-3 {
            grid-column: span 1;
        }

        .field-5,
        .field-6,
        .field-7 {
            grid-column: span 2;
        }
    }

    @media (max-width: 620px) {
        .detail-toolbar {
            display: block;
        }

        .detail-body,
        .detail-header {
            padding-left: 16px;
            padding-right: 16px;
        }

        .information-grid,
        .result-information {
            grid-template-columns: 1fr;
        }

        .field-3,
        .field-5,
        .field-6,
        .field-7 {
            grid-column: span 1;
        }

        .total-line {
            grid-template-columns: 1fr;
            gap: 4px;
            padding: 8px 0;
        }

        .money-value {
            text-align: left;
        }

        .actions {
            flex-direction: column;
        }

        .action-button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $agente = $arqueo->agente;
    $ruta = $agente?->ruta;
    $region = $ruta?->region;

    $nombreResponsable = trim(
        ($responsable->nombres ?? '')
        . ' '
        . ($responsable->apellidos ?? '')
    );
@endphp

<div class="detail-wrapper">

    <div class="detail-toolbar">
        <div class="detail-toolbar-copy">
            <h2>Arqueo {{ $arqueo->numero_arqueo }}</h2>
            <p>Consulta detallada del arqueo realizado por el Agente.</p>
        </div>
    </div>

    <article class="detail-card">

        <header class="detail-header">
            <div class="detail-brand">
                ECOSABA
                <span>Agentes MICOOPE</span>
            </div>

            <div class="detail-title">
                <h2>Arqueo y Corte de Caja</h2>
                <p>Agente MICOOPE</p>
            </div>

            <div class="detail-reference">
                Número de arqueo
                <strong>{{ $arqueo->numero_arqueo }}</strong>
            </div>
        </header>

        <div class="detail-body">

            <section class="section">
                <h3 class="section-title">Información General</h3>

                <div class="information-grid">

                    <div class="field field-7">
                        <span class="field-label">Agente</span>
                        <span class="field-value">
                            {{ $agente?->codigo_agente }} — {{ $agente?->nombre_negocio }}
                        </span>
                    </div>

                    <div class="field field-5">
                        <span class="field-label">Propietario</span>
                        <span class="field-value">
                            {{ $agente?->nombre_propietario }}
                        </span>
                    </div>

                    <div class="field field-3">
                        <span class="field-label">Región</span>
                        <span class="field-value">
                            {{ $region?->nombre ?? '—' }}
                        </span>
                    </div>

                    <div class="field field-5">
                        <span class="field-label">Ruta</span>
                        <span class="field-value">
                            {{ $ruta?->codigo ?? '' }} {{ $ruta?->nombre ?? '—' }}
                        </span>
                    </div>

                    <div class="field field-3">
                        <span class="field-label">Fecha</span>
                        <span class="field-value">
                            {{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}
                        </span>
                    </div>

                    <div class="field field-3">
                        <span class="field-label">Estado</span>
                        <span class="field-value">
                            {{ str_replace('_',' ',$arqueo->estado) }}
                        </span>
                    </div>

                    <div class="field field-3">
                        <span class="field-label">Extemporáneo</span>
                        <span class="field-value">
                            {{ $arqueo->fuera_fecha_ordinaria ? 'Sí' : 'No' }}
                        </span>
                    </div>

                </div>
            </section>

            <section class="section">
                <h3 class="section-title">Conteo de efectivo</h3>

                <div class="count-layout">

                    <div class="denominations-grid">

                        <div class="count-card">
                            <div class="card-heading">Billetes</div>

                            <table class="count-table">
                                <thead>
                                    <tr>
                                        <th>Denominación</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($billetes as $detalle)
                                        <tr>
                                            <td class="denomination-label">
                                                Q {{ number_format((float)$detalle->denominacion,2) }}
                                            </td>

                                            <td>
                                                <span class="quantity-value">
                                                    {{ $detalle->cantidad }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="subtotal-value">
                                                    Q {{ number_format((float)$detalle->subtotal,2) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">
                                                Sin billetes registrados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="count-card">
                            <div class="card-heading">Monedas</div>

                            <table class="count-table">
                                <thead>
                                    <tr>
                                        <th>Denominación</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($monedas as $detalle)
                                        <tr>
                                            <td class="denomination-label">
                                                Q {{ number_format((float)$detalle->denominacion,2) }}
                                            </td>

                                            <td>
                                                <span class="quantity-value">
                                                    {{ $detalle->cantidad }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="subtotal-value">
                                                    Q {{ number_format((float)$detalle->subtotal,2) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">
                                                Sin monedas registradas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <aside class="totals-card">
                        <div class="card-heading">
                            Resultado
                        </div>

                        <div class="totals-content">

                            <div class="total-line">
                                <span>Saldo Sistema</span>
                                <span class="money-value">
                                    Q {{ number_format((float)$arqueo->saldo_sistema,2) }}
                                </span>
                            </div>

                            <div class="total-line">
                                <span>Total Arqueado</span>
                                <span class="money-value total-arqueado">
                                    Q {{ number_format((float)$arqueo->total_arqueado,2) }}
                                </span>
                            </div>

                            <div class="difference-card">
                                <span>Diferencia</span>
                                <strong>
                                    Q {{ number_format(abs((float)$arqueo->diferencia),2) }}
                                </strong>
                            </div>

                        </div>
                    </aside>

                </div>
            </section>

            <section class="section">
                <h3 class="section-title">Información del resultado</h3>

                <div class="result-information">

                    <div class="field">
                        <span class="field-label">Responsable</span>
                        <span class="field-value">
                            {{ $nombreResponsable !== '' ? $nombreResponsable : ($responsable->usuario ?? '—') }}
                        </span>
                    </div>

                    <div class="field">
                        <span class="field-label">Observaciones</span>
                        <div class="text-box">{{ $arqueo->observaciones ?: 'Sin observaciones' }}</div>
                    </div>

                </div>
            </section>

            <div class="actions">
                <a
                    href="{{ route('gerencia.arqueos-agentes.imprimir',$arqueo->id) }}"
                    target="_blank"
                    class="action-button print-button"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        style="width:16px;height:16px;"
                    >
                        <path d="M6 9V2h12v7"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>

                    Imprimir PDF
                </a>
            </div>

        </div>
    </article>

</div>
@endsection
