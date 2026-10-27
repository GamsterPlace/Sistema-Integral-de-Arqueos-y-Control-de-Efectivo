@extends('layouts.jefe')

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

    .summary-card.warning {
        border-color: #efd99f;
        background: #fffdf6;
    }

    .summary-card.success {
        border-color: #c4e4d1;
        background: #f7fcf9;
    }

    .filters-card {
        margin-bottom: 20px;
        padding: 18px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns:
            minmax(220px, 1fr)
            230px
            190px
            160px
            160px;
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
        min-width: 1220px;
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
        letter-spacing: .3px;
        text-transform: uppercase;
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

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .badge.pending {
        background: #fff4d9;
        color: #946b14;
    }

    .badge.certified {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .badge.cancelled {
        background: #fdecec;
        color: #b13c3c;
    }

    .badge.default {
        background: #edf2f6;
        color: #50667a;
    }

    .amount {
        font-weight: 700;
        white-space: nowrap;
    }

    .difference-positive {
        color: #1d7b4e;
        font-weight: 800;
    }

    .difference-negative {
        color: #c0392b;
        font-weight: 800;
    }

    .difference-zero {
        color: #0a3158;
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
            Consulte los arqueos realizados por cada Promotor durante
            sus visitas a los Agentes MICOOPE.
        </p>
    </div>
</div>

<section class="summary-grid">
    <article class="summary-card">
        <span>Total arqueos de Promotor</span>
        <strong>{{ $totalArqueos }}</strong>
    </article>

    <article class="summary-card">
        <span>Realizados hoy</span>
        <strong>{{ $arqueosHoy }}</strong>
    </article>

    <article class="summary-card warning">
        <span>Pendientes de certificación</span>
        <strong>{{ $pendientes }}</strong>
    </article>

    <article class="summary-card success">
        <span>Certificados</span>
        <strong>{{ $certificados }}</strong>
    </article>
</section>

<section class="filters-card">
    <form
        method="GET"
        action="{{ route('jefe.arqueos-promotores.index') }}"
    >
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                value="{{ $buscar }}"
                class="form-control"
                placeholder="Número, agente, negocio o promotor"
            >

            <select
                name="promotor_id"
                class="form-control"
            >
                <option value="">Todos los promotores</option>

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
                        {{ $nombrePromotor !== ''
                            ? $nombrePromotor
                            : $promotor->usuario }}
                    </option>
                @endforeach
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
                href="{{ route('jefe.arqueos-promotores.index') }}"
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
        <h3>Arqueos realizados por Promotores</h3>

        <p>
            Resultados ordenados del más reciente al más antiguo.
        </p>
    </header>

    <div class="table-responsive">
        @if ($arqueos->isEmpty())
            <div class="empty-state">
                No se encontraron arqueos para los filtros seleccionados.
            </div>
        @else
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
                        <th>Total arqueado</th>
                        <th>Diferencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($arqueos as $arqueo)
                        @php
                            $nombrePromotor = trim(
                                ($arqueo->promotor_nombres ?? '')
                                . ' '
                                . ($arqueo->promotor_apellidos ?? '')
                            );

                            $estadoClase = match ($arqueo->estado) {
                                'PENDIENTE_CERTIFICACION' => 'pending',
                                'CERTIFICADO' => 'certified',
                                'ANULADO' => 'cancelled',
                                default => 'default',
                            };

                            $estadoTexto = match ($arqueo->estado) {
                                'PENDIENTE_CERTIFICACION' => 'Pendiente',
                                'CERTIFICADO' => 'Certificado',
                                'ANULADO' => 'Anulado',
                                default => str_replace('_', ' ', $arqueo->estado),
                            };
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $arqueo->numero_arqueo }}</strong>
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse(
                                    $arqueo->fecha_arqueo
                                )->format('d/m/Y') }}
                            </td>

                            <td>
                                {{ $nombrePromotor !== ''
                                    ? $nombrePromotor
                                    : ($arqueo->promotor_usuario ?: '—') }}
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
                                <span class="badge {{ $estadoClase }}">
                                    {{ $estadoTexto }}
                                </span>
                            </td>

                            <td class="amount">
                                Q {{ number_format(
                                    (float) $arqueo->total_arqueado,
                                    2
                                ) }}
                            </td>

                            <td class="amount">
                                @if ((float) $arqueo->diferencia > 0)
                                    <span class="difference-positive">
                                        +Q {{ number_format(
                                            (float) $arqueo->diferencia,
                                            2
                                        ) }}
                                    </span>
                                @elseif ((float) $arqueo->diferencia < 0)
                                    <span class="difference-negative">
                                        Q {{ number_format(
                                            (float) $arqueo->diferencia,
                                            2
                                        ) }}
                                    </span>
                                @else
                                    <span class="difference-zero">
                                        Q 0.00
                                    </span>
                                @endif
                            </td>

                            <td class="actions-cell">
                                <div class="table-actions">
                                    <a
                                        href="{{ route(
                                            'jefe.arqueos-promotores.show',
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
                                            <circle cx="12" cy="12" r="2.8"></circle>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route(
                                            'jefe.arqueos-promotores.imprimir',
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
        <div class="pagination">
            {{ $arqueos->links() }}
        </div>
    @endif
</section>
@endsection
