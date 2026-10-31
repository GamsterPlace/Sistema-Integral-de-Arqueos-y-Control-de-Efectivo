
@extends($rutaBase === 'jefecaja' ? 'layouts.jefe-caja' : 'layouts.subjefe-caja')

@section('title', 'Detalle de Asignación Operativa')
@section('module-title', 'Gestión de Jornadas')

@push('styles')
<style>
    .gjs-page {
        max-width: 1250px;
        margin: 0 auto;
        display: grid;
        gap: 22px;
        color: #193552;
    }

    .gjs-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 18px;
    }

    .gjs-header h2 {
        margin: 0;
        color: #10345b;
        font-size: 26px;
        font-weight: 800;
    }

    .gjs-header p {
        margin: 8px 0 0;
        color: #687e94;
        font-size: 14px;
        line-height: 1.65;
    }

    .gjs-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .gjs-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 11px 17px;
        border: 1px solid transparent;
        border-radius: 9px;
        background: #14528c;
        color: #fff !important;
        text-decoration: none;
        font-size: 13px;
        font-weight: 800;
    }

    .gjs-btn:hover {
        background: #0d3c68;
    }

    .gjs-btn-secondary {
        background: #fff;
        border-color: #d5e0eb;
        color: #234766 !important;
    }

    .gjs-btn-secondary:hover {
        background: #edf4fb;
    }

    .gjs-card {
        overflow: hidden;
        background: #fff;
        border: 1px solid #dce5ef;
        border-radius: 14px;
        box-shadow: 0 5px 20px rgba(22, 48, 76, .045);
    }

    .gjs-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        padding: 20px 23px;
        border-bottom: 1px solid #e7edf4;
    }

    .gjs-card-header h3 {
        margin: 0;
        color: #173c64;
        font-size: 16px;
        font-weight: 800;
    }

    .gjs-card-header p {
        margin: 5px 0 0;
        color: #74869a;
        font-size: 12px;
        line-height: 1.6;
    }

    .gjs-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        padding: 24px;
    }

    .gjs-field {
        min-width: 0;
    }

    .gjs-field-label {
        display: block;
        margin-bottom: 7px;
        color: #7b8da0;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .035em;
    }

    .gjs-field-value {
        display: block;
        color: #234766;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .gjs-field-secondary {
        display: block;
        margin-top: 4px;
        color: #8293a5;
        font-size: 12px;
        line-height: 1.5;
    }

    .gjs-badge {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 25px;
        font-size: 11px;
        font-weight: 800;
    }

    .gjs-badge-success {
        background: #e5f6eb;
        color: #237444;
    }

    .gjs-badge-warning {
        background: #fff3d9;
        color: #94640c;
    }

    .gjs-badge-muted {
        background: #edf0f4;
        color: #657588;
    }

    .gjs-info {
        padding: 17px 20px;
        background: #eff6fd;
        border: 1px solid #cfe1f3;
        border-radius: 10px;
        color: #28547b;
        font-size: 13px;
        line-height: 1.7;
    }

    .gjs-table-wrapper {
        overflow-x: auto;
    }

    .gjs-table {
        width: 100%;
        min-width: 950px;
        border-collapse: collapse;
    }

    .gjs-table thead {
        background: #f2f6fb;
    }

    .gjs-table th {
        padding: 14px 15px;
        text-align: left;
        color: #526c85;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
    }

    .gjs-table td {
        padding: 15px;
        border-top: 1px solid #edf1f5;
        color: #3c536b;
        font-size: 13px;
        vertical-align: middle;
    }

    .gjs-table tbody tr:hover {
        background: #fafcff;
    }

    .gjs-primary {
        display: block;
        color: #1a3f66;
        font-weight: 800;
    }

    .gjs-secondary {
        display: block;
        margin-top: 4px;
        color: #8191a2;
        font-size: 11px;
    }

    .gjs-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        padding: 5px 9px;
        border-radius: 7px;
        background: #edf4fb;
        color: #1e5686;
        font-size: 12px;
        font-weight: 800;
    }

    .gjs-empty {
        padding: 50px 22px;
        text-align: center;
    }

    .gjs-empty h4 {
        margin: 0 0 8px;
        color: #294b6d;
        font-size: 16px;
    }

    .gjs-empty p {
        margin: 0;
        color: #71849a;
        font-size: 13px;
        line-height: 1.6;
    }

    .gjs-pagination {
        padding: 17px 22px;
        border-top: 1px solid #edf1f5;
    }

    .gjs-reason {
        padding: 18px 22px;
        background: #fbfcfe;
        color: #3e5770;
        font-size: 13px;
        line-height: 1.75;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    @media (max-width: 850px) {
        .gjs-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 580px) {
        .gjs-grid {
            grid-template-columns: 1fr;
        }

        .gjs-header h2 {
            font-size: 22px;
        }

        .gjs-actions {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $hoy = now()->toDateString();

    $esVigente = $asignacionOperativa->estaVigente();

    $esFutura = $asignacionOperativa->estado
        && $asignacionOperativa->fecha_inicio
        && $asignacionOperativa->fecha_inicio->toDateString() > $hoy;

    $turno = $asignacionOperativa->turno;

    $horario = $turno
        ? substr((string) $turno->hora_inicio, 0, 5)
            . ' - '
            . substr((string) $turno->hora_fin, 0, 5)
        : 'No disponible';

    if ($turno?->cruza_medianoche) {
        $horario .= ' (día siguiente)';
    }

    $totalJornadas = $jornadas->total();

    $totalArqueosPagina = $jornadas->getCollection()
        ->sum(function ($jornada) {
            return $jornada->arqueos->count();
        });

    $totalEntrantesPagina = $jornadas->getCollection()
        ->sum(function ($jornada) {
            return $jornada->arqueosEntrantes->count();
        });
@endphp

<div class="gjs-page">

    <div class="gjs-header">
        <div>
            <h2>Detalle de asignación operativa</h2>
            <p>
                Registro #{{ $asignacionOperativa->id }}.
                Consulte la distribución asignada y su historial
                de jornadas y arqueos.
            </p>
        </div>

        <div class="gjs-actions">
            <a href="{{ route($rutaBase . '.jornadas.index') }}"
               class="gjs-btn gjs-btn-secondary">
                Volver al listado
            </a>

            @if($esVigente)
                <a href="{{ route(
                    $rutaBase . '.jornadas.edit',
                    $asignacionOperativa->id
                ) }}" class="gjs-btn">
                    Modificar asignación
                </a>
            @endif
        </div>
    </div>

    <div class="gjs-card">

        <div class="gjs-card-header">
            <div>
                <h3>Información de la asignación</h3>
                <p>Datos operativos y vigencia registrada.</p>
            </div>

            @if($esVigente)
                <span class="gjs-badge gjs-badge-success">
                    Vigente
                </span>
            @elseif($esFutura)
                <span class="gjs-badge gjs-badge-warning">
                    Próxima
                </span>
            @else
                <span class="gjs-badge gjs-badge-muted">
                    Finalizada
                </span>
            @endif
        </div>

        <div class="gjs-grid">

            <div class="gjs-field">
                <span class="gjs-field-label">Receptor</span>
                <span class="gjs-field-value">
                    {{ $asignacionOperativa->receptor?->nombre_completo
                        ?? 'Usuario #' . $asignacionOperativa->receptor_usuario_id }}
                </span>
                <span class="gjs-field-secondary">
                    Identificador de usuario:
                    {{ $asignacionOperativa->receptor_usuario_id }}
                </span>
            </div>

            <div class="gjs-field">
                <span class="gjs-field-label">Agencia</span>
                <span class="gjs-field-value">
                    {{ $asignacionOperativa->agencia?->nombre ?? 'N/D' }}
                </span>
                <span class="gjs-field-secondary">
                    {{ $asignacionOperativa->agencia?->codigo ?? '' }}
                </span>
            </div>

            <div class="gjs-field">
                <span class="gjs-field-label">Ventanilla</span>
                <span class="gjs-field-value">
                    {{ $asignacionOperativa->ventanilla?->codigo ?? 'N/D' }}
                </span>
                <span class="gjs-field-secondary">
                    {{ $asignacionOperativa->ventanilla?->descripcion ?? '' }}
                </span>
            </div>

            <div class="gjs-field">
                <span class="gjs-field-label">Turno</span>
                <span class="gjs-field-value">
                    {{ $turno?->nombre ?? 'N/D' }}
                </span>
                <span class="gjs-field-secondary">
                    {{ $horario }}
                </span>
            </div>

            <div class="gjs-field">
                <span class="gjs-field-label">Inicio de vigencia</span>
                <span class="gjs-field-value">
                    {{ $asignacionOperativa->fecha_inicio?->format('d/m/Y') ?? 'N/D' }}
                </span>
            </div>

            <div class="gjs-field">
                <span class="gjs-field-label">Finalización</span>
                <span class="gjs-field-value">
                    @if($asignacionOperativa->fecha_fin)
                        {{ $asignacionOperativa->fecha_fin->format('d/m/Y') }}
                    @else
                        Indefinida
                    @endif
                </span>
            </div>

            <div class="gjs-field">
                <span class="gjs-field-label">Registrada por</span>
                <span class="gjs-field-value">
                    {{ $asignacionOperativa->asignador?->nombre_completo
                        ?? 'Usuario #' . $asignacionOperativa->asignado_por }}
                </span>
            </div>

            <div class="gjs-field">
                <span class="gjs-field-label">Fecha de registro</span>
                <span class="gjs-field-value">
                    {{ $asignacionOperativa->created_at?->format('d/m/Y H:i') ?? 'N/D' }}
                </span>
            </div>

            <div class="gjs-field">
                <span class="gjs-field-label">Estado operativo</span>
                <span class="gjs-field-value">
                    @if($esVigente)
                        Asignación activa
                    @elseif($esFutura)
                        Pendiente de inicio
                    @else
                        Asignación finalizada
                    @endif
                </span>
            </div>

        </div>
    </div>

    @if($asignacionOperativa->motivo_cambio)
        <div class="gjs-card">
            <div class="gjs-card-header">
                <h3>Motivo de finalización o modificación</h3>
            </div>

            <div class="gjs-reason">{{ $asignacionOperativa->motivo_cambio }}</div>
        </div>
    @endif

    <div class="gjs-info">
        <strong>Control histórico:</strong>
        Las asignaciones operativas determinan la distribución
        del personal. Las jornadas diarias registran la actividad
        correspondiente a cada fecha y los arqueos conservan
        sus firmas y registros originales.
    </div>

    <div class="gjs-card">

        <div class="gjs-card-header">
            <div>
                <h3>Historial de jornadas</h3>
                <p>
                    {{ $totalJornadas }} jornadas encontradas.
                    En esta página:
                    {{ $totalArqueosPagina }} arqueos asociados
                    y {{ $totalEntrantesPagina }} registros de arqueo entrante.
                </p>
            </div>
        </div>

        @if($jornadas->count())

            <div class="gjs-table-wrapper">
                <table class="gjs-table">
                    <thead>
                        <tr>
                            <th>Fecha operativa</th>
                            <th>Horario</th>
                            <th>Estado</th>
                            <th>Arqueos</th>
                            <th>Arqueos entrantes</th>
                            <th>Identificador</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($jornadas as $jornada)
                            <tr>
                                <td>
                                    <span class="gjs-primary">
                                        {{ $jornada->fecha_operativa
                                            ? \Illuminate\Support\Carbon::parse($jornada->fecha_operativa)->format('d/m/Y')
                                            : 'N/D' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="gjs-primary">
                                        {{ $jornada->inicio_programado
                                            ? \Illuminate\Support\Carbon::parse($jornada->inicio_programado)->format('H:i')
                                            : 'N/D' }}
                                        -
                                        {{ $jornada->fin_programado
                                            ? \Illuminate\Support\Carbon::parse($jornada->fin_programado)->format('H:i')
                                            : 'N/D' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="gjs-primary">
                                        {{ str_replace('_', ' ', $jornada->estado ?? 'SIN ESTADO') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="gjs-count">
                                        {{ $jornada->arqueos->count() }}
                                    </span>
                                </td>

                                <td>
                                    <span class="gjs-count">
                                        {{ $jornada->arqueosEntrantes->count() }}
                                    </span>
                                </td>

                                <td>
                                    <span class="gjs-secondary">
                                        Jornada #{{ $jornada->id }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($jornadas->hasPages())
                <div class="gjs-pagination">
                    {{ $jornadas->links() }}
                </div>
            @endif

        @else
            <div class="gjs-empty">
                <h4>Sin jornadas registradas</h4>
                <p>
                    Todavía no existen jornadas diarias asociadas
                    a esta distribución operativa.
                    Las jornadas se generarán cuando corresponda
                    su fecha de operación y se utilice el módulo del receptor.
                </p>
            </div>
        @endif

    </div>

</div>
@endsection
