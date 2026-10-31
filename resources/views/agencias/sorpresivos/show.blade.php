
@php
    $esReceptor = ($ruta === 'receptor');

    $layout = match ($ruta) {
        'receptor' => 'layouts.receptor',
        'jefecaja' => 'layouts.jefe-caja',
        'subjefecaja' => 'layouts.subjefe-caja',
        default => abort(403),
    };

    $billetes = [200, 100, 50, 20, 10, 5, 2, 1];
    $monedas = ['1.00', '0.50', '0.25', '0.10', '0.05'];

    $detalles = $registro->detalles ?? collect();

    $firmaReceptor = ($registro->firmas ?? collect())
        ->firstWhere('tipo_firma', 'RECEPTOR');

    $firmaSupervisor = ($registro->firmas ?? collect())
        ->firstWhere('tipo_firma', 'CERTIFICADOR');

    $firmaReceptorValida = (bool) ($verificaciones['RECEPTOR'] ?? false);
    $firmaSupervisorValida = (bool) ($verificaciones['CERTIFICADOR'] ?? false);

    $diferencia = (float) ($registro->diferencia ?? 0);
    $estado = strtoupper((string) $registro->estado);

    $nombreSupervisor = $firmaSupervisor
        ? (data_get($firmaSupervisor, 'firmante_nombre_historico')
            ?: data_get($firmaSupervisor, 'nombre_firmante')
            ?: trim(
                (string) data_get($firmaSupervisor, 'nombres_historicos')
                .' '.
                (string) data_get($firmaSupervisor, 'apellidos_historicos')
            )
            ?: data_get($firmaSupervisor, 'usuario.nombre_completo')
            ?: data_get($firmaSupervisor, 'usuario.nombre')
            ?: 'Nombre del supervisor no disponible')
        : 'Pendiente';

    $fechaSupervisor = $firmaSupervisor
        ? (data_get($firmaSupervisor, 'fecha_firma') ?: $registro->certificado_at)
        : null;

    $detalleDenominacion = fn ($tipo, $valor) =>
        $detalles->first(
            fn ($d) =>
                $d->tipo === $tipo
                && (int) round((float) $d->denominacion * 100)
                    === (int) round((float) $valor * 100)
        );

    $moneda = fn ($valor) =>
        'Q '.number_format((float) ($valor ?? 0), 2, '.', ',');
@endphp

@extends($layout)

@section('title', 'Detalle de arqueo de Jefatura de Caja')

@push('styles')
<style>
.ec-show {
    max-width: 1120px;
    margin: 0 auto 36px;
    font-family: Arial, Helvetica, sans-serif;
    color: #263e52;
}

.ec-show * {
    box-sizing: border-box;
}

.ec-show-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 22px;
}

.ec-show-top h1 {
    margin: 0 0 6px;
    font-size: 26px;
    color: #123e68;
}

.ec-show-top p {
    margin: 0;
    font-size: 13px;
    color: #6e8193;
}

.ec-show-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.ec-show-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 18px;
    min-height: 44px;
    border: 0;
    border-radius: 9px;
    background: #155b98;
    color: #fff !important;
    text-decoration: none;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
}

.ec-show-btn.secondary {
    background: #e6f0f9;
    color: #15517e !important;
}

.ec-show-btn:hover {
    filter: brightness(.95);
}

.ec-show-btn:disabled {
    opacity: .6;
    cursor: wait;
}

.ec-show-paper {
    background: #fff;
    border: 1px solid #d5e1eb;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(15,45,75,.07);
}

.ec-show-header {
    display: grid;
    grid-template-columns: 170px 1fr 180px;
    gap: 20px;
    align-items: center;
    padding: 28px 30px;
    border-bottom: 2px solid #dbe6ee;
}

.ec-show-logo {
    max-width: 165px;
    max-height: 75px;
    object-fit: contain;
}

.ec-show-heading {
    text-align: center;
}

.ec-show-heading h2 {
    margin: 0;
    color: #153f67;
    font-size: 24px;
    text-transform: uppercase;
}

.ec-show-heading p {
    margin: 8px 0 0;
    font-size: 14px;
    font-weight: 800;
    color: #536b7e;
    text-transform: uppercase;
}

