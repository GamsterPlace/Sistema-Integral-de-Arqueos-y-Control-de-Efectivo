@extends('layouts.gerencia')

@section('title', 'Dashboard de Gerencia')
@section('module-title', 'Dashboard')

@push('styles')
<style>
.ger-dashboard{max-width:1500px;margin:0 auto;color:#183b59}
.ger-dashboard *{box-sizing:border-box}
.ger-head{margin-bottom:22px}.ger-head h2{margin:0 0 7px;color:#092f59;font-size:26px}.ger-head p{margin:0;color:#73899b;font-size:13px}
.ger-welcome{padding:25px 28px;border-radius:18px;background:linear-gradient(115deg,#07345f,#164c96 65%,#128257);color:white;margin-bottom:23px;box-shadow:0 12px 28px #103c641c}
.ger-welcome small{font-size:11px;letter-spacing:1px;text-transform:uppercase;opacity:.75}.ger-welcome h3{font-size:24px;margin:8px 0}.ger-welcome p{margin:0;font-size:13px;opacity:.85;line-height:1.65}
.ger-switch{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px;margin-bottom:24px}
.ger-switch button{font:inherit;text-align:left;display:flex;align-items:center;gap:15px;background:white;border:2px solid #dde8f1;border-radius:15px;padding:18px;cursor:pointer;color:#153e64;transition:.15s;box-shadow:0 5px 15px #1439530b}
.ger-switch button:hover{border-color:#86acd0}.ger-switch button[aria-selected="true"]{border-color:#164c96;background:#f0f6fe;box-shadow:0 7px 20px #164c961b}
.ger-switch-icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:12px;background:#e8f1fb;color:#164c96}
.ger-switch-icon svg{width:23px;height:23px}.ger-switch strong{display:block;font-size:15px}.ger-switch small{display:block;font-size:11px;color:#788d9e;margin-top:4px;line-height:1.4}
.ger-pane[hidden]{display:none!important}.ger-pane-heading{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin:0 0 19px}.ger-pane-heading h3{margin:0;color:#0a3158;font-size:21px}.ger-pane-heading p{margin:5px 0 0;color:#7b8d9c;font-size:12px}
.ger-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-bottom:23px}
.ger-stat{background:#fff;border:1px solid #e0e8ee;border-radius:15px;padding:19px;min-height:119px;box-shadow:0 6px 18px #1439530b}
.ger-stat small{display:block;font-size:11px;font-weight:750;color:#74889a;text-transform:uppercase;letter-spacing:.35px}.ger-stat strong{display:block;margin:13px 0 6px;font-size:26px;color:#103c6a}.ger-stat span{font-size:11px;color:#8394a2}.ger-stat.green{border-color:#c7e8d3;background:#f9fdfa}.ger-stat.red{border-color:#f0cece;background:#fffbfb}.ger-stat.amber{border-color:#f0e1b9;background:#fffdf8}
.ger-columns{display:grid;grid-template-columns:minmax(280px,.85fr) minmax(0,1.5fr);gap:18px;margin-bottom:20px}
.ger-panel{background:#fff;border:1px solid #e0e8ee;border-radius:16px;overflow:hidden;margin-bottom:19px;box-shadow:0 6px 18px #1439530b}
.ger-panel-header{padding:18px 20px;border-bottom:1px solid #e8eef3;display:flex;justify-content:space-between;align-items:center;gap:12px}
.ger-panel-header h4{margin:0;color:#0a3158;font-size:16px}.ger-panel-header p{margin:5px 0 0;color:#7d8e9d;font-size:11px}
.ger-panel-body{padding:20px}.ger-progress{height:13px;background:#e9eff4;border-radius:20px;overflow:hidden;margin:12px 0}.ger-progress>div{height:100%;background:linear-gradient(90deg,#164c96,#00a651);border-radius:20px}
.ger-progress-label{display:flex;justify-content:space-between;font-size:12px;color:#62798d}.ger-progress-label strong{font-size:18px;color:#0a3158}
.ger-mini-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:11px;margin-top:18px}.ger-mini{padding:12px;background:#f7fafc;border:1px solid #e6edf2;border-radius:10px}.ger-mini small{display:block;color:#71879a;font-size:10px}.ger-mini strong{display:block;font-size:19px;color:#143f67;margin-top:7px}
.ger-note{font-size:11px;color:#718597;line-height:1.65;margin:15px 0 0}
.ger-table-wrap{overflow-x:auto}.ger-table{width:100%;min-width:850px;border-collapse:collapse}.ger-table th,.ger-table td{padding:13px 15px;text-align:left;border-bottom:1px solid #eaf0f4;font-size:11px;vertical-align:middle}.ger-table th{background:#f5f9fc;color:#657c90;font-size:10px;text-transform:uppercase}.ger-table td{color:#405d73}.ger-table td strong{color:#123c62}.ger-table tr:last-child td{border-bottom:0}
.ger-link{color:#164c96;font-size:12px;font-weight:800;text-decoration:none}.ger-link:hover{text-decoration:underline}
/* Botones principales de navegación en los encabezados de cada sección */
.ger-dashboard .ger-pane-heading .ger-link{display:inline-flex;align-items:center;justify-content:center;gap:9px;flex-shrink:0;min-height:43px;padding:11px 18px;border:1px solid #164c96;border-radius:11px;background:#164c96;color:#fff!important;font-size:12px;font-weight:800;line-height:1.25;text-align:center;text-decoration:none!important;box-shadow:0 6px 16px rgba(22,76,150,.17);transition:background .18s ease,border-color .18s ease,transform .18s ease,box-shadow .18s ease}
.ger-dashboard .ger-pane-heading .ger-link:hover{background:#0b315f;border-color:#0b315f;color:#fff!important;transform:translateY(-1px);box-shadow:0 9px 20px rgba(22,76,150,.22)}
.ger-dashboard .ger-pane-heading .ger-link:focus-visible{outline:3px solid #79b7f2;outline-offset:3px}
@media(max-width:480px){.ger-dashboard .ger-pane-heading .ger-link{width:100%}}

.ger-tag{display:inline-block;padding:5px 8px;border-radius:7px;background:#edf3fa;color:#164c96;font-size:10px;font-weight:800}.ger-tag.ok{background:#e8f7ee;color:#16834e}.ger-tag.bad{background:#fcebec;color:#b33a3a}.ger-tag.warn{background:#fff5df;color:#a46a0a}
.ger-empty{padding:26px;text-align:center;color:#7c90a0;font-size:12px}
.ger-agency-progress{min-width:100px;height:8px;background:#e6edf3;border-radius:10px;overflow:hidden}.ger-agency-progress>div{height:100%;background:linear-gradient(90deg,#1681c4,#00a651)}
@media(max-width:1100px){.ger-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.ger-columns{grid-template-columns:1fr}}
@media(max-width:680px){.ger-switch{grid-template-columns:1fr}.ger-stats{grid-template-columns:1fr 1fr;gap:9px}.ger-stat{padding:13px}.ger-stat strong{font-size:22px}.ger-welcome{padding:20px}.ger-mini-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
    $nombreGerencia = auth()->user()?->nombre_completo ?? auth()->user()?->usuario ?? 'Gerencia';
    $turnosExigiblesInstitucional = $resumenAgencias->sum(fn ($a) => (int) ($a->arqueos_esperados ?? 0));
    $turnosCumplidosInstitucional = $resumenAgencias->sum(fn ($a) => (int) ($a->arqueos_cumplidos ?? 0));
    $turnosPendientesInstitucional = max(0, $turnosExigiblesInstitucional - $turnosCumplidosInstitucional);
    $porcentajeInstitucional = $turnosExigiblesInstitucional > 0 ? min(100, round($turnosCumplidosInstitucional * 100 / $turnosExigiblesInstitucional, 1)) : null;
@endphp
<div class="ger-dashboard">
    <header class="ger-head"><h2>Dashboard de Gerencia</h2><p>Supervisión ejecutiva de los arqueos institucionales.</p></header>
    <section class="ger-welcome"><small>Vista ejecutiva</small><h3>{{ $nombreGerencia }}</h3><p>Consulte el estado operativo, el cumplimiento y las incidencias de Agentes MICOOPE y Agencias ECOSABA.</p></section>

    <div class="ger-switch" role="tablist" aria-label="Seleccionar área del dashboard">
        <button type="button" id="ger-tab-general" role="tab" aria-controls="ger-panel-general" aria-selected="true" data-ger-tab="general">
            <span class="ger-switch-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span><span><strong>General</strong><small>Panorama institucional</small></span>
        </button>
        <button type="button" id="ger-tab-agentes" role="tab" aria-controls="ger-panel-agentes" aria-selected="false" data-ger-tab="agentes" tabindex="-1">
            <span class="ger-switch-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a7 7 0 0 1 14 0v2M18 8h5M20.5 5.5v5"/></svg></span><span><strong>Agentes</strong><small>Operación MICOOPE</small></span>
        </button>
        <button type="button" id="ger-tab-agencias" role="tab" aria-controls="ger-panel-agencias" aria-selected="false" data-ger-tab="agencias" tabindex="-1">
            <span class="ger-switch-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 21v-8h8v8M7 8h2m6 0h2"/></svg></span><span><strong>Agencias</strong><small>Operación ECOSABA</small></span>
        </button>
    </div>

    <section class="ger-pane" id="ger-panel-general" role="tabpanel" aria-labelledby="ger-tab-general">
        <div class="ger-pane-heading"><div><h3>Resumen institucional</h3><p>Indicadores de ambas operaciones, separados para evitar confundir agentes con agencias.</p></div></div>
        <div class="ger-stats">
            <article class="ger-stat"><small>Agentes activos</small><strong>{{ number_format($agentesActivos) }}</strong><span>MICOOPE</span></article>
            <article class="ger-stat"><small>Agencias activas</small><strong>{{ number_format($indicadoresAgencias['agencias']) }}</strong><span>ECOSABA</span></article>
            <article class="ger-stat"><small>Arqueos agentes hoy</small><strong>{{ number_format($arqueosHoy) }}</strong><span>Vigentes · MICOOPE</span></article>
            <article class="ger-stat"><small>Arqueos agencias hoy</small><strong>{{ number_format($indicadoresAgencias['arqueos_hoy']) }}</strong><span>Vigentes · ECOSABA</span></article>
            <article class="ger-stat green"><small>Cumplimiento agentes</small><strong>{{ number_format($cumplimiento,1) }}%</strong><span>Agentes activos con arqueo diario</span></article>
            <article class="ger-stat green"><small>Cumplimiento agencias</small><strong>{{ $porcentajeInstitucional === null ? 'N/A' : number_format($porcentajeInstitucional,1).'%' }}</strong><span>Turnos exigibles con arqueo</span></article>
            <article class="ger-stat amber"><small>Agentes pendientes hoy</small><strong>{{ number_format($agentesPendientesHoy) }}</strong><span>Sin arqueo diario vigente</span></article>
            <article class="ger-stat amber"><small>Turnos pendientes agencias</small><strong>{{ number_format($turnosPendientesInstitucional) }}</strong><span>Turnos exigibles no cumplidos</span></article>
        </div>
        <div class="ger-columns">
            <article class="ger-panel"><header class="ger-panel-header"><div><h4>Cumplimiento MICOOPE</h4><p>Agentes activos con arqueo diario</p></div></header><div class="ger-panel-body"><div class="ger-progress-label"><span>Avance de hoy</span><strong>{{ number_format($cumplimiento,1) }}%</strong></div><div class="ger-progress"><div style="width:{{ min(100,max(0,$cumplimiento)) }}%"></div></div><p class="ger-note">{{ $agentesConArqueoHoy }} con arqueo de {{ $agentesActivos }} agentes activos.</p></div></article>
            <article class="ger-panel"><header class="ger-panel-header"><div><h4>Cumplimiento ECOSABA</h4><p>Turnos cuyo horario de cierre ya transcurrió</p></div></header><div class="ger-panel-body"><div class="ger-progress-label"><span>Avance de hoy</span><strong>{{ $porcentajeInstitucional === null ? 'N/A' : number_format($porcentajeInstitucional,1).'%' }}</strong></div><div class="ger-progress"><div style="width:{{ $porcentajeInstitucional ?? 0 }}%"></div></div><p class="ger-note">{{ $turnosCumplidosInstitucional }} de {{ $turnosExigiblesInstitucional }} turnos exigibles. Los turnos futuros no se cuentan como incumplidos.</p></div></article>
        </div>
    </section>

    <section class="ger-pane" id="ger-panel-agentes" role="tabpanel" aria-labelledby="ger-tab-agentes" hidden>
        <div class="ger-pane-heading"><div><h3>Supervisión de Agentes MICOOPE</h3><p>Arqueos y cumplimiento de agentes.</p></div><a class="ger-link" href="{{ url('/gerencia/arqueos') }}">Consultar arqueos</a></div>
        <div class="ger-stats">
            <article class="ger-stat"><small>Agentes activos</small><strong>{{ number_format($agentesActivos) }}</strong><span>De {{ $totalAgentes }} registrados</span></article>
            <article class="ger-stat"><small>Arqueos hoy</small><strong>{{ number_format($arqueosHoy) }}</strong><span>No anulados</span></article>
            <article class="ger-stat green"><small>Exactos hoy</small><strong>{{ number_format($exactosHoy) }}</strong><span>Sin diferencia</span></article>
            <article class="ger-stat amber"><small>Agentes pendientes</small><strong>{{ number_format($agentesPendientesHoy) }}</strong><span>Sin arqueo diario</span></article>
            <article class="ger-stat red"><small>Faltantes hoy</small><strong>{{ number_format($faltantesHoy) }}</strong><span>Diferencia negativa</span></article>
            <article class="ger-stat green"><small>Sobrantes hoy</small><strong>{{ number_format($sobrantesHoy) }}</strong><span>Diferencia positiva</span></article>
            <article class="ger-stat amber"><small>Extemporáneos hoy</small><strong>{{ number_format($extemporaneosHoy) }}</strong><span>Fuera de fecha ordinaria</span></article>
            <article class="ger-stat red"><small>Anulados hoy</small><strong>{{ number_format($anuladosHoy) }}</strong><span>Registros anulados</span></article>
        </div>
        <div class="ger-columns">
            <article class="ger-panel"><header class="ger-panel-header"><div><h4>Cumplimiento diario</h4><p>Agentes activos con arqueo diario</p></div></header><div class="ger-panel-body"><div class="ger-progress-label"><span>Nivel de cumplimiento</span><strong>{{ number_format($cumplimiento,1) }}%</strong></div><div class="ger-progress"><div style="width:{{ min(100,max(0,$cumplimiento)) }}%"></div></div><div class="ger-mini-grid"><div class="ger-mini"><small>Con arqueo</small><strong>{{ $agentesConArqueoHoy }}</strong></div><div class="ger-mini"><small>Pendientes</small><strong>{{ $agentesPendientesHoy }}</strong></div><div class="ger-mini"><small>Arqueos agente</small><strong>{{ $arqueosAgenteHoy }}</strong></div><div class="ger-mini"><small>Arqueos promotor</small><strong>{{ $arqueosPromotorHoy }}</strong></div></div><p class="ger-note">El cumplimiento permite identificar agentes que requieren seguimiento.</p></div></article>
            <article class="ger-panel"><header class="ger-panel-header"><div><h4>Últimos arqueos de agentes</h4><p>Actividad reciente MICOOPE</p></div><a class="ger-link" href="{{ url('/gerencia/arqueos') }}">Ver todos</a></header><div class="ger-table-wrap"><table class="ger-table"><thead><tr><th>Número</th><th>Fecha</th><th>Agente</th><th>Región</th><th>Ruta</th><th>Tipo</th><th>Diferencia</th></tr></thead><tbody>@forelse($ultimosArqueos as $arqueo)<tr><td><strong>{{ $arqueo->numero_arqueo }}</strong></td><td>{{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}</td><td><strong>{{ $arqueo->codigo_agente }}</strong><div>{{ $arqueo->nombre_negocio }}</div></td><td>{{ $arqueo->region_nombre ?? '—' }}</td><td>{{ $arqueo->ruta_nombre ?? '—' }}</td><td><span class="ger-tag">{{ match($arqueo->tipo) {'DIARIO_AGENTE'=>'Agente','VISITA_PROMOTOR'=>'Promotor','VISITA_AUDITORIA'=>'Auditoría',default=>str_replace('_',' ',$arqueo->tipo)} }}</span></td><td><strong>{{ $arqueo->diferencia < 0 ? 'Faltante' : ($arqueo->diferencia > 0 ? 'Sobrante' : 'Exacto') }}</strong><div>Q {{ number_format(abs((float)$arqueo->diferencia),2) }}</div></td></tr>@empty<tr><td colspan="7" class="ger-empty">No hay arqueos registrados.</td></tr>@endforelse</tbody></table></div></article>
        </div>
    </section>

    <section class="ger-pane" id="ger-panel-agencias" role="tabpanel" aria-labelledby="ger-tab-agencias" hidden>
        <div class="ger-pane-heading"><div><h3>Supervisión de Agencias ECOSABA</h3><p>Indicadores operativos del {{ \Illuminate\Support\Carbon::parse($fechaAgencias)->locale('es')->translatedFormat('d \d\e F \d\e Y') }}.</p></div><a class="ger-link" href="{{ route('agencias.reportes.index') }}">Centro de Reportes</a></div>
        <div class="ger-stats">
            <article class="ger-stat"><small>Agencias activas</small><strong>{{ number_format($indicadoresAgencias['agencias']) }}</strong><span>Habilitadas</span></article>
            <article class="ger-stat"><small>Ventanillas activas</small><strong>{{ number_format($indicadoresAgencias['ventanillas']) }}</strong><span>Habilitadas</span></article>
            <article class="ger-stat"><small>Arqueos hoy</small><strong>{{ number_format($indicadoresAgencias['arqueos_hoy']) }}</strong><span>No anulados</span></article>
            <article class="ger-stat green"><small>Certificados hoy</small><strong>{{ number_format($indicadoresAgencias['certificados_hoy']) }}</strong><span>Con certificación</span></article>
            <article class="ger-stat amber"><small>Pendientes de certificar</small><strong>{{ number_format($indicadoresAgencias['pendientes_hoy']) }}</strong><span>Sin certificación</span></article>
            <article class="ger-stat red"><small>Con faltante</small><strong>{{ number_format($indicadoresAgencias['faltantes_hoy']) }}</strong><span>Diferencia negativa</span></article>
            <article class="ger-stat green"><small>Con sobrante</small><strong>{{ number_format($indicadoresAgencias['sobrantes_hoy']) }}</strong><span>Diferencia positiva</span></article>
            <article class="ger-stat red"><small>Anulados hoy</small><strong>{{ number_format($indicadoresAgencias['anulados_hoy']) }}</strong><span>Registros anulados</span></article>
        </div>
        <div class="ger-columns">
            <article class="ger-panel"><header class="ger-panel-header"><div><h4>Cumplimiento diario de agencias</h4><p>Turnos finalizados con arqueo ordinario</p></div></header><div class="ger-panel-body"><div class="ger-progress-label"><span>Nivel de cumplimiento</span><strong>{{ $porcentajeInstitucional === null ? 'N/A' : number_format($porcentajeInstitucional,1).'%' }}</strong></div><div class="ger-progress"><div style="width:{{ $porcentajeInstitucional ?? 0 }}%"></div></div><div class="ger-mini-grid"><div class="ger-mini"><small>Con arqueo</small><strong>{{ $turnosCumplidosInstitucional }}</strong></div><div class="ger-mini"><small>Pendientes</small><strong>{{ $turnosPendientesInstitucional }}</strong></div><div class="ger-mini"><small>Turnos exigibles</small><strong>{{ $turnosExigiblesInstitucional }}</strong></div><div class="ger-mini"><small>Agencias activas</small><strong>{{ $indicadoresAgencias['agencias'] }}</strong></div></div><p class="ger-note">Solo se consideran exigibles los turnos cuyo cierre ya transcurrió.</p></div></article>
            <article class="ger-panel"><header class="ger-panel-header"><div><h4>Últimos arqueos de agencias</h4><p>Actividad reciente ECOSABA</p></div><a class="ger-link" href="{{ route('agencias.reportes.index') }}">Ver todos</a></header><div class="ger-table-wrap"><table class="ger-table"><thead><tr><th>Número</th><th>Fecha</th><th>Agencia</th><th>Receptor</th><th>Tipo</th><th>Estado</th><th>Diferencia</th></tr></thead><tbody>@forelse($ultimosArqueosAgencias as $arqueo)<tr><td><strong>{{ $arqueo->numero_arqueo }}</strong></td><td>{{ \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y') }}</td><td><strong>{{ $arqueo->agencia_nombre_historico }}</strong></td><td>{{ $arqueo->receptor_nombre_historico ?? '—' }}</td><td><span class="ger-tag">{{ str_replace('_',' ',$arqueo->tipo) }}</span></td><td><span class="ger-tag {{ $arqueo->estado === 'CERTIFICADO' ? 'ok' : ($arqueo->estado === 'ANULADO' ? 'bad' : 'warn') }}">{{ $arqueo->estado }}</span></td><td>Q {{ number_format(abs((float)$arqueo->diferencia),2) }}<div>{{ $arqueo->diferencia < 0 ? 'Faltante' : ($arqueo->diferencia > 0 ? 'Sobrante' : 'Exacto') }}</div></td></tr>@empty<tr><td colspan="7" class="ger-empty">No hay arqueos registrados en agencias.</td></tr>@endforelse</tbody></table></div></article>
        </div>
        <article class="ger-panel"><header class="ger-panel-header"><div><h4>Resumen por agencia — hoy</h4><p>Certificaciones, diferencias y cumplimiento de turnos</p></div><a class="ger-link" href="{{ route('agencias.reportes.index') }}">Ver reportes</a></header><div class="ger-table-wrap"><table class="ger-table"><thead><tr><th>Agencia</th><th>Estado</th><th>Cumplimiento</th><th>Arqueos</th><th>Certificados</th><th>Pendientes</th><th>Faltantes</th><th>Sobrantes</th><th>Reportes</th></tr></thead><tbody>@forelse($resumenAgencias as $agencia)@php $esperados=(int)($agencia->arqueos_esperados??0);$cumplidos=(int)($agencia->arqueos_cumplidos??0);$porcentajeAgencia=$esperados>0?min(100,round($cumplidos*100/$esperados,1)):null; @endphp<tr><td><strong>{{ $agencia->nombre }}</strong><div>{{ $agencia->codigo }}</div></td><td><span class="ger-tag {{ $agencia->estado ? 'ok' : 'bad' }}">{{ $agencia->estado ? 'Activa' : 'Inactiva' }}</span></td><td><strong>{{ $porcentajeAgencia===null?'N/A':number_format($porcentajeAgencia,1).'%' }}</strong><div class="ger-agency-progress"><div style="width:{{ $porcentajeAgencia??0 }}%"></div></div><small>{{ $cumplidos }} de {{ $esperados }} turnos</small></td><td>{{ number_format($agencia->total_hoy) }}</td><td>{{ number_format($agencia->certificados_hoy) }}</td><td>{{ number_format($agencia->pendientes_hoy) }}</td><td>{{ number_format($agencia->faltantes_hoy) }}</td><td>{{ number_format($agencia->sobrantes_hoy) }}</td><td><a class="ger-link" href="{{ route('agencias.reportes.index',['agencia_id'=>$agencia->id]) }}">Consultar</a></td></tr>@empty<tr><td colspan="9" class="ger-empty">No hay agencias registradas.</td></tr>@endforelse</tbody></table></div><div class="ger-panel-body"><p class="ger-note">El cumplimiento se calcula con turnos programados cuyo horario de cierre ya transcurrió y que tienen arqueo ordinario vigente.</p></div></article>
    </section>
</div>
<script>
(function () {
    const tabs = Array.from(document.querySelectorAll('[data-ger-tab]'));
    const allowed = ['general','agentes','agencias'];
    function activate(name, updateUrl) {
        if (!allowed.includes(name)) name = 'general';
        tabs.forEach(function (tab) {
            const selected = tab.dataset.gerTab === name;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.tabIndex = selected ? 0 : -1;
        });
        allowed.forEach(function (key) {
            document.getElementById('ger-panel-' + key).hidden = key !== name;
        });
        if (updateUrl && window.history && window.history.replaceState) {
            window.history.replaceState(null, '', window.location.pathname + window.location.search + '#' + name);
        }
    }
    tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function () { activate(tab.dataset.gerTab, true); });
        tab.addEventListener('keydown', function (event) {
            if (!['ArrowLeft','ArrowRight','Home','End'].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
            tabs[next].focus();
            activate(tabs[next].dataset.gerTab, true);
        });
    });
    window.addEventListener('hashchange', function () { activate(window.location.hash.slice(1), false); });
    activate(window.location.hash.slice(1), false);
})();
</script>
@endsection
