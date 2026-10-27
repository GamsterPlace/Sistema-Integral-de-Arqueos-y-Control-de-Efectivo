@extends('layouts.promotor')

@section('title', 'Detalle del Arqueo')
@section('module-title', 'Detalle del Arqueo')

@push('styles')
<style>
    .show-wrapper{
        width:calc(100% - 28px);
        max-width:1120px;
        margin:0 auto;
        padding-bottom:28px;
    }

    .page-actions{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    .btn-secondary-custom,
    .btn-primary-custom{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:42px;
        padding:0 17px;
        border-radius:11px;
        font-size:11px;
        font-weight:800;
        text-decoration:none;
    }

    .btn-secondary-custom{
        border:1px solid #d5dfe5;
        background:#fff;
        color:#31536e;
    }

    .btn-primary-custom{
        border:0;
        background:linear-gradient(135deg,#164c96,#1c64b5);
        color:#fff;
        box-shadow:0 9px 18px rgba(22,76,150,.18);
    }

    .document-wrap{
        overflow-x:auto;
        padding-bottom:8px;
    }

    .detail-sheet{
        position:relative;
        min-width:1040px;
        max-width:1220px;
        margin:0 auto;
        padding:34px 42px 30px;
        overflow:hidden;
        border:1px solid #d9e2e8;
        border-radius:18px;
        background:#fff;
        box-shadow:0 14px 36px rgba(18,54,79,.08);
        color:#222;
        font-family:Georgia,"Times New Roman",serif;
    }

    .detail-sheet::after{
        content:"";
        position:absolute;
        z-index:0;
        top:285px;
        left:50%;
        width:390px;
        height:460px;
        transform:translateX(-50%);
        background:url("{{ asset('images/logos/agentes-micoope.png') }}") center/contain no-repeat;
        opacity:.035;
        pointer-events:none;
    }

    .sheet-content{
        position:relative;
        z-index:1;
    }

    .sheet-header{
        display:grid;
        grid-template-columns:230px 1fr 220px;
        align-items:start;
        gap:18px;
        min-height:92px;
        padding:0;
        border:0;
    }

    .sheet-logo{
        width:205px;
        height:72px;
        object-fit:contain;
        object-position:left top;
    }

    .sheet-title{
        padding-top:10px;
        text-align:center;
    }

    .sheet-title h3{
        margin:0;
        color:#171717;
        font-family:Georgia,"Times New Roman",serif;
        font-size:23px;
        line-height:1.1;
        text-transform:uppercase;
    }

    .sheet-title p{
        margin:6px 0 0;
        color:#171717;
        font-family:Georgia,"Times New Roman",serif;
        font-size:14px;
        font-weight:700;
        text-transform:uppercase;
    }

    .sheet-number{
        padding-top:14px;
        color:#a82f2b;
        font-family:Georgia,"Times New Roman",serif;
        text-align:right;
    }

    .sheet-number span{
        color:#a82f2b;
        font-size:10px;
        font-weight:700;
    }

    .sheet-number strong{
        display:inline;
        margin:0;
        color:#a82f2b;
        font-size:12px;
        font-weight:700;
        letter-spacing:.3px;
        white-space:nowrap;
    }

    .pdf-fields{
        margin-top:8px;
        font-family:Arial,sans-serif;
    }

    .pdf-row{
        display:grid;
        gap:14px;
        margin-bottom:10px;
        align-items:end;
    }

    .pdf-row.row-time{
        grid-template-columns:1.05fr 1fr 1.15fr;
    }

    .pdf-row.row-agent{
        grid-template-columns:.75fr 1.4fr 1.25fr;
    }

    .pdf-row.row-owner{
        grid-template-columns:1fr;
    }

    .pdf-field{
        display:flex;
        align-items:flex-end;
        gap:7px;
        min-width:0;
    }

    .pdf-field label{
        flex:0 0 auto;
        color:#222;
        font-size:10px;
        white-space:nowrap;
    }

    .pdf-line{
        flex:1;
        min-width:0;
        min-height:25px;
        padding:5px 5px 3px;
        overflow:hidden;
        border-bottom:1px solid #555;
        color:#111;
        font-size:11px;
        font-weight:600;
        text-align:center;
        text-overflow:ellipsis;
        white-space:nowrap;
    }

    .cash-layout{
        display:grid;
        grid-template-columns:minmax(0,1fr) 310px;
        gap:42px;
        margin-top:20px;
    }

    .cash-main{
        min-width:0;
    }

    .pdf-section{
        margin-bottom:25px;
    }

    .pdf-section-title{
        margin:0 0 3px;
        color:#171717;
        font-family:Georgia,"Times New Roman",serif;
        font-size:17px;
        font-weight:700;
    }

    .pdf-section-subtitle{
        margin-bottom:13px;
        color:#222;
        font-family:Arial,sans-serif;
        font-size:10px;
        font-weight:700;
    }

    .money-table{
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        font-family:Arial,sans-serif;
    }

    .money-table th{
        padding:4px 8px 8px;
        color:#222;
        font-size:10px;
        font-weight:700;
        text-align:center;
    }

    .money-table th:first-child{
        text-align:left;
    }

    .money-table td{
        padding:4px 8px;
        color:#222;
        font-size:11px;
        vertical-align:bottom;
    }

    .money-table td:nth-child(1){
        text-align:right;
    }

    .money-table td:nth-child(2){
        text-align:center;
    }

    .money-table td:nth-child(3){
        text-align:left;
    }

    .qty-line{
        display:inline-block;
        min-width:72px;
        padding-bottom:2px;
        border-bottom:1px solid #555;
        font-weight:700;
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
        border-bottom:1px solid #555;
        text-align:right;
    }

    .section-total{
        display:flex;
        justify-content:flex-end;
        align-items:flex-end;
        gap:7px;
        margin-top:7px;
        color:#222;
        font-family:Arial,sans-serif;
        font-size:11px;
    }

    .section-total .value{
        width:155px;
        padding:0 4px 3px;
        border-bottom:1px solid #555;
        text-align:right;
        font-weight:700;
    }

    .middle-total{
        display:flex;
        justify-content:flex-end;
        align-items:flex-end;
        gap:8px;
        margin-top:11px;
        color:#222;
        font-family:Arial,sans-serif;
        font-size:12px;
    }

    .middle-total .value{
        width:160px;
        padding:0 4px 3px;
        border-bottom:1px solid #555;
        text-align:right;
        font-weight:700;
    }

    .summary{
        padding-top:30px;
        font-family:Arial,sans-serif;
    }

    .summary-row{
        display:grid;
        grid-template-columns:1fr 20px 120px;
        align-items:end;
        gap:6px;
        margin-bottom:18px;
        font-size:11px;
    }

    .summary-row .label{
        text-align:right;
    }

    .summary-row .value{
        padding:0 4px 3px;
        border-bottom:1px solid #555;
        text-align:right;
        font-weight:700;
    }

    .summary-row.difference{
        margin-top:18px;
        font-size:12px;
    }

    .summary-row.difference .value{
        color:#174f80;
    }

    .summary-row.difference.positive .value{
        color:#1d7b4e;
    }

    .summary-row.difference.negative .value{
        color:#b13c3c;
    }

    .text-section{
        margin-top:18px;
        font-family:Arial,sans-serif;
    }

    .text-section label{
        display:block;
        margin-bottom:5px;
        color:#222;
        font-size:10px;
        font-weight:700;
    }

    .lined-text{
        min-height:58px;
        padding:4px 3px;
        background:repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent 22px,
            #777 23px
        );
        color:#222;
        font-size:10px;
        line-height:23px;
        white-space:pre-wrap;
        overflow-wrap:anywhere;
    }

    .signatures{
        display:grid;
        grid-template-columns:repeat(3,1fr);
        gap:30px;
        margin-top:28px;
        font-family:Arial,sans-serif;
        text-align:center;
    }

    .signature-box{
        min-height:88px;
        display:flex;
        flex-direction:column;
        justify-content:flex-end;
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
        color:#777;
        font-style:italic;
    }

    .signature-data strong{
        font-size:9px;
    }

    .signature-code{
        margin-top:2px;
        color:#6b747b;
        font-family:monospace;
        font-size:7px;
        overflow-wrap:anywhere;
    }

    .signature-line{
        border-bottom:1px solid #444;
    }

    .signature-role{
        margin-top:5px;
        color:#111;
        font-size:9px;
        font-weight:700;
        line-height:1.35;
    }

    .footer-actions{
        display:flex;
        justify-content:flex-end;
        gap:10px;
        margin-top:24px;
        padding-top:18px;
        border-top:1px solid #dfe6eb;
        font-family:Arial,sans-serif;
    }

    @media(max-width:900px){
        .detail-sheet{
            min-width:940px;
        }
    }
</style>
@endpush

@section('content')

<div class="show-wrapper">
    <div class="page-header">
        <div class="page-title">
            <h2>Detalle del Arqueo</h2>
            <p>Consulte el contenido y las firmas electrónicas del arqueo.</p>
        </div>

        <div class="page-actions">
            <a
                href="{{ route('promotor.arqueos.index') }}"
                class="btn-secondary-custom"
            >
                Volver al Historial
            </a>

            <a
                href="{{ route('promotor.arqueos.imprimir', $arqueo) }}"
                target="_blank"
                class="btn-primary-custom"
            >
                Imprimir PDF
            </a>
        </div>
    </div>

    <div class="document-wrap">
        <section class="detail-sheet">
            <div class="sheet-content">

                <header class="sheet-header">
                    <img
                        src="{{ asset('images/logos/ecosaba.png') }}"
                        alt="ECOSABA MICOOPE"
                        class="sheet-logo"
                    >

                    <div class="sheet-title">
                        <h3>Arqueo y Corte de Caja</h3>
                        <p>Agentes MICOOPE</p>
                    </div>

                    <div class="sheet-number">
                        <span>N.º </span>
                        <strong>{{ $arqueo->numero_arqueo }}</strong>
                    </div>
                </header>

                <section class="pdf-fields">

                    <div class="pdf-row row-time">
                        <div class="pdf-field">
                            <label>Fecha:</label>
                            <span class="pdf-line">
                                {{ $arqueo->fecha_arqueo->format('d/m/Y') }}
                            </span>
                        </div>

                        <div class="pdf-field">
                            <label>Hora inicio:</label>
                            <span class="pdf-line">
                                {{ $arqueo->hora_inicio?->format('H:i') ?? '—' }}
                            </span>
                        </div>

                        <div class="pdf-field">
                            <label>Hora finalización:</label>
                            <span class="pdf-line">
                                {{ $arqueo->hora_fin?->format('H:i') ?? 'Pendiente' }}
                            </span>
                        </div>
                    </div>

                    <div class="pdf-row row-agent">
                        <div class="pdf-field">
                            <label>Agente No.:</label>
                            <span class="pdf-line">
                                {{ $arqueo->codigo_agente_historico }}
                            </span>
                        </div>

                        <div class="pdf-field">
                            <label>Nombre Negocio:</label>
                            <span class="pdf-line">
                                {{ $arqueo->nombre_negocio_historico }}
                            </span>
                        </div>

                        <div class="pdf-field">
                            <label>Agente MICOOPE:</label>
                            <span class="pdf-line">
                                {{ $arqueo->nombre_negocio_historico }}
                            </span>
                        </div>
                    </div>

                    <div class="pdf-row row-owner">
                        <div class="pdf-field">
                            <label>
                                Nombre del Propietario o Receptor Pagador:
                            </label>

                            <span class="pdf-line">
                                {{ $arqueo->nombre_propietario_historico }}
                            </span>
                        </div>
                    </div>

                </section>

                <div class="cash-layout">

                    <div class="cash-main">

                        <section class="pdf-section">
                            <h4 class="pdf-section-title">Billetes</h4>
                            <div class="pdf-section-subtitle">
                                Denominación
                            </div>

                            <table class="money-table">
                                <thead>
                                    <tr>
                                        <th style="width:27%"></th>
                                        <th style="width:25%">Cantidad</th>
                                        <th style="width:48%">Sub-total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($billetes as $detalle)
                                        <tr>
                                            <td>
                                                Q. {{ number_format(
                                                    (float)$detalle->denominacion,
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
                                                            (float)$detalle->subtotal,
                                                            2
                                                        ) }}
                                                    </strong>
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

                            <div class="section-total">
                                <span>Q.</span>
                                <span class="value">
                                    {{ number_format(
                                        (float)$arqueo->total_billetes,
                                        2
                                    ) }}
                                </span>
                            </div>
                        </section>

                        <section class="pdf-section">
                            <h4 class="pdf-section-title">Monedas</h4>
                            <div class="pdf-section-subtitle">
                                Denominación
                            </div>

                            <table class="money-table">
                                <thead>
                                    <tr>
                                        <th style="width:27%"></th>
                                        <th style="width:25%">Cantidad</th>
                                        <th style="width:48%">Sub-total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($monedas as $detalle)
                                        <tr>
                                            <td>
                                                Q. {{ number_format(
                                                    (float)$detalle->denominacion,
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
                                                            (float)$detalle->subtotal,
                                                            2
                                                        ) }}
                                                    </strong>
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

                            <div class="section-total">
                                <span>Q.</span>
                                <span class="value">
                                    {{ number_format(
                                        (float)$arqueo->total_monedas,
                                        2
                                    ) }}
                                </span>
                            </div>

                            <div class="middle-total">
                                <span>Total Arqueado</span>
                                <span>Q.</span>
                                <span class="value">
                                    {{ number_format(
                                        (float)$arqueo->total_arqueado,
                                        2
                                    ) }}
                                </span>
                            </div>
                        </section>

                    </div>

                    <aside class="summary">
                        <div class="summary-row">
                            <span class="label">Saldo del Sistema</span>
                            <span>Q.</span>
                            <span class="value">
                                {{ number_format(
                                    (float)$arqueo->saldo_sistema,
                                    2
                                ) }}
                            </span>
                        </div>

                        <div class="summary-row">
                            <span class="label">Total Arqueado</span>
                            <span>Q.</span>
                            <span class="value">
                                {{ number_format(
                                    (float)$arqueo->total_arqueado,
                                    2
                                ) }}
                            </span>
                        </div>

                        <div class="summary-row difference
                            {{ (float)$arqueo->diferencia > 0 ? 'positive' : '' }}
                            {{ (float)$arqueo->diferencia < 0 ? 'negative' : '' }}"
                        >
                            <span class="label">DIFERENCIA</span>
                            <span>Q.</span>
                            <span class="value">
                                {{ number_format(
                                    (float)$arqueo->diferencia,
                                    2
                                ) }}
                            </span>
                        </div>
                    </aside>

                </div>

                <section class="text-section">
                    <label>Observaciones:</label>
                    <div class="lined-text">
                        {{ $arqueo->observaciones ?: 'Sin observaciones registradas.' }}
                    </div>
                </section>

                <section class="text-section">
                    <label>Calificación:</label>
                    <div class="lined-text">
                        {{ $arqueo->certificacion ?: 'Sin calificación registrada.' }}
                    </div>
                </section>

                <section class="signatures">

                    <div class="signature-box">
                        @if($firmaAgente)
                            <div class="signature-data">
                                <div>
                                    <strong>
                                        {{ $firmaAgente->nombres_historicos }}
                                        {{ $firmaAgente->apellidos_historicos }}
                                    </strong>
                                    <br>
                                    Firmado el
                                    {{ $firmaAgente->fecha_firma->format('d/m/Y H:i') }}

                                    <div class="signature-code">
                                        {{ $firmaAgente->firma_electronica }}
                                    </div>
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

                    <div class="signature-box">
                        @if($firmaPromotor)
                            <div class="signature-data">
                                <div>
                                    <strong>
                                        {{ $firmaPromotor->nombres_historicos }}
                                        {{ $firmaPromotor->apellidos_historicos }}
                                    </strong>
                                    <br>
                                    Firmado el
                                    {{ $firmaPromotor->fecha_firma->format('d/m/Y H:i') }}

                                    <div class="signature-code">
                                        {{ $firmaPromotor->firma_electronica }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="signature-data pending">
                                Firma electrónica pendiente
                            </div>
                        @endif

                        <div class="signature-line"></div>

                        <div class="signature-role">
                            Promotor Agentes MICOOPE
                        </div>
                    </div>

                    <div class="signature-box">
                        @if($firmaJefe)
                            <div class="signature-data">
                                <div>
                                    <strong>
                                        {{ $firmaJefe->nombres_historicos }}
                                        {{ $firmaJefe->apellidos_historicos }}
                                    </strong>
                                    <br>
                                    Firmado el
                                    {{ $firmaJefe->fecha_firma->format('d/m/Y H:i') }}

                                    <div class="signature-code">
                                        {{ $firmaJefe->firma_electronica }}
                                    </div>
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

                <div class="footer-actions">
                    <a
                        href="{{ route('promotor.arqueos.index') }}"
                        class="btn-secondary-custom"
                    >
                        Volver al Historial
                    </a>

                    <a
                        href="{{ route('promotor.arqueos.imprimir', $arqueo) }}"
                        target="_blank"
                        class="btn-primary-custom"
                    >
                        Imprimir PDF
                    </a>
                </div>

            </div>
        </section>
    </div>
</div>
@endsection
