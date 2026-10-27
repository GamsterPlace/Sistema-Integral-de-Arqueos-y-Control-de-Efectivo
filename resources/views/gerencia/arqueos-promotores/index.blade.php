@extends('layouts.gerencia')

@section('title', 'Arqueos por Promotor')
@section('module-title', 'Arqueos por Promotor')

@push('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
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
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .summary-card strong {
        display: block;
        margin-top: 8px;
        color: #082d55;
        font-size: 25px;
    }

    .summary-card.success {
        border-color: #c4e4d1;
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

    .filters-card {
        margin-bottom: 20px;
        padding: 18px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 11px;
    }

    .filters-grid + .filters-grid {
        margin-top: 11px;
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
        min-width: 1350px;
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
        letter-spacing: .3px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .arqueos-table tbody tr {
        transition: background .15s ease;
    }

    .arqueos-table tbody tr:hover {
        background: #fbfdfe;
    }

    .arqueos-table td strong {
        color: #163b5b;
    }

    .promotor-name {
        color: #30485b;
        font-weight: 700;
    }

    .agent-code {
        color: #164c96;
        font-weight: 800;
    }

    .agent-business {
        color: #536b7d;
    }

    .status-value {
        display: inline-flex;
        align-items: center;
        padding: 6px 9px;
        border-radius: 999px;
        background: #edf2f6;
        color: #50667a;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .difference-value {
        color: #30485b;
        font-weight: 800;
        white-space: nowrap;
    }

    .extemporaneo-value {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        padding: 6px 9px;
        border-radius: 999px;
        background: #edf2f6;
        color: #50667a;
        font-size: 8px;
        font-weight: 800;
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
        box-sizing: border-box;
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
        padding: 44px 20px;
        color: #7d8b95;
        text-align: center;
        font-size: 11px;
    }

    .pagination {
        padding: 16px 18px;
        border-top: 1px solid #edf1f4;
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filters-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
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
        <h2>Arqueos por Promotor</h2>

        <p>
            Consulte los arqueos realizados durante visitas de Promotores.
        </p>
    </div>
</div>

<div class="summary-grid">
    <div class="summary-card">
        <span>Total</span>

        <strong>
            {{ $total }}
        </strong>
    </div>

    <div class="summary-card success">
        <span>Certificados</span>

        <strong>
            {{ $certificados }}
        </strong>
    </div>

    <div class="summary-card warning">
        <span>Pendientes</span>

        <strong>
            {{ $pendientes }}
        </strong>
    </div>

    <div class="summary-card danger">
        <span>Anulados</span>

        <strong>
            {{ $anulados }}
        </strong>
    </div>
</div>

<form
    method="GET"
    action="{{ route('gerencia.arqueos-promotores.index') }}"
    class="filters-card"
>
    <div class="filters-grid">
        <input
            type="text"
            name="buscar"
            value="{{ $buscar }}"
            placeholder="Número, Agente, Promotor, Ruta o Región"
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
            name="agente_id"
            class="form-control"
        >
            <option value="">
                Todos los Agentes
            </option>

            @foreach($agentes as $agente)
                <option
                    value="{{ $agente->id }}"
                    @selected($agenteId === (int) $agente->id)
                >
                    {{ $agente->codigo_agente }}
                    — {{ $agente->nombre_negocio }}
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
    </div>

    <div class="filters-grid">
        <select
            name="ruta_id"
            class="form-control"
        >
            <option value="">
                Todas las Rutas
            </option>

            @foreach($rutas as $ruta)
                <option
                    value="{{ $ruta->id }}"
                    @selected($rutaId === (int) $ruta->id)
                >
                    {{ $ruta->codigo }} — {{ $ruta->nombre }}
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

        <input
            type="date"
            name="desde"
            value="{{ $desde }}"
            class="form-control"
        >

        <input
            type="date"
            name="hasta"
            value="{{ $hasta }}"
            class="form-control"
        >
    </div>

    <div class="filters-actions">
        <a
            href="{{ route('gerencia.arqueos-promotores.index') }}"
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

<section class="table-card">
    <header class="table-header">
        <h3>
            Arqueos realizados por Promotores
        </h3>

        <p>
            Información de consulta para Gerencia.
        </p>
    </header>

    <div class="table-responsive">
        <table class="arqueos-table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Promotor</th>
                    <th>Agente</th>
                    <th>Región</th>
                    <th>Ruta</th>
                    <th>Estado</th>
                    <th>Diferencia</th>
                    <th>Extemporáneo</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($arqueos as $arqueo)
                    @php
                        $nombrePromotor = trim(
                            ($arqueo->promotor_nombres ?? '')
                            . ' '
                            . ($arqueo->promotor_apellidos ?? '')
                        );

                        $diferencia = (float) $arqueo->diferencia;
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
                            <span class="promotor-name">
                                {{ $nombrePromotor !== ''
                                    ? $nombrePromotor
                                    : $arqueo->promotor_usuario }}
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
                            {{ $arqueo->region_nombre ?? '—' }}
                        </td>

                        <td>
                            {{ $arqueo->ruta_codigo }}
                            — {{ $arqueo->ruta_nombre }}
                        </td>

                        <td>
                            <span class="status-value">
                                {{ str_replace(
                                    '_',
                                    ' ',
                                    $arqueo->estado
                                ) }}
                            </span>
                        </td>

                        <td>
                            <span class="difference-value">
                                Q {{ number_format(abs($diferencia), 2) }}

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
                            <span class="extemporaneo-value">
                                {{ $arqueo->fuera_fecha_ordinaria
                                    ? 'Sí'
                                    : 'No' }}
                            </span>
                        </td>

                        <td class="actions-cell">
                            <div class="table-actions">
                                <a
                                    href="{{ route(
                                        'gerencia.arqueos-promotores.show',
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
                                        'gerencia.arqueos-promotores.imprimir',
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
                        <td colspan="10">
                            <div class="empty-state">
                                No se encontraron arqueos.
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
