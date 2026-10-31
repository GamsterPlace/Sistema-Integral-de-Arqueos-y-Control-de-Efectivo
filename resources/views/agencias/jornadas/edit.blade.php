
@extends($rutaBase === 'jefecaja' ? 'layouts.jefe-caja' : 'layouts.subjefe-caja')

@section('title', 'Modificar Asignación Operativa')
@section('module-title', 'Gestión de Jornadas')

@push('styles')
<style>
    .gae-page {
        max-width: 1120px;
        margin: 0 auto;
        display: grid;
        gap: 22px;
        color: #193552;
    }

    .gae-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 18px;
    }

    .gae-header h2 {
        margin: 0;
        color: #10345b;
        font-size: 26px;
        font-weight: 800;
    }

    .gae-header p {
        margin: 8px 0 0;
        color: #687e94;
        font-size: 14px;
        line-height: 1.7;
        max-width: 780px;
    }

    .gae-card {
        background: #fff;
        border: 1px solid #dce5ef;
        border-radius: 14px;
        box-shadow: 0 5px 20px rgba(22, 48, 76, .045);
        overflow: hidden;
    }

    .gae-card-header {
        padding: 21px 25px;
        border-bottom: 1px solid #e7edf4;
    }

    .gae-card-header h3 {
        margin: 0;
        color: #173c64;
        font-size: 17px;
        font-weight: 800;
    }

    .gae-card-header p {
        margin: 7px 0 0;
        color: #74869a;
        font-size: 12px;
        line-height: 1.6;
    }

    .gae-body {
        padding: 25px;
    }

    .gae-current {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
    }

    .gae-current-item {
        padding: 15px;
        border: 1px solid #e0e8f0;
        border-radius: 9px;
        background: #f6f9fc;
    }

    .gae-current-item span {
        display: block;
        margin-bottom: 7px;
        color: #788a9d;
        font-size: 11px;
    }

    .gae-current-item strong {
        display: block;
        color: #234b70;
        font-size: 13px;
        line-height: 1.5;
    }

    .gae-section-title {
        margin: 0 0 18px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e8eef5;
        color: #34638f;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .07em;
    }

    .gae-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .gae-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .gae-field-full {
        grid-column: 1 / -1;
    }

    .gae-field label {
        color: #344f6b;
        font-size: 13px;
        font-weight: 800;
    }

    .gae-required {
        color: #bd3944;
    }

    .gae-field select,
    .gae-field input,
    .gae-field textarea {
        width: 100%;
        min-height: 45px;
        padding: 10px 13px;
        border: 1px solid #cbd9e7;
        border-radius: 9px;
        background: #fff;
        color: #284663;
        font: inherit;
        font-size: 13px;
        box-sizing: border-box;
    }

    .gae-field textarea {
        min-height: 115px;
        resize: vertical;
        line-height: 1.6;
    }

    .gae-field select:focus,
    .gae-field input:focus,
    .gae-field textarea:focus {
        outline: none;
        border-color: #3c84bf;
        box-shadow: 0 0 0 3px rgba(60, 132, 191, .12);
    }

    .gae-field select:disabled {
        background: #f2f5f8;
        color: #8997a6;
    }

    .gae-help {
        color: #7a8b9d;
        font-size: 11px;
        line-height: 1.6;
    }

    .gae-error {
        color: #b63842;
        font-size: 12px;
        font-weight: 600;
    }

    .gae-info {
        padding: 17px 20px;
        border: 1px solid #cfe1f3;
        border-radius: 10px;
        background: #eff6fd;
        color: #28547b;
        font-size: 13px;
        line-height: 1.75;
    }

    .gae-warning {
        padding: 17px 20px;
        border: 1px solid #eed9a9;
        border-radius: 10px;
        background: #fff8e9;
        color: #80601e;
        font-size: 13px;
        line-height: 1.75;
    }

    .gae-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid #e7edf4;
    }

    .gae-btn {
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
    }

    .gae-btn:hover {
        background: #0e3e6d;
    }

    .gae-btn-secondary {
        background: #fff;
        border-color: #d3dfeb;
        color: #2a4b69 !important;
    }

    .gae-btn-secondary:hover {
        background: #edf4fb;
    }

    .gae-alert-error {
        padding: 15px 20px;
        border: 1px solid #efcaca;
        border-radius: 10px;
        background: #fff0f0;
        color: #9c3434;
        font-size: 13px;
    }

    @media (max-width: 760px) {
        .gae-grid,
        .gae-current {
            grid-template-columns: 1fr;
        }

        .gae-body {
            padding: 18px;
        }

        .gae-header h2 {
            font-size: 22px;
        }
    }
</style>
@endpush

