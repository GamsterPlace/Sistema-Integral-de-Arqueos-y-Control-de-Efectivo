@extends('layouts.jefe')

@section('title', 'Rutas Asignadas')
@section('module-title', 'Rutas Asignadas')

@push('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
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

    .summary-card { padding: 18px; }

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

    .summary-card.warning {
        border-color: #efd99f;
        background: #fffdf6;
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
        grid-template-columns: minmax(240px,1fr) 230px 200px 180px;
        gap: 11px;
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #fff;
        color: #30485b;
    }

    .filters-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 13px;
    }

    .btn,
    .action-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        text-decoration: none;
    }

    .btn {
        min-height: 40px;
        padding: 0 15px;
        border: 0;
        border-radius: 10px;
        font-size: 10px;
    }

    .btn-primary { background:#164c96; color:#fff; }
    .btn-secondary { background:#edf2f5; color:#3d596f; }

    .table-card { overflow: hidden; }

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

    .table-responsive { overflow-x:auto; }

    .assignments-table {
        width: 100%;
        min-width: 1280px;
        border-collapse: collapse;
    }

    .assignments-table th,
    .assignments-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        font-size: 10px;
    }

    .assignments-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        text-transform: uppercase;
    }

    .badge {
        display:inline-flex;
        padding:6px 9px;
        border-radius:999px;
        font-size:8px;
        font-weight:800;
        text-transform:uppercase;
    }

    .badge.active { background:#eaf8ef;color:#1d7b4e; }
    .badge.finished { background:#edf0f2;color:#596a78; }
    .badge.scheduled { background:#eee9ff;color:#6345a2; }

    .actions { display:flex;gap:7px; }

    .action-link {
        min-height:34px;
        padding:0 10px;
        border:1px solid #d5dfe5;
        border-radius:9px;
        background:#fff;
        color:#31536e;
        font-size:9px;
    }

    .action-link.primary {
        border-color:#cbdceb;
        background:#edf5fb;
        color:#164c96;
    }

    .progress-wrap { min-width:120px; }

    .progress-text {
        display:flex;
        justify-content:space-between;
        margin-bottom:5px;
        color:#64798a;
        font-size:8px;
        font-weight:800;
    }

    .progress-bar {
        height:7px;
        overflow:hidden;
        border-radius:999px;
        background:#edf1f4;
    }

    .progress-value {
        height:100%;
        border-radius:999px;
        background:#2c72b7;
    }

    .pagination { padding:16px 18px; }

    @media (max-width:1100px) {
        .summary-grid,
        .filters-grid {
            grid-template-columns:repeat(2,1fr);
        }
    }

    @media (max-width:700px) {
        .summary-grid,
        .filters-grid {
            grid-template-columns:1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h2>Rutas Asignadas a Promotores</h2>
        <p>
            Supervise las asignaciones de rutas, los Promotores responsables
            y el cumplimiento diario de los Agentes.
        </p>
    </div>
</div>

<section class="summary-grid">
    <article class="summary-card">
        <span>Asignaciones activas</span>
        <strong>{{ $totalAsignacionesActivas }}</strong>
    </article>

    <article class="summary-card">
        <span>Promotores con ruta</span>
        <strong>{{ $promotoresConRuta }}</strong>
    </article>

    <article class="summary-card warning">
        <span>Promotores sin ruta</span>
        <strong>{{ $promotoresSinRuta }}</strong>
    </article>

    <article class="summary-card info">
        <span>Rutas sin Promotor</span>
        <strong>{{ $rutasSinPromotor }}</strong>
    </article>
</section>

<section class="filters-card">
    <form method="GET" action="{{ route('jefe.rutas-promotores.index') }}">
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                class="form-control"
                value="{{ $buscar }}"
                placeholder="Ruta, región o Promotor"
            >

            <select name="promotor_id" class="form-control">
                <option value="">Todos los Promotores</option>
                @foreach ($promotores as $promotor)
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
                        {{ $nombrePromotor !== '' ? $nombrePromotor : $promotor->nombre_usuario }}
                    </option>
                @endforeach
            </select>

            <select name="region_id" class="form-control">
                <option value="">Todas las regiones</option>
                @foreach ($regiones as $region)
                    <option
                        value="{{ $region->id }}"
                        @selected($regionId === (int) $region->id)
                    >
                        {{ $region->nombre }}
                    </option>
                @endforeach
            </select>

            <select name="estado" class="form-control">
                <option value="">Todos los estados</option>
                <option value="ACTIVA" @selected($estado === 'ACTIVA')>Activas</option>
                <option value="PROGRAMADA" @selected($estado === 'PROGRAMADA')>Programadas</option>
                <option value="FINALIZADA" @selected($estado === 'FINALIZADA')>Finalizadas</option>
            </select>
        </div>

        <div class="filters-actions">
            <a href="{{ route('jefe.rutas-promotores.index') }}" class="btn btn-secondary">
                Limpiar
            </a>
            <button type="submit" class="btn btn-primary">
                Aplicar filtros
            </button>
        </div>
    </form>
</section>

<section class="table-card">
    <header class="table-header">
        <h3>Asignaciones de rutas</h3>
        <p>
            Historial y estado operativo de las rutas asignadas a Promotores.
        </p>
    </header>

    <div class="table-responsive">
        <table class="assignments-table">
            <thead>
                <tr>
                    <th>Promotor</th>
                    <th>Región</th>
                    <th>Ruta</th>
                    <th>Agentes activos</th>
                    <th>Arqueados hoy</th>
                    <th>Cumplimiento</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($asignaciones as $asignacion)
                    @php
                        $nombrePromotor = trim(
                            ($asignacion->promotor_nombres ?? '')
                            . ' '
                            . ($asignacion->promotor_apellidos ?? '')
                        );

                        $activos = (int) $asignacion->agentes_activos;
                        $arqueados = (int) $asignacion->agentes_arqueados_hoy;
                        $porcentaje = $activos > 0
                            ? round(($arqueados / $activos) * 100)
                            : 0;

                        $inicio = \Carbon\Carbon::parse($asignacion->fecha_inicio);
                        $fin = $asignacion->fecha_fin
                            ? \Carbon\Carbon::parse($asignacion->fecha_fin)
                            : null;
                        $hoy = \Carbon\Carbon::today();

                        if ($asignacion->asignacion_estado && $inicio->gt($hoy)) {
                            $estadoTexto = 'Programada';
                            $estadoClase = 'scheduled';
                        } elseif (
                            $asignacion->asignacion_estado
                            && (! $fin || $fin->gte($hoy))
                        ) {
                            $estadoTexto = 'Activa';
                            $estadoClase = 'active';
                        } else {
                            $estadoTexto = 'Finalizada';
                            $estadoClase = 'finished';
                        }
                    @endphp

                    <tr>
                        <td>
                            <strong>
                                {{ $nombrePromotor !== ''
                                    ? $nombrePromotor
                                    : $asignacion->promotor_usuario }}
                            </strong>
                        </td>

                        <td>{{ $asignacion->region_nombre }}</td>

                        <td>
                            <strong>
                                {{ $asignacion->ruta_codigo }}
                                — {{ $asignacion->ruta_nombre }}
                            </strong>
                        </td>

                        <td>{{ $activos }}</td>
                        <td>{{ $arqueados }}</td>

                        <td>
                            <div class="progress-wrap">
                                <div class="progress-text">
                                    <span>{{ $porcentaje }}%</span>
                                    <span>{{ $arqueados }}/{{ $activos }}</span>
                                </div>

                                <div class="progress-bar">
                                    <div
                                        class="progress-value"
                                        style="width:{{ min(100, $porcentaje) }}%;"
                                    ></div>
                                </div>
                            </div>
                        </td>

                        <td>{{ $inicio->format('d/m/Y') }}</td>

                        <td>
                            {{ $fin ? $fin->format('d/m/Y') : 'Sin fecha fin' }}
                        </td>

                        <td>
                            <span class="badge {{ $estadoClase }}">
                                {{ $estadoTexto }}
                            </span>
                        </td>

                        <td>
                            <div class="actions">
                                <a
                                    href="{{ route(
                                        'jefe.promotores.show',
                                        $asignacion->promotor_usuario_id
                                    ) }}"
                                    class="action-link primary"
                                >
                                    Ver Promotor
                                </a>

                                <a
                                    href="{{ route(
                                        'jefe.agentes-ruta.index',
                                        ['ruta_id' => $asignacion->ruta_id]
                                    ) }}"
                                    class="action-link"
                                >
                                    Ver Agentes
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:35px;">
                            No se encontraron asignaciones.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($asignaciones->hasPages())
        <div class="pagination">
            {{ $asignaciones->links() }}
        </div>
    @endif
</section>
@endsection
