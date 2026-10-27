@extends('layouts.gerencia')

@section('title', 'Rutas Asignadas')
@section('module-title', 'Rutas Asignadas')

@push('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 22px;
    }

    .summary-card,
    .filters-card,
    .table-card {
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .summary-card {
        padding: 18px;
    }

    .summary-card span {
        display: block;
        color: #768692;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .summary-card strong {
        display: block;
        margin-top: 8px;
        color: #082d55;
        font-size: 25px;
    }

    .summary-card.success {
        border-color: #c5e5d1;
        background: #f7fcf9;
    }

    .summary-card.warning {
        border-color: #efd99f;
        background: #fffdf6;
    }

    .summary-card.danger {
        border-color: #efc5c5;
        background: #fffafa;
    }

    .summary-card.info {
        border-color: #cbdff1;
        background: #f7fbff;
    }

    .filters-card {
        margin-bottom: 20px;
        padding: 18px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) 230px 200px 180px;
        gap: 11px;
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #ffffff;
        color: #30485b;
        box-sizing: border-box;
        outline: none;
    }

    .form-control:focus {
        border-color: #8eb0cb;
        box-shadow: 0 0 0 3px rgba(22, 76, 150, .08);
    }

    .filters-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 13px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 15px;
        border: 0;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        background: #164c96;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #123f7e;
    }

    .btn-secondary {
        background: #edf2f5;
        color: #3d596f;
    }

    .btn-secondary:hover {
        background: #e2e9ee;
    }

    .table-card {
        overflow: hidden;
    }

    .table-header {
        padding: 18px 20px;
        border-bottom: 1px solid #edf1f4;
        background: #fafcfd;
    }

    .table-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 15px;
    }

    .table-header p {
        margin: 5px 0 0;
        color: #82909a;
        font-size: 10px;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .assignments-table {
        width: 100%;
        min-width: 1250px;
        border-collapse: collapse;
    }

    .assignments-table th,
    .assignments-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        color: #30485b;
        font-size: 10px;
    }

    .assignments-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .assignments-table tbody tr {
        transition: background .15s ease;
    }

    .assignments-table tbody tr:hover {
        background: #fbfdfe;
    }

    .assignments-table td strong {
        color: #173b59;
    }

    .badge {
        display: inline-flex;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge.active {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .badge.finished {
        background: #edf0f2;
        color: #596a78;
    }

    .badge.scheduled {
        background: #eee9ff;
        color: #6345a2;
    }

    .progress-wrap {
        min-width: 120px;
    }

    .progress-text {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        color: #64798a;
        font-size: 8px;
        font-weight: 800;
    }

    .progress-bar {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf1f4;
    }

    .progress-value {
        height: 100%;
        border-radius: 999px;
        background: #2c72b7;
    }

    .empty-state {
        padding: 35px;
        color: #7d8b95;
        text-align: center;
        font-size: 10px;
    }

    .pagination {
        padding: 16px 18px;
    }

    @media (max-width: 1200px) {
        .summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 1100px) {
        .filters-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .summary-grid,
        .filters-grid {
            grid-template-columns: 1fr;
        }

        .filters-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h2>Rutas Asignadas a Promotores</h2>

        <p>
            Consulte la distribución vigente e histórica de Rutas,
            Promotores responsables y cobertura de Agentes.
        </p>
    </div>
</div>

<section class="summary-grid">
    <article class="summary-card">
        <span>Total Asignaciones</span>
        <strong>{{ $totalAsignaciones }}</strong>
    </article>

    <article class="summary-card success">
        <span>Asignaciones Activas</span>
        <strong>{{ $totalAsignacionesActivas }}</strong>
    </article>

    <article class="summary-card warning">
        <span>Rutas sin Promotor</span>
        <strong>{{ $rutasSinPromotor }}</strong>
    </article>

    <article class="summary-card success">
        <span>Promotores con Ruta</span>
        <strong>{{ $promotoresConRuta }}</strong>
    </article>

    <article class="summary-card danger">
        <span>Promotores sin Ruta</span>
        <strong>{{ $promotoresSinRuta }}</strong>
    </article>
</section>

<section class="filters-card">
    <form
        method="GET"
        action="{{ route('gerencia.rutas-promotores.index') }}"
    >
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                value="{{ $buscar }}"
                placeholder="Ruta, Región, Promotor o usuario"
                class="form-control"
            >

            <select
                name="promotor_id"
                class="form-control"
            >
                <option value="">
                    Todos los Promotores
                </option>

                @foreach($promotores as $promotor)
                    @php
                        $nombrePromotor = trim(
                            ($promotor->nombres ?? '')
                            . ' '
                            . ($promotor->apellidos ?? '')
                        );
                    @endphp

                    <option
                        value="{{ $promotor->id }}"
                        @selected($promotorId === (int) $promotor->id)
                    >
                        {{ $nombrePromotor !== ''
                            ? $nombrePromotor
                            : $promotor->usuario }}
                    </option>
                @endforeach
            </select>

            <select
                name="region_id"
                class="form-control"
            >
                <option value="">
                    Todas las Regiones
                </option>

                @foreach($regiones as $region)
                    <option
                        value="{{ $region->id }}"
                        @selected($regionId === (int) $region->id)
                    >
                        {{ $region->nombre }}
                    </option>
                @endforeach
            </select>

            <select
                name="estado"
                class="form-control"
            >
                <option value="">
                    Todos los estados
                </option>

                <option
                    value="ACTIVA"
                    @selected($estado === 'ACTIVA')
                >
                    Activas
                </option>

                <option
                    value="PROGRAMADA"
                    @selected($estado === 'PROGRAMADA')
                >
                    Programadas
                </option>

                <option
                    value="FINALIZADA"
                    @selected($estado === 'FINALIZADA')
                >
                    Finalizadas
                </option>
            </select>
        </div>

        <div class="filters-actions">
            <a
                href="{{ route('gerencia.rutas-promotores.index') }}"
                class="btn btn-secondary"
            >
                Limpiar
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Aplicar filtros
            </button>
        </div>
    </form>
</section>

<section class="table-card">
    <header class="table-header">
        <h3>Asignaciones de Rutas</h3>

        <p>
            Información de consulta para supervisar la cobertura territorial.
        </p>
    </header>

    <div class="table-responsive">
        <table class="assignments-table">
            <thead>
                <tr>
                    <th>Promotor</th>
                    <th>Región</th>
                    <th>Ruta</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estado</th>
                    <th>Agentes Activos</th>
                    <th>Arqueados Hoy</th>
                    <th>Cumplimiento</th>
                </tr>
            </thead>

            <tbody>
                @forelse($asignaciones as $asignacion)
                    @php
                        $nombrePromotor = trim(
                            ($asignacion->promotor_nombres ?? '')
                            . ' '
                            . ($asignacion->promotor_apellidos ?? '')
                        );

                        $esProgramada =
                            (bool) $asignacion->asignacion_estado
                            && \Carbon\Carbon::parse(
                                $asignacion->fecha_inicio
                            )->isFuture();

                        $esActiva =
                            (bool) $asignacion->asignacion_estado
                            && ! $esProgramada
                            && (
                                ! $asignacion->fecha_fin
                                || \Carbon\Carbon::parse(
                                    $asignacion->fecha_fin
                                )->endOfDay()->gte(now())
                            );

                        if ($esActiva) {
                            $estadoTexto = 'ACTIVA';
                        } elseif ($esProgramada) {
                            $estadoTexto = 'PROGRAMADA';
                        } else {
                            $estadoTexto = 'FINALIZADA';
                        }

                        $agentesActivos = (int) $asignacion->agentes_activos;
                        $arqueadosHoy = (int) $asignacion->agentes_arqueados_hoy;

                        $cumplimiento =
                            $agentesActivos > 0
                                ? round(
                                    ($arqueadosHoy / $agentesActivos) * 100,
                                    1
                                )
                                : 0;
                    @endphp

                    <tr>
                        <td>
                            <strong>
                                {{ $nombrePromotor !== ''
                                    ? $nombrePromotor
                                    : $asignacion->promotor_usuario }}
                            </strong>
                        </td>

                        <td>
                            {{ $asignacion->region_nombre }}
                        </td>

                        <td>
                            <strong>
                                {{ $asignacion->ruta_codigo }}
                                — {{ $asignacion->ruta_nombre }}
                            </strong>
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse(
                                $asignacion->fecha_inicio
                            )->format('d/m/Y') }}
                        </td>

                        <td>
                            {{ $asignacion->fecha_fin
                                ? \Carbon\Carbon::parse(
                                    $asignacion->fecha_fin
                                )->format('d/m/Y')
                                : 'Indefinida' }}
                        </td>

                        <td>
                            <span
                                class="badge {{
                                    $estadoTexto === 'ACTIVA'
                                        ? 'active'
                                        : (
                                            $estadoTexto === 'PROGRAMADA'
                                                ? 'scheduled'
                                                : 'finished'
                                        )
                                }}"
                            >
                                {{ $estadoTexto }}
                            </span>
                        </td>

                        <td>
                            {{ $agentesActivos }}
                        </td>

                        <td>
                            {{ $arqueadosHoy }}
                        </td>

                        <td>
                            <div class="progress-wrap">
                                <div class="progress-text">
                                    <span>
                                        {{ number_format($cumplimiento, 1) }}%
                                    </span>

                                    <span>
                                        {{ $arqueadosHoy }}/{{ $agentesActivos }}
                                    </span>
                                </div>

                                <div class="progress-bar">
                                    <div
                                        class="progress-value"
                                        style="width:{{ min(100, $cumplimiento) }}%;"
                                    ></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="9"
                            class="empty-state"
                        >
                            No se encontraron asignaciones
                            para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($asignaciones->hasPages())
        <div class="pagination">
            {{ $asignaciones->links() }}
        </div>
    @endif
</section>
@endsection