@section('content')
@php
    $fechaMinima = now()->toDateString();

    $asignacionesDatos = $asignaciones->map(function ($item) {
        return [
            'id' => (int) $item->id,
            'agencia_id' => (int) $item->agencia_id,
            'nombre' => $item->usuario?->nombre_completo
                ?? 'Usuario #' . $item->usuario_id,
            'fecha_inicio' => $item->fecha_inicio
                ? \Illuminate\Support\Carbon::parse($item->fecha_inicio)->format('Y-m-d')
                : null,
            'fecha_fin' => $item->fecha_fin
                ? \Illuminate\Support\Carbon::parse($item->fecha_fin)->format('Y-m-d')
                : null,
        ];
    })->values();

    $ventanillasDatos = $ventanillas->map(function ($item) {
        return [
            'id' => (int) $item->id,
            'agencia_id' => (int) $item->agencia_id,
            'nombre' => $item->codigo
                . ($item->descripcion ? ' - ' . $item->descripcion : ''),
        ];
    })->values();

    $turnosDatos = $turnos->map(function ($item) {
        return [
            'id' => (int) $item->id,
            'nombre' => $item->nombre,
            'inicio' => substr((string) $item->hora_inicio, 0, 5),
            'fin' => substr((string) $item->hora_fin, 0, 5),
            'cruza_medianoche' => (bool) $item->cruza_medianoche,
        ];
    })->values();
@endphp

