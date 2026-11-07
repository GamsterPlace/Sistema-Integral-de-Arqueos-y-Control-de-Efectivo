@extends('layouts.gerencia')

@section('title', 'Cumplimiento de Agencias')
@section('module-title', 'Cumplimiento de Agencias ECOSABA')

@push('styles')
<style>
.gc-page,.gc-page *{box-sizing:border-box}
.gc-page{display:grid;gap:20px;color:#173754}
.gc-head{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;flex-wrap:wrap}
.gc-head h2{font-size:26px;margin:0;color:#092e53;font-weight:800}
.gc-head p{font-size:13px;line-height:1.6;color:#718497;margin:7px 0 0}
.gc-tag{background:#e7f2ff;color:#175394;padding:9px 13px;border-radius:30px;font-size:11px;font-weight:800}
.gc-card{background:#fff;border:1px solid #dfe8ef;border-radius:16px;box-shadow:0 8px 25px rgba(11,51,81,.04);overflow:hidden}
.gc-filters{display:flex;gap:13px;align-items:end;flex-wrap:wrap;padding:20px}
.gc-filters label{display:grid;gap:7px;min-width:190px;flex:1;font-size:12px;font-weight:750;color:#4d6378}
.gc-filters select{height:43px;width:100%;padding:0 11px;border:1px solid #ccd9e4;border-radius:9px;background:#fff;color:#173754}
.gc-actions{display:flex;gap:8px;flex-wrap:wrap}
.gc-btn{display:inline-flex;align-items:center;justify-content:center;min-height:43px;padding:0 17px;border:0;border-radius:9px;background:#164c96;color:white;font-size:12px;font-weight:800;cursor:pointer;text-decoration:none;transition:.2s}
.gc-btn:hover{background:#0c3670;color:white}
.gc-btn.alt{background:#eff3f7;color:#34516b}.gc-btn.alt:hover{background:#dfeaf4;color:#23496a}
.gc-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.gc-stat{background:white;padding:17px 15px;border:1px solid #dfe8ef;border-top:3px solid #3178bd;border-radius:14px;min-width:0}
.gc-stat small{display:block;min-height:29px;font-size:11px;font-weight:750;color:#63798c}
.gc-stat strong{display:block;font-size:27px;line-height:1.1;color:#123b60;margin-top:8px}
.gc-stat.green{border-top-color:#20a16c}.gc-stat.red{border-top-color:#d64e4e}.gc-stat.amber{border-top-color:#e5a32c}.gc-stat.gray{border-top-color:#8292a3}
.gc-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:20px;border-bottom:1px solid #e6edf3}
.gc-toolbar h3{margin:0;color:#103b60;font-size:20px;text-transform:capitalize}.gc-toolbar p{margin:5px 0 0;color:#718497;font-size:12px}
.gc-nav{display:flex;gap:8px;flex-wrap:wrap}.gc-nav a{padding:10px 12px;background:#f1f5f9;border-radius:9px;color:#244d70;text-decoration:none;font-size:11px;font-weight:750}.gc-nav a:hover{background:#e4edf8}
.gc-scroll{overflow-x:auto}.gc-calendar{display:grid;grid-template-columns:repeat(7,minmax(135px,1fr));min-width:945px}
.gc-week{padding:13px 8px;background:#f3f7fa;text-align:center;font-size:11px;font-weight:800;color:#667b8c}
.gc-day,.gc-blank{min-height:153px;border-top:1px solid #e7edf3;border-right:1px solid #e7edf3;padding:10px}.gc-blank{background:#fafbfd}.gc-day.today{background:#f2f8ff}.gc-day.future{background:#fafbfd}
.gc-dayhead{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px;color:#789;font-size:10px}.gc-dayhead b{font-size:15px;color:#143b5b}
.gc-event{display:flex;justify-content:space-between;gap:6px;align-items:center;width:100%;border:0;border-radius:7px;margin:5px 0;padding:7px 8px;font-size:10px;font-weight:700;cursor:pointer;text-align:left}.gc-event:hover{filter:brightness(.96)}.gc-event strong{font-size:11px}
.gc-green{background:#e8f7ee;color:#1b7449}.gc-yellow{background:#fff3d9;color:#916515}.gc-red{background:#ffeaea;color:#a43b3b}.gc-gray{background:#edf2f6;color:#5e7383}.gc-blue{background:#e9f2ff;color:#225a96}
.gc-empty{font-size:11px;color:#a1afbb}.gc-foot{padding:15px 20px;border-top:1px solid #e6edf3;color:#667b8c;font-size:11px;line-height:1.6}
.gc-dialog{position:fixed;inset:0;margin:auto;border:0;border-radius:16px;padding:0;width:min(1080px,95vw);max-height:88vh;overflow:auto;box-shadow:0 25px 70px rgba(9,39,67,.35);color:#173754}.gc-dialog::backdrop{background:rgba(8,31,54,.67)}
.gc-dialoghead{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:20px;border-bottom:1px solid #e2eaf0}.gc-dialoghead strong{font-size:17px}.gc-dialoghead p{margin:5px 0 0;color:#718497;font-size:12px}.gc-close{border:0;background:#eef2f6;padding:10px 14px;border-radius:9px;cursor:pointer;font-weight:700}.gc-dialogbody{padding:19px;max-height:68vh;overflow:auto}
.gc-table-wrap{overflow-x:auto}.gc-table{width:100%;min-width:800px;border-collapse:collapse}.gc-table th,.gc-table td{padding:12px 10px;text-align:left;border-bottom:1px solid #e5ecf2;font-size:12px}.gc-table th{background:#f4f7fa;color:#36536b}
@media(max-width:1100px){.gc-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.gc-head h2{font-size:22px}.gc-filters label{min-width:100%}.gc-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.gc-toolbar{align-items:flex-start}}
</style>
@endpush

@section('content')
@php
    $tituloMes = $inicio->copy()->locale('es')->translatedFormat('F Y');
    $espacios = $inicio->dayOfWeekIso - 1;
    $filtros = array_filter(['agencia_id' => $agenciaId, 'receptor_id' => $receptorId], fn ($v) => $v !== null);
    $categorias = [
        'CON_ARQUEO' => ['Con arqueo', 'gc-green'],
        'PENDIENTE_HOY' => ['Pendiente hoy', 'gc-yellow'],
        'SIN_ARQUEO' => ['Sin arqueo', 'gc-red'],
        'ANULADO_SIN_REEMPLAZO' => ['Anulado sin reemplazo', 'gc-gray'],
        'PROGRAMADO' => ['Programado', 'gc-blue'],
        'SIN_JORNADA_GENERADA' => ['Asignación sin jornada', 'gc-yellow'],
    ];
@endphp
<div class="gc-page">
    <header class="gc-head">
        <div>
            <h2>Cumplimiento de arqueos por turno</h2>
            <p>Supervisión institucional de jornadas, arqueos ordinarios y certificaciones de todas las agencias ECOSABA.</p>
        </div>
        <span class="gc-tag">Gerencia · Solo consulta</span>
    </header>

    <section class="gc-card">
        <form class="gc-filters" method="GET" action="{{ route('gerencia.cumplimiento-agencias.index') }}">
            <input type="hidden" name="mes" value="{{ $mes }}">
            <label>Agencia
                <select name="agencia_id">
                    <option value="">Todas las agencias</option>
                    @foreach ($agencias as $agencia)
                        <option value="{{ $agencia->id }}" @selected($agenciaId === (int) $agencia->id)>{{ $agencia->codigo }} — {{ $agencia->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label>Receptor
                <select name="receptor_id">
                    <option value="">Todos los receptores</option>
                    @foreach ($receptores as $receptor)
                        <option value="{{ $receptor->id }}" @selected($receptorId === (int) $receptor->id)>{{ $receptor->nombre_completo ?? $receptor->usuario }}</option>
                    @endforeach
                </select>
            </label>
            <div class="gc-actions">
                <button class="gc-btn" type="submit">Aplicar filtros</button>
                <a class="gc-btn alt" href="{{ route('gerencia.cumplimiento-agencias.index', ['mes' => $mes]) }}">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="gc-stats" aria-label="Indicadores de cumplimiento">
        <div class="gc-stat green"><small>Con arqueo firmado</small><strong>{{ number_format($resumen['CON_ARQUEO']) }}</strong></div>
        <div class="gc-stat red"><small>Sin arqueo</small><strong>{{ number_format($resumen['SIN_ARQUEO']) }}</strong></div>
        <div class="gc-stat amber"><small>Pendiente hoy</small><strong>{{ number_format($resumen['PENDIENTE_HOY']) }}</strong></div>
        <div class="gc-stat gray"><small>Anulado sin reemplazo</small><strong>{{ number_format($resumen['ANULADO_SIN_REEMPLAZO']) }}</strong></div>
        <div class="gc-stat green"><small>Certificados</small><strong>{{ number_format($resumen['CERTIFICADO']) }}</strong></div>
        <div class="gc-stat"><small>Pendientes de certificación</small><strong>{{ number_format($resumen['PENDIENTE_CERTIFICACION']) }}</strong></div>
        <div class="gc-stat amber"><small>Asignaciones sin jornada generada</small><strong>{{ number_format($resumen['TOTAL_ASIGNACIONES_SIN_JORNADA']) }}</strong></div>
        <div class="gc-stat"><small>Jornadas registradas</small><strong>{{ number_format($resumen['TOTAL_JORNADAS']) }}</strong></div>
    </section>

    <section class="gc-card">
        <div class="gc-toolbar">
            <div>
                <h3>{{ $tituloMes }}</h3>
                <p>Selecciona una categoría del calendario para consultar sus receptores y turnos.</p>
            </div>
            <nav class="gc-nav" aria-label="Navegación mensual">
                <a href="{{ route('gerencia.cumplimiento-agencias.index', array_merge($filtros, ['mes' => $mesAnterior])) }}">← Mes anterior</a>
                <a href="{{ route('gerencia.cumplimiento-agencias.index', array_merge($filtros, ['mes' => $mesActual])) }}">Mes actual</a>
                <a href="{{ route('gerencia.cumplimiento-agencias.index', array_merge($filtros, ['mes' => $mesSiguiente])) }}">Mes siguiente →</a>
            </nav>
        </div>
        <div class="gc-scroll">
            <div class="gc-calendar">
                @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $nombre)
                    <div class="gc-week">{{ $nombre }}</div>
                @endforeach
                @for ($i = 0; $i < $espacios; $i++)
                    <div class="gc-blank"></div>
                @endfor
                @foreach ($calendario as $dia)
                    <div class="gc-day {{ $dia['es_hoy'] ? 'today' : '' }} {{ $dia['es_futuro'] ? 'future' : '' }}">
                        <div class="gc-dayhead"><b>{{ $dia['dia'] }}</b><span>{{ $dia['total'] }} turnos</span></div>
                        @foreach ($categorias as $codigo => $categoria)
                            @if ($dia['estados'][$codigo] > 0)
                                <button type="button" class="gc-event {{ $categoria[1] }} js-gc-detail"
                                    data-fecha="{{ $dia['fecha'] }}" data-categoria="{{ $codigo }}" data-titulo="{{ $categoria[0] }}">
                                    <span>{{ $categoria[0] }}</span><strong>{{ $dia['estados'][$codigo] }}</strong>
                                </button>
                            @endif
                        @endforeach
                        @if ($dia['total'] === 0)
                            <span class="gc-empty">Sin turnos registrados</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="gc-foot">
            Se presentan jornadas registradas y asignaciones operativas sin jornada generada. Un arqueo ordinario firmado cuenta como realizado aunque esté pendiente de certificación. Las fechas futuras no se consideran incumplimientos. Los arqueos sorpresivos, de auditoría y de entrega no sustituyen el arqueo ordinario. Los estados de hoy son provisionales y deben interpretarse según el horario del turno.
        </div>
    </section>
</div>

<dialog class="gc-dialog" id="gcDialog" aria-labelledby="gcDialogTitle">
    <div class="gc-dialoghead">
        <div><strong id="gcDialogTitle">Detalle de cumplimiento</strong><p id="gcDialogDate"></p></div>
        <button type="button" class="gc-close" id="gcDialogClose">Cerrar</button>
    </div>
    <div class="gc-dialogbody" id="gcDialogBody">Cargando información…</div>
</dialog>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('gcDialog');
    const body = document.getElementById('gcDialogBody');
    if (!dialog || !body) return;
    const endpoint = @json(route('gerencia.cumplimiento-agencias.detalle'));
    const filtros = {agencia_id: @json($agenciaId), receptor_id: @json($receptorId)};
    const escapeHtml = value => String(value ?? '—').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[ch]));
    const estadoTexto = value => ({CERTIFICADO:'Certificado',PENDIENTE_CERTIFICACION:'Pendiente de certificación',NO_APLICA:'No aplica'})[value] || value;
    document.getElementById('gcDialogClose').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    document.querySelectorAll('.js-gc-detail').forEach(button => button.addEventListener('click', async () => {
        document.getElementById('gcDialogTitle').textContent = button.dataset.titulo;
        document.getElementById('gcDialogDate').textContent = button.dataset.fecha;
        body.textContent = 'Cargando información…';
        dialog.showModal();
        const params = new URLSearchParams({fecha: button.dataset.fecha, categoria: button.dataset.categoria});
        Object.entries(filtros).forEach(([key, value]) => { if (value !== null) params.set(key, value); });
        try {
            const response = await fetch(endpoint + '?' + params.toString(), {
                headers: {'Accept': 'application/json'}, credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('No se pudo consultar el detalle.');
            const data = await response.json();
            if (!data.jornadas || data.jornadas.length === 0) {
                body.textContent = 'No se encontraron jornadas para esta categoría.';
                return;
            }
            const columnas = ['fecha','agencia','ventanilla','receptor','turno','arqueo','certificacion'];
            body.innerHTML = '<div class="gc-table-wrap"><table class="gc-table"><thead><tr>' +
                ['Fecha','Agencia','Ventanilla','Receptor','Turno','Arqueo','Certificación'].map(x => '<th>' + x + '</th>').join('') +
                '</tr></thead><tbody>' + data.jornadas.map(j => '<tr>' + columnas.map(key => '<td>' + escapeHtml(key === 'certificacion' ? estadoTexto(j[key]) : j[key]) + '</td>').join('') + '</tr>').join('') +
                '</tbody></table></div>';
        } catch (error) {
            body.textContent = 'No fue posible cargar el detalle. Verifica la conexión e inténtalo nuevamente.';
        }
    }));
});
</script>
@endpush
