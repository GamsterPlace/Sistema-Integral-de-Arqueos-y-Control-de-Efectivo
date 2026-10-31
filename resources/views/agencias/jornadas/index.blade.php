
@extends($rutaBase === 'jefecaja' ? 'layouts.jefe-caja' : 'layouts.subjefe-caja')

@section('title', 'Gestión de Jornadas')
@section('module-title', 'Gestión de Jornadas')

@push('styles')
<style>
    .gj-page {
        display: grid;
        gap: 22px;
        color: #193552;
    }

    .gj-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 18px;
    }

    .gj-header h2 {
        margin: 0;
        color: #10345b;
        font-size: 26px;
        font-weight: 800;
    }

    .gj-header p {
        margin: 8px 0 0;
        color: #667b91;
        font-size: 14px;
        line-height: 1.65;
        max-width: 780px;
    }

    .gj-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        padding: 11px 17px;
        border: 1px solid transparent;
        border-radius: 9px;
        background: #14528c;
        color: #fff !important;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: .2s ease;
    }

    .gj-btn:hover {
        background: #0d3c68;
        color: #fff;
    }

    .gj-btn-secondary {
        background: #fff;
        border-color: #d5e0eb;
        color: #234766 !important;
    }

    .gj-btn-secondary:hover {
        background: #edf4fb;
        color: #163e63 !important;
    }

    .gj-card {
        background: #fff;
        border: 1px solid #dce5ef;
        border-radius: 14px;
        box-shadow: 0 5px 20px rgba(22, 48, 76, .045);
        overflow: hidden;
    }

    .gj-card-header {
        padding: 19px 22px;
        border-bottom: 1px solid #e7edf4;
    }

    .gj-card-header h3 {
        margin: 0;
        font-size: 16px;
        color: #183d65;
        font-weight: 800;
    }

    .gj-card-header p {
        margin: 5px 0 0;
        font-size: 12px;
        color: #718297;
    }

    .gj-filters {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
        padding: 20px 22px;
    }

    .gj-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .gj-field label {
        font-size: 12px;
        font-weight: 700;
        color: #35516e;
    }

    .gj-field select {
        width: 100%;
        min-height: 42px;
        padding: 9px 12px;
        border: 1px solid #cedbe8;
        border-radius: 8px;
        background: #fff;
        color: #254461;
        font: inherit;
        font-size: 13px;
    }

    .gj-field select:focus {
        outline: none;
        border-color: #3781bf;
        box-shadow: 0 0 0 3px rgba(55, 129, 191, .12);
    }

    .gj-filter-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        padding: 0 22px 20px;
    }

    .gj-table-wrapper {
        overflow-x: auto;
    }

    .gj-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1050px;
    }

    .gj-table thead {
        background: #f2f6fb;
    }

    .gj-table th {
        padding: 14px 15px;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
        color: #526c85;
        text-transform: uppercase;
        letter-spacing: .045em;
        white-space: nowrap;
    }

    .gj-table td {
        padding: 15px;
        border-top: 1px solid #edf1f5;
        font-size: 13px;
        color: #3c536b;
        vertical-align: middle;
    }

    .gj-table tbody tr:hover {
        background: #fafcff;
    }

    .gj-primary {
        display: block;
        font-weight: 800;
        color: #1a3f66;
    }

    .gj-secondary {
        display: block;
        margin-top: 4px;
        font-size: 11px;
        color: #8191a2;
    }

    .gj-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 30px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .gj-badge-success {
        background: #e6f6ec;
        color: #237444;
    }

    .gj-badge-warning {
        background: #fff4db;
        color: #9b6b11;
    }

    .gj-badge-muted {
        background: #edf0f4;
        color: #657588;
    }

    .gj-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .gj-action {
        padding: 7px 10px;
        border: 1px solid #d6e3ef;
        border-radius: 7px;
        color: #1a5a91;
        text-decoration: none;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .gj-action:hover {
        background: #edf5fc;
    }

    .gj-empty {
        padding: 55px 22px;
        text-align: center;
        color: #71849a;
    }

    .gj-empty h3 {
        margin: 0 0 8px;
        color: #294b6d;
        font-size: 17px;
    }

    .gj-empty p {
        margin: 0;
        font-size: 13px;
    }

    .gj-pagination {
        padding: 16px 22px;
        border-top: 1px solid #edf1f5;
    }

    .gj-alert {
        padding: 14px 18px;
        border-radius: 10px;
        font-size: 13px;
        line-height: 1.6;
    }

    .gj-alert-success {
        background: #e8f7ee;
        border: 1px solid #bce5ca;
        color: #22633d;
    }

    .gj-alert-error {
        background: #fff0f0;
        border: 1px solid #efcaca;
        color: #9c3434;
    }

    @media (max-width: 850px) {
        .gj-filters {
            grid-template-columns: 1fr;
        }

        .gj-header h2 {
            font-size: 22px;
        }
    }
</style>
@endpush

@section('content')
@php
    $hoy = now()->toDateString();

    $totalPagina = $asignacionesOperativas->count();

    $vigentesPagina = $asignacionesOperativas
        ->getCollection()
        ->filter(fn ($item) => $item->estaVigente())
        ->count();

    $receptores = $asignaciones
        ->unique('usuario_id')
        ->sortBy(fn ($item) => $item->usuario?->nombre_completo ?? '');
@endphp

<div class="gj-page">

    <div class="gj-header">
        <div>
            <h2>Gestión de Jornadas</h2>
            <p>
                Administre las asignaciones de receptores a ventanillas
                y turnos. Cada asignación permanece vigente hasta que
                sea modificada por el Jefe o Subjefe de Caja.
                Las jornadas diarias se generan automáticamente.
            </p>
        </div>

        <a href="{{ route($rutaBase . '.jornadas.create') }}"
           class="gj-btn">
            + Nueva asignación
        </a>
    </div>

    @if(session('success'))
        <div class="gj-alert gj-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="gj-alert gj-alert-error">
            <strong>No se pudo completar la operación.</strong>
            <ul style="margin: 8px 0 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="gj-card">
        <div class="gj-card-header">
            <h3>Filtros de asignaciones</h3>
            <p>
                Consulte la distribución operativa por agencia,
                receptor o estado de vigencia.
            </p>
        </div>

        <form method="GET"
              action="{{ route($rutaBase . '.jornadas.index') }}">

            <div class="gj-filters">

                <div class="gj-field">
                    <label for="agencia_id">Agencia</label>
                    <select name="agencia_id" id="agencia_id">
                        <option value="">Todas las agencias</option>

                        @foreach($agencias as $agencia)
                            <option value="{{ $agencia->id }}"
                                @selected(
                                    (string)($filtros['agencia_id'] ?? '')
                                    === (string)$agencia->id
                                )>
                                {{ $agencia->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="gj-field">
                    <label for="receptor_usuario_id">Receptor</label>
                    <select name="receptor_usuario_id"
                            id="receptor_usuario_id">

                        <option value="">Todos los receptores</option>

                        @foreach($receptores as $asignacion)
                            <option value="{{ $asignacion->usuario_id }}"
                                @selected(
                                    (string)($filtros['receptor_usuario_id'] ?? '')
                                    === (string)$asignacion->usuario_id
                                )>
                                {{ $asignacion->usuario?->nombre_completo
                                    ?? 'Usuario #' . $asignacion->usuario_id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="gj-field">
                    <label for="estado">Estado</label>
                    <select name="estado" id="estado">
                        <option value="">Todos los estados</option>

                        <option value="VIGENTE"
                            @selected(($filtros['estado'] ?? '') === 'VIGENTE')>
                            Vigentes
                        </option>

                        <option value="FUTURA"
                            @selected(($filtros['estado'] ?? '') === 'FUTURA')>
                            Próximas
                        </option>

                        <option value="FINALIZADA"
                            @selected(($filtros['estado'] ?? '') === 'FINALIZADA')>
                            Finalizadas
                        </option>
                    </select>
                </div>

            </div>

            <div class="gj-filter-actions">
                <a href="{{ route($rutaBase . '.jornadas.index') }}"
                   class="gj-btn gj-btn-secondary">
                    Limpiar filtros
                </a>

                <button type="submit" class="gj-btn">
                    Aplicar filtros
                </button>
            </div>
        </form>
    </div>

    <div class="gj-card">

        <div class="gj-card-header">
            <h3>Asignaciones operativas</h3>
            <p>
                {{ $asignacionesOperativas->total() }}
                asignaciones encontradas.
                En esta página: {{ $totalPagina }},
                de las cuales {{ $vigentesPagina }} están vigentes.
            </p>
        </div>

        @if($asignacionesOperativas->count())

            <div class="gj-table-wrapper">
                <table class="gj-table">
                    <thead>
                        <tr>
                            <th>Receptor</th>
                            <th>Agencia</th>
                            <th>Ventanilla</th>
                            <th>Turno y horario</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($asignacionesOperativas as $asignacion)
                            @php
                                $esVigente = $asignacion->estaVigente();

                                $esFutura = $asignacion->estado
                                    && $asignacion->fecha_inicio
                                    && $asignacion->fecha_inicio
                                        ->toDateString() > $hoy;

                                $horaInicio = $asignacion->turno?->hora_inicio;
                                $horaFin = $asignacion->turno?->hora_fin;
                            @endphp

                            <tr>
                                <td>
                                    <span class="gj-primary">
                                        {{ $asignacion->receptor?->nombre_completo
                                            ?? 'Usuario #' . $asignacion->receptor_usuario_id }}
                                    </span>

                                    <span class="gj-secondary">
                                        Receptor #{{ $asignacion->receptor_usuario_id }}
                                    </span>
                                </td>

                                <td>
                                    <span class="gj-primary">
                                        {{ $asignacion->agencia?->nombre ?? 'Sin agencia' }}
                                    </span>

                                    <span class="gj-secondary">
                                        {{ $asignacion->agencia?->codigo ?? '' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="gj-primary">
                                        {{ $asignacion->ventanilla?->codigo ?? 'N/D' }}
                                    </span>

                                    <span class="gj-secondary">
                                        {{ $asignacion->ventanilla?->descripcion ?? '' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="gj-primary">
                                        {{ $asignacion->turno?->nombre ?? 'Sin turno' }}
                                    </span>

                                    <span class="gj-secondary">
                                        @if($horaInicio && $horaFin)
                                            {{ substr((string)$horaInicio, 0, 5) }}
                                            -
                                            {{ substr((string)$horaFin, 0, 5) }}

                                            @if($asignacion->turno?->cruza_medianoche)
                                                (día siguiente)
                                            @endif
                                        @else
                                            Horario no disponible
                                        @endif
                                    </span>
                                </td>

                                <td>
                                    <span class="gj-primary">
                                        Desde
                                        {{ $asignacion->fecha_inicio?->format('d/m/Y') ?? 'N/D' }}
                                    </span>

                                    <span class="gj-secondary">
                                        @if($asignacion->fecha_fin)
                                            Hasta
                                            {{ $asignacion->fecha_fin->format('d/m/Y') }}
                                        @else
                                            Vigencia indefinida
                                        @endif
                                    </span>
                                </td>

                                <td>
                                    @if($esVigente)
                                        <span class="gj-badge gj-badge-success">
                                            Vigente
                                        </span>
                                    @elseif($esFutura)
                                        <span class="gj-badge gj-badge-warning">
                                            Próxima
                                        </span>
                                    @else
                                        <span class="gj-badge gj-badge-muted">
                                            Finalizada
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="gj-actions">
                                        <a class="gj-action"
                                           href="{{ route(
                                               $rutaBase . '.jornadas.show',
                                               $asignacion->id
                                           ) }}">
                                            Consultar
                                        </a>

                                        @if($esVigente)
                                            <a class="gj-action"
                                               href="{{ route(
                                                   $rutaBase . '.jornadas.edit',
                                                   $asignacion->id
                                               ) }}">
                                                Modificar
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($asignacionesOperativas->hasPages())
                <div class="gj-pagination">
                    {{ $asignacionesOperativas->links() }}
                </div>
            @endif

        @else
            <div class="gj-empty">
                <h3>No se encontraron asignaciones</h3>
                <p>
                    No existen asignaciones operativas que coincidan
                    con los filtros seleccionados.
                </p>
            </div>
        @endif

    </div>

</div>
@endsection
