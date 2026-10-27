@extends('layouts.auditoria')

@section('title', 'Realizar Arqueo')
@section('module-title', 'Realizar Arqueo')

@push('styles')
<style>
    .page-actions,.form-actions{
        display:flex;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
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
        transition:.2s;
    }

    .btn-primary-custom{
        border:0;
        background:linear-gradient(135deg,#164c96,#1c64b5);
        color:#fff;
        box-shadow:0 9px 18px rgba(22,76,150,.18);
        cursor:pointer;
    }

    .btn-secondary-custom{
        border:1px solid #d5dfe5;
        background:#fff;
        color:#31536e;
    }

    .btn-primary-custom:disabled{
        opacity:.65;
        cursor:not-allowed;
    }

    .form-sheet{
        max-width:1220px;
        margin:0 auto;
        overflow:hidden;
        border:1px solid #d9e2e8;
        border-radius:18px;
        background:#fff;
        box-shadow:0 8px 25px rgba(20,57,83,.05);
    }

    .sheet-header{
        display:grid;
        grid-template-columns:220px 1fr 220px;
        align-items:start;
        gap:20px;
        padding:30px 34px 12px;
    }

    .sheet-logo{
        width:100%;
        max-width:205px;
        height:72px;
        object-fit:contain;
    }

    .sheet-title{
        text-align:center;
    }

    .sheet-title h3{
        margin:0;
        color:#171717;
        font-family:Georgia,serif;
        font-size:23px;
        line-height:1.05;
        text-transform:uppercase;
    }

    .sheet-title p{
        margin:5px 0 0;
        color:#171717;
        font-family:Georgia,serif;
        font-size:14px;
        font-weight:700;
        text-transform:uppercase;
    }

    .sheet-number{
        padding-top:10px;
        text-align:right;
    }

    .sheet-number span{
        display:block;
        color:#6c7c88;
        font-size:9px;
        font-weight:800;
        text-transform:uppercase;
    }

    .sheet-number strong{
        display:block;
        margin-top:5px;
        color:#a82f2b;
        font-family:Georgia,serif;
        font-size:12px;
        letter-spacing:.3px;
    }

    .sheet-body{
        position:relative;
        padding:8px 34px 30px;
    }

    .sheet-body:before{
        content:"";
        position:absolute;
        z-index:0;
        top:180px;
        left:50%;
        width:360px;
        height:430px;
        transform:translateX(-50%);
        background:url("{{ asset('images/logos/agentes-micoope.png') }}") center/contain no-repeat;
        opacity:.035;
        pointer-events:none;
    }

    .sheet-body>*{
        position:relative;
        z-index:1;
    }

    .agent-selector{
        margin-bottom:22px;
        padding:14px 16px;
        border:1px solid #d9e3e9;
        border-radius:13px;
        background:#f8fafb;
    }

    .field-label{
        display:block;
        margin-bottom:7px;
        color:#345067;
        font-size:10px;
        font-weight:800;
        letter-spacing:.4px;
        text-transform:uppercase;
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
        outline:none;
        box-sizing:border-box;
    }

    .form-control-custom:focus{
        border-color:#6f9bc1;
        box-shadow:0 0 0 3px rgba(22,76,150,.09);
    }

    .pdf-general{
        margin:0 0 22px;
        font-family:Arial,sans-serif;
    }

    .pdf-general-row{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:16px;
        margin-bottom:10px;
    }

    .pdf-inline{
        display:flex;
        align-items:flex-end;
        gap:7px;
    }

    .pdf-inline span{
        color:#222;
        font-size:10px;
        white-space:nowrap;
    }

    .pdf-inline strong{
        flex:1;
        min-height:24px;
        padding:5px 4px 3px;
        border-bottom:1px solid #555;
        color:#111;
        font-size:11px;
        text-align:center;
    }

   .cash-grid{
        display:grid;
        grid-template-columns:minmax(0,1.25fr) minmax(260px,.45fr);
        gap:42px;
        align-items:start;
    }

    .denomination-grid{
        display:grid;
        grid-template-columns:1fr;
        gap:20px;
    }

    .denomination-card,.totals-card{
        border:0;
        background:transparent;
    }

    .denomination-card h4,.totals-card h4{
        margin:0;
        padding:0 0 5px;
        color:#171717;
        font-family:Georgia,serif;
        font-size:17px;
        font-weight:850;
    }

    .denomination-table{
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
    }

    .denomination-table th{
        padding:5px 9px;
        border:0;
        background:transparent;
        color:#222;
        font-size:10px;
        font-weight:850;
        text-align:center;
    }

    .denomination-table th:first-child{
        text-align:left;
    }

    .denomination-table td{
        padding:4px 9px;
        border:0;
        color:#222;
        font-size:11px;
        font-weight:700;
    }

    .quantity-input{
        width:100%;
        min-width:65px;
        height:25px;
        min-height:25px;
        padding:2px 5px;
        border:0;
        border-bottom:1px solid #555;
        border-radius:0;
        background:transparent;
        text-align:center;
        font-size:12px;
        font-weight:750;
        outline:none;
        box-sizing:border-box;
    }

    .subtotal-value{
        display:block;
        padding-bottom:3px;
        border-bottom:1px solid #555;
        text-align:right;
        white-space:nowrap;
    }

    .totals-body{
        padding:30px 0 0;
    }

    .total-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        min-height:38px;
        padding:7px 0;
    }

    .total-row span{
        color:#222;
        font-size:11px;
        font-weight:750;
    }

    .total-row strong{
        min-width:115px;
        padding:0 3px 3px;
        border-bottom:1px solid #555;
        color:#111;
        font-size:11px;
        text-align:right;
        white-space:nowrap;
    }

    .total-row\.highlight strong{
        font-weight:900;
    }

    .saldo-group{
        margin-top:10px;
    }

    #saldo_sistema{
        min-height:28px;
        padding:3px;
        border:0;
        border-bottom:1px solid #555;
        border-radius:0;
        background:transparent;
        text-align:right;
    }

    .difference-box{
        margin-top:12px;
        padding:8px 0;
        text-align:right;
    }

    .difference-box span{
        display:block;
        color:#222;
        font-size:10px;
        font-weight:850;
        text-transform:uppercase;
    }

    .difference-box strong{
        display:inline-block;
        min-width:115px;
        margin:5px 0 0;
        padding:0 3px 3px;
        border-bottom:1px solid #555;
        color:#111;
        font-size:12px;
    }

    .difference-box small{
        display:block;
        margin-top:5px;
        color:#60778a;
        font-size:8px;
        font-weight:800;
        text-transform:uppercase;
    }

    .difference-box.positive strong,
    .difference-box.positive small{
        color:#1d7b4e;
    }

    .difference-box.negative strong,
    .difference-box.negative small{
        color:#b13c3c;
    }

    .certification-section,.observations-section,.signatures-section{
        margin-top:22px;
    }

    .section-heading{
        margin:0 0 4px;
        color:#171717;
        font-family:Arial,sans-serif;
        font-size:10px;
        font-weight:850;
    }

    textarea.form-control-custom{
        min-height:70px;
        padding:4px 3px;
        border:0;
        border-bottom:1px solid #555;
        border-radius:0;
        background:repeating-linear-gradient(to bottom,transparent 0,transparent 22px,#777 23px);
        font-size:10px;
        line-height:23px;
        resize:vertical;
    }

    .certification-note{
        margin-top:8px;
        color:#60778a;
        font-size:9px;
        line-height:1.5;
    }
    .audit-note{
        margin-top:13px;
        padding:10px 12px;
        border-left:4px solid #164c96;
        border-radius:7px;
        background:#f2f6fa;
        color:#496276;
        font-size:10px;
        line-height:1.55;
    }

    .signatures-section .section-heading{
        display:none;
    }

    .signatures-grid{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:28px;
        max-width:100%;
        margin:0 auto;
    }

    .signature-card{
        min-height:85px;
        padding:10px 5px 0;
        display:flex;
        flex-direction:column;
        justify-content:flex-end;
        text-align:center;
    }

    .signature-card:after{
        content:"";
        display:block;
        order:2;
        border-bottom:1px solid #444;
    }

    .signature-card span{
        order:0;
        color:#777;
        font-size:8px;
        font-weight:800;
        text-transform:uppercase;
    }

    .signature-card strong{
        order:3;
        display:block;
        margin-top:5px;
        color:#111;
        font-size:9px;
    }

    .signature-card p{
        order:1;
        min-height:28px;
        margin:5px 0;
        color:#777;
        font-size:8px;
    }

    .form-actions{
        justify-content:flex-end;
        margin-top:28px;
        padding-top:20px;
        border-top:1px solid #e0e7eb;
    }

    .error-list{
        margin:0;
        padding-left:18px;
    }

    @media(max-width:1150px){
        .sheet-header{grid-template-columns:150px 1fr 145px}
        .cash-grid{grid-template-columns:1fr}
    }

    @media(max-width:850px){
        .sheet-header{grid-template-columns:1fr;text-align:center}
        .sheet-logo{margin:0 auto}
        .sheet-number{text-align:center}
        .pdf-general-row,.signatures-grid{grid-template-columns:1fr}
    }

    @media(max-width:650px){
        .sheet-body,.sheet-header{padding-left:16px;padding-right:16px}
        .page-actions{width:100%;margin-top:15px}
        .btn-primary-custom,.btn-secondary-custom{width:100%}
        .form-actions{flex-direction:column-reverse}
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h2>Realizar Arqueo de Auditoría</h2>
        <p>Registre el efectivo encontrado durante la verificación del Agente MICOOPE.</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('auditoria.arqueos.index') }}" class="btn-secondary-custom">
            Ver Historial
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert-message warning">
        <ul class="error-list">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('auditoria.arqueos.store') }}" id="arqueoForm" autocomplete="off">
    @csrf

    <section class="form-sheet">
        <header class="sheet-header">
            <img src="{{ asset('images/logos/ecosaba.png') }}" alt="ECOSABA MICOOPE" class="sheet-logo">

            <div class="sheet-title">
                <h3>Arqueo y Corte de Caja</h3>
                <p>Agentes MICOOPE</p>
            </div>

            <div class="sheet-number">
                <span>Auditoría</span>
                <strong>N.º SE GENERA AL FINALIZAR</strong>
            </div>
        </header>

        <div class="sheet-body">
            <div class="agent-selector">
                <label for="agente_id" class="field-label">Agente a auditar</label>

                <select name="agente_id" id="agente_id" class="form-control-custom" required>
                    <option value="">Seleccione un Agente</option>

                    @foreach($agentes as $agente)
                        <option
                            value="{{ $agente->id }}"
                            data-codigo="{{ $agente->codigo_agente }}"
                            data-negocio="{{ $agente->nombre_negocio }}"
                            data-propietario="{{ $agente->nombre_propietario }}"
                            data-direccion="{{ $agente->direccion }}"
                            data-ruta="{{ $agente->ruta_codigo }} - {{ $agente->ruta_nombre }}"
                            data-region="{{ $agente->region_nombre }}"
                            @selected(old('agente_id') == $agente->id)
                        >
                            {{ $agente->codigo_agente }}
                            — {{ $agente->nombre_negocio }}
                            — {{ $agente->region_nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pdf-general">
                <div class="pdf-general-row">
                    <div class="pdf-inline">
                        <span>Fecha:</span>
                        <strong>{{ now()->format('d/m/Y') }}</strong>
                    </div>

                    <div class="pdf-inline">
                        <span>Hora inicio:</span>
                        <strong>{{ now()->format('H:i') }}</strong>
                    </div>

                    <div class="pdf-inline">
                        <span>Hora finalización:</span>
                        <strong>Se registra al finalizar</strong>
                    </div>
                </div>

                <div class="pdf-general-row">
                    <div class="pdf-inline">
                        <span>Agente No.:</span>
                        <strong id="codigoAgente">—</strong>
                    </div>

                    <div class="pdf-inline">
                        <span>Nombre Negocio:</span>
                        <strong id="negocioAgente">—</strong>
                    </div>

                    <div class="pdf-inline">
                        <span>Propietario:</span>
                        <strong id="propietario">—</strong>
                    </div>
                </div>

                <div class="pdf-general-row">
                    <div class="pdf-inline">
                        <span>Región:</span>
                        <strong id="region">—</strong>
                    </div>

                    <div class="pdf-inline">
                        <span>Ruta:</span>
                        <strong id="ruta">—</strong>
                    </div>

                    <div class="pdf-inline">
                        <span>Dirección:</span>
                        <strong id="direccion">—</strong>
                    </div>
                </div>
            </div>

            <div class="cash-grid">
                <div class="denomination-grid">
                    <article class="denomination-card">
                        <h4>Billetes</h4>

                        <table class="denomination-table">
                            <thead>
                                <tr>
                                    <th>Denominación</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($billetes as $denominacion)
                                    @php
                                        $clave = (string) $denominacion;
                                    @endphp

                                    <tr>
                                        <td>Q {{ number_format($denominacion,2) }}</td>

                                        <td>
                                            <input
                                                type="number"
                                                min="0"
                                                step="1"
                                                name="billetes[{{ $clave }}]"
                                                value="{{ old('billetes.' . $clave,0) }}"
                                                class="quantity-input cantidad"
                                                data-denominacion="{{ $denominacion }}"
                                                data-tipo="billete"
                                            >
                                        </td>

                                        <td>
                                            <span class="subtotal-value">
                                                Q <span class="subtotal">0.00</span>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </article>

                    <article class="denomination-card">
                        <h4>Monedas</h4>

                        <table class="denomination-table">
                            <thead>
                                <tr>
                                    <th>Denominación</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($monedas as $denominacion)
                                    @php
                                        $clave = number_format($denominacion,2,'.','');
                                    @endphp

                                    <tr>
                                        <td>Q {{ number_format($denominacion,2) }}</td>

                                        <td>
                                            <input
                                                type="number"
                                                min="0"
                                                step="1"
                                                name="monedas[{{ $clave }}]"
                                                value="{{ old('monedas.' . $clave,0) }}"
                                                class="quantity-input cantidad"
                                                data-denominacion="{{ $denominacion }}"
                                                data-tipo="moneda"
                                            >
                                        </td>

                                        <td>
                                            <span class="subtotal-value">
                                                Q <span class="subtotal">0.00</span>
                                            </span>
                                        </td>
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

                        <div class="saldo-group">
                            <label for="saldo_sistema" class="field-label">
                                Saldo del sistema
                            </label>

                            <input
                                type="number"
                                name="saldo_sistema"
                                id="saldo_sistema"
                                class="form-control-custom"
                                step="0.01"
                                min="0"
                                value="{{ old('saldo_sistema','0.00') }}"
                                required
                            >
                        </div>

                        <div class="difference-box" id="differenceBox">
                            <span>Diferencia</span>
                            <strong id="diferencia">Q 0.00</strong>
                            <small id="resultado">Exacto</small>
                        </div>
                    </div>
                </aside>
            </div>

            <section class="certification-section">

                <h4 class="section-heading">Certificación</h4>


               <textarea

                    name="certificacion"
                    id="certificacion"
                    class="form-control-custom"
                    maxlength="2000"
                    required

                >{{ old('certificacion','Con el presente arqueo de caja se deja constancia de que los valores registrados corresponden al efectivo contado y verificado al momento de realizar el arqueo.') }}</textarea>

                <div class="certification-note">

                    Al finalizar, el arqueo quedará firmado electrónicamente por Auditoría y pendiente de validación del Agente y certificación del Jefe de Agentes.

                </div>

            </section>


           <section class="observations-section">
                <h4 class="section-heading">Observaciones</h4>

                <textarea
                    name="observaciones"
                    class="form-control-custom"
                    maxlength="1000"
                    placeholder="Ingrese observaciones relacionadas con la auditoría, sobrantes, faltantes o cualquier hallazgo relevante."
                >{{ old('observaciones') }}</textarea>

                <div class="audit-note">
                    Este arqueo será registrado como una visita de Auditoría.
                    Los valores ingresados deben corresponder al efectivo contado y
                    verificado físicamente durante la revisión. Al finalizar, quedará
                    pendiente de validación del Agente y certificación del Jefe de Agentes MICOOPE.
                </div>
            </section>

            <section class="signatures-section">
                <h4 class="section-heading">Firmas Electrónicas</h4>

                <div class="signatures-grid">
                    <article class="signature-card">
                        <span>Realizador</span>
                        <strong>Auditoría MICOOPE</strong>
                        <p>
                            La identificación del auditor quedará registrada
                            electrónicamente al finalizar.
                        </p>
                    </article>

                    <article class="signature-card">
                        <span>Validador</span>
                        <strong>Propietario o Receptor Pagador</strong>
                        <p>
                            Quedará pendiente de validación electrónica
                            por parte del Agente MICOOPE auditado.
                        </p>
                    </article>

                    <article class="signature-card">
                        <span>Certificador</span>
                        <strong>Jefe de Agentes MICOOPE</strong>
                        <p>
                            Realizará la certificación final del arqueo
                            después de la validación del agente.
                        </p>
                    </article>
                </div>
            </section>
            <div class="form-actions">
                <a href="{{ route('auditoria.arqueos.index') }}" class="btn-secondary-custom">
                    Cancelar
                </a>

                <button type="submit" class="btn-primary-custom" id="submitButton">
                    Finalizar Arqueo de Auditoría
                </button>
            </div>
        </div>
    </section>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('arqueoForm');
    const agente = document.getElementById('agente_id');
    const cantidades = document.querySelectorAll('.cantidad');
    const saldo = document.getElementById('saldo_sistema');
    const submit = document.getElementById('submitButton');
    const box = document.getElementById('differenceBox');

    function actualizarAgente() {
        const option = agente.options[agente.selectedIndex];
        const data = option && option.value ? option.dataset : {};

        document.getElementById('codigoAgente').textContent =
            data.codigo || '—';

        document.getElementById('negocioAgente').textContent =
            data.negocio || '—';

        document.getElementById('propietario').textContent =
            data.propietario || '—';

        document.getElementById('region').textContent =
            data.region || '—';

        document.getElementById('ruta').textContent =
            data.ruta || '—';

        document.getElementById('direccion').textContent =
            data.direccion || '—';
    }

    function moneda(valor) {
        return 'Q ' + Number(valor || 0).toLocaleString('es-GT',{
            minimumFractionDigits:2,
            maximumFractionDigits:2
        });
    }

    function recalcular() {
        let totalBilletes = 0;
        let totalMonedas = 0;

        cantidades.forEach(function (input) {
            const cantidad = Math.max(
                0,
                parseInt(input.value || '0',10) || 0
            );

            const denominacion =
                Number(input.dataset.denominacion || 0);

            const subtotal =
                cantidad * denominacion;

            input.value = cantidad;

            const subtotalNode =
                input.closest('tr').querySelector('.subtotal');

            if (subtotalNode) {
                subtotalNode.textContent =
                    subtotal.toLocaleString('es-GT',{
                        minimumFractionDigits:2,
                        maximumFractionDigits:2
                    });
            }

            if (input.dataset.tipo === 'billete') {
                totalBilletes += subtotal;
            } else {
                totalMonedas += subtotal;
            }
        });

        const totalArqueado =
            totalBilletes + totalMonedas;

        const saldoSistema =
            Number(saldo.value || 0);

        const diferencia =
            totalArqueado - saldoSistema;

        document.getElementById('totalBilletes').textContent =
            moneda(totalBilletes);

        document.getElementById('totalMonedas').textContent =
            moneda(totalMonedas);

        document.getElementById('totalArqueado').textContent =
            moneda(totalArqueado);

        document.getElementById('diferencia').textContent =
            moneda(diferencia);

        box.classList.remove('positive','negative');

        if (diferencia > 0.004) {
            box.classList.add('positive');
            document.getElementById('resultado').textContent =
                'Sobrante';
        } else if (diferencia < -0.004) {
            box.classList.add('negative');
            document.getElementById('resultado').textContent =
                'Faltante';
        } else {
            document.getElementById('resultado').textContent =
                'Exacto';
        }
    }

    agente.addEventListener('change',actualizarAgente);

    cantidades.forEach(function (input) {
        input.addEventListener('input',recalcular);
    });

    saldo.addEventListener('input',recalcular);

    form.addEventListener('submit',function () {
        submit.disabled = true;
        submit.textContent = 'Registrando Arqueo...';
    });

    actualizarAgente();
    recalcular();
});
</script>
@endpush
