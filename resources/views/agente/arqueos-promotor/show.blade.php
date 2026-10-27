@extends('layouts.agente')

@section('title', 'Detalle del Arqueo del Promotor')
@section('module-title', 'Arqueos del Promotor')

@push('styles')
<style>
    .show-wrapper{
        width:calc(100% - 28px);
        max-width:1120px;
        margin:0 auto;
        padding-bottom:28px;
    }

    .show-toolbar{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:18px;
        margin-bottom:16px;
    }

    .show-toolbar h2{
        margin:0;
        color:#0a3158;
        font-size:24px;
        letter-spacing:-.45px;
    }

    .show-toolbar p{
        margin:6px 0 0;
        color:#748592;
        font-size:12px;
        line-height:1.5;
    }

    .toolbar-actions{
        display:flex;
        flex-wrap:wrap;
        justify-content:flex-end;
        gap:10px;
    }

    .btn-action{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        min-height:42px;
        padding:0 16px;
        border:0;
        border-radius:10px;
        font-size:12px;
        font-weight:800;
        text-decoration:none;
        cursor:pointer;
        transition:.2s ease;
    }

    .btn-action:hover{transform:translateY(-1px)}

    .btn-back{
        border:1px solid #d6e0e7;
        background:#fff;
        color:#36556e;
    }

    .btn-print{
        background:linear-gradient(135deg,#164c96,#1c64b5);
        color:#fff;
        box-shadow:0 8px 18px rgba(22,76,150,.18);
    }

    .btn-sign{
        background:#18794e;
        color:#fff;
        box-shadow:0 8px 18px rgba(24,121,78,.15);
    }

    .status-banner{
        margin-bottom:16px;
        padding:14px 16px;
        border:1px solid #e1e9ee;
        border-radius:12px;
        font-size:11px;
        line-height:1.55;
    }

    .status-banner strong{
        display:block;
        margin-bottom:3px;
        color:#173b59;
        font-size:12px;
    }

    .status-banner.success{
        border-color:#bfe2cd;
        background:#effaf3;
        color:#49695a;
    }

    .status-banner.warning{
        border-color:#efd89f;
        background:#fff9e8;
        color:#7c6119;
    }

    .status-banner.danger{
        border-color:#efc0c0;
        background:#fff2f2;
        color:#8f3b3b;
    }

    /* =========================================================
       DOCUMENTO DIGITAL - MISMA ESTRUCTURA VISUAL DEL PDF
       ========================================================= */

    .paper{
        position:relative;
        overflow:hidden;
        padding:24px 30px 26px;
        border:1px solid #d5dce1;
        border-radius:16px;
        background:#fff;
        box-shadow:0 16px 38px rgba(12,49,82,.09);
    }

    .paper::after{
        content:"";
        position:absolute;
        z-index:0;
        top:225px;
        left:50%;
        width:380px;
        height:470px;
        opacity:.045;
        pointer-events:none;
        transform:translateX(-50%);
        background:
            url("{{ asset('images/logos/agentes-micoope.png') }}")
            center/contain no-repeat;
    }

    .paper-content{
        position:relative;
        z-index:2;
    }

    /* Encabezado idéntico al orden del PDF:
       logo | título/subtítulo | número
    */
    .pdf-header{
        position:relative;
        display:grid;
        grid-template-columns:190px minmax(360px,1fr) 245px;
        align-items:start;
        gap:16px;
        min-height:82px;
        margin-bottom:8px;
    }

    .pdf-logo{
        width:180px;
        height:62px;
        object-fit:contain;
        object-position:left top;
    }

    .pdf-title{
        padding-top:8px;
        text-align:center;
    }

    .pdf-title h1{
        margin:0;
        color:#111;
        font-family:Georgia,"Times New Roman",serif;
        font-size:24px;
        font-weight:700;
        line-height:1.08;
        text-transform:uppercase;
        white-space:nowrap;
    }

    .pdf-title p{
        margin:6px 0 0;
        color:#111;
        font-family:Georgia,"Times New Roman",serif;
        font-size:14px;
        font-weight:700;
        text-transform:uppercase;
    }

    .pdf-number{
        padding-top:10px;
        color:#a31d17;
        font-family:Georgia,"Times New Roman",serif;
        text-align:right;
    }

    .pdf-number span{
        color:#a31d17;
        font-family:inherit;
        font-size:10px;
        font-weight:700;
    }

    .pdf-number strong{
        display:inline;
        font-size:12px;
        font-weight:700;
        letter-spacing:.2px;
        white-space:nowrap;
    }

    /* Datos: exactamente como el PDF */
    .general-lines{
        margin-top:4px;
    }

    .general-row{
        display:grid;
        align-items:end;
        gap:12px;
        margin-bottom:8px;
    }

    .general-row.row-time{
        grid-template-columns:92px 110px 92px 110px 118px 110px;
        justify-content:end;
    }

    .general-row.row-agent{
        grid-template-columns:105px minmax(210px,1fr) minmax(210px,1fr);
    }

    .general-row.row-owner{
        grid-template-columns:1fr;
    }

    .line-field{
        min-width:0;
    }

    .line-pair{
        display:grid;
        grid-template-columns:auto minmax(0,1fr);
        align-items:end;
        gap:7px;
    }

    .line-label{
        color:#111;
        font-size:9px;
        font-weight:800;
        white-space:nowrap;
    }

    .line-value{
        min-width:0;
        min-height:22px;
        padding:3px 4px 2px;
        overflow:hidden;
        border-bottom:1px solid #5a6268;
        color:#111;
        font-size:10px;
        font-weight:700;
        text-align:center;
        text-overflow:ellipsis;
        white-space:nowrap;
    }

    /* Conteo - reproduce la estructura del PDF */
    .cash-section{
        margin-top:14px;
    }

    .cash-title{
        margin:0 0 2px;
        color:#111;
        font-family:Georgia,"Times New Roman",serif;
        font-size:16px;
        font-weight:700;
    }

    .cash-subtitle{
        margin-bottom:8px;
        color:#111;
        font-size:9px;
        font-weight:800;
    }

    .cash-table-wrap{
        display:grid;
        grid-template-columns:minmax(0,1fr) 150px;
        gap:22px;
        align-items:start;
    }

    .cash-table{
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
    }

    .cash-table th,
    .cash-table td{
        border:0;
        padding:4px 8px;
        color:#111;
        font-size:10px;
    }

    .cash-table th{
        font-size:8px;
        font-weight:800;
    }

    .cash-table th:nth-child(1),
    .cash-table td:nth-child(1){
        width:29%;
        text-align:right;
    }

    .cash-table th:nth-child(2),
    .cash-table td:nth-child(2){
        width:28%;
        text-align:center;
    }

    .cash-table th:nth-child(3),
    .cash-table td:nth-child(3){
        width:43%;
        text-align:left;
    }

    .money-line{
        display:inline-block;
        min-width:90px;
        padding-bottom:2px;
        border-bottom:1px solid #5a6268;
        text-align:right;
    }

    .qty-line{
        display:inline-block;
        min-width:72px;
        padding-bottom:2px;
        border-bottom:1px solid #5a6268;
        font-weight:800;
        text-align:center;
    }

    .subtotal-line{
        display:inline-grid;
        grid-template-columns:18px 1fr;
        align-items:end;
        gap:3px;
        min-width:145px;
    }

    .subtotal-line strong{
        padding-bottom:2px;
        border-bottom:1px solid #5a6268;
        text-align:right;
    }

    .cash-total-box{
        padding-top:118px;
    }

    .cash-total-box.coin{
        padding-top:72px;
    }

    .cash-total-row{
        display:grid;
        grid-template-columns:18px 1fr;
        align-items:end;
        gap:3px;
        color:#111;
        font-size:10px;
        font-weight:800;
    }

    .cash-total-row strong{
        min-height:20px;
        padding:3px 3px 2px;
        border-bottom:1px solid #5a6268;
        text-align:right;
    }

    .total-arqueado-line{
        display:flex;
        justify-content:flex-end;
        align-items:end;
        gap:8px;
        margin-top:8px;
        padding-right:10px;
        color:#111;
        font-size:10px;
    }

    .total-arqueado-line strong{
        min-width:130px;
        padding:3px 3px 2px;
        border-bottom:1px solid #5a6268;
        text-align:right;
    }

    /* Resumen inferior derecho igual al PDF */
    .summary-block{
        width:300px;
        margin:22px 0 0 auto;
    }

    .summary-row{
        display:grid;
        grid-template-columns:150px 18px 1fr;
        align-items:end;
        gap:4px;
        min-height:30px;
    }

    .summary-row span:first-child{
        color:#111;
        font-size:9px;
        text-align:right;
    }

    .summary-row strong{
        padding:3px 3px 2px;
        border-bottom:1px solid #5a6268;
        color:#111;
        font-size:10px;
        text-align:right;
    }

    .summary-row.difference{
        margin-top:4px;
    }

    .summary-row.difference span:first-child{
        font-weight:800;
        text-transform:uppercase;
    }

    .summary-row.difference strong{
        color:#174f80;
    }

    .summary-row.difference.sobrante strong{
        color:#197247;
    }

    .summary-row.difference.faltante strong{
        color:#ae342f;
    }

    /* Observaciones y Calificación */
    .text-section{
        margin-top:16px;
    }

    .text-label{
        margin-bottom:4px;
        color:#111;
        font-size:9px;
        font-weight:800;
    }

    .lined-text{
        min-height:54px;
        padding:4px 3px;
        background:
            repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent 17px,
                #5a6268 18px
            );
        color:#111;
        font-size:9px;
        line-height:18px;
        white-space:pre-wrap;
        overflow-wrap:anywhere;
    }

    /* Firmas: 3 columnas como PDF */
    .signatures{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:26px;
        margin-top:18px;
    }

    .signature{
        text-align:center;
    }

    .signature-data{
        min-height:42px;
        display:flex;
        align-items:flex-end;
        justify-content:center;
        padding:0 5px 5px;
        color:#2d3c48;
        font-size:8px;
        line-height:1.3;
    }

    .signature-data.pending{
        color:#7d8992;
        font-style:italic;
    }

    .signature-data strong{
        font-size:9px;
    }

    .signature-line{
        border-bottom:1px solid #333;
    }

    .signature-role{
        margin-top:4px;
        color:#111;
        font-size:8px;
        font-weight:800;
        line-height:1.3;
    }

    .signature-action{
        margin-top:16px;
        padding:14px;
        border:1px solid #c9e4d4;
        border-radius:11px;
        background:#eff9f3;
        color:#49695a;
        font-size:10px;
        line-height:1.5;
    }

    .signature-action p{
        margin:0 0 10px;
    }

    .paper-footer{
        display:flex;
        justify-content:flex-end;
        gap:10px;
        margin-top:20px;
        padding-top:15px;
        border-top:1px solid #e1e7eb;
    }

    .status-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:7px 10px;
        border-radius:999px;
        font-size:8px;
        font-weight:850;
        text-transform:uppercase;
        white-space:nowrap;
    }

    .status-badge.pendiente{
        border:1px solid #efd99f;
        background:#fff9e8;
        color:#9c7014;
    }

    .status-badge.certificado{
        border:1px solid #c7e4d2;
        background:#effaf3;
        color:#197247;
    }

    .status-badge.anulado{
        border:1px solid #edcaca;
        background:#fff1f0;
        color:#ae342f;
    }

    /* Modal */
    .modal-overlay{
        position:fixed;
        z-index:2000;
        inset:0;
        display:none;
        align-items:center;
        justify-content:center;
        padding:20px;
        background:rgba(6,31,54,.66);
        backdrop-filter:blur(3px);
    }

    .modal-overlay.open{display:flex}

    .modal-card{
        width:min(540px,100%);
        overflow:hidden;
        border-radius:18px;
        background:#fff;
        box-shadow:0 28px 80px rgba(0,0,0,.28);
    }

    .modal-header{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:15px;
        padding:20px 22px 16px;
        border-bottom:1px solid #e8eef2;
    }

    .modal-header h3{
        margin:0;
        color:#123b5d;
        font-size:18px;
    }

    .modal-close{
        width:34px;
        height:34px;
        border:0;
        border-radius:9px;
        background:#eef3f6;
        color:#526b7e;
        font-size:20px;
        cursor:pointer;
    }

    .modal-body{padding:20px 22px}

    .modal-data{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
    }

    .modal-data-item{
        padding:11px 12px;
        border:1px solid #e2e9ee;
        border-radius:10px;
        background:#fafcfd;
    }

    .modal-data-item span{
        display:block;
        color:#7c8b96;
        font-size:8px;
        font-weight:800;
        text-transform:uppercase;
    }

    .modal-data-item strong{
        display:block;
        margin-top:5px;
        color:#173b59;
        font-size:11px;
    }

    .modal-notice{
        margin-top:14px;
        padding:13px;
        border:1px solid #eed699;
        border-radius:10px;
        background:#fff9e8;
        color:#7c6119;
        font-size:10px;
        line-height:1.55;
    }

    .modal-footer{
        display:flex;
        justify-content:flex-end;
        gap:10px;
        padding:15px 22px 20px;
        border-top:1px solid #e8eef2;
        background:#fbfcfd;
    }

    .modal-cancel{
        background:#edf2f5;
        color:#3d596f;
    }

    .modal-confirm{
        background:#18794e;
        color:#fff;
    }

    body.modal-open{overflow:hidden}

    @media(max-width:980px){
        .pdf-header{
            grid-template-columns:170px minmax(300px,1fr) 210px;
        }

        .pdf-title h1{
            font-size:21px;
        }

        .general-row.row-time{
            grid-template-columns:80px 95px 80px 95px 105px 95px;
        }
    }

    @media(max-width:820px){
        .show-toolbar{
            flex-direction:column;
        }

        .toolbar-actions{
            justify-content:flex-start;
        }

        .pdf-header{
            grid-template-columns:1fr;
            text-align:center;
        }

        .pdf-logo{
            margin:0 auto;
        }

        .pdf-title h1{
            white-space:normal;
        }

        .pdf-number{
            text-align:center;
        }

        .general-row.row-time,
        .general-row.row-agent{
            grid-template-columns:1fr 1fr;
        }

        .general-row.row-owner{
            grid-template-columns:1fr;
        }

        .cash-table-wrap{
            grid-template-columns:1fr;
        }

        .cash-total-box,
        .cash-total-box.coin{
            padding-top:0;
        }

        .summary-block{
            width:100%;
        }

        .signatures{
            grid-template-columns:1fr;
        }
    }

    @media(max-width:620px){
        .show-wrapper{
            width:100%;
        }

        .paper{
            padding-right:16px;
            padding-left:16px;
        }

        .general-row.row-time,
        .general-row.row-agent,
        .modal-data{
            grid-template-columns:1fr;
        }

        .line-pair{
            grid-template-columns:1fr;
            gap:2px;
        }

        .line-value{
            text-align:left;
        }

        .paper-footer{
            flex-direction:column-reverse;
        }

        .paper-footer .btn-action,
        .toolbar-actions .btn-action{
            width:100%;
        }
    }