.ec-show-number {
    text-align: right;
}

.ec-show-number small {
    display: block;
    font-size: 10px;
    color: #7b8fa0;
    font-weight: 800;
    text-transform: uppercase;
}

.ec-show-number strong {
    display: block;
    margin-top: 7px;
    font-size: 12px;
    color: #194c75;
    overflow-wrap: anywhere;
}

.ec-show-body {
    padding: 30px 35px 35px;
}

.ec-show-section {
    margin-bottom: 28px;
}

.ec-show-section h3 {
    margin: 0 0 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid #d7e2eb;
    color: #17456d;
    font-size: 16px;
}

.ec-show-info {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
}

.ec-show-info-item {
    min-width: 0;
}

.ec-show-info-item.wide {
    grid-column: span 2;
}

.ec-show-info-item label {
    display: block;
    margin-bottom: 7px;
    font-size: 11px;
    color: #61788c;
    font-weight: 800;
}

.ec-show-info-item strong {
    display: block;
    padding: 8px 2px;
    border-bottom: 1px solid #a8bac9;
    font-size: 13px;
    color: #25465f;
    overflow-wrap: anywhere;
}

.ec-show-tables {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.ec-show-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.ec-show-table th {
    text-align: left;
    padding: 10px 7px;
    border-bottom: 1px solid #b9cbd9;
    font-size: 11px;
    color: #587084;
}

.ec-show-table td {
    padding: 10px 7px;
    border-bottom: 1px solid #edf1f5;
    font-size: 12px;
    color: #36536a;
}

.ec-show-table th:nth-child(2),
.ec-show-table td:nth-child(2) {
    text-align: center;
}

.ec-show-table th:last-child,
.ec-show-table td:last-child {
    text-align: right;
}

.ec-show-table td:last-child {
    font-weight: 800;
    color: #16466e;
}

.ec-show-table tfoot td {
    padding-top: 16px;
    border-top: 2px solid #c2d1dd;
    border-bottom: 0;
    font-weight: 800;
    color: #133f68;
    font-size: 13px;
}

.ec-show-grand {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 22px;
    flex-wrap: wrap;
    padding: 20px 24px;
    border: 1px solid #c8dced;
    background: #eef6fc;
    border-radius: 11px;
    margin: 24px 0 30px;
    font-size: 14px;
    font-weight: 800;
    color: #17466e;
}

.ec-show-grand strong {
    font-size: 26px;
    color: #0c4a7e;
}

.ec-show-check {
    max-width: 530px;
    margin-left: auto;
    background: #f7fbfe;
    border: 1px solid #d7e4ee;
    border-radius: 11px;
    padding: 18px 22px;
}

.ec-show-check h3 {
    text-align: center;
    border: 0;
    margin: 0 0 14px;
    color: #17466e;
    font-size: 17px;
}

.ec-show-check-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 13px 0;
    border-bottom: 1px solid #dce6ee;
    font-size: 13px;
    color: #506a7e;
}

.ec-show-check-line strong {
    font-size: 16px;
    color: #16456d;
}

.ec-show-check-line.last {
    border-top: 2px solid #c7d8e5;
    border-bottom: 0;
    margin-top: 8px;
    font-weight: 800;
}

.ec-show-check-status {
    text-align: right;
    margin-top: 8px;
    font-size: 12px;
    font-weight: 800;
}

