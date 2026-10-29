
@extends('layouts.jefe-agencia')

@section('title', 'Dashboard - Jefe de Agencia')

@push('styles')
<style>
    .ja-dashboard {
        display: grid;
        gap: 24px;
    }

    .ja-dashboard * {
        box-sizing: border-box;
    }

    .ja-dashboard-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .ja-dashboard-title h2 {
        margin: 0;
        color: #06284f;
        font-size: 28px;
        font-weight: 800;
    }

    .ja-dashboard-title p {
        margin: 9px 0 0;
        color: #6d7d8a;
        font-size: 14px;
        line-height: 1.6;
    }

    .ja-date {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 17px;
        background: #fff;
        border: 1px solid #dfe7ee;
        border-radius: 12px;
        color: #164c96;
        font-size: 13px;
        font-weight: 700;
    }

    .ja-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 17px;
    }

    .ja-stat {
        position: relative;
        min-height: 145px;
        padding: 22px;
        border: 1px solid #dfe7ee;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 7px 24px rgba(6, 40, 79, .035);
        overflow: hidden;
    }

    .ja-stat::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--stat-color, #164c96);
    }

    .ja-stat-label {
        display: block;
        color: #718295;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
    }

    .ja-stat-value {
        display: block;
        margin-top: 15px;
        color: #06284f;
        font-size: 35px;
        font-weight: 850;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .ja-stat-note {
        display: block;
        margin-top: 12px;
        color: #8190a0;
        font-size: 11px;
    }

    .ja-panel {
        background: #fff;
        border: 1px solid #dfe7ee;
        border-radius: 17px;
        overflow: hidden;
        box-shadow: 0 7px 24px rgba(6, 40, 79, .035);
    }

    .ja-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 21px 23px;
        border-bottom: 1px solid #e8edf2;
        flex-wrap: wrap;
    }

    .ja-panel-header h3 {
        margin: 0;
        color: #06284f;
        font-size: 17px;
        font-weight: 800;
    }

    .ja-panel-header p {
        margin: 6px 0 0;
        color: #758598;
        font-size: 12px;
        line-height: 1.5;
    }

    .ja-panel-body {
        padding: 24px;
    }

    .ja-progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 13px;
    }

    .ja-progress-header strong {
        color: #06284f;
        font-size: 28px;
        font-weight: 850;
    }

    .ja-progress-header span {
        color: #718295;
        font-size: 12px;
    }

    .ja-progress-track {
        height: 13px;
        overflow: hidden;
        border-radius: 20px;
        background: #e9eef3;
    }

    .ja-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #008d48, #00a651);
        transition: width .3s ease;
    }

    .ja-progress-detail {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 16px;
        color: #718295;
        font-size: 12px;
    }

    .ja-progress-detail strong {
        color: #19344d;
    }

    .ja-section-grid {
        display: grid;
        grid-template-columns: 1.15fr .85fr;
        gap: 20px;
    }

    .ja-status-list {
        display: grid;
        gap: 14px;
    }

    .ja-status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 15px;
        border: 1px solid #e8edf2;
        border-radius: 12px;
        background: #fafcfe;
    }

    .ja-status-item span {
        color: #53677b;
        font-size: 13px;
    }

    .ja-status-item strong {
        color: #06284f;
        font-size: 19px;
        font-weight: 800;
    }

    .ja-agency-list {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
    }

    .ja-agency-tag {
        display: inline-flex;
        padding: 9px 12px;
        border: 1px solid #d7e5f4;
        border-radius: 9px;
        background: #f0f6fc;
        color: #164c96;
        font-size: 12px;
        font-weight: 700;
    }

    .ja-table-scroll {
        width: 100%;
        overflow-x: auto;
    }

    .ja-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .ja-table thead {
        background: #f5f8fb;
    }

    .ja-table th {
        padding: 14px 17px;
        color: #63778c;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .ja-table td {
        padding: 16px 17px;
        border-top: 1px solid #edf1f5;
        color: #344c64;
        font-size: 12px;
    }

    .ja-table tbody tr:hover {
        background: #fafcfe;
    }

    .ja-table-number {
        color: #164c96;
        font-weight: 800;
    }

    .ja-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 7px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .2px;
    }

    .ja-badge-certified {
        color: #087b41;
        background: #e4f7eb;
    }

    .ja-badge-pending {
        color: #9a650a;
        background: #fff3d9;
    }

    .ja-badge-cancelled {
        color: #b02d37;
        background: #ffebed;
    }

    .ja-badge-other {
        color: #52677c;
        background: #edf1f5;
    }

    .ja-empty {
        padding: 30px 20px;
        color: #8190a0;
        text-align: center;
        font-size: 13px;
        line-height: 1.7;
    }

    .ja-readonly {
        padding: 15px 18px;
        border: 1px solid #d7e5f4;
        border-radius: 12px;
        background: #f0f6fc;
        color: #416181;
        font-size: 12px;
        line-height: 1.6;
    }

    @media (max-width: 1200px) {
        .ja-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 850px) {
        .ja-section-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 550px) {
        .ja-summary {
            grid-template-columns: 1fr;
        }

        .ja-dashboard-title h2 {
            font-size: 24px;
        }

        .ja-panel-body {
            padding: 17px;
        }

        .ja-panel-header {
            padding: 17px;
        }
    }
