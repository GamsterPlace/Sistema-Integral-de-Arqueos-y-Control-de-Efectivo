
@extends('layouts.receptor')

@section('title', 'Detalle de arqueo')

@push('styles')
<style>
.ec-show{max-width:1120px;margin:0 auto 36px;font-family:Arial,Helvetica,sans-serif;color:#263e52}
.ec-show *{box-sizing:border-box}
.ec-show-top{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:22px}
.ec-show-top h1{margin:0 0 6px;font-size:26px;color:#123e68}
.ec-show-top p{margin:0;font-size:13px;color:#6e8193}
.ec-show-actions{display:flex;gap:10px;flex-wrap:wrap}
.ec-show-btn{display:inline-flex;align-items:center;justify-content:center;padding:12px 18px;min-height:44px;border-radius:9px;background:#155b98;color:#fff!important;text-decoration:none;font-size:13px;font-weight:800}
.ec-show-btn.secondary{background:#e6f0f9;color:#15517e!important}
.ec-show-btn:hover{filter:brightness(.95)}
.ec-show-paper{background:#fff;border:1px solid #d5e1eb;border-radius:14px;overflow:hidden;box-shadow:0 12px 35px rgba(15,45,75,.07)}
.ec-show-header{display:grid;grid-template-columns:170px 1fr 180px;gap:20px;align-items:center;padding:28px 30px;border-bottom:2px solid #dbe6ee}
.ec-show-logo{max-width:165px;max-height:75px;object-fit:contain}
.ec-show-heading{text-align:center}
.ec-show-heading h2{margin:0;color:#153f67;font-size:24px;text-transform:uppercase}
.ec-show-heading p{margin:8px 0 0;font-size:14px;font-weight:800;color:#536b7e;text-transform:uppercase}
.ec-show-number{text-align:right}
.ec-show-number small{display:block;font-size:10px;color:#7b8fa0;font-weight:800;text-transform:uppercase}
.ec-show-number strong{display:block;margin-top:7px;font-size:12px;color:#194c75;overflow-wrap:anywhere}
.ec-show-body{padding:30px 35px 35px}
.ec-show-section{margin-bottom:28px}
.ec-show-section h3{margin:0 0 16px;padding-bottom:10px;border-bottom:1px solid #d7e2eb;color:#17456d;font-size:16px}
.ec-show-info{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px}
.ec-show-info-item{min-width:0}
.ec-show-info-item.wide{grid-column:span 2}
.ec-show-info-item label{display:block;margin-bottom:7px;font-size:11px;color:#61788c;font-weight:800}
.ec-show-info-item strong{display:block;padding:8px 2px;border-bottom:1px solid #a8bac9;font-size:13px;color:#25465f;overflow-wrap:anywhere}
.ec-show-tables{display:grid;grid-template-columns:1fr 1fr;gap:30px}
.ec-show-table{width:100%;border-collapse:collapse;table-layout:fixed}
.ec-show-table th{text-align:left;padding:10px 7px;border-bottom:1px solid #b9cbd9;font-size:11px;color:#587084}
.ec-show-table td{padding:10px 7px;border-bottom:1px solid #edf1f5;font-size:12px;color:#36536a}
.ec-show-table th:nth-child(2),.ec-show-table td:nth-child(2){text-align:center}
.ec-show-table th:last-child,.ec-show-table td:last-child{text-align:right}
.ec-show-table td:last-child{font-weight:800;color:#16466e}
.ec-show-table tfoot td{padding-top:16px;border-top:2px solid #c2d1dd;border-bottom:0;font-weight:800;color:#133f68;font-size:13px}
.ec-show-grand{display:flex;justify-content:flex-end;align-items:center;gap:22px;flex-wrap:wrap;padding:20px 24px;border:1px solid #c8dced;background:#eef6fc;border-radius:11px;margin:24px 0 30px;font-size:14px;font-weight:800;color:#17466e}
.ec-show-grand strong{font-size:26px;color:#0c4a7e}
.ec-show-check{max-width:530px;margin-left:auto;background:#f7fbfe;border:1px solid #d7e4ee;border-radius:11px;padding:18px 22px}
.ec-show-check h3{text-align:center;border:0;margin:0 0 14px;color:#17466e;font-size:17px}
.ec-show-check-line{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:13px 0;border-bottom:1px solid #dce6ee;font-size:13px;color:#506a7e}
.ec-show-check-line strong{font-size:16px;color:#16456d}
.ec-show-check-line.last{border-top:2px solid #c7d8e5;border-bottom:0;margin-top:8px;font-weight:800}
.ec-show-check-status{text-align:right;margin-top:8px;font-size:12px;font-weight:800}
.ec-show-note{padding:15px 18px;background:#f8fbfd;border:1px solid #e0e9f0;border-radius:9px;font-size:13px;color:#415c71;line-height:1.7;white-space:pre-wrap;overflow-wrap:anywhere}
.ec-show-note.empty{color:#8193a2;font-style:italic}
.ec-show-signatures{display:grid;grid-template-columns:1fr 1fr;gap:50px;margin-top:35px}
.ec-show-signature{text-align:center;padding-top:20px}
.ec-show-signature small{display:block;min-height:35px;color:#698096;font-size:11px}
.ec-show-signature strong{display:block;border-top:1px solid #91a5b6;padding-top:11px;color:#38566d;font-size:12px}
.ec-show-signature span{display:block;margin-top:7px;color:#657f92;font-size:11px}
.ec-show-status{display:inline-flex;padding:7px 12px;border-radius:7px;font-size:11px;font-weight:800;background:#edf2f6;color:#476174}
.ec-show-status.certificado{background:#e7f5ec;color:#18703e}
.ec-show-status.pendiente{background:#fff4de;color:#956313}
.ec-show-status.anulado{background:#fce9e9;color:#a23737}
.ec-show-signature-valid{margin-top:25px;padding:15px 18px;border:1px solid #c8e3d1;background:#f1faf4;border-radius:10px;color:#276643;font-size:12px;line-height:1.7}
.ec-show-signature-invalid{margin-top:25px;padding:15px 18px;border:1px solid #edcece;background:#fff5f5;border-radius:10px;color:#983c3c;font-size:12px;line-height:1.7}
.ec-show-alert{padding:14px 18px;margin-bottom:18px;border:1px solid #c8e3d1;background:#eff9f2;color:#246a42;border-radius:9px;font-size:13px}
@media(max-width:850px){
.ec-show-header{grid-template-columns:1fr;text-align:center}
.ec-show-logo{margin:auto}
.ec-show-number{text-align:center}
.ec-show-tables{grid-template-columns:1fr}
.ec-show-info{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:550px){
.ec-show-body{padding:20px 15px}
.ec-show-header{padding:20px 15px}
.ec-show-info{grid-template-columns:1fr}
.ec-show-info-item.wide{grid-column:span 1}
.ec-show-signatures{grid-template-columns:1fr;gap:20px}
.ec-show-actions{width:100%}
.ec-show-btn{flex:1}
}
@media print{
.ec-show-top,.ec-show-alert{display:none!important}
.ec-show-paper{border:0;box-shadow:none}
.ec-show-body{padding:20px}
}
</style>
@endpush

@section('content')
@php
    $billetes = [200, 100, 50, 20, 10, 5, 1];

    $monedas = [
        '1.00',
        '0.50',
        '0.25',
        '0.10',
        '0.05',
        '0.01'
    ];

    $detallesBilletes = $arqueo->detalles
        ->where('tipo', 'BILLETE');

    $detallesMonedas = $arqueo->detalles
        ->where('tipo', 'MONEDA');

    $detallesOtros = $arqueo->detalles
        ->where('tipo', 'OTRO');

    $otrosConceptos = [
        'CHEQUES' => 'Cheques',
        'VALES' => 'Vales',
        'OTROS_VALORES' => 'Otros valores'
    ];

    $diferencia = (float) $arqueo->diferencia;

    $firmaReceptor = $arqueo->firmas
        ->firstWhere('tipo_firma', 'RECEPTOR');

    $firmaCertificador = $arqueo->firmas
        ->firstWhere('tipo_firma', 'CERTIFICADOR');

    $estado = strtoupper((string) $arqueo->estado);

    $inicio = $arqueo->inicio_at
        ? \Illuminate\Support\Carbon::parse($arqueo->inicio_at)
        : null;

    $finalizacion = $arqueo->finalizado_at
        ? \Illuminate\Support\Carbon::parse($arqueo->finalizado_at)
        : null;
@endphp

<div class="ec-show">

    <div class="ec-show-top">
        <div>
            <h1>Detalle de arqueo</h1>
            <p>Consulta del arqueo ordinario registrado.</p>
        </div>

        <div class="ec-show-actions">
            <a
                href="{{ route('receptor.arqueos.index') }}"
                class="ec-show-btn secondary"
            >
                Volver al historial
            </a>

            <a
                href="{{ route('receptor.arqueos.imprimir', $arqueo->id) }}"
                class="ec-show-btn"
                target="_blank"
                rel="noopener"
            >
                Ver / Imprimir PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="ec-show-alert" role="status">
            {{ session('success') }}
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
                <h2>Arqueo y corte de caja</h2>
                <p>
                    {{ $arqueo->agencia_nombre_historico
                        ?? $arqueo->agencia?->nombre
                        ?? 'Agencia' }}
                </p>
            </div>

            <div class="ec-show-number">
                <small>Número de arqueo</small>
                <strong>{{ $arqueo->numero_arqueo }}</strong>

                <div style="margin-top:12px">
                    <span class="ec-show-status {{ strtolower($estado) }}">
                        {{ $estado }}
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
                            {{ $arqueo->receptor_nombre_historico ?? '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Fecha operativa</label>
                        <strong>
                            {{ $arqueo->fecha_operativa
                                ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y')
                                : '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Turno</label>
                        <strong>
                            {{ $arqueo->turno_nombre_historico ?? '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Agencia</label>
                        <strong>
                            {{ $arqueo->agencia_nombre_historico
                                ?? $arqueo->agencia?->nombre
                                ?? '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Ventanilla</label>
                        <strong>
                            {{ $arqueo->ventanilla_codigo_historico
                                ?? $arqueo->ventanilla?->codigo
                                ?? '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Hora de inicio</label>
                        <strong>
                            {{ $inicio?->format('H:i:s') ?? '—' }}
                        </strong>
                    </div>

                    <div class="ec-show-info-item">
                        <label>Hora de finalización</label>
                        <strong>
                            {{ $finalizacion?->format('H:i:s') ?? '—' }}
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
                                    @php
                                        $detalle = $detallesBilletes->first(
                                            fn ($item) =>
                                                (float) $item->denominacion === (float) $valor
                                        );
                                    @endphp

                                    <tr>
                                        <td>
                                            Q {{ number_format($valor, 2) }}
                                        </td>

                                        <td>
                                            {{ number_format((int) ($detalle?->cantidad ?? 0)) }}
                                        </td>

                                        <td>
                                            Q {{ number_format((float) ($detalle?->subtotal ?? 0), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="2">Total billetes</td>
                                    <td>
                                        Q {{ number_format((float) $arqueo->total_billetes, 2) }}
                                    </td>
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
                                    @php
                                        $detalle = $detallesMonedas->first(
                                            fn ($item) =>
                                                (float) $item->denominacion === (float) $valor
                                        );
                                    @endphp

                                    <tr>
                                        <td>
                                            Q {{ number_format((float) $valor, 2) }}
                                        </td>

                                        <td>
                                            {{ number_format((int) ($detalle?->cantidad ?? 0)) }}
                                        </td>

                                        <td>
                                            Q {{ number_format((float) ($detalle?->subtotal ?? 0), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="2">Total monedas</td>
                                    <td>
                                        Q {{ number_format((float) $arqueo->total_monedas, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

            </section>

            <section class="ec-show-section">

                <h3>Otros valores</h3>

                <table class="ec-show-table">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Cantidad</th>
                            <th>Sub-total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($otrosConceptos as $codigo => $nombre)
                            @php
                                $detalle = $detallesOtros
                                    ->firstWhere('concepto', $codigo);
                            @endphp

                            <tr>
                                <td>{{ $nombre }}</td>

                                <td>
                                    {{ (int) ($detalle?->cantidad ?? 0) }}
                                </td>

                                <td>
                                    Q {{ number_format((float) ($detalle?->subtotal ?? 0), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="2">Total otros valores</td>
                            <td>
                                Q {{ number_format((float) $arqueo->total_otros_valores, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

            </section>

            <div class="ec-show-grand">
                <span>TOTAL ARQUEADO</span>
                <strong>
                    Q {{ number_format((float) $arqueo->total_arqueado, 2) }}
                </strong>
            </div>

            <section class="ec-show-section">

                <div class="ec-show-check">

                    <h3>COMPROBACIÓN</h3>

                    <div class="ec-show-check-line">
                        <span>Total arqueado</span>
                        <strong>
                            Q {{ number_format((float) $arqueo->total_arqueado, 2) }}
                        </strong>
                    </div>

                    <div class="ec-show-check-line">
                        <span>Total corte bancario</span>
                        <strong>
                            Q {{ number_format((float) $arqueo->total_corte, 2) }}
                        </strong>
                    </div>

                    <div class="ec-show-check-line last">
                        <span>Diferencia</span>
                        <strong>
                            {{ $diferencia < 0 ? '-' : '' }}Q
                            {{ number_format(abs($diferencia), 2) }}
                        </strong>
                    </div>

                    <div
                        class="ec-show-check-status"
                        style="color:{{ $diferencia == 0 ? '#18703e' : '#a04427' }}"
                    >
                        @if($diferencia > 0)
                            Sobrante
                        @elseif($diferencia < 0)
                            Faltante
                        @else
                            Cuadrado
                        @endif
                    </div>

                </div>

            </section>

            <section class="ec-show-section">

                <h3>Justificación de la diferencia</h3>

                <div class="ec-show-note {{ !$arqueo->justificacion_diferencia ? 'empty' : '' }}">
                    {{ $arqueo->justificacion_diferencia ?: 'Sin justificación registrada.' }}
                </div>

            </section>

            <section class="ec-show-section">

                <h3>Observaciones</h3>

                <div class="ec-show-note {{ !$arqueo->observaciones ? 'empty' : '' }}">
                    {{ $arqueo->observaciones ?: 'Sin observaciones registradas.' }}
                </div>

            </section>

            <section class="ec-show-section">

                <h3>Firmas electrónicas</h3>

                <div class="ec-show-signatures">

                    <div class="ec-show-signature">
                        <small>
                            @if($firmaReceptor)
                                Firma electrónica registrada
                            @else
                                Firma no registrada
                            @endif
                        </small>

                        <strong>Receptor - Pagador</strong>

                        <span>
                            {{ $arqueo->firmado_receptor_at
                                ? \Illuminate\Support\Carbon::parse($arqueo->firmado_receptor_at)->format('d/m/Y H:i:s')
                                : 'Pendiente' }}
                        </span>
                    </div>

                    <div class="ec-show-signature">
                        <small>
                            {{ $firmaCertificador
                                ? 'Firma electrónica registrada'
                                : 'Pendiente de certificación' }}
                        </small>

                        <strong>Jefe / Subjefe de Caja</strong>

                        <span>
                            {{ $arqueo->firmado_certificador_at
                                ? \Illuminate\Support\Carbon::parse($arqueo->firmado_certificador_at)->format('d/m/Y H:i:s')
                                : 'Pendiente' }}
                        </span>
                    </div>

                </div>

                @if($firmaReceptor && $firmaValida)
                    <div class="ec-show-signature-valid">
                        <strong>Firma electrónica del receptor verificada.</strong>
                        La integridad del documento coincide con
                        la firma registrada.
                    </div>
                @else
                    <div class="ec-show-signature-invalid">
                        <strong>
                            No se pudo verificar la firma electrónica del receptor.
                        </strong>
                        El documento requiere revisión antes de considerarse
                        íntegro.
                    </div>
                @endif

            </section>

        </div>
    </div>

</div>
@endsection
