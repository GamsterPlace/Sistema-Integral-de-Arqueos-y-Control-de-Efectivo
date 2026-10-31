
@php
    $billetes = $billetes ?? ['200', '100', '50', '20', '10', '5', '2', '1'];
    $monedas = $monedas ?? ['1.00', '0.50', '0.25', '0.10', '0.05'];
    $arqueo = $registro;
    $detalles = $arqueo->detalles ?? collect();
    $firmaReceptor = ($arqueo->firmas ?? collect())->firstWhere('tipo_firma', 'RECEPTOR');
    $firmaCertificador = ($arqueo->firmas ?? collect())->firstWhere('tipo_firma', 'CERTIFICADOR');
    $logoBase64 = $logoData ?? null;

    $moneda = fn ($valor) => 'Q '.number_format((float) ($valor ?? 0), 2, '.', ',');

    $detalleDenominacion = function ($tipo, $valor) use ($detalles) {
        return $detalles->first(fn ($detalle) => $detalle->tipo === $tipo
            && (int) round((float) $detalle->denominacion * 100) === (int) round((float) $valor * 100));
    };

    $fecha = $arqueo->fecha_operativa ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y') : '—';
    $inicio = $arqueo->inicio_at ? \Illuminate\Support\Carbon::parse($arqueo->inicio_at)->format('H:i') : '—';
    $fin = $arqueo->finalizado_at ? \Illuminate\Support\Carbon::parse($arqueo->finalizado_at)->format('H:i') : '—';
    $diferencia = (float) ($arqueo->diferencia ?? 0);
    $estado = strtoupper((string) $arqueo->estado);
    $firmaReceptorValida = (bool) ($verificaciones['RECEPTOR'] ?? false);
    $firmaCertificadorValida = (bool) ($verificaciones['CERTIFICADOR'] ?? false);
    $justificacion = trim((string) ($arqueo->justificacion_diferencia ?? ''));
    $observaciones = trim((string) ($arqueo->observaciones ?? ''));

    // La firma de certificación debe identificar al firmante real, no al jefe asignado.
    // Se aprovechan únicamente los datos disponibles en el registro o su relación cargada.
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
        ? (data_get($firmaCertificador, 'fecha_firma') ?: $arqueo->certificado_at)
        : null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Arqueo de Jefatura de Caja - {{ $arqueo->numero_arqueo }}</title>
    <style>
        @page { size: letter portrait; margin: 16px 28px 16px 28px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #243d53; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.3; }
        table { border-collapse: collapse; width: 100%; }

        .head { border-bottom: 2px solid #174a73; padding-bottom: 8px; margin-bottom: 7px; }
        .head td { vertical-align: middle; }
        .logo-cell { width: 29%; }
        .logo { width: 155px; max-height: 74px; }
        .head-center { width: 43%; text-align: center; }
        .head-title { font-size: 15px; font-weight: bold; color: #123e65; letter-spacing: .2px; }
        .head-number { width: 28%; text-align: right; }
        .head-number small { font-size: 9px; color: #758b9c; }
        .head-number strong { display: block; font-size: 13px; color: #bd202b; margin-top: 4px; overflow-wrap: anywhere; font-weight: bold; }

        .info { margin-bottom: 7px; }
        .info td { padding: 4px 7px 7px 0; vertical-align: top; }
        .label { color: #6d8293; font-size: 9px; font-weight: bold; margin-bottom: 3px; }
        .value { font-size: 10px; font-weight: bold; border-bottom: 1px solid #b6c6d2; padding: 0 0 4px; min-height: 12px; }

        /* Diseño compacto inspirado en el formulario físico oficial. */
        .form-layout { width: 100%; table-layout: fixed; margin-top: 6px; }
        .form-layout > tbody > tr > td { vertical-align: top; }
        .denominations-column { width: 56%; padding-right: 10px; }
        .verification-column { width: 44%; padding-left: 10px; }

        .section-heading { color: #16486f; font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .coins-heading { margin-top: 12px; }
        .other-heading { margin-top: 12px; }

        .denomination-table { table-layout: fixed; width: 100%; }
        .denomination-table th { color: #536e82; font-size: 10px; text-align: left; border: none; padding: 3px 2px 4px; }
        .denomination-table th:nth-child(1) { width: 25%; }
        .denomination-table th:nth-child(2) { width: 17%; text-align: center; }
        .denomination-table th:nth-child(3) { width: 29%; text-align: right; }
        .denomination-table th:nth-child(4) { width: 29%; text-align: right; }
        .denomination-table td { font-size: 11px; padding: 6px 2px; border: none; white-space: nowrap; }
        .denomination-table td.quantity { text-align: center; }
        .denomination-table td.amount { text-align: right; font-weight: bold; }
        .denomination-table td.total-empty { border: none; }
        .denomination-table td.group-total { text-align: right; vertical-align: bottom; font-weight: bold; font-size: 10px; color: #174a73; border: none; }

        .value-rule { display: inline-block; border-bottom: 1px solid #8ea8b9; min-width: 39px; padding-bottom: 2px; }
        .amount .value-rule { min-width: 65px; }
        .group-total .value-rule { min-width: 68px; }

        .other-table td { padding-top: 5px; padding-bottom: 5px; }

        .total-arqueado { margin-top: 8px; border-top: 2px solid #16486f; border-bottom: 2px solid #16486f; }
        .total-arqueado td { padding: 6px 4px; font-size: 12px; font-weight: bold; color: #123e65; }
        .total-arqueado td:last-child { text-align: right; font-size: 15px; }

        .verification-position { padding-top: 220px; }
        .verification-title { color: #16486f; text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 7px; }
        .verification-table td { padding: 8px 2px; font-size: 11px; border-bottom: 1px solid #bacbd7; }
        .verification-table td:last-child { text-align: right; font-weight: bold; white-space: nowrap; }
        .verification-difference td { border-bottom: 2px solid #16486f; font-weight: bold; }
        .difference-kind { font-size: 9px; color: #4a6d83; text-align: right; font-weight: bold; padding-top: 5px; }

        .certification-title { color: #16486f; font-size: 12px; font-weight: bold; margin: 12px 0 5px; }
        .certification-text { font-size: 10px; line-height: 1.5; min-height: 53px; padding: 5px 2px; border-top: 1px solid #b9cad7; border-bottom: 1px solid #b9cad7; white-space: pre-wrap; overflow-wrap: break-word; }
        .observations-title { color: #617c90; font-size: 9px; font-weight: bold; margin: 7px 0 4px; }
        .observations-text { font-size: 10px; line-height: 1.4; min-height: 35px; border-bottom: 1px solid #b9cad7; white-space: pre-wrap; overflow-wrap: break-word; }

        .official-signatures { width: 100%; table-layout: fixed; margin-top: 18px; page-break-inside: avoid; }
        .official-signatures td { width: 50%; padding: 0 22px; text-align: center; vertical-align: bottom; }
        .signature-caption { font-size: 10px; color: #536e82; margin-bottom: 9px; }
        .signature-name { min-height: 16px; font-size: 13px; font-weight: bold; color: #123e65; padding-bottom: 4px; border-bottom: 1px solid #819eaf; overflow-wrap: break-word; }
        .signature-role { font-size: 12px; font-weight: bold; color: #243d53; padding-top: 6px; }
        .signature-status { font-size: 10px; color: #5b778b; margin-top: 5px; }
        .signature-timestamp { margin-top: 5px; color: #698095; font-size: 9px; }

        .document-integrity { margin-top: 5px; text-align: center; color: #567589; font-size: 9px; page-break-inside: avoid; }
        .document-footer { margin-top: 8px; padding-top: 4px; border-top: 1px solid #d6e2e9; text-align: center; color: #8193a0; font-size: 8px; page-break-inside: avoid; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td class="logo-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo" alt="ECOSABA">
                @else
                    <strong>ECOSABA R.L.</strong>
                @endif
            </td>
            <td class="head-center">
                <div class="head-title">ARQUEO DE JEFATURA DE CAJA</div><div style="font-size:8px;color:#60798d;margin-top:4px">CONTROL SUPERVISADO DE EFECTIVO</div>
            </td>
            <td class="head-number">
                <small>NÚMERO DE ARQUEO</small>
                <strong>{{ $arqueo->numero_arqueo }}</strong>
            </td>
        </tr>
    </table>

    <table class="info">
        <tr>
            <td width="50%"><div class="label">NOMBRE DEL RECEPTOR</div><div class="value">{{ $arqueo->receptor_nombre_historico ?? '—' }}</div></td>
            <td width="25%"><div class="label">FECHA</div><div class="value">{{ $fecha }}</div></td>
            <td width="12.5%"><div class="label">H. INICIO</div><div class="value">{{ $inicio }}</div></td>
            <td width="12.5%"><div class="label">H. FIN</div><div class="value">{{ $fin }}</div></td>
        </tr>
        <tr>
            <td><div class="label">AGENCIA</div><div class="value">{{ $arqueo->agencia_nombre_historico ?? '—' }}</div></td>
            <td><div class="label">VENTANILLA</div><div class="value">{{ $arqueo->ventanilla_codigo_historico ?? '—' }}</div></td>
            <td colspan="2"><div class="label">TURNO</div><div class="value">{{ $arqueo->turno_nombre_historico ?? '—' }}</div></td>
        </tr>
    </table>

    {{-- Distribución institucional basada en el formulario físico. --}}
    <table class="form-layout">
        <tr>
            <td class="denominations-column">
                <div class="section-heading">Billetes</div>
                <table class="denomination-table">
                    <thead>
                        <tr>
                            <th>Denominación</th>
                            <th>Cantidad</th>
                            <th>Sub-total</th>
                            <th class="totals-heading">Totales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($billetes as $valor)
                            @php($detalle = $detalleDenominacion('BILLETE', $valor))
                            <tr>
                                <td>{{ $moneda($valor) }}</td>
                                <td class="quantity"><span class="value-rule">{{ number_format((int) ($detalle?->cantidad ?? 0)) }}</span></td>
                                <td class="amount"><span class="value-rule">{{ $moneda($detalle?->subtotal) }}</span></td>
                                <td class="{{ $loop->last ? 'group-total' : 'total-empty' }}">
                                    @if($loop->last)<span class="value-rule">{{ $moneda($arqueo->total_billetes) }}</span>@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="section-heading coins-heading">Monedas</div>
                <table class="denomination-table">
                    <thead>
                        <tr>
                            <th>Denominación</th>
                            <th>Cantidad</th>
                            <th>Sub-total</th>
                            <th class="totals-heading">Totales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monedas as $valor)
                            @php($detalle = $detalleDenominacion('MONEDA', $valor))
                            <tr>
                                <td>{{ $moneda($valor) }}</td>
                                <td class="quantity"><span class="value-rule">{{ number_format((int) ($detalle?->cantidad ?? 0)) }}</span></td>
                                <td class="amount"><span class="value-rule">{{ $moneda($detalle?->subtotal) }}</span></td>
                                <td class="{{ $loop->last ? 'group-total' : 'total-empty' }}">
                                    @if($loop->last)<span class="value-rule">{{ $moneda($arqueo->total_monedas) }}</span>@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <table class="total-arqueado">
                    <tr>
                        <td>TOTAL ARQUEADO</td>
                        <td>{{ $moneda($arqueo->total_arqueado) }}</td>
                    </tr>
                </table>
            </td>

            <td class="verification-column">
                <div class="verification-position">
                    <div class="verification-title">COMPROBACIÓN</div>
                    <table class="verification-table">
                        <tr>
                            <td>Total Arqueado</td>
                            <td>{{ $moneda($arqueo->total_arqueado) }}</td>
                        </tr>
                        <tr>
                            <td>Total Corte</td>
                            <td>{{ $moneda($arqueo->total_corte) }}</td>
                        </tr>
                        <tr class="verification-difference">
                            <td>Diferencia</td>
                            <td>{{ $diferencia < 0 ? '-' : '' }}{{ $moneda(abs($diferencia)) }}</td>
                        </tr>
                    </table>

                    <div class="difference-kind">
                        {{ $diferencia > 0 ? 'SOBRANTE' : ($diferencia < 0 ? 'FALTANTE' : 'CUADRADO') }}
                    </div>

                    <div class="certification-title">Justificación de diferencia</div>
                    <div class="certification-text">{{ $justificacion !== '' ? $justificacion : 'Sin justificación registrada.' }}</div>

                    <div class="observations-title">OBSERVACIONES</div>
                    <div class="observations-text">{{ $observaciones !== '' ? $observaciones : 'Sin observaciones registradas.' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="official-signatures">
        <tr>
            <td>
                <div class="signature-caption">Firma electrónica</div>
                <div class="signature-name">{{ $firmaReceptor ? ($arqueo->receptor_nombre_historico ?: 'Nombre no disponible') : '—' }}</div>
                <div class="signature-role">Receptor - Pagador</div>
                <div class="signature-status">{{ $firmaReceptor ? 'Firmado electrónicamente' : 'Pendiente de firma' }}</div>
                <div class="signature-timestamp">{{ $firmaReceptor && ($firmaReceptor->fecha_firma ?? $arqueo->firmado_receptor_at) ? \Illuminate\Support\Carbon::parse($firmaReceptor->fecha_firma ?? $arqueo->firmado_receptor_at)->format('d/m/Y H:i:s') : '—' }}</div>
            </td>
            <td>
                <div class="signature-caption">Firma electrónica</div>
                <div class="signature-name">{{ $firmaCertificador ? ($nombreCertificador ?: 'Nombre del firmante no disponible') : '—' }}</div>
                <div class="signature-role">{{ $cargoCertificador }}</div>
                <div class="signature-status">{{ $firmaCertificador ? 'Firmado electrónicamente' : 'Pendiente de firma' }}</div>
                <div class="signature-timestamp">{{ $fechaFirmaCertificador ? \Illuminate\Support\Carbon::parse($fechaFirmaCertificador)->format('d/m/Y H:i:s') : '—' }}</div>
            </td>
        </tr>
    </table>

    <div class="document-integrity">Integridad de firma del supervisor: <strong>{{ $firmaCertificador && $firmaCertificadorValida ? 'VERIFICADA' : 'NO VERIFICADA' }}</strong> · Firma del receptor: <strong>{{ $firmaReceptor ? ($firmaReceptorValida ? 'VERIFICADA' : 'NO VERIFICADA') : 'PENDIENTE' }}</strong></div>
    <div class="document-footer">ECOSABA R.L. · Sistema Integral de Arqueos y Control de Efectivo · Documento generado electrónicamente · {{ $arqueo->numero_arqueo }}</div>
</body>
</html>
