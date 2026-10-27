<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        Arqueo {{ $arqueo->numero_arqueo }}
    </title>

    @php
        $billetesMapa = collect($billetes)->keyBy(
            fn ($detalle) => number_format(
                (float) data_get(
                    $detalle,
                    'denominacion'
                ),
                2,
                '.',
                ''
            )
        );

        $monedasMapa = collect($monedas)->keyBy(
            fn ($detalle) => number_format(
                (float) data_get(
                    $detalle,
                    'denominacion'
                ),
                2,
                '.',
                ''
            )
        );

        $denominacionesBilletes = [
            200,
            100,
            50,
            20,
            10,
            5,
            1,
        ];

        $denominacionesMonedas = [
            1,
            0.50,
            0.25,
            0.10,
            0.05,
        ];

        $fechaArqueo = $arqueo->fecha_arqueo
            ? \Carbon\Carbon::parse(
                $arqueo->fecha_arqueo
            )->format('d/m/Y')
            : '';

        $horaInicio = $arqueo->hora_inicio
            ? \Carbon\Carbon::parse(
                $arqueo->hora_inicio
            )->format('H:i')
            : '';

        $horaFin = $arqueo->hora_fin
            ? \Carbon\Carbon::parse(
                $arqueo->hora_fin
            )->format('H:i')
            : '';

        $numeroArqueo = preg_replace(
            '/[^0-9]/',
            '',
            (string) $arqueo->numero_arqueo
        );

        $firmaRealizador =
            \Illuminate\Support\Facades\DB::table(
                'firmas_arqueos'
            )
            ->where(
                'arqueo_id',
                $arqueo->id
            )
            ->where(
                'tipo_firma',
                'REALIZADOR'
            )
            ->where(
                'valida',
                1
            )
            ->first();

        $firmaCertificador =
            \Illuminate\Support\Facades\DB::table(
                'firmas_arqueos'
            )
            ->where(
                'arqueo_id',
                $arqueo->id
            )
            ->where(
                'tipo_firma',
                'CERTIFICADOR'
            )
            ->where(
                'valida',
                1
            )
            ->first();

        $rutaLogoEcosaba =
            public_path(
                'images/logos/ecosaba.png'
            );

        $rutaLogoAgentesMicoope =
            public_path(
                'images/logos/agentes-micoope.png'
            );

        if (! file_exists($rutaLogoEcosaba)) {
            throw new \RuntimeException(
                'No se encontró el logo de ECOSABA en: '
                . $rutaLogoEcosaba
            );
        }

        if (! file_exists(
            $rutaLogoAgentesMicoope
        )) {
            throw new \RuntimeException(
                'No se encontró el logo de Agentes MICOOPE en: '
                . $rutaLogoAgentesMicoope
            );
        }

        $logoEcosaba =
            'data:image/png;base64,'
            . base64_encode(
                file_get_contents(
                    $rutaLogoEcosaba
                )
            );

        $logoAgentesMicoope =
            'data:image/png;base64,'
            . base64_encode(
                file_get_contents(
                    $rutaLogoAgentesMicoope
                )
            );
    @endphp

    <style>
        :root {
            --azul-institucional: #0b315f;
            --azul-principal: #164c96;
            --verde-institucional: #4d693f;
            --amarillo-institucional: #d8b427;
            --rojo-folio: #a31d17;
            --texto: #111111;
            --linea: #344553;
            --texto-suave: #5d6a74;
        }

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
            color: var(--texto);
            background: #fff;
            font-family:
                DejaVu Sans,
                sans-serif;
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

        .field-line {
            display: inline-block;
            height: 13pt;
            padding: 0 2pt 1pt;
            overflow: hidden;
            border-bottom: .65pt solid var(--linea);
            vertical-align: bottom;
            white-space: nowrap;
        }

        .logo-ecosaba {
            position: absolute;
            top: 20pt;
            left: 32pt;
            z-index: 20;
            display: block;
            width: 150pt;
            height: 54pt;
            object-fit: contain;
        }

        .watermark-logo {
            position: absolute;
            top: 245pt;
            left: 154pt;
            z-index: 0;
            display: block;
            width: 304pt;
            height: 340pt;
            opacity: .075;
            object-fit: contain;
        }

        .header-rule {
            top: 45pt;
            left: 214pt;
            width: 338pt;
            height: 1.5pt;
        }

        .header-rule .green {
            float: left;
            width: 225pt;
            height: 1.5pt;
            background: var(--verde-institucional);
        }

        .header-rule .yellow {
            float: left;
            width: 113pt;
            height: 1.5pt;
            background: var(--amarillo-institucional);
        }

        .header-number {
            top: 50pt;
            left: 328pt;
            width: 232pt;
            height: 18pt;
            overflow: hidden;
            color: var(--rojo-folio);
            font-family:
                DejaVu Serif,
                serif;
            font-size: 10.5pt;
            font-weight: 700;
            line-height: 15pt;
            letter-spacing: .25pt;
            text-align: right;
            white-space: nowrap;
        }

        .main-title {
            top: 86pt;
            left: 194pt;
            width: 290pt;
            font-family:
                DejaVu Serif,
                serif;
            color: var(--azul-institucional);
            font-size: 14pt;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .main-subtitle {
            top: 105pt;
            left: 224pt;
            width: 230pt;
            font-family:
                DejaVu Serif,
                serif;
            color: var(--azul-institucional);
            font-size: 12pt;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .general-field {
            font-size: 7.7pt;
            font-weight: 700;
            white-space: nowrap;
        }

        .business {
            top: 145pt;
            left: 38pt;
        }

        .business .field-line {
            width: 190pt;
        }

        .address {
            top: 145pt;
            left: 338pt;
        }

        .address .field-line {
            width: 196pt;
        }

        .owner {
            top: 169pt;
            left: 38pt;
        }

        .owner .field-line {
            width: 362pt;
        }

        .agent-code {
            top: 194pt;
            left: 38pt;
        }

        .agent-code .field-line {
            width: 68pt;
        }

        .date {
            top: 194pt;
            left: 164pt;
        }

        .date .field-line {
            width: 84pt;
        }

        .start {
            top: 194pt;
            left: 360pt;
        }

        .start .field-line {
            width: 60pt;
        }

        .end {
            top: 194pt;
            left: 468pt;
        }

        .end .field-line {
            width: 66pt;
        }

        .section-title {
            color: var(--azul-institucional);
            font-family:
                DejaVu Serif,
                serif;
            font-size: 10.5pt;
            font-weight: 700;
        }

        .bills-title {
            top: 247pt;
            left: 38pt;
        }

        .section-subtitle {
            font-size: 7.3pt;
            font-weight: 700;
        }

        .bills-subtitle {
            top: 265pt;
            left: 38pt;
        }

        .bill-qty-title {
            top: 294pt;
            left: 123pt;
            width: 73pt;
            text-align: center;
        }

        .bill-sub-title {
            top: 294pt;
            left: 250pt;
            width: 76pt;
            text-align: center;
        }

        .bill-total-title {
            top: 294pt;
            left: 391pt;
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
            left: 74pt;
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
            border-bottom: .65pt solid var(--linea);
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
            border-bottom: .65pt solid var(--linea);
            text-align: right;
        }

        .bill-total {
            top: 403pt;
            left: 358pt;
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
            border-bottom: .65pt solid var(--linea);
            text-align: right;
        }

        .coins-title {
            top: 437pt;
            left: 38pt;
        }

        .coins-subtitle {
            top: 455pt;
            left: 38pt;
        }

        .coin-qty-title {
            top: 484pt;
            left: 58pt;
            width: 75pt;
            text-align: center;
        }

        .coin-sub-title {
            top: 484pt;
            left: 196pt;
            width: 75pt;
            text-align: center;
        }

        .coin-row {
            left: 77pt;
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
            border-bottom: .65pt solid var(--linea);
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
            border-bottom: .65pt solid var(--linea);
            text-align: right;
        }

        .coin-total {
            top: 550pt;
            left: 358pt;
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
            border-bottom: .65pt solid var(--linea);
            text-align: right;
        }

        .total-arqueado-middle {
            top: 582pt;
            left: 244pt;
            width: 225pt;
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
            border-bottom: .7pt solid var(--linea);
            text-align: right;
        }

        .summary {
            top: 617pt;
            left: 387pt;
            width: 173pt;
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
            border-bottom: .7pt solid var(--linea);
            text-align: right;
        }

        .summary-difference {
            margin-top: 8pt;
            color: var(--azul-institucional);
            font-size: 9pt;
            font-weight: 700;
        }

        .certification {
            position: absolute;
            top: 675pt;
            left: 38pt;
            width: 522pt;
            z-index: 2;
            color: #111;
        }

        .certification-title {
            margin: 0 0 2pt;
            color: var(--azul-institucional);
            font-size: 7.2pt;
            font-weight: 700;
            line-height: 9pt;
        }

        .certification-row {
            width: 100%;
            height: 11pt;
            overflow: hidden;
            padding: 0 2pt;
            border-bottom: .65pt solid var(--linea);
            font-size: 5.7pt;
            line-height: 9.5pt;
            text-align: left;
            white-space: nowrap;
        }

        .signatures {
            top: 732pt;
            left: 38pt;
            width: 522pt;
            height: 48pt;
        }

        .signature {
            position: absolute;
            width: 220pt;
            text-align: center;
        }

        .signature-agent {
            left: 0;
        }

        .signature-promoter {
            right: 0;
        }

        .signature-line {
            width: 220pt;
            height: 2pt;
            margin: 0 auto;
            border-bottom: .7pt solid var(--linea);
        }

        .signature-label {
            margin-top: 2pt;
            font-size: 5.8pt;
            font-weight: 700;
            line-height: 7pt;
        }

        .electronic-signature {
            width: 100%;
            min-height: 24pt;
            padding: 1pt 4pt 0;
            text-align: center;
        }

        .electronic-signature-name {
            font-size: 6.4pt;
            font-weight: 700;
            line-height: 8pt;
            text-transform: uppercase;
        }

        .electronic-signature-meta {
            margin-top: 1pt;
            color: var(--texto-suave);
            font-size: 4.8pt;
            line-height: 6pt;
        }

        .electronic-signature-code {
            font-family:
                DejaVu Sans Mono,
                monospace;
            font-size: 4.3pt;
            letter-spacing: .15pt;
        }

        .signature-pending {
            padding-top: 4pt;
            color: #7b8790;
            font-size: 5.3pt;
            font-style: italic;
            text-align: center;
        }

        .document-footer {
            position: absolute;
            left: 38pt;
            bottom: 6pt;
            width: 522pt;
            color: #8a969f;
            font-size: 4.6pt;
            letter-spacing: .15pt;
            text-align: center;
        }

    </style>
</head>

<body>
<div class="page">

    <img
        class="logo-ecosaba"
        src="{{ $logoEcosaba }}"
        alt="ECOSABA"
    >

    <div class="absolute header-rule">
        <div class="green"></div>
        <div class="yellow"></div>
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

    <img
        class="watermark-logo"
        src="{{ $logoAgentesMicoope }}"
        alt=""
    >

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

    @foreach (
        $denominacionesBilletes
        as $index => $denominacion
    )
        @php
            $clave = number_format(
                (float) $denominacion,
                2,
                '.',
                ''
            );

            $detalle =
                $billetesMapa->get(
                    $clave
                );

            $cantidad =
                (int) data_get(
                    $detalle,
                    'cantidad',
                    0
                );

            $subtotal =
                (float) data_get(
                    $detalle,
                    'subtotal',
                    0
                );

            $top =
                316
                + ($index * 13);
        @endphp

        <div
            class="absolute money-row bill-row"
            style="top: {{ $top }}pt;"
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

            <span class="money-prefix">
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
                (float) $arqueo->total_billetes,
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

    @foreach (
        $denominacionesMonedas
        as $index => $denominacion
    )
        @php
            $clave = number_format(
                (float) $denominacion,
                2,
                '.',
                ''
            );

            $detalle =
                $monedasMapa->get(
                    $clave
                );

            $cantidad =
                (int) data_get(
                    $detalle,
                    'cantidad',
                    0
                );

            $subtotal =
                (float) data_get(
                    $detalle,
                    'subtotal',
                    0
                );

            $top =
                503
                + ($index * 13);
        @endphp

        <div
            class="absolute money-row coin-row"
            style="top: {{ $top }}pt;"
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
                (float) $arqueo->total_monedas,
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
                    (float) $arqueo->diferencia,
                    2
                ) }}
            </span>
        </div>
    </div>

    @php
        $textoCertificacion =
            wordwrap(
                trim(
                    (string)
                    $arqueo->certificacion
                ),
                118,
                "\n",
                false
            );

        $lineasCertificacion =
            array_slice(
                explode(
                    "\n",
                    $textoCertificacion
                ),
                0,
                3
            );

        while (
            count(
                $lineasCertificacion
            ) < 3
        ) {
            $lineasCertificacion[] = '';
        }
    @endphp

    <div class="certification">
        <div class="certification-title">
            Certificación:
        </div>

        @foreach (
            $lineasCertificacion
            as $linea
        )
            <div class="certification-row">
                {{ $linea }}
            </div>
        @endforeach
    </div>

    <div class="absolute signatures">
        <div class="signature signature-agent">
            <div class="electronic-signature">
                @if ($firmaRealizador)
                    <div class="electronic-signature-name">
                        {{ trim(
                            $firmaRealizador->nombres_historicos
                            . ' '
                            . $firmaRealizador->apellidos_historicos
                        ) }}
                    </div>

                    <div class="electronic-signature-meta">
                        Firmado electrónicamente el
                        {{ \Carbon\Carbon::parse(
                            $firmaRealizador->fecha_firma
                        )->format('d/m/Y H:i') }}
                    </div>

                    <div class="electronic-signature-code">
                        Código:
                        {{ strtoupper(
                            substr(
                                $firmaRealizador->firma_electronica,
                                0,
                                20
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
                Elaborado Por:<br>
                Propietario o Receptor Pagador
            </div>
        </div>

        <div class="signature signature-promoter">
            <div class="electronic-signature">
                @if ($firmaCertificador)
                    <div class="electronic-signature-name">
                        {{ trim(
                            $firmaCertificador->nombres_historicos
                            . ' '
                            . $firmaCertificador->apellidos_historicos
                        ) }}
                    </div>

                    <div class="electronic-signature-meta">
                        Firmado electrónicamente el
                        {{ \Carbon\Carbon::parse(
                            $firmaCertificador->fecha_firma
                        )->format('d/m/Y H:i') }}
                    </div>

                    <div class="electronic-signature-code">
                        Código:
                        {{ strtoupper(
                            substr(
                                $firmaCertificador->firma_electronica,
                                0,
                                20
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
                Revisado Por:<br>
                Promotor Agentes MICOOPE
            </div>
        </div>
    </div>


    <div class="document-footer">
        Sistema de Arqueos para Agentes MICOOPE · Documento generado electrónicamente
    </div>

</div>
</body>
</html>
