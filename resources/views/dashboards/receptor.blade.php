
@extends('layouts.receptor')

@section('title', 'Dashboard del Receptor')

@push('styles')
<style>
    .rec-dashboard { --rec-ink:#133352; --rec-muted:#657b92; --rec-blue:#0c4d87; --rec-line:#dfebf5; color:var(--rec-ink); display:grid; gap:22px; }
    .rec-dashboard * { box-sizing:border-box; }
    .rec-welcome { padding:26px 28px; border-radius:18px; background:linear-gradient(120deg,#07375f,#0b5a91); color:#fff; display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; }
    .rec-welcome h1 { color:#fff; font-size:clamp(23px,3vw,30px); line-height:1.25; margin:5px 0 9px; }
    .rec-welcome p { margin:0; color:#d9ecfa; font-size:14px; line-height:1.65; }
    .rec-welcome .rec-date { padding:10px 14px; border:1px solid #ffffff55; border-radius:12px; font-weight:700; font-size:13px; }
    .rec-eyebrow { text-transform:uppercase; letter-spacing:.12em; font-size:11px; font-weight:800; opacity:.84; }
    .rec-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:14px; }
    .rec-stat { background:#fff; border:1px solid var(--rec-line); border-radius:16px; padding:19px 17px; box-shadow:0 5px 18px #102f5010; }
    .rec-stat .label { display:block; color:var(--rec-muted); font-size:12px; line-height:1.4; font-weight:700; min-height:35px; }
    .rec-stat strong { display:block; font-size:32px; color:var(--rec-blue); margin-top:8px; font-weight:800; }
    .rec-stat small { display:block; margin-top:6px; font-size:11px; color:var(--rec-muted); }
    .rec-section-head { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:14px; }
    .rec-section-head h2 { margin:0; color:var(--rec-ink); font-size:19px; }
    .rec-section-head a { color:var(--rec-blue); text-decoration:none; font-size:13px; font-weight:700; }
    .rec-panel { background:#fff; border:1px solid var(--rec-line); border-radius:16px; padding:20px; min-width:0; }
    .rec-columns { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(0,1fr); gap:18px; align-items:start; }
    .rec-jornada { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:15px 0; border-top:1px solid var(--rec-line); }
    .rec-jornada:first-of-type { border-top:0; }
    .rec-jornada h3 { margin:0 0 5px; font-size:14px; color:var(--rec-ink); }
    .rec-jornada p { margin:0; font-size:12px; color:var(--rec-muted); line-height:1.6; }
    .rec-pill { display:inline-flex; border-radius:99px; padding:5px 9px; background:#eaf2fc; color:#205c91; font-size:11px; font-weight:800; white-space:nowrap; }
    .rec-actions { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
    .rec-action { display:flex; flex-direction:column; justify-content:center; gap:7px; min-height:82px; padding:15px; border:1px solid #d8e7f5; border-radius:12px; background:#f8fbff; text-decoration:none; color:var(--rec-blue); font-weight:800; font-size:13px; transition:background .15s ease; }
    .rec-action:hover { background:#eaf4ff; }
    .rec-action span { font-size:11px; color:var(--rec-muted); font-weight:500; }
    .rec-table-wrap { overflow-x:auto; }
    .rec-table { width:100%; border-collapse:collapse; text-align:left; font-size:13px; }
    .rec-table th { padding:12px; background:#f2f7fc; color:#506e8a; font-size:11px; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
    .rec-table td { padding:13px 12px; border-bottom:1px solid var(--rec-line); vertical-align:middle; }
    .rec-table a { color:var(--rec-blue); font-weight:800; text-decoration:none; }
    .rec-empty { padding:22px 8px; color:var(--rec-muted); font-size:13px; line-height:1.65; text-align:center; }
    .rec-footnote { margin:0; font-size:12px; color:var(--rec-muted); line-height:1.6; }
    @media(max-width:1180px) { .rec-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media(max-width:850px) { .rec-columns { grid-template-columns:1fr; } }
    @media(max-width:580px) { .rec-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .rec-welcome { padding:21px; } .rec-panel { padding:15px; } .rec-actions { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="rec-dashboard">
    <section class="rec-welcome">
        <div>
            <div class="rec-eyebrow">Panel de operaciones · Receptor</div>
            <h1>Bienvenido, {{ $usuario->nombre_completo }}</h1>
            <p>Consulta tus jornadas, arqueos y operaciones de efectivo asignadas.</p>
        </div>
        <div class="rec-date">{{ $fecha->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}</div>
    </section>

    <section aria-label="Indicadores del día">
        <div class="rec-section-head"><h2>Resumen de hoy</h2></div>
        <div class="rec-grid">
            <div class="rec-stat"><span class="label">Jornadas asignadas</span><strong>{{ $indicadores['jornadas_hoy'] }}</strong><small>Fecha operativa actual</small></div>
            <div class="rec-stat"><span class="label">Arqueos registrados</span><strong>{{ $indicadores['arqueos_hoy'] }}</strong><small>Vinculados a jornadas de hoy</small></div>
            <div class="rec-stat"><span class="label">Pendientes de certificación</span><strong>{{ $indicadores['pendientes_certificacion'] }}</strong><small>Firmados y no certificados</small></div>
            <div class="rec-stat"><span class="label">Arqueos certificados</span><strong>{{ $indicadores['certificados_hoy'] }}</strong><small>No anulados</small></div>
            <div class="rec-stat"><span class="label">Con diferencias</span><strong>{{ $indicadores['con_diferencia'] }}</strong><small>Certificados con diferencia distinta de Q 0.00</small></div>
        </div>
    </section>

    <div class="rec-columns">
        <section class="rec-panel">
            <div class="rec-section-head">
                <h2>Mis jornadas de hoy</h2>
                <a href="{{ route('receptor.jornadas.index') }}">Ver todas</a>
            </div>
            @forelse($jornadasHoy as $jornada)
                <div class="rec-jornada">
                    <div>
                        <h3>{{ $jornada->agencia?->nombre ?? 'Agencia sin nombre' }} · {{ $jornada->ventanilla?->nombre ?? 'Ventanilla sin nombre' }}</h3>
                        <p>Turno: {{ $jornada->turno?->nombre ?? 'No especificado' }}</p>
                        <p>Horario: {{ $jornada->inicio_programado?->format('H:i') ?? '—' }} – {{ $jornada->fin_programado?->format('H:i') ?? '—' }}</p>
                        <p><a href="{{ route('receptor.jornadas.show', ['jornada' => $jornada->id]) }}">Consultar jornada</a></p>
                    </div>
                    <span class="rec-pill">{{ $jornada->estado ?? 'Sin estado' }}</span>
                </div>
            @empty
                <div class="rec-empty">No tienes jornadas registradas para la fecha operativa de hoy.</div>
            @endforelse
        </section>

        <section class="rec-panel">
            <div class="rec-section-head"><h2>Accesos rápidos</h2></div>
            <div class="rec-actions">
                <a class="rec-action" href="{{ route('receptor.jornadas.index') }}">Mis jornadas <span>Seleccionar jornada y gestionar arqueos</span></a>
                <a class="rec-action" href="{{ route('receptor.arqueos.index') }}">Mis arqueos <span>Consultar registros ordinarios</span></a>
                <a class="rec-action" href="{{ route('receptor.sorpresivos.index') }}">Arqueos de Jefatura de Caja <span>Consultar solicitudes</span></a>
                <a class="rec-action" href="{{ route('receptor.auditoria-agencias.index') }}">Arqueos de auditoría <span>Consultar y firmar cuando corresponda</span></a>
                <a class="rec-action" href="{{ route('receptor.cumplimiento-agencias.index') }}">Cumplimiento <span>Revisar registros por turno</span></a>
            </div>
        </section>
    </div>

    <section class="rec-panel">
        <div class="rec-section-head">
            <h2>Mis arqueos recientes de hoy</h2>
            <a href="{{ route('receptor.arqueos.index') }}">Historial de arqueos</a>
        </div>
        <div class="rec-table-wrap">
            <table class="rec-table">
                <thead><tr><th>Número</th><th>Tipo</th><th>Agencia</th><th>Total arqueado</th><th>Situación</th><th>Detalle</th></tr></thead>
                <tbody>
                    @forelse($arqueosRecientes as $arqueo)
                        <tr>
                            <td>{{ $arqueo->numero_arqueo ?? ('#'.$arqueo->id) }}</td>
                            <td>{{ $arqueo->tipo ?? '—' }}</td>
                            <td>{{ $arqueo->agencia?->nombre ?? '—' }}</td>
                            <td>Q {{ $arqueo->total_arqueado === null ? '—' : number_format((float) $arqueo->total_arqueado, 2) }}</td>
                            <td>
                                <span class="rec-pill">
                                    @if($arqueo->anulado_at) Anulado
                                    @elseif($arqueo->certificado_at) Certificado
                                    @elseif($arqueo->firmado_receptor_at) Firmado, pendiente
                                    @else {{ $arqueo->estado ?? 'En proceso' }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($arqueo->jornada_id)
                                    <a href="{{ route('receptor.jornadas.show', ['jornada' => $arqueo->jornada_id]) }}">Ver jornada</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="rec-empty">Aún no tienes arqueos registrados con fecha operativa de hoy.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <p class="rec-footnote">Los indicadores muestran registros asociados al receptor autenticado. Los totales no suman movimientos de distintas modalidades para evitar duplicaciones de efectivo.</p>
</div>
@endsection
