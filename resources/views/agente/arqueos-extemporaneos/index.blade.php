@extends('layouts.agente')

@section('title', 'Arqueos Extemporáneos')
@section('module-title', 'Arqueos Extemporáneos')

@push('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }

    .summary-card {
        padding: 20px;
        border: 1px solid #e0e8ee;
        border-radius: 17px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .summary-card span {
        display: block;
        color: #768692;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .summary-card strong {
        display: block;
        margin-top: 10px;
        color: #082d55;
        font-size: 24px;
    }

    .summary-card.pending {
        border-color: #efd99f;
        background: #fffdf6;
    }

    .summary-card.used {
        border-color: #c4e4d1;
        background: #f7fcf9;
    }

    .summary-card.cancelled {
        border-color: #efc5c5;
        background: #fffafa;
    }

    .filter-panel,
    .table-panel {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .filter-panel {
        margin-bottom: 20px;
        padding: 17px 19px;
    }

    .filter-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-control {
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #d5dfe5;
        border-radius: 10px;
        background: #ffffff;
        color: #30485b;
        outline: none;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border: 0;
        border-radius: 10px;
        font-size: 11px;
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

    .btn-start {
        background: #18794e;
        color: #ffffff;
    }

    .table-header {
        padding: 19px 21px;
        border-bottom: 1px solid #edf1f4;
    }

    .table-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 16px;
    }

    .table-header p {
        margin: 5px 0 0;
        color: #82909a;
        font-size: 11px;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .ext-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .ext-table th {
        padding: 13px 17px;
        background: #f7f9fb;
        color: #6c7c88;
        font-size: 10px;
        font-weight: 800;
        text-align: left;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .ext-table td {
        padding: 15px 17px;
        border-top: 1px solid #edf1f4;
        color: #30485b;
        font-size: 12px;
        vertical-align: middle;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .4px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-badge.pending {
        background: #fff5dc;
        color: #9c7014;
    }

    .status-badge.used {
        background: #eaf8ef;
        color: #1d7b4e;
    }

    .status-badge.cancelled {
        background: #fdecec;
        color: #b13c3c;
    }

    .reason {
        max-width: 340px;
        line-height: 1.5;
    }

    .empty-record {
        padding: 48px 20px;
        text-align: center;
        color: #7d8b95;
    }

    .empty-record h4 {
        margin: 0;
        color: #17364f;
        font-size: 14px;
    }

    .empty-record p {
        margin: 7px auto 0;
        max-width: 430px;
        font-size: 11px;
        line-height: 1.55;
    }

    .pagination-container {
        padding: 17px 20px;
        border-top: 1px solid #edf1f4;
    }

    @media (max-width: 850px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .filter-form {
            align-items: stretch;
            flex-direction: column;
        }

        .form-control,
        .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">
        <h2>Arqueos Extemporáneos</h2>

        <p>
            Consulte las fechas que fueron habilitadas para realizar un
            arqueo fuera del período ordinario.
        </p>
    </div>

    <div class="page-badge">
        <span class="page-badge-dot"></span>
        {{ $agente->codigo_agente }}
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success mb-4">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger mb-4">
        {{ $errors->first() }}
    </div>
@endif

<section class="summary-grid">
    <article class="summary-card pending">
        <span>Pendientes</span>
        <strong>{{ $pendientes }}</strong>
    </article>

    <article class="summary-card used">
        <span>Utilizados</span>
        <strong>{{ $utilizadas }}</strong>
    </article>

    <article class="summary-card cancelled">
        <span>Cancelados</span>
        <strong>{{ $canceladas }}</strong>
    </article>
</section>

<section class="filter-panel">
    <form
        method="GET"
        action="{{ route('agente.arqueos-extemporaneos.index') }}"
        class="filter-form"
    >
        <select
            name="estado"
            class="form-control"
        >
            <option value="">Todos los estados</option>

            <option
                value="PENDIENTE"
                @selected($estado === 'PENDIENTE')
            >
                Pendientes
            </option>

            <option
                value="UTILIZADA"
                @selected($estado === 'UTILIZADA')
            >
                Utilizados
            </option>

            <option
                value="CANCELADA"
                @selected($estado === 'CANCELADA')
            >
                Cancelados
            </option>
        </select>

        <button
            type="submit"
            class="btn btn-primary"
        >
            Filtrar
        </button>

        @if ($estado !== '')
            <a
                href="{{ route('agente.arqueos-extemporaneos.index') }}"
                class="btn btn-secondary"
            >
                Limpiar
            </a>
        @endif
    </form>
</section>

<section class="table-panel">
    <header class="table-header">
        <h3>Historial de habilitaciones</h3>

        <p>
            Las habilitaciones pendientes pueden utilizarse una sola vez.
        </p>
    </header>

    <div class="table-responsive">
        @if ($habilitaciones->isEmpty())
            <div class="empty-record">
                <h4>No existen habilitaciones registradas</h4>

                <p>
                    Cuando un Promotor o Jefe de Agentes habilite un arqueo
                    fuera de tiempo, aparecerá en esta sección.
                </p>
            </div>
        @else
            <table class="ext-table">
                <thead>
                    <tr>
                        <th>Fecha habilitada</th>
                        <th>Autorizado por</th>
                        <th>Fecha autorización</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($habilitaciones as $habilitacion)
                        @php
                            $estadoClase = match ($habilitacion->estado) {
                                'PENDIENTE' => 'pending',
                                'UTILIZADA' => 'used',
                                'CANCELADA' => 'cancelled',
                                default => 'pending',
                            };

                            $nombreAutorizador = trim(
                                ($habilitacion->autorizado_nombres ?? '')
                                . ' '
                                . ($habilitacion->autorizado_apellidos ?? '')
                            );
                        @endphp

                        <tr>
                            <td>
                                <strong>
                                    {{ \Carbon\Carbon::parse(
                                        $habilitacion->fecha_autorizada
                                    )->format('d/m/Y') }}
                                </strong>
                            </td>

                            <td>
                                {{ $nombreAutorizador !== ''
                                    ? $nombreAutorizador
                                    : 'Usuario institucional' }}
                            </td>

                            <td>
                                {{ $habilitacion->autorizado_at
                                    ? \Carbon\Carbon::parse(
                                        $habilitacion->autorizado_at
                                    )->format('d/m/Y H:i')
                                    : 'No disponible' }}
                            </td>

                            <td class="reason">
                                {{ $habilitacion->motivo }}
                            </td>

                            <td>
                                <span class="status-badge {{ $estadoClase }}">
                                    {{ $habilitacion->estado }}
                                </span>
                            </td>

                            <td>
                                @if ($habilitacion->estado === 'PENDIENTE')
                                    <a
                                        href="{{ route(
                                            'agente.arqueos.create',
                                            ['habilitacion' => $habilitacion->id]
                                        ) }}"
                                        class="btn btn-start"
                                    >
                                        Realizar arqueo
                                    </a>
                                @elseif (
                                    $habilitacion->estado === 'UTILIZADA'
                                )
                                    <span style="color:#1d7b4e;font-weight:700;">
                                        Utilizado
                                    </span>
                                @else
                                    <span style="color:#7d8b95;">
                                        No disponible
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if ($habilitaciones->hasPages())
        <div class="pagination-container">
            {{ $habilitaciones->links() }}
        </div>
    @endif
</section>

@endsection
