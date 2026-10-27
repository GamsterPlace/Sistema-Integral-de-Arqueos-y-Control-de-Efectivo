<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $arqueo->numero_arqueo }}</title>

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

        /*
         * Flujo oficial del arqueo realizado por Promotor:
         * REALIZADOR   = Promotor
         * VALIDADOR    = Agente
         * CERTIFICADOR = Jefe de Agentes
         */
        $firmaPromotor = collect($arqueo->firmas ?? [])
            ->first(fn ($firma) =>
                data_get($firma, 'tipo_firma') === 'REALIZADOR'
                && (int) data_get($firma, 'valida') === 1
            );

        $firmaAgente = collect($arqueo->firmas ?? [])
            ->first(fn ($firma) =>
                data_get($firma, 'tipo_firma') === 'VALIDADOR'
                && (int) data_get($firma, 'valida') === 1
            );

        $firmaJefe = collect($arqueo->firmas ?? [])
            ->first(fn ($firma) =>
                data_get($firma, 'tipo_firma') === 'CERTIFICADOR'
                && (int) data_get($firma, 'valida') === 1
            );

        $rutaLogoEcosaba = public_path('images/logos/ecosaba.png');
        $rutaLogoAgentes = public_path('images/logos/agentes-micoope.png');

        if (! file_exists($rutaLogoEcosaba)) {
            throw new \RuntimeException(
                'No se encontró el logo de ECOSABA en: '
                . $rutaLogoEcosaba
            );
        }

        if (! file_exists($rutaLogoAgentes)) {
            throw new \RuntimeException(
                'No se encontró el logo de Agentes MICOOPE en: '
                . $rutaLogoAgentes
            );
        }

        $logoEcosaba = 'data:image/png;base64,' . base64_encode(
            file_get_contents($rutaLogoEcosaba)
        );

        $logoAgentes = 'data:image/png;base64,' . base64_encode(
            file_get_contents($rutaLogoAgentes)
        );

        $textoObservaciones = wordwrap(
            trim(
                (string) (
                    $arqueo->observaciones
                    ?: 'Sin observaciones registradas.'
                )
            ),
            150,
            "\n",
            false
        );

        $lineasObservaciones = array_slice(
            explode("\n", $textoObservaciones),
            0,
            2
        );

        while (count($lineasObservaciones) < 2) {
            $lineasObservaciones[] = '';
        }

        $textoCalificacion = wordwrap(
            trim(
                (string) (
                    $arqueo->certificacion
                    ?: 'Sin calificación registrada.'
                )
            ),
            150,
            "\n",
            false
        );

        $lineasCalificacion = array_slice(
            explode("\n", $textoCalificacion),
            0,
            3
        );

        while (count($lineasCalificacion) < 3) {
            $lineasCalificacion[] = '';
        }
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

        /* ENCABEZADO */

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

        /* DATOS SUPERIORES */

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

        /* MARCA DE AGUA */

        .watermark-logo {
            position: absolute;
            top: 242pt;
            left: 160pt;
            width: 292pt;
            height: 348pt;
            opacity: .045;
            object-fit: contain;
        }

        /* BILLETES */

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

        /* MONEDAS */

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

        /* RESUMEN */

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

        /* OBSERVACIONES */

        .observations {
            position: absolute;
            top: 618pt;
            left: 46pt;
            width: 520pt;
        }

        .qualification {
            position: absolute;
            top: 658pt;
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
            height: 11pt;
            padding: 0 2pt;
            overflow: hidden;
            border-bottom: .65pt solid #333333;
            font-size: 6.4pt;
            line-height: 10.6pt;
            text-align: justify;
            white-space: normal;
        }

        /* FIRMAS */

        .signatures {
            top: 728pt;
            left: 27pt;
            width: 558pt;
            height: 56pt;
        }

        .signature {
            position: absolute;
            width: 171pt;
            text-align: center;
        }

        .signature-agent { left: 0; }
        .signature-promoter { left: 194pt; }
        .signature-chief { right: 0; }

        .electronic-signature {
            width: 100%;
            min-height: 27pt;
            padding: 1pt 3pt 0;
            text-align: center;
        }

        .electronic-signature-name {
            font-size: 5.7pt;
            font-weight: 700;
            line-height: 6.8pt;
            text-transform: uppercase;
        }

        .electronic-signature-meta {
            margin-top: 1pt;
            color: #555555;
            font-size: 4.3pt;
            line-height: 5.2pt;
        }

        .electronic-signature-code {
            overflow: hidden;
            color: #666666;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 3.8pt;
            line-height: 4.6pt;
            white-space: nowrap;
        }

        .signature-pending {
            padding-top: 7pt;
            color: #777777;
            font-size: 5pt;
            font-style: italic;
        }

        .signature-line {
            width: 171pt;
            border-bottom: .7pt solid #333333;
        }

        .signature-label {
            margin-top: 2pt;
            font-size: 5.2pt;
            font-weight: 700;
            line-height: 6.4pt;
        }
    </style>
