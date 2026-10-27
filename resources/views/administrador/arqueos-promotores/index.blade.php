@extends('layouts.administrador')

@section('title', 'Arqueos de Promotores')
@section('module-title', 'Arqueos de Promotores')

@push('styles')
<style>
    .promotor-arqueos-page{max-width:1550px;margin:0 auto}
    .module-heading{display:flex;align-items:center;gap:15px}
    .module-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .module-icon svg,.summary-icon svg,.panel-icon svg,.empty-icon svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .module-icon svg{width:26px;height:26px}
    .summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}
    .summary-card{min-height:106px;display:flex;align-items:center;gap:14px;padding:17px;border:1px solid #dfe7ee;border-radius:16px;background:#fff;box-shadow:0 7px 20px rgba(28,64,92,.045)}
    .summary-icon{width:44px;height:44px;flex:0 0 44px;display:flex;align-items:center;justify-content:center;border-radius:13px;color:var(--azul-principal);background:#edf4fb}
    .summary-card.certified .summary-icon{color:var(--verde-oscuro);background:#eaf8f1}
    .summary-card.pending .summary-icon{color:#9a7100;background:#fff7df}
    .summary-card.cancelled .summary-icon{color:#b13b3b;background:#fff0f0}
    .summary-label{display:block;margin-bottom:4px;color:var(--texto-secundario);font-size:12px;font-weight:700}
    .summary-value{display:block;color:var(--azul-profundo);font-size:25px;font-weight:800;line-height:1.05}
    .table-panel{overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(28,64,92,.05)}
    .table-panel-header{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:17px 19px;border-bottom:1px solid #e5edf2;background:#fbfcfd}
    .panel-title{display:flex;align-items:center;gap:11px}
    .panel-icon{width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:var(--azul-principal);background:#edf4fb}
    .panel-title h3{margin:0;color:var(--azul-profundo);font-size:15px}
    .panel-title p{margin:2px 0 0;color:var(--texto-secundario);font-size:12px}
    .records-count{padding:6px 10px;border:1px solid #dce6ed;border-radius:999px;background:#fff;color:#5c7487;font-size:11px;font-weight:800;white-space:nowrap}
    .table-responsive{overflow-x:auto}
    .data-table{width:100%;min-width:1050px;border-collapse:collapse}
    .data-table th{padding:12px 14px;border-bottom:1px solid #dfe7ee;background:#f7f9fb;color:#60788b;font-size:11px;font-weight:800;letter-spacing:.035em;text-align:left;text-transform:uppercase;white-space:nowrap}
    .data-table td{padding:13px 14px;border-bottom:1px solid #edf1f4;color:var(--texto);font-size:12.5px;vertical-align:middle}
    .data-table tbody tr:hover{background:#fafcfd}.data-table tbody tr:last-child td{border-bottom:0}
    .document-number{color:var(--azul-principal);font-weight:800;white-space:nowrap}
    .person-name{display:block;color:var(--texto);font-weight:700;min-width:160px}
    .person-user{display:block;margin-top:2px;color:var(--texto-secundario);font-size:10.5px}
    .agent-code{display:block;margin-bottom:2px;color:var(--azul-principal);font-size:11px;font-weight:800}
    .agent-business{display:block;color:var(--texto);font-weight:700;min-width:190px}
    .status-badge{display:inline-flex;align-items:center;gap:6px;min-height:27px;padding:0 9px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.02em;text-transform:uppercase;white-space:nowrap}
    .status-badge:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
    .status-certified{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8}
    .status-pending{color:#936b00;background:#fff8e3;border:1px solid #f0dfa3}
    .status-cancelled{color:#b13b3b;background:#fff0f0;border:1px solid #f0cccc}
    .status-neutral{color:#526d82;background:#f1f5f8;border:1px solid #dce5eb}
    .difference{color:var(--azul-profundo);font-weight:800;white-space:nowrap}
    .actions-cell{white-space:nowrap}
    .table-actions{display:inline-flex;align-items:center;gap:8px}
    .icon-button{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border:1px solid #d5dfe5;border-radius:9px;background:#fff;color:#31536e;text-decoration:none;transition:.2s ease}
    .icon-button:hover{transform:translateY(-1px);border-color:#9db6c9;background:#f4f8fb;color:#164c96;box-shadow:0 6px 14px rgba(24,66,99,.10)}
    .icon-button.print{border-color:#c8d7e3;background:#edf5fb;color:#164c96}
    .icon-button svg{width:17px;height:17px;stroke:currentColor;stroke-width:1.9;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .empty-state{padding:48px 20px!important;text-align:center}
    .empty-icon{width:52px;height:52px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;border-radius:15px;color:#7890a2;background:#f1f5f8}
    .empty-state strong{display:block;margin-bottom:4px;color:var(--azul-profundo);font-size:14px}
    .empty-state span{color:var(--texto-secundario);font-size:12px}
    .pagination-wrapper{padding:16px 18px;border-top:1px solid #edf1f4}
    @media(max-width:1000px){.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:620px){.summary-grid{grid-template-columns:1fr}.table-panel-header{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
<div class="promotor-arqueos-page">
    <div class="page-header">
        <div class="page-title module-heading">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 5h16v14H4z"></path>
                    <path d="M8 9h8M8 13h5"></path>
                    <path d="M17 16h.01"></path>
                </svg>
            </div>
            <div>
                <h2>Arqueos de Promotores</h2>
                <p>Consulta de arqueos realizados durante visitas de Promotores a Agentes MICOOPE.</p>
            </div>
        </div>
    </div>

    <div class="summary-grid">
        <article class="summary-card">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 7h8M8 11h8M8 15h5"></path></svg>
            </div>
            <div><span class="summary-label">Total de Arqueos</span><strong class="summary-value">{{ $resumen->total }}</strong></div>
        </article>

        <article class="summary-card certified">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m8 12 2.5 2.5L16 9"></path></svg>
            </div>
            <div><span class="summary-label">Certificados</span><strong class="summary-value">{{ $resumen->certificados }}</strong></div>
        </article>

        <article class="summary-card pending">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
            </div>
            <div><span class="summary-label">Pendientes</span><strong class="summary-value">{{ $resumen->pendientes }}</strong></div>
        </article>

        <article class="summary-card cancelled">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m9 9 6 6M15 9l-6 6"></path></svg>
            </div>
            <div><span class="summary-label">Anulados</span><strong class="summary-value">{{ $resumen->anulados }}</strong></div>
        </article>
    </div>

    <section class="table-panel">
        <div class="table-panel-header">
            <div class="panel-title">
                <div class="panel-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M4 10h16M9 5v14"></path></svg>
                </div>
                <div>
                    <h3>Registro de Arqueos</h3>
                    <p>Historial de visitas y arqueos realizados por Promotores.</p>
                </div>
            </div>
            <span class="records-count">{{ $arqueos->total() }} {{ $arqueos->total() === 1 ? 'registro' : 'registros' }}</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Promotor</th>
                        <th>Agente</th>
                        <th>Estado</th>
                        <th>Diferencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arqueos as $a)
                        @php
                            $p = trim(($a->promotor_nombres ?? '') . ' ' . ($a->promotor_apellidos ?? ''));

                            $estadoClase = match ($a->estado) {
                                'CERTIFICADO' => 'status-certified',
                                'PENDIENTE_CERTIFICACION' => 'status-pending',
                                'ANULADO' => 'status-cancelled',
                                default => 'status-neutral',
                            };
                        @endphp
                        <tr>
                            <td><span class="document-number">{{ $a->numero_arqueo }}</span></td>

                            <td>{{ \Carbon\Carbon::parse($a->fecha_arqueo)->format('d/m/Y') }}</td>

                            <td>
                                <span class="person-name">
                                    {{ $p !== '' ? $p : ($a->promotor_usuario ?? '—') }}
                                </span>
                                @if($p !== '' && !empty($a->promotor_usuario))
                                    <span class="person-user">{{ $a->promotor_usuario }}</span>
                                @endif
                            </td>

                            <td>
                                <span class="agent-code">{{ $a->codigo_agente }}</span>
                                <span class="agent-business">{{ $a->nombre_negocio }}</span>
                            </td>

                            <td>
                                <span class="status-badge {{ $estadoClase }}">
                                    {{ str_replace('_', ' ', $a->estado) }}
                                </span>
                            </td>

                            <td>
                                <span class="difference">
                                    Q {{ number_format(abs((float) $a->diferencia), 2) }}
                                </span>
                            </td>

                            <td class="actions-cell">
                                <div class="table-actions">
                                    <a
                                        href="{{ route('administrador.arqueos-promotores.show', $a->id) }}"
                                        class="icon-button"
                                        title="Ver detalle"
                                        aria-label="Ver detalle"
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="2.8"></circle>
                                        </svg>
                                    </a>

                                    <a
                                        target="_blank"
                                        href="{{ route('administrador.arqueos-promotores.imprimir', $a->id) }}"
                                        class="icon-button print"
                                        title="Imprimir PDF"
                                        aria-label="Imprimir en PDF"
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path d="M6 9V3h12v6"></path>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                            <rect x="6" y="14" width="12" height="7"></rect>
                                            <path d="M18 12h.01"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <div class="empty-icon">
                                    <svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 8h8M8 12h8M8 16h5"></path></svg>
                                </div>
                                <strong>No hay arqueos de Promotores</strong>
                                <span>Los arqueos realizados durante visitas de Promotores aparecerán aquí.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($arqueos->hasPages())
            <div class="pagination-wrapper">
                {{ $arqueos->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
