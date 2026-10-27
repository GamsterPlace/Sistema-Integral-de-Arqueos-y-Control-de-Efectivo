@extends('layouts.agente')

@section('title', 'Arqueos del Agente')
@section('module-title', 'Arqueos del Agente')

@push('styles')
<style>
    .arqueos-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }

    .summary-box {
        padding: 20px;
        border: 1px solid #e0e8ee;
        border-radius: 17px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, 0.05);
    }

    .summary-box span {
        display: block;
        color: #768692;
        font-size: 11px;
        font-weight: 750;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .summary-box strong {
        display: block;
        margin-top: 10px;
        color: #082d55;
        font-size: 24px;
    }

    .today-panel {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 22px;
        margin-bottom: 22px;
        padding: 22px;
        border: 1px solid #dfe8ee;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(20,57,83,.05);
    }

    .today-information h3 {
        margin: 0;
        color: #0a3158;
        font-size: 17px;
    }

    .today-information p {
        margin: 7px 0 0;
        color: #7d8c97;
        font-size: 12px;
        line-height: 1.6;
    }

    .today-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-badge.sin-arqueo {
        background: #eef2f5;
        color: #667580;
    }

    .status-badge.pendiente {
        background: #e8f1ff;
        color: #285b9b;
    }

    .status-badge.certificado {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .status-badge.anulado {
        background: #fdecec;
        color: #b13c3c;
    }

    .primary-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 43px;
        padding: 0 18px;
        border: none;
        border-radius: 11px;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #fff;
        text-decoration: none;
        font-size: 12px;
        font-weight: 800;
        box-shadow: 0 10px 18px rgba(22,76,150,.18);
        transition: .2s;
    }

    .primary-button:hover {
        transform: translateY(-1px);
        color: #fff;
    }

    .primary-button.disabled {
        opacity: .6;
        cursor: not-allowed;
        pointer-events: none;
    }

    .table-panel {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(20,57,83,.05);
    }

    .table-header {
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

    .arqueos-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 970px;
    }

    .arqueos-table th {
        padding: 13px 17px;
        background: #f7f9fb;
        color: #6c7c88;
        font-size: 10px;
        font-weight: 800;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .45px;
    }

    .arqueos-table td {
        padding: 15px 17px;
        border-top: 1px solid #edf1f4;
        color: #30485b;
        font-size: 12px;
        vertical-align: middle;
    }

    .arqueos-table td strong {
        color: #163b5b;
    }

    .amount {
        font-weight: 700;
        white-space: nowrap;
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

    .icon-button.print {
        border-color: #c8d7e3;
        background: #edf5fb;
        color: #164c96;
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

    .empty-table {
        padding: 45px 20px;
        color: #7d8b95;
        font-size: 13px;
        text-align: center;
    }

    .pagination-container {
        padding: 17px 20px;
        border-top: 1px solid #edf1f4;
    }

    @media(max-width:850px) {
        .arqueos-summary {
            grid-template-columns: 1fr;
        }

        .today-panel {
            flex-direction: column;
            align-items: flex-start;
        }

        .today-actions {
            width: 100%;
        }

        .primary-button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">
        <h2>Arqueos del Agente</h2>

        <p>
            Realice su arqueo diario y consulte el historial de arqueos registrados.
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

<section class="arqueos-summary">

    <article class="summary-box">
        <span>Arqueo de hoy</span>

        <strong>
            {{ $arqueoHoy ? 'Registrado' : 'Pendiente' }}
        </strong>
    </article>

    <article class="summary-box">
        <span>Total de arqueos</span>

        <strong>
            {{ $totalArqueos }}
        </strong>
    </article>

    <article class="summary-box">
        <span>Arqueos certificados</span>

        <strong>
            {{ $totalCertificados }}
        </strong>
    </article>

</section>

<section class="today-panel">

    <div class="today-information">

        <h3>Arqueo diario</h3>

        @if(!$arqueoHoy)

            <p>
                Aún no ha realizado el arqueo correspondiente al día
                <strong>{{ now()->locale('es')->translatedFormat('d \\d\\e F \\d\\e Y') }}</strong>.
                Presione el botón para iniciar el proceso.
            </p>

        @elseif($arqueoHoy->estado === 'PENDIENTE_CERTIFICACION')

            <p>
                El arqueo fue enviado correctamente y actualmente se encuentra
                pendiente de certificación por el Promotor de Agentes.
            </p>

        @elseif($arqueoHoy->estado === 'CERTIFICADO')

            <p>
                El arqueo del día fue certificado correctamente.
                No puede registrar otro arqueo para esta fecha.
            </p>

        @elseif($arqueoHoy->estado === 'ANULADO')

            <p>
                El arqueo fue anulado. Si corresponde, podrá registrar uno nuevo
                cuando sea habilitado por el personal autorizado.
            </p>

        @else

            <p>
                El arqueo del día ya fue registrado.
            </p>

        @endif

    </div>

    <div class="today-actions">

        @if(!$arqueoHoy)

            <a
                href="{{ route('agente.arqueos.create') }}"
                class="primary-button"
            >
                Iniciar Arqueo
            </a>

            <span class="status-badge sin-arqueo">
                Pendiente
            </span>

        @elseif($arqueoHoy->estado === 'PENDIENTE_CERTIFICACION')

            <span class="status-badge pendiente">
                Pendiente de Certificación
            </span>

        @elseif($arqueoHoy->estado === 'CERTIFICADO')

            <span class="status-badge certificado">
                Certificado
            </span>

        @elseif($arqueoHoy->estado === 'ANULADO')

            <span class="status-badge anulado">
                Anulado
            </span>

        @endif

    </div>

</section>

<section class="table-panel">

    <header class="table-header">

        <h3>Historial de Arqueos</h3>

        <p>
            Consulte todos los arqueos registrados para este agente.
        </p>

    </header>

    <div class="table-responsive">

        @if ($arqueos->isEmpty())

            <div class="empty-table">
                No existen arqueos registrados.
            </div>

        @else

            <table class="arqueos-table">

                <thead>
                    <tr>
                        <th>Número</th>
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
                                default => 'sin-arqueo',
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
                                @if($arqueo->diferencia > 0)
                                    <span style="color:#1d7b4e;font-weight:700;">
                                        +Q {{ number_format((float) $arqueo->diferencia, 2) }}
                                    </span>
                                @elseif($arqueo->diferencia < 0)
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
                                        href="{{ route('agente.arqueos.show', $arqueo) }}"
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
                                        href="{{ route('agente.arqueos.imprimir', $arqueo) }}"
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
