@extends($rutaBase === 'jefecaja' ? 'layouts.jefe-caja' : 'layouts.subjefe-caja')

@section('title', 'Detalle de Jornada')
@section('module-title', 'Detalle de Jornada')

@push('styles')
<style>
    .gjs-page{max-width:1120px;margin:0 auto;padding:24px 18px 45px;color:#173954}
    .gjs-page *{box-sizing:border-box}
    .gjs-head{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:18px;margin-bottom:22px}
    .gjs-head h1{margin:0 0 7px;color:#0a3358;font-size:27px;font-weight:800}
    .gjs-head p{margin:0;color:#6a7e90;font-size:14px}
    .gjs-actions{display:flex;gap:9px;flex-wrap:wrap;align-items:center}
    .gjs-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none!important;padding:11px 16px;border-radius:9px;font-size:13px;font-weight:800;border:1px solid #cbdce9;color:#285779;background:#fff}
    .gjs-btn-primary{background:#0b548e;border-color:#0b548e;color:#fff!important}
    .gjs-btn-primary:hover{background:#083f6a}
    .gjs-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-bottom:18px}
    .gjs-card{background:#fff;border:1px solid #dce6ef;border-radius:14px;overflow:hidden;box-shadow:0 6px 22px rgba(20,58,89,.045)}
    .gjs-card-title{padding:16px 20px;background:#f5f9fc;border-bottom:1px solid #e5edf4;font-size:14px;color:#153e60;font-weight:800}
    .gjs-card-body{padding:18px 20px}
    .gjs-field{margin-bottom:15px}
    .gjs-field:last-child{margin-bottom:0}
    .gjs-label{font-size:11px;letter-spacing:.035em;text-transform:uppercase;color:#6a8093;font-weight:800;margin-bottom:5px}
    .gjs-value{font-size:14px;color:#173b5b;font-weight:700;overflow-wrap:anywhere}
    .gjs-muted{color:#6c8091;font-weight:500;font-size:12px;margin-top:3px}
    .gjs-badge{display:inline-flex;padding:5px 11px;border-radius:20px;background:#e8f2fc;color:#17527e;font-size:12px;font-weight:800}
    .gjs-badge-complete{background:#e7f5ec;color:#26734a}
    .gjs-panel{margin-top:18px}
    .gjs-table-wrap{overflow-x:auto}
    .gjs-table{width:100%;border-collapse:collapse;font-size:13px;text-align:left}
    .gjs-table th{background:#f8fafc;color:#527088;text-transform:uppercase;letter-spacing:.03em;font-size:11px;font-weight:800;padding:12px 17px}
    .gjs-table td{padding:13px 17px;border-top:1px solid #e9eff4;color:#244760}
    .gjs-empty{padding:22px 20px;color:#718596;font-size:13px}
    .gjs-alert{padding:13px 16px;margin-bottom:18px;border-radius:10px;background:#e9f7ee;border:1px solid #bde3cb;color:#226c42;font-size:13px;font-weight:700}
    .gjs-note{padding:14px 17px;margin-top:18px;background:#f5f8fc;border:1px solid #dce6ef;border-radius:10px;color:#526c82;font-size:12px;line-height:1.6}
    @media(max-width:740px){.gjs-grid{grid-template-columns:1fr}.gjs-head h1{font-size:23px}.gjs-page{padding:16px 10px 30px}}
</style>
@endpush

@section('content')
@php
    $tieneRegistros = $jornada->arqueos->isNotEmpty() || $jornada->arqueosEntrantes->isNotEmpty() || $jornada->habilitaciones->isNotEmpty();
    $fechaPasada = $jornada->fecha_operativa && $jornada->fecha_operativa->copy()->startOfDay()->lt(today());
    $puedeEditar = !$fechaPasada && $jornada->estado === 'PROGRAMADA' && !$jornada->inicio_real && !$jornada->fin_real && !$tieneRegistros;
    $arqueosRelacionados = $jornada->arqueos->concat($jornada->arqueosEntrantes)->unique('id');
@endphp
<div class="gjs-page">
    <div class="gjs-head">
        <div>
            <h1>Jornada #{{ $jornada->id }}</h1>
            <p>Consulta de la programación y los registros asociados al receptor.</p>
        </div>
        <div class="gjs-actions">
            <a class="gjs-btn" href="{{ route($rutaBase.'.jornadas.index') }}">Volver al listado</a>
            @if($puedeEditar)
                <a class="gjs-btn gjs-btn-primary" href="{{ route($rutaBase.'.jornadas.edit', $jornada->id) }}">Editar jornada</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="gjs-alert" role="status">{{ session('success') }}</div>
    @endif

    <div class="gjs-grid">
        <section class="gjs-card">
            <div class="gjs-card-title">Información del receptor</div>
            <div class="gjs-card-body">
                <div class="gjs-field">
                    <div class="gjs-label">Receptor asignado</div>
                    <div class="gjs-value">{{ $jornada->receptor?->nombre_completo ?? ('Usuario #'.$jornada->receptor_usuario_id) }}</div>
                    <div class="gjs-muted">Identificador de usuario: {{ $jornada->receptor_usuario_id }}</div>
                </div>
                <div class="gjs-field">
                    <div class="gjs-label">Agencia</div>
                    <div class="gjs-value">{{ $jornada->agencia?->nombre ?? $jornada->agencia?->codigo ?? ('Agencia #'.$jornada->agencia_id) }}</div>
                </div>
                <div class="gjs-field">
                    <div class="gjs-label">Ventanilla</div>
                    <div class="gjs-value">{{ $jornada->ventanilla?->codigo ?? ('Ventanilla #'.$jornada->ventanilla_id) }}</div>
                    @if($jornada->ventanilla?->descripcion)
                        <div class="gjs-muted">{{ $jornada->ventanilla->descripcion }}</div>
                    @endif
                </div>
                <div class="gjs-field">
                    <div class="gjs-label">Tipo de asignación de personal</div>
                    <div class="gjs-value">{{ $jornada->asignacionPersonal?->tipo ?? 'No disponible' }}</div>
                </div>
            </div>
        </section>

        <section class="gjs-card">
            <div class="gjs-card-title">Programación y estado</div>
            <div class="gjs-card-body">
                <div class="gjs-field">
                    <div class="gjs-label">Estado de la jornada</div>
                    <span class="gjs-badge {{ in_array($jornada->estado, ['FINALIZADA', 'CERRADA'], true) ? 'gjs-badge-complete' : '' }}">{{ $jornada->estado ?? 'Sin estado' }}</span>
                </div>
                <div class="gjs-field">
                    <div class="gjs-label">Fecha operativa</div>
                    <div class="gjs-value">{{ $jornada->fecha_operativa?->format('d/m/Y') ?? 'No registrada' }}</div>
                </div>
                <div class="gjs-field">
                    <div class="gjs-label">Turno</div>
                    <div class="gjs-value">{{ $jornada->turno?->nombre ?? ('Turno #'.$jornada->turno_id) }}</div>
                    @if($jornada->turno?->cruza_medianoche)
                        <div class="gjs-muted">Este turno cruza la medianoche.</div>
                    @endif
                </div>
                <div class="gjs-field">
                    <div class="gjs-label">Horario programado</div>
                    <div class="gjs-value">{{ $jornada->inicio_programado?->format('d/m/Y H:i') ?? 'No registrado' }} — {{ $jornada->fin_programado?->format('d/m/Y H:i') ?? 'No registrado' }}</div>
                </div>
                <div class="gjs-field">
                    <div class="gjs-label">Horario real</div>
                    <div class="gjs-value">Inicio: {{ $jornada->inicio_real?->format('d/m/Y H:i') ?? 'Sin registro' }}</div>
                    <div class="gjs-value" style="margin-top:5px">Fin: {{ $jornada->fin_real?->format('d/m/Y H:i') ?? 'Sin registro' }}</div>
                </div>
                <div class="gjs-field">
                    <div class="gjs-label">Programada o actualizada por</div>
                    <div class="gjs-value">{{ $jornada->asignador?->nombre_completo ?? ($jornada->asignado_por ? 'Usuario #'.$jornada->asignado_por : 'No disponible') }}</div>
                </div>
            </div>
        </section>
    </div>

    <section class="gjs-card gjs-panel">
        <div class="gjs-card-title">Arqueos vinculados a la jornada ({{ $arqueosRelacionados->count() }})</div>
        @if($arqueosRelacionados->isNotEmpty())
            <div class="gjs-table-wrap">
                <table class="gjs-table">
                    <thead><tr><th>Número</th><th>Tipo</th><th>Fecha operativa</th><th>Estado</th><th>Relación</th></tr></thead>
                    <tbody>
                        @foreach($arqueosRelacionados as $arqueo)
                            <tr>
                                <td>{{ $arqueo->numero_arqueo ?? ('#'.$arqueo->id) }}</td>
                                <td>{{ $arqueo->tipo ?? 'No especificado' }}</td>
                                <td>{{ $arqueo->fecha_operativa ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y') : 'No registrada' }}</td>
                                <td>{{ $arqueo->estado ?? 'Sin estado' }}</td>
                                <td>{{ (int) $arqueo->jornada_id === (int) $jornada->id ? 'Jornada del receptor' : 'Jornada entrante' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="gjs-empty">No hay arqueos asociados a esta jornada.</div>
        @endif
    </section>

    <section class="gjs-card gjs-panel">
        <div class="gjs-card-title">Habilitaciones asociadas ({{ $jornada->habilitaciones->count() }})</div>
        @if($jornada->habilitaciones->isNotEmpty())
            <div class="gjs-table-wrap">
                <table class="gjs-table">
                    <thead><tr><th>Identificador</th><th>Registro</th></tr></thead>
                    <tbody>
                        @foreach($jornada->habilitaciones as $habilitacion)
                            <tr><td>#{{ $habilitacion->id }}</td><td>Habilitación vinculada a esta jornada</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="gjs-empty">No hay habilitaciones vinculadas a esta jornada.</div>
        @endif
    </section>

    @unless($puedeEditar)
        <div class="gjs-note">Esta jornada se presenta en modo de consulta. La edición solo está disponible para jornadas programadas, no anteriores a la fecha actual y sin actividad ni registros asociados.</div>
    @endunless
</div>
@endsection
