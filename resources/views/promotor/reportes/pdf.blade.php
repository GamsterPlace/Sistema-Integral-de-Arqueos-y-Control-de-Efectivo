<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { margin: 24px; }

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

        .sub {
            margin: 4px 0 8px;
            color: #718493;
        }

        .scope {
            margin: 0 0 12px;
            padding: 7px 9px;
            border: 1px solid #dce5eb;
            background: #f8fafb;
            color: #536a7d;
        }

        .filters {
            margin-bottom: 12px;
            color: #718493;
            font-size: 7px;
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
        }

        .metrics span {
            display: block;
            color: #718493;
            font-size: 6px;
            text-transform: uppercase;
        }

        .metrics strong {
            display: block;
            margin-top: 3px;
            color: #12375f;
            font-size: 11px;
        }

        table.results {
            width: 100%;
            border-collapse: collapse;
        }

        .results th {
            padding: 6px 4px;
            border: 1px solid #d5dfe6;
            background: #173e6b;
            color: #ffffff;
            font-size: 6px;
            text-transform: uppercase;
        }

        .results td {
            padding: 6px 4px;
            border: 1px solid #dce5eb;
            vertical-align: top;
        }

        .negative {
            color: #a93b35;
            font-weight: bold;
        }

        .positive {
            color: #247048;
            font-weight: bold;
        }

        .zero {
            color: #607586;
            font-weight: bold;
        }

        .empty {
            padding: 18px;
            text-align: center;
            color: #718493;
        }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>

    <div class="sub">
        Sistema de Arqueos para Agentes MICOOPE ·
        Reportería del Promotor ·
        Generado {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="scope">
        Este reporte contiene únicamente información correspondiente a los
        agentes de las rutas vigentes asignadas al Promotor autenticado.
    </div>

    <div class="filters">
        Período:
        {{ !empty($filtros['desde'])
            ? \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y')
            : 'Sin fecha inicial' }}
        —
        {{ !empty($filtros['hasta'])
            ? \Carbon\Carbon::parse($filtros['hasta'])->format('d/m/Y')
            : 'Sin fecha final' }}

        @if(!empty($filtros['tipo']))
            · Tipo:
            {{ $filtros['tipo'] === 'DIARIO_AGENTE'
                ? 'Arqueo del Agente'
                : ($filtros['tipo'] === 'VISITA_PROMOTOR'
                    ? 'Arqueo del Promotor'
                    : str_replace('_', ' ', $filtros['tipo'])) }}
        @endif
    </div>

    @if(count($metricas))
        <table class="metrics">
            <tr>
                @foreach($metricas as $etiqueta => $valor)
                    <td>
                        <span>{{ $etiqueta }}</span>
                        <strong>{{ $valor }}</strong>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <table class="results">
        <thead>
            <tr>
                @foreach($columnas as $tituloColumna)
                    <th>{{ $tituloColumna }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse($resultados as $indice => $fila)
                <tr>
                    @foreach($columnas as $columna => $tituloColumna)
                        <td>
                            @switch($columna)
                                @case('posicion')
                                    {{ $indice + 1 }}
                                @break

                                @case('agente')
                                    <strong>
                                        {{ $fila->codigo_agente }}
                                        — {{ $fila->nombre_negocio }}
                                    </strong>
                                @break

                                @case('ruta')
                                    {{ $fila->ruta_codigo }}
                                    — {{ $fila->ruta_nombre }}
                                @break

                                @case('responsable')
                                    @php
                                        $responsable = trim(
                                            ($fila->responsable_nombres ?? '')
                                            . ' '
                                            . ($fila->responsable_apellidos ?? '')
                                        );
                                    @endphp

                                    {{ $responsable !== ''
                                        ? $responsable
                                        : ($fila->responsable_usuario ?? '—') }}
                                @break

                                @case('fecha_arqueo')
                                    {{ \Carbon\Carbon::parse(
                                        $fila->fecha_arqueo
                                    )->format('d/m/Y') }}
                                @break

                                @case('tipo')
                                    {{ $fila->tipo === 'DIARIO_AGENTE'
                                        ? 'Agente'
                                        : ($fila->tipo === 'VISITA_PROMOTOR'
                                            ? 'Promotor'
                                            : str_replace('_', ' ', $fila->tipo)) }}
                                @break

                                @case('estado')
                                    {{ str_replace('_', ' ', $fila->estado) }}
                                @break

                                @case('diferencia')
                                    @php
                                        $diferencia = (float) $fila->diferencia;
                                        $clase = $diferencia < 0
                                            ? 'negative'
                                            : ($diferencia > 0
                                                ? 'positive'
                                                : 'zero');
                                    @endphp

                                    <span class="{{ $clase }}">
                                        Q {{ number_format(
                                            abs($diferencia),
                                            2
                                        ) }}

                                        @if($diferencia < 0)
                                            Faltante
                                        @elseif($diferencia > 0)
                                            Sobrante
                                        @else
                                            Exacto
                                        @endif
                                    </span>
                                @break

                                @case('saldo_sistema')
                                @case('total_arqueado')
                                @case('monto_acumulado')
                                @case('mayor_incidencia')
                                    Q {{ number_format(
                                        (float) $fila->{$columna},
                                        2
                                    ) }}
                                @break

                                @default
                                    {{ $fila->{$columna} ?? '—' }}
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td
                        colspan="{{ count($columnas) }}"
                        class="empty"
                    >
                        Sin resultados para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