</style>
@endpush

@section('content')

<div class="ja-dashboard">

    <header class="ja-dashboard-header">
        <div class="ja-dashboard-title">
            <h2>Panel de Jefe de Agencia</h2>

            <p>
                Supervisión general de arqueos, cumplimiento
                operativo y control de efectivo.
            </p>
        </div>

        <div class="ja-date">
            <span>Fecha operativa:</span>
            <strong>
                {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
            </strong>
        </div>
    </header>

    <section class="ja-summary">

        <article class="ja-stat" style="--stat-color:#164c96">
            <span class="ja-stat-label">
                Agencias asignadas
            </span>

            <strong class="ja-stat-value">
                {{ number_format($totalAgencias) }}
            </strong>

            <span class="ja-stat-note">
                Agencias autorizadas para consulta
            </span>
        </article>

        <article class="ja-stat" style="--stat-color:#00a651">
            <span class="ja-stat-label">
                Ventanillas activas
            </span>

            <strong class="ja-stat-value">
                {{ number_format($totalVentanillas) }}
            </strong>

            <span class="ja-stat-note">
                Total de ventanillas habilitadas
            </span>
        </article>

        <article class="ja-stat" style="--stat-color:#e4a12b">
            <span class="ja-stat-label">
                Jornadas pendientes de arqueo
            </span>

            <strong class="ja-stat-value">
                {{ number_format($jornadasPendientes) }}
            </strong>

            <span class="ja-stat-note">
                Jornadas de hoy sin arqueo válido
            </span>
        </article>

        <article class="ja-stat" style="--stat-color:#0a9461">
            <span class="ja-stat-label">
                Arqueos certificados
            </span>

            <strong class="ja-stat-value">
                {{ number_format($arqueosCertificados) }}
            </strong>

            <span class="ja-stat-note">
                Arqueos ordinarios válidos de hoy
            </span>
        </article>

    </section>

    <section class="ja-section-grid">

        <article class="ja-panel">

            <div class="ja-panel-header">
                <div>
                    <h3>Cumplimiento de arqueos</h3>
                    <p>
                        Jornadas programadas para la fecha operativa
                    </p>
                </div>
            </div>

            <div class="ja-panel-body">

                <div class="ja-progress-header">
                    <strong>
                        {{ number_format($porcentajeCumplimiento, 1) }}%
                    </strong>

                    <span>
                        Cumplimiento diario
                    </span>
                </div>

                <div
                    class="ja-progress-track"
                    role="progressbar"
                    aria-label="Cumplimiento de arqueos"
                    aria-valuenow="{{ $porcentajeCumplimiento }}"
                    aria-valuemin="0"
                    aria-valuemax="100"
                >
                    <div
                        class="ja-progress-fill"
                        style="width:{{ $porcentajeCumplimiento }}%"
                    ></div>
                </div>

                <div class="ja-progress-detail">
                    <span>
                        Jornadas registradas:
                        <strong>{{ $totalJornadas }}</strong>
                    </span>

                    <span>
                        Con arqueo:
                        <strong>{{ $jornadasCumplidas }}</strong>
                    </span>

                    <span>
                        Sin arqueo:
                        <strong>{{ $jornadasPendientes }}</strong>
                    </span>
                </div>

                @if($totalJornadas === 0)
                    <div class="ja-empty">
                        No existen jornadas registradas para
                        la fecha operativa actual.
                    </div>
                @endif

            </div>
        </article>

        <article class="ja-panel">

            <div class="ja-panel-header">
                <div>
                    <h3>Situación de los arqueos</h3>
                    <p>
                        Indicadores de control correspondientes a hoy
                    </p>
                </div>
            </div>

            <div class="ja-panel-body">
                <div class="ja-status-list">

                    <div class="ja-status-item">
                        <span>
                            Pendientes de certificación
                        </span>
                        <strong>
                            {{ $arqueosPendientesCertificacion }}
                        </strong>
                    </div>

                    <div class="ja-status-item">
                        <span>
                            Arqueos con diferencias
                        </span>
                        <strong>
                            {{ $arqueosConDiferencia }}
                        </strong>
                    </div>

                    <div class="ja-status-item">
                        <span>
                            Arqueos anulados
                        </span>
                        <strong>
                            {{ $arqueosAnulados }}
                        </strong>
                    </div>

                </div>
            </div>

        </article>

    </section>

    <section class="ja-panel">

        <div class="ja-panel-header">
            <div>
                <h3>Agencias bajo supervisión</h3>
                <p>
                    Dependencias asignadas al usuario autenticado
                </p>
            </div>
        </div>

        <div class="ja-panel-body">

            @if($agencias->isNotEmpty())
                <div class="ja-agency-list">
                    @foreach($agencias as $agencia)
                        <span class="ja-agency-tag">
                            {{ $agencia->nombre }}
                        </span>
                    @endforeach
                </div>
            @else
                <div class="ja-empty">
                    No tiene agencias activas asignadas.
                </div>
            @endif

        </div>

    </section>

    <section class="ja-panel">

        <div class="ja-panel-header">
            <div>
                <h3>Actividad reciente de arqueos</h3>
                <p>
                    Últimos diez registros de la fecha operativa
                </p>
            </div>
        </div>

        <div class="ja-table-scroll">
            <table class="ja-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Agencia</th>
                        <th>Ventanilla</th>
                        <th>Receptor</th>
                        <th>Turno</th>
                        <th>Tipo</th>
                        <th>Total arqueado</th>
                        <th>Diferencia</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($ultimosArqueos as $arqueo)

                        @php
                            $estado = strtoupper(
                                (string) $arqueo->estado
                            );

                            $badgeClass = match ($estado) {
                                'CERTIFICADO' => 'ja-badge-certified',
                                'PENDIENTE' => 'ja-badge-pending',
                                'ANULADO' => 'ja-badge-cancelled',
                                default => 'ja-badge-other',
                            };
                        @endphp

                        <tr>
                            <td>
                                <span class="ja-table-number">
                                    {{ $arqueo->numero_arqueo }}
                                </span>
                            </td>

                            <td>
                                {{ $arqueo->agencia_nombre_historico }}
                            </td>

                            <td>
                                {{ $arqueo->ventanilla_codigo_historico }}
                            </td>

                            <td>
                                {{ $arqueo->receptor_nombre_historico }}
                            </td>

                            <td>
                                {{ $arqueo->turno_nombre_historico ?: '—' }}
                            </td>

                            <td>
                                {{ $arqueo->tipo }}
                            </td>

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

                            <td>
                                <span class="ja-badge {{ $badgeClass }}">
                                    {{ $estado }}
                                </span>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="ja-empty">
                                    No se han registrado arqueos
                                    durante la fecha operativa actual.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </section>

    <div class="ja-readonly">
        <strong>Panel de supervisión:</strong>
        la información presentada es exclusivamente de consulta.
        La creación, certificación, modificación y anulación de
        arqueos corresponde a los roles autorizados.
    </div>

</div>

@endsection
