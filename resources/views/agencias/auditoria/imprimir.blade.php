
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Arqueo de Auditoría - {{ $registro->numero_arqueo }}</title>

    <style>
        @page {
            size: letter portrait;
            margin: 12mm 14mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            color: #243447;
            margin: 0;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #234e78;
            padding-bottom: 12px;
        }

        .header td {
            vertical-align: middle;
        }

        .brand {
            font-size: 18px;
            font-weight: bold;
            color: #174b80;
        }

        .title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
        }

        .number {
            text-align: right;
            font-size: 9px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
        }

        .meta td {
            padding: 6px;
            border-bottom: 1px solid #dce4eb;
        }

        .section {
            font-weight: bold;
            color: #174b80;
            font-size: 11px;
            margin: 14px 0 5px;
        }

        .tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .tbl th,
        .tbl td {
            padding: 5px;
            border-bottom: 1px solid #dce4eb;
            text-align: left;
        }

        .tbl th {
            background: #f1f5f9;
        }

        .right {
            text-align: right !important;
        }

        .totals {
            width: 55%;
            margin-left: 45%;
            margin-top: 15px;
            border-collapse: collapse;
        }

        .totals td {
            padding: 6px;
            border-bottom: 1px solid #cbd5df;
        }

        .note {
            margin-top: 14px;
            min-height: 65px;
            white-space: pre-wrap;
            overflow-wrap: break-word;
        }

        .signatures {
            width: 100%;
            margin-top: 38px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 10px;
        }

        .line {
            border-top: 1px solid #6b7d8f;
            padding-top: 7px;
        }

        .small {
            font-size: 7px;
            color: #607589;
        }

        .foot {
            font-size: 7px;
            margin-top: 16px;
            color: #607589;
        }
    </style>
</head>

<body>
    @php
        $moneda = fn ($v) => 'Q ' . number_format((float) $v, 2, '.', ',');
    @endphp

    <table class="header">
        <tr>
            <td style="width: 23%">
                <div class="brand">ECOSABA</div>
                <span class="small">Control institucional de efectivo</span>
            </td>

            <td style="width: 54%" class="title">
                ARQUEO Y CORTE DE CAJA
                <br>
                <span style="font-size: 10px">
                    ARQUEO DE AUDITORÍA
                </span>
            </td>

            <td style="width: 23%" class="number">
                N.º {{ $registro->numero_arqueo }}
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td>
                <b>Agencia:</b>
                {{ $registro->agencia_nombre_historico }}
            </td>
            <td>
                <b>Ventanilla:</b>
                {{ $registro->ventanilla_codigo_historico }}
            </td>
        </tr>

        <tr>
            <td>
                <b>Receptor:</b>
                {{ $registro->receptor_nombre_historico }}
            </td>
            <td>
                <b>Turno:</b>
                {{ $registro->turno_nombre_historico }}
            </td>
        </tr>

        <tr>
            <td>
                <b>Fecha:</b>
                {{ $registro->fecha_operativa?->format('d/m/Y') }}
            </td>
            <td>
                <b>H.I.:</b>
                {{ $registro->inicio_at?->format('H:i') ?? '—' }}

                ·

                <b>H.F.:</b>
                {{ $registro->finalizado_at?->format('H:i') ?? '—' }}
            </td>
        </tr>
    </table>

    @foreach([
        ['Billetes', 'BILLETE', $billetes],
        ['Monedas', 'MONEDA', $monedas]
    ] as [$titulo, $tipo, $valores])

        <div class="section">{{ $titulo }}</div>

        <table class="tbl">
            <thead>
                <tr>
                    <th>Denominación</th>
                    <th>Cantidad</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @foreach($valores as $v)
                    @php
                        $detalle = $registro->detalles->first(
                            fn ($d) =>
                                $d->tipo === $tipo
                                && (int) round((float) $d->denominacion * 100)
                                === (int) round((float) $v * 100)
                        );
                    @endphp

                    <tr>
                        <td>{{ $moneda($v) }}</td>
                        <td>{{ $detalle?->cantidad ?? 0 }}</td>
                        <td class="right">
                            {{ $moneda($detalle?->subtotal ?? 0) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="section">Comprobación</div>

    <table class="totals">
        <tr>
            <td>Total billetes</td>
            <td class="right">
                {{ $moneda($registro->total_billetes) }}
            </td>
        </tr>

        <tr>
            <td>Total monedas</td>
            <td class="right">
                {{ $moneda($registro->total_monedas) }}
            </td>
        </tr>

        <tr>
            <td>Total otros valores</td>
            <td class="right">
                {{ $moneda($registro->total_otros_valores) }}
            </td>
        </tr>

        <tr>
            <td>
                <b>Total arqueado</b>
            </td>
            <td class="right">
                <b>{{ $moneda($registro->total_arqueado) }}</b>
            </td>
        </tr>

        <tr>
            <td>Total corte</td>
            <td class="right">
                {{ $moneda($registro->total_corte) }}
            </td>
        </tr>

        <tr>
            <td><b>Diferencia</b></td>
            <td class="right">
                <b>{{ $moneda($registro->diferencia) }}</b>
            </td>
        </tr>
    </table>

    <div class="note">
        <b>Justificación:</b>
        {{ $registro->justificacion_diferencia ?: 'No aplica' }}

        <br>

        <b>Observaciones:</b>
        {{ $registro->observaciones ?: 'Sin observaciones' }}
    </div>

    <table class="signatures">
        <tr>
            @foreach([
                'AUDITOR' => 'Auditoría',
                'RECEPTOR' => 'Receptor - Pagador',
                'CERTIFICADOR' => 'Vo. Bo. Jefe / Subjefe de Caja'
            ] as $tipo => $etiqueta)

                @php
                    $firma = $registro->firmas->firstWhere(
                        'tipo_firma',
                        $tipo
                    );
                @endphp

                <td>
                    <div class="line">
                        {{ $etiqueta }}
                    </div>

                    <div class="small">
                        {{ $firma?->nombres_historicos }}
                        {{ $firma?->apellidos_historicos }}
                    </div>

                    <div class="small">
                        @if($firma)
                            {{ ($verificaciones[$tipo] ?? false)
                                ? 'Firma electrónica verificada'
                                : 'Firma no verificada' }}
                        @else
                            Pendiente de firma
                        @endif
                    </div>

                    <div class="small">
                        {{ $firma?->fecha_firma?->format('d/m/Y H:i') }}
                    </div>
                </td>
            @endforeach
        </tr>
    </table>

    <div class="foot">
        Documento electrónico ·
        Estado: {{ str_replace('_', ' ', $registro->estado) }} ·
        N.º {{ $registro->numero_arqueo }}.

        Las firmas se verifican contra los datos registrados.
    </div>
</body>
</html>
