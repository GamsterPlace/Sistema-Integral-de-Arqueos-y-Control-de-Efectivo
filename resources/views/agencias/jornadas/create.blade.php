
@extends($rutaBase === 'jefecaja' ? 'layouts.jefe-caja' : 'layouts.subjefe-caja')

@section('title', 'Nueva Asignación Operativa')
@section('module-title', 'Gestión de Jornadas')

@push('styles')
<style>
    .gja-page {
        max-width: 1120px;
        margin: 0 auto;
        display: grid;
        gap: 22px;
        color: #193552;
    }

    .gja-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 18px;
    }

    .gja-header h2 {
        margin: 0;
        color: #10345b;
        font-size: 26px;
        font-weight: 800;
    }

    .gja-header p {
        margin: 8px 0 0;
        color: #687e94;
        font-size: 14px;
        line-height: 1.7;
        max-width: 780px;
    }

    .gja-card {
        background: #fff;
        border: 1px solid #dce5ef;
        border-radius: 14px;
        box-shadow: 0 5px 20px rgba(22, 48, 76, .045);
        overflow: hidden;
    }

    .gja-card-header {
        padding: 22px 25px;
        border-bottom: 1px solid #e7edf4;
    }

    .gja-card-header h3 {
        margin: 0;
        font-size: 17px;
        color: #173c64;
        font-weight: 800;
    }

    .gja-card-header p {
        margin: 7px 0 0;
        color: #74869a;
        font-size: 12px;
        line-height: 1.6;
    }

    .gja-form {
        padding: 25px;
    }

    .gja-section-title {
        font-size: 12px;
        font-weight: 800;
        color: #34638f;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin: 0 0 18px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e8eef5;
    }

    .gja-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .gja-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .gja-field-full {
        grid-column: 1 / -1;
    }

    .gja-field label {
        font-size: 13px;
        font-weight: 800;
        color: #344f6b;
    }

    .gja-required {
        color: #bd3944;
    }

    .gja-field select,
    .gja-field input {
        width: 100%;
        min-height: 45px;
        padding: 10px 13px;
        border: 1px solid #cbd9e7;
        border-radius: 9px;
        background: #fff;
        color: #284663;
        font: inherit;
        font-size: 13px;
    }

    .gja-field select:focus,
    .gja-field input:focus {
        outline: none;
        border-color: #3c84bf;
        box-shadow: 0 0 0 3px rgba(60, 132, 191, .12);
    }

    .gja-field select:disabled {
        background: #f2f5f8;
        color: #8997a6;
        cursor: not-allowed;
    }

    .gja-help {
        color: #7a8b9d;
        font-size: 11px;
        line-height: 1.6;
    }

    .gja-error {
        color: #b63842;
        font-size: 12px;
        font-weight: 600;
    }

    .gja-info {
        padding: 17px 20px;
        background: #eff6fd;
        border: 1px solid #cfe1f3;
        border-radius: 10px;
        color: #28547b;
        font-size: 13px;
        line-height: 1.75;
    }

    .gja-info strong {
        color: #17436c;
    }

    .gja-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .gja-summary-item {
        background: #f6f9fc;
        border: 1px solid #e2eaf2;
        border-radius: 9px;
        padding: 15px;
    }

    .gja-summary-item span {
        display: block;
        font-size: 11px;
        color: #75889c;
        margin-bottom: 7px;
    }

    .gja-summary-item strong {
        display: block;
        color: #234b70;
        font-size: 13px;
        line-height: 1.5;
    }

    .gja-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid #e7edf4;
    }

    .gja-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
        border: 1px solid transparent;
        border-radius: 9px;
        background: #14528c;
        color: #fff !important;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .gja-btn:hover {
        background: #0e3e6d;
    }

    .gja-btn-secondary {
        background: #fff;
        border-color: #d3dfeb;
        color: #2a4b69 !important;
    }

    .gja-btn-secondary:hover {
        background: #edf4fb;
    }

    .gja-alert-error {
        background: #fff0f0;
        border: 1px solid #efcaca;
        color: #9c3434;
        border-radius: 10px;
        padding: 15px 20px;
        font-size: 13px;
    }

    @media (max-width: 760px) {
        .gja-grid,
        .gja-summary {
            grid-template-columns: 1fr;
        }

        .gja-form {
            padding: 18px;
        }

        .gja-header h2 {
            font-size: 22px;
        }
    }
</style>
@endpush

