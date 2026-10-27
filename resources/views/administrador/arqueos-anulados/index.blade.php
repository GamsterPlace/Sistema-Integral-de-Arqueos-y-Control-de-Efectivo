@extends('layouts.administrador')

@section('title', 'Arqueos Anulados')
@section('module-title', 'Arqueos Anulados')

@push('styles')
<style>
    .cancelled-page{max-width:1550px;margin:0 auto}
    .module-heading{display:flex;align-items:center;gap:15px}
    .module-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .module-icon svg,.summary-icon svg,.panel-icon svg,.empty-icon svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .module-icon svg{width:26px;height:26px}
    .summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}
    .summary-card{min-height:106px;display:flex;align-items:center;gap:14px;padding:17px;border:1px solid #dfe7ee;border-radius:16px;background:#fff;box-shadow:0 7px 20px rgba(28,64,92,.045)}
    .summary-icon{width:44px;height:44px;flex:0 0 44px;display:flex;align-items:center;justify-content:center;border-radius:13px;color:#b13b3b;background:#fff0f0}
    .summary-card.agent .summary-icon{color:var(--azul-principal);background:#edf4fb}
    .summary-card.promoter .summary-icon{color:var(--verde-oscuro);background:#eaf8f1}
    .summary-card.late .summary-icon{color:#946b00;background:#fff7df}
    .summary-label{display:block;margin-bottom:4px;color:var(--texto-secundario);font-size:12px;font-weight:700}
    .summary-value{display:block;color:var(--azul-profundo);font-size:25px;font-weight:800;line-height:1.05}
    .notice{display:flex;align-items:flex-start;gap:11px;margin-bottom:18px;padding:13px 15px;border:1px solid #efd4d4;border-radius:13px;background:#fff7f7;color:#7e4545;font-size:12px;line-height:1.5}
    .notice svg{width:19px;height:19px;flex:0 0 19px;margin-top:1px;fill:none;stroke:#b13b3b;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
    .notice strong{color:#9d3030}
    .table-panel{overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(28,64,92,.05)}
    .table-panel-header{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:17px 19px;border-bottom:1px solid #e5edf2;background:#fbfcfd}
    .panel-title{display:flex;align-items:center;gap:11px}
    .panel-icon{width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:#b13b3b;background:#fff0f0}
    .panel-title h3{margin:0;color:var(--azul-profundo);font-size:15px}.panel-title p{margin:2px 0 0;color:var(--texto-secundario);font-size:12px}
    .records-count{padding:6px 10px;border:1px solid #dce6ed;border-radius:999px;background:#fff;color:#5c7487;font-size:11px;font-weight:800;white-space:nowrap}
    .table-responsive{overflow-x:auto}
    .data-table{width:100%;min-width:1050px;border-collapse:collapse}
    .data-table th{padding:12px 14px;border-bottom:1px solid #dfe7ee;background:#f7f9fb;color:#60788b;font-size:11px;font-weight:800;letter-spacing:.035em;text-align:left;text-transform:uppercase;white-space:nowrap}
    .data-table td{padding:13px 14px;border-bottom:1px solid #edf1f4;color:var(--texto);font-size:12.5px;vertical-align:middle}
    .data-table tbody tr:hover{background:#fafcfd}.data-table tbody tr:last-child td{border-bottom:0}
    .document-number{color:var(--azul-principal);font-weight:800;white-space:nowrap}
    .agent-code{display:block;margin-bottom:2px;color:var(--azul-principal);font-size:11px;font-weight:800}.agent-business{display:block;font-weight:700}
    .type-badge{display:inline-flex;align-items:center;min-height:27px;padding:0 9px;border:1px solid #dce5eb;border-radius:999px;background:#f1f5f8;color:#526d82;font-size:10px;font-weight:800;text-transform:uppercase;white-space:nowrap}
    .type-agent{border-color:#cfe0ee;background:#edf5fb;color:#164c96}.type-promoter{border-color:#c7ecd8;background:#e9f8f0;color:#08783d}.type-audit{border-color:#ded7ee;background:#f5f1fb;color:#65468a}
    .difference{color:var(--azul-profundo);font-weight:800;white-space:nowrap}
    .empty-state{padding:48px 20px!important;text-align:center}.empty-icon{width:52px;height:52px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;border-radius:15px;color:#9a7373;background:#fff2f2}.empty-state strong{display:block;margin-bottom:4px;color:var(--azul-profundo);font-size:14px}.empty-state span{color:var(--texto-secundario);font-size:12px}
    .pagination-wrapper{padding:16px 18px;border-top:1px solid #edf1f4}
    @media(max-width:1000px){.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:620px){.summary-grid{grid-template-columns:1fr}.table-panel-header{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
<div class="cancelled-page">
    <div class="page-header">
        <div class="page-title module-heading">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="m8.5 8.5 7 7M15.5 8.5l-7 7"></path>
                </svg>
            </div>
            <div>
                <h2>Arqueos Anulados</h2>
                <p>Historial institucional de arqueos que han sido anulados.</p>
            </div>
        </div>
    </div>

    <div class="notice">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M12 8v5M12 16.5h.01"></path>
        </svg>
        <div>
            <strong>Registro histórico definitivo.</strong>
            Los arqueos anulados permanecen únicamente para consulta y trazabilidad. No existe rectificación ni reactivación.
        </div>
    </div>

    <div class="summary-grid">
        <article class="summary-card">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m9 9 6 6M15 9l-6 6"></path></svg>
            </div>
            <div><span class="summary-label">Total Anulados</span><strong class="summary-value">{{ $resumen->total }}</strong></div>
        </article>

        <article class="summary-card agent">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3"></circle><path d="M5 21v-2a7 7 0 0 1 14 0v2"></path></svg>
            </div>
            <div><span class="summary-label">Arqueos de Agente</span><strong class="summary-value">{{ $resumen->agente }}</strong></div>
        </article>

        <article class="summary-card promoter">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path></svg>
            </div>
            <div><span class="summary-label">Arqueos de Promotor</span><strong class="summary-value">{{ $resumen->promotor }}</strong></div>
        </article>

        <article class="summary-card late">
            <div class="summary-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
            </div>
            <div><span class="summary-label">Extemporáneos</span><strong class="summary-value">{{ $resumen->extemporaneos }}</strong></div>
        </article>
    </div>

    <section class="table-panel">
        <div class="table-panel-header">
            <div class="panel-title">
                <div class="panel-icon">
                    <svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 8h8M8 12h8M8 16h5"></path></svg>
                </div>
                <div>
                    <h3>Historial de Anulaciones</h3>
                    <p>Arqueos conservados para consulta y control administrativo.</p>
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
                        <th>Tipo</th>
                        <th>Agente</th>
                        <th>Región</th>
                        <th>Ruta</th>
                        <th>Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arqueos as $a)
                        @php
                            $tipoClase = match ($a->tipo) {
                                'DIARIO_AGENTE' => 'type-agent',
                                'VISITA_PROMOTOR' => 'type-promoter',
                                'VISITA_AUDITORIA' => 'type-audit',
                                default => '',
                            };

                            $tipoTexto = match ($a->tipo) {
                                'DIARIO_AGENTE' => 'Agente',
                                'VISITA_PROMOTOR' => 'Promotor',
                                'VISITA_AUDITORIA' => 'Auditoría',
                                default => str_replace('_', ' ', $a->tipo),
                            };
                        @endphp
                        <tr>
                            <td><span class="document-number">{{ $a->numero_arqueo }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($a->fecha_arqueo)->format('d/m/Y') }}</td>
                            <td><span class="type-badge {{ $tipoClase }}">{{ $tipoTexto }}</span></td>
                            <td>
                                <span class="agent-code">{{ $a->codigo_agente }}</span>
                                <span class="agent-business">{{ $a->nombre_negocio }}</span>
                            </td>
                            <td>{{ $a->region_nombre ?? '—' }}</td>
                            <td>{{ $a->ruta_nombre ?? '—' }}</td>
                            <td><span class="difference">Q {{ number_format(abs((float) $a->diferencia), 2) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <div class="empty-icon">
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m9 9 6 6M15 9l-6 6"></path></svg>
                                </div>
                                <strong>No hay arqueos anulados</strong>
                                <span>Actualmente no existen registros de arqueos anulados.</span>
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
