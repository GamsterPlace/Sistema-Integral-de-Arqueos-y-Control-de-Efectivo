@extends($rutaBase === 'jefecaja' ? 'layouts.jefe-caja' : 'layouts.subjefe-caja')

@section('title', 'Gestión de Jornadas')
@section('module-title', 'Gestión de Jornadas')

@push('styles')
<style>
    .gj-page{max-width:1400px;margin:0 auto;padding:22px 18px 42px;color:#173452}
    .gj-page *{box-sizing:border-box}
    .gj-header{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:22px}
    .gj-header h1{font-size:27px;color:#092f57;margin:0 0 6px;font-weight:800}
    .gj-header p{margin:0;color:#62768a;font-size:14px;line-height:1.5}
    .gj-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;border-radius:10px;background:#0b548e;color:#fff!important;text-decoration:none!important;font-weight:700;font-size:13px;padding:12px 17px;border:1px solid #0b548e;white-space:nowrap}
    .gj-button:hover{background:#083e6b}
    .gj-panel{background:#fff;border:1px solid #dce6f0;border-radius:15px;box-shadow:0 7px 24px rgba(13,52,86,.05);margin-bottom:19px;overflow:hidden}
    .gj-panel-head{padding:17px 21px;border-bottom:1px solid #e8eef4;font-weight:800;color:#143e63;font-size:15px}
    .gj-filters{padding:19px 21px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr)) auto;gap:14px;align-items:end}
    .gj-field label{display:block;font-weight:700;font-size:12px;color:#35536d;margin-bottom:7px}
    .gj-field input,.gj-field select{display:block;width:100%;min-height:41px;border:1px solid #cfdae6;border-radius:9px;padding:9px 11px;background:#fff;color:#183d5b;font:inherit;font-size:13px}
    .gj-filter-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .gj-filter-actions button{background:#0b548e;color:#fff;border:0;border-radius:9px;padding:12px 14px;font-weight:700;cursor:pointer}
    .gj-clear{color:#40637f;text-decoration:none;font-weight:700;font-size:13px;padding:10px 5px}
    .gj-table-wrap{overflow-x:auto}
    .gj-table{width:100%;border-collapse:collapse;text-align:left;font-size:13px;min-width:940px}
    .gj-table th{background:#f4f8fc;color:#355470;font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:.04em;padding:13px 15px;white-space:nowrap}
    .gj-table td{padding:15px;border-top:1px solid #eaf0f5;vertical-align:middle;color:#2e4b65}
    .gj-table tbody tr:hover{background:#fafcff}
    .gj-main{font-weight:800;color:#143e63}
    .gj-secondary{display:block;margin-top:4px;color:#7a8a9a;font-size:11px}
    .gj-state{display:inline-block;border-radius:20px;padding:5px 10px;font-weight:800;font-size:11px;background:#eaf2fb;color:#15517d}
    .gj-state--done{background:#e7f5ed;color:#207448}
    .gj-state--cancel{background:#fcecec;color:#9c3737}
    .gj-actions{display:flex;gap:8px;align-items:center}
    .gj-link{font-weight:800;color:#075c9e;text-decoration:none;white-space:nowrap}
    .gj-link:hover{text-decoration:underline}
    .gj-empty{text-align:center;padding:42px 18px;color:#698096}
    .gj-pagination{padding:17px 20px;border-top:1px solid #e8eef4}
    .gj-alert{padding:12px 15px;border-radius:9px;margin-bottom:16px;background:#eaf7ef;border:1px solid #b9e1c8;color:#21683f;font-size:13px;font-weight:700}
    @media(max-width:1000px){.gj-filters{grid-template-columns:repeat(2,minmax(0,1fr))}.gj-filter-actions{grid-column:1/-1}}
    @media(max-width:600px){.gj-page{padding:14px 10px 30px}.gj-filters{grid-template-columns:1fr}.gj-filter-actions{grid-column:auto}.gj-header h1{font-size:23px}}
</style>
@endpush

@section('content')
<div class="gj-page">
    <div class="gj-header">
        <div>
            <h1>Gestión de Jornadas</h1>
            <p>Programación de turnos y asignación de ventanillas para receptores de las agencias bajo su responsabilidad.</p>
        </div>
        <a class="gj-button" href="{{ route($rutaBase.'.jornadas.create') }}">+ Programar jornada</a>
    </div>

    @if(session('success'))
        <div class="gj-alert" role="status">{{ session('success') }}</div>
    @endif

    <section class="gj-panel" aria-label="Filtros de jornadas">
        <div class="gj-panel-head">Buscar jornadas</div>
        <form class="gj-filters" method="GET" action="{{ route($rutaBase.'.jornadas.index') }}">
            <div class="gj-field">
                <label for="agencia_id">Agencia</label>
                <select id="agencia_id" name="agencia_id">
                    <option value="">Todas las agencias autorizadas</option>
                    @foreach($agencias as $agencia)
                        <option value="{{ $agencia->id }}" @selected((string)($filtros['agencia_id'] ?? '') === (string)$agencia->id)>{{ $agencia->nombre ?? $agencia->codigo ?? ('Agencia #'.$agencia->id) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gj-field">
                <label for="fecha">Fecha operativa</label>
                <input type="date" id="fecha" name="fecha" value="{{ $filtros['fecha'] ?? '' }}">
            </div>
            <div class="gj-field">
                <label for="receptor_usuario_id">Receptor</label>
                <select id="receptor_usuario_id" name="receptor_usuario_id">
                    <option value="">Todos los receptores</option>
                    @foreach($asignaciones->unique('usuario_id') as $asignacion)
                        <option value="{{ $asignacion->usuario_id }}" @selected((string)($filtros['receptor_usuario_id'] ?? '') === (string)$asignacion->usuario_id)>{{ $asignacion->usuario?->nombre_completo ?? ('Usuario #'.$asignacion->usuario_id) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gj-filter-actions">
                <button type="submit">Filtrar</button>
                <a class="gj-clear" href="{{ route($rutaBase.'.jornadas.index') }}">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="gj-panel" aria-label="Listado de jornadas">
        <div class="gj-panel-head">Jornadas registradas ({{ $jornadas->total() }})</div>
        <div class="gj-table-wrap">
            <table class="gj-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Receptor</th>
                        <th>Agencia</th>
                        <th>Ventanilla</th>
                        <th>Turno y horario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jornadas as $jornada)
                        @php
                            $estado = (string) $jornada->estado;
                            $estadoClase = in_array($estado, ['FINALIZADA', 'CERRADA', 'COMPLETADA'], true) ? 'gj-state--done' : (in_array($estado, ['CANCELADA', 'ANULADA'], true) ? 'gj-state--cancel' : '');
                            $editable = $estado === 'PROGRAMADA' && $jornada->fecha_operativa?->startOfDay()->greaterThanOrEqualTo(today()) && $jornada->inicio_real === null && $jornada->fin_real === null;
                        @endphp
                        <tr>
                            <td><span class="gj-main">{{ $jornada->fecha_operativa?->format('d/m/Y') ?? '—' }}</span></td>
                            <td><span class="gj-main">{{ $jornada->receptor?->nombre_completo ?? ('Usuario #'.$jornada->receptor_usuario_id) }}</span></td>
                            <td>{{ $jornada->agencia?->nombre ?? $jornada->agencia?->codigo ?? ('Agencia #'.$jornada->agencia_id) }}</td>
                            <td>{{ $jornada->ventanilla?->codigo ?? '—' }}<span class="gj-secondary">{{ $jornada->ventanilla?->descripcion ?? '' }}</span></td>
                            <td>
                                <span class="gj-main">{{ $jornada->turno?->nombre ?? '—' }}</span>
                                <span class="gj-secondary">{{ $jornada->inicio_programado?->format('d/m H:i') ?? '—' }} – {{ $jornada->fin_programado?->format('d/m H:i') ?? '—' }}</span>
                            </td>
                            <td><span class="gj-state {{ $estadoClase }}">{{ str_replace('_', ' ', $estado) }}</span></td>
                            <td>
                                <div class="gj-actions">
                                    <a class="gj-link" href="{{ route($rutaBase.'.jornadas.show', $jornada->id) }}">Ver detalle</a>
                                    @if($editable)
                                        <a class="gj-link" href="{{ route($rutaBase.'.jornadas.edit', $jornada->id) }}">Editar</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="gj-empty">No se encontraron jornadas con los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jornadas->hasPages())
            <div class="gj-pagination">{{ $jornadas->links() }}</div>
        @endif
    </section>
</div>
@endsection
