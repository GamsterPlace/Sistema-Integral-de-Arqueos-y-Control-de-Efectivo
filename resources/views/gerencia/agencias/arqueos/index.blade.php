
@extends('layouts.gerencia')

@section('title', $titulo)

@push('styles')
<style>
    .ga-page {
        display: grid;
        gap: 24px;
        color: #092b52;
    }

    .ga-page * {
        box-sizing: border-box;
    }

    .ga-heading h2 {
        margin: 0 0 12px;
        color: #082b53;
        font-size: 30px;
        font-weight: 500;
        line-height: 1.3;
    }

    .ga-heading p {
        margin: 0;
        color: #6a8199;
        font-size: 14px;
    }

    .ga-stats {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 16px;
    }

    .ga-stat {
        min-height: 102px;
        padding: 20px 19px;
        background: #fff;
        border: 1px solid #dce7f2;
        border-radius: 20px;
        box-shadow: 0 8px 24px rgba(17, 49, 84, .035);
    }

    .ga-stat-label {
        display: block;
        margin-bottom: 15px;
        color: #7185a0;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .ga-stat-value {
        color: #072a53;
        font-size: 25px;
        font-weight: 800;
        line-height: 1;
    }

    .ga-stat--certified {
        background: #f5fbf7;
        border-color: #bde5ca;
    }

    .ga-stat--pending {
        background: #fffdf5;
        border-color: #f5d68c;
    }

    .ga-stat--cancelled {
        background: #fff8f8;
        border-color: #f3bfc1;
    }

    .ga-stat--other {
        background: #f5faff;
        border-color: #c4ddf7;
    }

    .ga-panel {
        background: #fff;
        border: 1px solid #dce7f2;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(16, 47, 83, .035);
        overflow: hidden;
    }

    .ga-filters {
        padding: 20px;
    }

    .ga-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .ga-field {
        min-width: 0;
    }

    .ga-field label {
        display: block;
        margin-bottom: 6px;
        color: #516d8c;
        font-size: 12px;
        font-weight: 700;
    }

    .ga-control {
        display: block;
        width: 100%;
        height: 48px;
        padding: 0 15px;
        border: 1px solid #cbd9e8;
        border-radius: 12px;
        background: #fff;
        color: #12395e;
        font-family: inherit;
        font-size: 14px;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .ga-control:focus {
        border-color: #235fa6;
        box-shadow: 0 0 0 3px rgba(35, 95, 166, .10);
    }

    .ga-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .ga-actions-right {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .ga-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 0 17px;
        border: 0;
        border-radius: 12px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: background .2s, transform .2s;
    }

    .ga-btn:hover {
        transform: translateY(-1px);
    }

    .ga-btn-primary {
        background: #1c5099;
        color: #fff;
    }

    .ga-btn-primary:hover {
        background: #123e7c;
        color: #fff;
    }

    .ga-btn-secondary {
        background: #edf2f6;
        color: #23466a;
    }

    .ga-btn-secondary:hover {
        background: #dfe8f0;
    }

    .ga-btn-outline {
        background: #f4f8fd;
        color: #164c96;
        border: 1px solid #d1e1f3;
    }

    .ga-table-header {
        padding: 22px;
        background: #f9fcff;
        border-bottom: 1px solid #e6edf5;
    }

    .ga-table-header h3 {
        margin: 0 0 7px;
        color: #10365e;
        font-size: 17px;
        font-weight: 600;
    }

    .ga-table-header p {
        margin: 0;
        color: #7186a0;
        font-size: 12px;
    }

    .ga-table-scroll {
        overflow-x: auto;
    }

    .ga-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 12px;
    }

    .ga-table thead {
        background: #eef4fa;
    }

    .ga-table th {
        padding: 15px 13px;
        color: #244a70;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .ga-table td {
        padding: 16px 13px;
        color: #24425e;
        border-top: 1px solid #e8eef5;
        vertical-align: middle;
    }

    .ga-table tbody tr:hover {
        background: #f8fbff;
    }

    .ga-number {
        color: #164c96;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .ga-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 9px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .ga-badge--certified {
        background: #e7f6ec;
        color: #187344;
    }

    .ga-badge--pending {
        background: #fff3d6;
        color: #936000;
    }

    .ga-badge--cancelled {
        background: #fde8e8;
        color: #b4232d;
    }

    .ga-badge--neutral {
        background: #eaf1f9;
        color: #416488;
    }

    .ga-link {
        color: #164c96;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .ga-link:hover {
        text-decoration: underline;
    }

    .ga-empty {
        padding: 65px 20px !important;
        text-align: center;
        color: #8193a8 !important;
        font-size: 13px;
    }

    .ga-pagination {
        padding: 18px 22px;
        border-top: 1px solid #e8eef5;
    }

    @media (max-width: 1350px) {
        .ga-stats {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .ga-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ga-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 550px) {
        .ga-heading h2 {
            font-size: 24px;
        }

        .ga-filter-grid {
            grid-template-columns: 1fr;
        }

        .ga-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .ga-actions-right,
        .ga-actions-right .ga-btn,
        .ga-actions > .ga-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

@php
    /*
     * Indicadores de los registros cargados en la página actual.
     * No representan totales institucionales cuando hay paginación.
     */
    $registrosPagina = $arqueos->getCollection();

    $totalPagina = $registrosPagina->count();

    $ordinariosPagina = $registrosPagina
        ->where('tipo', 'ORDINARIO')
        ->count();

    $sorpresivosPagina = $registrosPagina
        ->where('tipo', 'SORPRESIVO')
        ->count();

    $certificadosPagina = $registrosPagina
        ->where('estado', 'CERTIFICADO')
        ->count();

    $pendientesPagina = $registrosPagina
        ->where('estado', 'PENDIENTE')
        ->count();

    $anuladosPagina = $registrosPagina
        ->where('estado', 'ANULADO')
        ->count();

    $otrosPagina = $registrosPagina
        ->whereIn('tipo', ['AUDITORIA', 'ENTREGA'])
        ->count();
@endphp

<div class="ga-page">

    {{-- ENCABEZADO --}}
    <div class="ga-heading">
        <h2>{{ $titulo }}</h2>
        <p>
            Consulte de forma consolidada los arqueos realizados
            en las agencias de la institución.
        </p>
    </div>

    {{-- INDICADORES --}}
    <div class="ga-stats">

        <div class="ga-stat">
            <span class="ga-stat-label">Registros en página</span>
            <span class="ga-stat-value">{{ $totalPagina }}</span>
        </div>

        <div class="ga-stat">
            <span class="ga-stat-label">Ordinarios</span>
            <span class="ga-stat-value">{{ $ordinariosPagina }}</span>
        </div>

        <div class="ga-stat">
            <span class="ga-stat-label">Sorpresivos</span>
            <span class="ga-stat-value">{{ $sorpresivosPagina }}</span>
        </div>

        <div class="ga-stat ga-stat--certified">
            <span class="ga-stat-label">Certificados</span>
            <span class="ga-stat-value">{{ $certificadosPagina }}</span>
        </div>

        <div class="ga-stat ga-stat--pending">
            <span class="ga-stat-label">Pendientes</span>
            <span class="ga-stat-value">{{ $pendientesPagina }}</span>
        </div>

        <div class="ga-stat ga-stat--cancelled">
            <span class="ga-stat-label">Anulados</span>
            <span class="ga-stat-value">{{ $anuladosPagina }}</span>
        </div>

        <div class="ga-stat ga-stat--other">
            <span class="ga-stat-label">Auditoría / Entrega</span>
            <span class="ga-stat-value">{{ $otrosPagina }}</span>
        </div>

    </div>

    {{-- FILTROS --}}
    <div class="ga-panel ga-filters">

        <form
            method="GET"
            action="{{ route('gerencia.agencias.arqueos.index') }}"
        >

            <div class="ga-filter-grid">

                <div class="ga-field">
                    <label for="ga-numero">Número de arqueo</label>
                    <input
                        id="ga-numero"
                        class="ga-control"
                        type="text"
                        name="numero_arqueo"
                        value="{{ $filtros['numero_arqueo'] ?? '' }}"
                        placeholder="Buscar por número"
                    >
                </div>

                <div class="ga-field">
                    <label for="ga-agencia">Agencia</label>
                    <select
                        id="ga-agencia"
                        class="ga-control"
                        name="agencia_id"
                    >
                        <option value="">Todas las agencias</option>

                        @foreach($agencias as $agencia)
                            <option
                                value="{{ $agencia->id }}"
                                @selected(($filtros['agencia_id'] ?? '') == $agencia->id)
                            >
                                {{ $agencia->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ga-field">
                    <label for="ga-tipo">Tipo de arqueo</label>
                    <select
                        id="ga-tipo"
                        class="ga-control"
                        name="tipo"
                    >
                        <option value="">Todos los tipos</option>

                        @foreach(['ORDINARIO', 'SORPRESIVO', 'AUDITORIA', 'ENTREGA'] as $tipo)
                            <option
                                value="{{ $tipo }}"
                                @selected(($filtros['tipo'] ?? '') === $tipo)
                            >
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ga-field">
                    <label for="ga-estado">Estado</label>
                    <select
                        id="ga-estado"
                        class="ga-control"
                        name="estado"
                    >
                        <option value="">Todos los estados</option>

                        @foreach($estados as $estado)
                            <option
                                value="{{ $estado }}"
                                @selected(($filtros['estado'] ?? '') === $estado)
                            >
                                {{ $estado }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ga-field">
                    <label for="ga-receptor">ID del receptor</label>
                    <input
                        id="ga-receptor"
                        class="ga-control"
                        type="number"
                        min="1"
                        name="receptor_id"
                        value="{{ $filtros['receptor_id'] ?? '' }}"
                        placeholder="Identificador"
                    >
                </div>

                <div class="ga-field">
                    <label for="ga-desde">Fecha desde</label>
                    <input
                        id="ga-desde"
                        class="ga-control"
                        type="date"
                        name="desde"
                        value="{{ $filtros['desde'] ?? '' }}"
                    >
                </div>

                <div class="ga-field">
                    <label for="ga-hasta">Fecha hasta</label>
                    <input
                        id="ga-hasta"
                        class="ga-control"
                        type="date"
                        name="hasta"
                        value="{{ $filtros['hasta'] ?? '' }}"
                    >
                </div>

            </div>

            <div class="ga-actions">

                <a
                    href="{{ route('gerencia.agencias.receptores.index') }}"
                    class="ga-btn ga-btn-outline"
                >
                    Consultar fichas por receptor
                </a>

                <div class="ga-actions-right">

                    <a
                        href="{{ route('gerencia.agencias.arqueos.index') }}"
                        class="ga-btn ga-btn-secondary"
                    >
                        Limpiar
                    </a>

                    <button
                        type="submit"
                        class="ga-btn ga-btn-primary"
                    >
                        Aplicar filtros
                    </button>

                </div>

            </div>

        </form>

    </div>

    {{-- HISTORIAL --}}
    <div class="ga-panel">

        <div class="ga-table-header">
            <h3>Historial general de agencias</h3>
            <p>
                {{ number_format($arqueos->total()) }}
                registros encontrados.
                Seleccione un arqueo para consultar su información.
            </p>
        </div>

        <div class="ga-table-scroll">

            <table class="ga-table">

                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Agencia</th>
                        <th>Ventanilla</th>
                        <th>Tipo</th>
                        <th>Receptor</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($arqueos as $arqueo)

                        @php
                            $estadoArqueo = strtoupper((string) $arqueo->estado);

                            $claseEstado = match ($estadoArqueo) {
                                'CERTIFICADO' => 'ga-badge--certified',
                                'PENDIENTE' => 'ga-badge--pending',
                                'ANULADO' => 'ga-badge--cancelled',
                                default => 'ga-badge--neutral',
                            };
                        @endphp

                        <tr>

                            <td>
                                <span class="ga-number">
                                    {{ $arqueo->numero_arqueo }}
                                </span>
                            </td>

                            <td>
                                {{ $arqueo->fecha_operativa
                                    ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y')
                                    : '—' }}
                            </td>

                            <td>
                                {{ $arqueo->agencia?->nombre
                                    ?? $arqueo->agencia_nombre_historico
                                    ?? '—' }}
                            </td>

                            <td>
                                {{ $arqueo->ventanilla?->codigo
                                    ?? $arqueo->ventanilla_codigo_historico
                                    ?? '—' }}
                            </td>

                            <td>
                                {{ $arqueo->tipo }}
                            </td>

                            <td>
                                {{ $arqueo->receptor_nombre_historico
                                    ?: ($arqueo->receptor?->nombre_completo ?? '—') }}
                            </td>

                            <td>
                                <span class="ga-badge {{ $claseEstado }}">
                                    {{ $arqueo->estado }}
                                </span>
                            </td>

                            <td style="white-space:nowrap;font-weight:700">
                                Q {{ number_format((float) $arqueo->total_arqueado, 2) }}
                            </td>

                            <td>
                                <a
                                    class="ga-link"
                                    href="{{ route('gerencia.agencias.arqueos.show', $arqueo->id) }}"
                                >
                                    Ver detalle
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="ga-empty">
                                No se encontraron arqueos con los filtros seleccionados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($arqueos->hasPages())
            <div class="ga-pagination">
                {{ $arqueos->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
