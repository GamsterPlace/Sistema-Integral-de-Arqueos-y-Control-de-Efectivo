
@extends('layouts.receptor')

@section('title', 'Nuevo arqueo diario')

@push('styles')
<style>
.ec-arqueo{max-width:1120px;margin:0 auto 35px;color:#203447;font-family:Arial,Helvetica,sans-serif}
.ec-arqueo *{box-sizing:border-box}
.ec-page-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:22px}
.ec-page-head h1{margin:0 0 6px;font-size:26px;color:#123d67}
.ec-page-head p{margin:0;color:#6a8093;font-size:13px}
.ec-paper{background:#fff;border:1px solid #d4e0ea;border-radius:14px;box-shadow:0 12px 36px rgba(15,45,75,.08);overflow:hidden}
.ec-paper-header{display:grid;grid-template-columns:180px 1fr 145px;align-items:center;gap:20px;padding:27px 30px;border-bottom:2px solid #dce5ed}
.ec-logo{max-width:175px;max-height:75px;object-fit:contain}
.ec-title{text-align:center}
.ec-title h2{margin:0;color:#163f68;font-size:25px;text-transform:uppercase;letter-spacing:.3px}
.ec-title p{margin:8px 0 0;font-size:15px;font-weight:800;color:#40586e;text-transform:uppercase}
.ec-number{text-align:right;color:#8292a1;font-size:11px;font-weight:800}
.ec-number strong{display:block;margin-top:8px;color:#a84a4a;font-size:16px}
.ec-body{padding:28px 34px 34px}
.ec-section{margin-bottom:27px}
.ec-section h3{font-size:16px;color:#173f67;margin:0 0 13px;padding-bottom:9px;border-bottom:1px solid #d9e2ea}
.ec-info{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:16px 20px}
.ec-info-item{grid-column:span 3;min-width:0}
.ec-info-item.wide{grid-column:span 6}
.ec-info-item label{display:block;font-size:11px;font-weight:800;color:#597084;margin-bottom:6px}
.ec-info-item div{border-bottom:1px solid #9aa9b7;min-height:31px;padding:7px 3px;font-size:13px;font-weight:750;overflow-wrap:anywhere}
.ec-count-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:30px}
.ec-count-grid h3{font-size:18px;font-family:Georgia,'Times New Roman',serif}
.ec-table{width:100%;border-collapse:collapse;table-layout:fixed}
.ec-table th{font-size:11px;text-align:left;color:#4b657b;border-bottom:1px solid #bacbd9;padding:10px 6px}
.ec-table td{border-bottom:1px solid #edf1f5;padding:6px;font-size:12px}
.ec-table th:nth-child(1){width:33%}
.ec-table th:nth-child(2){width:26%;text-align:center}
.ec-table th:nth-child(3){width:41%;text-align:right}
.ec-table td:nth-child(2){text-align:center}
.ec-table td:nth-child(3){text-align:right}
.ec-quantity,.ec-money-input{width:100%;max-width:120px;border:1px solid #c2d1df;border-radius:7px;background:#fff;padding:10px 8px;text-align:center;color:#154b7b;font-weight:800;font-size:13px}
.ec-money-input{text-align:right;max-width:200px}
.ec-quantity:focus,.ec-money-input:focus,.ec-textarea:focus{outline:none;border-color:#1764a1;box-shadow:0 0 0 3px rgba(23,100,161,.12)}
.ec-subtotal{display:block;padding:9px 0 6px;border-bottom:1px solid #91a5b6;font-weight:800;color:#153f68;white-space:nowrap}
.ec-count-total{display:flex;justify-content:flex-end;align-items:center;gap:15px;border-top:1px solid #bdcbd8;padding:15px 0;margin-top:15px;font-size:12px;font-weight:800}
.ec-count-total strong{font-size:18px;color:#0f426d;white-space:nowrap}
.ec-other-table{width:100%;max-width:780px;border-collapse:collapse}
.ec-other-table th{text-align:left;font-size:11px;color:#536d83;padding:9px;border-bottom:1px solid #bdcbd8}
.ec-other-table td{padding:9px;border-bottom:1px solid #e5ecf2;font-size:13px}
.ec-other-table td:nth-child(2){width:220px;text-align:right}
.ec-other-table td:nth-child(3){width:175px;text-align:right;font-weight:800;color:#17476f}
.ec-note{font-size:12px;color:#708397;line-height:1.6;margin:10px 0 0}
.ec-grand-total{display:flex;align-items:center;justify-content:flex-end;gap:20px;flex-wrap:wrap;background:#edf5fc;border:1px solid #c4d9e9;border-radius:10px;padding:18px 22px;margin:18px 0 30px;font-size:14px;font-weight:800;color:#173e64}
.ec-grand-total strong{font-size:26px;color:#0d4a7d}
.ec-check-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(310px,.9fr);gap:32px}
.ec-textarea{width:100%;min-height:120px;padding:10px;border:1px solid #ccd9e4;border-radius:8px;resize:vertical;font:13px/1.6 Arial,Helvetica,sans-serif;color:#24465e}
.ec-field-label{display:block;margin:14px 0 8px;font-size:12px;font-weight:800;color:#4c667c}
.ec-check-box{background:#f8fbfe;border:1px solid #d5e2ed;border-radius:12px;padding:20px}
.ec-check-box h3{text-align:center;border-bottom:0;margin-bottom:18px}
.ec-check-line{display:grid;grid-template-columns:1fr 180px;gap:12px;align-items:center;margin-bottom:15px;font-size:12px;font-weight:800;color:#4b6378}
.ec-check-line strong{display:block;text-align:right;font-size:16px;color:#173e65;padding:9px 0;border-bottom:1px solid #91a7b9}
.ec-check-line.difference{padding-top:13px;border-top:2px solid #c6d6e3}
.ec-result{text-align:right;font-size:12px;font-weight:800;color:#155e8f}
.ec-signatures{display:grid;grid-template-columns:1fr 1fr;gap:50px;margin:40px 0 25px}
.ec-signature{text-align:center;padding-top:45px}
.ec-signature small{display:block;color:#8496a6;margin-bottom:15px;font-size:11px}
.ec-signature strong{display:block;border-top:1px solid #71889b;padding-top:10px;color:#405c72;font-size:12px}
.ec-confirmation{padding:20px;border:1px solid #d6e5f0;background:#f5f9fd;border-radius:11px}
.ec-confirmation h3{border:0;padding:0;margin:0 0 8px}
.ec-confirmation p{font-size:12px;line-height:1.6;color:#647b90}
.ec-checkbox{display:flex;gap:11px;align-items:flex-start;font-size:13px;font-weight:700;line-height:1.6}
.ec-checkbox input{width:18px;height:18px;margin-top:3px;accent-color:#155b98}
.ec-actions{display:flex;justify-content:flex-end;gap:12px;flex-wrap:wrap;margin-top:25px}
.ec-btn{display:inline-flex;justify-content:center;align-items:center;min-height:44px;padding:12px 18px;border:0;border-radius:9px;background:#155b98;color:#fff!important;text-decoration:none;font-size:13px;font-weight:800;cursor:pointer}
.ec-btn.secondary{background:#e6f0f9;color:#15517e!important}
.ec-btn:hover{filter:brightness(.94)}
.ec-btn:disabled{opacity:.6;cursor:wait}
.ec-errors{margin-bottom:20px;padding:16px 20px;background:#fff3f3;border:1px solid #eabbbb;border-radius:10px;color:#923333;font-size:13px}
.ec-modal[hidden]{display:none!important}
.ec-modal{position:fixed;inset:0;z-index:99999;background:rgba(8,25,45,.76);display:flex;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(4px)}
.ec-dialog{width:100%;max-width:510px;max-height:calc(100vh - 40px);overflow-y:auto;background:#fff;border-radius:16px;box-shadow:0 25px 75px rgba(0,0,0,.3)}
.ec-dialog-head{padding:25px 26px;border-bottom:1px solid #e2eaf1}
.ec-dialog-head h2{margin:0 0 9px;color:#123e66;font-size:21px}
.ec-dialog-head p{margin:0;color:#667d90;font-size:13px;line-height:1.6}
.ec-dialog-body{padding:23px 26px}
.ec-dialog-row{display:flex;justify-content:space-between;gap:12px;padding:13px 0;border-bottom:1px solid #e5edf3;font-size:13px}
.ec-dialog-row strong{color:#12456e}
.ec-warning{margin-top:17px;padding:14px;background:#fff8e9;border:1px solid #eed9a5;border-radius:9px;color:#795c20;font-size:13px;line-height:1.6}
.ec-dialog-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 26px 25px}
body.ec-modal-open{overflow:hidden}
@media(max-width:900px){
.ec-paper-header{grid-template-columns:1fr;text-align:center}
.ec-logo{margin:auto}
.ec-number{text-align:center}
.ec-count-grid,.ec-check-grid{grid-template-columns:1fr}
}
@media(max-width:650px){
.ec-body{padding:20px 15px}
.ec-paper-header{padding:20px 15px}
.ec-info-item,.ec-info-item.wide{grid-column:span 12}
.ec-signatures{grid-template-columns:1fr;gap:10px}
.ec-check-line{grid-template-columns:1fr;gap:4px}
.ec-other-table td:nth-child(2){width:145px}
.ec-other-table td:nth-child(3){width:115px}
.ec-money-input{max-width:130px}
.ec-actions,.ec-dialog-actions{flex-direction:column}
.ec-actions .ec-btn,.ec-dialog-actions .ec-btn{width:100%}
}
@media print{
.ec-page-head,.ec-actions,.ec-confirmation,.ec-modal{display:none!important}
.ec-paper{border:0;box-shadow:none}
}
</style>
@endpush

@section('content')
<div class="ec-arqueo">

    <div class="ec-page-head">
        <div>
            <h1>Registro de arqueo diario</h1>
            <p>Complete el formato institucional de arqueo y corte de caja.</p>
        </div>

        <a href="{{ route('receptor.arqueos.index') }}" class="ec-btn secondary">
            Volver al historial
        </a>
    </div>

    @if($errors->any())
        <div class="ec-errors" role="alert">
            <strong>Corrija los siguientes datos:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="ec-arqueo-form"
        method="POST"
        action="{{ route('receptor.arqueos.store', $jornada->id) }}"
    >
        @csrf

        <input
            type="hidden"
            name="inicio_formulario_at"
            value="{{ old('inicio_formulario_at', now()->format('Y-m-d H:i:s')) }}"
            id="ec-inicio-formulario"
        >

        <div class="ec-paper">

            <header class="ec-paper-header">

                <div>
                    <img
                        class="ec-logo"
                        src="{{ asset('images/logos/ecosaba.png') }}"
                        alt="ECOSABA"
                    >
                </div>

                <div class="ec-title">
                    <h2>Arqueo y corte de caja</h2>
                    <p>{{ $jornada->agencia?->nombre ?? 'Agencia' }}</p>
                </div>

                <div class="ec-number">
                    DOCUMENTO EN PREPARACIÓN
                    <strong>ARQUEO DIARIO</strong>
                </div>

            </header>

            <div class="ec-body">

                <section class="ec-section">
                    <div class="ec-info">

                        <div class="ec-info-item wide">
                            <label>Nombre del receptor</label>
                            <div>
                                {{ auth()->user()?->nombre_completo
                                    ?? auth()->user()?->nombre
                                    ?? 'Receptor' }}
                            </div>
                        </div>

                        <div class="ec-info-item">
                            <label>Fecha operativa</label>
                            <div>
                                {{ $jornada->fecha_operativa?->format('d/m/Y') }}
                            </div>
                        </div>

                        <div class="ec-info-item">
                            <label>Turno</label>
                            <div>{{ $jornada->turno?->nombre ?? '—' }}</div>
                        </div>

                        <div class="ec-info-item">
                            <label>Agencia</label>
                            <div>{{ $jornada->agencia?->nombre ?? '—' }}</div>
                        </div>

                        <div class="ec-info-item">
                            <label>Ventanilla</label>
                            <div>{{ $jornada->ventanilla?->codigo ?? '—' }}</div>
                        </div>

                        <div class="ec-info-item">
                            <label>Hora de inicio</label>
                            <div id="ec-hora-inicio">
                                {{ \Illuminate\Support\Carbon::parse(
                                    old('inicio_formulario_at', now())
                                )->format('H:i:s') }}
                            </div>
                        </div>

                        <div class="ec-info-item">
                            <label>Hora de finalización</label>
                            <div id="ec-hora-finalizacion">
                                Pendiente de finalizar
                            </div>
                        </div>

                    </div>
                </section>

                <section class="ec-section">

                    <div class="ec-count-grid">

                        <div>
                            <h3>Billetes</h3>

                            <table class="ec-table">
                                <thead>
                                    <tr>
                                        <th>Denominación</th>
                                        <th>Cantidad</th>
                                        <th>Sub-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($billetes as $v)
                                        @if((float)$v !== 2.0)
                                            <tr>
                                                <td>
                                                    Q {{ number_format((float)$v, 2) }}
                                                </td>

                                                <td>
                                                    <input
                                                        type="number"
                                                        class="ec-quantity ec-denom"
                                                        name="billetes[{{ $v }}]"
                                                        value="{{ old('billetes.'.$v, 0) }}"
                                                        min="0"
                                                        max="10000000"
                                                        step="1"
                                                        data-centavos="{{ (int)round((float)$v * 100) }}"
                                                        aria-label="Billetes de Q {{ $v }}"
                                                        required
                                                    >
                                                </td>

                                                <td>
                                                    <span class="ec-subtotal">
                                                        Q 0.00
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="ec-count-total">
                                <span>Total billetes</span>
                                <strong id="ec-total-billetes">Q 0.00</strong>
                            </div>
                        </div>

                        <div>
                            <h3>Monedas</h3>

                            <table class="ec-table">
                                <thead>
                                    <tr>
                                        <th>Denominación</th>
                                        <th>Cantidad</th>
                                        <th>Sub-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monedas as $v)
                                        <tr>
                                            <td>
                                                Q {{ number_format((float)$v, 2) }}
                                            </td>

                                            <td>
                                                <input
                                                    type="number"
                                                    class="ec-quantity ec-denom"
                                                    name="monedas[{{ $v }}]"
                                                    value="{{ old('monedas.'.$v, 0) }}"
                                                    min="0"
                                                    max="10000000"
                                                    step="1"
                                                    data-centavos="{{ (int)round((float)$v * 100) }}"
                                                    aria-label="Monedas de Q {{ $v }}"
                                                    required
                                                >
                                            </td>

                                            <td>
                                                <span class="ec-subtotal">
                                                    Q 0.00
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="ec-count-total">
                                <span>Total monedas</span>
                                <strong id="ec-total-monedas">Q 0.00</strong>
                            </div>
                        </div>

                    </div>

                </section>

                <section class="ec-section">

                    <h3>Otros valores</h3>

                    <table class="ec-other-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Importe (Q)</th>
                                <th>Sub-total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach([
                                'CHEQUES' => 'Cheques',
                                'VALES' => 'Vales',
                                'OTROS_VALORES' => 'Otros valores'
                            ] as $codigo => $nombre)
                                <tr>
                                    <td>
                                        <label for="ec-otro-{{ $codigo }}">
                                            {{ $nombre }}
                                        </label>
                                    </td>

                                    <td>
                                        <input
                                            id="ec-otro-{{ $codigo }}"
                                            class="ec-money-input ec-otro"
                                            type="number"
                                            name="otros_valores[{{ $codigo }}]"
                                            value="{{ old('otros_valores.'.$codigo, '0.00') }}"
                                            min="0"
                                            max="999999999999.99"
                                            step="0.01"
                                            required
                                        >
                                    </td>

                                    <td>
                                        <span class="ec-otro-subtotal">
                                            Q 0.00
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="ec-count-total" style="max-width:780px">
                        <span>Total otros valores</span>
                        <strong id="ec-total-otros">Q 0.00</strong>
                    </div>

                    <p class="ec-note">
                        Los importes de cheques, vales y otros valores
                        se suman al total arqueado y participan en
                        la comprobación contra el corte bancario.
                    </p>

                </section>

                <div class="ec-grand-total">
                    <span>TOTAL ARQUEADO</span>
                    <strong id="ec-total-arqueado">Q 0.00</strong>
                </div>

                <section class="ec-section">

                    <div class="ec-check-grid">

                        <div>
                            <h3>Certificación / Justificación</h3>

                            <label
                                for="ec-justificacion"
                                class="ec-field-label"
                            >
                                Justificación de la diferencia
                            </label>

                            <textarea
                                id="ec-justificacion"
                                class="ec-textarea"
                                name="justificacion_diferencia"
                                rows="4"
                                maxlength="2000"
                                placeholder="Describa el motivo del sobrante o faltante."
                            >{{ old('justificacion_diferencia') }}</textarea>

                            <p class="ec-note">
                                Obligatoria cuando exista sobrante o faltante.
                            </p>

                            <label
                                for="ec-observaciones"
                                class="ec-field-label"
                                style="margin-top:22px"
                            >
                                Observaciones
                            </label>

                            <textarea
                                id="ec-observaciones"
                                class="ec-textarea"
                                name="observaciones"
                                rows="4"
                                maxlength="5000"
                                placeholder="Observaciones adicionales del arqueo."
                            >{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="ec-check-box">

                            <h3>COMPROBACIÓN</h3>

                            <div class="ec-check-line">
                                <span>Total arqueado</span>
                                <strong id="ec-comprobacion-arqueado">
                                    Q 0.00
                                </strong>
                            </div>

                            <div class="ec-check-line">
                                <label for="ec-corte">
                                    Total corte bancario
                                </label>

                                <input
                                    id="ec-corte"
                                    class="ec-money-input"
                                    type="number"
                                    name="total_corte"
                                    value="{{ old('total_corte', '0.00') }}"
                                    min="0"
                                    max="999999999999.99"
                                    step="0.01"
                                    required
                                >
                            </div>

                            <div class="ec-check-line difference">
                                <span>Diferencia</span>
                                <strong id="ec-diferencia">
                                    Q 0.00
                                </strong>
                            </div>

                            <div class="ec-result" id="ec-resultado">
                                Cuadrado
                            </div>

                        </div>

                    </div>

                </section>

                <section class="ec-section">

                    <div class="ec-signatures">

                        <div class="ec-signature">
                            <small>Pendiente de firma electrónica</small>
                            <strong>Receptor - Pagador</strong>
                        </div>

                        <div class="ec-signature">
                            <small>Pendiente de certificación</small>
                            <strong>Jefe / Subjefe de Caja</strong>
                        </div>

                    </div>

                </section>

                <section class="ec-confirmation">

                    <h3>Firma electrónica del receptor</h3>

                    <p>
                        Al confirmar, el arqueo quedará registrado
                        definitivamente y sus importes no podrán modificarse.
                    </p>

                    <label class="ec-checkbox">
                        <input
                            type="checkbox"
                            name="confirmar_firma"
                            value="1"
                            required
                            {{ old('confirmar_firma') ? 'checked' : '' }}
                        >

                        <span>
                            Confirmo que he realizado el conteo,
                            verificado los importes y autorizo
                            mi firma electrónica.
                        </span>
                    </label>

                </section>

                <div class="ec-actions">

                    <a
                        href="{{ route('receptor.arqueos.index') }}"
                        class="ec-btn secondary"
                    >
                        Cancelar
                    </a>

                    <button
                        id="ec-enviar"
                        type="submit"
                        class="ec-btn"
                    >
                        Firmar y finalizar arqueo
                    </button>

                </div>

            </div>
        </div>
    </form>
</div>

<div
    id="ec-modal"
    class="ec-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="ec-modal-title"
    aria-describedby="ec-modal-description"
    hidden
>
    <div class="ec-dialog">

        <div class="ec-dialog-head">
            <h2 id="ec-modal-title">
                Confirmar firma electrónica
            </h2>

            <p id="ec-modal-description">
                Revise los importes antes de registrar
                definitivamente el arqueo.
            </p>
        </div>

        <div class="ec-dialog-body">

            <div class="ec-dialog-row">
                <span>Hora de inicio</span>
                <strong id="ec-modal-inicio">
                    {{ \Illuminate\Support\Carbon::parse(
                        old('inicio_formulario_at', now())
                    )->format('H:i:s') }}
                </strong>
            </div>

            <div class="ec-dialog-row">
                <span>Total billetes</span>
                <strong id="ec-modal-billetes">Q 0.00</strong>
            </div>

            <div class="ec-dialog-row">
                <span>Total monedas</span>
                <strong id="ec-modal-monedas">Q 0.00</strong>
            </div>

            <div class="ec-dialog-row">
                <span>Total otros valores</span>
                <strong id="ec-modal-otros">Q 0.00</strong>
            </div>

            <div class="ec-dialog-row">
                <span>Total arqueado</span>
                <strong id="ec-modal-arqueado">Q 0.00</strong>
            </div>

            <div class="ec-dialog-row">
                <span>Corte bancario</span>
                <strong id="ec-modal-corte">Q 0.00</strong>
            </div>

            <div class="ec-dialog-row">
                <span>Diferencia</span>
                <strong id="ec-modal-diferencia">Q 0.00</strong>
            </div>

            <div class="ec-warning">
                <strong>Importante:</strong>
                al confirmar se registrará su firma electrónica.
                Los importes no podrán modificarse posteriormente.
            </div>

        </div>

        <div class="ec-dialog-actions">

            <button
                type="button"
                id="ec-modal-cancelar"
                class="ec-btn secondary"
            >
                Volver a revisar
            </button>

            <button
                type="button"
                id="ec-modal-confirmar"
                class="ec-btn"
            >
                Confirmar y firmar
            </button>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const form = document.getElementById('ec-arqueo-form');

    if (!form) return;

    const denominaciones = [
        ...form.querySelectorAll('.ec-denom')
    ];

    const otrosValores = [
        ...form.querySelectorAll('.ec-otro')
    ];

    const corte = document.getElementById('ec-corte');
    const justificacion = document.getElementById('ec-justificacion');

    const modal = document.getElementById('ec-modal');
    const botonEnviar = document.getElementById('ec-enviar');
    const botonCancelar = document.getElementById('ec-modal-cancelar');
    const botonConfirmar = document.getElementById('ec-modal-confirmar');

    let enviando = false;
    let envioConfirmado = false;
    let focoAnterior = null;

    let resumen = {
        billetes: 0,
        monedas: 0,
        otros: 0,
        arqueado: 0,
        corte: 0,
        diferencia: 0
    };

    function dinero(centavos) {
        return 'Q ' + (
            Math.abs(centavos) / 100
        ).toLocaleString('es-GT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function dineroConSigno(centavos) {
        return (centavos < 0 ? '-' : '') + dinero(centavos);
    }

    function importeEnCentavos(valor) {
        const texto = String(valor ?? '').trim();

        if (!/^\d+(\.\d{1,2})?$/.test(texto)) {
            return 0;
        }

        const partes = texto.split('.');

        return Number(partes[0]) * 100 +
            Number((partes[1] || '').padEnd(2, '0'));
    }

    function calcular() {
        let billetes = 0;
        let monedas = 0;
        let otros = 0;

        denominaciones.forEach(function (campo) {
            const cantidad = Number(campo.value);
            const centavos = Number(campo.dataset.centavos);

            const subtotal = (
                Number.isSafeInteger(cantidad) &&
                cantidad >= 0 &&
                Number.isSafeInteger(cantidad * centavos)
            ) ? cantidad * centavos : 0;

            campo.closest('tr')
                .querySelector('.ec-subtotal')
                .textContent = dinero(subtotal);

            if (campo.name.startsWith('billetes')) {
                billetes += subtotal;
            } else {
                monedas += subtotal;
            }
        });

        otrosValores.forEach(function (campo) {
            const subtotal = importeEnCentavos(campo.value);

            campo.closest('tr')
                .querySelector('.ec-otro-subtotal')
                .textContent = dinero(subtotal);

            otros += subtotal;
        });

        const total = billetes + monedas + otros;
        const totalCorte = importeEnCentavos(corte.value);
        const diferencia = total - totalCorte;

        resumen = {
            billetes: billetes,
            monedas: monedas,
            otros: otros,
            arqueado: total,
            corte: totalCorte,
            diferencia: diferencia
        };

        document.getElementById('ec-total-billetes').textContent =
            dinero(billetes);

        document.getElementById('ec-total-monedas').textContent =
            dinero(monedas);

        document.getElementById('ec-total-otros').textContent =
            dinero(otros);

        document.getElementById('ec-total-arqueado').textContent =
            dinero(total);

        document.getElementById('ec-comprobacion-arqueado').textContent =
            dinero(total);

        document.getElementById('ec-diferencia').textContent =
            dineroConSigno(diferencia);

        const resultado = document.getElementById('ec-resultado');

        resultado.textContent = diferencia > 0
            ? 'Sobrante'
            : diferencia < 0
                ? 'Faltante'
                : 'Cuadrado';

        resultado.style.color = diferencia === 0
            ? '#157347'
            : '#a04427';

        justificacion.required = diferencia !== 0;
    }

    function actualizarModal() {
        calcular();

        document.getElementById('ec-modal-billetes').textContent =
            dinero(resumen.billetes);

        document.getElementById('ec-modal-monedas').textContent =
            dinero(resumen.monedas);

        document.getElementById('ec-modal-otros').textContent =
            dinero(resumen.otros);

        document.getElementById('ec-modal-arqueado').textContent =
            dinero(resumen.arqueado);

        document.getElementById('ec-modal-corte').textContent =
            dinero(resumen.corte);

        document.getElementById('ec-modal-diferencia').textContent =
            dineroConSigno(resumen.diferencia);

        document.getElementById('ec-modal-inicio').textContent =
            document.getElementById('ec-hora-inicio').textContent.trim();
    }

    function abrirModal() {
        if (enviando) return;

        actualizarModal();

        focoAnterior = document.activeElement;

        modal.hidden = false;
        document.body.classList.add('ec-modal-open');

        botonCancelar.focus();
    }

    function cerrarModal() {
        if (enviando) return;

        modal.hidden = true;
        document.body.classList.remove('ec-modal-open');

        if (
            focoAnterior &&
            typeof focoAnterior.focus === 'function'
        ) {
            focoAnterior.focus();
        }
    }

    denominaciones
        .concat(otrosValores, [corte])
        .forEach(function (campo) {
            campo.addEventListener('input', calcular);
        });

    calcular();

    form.addEventListener('submit', function (evento) {
        if (envioConfirmado) return;

        evento.preventDefault();

        if (enviando) return;

        if (!form.reportValidity()) return;

        abrirModal();
    });

    botonCancelar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', function (evento) {
        if (evento.target === modal) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (modal.hidden) return;

        if (evento.key === 'Escape') {
            evento.preventDefault();
            cerrarModal();
            return;
        }

        if (evento.key === 'Tab') {
            const primero = botonCancelar;
            const ultimo = botonConfirmar;

            if (
                evento.shiftKey &&
                document.activeElement === primero
            ) {
                evento.preventDefault();
                ultimo.focus();
            } else if (
                !evento.shiftKey &&
                document.activeElement === ultimo
            ) {
                evento.preventDefault();
                primero.focus();
            }
        }
    });

    botonConfirmar.addEventListener('click', function () {
        if (enviando) return;

        if (!form.reportValidity()) {
            cerrarModal();
            return;
        }

        enviando = true;
        envioConfirmado = true;

        botonConfirmar.disabled = true;
        botonCancelar.disabled = true;
        botonEnviar.disabled = true;

        botonConfirmar.textContent = 'Registrando firma...';

        form.requestSubmit();
    });

    window.addEventListener('pageshow', function () {
        enviando = false;
        envioConfirmado = false;

        botonConfirmar.disabled = false;
        botonCancelar.disabled = false;
        botonEnviar.disabled = false;

        botonConfirmar.textContent = 'Confirmar y firmar';

        modal.hidden = true;
        document.body.classList.remove('ec-modal-open');
    });
})();
</script>
@endpush
