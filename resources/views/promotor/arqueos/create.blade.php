@extends('layouts.promotor')

@section('title', 'Realizar Arqueo')
@section('module-title', 'Realizar Arqueo')

@push('styles')
<style>
    .page-actions,.form-actions{
        display:flex;
        align-items:center;
        gap:12px;flex-wrap:wrap
    }

    .btn-primary-custom,.btn-secondary-custom{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:42px;
        padding:0 18px;
        border-radius:11px;
        font-size:11px;
        font-weight:800;
        text-decoration:none;
        transition:.2s
    }

    .btn-primary-custom{
        border:0;
        background:linear-gradient(135deg,#164c96,#1c64b5);
        color:#fff;
        box-shadow:0 9px 18px rgba(22,76,150,.18);
        cursor:pointer
    }

    .btn-secondary-custom{
        border:1px solid #d5dfe5;
        background:#fff;
        color:#31536e
    }

    .btn-primary-custom:disabled{
        opacity:.65;
        cursor:not-allowed
    }

    .selection-panel,.form-sheet{
        border:1px solid #dfe7ee;
        border-radius:18px;
        background:#fff;
        box-shadow:0 8px 25px rgba(20,57,83,.05)
    }

    .selection-panel{
        max-width:760px;
        margin:0 auto;
        padding:25px
    }

    .selection-panel h3{
        margin:0;
        color:#0a3158;
        font-size:18px
    }

    .selection-panel p{
        margin:7px 0 20px;
        color:#7d8c97;
        font-size:12px;
        line-height:1.55
    }

    .field-group{
        margin-bottom:16px
    }

    .field-label{
        display:block;
        margin-bottom:7px;
        color:#345067;
        font-size:10px;
        font-weight:800;
        letter-spacing:.4px;
        text-transform:uppercase
    }

    .form-control-custom{
        width:100%;
        min-height:43px;
        padding:10px 12px;
        border:1px solid #ccd9e2;
        border-radius:10px;
        background:#fff;
        color:#17364f;
        font-size:12px;
        outline:none
    }

    .form-control-custom:focus{
        border-color:#6f9bc1;
        box-shadow:0 0 0 3px rgba(22,76,150,.09)
    }

    .form-control-custom[readonly]{
        background:#f7f9fb
    }

    textarea.form-control-custom{
        min-height:105px;
        resize:vertical
    }

    .sheet-header{
        display:grid;
        grid-template-columns:220px 1fr 220px;
        align-items:center;gap:20px;
        padding:24px 28px 20px;
        border-bottom:0
    }

    .sheet-logo{
        width:100%;
        max-width:205px;
        height:72px;
        object-fit:contain
    }

    .sheet-title{
        text-align:center
    }

    .sheet-title h3{
        margin:0;
        color:#0a3158;
        font-family:Georgia,serif;
        font-size:23px;
        text-transform:uppercase
    }

    .sheet-title p{
        margin:5px 0 0;
        color:#171717;
        font-family:Georgia,serif;
        font-size:14px;
        font-weight:700;
        text-transform:uppercase
    }

    .sheet-number{
        text-align:right
    }

    .sheet-number span{
        display:block;
        color:#6c7c88;
        font-size:9px;
        font-weight:800;
        text-transform:uppercase
    }

    .sheet-number strong{
        display:block;
        margin-top:5px;
        color:#a82f2b;
        font-family:Georgia,serif;
        font-size:13px
    }

    .sheet-body{
        padding:25px 28px 30px
    }

    .section-heading{
        margin:0 0 15px;
        padding-bottom:9px;
        border-bottom:1px solid #d9e2e8;
        color:#0a3158;
        font-size:14px;
        font-weight:850;
        text-transform:uppercase
    }

    .general-grid{
        display:grid;
        grid-template-columns:repeat(12,minmax(0,1fr));
        gap:15px 18px;
        margin-bottom:25px
    }

    .col-12{
        grid-column:span 12
    }

    .col-8{
        grid-column:span 8
    }

    .col-6{
        grid-column:span 6
    }

    .col-4{
        grid-column:span 4
    }

    .col-3{
        grid-column:span 3
    }

    .line-field{
        min-height:39px;
        padding:8px 3px 6px;
        border:0;
        border-bottom:1px solid #8ca0b0;
        border-radius:0;
        background:transparent;
        font-weight:700
    }

    .cash-grid{
        display:grid;
        grid-template-columns:minmax(0,1.15fr) minmax(0,.85fr);
        gap:20px;
        align-items:start
    }

    .denomination-grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:16px
    }

    .denomination-card,.totals-card{
        overflow:hidden;
        border:1px solid #d9e3e9;
        border-radius:14px;
        background:#fff
    }

    .denomination-card h4,.totals-card h4{
        margin:0;padding:14px 15px;
        border-bottom:1px solid #d9e3e9;
        background:#f4f7f9;
        color:#0a3158;
        font-size:13px;
        font-weight:850;
        text-transform:uppercase
    }

    .denomination-table{
        width:100%;
        border-collapse:collapse;
        table-layout:fixed
    }

    .denomination-table th{
        padding:10px 9px;
        border-bottom:1px solid #e4eaee;
        background:#fafcfd;
        color:#667988;
        font-size:9px;
        font-weight:850;
        text-align:left;
        text-transform:uppercase
    }

    .denomination-table td{
        padding:10px 9px;
        border-bottom:1px solid #edf1f4;
        color:#17364f;
        font-size:11px;
        font-weight:700
    }

    .quantity-input{
        width:100%;
        min-width:65px;
        min-height:38px;
        padding:8px;
        border:1px solid #ccd9e2;
        border-radius:9px;
        text-align:center;font-size:12px;
        font-weight:750
    }

    .subtotal-value{
        display:block;
        text-align:right;
        white-space:nowrap
    }

    .totals-body{
        padding:14px
    }

    .total-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        min-height:48px;
        padding:10px 0;
        border-bottom:1px solid #e6ecef
    }

    .total-row span{
        color:#506576;
        font-size:11px;
        font-weight:750
    }

    .total-row strong{
        color:#082d55;
        font-size:13px;
        white-space:nowrap
    }

    .total-row.highlight{
        margin:5px 0;
        padding:12px;
        border:1px solid #b8d0e3;
        border-radius:10px;
        background:#eef5fb
    }

    .difference-box{
        margin-top:15px;
        padding:17px 14px;
        border:1px solid #b8d0e3;
        border-radius:12px;
        background:#eef5fb;
        text-align:center
    }

    .difference-box span{
        display:block;
        color:#60778a;
        font-size:9px;
        font-weight:850;
        text-transform:uppercase
    }

    .difference-box strong{
        display:block;
        margin-top:7px;
        color:#0a3158;
        font-size:22px
    }

    .difference-box small{
        display:block;
        margin-top:5px;
        color:#60778a;
        font-size:9px;
        font-weight:800;
        text-transform:uppercase
    }

    .difference-box.positive{
        border-color:#aad8bd;
        background:#edf9f2
    }

    .difference-box.positive strong,.difference-box.positive small{
        color:#1d7b4e
    }

    .difference-box.negative{
        border-color:#e7b9b7;
        background:#fff1f0
    }
    .difference-box.negative strong,.difference-box.negative small{
        color:#b13c3c
    }

    .certification-section,.observations-section,.signatures-section{
        margin-top:25px
    }

    .certification-note{
        margin-top:10px;
        padding:12px 14px;
        border-left:4px solid #164c96;
        border-radius:8px;
        background:#f2f6fa;
        color:#496276;
        font-size:11px;
        line-height:1.55
    }

    .signatures-grid{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:18px
    }

    .signature-card{
        min-height:120px;
        padding:16px;
        border:1px solid #dfe7ee;
        border-radius:13px;
        background:#fafcfd;
        text-align:center
    }

    .signature-card span{
        display:block;
        color:#7b8b96;
        font-size:9px;
        font-weight:800;
        text-transform:uppercase
    }

    .signature-card strong{
        display:block;
        margin-top:12px;
        color:#17364f;
        font-size:12px
    }

    .signature-card p{
        margin:8px 0 0;
        color:#7b8b96;
        font-size:10px;
        line-height:1.45
    }

    .signature-card.current{
        border-color:#b6d8c4;
        background:#f0faf4
    }

    .signature-card.current strong{
        color:#1d7b4e
    }

    .form-actions{
        justify-content:flex-end;
        margin-top:28px;
        padding-top:20px;
        border-top:1px solid #e0e7eb
    }

    .error-list{
        margin:0;
        padding-left:18px
    }

    @media(max-width:1150px){
        .sheet-header{
            grid-template-columns:150px 1fr 145px
        }

        .cash-grid{
            grid-template-columns:1fr
        }
    }

    @media(max-width:850px){
        .sheet-header{
            grid-template-columns:1fr;
            text-align:center
        }

        .sheet-logo{
            margin:0 auto
        }

        .sheet-number{
            text-align:center
        }

        .denomination-grid,.signatures-grid{
            grid-template-columns:1fr
        }

        .col-8,.col-6,.col-4,.col-3{
            grid-column:span 12
        }
    }

    @media(max-width:650px){
        .sheet-body,.sheet-header{
            padding-left:16px;
            padding-right:16px
        }

        .page-actions{
            width:100%;
            margin-top:15px
        }

        .btn-primary-custom,.btn-secondary-custom{
            width:100%
        }

        .form-actions{
            flex-direction:column-reverse
        }
    }


    /* Homologación visual con PDF oficial del Promotor */
    .form-sheet{max-width:1220px;margin:0 auto;border:1px solid #d9e2e8;border-radius:18px;background:#fff}
    .sheet-header{padding:30px 34px 12px;align-items:start}
    .sheet-title h3{color:#171717;line-height:1.05}
    .sheet-number{padding-top:10px}
    .sheet-number strong{font-size:12px;letter-spacing:.3px}
    .sheet-body{position:relative;padding:8px 34px 30px}
    .sheet-body:before{content:"";position:absolute;z-index:0;top:180px;left:50%;width:360px;height:430px;transform:translateX(-50%);background:url("{{ asset('images/logos/agentes-micoope.png') }}") center/contain no-repeat;opacity:.035;pointer-events:none}
    .sheet-body>*{position:relative;z-index:1}
    .pdf-general{margin:0 0 20px;font-family:Arial,sans-serif}
    .pdf-general-row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:10px}
    .pdf-general-row.one{display:flex;align-items:flex-end;gap:8px}
    .pdf-general-row.one label,.pdf-inline span{font-size:10px;color:#222;white-space:nowrap}
    .pdf-inline{display:flex;align-items:flex-end;gap:7px}
    .pdf-inline strong{flex:1;min-height:24px;padding:5px 4px 3px;border-bottom:1px solid #555;color:#111;font-size:11px;text-align:center}
    .pdf-owner{flex:1;height:25px;padding:4px;border:0;border-bottom:1px solid #555;background:transparent;font-size:11px;font-weight:700;outline:none;text-align:center}
    .cash-grid{grid-template-columns:minmax(0,1.25fr) minmax(260px,.45fr);gap:42px}
    .denomination-grid{grid-template-columns:1fr;gap:20px}
    .denomination-card,.totals-card{border:0;border-radius:0;background:transparent;box-shadow:none}
    .denomination-card h4,.totals-card h4{padding:0 0 5px;border:0;background:transparent;color:#171717;font-family:Georgia,serif;font-size:17px;text-transform:none}
    .denomination-table th{padding:5px 9px;background:transparent;border:0;color:#222;font-size:10px;text-transform:none;text-align:center}
    .denomination-table th:first-child{text-align:left}
    .denomination-table td{padding:4px 9px;border:0;color:#222;font-size:11px}
    .quantity-input{height:25px;min-height:25px;padding:2px 5px;border:0;border-bottom:1px solid #555;border-radius:0;background:transparent}
    .subtotal-value{padding-bottom:3px;border-bottom:1px solid #555}
    .totals-body{padding:30px 0 0}
    .total-row{min-height:38px;padding:7px 0;border:0}
    .total-row span{color:#222;font-size:11px}
    .total-row strong{min-width:115px;padding:0 3px 3px;border-bottom:1px solid #555;color:#111;font-size:11px;text-align:right}
    .total-row.highlight{margin:0;padding:7px 0;border:0;border-radius:0;background:transparent}
    #saldoSistema{min-height:28px;padding:3px;border:0;border-bottom:1px solid #555;border-radius:0;background:transparent;text-align:right}
    .difference-box{margin-top:12px;padding:8px 0;border:0;border-radius:0;background:transparent;text-align:right}
    .difference-box span{color:#222;font-size:10px}
    .difference-box strong{display:inline-block;min-width:115px;margin:5px 0 0;padding:0 3px 3px;border-bottom:1px solid #555;color:#111;font-size:12px}
    .difference-box small{font-size:8px}
    .certification-section,.observations-section{margin-top:20px}
    .certification-section .section-heading,.observations-section .section-heading{margin:0 0 4px;padding:0;border:0;color:#171717;font-family:Arial,sans-serif;font-size:10px;text-transform:none}
    .certification-section .section-heading{font-size:0}
    .certification-section .section-heading:after{content:"Calificación";font-size:10px}
    .certification-section textarea,.observations-section textarea{min-height:58px;padding:4px 3px;border:0;border-bottom:1px solid #555;border-radius:0;background:repeating-linear-gradient(to bottom,transparent 0,transparent 22px,#777 23px);font-size:10px;line-height:23px}
    .certification-note{padding:5px 0;border:0;background:transparent;font-size:9px}
    .signatures-section .section-heading{display:none}
    .signatures-grid{gap:28px}
    .signature-card{min-height:85px;padding:10px 5px 0;border:0;border-radius:0;background:transparent;display:flex;flex-direction:column;justify-content:flex-end}
    .signature-card.current{border:0;background:transparent}
    .signature-card:after{content:"";display:block;order:2;border-bottom:1px solid #444}
    .signature-card span{order:0;color:#777;font-size:8px}
    .signature-card strong{order:3;margin-top:5px;color:#111;font-size:9px}
    .signature-card p{order:1;min-height:28px;margin:5px 0;color:#777;font-size:8px}

</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">
        <h2>Realizar Arqueo</h2>
        <p>Registre un arqueo de visita para uno de sus agentes asignados.</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('promotor.arqueos.index') }}" class="btn-secondary-custom">
            Ver Historial
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert-message warning">
        <ul class="error-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (! $agenteSeleccionado)
    <section class="selection-panel">
        <h3>Seleccione el agente</h3>
        <p>La hora de inicio se registrará cuando seleccione el agente y abra el formulario.</p>

        <form method="GET" action="{{ route('promotor.arqueos.create') }}">
            <div class="field-group">
                <label for="agente_id" class="field-label">Agente asignado</label>

                <select name="agente_id" id="agente_id" class="form-control-custom" required>
                    <option value="">Seleccione un agente</option>

                    @foreach ($agentes as $agente)
                        <option value="{{ $agente->id }}">
                            {{ $agente->codigo_agente }}
                            — {{ $agente->nombre_negocio }}
                            — {{ $agente->ruta?->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-primary-custom">
                Iniciar Arqueo
            </button>
        </form>
    </section>
@else
    <form id="formArqueo" method="POST" action="{{ route('promotor.arqueos.store') }}" autocomplete="off">
        @csrf

        <input type="hidden" name="agente_id" value="{{ $agenteSeleccionado->id }}">
        <input type="hidden" name="detalle" id="detalleInput">

        @php
            $billetes = [200,100,50,20,10,5,1];
            $monedas = [1,0.50,0.25,0.10,0.05];
        @endphp

        <section class="form-sheet">
            <header class="sheet-header">
                <img src="{{ asset('images/logos/ecosaba.png') }}" alt="ECOSABA MICOOPE" class="sheet-logo">

                <div class="sheet-title">
                    <h3>Arqueo y Corte de Caja</h3>
                    <p>Agentes MICOOPE</p>
                </div>

                <div class="sheet-number">
                    <strong>N.º SE GENERA AL FINALIZAR</strong>
                </div>
            </header>

            <div class="sheet-body">
                <div class="pdf-general">
                    <div class="pdf-general-row">
                        <div class="pdf-inline"><span>Fecha:</span><strong>{{ now()->format('d/m/Y') }}</strong></div>
                        <div class="pdf-inline"><span>Hora inicio:</span><strong>{{ $horaInicio?->format('H:i') }}</strong></div>
                        <div class="pdf-inline"><span>Hora finalización:</span><strong>Se registra al finalizar</strong></div>
                    </div>
                    <div class="pdf-general-row">
                        <div class="pdf-inline"><span>Agente No.:</span><strong>{{ $agenteSeleccionado->codigo_agente }}</strong></div>
                        <div class="pdf-inline"><span>Nombre Negocio:</span><strong>{{ $agenteSeleccionado->nombre_negocio }}</strong></div>
                        <div class="pdf-inline"><span>Agente MICOOPE:</span><strong>{{ $agenteSeleccionado->nombre_negocio }}</strong></div>
                    </div>
                    <div class="pdf-general-row one">
                        <label>Nombre del Propietario o Receptor Pagador:</label>
                        <input type="text" name="nombre_propietario" id="nombre_propietario" class="pdf-owner" maxlength="150"
                               value="{{ old('nombre_propietario',$agenteSeleccionado->nombre_propietario) }}" required>
                    </div>
                </div>

                <div class="cash-grid">
                    <div class="denomination-grid">
                        <article class="denomination-card">
                            <h4>Billetes</h4>
                            <table class="denomination-table">
                                <thead>
                                    <tr><th>Denominación</th><th>Cantidad</th><th>Subtotal</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($billetes as $valor)
                                        <tr>
                                            <td>Q {{ number_format($valor,2) }}</td>
                                            <td>
                                                <input type="number" min="0" step="1" value="0" class="quantity-input cantidad" data-tipo="BILLETE" data-valor="{{ $valor }}">
                                            </td>
                                            <td><span class="subtotal-value">Q <span class="subtotal">0.00</span></span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </article>

                        <article class="denomination-card">
                            <h4>Monedas</h4>
                            <table class="denomination-table">
                                <thead>
                                    <tr><th>Denominación</th><th>Cantidad</th><th>Subtotal</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($monedas as $valor)
                                        <tr>
                                            <td>Q {{ number_format($valor,2) }}</td>
                                            <td>
                                                <input type="number" min="0" step="1" value="0" class="quantity-input cantidad" data-tipo="MONEDA" data-valor="{{ $valor }}">
                                            </td>
                                            <td><span class="subtotal-value">Q <span class="subtotal">0.00</span></span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </article>
                    </div>

                    <aside class="totals-card">
                        <h4>Totales</h4>

                        <div class="totals-body">
                            <div class="total-row">
                                <span>Total billetes</span>
                                <strong id="totalBilletes">Q 0.00</strong>
                            </div>

                            <div class="total-row">
                                <span>Total monedas</span>
                                <strong id="totalMonedas">Q 0.00</strong>
                            </div>

                            <div class="total-row highlight">
                                <span>Total arqueado</span>
                                <strong id="totalArqueado">Q 0.00</strong>
                            </div>

                            <div class="field-group">
                                <label for="saldoSistema" class="field-label">Saldo del sistema</label>
                                <input type="number" name="saldo_sistema" id="saldoSistema" class="form-control-custom" min="0" step="0.01" value="{{ old('saldo_sistema','0.00') }}" required>
                            </div>

                            <div class="difference-box" id="differenceBox">
                                <span>Diferencia</span>
                                <strong id="diferencia">Q 0.00</strong>
                                <small id="estadoDiferencia">Cuadrado</small>
                            </div>
                        </div>
                    </aside>
                </div>

                <section class="certification-section">
                    <h4 class="section-heading">Certificación</h4>

                    <textarea name="certificacion" id="certificacion" class="form-control-custom" maxlength="2000" required>{{ old('certificacion','Con el presente arqueo de caja se deja constancia de que los valores registrados corresponden al efectivo contado y verificado al momento de realizar el arqueo.') }}</textarea>

                    <div class="certification-note">
                        Al finalizar, el arqueo quedará firmado electrónicamente por el Promotor y pendiente de validación del Agente y certificación del Jefe de Agentes.
                    </div>
                </section>

                <section class="observations-section">
                    <h4 class="section-heading">Observaciones</h4>
                    <textarea name="observaciones" class="form-control-custom" maxlength="1000" placeholder="Ingrese observaciones relacionadas con el arqueo, sobrantes o faltantes.">{{ old('observaciones') }}</textarea>
                </section>

                <section class="signatures-section">
                    <h4 class="section-heading">Firmas Electrónicas</h4>

                    <div class="signatures-grid">
                        <article class="signature-card current">
                            <span>Realizador</span>
                            <strong>Promotor Agentes MICOOPE</strong>
                            <p>La firma electrónica se registrará automáticamente al finalizar.</p>
                        </article>

                        <article class="signature-card">
                            <span>Validador</span>
                            <strong>Propietario o Receptor Pagador</strong>
                            <p>Quedará pendiente de firma electrónica del agente.</p>
                        </article>

                        <article class="signature-card">
                            <span>Certificador</span>
                            <strong>Jefe de Agentes MICOOPE</strong>
                            <p>Realizará la certificación final del arqueo.</p>
                        </article>
                    </div>
                </section>

                <div class="form-actions">
                    <a href="{{ route('promotor.arqueos.create') }}" class="btn-secondary-custom">Cambiar Agente</a>
                    <button type="submit" class="btn-primary-custom" id="submitButton">Finalizar y Firmar Arqueo</button>
                </div>
            </div>
        </section>
    </form>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formArqueo');
    if (!form) return;

    const inputs = document.querySelectorAll('.cantidad');
    const saldo = document.getElementById('saldoSistema');
    const totalBilletes = document.getElementById('totalBilletes');
    const totalMonedas = document.getElementById('totalMonedas');
    const totalArqueado = document.getElementById('totalArqueado');
    const diferencia = document.getElementById('diferencia');
    const box = document.getElementById('differenceBox');
    const estado = document.getElementById('estadoDiferencia');
    const detalle = document.getElementById('detalleInput');
    const submit = document.getElementById('submitButton');

    function moneda(valor) {
        return 'Q ' + Number(valor).toLocaleString('es-GT',{
            minimumFractionDigits:2,
            maximumFractionDigits:2
        });
    }

    function recalcular() {
        let billetes = 0;
        let monedas = 0;

        inputs.forEach(function (input) {
            const cantidad = Math.max(0, parseInt(input.value || '0',10) || 0);
            const valor = Number(input.dataset.valor);
            const subtotal = cantidad * valor;

            input.value = cantidad;
            input.closest('tr').querySelector('.subtotal').textContent =
                subtotal.toLocaleString('es-GT',{
                    minimumFractionDigits:2,
                    maximumFractionDigits:2
                });

            if (input.dataset.tipo === 'BILLETE') billetes += subtotal;
            else monedas += subtotal;
        });

        const arqueado = billetes + monedas;
        const saldoSistema = Number(saldo.value || 0);
        const diferenciaCalculada = arqueado - saldoSistema;

        totalBilletes.textContent = moneda(billetes);
        totalMonedas.textContent = moneda(monedas);
        totalArqueado.textContent = moneda(arqueado);
        diferencia.textContent = moneda(diferenciaCalculada);

        box.classList.remove('positive','negative');

        if (diferenciaCalculada > 0.004) {
            box.classList.add('positive');
            estado.textContent = 'Sobrante';
        } else if (diferenciaCalculada < -0.004) {
            box.classList.add('negative');
            estado.textContent = 'Faltante';
        } else {
            estado.textContent = 'Cuadrado';
        }
    }

    inputs.forEach(input => input.addEventListener('input',recalcular));
    saldo.addEventListener('input',recalcular);

    form.addEventListener('submit',function () {
        const datos = [];

        inputs.forEach(function (input) {
            datos.push({
                tipo: input.dataset.tipo,
                denominacion: Number(input.dataset.valor),
                cantidad: Math.max(0,parseInt(input.value || '0',10) || 0)
            });
        });

        detalle.value = JSON.stringify(datos);
        submit.disabled = true;
        submit.textContent = 'Registrando Arqueo...';
    });

    recalcular();
});
</script>
@endpush
