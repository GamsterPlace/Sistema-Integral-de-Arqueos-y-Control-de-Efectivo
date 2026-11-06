@extends('layouts.administrador')

@section('title', 'Cumplimiento de Arqueos de Agencias')
@section('module-title', 'Cumplimiento de Arqueos de Agencias ECOSABA')

@push('styles')
<style>
.ca-page{display:grid;gap:20px;color:#173754}
.ca-page *{box-sizing:border-box}
.ca-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
.ca-head h2{margin:0;font-size:26px;font-weight:800;color:#092e53}
.ca-head p{margin:8px 0 0;color:#718497;font-size:13px;line-height:1.6}
.ca-tag{background:#e7f2ff;color:#175394;padding:9px 13px;border-radius:999px;font-size:11px;font-weight:800}
.ca-card{background:#fff;border:1px solid #dfe8ef;border-radius:16px;box-shadow:0 8px 25px rgba(11,51,81,.04);overflow:hidden}
.ca-filters{display:flex;gap:13px;align-items:end;flex-wrap:wrap;padding:20px}
.ca-filters label{display:grid;gap:7px;min-width:210px;flex:1;font-size:12px;font-weight:750;color:#4d6378}
.ca-filters select{width:100%;height:43px;border:1px solid #ccd9e4;border-radius:9px;padding:0 11px;background:#fff;color:#173754}
.ca-actions{display:flex;gap:8px;flex-wrap:wrap}
.ca-btn{display:inline-flex;align-items:center;justify-content:center;min-height:43px;padding:0 17px;border:0;border-radius:9px;background:#164c96;color:#fff;font-size:12px;font-weight:800;cursor:pointer;text-decoration:none}
.ca-btn.alt{background:#eff3f7;color:#34516b}
.ca-stats{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:11px}
.ca-stat{padding:17px 15px;background:#fff;border:1px solid #dfe8ef;border-radius:14px;min-width:0}
.ca-stat small{display:block;min-height:29px;font-size:11px;font-weight:750;color:#63798c}
.ca-stat strong{display:block;margin-top:8px;font-size:28px;line-height:1.1;color:#123b60}
.ca-stat.green{border-top:3px solid #20a16c}.ca-stat.red{border-top:3px solid #d64e4e}.ca-stat.amber{border-top:3px solid #e5a32c}.ca-stat.gray{border-top:3px solid #8292a3}.ca-stat.blue{border-top:3px solid #3178bd}
.ca-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:20px;border-bottom:1px solid #e6edf3;flex-wrap:wrap}
.ca-toolbar h3{margin:0;color:#103b60;font-size:20px;text-transform:capitalize}
.ca-toolbar p{margin:5px 0 0;font-size:12px;color:#718497}
.ca-nav{display:flex;gap:8px;flex-wrap:wrap}
.ca-nav a{padding:10px 12px;background:#f1f5f9;border-radius:9px;color:#244d70;text-decoration:none;font-size:11px;font-weight:750}
.ca-nav a:hover{background:#e4edf8}
.ca-scroll{overflow-x:auto}
.ca-calendar{display:grid;grid-template-columns:repeat(7,minmax(135px,1fr));min-width:945px}
.ca-week{padding:13px 8px;background:#f3f7fa;text-align:center;font-size:11px;font-weight:800;color:#667b8c}
.ca-day,.ca-blank{min-height:153px;border-top:1px solid #e7edf3;border-right:1px solid #e7edf3;padding:10px}
.ca-blank{background:#fafbfd}.ca-day.today{background:#f2f8ff}.ca-day.future{background:#fafbfd}
.ca-dayhead{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px;color:#789;font-size:10px}
.ca-dayhead b{font-size:15px;color:#143b5b}
.ca-event{display:flex;justify-content:space-between;gap:6px;align-items:center;width:100%;border:0;border-radius:7px;margin:5px 0;padding:7px 8px;font-size:10px;font-weight:700;cursor:pointer;text-align:left}
.ca-event:hover{filter:brightness(.96)}.ca-event strong{font-size:11px}
.ca-green{background:#e8f7ee;color:#1b7449}.ca-yellow{background:#fff3d9;color:#916515}.ca-red{background:#ffeaea;color:#a43b3b}.ca-gray{background:#edf2f6;color:#5e7383}.ca-blue{background:#e9f2ff;color:#225a96}
.ca-empty{font-size:11px;color:#a1afbb}.ca-foot{padding:15px 20px;border-top:1px solid #e6edf3;color:#667b8c;font-size:11px;line-height:1.6}
.ca-dialog {
    position: fixed;
    inset: 0;
    margin: auto;
    border: 0;
    border-radius: 16px;
    padding: 0;
    width: min(1080px, 95vw);
    max-height: 88vh;
    overflow: auto;
    box-shadow: 0 25px 70px rgba(9, 39, 67, .35);
    color: #173754;
}
.ca-dialog::backdrop{background:rgba(8,31,54,.67)}
.ca-dialoghead{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:20px;border-bottom:1px solid #e2eaf0}
.ca-dialoghead strong{font-size:17px}.ca-dialoghead p{margin:5px 0 0;color:#718497;font-size:12px}
.ca-close{border:0;background:#eef2f6;padding:10px 14px;border-radius:9px;cursor:pointer;font-weight:700}
.ca-dialogbody{padding:19px;max-height:68vh;overflow:auto}
.ca-table{width:100%;min-width:800px;border-collapse:collapse}.ca-table th,.ca-table td{padding:12px 10px;text-align:left;border-bottom:1px solid #e5ecf2;font-size:12px}.ca-table th{background:#f4f7fa;color:#36536b}
@media(max-width:1150px){.ca-stats{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:680px){.ca-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.ca-head h2{font-size:22px}.ca-filters label{min-width:100%}.ca-toolbar{align-items:flex-start}}
</style>
@endpush

@section('content')
@php
    $tituloMes = $inicio->copy()->locale('es')->translatedFormat('F Y');
    $espacios = $inicio->dayOfWeekIso - 1;
    $filtros = array_filter(['agencia_id' => $agenciaId, 'receptor_id' => $receptorId], fn ($v) => $v !== null);
    $categorias = [
        'CON_ARQUEO' => ['Con arqueo', 'ca-green'],
        'PENDIENTE_HOY' => ['Pendiente hoy', 'ca-yellow'],
        'SIN_ARQUEO' => ['Sin arqueo', 'ca-red'],
        'ANULADO_SIN_REEMPLAZO' => ['Anulado sin reemplazo', 'ca-gray'],
        'PROGRAMADO' => ['Programado', 'ca-blue'],
        'SIN_JORNADA_GENERADA' => ['Asignación sin jornada', 'ca-yellow'],
    ];
@endphp
<div class="ca-page">
    <header class="ca-head">
        <div>
            <h2>Cumplimiento de arqueos por turno</h2>
            <p>Supervisión institucional de jornadas, arqueos ordinarios firmados y certificaciones de todas las agencias ECOSABA.</p>
        </div>
        <span class="ca-tag">Consulta administrativa</span>
    </header>

    <section class="ca-card">
        <form class="ca-filters" method="GET" action="{{ route('administrador.cumplimientos-agencias.index') }}">
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
            <div class="ca-actions">
                <button type="submit" class="ca-btn">Aplicar filtros</button>
                <a class="ca-btn alt" href="{{ route('administrador.cumplimientos-agencias.index', ['mes' => $mes]) }}">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="ca-stats" aria-label="Indicadores de cumplimiento">
        <div class="ca-stat green"><small>Con arqueo firmado</small><strong>{{ $resumen['CON_ARQUEO'] }}</strong></div>
        <div class="ca-stat red"><small>Sin arqueo</small><strong>{{ $resumen['SIN_ARQUEO'] }}</strong></div>
        <div class="ca-stat amber"><small>Pendiente hoy</small><strong>{{ $resumen['PENDIENTE_HOY'] }}</strong></div>
        <div class="ca-stat gray"><small>Anulado sin reemplazo</small><strong>{{ $resumen['ANULADO_SIN_REEMPLAZO'] }}</strong></div>
        <div class="ca-stat green"><small>Certificados</small><strong>{{ $resumen['CERTIFICADO'] }}</strong></div>
        <div class="ca-stat blue"><small>Pendientes de certificación</small><strong>{{ $resumen['PENDIENTE_CERTIFICACION'] }}</strong></div>
        <div class="ca-stat amber"><small>Asignaciones sin jornada generada</small><strong>{{ $resumen['SIN_JORNADA_GENERADA'] }}</strong></div>
    </section>

    <section class="ca-card">
        <div class="ca-toolbar">
            <div>
                <h3>{{ $tituloMes }}</h3>
                <p>{{ $resumen['TOTAL_JORNADAS'] }} jornadas registradas y {{ $resumen['TOTAL_ASIGNACIONES_SIN_JORNADA'] }} asignaciones sin jornada generada. Selecciona una categoría para consultar el detalle.</p>
            </div>
            <nav class="ca-nav" aria-label="Navegación mensual">
                <a href="{{ route('administrador.cumplimientos-agencias.index', array_merge($filtros, ['mes' => $mesAnterior])) }}">← Mes anterior</a>
                <a href="{{ route('administrador.cumplimientos-agencias.index', array_merge($filtros, ['mes' => $mesActual])) }}">Mes actual</a>
                <a href="{{ route('administrador.cumplimientos-agencias.index', array_merge($filtros, ['mes' => $mesSiguiente])) }}">Mes siguiente →</a>
            </nav>
        </div>
        <div class="ca-scroll">
            <div class="ca-calendar">
                @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $nombre)
                    <div class="ca-week">{{ $nombre }}</div>
                @endforeach
                @for ($i = 0; $i < $espacios; $i++)
                    <div class="ca-blank"></div>
                @endfor
                @foreach ($calendario as $dia)
                    <div class="ca-day {{ $dia['es_hoy'] ? 'today' : '' }} {{ $dia['es_futuro'] ? 'future' : '' }}">
                        <div class="ca-dayhead"><b>{{ $dia['dia'] }}</b><span>{{ $dia['total'] }} turnos</span></div>
                        @foreach ($categorias as $codigo => $categoria)
                            @if ($dia['estados'][$codigo] > 0)
                                <button type="button" class="ca-event {{ $categoria[1] }} js-ca-detail"
                                    data-fecha="{{ $dia['fecha'] }}" data-categoria="{{ $codigo }}" data-titulo="{{ $categoria[0] }}">
                                    <span>{{ $categoria[0] }}</span><strong>{{ $dia['estados'][$codigo] }}</strong>
                                </button>
                            @endif
                        @endforeach
                        @if ($dia['total'] === 0)
                            <span class="ca-empty">Sin jornadas registradas</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="ca-foot">Se muestran las jornadas registradas y las asignaciones operativas vigentes sin jornada generada. Estas últimas no se consideran automáticamente incumplimientos hasta aplicar las excepciones operativas existentes. Un arqueo firmado cuenta como realizado aunque aún no esté certificado; la certificación se presenta por separado. Las fechas futuras no se consideran incumplimientos. Los arqueos sorpresivos, de auditoría y de entrega no sustituyen automáticamente el arqueo ordinario.</div>
    </section>
</div>

<dialog class="ca-dialog" id="caDialog" aria-labelledby="caDialogTitle">
    <div class="ca-dialoghead">
        <div><strong id="caDialogTitle">Detalle de cumplimiento</strong><p id="caDialogDate"></p></div>
        <button type="button" class="ca-close" id="caDialogClose">Cerrar</button>
    </div>
    <div class="ca-dialogbody" id="caDialogBody">Cargando información…</div>
</dialog>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('caDialog');
    const body = document.getElementById('caDialogBody');
    if (!dialog || !body) return;
    const endpoint = @json(route('administrador.cumplimientos-agencias.detalle'));
    const filtros = {agencia_id: @json($agenciaId), receptor_id: @json($receptorId)};
    const escapeHtml = value => String(value ?? '—').replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character]));
    const estadoTexto = value => ({CERTIFICADO:'Certificado',PENDIENTE_CERTIFICACION:'Pendiente de certificación',NO_APLICA:'No aplica'})[value] || value;
    document.getElementById('caDialogClose').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });

    document.querySelectorAll('.js-ca-detail').forEach(button => button.addEventListener('click', async () => {
        document.getElementById('caDialogTitle').textContent = button.dataset.titulo;
        document.getElementById('caDialogDate').textContent = button.dataset.fecha;
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
            body.innerHTML = '<div style="overflow-x:auto"><table class="ca-table"><thead><tr>' +
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
