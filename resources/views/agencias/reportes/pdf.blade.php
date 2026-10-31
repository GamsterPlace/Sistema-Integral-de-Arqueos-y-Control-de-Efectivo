
@php
    $titulo = $titulo ?? 'Reporte de Arqueos de Agencias';
    $columnas = $columnas ?? [];
    $metricas = $metricas ?? [];
    $filtros = $filtros ?? [];
    $resultados = $resultados ?? collect();

    $fechaGeneracion = now()->timezone('America/Guatemala');

    $cantidadRegistros = $resultados->count();

    $columnasMonetarias = [
        'total_corte',
        'total_arqueado',
        'diferencia',
        'monto_acumulado',
        'mayor_incidencia',
        'monto_faltantes',
        'monto_sobrantes',
    ];

    $nombresTipos = [
        'ORDINARIO' => 'Ordinario',
        'ENTREGA' => 'Entrega de Ventanilla',
        'SORPRESIVO' => 'Sorpresivo',
        'AUDITORIA' => 'Auditoría',
    ];

    $filtrosVisibles = [];

    if (!empty($filtros['desde'])) {
        $filtrosVisibles['Desde'] = \Illuminate\Support\Carbon::parse(
            $filtros['desde']
        )->format('d/m/Y');
    }

    if (!empty($filtros['hasta'])) {
        $filtrosVisibles['Hasta'] = \Illuminate\Support\Carbon::parse(
            $filtros['hasta']
        )->format('d/m/Y');
    }

    if (!empty($filtros['agencia_id'])) {
        $filtrosVisibles['Agencia ID'] = $filtros['agencia_id'];
    }

    if (!empty($filtros['receptor_id'])) {
        $filtrosVisibles['Receptor ID'] = $filtros['receptor_id'];
    }

    if (!empty($filtros['ventanilla_id'])) {
        $filtrosVisibles['Ventanilla ID'] = $filtros['ventanilla_id'];
    }

    if (!empty($filtros['turno'])) {
        $filtrosVisibles['Turno'] = $filtros['turno'];
    }

    if (!empty($filtros['tipo'])) {
        $filtrosVisibles['Tipo'] =
            $nombresTipos[$filtros['tipo']] ?? $filtros['tipo'];
    }

    if (!empty($filtros['estado'])) {
        $filtrosVisibles['Estado'] = str_replace(
            '_',
            ' ',
            $filtros['estado']
        );
    }

    $totalesMonetarios = [];

    /*
     * Los totales al pie se calculan únicamente para
     * las columnas monetarias del reporte.
     *
     * En reportes de detalle no se suman los registros
     * anulados ni aquellos sin certificación.
     *
     * Los reportes de agrupación ya incorporan sus
     * propias cifras calculadas desde el servicio.
     */
    $reporte = $filtros['reporte'] ?? 'resumen';

    $reportesAgrupados = [
        'ranking-faltantes',
        'ranking-sobrantes',
        'diferencias-agencia',
    ];

    foreach ($columnas as $campo => $encabezado) {
        if (!in_array($campo, $columnasMonetarias, true)) {
            continue;
        }

        $total = 0;

        foreach ($resultados as $registro) {
            if (!in_array($reporte, $reportesAgrupados, true)) {
                if (
                    data_get($registro, 'estado') === 'ANULADO'
                    || empty(data_get($registro, 'certificado_at'))
                ) {
                    continue;
                }
            }

            $total += (float) data_get($registro, $campo, 0);
        }

        $totalesMonetarios[$campo] = $total;
    }

    $mostrarTotales = !empty($totalesMonetarios)
        && $cantidadRegistros > 0;

    $numeroColumnas = max(count($columnas), 1);

    $tamanoFuenteTabla = $numeroColumnas >= 10
        ? '7px'
        : ($numeroColumnas >= 7 ? '8px' : '9px');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>{{ $titulo }}</title>

    <style>
        @page {
            size: letter landscape;
            margin: 24mm 12mm 19mm 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            line-height: 1.45;
            color: #26384e;
            margin: 0;
        }

        .document-header {
            position: fixed;
            top: -18mm;
            left: 0;
            right: 0;
            height: 15mm;
            border-bottom: 2px solid #123e70;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .brand-name {
            font-size: 15px;
            font-weight: bold;
            color: #123e70;
            letter-spacing: .4px;
        }

        .brand-description {
            font-size: 8px;
            color: #61748b;
            margin-top: 2px;
        }

        .header-right {
            text-align: right;
            font-size: 8px;
            color: #52677f;
        }

        .header-right strong {
            color: #173f70;
        }

        .document-footer {
            position: fixed;
            bottom: -13mm;
            left: 0;
            right: 0;
            height: 10mm;
            border-top: 1px solid #d9e3ed;
            padding-top: 5px;
            font-size: 7px;
            color: #7b8b9b;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            padding: 0;
        }

        .footer-right {
            text-align: right;
        }

        .page-number:after {
            content: counter(page);
        }

        .report-heading {
            margin-bottom: 13px;
        }

        .report-eyebrow {
            color: #2a75b8;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .report-title {
            color: #12345a;
            font-size: 19px;
            font-weight: bold;
            margin: 0 0 4px;
        }

        .report-description {
            color: #75869a;
            font-size: 8px;
        }

        .section-label {
            color: #294a6e;
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 7px;
        }

        .filters-box {
            border: 1px solid #dce5ee;
            background: #f6f9fc;
            padding: 10px 12px;
            border-radius: 5px;
            margin-bottom: 13px;
        }

        .filters-table {
            width: 100%;
            border-collapse: collapse;
        }

        .filters-table td {
            padding: 4px 10px 4px 0;
            vertical-align: top;
            font-size: 8px;
        }

        .filter-name {
            color: #73849a;
            font-weight: bold;
        }

        .filter-value {
            color: #29445f;
            font-weight: bold;
        }

        .metrics-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
            margin: 0 -5px 13px;
            table-layout: fixed;
        }

        .metric-box {
            border: 1px solid #dce5ee;
            background: #f8fbfe;
            border-radius: 5px;
            padding: 9px 10px;
            vertical-align: top;
        }

        .metric-label {
            color: #6b7d91;
            font-size: 7px;
            margin-bottom: 6px;
        }

        .metric-value {
            color: #153b67;
            font-size: 13px;
            font-weight: bold;
            word-wrap: break-word;
        }

        .results-heading {
            margin: 13px 0 7px;
        }

        .results-count {
            float: right;
            font-size: 8px;
            color: #6c8096;
            font-weight: normal;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            font-size: {{ $tamanoFuenteTabla }};
        }

        .report-table thead {
            display: table-header-group;
        }

        .report-table th {
            background: #123e70;
            color: #ffffff;
            text-align: left;
            padding: 8px 6px;
            font-weight: bold;
            border: 1px solid #123e70;
        }

        .report-table td {
            border-bottom: 1px solid #e3eaf1;
            padding: 7px 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .report-table tbody tr:nth-child(even) {
            background: #f7f9fc;
        }

        .report-table tr {
            page-break-inside: avoid;
        }

        .report-table .money {
            white-space: nowrap;
            text-align: right;
        }

        .report-table .negative {
            color: #b73535;
            font-weight: bold;
        }

        .report-table .positive {
            color: #177347;
            font-weight: bold;
        }

        .report-table .neutral {
            color: #52677c;
        }

        .report-table tfoot td {
            background: #eaf1f8;
            color: #163e69;
            font-weight: bold;
            border-top: 2px solid #123e70;
            padding: 9px 6px;
        }

        .empty-message {
            border: 1px solid #dce5ee;
            text-align: center;
            padding: 30px;
            color: #8291a2;
            font-size: 10px;
        }

        .note-box {
            border-left: 3px solid #367cb7;
            background: #f4f8fc;
            padding: 9px 12px;
            margin-top: 14px;
            color: #65798d;
            font-size: 8px;
        }

        .clearfix {
            clear: both;
        }
    </style>
</head>

<body>

    <div class="document-header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand-name">
                        ECOSABA
                    </div>

                    <div class="brand-description">
                        Sistema Integral de Arqueos y Control de Efectivo
                    </div>
                </td>

                <td class="header-right">
                    <strong>REPORTE INSTITUCIONAL</strong>

                    <div>
                        Generado:
                        {{ $fechaGeneracion->format('d/m/Y') }}
                        a las
                        {{ $fechaGeneracion->format('H:i') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="document-footer">
        <table class="footer-table">
            <tr>
                <td>
                    Sistema Integral de Arqueos y Control de Efectivo
                </td>

                <td class="footer-right">
                    Documento generado electrónicamente
                    |
                    Página <span class="page-number"></span>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-heading">
        <div class="report-eyebrow">
            CENTRO DE REPORTES / AGENCIAS
        </div>

        <h1 class="report-title">
            {{ $titulo }}
        </h1>

        <div class="report-description">
            Información obtenida de los registros de arqueos
            correspondientes a los criterios de consulta seleccionados.
        </div>
    </div>

    <div class="filters-box">
        <div class="section-label">
            Criterios de consulta
        </div>

        @if(count($filtrosVisibles) > 0)
            <table class="filters-table">
                <tr>
                    @foreach($filtrosVisibles as $nombre => $valor)
                        <td>
                            <span class="filter-name">
                                {{ $nombre }}:
                            </span>

                            <span class="filter-value">
                                {{ $valor }}
                            </span>
                        </td>

                        @if($loop->iteration % 4 === 0 && !$loop->last)
                            </tr><tr>
                        @endif
                    @endforeach
                </tr>
            </table>
        @else
            <div style="font-size:8px;color:#61758a;">
                Consulta general, sin filtros adicionales.
            </div>
        @endif
    </div>

    @if(count($metricas) > 0)
        <div class="section-label">
            Indicadores del reporte
        </div>

        @foreach(array_chunk($metricas, 4, true) as $grupoMetricas)
            <table class="metrics-table">
                <tr>
                    @foreach($grupoMetricas as $nombre => $valor)
                        <td class="metric-box">
                            <div class="metric-label">
                                {{ $nombre }}
                            </div>

                            <div class="metric-value">
                                {{ is_numeric($valor)
                                    ? number_format((float) $valor, 0)
                                    : $valor }}
                            </div>
                        </td>
                    @endforeach

                    @for($i = count($grupoMetricas); $i < 4; $i++)
                        <td style="width:25%;"></td>
                    @endfor
                </tr>
            </table>
        @endforeach
    @endif

    <div class="results-heading">
        <span class="results-count">
            {{ number_format($cantidadRegistros) }}
            {{ $cantidadRegistros === 1 ? 'registro' : 'registros' }}
        </span>

        <div class="section-label">
            Detalle de resultados
        </div>

        <div class="clearfix"></div>
    </div>

    @if($cantidadRegistros > 0)
        <table class="report-table">
            <thead>
                <tr>
                    @foreach($columnas as $campo => $encabezado)
                        <th class="{{ in_array($campo, $columnasMonetarias, true) ? 'money' : '' }}">
                            {{ $encabezado }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($resultados as $indice => $registro)
                    <tr>
                        @foreach($columnas as $campo => $encabezado)
                            @php
                                $valor = data_get($registro, $campo);

                                if ($campo === 'posicion') {
                                    $valor = $indice + 1;
                                }

                                if ($campo === 'tipo') {
                                    $valor = $nombresTipos[$valor] ?? $valor;
                                }

                                if (
                                    $campo === 'fecha_operativa'
                                    && !empty($valor)
                                ) {
                                    try {
                                        $valor = \Illuminate\Support\Carbon::parse(
                                            $valor
                                        )->format('d/m/Y');
                                    } catch (\Throwable $e) {
                                        // Conservar valor original.
                                    }
                                }

                                $esMonetario = in_array(
                                    $campo,
                                    $columnasMonetarias,
                                    true
                                );

                                if (
                                    $esMonetario
                                    && $valor !== null
                                    && $valor !== ''
                                ) {
                                    $valor = 'Q ' . number_format(
                                        (float) $valor,
                                        2,
                                        '.',
                                        ','
                                    );
                                }

                                $diferencia = (float) data_get(
                                    $registro,
                                    'diferencia',
                                    0
                                );
                            @endphp

                            <td class="{{ $esMonetario ? 'money' : '' }}">
                                @if($campo === 'diferencia')
                                    <span class="{{
                                        $diferencia < 0
                                            ? 'negative'
                                            : ($diferencia > 0
                                                ? 'positive'
                                                : 'neutral')
                                    }}">
                                        {{ $valor }}
                                    </span>
                                @elseif($campo === 'estado' || $campo === 'cumplimiento')
                                    {{ str_replace('_', ' ', (string) $valor) }}
                                @else
                                    {{ $valor ?? '—' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>

            @if($mostrarTotales)
                <tfoot>
                    <tr>
                        @foreach($columnas as $campo => $encabezado)
                            <td class="{{ in_array($campo, $columnasMonetarias, true) ? 'money' : '' }}">
                                @if($loop->first)
                                    TOTALES
                                @elseif(isset($totalesMonetarios[$campo]))
                                    Q {{ number_format(
                                        $totalesMonetarios[$campo],
                                        2,
                                        '.',
                                        ','
                                    ) }}
                                @else
                                    &nbsp;
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    @else
        <div class="empty-message">
            No se encontraron registros para los filtros seleccionados.
        </div>
    @endif

    <div class="note-box">
        <strong>Nota institucional:</strong>
        Este reporte refleja la información registrada en el sistema
        al momento de su generación. Los arqueos anulados se conservan
        para fines de trazabilidad y no se consideran dentro de los
        totales monetarios de arqueos vigentes certificados.
    </div>

</body>
</html>
