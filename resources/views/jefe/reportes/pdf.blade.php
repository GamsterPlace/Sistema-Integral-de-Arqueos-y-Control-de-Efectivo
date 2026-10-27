<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        @page{margin:24px}
        body{
            font-family:DejaVu Sans,sans-serif;
            color:#263d50;
            font-size:8px;
        }
        h1{margin:0;color:#12375f;font-size:18px}
        .sub{margin:4px 0 12px;color:#718493}
        .metrics{
            width:100%;
            margin-bottom:12px;
            border-collapse:collapse;
        }
        .metrics td{
            padding:8px;
            border:1px solid #dce5eb;
            background:#f8fafb;
        }
        .metrics span{
            display:block;
            color:#718493;
            font-size:6px;
            text-transform:uppercase;
        }
        .metrics strong{
            display:block;
            margin-top:3px;
            color:#12375f;
            font-size:11px;
        }
        table.results{
            width:100%;
            border-collapse:collapse;
        }
        .results th{
            padding:6px 4px;
            border:1px solid #d5dfe6;
            background:#173e6b;
            color:#fff;
            font-size:6px;
            text-transform:uppercase;
        }
        .results td{
            padding:6px 4px;
            border:1px solid #dce5eb;
            vertical-align:top;
        }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>
    <div class="sub">
        Sistema de Arqueos para Agentes MICOOPE ·
        Generado {{ now()->format('d/m/Y H:i') }}
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
                                    {{ $fila->codigo_agente }}
                                    — {{ $fila->nombre_negocio }}
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

                                @case('diferencia')
                                    Q {{ number_format(
                                        abs((float) $fila->diferencia),
                                        2
                                    ) }}
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
                    <td colspan="{{ count($columnas) }}">
                        Sin resultados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
