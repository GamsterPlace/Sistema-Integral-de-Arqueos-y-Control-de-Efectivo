
@php
    $layout = collect(
        $rutaBase === 'jefecaja'
            ? ['layouts.jefecaja', 'layouts.jefe-caja', 'layouts.jefe_caja']
            : ['layouts.subjefecaja', 'layouts.subjefe-caja', 'layouts.subjefe_caja']
    )->first(fn ($nombre) => view()->exists($nombre));

    if (!$layout) {
        throw new \RuntimeException(
            'No se encontró el layout del rol. Verifique el nombre del archivo en resources/views/layouts.'
        );
    }
@endphp

@extends($layout)

@section('title', 'Revisión y Certificación de Arqueo')

@push('styles')
<style>
.cr-page{display:grid;gap:20px}
.cr-header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
.cr-header h1{margin:0;color:#06284f;font-size:27px}
.cr-header p{margin:8px 0 0;color:#708398;font-size:13px}
.cr-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.cr-back{display:inline-flex;padding:10px 14px;border:1px solid #d9e5f0;border-radius:10px;background:#fff;color:#164c96;text-decoration:none;font-size:12px;font-weight:800}
.cr-print{background:#164c96;color:#fff;border-color:#164c96}
.cr-print:hover{background:#0c3976;color:#fff}
.cr-panel{background:#fff;border:1px solid #dfe7ee;border-radius:16px;padding:22px;box-shadow:0 5px 20px rgba(6,40,79,.045)}
.cr-panel h2{margin:0 0 18px;color:#06284f;font-size:18px}
.cr-alert{padding:15px 17px;border-radius:11px;font-size:13px;line-height:1.65}
.cr-alert.warning{background:#fff7e8;border:1px solid #f0dba8;color:#93631d}
.cr-alert.error{background:#fff2f2;border:1px solid #efcaca;color:#aa3535}
.cr-alert.success{background:#ebf8f0;border:1px solid #bde5cd;color:#167344}
.cr-confirm{padding:20px;border:1px solid #cbdff2;border-radius:13px;background:#f4f9ff}
.cr-confirm h3{margin:0 0 10px;color:#0b3d78;font-size:17px}
.cr-confirm p{color:#5e7690;font-size:13px;line-height:1.65}
.cr-checkbox{display:flex;align-items:flex-start;gap:11px;margin:18px 0;color:#254969;font-size:13px;line-height:1.6;cursor:pointer}
.cr-checkbox input{margin-top:4px;width:17px;height:17px;accent-color:#164c96;flex:0 0 auto}
.cr-submit{display:inline-flex;align-items:center;justify-content:center;padding:13px 20px;border:0;border-radius:10px;background:#164c96;color:#fff;font-size:13px;font-weight:800;cursor:pointer}
.cr-submit:hover{background:#0a3977}
.cr-submit:disabled{opacity:.6;cursor:not-allowed}
.cr-meta{color:#718398;font-size:12px;margin-top:12px}
.cr-signatures{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.cr-signature{padding:17px;border-radius:12px;border:1px solid #dce6ee;background:#fafcfe}
.cr-signature span{display:block;color:#6b8094;font-size:12px;font-weight:750}
.cr-signature strong{display:block;margin-top:8px;font-size:14px}
.cr-valid{color:#16814c}
.cr-invalid{color:#b53f3f}
.cr-pending{color:#a16d20}

/* Documento físico de arqueo */
.cr-paper{background:#fff;border:1px solid #d5e1eb;border-radius:13px;overflow:hidden;box-shadow:0 8px 25px rgba(15,45,75,.06)}
.cr-paper-header{display:grid;grid-template-columns:170px minmax(0,1fr) 175px;gap:16px;align-items:center;padding:24px 27px;border-bottom:2px solid #174a73}
.cr-paper-logo{display:block;width:155px;max-width:100%;max-height:75px;object-fit:contain}
.cr-paper-heading{text-align:center;font-size:20px;font-weight:800;color:#123e65}
.cr-paper-number{text-align:right;min-width:0}
.cr-paper-number small{display:block;color:#758b9c;font-size:10px;font-weight:800}
.cr-paper-number strong{display:block;margin-top:6px;color:#bd202b;font-size:15px;overflow-wrap:anywhere}
.cr-paper-body{padding:25px 29px 30px}
.cr-paper-info{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:13px 16px;margin-bottom:25px}
.cr-paper-field{min-width:0}
.cr-paper-field.wide{grid-column:span 2}
.cr-paper-field label{display:block;margin-bottom:5px;color:#6d8293;font-size:10px;font-weight:800}
.cr-paper-field strong{display:block;min-height:27px;padding:5px 2px;border-bottom:1px solid #b6c6d2;font-size:12px;color:#25465f;overflow-wrap:anywhere}
.cr-paper-columns{display:grid;grid-template-columns:minmax(0,56fr) minmax(0,44fr);gap:25px;align-items:start}
.cr-paper-section{margin-bottom:19px}
.cr-paper-section h3{margin:0 0 8px;color:#16486f;font-size:16px;font-weight:800}
.cr-paper-table{width:100%;border-collapse:collapse;table-layout:fixed}
.cr-paper-table th{padding:7px 3px;color:#536e82;font-size:10px;font-weight:800;text-align:left}
.cr-paper-table td{padding:7px 3px;color:#36536a;font-size:12px;white-space:nowrap}
.cr-paper-table th:nth-child(1){width:25%}
.cr-paper-table th:nth-child(2){width:17%;text-align:center}
.cr-paper-table th:nth-child(3){width:29%;text-align:right}
.cr-paper-table th:nth-child(4){width:29%;text-align:right}
.cr-paper-table td:nth-child(2){text-align:center}
.cr-paper-table td:nth-child(3),.cr-paper-table td:nth-child(4){text-align:right}
.cr-paper-rule{display:inline-block;min-width:36px;border-bottom:1px solid #8ea8b9;padding-bottom:3px}
.cr-paper-table td:nth-child(3) .cr-paper-rule,
.cr-paper-table td:nth-child(4) .cr-paper-rule{min-width:67px}
.cr-paper-group-total{color:#174a73!important;font-weight:800}
.cr-paper-grand{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 5px;border-top:2px solid #16486f;border-bottom:2px solid #16486f;font-size:13px;font-weight:800;color:#123e65}
.cr-paper-grand strong{font-size:18px}
.cr-paper-verification{padding-top:245px}
.cr-paper-verification h3{text-align:center;margin:0 0 12px;color:#16486f;font-size:16px}
.cr-paper-check-line{display:flex;justify-content:space-between;gap:12px;padding:11px 2px;border-bottom:1px solid #bacbd7;font-size:12px}
.cr-paper-check-line strong{color:#16456d;white-space:nowrap}
.cr-paper-check-line.difference{border-bottom:2px solid #16486f;font-weight:800}
.cr-paper-kind{text-align:right;margin-top:7px;color:#4a6d83;font-size:11px;font-weight:800}
.cr-paper-notes{margin-top:20px}
.cr-paper-notes h4{margin:14px 0 7px;color:#16486f;font-size:13px}
.cr-paper-note{min-height:45px;padding:10px 4px;border-top:1px solid #b9cad7;border-bottom:1px solid #b9cad7;font-size:12px;color:#415c71;line-height:1.5;white-space:pre-wrap;overflow-wrap:anywhere}
.cr-paper-signatures{display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-top:35px}
.cr-paper-signature{text-align:center;min-width:0}
.cr-paper-signature .caption{display:block;margin-bottom:14px;color:#536e82;font-size:11px}
.cr-paper-signature .name{display:block;min-height:23px;padding-bottom:7px;border-bottom:1px solid #819eaf;color:#123e65;font-size:13px;font-weight:800;overflow-wrap:anywhere}
.cr-paper-signature .role{display:block;margin-top:8px;color:#243d53;font-size:12px;font-weight:800}
.cr-paper-signature .status{display:block;margin-top:6px;color:#5b778b;font-size:11px}
.cr-paper-signature .time{display:block;margin-top:6px;color:#698095;font-size:10px}
.cr-paper-integrity{text-align:center;margin-top:24px;color:#567589;font-size:11px}
.cr-paper-footer{text-align:center;margin-top:13px;padding-top:9px;border-top:1px solid #d6e2e9;color:#8193a0;font-size:10px}

@media(max-width:950px){
.cr-paper-columns{grid-template-columns:1fr}
.cr-paper-verification{padding-top:0}
.cr-paper-info{grid-template-columns:repeat(2,minmax(0,1fr))}
.cr-paper-header{grid-template-columns:135px 1fr 150px}
}
@media(max-width:650px){
.cr-paper-header{grid-template-columns:1fr;text-align:center}
.cr-paper-logo{margin:auto}
.cr-paper-number{text-align:center}
.cr-paper-body{padding:18px 12px}
.cr-paper-info{grid-template-columns:1fr}
.cr-paper-field.wide{grid-column:span 1}
.cr-paper-signatures,.cr-signatures{grid-template-columns:1fr}
.cr-paper-table td{font-size:10px}
.cr-paper-table th{font-size:9px}
.cr-paper-table td:nth-child(3) .cr-paper-rule,
.cr-paper-table td:nth-child(4) .cr-paper-rule{min-width:45px}
.cr-header h1{font-size:22px}
}
@media print{
.cr-header,.cr-confirm,.cr-panel,.cr-alert{display:none!important}
.cr-page{display:block}
.cr-paper{border:0;box-shadow:none}
.cr-paper-verification{padding-top:180px}
}
</style>
@endpush

@section('content')
@php
    $billetes = [200, 100, 50, 20, 10, 5, 1];
    $monedas = ['1.00', '0.50', '0.25', '0.10', '0.05', '0.01'];
    $otrosConceptos = [
        'CHEQUES' => 'Cheques',
        'VALES' => 'Vales',
        'OTROS_VALORES' => 'Otros valores',
    ];

    $detalles = $arqueo->detalles ?? collect();
    $firmas = $arqueo->firmas ?? collect();

    $firmaReceptor = $firmas->firstWhere('tipo_firma', 'RECEPTOR');
    $firmaCertificador = $firmas->firstWhere('tipo_firma', 'CERTIFICADOR');

    $moneda = fn ($valor) => 'Q '.number_format((float) ($valor ?? 0), 2, '.', ',');

    $detalleDenominacion = function ($tipo, $valor) use ($detalles) {
        return $detalles->first(fn ($detalle) =>
            $detalle->tipo === $tipo
            && (int) round((float) $detalle->denominacion * 100)
                === (int) round((float) $valor * 100)
        );
    };

    $detalleConcepto = fn ($concepto) =>
        $detalles->first(fn ($detalle) =>
            $detalle->tipo === 'OTRO'
            && $detalle->concepto === $concepto
        );

    $diferencia = (float) $arqueo->diferencia;
    $tieneDiferencia = abs($diferencia) > 0.00001;
    $tieneJustificacion = trim((string) $arqueo->justificacion_diferencia) !== '';

    $esPendiente = $arqueo->estado === 'PENDIENTE';
    $esCertificado = $arqueo->estado === 'CERTIFICADO';

    $puedeCertificar =
        $esPendiente &&
        $arqueo->certificado_at === null &&
        $arqueo->anulado_at === null &&
        $arqueo->firmado_receptor_at !== null &&
        $firmaReceptorValida &&
        (!$tieneDiferencia || $tieneJustificacion);

    $nombreCertificador = $firmaCertificador
        ? (data_get($firmaCertificador, 'firmante_nombre_historico')
            ?: data_get($firmaCertificador, 'nombre_firmante')
            ?: data_get($firmaCertificador, 'usuario.nombre_completo')
            ?: data_get($firmaCertificador, 'usuario.nombre'))
        : null;

    $cargoCertificador = $firmaCertificador
        ? (data_get($firmaCertificador, 'cargo_historico')
            ?: data_get($firmaCertificador, 'cargo_firmante')
            ?: 'Jefe / Subjefe de Caja')
        : 'Jefe / Subjefe de Caja';

    $fechaFirmaCertificador = $firmaCertificador
        ? (data_get($firmaCertificador, 'fecha_firma')
            ?: $arqueo->certificado_at)
        : null;

    $fecha = $arqueo->fecha_operativa
        ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y')
        : '—';

    $inicio = $arqueo->inicio_at
        ? \Illuminate\Support\Carbon::parse($arqueo->inicio_at)->format('H:i')
        : '—';

    $fin = $arqueo->finalizado_at
        ? \Illuminate\Support\Carbon::parse($arqueo->finalizado_at)->format('H:i')
        : '—';
@endphp

<div class="cr-page">

    <header class="cr-header">
        <div>
            <h1>Revisión de Arqueo</h1>
            <p>
                Número:
                <strong>{{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}</strong>
                · Estado: <strong>{{ $arqueo->estado }}</strong>
            </p>
        </div>

        <div class="cr-actions">
            <a
                href="{{ route($rutaBase.'.certificaciones.imprimir', $arqueo->id) }}"
                class="cr-back cr-print"
                target="_blank"
                rel="noopener noreferrer"
            >
                Imprimir PDF
            </a>

            <a
                href="{{ route($rutaBase.'.certificaciones.index') }}"
                class="cr-back"
            >
                ← Volver a Certificaciones
            </a>
        </div>
    </header>

    @if($errors->any())
        <div class="cr-alert error" role="alert">
            <strong>No se pudo completar la operación.</strong>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div class="cr-paper">

        <header class="cr-paper-header">
            <div>
                <img
                    src="{{ asset('images/logos/ecosaba.png') }}"
                    alt="ECOSABA"
                    class="cr-paper-logo"
                >
            </div>

            <div class="cr-paper-heading">
                ARQUEO Y CORTE DE CAJA
            </div>

            <div class="cr-paper-number">
                <small>NÚMERO DE ARQUEO</small>
                <strong>{{ $arqueo->numero_arqueo }}</strong>
            </div>
        </header>

        <div class="cr-paper-body">

            <div class="cr-paper-info">
                <div class="cr-paper-field wide">
                    <label>NOMBRE DEL RECEPTOR</label>
                    <strong>{{ $arqueo->receptor_nombre_historico ?? '—' }}</strong>
                </div>

                <div class="cr-paper-field">
                    <label>FECHA</label>
                    <strong>{{ $fecha }}</strong>
                </div>

                <div class="cr-paper-field">
                    <label>H. INICIO</label>
                    <strong>{{ $inicio }}</strong>
                </div>

                <div class="cr-paper-field">
                    <label>H. FIN</label>
                    <strong>{{ $fin }}</strong>
                </div>

                <div class="cr-paper-field">
                    <label>AGENCIA</label>
                    <strong>{{ $arqueo->agencia_nombre_historico ?? '—' }}</strong>
                </div>

                <div class="cr-paper-field">
                    <label>VENTANILLA</label>
                    <strong>{{ $arqueo->ventanilla_codigo_historico ?? '—' }}</strong>
                </div>

                <div class="cr-paper-field">
                    <label>TURNO</label>
                    <strong>{{ $arqueo->turno_nombre_historico ?? '—' }}</strong>
                </div>
            </div>

            <div class="cr-paper-columns">

                <div>

                    <section class="cr-paper-section">
                        <h3>Billetes</h3>

                        <table class="cr-paper-table">
                            <thead>
                                <tr>
                                    <th>Denominación</th>
                                    <th>Cantidad</th>
                                    <th>Sub-total</th>
                                    <th>Totales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($billetes as $valor)
                                    @php($detalle = $detalleDenominacion('BILLETE', $valor))
                                    <tr>
                                        <td>{{ $moneda($valor) }}</td>
                                        <td><span class="cr-paper-rule">{{ number_format((int) ($detalle?->cantidad ?? 0)) }}</span></td>
                                        <td><span class="cr-paper-rule">{{ $moneda($detalle?->subtotal) }}</span></td>
                                        <td class="cr-paper-group-total">
                                            @if($loop->last)
                                                <span class="cr-paper-rule">{{ $moneda($arqueo->total_billetes) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </section>

                    <section class="cr-paper-section">
                        <h3>Monedas</h3>

                        <table class="cr-paper-table">
                            <thead>
                                <tr>
                                    <th>Denominación</th>
                                    <th>Cantidad</th>
                                    <th>Sub-total</th>
                                    <th>Totales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monedas as $valor)
                                    @php($detalle = $detalleDenominacion('MONEDA', $valor))
                                    <tr>
                                        <td>{{ $moneda($valor) }}</td>
                                        <td><span class="cr-paper-rule">{{ number_format((int) ($detalle?->cantidad ?? 0)) }}</span></td>
                                        <td><span class="cr-paper-rule">{{ $moneda($detalle?->subtotal) }}</span></td>
                                        <td class="cr-paper-group-total">
                                            @if($loop->last)
                                                <span class="cr-paper-rule">{{ $moneda($arqueo->total_monedas) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </section>

                    <section class="cr-paper-section">
                        <h3>Otros valores</h3>

                        <table class="cr-paper-table">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th>Cantidad</th>
                                    <th>Sub-total</th>
                                    <th>Totales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($otrosConceptos as $codigo => $nombre)
                                    @php($detalle = $detalleConcepto($codigo))
                                    <tr>
                                        <td>{{ $nombre }}</td>
                                        <td><span class="cr-paper-rule">{{ (int) ($detalle?->cantidad ?? 0) }}</span></td>
                                        <td><span class="cr-paper-rule">{{ $moneda($detalle?->subtotal) }}</span></td>
                                        <td class="cr-paper-group-total">
                                            @if($loop->last)
                                                <span class="cr-paper-rule">{{ $moneda($arqueo->total_otros_valores) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </section>

                    <div class="cr-paper-grand">
                        <span>TOTAL ARQUEADO</span>
                        <strong>{{ $moneda($arqueo->total_arqueado) }}</strong>
                    </div>

                </div>

                <div class="cr-paper-verification">

                    <h3>COMPROBACIÓN</h3>

                    <div class="cr-paper-check-line">
                        <span>Total Arqueado</span>
                        <strong>{{ $moneda($arqueo->total_arqueado) }}</strong>
                    </div>

                    <div class="cr-paper-check-line">
                        <span>Total Corte</span>
                        <strong>{{ $moneda($arqueo->total_corte) }}</strong>
                    </div>

                    <div class="cr-paper-check-line difference">
                        <span>Diferencia</span>
                        <strong>{{ $diferencia < 0 ? '-' : '' }}{{ $moneda(abs($diferencia)) }}</strong>
                    </div>

                    <div class="cr-paper-kind">
                        {{ $diferencia > 0 ? 'SOBRANTE' : ($diferencia < 0 ? 'FALTANTE' : 'CUADRADO') }}
                    </div>

                    <div class="cr-paper-notes">
                        <h4>Certificación / Justificación</h4>
                        <div class="cr-paper-note">{{ $arqueo->justificacion_diferencia ?: 'Sin justificación registrada.' }}</div>

                        <h4>OBSERVACIONES</h4>
                        <div class="cr-paper-note">{{ $arqueo->observaciones ?: 'Sin observaciones registradas.' }}</div>
                    </div>

                </div>

            </div>

            <div class="cr-paper-signatures">

                <div class="cr-paper-signature">
                    <span class="caption">Firma</span>
                    <span class="name">
                        {{ $firmaReceptor ? ($arqueo->receptor_nombre_historico ?: 'Nombre no disponible') : '—' }}
                    </span>
                    <span class="role">Receptor - Pagador</span>
                    <span class="status">
                        {{ $firmaReceptor ? 'Firmado electrónicamente' : 'Pendiente de firma' }}
                    </span>
                    <span class="time">
                        {{ $firmaReceptor && $arqueo->firmado_receptor_at
                            ? \Illuminate\Support\Carbon::parse($arqueo->firmado_receptor_at)->format('d/m/Y H:i:s')
                            : '—' }}
                    </span>
                </div>

                <div class="cr-paper-signature">
                    <span class="caption">Firma</span>
                    <span class="name">
                        {{ $firmaCertificador ? ($nombreCertificador ?: 'Nombre del firmante no disponible') : '—' }}
                    </span>
                    <span class="role">{{ $cargoCertificador }}</span>
                    <span class="status">
                        {{ $firmaCertificador ? 'Firmado electrónicamente' : 'Pendiente de certificación' }}
                    </span>
                    <span class="time">
                        {{ $fechaFirmaCertificador
                            ? \Illuminate\Support\Carbon::parse($fechaFirmaCertificador)->format('d/m/Y H:i:s')
                            : '—' }}
                    </span>
                </div>

            </div>

            <div class="cr-paper-integrity">
                Integridad de firma del receptor:
                <strong>{{ $firmaReceptor && $firmaReceptorValida ? 'VERIFICADA' : 'NO VERIFICADA' }}</strong>
            </div>

            <div class="cr-paper-footer">
                ECOSABA R.L. · Sistema Integral de Arqueos y Control de Efectivo · Documento generado electrónicamente · {{ $arqueo->numero_arqueo }}
            </div>

        </div>
    </div>

    @if($tieneDiferencia && !$tieneJustificacion)
        <div class="cr-alert warning">
            <strong>Diferencia pendiente de justificación.</strong>
            Este arqueo presenta un faltante o sobrante.
            No podrá certificarse hasta que se complete
            el procedimiento autorizado de justificación.
        </div>
    @elseif($tieneDiferencia)
        <div class="cr-alert warning">
            Este arqueo presenta una diferencia registrada.
            Revise cuidadosamente la justificación antes de certificar.
        </div>
    @endif

    <section class="cr-panel">
        <h2>Verificación de Firmas Electrónicas</h2>

        <div class="cr-signatures">

            <div class="cr-signature">
                <span>Firma del receptor</span>

                @if($firmaReceptorValida)
                    <strong class="cr-valid">
                        Íntegra y verificada
                    </strong>
                @else
                    <strong class="cr-invalid">
                        No válida o no disponible
                    </strong>
                @endif

                @if($arqueo->firmado_receptor_at)
                    <div class="cr-meta">
                        Firmado:
                        {{ $arqueo->firmado_receptor_at->format('d/m/Y H:i:s') }}
                    </div>
                @endif
            </div>

            <div class="cr-signature">
                <span>Firma del certificador</span>

                @if($esCertificado)
                    @if($firmaCertificadorValida)
                        <strong class="cr-valid">
                            Íntegra y verificada
                        </strong>
                    @else
                        <strong class="cr-invalid">
                            No válida o no disponible
                        </strong>
                    @endif

                    @if($arqueo->certificado_at)
                        <div class="cr-meta">
                            Certificado:
                            {{ $arqueo->certificado_at->format('d/m/Y H:i:s') }}
                        </div>
                    @endif
                @else
                    <strong class="cr-pending">
                        Pendiente de certificación
                    </strong>
                @endif
            </div>

        </div>
    </section>

    @if($esCertificado)
        <div class="cr-alert {{ $firmaCertificadorValida ? 'success' : 'error' }}">
            @if($firmaCertificadorValida)
                <strong>Arqueo certificado.</strong>
                La firma electrónica del certificador ha sido verificada.
            @else
                <strong>Advertencia de integridad.</strong>
                El arqueo figura como certificado, pero no se pudo
                verificar correctamente su firma electrónica.
                Debe revisarse antes de utilizarlo como documento validado.
            @endif
        </div>

    @elseif($puedeCertificar)
        <section class="cr-confirm">
            <h3>Certificación Electrónica</h3>

            <p>
                Al confirmar esta operación, el sistema registrará
                su firma electrónica como responsable de caja.
                Revise los importes y el desglose antes de continuar.
            </p>

            <form
                method="POST"
                action="{{ route($rutaBase.'.certificaciones.store', $arqueo->id) }}"
                id="formCertificacion"
            >
                @csrf

                <label class="cr-checkbox">
                    <input
                        type="checkbox"
                        name="confirmar_certificacion"
                        value="1"
                        required
                    >

                    <span>
                        He revisado el arqueo, sus denominaciones,
                        importes, diferencia, observaciones y firma
                        electrónica del receptor. Confirmo que deseo
                        certificar electrónicamente este registro.
                    </span>
                </label>

                <button type="submit" class="cr-submit" id="btnCertificar">
                    Firmar y Certificar Arqueo
                </button>
            </form>
        </section>

    @else
        <div class="cr-alert warning">
            <strong>Certificación no disponible.</strong>
            Este arqueo no cumple actualmente todas las condiciones
            necesarias para realizar la certificación electrónica.
            Verifique su estado, la firma del receptor y las
            justificaciones correspondientes.
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formCertificacion');
    const boton = document.getElementById('btnCertificar');

    if (!form || !boton) return;

    form.addEventListener('submit', function () {
        if (!form.checkValidity()) return;

        boton.disabled = true;
        boton.textContent = 'Procesando certificación...';
    });
});
</script>
@endpush
