@extends('layouts.jefe')

@section('title', 'Certificar Arqueos')
@section('module-title', 'Certificar Arqueos')

@push('styles')
<style>
    .cert-summary,
    .cert-filters,
    .cert-table-card {
        background: #fff;
        border: 1px solid #e0e8ee;
        border-radius: 16px;
        overflow: hidden;
    }

    .cert-summary {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        margin-bottom: 18px;
    }

    .cert-summary-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #edf5fb;
        color: #164c96;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cert-summary-icon svg,
    .icon-button svg {
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .cert-summary-icon svg {
        width: 20px;
        height: 20px;
    }

    .cert-summary small {
        display: block;
        color: #748592;
        font-weight: 800;
        text-transform: uppercase;
    }

    .cert-summary strong {
        display: block;
        margin-top: 3px;
        color: #0a3158;
        font-size: 21px;
    }

    .cert-filters {
        padding: 18px;
        margin-bottom: 20px;
    }

    .cert-filter-row {
        display: flex;
        gap: 10px;
    }

    .cert-control {
        width: 100%;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        outline: none;
    }

    .cert-filter-btn {
        min-height: 42px;
        padding: 0 17px;
        border: 0;
        border-radius: 10px;
        background: #164c96;
        color: #fff;
        font-weight: 800;
        cursor: pointer;
    }

    .cert-table-wrap {
        overflow-x: auto;
    }

    .cert-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: collapse;
    }

    .cert-table th,
    .cert-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf1f4;
        text-align: left;
        vertical-align: middle;
    }

    .cert-table th {
        background: #f7f9fb;
        color: #687b8b;
        font-size: 11px;
        text-transform: uppercase;
    }

    .cert-table td {
        font-size: 13px;
        color: #344e62;
    }

    .cert-number {
        font-weight: 800;
        color: #164c96;
    }

    .cert-type {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        border: 1px solid #c9dcf4;
        border-radius: 999px;
        background: #eef5ff;
        color: #285b9b;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .25px;
        white-space: nowrap;
    }

    .cert-type.auditoria {
        border-color: #d8d0ec;
        background: #f5f2fb;
        color: #67489a;
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
        background: #fff;
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

    .icon-button svg {
        width: 17px;
        height: 17px;
    }

    .empty-row {
        text-align: center !important;
        padding: 35px !important;
        color: #748592 !important;
    }

    .cert-pagination {
        padding: 16px 18px;
    }

    @media (max-width: 700px) {
        .cert-filter-row {
            flex-direction: column;
        }

        .cert-filter-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h2>Certificar Arqueos</h2>
        <p>Revise los arqueos realizados por Promotores o Auditoría y validados por el Agente antes de realizar la certificación electrónica.</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert-message warning">{{ $errors->first() }}</div>
@endif

<div class="cert-summary">
    <div class="cert-summary-icon">
        <svg viewBox="0 0 24 24">
            <path d="M9 12l2 2 4-4"></path>
            <circle cx="12" cy="12" r="9"></circle>
        </svg>
    </div>

    <div>
        <small>Pendientes de certificación</small>
        <strong>{{ $totalPendientes }}</strong>
    </div>
</div>

<div class="cert-filters">
    <form method="GET" action="{{ route('jefe.certificaciones.index') }}">
        <div class="cert-filter-row">
            <input
                type="text"
                name="buscar"
                value="{{ $buscar }}"
                class="cert-control"
                placeholder="Número, agente, negocio o realizador"
            >

            <button type="submit" class="cert-filter-btn">
                Buscar
            </button>
        </div>
    </form>
</div>

<div class="cert-table-card">
    <div class="cert-table-wrap">
        <table class="cert-table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Agente</th>
                    <th>Realizado por</th>
                    <th>Ruta</th>
                    <th>Región</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>
            @forelse ($arqueos as $arqueo)
                @php
                    $realizador = trim(
                        ($arqueo->realizador_nombres ?? '') . ' ' .
                        ($arqueo->realizador_apellidos ?? '')
                    );

                    $esAuditoria = $arqueo->tipo === 'VISITA_AUDITORIA';

                    $tipoTexto = $esAuditoria
                        ? 'Auditoría'
                        : 'Promotor';
                @endphp

                <tr>
                    <td class="cert-number">
                        {{ $arqueo->numero_arqueo }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}
                    </td>

                    <td>
                        <span class="cert-type {{ $esAuditoria ? 'auditoria' : '' }}">
                            {{ $tipoTexto }}
                        </span>
                    </td>

                    <td>
                        <strong>{{ $arqueo->codigo_agente }}</strong>
                        —
                        {{ $arqueo->nombre_negocio }}
                    </td>

                    <td>
                        {{ $realizador !== ''
                            ? $realizador
                            : ($arqueo->realizador_usuario ?? '—') }}
                    </td>

                    <td>
                        {{ $arqueo->ruta_nombre ?? '—' }}
                    </td>

                    <td>
                        {{ $arqueo->region_nombre ?? '—' }}
                    </td>

                    <td class="actions-cell">
                        <div class="table-actions">
                            <a
                                href="{{ route('jefe.certificaciones.show', $arqueo->id) }}"
                                class="icon-button"
                                title="Visualizar arqueo"
                                aria-label="Visualizar arqueo"
                            >
                                <svg viewBox="0 0 24 24">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="2.8"></circle>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty-row">
                        No hay arqueos pendientes de certificación.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($arqueos->hasPages())
        <div class="cert-pagination">
            {{ $arqueos->links() }}
        </div>
    @endif
</div>
@endsection
