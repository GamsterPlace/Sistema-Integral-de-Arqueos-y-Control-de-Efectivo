@extends('layouts.administrador')

@section('title', 'Detalle Auditoría de Agente')
@section('module-title', 'Auditoría de Agentes')

@push('styles')
<style>
    .audit-detail-page{max-width:1380px;margin:0 auto}
    .detail-header{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:22px}
    .detail-heading{display:flex;align-items:center;gap:15px;min-width:0}
    .detail-heading-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .detail-heading-icon svg,.btn-detail svg,.panel-icon svg,.money-icon svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .detail-heading-icon svg{width:26px;height:26px}
    .detail-heading h2{margin:0 0 4px;color:var(--azul-profundo);font-size:25px;line-height:1.15}
    .detail-heading p{margin:0;color:var(--texto-secundario);font-size:14px}
    .header-actions{display:flex;align-items:center;gap:10px;flex-shrink:0}
    .btn-detail{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 15px;border:1px solid #d6e1e9;border-radius:11px;background:#fff;color:#31536e;font-size:13px;font-weight:700;text-decoration:none;transition:.2s ease}
    .btn-detail:hover{transform:translateY(-1px);border-color:#a9becd;color:var(--azul-principal);box-shadow:0 7px 18px rgba(24,66,99,.09)}
    .btn-detail.primary{border-color:transparent;color:#fff;background:linear-gradient(135deg,var(--azul-principal),var(--azul-secundario));box-shadow:0 8px 18px rgba(22,76,150,.16)}
    .btn-detail.primary:hover{color:#fff;box-shadow:0 10px 22px rgba(22,76,150,.23)}
    .btn-detail svg{width:17px;height:17px}
    .document-banner{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:18px;padding:20px 22px;border:1px solid #dce6ed;border-radius:18px;background:linear-gradient(105deg,rgba(22,76,150,.055),rgba(0,166,81,.025)),#fff;box-shadow:0 8px 24px rgba(28,64,92,.055)}
    .document-banner:before{content:"";position:absolute;top:0;left:0;width:5px;height:100%;background:var(--verde-principal)}
    .document-eyebrow{margin-bottom:5px;color:var(--texto-secundario);font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .document-title{margin:0 0 6px;color:var(--azul-profundo);font-size:20px;line-height:1.25}
    .document-meta{display:flex;flex-wrap:wrap;gap:7px 16px;color:var(--texto-secundario);font-size:13px}
    .document-meta strong{color:var(--texto)}
    .status-badge,.result-badge,.ordinary-badge{display:inline-flex;align-items:center;gap:7px;min-height:30px;padding:0 10px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.02em;text-transform:uppercase;white-space:nowrap}
    .status-badge:before,.result-badge:before,.ordinary-badge:before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}
    .status-certified{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8}
    .status-pending{color:#936b00;background:#fff8e3;border:1px solid #f0dfa3}
    .status-cancelled{color:#b13b3b;background:#fff0f0;border:1px solid #f0cccc}
    .status-draft,.status-neutral{color:#526d82;background:#f1f5f8;border:1px solid #dce5eb}
    .ordinary-yes{color:#936b00;background:#fff8e3;border:1px solid #f0dfa3}
    .ordinary-no{color:#526d82;background:#f1f5f8;border:1px solid #dce5eb}
    .top-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}
    .detail-panel{overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(28,64,92,.05)}
    .panel-heading{display:flex;align-items:center;gap:11px;padding:17px 19px;border-bottom:1px solid #e6edf2;background:#fbfcfd}
    .panel-icon{width:37px;height:37px;flex:0 0 37px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:var(--azul-principal);background:#edf4fb}
    .panel-icon.agent{color:var(--verde-oscuro);background:#eaf8f1}
    .panel-icon.result{color:#7a5a00;background:#fff7df}
    .panel-icon svg{width:19px;height:19px}
    .panel-heading h3{margin:0;color:var(--azul-profundo);font-size:15px}
    .panel-heading p{margin:2px 0 0;color:var(--texto-secundario);font-size:12px}
    .info-list{padding:5px 19px 8px}
    .info-row{display:grid;grid-template-columns:155px minmax(0,1fr);gap:15px;align-items:center;min-height:50px;border-bottom:1px solid #edf1f4}
    .info-row:last-child{border-bottom:0}
    .info-label{color:var(--texto-secundario);font-size:12px;font-weight:700}
    .info-value{color:var(--texto);font-size:13px;font-weight:700;text-align:right;overflow-wrap:anywhere}
    .info-value.primary{color:var(--azul-principal)}
    .result-panel{margin-bottom:18px}
    .money-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;padding:18px 18px 12px}
    .money-card{position:relative;overflow:hidden;padding:15px;border:1px solid #e2eaf0;border-radius:14px;background:var(--fondo-suave)}
    .money-card .money-icon{width:34px;height:34px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;border-radius:9px;color:var(--azul-principal);background:#e8f1f8}
    .money-icon svg{width:17px;height:17px}
    .money-label{display:block;margin-bottom:5px;color:var(--texto-secundario);font-size:10px;font-weight:800;letter-spacing:.03em;text-transform:uppercase}
    .money-value{display:block;color:var(--azul-profundo);font-size:18px;font-weight:800;white-space:nowrap}
    .result-row{display:grid;grid-template-columns:260px minmax(0,1fr);gap:12px;padding:0 18px 18px}
    .difference-box,.text-box{padding:15px 16px;border:1px solid #dce6ed;border-radius:14px;background:#fff}
    .difference-box{background:#f2f7fb}
    .difference-label,.text-title{display:block;margin-bottom:6px;color:#60788b;font-size:10px;font-weight:800;letter-spacing:.035em;text-transform:uppercase}
    .difference-value{display:block;margin-bottom:8px;color:var(--azul-principal);font-size:21px;font-weight:800}
    .result-shortage{color:#b13b3b;background:#fff0f0;border:1px solid #f0cccc}
    .result-surplus{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8}
    .result-exact{color:#526d82;background:#f1f5f8;border:1px solid #dce5eb}
    .text-box p{margin:0;color:var(--texto);font-size:13px;line-height:1.6;white-space:pre-line}
    .notes-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:0 18px 18px}
    .denomination-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    .denomination-body{padding:7px 18px 12px}
    .denomination-row{display:grid;grid-template-columns:1fr 90px 130px;gap:10px;align-items:center;min-height:43px;border-bottom:1px solid #edf1f4}
    .denomination-row:last-child{border-bottom:0}
    .denomination-name{color:var(--texto);font-size:12.5px;font-weight:700}
    .denomination-qty{color:var(--texto-secundario);font-size:12px;text-align:center}
    .denomination-subtotal{color:var(--azul-profundo);font-size:12.5px;font-weight:800;text-align:right;white-space:nowrap}
    .empty-detail{padding:24px 18px;color:var(--texto-secundario);font-size:12px;text-align:center}
    @media(max-width:1050px){.money-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.denomination-grid{grid-template-columns:1fr}}
    @media(max-width:850px){.top-grid{grid-template-columns:1fr}.result-row,.notes-grid{grid-template-columns:1fr}}
    @media(max-width:700px){.detail-header,.document-banner{align-items:flex-start;flex-direction:column}.header-actions{width:100%}.btn-detail{flex:1}.info-row{grid-template-columns:1fr;gap:4px;padding:12px 0}.info-value{text-align:left}}
    @media(max-width:560px){.money-grid{grid-template-columns:1fr}.denomination-row{grid-template-columns:1fr 70px 110px}}
</style>
@endpush

@section('content')
@php
    $nombreAuditor = trim(
        ($auditor->nombres ?? '') . ' ' . ($auditor->apellidos ?? '')
    );

    $nombreAuditor = $nombreAuditor !== ''
        ? $nombreAuditor
        : ($auditor->usuario ?? '—');

    $diferencia = (float) $arqueo->diferencia;

    $estadoClase = match ($arqueo->estado) {
        'CERTIFICADO' => 'status-certified',
        'PENDIENTE_CERTIFICACION' => 'status-pending',
        'ANULADO' => 'status-cancelled',
        'BORRADOR' => 'status-draft',
        default => 'status-neutral',
    };

    $resultadoTexto = $diferencia < 0
        ? 'Faltante'
        : ($diferencia > 0 ? 'Sobrante' : 'Exacto');

    $resultadoClase = $diferencia < 0
        ? 'result-shortage'
        : ($diferencia > 0 ? 'result-surplus' : 'result-exact');

    $fechaArqueo = $arqueo->fecha_arqueo
        ? \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y')
        : '—';

    $horaInicio = $arqueo->hora_inicio
        ? \Carbon\Carbon::parse($arqueo->hora_inicio)->format('H:i')
        : '—';

    $horaFin = $arqueo->hora_fin
        ? \Carbon\Carbon::parse($arqueo->hora_fin)->format('H:i')
        : '—';
@endphp

<div class="audit-detail-page">
    <div class="detail-header">
        <div class="detail-heading">
            <div class="detail-heading-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
            </div>
            <div>
                <h2>{{ $arqueo->numero_arqueo }}</h2>
                <p>Detalle completo del arqueo realizado por el departamento de Auditoría.</p>
            </div>
        </div>

        <div class="header-actions">
            <a href="{{ route('administrador.auditoria-agentes.index') }}" class="btn-detail">
                <svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"></path></svg>
                Regresar
            </a>

            <a
                target="_blank"
                href="{{ route('administrador.auditoria-agentes.imprimir', $arqueo->id) }}"
                class="btn-detail primary"
            >
                <svg viewBox="0 0 24 24">
                    <path d="M6 9V3h12v6"></path>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="7"></rect>
                    <path d="M18 12h.01"></path>
                </svg>
                Imprimir PDF
            </a>
        </div>
    </div>

    <section class="document-banner">
        <div>
            <div class="document-eyebrow">Auditoría de Agente MICOOPE</div>
            <h3 class="document-title">{{ $arqueo->nombre_negocio_historico ?: 'Agente MICOOPE' }}</h3>
            <div class="document-meta">
                <span>Auditor: <strong>{{ $nombreAuditor }}</strong></span>
                <span>Agente: <strong>{{ $arqueo->codigo_agente_historico ?: '—' }}</strong></span>
                <span>Fecha: <strong>{{ $fechaArqueo }}</strong></span>
            </div>
        </div>

        <span class="status-badge {{ $estadoClase }}">
            {{ str_replace('_', ' ', $arqueo->estado) }}
        </span>
    </section>

    <div class="top-grid">
        <section class="detail-panel">
            <div class="panel-heading">
                <div class="panel-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path>
                        <path d="M9 11h6M9 14h4"></path>
                    </svg>
                </div>
                <div>
                    <h3>Información de Auditoría</h3>
                    <p>Datos del auditor y ejecución del arqueo.</p>
                </div>
            </div>

            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">Auditor</span>
                    <span class="info-value primary">{{ $nombreAuditor }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Fecha</span>
                    <span class="info-value">{{ $fechaArqueo }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Hora de inicio</span>
                    <span class="info-value">{{ $horaInicio }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Hora de finalización</span>
                    <span class="info-value">{{ $horaFin }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Estado</span>
                    <span class="info-value">
                        <span class="status-badge {{ $estadoClase }}">
                            {{ str_replace('_', ' ', $arqueo->estado) }}
                        </span>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Extemporáneo</span>
                    <span class="info-value">
                        <span class="ordinary-badge {{ $arqueo->fuera_fecha_ordinaria ? 'ordinary-yes' : 'ordinary-no' }}">
                            {{ $arqueo->fuera_fecha_ordinaria ? 'Sí' : 'No' }}
                        </span>
                    </span>
                </div>
            </div>
        </section>

        <section class="detail-panel">
            <div class="panel-heading">
                <div class="panel-icon agent">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 10h16v10H4z"></path>
                        <path d="M3 10 5 4h14l2 6"></path>
                        <path d="M8 20v-5h8v5"></path>
                    </svg>
                </div>
                <div>
                    <h3>Agente Auditado</h3>
                    <p>Información histórica registrada al momento del arqueo.</p>
                </div>
            </div>

            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">Código</span>
                    <span class="info-value primary">{{ $arqueo->codigo_agente_historico ?: '—' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Negocio</span>
                    <span class="info-value">{{ $arqueo->nombre_negocio_historico ?: '—' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Propietario</span>
                    <span class="info-value">{{ $arqueo->nombre_propietario_historico ?: '—' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Dirección</span>
                    <span class="info-value">{{ $arqueo->direccion_historica ?: '—' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Región</span>
                    <span class="info-value">{{ $arqueo->region_historica ?: '—' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Ruta</span>
                    <span class="info-value">{{ $arqueo->ruta_historica ?: '—' }}</span>
                </div>
            </div>
        </section>
    </div>

    <section class="detail-panel result-panel">
        <div class="panel-heading">
            <div class="panel-icon result">
                <svg viewBox="0 0 24 24">
                    <path d="M4 19V5"></path>
                    <path d="M4 19h16"></path>
                    <path d="m7 15 4-4 3 2 5-6"></path>
                </svg>
            </div>
            <div>
                <h3>Resultado del Arqueo</h3>
                <p>Resumen financiero y resultado obtenido durante la auditoría.</p>
            </div>
        </div>

        <div class="money-grid">
            <div class="money-card">
                <div class="money-icon"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"></rect><circle cx="12" cy="12" r="2.5"></circle></svg></div>
                <span class="money-label">Total Billetes</span>
                <span class="money-value">Q {{ number_format((float) $arqueo->total_billetes, 2) }}</span>
            </div>

            <div class="money-card">
                <div class="money-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="12" r="5"></circle><circle cx="15" cy="12" r="5"></circle></svg></div>
                <span class="money-label">Total Monedas</span>
                <span class="money-value">Q {{ number_format((float) $arqueo->total_monedas, 2) }}</span>
            </div>

            <div class="money-card">
                <div class="money-icon"><svg viewBox="0 0 24 24"><path d="M4 7h16v12H4z"></path><path d="M7 4h10v3"></path><path d="M8 13h8"></path></svg></div>
                <span class="money-label">Total Arqueado</span>
                <span class="money-value">Q {{ number_format((float) $arqueo->total_arqueado, 2) }}</span>
            </div>

            <div class="money-card">
                <div class="money-icon"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path></svg></div>
                <span class="money-label">Saldo Sistema</span>
                <span class="money-value">Q {{ number_format((float) $arqueo->saldo_sistema, 2) }}</span>
            </div>
        </div>

        <div class="result-row">
            <div class="difference-box">
                <span class="difference-label">Diferencia</span>
                <span class="difference-value">Q {{ number_format(abs($diferencia), 2) }}</span>
                <span class="result-badge {{ $resultadoClase }}">{{ $resultadoTexto }}</span>
            </div>

            <div class="text-box">
                <span class="text-title">Certificación</span>
                <p>{{ $arqueo->certificacion ?: 'Sin certificación registrada.' }}</p>
            </div>
        </div>

        <div class="notes-grid">
            <div class="text-box">
                <span class="text-title">Observaciones</span>
                <p>{{ $arqueo->observaciones ?: 'Sin observaciones registradas.' }}</p>
            </div>

            <div class="text-box">
                <span class="text-title">Referencia</span>
                <p>Arqueo realizado por el departamento de Auditoría sobre el Agente MICOOPE indicado en este registro.</p>
            </div>
        </div>
    </section>

    <div class="denomination-grid">
        <section class="detail-panel">
            <div class="panel-heading">
                <div class="panel-icon">
                    <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"></rect><circle cx="12" cy="12" r="2.5"></circle></svg>
                </div>
                <div>
                    <h3>Billetes</h3>
                    <p>Detalle de denominaciones registradas.</p>
                </div>
            </div>

            @forelse($billetes as $detalle)
                @if($loop->first)
                    <div class="denomination-body">
                @endif

                <div class="denomination-row">
                    <span class="denomination-name">Q {{ number_format((float) $detalle->denominacion, 2) }}</span>
                    <span class="denomination-qty">× {{ $detalle->cantidad }}</span>
                    <span class="denomination-subtotal">Q {{ number_format((float) $detalle->subtotal, 2) }}</span>
                </div>

                @if($loop->last)
                    </div>
                @endif
            @empty
                <div class="empty-detail">Sin detalle de billetes.</div>
            @endforelse
        </section>

        <section class="detail-panel">
            <div class="panel-heading">
                <div class="panel-icon agent">
                    <svg viewBox="0 0 24 24"><circle cx="9" cy="12" r="5"></circle><circle cx="15" cy="12" r="5"></circle></svg>
                </div>
                <div>
                    <h3>Monedas</h3>
                    <p>Detalle de denominaciones registradas.</p>
                </div>
            </div>

            @forelse($monedas as $detalle)
                @if($loop->first)
                    <div class="denomination-body">
                @endif

                <div class="denomination-row">
                    <span class="denomination-name">Q {{ number_format((float) $detalle->denominacion, 2) }}</span>
                    <span class="denomination-qty">× {{ $detalle->cantidad }}</span>
                    <span class="denomination-subtotal">Q {{ number_format((float) $detalle->subtotal, 2) }}</span>
                </div>

                @if($loop->last)
                    </div>
                @endif
            @empty
                <div class="empty-detail">Sin detalle de monedas.</div>
            @endforelse
        </section>
    </div>
</div>
@endsection