<div class="gae-page">

    <div class="gae-header">
        <div>
            <h2>Modificar asignación operativa</h2>
            <p>
                Actualice la distribución del receptor, ventanilla o turno.
                El sistema conservará el registro anterior y creará
                una nueva asignación con los cambios autorizados.
            </p>
        </div>

        <a href="{{ route($rutaBase . '.jornadas.show', $asignacionOperativa->id) }}"
           class="gae-btn gae-btn-secondary">
            Volver al detalle
        </a>
    </div>

    @if($errors->any())
        <div class="gae-alert-error">
            <strong>No se pudo completar la modificación:</strong>
            <ul style="margin: 8px 0 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="gae-card">

        <div class="gae-card-header">
            <h3>Asignación actual</h3>
            <p>
                Registro #{{ $asignacionOperativa->id }}.
                Esta información se conservará en el historial.
            </p>
        </div>

        <div class="gae-body">
            <div class="gae-current">

                <div class="gae-current-item">
                    <span>Receptor actual</span>
                    <strong>
                        {{ $asignacionOperativa->receptor?->nombre_completo
                            ?? 'Usuario #' . $asignacionOperativa->receptor_usuario_id }}
                    </strong>
                </div>

                <div class="gae-current-item">
                    <span>Agencia y ventanilla</span>
                    <strong>
                        {{ $asignacionOperativa->agencia?->nombre ?? 'N/D' }}
                        /
                        {{ $asignacionOperativa->ventanilla?->codigo ?? 'N/D' }}
                    </strong>
                </div>

                <div class="gae-current-item">
                    <span>Turno actual</span>
                    <strong>
                        {{ $asignacionOperativa->turno?->nombre ?? 'N/D' }}
                        @if($asignacionOperativa->turno)
                            ({{ substr((string)$asignacionOperativa->turno->hora_inicio, 0, 5) }}
                            -
                            {{ substr((string)$asignacionOperativa->turno->hora_fin, 0, 5) }})
                        @endif
                    </strong>
                </div>

            </div>
        </div>
    </div>

    <div class="gae-warning">
        <strong>Control de seguridad:</strong>
        Si la jornada actual ya tiene actividad, arqueos o firmas,
        el sistema impedirá modificar la asignación hasta completar
        el procedimiento correspondiente de entrega de ventanilla.
        Ningún arqueo histórico será alterado.
    </div>

    <div class="gae-card">

        <div class="gae-card-header">
            <h3>Nueva distribución operativa</h3>
            <p>
                Seleccione los datos que tendrá la nueva asignación.
                Debe indicar el motivo del cambio.
            </p>
        </div>

        <form method="POST"
              action="{{ route($rutaBase . '.jornadas.update', $asignacionOperativa->id) }}"
              class="gae-body"
              id="formModificarAsignacion">

            @csrf
            @method('PUT')

            <h4 class="gae-section-title">Ubicación y receptor</h4>

            <div class="gae-grid">

                <div class="gae-field">
                    <label for="agencia_id">
                        Agencia <span class="gae-required">*</span>
                    </label>

                    <select name="agencia_id"
                            id="agencia_id"
                            required>
                        <option value="">Seleccione una agencia</option>

                        @foreach($agencias as $agencia)
                            <option value="{{ $agencia->id }}"
                                @selected(
                                    old('agencia_id', $asignacionOperativa->agencia_id)
                                    == $agencia->id
                                )>
                                {{ $agencia->nombre }}
                            </option>
                        @endforeach
                    </select>

                    @error('agencia_id')
                        <span class="gae-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gae-field">
                    <label for="asignacion_personal_id">
                        Receptor <span class="gae-required">*</span>
                    </label>

                    <select name="asignacion_personal_id"
                            id="asignacion_personal_id"
                            required>
                        <option value="">Seleccione un receptor</option>
                    </select>

                    @error('asignacion_personal_id')
                        <span class="gae-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gae-field">
                    <label for="ventanilla_id">
                        Ventanilla <span class="gae-required">*</span>
                    </label>

                    <select name="ventanilla_id"
                            id="ventanilla_id"
                            required>
                        <option value="">Seleccione una ventanilla</option>
                    </select>

                    @error('ventanilla_id')
                        <span class="gae-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gae-field">
                    <label for="fecha_inicio">
                        Inicio de la nueva asignación
                        <span class="gae-required">*</span>
                    </label>

                    <input type="date"
                           name="fecha_inicio"
                           id="fecha_inicio"
                           min="{{ $fechaMinima }}"
                           value="{{ old('fecha_inicio', $fechaMinima) }}"
                           required>

                    <span class="gae-help">
                        La nueva asignación tendrá vigencia indefinida.
                    </span>

                    @error('fecha_inicio')
                        <span class="gae-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gae-field gae-field-full">
                    <label for="turno_id">
                        Turno <span class="gae-required">*</span>
                    </label>

                    <select name="turno_id"
                            id="turno_id"
                            required>
                        <option value="">Seleccione un turno</option>

                        @foreach($turnos as $turno)
                            <option value="{{ $turno->id }}"
                                @selected(
                                    old('turno_id', $asignacionOperativa->turno_id)
                                    == $turno->id
                                )>
                                {{ $turno->nombre }}
                                ({{ substr((string)$turno->hora_inicio, 0, 5) }}
                                -
                                {{ substr((string)$turno->hora_fin, 0, 5) }})
                            </option>
                        @endforeach
                    </select>

                    @error('turno_id')
                        <span class="gae-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gae-field gae-field-full">
                    <label for="motivo_cambio">
                        Motivo de la modificación
                        <span class="gae-required">*</span>
                    </label>

                    <textarea name="motivo_cambio"
                              id="motivo_cambio"
                              minlength="10"
                              maxlength="500"
                              required
                              placeholder="Describa el motivo por el cual se modifica esta asignación operativa...">{{ old('motivo_cambio') }}</textarea>

                    <span class="gae-help">
                        Entre 10 y 500 caracteres. El motivo quedará
                        registrado en el historial de la asignación anterior.
                    </span>

                    @error('motivo_cambio')
                        <span class="gae-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div class="gae-info" style="margin-top: 24px;">
                <strong>Conservación del historial:</strong>
                Al guardar, la asignación anterior se marcará como
                finalizada y se creará un nuevo registro. Los arqueos,
                jornadas y firmas previamente registrados permanecerán
                asociados a sus respectivos registros históricos.
            </div>

            <div class="gae-actions">
                <a href="{{ route($rutaBase . '.jornadas.show', $asignacionOperativa->id) }}"
                   class="gae-btn gae-btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="gae-btn">
                    Guardar modificación
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

    const agenciaSelect = document.getElementById('agencia_id');
    const receptorSelect = document.getElementById('asignacion_personal_id');
    const ventanillaSelect = document.getElementById('ventanilla_id');
    const fechaInput = document.getElementById('fecha_inicio');

    const valoresIniciales = {
        receptor: @json(old('asignacion_personal_id', $asignacionOperativa->asignacion_personal_id)),
        ventanilla: @json(old('ventanilla_id', $asignacionOperativa->ventanilla_id))
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
        const seleccionado = receptorSelect.value || valoresIniciales.receptor;

        receptorSelect.innerHTML = '';
        agregarOpcion(receptorSelect, '', 'Seleccione un receptor');

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

        valoresIniciales.receptor = null;
    }

    function actualizarVentanillas() {
        const agenciaId = Number(agenciaSelect.value);
        const seleccionado = ventanillaSelect.value || valoresIniciales.ventanilla;

        ventanillaSelect.innerHTML = '';
        agregarOpcion(ventanillaSelect, '', 'Seleccione una ventanilla');

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

        valoresIniciales.ventanilla = null;
    }

    agenciaSelect.addEventListener('change', function () {
        receptorSelect.value = '';
        ventanillaSelect.value = '';
        actualizarReceptores();
        actualizarVentanillas();
    });

    fechaInput.addEventListener('change', actualizarReceptores);

    actualizarReceptores();
    actualizarVentanillas();
});
</script>
@endsection
