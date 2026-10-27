<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 28px 34px; }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #263d50;
        }
        .header {
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 3px solid #164c96;
        }
        table { width: 100%; border-collapse: collapse; }
        .logo { width: 150px; max-height: 60px; }
        h1 {
            margin: 0;
            color: #0b315f;
            font-size: 20px;
            text-align: right;
        }
        .subtitle {
            margin-top: 5px;
            color: #657887;
            font-size: 9px;
            text-align: right;
        }
        .agent-title {
            margin-bottom: 16px;
            padding: 14px 16px;
            background: #f2f6f9;
        }
        .agent-title h2 {
            margin: 0;
            color: #0b315f;
            font-size: 16px;
        }
        .agent-title p {
            margin: 5px 0 0;
            color: #687b8b;
        }
        .section { margin-bottom: 16px; }
        .section-title {
            margin: 0 0 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #dce5eb;
            color: #12375f;
            font-size: 12px;
        }
        .info-table td {
            width: 50%;
            padding: 7px 9px;
            border: 1px solid #dce5eb;
            vertical-align: top;
        }
        .label {
            display: block;
            margin-bottom: 3px;
            color: #718391;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .value {
            color: #183b59;
            font-size: 9px;
            font-weight: bold;
        }
        .stats-table td {
            width: 25%;
            padding: 10px 8px;
            border: 1px solid #dce5eb;
            text-align: center;
        }
        .stat-number {
            display: block;
            margin-top: 5px;
            color: #0b315f;
            font-size: 15px;
            font-weight: bold;
        }
        .history-table th,
        .history-table td {
            padding: 6px 7px;
            border: 1px solid #dce5eb;
            text-align: left;
        }
        .history-table th {
            background: #f3f6f8;
            color: #617586;
            font-size: 7px;
            text-transform: uppercase;
        }
        .footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #dce5eb;
            color: #82909a;
            font-size: 7px;
            text-align: right;
        }
    </style>
</head>

<body>
@php
    $nombrePromotor = trim(
        ($agente->promotor_nombres ?? '')
        . ' '
        . ($agente->promotor_apellidos ?? '')
    );
@endphp

<div class="header">
    <table>
        <tr>
            <td style="width:28%;">
                <img
                    src="{{ public_path('images/logos/ecosaba.png') }}"
                    alt="ECOSABA MICOOPE"
                    class="logo"
                >
            </td>
            <td style="width:72%;">
                <h1>Ficha General del Agente</h1>
                <div class="subtitle">
                    Sistema de Arqueos para Agentes MICOOPE
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="agent-title">
    <h2>{{ $agente->nombre_negocio }}</h2>
    <p>
        Código {{ $agente->codigo_agente }}
        · {{ $agente->ruta_nombre }}
        · {{ $agente->region_nombre }}
    </p>
</div>

<div class="section">
    <h3 class="section-title">Información institucional</h3>

    <table class="info-table">
        <tr>
            <td>
                <span class="label">Código de agente</span>
                <span class="value">{{ $agente->codigo_agente }}</span>
            </td>
            <td>
                <span class="label">Estado</span>
                <span class="value">{{ $agente->estado }}</span>
            </td>
        </tr>

        <tr>
            <td>
                <span class="label">Nombre del negocio</span>
                <span class="value">{{ $agente->nombre_negocio }}</span>
            </td>
            <td>
                <span class="label">Propietario / Receptor</span>
                <span class="value">{{ $agente->nombre_propietario }}</span>
            </td>
        </tr>

        <tr>
            <td>
                <span class="label">Usuario asociado</span>
                <span class="value">
                    {{ $agente->agente_usuario ?: 'Sin usuario asociado' }}
                </span>
            </td>
            <td>
                <span class="label">Promotor asignado</span>
                <span class="value">
                    {{ $nombrePromotor !== ''
                        ? $nombrePromotor
                        : ($agente->promotor_usuario ?: 'Sin promotor asignado') }}
                </span>
            </td>
        </tr>

        <tr>
            <td>
                <span class="label">Ruta</span>
                <span class="value">
                    {{ $agente->ruta_codigo }} — {{ $agente->ruta_nombre }}
                </span>
            </td>
            <td>
                <span class="label">Región</span>
                <span class="value">{{ $agente->region_nombre }}</span>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <span class="label">Dirección</span>
                <span class="value">
                    {{ $agente->direccion ?: 'No registrada' }}
                </span>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <h3 class="section-title">Resumen de arqueos</h3>

    <table class="stats-table">
        <tr>
            <td>
                <span class="label">Total registros</span>
                <span class="stat-number">{{ (int) $resumen->total_arqueos }}</span>
            </td>
            <td>
                <span class="label">Arqueos del agente</span>
                <span class="stat-number">{{ (int) $resumen->arqueos_agente }}</span>
            </td>
            <td>
                <span class="label">Arqueos de promotor</span>
                <span class="stat-number">{{ (int) $resumen->arqueos_promotor }}</span>
            </td>
            <td>
                <span class="label">Anulados</span>
                <span class="stat-number">{{ (int) $resumen->anulados }}</span>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <h3 class="section-title">Últimos arqueos</h3>

    <table class="history-table">
        <thead>
            <tr>
                <th>Número</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Extemp.</th>
                <th>Total</th>
                <th>Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ultimosArqueos as $arqueo)
                <tr>
                    <td>{{ $arqueo->numero_arqueo }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse(
                            $arqueo->fecha_arqueo
                        )->format('d/m/Y') }}
                    </td>
                    <td>
                        {{ $arqueo->tipo === 'DIARIO_AGENTE'
                            ? 'Agente'
                            : 'Promotor' }}
                    </td>
                    <td>{{ str_replace('_', ' ', $arqueo->estado) }}</td>
                    <td>{{ $arqueo->fuera_fecha_ordinaria ? 'Sí' : 'No' }}</td>
                    <td>
                        Q {{ number_format(
                            (float) $arqueo->total_arqueado,
                            2
                        ) }}
                    </td>
                    <td>
                        Q {{ number_format(
                            (float) $arqueo->diferencia,
                            2
                        ) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">
                        Sin arqueos registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="footer">
    Generado el {{ now()->format('d/m/Y H:i') }}
    · Sistema de Arqueos MICOOPE
</div>

</body>
</html>