@section('content')
@php
    $fechaMinima = now()->toDateString();

    $asignacionesDatos = $asignaciones->map(function ($asignacion) {
        return [
            'id' => (int) $asignacion->id,
            'agencia_id' => (int) $asignacion->agencia_id,
            'nombre' => $asignacion->usuario?->nombre_completo
                ?? 'Usuario #' . $asignacion->usuario_id,
            'fecha_inicio' => $asignacion->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $asignacion->fecha_fin?->format('Y-m-d'),
        ];
    })->values();

    $ventanillasDatos = $ventanillas->map(function ($ventanilla) {
        return [
            'id' => (int) $ventanilla->id,
            'agencia_id' => (int) $ventanilla->agencia_id,
            'nombre' => $ventanilla->codigo
                . ($ventanilla->descripcion ? ' - ' . $ventanilla->descripcion : ''),
        ];
    })->values();

    $turnosDatos = $turnos->map(function ($turno) {
        return [
            'id' => (int) $turno->id,
            'nombre' => $turno->nombre,
            'inicio' => substr((string) $turno->hora_inicio, 0, 5),
            'fin' => substr((string) $turno->hora_fin, 0, 5),
            'cruza_medianoche' => (bool) $turno->cruza_medianoche,
        ];
    })->values();
@endphp

