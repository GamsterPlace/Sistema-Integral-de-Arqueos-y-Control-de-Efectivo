<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Arqueo {{ $arqueo->numero_arqueo }}</title>

    @php
        $billetesMapa = collect($billetes)->keyBy(
            fn ($detalle) => number_format(
                (float) data_get($detalle, 'denominacion'),
                2,
                '.',
                ''
            )
        );

        $monedasMapa = collect($monedas)->keyBy(
            fn ($detalle) => number_format(
                (float) data_get($detalle, 'denominacion'),
                2,
                '.',
                ''
            )
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

        $numeroArqueo = preg_replace(
            '/[^0-9]/',
            '',
            (string) $arqueo->numero_arqueo
        );
    @endphp

    <style>
        @page {
            size: 612pt 792pt;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 612pt;
            height: 792pt;
            margin: 0;
            padding: 0;
        }

        body {
            color: #111;
            background: #fff;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8pt;
        }

        .page {
            position: relative;
            width: 612pt;
            height: 792pt;
            overflow: hidden;
            background: #fff;
        }

        .absolute {
            position: absolute;
        }

        .bold {
            font-weight: 700;
        }

        .serif {
            font-family: DejaVu Serif, serif;
        }

        .field-line {
            display: inline-block;
            height: 13pt;
            padding: 0 2pt 1pt;
            overflow: hidden;
            border-bottom: .65pt solid #111;
            vertical-align: bottom;
            white-space: nowrap;
        }

        /* ================================================================
           ENCABEZADO
        ================================================================= */

        .ecosaba-logo {
            top: 34pt;
            left: 48pt;
            width: 127pt;
            height: 43pt;
            border: 1.2pt solid #26392b;
            border-radius: 3pt;
            color: #294a35;
            background: #fff;
        }

        .ecosaba-symbol {
            position: absolute;
            top: 6pt;
            left: 7pt;
            width: 28pt;
            height: 27pt;
        }

        .ecosaba-symbol .ring {
            position: absolute;
            top: 1pt;
            left: 1pt;
            width: 25pt;
            height: 25pt;
            border: 2.2pt solid #38633f;
            border-radius: 50%;
        }

        .ecosaba-symbol .leaf-a,
        .ecosaba-symbol .leaf-b {
            position: absolute;
            width: 12pt;
            height: 6pt;
            border-radius: 50%;
            background: #38633f;
        }

        .ecosaba-symbol .leaf-a {
            top: 6pt;
            left: 8pt;
            transform: rotate(-32deg);
        }

        .ecosaba-symbol .leaf-b {
            top: 15pt;
            left: 8pt;
            transform: rotate(32deg);
        }

        .ecosaba-word {
            position: absolute;
            top: 8pt;
            left: 38pt;
            width: 82pt;
            font-family: DejaVu Serif, serif;
            font-size: 15pt;
            font-weight: 700;
            letter-spacing: -.4pt;
        }

        .ecosaba-tagline {
            position: absolute;
            top: 28pt;
            left: 38pt;
            width: 82pt;
            font-size: 4.2pt;
            font-weight: 700;
            letter-spacing: .15pt;
            text-align: center;
        }

        .micoope-logo {
            top: 34pt;
            left: 181pt;
            width: 61pt;
            height: 43pt;
            border: 1.2pt solid #26392b;
            border-radius: 3pt;
            color: #294a35;
            background: #fff;
            text-align: center;
        }

        .micoope-icon {
            position: absolute;
            top: 5pt;
            left: 17pt;
            width: 26pt;
            height: 18pt;
        }

        .micoope-icon .piece {
            position: absolute;
            width: 10pt;
            height: 10pt;
            border-radius: 1.2pt;
            background: #3f6844;
            transform: rotate(45deg);
        }

        .micoope-icon .p1 {
            top: 2pt;
            left: 1pt;
        }

        .micoope-icon .p2 {
            top: 2pt;
            left: 13pt;
        }

        .micoope-icon .p3 {
            top: 10pt;
            left: 7pt;
        }

        .micoope-word {
            position: absolute;
            top: 27pt;
            left: 2pt;
            width: 56pt;
            font-size: 7.2pt;
            font-weight: 700;
            letter-spacing: .4pt;
        }

        .header-rule {
            top: 53pt;
            left: 246pt;
            width: 245pt;
            height: 1.5pt;
        }

        .header-rule .green {
            float: left;
            width: 153pt;
            height: 1.5pt;
            background: #4d693f;
        }

        .header-rule .yellow {
            float: left;
            width: 92pt;
            height: 1.5pt;
            background: #d8b427;
        }

        .header-type {
            top: 37pt;
            left: 496pt;
            width: 82pt;
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .header-number {
            top: 55pt;
            left: 491pt;
            width: 91pt;
            color: #a31d17;
            font-family: DejaVu Serif, serif;
            font-size: 14pt;
            font-weight: 700;
            letter-spacing: 1pt;
            text-align: right;
            white-space: nowrap;
        }

        .main-title {
            top: 93pt;
            left: 226pt;
            width: 260pt;
            font-family: DejaVu Serif, serif;
            font-size: 14pt;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .main-subtitle {
            top: 111pt;
            left: 273pt;
            width: 166pt;
            font-family: DejaVu Serif, serif;
            font-size: 12pt;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        /* ================================================================
           DATOS GENERALES
        ================================================================= */

        .general-field {
            font-size: 7.7pt;
            font-weight: 700;
            white-space: nowrap;
        }

        .business {
            top: 157pt;
            left: 50pt;
        }

        .business .field-line {
            width: 181pt;
        }

        .address {
            top: 157pt;
            left: 350pt;
        }

        .address .field-line {
            width: 207pt;
        }

        .owner {
            top: 182pt;
            left: 50pt;
        }

        .owner .field-line {
            width: 356pt;
        }

        .agent-code {
            top: 207pt;
            left: 50pt;
        }

        .agent-code .field-line {
            width: 75pt;
        }

        .date {
            top: 207pt;
            left: 183pt;
        }

        .date .field-line {
            width: 91pt;
        }

        .start {
            top: 207pt;
            left: 397pt;
        }

        .start .field-line {
            width: 71pt;
        }

        .end {
            top: 207pt;
            left: 489pt;
        }

        .end .field-line {
            width: 69pt;
        }

        /* ================================================================
           MARCA DE AGUA
        ================================================================= */

        .watermark {
            top: 277pt;
            left: 183pt;
            width: 244pt;
            height: 281pt;
            color: #e5e5e5;
            opacity: .64;
            text-align: center;
            z-index: 0;
        }

        .watermark-shape {
            position: absolute;
            top: 30pt;
            left: 70pt;
            width: 104pt;
            height: 104pt;
            border: 9pt solid #e5e5e5;
            border-radius: 50%;
        }

        .watermark-shape:before,
        .watermark-shape:after {
            content: "";
            position: absolute;
            width: 83pt;
            height: 20pt;
            background: #e5e5e5;
            border-radius: 12pt;
        }

        .watermark-shape:before {
            top: 21pt;
            left: 1pt;
            transform: rotate(-28deg);
        }

        .watermark-shape:after {
            top: 53pt;
            left: 1pt;
            transform: rotate(28deg);
        }

        .watermark-word-a {
            position: absolute;
            top: 147pt;
            left: 0;
            width: 244pt;
            font-size: 31pt;
            font-weight: 700;
            letter-spacing: 1pt;
        }

        .watermark-word-b {
            position: absolute;
            top: 190pt;
            left: 0;
            width: 244pt;
            font-size: 30pt;
            font-weight: 700;
            letter-spacing: 2pt;
        }

        /* ================================================================
           SECCIÓN BILLETES
        ================================================================= */

        .section-title {
            font-family: DejaVu Serif, serif;
            font-size: 10.5pt;
            font-weight: 700;
        }

        .bills-title {
            top: 276pt;
            left: 51pt;
        }

        .section-subtitle {
            font-size: 7.3pt;
            font-weight: 700;
        }

        .bills-subtitle {
            top: 293pt;
            left: 51pt;
        }

        .bill-qty-title {
            top: 322pt;
            left: 144pt;
            width: 73pt;
            text-align: center;
        }

        .bill-sub-title {
            top: 322pt;
            left: 270pt;
            width: 76pt;
            text-align: center;
        }

        .bill-total-title {
            top: 322pt;
            left: 412pt;
            width: 67pt;
            text-align: center;
        }

        .money-row {
            height: 14pt;
            font-size: 8pt;
            font-weight: 700;
            white-space: nowrap;
        }

        .bill-row {
            left: 94pt;
            width: 269pt;
        }

        .bill-denomination {
            position: absolute;
            left: 0;
            width: 69pt;
            text-align: right;
        }

        .bill-quantity {
            position: absolute;
            left: 78pt;
            width: 63pt;
            height: 12pt;
            border-bottom: .65pt solid #111;
            text-align: center;
        }

        .money-prefix {
            position: absolute;
            left: 153pt;
        }

        .bill-subtotal {
            position: absolute;
            left: 170pt;
            width: 93pt;
            height: 12pt;
            padding-right: 2pt;
            border-bottom: .65pt solid #111;
            text-align: right;
        }

        .bill-total {
            top: 431pt;
            left: 377pt;
            width: 111pt;
            height: 15pt;
            font-size: 8pt;
            font-weight: 700;
        }

        .bill-total .prefix {
            position: absolute;
            left: 0;
        }

        .bill-total .value {
            position: absolute;
            left: 17pt;
            width: 94pt;
            height: 12pt;
            padding-right: 2pt;
            border-bottom: .65pt solid #111;
            text-align: right;
        }

        /* ================================================================
           SECCIÓN MONEDAS
        ================================================================= */

        .coins-title {
            top: 466pt;
            left: 51pt;
        }

        .coins-subtitle {
            top: 483pt;
            left: 51pt;
        }

        .coin-qty-title {
            top: 507pt;
            left: 56pt;
            width: 75pt;
            text-align: center;
        }

        .coin-sub-title {
            top: 507pt;
            left: 195pt;
            width: 75pt;
            text-align: center;
        }

        .coin-row {
            left: 95pt;
            width: 267pt;
        }

        .coin-denomination {
            position: absolute;
            left: 0;
            width: 62pt;
            text-align: right;
        }

        .coin-quantity {
            position: absolute;
            left: 69pt;
            width: 61pt;
            height: 12pt;
            border-bottom: .65pt solid #111;
            text-align: center;
        }

        .coin-prefix {
            position: absolute;
            left: 143pt;
        }

        .coin-subtotal {
            position: absolute;
            left: 160pt;
            width: 92pt;
            height: 12pt;
            padding-right: 2pt;
            border-bottom: .65pt solid #111;
            text-align: right;
        }

        .coin-total {
            top: 571pt;
            left: 377pt;
            width: 111pt;
            height: 15pt;
            font-size: 8pt;
            font-weight: 700;
        }

        .coin-total .prefix {
            position: absolute;
            left: 0;
        }

        .coin-total .value {
            position: absolute;
            left: 17pt;
            width: 94pt;
            height: 12pt;
            padding-right: 2pt;
            border-bottom: .65pt solid #111;
            text-align: right;
        }

        .total-arqueado-middle {
            top: 601pt;
            left: 265pt;
            width: 224pt;
            height: 15pt;
            font-size: 8.5pt;
            font-weight: 700;
            text-align: right;
        }

        .total-arqueado-middle .value {
            display: inline-block;
            width: 102pt;
            height: 12pt;
            padding-right: 2pt;
            border-bottom: .7pt solid #111;
            text-align: right;
        }

        /* ================================================================
           RESUMEN FINAL
        ================================================================= */

        .summary {
            top: 646pt;
            left: 410pt;
            width: 166pt;
            font-size: 8.4pt;
            font-weight: 700;
        }

        .summary-row {
            position: relative;
            height: 22pt;
        }

        .summary-label {
            position: absolute;
            left: 0;
            width: 92pt;
            text-align: right;
        }

        .summary-prefix {
            position: absolute;
            left: 99pt;
        }

        .summary-value {
            position: absolute;
            left: 115pt;
            top: -1pt;
            width: 51pt;
            height: 13pt;
            padding-right: 2pt;
            border-bottom: .7pt solid #111;
            text-align: right;
        }

        .summary-difference {
            margin-top: 8pt;
            font-size: 9pt;
        }

        /* ================================================================
           CERTIFICACIÓN
        ================================================================= */

        .certification {
            top: 705pt;
            left: 49pt;
            width: 529pt;
            color: #111;
            font-size: 6.3pt;
            line-height: 11pt;
            z-index: 2;
        }

        .certification-title {
            font-size: 7.2pt;
            font-weight: 700;
        }

        .certification-line {
            width: 100%;
            min-height: 12pt;
            padding: 1pt 2pt 0;
            border-bottom: .65pt solid #111;
        }

        /* ================================================================
           FIRMAS Y CÓDIGO
        ================================================================= */

        .signatures {
            top: 753pt;
            left: 54pt;
            width: 504pt;
            height: 27pt;
        }

        .signature {
            position: absolute;
            width: 189pt;
            text-align: center;
        }

        .signature-agent {
            left: 0;
        }

        .signature-promoter {
            right: 0;
        }

        .signature-line {
            width: 189pt;
            height: 8pt;
            border-bottom: .7pt solid #111;
        }

        .signature-label {
            margin-top: 1.5pt;
            font-size: 6.4pt;
            font-weight: 700;
            line-height: 8pt;
        }

        .form-code {
            right: 49pt;
            bottom: 8pt;
            font-size: 6.6pt;
            font-weight: 700;
        }
    </style>
</head>

<body>
<div class="page">

    <div class="absolute ecosaba-logo">
        <div class="ecosaba-symbol">
            <div class="ring"></div>
            <div class="leaf-a"></div>
            <div class="leaf-b"></div>
        </div>

        <div class="ecosaba-word">ECOSABA</div>
        <div class="ecosaba-tagline">AHORRO, PRÉSTAMOS, SEGUROS Y MÁS</div>
    </div>

    <div class="absolute micoope-logo">
        <div class="micoope-icon">
            <span class="piece p1"></span>
            <span class="piece p2"></span>
            <span class="piece p3"></span>
        </div>

        <div class="micoope-word">MICOOPE</div>
    </div>

    <div class="absolute header-rule">
        <div class="green"></div>
        <div class="yellow"></div>
    </div>

    <div class="absolute header-type">
        Agentes MICOOPE
    </div>

    <div class="absolute header-number">
        N.º {{ $numeroArqueo }}
    </div>

    <div class="absolute main-title">
        Arqueo y Corte de Caja
    </div>

    <div class="absolute main-subtitle">
        Agente MICOOPE
    </div>

    <div class="absolute general-field business">
        Nombre del negocio:
        <span class="field-line">
            {{ $arqueo->nombre_negocio_historico }}
        </span>
    </div>

    <div class="absolute general-field address">
        Dirección:
        <span class="field-line">
            {{ $arqueo->direccion_historica }}
        </span>
    </div>

    <div class="absolute general-field owner">
        Nombre del Propietario o Receptor Pagador:
        <span class="field-line">
            {{ $arqueo->nombre_propietario_historico }}
        </span>
    </div>

    <div class="absolute general-field agent-code">
        Agente No.:
        <span class="field-line">
            {{ $arqueo->codigo_agente_historico }}
        </span>
    </div>

    <div class="absolute general-field date">
        Fecha:
        <span class="field-line">
            {{ $fechaArqueo }}
        </span>
    </div>

    <div class="absolute general-field start">
        H.I.:
        <span class="field-line">
            {{ $horaInicio }}
        </span>
    </div>

    <div class="absolute general-field end">
        H.F.:
        <span class="field-line">
            {{ $horaFin }}
        </span>
    </div>

    <div class="absolute watermark">
        <div class="watermark-shape"></div>
        <div class="watermark-word-a">AGENTE</div>
        <div class="watermark-word-b">MICOOPE</div>
    </div>

    <div class="absolute section-title bills-title">
        Billetes
    </div>

    <div class="absolute section-subtitle bills-subtitle">
        Denominación
    </div>

    <div class="absolute section-subtitle bill-qty-title">
        Cantidad
    </div>

    <div class="absolute section-subtitle bill-sub-title">
        Sub-total
    </div>

    <div class="absolute section-subtitle bill-total-title">
        Totales
    </div>

    @foreach ($denominacionesBilletes as $index => $denominacion)
        @php
            $clave = number_format(
                (float) $denominacion,
                2,
                '.',
                ''
            );

            $detalle = $billetesMapa->get($clave);

            $cantidad = (int) data_get(
                $detalle,
                'cantidad',
                0
            );

            $subtotal = (float) data_get(
                $detalle,
                'subtotal',
                0
            );

            $top = 344 + ($index * 13);
        @endphp

        <div
            class="absolute money-row bill-row"
            style="top: {{ $top }}pt;"
        >
            <span class="bill-denomination">
                Q. {{ number_format((float) $denominacion, 2) }}
            </span>

            <span class="bill-quantity">
                {{ $cantidad }}
            </span>

            <span class="money-prefix">
                Q.
            </span>

            <span class="bill-subtotal">
                {{ number_format($subtotal, 2) }}
            </span>
        </div>
    @endforeach

    <div class="absolute bill-total">
        <span class="prefix">Q.</span>

        <span class="value">
            {{ number_format((float) $arqueo->total_billetes, 2) }}
        </span>
    </div>

    <div class="absolute section-title coins-title">
        Monedas
    </div>

    <div class="absolute section-subtitle coins-subtitle">
        Denominación
    </div>

    <div class="absolute section-subtitle coin-qty-title">
        Cantidad
    </div>

    <div class="absolute section-subtitle coin-sub-title">
        Sub-total
    </div>

    @foreach ($denominacionesMonedas as $index => $denominacion)
        @php
            $clave = number_format(
                (float) $denominacion,
                2,
                '.',
                ''
            );

            $detalle = $monedasMapa->get($clave);

            $cantidad = (int) data_get(
                $detalle,
                'cantidad',
                0
            );

            $subtotal = (float) data_get(
                $detalle,
                'subtotal',
                0
            );

            $top = 528 + ($index * 13);
        @endphp

        <div
            class="absolute money-row coin-row"
            style="top: {{ $top }}pt;"
        >
            <span class="coin-denomination">
                Q. {{ number_format((float) $denominacion, 2) }}
            </span>

            <span class="coin-quantity">
                {{ $cantidad }}
            </span>

            <span class="coin-prefix">
                Q.
            </span>

            <span class="coin-subtotal">
                {{ number_format($subtotal, 2) }}
            </span>
        </div>
    @endforeach

    <div class="absolute coin-total">
        <span class="prefix">Q.</span>

        <span class="value">
            {{ number_format((float) $arqueo->total_monedas, 2) }}
        </span>
    </div>

    <div class="absolute total-arqueado-middle">
        Total Arqueado&nbsp;&nbsp;&nbsp; Q.
        <span class="value">
            {{ number_format((float) $arqueo->total_arqueado, 2) }}
        </span>
    </div>

    <div class="absolute summary">
        <div class="summary-row">
            <span class="summary-label">
                Saldo del Sistema
            </span>

            <span class="summary-prefix">Q.</span>

            <span class="summary-value">
                {{ number_format((float) $arqueo->saldo_sistema, 2) }}
            </span>
        </div>

        <div class="summary-row">
            <span class="summary-label">
                Total Arqueado
            </span>

            <span class="summary-prefix">Q.</span>

            <span class="summary-value">
                {{ number_format((float) $arqueo->total_arqueado, 2) }}
            </span>
        </div>

        <div class="summary-row summary-difference">
            <span class="summary-label">
                DIFERENCIA
            </span>

            <span class="summary-prefix">Q.</span>

            <span class="summary-value">
                {{ number_format((float) $arqueo->diferencia, 2) }}
            </span>
        </div>
    </div>

    <div class="absolute certification">
        <div class="certification-title">
            Certificación:
        </div>

        <div class="certification-line">
            {{ $arqueo->certificacion }}
        </div>

        <div class="certification-line"></div>
        <div class="certification-line"></div>
    </div>

    <div class="absolute signatures">
        <div class="signature signature-agent">
            <div class="signature-line"></div>

            <div class="signature-label">
                Elaborado Por:<br>
                Propietario o Receptor Pagador
            </div>
        </div>

        <div class="signature signature-promoter">
            <div class="signature-line"></div>

            <div class="signature-label">
                Revisado Por:<br>
                Promotor Agentes MICOOPE
            </div>
        </div>
    </div>

    <div class="absolute form-code">
        DEL 01 AL 13,500
    </div>

</div>
</body>
</html>
