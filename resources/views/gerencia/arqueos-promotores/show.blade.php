@extends('layouts.gerencia')

@section('title', 'Detalle de Arqueo')
@section('module-title', 'Arqueos por Promotor')

@push('styles')
<style>
    .page-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 15px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        box-sizing: border-box;
        transition: .2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-secondary {
        border: 1px solid #d5dfe5;
        background: #ffffff;
        color: #31536e;
    }

    .btn-secondary:hover {
        border-color: #9db6c9;
        background: #f4f8fb;
        color: #164c96;
    }

    .btn-primary {
        border: 1px solid #164c96;
        background: #164c96;
        color: #ffffff;
        box-shadow: 0 6px 14px rgba(22, 76, 150, .12);
    }

    .btn-primary:hover {
        background: #123f7e;
        border-color: #123f7e;
    }

    .detail-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(300px, .7fr);
        gap: 20px;
        align-items: start;
    }

    .main-column {
        min-width: 0;
    }

    .detail-card {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .detail-card + .detail-card {
        margin-top: 20px;
    }

    .card-header {
        padding: 17px 19px;
        border-bottom: 1px solid #edf1f4;
        background: #fafcfd;
    }

    .card-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 15px;
    }

    .card-header p {
        margin: 5px 0 0;
        color: #82909a;
        font-size: 10px;
        line-height: 1.5;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        padding: 19px;
    }

    .info-item {
        min-width: 0;
        padding: 13px;
        border: 1px solid #e2e9ee;
        border-radius: 11px;
        background: #fbfcfd;
    }

    .info-label {
        display: block;
        color: #758697;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .25px;
        text-transform: uppercase;
    }

    .info-value {
        display: block;
        margin-top: 6px;
        color: #173b59;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.45;
        word-break: break-word;
    }

    .status-value {
        display: inline-flex;
        align-items: center;
        margin-top: 6px;
        padding: 6px 9px;
        border-radius: 999px;
        background: #edf2f6;
        color: #50667a;
        font-size: 8px;
        font-weight: 800;
        line-height: 1;
        text-transform: uppercase;
    }

    .cash-content {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        padding: 19px;
    }

    .cash-box {
        overflow: hidden;
        border: 1px solid #e2e9ee;
        border-radius: 13px;
        background: #ffffff;
    }

    .cash-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 13px 14px;
        border-bottom: 1px solid #edf1f4;
        background: #fbfcfd;
    }

    .cash-box-header h4 {
        margin: 0;
        color: #173b59;
        font-size: 12px;
    }

    .cash-box-header span {
        color: #81909c;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .cash-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cash-table th,
    .cash-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #edf1f4;
        color: #40596c;
        font-size: 10px;
        text-align: left;
    }

    .cash-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .cash-table th:nth-child(2),
    .cash-table td:nth-child(2) {
        text-align: center;
    }

    .cash-table th:last-child,
    .cash-table td:last-child {
        text-align: right;
    }

    .cash-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .cash-table tbody tr:hover {
        background: #fbfdfe;
    }

    .cash-denomination {
        color: #164c96;
        font-weight: 800;
    }

    .cash-quantity {
        color: #50687a;
        font-weight: 700;
    }

    .cash-subtotal {
        color: #173b59;
        font-weight: 800;
        white-space: nowrap;
    }

    .empty-cash {
        padding: 28px 15px;
        color: #82909a;
        font-size: 10px;
        text-align: center;
    }

    .summary-card {
        position: sticky;
        top: 20px;
    }

    .summary-content {
        padding: 7px 19px 18px;
    }

    .summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 13px 0;
        border-bottom: 1px solid #edf1f4;
    }

    .summary-row:last-child {
        border-bottom: 0;
    }

    .summary-label {
        color: #657887;
        font-size: 10px;
    }

    .summary-value {
        color: #173b59;
        font-size: 11px;
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
    }

    .summary-row.total {
        margin-top: 5px;
        padding: 14px 12px;
        border: 1px solid #d7e4ed;
        border-radius: 11px;
        background: #f7fafc;
    }

    .summary-row.total .summary-label,
    .summary-row.total .summary-value {
        color: #0a3158;
        font-weight: 850;
    }

    .observations {
        padding: 0 19px 19px;
    }

    .observations-box {
        padding: 14px;
        border: 1px solid #e2e9ee;
        border-radius: 11px;
        background: #fbfcfd;
    }

    .observations-label {
        display: block;
        margin-bottom: 7px;
        color: #758697;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .25px;
        text-transform: uppercase;
    }

    .observations-text {
        margin: 0;
        color: #40596c;
        font-size: 10px;
        line-height: 1.6;
        white-space: pre-line;
    }

    @media (max-width: 1100px) {
        .detail-layout {
            grid-template-columns: 1fr;
        }

        .summary-card {
            position: static;
        }
    }

    @media (max-width: 850px) {
        .info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cash-content {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .page-actions {
            width: 100%;
        }

        .page-actions .btn {
            flex: 1;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .summary-row {
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
@php
    $agente = $arqueo->agente;
    $ruta = $agente?->ruta;
    $region = $ruta?->region;

    $nombrePromotor = trim(
        ($promotor->nombres ?? '')
        . ' '
        . ($promotor->apellidos ?? '')
    );
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Arqueo {{ $arqueo->numero_arqueo }}</h2>

        <p>
            Consulta detallada del arqueo realizado por Promotor.
        </p>
    </div>

    <div class="page-actions">
        <a
            href="{{ route('gerencia.arqueos-promotores.index') }}"
            class="btn btn-secondary"
        >
            Regresar
        </a>

        <a
            href="{{ route(
                'gerencia.arqueos-promotores.imprimir',
                $arqueo->id
            ) }}"
            target="_blank"
            class="btn btn-primary"
        >
            Imprimir PDF
        </a>
    </div>
</div>

<div class="detail-layout">
    <div class="main-column">
        <section class="detail-card">
            <div class="card-header">
                <h3>Información General</h3>

                <p>
                    Información principal del arqueo y del Agente visitado.
                </p>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">
                        Número de arqueo
                    </span>

                    <strong class="info-value">
                        {{ $arqueo->numero_arqueo }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        Fecha
                    </span>

                    <strong class="info-value">
                        {{ \Carbon\Carbon::parse(
                            $arqueo->fecha_arqueo
                        )->format('d/m/Y') }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        Estado
                    </span>

                    <span class="status-value">
                        {{ str_replace(
                            '_',
                            ' ',
                            $arqueo->estado
                        ) }}
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        Promotor
                    </span>

                    <strong class="info-value">
                        {{ $nombrePromotor !== ''
                            ? $nombrePromotor
                            : ($promotor->usuario ?? '—') }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        Agente
                    </span>

                    <strong class="info-value">
                        {{ $agente?->codigo_agente }}
                        — {{ $agente?->nombre_negocio }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        Propietario
                    </span>

                    <strong class="info-value">
                        {{ $agente?->nombre_propietario }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        Región
                    </span>

                    <strong class="info-value">
                        {{ $region?->nombre ?? '—' }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        Ruta
                    </span>

                    <strong class="info-value">
                        {{ $ruta?->codigo ?? '' }}
                        {{ $ruta?->nombre ?? '—' }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">
                        Extemporáneo
                    </span>

                    <strong class="info-value">
                        {{ $arqueo->fuera_fecha_ordinaria
                            ? 'Sí'
                            : 'No' }}
                    </strong>
                </div>
            </div>

            <div class="observations">
                <div class="observations-box">
                    <span class="observations-label">
                        Observaciones
                    </span>

                    <p class="observations-text">{{ $arqueo->observaciones ?: 'Sin observaciones' }}</p>
                </div>
            </div>
        </section>

        <section class="detail-card">
            <div class="card-header">
                <h3>Conteo de efectivo</h3>

                <p>
                    Detalle de billetes y monedas registrados en el arqueo.
                </p>
            </div>

            <div class="cash-content">
                <div class="cash-box">
                    <div class="cash-box-header">
                        <h4>Billetes</h4>
                        <span>Detalle</span>
                    </div>

                    @if($billetes->isNotEmpty())
                        <table class="cash-table">
                            <thead>
                                <tr>
                                    <th>Denominación</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($billetes as $detalle)
                                    <tr>
                                        <td class="cash-denomination">
                                            Q {{ number_format(
                                                (float) $detalle->denominacion,
                                                2
                                            ) }}
                                        </td>

                                        <td class="cash-quantity">
                                            {{ $detalle->cantidad }}
                                        </td>

                                        <td class="cash-subtotal">
                                            Q {{ number_format(
                                                (float) $detalle->subtotal,
                                                2
                                            ) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="3"
                                            class="empty-cash"
                                        >
                                            Sin billetes registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @else
                        <div class="empty-cash">
                            Sin billetes registrados.
                        </div>
                    @endif
                </div>

                <div class="cash-box">
                    <div class="cash-box-header">
                        <h4>Monedas</h4>
                        <span>Detalle</span>
                    </div>

                    @if($monedas->isNotEmpty())
                        <table class="cash-table">
                            <thead>
                                <tr>
                                    <th>Denominación</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($monedas as $detalle)
                                    <tr>
                                        <td class="cash-denomination">
                                            Q {{ number_format(
                                                (float) $detalle->denominacion,
                                                2
                                            ) }}
                                        </td>

                                        <td class="cash-quantity">
                                            {{ $detalle->cantidad }}
                                        </td>

                                        <td class="cash-subtotal">
                                            Q {{ number_format(
                                                (float) $detalle->subtotal,
                                                2
                                            ) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="3"
                                            class="empty-cash"
                                        >
                                            Sin monedas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @else
                        <div class="empty-cash">
                            Sin monedas registradas.
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <aside>
        <section class="detail-card summary-card">
            <div class="card-header">
                <h3>Resultado</h3>

                <p>
                    Resumen monetario del arqueo.
                </p>
            </div>

            <div class="summary-content">
                <div class="summary-row">
                    <span class="summary-label">
                        Saldo Sistema
                    </span>

                    <strong class="summary-value">
                        Q {{ number_format(
                            (float) $arqueo->saldo_sistema,
                            2
                        ) }}
                    </strong>
                </div>

                <div class="summary-row total">
                    <span class="summary-label">
                        Total Arqueado
                    </span>

                    <strong class="summary-value">
                        Q {{ number_format(
                            (float) $arqueo->total_arqueado,
                            2
                        ) }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span class="summary-label">
                        Diferencia
                    </span>

                    <strong class="summary-value">
                        Q {{ number_format(
                            abs((float) $arqueo->diferencia),
                            2
                        ) }}
                    </strong>
                </div>
            </div>
        </section>
    </aside>
</div>
@endsection