</head>

<body>
<div class="page">

    <img class="logo-ecosaba" src="{{ $logoEcosaba }}" alt="ECOSABA">

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
                <td class="spacer-cell" style="width:141pt;"></td>

                <td class="label-cell">Fecha:</td>
                <td class="value-cell" style="width:76pt;">
                    {{ $fechaArqueo }}
                </td>

                <td class="label-cell">Hora inicio:</td>
                <td class="value-cell" style="width:63pt;">
                    {{ $horaInicio }}
                </td>

                <td class="label-cell">Hora finalización:</td>
                <td class="value-cell">
                    {{ $horaFin }}
                </td>
            </tr>
        </table>

        <table class="header-row">
            <tr>
                <td class="label-cell">Agente No.:</td>
                <td class="value-cell" style="width:58pt;">
                    {{ $arqueo->codigo_agente_historico }}
                </td>

                <td class="label-cell">Nombre Negocio:</td>
                <td class="value-cell" style="width:130pt;">
                    {{ $arqueo->nombre_negocio_historico }}
                </td>

                <td class="label-cell">Agente MICOOPE:</td>
                <td class="value-cell">
                    {{ $arqueo->nombre_negocio_historico }}
                </td>
            </tr>
        </table>

        <table class="header-row">
            <tr>
                <td class="label-cell">
                    Nombre del Propietario o Receptor Pagador:
                </td>

                <td class="value-cell">
                    {{ $arqueo->nombre_propietario_historico }}
                </td>
            </tr>
        </table>
    </div>

    <img class="watermark-logo" src="{{ $logoAgentes }}" alt="">

    <div class="absolute section-title bills-title">Billetes</div>
    <div class="absolute section-subtitle bills-subtitle">Denominación</div>
    <div class="absolute section-subtitle bill-qty-title">Cantidad</div>
    <div class="absolute section-subtitle bill-sub-title">Sub-total</div>
    <div class="absolute section-subtitle bill-total-title">Totales</div>

    @foreach ($denominacionesBilletes as $index => $denominacion)
        @php
            $clave = number_format((float) $denominacion, 2, '.', '');
            $detalle = $billetesMapa->get($clave);
            $cantidad = (int) data_get($detalle, 'cantidad', 0);
            $subtotal = (float) data_get($detalle, 'subtotal', 0);
            $top = 247 + ($index * 13);
        @endphp

        <div class="absolute money-row bill-row" style="top:{{ $top }}pt;">
            <span class="bill-denomination">
                Q. {{ number_format((float) $denominacion, 2) }}
            </span>

            <span class="bill-quantity">{{ $cantidad }}</span>

            <span class="bill-prefix">Q.</span>

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

    <div class="absolute section-title coins-title">Monedas</div>
    <div class="absolute section-subtitle coins-subtitle">Denominación</div>
    <div class="absolute section-subtitle coin-qty-title">Cantidad</div>
    <div class="absolute section-subtitle coin-sub-title">Sub-total</div>

    @foreach ($denominacionesMonedas as $index => $denominacion)
        @php
            $clave = number_format((float) $denominacion, 2, '.', '');
            $detalle = $monedasMapa->get($clave);
            $cantidad = (int) data_get($detalle, 'cantidad', 0);
            $subtotal = (float) data_get($detalle, 'subtotal', 0);
            $top = 426 + ($index * 13);
        @endphp

        <div class="absolute money-row coin-row" style="top:{{ $top }}pt;">
            <span class="coin-denomination">
                Q. {{ number_format((float) $denominacion, 2) }}
            </span>

            <span class="coin-quantity">{{ $cantidad }}</span>

            <span class="coin-prefix">Q.</span>

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
            <span class="summary-label">Saldo del Sistema</span>
            <span class="summary-prefix">Q.</span>
            <span class="summary-value">
                {{ number_format((float) $arqueo->saldo_sistema, 2) }}
            </span>
        </div>

        <div class="summary-row">
            <span class="summary-label">Total Arqueado</span>
            <span class="summary-prefix">Q.</span>
            <span class="summary-value">
                {{ number_format((float) $arqueo->total_arqueado, 2) }}
            </span>
        </div>

        <div class="summary-row summary-difference">
            <span class="summary-label">DIFERENCIA</span>
            <span class="summary-prefix">Q.</span>
            <span class="summary-value">
                {{ number_format((float) $arqueo->diferencia, 2) }}
            </span>
        </div>
    </div>

    <div class="observations">
        <div class="text-title">Observaciones:</div>

        @foreach ($lineasObservaciones as $linea)
            <div class="text-line">{{ $linea }}</div>
        @endforeach
    </div>

    <div class="qualification">
        <div class="text-title">Calificación:</div>

        @foreach ($lineasCalificacion as $linea)
            <div class="text-line">{{ $linea }}</div>
        @endforeach
    </div>

    <div class="absolute signatures">
        {{-- FIRMA DEL AGENTE / VALIDADOR --}}
        <div class="signature signature-agent">
            <div class="electronic-signature">
                @if ($firmaAgente)
                    <div class="electronic-signature-name">
                        {{ trim(
                            data_get($firmaAgente, 'nombres_historicos', '')
                            . ' '
                            . data_get($firmaAgente, 'apellidos_historicos', '')
                        ) }}
                    </div>

                    <div class="electronic-signature-meta">
                        Firmado electrónicamente:
                        {{ data_get($firmaAgente, 'fecha_firma')
                            ? \Carbon\Carbon::parse(
                                data_get($firmaAgente, 'fecha_firma')
                            )->format('d/m/Y H:i')
                            : ''
                        }}
                    </div>

                    <div class="electronic-signature-code">
                        Código:
                        {{ strtoupper(
                            substr(
                                (string) data_get(
                                    $firmaAgente,
                                    'firma_electronica',
                                    ''
                                ),
                                0,
                                18
                            )
                        ) }}
                    </div>
                @else
                    <div class="signature-pending">
                        Pendiente de firma del agente
                    </div>
                @endif
            </div>

            <div class="signature-line"></div>

            <div class="signature-label">
                Propietario y/o Receptor - Pagador<br>
                Agente MICOOPE
            </div>
        </div>

        {{-- FIRMA DEL PROMOTOR / REALIZADOR --}}
        <div class="signature signature-promoter">
            <div class="electronic-signature">
                @if ($firmaPromotor)
                    <div class="electronic-signature-name">
                        {{ trim(
                            data_get($firmaPromotor, 'nombres_historicos', '')
                            . ' '
                            . data_get($firmaPromotor, 'apellidos_historicos', '')
                        ) }}
                    </div>

                    <div class="electronic-signature-meta">
                        Firmado electrónicamente:
                        {{ data_get($firmaPromotor, 'fecha_firma')
                            ? \Carbon\Carbon::parse(
                                data_get($firmaPromotor, 'fecha_firma')
                            )->format('d/m/Y H:i')
                            : ''
                        }}
                    </div>

                    <div class="electronic-signature-code">
                        Código:
                        {{ strtoupper(
                            substr(
                                (string) data_get(
                                    $firmaPromotor,
                                    'firma_electronica',
                                    ''
                                ),
                                0,
                                18
                            )
                        ) }}
                    </div>
                @else
                    <div class="signature-pending">
                        Firma electrónica no registrada
                    </div>
                @endif
            </div>

            <div class="signature-line"></div>

            <div class="signature-label">
                Promotor Agentes MICOOPE
            </div>
        </div>

        {{-- FIRMA DEL JEFE / CERTIFICADOR --}}
        <div class="signature signature-chief">
            <div class="electronic-signature">
                @if ($firmaJefe)
                    <div class="electronic-signature-name">
                        {{ trim(
                            data_get($firmaJefe, 'nombres_historicos', '')
                            . ' '
                            . data_get($firmaJefe, 'apellidos_historicos', '')
                        ) }}
                    </div>

                    <div class="electronic-signature-meta">
                        Firmado electrónicamente:
                        {{ data_get($firmaJefe, 'fecha_firma')
                            ? \Carbon\Carbon::parse(
                                data_get($firmaJefe, 'fecha_firma')
                            )->format('d/m/Y H:i')
                            : ''
                        }}
                    </div>

                    <div class="electronic-signature-code">
                        Código:
                        {{ strtoupper(
                            substr(
                                (string) data_get(
                                    $firmaJefe,
                                    'firma_electronica',
                                    ''
                                ),
                                0,
                                18
                            )
                        ) }}
                    </div>
                @else
                    <div class="signature-pending">
                        Pendiente de certificación
                    </div>
                @endif
            </div>

            <div class="signature-line"></div>

            <div class="signature-label">
                Jefe de Agentes MICOOPE
            </div>
        </div>
    </div>

</div>
</body>
</html>