.ec-show-note {
    padding: 15px 18px;
    background: #f8fbfd;
    border: 1px solid #e0e9f0;
    border-radius: 9px;
    font-size: 13px;
    color: #415c71;
    line-height: 1.7;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

.ec-show-note.empty {
    color: #8193a2;
    font-style: italic;
}

.ec-show-signatures {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    margin-top: 25px;
}

.ec-show-signature {
    text-align: center;
    padding-top: 20px;
}

.ec-show-signature small {
    display: block;
    min-height: 35px;
    color: #698096;
    font-size: 11px;
}

.ec-show-signature .signer-name {
    display: block;
    color: #17466e;
    font-size: 13px;
    font-weight: 800;
    min-height: 20px;
    overflow-wrap: anywhere;
}

.ec-show-signature strong {
    display: block;
    border-top: 1px solid #91a5b6;
    padding-top: 11px;
    color: #38566d;
    font-size: 12px;
}

.ec-show-signature span {
    display: block;
    margin-top: 7px;
    color: #657f92;
    font-size: 11px;
}

.ec-show-status {
    display: inline-flex;
    padding: 7px 12px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 800;
    background: #edf2f6;
    color: #476174;
}

.ec-show-status.certificado {
    background: #e7f5ec;
    color: #18703e;
}

.ec-show-status.pendiente {
    background: #fff4de;
    color: #956313;
}

.ec-show-status.anulado {
    background: #fce9e9;
    color: #a23737;
}

.ec-show-signature-valid,
.ec-show-signature-invalid,
.ec-show-signature-pending {
    margin-top: 20px;
    padding: 15px 18px;
    border-radius: 10px;
    font-size: 12px;
    line-height: 1.7;
}

.ec-show-signature-valid {
    border: 1px solid #c8e3d1;
    background: #f1faf4;
    color: #276643;
}

.ec-show-signature-invalid {
    border: 1px solid #edcece;
    background: #fff5f5;
    color: #983c3c;
}

.ec-show-signature-pending {
    border: 1px solid #eadbb5;
    background: #fffaf0;
    color: #8a671e;
}

.ec-show-alert {
    padding: 14px 18px;
    margin-bottom: 18px;
    border: 1px solid #c8e3d1;
    background: #eff9f2;
    color: #246a42;
    border-radius: 9px;
    font-size: 13px;
}

.ec-show-alert.error {
    border-color: #edcece;
    background: #fff5f5;
    color: #983c3c;
}

.ec-show-sign-form {
    margin-top: 22px;
    padding: 18px;
    background: #f5f9fd;
    border: 1px solid #d6e5f0;
    border-radius: 11px;
}

.ec-show-checkbox {
    display: flex;
    gap: 11px;
    align-items: flex-start;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.6;
    margin: 16px 0;
}

.ec-show-checkbox input {
    width: 18px;
    height: 18px;
    margin-top: 3px;
    accent-color: #155b98;
}

/* MODAL INSTITUCIONAL */

.ec-firma-modal[hidden] {
    display: none !important;
}

.ec-firma-modal {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(9, 28, 47, .72);
    backdrop-filter: blur(5px);
}

.ec-firma-modal,
.ec-firma-modal * {
    box-sizing: border-box;
}

.ec-firma-modal-dialog {
    width: 100%;
    max-width: 480px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    background: #fff;
    border-radius: 17px;
    box-shadow: 0 25px 80px rgba(0,0,0,.25);
    animation: ecFirmaEntrada .2s ease-out;
}

@keyframes ecFirmaEntrada {
    from {
        opacity: 0;
        transform: translateY(12px) scale(.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.ec-firma-modal-header {
    padding: 24px 28px 17px;
    border-bottom: 1px solid #e5edf4;
}

.ec-firma-modal-icon {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    border-radius: 14px;
    background: #e9f3fb;
    color: #155b98;
}

.ec-firma-modal-icon svg {
    width: 28px;
    height: 28px;
}

.ec-firma-modal-header h2 {
    margin: 0;
    font-size: 21px;
    font-weight: 800;
    color: #123e68;
}

.ec-firma-modal-body {
    padding: 22px 28px;
    color: #526b80;
    font-size: 14px;
    line-height: 1.75;
}

.ec-firma-modal-body p {
    margin: 0 0 15px;
}

.ec-firma-modal-warning {
    padding: 13px 15px;
    background: #fff8e9;
    border: 1px solid #eeddb7;
    border-radius: 9px;
    color: #86621f;
    font-size: 12px;
    line-height: 1.65;
}

.ec-firma-modal-footer {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 11px;
    padding: 19px 28px 24px;
    background: #f8fafc;
    border-top: 1px solid #e5edf4;
}

.ec-firma-modal-btn {
    min-height: 44px;
    padding: 11px 18px;
    border: 0;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
}

.ec-firma-modal-btn.cancelar {
    background: #e6edf4;
    color: #35536d;
}

.ec-firma-modal-btn.confirmar {
    background: #155b98;
    color: #fff;
}

.ec-firma-modal-btn:hover {
    filter: brightness(.95);
}

.ec-firma-modal-btn:focus-visible {
    outline: 3px solid #78b9e6;
    outline-offset: 3px;
}

.ec-firma-modal-btn:disabled {
    opacity: .65;
    cursor: wait;
}

body.ec-firma-modal-abierto {
    overflow: hidden;
}

@media (max-width: 850px) {
    .ec-show-header {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .ec-show-logo {
        margin: auto;
    }

    .ec-show-number {
        text-align: center;
    }

    .ec-show-tables {
        grid-template-columns: 1fr;
    }

    .ec-show-info {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 550px) {
    .ec-show-body {
        padding: 20px 15px;
    }

    .ec-show-header {
        padding: 20px 15px;
    }

    .ec-show-info {
        grid-template-columns: 1fr;
    }

    .ec-show-info-item.wide {
        grid-column: span 1;
    }

    .ec-show-signatures {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .ec-show-actions {
        width: 100%;
    }

    .ec-show-btn {
        flex: 1;
    }

    .ec-firma-modal-dialog {
        max-width: 100%;
    }

    .ec-firma-modal-header,
    .ec-firma-modal-body {
        padding-left: 20px;
        padding-right: 20px;
    }

    .ec-firma-modal-footer {
        padding: 18px 20px;
    }

    .ec-firma-modal-btn {
        flex: 1;
    }
}

@media print {
    .ec-show-top,
    .ec-show-alert,
    .ec-show-sign-form,
    .ec-firma-modal {
        display: none !important;
    }

    .ec-show-paper {
        border: 0;
        box-shadow: none;
    }

    .ec-show-body {
        padding: 20px;
    }
}
</style>
@endpush

@section('content')
<div class="ec-show">

    <div class="ec-show-top">
        <div>
            <h1>Detalle de arqueo de Jefatura de Caja</h1>
            <p>
                Consulta del conteo supervisado y de sus firmas electrónicas.
            </p>
        </div>

        <div class="ec-show-actions">
            <a
                href="{{ route($ruta.'.sorpresivos.index') }}"
                class="ec-show-btn secondary"
            >
                Volver al historial
            </a>

            @if(\Illuminate\Support\Facades\Route::has($ruta.'.sorpresivos.imprimir'))
                <a
                    href="{{ route($ruta.'.sorpresivos.imprimir', $registro->id) }}"
                    class="ec-show-btn"
                    target="_blank"
                    rel="noopener"
                >
                    Ver / Imprimir PDF
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="ec-show-alert" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="ec-show-alert error" role="alert">
            <strong>Revise lo siguiente:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ec-show-paper">

        <header class="ec-show-header">
            <div>
                <img
                    class="ec-show-logo"
                    src="{{ asset('images/logos/ecosaba.png') }}"
                    alt="ECOSABA"
                >
            </div>

            <div class="ec-show-heading">
                <h2>Arqueo de Jefatura de Caja</h2>
                <p>
                    {{ $registro->agencia_nombre_historico ?? 'Agencia' }}
                </p>
            </div>

            <div class="ec-show-number">
                <small>Número de arqueo</small>
                <strong>{{ $registro->numero_arqueo }}</strong>

                <div style="margin-top:12px">
                    <span class="ec-show-status {{ $estado === 'CERTIFICADO' ? 'certificado' : ($estado === 'ANULADO' ? 'anulado' : 'pendiente') }}">
                        {{ str_replace('_', ' ', $estado) }}
                    </span>
                </div>
            </div>
        </header>

        <div class="ec-show-body">

            <section class="ec-show-section">
                <div class="ec-show-info">

                    <div class="ec-show-info-item wide">
                        <label>Nombre del receptor</label>
                        <strong>
                            {{ $registro->receptor_nombre_historico ?? '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Fecha operativa</label>
                        <strong>
                            {{ $registro->fecha_operativa
                                ? \Illuminate\Support\Carbon::parse($registro->fecha_operativa)->format('d/m/Y')
                                : '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Turno</label>
                        <strong>
                            {{ $registro->turno_nombre_historico ?? '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Agencia</label>
                        <strong>
                            {{ $registro->agencia_nombre_historico ?? '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Ventanilla</label>
                        <strong>
                            {{ $registro->ventanilla_codigo_historico ?? '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Hora de inicio</label>
                        <strong>
                            {{ $registro->inicio_at
                                ? \Illuminate\Support\Carbon::parse($registro->inicio_at)->format('H:i:s')
                                : '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Hora de finalización</label>
                        <strong>
                            {{ $registro->finalizado_at
                                ? \Illuminate\Support\Carbon::parse($registro->finalizado_at)->format('H:i:s')
                                : '—' }}
                        </strong>
                    </div>

                </div>
            </section>

            <section class="ec-show-section">
                <div class="ec-show-tables">

                    <div>
                        <h3>Billetes</h3>

                        <table class="ec-show-table">
                            <thead>
                                <tr>
                                    <th>Denominación</th>
                                    <th>Cantidad</th>
                                    <th>Sub-total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($billetes as $valor)
                                    @php($detalle = $detalleDenominacion('BILLETE', $valor))

                                    <tr>
                                        <td>{{ $moneda($valor) }}</td>
                                        <td>{{ number_format((int) ($detalle?->cantidad ?? 0)) }}</td>
                                        <td>{{ $moneda($detalle?->subtotal) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="2">Total billetes</td>
                                    <td>{{ $moneda($registro->total_billetes) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div>
                        <h3>Monedas</h3>

                        <table class="ec-show-table">
                            <thead>
                                <tr>
                                    <th>Denominación</th>
                                    <th>Cantidad</th>
                                    <th>Sub-total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($monedas as $valor)
                                    @php($detalle = $detalleDenominacion('MONEDA', $valor))

                                    <tr>
                                        <td>{{ $moneda($valor) }}</td>
                                        <td>{{ number_format((int) ($detalle?->cantidad ?? 0)) }}</td>
                                        <td>{{ $moneda($detalle?->subtotal) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="2">Total monedas</td>
                                    <td>{{ $moneda($registro->total_monedas) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </section>

            <div class="ec-show-grand">
                <span>TOTAL ARQUEADO</span>
                <strong>{{ $moneda($registro->total_arqueado) }}</strong>
            </div>

            <section class="ec-show-section">
                <div class="ec-show-check">

                    <h3>COMPROBACIÓN</h3>

                    <div class="ec-show-check-line">
                        <span>Total arqueado</span>
                        <strong>{{ $moneda($registro->total_arqueado) }}</strong>
                    </div>

                    <div class="ec-show-check-line">
                        <span>Total corte bancario</span>
                        <strong>{{ $moneda($registro->total_corte) }}</strong>
                    </div>

                    <div class="ec-show-check-line last">
                        <span>Diferencia</span>
                        <strong>
                            {{ $diferencia < 0 ? '-' : '' }}{{ $moneda(abs($diferencia)) }}
                        </strong>
                    </div>

                    <div
                        class="ec-show-check-status"
                        style="color:{{ $diferencia == 0 ? '#18703e' : '#a04427' }}"
                    >
                        {{ $diferencia > 0 ? 'Sobrante' : ($diferencia < 0 ? 'Faltante' : 'Cuadrado') }}
                    </div>

                </div>
            </section>

            <section class="ec-show-section">
                <h3>Justificación de la diferencia</h3>

                <div class="ec-show-note {{ !$registro->justificacion_diferencia ? 'empty' : '' }}">
                    {{ $registro->justificacion_diferencia ?: 'Sin justificación registrada.' }}
                </div>
            </section>

            <section class="ec-show-section">
                <h3>Observaciones</h3>

                <div class="ec-show-note {{ !$registro->observaciones ? 'empty' : '' }}">
                    {{ $registro->observaciones ?: 'Sin observaciones registradas.' }}
                </div>
            </section>

            <section class="ec-show-section">
                <h3>Firmas electrónicas</h3>

                <div class="ec-show-signatures">

                    <div class="ec-show-signature">
                        <small>
                            {{ $firmaReceptor ? 'Firma electrónica registrada' : 'Firma pendiente' }}
                        </small>

                        <span class="signer-name">
                            {{ $firmaReceptor
                                ? ($registro->receptor_nombre_historico ?: 'Receptor')
                                : '—' }}
                        </span>

                        <strong>Receptor - Pagador</strong>

                        <span>
                            {{ $firmaReceptor
                                ? (
                                    $firmaReceptor->fecha_firma
                                        ? \Illuminate\Support\Carbon::parse($firmaReceptor->fecha_firma)->format('d/m/Y H:i:s')
                                        : (
                                            $registro->firmado_receptor_at
                                                ? \Illuminate\Support\Carbon::parse($registro->firmado_receptor_at)->format('d/m/Y H:i:s')
                                                : 'Fecha no disponible'
                                        )
                                )
                                : 'Pendiente' }}
                        </span>

                        <span>
                            {{ !$firmaReceptor
                                ? 'Pendiente de firma'
                                : ($firmaReceptorValida
                                    ? 'Integridad verificada'
                                    : 'Integridad no verificada') }}
                        </span>
                    </div>

                    <div class="ec-show-signature">
                        <small>
                            {{ $firmaSupervisor ? 'Firma electrónica registrada' : 'Firma pendiente' }}
                        </small>

                        <span class="signer-name">
                            {{ $nombreSupervisor }}
                        </span>

                        <strong>Jefe / Subjefe de Caja</strong>

                        <span>
                            {{ $fechaSupervisor
                                ? \Illuminate\Support\Carbon::parse($fechaSupervisor)->format('d/m/Y H:i:s')
                                : 'Pendiente' }}
                        </span>

                        <span>
                            {{ !$firmaSupervisor
                                ? 'Pendiente de firma'
                                : ($firmaSupervisorValida
                                    ? 'Integridad verificada'
                                    : 'Integridad no verificada') }}
                        </span>
                    </div>

                </div>

                @if($firmaSupervisor && !$firmaSupervisorValida)
                    <div class="ec-show-signature-invalid">
                        <strong>La firma del supervisor no pudo verificarse.</strong>
                        Este arqueo requiere revisión antes de aceptar nuevas firmas.
                    </div>
                @elseif($firmaReceptor && !$firmaReceptorValida)
                    <div class="ec-show-signature-invalid">
                        <strong>La firma del receptor no pudo verificarse.</strong>
                        Es necesario revisar la integridad del documento.
                    </div>
                @elseif($firmaSupervisorValida && $firmaReceptorValida)
                    <div class="ec-show-signature-valid">
                        <strong>Firmas electrónicas verificadas.</strong>
                        Se ha comprobado la integridad de las firmas del supervisor y del receptor.
                    </div>
                @elseif($firmaSupervisorValida && !$firmaReceptor)
                    <div class="ec-show-signature-pending">
                        <strong>Firma del supervisor verificada.</strong>
                        El arqueo está pendiente de confirmación y firma del receptor.
                    </div>
                @endif

                @if($esReceptor && $estado === 'PENDIENTE_FIRMA_RECEPTOR' && $firmaSupervisorValida && !$firmaReceptor)

                    <form
                        method="POST"
                        action="{{ route('receptor.sorpresivos.firmar', $registro->id) }}"
                        id="ec-show-firma-form"
                        class="ec-show-sign-form"
                    >
                        @csrf

                        <strong>Confirmación del receptor</strong>

                        <p>
                            Revise el conteo y los importes antes de registrar
                            su firma electrónica. Una vez firmado, el arqueo
                            quedará certificado.
                        </p>

                        <label class="ec-show-checkbox">
                            <input
                                type="checkbox"
                                name="confirmar_firma"
                                value="1"
                                required
                            >

                            <span>
                                Confirmo que revisé el arqueo de Jefatura de Caja
                                y autorizo mi firma electrónica.
                            </span>
                        </label>

                        <button
                            type="submit"
                            class="ec-show-btn"
                            id="ec-show-firma-btn"
                        >
                            Firmar arqueo de Jefatura de Caja
                        </button>
                    </form>

                @endif

            </section>

        </div>
    </div>
</div>

{{-- MODAL PERSONALIZADO DE FIRMA ELECTRÓNICA --}}

@if($esReceptor && $estado === 'PENDIENTE_FIRMA_RECEPTOR' && $firmaSupervisorValida && !$firmaReceptor)

<div
    id="ec-firma-modal"
    class="ec-firma-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="ec-firma-modal-titulo"
    aria-describedby="ec-firma-modal-descripcion"
    hidden
>
    <div class="ec-firma-modal-dialog">

        <div class="ec-firma-modal-header">

            <div class="ec-firma-modal-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
            </div>

            <h2 id="ec-firma-modal-titulo">
                Confirmar firma electrónica
            </h2>

        </div>

        <div class="ec-firma-modal-body">

            <p id="ec-firma-modal-descripcion">
                ¿Confirma que ha revisado el arqueo de
                <strong>Jefatura de Caja</strong> y desea
                registrar su firma electrónica definitiva?
            </p>

            <div class="ec-firma-modal-warning">
                <strong>Importante:</strong>
                Esta acción registrará su firma electrónica
                en el documento. Verifique que los datos
                y montos sean correctos antes de continuar.
            </div>

        </div>

        <div class="ec-firma-modal-footer">

            <button
                type="button"
                class="ec-firma-modal-btn cancelar"
                id="ec-firma-modal-cancelar"
            >
                Cancelar
            </button>

            <button
                type="button"
                class="ec-firma-modal-btn confirmar"
                id="ec-firma-modal-confirmar"
            >
                Confirmar firma
            </button>

        </div>

    </div>
</div>

@endif

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const form = document.getElementById('ec-show-firma-form');
    const modal = document.getElementById('ec-firma-modal');
    const botonFirma = document.getElementById('ec-show-firma-btn');
    const botonCancelar = document.getElementById('ec-firma-modal-cancelar');
    const botonConfirmar = document.getElementById('ec-firma-modal-confirmar');

    if (
        !form ||
        !modal ||
        !botonFirma ||
        !botonCancelar ||
        !botonConfirmar
    ) {
        return;
    }

    let enviando = false;
    let confirmado = false;
    let elementoAnterior = null;

    function abrirModal() {
        if (enviando) {
            return;
        }

        elementoAnterior = document.activeElement;

        modal.hidden = false;

        document.body.classList.add(
            'ec-firma-modal-abierto'
        );

        botonConfirmar.focus();
    }

    function cerrarModal() {
        if (enviando) {
            return;
        }

        modal.hidden = true;

        document.body.classList.remove(
            'ec-firma-modal-abierto'
        );

        if (
            elementoAnterior &&
            typeof elementoAnterior.focus === 'function'
        ) {
            elementoAnterior.focus();
        }
    }

    form.addEventListener('submit', function (event) {
        if (enviando) {
            event.preventDefault();
            return;
        }

        if (!confirmado) {
            event.preventDefault();

            if (!form.reportValidity()) {
                return;
            }

            abrirModal();
            return;
        }

        enviando = true;

        botonFirma.disabled = true;
        botonConfirmar.disabled = true;
        botonCancelar.disabled = true;

        botonConfirmar.textContent = 'Registrando firma...';
    });

    botonCancelar.addEventListener('click', function () {
        cerrarModal();
    });

    botonConfirmar.addEventListener('click', function () {
        if (enviando) {
            return;
        }

        if (!form.reportValidity()) {
            cerrarModal();
            return;
        }

        confirmado = true;

        form.requestSubmit();
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (modal.hidden || enviando) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            cerrarModal();
            return;
        }

        if (event.key === 'Tab') {
            const controles = [
                botonCancelar,
                botonConfirmar
            ];

            const primero = controles[0];
            const ultimo = controles[controles.length - 1];

            if (event.shiftKey && document.activeElement === primero) {
                event.preventDefault();
                ultimo.focus();
            } else if (
                !event.shiftKey &&
                document.activeElement === ultimo
            ) {
                event.preventDefault();
                primero.focus();
            }
        }
    });

    window.addEventListener('pageshow', function () {
        enviando = false;
        confirmado = false;

        modal.hidden = true;

        document.body.classList.remove(
            'ec-firma-modal-abierto'
        );

        botonFirma.disabled = false;
        botonConfirmar.disabled = false;
        botonCancelar.disabled = false;

        botonConfirmar.textContent = 'Confirmar firma';
    });
})();
</script>
@endpush
