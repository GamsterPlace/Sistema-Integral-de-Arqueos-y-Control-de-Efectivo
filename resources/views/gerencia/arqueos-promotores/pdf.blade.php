<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $arqueo->numero_arqueo }}</title>

    @php
        $agente = $arqueo->agente;
        $ruta = $agente?->ruta;
        $region = $ruta?->region;

        $nombrePromotor = trim(
            ($promotor->nombres ?? '')
            . ' '
            . ($promotor->apellidos ?? '')
        );

        $billetesMapa = collect($billetes)->keyBy(
            fn ($detalle) => number_format(
                (float) $detalle->denominacion,
                2,
                '.',
                ''
            )
        );

        $monedasMapa = collect($monedas)->keyBy(
            fn ($detalle) => number_format(
                (float) $detalle->denominacion,
                2,
                '.',
                ''
            )
        );

        $denominacionesBilletes = [200, 100, 50, 20, 10, 5, 1];
        $denominacionesMonedas = [1, 0.50, 0.25, 0.10, 0.05];

        $numeroArqueo = preg_replace(
            '/[^0-9]/',
            '',
            (string) $arqueo->numero_arqueo
        );

        $rutaLogoEcosaba = public_path('images/logos/ecosaba.png');
        $rutaLogoAgentes = public_path('images/logos/agentes-micoope.png');

        $logoEcosaba = file_exists($rutaLogoEcosaba)
            ? 'data:image/png;base64,' . base64_encode(
                file_get_contents($rutaLogoEcosaba)
            )
            : null;

        $logoAgentes = file_exists($rutaLogoAgentes)
            ? 'data:image/png;base64,' . base64_encode(
                file_get_contents($rutaLogoAgentes)
            )
            : null;
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
            color: #333333;
            background: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8pt;
        }

        .page {
            position: relative;
            width: 612pt;
            height: 792pt;
            overflow: hidden;
            background: #ffffff;
        }

        .absolute {
            position: absolute;
        }

        .logo-ecosaba {
            position: absolute;
            top: 37pt;
            left: 38pt;
            width: 150pt;
            height: 53pt;
            object-fit: contain;
        }

        .main-title {
            top: 48pt;
            left: 170pt;
            width: 285pt;
            color: #333333;
            font-family: DejaVu Serif, serif;
            font-size: 13.4pt;
            font-weight: 700;
            line-height: 15pt;
            text-align: center;
            text-transform: uppercase;
        }

        .main-subtitle {
            top: 68pt;
            left: 185pt;
            width: 255pt;
            color: #333333;
            font-family: DejaVu Serif, serif;
            font-size: 10.8pt;
            font-weight: 700;
            line-height: 13pt;
            text-align: center;
            text-transform: uppercase;
        }

        .header-number {
            top: 49pt;
            right: 40pt;
            width: 145pt;
            height: 20pt;
            overflow: visible;
            color: #9f3a35;
            font-family: DejaVu Serif, serif;
            font-size: 9.5pt;
            font-weight: 700;
            line-height: 12pt;
            letter-spacing: .5pt;
            text-align: right;
            white-space: nowrap;
        }

        .header-fields {
            position: absolute;
            top: 108pt;
            left: 42pt;
            width: 528pt;
            font-size: 7.2pt;
            line-height: 13pt;
        }

        .header-row {
            width: 528pt;
            margin: 0;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: auto;
        }

        .header-row + .header-row {
            margin-top: 5pt;
        }

        .header-row td {
            height: 14pt;
            margin: 0;
            padding: 0;
            vertical-align: bottom;
            white-space: nowrap;
        }

        .header-row .label-cell {
            width: 1%;
            line-height: 13pt;
            text-align: left;
            white-space: nowrap;
        }

        .header-row .value-cell {
            padding: 0 2pt 1pt 0;
            overflow: hidden;
            border-bottom: .65pt solid #333333;
            line-height: 12pt;
            text-align: center;
            white-space: nowrap;
        }

        .header-row .spacer-cell {
            padding: 0;
        }

        .watermark-logo {
            position: absolute;
            top: 242pt;
            left: 160pt;
            width: 292pt;
            height: 348pt;
            opacity: .045;
            object-fit: contain;
        }

        .section-title {
            font-family: DejaVu Serif, serif;
            font-size: 10.5pt;
            font-weight: 700;
        }

        .section-subtitle {
            font-size: 7.3pt;
            font-weight: 700;
        }

        .bills-title {
            top: 184pt;
            left: 47pt;
        }

        .bills-subtitle {
            top: 202pt;
            left: 47pt;
        }

        .bill-qty-title {
            top: 225pt;
            left: 127pt;
            width: 72pt;
            text-align: center;
        }

        .bill-sub-title {
            top: 225pt;
            left: 247pt;
            width: 78pt;
            text-align: center;
        }

        .bill-total-title {
            top: 225pt;
            left: 391pt;
            width: 77pt;
            text-align: center;
        }

        .money-row {
            height: 14pt;
            font-size: 8pt;
            white-space: nowrap;
        }

        .bill-row {
            left: 68pt;
            width: 278pt;
        }

        .bill-denomination {
            position: absolute;
            left: 0;
            width: 76pt;
            text-align: right;
        }

        .bill-quantity {
            position: absolute;
            left: 86pt;
            width: 67pt;
            height: 12pt;
            border-bottom: .65pt solid #333333;
            text-align: center;
        }

        .bill-prefix {
            position: absolute;
            left: 168pt;
        }

        .bill-subtotal {
            position: absolute;
            left: 185pt;
            width: 92pt;
            height: 12pt;
            padding-right: 2pt;
            border-bottom: .65pt solid #333333;
            text-align: right;
        }

        .bill-total {
            top: 335pt;
            left: 355pt;
            width: 125pt;
            height: 15pt;
            font-size: 8pt;
        }

        .bill-total .prefix,
        .coin-total .prefix {
            position: absolute;
            left: 0;
        }

        .bill-total .value,
        .coin-total .value {
            position: absolute;
            left: 18pt;
            width: 106pt;
            height: 12pt;
            padding-right: 2pt;
            border-bottom: .65pt solid #333333;
            text-align: right;
        }

        .coins-title {
            top: 363pt;
            left: 47pt;
        }

        .coins-subtitle {
            top: 381pt;
            left: 47pt;
        }

        .coin-qty-title {
            top: 404pt;
            left: 88pt;
            width: 76pt;
            text-align: center;
        }

        .coin-sub-title {
            top: 404pt;
            left: 228pt;
            width: 78pt;
            text-align: center;
        }

        .coin-row {
            left: 73pt;
            width: 273pt;
        }

        .coin-denomination {
            position: absolute;
            left: 0;
            width: 69pt;
            text-align: right;
        }

        .coin-quantity {
            position: absolute;
            left: 78pt;
            width: 66pt;
            height: 12pt;
            border-bottom: .65pt solid #333333;
            text-align: center;
        }

        .coin-prefix {
            position: absolute;
            left: 158pt;
        }

        .coin-subtotal {
            position: absolute;
            left: 175pt;
            width: 94pt;
            height: 12pt;
            padding-right: 2pt;
            border-bottom: .65pt solid #333333;
            text-align: right;
        }

        .coin-total {
            top: 472pt;
            left: 355pt;
            width: 125pt;
            height: 15pt;
            font-size: 8pt;
        }

        .total-arqueado-middle {
            top: 500pt;
            left: 242pt;
            width: 238pt;
            height: 15pt;
            font-size: 8.5pt;
            text-align: right;
        }

        .total-arqueado-middle .value {
            display: inline-block;
            width: 108pt;
            height: 12pt;
            padding-right: 2pt;
            border-bottom: .7pt solid #333333;
            text-align: right;
        }

        .summary {
            top: 542pt;
            left: 382pt;
            width: 177pt;
            font-size: 8.4pt;
        }

        .summary-row {
            position: relative;
            height: 22pt;
        }

        .summary-label {
            position: absolute;
            left: 0;
            width: 98pt;
            text-align: right;
        }

        .summary-prefix {
            position: absolute;
            left: 106pt;
        }

        .summary-value {
            position: absolute;
            top: -1pt;
            left: 122pt;
            width: 55pt;
            height: 13pt;
            padding-right: 2pt;
            border-bottom: .7pt solid #333333;
            text-align: right;
        }

        .summary-difference {
            margin-top: 8pt;
            font-size: 9pt;
        }

        .observations {
            position: absolute;
            top: 625pt;
            left: 46pt;
            width: 520pt;
        }

        .text-title {
            margin-bottom: 2pt;
            font-size: 7.2pt;
            font-weight: 700;
        }

        .text-line {
            width: 100%;
            min-height: 11pt;
            padding: 0 2pt;
            border-bottom: .65pt solid #333333;
            font-size: 6.4pt;
            line-height: 10.6pt;
            text-align: justify;
        }

        .promoter-info {
            position: absolute;
            top: 674pt;
            left: 46pt;
            width: 520pt;
            font-size: 7pt;
        }

        .promoter-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .promoter-info td {
            height: 15pt;
            padding: 0;
            vertical-align: bottom;
        }

        .promoter-info .label {
            width: 68pt;
            font-weight: 700;
        }

        .promoter-info .value {
            padding: 0 3pt 1pt;
            border-bottom: .65pt solid #333333;
        }

        .footer-note {
            position: absolute;
            bottom: 28pt;
            left: 46pt;
            width: 520pt;
            color: #666666;
            font-size: 5.5pt;
            line-height: 7pt;
            text-align: center;
        }
    </style>
