
@extends('layouts.jefe-agencia')

@section('title', 'Monitoreo de Ventanillas')

@push('styles')
<style>
    .mv-page {
        display: grid;
        gap: 24px;
        color: #19344d;
    }

    .mv-page * {
        box-sizing: border-box;
    }

    .mv-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .mv-eyebrow {
        margin-bottom: 7px;
        color: #164c96;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .mv-header h1 {
        margin: 0;
        color: #06284f;
        font-size: clamp(24px, 3vw, 30px);
        font-weight: 850;
        letter-spacing: -.6px;
    }

    .mv-header p {
        max-width: 700px;
        margin: 9px 0 0;
        color: #718295;
        font-size: 13px;
        line-height: 1.7;
    }

    .mv-date-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px 17px;
        border: 1px solid #dfe7ee;
        border-radius: 12px;
        background: #fff;
        color: #164c96;
        font-size: 12px;
        font-weight: 800;
    }

    .mv-date-badge svg {
        width: 19px;
        height: 19px;
    }

    .mv-filters {
        display: flex;
        align-items: flex-end;
        gap: 15px;
        flex-wrap: wrap;
        padding: 20px;
        border: 1px solid #dfe7ee;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 6px 22px rgba(6, 40, 79, .035);
    }

    .mv-field {
        display: grid;
        gap: 7px;
        flex: 1 1 190px;
    }

    .mv-field label {
        color: #53677b;
        font-size: 11px;
        font-weight: 800;
    }

    .mv-field input,
    .mv-field select {
        width: 100%;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid #d8e2ec;
        border-radius: 10px;
        outline: none;
        background: #fff;
        color: #19344d;
        font-size: 13px;
    }

    .mv-field input:focus,
    .mv-field select:focus {
        border-color: #164c96;
        box-shadow: 0 0 0 3px rgba(22, 76, 150, .09);
    }

    .mv-filter-actions {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .mv-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 10px 17px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .mv-button-primary {
        background: #164c96;
        color: #fff;
    }

    .mv-button-primary:hover {
        background: #0c3b7b;
    }

    .mv-button-secondary {
        border-color: #d8e2ec;
        background: #f6f9fc;
        color: #53677b;
    }

    .mv-button-secondary:hover {
        background: #eaf1f8;
    }

    .mv-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
    }

    .mv-summary-card {
        position: relative;
        min-height: 125px;
        padding: 20px;
        overflow: hidden;
        border: 1px solid #dfe7ee;
        border-radius: 15px;
        background: #fff;
    }

    .mv-summary-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--mv-color, #164c96);
    }

    .mv-summary-label {
        display: block;
        color: #718295;
        font-size: 12px;
        font-weight: 700;
    }

    .mv-summary-value {
        display: block;
        margin-top: 14px;
        color: #06284f;
        font-size: 34px;
        font-weight: 850;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .mv-summary-note {
        display: block;
        margin-top: 10px;
        color: #8a98a6;
        font-size: 11px;
    }

    .mv-progress-panel {
        padding: 21px 23px;
        border: 1px solid #dfe7ee;
        border-radius: 15px;
        background: #fff;
    }

    .mv-progress-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .mv-progress-heading strong {
        color: #06284f;
        font-size: 15px;
    }

    .mv-progress-heading span {
        color: #164c96;
        font-size: 22px;
        font-weight: 850;
    }

    .mv-progress-track {
        height: 12px;
        overflow: hidden;
        border-radius: 30px;
        background: #e8eef4;
    }

    .mv-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #008d48, #00a651);
    }

    .mv-progress-description {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 13px;
        color: #718295;
        font-size: 12px;
        flex-wrap: wrap;
    }

    .mv-section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
    }

    .mv-section-heading h2 {
        margin: 0;
        color: #06284f;
        font-size: 19px;
        font-weight: 800;
    }

    .mv-section-heading p {
        margin: 5px 0 0;
        color: #8190a0;
        font-size: 12px;
    }

    .mv-legend {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .mv-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #617387;
        font-size: 11px;
        font-weight: 700;
    }

    .mv-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .mv-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 19px;
    }

    .mv-window {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #dfe7ee;
        border-top: 5px solid var(--window-color, #94a3b8);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 25px rgba(6, 40, 79, .04);
    }

    .mv-window.green {
        --window-color: #00a651;
        --window-bg: #e5f7ec;
        --window-text: #087d43;
    }

    .mv-window.red {
        --window-color: #d63e49;
        --window-bg: #ffebed;
        --window-text: #b52e39;
    }

    .mv-window.gray {
        --window-color: #94a3b8;
        --window-bg: #edf1f5;
        --window-text: #617387;
    }

    .mv-window-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 20px 15px;
    }

    .mv-window-number {
        color: #06284f;
        font-size: 19px;
        font-weight: 850;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .mv-window-description {
        margin-top: 5px;
        color: #8190a0;
        font-size: 12px;
        line-height: 1.5;
    }

    .mv-window-agency {
        display: block;
        margin-top: 8px;
        color: #164c96;
        font-size: 11px;
        font-weight: 800;
    }

    .mv-window-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex: 0 0 auto;
        padding: 7px 10px;
        border-radius: 8px;
        background: var(--window-bg);
        color: var(--window-text);
        font-size: 10px;
        font-weight: 850;
        white-space: nowrap;
    }

    .mv-window-status::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--window-color);
    }

    .mv-window-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin: 0 20px 17px;
        padding: 13px 9px;
        border: 1px solid #edf1f5;
        border-radius: 11px;
        background: #f8fafc;
        text-align: center;
    }

    .mv-window-stats strong {
        display: block;
        color: #06284f;
        font-size: 18px;
        font-weight: 850;
    }

    .mv-window-stats span {
        display: block;
        margin-top: 5px;
        color: #8291a0;
        font-size: 10px;
    }

    .mv-jornadas {
        display: grid;
        gap: 10px;
        padding: 0 20px 20px;
    }

    .mv-jornada {
        padding: 14px;
        border: 1px solid #e7edf3;
        border-radius: 12px;
        background: #fff;
    }

    .mv-jornada-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 9px;
        flex-wrap: wrap;
    }

    .mv-jornada-turno {
        color: #164c96;
        font-size: 12px;
        font-weight: 800;
    }

    .mv-jornada-status {
        padding: 5px 8px;
        border-radius: 7px;
        font-size: 10px;
        font-weight: 800;
    }

    .mv-jornada-status.done {
        background: #e5f7ec;
        color: #087d43;
    }

    .mv-jornada-status.pending {
        background: #ffebed;
        color: #b52e39;
    }

    .mv-receptor {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 13px;
    }

    .mv-receptor-avatar {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 10px;
        background: #eaf1fa;
        color: #164c96;
        font-size: 13px;
        font-weight: 850;
    }

    .mv-receptor-info {
        min-width: 0;
    }

    .mv-receptor-info strong {
        display: block;
        color: #19344d;
        font-size: 12px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .mv-receptor-info span {
        display: block;
        margin-top: 3px;
        color: #8190a0;
        font-size: 10px;
    }

    .mv-jornada-details {
        display: grid;
        gap: 8px;
        margin-top: 13px;
        padding-top: 12px;
        border-top: 1px solid #edf1f5;
    }

    .mv-detail-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        color: #718295;
        font-size: 11px;
        line-height: 1.5;
    }

    .mv-detail-row strong {
        color: #344c64;
        font-weight: 750;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .mv-empty-window {
        padding: 19px 15px;
        border: 1px dashed #d7e1eb;
        border-radius: 11px;
        background: #f8fafc;
        color: #8190a0;
        font-size: 12px;
        line-height: 1.6;
        text-align: center;
    }

    .mv-empty-global {
        padding: 55px 22px;
        border: 1px solid #dfe7ee;
        border-radius: 16px;
        background: #fff;
        text-align: center;
    }

    .mv-empty-global h3 {
        margin: 0;
        color: #06284f;
        font-size: 17px;
    }

    .mv-empty-global p {
        margin: 10px auto 0;
        max-width: 450px;
        color: #8190a0;
        font-size: 13px;
        line-height: 1.7;
    }

    .mv-notice {
        padding: 15px 18px;
        border: 1px solid #d7e5f4;
        border-radius: 12px;
        background: #f0f6fc;
        color: #416181;
        font-size: 12px;
        line-height: 1.7;
    }

    @media (max-width: 1350px) {
        .mv-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 950px) {
        .mv-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .mv-grid {
            grid-template-columns: 1fr;
        }

        .mv-header h1 {
            font-size: 24px;
        }

        .mv-filters {
            padding: 16px;
        }

        .mv-filter-actions {
            width: 100%;
        }

        .mv-filter-actions .mv-button {
            flex: 1;
        }
    }

    @media (max-width: 420px) {
        .mv-summary {
            grid-template-columns: 1fr;
        }

        .mv-window-header {
            flex-wrap: wrap;
        }
    }
</style>
@endpush

@section('content')

@php
    $fechaSeleccionada = $fecha->toDateString();

    $fechaLegible = $fecha
        ->locale('es')
        ->translatedFormat('d \d\e F \d\e Y');

    $estadosVentanilla = [
        'VERDE' => [
            'clase' => 'green',
            'etiqueta' => 'Completada',
        ],
        'ROJO' => [
            'clase' => 'red',
            'etiqueta' => 'Pendiente',
        ],
        'GRIS' => [
            'clase' => 'gray',
            'etiqueta' => 'Sin jornada',
        ],
    ];
@endphp

<div class="mv-page">

    <header class="mv-header">
        <div>
            <div class="mv-eyebrow">
                Supervisión operativa de agencia
            </div>

            <h1>Monitoreo de Ventanillas</h1>

            <p>
                Consulta el cumplimiento de arqueos de los receptores,
                los turnos asignados y el estado de cada ventanilla.
                La información corresponde únicamente a las agencias
                autorizadas para tu usuario.
            </p>
        </div>

        <div class="mv-date-badge">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <rect x="3" y="5" width="18" height="16" rx="2"/>
                <path d="M8 3v4M16 3v4M3 10h18"/>
            </svg>

            {{ $fechaLegible }}
        </div>
    </header>

    <form
        class="mv-filters"
        method="GET"
        action="{{ route('jefeagencia.monitoreo-ventanillas.index') }}"
    >
        <div class="mv-field">
            <label for="mv-fecha">
                Fecha operativa
            </label>

            <input
                type="date"
                id="mv-fecha"
                name="fecha"
                value="{{ $fechaSeleccionada }}"
                required
            >
        </div>

        <div class="mv-field">
            <label for="mv-agencia">
                Agencia
            </label>

            <select
                id="mv-agencia"
                name="agencia_id"
            >
                <option value="">
                    Todas mis agencias
                </option>

                @foreach($agencias as $agencia)
                    <option
                        value="{{ $agencia->id }}"
                        @selected(
                            (int) $agenciaSeleccionadaId
                            === (int) $agencia->id
                        )
                    >
                        {{ $agencia->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mv-filter-actions">
            <button
                type="submit"
                class="mv-button mv-button-primary"
            >
                Consultar
            </button>

            <a
                href="{{ route('jefeagencia.monitoreo-ventanillas.index') }}"
                class="mv-button mv-button-secondary"
            >
                Limpiar
            </a>
        </div>
    </form>

    <section class="mv-summary">

        <article
            class="mv-summary-card"
            style="--mv-color:#164c96"
        >
            <span class="mv-summary-label">
                Ventanillas activas
            </span>

            <strong class="mv-summary-value">
                {{ number_format($totalVentanillas) }}
            </strong>

            <span class="mv-summary-note">
                Ventanillas incluidas en la consulta
            </span>
        </article>

        <article
            class="mv-summary-card"
            style="--mv-color:#00a651"
        >
            <span class="mv-summary-label">
                Ventanillas completadas
            </span>

            <strong class="mv-summary-value">
                {{ number_format($ventanillasVerdes) }}
            </strong>

            <span class="mv-summary-note">
                Todas sus jornadas con arqueo
            </span>
        </article>

        <article
            class="mv-summary-card"
            style="--mv-color:#d63e49"
        >
            <span class="mv-summary-label">
                Ventanillas pendientes
            </span>

            <strong class="mv-summary-value">
                {{ number_format($ventanillasRojas) }}
            </strong>

            <span class="mv-summary-note">
                Una o más jornadas sin arqueo
            </span>
        </article>

        <article
            class="mv-summary-card"
            style="--mv-color:#94a3b8"
        >
            <span class="mv-summary-label">
                Sin jornada asignada
            </span>

            <strong class="mv-summary-value">
                {{ number_format($ventanillasGrises) }}
            </strong>

            <span class="mv-summary-note">
                Ventanillas sin asignación en la fecha
            </span>
        </article>

    </section>

    <section class="mv-progress-panel">

        <div class="mv-progress-heading">
            <strong>
                Cumplimiento de jornadas
            </strong>

            <span>
                {{ number_format($porcentajeCumplimiento, 1) }}%
            </span>
        </div>

        <div
            class="mv-progress-track"
            role="progressbar"
            aria-label="Porcentaje de jornadas con arqueo"
            aria-valuenow="{{ $porcentajeCumplimiento }}"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div
                class="mv-progress-fill"
                style="width:{{ $porcentajeCumplimiento }}%"
            ></div>
        </div>

        <div class="mv-progress-description">
            <span>
                Jornadas registradas:
                <strong>{{ $totalJornadas }}</strong>
            </span>

            <span>
                Con arqueo:
                <strong>{{ $jornadasRealizadas }}</strong>
            </span>

            <span>
                Pendientes:
                <strong>{{ $jornadasPendientes }}</strong>
            </span>
        </div>

    </section>

    <div class="mv-section-heading">
        <div>
            <h2>Estado de las ventanillas</h2>

            <p>
                Detalle individual por ventanilla y jornada
            </p>
        </div>

        <div class="mv-legend">
            <span class="mv-legend-item">
                <span
                    class="mv-legend-dot"
                    style="background:#00a651"
                ></span>
                Completada
            </span>

            <span class="mv-legend-item">
                <span
                    class="mv-legend-dot"
                    style="background:#d63e49"
                ></span>
                Pendiente
            </span>

            <span class="mv-legend-item">
                <span
                    class="mv-legend-dot"
                    style="background:#94a3b8"
                ></span>
                Sin jornada
            </span>
        </div>
    </div>

    @if($monitoreo->isEmpty())

        <div class="mv-empty-global">
            <h3>
                No hay ventanillas para mostrar
            </h3>

            <p>
                No se encontraron ventanillas activas en las
                agencias autorizadas para la consulta seleccionada.
            </p>
        </div>

    @else

        <section class="mv-grid">

            @foreach($monitoreo as $ventanilla)

                @php
                    $configuracionEstado =
                        $estadosVentanilla[
                            $ventanilla['estado_visual']
                        ] ?? $estadosVentanilla['GRIS'];

                    $claseVentanilla =
                        $configuracionEstado['clase'];

                    $etiquetaVentanilla =
                        $configuracionEstado['etiqueta'];
                @endphp

                <article
                    class="mv-window {{ $claseVentanilla }}"
                >

                    <header class="mv-window-header">

                        <div>
                            <div class="mv-window-number">
                                Ventanilla
                                {{ $ventanilla['codigo'] }}
                            </div>

                            @if(!empty($ventanilla['descripcion']))
                                <div class="mv-window-description">
                                    {{ $ventanilla['descripcion'] }}
                                </div>
                            @endif

                            <span class="mv-window-agency">
                                {{ $ventanilla['agencia'] }}
                            </span>
                        </div>

                        <span class="mv-window-status">
                            {{ $etiquetaVentanilla }}
                        </span>

                    </header>

                    <div class="mv-window-stats">

                        <div>
                            <strong>
                                {{ $ventanilla['total_jornadas'] }}
                            </strong>
                            <span>Jornadas</span>
                        </div>

                        <div>
                            <strong>
                                {{ $ventanilla['jornadas_realizadas'] }}
                            </strong>
                            <span>Realizadas</span>
                        </div>

                        <div>
                            <strong>
                                {{ $ventanilla['jornadas_pendientes'] }}
                            </strong>
                            <span>Pendientes</span>
                        </div>

                    </div>

                    <div class="mv-jornadas">

                        @forelse($ventanilla['jornadas'] as $jornada)

                            @php
                                $realizado =
                                    (bool) $jornada['arqueo_realizado'];

                                $nombreReceptor =
                                    $jornada['receptor'];

                                $inicial = mb_strtoupper(
                                    mb_substr(
                                        trim($nombreReceptor),
                                        0,
                                        1
                                    )
                                );

                                $inicioProgramado =
                                    $jornada['inicio_programado'];

                                $finProgramado =
                                    $jornada['fin_programado'];
                            @endphp

                            <div class="mv-jornada">

                                <div class="mv-jornada-heading">

                                    <span class="mv-jornada-turno">
                                        {{ $jornada['turno'] }}
                                    </span>

                                    <span
                                        class="mv-jornada-status {{ $realizado ? 'done' : 'pending' }}"
                                    >
                                        {{ $realizado ? 'Arqueo realizado' : 'Arqueo pendiente' }}
                                    </span>

                                </div>

                                <div class="mv-receptor">

                                    <div class="mv-receptor-avatar">
                                        {{ $inicial ?: 'R' }}
                                    </div>

                                    <div class="mv-receptor-info">
                                        <strong>
                                            {{ $nombreReceptor }}
                                        </strong>

                                        <span>
                                            Receptor asignado
                                        </span>
                                    </div>

                                </div>

                                <div class="mv-jornada-details">

                                    <div class="mv-detail-row">
                                        <span>
                                            Estado de jornada
                                        </span>

                                        <strong>
                                            {{ $jornada['estado_jornada'] ?: 'Sin estado' }}
                                        </strong>
                                    </div>

                                    <div class="mv-detail-row">
                                        <span>
                                            Horario programado
                                        </span>

                                        <strong>
                                            @if($inicioProgramado && $finProgramado)
                                                {{ $inicioProgramado->format('H:i') }}
                                                -
                                                {{ $finProgramado->format('H:i') }}
                                            @elseif($inicioProgramado)
                                                Desde
                                                {{ $inicioProgramado->format('H:i') }}
                                            @else
                                                No registrado
                                            @endif
                                        </strong>
                                    </div>

                                    @if($realizado)

                                        <div class="mv-detail-row">
                                            <span>
                                                Número de arqueo
                                            </span>

                                            <strong>
                                                {{ $jornada['numero_arqueo'] ?: '—' }}
                                            </strong>
                                        </div>

                                        <div class="mv-detail-row">
                                            <span>
                                                Estado del arqueo
                                            </span>

                                            <strong>
                                                {{ $jornada['estado_arqueo'] }}
                                            </strong>
                                        </div>

                                        <div class="mv-detail-row">
                                            <span>
                                                Total arqueado
                                            </span>

                                            <strong>
                                                Q {{ number_format(
                                                    (float) $jornada['total_arqueado'],
                                                    2
                                                ) }}
                                            </strong>
                                        </div>

                                        <div class="mv-detail-row">
                                            <span>
                                                Diferencia
                                            </span>

                                            <strong>
                                                Q {{ number_format(
                                                    (float) $jornada['diferencia'],
                                                    2
                                                ) }}
                                            </strong>
                                        </div>

                                    @else

                                        <div class="mv-detail-row">
                                            <span>
                                                Situación
                                            </span>

                                            <strong>
                                                Sin arqueo ordinario
                                                firmado y vigente
                                            </strong>
                                        </div>

                                    @endif

                                </div>

                            </div>

                        @empty

                            <div class="mv-empty-window">
                                No hay receptores ni jornadas
                                registrados para esta ventanilla
                                en la fecha consultada.
                            </div>

                        @endforelse

                    </div>

                </article>

            @endforeach

        </section>

    @endif

    <div class="mv-notice">
        <strong>Supervisión en modo consulta.</strong>
        Este módulo no permite crear, modificar, certificar
        ni anular arqueos. El color rojo indica que falta
        un arqueo válido en alguna jornada registrada,
        pero no necesariamente que el receptor esté atrasado.
        Los arqueos anulados no se consideran realizados.
    </div>

</div>

@endsection
