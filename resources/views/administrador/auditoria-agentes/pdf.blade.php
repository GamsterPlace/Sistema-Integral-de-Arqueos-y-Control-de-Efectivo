<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $arqueo->numero_arqueo }}</title>

    @php
        $billetesMapa = collect($billetes)->keyBy(
            fn ($detalle) => number_format((float) data_get($detalle, 'denominacion'), 2, '.', '')
        );

        $monedasMapa = collect($monedas)->keyBy(
            fn ($detalle) => number_format((float) data_get($detalle, 'denominacion'), 2, '.', '')
        );

        $denominacionesBilletes = [200, 100, 50, 20, 10, 5, 1];
        $denominacionesMonedas = [1, 0.50, 0.25, 0.10, 0.05];

        $fechaArqueo = $arqueo->fecha_arqueo
            ? \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y')
            : '';

        $horaInicio = $arqueo->hora_inicio
            ? \Carbon\Carbon::parse($arqueo->hora_inicio)->format('H:i')
            : '';

        $horaFin = $arqueo->hora_fin
            ? \Carbon\Carbon::parse($arqueo->hora_fin)->format('H:i')
            : '';

        $numeroArqueo = preg_replace('/[^0-9]/', '', (string) $arqueo->numero_arqueo);

        $nombreAuditor = trim(
            ($auditor->nombres ?? '') . ' ' . ($auditor->apellidos ?? '')
        );

        $nombreAuditor = $nombreAuditor !== ''
            ? $nombreAuditor
            : ($auditor->usuario ?? '—');

        $diferencia = (float) $arqueo->diferencia;

        $resultado = $diferencia < 0
            ? 'FALTANTE'
            : ($diferencia > 0 ? 'SOBRANTE' : 'EXACTO');

        $rutaLogoEcosaba = public_path('images/logos/ecosaba.png');
        $rutaLogoAgentes = public_path('images/logos/agentes-micoope.png');

        if (! file_exists($rutaLogoEcosaba)) {
            throw new \RuntimeException(
                'No se encontró el logo de ECOSABA en: ' . $rutaLogoEcosaba
            );
        }

        if (! file_exists($rutaLogoAgentes)) {
            throw new \RuntimeException(
                'No se encontró el logo de Agentes MICOOPE en: ' . $rutaLogoAgentes
            );
        }

        $logoEcosaba = 'data:image/png;base64,' . base64_encode(
            file_get_contents($rutaLogoEcosaba)
        );

        $logoAgentes = 'data:image/png;base64,' . base64_encode(
            file_get_contents($rutaLogoAgentes)
        );
    @endphp

    <style>
        @page {
            size: letter;
            margin: 24pt 30pt 28pt;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #263d50;
            background: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8pt;
            line-height: 1.35;
        }

        .header-table {
            width: 100%;
            margin-bottom: 8pt;
            border-collapse: collapse;
        }

        .header-table td {
            border: 0;
            padding: 0;
            vertical-align: middle;
        }

        .logo-cell {
            width: 31%;
        }

        .title-cell {
            width: 45%;
            text-align: center;
        }

        .number-cell {
            width: 24%;
            color: #9f3a35;
            font-family: DejaVu Serif, serif;
            font-size: 9pt;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .logo-ecosaba {
            width: 126pt;
            height: 43pt;
            object-fit: contain;
        }

        .main-title {
            margin: 0;
            color: #173b63;
            font-family: DejaVu Serif, serif;
            font-size: 13pt;
            font-weight: 700;
            line-height: 15pt;
            text-transform: uppercase;
        }

        .main-subtitle {
            margin-top: 2pt;
            color: #173b63;
            font-family: DejaVu Serif, serif;
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .rule {
            width: 100%;
            height: 3pt;
            margin: 3pt 0 12pt;
            background: #00a651;
        }

        .rule-yellow {
            width: 28%;
            height: 3pt;
            margin-top: -15pt;
            margin-bottom: 12pt;
            background: #f0c419;
        }

        .audit-label {
            margin-bottom: 10pt;
            color: #60788b;
            font-size: 6.8pt;
            font-weight: 700;
            letter-spacing: .6pt;
            text-align: center;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 10pt;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .info-table td {
            padding: 4pt 6pt;
            border: .6pt solid #d8e2e9;
            vertical-align: middle;
        }

        .info-label {
            width: 18%;
            color: #61778a;
            background: #f5f8fa;
            font-size: 6.7pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .info-value {
            color: #233e54;
            font-size: 7.4pt;
            font-weight: 600;
        }

        .section-title {
            margin: 10pt 0 5pt;
            padding: 5pt 7pt;
            color: #ffffff;
            background: #164c96;
            font-family: DejaVu Serif, serif;
            font-size: 8.5pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .money-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .money-table th {
            padding: 4.5pt 6pt;
            border: .6pt solid #d5e0e7;
            color: #526c80;
            background: #f3f7fa;
            font-size: 6.7pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .money-table td {
            padding: 4pt 6pt;
            border: .6pt solid #dce5eb;
            color: #2e485d;
            font-size: 7.2pt;
        }

        .money-table .center {
            text-align: center;
        }

        .money-table .right {
            text-align: right;
        }

        .money-table .denomination {
            font-weight: 700;
        }

        .summary-table {
            width: 100%;
            margin-top: 10pt;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .summary-table td {
            padding: 6pt 7pt;
            border: .6pt solid #d7e2e9;
        }

        .summary-label {
            color: #60788b;
            background: #f5f8fa;
            font-size: 6.7pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary-value {
            color: #173b63;
            font-size: 8pt;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .difference {
            color: #164c96;
            font-size: 9pt;
        }

        .result {
            color: #4f687b;
            font-size: 7pt;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .notes {
            margin-top: 10pt;
            padding: 7pt 8pt;
            border: .6pt solid #dce5eb;
            background: #fbfcfd;
        }

        .notes-title {
            margin-bottom: 3pt;
            color: #60788b;
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .notes-text {
            color: #314b60;
            font-size: 7pt;
            line-height: 1.45;
        }

        .footer {
            margin-top: 12pt;
            padding-top: 6pt;
            border-top: .6pt solid #dce5eb;
            color: #7b8d9a;
            font-size: 5.8pt;
            text-align: center;
        }

        .watermark {
            position: fixed;
            top: 255pt;
            left: 190pt;
            width: 230pt;
            opacity: .035;
            z-index: -1;
        }
    </style>
</head>

<body>
    <img class="watermark" src="{{ $logoAgentes }}" alt="">

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img class="logo-ecosaba" src="{{ $logoEcosaba }}" alt="ECOSABA">
            </td>

            <td class="title-cell">
                <div class="main-title">Arqueo y Corte de Caja</div>
                <div class="main-subtitle">Auditoría de Agentes MICOOPE</div>
            </td>

            <td class="number-cell">
                N.º {{ $numeroArqueo !== '' ? $numeroArqueo : $arqueo->numero_arqueo }}
            </td>
        </tr>
    </table>

    <div class="rule"></div>
    <div class="rule-yellow"></div>

    <div class="audit-label">
        Documento de Auditoría · Sistema de Arqueos para Agentes MICOOPE
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Auditor</td>
            <td class="info-value" colspan="3">{{ $nombreAuditor }}</td>
        </tr>

        <tr>
            <td class="info-label">Fecha</td>
            <td class="info-value">{{ $fechaArqueo }}</td>

            <td class="info-label">Horario</td>
            <td class="info-value">
                {{ $horaInicio }}{{ $horaFin !== '' ? ' - ' . $horaFin : '' }}
            </td>
        </tr>

        <tr>
            <td class="info-label">Agente No.</td>
            <td class="info-value">{{ $arqueo->codigo_agente_historico ?: '—' }}</td>

            <td class="info-label">Negocio</td>
            <td class="info-value">{{ $arqueo->nombre_negocio_historico ?: '—' }}</td>
        </tr>

        <tr>
            <td class="info-label">Propietario</td>
            <td class="info-value" colspan="3">{{ $arqueo->nombre_propietario_historico ?: '—' }}</td>
        </tr>

        <tr>
            <td class="info-label">Región</td>
            <td class="info-value">{{ $arqueo->region_historica ?: '—' }}</td>

            <td class="info-label">Ruta</td>
            <td class="info-value">{{ $arqueo->ruta_historica ?: '—' }}</td>
        </tr>

        <tr>
            <td class="info-label">Estado</td>
            <td class="info-value" colspan="3">{{ str_replace('_', ' ', $arqueo->estado) }}</td>
        </tr>
    </table>

    <div class="section-title">Billetes</div>

    <table class="money-table">
        <thead>
            <tr>
                <th>Denominación</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($denominacionesBilletes as $denominacion)
                @php
                    $clave = number_format((float) $denominacion, 2, '.', '');
                    $detalle = $billetesMapa->get($clave);
                    $cantidad = (int) data_get($detalle, 'cantidad', 0);
                    $subtotal = (float) data_get($detalle, 'subtotal', 0);
                @endphp
                <tr>
                    <td class="denomination">Q {{ number_format((float) $denominacion, 2) }}</td>
                    <td class="center">{{ $cantidad }}</td>
                    <td class="right">Q {{ number_format($subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Monedas</div>

    <table class="money-table">
        <thead>
            <tr>
                <th>Denominación</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($denominacionesMonedas as $denominacion)
                @php
                    $clave = number_format((float) $denominacion, 2, '.', '');
                    $detalle = $monedasMapa->get($clave);
                    $cantidad = (int) data_get($detalle, 'cantidad', 0);
                    $subtotal = (float) data_get($detalle, 'subtotal', 0);
                @endphp
                <tr>
                    <td class="denomination">Q {{ number_format((float) $denominacion, 2) }}</td>
                    <td class="center">{{ $cantidad }}</td>
                    <td class="right">Q {{ number_format($subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="summary-label">Total Billetes</td>
            <td class="summary-value">Q {{ number_format((float) $arqueo->total_billetes, 2) }}</td>

            <td class="summary-label">Total Monedas</td>
            <td class="summary-value">Q {{ number_format((float) $arqueo->total_monedas, 2) }}</td>
        </tr>

        <tr>
            <td class="summary-label">Saldo Sistema</td>
            <td class="summary-value">Q {{ number_format((float) $arqueo->saldo_sistema, 2) }}</td>

            <td class="summary-label">Total Arqueado</td>
            <td class="summary-value">Q {{ number_format((float) $arqueo->total_arqueado, 2) }}</td>
        </tr>

        <tr>
            <td class="summary-label">Diferencia</td>
            <td class="summary-value difference">Q {{ number_format(abs($diferencia), 2) }}</td>

            <td class="summary-label">Resultado</td>
            <td class="result">{{ $resultado }}</td>
        </tr>
    </table>

    @if(!empty($arqueo->observaciones))
        <div class="notes">
            <div class="notes-title">Observaciones</div>
            <div class="notes-text">{{ $arqueo->observaciones }}</div>
        </div>
    @endif

    <div class="footer">
        Documento generado por el Sistema de Arqueos para Agentes MICOOPE · Auditoría de Agentes
    </div>
</body>
</html>
