@extends('layouts.gerencia')

@section('title', 'Todos los Arqueos')
@section('module-title', 'Todos los Arqueos')

@push('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .summary-card,
    .filters-card,
    .table-card {
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .summary-card {
        padding: 17px;
    }

    .summary-card span {
        display: block;
        color: #768692;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .summary-card strong {
        display: block;
        margin-top: 8px;
        color: #082d55;
        font-size: 23px;
    }

    .summary-card.warning {
        border-color: #efd99f;
        background: #fffdf6;
    }

    .summary-card.success {
        border-color: #c4e4d1;
        background: #f7fcf9;
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
        padding: 18px;
        margin-bottom: 20px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 11px;
    }

    .filters-grid + .filters-grid {
        margin-top: 11px;
    }

    .filters-grid.dates {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .form-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #fff;
        color: #30485b;
        outline: none;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #82a9cc;
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
        color: #fff;
    }

    .btn-secondary {
        background: #edf2f5;
        color: #3d596f;
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

    .arqueos-table {
        width: 100%;
        min-width: 1550px;
        border-collapse: collapse;
    }

    .arqueos-table th,
    .arqueos-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
        font-size: 10px;
    }

    .arqueos-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .arqueos-table tbody tr:hover {
        background: #fbfdfe;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 9px;
        border-radius: 999px;
        background: #edf2f6;
        color: #50667a;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge.extra {
        background: #eee9ff;
        color: #6345a2;
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

    .empty-state {
        padding: 44px 20px;
        color: #7d8b95;
        text-align: center;
        font-size: 11px;
    }

    .pagination {
        padding: 16px 18px;
    }

    @media (max-width: 1250px) {
        .summary-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 1000px) {
        .summary-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .filters-grid,
        .filters-grid.dates {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 700px) {
        .summary-grid,
        .filters-grid,
        .filters-grid.dates {
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
        <h2>Todos los Arqueos</h2>

        <p>
            Consulte de forma consolidada los arqueos realizados por
            Agentes y Promotores.
        </p>
    </div>
</div>

<section class="summary-grid">
    <article class="summary-card">
        <span>Total</span>
        <strong>{{ $total }}</strong>
    </article>

    <article class="summary-card">
        <span>Agente</span>
        <strong>{{ $agente }}</strong>
    </article>

    <article class="summary-card">
        <span>Promotor</span>
        <strong>{{ $promotor }}</strong>
    </article>

    <article class="summary-card success">
        <span>Certificados</span>
        <strong>{{ $certificados }}</strong>
    </article>

    <article class="summary-card warning">
        <span>Pendientes</span>
        <strong>{{ $pendientes }}</strong>
    </article>

    <article class="summary-card danger">
        <span>Anulados</span>
        <strong>{{ $anulados }}</strong>
    </article>

    <article class="summary-card info">
        <span>Extemporáneos</span>
        <strong>{{ $extemporaneos }}</strong>
    </article>
</section>

<section class="filters-card">
    <form
        method="GET"
        action="{{ route('gerencia.arqueos.index') }}"
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
                    Agente
                </option>

                <option
                    value="VISITA_PROMOTOR"
                    @selected($tipo === 'VISITA_PROMOTOR')
                >
                    Promotor
                </option>
            </select>

            <select
                name="estado"
                class="form-control"
            >
                <option value="">Todos los estados</option>

                <option
                    value="PENDIENTE_CERTIFICACION"
                    @selected($estado === 'PENDIENTE_CERTIFICACION')
                >
                    Pendiente certificación
                </option>

                <option
                    value="CERTIFICADO"
                    @selected($estado === 'CERTIFICADO')
                >
                    Certificado
                </option>

                <option
                    value="ANULADO"
                    @selected($estado === 'ANULADO')
                >
                    Anulado
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
        </div>

        <div class="filters-grid">
            <select
                name="agente_id"
                class="form-control"
            >
                <option value="">Todos los Agentes</option>

                @foreach($agentes as $agenteItem)
                    <option
                        value="{{ $agenteItem->id }}"
                        @selected($agenteId === (int) $agenteItem->id)
                    >
                        {{ $agenteItem->codigo_agente }}
                        — {{ $agenteItem->nombre_negocio }}
                    </option>
                @endforeach
            </select>

            <select
                name="promotor_id"
                class="form-control"
            >
                <option value="">Todos los Promotores</option>

                @foreach($promotores as $promotorItem)
                    @php
                        $nombrePromotor = trim(
                            ($promotorItem->nombres ?? '')
                            . ' '
                            . ($promotorItem->apellidos ?? '')
                        );
                    @endphp

                    <option
                        value="{{ $promotorItem->id }}"
                        @selected($promotorId === (int) $promotorItem->id)
                    >
                        {{ $nombrePromotor !== ''
                            ? $nombrePromotor
                            : $promotorItem->usuario }}
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
                        @selected($regionId === (int) $region->id)
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
                        @selected($rutaId === (int) $ruta->id)
                    >
                        {{ $ruta->codigo }} — {{ $ruta->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filters-grid dates">
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

        <div class="filters-actions">
            <a
                href="{{ route('gerencia.arqueos.index') }}"
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
        <h3>Historial General</h3>

        <p>
            Consulta consolidada de arqueos.
        </p>
    </header>

    <div class="table-responsive">
        @if($arqueos->isEmpty())
            <div class="empty-state">
                No se encontraron arqueos.
            </div>
        @else
            <table class="arqueos-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Agente</th>
                        <th>Responsable</th>
                        <th>Región</th>
                        <th>Ruta</th>
                        <th>Estado</th>
                        <th>Saldo Sistema</th>
                        <th>Arqueado</th>
                        <th>Diferencia</th>
                        <th>Extemporáneo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($arqueos as $arqueo)
                        @php
                            $responsable = trim(
                                ($arqueo->responsable_nombres ?? '')
                                . ' '
                                . ($arqueo->responsable_apellidos ?? '')
                            );

                            $diferencia = (float) $arqueo->diferencia;

                            $claseDiferencia =
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
                                <span class="badge">
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
                                {{ $arqueo->codigo_agente }}
                                — {{ $arqueo->nombre_negocio }}
                            </td>

                            <td>
                                {{ $responsable !== ''
                                    ? $responsable
                                    : ($arqueo->responsable_usuario ?? '—') }}
                            </td>

                            <td>
                                {{ $arqueo->region_nombre ?? '—' }}
                            </td>

                            <td>
                                {{ $arqueo->ruta_codigo }}
                                — {{ $arqueo->ruta_nombre }}
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
                                    (float) $arqueo->saldo_sistema,
                                    2
                                ) }}
                            </td>

                            <td>
                                Q {{ number_format(
                                    (float) $arqueo->total_arqueado,
                                    2
                                ) }}
                            </td>

                            <td>
                                <span class="{{ $claseDiferencia }}">
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

                            <td>
                                @if($arqueo->fuera_fecha_ordinaria)
                                    <span class="badge extra">
                                        Sí
                                    </span>
                                @else
                                    No
                                @endif
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
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($arqueos->hasPages())
        <div class="pagination">
            {{ $arqueos->links() }}
        </div>
    @endif
</section>
@endsection
