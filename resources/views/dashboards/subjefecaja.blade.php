@extends('layouts.subjefe-caja')

@section('title', 'Dashboard del Subjefe de Caja')

@push('styles')
<style>
    .sc-dashboard{display:grid;gap:22px}
    .sc-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:15px;flex-wrap:wrap}
    .sc-heading h1{margin:0;color:#06284f;font-size:clamp(23px,3vw,30px)}
    .sc-heading p{margin:7px 0 0;color:#63768b;font-size:14px;line-height:1.6}
    .sc-date{background:#eaf2fc;color:#164c96;border:1px solid #cfe0f5;border-radius:12px;padding:10px 14px;font-size:13px;font-weight:750}
    .sc-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px}
    .sc-stat,.sc-panel{background:#fff;border:1px solid #dfe7ee;border-radius:16px;box-shadow:0 5px 22px rgba(6,40,79,.045)}
    .sc-stat{padding:20px;min-width:0}
    .sc-stat-label{color:#63768b;font-size:12px;font-weight:750;line-height:1.5}
    .sc-stat-value{display:block;color:#06284f;font-size:32px;font-weight:850;margin-top:10px;line-height:1.1}
    .sc-stat-note{display:block;color:#8493a3;font-size:11px;margin-top:9px}
    .sc-stat.warning .sc-stat-value{color:#aa6a12}
    .sc-stat.danger .sc-stat-value{color:#b33c3c}
    .sc-stat.success .sc-stat-value{color:#14814a}
    .sc-grid{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(270px,1fr);gap:18px}
    .sc-panel{padding:21px;min-width:0}
    .sc-panel h2{font-size:17px;color:#06284f;margin:0 0 14px}
    .sc-panel-sub{color:#708194;font-size:12px;margin:-7px 0 17px}
    .sc-table-wrap{overflow-x:auto}
    .sc-table{width:100%;border-collapse:collapse;font-size:12px;text-align:left}
    .sc-table th{color:#6a7c8e;font-weight:800;background:#f6f9fc;padding:12px 10px;white-space:nowrap}
    .sc-table td{padding:13px 10px;border-bottom:1px solid #edf1f5;vertical-align:middle}
    .sc-table tr:last-child td{border-bottom:0}
    .sc-strong{font-weight:800;color:#183c62}
    .sc-muted{color:#7b8c9d}
    .sc-tag{display:inline-block;border-radius:999px;padding:5px 9px;font-size:11px;font-weight:750;background:#fff5e4;color:#9b6210;white-space:nowrap}
    .sc-tag.ok{background:#e9f8ee;color:#167c46}
    .sc-tag.neutral{background:#edf3f9;color:#4e6c89}
    .sc-empty{padding:20px 12px;text-align:center;color:#718397;background:#f8fafc;border:1px dashed #dbe5ee;border-radius:11px;font-size:13px;line-height:1.6}
    .sc-agency{padding:13px 0;border-bottom:1px solid #edf1f5}
    .sc-agency:last-child{border-bottom:0}
    .sc-agency strong{display:block;color:#183c62;font-size:13px}
    .sc-agency small{display:block;color:#708194;margin-top:5px;line-height:1.5}
    .sc-actions{display:flex;gap:10px;flex-wrap:wrap}
    .sc-action{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:10px;background:#164c96;color:white;font-size:12px;font-weight:750;text-decoration:none}
    .sc-action.secondary{background:#eaf2fc;color:#164c96}
    .sc-action:hover{filter:brightness(.95)}
    @media(max-width:1250px){.sc-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:900px){.sc-grid{grid-template-columns:1fr}}
    @media(max-width:540px){.sc-stats{grid-template-columns:1fr}.sc-panel{padding:16px}}
</style>
@endpush

@section('content')
<div class="sc-dashboard">
    <div class="sc-heading">
        <div>
            <h1>Supervisión de Caja</h1>
            <p>Resumen operativo de las agencias bajo su responsabilidad como Subjefe de Caja.</p>
        </div>
        <span class="sc-date">{{ \Illuminate\Support\Carbon::parse($fecha)->format('d/m/Y') }}</span>
    </div>

    @if($asignaciones->isEmpty())
        <div class="alert-message warning">No tiene asignaciones vigentes a una agencia. Consulte con Administración para verificar su asignación.</div>
    @endif

    <div class="sc-stats">
        <div class="sc-stat">
            <span class="sc-stat-label">Arqueos del día</span>
            <strong class="sc-stat-value">{{ $totalArqueos }}</strong>
            <span class="sc-stat-note">Sin incluir arqueos anulados</span>
        </div>
        <div class="sc-stat warning">
            <span class="sc-stat-label">Pendientes de certificación</span>
            <strong class="sc-stat-value">{{ $pendientesCertificacion }}</strong>
            <span class="sc-stat-note">Firmados por receptor, sin certificar</span>
        </div>
        <div class="sc-stat success">
            <span class="sc-stat-label">Arqueos certificados</span>
            <strong class="sc-stat-value">{{ $certificados }}</strong>
            <span class="sc-stat-note">Certificados en la fecha operativa</span>
        </div>
        <div class="sc-stat danger">
            <span class="sc-stat-label">Arqueos con diferencia</span>
            <strong class="sc-stat-value">{{ $conDiferencia }}</strong>
            <span class="sc-stat-note">Faltante o sobrante distinto de cero</span>
        </div>
        <div class="sc-stat">
            <span class="sc-stat-label">Jornadas programadas</span>
            <strong class="sc-stat-value">{{ $totalJornadas }}</strong>
            <span class="sc-stat-note">Registros de jornada del día</span>
        </div>
        <div class="sc-stat">
            <span class="sc-stat-label">Receptores programados</span>
            <strong class="sc-stat-value">{{ $receptoresProgramados }}</strong>
            <span class="sc-stat-note">Usuarios distintos con jornada hoy</span>
        </div>
    </div>

    <div class="sc-actions">
        @if(\Illuminate\Support\Facades\Route::has('subjefecaja.certificaciones.index'))
            <a class="sc-action" href="{{ route('subjefecaja.certificaciones.index') }}">Revisar certificaciones</a>
        @endif
        @if(\Illuminate\Support\Facades\Route::has('subjefecaja.sorpresivos.index'))
            <a class="sc-action secondary" href="{{ route('subjefecaja.sorpresivos.index') }}">Arqueos sorpresivos</a>
        @endif
        @if(\Illuminate\Support\Facades\Route::has('subjefecaja.cumplimiento-agencias.index'))
            <a class="sc-action secondary" href="{{ route('subjefecaja.cumplimiento-agencias.index') }}">Ver cumplimiento</a>
        @endif
        @if(\Illuminate\Support\Facades\Route::has('subjefecaja.jornadas.index'))
            <a class="sc-action secondary" href="{{ route('subjefecaja.jornadas.index') }}">Gestión de jornadas</a>
        @endif
    </div>

    <div class="sc-grid">
        <section class="sc-panel">
            <h2>Arqueos pendientes de certificación</h2>
            <p class="sc-panel-sub">Últimos ocho arqueos firmados que requieren atención.</p>
            @if($arqueosPendientes->isEmpty())
                <div class="sc-empty">No hay arqueos pendientes de certificación para hoy.</div>
            @else
                <div class="sc-table-wrap">
                    <table class="sc-table">
                        <thead><tr><th>Arqueo</th><th>Agencia</th><th>Receptor</th><th>Total arqueado</th></tr></thead>
                        <tbody>
                        @foreach($arqueosPendientes as $arqueo)
                            <tr>
                                <td class="sc-strong">{{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}</td>
                                <td>{{ $arqueo->agencia_nombre_historico ?: 'Agencia #'.$arqueo->agencia_id }}</td>
                                <td>{{ $arqueo->receptor_nombre_historico ?: ($arqueo->receptor?->nombre_completo ?? 'No disponible') }}</td>
                                <td>Q {{ number_format((float) $arqueo->total_arqueado, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="sc-panel">
            <h2>Agencias asignadas</h2>
            <p class="sc-panel-sub">Asignaciones vigentes del usuario.</p>
            @forelse($asignaciones as $asignacion)
                <div class="sc-agency">
                    <strong>{{ $asignacion->agencia?->nombre ?? 'Agencia #'.$asignacion->agencia_id }}</strong>
                    <small>{{ str_replace('_', ' ', $asignacion->cargo) }} · {{ $asignacion->tipo }}</small>
                    <small>Desde {{ $asignacion->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha' }} · Hasta {{ $asignacion->fecha_fin?->format('d/m/Y') ?? 'Sin vencimiento' }}</small>
                </div>
            @empty
                <div class="sc-empty">No existen agencias asignadas actualmente.</div>
            @endforelse
        </section>
    </div>

    <section class="sc-panel">
        <h2>Últimos arqueos del día</h2>
        <p class="sc-panel-sub">Actividad registrada en sus agencias.</p>
        @if($ultimosArqueos->isEmpty())
            <div class="sc-empty">Todavía no se han registrado arqueos en la fecha operativa actual.</div>
        @else
            <div class="sc-table-wrap">
                <table class="sc-table">
                    <thead><tr><th>Número</th><th>Agencia</th><th>Receptor</th><th>Tipo</th><th>Situación</th><th>Diferencia</th></tr></thead>
                    <tbody>
                    @foreach($ultimosArqueos as $arqueo)
                        <tr>
                            <td class="sc-strong">{{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}</td>
                            <td>{{ $arqueo->agencia_nombre_historico ?: 'Agencia #'.$arqueo->agencia_id }}</td>
                            <td>{{ $arqueo->receptor_nombre_historico ?: ($arqueo->receptor?->nombre_completo ?? 'No disponible') }}</td>
                            <td>{{ str_replace('_', ' ', $arqueo->tipo) }}</td>
                            <td>
                                @if($arqueo->certificado_at)
                                    <span class="sc-tag ok">Certificado</span>
                                @elseif($arqueo->firmado_receptor_at)
                                    <span class="sc-tag">Por certificar</span>
                                @else
                                    <span class="sc-tag neutral">En proceso</span>
                                @endif
                            </td>
                            <td>Q {{ number_format((float) $arqueo->diferencia, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
