@extends('layouts.jefe')

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

    .btn-primary {
        border-color: #164c96;
        background: #164c96;
        color: #ffffff;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 20px;
    }

    .card {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .card + .card {
        margin-top: 20px;
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
        border: 4px solid rgba(255,255,255,.24);
        border-radius: 22px;
        background: rgba(255,255,255,.13);
        font-size: 24px;
        font-weight: 800;
    }

    .identity-header h3 {
        margin: 0;
        font-size: 18px;
    }

    .identity-header p {
        margin: 6px 0 0;
        color: rgba(255,255,255,.68);
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

    .card-body {
        padding: 19px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .stat-box {
        padding: 14px;
        border: 1px solid #e1e8ed;
        border-radius: 11px;
        background: #fbfcfd;
    }

    .stat-box span {
        display: block;
        color: #748596;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .stat-box strong {
        display: block;
        margin-top: 6px;
        color: #082d55;
        font-size: 21px;
    }

    .routes-table,
    .history-table {
        width: 100%;
        border-collapse: collapse;
    }

    .routes-table th,
    .routes-table td,
    .history-table th,
    .history-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        font-size: 9px;
    }

    .routes-table th,
    .history-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        text-transform: uppercase;
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

    @media (max-width: 950px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .stats-grid {
            grid-template-columns: 1fr;
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

    $iniciales = collect(
        preg_split('/\s+/', $nombreCompleto)
    )
        ->filter()
        ->take(2)
        ->map(
            fn ($parte) => mb_strtoupper(
                mb_substr($parte, 0, 1)
            )
        )
        ->implode('');
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Detalle del Promotor</h2>

        <p>
            Consulte sus rutas asignadas,
            actividad y arqueos recientes.
        </p>
    </div>

    <div class="page-actions">
        <a
            href="{{ route('jefe.promotores.index') }}"
            class="btn"
        >
            Regresar
        </a>

        <a
            href="{{ route(
                'jefe.arqueos-promotores.index',
                ['promotor_id' => $promotor->id]
            ) }}"
            class="btn btn-primary"
        >
            Ver todos los arqueos
        </a>
    </div>
</div>

<div class="profile-grid">
    <aside>
        <section class="card">
            <div class="identity-header">
                <div class="identity-avatar">
                    {{ $iniciales !== '' ? $iniciales : 'PR' }}
                </div>

                <h3>
                    {{ $nombreCompleto !== ''
                        ? $nombreCompleto
                        : $promotor->usuario }}
                </h3>

                <p>Promotor de Agentes MICOOPE</p>
            </div>

            <div class="identity-body">
                <div class="identity-row">
                    <span>Usuario</span>
                    <strong>
                        {{ $promotor->usuario }}
                    </strong>
                </div>

                <div class="identity-row">
                    <span>Estado</span>
                    <strong>
                        {{ $promotor->estado
                            ? 'ACTIVO'
                            : 'INACTIVO' }}
                    </strong>
                </div>

                <div class="identity-row">
                    <span>Rutas actuales</span>
                    <strong>{{ $rutas->count() }}</strong>
                </div>
            </div>
        </section>
    </aside>

    <main>
        <section class="card">
            <header class="card-header">
                <h3>Resumen de actividad</h3>
            </header>

            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-box">
                        <span>Total arqueos</span>
                        <strong>
                            {{ (int) $resumen->total_arqueos }}
                        </strong>
                    </div>

                    <div class="stat-box">
                        <span>Pendientes</span>
                        <strong>
                            {{ (int) $resumen->pendientes }}
                        </strong>
                    </div>

                    <div class="stat-box">
                        <span>Certificados</span>
                        <strong>
                            {{ (int) $resumen->certificados }}
                        </strong>
                    </div>

                    <div class="stat-box">
                        <span>Anulados</span>
                        <strong>
                            {{ (int) $resumen->anulados }}
                        </strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <header class="card-header">
                <h3>Rutas asignadas actualmente</h3>
            </header>

            <div style="overflow-x:auto;">
                <table class="routes-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Ruta</th>
                            <th>Región</th>
                            <th>Agentes activos</th>
                            <th>Fecha inicio</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($rutas as $ruta)
                            <tr>
                                <td>{{ $ruta->codigo }}</td>
                                <td>{{ $ruta->nombre }}</td>
                                <td>{{ $ruta->region_nombre }}</td>
                                <td>{{ $ruta->agentes_activos }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse(
                                        $ruta->fecha_inicio
                                    )->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    No tiene rutas asignadas actualmente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <header class="card-header">
                <h3>Últimos arqueos realizados</h3>
            </header>

            <div style="overflow-x:auto;">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Agente</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($ultimosArqueos as $arqueo)
                            <tr>
                                <td>
                                    {{ $arqueo->numero_arqueo }}
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
                                    <span class="badge">
                                        {{ str_replace(
                                            '_',
                                            ' ',
                                            $arqueo->estado
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    Q {{ number_format(
                                        (float) $arqueo->total_arqueado,
                                        2
                                    ) }}
                                </td>

                                <td>
                                    <a
                                        href="{{ route(
                                            'jefe.arqueos-promotores.show',
                                            $arqueo->id
                                        ) }}"
                                    >
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    No existen arqueos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection
