@extends('layouts.gerencia')

@section('title', 'Arqueos Anulados')
@section('module-title', 'Arqueos Anulados')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .stat-card {
        padding: 15px;
        border: 1px solid #e0e8ee;
        border-radius: 15px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .stat-card span {
        display: block;
        color: #758697;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .stat-card strong {
        display: block;
        margin-top: 7px;
        color: #082d55;
        font-size: 21px;
    }

    .warning-note {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 14px 16px;
        margin-bottom: 20px;
        border: 1px solid #efd0d0;
        border-radius: 13px;
        background: #fff8f8;
        color: #8b3b3b;
        font-size: 11px;
        line-height: 1.5;
    }

    .warning-note svg {
        flex: 0 0 auto;
        width: 17px;
        height: 17px;
        margin-top: 1px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .filters-card,
    .table-card {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .filters-card {
        margin-bottom: 20px;
        padding: 18px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .filters-grid + .filters-grid {
        margin-top: 10px;
    }

    .date-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #ffffff;
        color: #30485b;
        font-size: 11px;
        outline: none;
        box-sizing: border-box;
        transition: .2s ease;
    }

    .date-grid .form-control {
        padding: 0 10px;
    }

    .form-control:focus {
        border-color: #8eabc0;
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
    }

    .btn-primary {
        background: #164c96;
        color: #ffffff;
    }

    .btn-secondary {
        background: #edf2f5;
        color: #3d596f;
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
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

    .history-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 11px;
        border: 1px solid #f0d2cf;
        border-radius: 10px;
        background: #fff6f5;
        color: #a6403a;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .history-badge svg {
        width: 15px;
        height: 15px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .arqueos-table {
        width: 100%;
        min-width: 1400px;
        border-collapse: collapse;
    }

    .arqueos-table th,
    .arqueos-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        color: #30485b;
        font-size: 10px;
        text-align: left;
        vertical-align: middle;
    }

    .arqueos-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .arqueos-table tbody tr {
        transition: .18s ease;
    }

    .arqueos-table tbody tr:hover {
        background: #fbfcfd;
    }

    .number-cell {
        color: #163f66;
        font-weight: 850;
        white-space: nowrap;
    }

    .agent-code {
        color: #164c96;
        font-weight: 850;
    }

    .agent-business {
        color: #536b7d;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 850;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge.type-agent {
        background: #edf5fb;
        color: #164c96;
    }

    .badge.type-promotor {
        background: #eef7f2;
        color: #28704d;
    }

    .badge.type-other {
        background: #f1f3f5;
        color: #5e6d79;
    }

    .money-cell {
        color: #334e62;
        font-weight: 700;
        white-space: nowrap;
    }

    .difference-cell {
        white-space: nowrap;
    }

    .extemporaneo-cell {
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
        box-sizing: border-box;
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

    .icon-button.print:hover {
        border-color: #9db9ce;
        background: #e2f0fa;
        color: #123f79;
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

    .empty-state {
        padding: 48px 20px;
        color: #7d8b95;
        text-align: center;
        font-size: 11px;
    }

    .empty-state svg {
        display: block;
        width: 34px;
        height: 34px;
        margin: 0 auto 10px;
        stroke: #9aabb8;
        fill: none;
        stroke-width: 1.6;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .pagination {
        padding: 16px 18px;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .filters-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 800px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }

        .filters-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }

        .table-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .history-badge {
            white-space: normal;
        }
    }

    @media (max-width: 560px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .date-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h2>Arqueos Anulados</h2>

        <p>
            Consulte los arqueos que fueron anulados y su información
            original para fines de supervisión y trazabilidad.
        </p>
    </div>
</div>

<div class="stats-grid">
    @foreach([
        ['Total Anulados', $totalAnulados],
        ['Arqueos Agente', $anuladosAgente],
        ['Arqueos Promotor', $anuladosPromotor],
        ['Extemporáneos', $anuladosExtemporaneos],
        ['Fecha de Hoy', $anuladosHoy],
    ] as [$label,$value])
        <div class="stat-card">
            <span>
                {{ $label }}
            </span>

            <strong>
                {{ $value }}
            </strong>
        </div>
    @endforeach
</div>

<div class="warning-note">
    <svg viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="9"></circle>
        <path d="M12 8v5"></path>
        <path d="M12 17h.01"></path>
    </svg>

    <div>
        Los arqueos anulados se conservan únicamente para consulta e historial.
        Gerencia no puede modificar, reactivar ni rectificar un arqueo anulado.
    </div>
</div>

<section class="filters-card">
    <form
        method="GET"
        action="{{ route('gerencia.arqueos-anulados.index') }}"
    >
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                class="form-control"
                value="{{ $buscar }}"
                placeholder="Número, Agente, responsable, Ruta o Región"
            >

            <select
                name="tipo"
                class="form-control"
            >
                <option value="">Todos los tipos</option>

                <option
                    value="DIARIO_AGENTE"
                    @selected($tipo === 'DIARIO_AGENTE')
                >
                    Arqueos de Agente
                </option>

                <option
                    value="VISITA_PROMOTOR"
                    @selected($tipo === 'VISITA_PROMOTOR')
                >
                    Arqueos de Promotor
                </option>
            </select>

            <select
                name="extemporaneo"
                class="form-control"
            >
                <option value="">Todos</option>

                <option
                    value="SI"
                    @selected($extemporaneo === 'SI')
                >
                    Solo extemporáneos
                </option>

                <option
                    value="NO"
                    @selected($extemporaneo === 'NO')
                >
                    Solo ordinarios
                </option>
            </select>

            <select
                name="agente_id"
                class="form-control"
            >
                <option value="">Todos los Agentes</option>

                @foreach($agentes as $agente)
                    <option
                        value="{{ $agente->id }}"
                        @selected(
                            $agenteId === (int) $agente->id
                        )
                    >
                        {{ $agente->codigo_agente }}
                        — {{ $agente->nombre_negocio }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filters-grid">
            <select
                name="promotor_id"
                class="form-control"
            >
                <option value="">Todos los Promotores</option>

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
                        @selected(
                            $promotorId === (int) $promotor->id
                        )
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
                <option value="">Todas las Regiones</option>

                @foreach($regiones as $region)
                    <option
                        value="{{ $region->id }}"
                        @selected(
                            $regionId === (int) $region->id
                        )
                    >
                        {{ $region->nombre }}
                    </option>
                @endforeach
            </select>

            <select
                name="ruta_id"
                class="form-control"
            >
                <option value="">Todas las Rutas</option>

                @foreach($rutas as $ruta)
                    <option
                        value="{{ $ruta->id }}"
                        @selected(
                            $rutaId === (int) $ruta->id
                        )
                    >
                        {{ $ruta->codigo }}
                        — {{ $ruta->nombre }}
                    </option>
                @endforeach
            </select>

            <div class="date-grid">
                <input
                    type="date"
                    name="desde"
                    class="form-control"
                    value="{{ $desde }}"
                >

                <input
                    type="date"
                    name="hasta"
                    class="form-control"
                    value="{{ $hasta }}"
                >
            </div>
        </div>

        <div class="filters-actions">
            <a
                href="{{ route(
                    'gerencia.arqueos-anulados.index'
                ) }}"
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
        <div>
            <h3>
                Historial de Arqueos Anulados
            </h3>

            <p>
                Los registros permanecen disponibles para auditoría y consulta.
            </p>
        </div>

        <div class="history-badge">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M12 7v5l3 2"></path>
            </svg>

            Registros conservados para trazabilidad
        </div>
    </header>

    <div class="table-responsive">
        <table class="arqueos-table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Agente</th>
                    <th>Responsable original</th>
                    <th>Región</th>
                    <th>Ruta</th>
                    <th>Saldo Sistema</th>
                    <th>Arqueado</th>
                    <th>Diferencia</th>
                    <th>Extemporáneo</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($arqueos as $arqueo)
                    @php
                        $responsable = trim(
                            ($arqueo->responsable_nombres ?? '')
                            . ' '
                            . ($arqueo->responsable_apellidos ?? '')
                        );

                        $diferencia = (float) $arqueo->diferencia;
                    @endphp

                    <tr>
                        <td class="number-cell">
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
                            <span
                                class="badge
                                    {{ $arqueo->tipo === 'DIARIO_AGENTE'
                                        ? 'type-agent'
                                        : (
                                            $arqueo->tipo === 'VISITA_PROMOTOR'
                                                ? 'type-promotor'
                                                : 'type-other'
                                        ) }}"
                            >
                                {{ $arqueo->tipo === 'DIARIO_AGENTE'
                                    ? 'Agente'
                                    : (
                                        $arqueo->tipo === 'VISITA_PROMOTOR'
                                            ? 'Promotor'
                                            : str_replace(
                                                '_',
                                                ' ',
                                                $arqueo->tipo
                                            )
                                    ) }}
                            </span>
                        </td>

                        <td>
                            <span class="agent-code">
                                {{ $arqueo->codigo_agente }}
                            </span>

                            <span class="agent-business">
                                — {{ $arqueo->nombre_negocio }}
                            </span>
                        </td>

                        <td>
                            {{ $responsable !== ''
                                ? $responsable
                                : (
                                    $arqueo->responsable_usuario
                                    ?? '—'
                                ) }}
                        </td>

                        <td>
                            {{ $arqueo->region_nombre ?? '—' }}
                        </td>

                        <td>
                            {{ $arqueo->ruta_codigo }}
                            — {{ $arqueo->ruta_nombre }}
                        </td>

                        <td class="money-cell">
                            Q {{ number_format(
                                (float) $arqueo->saldo_sistema,
                                2
                            ) }}
                        </td>

                        <td class="money-cell">
                            Q {{ number_format(
                                (float) $arqueo->total_arqueado,
                                2
                            ) }}
                        </td>

                        <td class="difference-cell">
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
                        </td>

                        <td class="extemporaneo-cell">
                            {{ $arqueo->fuera_fecha_ordinaria
                                ? 'Sí'
                                : 'No' }}
                        </td>

                        <td class="actions-cell">
                            <div class="table-actions">
                                <a
                                    href="{{ route(
                                        'gerencia.arqueos.show',
                                        $arqueo->id
                                    ) }}"
                                    class="icon-button"
                                    title="Ver detalle"
                                    aria-label="Ver detalle del arqueo"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="2.8"
                                        ></circle>
                                    </svg>
                                </a>

                                <a
                                    href="{{ route(
                                        'gerencia.arqueos.imprimir',
                                        $arqueo->id
                                    ) }}"
                                    target="_blank"
                                    class="icon-button print"
                                    title="Imprimir PDF"
                                    aria-label="Imprimir arqueo en PDF"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M6 9V3h12v6"></path>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                        <rect
                                            x="6"
                                            y="14"
                                            width="12"
                                            height="7"
                                        ></rect>
                                        <path d="M18 12h.01"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">
                            <div class="empty-state">
                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                    ></circle>
                                    <path d="M9 12l2 2 4-4"></path>
                                </svg>

                                No se encontraron arqueos anulados.
                            </div>
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
@endsection
