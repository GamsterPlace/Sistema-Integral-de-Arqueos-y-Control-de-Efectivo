@extends('layouts.gerencia')

@section('title', 'Detalle del Promotor')
@section('module-title', 'Promotores')

@push('styles')
<style>
    .page-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 15px;
        border: 1px solid #d5dfe5;
        border-radius: 10px;
        background: #ffffff;
        color: #31536e;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 20px;
    }

    .content-stack {
        display: grid;
        gap: 20px;
        min-width: 0;
    }

    .card {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .identity-header {
        padding: 27px 20px 23px;
        background: linear-gradient(
            145deg,
            #0b315f,
            #164c96
        );
        color: #ffffff;
        text-align: center;
    }

    .identity-avatar {
        width: 82px;
        height: 82px;
        display: grid;
        place-items: center;
        margin: 0 auto 14px;
        border: 4px solid rgba(255, 255, 255, .24);
        border-radius: 22px;
        background: rgba(255, 255, 255, .13);
        font-size: 24px;
        font-weight: 800;
    }

    .identity-header h3 {
        margin: 0;
        font-size: 18px;
    }

    .identity-header p {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .68);
        font-size: 10px;
    }

    .identity-body {
        padding: 17px;
    }

    .identity-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px solid #edf1f4;
    }

    .identity-row:last-child {
        border-bottom: 0;
    }

    .identity-row span {
        color: #738493;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .identity-row strong {
        color: #173b59;
        font-size: 11px;
        text-align: right;
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
    }

    .card-body {
        padding: 19px;
    }

    .route-list {
        display: grid;
        gap: 10px;
    }

    .route-item {
        padding: 14px;
        border: 1px solid #e1e8ed;
        border-radius: 11px;
        background: #fbfcfd;
    }

    .route-item strong {
        display: block;
        color: #173b59;
        font-size: 11px;
    }

    .route-item span {
        display: block;
        margin-top: 6px;
        color: #718493;
        font-size: 9px;
        line-height: 1.5;
    }

    .empty-state {
        padding: 20px;
        border: 1px dashed #d7e1e7;
        border-radius: 11px;
        background: #fbfcfd;
        color: #7d8b95;
        font-size: 10px;
        text-align: center;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        min-width: 950px;
        border-collapse: collapse;
    }

    .history-table th,
    .history-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        font-size: 9px;
    }

    .history-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .history-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .history-table tbody tr:hover {
        background: #fbfdfe;
    }

    .history-table td strong {
        color: #173b59;
    }

    .badge {
        display: inline-flex;
        padding: 6px 9px;
        border-radius: 999px;
        background: #edf2f6;
        color: #50667a;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .diff-negative {
        color: #b33a34;
        font-weight: 800;
    }

    .diff-positive {
        color: #16834f;
        font-weight: 800;
    }

    .diff-zero {
        color: #607586;
        font-weight: 800;
    }

    .pagination {
        padding: 16px 18px;
        border-top: 1px solid #edf1f4;
    }

    @media (max-width: 950px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .page-actions {
            width: 100%;
        }

        .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $nombreCompleto = trim(
        ($promotor->nombres ?? '')
        . ' '
        . ($promotor->apellidos ?? '')
    );
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>
            {{ $nombreCompleto !== ''
                ? $nombreCompleto
                : $promotor->usuario }}
        </h2>

        <p>
            Consulta de asignaciones y actividad del Promotor.
        </p>
    </div>

    <div class="page-actions">
        <a
            href="{{ route('gerencia.promotores.index') }}"
            class="btn"
        >
            Regresar
        </a>
    </div>
</div>

<div class="profile-grid">
    <aside>
        <section class="card">
            <div class="identity-header">
                <div class="identity-avatar">
                    {{ mb_strtoupper(
                        mb_substr(
                            $nombreCompleto !== ''
                                ? $nombreCompleto
                                : $promotor->usuario,
                            0,
                            1
                        )
                    ) }}
                </div>

                <h3>
                    {{ $nombreCompleto !== ''
                        ? $nombreCompleto
                        : $promotor->usuario }}
                </h3>

                <p>
                    Promotor de Agentes MICOOPE
                </p>
            </div>

            <div class="identity-body">
                <div class="identity-row">
                    <span>Usuario</span>

                    <strong>
                        {{ $promotor->usuario }}
                    </strong>
                </div>

                <div class="identity-row">
                    <span>Nombres</span>

                    <strong>
                        {{ $promotor->nombres ?: '—' }}
                    </strong>
                </div>

                <div class="identity-row">
                    <span>Apellidos</span>

                    <strong>
                        {{ $promotor->apellidos ?: '—' }}
                    </strong>
                </div>

                <div class="identity-row">
                    <span>Estado</span>

                    <strong>
                        {{ $promotor->estado }}
                    </strong>
                </div>
            </div>
        </section>
    </aside>

    <main class="content-stack">
        <section class="card">
            <header class="card-header">
                <h3>Rutas Asignadas</h3>

                <p>
                    Asignaciones activas vigentes a la fecha.
                </p>
            </header>

            <div class="card-body">
                <div class="route-list">
                    @forelse($rutas as $ruta)
                        <article class="route-item">
                            <strong>
                                {{ $ruta->ruta_codigo }}
                                — {{ $ruta->ruta_nombre }}
                            </strong>

                            <span>
                                Región: {{ $ruta->region_nombre }}
                            </span>

                            <span>
                                Agentes activos:
                                {{ (int) $ruta->agentes_activos }}
                            </span>

                            <span>
                                Desde:
                                {{ \Carbon\Carbon::parse(
                                    $ruta->fecha_inicio
                                )->format('d/m/Y') }}

                                @if($ruta->fecha_fin)
                                    · Hasta:
                                    {{ \Carbon\Carbon::parse(
                                        $ruta->fecha_fin
                                    )->format('d/m/Y') }}
                                @endif
                            </span>
                        </article>
                    @empty
                        <div class="empty-state">
                            El Promotor no tiene Rutas activas asignadas.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="card">
            <header class="card-header">
                <h3>Arqueos Realizados</h3>

                <p>
                    Historial de visitas de arqueo realizadas por este Promotor.
                </p>
            </header>

            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Agente</th>
                            <th>Región</th>
                            <th>Ruta</th>
                            <th>Estado</th>
                            <th>Diferencia</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($arqueos as $arqueo)
                            @php
                                $diferencia = (float) $arqueo->diferencia;

                                $clase =
                                    $diferencia < 0
                                        ? 'diff-negative'
                                        : (
                                            $diferencia > 0
                                                ? 'diff-positive'
                                                : 'diff-zero'
                                        );
                            @endphp

                            <tr>
                                <td>
                                    <strong>
                                        {{ $arqueo->numero_arqueo }}
                                    </strong>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse(
                                        $arqueo->fecha_arqueo
                                    )->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $arqueo->codigo_agente }}
                                    — {{ $arqueo->nombre_negocio }}
                                </td>

                                <td>
                                    {{ $arqueo->region_nombre ?? '—' }}
                                </td>

                                <td>
                                    {{ $arqueo->ruta_nombre ?? '—' }}
                                </td>

                                <td>
                                    <span class="badge">
                                        {{ str_replace(
                                            '_',
                                            ' ',
                                            $arqueo->estado
                                        ) }}
                                    </span>
                                </td>

                                <td>
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="empty-state"
                                >
                                    El Promotor no tiene arqueos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($arqueos->hasPages())
                <div class="pagination">
                    {{ $arqueos->links() }}
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