<div class="gja-page">

    <div class="gja-header">
        <div>
            <h2>Nueva asignación operativa</h2>
            <p>
                Asigne un receptor a una ventanilla y turno de trabajo.
                La asignación tendrá vigencia indefinida y las jornadas
                diarias se generarán automáticamente.
            </p>
        </div>

        <a href="{{ route($rutaBase . '.jornadas.index') }}"
           class="gja-btn gja-btn-secondary">
            Volver al listado
        </a>
    </div>

    @if($errors->any())
        <div class="gja-alert-error">
            <strong>Revise la información ingresada:</strong>
            <ul style="margin: 8px 0 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="gja-card">

        <div class="gja-card-header">
            <h3>Datos de la asignación</h3>
            <p>
                Todos los campos son obligatorios.
                El sistema verificará que no existan horarios superpuestos.
            </p>
        </div>

        <form method="POST"
              action="{{ route($rutaBase . '.jornadas.store') }}"
              class="gja-form"
              id="formAsignacionOperativa">

            @csrf

            <h4 class="gja-section-title">Ubicación y receptor</h4>

            <div class="gja-grid">

                <div class="gja-field">
                    <label for="agencia_id">
                        Agencia <span class="gja-required">*</span>
                    </label>

                    <select name="agencia_id"
                            id="agencia_id"
                            required>
                        <option value="">Seleccione una agencia</option>

                        @foreach($agencias as $agencia)
                            <option value="{{ $agencia->id }}"
                                @selected(old('agencia_id') == $agencia->id)>
                                {{ $agencia->nombre }}
                            </option>
                        @endforeach
                    </select>

                    @error('agencia_id')
                        <span class="gja-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gja-field">
                    <label for="asignacion_personal_id">
                        Receptor <span class="gja-required">*</span>
                    </label>

                    <select name="asignacion_personal_id"
                            id="asignacion_personal_id"
                            required
                            disabled>
                        <option value="">Seleccione primero una agencia</option>
                    </select>

                    <span class="gja-help">
                        Se muestran únicamente receptores con asignación
                        de personal vigente para la fecha seleccionada.
                    </span>

                    @error('asignacion_personal_id')
                        <span class="gja-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gja-field">
                    <label for="ventanilla_id">
                        Ventanilla <span class="gja-required">*</span>
                    </label>

                    <select name="ventanilla_id"
                            id="ventanilla_id"
                            required
                            disabled>
                        <option value="">Seleccione primero una agencia</option>
                    </select>

                    @error('ventanilla_id')
                        <span class="gja-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gja-field">
                    <label for="fecha_inicio">
                        Inicio de vigencia <span class="gja-required">*</span>
                    </label>

                    <input type="date"
                           name="fecha_inicio"
                           id="fecha_inicio"
                           min="{{ $fechaMinima }}"
                           value="{{ old('fecha_inicio', $fechaMinima) }}"
                           required>

                    <span class="gja-help">
                        La asignación continuará vigente hasta que
                        el Jefe o Subjefe decida modificarla.
                    </span>

                    @error('fecha_inicio')
                        <span class="gja-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <h4 class="gja-section-title" style="margin-top: 30px;">
                Turno de trabajo
            </h4>

            <div class="gja-grid">

                <div class="gja-field gja-field-full">
                    <label for="turno_id">
                        Turno <span class="gja-required">*</span>
                    </label>

                    <select name="turno_id"
                            id="turno_id"
                            required>
                        <option value="">Seleccione un turno</option>

                        @foreach($turnos as $turno)
                            <option value="{{ $turno->id }}"
                                @selected(old('turno_id') == $turno->id)>
                                {{ $turno->nombre }}
                                ({{ substr((string)$turno->hora_inicio, 0, 5) }}
                                -
                                {{ substr((string)$turno->hora_fin, 0, 5) }})
                            </option>
                        @endforeach
                    </select>

                    @error('turno_id')
                        <span class="gja-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div class="gja-summary">
                <div class="gja-summary-item">
                    <span>Horario seleccionado</span>
                    <strong id="resumenHorario">
                        Seleccione un turno
                    </strong>
                </div>

                <div class="gja-summary-item">
                    <span>Tipo de vigencia</span>
                    <strong>Indefinida</strong>
                </div>

                <div class="gja-summary-item">
                    <span>Generación de jornadas</span>
                    <strong>Automática, por fecha operativa</strong>
                </div>
            </div>

            <div class="gja-info" style="margin-top: 22px;">
                <strong>Importante:</strong>
                Un receptor puede tener varias asignaciones en diferentes
                turnos, siempre que sus horarios no se superpongan.
                Una misma ventanilla también puede utilizarse en
                distintos turnos por diferentes receptores.
                Las jornadas y arqueos históricos no se modificarán.
            </div>

            <div class="gja-actions">
                <a href="{{ route($rutaBase . '.jornadas.index') }}"
                   class="gja-btn gja-btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="gja-btn">
                    Guardar asignación
                </button>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const asignaciones = @json($asignacionesDatos);
    const ventanillas = @json($ventanillasDatos);
    const turnos = @json($turnosDatos);

    const agenciaSelect = document.getElementById('agencia_id');
    const receptorSelect = document.getElementById('asignacion_personal_id');
    const ventanillaSelect = document.getElementById('ventanilla_id');
    const fechaInput = document.getElementById('fecha_inicio');
    const turnoSelect = document.getElementById('turno_id');
    const resumenHorario = document.getElementById('resumenHorario');

    const valoresAnteriores = {
        receptor: @json(old('asignacion_personal_id')),
        ventanilla: @json(old('ventanilla_id'))
    };

    function agregarOpcion(select, valor, texto) {
        const option = document.createElement('option');
        option.value = String(valor);
        option.textContent = texto;
        select.appendChild(option);
    }

    function actualizarReceptores() {
        const agenciaId = Number(agenciaSelect.value);
        const fecha = fechaInput.value;
        const seleccionado = receptorSelect.value
            || valoresAnteriores.receptor;

        receptorSelect.innerHTML = '';
        agregarOpcion(receptorSelect, '', 'Seleccione un receptor');

        if (!agenciaId || !fecha) {
            receptorSelect.disabled = true;
            return;
        }

        const disponibles = asignaciones.filter(function (item) {
            return item.agencia_id === agenciaId
                && item.fecha_inicio
                && item.fecha_inicio <= fecha
                && (!item.fecha_fin || item.fecha_fin >= fecha);
        });

        disponibles.forEach(function (item) {
            agregarOpcion(receptorSelect, item.id, item.nombre);
        });

        receptorSelect.disabled = disponibles.length === 0;

        if (disponibles.some(item => String(item.id) === String(seleccionado))) {
            receptorSelect.value = String(seleccionado);
        }

        valoresAnteriores.receptor = null;
    }

    function actualizarVentanillas() {
        const agenciaId = Number(agenciaSelect.value);
        const seleccionado = ventanillaSelect.value
            || valoresAnteriores.ventanilla;

        ventanillaSelect.innerHTML = '';
        agregarOpcion(ventanillaSelect, '', 'Seleccione una ventanilla');

        if (!agenciaId) {
            ventanillaSelect.disabled = true;
            return;
        }

        const disponibles = ventanillas.filter(function (item) {
            return item.agencia_id === agenciaId;
        });

        disponibles.forEach(function (item) {
            agregarOpcion(ventanillaSelect, item.id, item.nombre);
        });

        ventanillaSelect.disabled = disponibles.length === 0;

        if (disponibles.some(item => String(item.id) === String(seleccionado))) {
            ventanillaSelect.value = String(seleccionado);
        }

        valoresAnteriores.ventanilla = null;
    }

    function actualizarHorario() {
        const turno = turnos.find(function (item) {
            return item.id === Number(turnoSelect.value);
        });

        if (!turno) {
            resumenHorario.textContent = 'Seleccione un turno';
            return;
        }

        resumenHorario.textContent =
            turno.inicio + ' - ' + turno.fin
            + (turno.cruza_medianoche ? ' (día siguiente)' : '');
    }

    agenciaSelect.addEventListener('change', function () {
        receptorSelect.value = '';
        ventanillaSelect.value = '';
        actualizarReceptores();
        actualizarVentanillas();
    });

    fechaInput.addEventListener('change', actualizarReceptores);
    turnoSelect.addEventListener('change', actualizarHorario);

    actualizarReceptores();
    actualizarVentanillas();
    actualizarHorario();
});
</script>
@endsection
