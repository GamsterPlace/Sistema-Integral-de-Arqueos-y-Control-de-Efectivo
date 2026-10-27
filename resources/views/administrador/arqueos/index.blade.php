@extends('layouts.administrador')

@section('title', 'Todos los Arqueos')
@section('module-title', 'Todos los Arqueos')

@push('styles')
<style>
    .arqueos-page{display:grid;gap:20px}
    .arqueos-header{display:flex;align-items:flex-start;justify-content:space-between;gap:22px}
    .arqueos-title{display:flex;align-items:flex-start;gap:15px}
    .arqueos-title-icon{flex:0 0 auto;width:48px;height:48px;display:grid;place-items:center;border-radius:14px;background:linear-gradient(135deg,#164c96,#1c64b5);color:#fff;box-shadow:0 9px 20px rgba(22,76,150,.17)}
    .arqueos-title-icon svg,.summary-icon svg,.filter-icon svg,.button-icon,.icon-button svg,.empty-icon svg{fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
    .arqueos-title-icon svg{width:23px;height:23px}
    .arqueos-title h2{margin:0;color:#06284f;font-size:27px;letter-spacing:-.6px}
    .arqueos-title p{max-width:760px;margin:7px 0 0;color:#718391;font-size:12px;line-height:1.55}

    .summary-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:12px}
    .summary-card{position:relative;overflow:hidden;min-height:112px;padding:16px;border:1px solid #dfe7ed;border-radius:16px;background:#fff;box-shadow:0 8px 22px rgba(20,57,83,.04);transition:.2s ease}
    .summary-card:hover{transform:translateY(-2px);box-shadow:0 12px 27px rgba(20,57,83,.07)}
    .summary-card::after{content:"";position:absolute;width:68px;height:68px;right:-26px;bottom:-30px;border-radius:50%;background:rgba(22,76,150,.035)}
    .summary-icon{width:34px;height:34px;display:grid;place-items:center;margin-bottom:11px;border-radius:10px;background:#edf4fb;color:#164c96}
    .summary-card:nth-child(2) .summary-icon{background:#eff9f3;color:#008640}
    .summary-card:nth-child(3) .summary-icon{background:#eef6fb;color:#246b9c}
    .summary-card:nth-child(4) .summary-icon{background:#eff9f3;color:#008640}
    .summary-card:nth-child(5) .summary-icon{background:#fff8e8;color:#9a7115}
    .summary-card:nth-child(6) .summary-icon{background:#fff3f2;color:#b14b43}
    .summary-card:nth-child(7) .summary-icon{background:#f3effb;color:#6e52a3}
    .summary-icon svg{width:17px;height:17px}
    .summary-label{display:block;color:#758697;font-size:8px;font-weight:850;letter-spacing:.5px;text-transform:uppercase}
    .summary-value{position:relative;z-index:1;display:block;margin-top:5px;color:#082d55;font-size:22px;font-weight:850}

    .filters-card,.table-card{overflow:hidden;border:1px solid #dfe7ed;border-radius:18px;background:#fff;box-shadow:0 9px 25px rgba(20,57,83,.04)}
    .filters-header,.table-header{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:15px 18px;border-bottom:1px solid #e7edf1;background:#fbfcfd}
    .filters-heading{display:flex;align-items:center;gap:11px}
    .filter-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;background:#edf4fb;color:#164c96}
    .filter-icon svg{width:17px;height:17px}
    .filters-header h3,.table-header h3{margin:0;color:#0b315f;font-size:13px;font-weight:850}
    .filters-header p,.table-header p{margin:3px 0 0;color:#8795a0;font-size:9px}
    .filters-body{padding:17px 18px}
    .filters-grid{display:grid;grid-template-columns:minmax(280px,1.4fr) repeat(3,minmax(170px,.75fr));gap:11px}
    .filter-label{display:block;margin-bottom:6px;color:#617586;font-size:8px;font-weight:850;letter-spacing:.5px;text-transform:uppercase}
    .filter-control{width:100%;min-height:42px;padding:0 12px;border:1px solid #ced9e1;border-radius:10px;outline:none;background:#fff;color:#30485b;font-size:10px;transition:.18s ease}
    .filter-control:focus{border-color:#5d8fc6;box-shadow:0 0 0 3px rgba(22,76,150,.08)}
    .filter-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:13px}
    .filter-btn{min-height:39px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 14px;border-radius:10px;font-size:9px;font-weight:850;text-decoration:none;cursor:pointer;transition:.2s ease}
    .filter-btn.secondary{border:1px solid #d5e0e7;background:#fff;color:#526a7c}
    .filter-btn.secondary:hover{background:#f5f8fa;border-color:#b4c4cf}
    .filter-btn.primary{border:1px solid #164c96;background:#164c96;color:#fff}
    .filter-btn.primary:hover{background:#123f7d}
    .button-icon{width:14px;height:14px}

    .records-count{display:inline-flex;align-items:center;gap:7px;padding:7px 10px;border:1px solid #dbe5eb;border-radius:999px;background:#fff;color:#607687;font-size:9px;font-weight:800}
    .records-dot{width:6px;height:6px;border-radius:50%;background:#00a651}
    .table-responsive{overflow-x:auto}
    .arqueos-table{width:100%;min-width:1320px;border-collapse:collapse}
    .arqueos-table th{padding:12px 14px;border-bottom:1px solid #e2e9ee;background:#f7f9fb;color:#687d8d;font-size:8px;font-weight:850;letter-spacing:.55px;text-align:left;text-transform:uppercase;white-space:nowrap}
    .arqueos-table td{padding:13px 14px;border-bottom:1px solid #edf1f4;color:#415b6e;font-size:10px;vertical-align:middle}
    .arqueos-table tbody tr{transition:background .16s ease}
    .arqueos-table tbody tr:hover{background:#fafcfd}
    .arqueos-table tbody tr:last-child td{border-bottom:0}
    .arqueo-number{color:#0b315f;font-weight:850;white-space:nowrap}
    .agent-cell strong{display:block;color:#173b59;font-size:10px}
    .agent-cell span{display:block;margin-top:3px;color:#82919c;font-size:9px}
    .type-badge,.status-badge,.mode-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid #d4e0e8;border-radius:999px;background:#f4f8fb;color:#315f80;font-size:8px;font-weight:850;letter-spacing:.25px;white-space:nowrap}
    .type-badge.promotor{border-color:#cce7d7;background:#eff9f3;color:#197245}
    .status-badge.certificado{border-color:#c9e5d4;background:#effaf3;color:#197245}
    .status-badge.pendiente{border-color:#efdda9;background:#fff9e9;color:#8a6511}
    .status-badge.anulado{border-color:#e6d1d1;background:#fff4f4;color:#a33c3c}
    .badge-dot{width:6px;height:6px;border-radius:50%;background:currentColor}
    .difference{font-weight:850;color:#173b59;white-space:nowrap}
    .difference.positive{color:#197245}
    .difference.negative{color:#a33c3c}

    .actions-cell{white-space:nowrap}
    .table-actions{display:inline-flex;align-items:center;gap:8px}
    .icon-button{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border:1px solid #d5dfe5;border-radius:9px;background:#fff;color:#31536e;text-decoration:none;transition:.2s ease}
    .icon-button:hover{transform:translateY(-1px);border-color:#9db6c9;background:#f4f8fb;color:#164c96;box-shadow:0 6px 14px rgba(24,66,99,.10)}
    .icon-button.print{border-color:#c8d7e3;background:#edf5fb;color:#164c96}
    .icon-button svg{width:17px;height:17px}

    .empty-state{padding:48px 20px!important;text-align:center}
    .empty-icon{width:48px;height:48px;display:grid;place-items:center;margin:0 auto 12px;border-radius:14px;background:#edf4fb;color:#164c96}
    .empty-icon svg{width:23px;height:23px}
    .empty-state strong{display:block;color:#173b59;font-size:12px}
    .empty-state span{display:block;margin-top:5px;color:#8795a0;font-size:10px}
    .pagination-wrap{padding:15px 18px;border-top:1px solid #e7edf1;background:#fbfcfd}

    @media(max-width:1250px){.summary-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
    @media(max-width:1000px){.filters-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:720px){
        .summary-grid,.filters-grid{grid-template-columns:1fr}
        .filter-actions{flex-direction:column-reverse}
        .filter-btn{width:100%}
        .table-header{align-items:flex-start;flex-direction:column}
    }
</style>
@endpush

@section('content')
<div class="arqueos-page">
    <header class="arqueos-header">
        <div class="arqueos-title">
            <div class="arqueos-title-icon">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="M7 9h10"/>
                    <path d="M7 13h5"/>
                    <path d="M17 13h.01"/>
                </svg>
            </div>
            <div>
                <h2>Todos los Arqueos</h2>
                <p>Consulta consolidada de arqueos realizados por Agentes y Promotores MICOOPE.</p>
            </div>
        </div>
    </header>

    <section class="summary-grid">
        @php
            $cards = [
                ['Total', $resumen->total, 'total'],
                ['Agente', $resumen->agente, 'agente'],
                ['Promotor', $resumen->promotor, 'promotor'],
                ['Certificados', $resumen->certificados, 'certificado'],
                ['Pendientes', $resumen->pendientes, 'pendiente'],
                ['Anulados', $resumen->anulados, 'anulado'],
                ['Extemporáneos', $resumen->extemporaneos, 'extemporaneo'],
            ];
        @endphp

        @foreach($cards as [$label, $value, $kind])
            <article class="summary-card">
                <div class="summary-icon">
                    @if($kind === 'certificado')
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                    @elseif($kind === 'pendiente')
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5"/><path d="M12 16h.01"/></svg>
                    @elseif($kind === 'anulado')
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m7 7 10 10"/></svg>
                    @elseif($kind === 'extemporaneo')
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4"/><path d="M16 3v4"/><path d="M3 10h18"/><path d="M12 14v3"/><path d="M12 19h.01"/></svg>
                    @elseif($kind === 'agente')
                        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M3 21a6 6 0 0 1 12 0"/></svg>
                    @elseif($kind === 'promotor')
                        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M3 21a6 6 0 0 1 12 0"/><path d="m16 11 2 2 4-4"/></svg>
                    @else
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h10"/><path d="M7 13h5"/></svg>
                    @endif
                </div>
                <span class="summary-label">{{ $label }}</span>
                <strong class="summary-value">{{ $value }}</strong>
            </article>
        @endforeach
    </section>

    <form method="GET" action="{{ route('administrador.arqueos.index') }}" class="filters-card">
        <header class="filters-header">
            <div class="filters-heading">
                <div class="filter-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 6h16"/><path d="M7 12h10"/><path d="M10 18h4"/></svg>
                </div>
                <div>
                    <h3>Filtros de consulta</h3>
                    <p>Localice arqueos por información general, tipo, estado o modalidad.</p>
                </div>
            </div>
        </header>

        <div class="filters-body">
            <div class="filters-grid">
                <div>
                    <label class="filter-label" for="buscar">Buscar</label>
                    <input id="buscar" class="filter-control" name="buscar" value="{{ $buscar }}" placeholder="Número, Agente, responsable, Ruta o Región">
                </div>

                <div>
                    <label class="filter-label" for="tipo">Tipo de arqueo</label>
                    <select id="tipo" name="tipo" class="filter-control">
                        <option value="">Todos los tipos</option>
                        <option value="DIARIO_AGENTE" @selected($tipo === 'DIARIO_AGENTE')>Agente</option>
                        <option value="VISITA_PROMOTOR" @selected($tipo === 'VISITA_PROMOTOR')>Promotor</option>
                    </select>
                </div>

                <div>
                    <label class="filter-label" for="estado">Estado</label>
                    <select id="estado" name="estado" class="filter-control">
                        <option value="">Todos los estados</option>
                        <option value="PENDIENTE_CERTIFICACION" @selected($estado === 'PENDIENTE_CERTIFICACION')>Pendiente</option>
                        <option value="CERTIFICADO" @selected($estado === 'CERTIFICADO')>Certificado</option>
                        <option value="ANULADO" @selected($estado === 'ANULADO')>Anulado</option>
                    </select>
                </div>

                <div>
                    <label class="filter-label" for="extemporaneo">Modalidad</label>
                    <select id="extemporaneo" name="extemporaneo" class="filter-control">
                        <option value="">Todos</option>
                        <option value="SI" @selected($extemporaneo === 'SI')>Extemporáneos</option>
                        <option value="NO" @selected($extemporaneo === 'NO')>Ordinarios</option>
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <a href="{{ route('administrador.arqueos.index') }}" class="filter-btn secondary">
                    <svg class="button-icon" viewBox="0 0 24 24"><path d="M4 4l16 16"/><path d="M20 4 4 20"/></svg>
                    Limpiar
                </a>
                <button type="submit" class="filter-btn primary">
                    <svg class="button-icon" viewBox="0 0 24 24"><path d="M4 6h16"/><path d="M7 12h10"/><path d="M10 18h4"/></svg>
                    Aplicar Filtros
                </button>
            </div>
        </div>
    </form>

    <section class="table-card">
        <header class="table-header">
            <div>
                <h3>Registro consolidado</h3>
                <p>Historial general de arqueos registrados en el sistema.</p>
            </div>
            <div class="records-count">
                <span class="records-dot"></span>
                {{ $arqueos->total() }} registros
            </div>
        </header>

        <div class="table-responsive">
            <table class="arqueos-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Agente</th>
                        <th>Responsable</th>
                        <th>Región</th>
                        <th>Ruta</th>
                        <th>Estado</th>
                        <th>Diferencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arqueos as $arqueo)
                        @php
                            $resp = trim(($arqueo->responsable_nombres ?? '') . ' ' . ($arqueo->responsable_apellidos ?? ''));
                            $tipoPromotor = $arqueo->tipo === 'VISITA_PROMOTOR';
                            $estadoClass = match($arqueo->estado) {
                                'CERTIFICADO' => 'certificado',
                                'PENDIENTE_CERTIFICACION' => 'pendiente',
                                'ANULADO' => 'anulado',
                                default => ''
                            };
                            $diferencia = (float) $arqueo->diferencia;
                        @endphp
                        <tr>
                            <td><span class="arqueo-number">{{ $arqueo->numero_arqueo }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}</td>
                            <td>
                                <span class="type-badge {{ $tipoPromotor ? 'promotor' : '' }}">
                                    <span class="badge-dot"></span>
                                    {{ $tipoPromotor ? 'Promotor' : 'Agente' }}
                                </span>
                            </td>
                            <td class="agent-cell">
                                <strong>{{ $arqueo->codigo_agente }}</strong>
                                <span>{{ $arqueo->nombre_negocio }}</span>
                            </td>
                            <td>{{ $resp !== '' ? $resp : ($arqueo->responsable_usuario ?? '—') }}</td>
                            <td>{{ $arqueo->region_nombre ?? '—' }}</td>
                            <td>{{ $arqueo->ruta_nombre ?? '—' }}</td>
                            <td>
                                <span class="status-badge {{ $estadoClass }}">
                                    <span class="badge-dot"></span>
                                    {{ str_replace('_', ' ', $arqueo->estado) }}
                                </span>
                            </td>
                            <td>
                                <span class="difference {{ $diferencia > 0 ? 'positive' : ($diferencia < 0 ? 'negative' : '') }}">
                                    Q {{ number_format(abs($diferencia), 2) }}
                                </span>
                            </td>
                            <td class="actions-cell">
                                <div class="table-actions">
                                    <a href="{{ route('administrador.arqueos.show', $arqueo->id) }}" class="icon-button" title="Ver detalle" aria-label="Ver detalle">
                                        <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                                    </a>
                                    <a href="{{ route('administrador.arqueos.imprimir', $arqueo->id) }}" target="_blank" class="icon-button print" title="Imprimir PDF" aria-label="Imprimir en PDF">
                                        <svg viewBox="0 0 24 24"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7"/><path d="M18 12h.01"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="empty-state">
                                <div class="empty-icon">
                                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                                </div>
                                <strong>No hay arqueos para mostrar</strong>
                                <span>Modifique los filtros de consulta para ampliar los resultados.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($arqueos->hasPages())
            <div class="pagination-wrap">
                {{ $arqueos->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
