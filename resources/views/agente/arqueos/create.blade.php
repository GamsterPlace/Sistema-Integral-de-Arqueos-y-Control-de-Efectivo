@extends('layouts.agente')

@section('title', 'Crear Arqueo')
@section('module-title', 'Crear Arqueo')

@push('styles')
<style>
    .arqueo-wrapper {
        width: 100%;
        max-width: 1120px;
        margin: 0 auto;
    }

    .arqueo-form {
        position: relative;
        overflow: hidden;
        border: 1px solid #d5dce1;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 18px 48px rgba(12, 49, 82, 0.10);
    }

    .arqueo-form::after {
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
        background: url("{{ asset('images/logos/agentes-micoope.png') }}") center / contain no-repeat;
    }

    .arqueo-header,
    .arqueo-body {
        position: relative;
        z-index: 2;
    }

    .arqueo-header {
        position: relative;
        display: grid;
        grid-template-columns: 190px minmax(300px, 1fr) 190px;
        align-items: center;
        gap: 18px;
        padding: 24px 30px 28px;
        border-bottom: 1px solid #e5eaee;
    }

    .arqueo-header::after {
        content: "";
        position: absolute;
        top: 70px;
        right: 30px;
        width: calc(100% - 245px);
        height: 3px;
        background: linear-gradient(
            to right,
            #4d693f 0%,
            #4d693f 66.666%,
            #d8b427 66.666%,
            #d8b427 100%
        );
    }

    .arqueo-brand {
        width: 178px;
        height: 64px;
        overflow: hidden;
        color: transparent;
        font-size: 0;
        background: url("{{ asset('images/logos/ecosaba.png') }}") left center / contain no-repeat;
    }

    .arqueo-brand span {
        display: none;
    }

    .arqueo-title {
        min-width: 0;
        padding-top: 62px;
        text-align: center;
    }

    .arqueo-title h2 {
        margin: 0;
        color: #111111;
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(22px, 2.1vw, 30px);
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -.45px;
        text-transform: uppercase;
    }

    .arqueo-title p {
        margin: 7px 0 0;
        color: #111111;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .arqueo-reference {
        align-self: start;
        padding-top: 53px;
        color: #7d8a94;
        font-size: 9px;
        font-weight: 800;
        text-align: right;
        letter-spacing: .6px;
        text-transform: uppercase;
    }

    .arqueo-reference strong {
        display: block;
        margin-top: 4px;
        color: #a31d17;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 17px;
        letter-spacing: .8px;
    }

    .arqueo-body {
        padding: 28px;
    }

    .form-section {
        margin-bottom: 28px;
    }

    .section-heading {
        margin: 0 0 15px;
        padding-bottom: 6px;
        border-bottom: 1px solid #dce4e9;
        color: #111111;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: none;
    }

    .information-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 18px 22px;
    }

    .field { min-width: 0; }
    .field-3 { grid-column: span 3; }
    .field-5 { grid-column: span 5; }
    .field-7 { grid-column: span 7; }

    .field-label {
        display: block;
        margin-bottom: 3px;
        color: #333333;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: none;
    }

    .document-value {
        display: block;
        width: 100%;
        min-width: 0;
        min-height: 36px;
        padding: 7px 3px 4px;
        overflow: hidden;
        border: 0;
        border-bottom: 1px solid #4f5961;
        border-radius: 0;
        outline: none;
        background: transparent;
        color: #111111;
        font-size: 12px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .document-value-editable {
        padding-right: 10px;
        padding-left: 10px;
        border: 1px solid #ccd7de;
        border-radius: 7px;
        background: #ffffff;
    }

    .document-value-editable:focus {
        border-color: #1c64b5;
        box-shadow: 0 0 0 3px rgba(28, 100, 181, .11);
    }

    .count-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(285px, .65fr);
        gap: 24px;
        align-items: start;
    }

    .denominations-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        min-width: 0;
    }

    .count-card {
        width: 100%;
        min-width: 0;
        overflow: hidden;
        border: 0;
        border-radius: 0;
        background: transparent;
    }

    .totals-card {
        width: 100%;
        min-width: 0;
        overflow: hidden;
        border: 1px solid #d5dce1;
        border-radius: 13px;
        background: rgba(250, 252, 253, .95);
        box-shadow: 0 10px 26px rgba(20, 57, 83, .05);
    }

    .card-heading {
        padding: 6px 0 9px;
        border-bottom: 0;
        background: transparent;
        color: #111111;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: none;
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

    .table-scroll {
        width: 100%;
        overflow: visible;
    }

    .count-table {
        width: 100%;
        min-width: 0;
        table-layout: fixed;
        border-collapse: collapse;
    }

    .count-table th,
    .count-table td {
        box-sizing: border-box;
    }

    .count-table th:nth-child(1),
    .count-table td:nth-child(1) { width: 38%; }

    .count-table th:nth-child(2),
    .count-table td:nth-child(2) {
        width: 27%;
        text-align: center;
    }

    .count-table th:nth-child(3),
    .count-table td:nth-child(3) {
        width: 35%;
        text-align: right;
    }

    .count-table th {
        padding: 7px 8px;
        border-bottom: 0;
        background: transparent;
        color: #333333;
        font-size: 9px;
        font-weight: 800;
        text-align: left;
        letter-spacing: 0;
        text-transform: none;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .count-table td {
        padding: 7px 8px;
        border-bottom: 0;
        color: #111111;
        font-size: 12px;
    }

    .count-table tbody tr:last-child td { border-bottom: 0; }

    .denomination-label {
        font-weight: 850;
        white-space: nowrap;
    }

    .quantity-input {
        display: block;
        width: 100%;
        max-width: 96px;
        min-width: 0;
        min-height: 36px;
        margin: 0 auto;
        padding: 6px 8px;
        border: 1px solid #cbd6dd;
        border-radius: 7px;
        outline: none;
        background: #ffffff;
        color: #0a3158;
        font-size: 13px;
        font-weight: 750;
        text-align: center;
    }

    .quantity-input:focus {
        border-color: #1c64b5;
        box-shadow: 0 0 0 3px rgba(28, 100, 181, .11);
    }

    .subtotal {
        display: block;
        width: 100%;
        min-width: 0;
        min-height: 28px;
        padding: 6px 3px 3px;
        border-bottom: 1px solid #4f5961;
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
    }

    .totals-content { padding: 17px; }

    .total-line {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 135px;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #e6ecef;
    }

    .total-line:last-of-type { border-bottom: 0; }

    .total-line label {
        margin: 0;
        color: #455b6b;
        font-size: 12px;
        font-weight: 800;
    }

    .money-field {
        width: 100%;
        min-width: 0;
        min-height: 39px;
        padding: 7px 10px;
        border: 1px solid #ccd7de;
        border-radius: 7px;
        outline: none;
        background: #f7f9fa;
        color: #0a3158;
        font-size: 13px;
        font-weight: 850;
        text-align: right;
    }

    .money-field-editable { background: #ffffff; }

    .money-field-editable:focus {
        border-color: #1c64b5;
        box-shadow: 0 0 0 3px rgba(28, 100, 181, .11);
    }

    .total-arqueado-line {
        margin-top: 3px;
        padding: 15px 0;
    }

    .total-arqueado-line label {
        color: #082d55;
        font-size: 13px;
        font-weight: 900;
    }

    .total-arqueado-line .money-field {
        border-color: #6892b6;
        background: #edf5fb;
        font-size: 15px;
    }

    .difference-card {
        margin-top: 17px;
        padding: 18px 14px;
        border: 2px solid #a7bfd3;
        border-radius: 10px;
        background: #eef5fb;
        text-align: center;
    }

    .difference-card span {
        display: block;
        color: #597082;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .6px;
        text-transform: uppercase;
    }

    .difference-card strong {
        display: block;
        margin-top: 7px;
        color: #174f80;
        font-size: 23px;
        overflow-wrap: anywhere;
    }

    .difference-card small {
        display: block;
        margin-top: 4px;
        color: #174f80;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .difference-card.sobrante {
        border-color: #75b993;
        background: #edf9f2;
    }

    .difference-card.sobrante strong,
    .difference-card.sobrante small { color: #197247; }

    .difference-card.faltante {
        border-color: #dda19d;
        background: #fff1f0;
    }

    .difference-card.faltante strong,
    .difference-card.faltante small { color: #ae342f; }

    .observations-input {
        display: block;
        width: 100%;
        min-height: 112px;
        padding: 8px 4px;
        border: 0;
        border-radius: 0;
        outline: none;
        resize: vertical;
        background:
            repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent 27px,
                #5f6971 28px
            );
        color: #111111;
        font-family: inherit;
        font-size: 12px;
        line-height: 28px;
    }

    .certification-textarea {
        display: block;
        width: 100%;
        min-height: 112px;
        padding: 8px 4px;
        border: 0;
        border-radius: 0;
        outline: none;
        resize: vertical;
        background:
            repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent 27px,
                #5f6971 28px
            );
        color: #111111;
        font-family: inherit;
        font-size: 12px;
        line-height: 28px;
    }

    .certification-textarea:focus {
        background:
            repeating-linear-gradient(
                to bottom,
                rgba(28, 100, 181, .02) 0,
                rgba(28, 100, 181, .02) 27px,
                #1c64b5 28px
            );
    }

    .certification-textarea::placeholder {
        color: #8a99a5;
    }

    .observations-input:focus {
        background:
            repeating-linear-gradient(
                to bottom,
                rgba(28, 100, 181, .02) 0,
                rgba(28, 100, 181, .02) 27px,
                #1c64b5 28px
            );
    }

    .signature-preview {
        margin-top: 6px;
        padding-top: 10px;
    }

    .signature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 42px;
        margin-top: 10px;
    }

    .signature-block {
        text-align: center;
    }

    .signature-placeholder {
        min-height: 44px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: 7px;
        color: #7c8993;
        font-size: 10px;
        font-style: italic;
    }

    .signature-line {
        border-bottom: 1px solid #333333;
    }

    .signature-label {
        margin-top: 5px;
        color: #111111;
        font-size: 9px;
        font-weight: 800;
        line-height: 1.4;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 30px;
        padding-top: 22px;
        border-top: 1px solid #dce4e9;
    }

    .form-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 43px;
        padding: 0 20px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
        cursor: pointer;
    }

    .cancel-button {
        border: 1px solid #ccd7de;
        background: #ffffff;
        color: #4f6575;
    }

    .submit-button {
        border: 0;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #ffffff;
        box-shadow: 0 8px 18px rgba(22, 76, 150, .20);
    }

    .submit-button:disabled {
        opacity: .65;
        cursor: not-allowed;
    }

    @media (max-width: 1050px) {
        .count-layout { grid-template-columns: 1fr; }
    }

    @media (max-width: 850px) {
        .arqueo-header {
            grid-template-columns: 1fr;
            gap: 13px;
            text-align: center;
        }

        .arqueo-reference { text-align: center; }
        .denominations-grid { grid-template-columns: 1fr; }
        .information-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .field-3 { grid-column: span 1; }
        .field-5,
        .field-7 { grid-column: span 2; }

        .count-table th:nth-child(1),
        .count-table td:nth-child(1) { width: 35%; }

        .count-table th:nth-child(2),
        .count-table td:nth-child(2) { width: 30%; }

        .count-table th:nth-child(3),
        .count-table td:nth-child(3) { width: 35%; }
    }

    @media (max-width: 560px) {
        .arqueo-body { padding: 18px 13px; }
        .arqueo-header { padding: 20px 14px; }
        .information-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 15px;
        }

        .field-3,
        .field-5,
        .field-7 { grid-column: span 1; }

        .count-table th {
            padding: 9px 6px;
            font-size: 8px;
        }

        .count-table td {
            padding: 8px 6px;
            font-size: 11px;
        }

        .quantity-input {
            max-width: 72px;
            min-height: 34px;
            padding: 5px;
        }

        .denomination-label,
        .subtotal { font-size: 11px; }

        .total-line { grid-template-columns: minmax(0, 1fr); }
        .money-field { text-align: left; }
        .signature-preview {
        margin-top: 6px;
        padding-top: 10px;
    }

    .signature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 42px;
        margin-top: 10px;
    }

    .signature-block {
        text-align: center;
    }

    .signature-placeholder {
        min-height: 44px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: 7px;
        color: #7c8993;
        font-size: 10px;
        font-style: italic;
    }

    .signature-line {
        border-bottom: 1px solid #333333;
    }

    .signature-label {
        margin-top: 5px;
        color: #111111;
        font-size: 9px;
        font-weight: 800;
        line-height: 1.4;
    }

    .form-actions { flex-direction: column-reverse; }
        .form-button { width: 100%; }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">
        <h2>Nuevo Arqueo</h2>
        <p>
            @if($esExtemporaneo ?? false)
                Complete el arqueo extemporáneo correspondiente a la fecha
                habilitada {{ $fechaArqueo->format('d/m/Y') }}.
            @else
                Complete el arqueo diario utilizando el mismo formato
                institucional que se reflejará en el PDF.
            @endif
        </p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger mb-4">
        {{ $errors->first() }}
    </div>
