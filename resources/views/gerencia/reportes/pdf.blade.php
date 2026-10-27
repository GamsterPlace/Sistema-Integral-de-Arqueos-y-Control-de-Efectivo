<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $tituloReporte }}</title>

    <style>
        @page {
            margin: 24px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #263d50;
            font-size: 8px;
        }

        h1 {
            margin: 0;
            color: #12375f;
            font-size: 18px;
        }

        h2 {
            margin: 4px 0 0;
            color: #315c83;
            font-size: 13px;
        }

        .meta {
            margin: 5px 0 12px;
            color: #718493;
            font-size: 8px;
        }

        .metrics {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .metrics td {
            padding: 8px;
            border: 1px solid #dce5eb;
            background: #f8fafb;
            vertical-align: top;
        }

        .metrics span {
            display: block;
            color: #718493;
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .metrics strong {
            display: block;
            margin-top: 3px;
            color: #12375f;
            font-size: 11px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        .data th {
            padding: 6px 4px;
            border: 1px solid #d5dfe6;
            background: #173e6b;
            color: #ffffff;
            font-size: 6px;
            text-transform: uppercase;
        }

        .data td {
            padding: 6px 4px;
            border: 1px solid #dce5eb;
            vertical-align: top;
        }
    </style>
</head>

<body>
    <h1>Sistema de Arqueos para Agentes MICOOPE</h1>

    <h2>{{ $tituloReporte }}</h2>

    <div class="meta">
        Período:
        {{ $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : 'Inicio' }}
        al
        {{ $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : 'Actualidad' }}
    </div>

    <table class="metrics">
        <tr>
            <td>
                <span>Total</span>
                <strong>{{ $metricas->total }}</strong>
            </td>

            <td>
                <span>Faltantes</span>
                <strong>{{ $metricas->faltantes }}</strong>
            </td>

            <td>
                <span>Sobrantes</span>
                <strong>{{ $metricas->sobrantes }}</strong>
            </td>

            <td>
                <span>Exactos</span>
                <strong>{{ $metricas->exactos }}</strong>
            </td>

            <td>
                <span>Extemporáneos</span>
                <strong>{{ $metricas->extemporaneos }}</strong>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <span>Monto Faltantes</span>

                <strong>
                    Q {{ number_format($metricas->monto_faltantes, 2) }}
                </strong>
            </td>

            <td colspan="2">
                <span>Monto Sobrantes</span>

                <strong>
                    Q {{ number_format($metricas->monto_sobrantes, 2) }}
                </strong>
            </td>

            <td>
                <span>Arqueos Promotor</span>
                <strong>{{ $metricas->promotores }}</strong>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                @foreach($columnas as $titulo)
                    <th>{{ $titulo }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse($resultados as $fila)
                <tr>
                    @foreach($columnas as $campo => $titulo)
                        @php
                            $valor = $fila->{$campo} ?? null;
                        @endphp

                        <td>
                            @if(in_array($campo, ['diferencia', 'monto'], true))
                                Q {{ number_format(
                                    abs((float) $valor),
                                    2
                                ) }}
                            @elseif($campo === 'fecha_arqueo' && $valor)
                                {{ \Carbon\Carbon::parse(
                                    $valor
                                )->format('d/m/Y') }}
                            @elseif($campo === 'tipo' && $valor)
                                {{ $valor === 'DIARIO_AGENTE'
                                    ? 'Agente'
                                    : ($valor === 'VISITA_PROMOTOR'
                                        ? 'Promotor'
                                        : str_replace('_', ' ', $valor)) }}
                            @else
                                {{ $valor ?? '—' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columnas) }}">
                        Sin resultados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
