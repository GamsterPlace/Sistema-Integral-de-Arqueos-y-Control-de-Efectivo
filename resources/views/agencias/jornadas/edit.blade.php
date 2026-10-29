@extends($rutaBase === 'jefecaja' ? 'layouts.jefe-caja' : 'layouts.subjefe-caja')

@section('title', 'Editar Jornada')
@section('module-title', 'Editar Jornada')

@push('styles')
<style>
    .gjc-page{max-width:1050px;margin:0 auto;padding:24px 18px 48px;color:#183b5b}
    .gjc-page *{box-sizing:border-box}
    .gjc-heading{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;margin-bottom:24px}
    .gjc-heading h1{font-size:27px;font-weight:800;color:#0b345a;margin:0 0 7px}
    .gjc-heading p{margin:0;color:#647d91;font-size:14px;line-height:1.6}
    .gjc-back{color:#145c91;text-decoration:none;font-weight:750;font-size:13px}
    .gjc-card{background:#fff;border:1px solid #dce6ef;border-radius:15px;box-shadow:0 8px 25px rgba(19,57,92,.05);overflow:hidden;margin-bottom:18px}
    .gjc-card-title{padding:18px 22px;background:#f6f9fd;border-bottom:1px solid #e4edf4;color:#163f62;font-size:15px;font-weight:800}
    .gjc-card-body{padding:23px}
    .gjc-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
    .gjc-field label{display:block;color:#274d6c;font-size:13px;font-weight:750;margin-bottom:8px}
    .gjc-field select,.gjc-field input{width:100%;min-height:44px;border:1px solid #cbd9e5;border-radius:9px;padding:10px 12px;color:#173e5c;background:#fff;font:inherit;font-size:14px}
    .gjc-field select:focus,.gjc-field input:focus{outline:2px solid #badbf4;outline-offset:1px;border-color:#3986bd}
    .gjc-help{font-size:12px;line-height:1.55;color:#718397;margin-top:7px}
    .gjc-error{color:#a82836;font-size:12px;font-weight:700;margin-top:7px}
    .gjc-alert{padding:14px 17px;border:1px solid #edbdc1;background:#fff2f2;color:#8f2532;border-radius:10px;margin-bottom:20px;font-size:13px}
    .gjc-alert ul{margin:8px 0 0;padding-left:20px}
    .gjc-summary{background:#f4f9fd;border:1px solid #dce9f4;border-radius:11px;padding:16px 19px;font-size:13px;line-height:1.75;color:#315573}
    .gjc-summary strong{color:#153f63}
    .gjc-actions{display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-wrap:wrap;margin-top:22px}
    .gjc-cancel{padding:12px 18px;color:#42627b;text-decoration:none;font-weight:750;font-size:13px}
    .gjc-submit{background:#0b568f;border:1px solid #0b568f;color:#fff;padding:13px 22px;border-radius:10px;font:inherit;font-size:13px;font-weight:800;cursor:pointer}
    .gjc-submit:hover{background:#08446f}
    .gjc-submit:disabled{opacity:.55;cursor:not-allowed}
    @media(max-width:650px){.gjc-page{padding:16px 10px 35px}.gjc-grid{grid-template-columns:1fr}.gjc-card-body{padding:17px}.gjc-heading h1{font-size:23px}}
</style>
@endpush

@section('content')
<div class="gjc-page">
    <div class="gjc-heading">
        <div>
            <h1>Editar jornada #{{ $jornada->id }}</h1>
            <p>Actualice la programación antes de que la jornada inicie y sin alterar registros de arqueo.</p>
        </div>
        <a class="gjc-back" href="{{ route($rutaBase.'.jornadas.show', $jornada->id) }}">← Volver al detalle</a>
    </div>

    @if($errors->any())
        <div class="gjc-alert" role="alert">
            <strong>No fue posible actualizar la jornada. Revise los datos:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route($rutaBase.'.jornadas.update', $jornada->id) }}" method="POST" id="form-jornada">
        @csrf
        @method('PUT')
        <section class="gjc-card" aria-label="Condiciones de edición">
            <div class="gjc-card-title">Condiciones de edición</div>
            <div class="gjc-card-body">
                <div class="gjc-summary">
                    <strong>Jornada #{{ $jornada->id }}</strong> · Estado: {{ $jornada->estado }}.
                    Puede modificar el receptor, la agencia autorizada, la ventanilla, el turno y la fecha, siempre que no exista actividad ni registros asociados. El cambio no elimina el historial de arqueos.
                </div>
            </div>
        </section>

        <section class="gjc-card">
            <div class="gjc-card-title">Datos de la programación</div>
            <div class="gjc-card-body">
                <div class="gjc-grid">
                    <div class="gjc-field">
                        <label for="agencia_id">Agencia *</label>
                        <select id="agencia_id" name="agencia_id" required>
                            <option value="">Seleccione una agencia</option>
                            @foreach($agencias as $agencia)
                                <option value="{{ $agencia->id }}" @selected((string)old('agencia_id', $jornada->agencia_id) === (string)$agencia->id)>{{ $agencia->nombre ?? $agencia->codigo ?? ('Agencia #'.$agencia->id) }}</option>
                            @endforeach
                        </select>
                        @error('agencia_id')<div class="gjc-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="gjc-field">
                        <label for="fecha_operativa">Fecha operativa *</label>
                        <input type="date" id="fecha_operativa" name="fecha_operativa" min="{{ today()->toDateString() }}" value="{{ old('fecha_operativa', $jornada->fecha_operativa?->format('Y-m-d')) }}" required>
                        <div class="gjc-help">Solo se permiten jornadas de hoy o fechas futuras.</div>
                        @error('fecha_operativa')<div class="gjc-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="gjc-field">
                        <label for="asignacion_personal_id">Receptor *</label>
                        <select id="asignacion_personal_id" name="asignacion_personal_id" required>
                            <option value="">Seleccione primero una agencia</option>
                            @foreach($asignaciones as $asignacion)
                                <option value="{{ $asignacion->id }}" data-agencia="{{ $asignacion->agencia_id }}" data-inicio="{{ $asignacion->fecha_inicio?->format('Y-m-d') }}" data-fin="{{ $asignacion->fecha_fin?->format('Y-m-d') }}" @selected((string)old('asignacion_personal_id', $jornada->asignacion_personal_id) === (string)$asignacion->id)>{{ $asignacion->usuario?->nombre_completo ?? ('Usuario #'.$asignacion->usuario_id) }} — {{ $asignacion->tipo }}</option>
                            @endforeach
                        </select>
                        <div class="gjc-help">Solo se muestran asignaciones vigentes para la agencia y fecha elegidas.</div>
                        @error('asignacion_personal_id')<div class="gjc-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="gjc-field">
                        <label for="ventanilla_id">Ventanilla *</label>
                        <select id="ventanilla_id" name="ventanilla_id" required>
                            <option value="">Seleccione primero una agencia</option>
                            @foreach($ventanillas as $ventanilla)
                                <option value="{{ $ventanilla->id }}" data-agencia="{{ $ventanilla->agencia_id }}" @selected((string)old('ventanilla_id', $jornada->ventanilla_id) === (string)$ventanilla->id)>{{ $ventanilla->codigo }}{{ $ventanilla->descripcion ? ' — '.$ventanilla->descripcion : '' }}</option>
                            @endforeach
                        </select>
                        @error('ventanilla_id')<div class="gjc-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="gjc-field">
                        <label for="turno_id">Turno *</label>
                        <select id="turno_id" name="turno_id" required>
                            <option value="">Seleccione un turno</option>
                            @foreach($turnos as $turno)
                                <option value="{{ $turno->id }}" data-nombre="{{ $turno->nombre }}" data-inicio="{{ substr((string)$turno->hora_inicio, 0, 5) }}" data-fin="{{ substr((string)$turno->hora_fin, 0, 5) }}" data-medianoche="{{ $turno->cruza_medianoche ? '1' : '0' }}" @selected((string)old('turno_id', $jornada->turno_id) === (string)$turno->id)>{{ $turno->nombre }} ({{ substr((string)$turno->hora_inicio, 0, 5) }} – {{ substr((string)$turno->hora_fin, 0, 5) }}{{ $turno->cruza_medianoche ? ', día siguiente' : '' }})</option>
                            @endforeach
                        </select>
                        @error('turno_id')<div class="gjc-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="gjc-card">
            <div class="gjc-card-title">Resumen de la jornada</div>
            <div class="gjc-card-body">
                <div class="gjc-summary" id="resumen-jornada" aria-live="polite">Complete los datos para visualizar el resumen de la programación.</div>
                <div class="gjc-help" style="margin-top:12px">Al guardar, el sistema verificará la vigencia de la asignación, la disponibilidad de la ventanilla y los conflictos de horario. No se permite modificar jornadas con arqueos, habilitaciones o actividad registrada.</div>
                <div class="gjc-actions">
                    <a class="gjc-cancel" href="{{ route($rutaBase.'.jornadas.show', $jornada->id) }}">Cancelar</a>
                    <button class="gjc-submit" type="submit">Guardar cambios</button>
                </div>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const agencia = document.getElementById('agencia_id');
    const fecha = document.getElementById('fecha_operativa');
    const receptor = document.getElementById('asignacion_personal_id');
    const ventanilla = document.getElementById('ventanilla_id');
    const turno = document.getElementById('turno_id');
    const resumen = document.getElementById('resumen-jornada');
    const opcionesReceptor = Array.from(receptor.options).filter(o => o.value);
    const opcionesVentanilla = Array.from(ventanilla.options).filter(o => o.value);

    function filtrar(select, opciones, vigente) {
        const anterior = select.value;
        opciones.forEach(o => {
            o.hidden = !vigente(o);
            o.disabled = !vigente(o);
        });
        if (!Array.from(select.options).some(o => o.value === anterior && !o.disabled)) select.value = '';
    }
    function actualizar() {
        const idAgencia = agencia.value;
        const dia = fecha.value;
        filtrar(receptor, opcionesReceptor, o => o.dataset.agencia === idAgencia && dia && (!o.dataset.inicio || o.dataset.inicio <= dia) && (!o.dataset.fin || o.dataset.fin >= dia));
        filtrar(ventanilla, opcionesVentanilla, o => o.dataset.agencia === idAgencia);
        const agenciaNombre = agencia.selectedOptions[0]?.textContent || '';
        const receptorNombre = receptor.selectedOptions[0]?.value ? receptor.selectedOptions[0].textContent : 'Sin seleccionar';
        const ventanillaNombre = ventanilla.selectedOptions[0]?.value ? ventanilla.selectedOptions[0].textContent : 'Sin seleccionar';
        const turnoOpcion = turno.selectedOptions[0];
        const turnoNombre = turnoOpcion?.value ? turnoOpcion.textContent : 'Sin seleccionar';
        resumen.innerHTML = '';
        const datos = [
            ['Agencia', idAgencia ? agenciaNombre : 'Sin seleccionar'],
            ['Fecha operativa', dia || 'Sin seleccionar'],
            ['Receptor', receptorNombre],
            ['Ventanilla', ventanillaNombre],
            ['Turno', turnoNombre]
        ];
        datos.forEach(([titulo, valor]) => {
            const fila = document.createElement('div');
            const negrita = document.createElement('strong');
            negrita.textContent = titulo + ': ';
            fila.appendChild(negrita);
            fila.appendChild(document.createTextNode(valor));
            resumen.appendChild(fila);
        });
    }
    [agencia, fecha, receptor, ventanilla, turno].forEach(el => el.addEventListener('change', actualizar));
    actualizar();
});
</script>
@endpush