</style>
@endpush

@section('content')

@php
    $firmaRealizador = $arqueo->firmas->first(
        fn ($firma) =>
            $firma->tipo_firma === 'REALIZADOR'
            && $firma->valida
    );

    $firmaCertificador = $arqueo->firmas->first(
        fn ($firma) =>
            $firma->tipo_firma === 'CERTIFICADOR'
            && $firma->valida
    );

    $puedeFirmar =
        $arqueo->estado === 'PENDIENTE_CERTIFICACION'
        && ! $firmaValidador;

    $claseEstado = match ($arqueo->estado) {
        'PENDIENTE_CERTIFICACION' => 'pendiente',
        'CERTIFICADO' => 'certificado',
        'ANULADO' => 'anulado',
        default => 'pendiente',
    };

    $textoEstado = match ($arqueo->estado) {
        'PENDIENTE_CERTIFICACION' => 'Pendiente de certificación',
        'CERTIFICADO' => 'Certificado',
        'ANULADO' => 'Anulado',
        default => str_replace('_', ' ', $arqueo->estado),
    };

    $diferencia = (float) $arqueo->diferencia;

    $diferenciaClase = $diferencia > 0
        ? 'sobrante'
        : ($diferencia < 0 ? 'faltante' : '');
@endphp

<div class="show-wrapper">

    <div class="show-toolbar">
        <div>
            <h2>Detalle del Arqueo del Promotor</h2>
            <p>
                Visualización digital basada en el formato oficial del arqueo.
            </p>
        </div>

        <div class="toolbar-actions">
            <a
                href="{{ route('agente.arqueos-promotor.index') }}"
                class="btn-action btn-back"
            >
                Regresar
            </a>

            <a
                href="{{ route('agente.arqueos-promotor.imprimir', $arqueo) }}"
                target="_blank"
                class="btn-action btn-print"
            >
                Imprimir PDF
            </a>

            @if ($puedeFirmar)
                <button
                    type="button"
                    class="btn-action btn-sign"
                    id="openSignModal"
                >
                    Firmar arqueo
                </button>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="status-banner success">
            <strong>Operación realizada</strong>
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="status-banner warning">
            <strong>Atención</strong>
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="status-banner danger">
            <strong>No fue posible completar la operación</strong>
            {{ $errors->first() }}
        </div>
    @endif

    <article class="paper">
        <div class="paper-content">

            <header class="pdf-header">
                <img
                    src="{{ asset('images/logos/ecosaba.png') }}"
                    alt="ECOSABA MICOOPE"
                    class="pdf-logo"
                >

                <div class="pdf-title">
                    <h1>Arqueo y Corte de Caja</h1>
                    <p>Agentes MICOOPE</p>
                </div>

                <div class="pdf-number">
                    <span>N.º </span>
                    <strong>{{ $arqueo->numero_arqueo }}</strong>
                </div>
            </header>

            <section class="general-lines">

                <div class="general-row row-time">
                    <div class="line-label">Fecha:</div>
                    <div class="line-value">
                        {{ $arqueo->fecha_arqueo->format('d/m/Y') }}
                    </div>

                    <div class="line-label">Hora inicio:</div>
                    <div class="line-value">
                        {{ $arqueo->hora_inicio
                            ? $arqueo->hora_inicio->format('H:i')
                            : '—' }}
                    </div>

                    <div class="line-label">Hora finalización:</div>
                    <div class="line-value">
                        {{ $arqueo->hora_fin
                            ? $arqueo->hora_fin->format('H:i')
                            : '—' }}
                    </div>
                </div>

                <div class="general-row row-agent">
                    <div class="line-pair">
                        <span class="line-label">Agente No.:</span>
                        <span class="line-value">
                            {{ $arqueo->codigo_agente_historico }}
                        </span>
                    </div>

                    <div class="line-pair">
                        <span class="line-label">Nombre Negocio:</span>
                        <span class="line-value">
                            {{ $arqueo->nombre_negocio_historico }}
                        </span>
                    </div>

                    <div class="line-pair">
                        <span class="line-label">Agente MICOOPE:</span>
                        <span class="line-value">
                            {{ $arqueo->nombre_negocio_historico }}
                        </span>
                    </div>
                </div>

                <div class="general-row row-owner">
                    <div class="line-pair">
                        <span class="line-label">
                            Nombre del Propietario o Receptor Pagador:
                        </span>

                        <span class="line-value">
                            {{ $arqueo->nombre_propietario_historico }}
                        </span>
                    </div>
                </div>

            </section>

            <section class="cash-section">
                <h3 class="cash-title">Billetes</h3>
                <div class="cash-subtitle">Denominación</div>

                <div class="cash-table-wrap">
                    <table class="cash-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Cantidad</th>
                                <th>Sub-total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($billetes as $detalle)
                                <tr>
                                    <td>
                                        Q. {{ number_format(
                                            (float) $detalle->denominacion,
                                            2
                                        ) }}
                                    </td>

                                    <td>
                                        <span class="qty-line">
                                            {{ $detalle->cantidad }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="subtotal-line">
                                            <span>Q.</span>
                                            <strong>
                                                {{ number_format(
                                                    (float) $detalle->subtotal,
                                                    2
                                                ) }}
                                            </strong>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">Sin registros.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="cash-total-box">
                        <div class="cash-total-row">
                            <span>Q.</span>
                            <strong>
                                {{ number_format(
                                    (float) $arqueo->total_billetes,
                                    2
                                ) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </section>

            <section class="cash-section">
                <h3 class="cash-title">Monedas</h3>
                <div class="cash-subtitle">Denominación</div>

                <div class="cash-table-wrap">
                    <table class="cash-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Cantidad</th>
                                <th>Sub-total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($monedas as $detalle)
                                <tr>
                                    <td>
                                        Q. {{ number_format(
                                            (float) $detalle->denominacion,
                                            2
                                        ) }}
                                    </td>

                                    <td>
                                        <span class="qty-line">
                                            {{ $detalle->cantidad }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="subtotal-line">
                                            <span>Q.</span>
                                            <strong>
                                                {{ number_format(
                                                    (float) $detalle->subtotal,
                                                    2
                                                ) }}
                                            </strong>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">Sin registros.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="cash-total-box coin">
                        <div class="cash-total-row">
                            <span>Q.</span>
                            <strong>
                                {{ number_format(
                                    (float) $arqueo->total_monedas,
                                    2
                                ) }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="total-arqueado-line">
                    <span>Total Arqueado</span>
                    <span>Q.</span>
                    <strong>
                        {{ number_format(
                            (float) $arqueo->total_arqueado,
                            2
                        ) }}
                    </strong>
                </div>
            </section>

            <section class="summary-block">
                <div class="summary-row">
                    <span>Saldo del Sistema</span>
                    <span>Q.</span>
                    <strong>
                        {{ number_format(
                            (float) $arqueo->saldo_sistema,
                            2
                        ) }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Total Arqueado</span>
                    <span>Q.</span>
                    <strong>
                        {{ number_format(
                            (float) $arqueo->total_arqueado,
                            2
                        ) }}
                    </strong>
                </div>

                <div class="summary-row difference {{ $diferenciaClase }}">
                    <span>DIFERENCIA</span>
                    <span>Q.</span>
                    <strong>
                        {{ number_format($diferencia, 2) }}
                    </strong>
                </div>
            </section>

            <section class="text-section">
                <div class="text-label">Observaciones:</div>
                <div class="lined-text">
                    {{ $arqueo->observaciones ?: 'Sin observaciones registradas.' }}
                </div>
            </section>

            <section class="text-section">
                <div class="text-label">Calificación:</div>
                <div class="lined-text">
                    {{ $arqueo->certificacion ?: 'Sin calificación registrada.' }}
                </div>
            </section>

            <section class="signatures">

                <div class="signature">
                    @if ($firmaValidador)
                        <div class="signature-data">
                            <div>
                                <strong>
                                    {{ trim(
                                        $firmaValidador->nombres_historicos
                                        . ' '
                                        . $firmaValidador->apellidos_historicos
                                    ) }}
                                </strong>
                                <br>
                                Firmado electrónicamente:
                                {{ $firmaValidador->fecha_firma
                                    ? $firmaValidador->fecha_firma->format('d/m/Y H:i')
                                    : 'Fecha no disponible' }}
                            </div>
                        </div>
                    @else
                        <div class="signature-data pending">
                            Pendiente de firma del agente
                        </div>
                    @endif

                    <div class="signature-line"></div>

                    <div class="signature-role">
                        Propietario y/o Receptor - Pagador<br>
                        Agente MICOOPE
                    </div>
                </div>

                <div class="signature">
                    @if ($firmaRealizador)
                        <div class="signature-data">
                            <div>
                                <strong>
                                    {{ trim(
                                        $firmaRealizador->nombres_historicos
                                        . ' '
                                        . $firmaRealizador->apellidos_historicos
                                    ) }}
                                </strong>
                                <br>
                                Firmado electrónicamente:
                                {{ $firmaRealizador->fecha_firma
                                    ? $firmaRealizador->fecha_firma->format('d/m/Y H:i')
                                    : 'Fecha no disponible' }}
                            </div>
                        </div>
                    @else
                        <div class="signature-data pending">
                            Firma electrónica no registrada
                        </div>
                    @endif

                    <div class="signature-line"></div>

                    <div class="signature-role">
                        Promotor Agentes MICOOPE
                    </div>
                </div>

                <div class="signature">
                    @if ($firmaCertificador)
                        <div class="signature-data">
                            <div>
                                <strong>
                                    {{ trim(
                                        $firmaCertificador->nombres_historicos
                                        . ' '
                                        . $firmaCertificador->apellidos_historicos
                                    ) }}
                                </strong>
                                <br>
                                Firmado electrónicamente:
                                {{ $firmaCertificador->fecha_firma
                                    ? $firmaCertificador->fecha_firma->format('d/m/Y H:i')
                                    : 'Fecha no disponible' }}
                            </div>
                        </div>
                    @else
                        <div class="signature-data pending">
                            Pendiente de certificación
                        </div>
                    @endif

                    <div class="signature-line"></div>

                    <div class="signature-role">
                        Jefe de Agentes MICOOPE
                    </div>
                </div>

            </section>

            @if ($puedeFirmar)
                <div class="signature-action">
                    <p>
                        Al firmar confirma que revisó el efectivo contado,
                        los totales y la información registrada por el Promotor.
                    </p>

                    <button
                        type="button"
                        class="btn-action btn-sign"
                        id="openSignModalSecondary"
                    >
                        Firmar electrónicamente
                    </button>
                </div>
            @endif

            <div class="paper-footer">
                <span class="status-badge {{ $claseEstado }}">
                    {{ $textoEstado }}
                </span>

                <a
                    href="{{ route('agente.arqueos-promotor.index') }}"
                    class="btn-action btn-back"
                >
                    Regresar
                </a>

                <a
                    href="{{ route(
                        'agente.arqueos-promotor.imprimir',
                        $arqueo
                    ) }}"
                    target="_blank"
                    class="btn-action btn-print"
                >
                    Imprimir PDF
                </a>
            </div>

        </div>
    </article>
</div>

@if ($puedeFirmar)
    <div class="modal-overlay" id="signModal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true">

            <div class="modal-header">
                <h3>Confirmar firma electrónica</h3>

                <button
                    type="button"
                    class="modal-close"
                    id="closeSignModal"
                >
                    ×
                </button>
            </div>

            <div class="modal-body">
                <div class="modal-data">

                    <div class="modal-data-item">
                        <span>Número de arqueo</span>
                        <strong>{{ $arqueo->numero_arqueo }}</strong>
                    </div>

                    <div class="modal-data-item">
                        <span>Fecha</span>
                        <strong>{{ $arqueo->fecha_arqueo->format('d/m/Y') }}</strong>
                    </div>

                    <div class="modal-data-item">
                        <span>Total arqueado</span>
                        <strong>
                            Q {{ number_format(
                                (float) $arqueo->total_arqueado,
                                2
                            ) }}
                        </strong>
                    </div>

                    <div class="modal-data-item">
                        <span>Diferencia</span>
                        <strong>
                            Q {{ number_format(
                                (float) $arqueo->diferencia,
                                2
                            ) }}
                        </strong>
                    </div>

                </div>

                <div class="modal-notice">
                    Al continuar, el sistema generará su firma electrónica
                    como <strong>VALIDADOR</strong>.
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn-action modal-cancel"
                    id="cancelSignModal"
                >
                    Cancelar
                </button>

                <form
                    method="POST"
                    action="{{ route(
                        'agente.arqueos-promotor.firmar',
                        $arqueo
                    ) }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn-action modal-confirm"
                    >
                        Confirmar y firmar
                    </button>
                </form>
            </div>

        </div>
    </div>
@endif

@endsection

@if ($puedeFirmar)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal =
                document.getElementById('signModal');

            const openButtons = [
                document.getElementById('openSignModal'),
                document.getElementById('openSignModalSecondary'),
            ].filter(Boolean);

            const closeButton =
                document.getElementById('closeSignModal');

            const cancelButton =
                document.getElementById('cancelSignModal');

            if (! modal || openButtons.length === 0) {
                return;
            }

            function openModal() {
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
            }

            function closeModal() {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
            }

            openButtons.forEach(function (button) {
                button.addEventListener('click', openModal);
            });

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
                    && modal.classList.contains('open')
                ) {
                    closeModal();
                }
            });
        });
    </script>
    @endpush
@endif
