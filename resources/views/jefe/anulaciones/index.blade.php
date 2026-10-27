@extends('layouts.jefe')

@section('title', 'Anular Arqueos')
@section('module-title', 'Anular Arqueos')

@push('styles')
<style>
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
        grid-template-columns: minmax(260px, 1fr) 240px;
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

    .warning-note {
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

    .warning-note svg {
        width: 15px;
        height: 15px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .arqueos-table {
        width: 100%;
        min-width: 1100px;
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

    .arqueos-table tbody tr {
        transition: .18s ease;
    }

    .arqueos-table tbody tr:hover {
        background: #fbfcfd;
    }

    .number-cell {
        color: #163f66;
        font-weight: 850;
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

    .badge.pending {
        border: 1px solid #c9dcf4;
        background: #eef5ff;
        color: #285b9b;
    }

    .badge.certified {
        border: 1px solid #c7e4d2;
        background: #effaf3;
        color: #197247;
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

    .icon-button.danger {
        border-color: #e4c2bf;
        background: #fff4f3;
        color: #b33a34;
    }

    .icon-button.danger:hover {
        border-color: #d59b96;
        background: #ffebe9;
        color: #9e2e29;
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
    }

    .pagination {
        padding: 16px 18px;
    }

    @media (max-width: 800px) {
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

        .warning-note {
            white-space: normal;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">
        <h2>Anular Arqueos</h2>
        <p>
            Consulte los arqueos pendientes o certificados que pueden ser
            revisados antes de realizar una anulación.
        </p>
    </div>
</div>

<section class="filters-card">
    <form method="GET" action="{{ route('jefe.anulaciones.index') }}">
        <div class="filters-grid">
            <input
                type="text"
                name="buscar"
                class="form-control"
                value="{{ $buscar }}"
                placeholder="Número, agente, negocio o responsable"
            >

            <select name="estado" class="form-control">
                <option value="">Pendientes y certificados</option>

                <option
                    value="PENDIENTE_CERTIFICACION"
                    @selected($estado === 'PENDIENTE_CERTIFICACION')
                >
                    Pendiente de certificación
                </option>

                <option
                    value="CERTIFICADO"
                    @selected($estado === 'CERTIFICADO')
                >
                    Certificado
                </option>
            </select>
        </div>

        <div class="filters-actions">
            <a
                href="{{ route('jefe.anulaciones.index') }}"
                class="btn btn-secondary"
            >
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
        <div>
            <h3>Arqueos disponibles para anulación</h3>
            <p>
                Seleccione un registro para revisar su información antes de anularlo.
            </p>
        </div>

        <div class="warning-note">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 9v4"></path>
                <path d="M12 17h.01"></path>
                <path d="M10.3 3.7 2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"></path>
            </svg>
            La anulación requiere motivo y contraseña
        </div>
    </header>

    <div class="table-responsive">
        @if ($arqueos->isEmpty())
            <div class="empty-state">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M9 12l2 2 4-4"></path>
                </svg>
                No hay arqueos disponibles para anulación.
            </div>
        @else
            <table class="arqueos-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Agente</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Responsable</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($arqueos as $arqueo)
                        @php
                            $creador = trim(
                                ($arqueo->creador_nombres ?? '')
                                . ' '
                                . ($arqueo->creador_apellidos ?? '')
                            );

                            $tipoClase = match ($arqueo->tipo) {
                                'DIARIO_AGENTE' => 'type-agent',
                                'VISITA_PROMOTOR' => 'type-promotor',
                                default => 'type-other',
                            };

                            $tipoTexto = match ($arqueo->tipo) {
                                'DIARIO_AGENTE' => 'Agente',
                                'VISITA_PROMOTOR' => 'Promotor',
                                'VISITA_AUDITORIA' => 'Auditoría',
                                default => str_replace('_', ' ', $arqueo->tipo),
                            };

                            $estadoClase = $arqueo->estado === 'CERTIFICADO'
                                ? 'certified'
                                : 'pending';

                            $estadoTexto = $arqueo->estado === 'CERTIFICADO'
                                ? 'Certificado'
                                : 'Pendiente de certificación';
                        @endphp

                        <tr>
                            <td class="number-cell">
                                {{ $arqueo->numero_arqueo }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}
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
                                <span class="badge {{ $tipoClase }}">
                                    {{ $tipoTexto }}
                                </span>
                            </td>

                            <td>
                                <span class="badge {{ $estadoClase }}">
                                    {{ $estadoTexto }}
                                </span>
                            </td>

                            <td>
                                {{ $creador !== ''
                                    ? $creador
                                    : ($arqueo->creador_usuario ?? '—') }}
                            </td>

                            <td class="actions-cell">
                                <div class="table-actions">
                                    <a
                                        href="{{ route('jefe.anulaciones.show', $arqueo->id) }}"
                                        class="icon-button danger"
                                        title="Revisar y anular"
                                        aria-label="Revisar arqueo para anulación"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M3 6h18"></path>
                                            <path d="M8 6V4h8v2"></path>
                                            <path d="M19 6l-1 14H6L5 6"></path>
                                            <path d="M10 11v5"></path>
                                            <path d="M14 11v5"></path>
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
