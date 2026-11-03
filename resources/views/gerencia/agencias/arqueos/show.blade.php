
@extends('layouts.gerencia')

@section('title', 'Detalle de arqueo de agencia')

@push('styles')
<style>
.ga-detail{max-width:1180px;margin:0 auto 36px;color:#263e52}
.ga-detail *{box-sizing:border-box}
.ga-detail-top{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-bottom:24px}
.ga-detail-top h1{margin:0 0 7px;color:#10365e;font-size:28px;font-weight:600}
.ga-detail-top p{margin:0;color:#71869b;font-size:13px}
.ga-detail-actions{display:flex;gap:10px;flex-wrap:wrap}
.ga-detail-btn{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:11px 18px;border-radius:11px;background:#1c5099;color:#fff!important;text-decoration:none;font-size:12px;font-weight:800}
.ga-detail-btn.secondary{background:#edf2f6;color:#24486a!important}
.ga-detail-btn:hover{filter:brightness(.96)}
.ga-detail-paper{background:#fff;border:1px solid #dce7f2;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px rgba(16,47,83,.04)}
.ga-detail-header{display:grid;grid-template-columns:170px minmax(0,1fr) 190px;gap:20px;align-items:center;padding:30px;border-bottom:2px solid #e0e9f2}
.ga-detail-logo{max-width:300px;max-height:500px;object-fit:contain}
.ga-detail-heading{text-align:center}
.ga-detail-heading h2{margin:0;color:#153f67;font-size:23px;text-transform:uppercase}
.ga-detail-heading p{margin:9px 0 0;color:#61768a;font-size:13px;font-weight:800;text-transform:uppercase}
.ga-detail-number{text-align:right;min-width:0}
.ga-detail-number small{display:block;color:#8294a6;font-size:10px;font-weight:800;text-transform:uppercase}
.ga-detail-number strong{display:block;margin:8px 0 12px;color:#194c75;font-size:12px;overflow-wrap:anywhere}
.ga-detail-body{padding:30px 35px 36px}
.ga-detail-section{margin-bottom:30px}
.ga-detail-section h3{margin:0 0 17px;padding-bottom:11px;border-bottom:1px solid #dce6ee;color:#17456d;font-size:16px;font-weight:700}
.ga-detail-info{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:21px}
.ga-detail-field{min-width:0}
.ga-detail-field.wide{grid-column:span 2}
.ga-detail-field label{display:block;margin-bottom:7px;color:#6a8094;font-size:11px;font-weight:800}
.ga-detail-field strong{display:block;min-height:30px;padding:7px 2px;border-bottom:1px solid #afc0ce;color:#24445f;font-size:13px;overflow-wrap:anywhere}
.ga-detail-tables{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:30px}
.ga-detail-table{width:100%;border-collapse:collapse}
.ga-detail-table th{padding:11px 8px;border-bottom:1px solid #b9cbd9;color:#587084;font-size:11px;text-align:left}
.ga-detail-table td{padding:11px 8px;border-bottom:1px solid #edf1f5;color:#36536a;font-size:12px}
.ga-detail-table th:nth-child(2),.ga-detail-table td:nth-child(2){text-align:center}
.ga-detail-table th:last-child,.ga-detail-table td:last-child{text-align:right}
.ga-detail-table td:last-child{font-weight:800;color:#16466e}
.ga-detail-table tfoot td{padding-top:16px;border-top:2px solid #c2d1dd;border-bottom:0;font-size:13px;font-weight:800;color:#133f68}
.ga-detail-grand{display:flex;align-items:center;justify-content:flex-end;gap:22px;flex-wrap:wrap;margin:24px 0 30px;padding:21px 24px;border:1px solid #c8dced;border-radius:12px;background:#eef6fc;color:#17466e;font-size:14px;font-weight:800}
.ga-detail-grand strong{font-size:26px;color:#0c4a7e}
.ga-detail-check{max-width:530px;margin-left:auto;padding:19px 22px;border:1px solid #d7e4ee;border-radius:12px;background:#f7fbfe}
.ga-detail-check h3{text-align:center;border:0;margin:0 0 14px;color:#17466e;font-size:17px}
.ga-detail-check-line{display:flex;justify-content:space-between;gap:15px;padding:13px 0;border-bottom:1px solid #dce6ee;color:#506a7e;font-size:13px}
.ga-detail-check-line strong{color:#16456d;font-size:16px}
.ga-detail-check-line.last{margin-top:8px;border-top:2px solid #c7d8e5;border-bottom:0;font-weight:800}
.ga-detail-check-status{text-align:right;margin-top:8px;font-size:12px;font-weight:800}
.ga-detail-note{padding:16px 18px;border:1px solid #e0e9f0;border-radius:10px;background:#f8fbfd;color:#415c71;font-size:13px;line-height:1.7;white-space:pre-wrap;overflow-wrap:anywhere}
.ga-detail-note.empty{color:#8193a2;font-style:italic}
.ga-detail-signatures{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:28px}
.ga-detail-signature{text-align:center;padding:20px 12px 0}
.ga-detail-signature small{display:block;min-height:35px;color:#698096;font-size:11px}
.ga-detail-signature strong{display:block;padding-top:11px;border-top:1px solid #91a5b6;color:#38566d;font-size:12px}
.ga-detail-signature span{display:block;margin-top:7px;color:#657f92;font-size:11px}
.ga-detail-status{display:inline-flex;padding:7px 12px;border-radius:8px;background:#edf2f6;color:#476174;font-size:11px;font-weight:800}
.ga-detail-status.certificado{background:#e7f5ec;color:#18703e}
.ga-detail-status.pendiente{background:#fff4de;color:#956313}
.ga-detail-status.anulado{background:#fce9e9;color:#a23737}
.ga-detail-alert{padding:15px 18px;border:1px solid #e0e9f0;border-radius:10px;background:#f8fbfd;color:#415c71;font-size:12px;line-height:1.7}
.ga-detail-alert.danger{border-color:#edcece;background:#fff5f5;color:#983c3c}
.ga-detail-audit{padding:15px 18px;margin-bottom:10px;border:1px solid #e0e9f0;border-radius:10px;background:#f8fbfd;font-size:12px;line-height:1.7}
.ga-detail-audit strong{color:#17456d}
.ga-detail-audit p{margin:8px 0;color:#415c71;white-space:pre-wrap;overflow-wrap:anywhere}
.ga-detail-audit small{color:#71869b}
@media(max-width:900px){
.ga-detail-header{grid-template-columns:1fr;text-align:center}
.ga-detail-logo{margin:auto}
.ga-detail-number{text-align:center}
.ga-detail-info{grid-template-columns:repeat(2,minmax(0,1fr))}
.ga-detail-tables{grid-template-columns:1fr}
}
@media(max-width:550px){
.ga-detail-top h1{font-size:24px}
.ga-detail-header{padding:22px 16px}
.ga-detail-body{padding:22px 16px}
.ga-detail-info{grid-template-columns:1fr}
.ga-detail-field.wide{grid-column:span 1}
.ga-detail-signatures{grid-template-columns:1fr}
.ga-detail-actions{width:100%}
.ga-detail-btn{flex:1}
}
@media print{
.ga-detail-top{display:none!important}
.ga-detail-paper{border:0;box-shadow:none}
.ga-detail-body{padding:20px}
}
</style>
@endpush

@section('content')

@php
    $billetes = [200, 100, 50, 20, 10, 5, 2, 1];
    $monedas = ['1.00', '0.50', '0.25', '0.10', '0.05', '0.01'];

    $detallesBilletes = $arqueo->detalles->where('tipo', 'BILLETE');
    $detallesMonedas = $arqueo->detalles->where('tipo', 'MONEDA');
    $detallesOtros = $arqueo->detalles->where('tipo', 'OTRO');

    $otrosConceptos = [
        'CHEQUES' => 'Cheques',
        'VALES' => 'Vales',
        'OTROS_VALORES' => 'Otros valores',
    ];

    $estado = strtoupper((string) $arqueo->estado);
    $tipo = strtoupper((string) $arqueo->tipo);
    $diferencia = (float) ($arqueo->diferencia ?? 0);

    $inicio = $arqueo->inicio_at
        ? \Illuminate\Support\Carbon::parse($arqueo->inicio_at)
        : null;

    $finalizacion = $arqueo->finalizado_at
        ? \Illuminate\Support\Carbon::parse($arqueo->finalizado_at)
        : null;

    $fechaOperativa = $arqueo->fecha_operativa
        ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y')
        : '—';

    $nombreReceptor = $arqueo->receptor_nombre_historico
        ?: ($arqueo->receptor?->nombre_completo ?? '—');

    $nombreAgencia = $arqueo->agencia_nombre_historico
        ?: ($arqueo->agencia?->nombre ?? '—');

    $nombreVentanilla = $arqueo->ventanilla_codigo_historico
        ?: ($arqueo->ventanilla?->codigo ?? '—');

    $nombreTurno = $arqueo->turno_nombre_historico
        ?? $arqueo->jornada?->turno?->nombre
        ?? '—';

    $firmaReceptor = $arqueo->firmas->firstWhere('tipo_firma', 'RECEPTOR');
    $firmaCertificador = $arqueo->firmas->firstWhere('tipo_firma', 'CERTIFICADOR');

    $etiquetasFirmas = [
        'RECEPTOR' => 'Receptor - Pagador',
        'CERTIFICADOR' => 'Jefe / Subjefe de Caja',
        'ENTRANTE' => 'Receptor entrante',
        'AUDITOR' => 'Auditoría',
        'ANULADOR' => 'Responsable de anulación',
    ];
@endphp

<div class="ga-detail">

    {{-- ENCABEZADO DE LA PÁGINA --}}
    <div class="ga-detail-top">
        <div>
            <h1>Detalle de arqueo de agencia</h1>
            <p>Consulta institucional de solo lectura.</p>
        </div>

        <div class="ga-detail-actions">
            <a href="{{ route('gerencia.agencias.arqueos.index') }}"
               class="ga-detail-btn secondary">
                Volver al historial
            </a>

            <a href="{{ route('gerencia.agencias.arqueos.imprimir', $arqueo->id) }}"
               class="ga-detail-btn"
               target="_blank"
               rel="noopener">
                Ver / Imprimir PDF
            </a>
        </div>
    </div>

    <div class="ga-detail-paper">

        {{-- ENCABEZADO INSTITUCIONAL --}}
        <header class="ga-detail-header">

            <div>
                <img
                    class="ga-detail-logo"
                    src="{{ asset('images/logos/ecosaba.png') }}"
                    alt="ECOSABA"
                >
            </div>

            <div class="ga-detail-heading">
                <h2>Arqueo y corte de caja</h2>
                <p>{{ $nombreAgencia }}</p>
            </div>

            <div class="ga-detail-number">
                <small>Número de arqueo</small>
                <strong>{{ $arqueo->numero_arqueo }}</strong>

                <span class="ga-detail-status {{ strtolower($estado) }}">
                    {{ $estado }}
                </span>
            </div>

        </header>

        <div class="ga-detail-body">

            {{-- INFORMACIÓN GENERAL --}}
            <section class="ga-detail-section">
                <h3>Información general del arqueo</h3>

                <div class="ga-detail-info">

                    <div class="ga-detail-field wide">
                        <label>Nombre del receptor</label>
                        <strong>{{ $nombreReceptor }}</strong>
                    </div>

                    <div class="ga-detail-field">
                        <label>Fecha operativa</label>
                        <strong>{{ $fechaOperativa }}</strong>
                    </div>

                    <div class="ga-detail-field">
                        <label>Tipo de arqueo</label>
                        <strong>{{ $tipo }}</strong>
                    </div>

                    <div class="ga-detail-field">
                        <label>Agencia</label>
                        <strong>{{ $nombreAgencia }}</strong>
                    </div>

                    <div class="ga-detail-field">
                        <label>Ventanilla</label>
                        <strong>{{ $nombreVentanilla }}</strong>
                    </div>

                    <div class="ga-detail-field">
                        <label>Turno</label>
                        <strong>{{ $nombreTurno }}</strong>
                    </div>

                    <div class="ga-detail-field">
                        <label>Estado</label>
                        <strong>{{ $estado }}</strong>
                    </div>

                    <div class="ga-detail-field">
                        <label>Hora de inicio</label>
                        <strong>{{ $inicio?->format('H:i:s') ?? '—' }}</strong>
                    </div>

                    <div class="ga-detail-field">
                        <label>Hora de finalización</label>
                        <strong>{{ $finalizacion?->format('H:i:s') ?? '—' }}</strong>
                    </div>

                    <div class="ga-detail-field">
                        <label>Firma del receptor</label>
                        <strong>
                            {{ $arqueo->firmado_receptor_at
                                ? \Illuminate\Support\Carbon::parse($arqueo->firmado_receptor_at)->format('d/m/Y H:i')
                                : 'Pendiente' }}
                        </strong>
                    </div>

                    <div class="ga-detail-field">
                        <label>Certificación</label>
                        <strong>
                            {{ $arqueo->certificado_at
                                ? \Illuminate\Support\Carbon::parse($arqueo->certificado_at)->format('d/m/Y H:i')
                                : 'Pendiente' }}
                        </strong>
                    </div>

                    @if($tipo === 'ENTREGA')
                        <div class="ga-detail-field wide">
                            <label>Receptor entrante</label>
                            <strong>
                                {{ $arqueo->receptorEntrante?->nombre_completo ?? '—' }}
                            </strong>
                        </div>
                    @endif

                </div>
            </section>

            {{-- DENOMINACIONES --}}
            <section class="ga-detail-section">
                <div class="ga-detail-tables">

                    <div>
                        <h3>Billetes</h3>

                        <table class="ga-detail-table">
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
                                                (int) round((float) $item->denominacion * 100)
                                                === (int) round($valor * 100)
                                        );
                                    @endphp

                                    <tr>
                                        <td>Q {{ number_format($valor, 2) }}</td>
                                        <td>{{ number_format((int) ($detalle?->cantidad ?? 0)) }}</td>
                                        <td>Q {{ number_format((float) ($detalle?->subtotal ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="2">Total billetes</td>
                                    <td>Q {{ number_format((float) $arqueo->total_billetes, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div>
                        <h3>Monedas</h3>

                        <table class="ga-detail-table">
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
                                                (int) round((float) $item->denominacion * 100)
                                                === (int) round((float) $valor * 100)
                                        );
                                    @endphp

                                    <tr>
                                        <td>Q {{ number_format((float) $valor, 2) }}</td>
                                        <td>{{ number_format((int) ($detalle?->cantidad ?? 0)) }}</td>
                                        <td>Q {{ number_format((float) ($detalle?->subtotal ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="2">Total monedas</td>
                                    <td>Q {{ number_format((float) $arqueo->total_monedas, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </section>

            {{-- OTROS VALORES --}}
            <section class="ga-detail-section">
                <h3>Otros valores</h3>

                <table class="ga-detail-table">
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
                                $detalle = $detallesOtros->firstWhere('concepto', $codigo);
                            @endphp

                            <tr>
                                <td>{{ $nombre }}</td>
                                <td>{{ (int) ($detalle?->cantidad ?? 0) }}</td>
                                <td>Q {{ number_format((float) ($detalle?->subtotal ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="2">Total otros valores</td>
                            <td>Q {{ number_format((float) $arqueo->total_otros_valores, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            {{-- TOTAL ARQUEADO --}}
            <div class="ga-detail-grand">
                <span>TOTAL ARQUEADO</span>
                <strong>Q {{ number_format((float) $arqueo->total_arqueado, 2) }}</strong>
            </div>

            {{-- COMPROBACIÓN --}}
            <section class="ga-detail-section">
                <div class="ga-detail-check">

                    <h3>COMPROBACIÓN</h3>

                    <div class="ga-detail-check-line">
                        <span>Total arqueado</span>
                        <strong>Q {{ number_format((float) $arqueo->total_arqueado, 2) }}</strong>
                    </div>

                    <div class="ga-detail-check-line">
                        <span>Total corte bancario</span>
                        <strong>
                            {{ $arqueo->total_corte !== null
                                ? 'Q '.number_format((float) $arqueo->total_corte, 2)
                                : 'No registrado' }}
                        </strong>
                    </div>

                    <div class="ga-detail-check-line last">
                        <span>Diferencia</span>
                        <strong>
                            @if($arqueo->diferencia !== null)
                                {{ $diferencia < 0 ? '-' : '' }}Q {{ number_format(abs($diferencia), 2) }}
                            @else
                                No calculada
                            @endif
                        </strong>
                    </div>

                    <div class="ga-detail-check-status"
                         style="color:{{ $diferencia == 0 ? '#18703e' : '#a04427' }}">
                        @if($arqueo->diferencia === null)
                            Sin comprobación
                        @elseif($diferencia > 0)
                            Sobrante
                        @elseif($diferencia < 0)
                            Faltante
                        @else
                            Cuadrado
                        @endif
                    </div>

                </div>
            </section>

            {{-- JUSTIFICACIÓN --}}
            <section class="ga-detail-section">
                <h3>Justificación de la diferencia</h3>

                <div class="ga-detail-note {{ !$arqueo->justificacion_diferencia ? 'empty' : '' }}">
                    {{ $arqueo->justificacion_diferencia ?: 'Sin justificación registrada.' }}
                </div>
            </section>

            {{-- OBSERVACIONES --}}
            <section class="ga-detail-section">
                <h3>Observaciones</h3>

                <div class="ga-detail-note {{ !$arqueo->observaciones ? 'empty' : '' }}">
                    {{ $arqueo->observaciones ?: 'Sin observaciones registradas.' }}
                </div>
            </section>

            {{-- FIRMAS ELECTRÓNICAS --}}
            <section class="ga-detail-section">
                <h3>Firmas electrónicas</h3>

                <div class="ga-detail-signatures">

                    @forelse($arqueo->firmas as $firma)
                        @php
                            $tipoFirma = strtoupper((string) $firma->tipo_firma);
                            $nombreFirmante = trim(
                                (string) ($firma->nombres_historicos ?? '')
                                .' '.
                                (string) ($firma->apellidos_historicos ?? '')
                            );
                        @endphp

                        <div class="ga-detail-signature">
                            <small>Firma electrónica registrada</small>

                            <strong>
                                {{ $etiquetasFirmas[$tipoFirma] ?? $tipoFirma }}
                            </strong>

                            <span>{{ $nombreFirmante ?: 'Firmante no identificado' }}</span>

                            <span>
                                {{ $firma->fecha_firma
                                    ? \Illuminate\Support\Carbon::parse($firma->fecha_firma)->format('d/m/Y H:i:s')
                                    : 'Fecha no disponible' }}
                            </span>
                        </div>

                    @empty
                        <div class="ga-detail-alert">
                            Este arqueo no tiene firmas electrónicas registradas.
                        </div>
                    @endforelse

                </div>

                @if($arqueo->firmas->isNotEmpty())
                    <div class="ga-detail-alert" style="margin-top:25px">
                        Las firmas electrónicas se encuentran registradas en el sistema.
                    </div>
                @endif
            </section>

            {{-- ANULACIÓN --}}
            @if($arqueo->anulacion)
                <section class="ga-detail-section">
                    <h3>Información de anulación</h3>

                    <div class="ga-detail-alert danger">
                        <strong>Arqueo anulado</strong>

                        <div style="margin-top:8px">
                            {{ $arqueo->anulacion->motivo }}
                        </div>
                    </div>
                </section>
            @endif

            {{-- HALLAZGOS DE AUDITORÍA --}}
            @if($arqueo->observacionesAuditoria->isNotEmpty())
                <section class="ga-detail-section">
                    <h3>Hallazgos de auditoría</h3>

                    @foreach($arqueo->observacionesAuditoria as $hallazgo)
                        <div class="ga-detail-audit">
                            <strong>Estado: {{ $hallazgo->estado ?? '—' }}</strong>
                            <p>{{ $hallazgo->descripcion ?? 'Sin descripción.' }}</p>
                        </div>
                    @endforeach
                </section>
            @endif

        </div>
    </div>
</div>

@endsection