@endif

<div class="arqueo-wrapper">
    <form id="formArqueo" method="POST" action="{{ route('agente.arqueos.store') }}">
        @csrf

        @php
            $billetes = [200, 100, 50, 20, 10, 5, 1];
            $monedas = [1, 0.50, 0.25, 0.10, 0.05];
        @endphp

        <article class="arqueo-form">
            <header class="arqueo-header">
                <div class="arqueo-brand">
                    ECOSABA
                    <span>Agentes MICOOPE</span>
                </div>

                <div class="arqueo-title">
                    <h2>Arqueo y Corte de Caja</h2>
                    <p>Agente MICOOPE</p>
                </div>

                <div class="arqueo-reference">
                    Arqueo digital
                    <strong>NUEVO</strong>
                </div>
            </header>

            <div class="arqueo-body">
                <section class="form-section">
                    <h3 class="section-heading">Información general</h3>

                    <div class="information-grid">
                        <div class="field field-7">
                            <label class="field-label">Nombre del negocio</label>
                            <input type="text" class="document-value" value="{{ $agente->nombre_negocio }}" readonly>
                        </div>

                        <div class="field field-5">
                            <label class="field-label">Dirección</label>
                            <input type="text" class="document-value" value="{{ $agente->direccion }}" readonly>
                        </div>

                        <div class="field field-7">
                            <label
                                for="nombre_propietario"
                                class="field-label"
                            >
                                Nombre del propietario o receptor pagador
                            </label>

                            <input
                                type="text"
                                name="nombre_propietario"
                                id="nombre_propietario"
                                class="document-value document-value-editable"
                                value="{{ old('nombre_propietario', $agente->propietario) }}"
                                maxlength="150"
                                required
                            >
                        </div>

                        <div class="field field-5">
                            <label class="field-label">Ruta</label>
                            <input type="text" class="document-value" value="{{ $agente->ruta->nombre }}" readonly>
                        </div>

                        <div class="field field-3">
                            <label class="field-label">Agente No.</label>
                            <input type="text" class="document-value" value="{{ $agente->codigo_agente }}" readonly>
                        </div>

                        <div class="field field-3">
                            <label class="field-label">Fecha</label>
                            <input type="text" class="document-value" value="{{ ($fechaArqueo ?? now())->format('d/m/Y') }}" readonly>
                        </div>

                        <div class="field field-3">
                            <label class="field-label">Hora de inicio</label>
                            <input type="text" class="document-value" value="{{ $horaInicio->format('H:i') }}" readonly>
                        </div>

                        <div class="field field-3">
                            <label class="field-label">Región</label>
                            <input type="text" class="document-value" value="{{ $agente->ruta->region->nombre }}" readonly>
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3 class="section-heading">Conteo de efectivo</h3>

                    <div class="count-layout">
                        <div class="denominations-grid">
                            <div class="count-card">
                                <div class="card-heading">Billetes</div>
                                <div class="table-scroll">
                                    <table class="count-table">
                                        <thead>
                                            <tr>
                                                <th>Denominación</th>
                                                <th>Cantidad</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($billetes as $valor)
                                                <tr>
                                                    <td class="denomination-label">Q {{ number_format($valor, 2) }}</td>
                                                    <td>
                                                        <input
                                                            type="number"
                                                            min="0"
                                                            step="1"
                                                            value="0"
                                                            inputmode="numeric"
                                                            class="quantity-input cantidad"
                                                            data-tipo="BILLETE"
                                                            data-valor="{{ $valor }}"
                                                        >
                                                    </td>
                                                    <td><span class="subtotal">Q 0.00</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="count-card">
                                <div class="card-heading">Monedas</div>
                                <div class="table-scroll">
                                    <table class="count-table">
                                        <thead>
                                            <tr>
                                                <th>Denominación</th>
                                                <th>Cantidad</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($monedas as $valor)
                                                <tr>
                                                    <td class="denomination-label">Q {{ number_format($valor, 2) }}</td>
                                                    <td>
                                                        <input
                                                            type="number"
                                                            min="0"
                                                            step="1"
                                                            value="0"
                                                            inputmode="numeric"
                                                            class="quantity-input cantidad"
                                                            data-tipo="MONEDA"
                                                            data-valor="{{ $valor }}"
                                                        >
                                                    </td>
                                                    <td><span class="subtotal">Q 0.00</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <aside class="totals-card">
                            <div class="card-heading">Totales</div>
                            <div class="totals-content">
                                <div class="total-line">
                                    <label for="totalBilletes">Total billetes</label>
                                    <input id="totalBilletes" type="text" class="money-field" value="Q 0.00" readonly>
                                </div>

                                <div class="total-line">
                                    <label for="totalMonedas">Total monedas</label>
                                    <input id="totalMonedas" type="text" class="money-field" value="Q 0.00" readonly>
                                </div>

                                <div class="total-line total-arqueado-line">
                                    <label for="totalArqueado">Total arqueado</label>
                                    <input id="totalArqueado" type="text" class="money-field" value="Q 0.00" readonly>
                                </div>

                                <div class="total-line">
                                    <label for="saldoSistema">Saldo del sistema</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        name="saldo_sistema"
                                        id="saldoSistema"
                                        class="money-field money-field-editable"
                                        value="{{ old('saldo_sistema', '0.00') }}"
                                        required
                                    >
                                </div>

                                <div id="differenceCard" class="difference-card">
                                    <span>Diferencia</span>
                                    <strong id="diferencia">Q 0.00</strong>
                                    <small id="differenceStatus">Cuadrado</small>
                                </div>
                            </div>
                        </aside>
                    </div>
                </section>

                <section class="form-section">
                    <h3 class="section-heading">
                        Certificación
                    </h3>

                    <textarea
                        name="certificacion"
                        id="certificacion"
                        rows="5"
                        maxlength="2000"
                        class="certification-textarea"
                        required
                        placeholder="Escriba la certificación del arqueo..."
                    >{{ old('certificacion', 'Con el presente arqueo de caja se deja constancia de que los valores registrados corresponden al efectivo contado y verificado al momento de realizar el arqueo.') }}</textarea>

                </section>

                <section class="form-section">
                    <h3 class="section-heading">Observaciones</h3>
                    <textarea
                        name="observaciones"
                        id="observaciones"
                        rows="4"
                        maxlength="1000"
                        class="observations-input"
                        placeholder="Ingrese observaciones relacionadas con el arqueo, sobrantes o faltantes."
                    >{{ old('observaciones') }}</textarea>
                </section>

                <section class="form-section signature-preview">
                    <h3 class="section-heading">
                        Firmas electrónicas
                    </h3>

                    <div class="signature-grid">
                        <div class="signature-block">
                            <div class="signature-placeholder">
                                Se generará al finalizar el arqueo
                            </div>

                            <div class="signature-line"></div>

                            <div class="signature-label">
                                Elaborado Por:<br>
                                Propietario o Receptor Pagador
                            </div>
                        </div>

                        <div class="signature-block">
                            <div class="signature-placeholder">
                                Pendiente de certificación
                            </div>

                            <div class="signature-line"></div>

                            <div class="signature-label">
                                Revisado Por:<br>
                                Promotor Agentes MICOOPE
                            </div>
                        </div>
                    </div>
                </section>

                <input type="hidden" name="detalle" id="detalleInput">

                    @if (($esExtemporaneo ?? false) && $habilitacion)
                        <input
                            type="hidden"
                            name="habilitacion_id"
                            value="{{ $habilitacion->id }}"
                        >
                    @endif

                    <div class="form-actions">
                        <a
                            href="{{ ($esExtemporaneo ?? false)
                                ? route('agente.arqueos-extemporaneos.index')
                                : route('agente.arqueos.index') }}"
                            class="form-button cancel-button"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            id="submitButton"
                            class="form-button submit-button"
                        >
                            Finalizar Arqueo
                        </button>
                    </div>
            </div>
        </article>
    </form>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formArqueo');
    const saldoSistemaInput = document.getElementById('saldoSistema');
    const totalBilletesInput = document.getElementById('totalBilletes');
    const totalMonedasInput = document.getElementById('totalMonedas');
    const totalArqueadoInput = document.getElementById('totalArqueado');
    const diferenciaElement = document.getElementById('diferencia');
    const differenceCard = document.getElementById('differenceCard');
    const differenceStatus = document.getElementById('differenceStatus');
    const detalleInput = document.getElementById('detalleInput');
    const submitButton = document.getElementById('submitButton');

    function formatearMoneda(valor) {
        return 'Q ' + Number(valor).toLocaleString('es-GT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function recalcular() {
        let totalBilletes = 0;
        let totalMonedas = 0;

        document.querySelectorAll('.cantidad').forEach(function (input) {
            let cantidad = parseInt(input.value, 10);

            if (Number.isNaN(cantidad) || cantidad < 0) {
                cantidad = 0;
                input.value = 0;
            }

            const denominacion = parseFloat(input.dataset.valor);
            const subtotal = cantidad * denominacion;

            input.closest('tr').querySelector('.subtotal').textContent = formatearMoneda(subtotal);

            if (input.dataset.tipo === 'BILLETE') {
                totalBilletes += subtotal;
            } else {
                totalMonedas += subtotal;
            }
        });

        const totalArqueado = totalBilletes + totalMonedas;
        const saldoSistema = parseFloat(saldoSistemaInput.value) || 0;
        const diferencia = totalArqueado - saldoSistema;

        totalBilletesInput.value = formatearMoneda(totalBilletes);
        totalMonedasInput.value = formatearMoneda(totalMonedas);
        totalArqueadoInput.value = formatearMoneda(totalArqueado);
        diferenciaElement.textContent = formatearMoneda(diferencia);

        differenceCard.classList.remove('sobrante', 'faltante');

        if (diferencia > 0.009) {
            differenceCard.classList.add('sobrante');
            differenceStatus.textContent = 'Sobrante';
        } else if (diferencia < -0.009) {
            differenceCard.classList.add('faltante');
            differenceStatus.textContent = 'Faltante';
        } else {
            differenceStatus.textContent = 'Cuadrado';
        }
    }

    document.querySelectorAll('.cantidad').forEach(function (input) {
        input.addEventListener('input', recalcular);
    });

    saldoSistemaInput.addEventListener('input', recalcular);

    form.addEventListener('submit', function (event) {
        if (! form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        const detalle = [];

        document.querySelectorAll('.cantidad').forEach(function (input) {
            detalle.push({
                tipo: input.dataset.tipo,
                denominacion: parseFloat(input.dataset.valor),
                cantidad: parseInt(input.value, 10) || 0
            });
        });

        detalleInput.value = JSON.stringify(detalle);
        submitButton.disabled = true;
        submitButton.textContent = 'Registrando...';
    });

    recalcular();
});
</script>
@endpush
