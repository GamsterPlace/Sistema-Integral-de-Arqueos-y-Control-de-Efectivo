@extends('layouts.promotor')

@section('title', 'Historial de Arqueos')
@section('module-title', 'Historial de Arqueos')

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
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
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
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
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

    .primary-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 17px;
        border-radius: 11px;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 9px 18px rgba(22, 76, 150, .18);
    }

    .table-responsive {
        overflow-x: auto;
    }

    .arqueos-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .arqueos-table th {
        padding: 13px 17px;
        background: #f7f9fb;
        color: #6c7c88;
        font-size: 10px;
        font-weight: 800;
        text-align: left;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .arqueos-table td {
        padding: 15px 17px;
        border-top: 1px solid #edf1f4;
        color: #30485b;
        font-size: 12px;
        vertical-align: middle;
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

    .status-badge.certificado {
        background: #eaf8ef;
        color: #1d7b4e;
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
    }

    .icon-button.print {
        border-color: #c8d7e3;
        background: #edf5fb;
        color: #164c96;
    }

    .icon-button svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .amount {
        font-weight: 700;
        white-space: nowrap;
    }

    .empty-record {
        padding: 46px 20px;
        color: #7d8b95;
        text-align: center;
        font-size: 12px;
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
        <h2>Historial de Arqueos</h2>

        <p>
            Consulte los arqueos de visita realizados por usted.
        </p>
    </div>

    <a
        href="{{ route('promotor.arqueos.create') }}"
        class="primary-button"
    >
        Realizar Arqueo
    </a>
</div>


@if(session('warning'))
    <div class="alert-message warning">
        {{ session('warning') }}
    </div>
@endif

@if($errors->any())
    <div class="alert-message warning">
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
        <span>Certificados</span>
        <strong>{{ $certificados }}</strong>
    </article>
</section>

<section class="table-panel">
    <header class="table-header">
        <div>
            <h3>Arqueos registrados</h3>

            <p>
                Historial de visitas realizadas por el promotor.
            </p>
        </div>
    </header>

    <div class="table-responsive">
        @if ($arqueos->isEmpty())
            <div class="empty-record">
                Aún no ha realizado arqueos de visita.
            </div>
        @else
            <table class="arqueos-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Agente</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total Arqueado</th>
                        <th>Saldo Sistema</th>
                        <th>Diferencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($arqueos as $arqueo)
                        @php
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
                                {{ $arqueo->nombre_negocio_historico }}
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

                            <td class="actions-cell">
                                <div class="table-actions">
                                    <a
                                        href="{{ route('promotor.arqueos.show', $arqueo) }}"
                                        class="icon-button"
                                        title="Ver detalle"
                                        aria-label="Ver detalle del arqueo"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="2.8"></circle>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('promotor.arqueos.imprimir', $arqueo) }}"
                                        target="_blank"
                                        class="icon-button print"
                                        title="Imprimir PDF"
                                        aria-label="Imprimir arqueo en PDF"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M6 9V3h12v6"></path>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                            <rect x="6" y="14" width="12" height="7"></rect>
                                            <path d="M18 12h.01"></path>
                                        </svg>
                                    </a>
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
