
@extends('layouts.jefe-caja')

@section('title', 'Dashboard del Jefe de Caja')

@push('styles')
<style>
    .jc-dashboard{display:grid;gap:22px}
    .jc-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:15px;flex-wrap:wrap}
    .jc-heading h1{margin:0;color:#06284f;font-size:clamp(23px,3vw,30px)}
    .jc-heading p{margin:7px 0 0;color:#63768b;font-size:14px;line-height:1.6}
    .jc-date{background:#eaf2fc;color:#164c96;border:1px solid #cfe0f5;border-radius:12px;padding:10px 14px;font-size:13px;font-weight:750}
    .jc-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px}
    .jc-stat,.jc-panel{background:#fff;border:1px solid #dfe7ee;border-radius:16px;box-shadow:0 5px 22px rgba(6,40,79,.045)}
    .jc-stat{padding:20px;min-width:0}
    .jc-stat-label{color:#63768b;font-size:12px;font-weight:750;line-height:1.5}
    .jc-stat-value{display:block;color:#06284f;font-size:32px;font-weight:850;margin-top:10px;line-height:1.1}
    .jc-stat-note{display:block;color:#8493a3;font-size:11px;margin-top:9px}
    .jc-stat.warning .jc-stat-value{color:#aa6a12}
    .jc-stat.danger .jc-stat-value{color:#b33c3c}
    .jc-stat.success .jc-stat-value{color:#14814a}
    .jc-grid{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(270px,1fr);gap:18px}
    .jc-panel{padding:21px;min-width:0}
    .jc-panel h2{font-size:17px;color:#06284f;margin:0 0 14px}
    .jc-panel-sub{color:#708194;font-size:12px;margin:-7px 0 17px}
    .jc-table-wrap{overflow-x:auto}
    .jc-table{width:100%;border-collapse:collapse;font-size:12px;text-align:left}
    .jc-table th{color:#6a7c8e;font-weight:800;background:#f6f9fc;padding:12px 10px;white-space:nowrap}
    .jc-table td{padding:13px 10px;border-bottom:1px solid #edf1f5;vertical-align:middle}
    .jc-table tr:last-child td{border-bottom:0}
    .jc-strong{font-weight:800;color:#183c62}
    .jc-muted{color:#7b8c9d}
    .jc-tag{display:inline-block;border-radius:999px;padding:5px 9px;font-size:11px;font-weight:750;background:#fff5e4;color:#9b6210;white-space:nowrap}
    .jc-tag.ok{background:#e9f8ee;color:#167c46}
    .jc-tag.neutral{background:#edf3f9;color:#4e6c89}
    .jc-empty{padding:20px 12px;text-align:center;color:#718397;background:#f8fafc;border:1px dashed #dbe5ee;border-radius:11px;font-size:13px;line-height:1.6}
    .jc-agency{padding:13px 0;border-bottom:1px solid #edf1f5}
    .jc-agency:last-child{border-bottom:0}
    .jc-agency strong{display:block;color:#183c62;font-size:13px}
    .jc-agency small{display:block;color:#708194;margin-top:5px;line-height:1.5}
    .jc-actions{display:flex;gap:10px;flex-wrap:wrap}
    .jc-action{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:10px;background:#164c96;color:white;font-size:12px;font-weight:750;text-decoration:none}
    .jc-action.secondary{background:#eaf2fc;color:#164c96}
    .jc-action:hover{filter:brightness(.95)}
    @media(max-width:1250px){.jc-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:900px){.jc-grid{grid-template-columns:1fr}}
    @media(max-width:540px){.jc-stats{grid-template-columns:1fr}.jc-panel{padding:16px}}
</style>
@endpush

@section('content')
<div class="jc-dashboard">
    <div class="jc-heading">
        <div>
            <h1>Supervisión de Caja</h1>
            <p>Resumen operativo de las agencias bajo su responsabilidad.</p>
        </div>
        <span class="jc-date">{{ \Illuminate\Support\Carbon::parse($fecha)->format('d/m/Y') }}</span>
    </div>

    @if($asignaciones->isEmpty())
        <div class="alert-message warning">No tiene asignaciones vigentes a una agencia. Consulte con Administración para verificar su asignación.</div>
    @endif

    <div class="jc-stats">
        <div class="jc-stat">
            <span class="jc-stat-label">Arqueos del día</span>
            <strong class="jc-stat-value">{{ $totalArqueos }}</strong>
            <span class="jc-stat-note">Sin incluir arqueos anulados</span>
        </div>

        <div class="jc-stat warning">
            <span class="jc-stat-label">Pendientes de certificación</span>
            <strong class="jc-stat-value">{{ $pendientesCertificacion }}</strong>
            <span class="jc-stat-note">Firmados por receptor, sin certificar</span>
        </div>

        <div class="jc-stat success">
            <span class="jc-stat-label">Arqueos certificados</span>
            <strong class="jc-stat-value">{{ $certificados }}</strong>
            <span class="jc-stat-note">Certificados en la fecha operativa</span>
        </div>

        <div class="jc-stat danger">
            <span class="jc-stat-label">Arqueos con diferencia</span>
            <strong class="jc-stat-value">{{ $conDiferencia }}</strong>
            <span class="jc-stat-note">Faltante o sobrante distinto de cero</span>
        </div>

        <div class="jc-stat">
            <span class="jc-stat-label">Jornadas programadas</span>
            <strong class="jc-stat-value">{{ $totalJornadas }}</strong>
            <span class="jc-stat-note">Registros de jornada del día</span>
        </div>

        <div class="jc-stat">
            <span class="jc-stat-label">Receptores programados</span>
            <strong class="jc-stat-value">{{ $receptoresProgramados }}</strong>
            <span class="jc-stat-note">Usuarios distintos con jornada hoy</span>
        </div>
    </div>

    <div class="jc-actions">
        @if(\Illuminate\Support\Facades\Route::has('jefecaja.certificaciones.index'))
            <a class="jc-action" href="{{ route('jefecaja.certificaciones.index') }}">
                Revisar certificaciones
            </a>
        @endif

        @if(\Illuminate\Support\Facades\Route::has('jefecaja.sorpresivos.index'))
            <a class="jc-action secondary" href="{{ route('jefecaja.sorpresivos.index') }}">
                Arqueos sorpresivos
            </a>
        @endif

        @if(\Illuminate\Support\Facades\Route::has('jefecaja.cumplimiento-agencias.index'))
            <a class="jc-action secondary" href="{{ route('jefecaja.cumplimiento-agencias.index') }}">
                Ver cumplimiento
            </a>
        @endif
    </div>

    <div class="jc-grid">
        <section class="jc-panel">
            <h2>Arqueos pendientes de certificación</h2>
            <p class="jc-panel-sub">Últimos ocho arqueos firmados que requieren atención.</p>

            @if($arqueosPendientes->isEmpty())
                <div class="jc-empty">
                    No hay arqueos pendientes de certificación para hoy.
                </div>
            @else
                <div class="jc-table-wrap">
                    <table class="jc-table">
                        <thead>
                            <tr>
                                <th>Arqueo</th>
                                <th>Agencia</th>
                                <th>Receptor</th>
                                <th>Total arqueado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($arqueosPendientes as $arqueo)
                                <tr>
                                    <td class="jc-strong">
                                        {{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}
                                    </td>
                                    <td>
                                        {{ $arqueo->agencia_nombre_historico ?: 'Agencia #'.$arqueo->agencia_id }}
                                    </td>
                                    <td>
                                        {{ $arqueo->receptor_nombre_historico ?: ($arqueo->receptor?->nombre_completo ?? 'No disponible') }}
                                    </td>
                                    <td>
                                        Q {{ number_format((float) $arqueo->total_arqueado, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="jc-panel">
            <h2>Agencias asignadas</h2>
            <p class="jc-panel-sub">Asignaciones vigentes del usuario.</p>

            @forelse($asignaciones as $asignacion)
                <div class="jc-agency">
                    <strong>
                        {{ $asignacion->agencia?->nombre ?? 'Agencia #'.$asignacion->agencia_id }}
                    </strong>
                    <small>
                        {{ str_replace('_', ' ', $asignacion->cargo) }} · {{ $asignacion->tipo }}
                    </small>
                    <small>
                        Desde {{ $asignacion->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha' }}
                        · Hasta {{ $asignacion->fecha_fin?->format('d/m/Y') ?? 'Sin vencimiento' }}
                    </small>
                </div>
            @empty
                <div class="jc-empty">
                    No existen agencias asignadas actualmente.
                </div>
            @endforelse
        </section>
    </div>

    <section class="jc-panel">
        <h2>Últimos arqueos del día</h2>
        <p class="jc-panel-sub">Actividad registrada en sus agencias.</p>

        @if($ultimosArqueos->isEmpty())
            <div class="jc-empty">
                Todavía no se han registrado arqueos en la fecha operativa actual.
            </div>
        @else
            <div class="jc-table-wrap">
                <table class="jc-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Agencia</th>
                            <th>Receptor</th>
                            <th>Tipo</th>
                            <th>Situación</th>
                            <th>Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimosArqueos as $arqueo)
                            <tr>
                                <td class="jc-strong">
                                    {{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}
                                </td>
                                <td>
                                    {{ $arqueo->agencia_nombre_historico ?: 'Agencia #'.$arqueo->agencia_id }}
                                </td>
                                <td>
                                    {{ $arqueo->receptor_nombre_historico ?: ($arqueo->receptor?->nombre_completo ?? 'No disponible') }}
                                </td>
                                <td>
                                    {{ str_replace('_', ' ', $arqueo->tipo) }}
                                </td>
                                <td>
                                    @if($arqueo->certificado_at)
                                        <span class="jc-tag ok">Certificado</span>
                                    @elseif($arqueo->firmado_receptor_at)
                                        <span class="jc-tag">Por certificar</span>
                                    @else
                                        <span class="jc-tag neutral">En proceso</span>
                                    @endif
                                </td>
                                <td>
                                    Q {{ number_format((float) $arqueo->diferencia, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
