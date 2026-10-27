@extends('layouts.promotor')

@section('title', 'Detalle del Arqueo')
@section('module-title', 'Arqueos de Agentes')

@section('content')
<style>
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }

    .page-title h2 {
        margin: 0;
        color: #123d69;
        font-size: 28px;
        font-weight: 800;
    }

    .page-title p {
        margin: 7px 0 0;
        color: #6b7c8d;
    }

    .header-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border: 0;
        border-radius: 10px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-secondary {
        background: #edf2f6;
        color: #3d586e;
    }

    .btn-print {
        background: #e9f2fb;
        color: #174f8a;
    }

    .btn-certify {
        background: #1f7a4d;
        color: #ffffff;
    }

    .btn-danger {
        background: #b33a34;
        color: #ffffff;
    }

    .btn-danger:hover {
        background: #982f2a;
    }

    .btn-disabled {
        cursor: not-allowed;
        background: #e6eaed;
        color: #8a969f;
    }

    .alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 12px;
        font-weight: 700;
    }

    .alert-success {
        border: 1px solid #a9d8bc;
        background: #ebf8f0;
        color: #216b45;
    }

    .alert-warning {
        border: 1px solid #ead29a;
        background: #fff8e5;
        color: #805d05;
    }

    .alert-error {
        border: 1px solid #e3aaa5;
        background: #fff0ef;
        color: #9a302a;
    }

    .content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(280px, .75fr);
        gap: 20px;
    }

    .card {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
    }

    .card + .card {
        margin-top: 20px;
    }

    .card-header {
        padding: 16px 18px;
        border-bottom: 1px solid #e7edf1;
        background: #f8fafb;
    }

    .card-header h3 {
        margin: 0;
        color: #173f66;
        font-size: 16px;
        font-weight: 800;
    }

    .card-body {
        padding: 18px;
    }

    .information-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .information-item {
        min-width: 0;
    }

    .information-item.full {
        grid-column: 1 / -1;
    }

    .information-label {
        display: block;
        margin-bottom: 5px;
        color: #748596;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .information-value {
        display: block;
        overflow-wrap: anywhere;
        color: #173f66;
        font-weight: 800;
    }

    .money-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .money-panel {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 12px;
    }

    .money-panel-title {
        padding: 11px 13px;
        border-bottom: 1px solid #dfe7ed;
        background: #eef4f8;
        color: #173f66;
        font-weight: 800;
    }

    .money-table {
        width: 100%;
        border-collapse: collapse;
    }

    .money-table th,
    .money-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #edf1f4;
        text-align: right;
    }

    .money-table th {
        color: #6d7f90;
        background: #fafcfd;
        font-size: 11px;
        text-transform: uppercase;
    }

    .money-table th:first-child,
    .money-table td:first-child {
        text-align: left;
    }

    .money-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .amount {
        color: #173f66;
        font-weight: 800;
        white-space: nowrap;
    }

    .text-box {
        min-height: 72px;
        padding: 14px;
        border: 1px solid #dfe7ed;
        border-radius: 11px;
        background: #fbfcfd;
        color: #334d61;
        line-height: 1.6;
        white-space: pre-line;
    }

    .summary-list {
        display: grid;
        gap: 0;
    }

    .summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 13px 0;
        border-bottom: 1px solid #edf1f4;
    }

    .summary-row:last-child {
        border-bottom: 0;
    }

    .summary-row span {
        color: #65788a;
        font-weight: 700;
    }

    .summary-row strong {
        color: #173f66;
        text-align: right;
        white-space: nowrap;
    }

    .summary-row.highlight {
        margin-top: 4px;
        padding: 14px 12px;
        border: 1px solid #c9dceb;
        border-radius: 10px;
        background: #eff6fb;
    }

    .difference-positive strong {
        color: #247048;
    }

    .difference-negative strong {
        color: #ac3731;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        min-height: 27px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge-warning {
        background: #fff3d5;
        color: #8a6100;
    }

    .badge-success {
        background: #e8f7ee;
        color: #247048;
    }

    .badge-danger {
        background: #ffe8e6;
        color: #a93b35;
    }

    .badge-neutral {
        background: #edf2f6;
        color: #53697c;
    }

    .signature-card {
        padding: 14px;
        border: 1px solid #dfe7ed;
        border-radius: 12px;
        background: #fbfcfd;
    }

    .signature-card + .signature-card {
        margin-top: 12px;
    }

    .signature-role {
        color: #6e8091;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .signature-name {
        margin-top: 7px;
        color: #173f66;
        font-weight: 800;
    }

    .signature-date {
        margin-top: 4px;
        color: #708292;
        font-size: 12px;
    }

    .signature-code {
        margin-top: 7px;
        overflow-wrap: anywhere;
        color: #7a8995;
        font-family: monospace;
        font-size: 11px;
    }

    .signature-pending {
        margin-top: 8px;
        color: #926b09;
        font-weight: 800;
    }

    .certification-box {
        margin-top: 14px;
        padding: 14px;
        border: 1px solid #c9dceb;
        border-radius: 12px;
        background: #eff6fb;
    }

    .certification-box p {
        margin: 0 0 12px;
        color: #466176;
        line-height: 1.5;
    }

    .certification-box form {
        margin: 0;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(10, 24, 37, .65);
        backdrop-filter: blur(3px);
    }

    .modal-backdrop.is-open {
        display: flex;
    }

    .modal-dialog {
        width: min(580px, 100%);
        overflow: hidden;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
    }

    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px 16px;
        border-bottom: 1px solid #e7edf1;
    }

    .modal-header h3 {
        margin: 0;
        color: #8f2f2a;
        font-size: 20px;
        font-weight: 800;
    }

    .modal-header p {
        margin: 6px 0 0;
        color: #6c7f90;
        line-height: 1.45;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 10px;
        background: #eef3f7;
        color: #415d72;
        font-size: 22px;
        cursor: pointer;
    }

    .modal-body {
        padding: 20px 22px;
    }

    .modal-warning {
        margin-bottom: 16px;
        padding: 14px;
        border: 1px solid #e3aaa5;
        border-radius: 11px;
        background: #fff0ef;
        color: #8f2f2a;
        line-height: 1.5;
    }

    .modal-form-group + .modal-form-group {
        margin-top: 15px;
    }

    .modal-form-group label {
        display: block;
        margin-bottom: 7px;
        color: #52697b;
        font-size: 12px;
        font-weight: 800;
    }

    .modal-form-control {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #ffffff;
        color: #2f4659;
        outline: none;
    }

    textarea.modal-form-control {
        min-height: 110px;
        resize: vertical;
    }

    .modal-form-control:focus {
        border-color: #b33a34;
        box-shadow: 0 0 0 3px rgba(179, 58, 52, .12);
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 22px 20px;
        border-top: 1px solid #e7edf1;
        background: #fbfcfd;
    }

    .certify-modal-dialog {
        width: min(500px, 100%);
    }

    .certify-modal-header h3 {
        color: #1f7a4d;
    }

    .certify-modal-body {
        padding: 24px 22px;
        text-align: center;
    }

    .certify-icon {
        display: grid;
        place-items: center;
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 18px;
        background: #e8f7ee;
        color: #1f7a4d;
    }

    .certify-icon svg {
        width: 30px;
        height: 30px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .certify-modal-body h4 {
        margin: 0;
        color: #173f66;
        font-size: 17px;
        font-weight: 800;
    }

    .certify-modal-body p {
        max-width: 390px;
        margin: 9px auto 0;
        color: #6c7f90;
        font-size: 11px;
        line-height: 1.6;
    }

    .certify-summary {
        margin-top: 17px;
        padding: 13px 14px;
        border: 1px solid #d8e7df;
        border-radius: 11px;
        background: #f6fbf8;
        text-align: left;
    }

    .certify-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        padding: 6px 0;
        color: #536b5f;
        font-size: 10px;
    }

    .certify-summary-row strong {
        color: #194d34;
        text-align: right;
    }

    body.modal-open {
        overflow: hidden;
    }

    @media (max-width: 1050px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .page-header {
            flex-direction: column;
        }

        .header-actions {
            justify-content: flex-start;
        }

        .information-grid,
        .money-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div class="page-title">
        <h2>Detalle del Arqueo</h2>

        <p>
            Revise la información, el conteo y las firmas electrónicas antes
            de certificar.
        </p>
    </div>

    <div class="header-actions">
        <a
            href="{{ route(
                'promotor.arqueos-agentes.arqueos',
                $arqueo->agente_id
            ) }}"
            class="btn btn-secondary"
        >
            Regresar
        </a>

        <a
            href="{{ route(
                'promotor.arqueos-agentes.imprimir',
                $arqueo
            ) }}"
            target="_blank"
            class="btn btn-print"
        >
            Imprimir PDF
        </a>

        @if ($puedeAnular)
            <button
                type="button"
                class="btn btn-danger"
                id="open-annulment-modal"
            >
                Anular arqueo
            </button>
        @endif
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning">
        {{ session('warning') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-error">
        {{ $errors->first() }}
    </div>
@endif

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-header">
                <h3>Información general</h3>
            </div>

            <div class="card-body">
                <div class="information-grid">
                    <div class="information-item">
                        <span class="information-label">
                            Número de arqueo
                        </span>

                        <span class="information-value">
                            {{ $arqueo->numero_arqueo }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Fecha
                        </span>

                        <span class="information-value">
                            {{ $arqueo->fecha_arqueo->format('d/m/Y') }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Estado
                        </span>

                        @php
                            $estadoClase = match ($arqueo->estado) {
                                'PENDIENTE_CERTIFICACION' => 'badge-warning',
                                'CERTIFICADO' => 'badge-success',
                                'ANULADO' => 'badge-danger',
                                default => 'badge-neutral',
                            };
                        @endphp

                        <span class="badge {{ $estadoClase }}">
                            {{ str_replace('_', ' ', $arqueo->estado) }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Código del agente
                        </span>

                        <span class="information-value">
                            {{ $arqueo->codigo_agente_historico }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Nombre del negocio
                        </span>

                        <span class="information-value">
                            {{ $arqueo->nombre_negocio_historico }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Propietario
                        </span>

                        <span class="information-value">
                            {{ $arqueo->nombre_propietario_historico }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Hora de inicio
                        </span>

                        <span class="information-value">
                            {{ $arqueo->hora_inicio
                                ? \Carbon\Carbon::parse(
                                    $arqueo->hora_inicio
                                )->format('H:i')
                                : 'No registrada' }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Hora de finalización
                        </span>

                        <span class="information-value">
                            {{ $arqueo->hora_fin
                                ? \Carbon\Carbon::parse(
                                    $arqueo->hora_fin
                                )->format('H:i')
                                : 'No registrada' }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Ruta
                        </span>

                        <span class="information-value">
                            {{ $arqueo->ruta_historica }}
                        </span>
                    </div>

                    <div class="information-item">
                        <span class="information-label">
                            Región
                        </span>

                        <span class="information-value">
                            {{ $arqueo->region_historica }}
                        </span>
                    </div>

                    <div class="information-item full">
                        <span class="information-label">
                            Dirección
                        </span>

                        <span class="information-value">
                            {{ $arqueo->direccion_historica }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Conteo de efectivo</h3>
            </div>

            <div class="card-body">
                <div class="money-grid">
                    <div class="money-panel">
                        <div class="money-panel-title">
                            Billetes
                        </div>

                        <table class="money-table">
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
                                        <td>
                                            Q {{ number_format(
                                                (float) $detalle->denominacion,
                                                2
                                            ) }}
                                        </td>

                                        <td>
                                            {{ $detalle->cantidad }}
                                        </td>

                                        <td class="amount">
                                            Q {{ number_format(
                                                (float) $detalle->subtotal,
                                                2
                                            ) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">
                                            No hay billetes registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="money-panel">
                        <div class="money-panel-title">
                            Monedas
                        </div>

                        <table class="money-table">
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
                                        <td>
                                            Q {{ number_format(
                                                (float) $detalle->denominacion,
                                                2
                                            ) }}
                                        </td>

                                        <td>
                                            {{ $detalle->cantidad }}
                                        </td>

                                        <td class="amount">
                                            Q {{ number_format(
                                                (float) $detalle->subtotal,
                                                2
                                            ) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">
                                            No hay monedas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Certificación y observaciones</h3>
            </div>

            <div class="card-body">
                <span class="information-label">
                    Certificación
                </span>

                <div class="text-box">
                    {{ $arqueo->certificacion
                        ?: 'Sin certificación registrada.' }}
                </div>

                <div style="height: 16px;"></div>

                <span class="information-label">
                    Observaciones
                </span>

                <div class="text-box">
                    {{ $arqueo->observaciones
                        ?: 'Sin observaciones registradas.' }}
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3>Resumen financiero</h3>
            </div>

            <div class="card-body">
                <div class="summary-list">
                    <div class="summary-row">
                        <span>Total de billetes</span>

                        <strong>
                            Q {{ number_format(
                                (float) $arqueo->total_billetes,
                                2
                            ) }}
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Total de monedas</span>

                        <strong>
                            Q {{ number_format(
                                (float) $arqueo->total_monedas,
                                2
                            ) }}
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Total arqueado</span>

                        <strong>
                            Q {{ number_format(
                                (float) $arqueo->total_arqueado,
                                2
                            ) }}
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Saldo del sistema</span>

                        <strong>
                            Q {{ number_format(
                                (float) $arqueo->saldo_sistema,
                                2
                            ) }}
                        </strong>
                    </div>

                    <div class="summary-row highlight
                        {{ (float) $arqueo->diferencia > 0
                            ? 'difference-positive'
                            : ((float) $arqueo->diferencia < 0
                                ? 'difference-negative'
                                : '') }}"
                    >
                        <span>Diferencia</span>

                        <strong>
                            Q {{ number_format(
                                (float) $arqueo->diferencia,
                                2
                            ) }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Firmas electrónicas</h3>
            </div>

            <div class="card-body">
                <div class="signature-card">
                    <div class="signature-role">
                        Firma del Agente
                    </div>

                    @if ($firmaAgente)
                        <div class="signature-name">
                            {{ trim(
                                $firmaAgente->nombres_historicos
                                . ' '
                                . $firmaAgente->apellidos_historicos
                            ) }}
                        </div>

                        <div class="signature-date">
                            Firmado el
                            {{ $firmaAgente->fecha_firma->format(
                                'd/m/Y H:i'
                            ) }}
                        </div>

                        <div class="signature-code">
                            {{ $firmaAgente->firma_electronica }}
                        </div>
                    @else
                        <div class="signature-pending">
                            Pendiente de firma del Agente.
                        </div>
                    @endif
                </div>

                <div class="signature-card">
                    <div class="signature-role">
                        Firma del Promotor
                    </div>

                    @if ($firmaPromotor)
                        <div class="signature-name">
                            {{ trim(
                                $firmaPromotor->nombres_historicos
                                . ' '
                                . $firmaPromotor->apellidos_historicos
                            ) }}
                        </div>

                        <div class="signature-date">
                            Firmado el
                            {{ $firmaPromotor->fecha_firma->format(
                                'd/m/Y H:i'
                            ) }}
                        </div>

                        <div class="signature-code">
                            {{ $firmaPromotor->firma_electronica }}
                        </div>
                    @else
                        <div class="signature-pending">
                            Pendiente de certificación del Promotor.
                        </div>
                    @endif
                </div>

                @if ($puedeCertificar)
                    <div class="certification-box">
                        <p>
                            Al certificar confirma que revisó el conteo,
                            los totales y la firma electrónica del Agente.
                        </p>

                        <form
                            method="POST"
                            action="{{ route(
                                'promotor.arqueos-agentes.certificar',
                                $arqueo
                            ) }}"
                            id="form-certificar-arqueo"
                        >
                            @csrf

                            <button
                                type="button"
                                class="btn btn-certify"
                                id="open-certify-modal"
                                style="width: 100%;"
                            >
                                Certificar y firmar arqueo
                            </button>
                        </form>
                    </div>
                @elseif ($firmaPromotor)
                    <div class="alert alert-success" style="margin-top: 14px;">
                        Este arqueo ya fue certificado por el Promotor.
                    </div>
                @elseif (! $firmaAgente)
                    <div class="alert alert-warning" style="margin-top: 14px;">
                        El Promotor no puede certificar hasta que el Agente
                        haya firmado electrónicamente.
                    </div>
                @elseif ($arqueo->estado === 'ANULADO')
                    <div class="alert alert-error" style="margin-top: 14px;">
                        Un arqueo anulado no puede certificarse.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@if ($puedeCertificar)
    <div
        class="modal-backdrop"
        id="certify-modal"
        aria-hidden="true"
    >
        <div
            class="modal-dialog certify-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="certify-modal-title"
        >
            <div class="modal-header certify-modal-header">
                <div>
                    <h3 id="certify-modal-title">
                        Certificar Arqueo
                    </h3>

                    <p>
                        Confirme la certificación electrónica antes de continuar.
                    </p>
                </div>

                <button
                    type="button"
                    class="modal-close"
                    id="close-certify-modal"
                    aria-label="Cerrar"
                >
                    ×
                </button>
            </div>

            <div class="certify-modal-body">
                <div class="certify-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path>
                        <path d="m8.5 12 2.2 2.2 4.8-5"></path>
                    </svg>
                </div>

                <h4>¿Certificar este arqueo?</h4>

                <p>
                    Al continuar, el sistema registrará su firma electrónica
                    como Promotor y el arqueo quedará certificado.
                </p>

                <div class="certify-summary">
                    <div class="certify-summary-row">
                        <span>Número de arqueo</span>
                        <strong>{{ $arqueo->numero_arqueo }}</strong>
                    </div>

                    <div class="certify-summary-row">
                        <span>Agente</span>
                        <strong>
                            {{ $arqueo->codigo_agente_historico }}
                            — {{ $arqueo->nombre_negocio_historico }}
                        </strong>
                    </div>

                    <div class="certify-summary-row">
                        <span>Total arqueado</span>
                        <strong>
                            Q {{ number_format(
                                (float) $arqueo->total_arqueado,
                                2
                            ) }}
                        </strong>
                    </div>

                    <div class="certify-summary-row">
                        <span>Diferencia</span>
                        <strong>
                            Q {{ number_format(
                                (float) $arqueo->diferencia,
                                2
                            ) }}
                        </strong>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    id="cancel-certify-modal"
                >
                    Volver
                </button>

                <button
                    type="button"
                    class="btn btn-certify"
                    id="confirm-certify-button"
                >
                    Sí, Certificar y Firmar
                </button>
            </div>
        </div>
    </div>
@endif

@if ($puedeCertificar)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('certify-modal');
            const openButton = document.getElementById('open-certify-modal');
            const closeButton = document.getElementById('close-certify-modal');
            const cancelButton = document.getElementById('cancel-certify-modal');
            const confirmButton = document.getElementById('confirm-certify-button');
            const form = document.getElementById('form-certificar-arqueo');

            if (!modal || !openButton || !form) {
                return;
            }

            const openModal = function () {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
            };

            const closeModal = function () {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                openButton.focus();
            };

            openButton.addEventListener('click', openModal);
            closeButton?.addEventListener('click', closeModal);
            cancelButton?.addEventListener('click', closeModal);

            confirmButton?.addEventListener('click', function () {
                this.disabled = true;
                this.textContent = 'Certificando...';
                form.submit();
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (
                    event.key === 'Escape'
                    && modal.classList.contains('is-open')
                ) {
                    closeModal();
                }
            });
        });
    </script>
@endif

@if ($puedeAnular)
    <div
        class="modal-backdrop"
        id="annulment-modal"
        aria-hidden="true"
    >
        <div
            class="modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="annulment-modal-title"
        >
            <div class="modal-header">
                <div>
                    <h3 id="annulment-modal-title">
                        Anular arqueo
                    </h3>

                    <p>
                        Esta acción conservará el arqueo y sus firmas,
                        pero cambiará su estado a anulado.
                    </p>
                </div>

                <button
                    type="button"
                    class="modal-close"
                    id="close-annulment-modal"
                    aria-label="Cerrar"
                >
                    ×
                </button>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ) }}"
            >
                @csrf

                <div class="modal-body">
                    <div class="modal-warning">
                        <strong>Arqueo:</strong>
                        {{ $arqueo->numero_arqueo }}<br>

                        <strong>Agente:</strong>
                        {{ $arqueo->codigo_agente_historico }}
                        — {{ $arqueo->nombre_negocio_historico }}
                    </div>

                    <div class="modal-form-group">
                        <label for="motivo_anulacion">
                            Motivo de la anulación
                        </label>

                        <textarea
                            id="motivo_anulacion"
                            name="motivo_anulacion"
                            class="modal-form-control"
                            minlength="10"
                            maxlength="500"
                            required
                            placeholder="Explique claramente por qué debe anularse este arqueo."
                        >{{ old('motivo_anulacion') }}</textarea>
                    </div>

                    <div class="modal-form-group">
                        <label for="password_anulacion">
                            Contraseña del Promotor
                        </label>

                        <input
                            type="password"
                            id="password_anulacion"
                            name="password"
                            class="modal-form-control"
                            required
                            autocomplete="current-password"
                            placeholder="Ingrese su contraseña para confirmar"
                        >
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        id="cancel-annulment-modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        Confirmar anulación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('annulment-modal');
            const openButton = document.getElementById(
                'open-annulment-modal'
            );
            const closeButton = document.getElementById(
                'close-annulment-modal'
            );
            const cancelButton = document.getElementById(
                'cancel-annulment-modal'
            );

            if (!modal || !openButton) {
                return;
            }

            const openModal = function () {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');

                document.getElementById(
                    'motivo_anulacion'
                )?.focus();
            };

            const closeModal = function () {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                openButton.focus();
            };

            openButton.addEventListener('click', openModal);
            closeButton?.addEventListener('click', closeModal);
            cancelButton?.addEventListener('click', closeModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (
                    event.key === 'Escape'
                    && modal.classList.contains('is-open')
                ) {
                    closeModal();
                }
            });

            @if ($errors->has('motivo_anulacion') || $errors->has('password'))
                openModal();
            @endif
        });
    </script>
@endif

@endsection
