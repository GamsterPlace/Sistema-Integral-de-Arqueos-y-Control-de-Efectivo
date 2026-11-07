@extends('layouts.auditoria')

@section('title', 'Dashboard de Auditoría')
@section('module-title', 'Dashboard de Auditoría')

@push('styles')
<style>
.aud-dashboard{display:grid;gap:20px;color:#19344d}
.aud-hero{background:linear-gradient(120deg,#07345f,#164c96 65%,#128257);color:#fff;border-radius:19px;padding:25px 28px}
.aud-hero small{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#c7e5f5;font-weight:800}
.aud-hero h2{font-size:25px;margin:7px 0 8px}.aud-hero p{font-size:13px;line-height:1.7;color:#d8e9f5;margin:0;max-width:760px}
.aud-tabs{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px;margin-bottom:24px}
.aud-tabs button{font:inherit;text-align:left;display:flex;align-items:center;gap:15px;background:white;border:2px solid #dde8f1;border-radius:15px;padding:18px;cursor:pointer;color:#153e64;transition:.15s;box-shadow:0 5px 15px #1439530b}
.aud-tabs button:hover{border-color:#86acd0}.aud-tabs button[aria-selected="true"]{border-color:#164c96;background:#f0f6fe;box-shadow:0 7px 20px #164c961b}
.aud-tabs-icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:12px;background:#e8f1fb;color:#164c96}
.aud-tabs-icon svg{width:23px;height:23px}.aud-tabs strong{display:block;font-size:15px}.aud-tabs small{display:block;font-size:11px;color:#788d9e;margin-top:4px;line-height:1.4}

.aud-panel[hidden]{display:none!important}.aud-panel{display:grid;gap:19px}
.aud-heading{margin:0}.aud-heading h3{font-size:19px;color:#0b315f;margin:0 0 5px}.aud-heading p{font-size:12px;color:#6e8191;margin:0}
.aud-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.aud-card{background:#fff;border:1px solid #e0e8ee;border-radius:16px;padding:18px;min-height:125px;box-shadow:0 6px 20px rgba(18,49,77,.04)}
.aud-card span{display:block;color:#71818e;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;line-height:1.5}
.aud-card strong{display:block;color:#123e6a;font-size:29px;margin-top:12px}.aud-card small{display:block;color:#8395a2;font-size:10px;margin-top:6px}
.aud-card.warn strong{color:#ae7409}.aud-card.danger strong{color:#b54141}.aud-card.success strong{color:#15814e}
.aud-box{background:#fff;border:1px solid #e0e8ee;border-radius:17px;overflow:hidden}
.aud-box-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:17px 20px;border-bottom:1px solid #e9eef2}
.aud-box-head h4{margin:0;color:#123e6a;font-size:15px}.aud-box-head p{margin:5px 0 0;color:#778b99;font-size:11px}
.aud-link{font-size:12px;color:#164c96;font-weight:800;text-decoration:none}.aud-link:hover{text-decoration:underline}
.aud-scroll{overflow-x:auto}.aud-table{width:100%;border-collapse:collapse;min-width:720px}.aud-table th{background:#f6f9fb;color:#62798c;text-transform:uppercase;font-size:10px;text-align:left;letter-spacing:.3px}.aud-table th,.aud-table td{padding:13px 16px;border-bottom:1px solid #edf1f4}.aud-table td{font-size:12px;color:#344c60}.aud-table tbody tr:last-child td{border-bottom:0}.aud-table strong{color:#133e68}
.aud-pill{display:inline-block;padding:5px 9px;border-radius:50px;background:#edf3f9;color:#2b5b88;font-size:10px;font-weight:800}.aud-pill.good{background:#e8f7ee;color:#197847}.aud-pill.bad{background:#fcecec;color:#a93d3d}
.aud-empty{padding:35px;text-align:center;color:#788b9a;font-size:13px}.aud-note{font-size:11px;line-height:1.65;color:#687d8e;margin:0;padding:14px 18px;background:#f7fafc;border-top:1px solid #edf1f4}
@media(max-width:1100px){.aud-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.aud-cards{grid-template-columns:1fr}.aud-hero{padding:22px}.aud-tabs{grid-template-columns:1fr}.aud-tabs button{padding:15px}.aud-box-head{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
<div class="aud-dashboard" id="audDashboard">
    <div class="aud-hero">
        <small>Supervisión y control institucional</small>
        <h2>Panel de Auditoría</h2>
        <p>Consulte su actividad de auditoría y supervise los arqueos de agentes MICOOPE y agencias ECOSABA desde un mismo lugar.</p>
    </div>

    <div class="aud-tabs" role="tablist" aria-label="Seleccionar área del dashboard">
        <button type="button" id="aud-tab-general" role="tab" aria-controls="aud-general" aria-selected="true" data-target="general">
            <span class="aud-tabs-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span><span><strong>General</strong><small>Panorama institucional</small></span>
        </button>
        <button type="button" id="aud-tab-agentes" role="tab" aria-controls="aud-agentes" aria-selected="false" data-target="agentes" tabindex="-1">
            <span class="aud-tabs-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a7 7 0 0 1 14 0v2M18 8h5M20.5 5.5v5"/></svg></span><span><strong>Agentes</strong><small>Operación MICOOPE</small></span>
        </button>
        <button type="button" id="aud-tab-agencias" role="tab" aria-controls="aud-agencias" aria-selected="false" data-target="agencias" tabindex="-1">
            <span class="aud-tabs-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 21v-8h8v8M7 8h2m6 0h2"/></svg></span><span><strong>Agencias</strong><small>Operación ECOSABA</small></span>
        </button>
    </div>

    <section class="aud-panel" id="aud-general" role="tabpanel" aria-labelledby="aud-tab-general">
        <div class="aud-heading"><h3>Resumen general de Auditoría</h3><p>Actividad del auditor autenticado y cobertura institucional a la fecha.</p></div>
        <div class="aud-cards">
            <div class="aud-card"><span>Auditorías propias de agentes</span><strong>{{ number_format($resumen->total) }}</strong><small>Histórico del auditor autenticado</small></div>
            <div class="aud-card success"><span>Agentes auditados</span><strong>{{ number_format($agentesAuditados) }}</strong><small>Agentes distintos con auditoría válida</small></div>
            <div class="aud-card"><span>Agentes activos</span><strong>{{ number_format($indicadoresAgentes['activos']) }}</strong><small>Cobertura institucional</small></div>
            <div class="aud-card"><span>Agencias activas</span><strong>{{ number_format($indicadoresAgencias['agencias']) }}</strong><small>Red institucional</small></div>
            <div class="aud-card"><span>Arqueos diarios de agentes hoy</span><strong>{{ number_format($indicadoresAgentes['arqueos_hoy']) }}</strong><small>Registros no anulados</small></div>
            <div class="aud-card"><span>Arqueos de agencias hoy</span><strong>{{ number_format($indicadoresAgencias['arqueos_hoy']) }}</strong><small>Todos los tipos no anulados</small></div>
            <div class="aud-card danger"><span>Faltantes en auditorías propias</span><strong>{{ number_format($resumen->faltantes) }}</strong><small>Histórico del auditor autenticado</small></div>
            <div class="aud-card warn"><span>Auditorías propias pendientes</span><strong>{{ number_format($resumen->pendientes) }}</strong><small>Pendientes de certificación</small></div>
        </div>
        <div class="aud-box"><div class="aud-box-head"><div><h4>Alcance de la información</h4><p>Indicadores claramente separados por origen.</p></div></div>
            <p class="aud-note">Las auditorías propias corresponden exclusivamente a arqueos de tipo VISITA_AUDITORIA creados por su usuario. Los indicadores de agentes y agencias muestran información institucional y no se limitan a sus auditorías personales.</p>
        </div>
    </section>

    <section class="aud-panel" id="aud-agentes" role="tabpanel" aria-labelledby="aud-tab-agentes" hidden>
        <div class="aud-heading"><h3>Supervisión de Agentes MICOOPE</h3><p>Actividad institucional de arqueos diarios y resultados de sus auditorías.</p></div>
        <div class="aud-cards">
            <div class="aud-card"><span>Agentes activos</span><strong>{{ number_format($indicadoresAgentes['activos']) }}</strong></div>
            <div class="aud-card success"><span>Agentes con arqueo hoy</span><strong>{{ number_format($indicadoresAgentes['con_arqueo_hoy']) }}</strong></div>
            <div class="aud-card warn"><span>Sin registro diario hoy</span><strong>{{ number_format($indicadoresAgentes['sin_registro_hoy']) }}</strong><small>No implica incumplimiento antes del cierre</small></div>
            <div class="aud-card"><span>Arqueos diarios hoy</span><strong>{{ number_format($indicadoresAgentes['arqueos_hoy']) }}</strong></div>
            <div class="aud-card danger"><span>Faltantes diarios hoy</span><strong>{{ number_format($indicadoresAgentes['faltantes_hoy']) }}</strong></div>
            <div class="aud-card success"><span>Sobrantes diarios hoy</span><strong>{{ number_format($indicadoresAgentes['sobrantes_hoy']) }}</strong></div>
            <div class="aud-card"><span>Auditorías propias certificadas</span><strong>{{ number_format($resumen->certificados) }}</strong></div>
            <div class="aud-card warn"><span>Auditorías propias anuladas</span><strong>{{ number_format($resumen->anulados) }}</strong></div>
        </div>
        <div class="aud-box"><div class="aud-box-head"><div><h4>Mis auditorías recientes de agentes</h4><p>Últimas ocho verificaciones realizadas por su usuario.</p></div>
            @if(\Illuminate\Support\Facades\Route::has('auditoria.arqueos.index'))<a class="aud-link" href="{{ route('auditoria.arqueos.index') }}">Ver historial</a>@endif</div>
            @if($arqueosRecientes->isEmpty())<div class="aud-empty">Todavía no existen auditorías de agentes registradas por su usuario.</div>
            @else<div class="aud-scroll"><table class="aud-table"><thead><tr><th>Número</th><th>Fecha</th><th>Agente</th><th>Región</th><th>Ruta</th><th>Estado</th><th>Diferencia</th></tr></thead><tbody>
            @foreach($arqueosRecientes as $arqueo)<tr><td><strong>{{ $arqueo->numero_arqueo }}</strong></td><td>{{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}</td><td>{{ $arqueo->codigo_agente_historico }} — {{ $arqueo->nombre_negocio_historico }}</td><td>{{ $arqueo->region_historica ?? '—' }}</td><td>{{ $arqueo->ruta_historica ?? '—' }}</td><td><span class="aud-pill {{ $arqueo->estado === 'CERTIFICADO' ? 'good' : ($arqueo->estado === 'ANULADO' ? 'bad' : '') }}">{{ str_replace('_', ' ', $arqueo->estado) }}</span></td><td>Q {{ number_format((float)$arqueo->diferencia, 2) }}</td></tr>@endforeach
            </tbody></table></div>@endif
        </div>
        <p class="aud-note">La cantidad de agentes sin registro hoy es informativa; para determinar incumplimiento se debe aplicar el calendario y las reglas operativas correspondientes.</p>
    </section>

    <section class="aud-panel" id="aud-agencias" role="tabpanel" aria-labelledby="aud-tab-agencias" hidden>
        <div class="aud-heading"><h3>Supervisión de Agencias ECOSABA</h3><p>Arqueos por agencia, certificaciones y diferencias registradas hoy.</p></div>
        <div class="aud-cards">
            <div class="aud-card"><span>Agencias activas</span><strong>{{ number_format($indicadoresAgencias['agencias']) }}</strong></div>
            <div class="aud-card"><span>Ventanillas activas</span><strong>{{ number_format($indicadoresAgencias['ventanillas']) }}</strong></div>
            <div class="aud-card"><span>Arqueos hoy</span><strong>{{ number_format($indicadoresAgencias['arqueos_hoy']) }}</strong></div>
            <div class="aud-card success"><span>Certificados hoy</span><strong>{{ number_format($indicadoresAgencias['certificados_hoy']) }}</strong></div>
            <div class="aud-card warn"><span>Pendientes de certificación</span><strong>{{ number_format($indicadoresAgencias['pendientes_hoy']) }}</strong></div>
            <div class="aud-card danger"><span>Faltantes certificados</span><strong>{{ number_format($indicadoresAgencias['faltantes_hoy']) }}</strong></div>
            <div class="aud-card success"><span>Sobrantes certificados</span><strong>{{ number_format($indicadoresAgencias['sobrantes_hoy']) }}</strong></div>
            <div class="aud-card danger"><span>Anulados hoy</span><strong>{{ number_format($indicadoresAgencias['anulados_hoy']) }}</strong></div>
        </div>
        <div class="aud-box"><div class="aud-box-head"><div><h4>Estado de las agencias hoy</h4><p>El cumplimiento se evalúa únicamente para turnos finalizados que no cruzan medianoche.</p></div></div>
            @if($resumenAgencias->isEmpty())<div class="aud-empty">No hay agencias registradas.</div>
            @else<div class="aud-scroll"><table class="aud-table"><thead><tr><th>Agencia</th><th>Arqueos</th><th>Certificados</th><th>Pendientes</th><th>Faltantes</th><th>Sobrantes</th><th>Turnos exigibles</th><th>Cumplidos</th></tr></thead><tbody>
            @foreach($resumenAgencias as $agencia)<tr><td><strong>{{ $agencia->codigo }} — {{ $agencia->nombre }}</strong></td><td>{{ $agencia->total_hoy }}</td><td>{{ $agencia->certificados_hoy }}</td><td>{{ $agencia->pendientes_hoy }}</td><td>{{ $agencia->faltantes_hoy }}</td><td>{{ $agencia->sobrantes_hoy }}</td><td>{{ $agencia->turnos_exigibles }}</td><td><span class="aud-pill {{ $agencia->turnos_cumplidos >= $agencia->turnos_exigibles && $agencia->turnos_exigibles > 0 ? 'good' : '' }}">{{ $agencia->turnos_cumplidos }}</span></td></tr>@endforeach
            </tbody></table></div>@endif
            <p class="aud-note">Los turnos aún no finalizados no se consideran incumplidos. Los turnos que cruzan medianoche requieren evaluación en el módulo específico de cumplimiento. Las cifras de arqueos pueden incluir tipos distintos al ordinario.</p>
        </div>
        <div class="aud-box"><div class="aud-box-head"><div><h4>Arqueos recientes de agencias</h4><p>Últimos registros institucionales.</p></div></div>
            @if($ultimosArqueosAgencias->isEmpty())<div class="aud-empty">No existen arqueos de agencias registrados.</div>
            @else<div class="aud-scroll"><table class="aud-table"><thead><tr><th>Número</th><th>Fecha</th><th>Agencia</th><th>Receptor</th><th>Tipo</th><th>Estado</th><th>Diferencia</th></tr></thead><tbody>
            @foreach($ultimosArqueosAgencias as $arqueo)<tr><td><strong>{{ $arqueo->numero_arqueo }}</strong></td><td>{{ \Carbon\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y') }}</td><td>{{ $arqueo->agencia_nombre_historico ?? '—' }}</td><td>{{ $arqueo->receptor_nombre_historico ?? '—' }}</td><td>{{ str_replace('_', ' ', $arqueo->tipo) }}</td><td><span class="aud-pill {{ $arqueo->estado === 'ANULADO' ? 'bad' : ($arqueo->certificado_at ? 'good' : '') }}">{{ str_replace('_', ' ', $arqueo->estado) }}</span></td><td>Q {{ number_format((float)$arqueo->diferencia, 2) }}</td></tr>@endforeach
            </tbody></table></div>@endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('audDashboard');
    if (!root) return;
    const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
    const panels = Array.from(root.querySelectorAll('[role="tabpanel"]'));
    function activate(name) {
        tabs.forEach(tab => { const active = tab.dataset.target === name; tab.setAttribute('aria-selected', active ? 'true' : 'false'); tab.tabIndex = active ? 0 : -1; });
        panels.forEach(panel => { panel.hidden = panel.id !== 'aud-' + name; });
    }
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(tab.dataset.target));
        tab.addEventListener('keydown', event => {
            if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return;
            event.preventDefault();
            const offset = event.key === 'ArrowRight' ? 1 : -1;
            const next = tabs[(index + offset + tabs.length) % tabs.length];
            activate(next.dataset.target); next.focus();
        });
    });
    activate('general');
});
</script>
@endpush