</head>

<body>
<div class="page">

    @if($logoEcosaba)
        <img
            class="logo-ecosaba"
            src="{{ $logoEcosaba }}"
            alt="ECOSABA"
        >
    @endif

    <div class="absolute main-title">
        Arqueo y Corte de Caja
    </div>

    <div class="absolute main-subtitle">
        Agentes MICOOPE
    </div>

    <div class="absolute header-number">
        N.º {{ $numeroArqueo }}
    </div>

    <div class="header-fields">
        <table class="header-row">
            <tr>
                <td
                    class="spacer-cell"
                    style="width:141pt;"
                ></td>

                <td class="label-cell">
                    Fecha:
                </td>

                <td
                    class="value-cell"
                    style="width:92pt;"
                >
                    {{ \Carbon\Carbon::parse(
                        $arqueo->fecha_arqueo
                    )->format('d/m/Y') }}
                </td>

                <td
                    class="spacer-cell"
                    style="width:18pt;"
                ></td>

                <td class="label-cell">
                    Estado:
                </td>

                <td class="value-cell">
                    {{ str_replace(
                        '_',
                        ' ',
                        $arqueo->estado
                    ) }}
                </td>
            </tr>
        </table>

        <table class="header-row">
            <tr>
                <td class="label-cell">
                    Agente No.:
                </td>

                <td
                    class="value-cell"
                    style="width:58pt;"
                >
                    {{ $agente?->codigo_agente }}
                </td>

                <td class="label-cell">
                    Nombre Negocio:
                </td>

                <td
                    class="value-cell"
                    style="width:150pt;"
                >
                    {{ $agente?->nombre_negocio }}
                </td>

                <td class="label-cell">
                    Extemporáneo:
                </td>

                <td class="value-cell">
                    {{ $arqueo->fuera_fecha_ordinaria
                        ? 'Sí'
                        : 'No' }}
                </td>
            </tr>
        </table>

        <table class="header-row">
            <tr>
                <td class="label-cell">
                    Nombre del Propietario o Receptor Pagador:
                </td>

                <td class="value-cell">
                    {{ $agente?->nombre_propietario }}
                </td>
            </tr>
        </table>

        <table class="header-row">
            <tr>
                <td class="label-cell">
                    Región:
                </td>

                <td
                    class="value-cell"
                    style="width:150pt;"
                >
                    {{ $region?->nombre ?? '—' }}
                </td>

                <td
                    class="spacer-cell"
                    style="width:15pt;"
                ></td>

                <td class="label-cell">
                    Ruta:
                </td>

                <td class="value-cell">
                    {{ $ruta?->codigo ?? '' }}
                    {{ $ruta?->nombre ?? '—' }}
                </td>
            </tr>
        </table>
    </div>

    @if($logoAgentes)
        <img
            class="watermark-logo"
            src="{{ $logoAgentes }}"
            alt=""
        >
    @endif

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

    @foreach($denominacionesBilletes as $index => $denominacion)
        @php
            $clave = number_format(
                (float) $denominacion,
                2,
                '.',
                ''
            );

            $detalle = $billetesMapa->get($clave);

            $cantidad = $detalle
                ? $detalle->cantidad
                : 0;

            $subtotal = $detalle
                ? (float) $detalle->subtotal
                : 0;

            $top = 247 + ($index * 13);
        @endphp

        <div
            class="absolute money-row bill-row"
            style="top:{{ $top }}pt;"
        >
            <span class="bill-denomination">
                Q. {{ number_format(
                    (float) $denominacion,
                    2
                ) }}
            </span>

            <span class="bill-quantity">
                {{ $cantidad }}
            </span>

            <span class="bill-prefix">
                Q.
            </span>

            <span class="bill-subtotal">
                {{ number_format(
                    $subtotal,
                    2
                ) }}
            </span>
        </div>
    @endforeach

    <div class="absolute bill-total">
        <span class="prefix">
            Q.
        </span>

        <span class="value">
            {{ number_format(
                (float) collect($billetes)->sum('subtotal'),
                2
            ) }}
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

    @foreach($denominacionesMonedas as $index => $denominacion)
        @php
            $clave = number_format(
                (float) $denominacion,
                2,
                '.',
                ''
            );

            $detalle = $monedasMapa->get($clave);

            $cantidad = $detalle
                ? $detalle->cantidad
                : 0;

            $subtotal = $detalle
                ? (float) $detalle->subtotal
                : 0;

            $top = 426 + ($index * 13);
        @endphp

        <div
            class="absolute money-row coin-row"
            style="top:{{ $top }}pt;"
        >
            <span class="coin-denomination">
                Q. {{ number_format(
                    (float) $denominacion,
                    2
                ) }}
            </span>

            <span class="coin-quantity">
                {{ $cantidad }}
            </span>

            <span class="coin-prefix">
                Q.
            </span>

            <span class="coin-subtotal">
                {{ number_format(
                    $subtotal,
                    2
                ) }}
            </span>
        </div>
    @endforeach

    <div class="absolute coin-total">
        <span class="prefix">
            Q.
        </span>

        <span class="value">
            {{ number_format(
                (float) collect($monedas)->sum('subtotal'),
                2
            ) }}
        </span>
    </div>

    <div class="absolute total-arqueado-middle">
        Total Arqueado&nbsp;&nbsp;&nbsp; Q.

        <span class="value">
            {{ number_format(
                (float) $arqueo->total_arqueado,
                2
            ) }}
        </span>
    </div>

    <div class="absolute summary">
        <div class="summary-row">
            <span class="summary-label">
                Saldo del Sistema
            </span>

            <span class="summary-prefix">
                Q.
            </span>

            <span class="summary-value">
                {{ number_format(
                    (float) $arqueo->saldo_sistema,
                    2
                ) }}
            </span>
        </div>

        <div class="summary-row">
            <span class="summary-label">
                Total Arqueado
            </span>

            <span class="summary-prefix">
                Q.
            </span>

            <span class="summary-value">
                {{ number_format(
                    (float) $arqueo->total_arqueado,
                    2
                ) }}
            </span>
        </div>

        <div class="summary-row summary-difference">
            <span class="summary-label">
                DIFERENCIA
            </span>

            <span class="summary-prefix">
                Q.
            </span>

            <span class="summary-value">
                {{ number_format(
                    abs((float) $arqueo->diferencia),
                    2
                ) }}
            </span>
        </div>
    </div>

    <div class="observations">
        <div class="text-title">
            Observaciones:
        </div>

        <div class="text-line">
            {{ $arqueo->observaciones ?: 'Sin observaciones' }}
        </div>
    </div>

    <div class="promoter-info">
        <table>
            <tr>
                <td class="label">
                    Promotor:
                </td>

                <td class="value">
                    {{ $nombrePromotor !== ''
                        ? $nombrePromotor
                        : ($promotor->usuario ?? '—') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-note">
        Sistema de Arqueos para Agentes MICOOPE
    </div>

</div>
</body>
</html>
