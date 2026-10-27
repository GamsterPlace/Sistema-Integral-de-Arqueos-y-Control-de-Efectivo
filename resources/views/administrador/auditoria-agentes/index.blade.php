@extends('layouts.administrador')

@section('title', 'Auditoría de Agentes')
@section('module-title', 'Auditoría de Agentes')

@push('styles')
<style>
    .audit-agents-page{max-width:1550px;margin:0 auto}
    .module-heading{display:flex;align-items:center;gap:15px}
    .module-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .module-icon svg,.summary-icon svg,.panel-icon svg,.filter-icon svg,.empty-icon svg,.action-btn svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .module-icon svg{width:26px;height:26px}
    .summary-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;margin-bottom:20px}
    .summary-card{min-height:105px;display:flex;align-items:center;gap:12px;padding:15px;border:1px solid #dfe7ee;border-radius:16px;background:#fff;box-shadow:0 7px 20px rgba(28,64,92,.045)}
    .summary-icon{width:42px;height:42px;flex:0 0 42px;display:flex;align-items:center;justify-content:center;border-radius:12px;color:var(--azul-principal);background:#edf4fb}
    .summary-icon svg{width:20px;height:20px}
    .summary-card.certified .summary-icon{color:var(--verde-oscuro);background:#eaf8f1}
    .summary-card.pending .summary-icon{color:#986b00;background:#fff7df}
    .summary-card.cancelled .summary-icon{color:#b13b3b;background:#fff0f0}
    .summary-card.shortage .summary-icon{color:#b33a3a;background:#fff1f1}
    .summary-card.surplus .summary-icon{color:#08783d;background:#eaf8f1}
    .summary-label{display:block;margin-bottom:4px;color:var(--texto-secundario);font-size:10px;font-weight:800;letter-spacing:.025em;text-transform:uppercase}
    .summary-value{display:block;color:var(--azul-profundo);font-size:23px;font-weight:800;line-height:1}
    .filter-panel{margin-bottom:20px;overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(28,64,92,.045)}
    .filter-heading{display:flex;align-items:center;gap:11px;padding:15px 18px;border-bottom:1px solid #e6edf2;background:#fbfcfd}
    .filter-icon,.panel-icon{width:37px;height:37px;flex:0 0 37px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:var(--azul-principal);background:#edf4fb}
    .filter-icon svg,.panel-icon svg{width:18px;height:18px}
    .filter-heading h3,.panel-title h3{margin:0;color:var(--azul-profundo);font-size:15px}
    .filter-heading p,.panel-title p{margin:2px 0 0;color:var(--texto-secundario);font-size:12px}
    .filter-body{padding:17px 18px}
    .filter-grid{display:grid;grid-template-columns:1.4fr repeat(3,1fr);gap:11px}
    .filter-grid-secondary{display:grid;grid-template-columns:220px 1fr 1fr;gap:11px;margin-top:11px}
    .field-group label{display:block;margin:0 0 6px;color:#536c7f;font-size:10px;font-weight:800;letter-spacing:.025em;text-transform:uppercase}
    .form-control{width:100%;min-height:42px;padding:0 12px;border:1px solid #ced9e1;border-radius:10px;outline:none;background:#fff;color:var(--texto);font:inherit;font-size:12px;transition:.18s ease}
    .form-control:focus{border-color:#82a9cc;box-shadow:0 0 0 3px rgba(22,76,150,.08)}
    .filter-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:14px;padding-top:14px;border-top:1px solid #edf1f4}
    .btn-filter{min-height:39px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 14px;border:1px solid #d7e1e7;border-radius:10px;background:#f3f6f8;color:#466277;text-decoration:none;font-size:11px;font-weight:800;cursor:pointer}
    .btn-filter.primary{border-color:var(--azul-principal);background:var(--azul-principal);color:#fff}
    .btn-filter:hover{filter:brightness(.98)}
    .table-panel{overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(28,64,92,.05)}
    .table-panel-header{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:17px 19px;border-bottom:1px solid #e5edf2;background:#fbfcfd}
    .panel-title{display:flex;align-items:center;gap:11px}
    .records-count{padding:6px 10px;border:1px solid #dce6ed;border-radius:999px;background:#fff;color:#5c7487;font-size:11px;font-weight:800;white-space:nowrap}
    .table-responsive{overflow-x:auto}
    .data-table{width:100%;min-width:1370px;border-collapse:collapse}
    .data-table th{padding:12px 13px;border-bottom:1px solid #dfe7ee;background:#f7f9fb;color:#60788b;font-size:10.5px;font-weight:800;letter-spacing:.035em;text-align:left;text-transform:uppercase;white-space:nowrap}
    .data-table td{padding:13px;border-bottom:1px solid #edf1f4;color:var(--texto);font-size:12px;vertical-align:middle}
    .data-table tbody tr:hover{background:#fafcfd}.data-table tbody tr:last-child td{border-bottom:0}
    .document-number{color:var(--azul-principal);font-weight:800;white-space:nowrap}
    .auditor-name{display:block;min-width:145px;font-weight:700}
    .agent-code{display:block;margin-bottom:2px;color:var(--azul-principal);font-size:10.5px;font-weight:800}
    .agent-business{display:block;min-width:180px;font-weight:700}
    .status-badge,.result-badge{display:inline-flex;align-items:center;gap:6px;min-height:27px;padding:0 9px;border-radius:999px;font-size:9.5px;font-weight:800;text-transform:uppercase;white-space:nowrap}
    .status-badge:before,.result-badge:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
    .status-certified{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8}
    .status-pending{color:#936b00;background:#fff8e3;border:1px solid #f0dfa3}
    .status-cancelled{color:#b13b3b;background:#fff0f0;border:1px solid #f0cccc}
    .status-draft{color:#526d82;background:#f1f5f8;border:1px solid #dce5eb}
    .status-neutral{color:#526d82;background:#f1f5f8;border:1px solid #dce5eb}
    .money{font-weight:700;white-space:nowrap}
    .difference-cell{min-width:120px}
    .difference-value{display:block;margin-bottom:4px;color:var(--azul-profundo);font-weight:800;white-space:nowrap}
    .result-shortage{color:#b13b3b;background:#fff0f0;border:1px solid #f0cccc}
    .result-surplus{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8}
    .result-exact{color:#526d82;background:#f1f5f8;border:1px solid #dce5eb}
    .actions{display:flex;align-items:center;gap:8px;white-space:nowrap}
    .action-btn{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #d5dfe5;border-radius:9px;background:#fff;color:#31536e;text-decoration:none;transition:.2s ease}
    .action-btn:hover{transform:translateY(-1px);border-color:#9db6c9;background:#f4f8fb;color:var(--azul-principal);box-shadow:0 6px 14px rgba(24,66,99,.1)}
    .action-btn.print{border-color:#c8d7e3;background:#edf5fb;color:var(--azul-principal)}
    .action-btn svg{width:17px;height:17px}
    .empty-state{padding:48px 20px!important;text-align:center}
    .empty-icon{width:52px;height:52px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;border-radius:15px;color:#7890a2;background:#f1f5f8}
    .empty-icon svg{width:23px;height:23px}
    .empty-state strong{display:block;margin-bottom:4px;color:var(--azul-profundo);font-size:14px}
    .empty-state span{color:var(--texto-secundario);font-size:12px}
    .pagination-wrapper{padding:16px 18px;border-top:1px solid #edf1f4}
    @media(max-width:1250px){.summary-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:800px){.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.filter-grid,.filter-grid-secondary{grid-template-columns:1fr}}
    @media(max-width:560px){.summary-grid{grid-template-columns:1fr}.table-panel-header{align-items:flex-start;flex-direction:column}.filter-actions{flex-direction:column}.btn-filter{width:100%}}
</style>
@endpush

@section('content')
<div class="audit-agents-page">
    <div class="page-header">
        <div class="page-title module-heading">
            <div class="module-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
            </div>
            <div>
                <h2>Auditoría de Agentes</h2>
                <p>Consulte los arqueos realizados por usuarios del departamento de Auditoría.</p>
            </div>
        </div>
    </div>

    <div class="summary-grid">
        <article class="summary-card">
            <div class="summary-icon"><svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 7h8M8 11h8M8 15h5"></path></svg></div>
            <div><span class="summary-label">Total</span><strong class="summary-value">{{ $resumen->total }}</strong></div>
        </article>

        <article class="summary-card certified">
            <div class="summary-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m8 12 2.5 2.5L16 9"></path></svg></div>
            <div><span class="summary-label">Certificados</span><strong class="summary-value">{{ $resumen->certificados }}</strong></div>
        </article>

        <article class="summary-card pending">
            <div class="summary-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg></div>
            <div><span class="summary-label">Pendientes</span><strong class="summary-value">{{ $resumen->pendientes }}</strong></div>
        </article>

        <article class="summary-card cancelled">
            <div class="summary-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m9 9 6 6M15 9l-6 6"></path></svg></div>
            <div><span class="summary-label">Anulados</span><strong class="summary-value">{{ $resumen->anulados }}</strong></div>
        </article>

        <article class="summary-card shortage">
            <div class="summary-icon"><svg viewBox="0 0 24 24"><path d="M12 3v18M17 7.5c0-1.9-2.2-3.5-5-3.5S7 5.4 7 7.5 9.2 11 12 11s5 1.5 5 3.5S14.8 18 12 18s-5-1.6-5-3.5"></path><path d="M18 19h4M20 17v4"></path></svg></div>
            <div><span class="summary-label">Faltantes</span><strong class="summary-value">{{ $resumen->faltantes }}</strong></div>
        </article>

        <article class="summary-card surplus">
            <div class="summary-icon"><svg viewBox="0 0 24 24"><path d="M12 3v18M17 7.5c0-1.9-2.2-3.5-5-3.5S7 5.4 7 7.5 9.2 11 12 11s5 1.5 5 3.5S14.8 18 12 18s-5-1.6-5-3.5"></path><path d="M18 19h4"></path></svg></div>
            <div><span class="summary-label">Sobrantes</span><strong class="summary-value">{{ $resumen->sobrantes }}</strong></div>
        </article>
    </div>

    <form method="GET" action="{{ route('administrador.auditoria-agentes.index') }}" class="filter-panel">
        <div class="filter-heading">
            <div class="filter-icon">
                <svg viewBox="0 0 24 24"><path d="M4 5h16l-6 7v5l-4 2v-7L4 5Z"></path></svg>
            </div>
            <div>
                <h3>Filtros de Consulta</h3>
                <p>Localice arqueos por auditor, agente, estado, resultado o rango de fechas.</p>
            </div>
        </div>

        <div class="filter-body">
            <div class="filter-grid">
                <div class="field-group">
                    <label for="buscar">Buscar</label>
                    <input id="buscar" class="form-control" name="buscar" value="{{ $buscar }}" placeholder="Arqueo, Agente, Auditor, Ruta o Región">
                </div>

                <div class="field-group">
                    <label for="auditor_id">Auditor</label>
                    <select id="auditor_id" class="form-control" name="auditor_id">
                        <option value="">Todos los Auditores</option>
                        @foreach($auditores as $auditor)
                            @php
                                $nombreAuditor = trim(($auditor->nombres ?? '') . ' ' . ($auditor->apellidos ?? ''));
                            @endphp
                            <option value="{{ $auditor->id }}" @selected($auditorId === (int) $auditor->id)>
                                {{ $nombreAuditor !== '' ? $nombreAuditor : $auditor->usuario }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label for="agente_id">Agente</label>
                    <select id="agente_id" class="form-control" name="agente_id">
                        <option value="">Todos los Agentes</option>
                        @foreach($agentes as $agente)
                            <option value="{{ $agente->id }}" @selected($agenteId === (int) $agente->id)>
                                {{ $agente->codigo_agente }} — {{ $agente->nombre_negocio }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label for="estado">Estado</label>
                    <select id="estado" class="form-control" name="estado">
                        <option value="">Todos los Estados</option>
                        <option value="BORRADOR" @selected($estado === 'BORRADOR')>Borrador</option>
                        <option value="PENDIENTE_CERTIFICACION" @selected($estado === 'PENDIENTE_CERTIFICACION')>Pendiente</option>
                        <option value="CERTIFICADO" @selected($estado === 'CERTIFICADO')>Certificado</option>
                        <option value="ANULADO" @selected($estado === 'ANULADO')>Anulado</option>
                    </select>
                </div>
            </div>

            <div class="filter-grid-secondary">
                <div class="field-group">
                    <label for="resultado">Resultado</label>
                    <select id="resultado" class="form-control" name="resultado">
                        <option value="">Todos los Resultados</option>
                        <option value="FALTANTE" @selected($resultado === 'FALTANTE')>Faltante</option>
                        <option value="SOBRANTE" @selected($resultado === 'SOBRANTE')>Sobrante</option>
                        <option value="EXACTO" @selected($resultado === 'EXACTO')>Exacto</option>
                    </select>
                </div>

                <div class="field-group">
                    <label for="desde">Desde</label>
                    <input id="desde" type="date" class="form-control" name="desde" value="{{ $desde }}">
                </div>

                <div class="field-group">
                    <label for="hasta">Hasta</label>
                    <input id="hasta" type="date" class="form-control" name="hasta" value="{{ $hasta }}">
                </div>
            </div>

            <div class="filter-actions">
                <a href="{{ route('administrador.auditoria-agentes.index') }}" class="btn-filter">Limpiar</a>
                <button type="submit" class="btn-filter primary">Aplicar Filtros</button>
            </div>
        </div>
    </form>

    <section class="table-panel">
        <div class="table-panel-header">
            <div class="panel-title">
                <div class="panel-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path><path d="M8 12h8M8 15h5"></path></svg>
                </div>
                <div>
                    <h3>Registro de Auditorías</h3>
                    <p>Arqueos efectuados por el departamento de Auditoría.</p>
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
                        <th>Auditor</th>
                        <th>Agente</th>
                        <th>Región</th>
                        <th>Ruta</th>
                        <th>Estado</th>
                        <th>Saldo</th>
                        <th>Arqueado</th>
                        <th>Diferencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arqueos as $arqueo)
                        @php
                            $auditor = trim(($arqueo->auditor_nombres ?? '') . ' ' . ($arqueo->auditor_apellidos ?? ''));
                            $diferencia = (float) $arqueo->diferencia;

                            $estadoClase = match ($arqueo->estado) {
                                'CERTIFICADO' => 'status-certified',
                                'PENDIENTE_CERTIFICACION' => 'status-pending',
                                'ANULADO' => 'status-cancelled',
                                'BORRADOR' => 'status-draft',
                                default => 'status-neutral',
                            };

                            $resultadoClase = $diferencia < 0
                                ? 'result-shortage'
                                : ($diferencia > 0 ? 'result-surplus' : 'result-exact');

                            $resultadoTexto = $diferencia < 0
                                ? 'Faltante'
                                : ($diferencia > 0 ? 'Sobrante' : 'Exacto');
                        @endphp

                        <tr>
                            <td><span class="document-number">{{ $arqueo->numero_arqueo }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}</td>
                            <td><span class="auditor-name">{{ $auditor !== '' ? $auditor : $arqueo->auditor_usuario }}</span></td>
                            <td>
                                <span class="agent-code">{{ $arqueo->codigo_agente_historico }}</span>
                                <span class="agent-business">{{ $arqueo->nombre_negocio_historico }}</span>
                            </td>
                            <td>{{ $arqueo->region_historica ?: '—' }}</td>
                            <td>{{ $arqueo->ruta_historica ?: '—' }}</td>
                            <td>
                                <span class="status-badge {{ $estadoClase }}">
                                    {{ str_replace('_', ' ', $arqueo->estado) }}
                                </span>
                            </td>
                            <td><span class="money">Q {{ number_format((float) $arqueo->saldo_sistema, 2) }}</span></td>
                            <td><span class="money">Q {{ number_format((float) $arqueo->total_arqueado, 2) }}</span></td>
                            <td class="difference-cell">
                                <span class="difference-value">Q {{ number_format(abs($diferencia), 2) }}</span>
                                <span class="result-badge {{ $resultadoClase }}">{{ $resultadoTexto }}</span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a
                                        href="{{ route('administrador.auditoria-agentes.show', $arqueo->id) }}"
                                        class="action-btn"
                                        title="Ver detalle"
                                        aria-label="Ver detalle"
                                    >
                                        <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.8"></circle></svg>
                                    </a>

                                    <a
                                        target="_blank"
                                        href="{{ route('administrador.auditoria-agentes.imprimir', $arqueo->id) }}"
                                        class="action-btn print"
                                        title="Imprimir PDF"
                                        aria-label="Imprimir PDF"
                                    >
                                        <svg viewBox="0 0 24 24"><path d="M6 9V3h12v6"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="7"></rect><path d="M18 12h.01"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="empty-state">
                                <div class="empty-icon">
                                    <svg viewBox="0 0 24 24"><path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path><path d="M9 12h6"></path></svg>
                                </div>
                                <strong>No se encontraron arqueos de Auditoría</strong>
                                <span>Modifique los filtros de consulta o espere a que se registren nuevas auditorías.</span>
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
