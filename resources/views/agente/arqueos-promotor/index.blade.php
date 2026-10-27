@extends('layouts.agente')

@section('title', 'Arqueos del Promotor')
@section('module-title', 'Arqueos del Promotor')

@push('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }

    .summary-card {
        padding: 20px;
        border: 1px solid #e0e8ee;
        border-radius: 17px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, 0.05);
    }

    .summary-card span {
        display: block;
        color: #768692;
        font-size: 11px;
        font-weight: 750;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .summary-card strong {
        display: block;
        margin-top: 10px;
        color: #082d55;
        font-size: 24px;
    }

    .table-panel {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, 0.05);
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 19px 21px;
        border-bottom: 1px solid #edf1f4;
    }

    .table-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 16px;
    }

    .table-header p {
        margin: 5px 0 0;
        color: #82909a;
        font-size: 11px;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .promotor-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .promotor-table th {
        padding: 13px 17px;
        background: #f7f9fb;
        color: #6c7c88;
        font-size: 10px;
        font-weight: 800;
        text-align: left;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .promotor-table td {
        padding: 15px 17px;
        border-top: 1px solid #edf1f4;
        color: #30485b;
        font-size: 12px;
        vertical-align: middle;
    }

    .promotor-table td strong {
        color: #163b5b;
    }

    .amount {
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .4px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-badge.pendiente {
        background: #fff5dc;
        color: #9c7014;
    }

    .status-badge.firmado {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .status-badge.certificado {
        background: #e8f1ff;
        color: #285b9b;
    }

    .status-badge.anulado {
        background: #fdecec;
        color: #b13c3c;
    }

    .actions-cell {
        white-space: nowrap;
    }

    .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .icon-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: 1px solid #d5dfe5;
        border-radius: 9px;
        background: #ffffff;
        color: #31536e;
        text-decoration: none;
        transition: .2s ease;
    }

    .icon-button:hover {
        transform: translateY(-1px);
        border-color: #9db6c9;
        background: #f4f8fb;
        color: #164c96;
        box-shadow: 0 6px 14px rgba(24, 66, 99, .10);
    }

    .icon-button.sign {
        border-color: #c6e2d2;
        background: #eef9f3;
        color: #1d7b4e;
    }

    .icon-button svg {
        width: 17px;
        height: 17px;
        stroke: currentColor;
        stroke-width: 1.9;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .empty-record {
        padding: 48px 20px;
        text-align: center;
        color: #7d8b95;
    }

    .empty-record h4 {
        margin: 0;
        color: #17364f;
        font-size: 14px;
    }

    .empty-record p {
        margin: 7px auto 0;
        max-width: 380px;
        font-size: 11px;
        line-height: 1.55;
    }

    .pagination-container {
        padding: 17px 20px;
        border-top: 1px solid #edf1f4;
    }

    @media (max-width: 850px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .table-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">
        <h2>Arqueos del Promotor</h2>

        <p>
            Consulte y firme los arqueos realizados por el promotor.
        </p>
    </div>

    <div class="page-badge">
        <span class="page-badge-dot"></span>
        {{ $agente->codigo_agente }}
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning mb-4">
        {{ session('warning') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-4">
        {{ $errors->first() }}
    </div>
@endif

<section class="summary-grid">
    <article class="summary-card">
        <span>Total de arqueos</span>
        <strong>{{ $totalArqueos }}</strong>
    </article>

    <article class="summary-card">
        <span>Pendientes de firma</span>
        <strong>{{ $pendientesFirma }}</strong>
    </article>

    <article class="summary-card">
        <span>Firmados por el agente</span>
        <strong>{{ $firmados }}</strong>
    </article>
</section>

<section class="table-panel">

    <header class="table-header">
        <div>
            <h3>Historial de arqueos del promotor</h3>

            <p>
                Registros realizados por promotores para este agente.
            </p>
        </div>
    </header>

    <div class="table-responsive">

        @if ($arqueos->isEmpty())

            <div class="empty-record">
                <h4>No existen arqueos del promotor</h4>

                <p>
                    Los arqueos realizados por promotores aparecerán en esta sección.
                </p>
            </div>

        @else

            <table class="promotor-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total arqueado</th>
                        <th>Saldo sistema</th>
                        <th>Diferencia</th>
                        <th>Firma del agente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($arqueos as $arqueo)
                        @php
                            $firmaValidador = $arqueo->firmas
                                ->first(function ($firma) use ($agente) {
                                    return $firma->tipo_firma === 'VALIDADOR'
                                        && $firma->valida;
                                });

                            $claseEstado = match ($arqueo->estado) {
                                'PENDIENTE_CERTIFICACION' => 'pendiente',
                                'CERTIFICADO' => 'certificado',
                                'ANULADO' => 'anulado',
                                default => 'pendiente',
                            };

                            $textoEstado = match ($arqueo->estado) {
                                'PENDIENTE_CERTIFICACION' => 'Pendiente',
                                'CERTIFICADO' => 'Certificado',
                                'ANULADO' => 'Anulado',
                                default => $arqueo->estado,
                            };
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $arqueo->numero_arqueo }}</strong>
                            </td>

                            <td>
                                {{ $arqueo->fecha_arqueo->format('d/m/Y') }}
                            </td>

                            <td>
                                <span class="status-badge {{ $claseEstado }}">
                                    {{ $textoEstado }}
                                </span>
                            </td>

                            <td class="amount">
                                Q {{ number_format((float) $arqueo->total_arqueado, 2) }}
                            </td>

                            <td class="amount">
                                Q {{ number_format((float) $arqueo->saldo_sistema, 2) }}
                            </td>

                            <td class="amount">
                                @if ((float) $arqueo->diferencia > 0)
                                    <span style="color:#1d7b4e;font-weight:700;">
                                        +Q {{ number_format((float) $arqueo->diferencia, 2) }}
                                    </span>
                                @elseif ((float) $arqueo->diferencia < 0)
                                    <span style="color:#c0392b;font-weight:700;">
                                        Q {{ number_format((float) $arqueo->diferencia, 2) }}
                                    </span>
                                @else
                                    <span style="color:#0a3158;font-weight:700;">
                                        Q 0.00
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($firmaValidador)
                                    <span class="status-badge firmado">
                                        Firmado
                                    </span>
                                @else
                                    <span class="status-badge pendiente">
                                        Pendiente
                                    </span>
                                @endif
                            </td>

                            <td class="actions-cell">
                                <div class="table-actions">

                                    <a
                                        href="{{ route('agente.arqueos-promotor.show', $arqueo) }}"
                                        class="icon-button"
                                        title="Ver detalle"
                                        aria-label="Ver detalle del arqueo"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="2.8"></circle>
                                        </svg>
                                    </a>

                                    @if (
                                        ! $firmaValidador
                                        && $arqueo->estado === 'PENDIENTE_CERTIFICACION'
                                    )
                                        <a
                                            href="{{ route('agente.arqueos-promotor.show', $arqueo) }}"
                                            class="icon-button sign"
                                            title="Firmar electrónicamente"
                                            aria-label="Firmar arqueo electrónicamente"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"></path>
                                            </svg>
                                        </a>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @endif

    </div>

    @if ($arqueos->hasPages())
        <div class="pagination-container">
            {{ $arqueos->links() }}
        </div>
    @endif

</section>

@endsection
