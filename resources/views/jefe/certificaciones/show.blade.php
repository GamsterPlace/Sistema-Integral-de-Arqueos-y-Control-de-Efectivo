@extends('layouts.jefe')

@section('title', 'Revisar Arqueo')
@section('module-title', 'Certificar Arqueos')

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

    .toolbar-actions {
        display: flex;
        align-items: center;
        gap: 9px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .alert-message {
        margin-bottom: 18px;
        padding: 13px 15px;
        border-radius: 11px;
        font-size: 12px;
        line-height: 1.5;
    }

    .alert-message.warning {
        border: 1px solid #f0d9a4;
        background: #fff9e9;
        color: #8a6511;
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
        background:
            url("{{ asset('images/logos/agentes-micoope.png') }}")
            center / contain no-repeat;
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
        background:
            url("{{ asset('images/logos/ecosaba.png') }}")
            left center / contain no-repeat;
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

    .type-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 23px;
        padding: 13px 16px;
        border: 1px solid #dce4e9;
        border-radius: 11px;
        background: #f8fafb;
    }

    .type-strip-label {
        color: #0a3158;
        font-size: 11px;
        font-weight: 800;
    }

    .type-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border: 1px solid #c9dcf4;
        border-radius: 999px;
        background: #eef5ff;
        color: #285b9b;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .count-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1.35fr)
            minmax(300px, .65fr);
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
        color: #333;
        font-size: 9px;
        font-weight: 800;
        text-align: left;
    }

    .count-table td {
        padding: 7px 8px;
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

    .empty-count {
        padding: 14px 8px !important;
        color: #7c8993 !important;
        font-style: italic;
        text-align: center !important;
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

    .difference-card small {
        display: block;
        margin-top: 4px;
        color: #174f80;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .difference-card.sobrante {
        border-color: #75b993;
        background: #edf9f2;
    }

    .difference-card.sobrante strong,
    .difference-card.sobrante small {
        color: #197247;
    }

    .difference-card.faltante {
        border-color: #dda19d;
        background: #fff1f0;
    }

    .difference-card.faltante strong,
    .difference-card.faltante small {
        color: #ae342f;
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

    .signature-preview {
        margin-top: 2px;
        padding-top: 6px;
    }

    .signature-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 30px;
        margin-top: 4px;
    }

    .signature-block {
        min-width: 0;
        text-align: center;
    }

    .signature-placeholder {
        min-height: 58px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 0 8px 6px;
        color: #263846;
        font-size: 10px;
        line-height: 1.45;
    }

    .signature-placeholder strong {
        color: #111;
        font-size: 11px;
    }

    .signature-placeholder.pending {
        color: #7c8993;
        font-style: italic;
    }

    .signature-line {
        border-bottom: 1px solid #333;
    }

    .signature-label {
        margin-top: 6px;
        color: #111;
        font-size: 9px;
        font-weight: 800;
        line-height: 1.4;
    }

    .status-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 15px 17px;
        border: 1px solid #dce4e9;
        border-radius: 11px;
        background: #f8fafb;
    }

    .status-box strong {
        color: #0a3158;
        font-size: 12px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 13px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .4px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-badge.pendiente {
        border: 1px solid #efdca9;
        background: #fff9e9;
        color: #876411;
    }

    .status-badge.certificado {
        border: 1px solid #c7e4d2;
        background: #effaf3;
        color: #197247;
    }

    .status-badge.anulado {
        border: 1px solid #edcaca;
        background: #fff1f0;
        color: #ae342f;
    }

    .actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid #dfe5e9;
    }

    .actions-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
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
        cursor: pointer;
    }

    .action-button svg {
        width: 16px;
        height: 16px;
    }

    .back-button {
        border: 1px solid #ccd7de;
        background: #fff;
        color: #4f6575;
    }

    .print-button {
        border: 0;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #fff;
        box-shadow: 0 8px 18px rgba(22, 76, 150, .20);
    }

    .certify-button {
        border: 0;
        background: linear-gradient(135deg, #197247, #23955d);
        color: #fff;
        box-shadow: 0 8px 18px rgba(25, 114, 71, .20);
    }

    .certify-button:hover {
        transform: translateY(-1px);
    }

    .certify-button:disabled {
        background: #dce4e8;
        color: #87949d;
        box-shadow: none;
        cursor: not-allowed;
        transform: none;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 2500;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(10, 24, 37, .62);
        backdrop-filter: blur(3px);
    }

    .modal-backdrop.is-open {
        display: flex;
    }

    .modal-dialog {
        width: min(500px, 100%);
        overflow: hidden;
        border: 1px solid #dce5eb;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(0, 0, 0, .25);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 20px 22px;
        border-bottom: 1px solid #e7edf1;
    }

    .modal-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 17px;
    }

    .modal-header p {
        margin: 5px 0 0;
        color: #6c7f90;
        font-size: 11px;
        line-height: 1.5;
    }

    .modal-close {
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        border: 0;
        border-radius: 9px;
        background: #eef3f7;
        color: #415d72;
        font-size: 21px;
        cursor: pointer;
    }

    .modal-body {
        padding: 20px 22px;
    }

    .modal-info {
        padding: 12px 14px;
        margin-bottom: 16px;
        border: 1px solid #dce6ed;
        border-radius: 11px;
        background: #f8fafb;
    }

    .modal-info span {
        display: block;
        color: #7a8b98;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .modal-info strong {
        display: block;
        margin-top: 4px;
        color: #173b59;
    }

    .modal-body label {
        display: block;
        margin-bottom: 7px;
        color: #52697b;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .modal-input {
        width: 100%;
        min-height: 43px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        box-sizing: border-box;
        outline: none;
    }

    .modal-input:focus {
        border-color: #164c96;
        box-shadow: 0 0 0 3px rgba(22, 76, 150, .10);
    }

    .modal-help {
        margin: 12px 0 0;
        color: #84939e;
        font-size: 10px;
        line-height: 1.5;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 22px 20px;
        border-top: 1px solid #e7edf1;
        background: #fbfcfd;
    }

    .modal-btn {
        min-height: 40px;
        padding: 0 15px;
        border: 1px solid #d5dfe5;
        border-radius: 10px;
        background: #fff;
        color: #3d596f;
        font-weight: 800;
        cursor: pointer;
    }

    .modal-btn.confirm {
        border-color: #197247;
        background: #197247;
        color: #fff;
    }

    @media (max-width: 1180px) {
        .detail-header {
            grid-template-columns: 165px minmax(300px, 1fr) 235px;
        }

        .detail-title h2 {
            font-size: 24px;
        }

        .detail-reference strong {
            font-size: 12.5px;
        }
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

        .detail-reference strong {
            font-size: 11.5px;
        }
    }

    @media (max-width: 850px) {
        .detail-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .toolbar-actions {
            width: 100%;
            justify-content: flex-start;
        }

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
        .field-7 {
            grid-column: span 2;
        }

        .signature-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
    }

    @media (max-width: 620px) {
        .detail-wrapper {
            width: 100%;
        }

        .detail-body,
        .detail-header {
            padding-left: 16px;
            padding-right: 16px;
        }

        .information-grid {
            grid-template-columns: 1fr;
        }

        .field-3,
        .field-5,
        .field-7 {
            grid-column: span 1;
        }

        .type-strip,
        .status-box {
            align-items: flex-start;
            flex-direction: column;
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
            align-items: stretch;
            flex-direction: column-reverse;
        }

        .actions-right {
            width: 100%;
            flex-direction: column;
        }

        .action-button {
            width: 100%;
        }

        .modal-footer {
            flex-direction: column-reverse;
        }

        .modal-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $fecha = \Carbon\Carbon::parse(
        $arqueo->fecha_arqueo
    )->format('d/m/Y');

    $horaInicio = $arqueo->hora_inicio
        ? \Carbon\Carbon::parse($arqueo->hora_inicio)->format('H:i')
        : '—';

    $horaFin = $arqueo->hora_fin
        ? \Carbon\Carbon::parse($arqueo->hora_fin)->format('H:i')
        : '—';

    $diferencia = (float) $arqueo->diferencia;

    $diferenciaClase = $diferencia > 0.009
        ? 'sobrante'
        : ($diferencia < -0.009 ? 'faltante' : '');

    $diferenciaTexto = $diferencia > 0.009
        ? 'Sobrante'
        : ($diferencia < -0.009 ? 'Faltante' : 'Cuadrado');

    $estadoClase = match ($arqueo->estado) {
        'PENDIENTE_CERTIFICACION' => 'pendiente',
        'CERTIFICADO' => 'certificado',
        'ANULADO' => 'anulado',
        default => 'pendiente',
    };

    $estadoTexto = match ($arqueo->estado) {
        'PENDIENTE_CERTIFICACION' => 'Pendiente de certificación',
        'CERTIFICADO' => 'Certificado',
        'ANULADO' => 'Anulado',
        default => str_replace('_', ' ', $arqueo->estado),
    };

        $tipoArqueoTexto = match ($arqueo->tipo) {
        'VISITA_AUDITORIA' => 'Visita de Auditoría',
        'VISITA_PROMOTOR' => 'Visita de Promotor',
        default => str_replace('_', ' ', $arqueo->tipo),
    };

    $realizadorTexto = match ($arqueo->tipo) {
        'VISITA_AUDITORIA' => 'Auditoría MICOOPE',
        'VISITA_PROMOTOR' => 'Promotor de Agentes MICOOPE',
        default => 'Realizador',
    };

    $rutaImprimir = $arqueo->tipo === 'VISITA_AUDITORIA'
        ? route('jefe.arqueos.imprimir', $arqueo->id)
        : route('jefe.arqueos-promotores.imprimir', $arqueo->id);


    $nombreFirma = function ($firma): string {
        if (! $firma) {
            return 'Pendiente';
        }

        $nombre = trim(
            ($firma->nombres_historicos ?? '')
            . ' '
            . ($firma->apellidos_historicos ?? '')
        );

        return $nombre !== ''
            ? $nombre
            : 'Firma electrónica registrada';
    };

    $fechaFirma = function ($firma): string {
        if (! $firma || ! $firma->fecha_firma) {
            return '—';
        }

        return \Carbon\Carbon::parse(
            $firma->fecha_firma
        )->format('d/m/Y H:i');
    };
@endphp

<div class="detail-wrapper">
    <div class="detail-toolbar">
        <div class="detail-toolbar-copy">
            <h2>Revisión para Certificación</h2>
            <p>
                Verifique cuidadosamente la información y las firmas
                electrónicas existentes antes de certificar el arqueo.
            </p>
        </div>
        <div class="toolbar-actions">
            <span class="status-badge {{ $estadoClase }}">
                {{ $estadoTexto }}
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-message warning">
            {{ $errors->first() }}
        </div>
    @endif

    <article class="detail-card">
        <header class="detail-header">
            <div class="detail-brand">
                ECOSABA
            </div>
            <div class="detail-title">
                <h2>Arqueo y Corte de Caja</h2>
                <p>Agente MICOOPE</p>
            </div>
            <div class="detail-reference">
                Número de arqueo
                <strong>
                    {{ $arqueo->numero_arqueo }}
                </strong>
            </div>
        </header>

        <div class="detail-body">
            <div class="type-strip">
                <span class="type-strip-label">
                    Tipo de arqueo
                </span>
                <span class="type-badge">
                    {{ $tipoArqueoTexto }}
                </span>
            </div>

            <section class="section">
                <div class="information-grid">
                    <div class="field field-7">
                        <span class="field-label">
                            Nombre del negocio
                        </span>
                        <span class="field-value">
                            {{ $arqueo->nombre_negocio_historico ?? '—' }}
                        </span>
                    </div>
                    <div class="field field-5">
                        <span class="field-label">
                            Dirección
                        </span>
                        <span class="field-value">
                            {{ $arqueo->direccion_historica ?? '—' }}
                        </span>
                    </div>
                    <div class="field field-7">
                        <span class="field-label">
                            Nombre del propietario o receptor pagador
                        </span>
                        <span class="field-value">
                            {{ $arqueo->nombre_propietario_historico ?? '—' }}
                        </span>
                    </div>
                    <div class="field field-5">
                        <span class="field-label">
                            Ruta
                        </span>
                        <span class="field-value">
                            {{ $arqueo->ruta_historica ?? '—' }}
                        </span>
                    </div>
                    <div class="field field-3">
                        <span class="field-label">
                            Agente No.
                        </span>
                        <span class="field-value">
                            {{ $arqueo->codigo_agente_historico ?? '—' }}
                        </span>
                    </div>
                    <div class="field field-3">
                        <span class="field-label">
                            Fecha
                        </span>
                        <span class="field-value">
                            {{ $fecha }}
                        </span>
                    </div>
                    <div class="field field-3">
                        <span class="field-label">
                            Hora de inicio
                        </span>
                        <span class="field-value">
                            {{ $horaInicio }}
                        </span>
                    </div>
                    <div class="field field-3">
                        <span class="field-label">
                            Hora de finalización
                        </span>
                        <span class="field-value">
                            {{ $horaFin }}
                        </span>
                    </div>
                    <div class="field field-3">
                        <span class="field-label">
                            Región
                        </span>
                        <span class="field-value">
                            {{ $arqueo->region_historica ?? '—' }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="section">
                <h3 class="section-title">
                    Conteo de efectivo
                </h3>
                <div class="count-layout">
                    <div class="denominations-grid">
                        <div class="count-card">
                            <div class="card-heading">
                                Billetes
                            </div>
                            <table class="count-table">
                                <thead>
                                    <tr>
                                        <th>Denominación</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($billetes as $detalle)
                                        <tr>
                                            <td class="denomination-label">
                                                Q {{ number_format(
                                                    (float) $detalle->denominacion,
                                                    2
                                                ) }}
                                            </td>
                                            <td>
                                                <span class="quantity-value">
                                                    {{ $detalle->cantidad }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="subtotal-value">
                                                    Q {{ number_format(
                                                        (float) $detalle->subtotal,
                                                        2
                                                    ) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td
                                                colspan="3"
                                                class="empty-count"
                                            >
                                                No se registraron billetes.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="count-card">
                            <div class="card-heading">
                                Monedas
                            </div>
                            <table class="count-table">
                                <thead>
                                    <tr>
                                        <th>Denominación</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($monedas as $detalle)
                                        <tr>
                                            <td class="denomination-label">
                                                Q {{ number_format(
                                                    (float) $detalle->denominacion,
                                                    2
                                                ) }}
                                            </td>
                                            <td>
                                                <span class="quantity-value">
                                                    {{ $detalle->cantidad }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="subtotal-value">
                                                    Q {{ number_format(
                                                        (float) $detalle->subtotal,
                                                        2
                                                    ) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td
                                                colspan="3"
                                                class="empty-count"
                                            >
                                                No se registraron monedas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <aside class="totals-card">
                        <div class="card-heading">
                            Totales
                        </div>
                        <div class="totals-content">
                            <div class="total-line">
                                <span>Total billetes</span>
                                <span class="money-value">
                                    Q {{ number_format(
                                        (float) $arqueo->total_billetes,
                                        2
                                    ) }}
                                </span>
                            </div>
                            <div class="total-line">
                                <span>Total monedas</span>
                                <span class="money-value">
                                    Q {{ number_format(
                                        (float) $arqueo->total_monedas,
                                        2
                                    ) }}
                                </span>
                            </div>
                            <div class="total-line">
                                <span>Total arqueado</span>
                                <span class="money-value total-arqueado">
                                    Q {{ number_format(
                                        (float) $arqueo->total_arqueado,
                                        2
                                    ) }}
                                </span>
                            </div>
                            <div class="total-line">
                                <span>Saldo del sistema</span>
                                <span class="money-value">
                                    Q {{ number_format(
                                        (float) $arqueo->saldo_sistema,
                                        2
                                    ) }}
                                </span>
                            </div>
                            <div
                                class="difference-card {{ $diferenciaClase }}"
                            >
                                <span>Diferencia</span>
                                <strong>
                                    Q {{ number_format($diferencia, 2) }}
                                </strong>
                                <small>
                                    {{ $diferenciaTexto }}
                                </small>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            <section class="section">
                <h3 class="section-title">
                    Certificación
                </h3>
                <div class="text-box">
                    {{ $arqueo->certificacion
                        ?: 'Sin texto de certificación registrado.' }}
                </div>
            </section>

            <section class="section">
                <h3 class="section-title">
                    Observaciones
                </h3>
                <div class="text-box">
                    {{ $arqueo->observaciones
                        ?: 'Sin observaciones registradas.' }}
                </div>
            </section>

            <section class="section signature-preview">
                <h3 class="section-title">
                    Firmas electrónicas
                </h3>
                <div class="signature-grid">
                    <div class="signature-block">
                        @if ($firmaRealizador)
                            <div class="signature-placeholder">
                                <div>
                                    <strong>
                                        {{ $nombreFirma($firmaRealizador) }}
                                    </strong>
                                    <br>
                                    Firmado electrónicamente el
                                    {{ $fechaFirma($firmaRealizador) }}
                                </div>
                            </div>
                        @else
                            <div class="signature-placeholder pending">
                                Firma electrónica no registrada
                            </div>
                        @endif
                        <div class="signature-line"></div>
                        <div class="signature-label">
                            Realizado Por:<br>
                            {{ $realizadorTexto }}
                        </div>
                    </div>

                    <div class="signature-block">
                        @if ($firmaAgente)
                            <div class="signature-placeholder">
                                <div>
                                    <strong>
                                        {{ $nombreFirma($firmaAgente) }}
                                    </strong>
                                    <br>
                                    Firmado electrónicamente el
                                    {{ $fechaFirma($firmaAgente) }}
                                </div>
                            </div>
                        @else
                            <div class="signature-placeholder pending">
                                Firma electrónica no registrada
                            </div>
                        @endif
                        <div class="signature-line"></div>
                        <div class="signature-label">
                            Validado Por:<br>
                            Propietario o Receptor Pagador
                        </div>
                    </div>

                    <div class="signature-block">
                        @if ($firmaJefe)
                            <div class="signature-placeholder">
                                <div>
                                    <strong>
                                        {{ $nombreFirma($firmaJefe) }}
                                    </strong>
                                    <br>
                                    Firmado electrónicamente el
                                    {{ $fechaFirma($firmaJefe) }}
                                </div>
                            </div>
                        @else
                            <div class="signature-placeholder pending">
                                Pendiente de certificación
                            </div>
                        @endif
                        <div class="signature-line"></div>
                        <div class="signature-label">
                            Certificado Por:<br>
                            Jefe de Agentes
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <h3 class="section-title">
                    Estado del arqueo
                </h3>
                <div class="status-box">
                    <strong>
                        Estado actual del registro
                    </strong>
                    <span class="status-badge {{ $estadoClase }}">
                        {{ $estadoTexto }}
                    </span>
                </div>
            </section>

            <div class="actions">
                <a
                    href="{{ route('jefe.certificaciones.index') }}"
                    class="action-button back-button"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M19 12H5"/>
                        <path d="m11 18-6-6 6-6"/>
                    </svg>
                    Regresar
                </a>

                <div class="actions-right">
                    <a
                        href="{{ $rutaImprimir }}"
                        target="_blank"
                        rel="noopener"
                        class="action-button print-button"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M6 9V2h12v7"/>
                            <path
                                d="M6 18H4a2 2 0 0 1-2-2v-5
                                   a2 2 0 0 1 2-2h16
                                   a2 2 0 0 1 2 2v5
                                   a2 2 0 0 1-2 2h-2"
                            />
                            <rect
                                x="6"
                                y="14"
                                width="12"
                                height="8"
                            />
                        </svg>
                        Imprimir PDF
                    </a>

                    @if ($puedeCertificar)
                        <button
                            type="button"
                            class="action-button certify-button"
                            id="open-certification"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="12" cy="12" r="9"/>
                                <path d="m8 12 3 3 5-6"/>
                            </svg>
                            Certificar Arqueo
                        </button>
                    @else
                        <button
                            type="button"
                            class="action-button certify-button"
                            disabled
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="12" cy="12" r="9"/>
                                <path d="m8 12 3 3 5-6"/>
                            </svg>
                            No disponible para certificación
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </article>
</div>

@if ($puedeCertificar)
    <div
        class="modal-backdrop"
        id="certification-modal"
        aria-hidden="true"
    >
        <div
            class="modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="certification-title"
        >
            <form
                method="POST"
                action="{{ route(
                    'jefe.certificaciones.certificar',
                    $arqueo->id
                ) }}"
            >
                @csrf

                <div class="modal-header">
                    <div>
                        <h3 id="certification-title">
                            Certificar arqueo
                        </h3>
                        <p>
                            Confirme su identidad para registrar
                            la certificación electrónica.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="modal-close"
                        id="close-certification"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>

                <div class="modal-body">
                    <div class="modal-info">
                        <span>
                            Número de arqueo
                        </span>
                        <strong>
                            {{ $arqueo->numero_arqueo }}
                        </strong>
                    </div>

                    <label for="certification-password">
                        Contraseña institucional
                    </label>

                    <input
                        type="password"
                        name="password"
                        id="certification-password"
                        class="modal-input"
                        required
                        autocomplete="current-password"
                        placeholder="Ingrese su contraseña"
                    >

                    <p class="modal-help">
                        Al confirmar se registrará su firma electrónica
                        como Jefe de Agentes y el arqueo cambiará
                        a estado CERTIFICADO.
                    </p>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="modal-btn"
                        id="cancel-certification"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="modal-btn confirm"
                    >
                        Confirmar certificación
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@endsection

@if ($puedeCertificar)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById(
                'certification-modal'
            );

            const openButton = document.getElementById(
                'open-certification'
            );

            const closeButton = document.getElementById(
                'close-certification'
            );

            const cancelButton = document.getElementById(
                'cancel-certification'
            );

            const password = document.getElementById(
                'certification-password'
            );

            if (
                !modal ||
                !openButton ||
                !closeButton ||
                !cancelButton ||
                !password
            ) {
                return;
            }

            function openModal() {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';

                setTimeout(function () {
                    password.focus();
                }, 80);
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                password.value = '';
            }

            openButton.addEventListener(
                'click',
                openModal
            );

            closeButton.addEventListener(
                'click',
                closeModal
            );

            cancelButton.addEventListener(
                'click',
                closeModal
            );

            modal.addEventListener(
                'click',
                function (event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                }
            );

            document.addEventListener(
                'keydown',
                function (event) {
                    if (
                        event.key === 'Escape' &&
                        modal.classList.contains('is-open')
                    ) {
                        closeModal();
                    }
                }
            );
        });
    </script>
    @endpush
@endif
