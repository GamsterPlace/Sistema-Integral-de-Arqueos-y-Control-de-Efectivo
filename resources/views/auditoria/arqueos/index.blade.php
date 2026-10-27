@extends('layouts.auditoria')

@section('title', 'Historial')
@section('module-title', 'Historial')

@push('styles')
<style>
    .page-actions{
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .btn-primary-custom,.btn-secondary-custom{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:42px;
        padding:0 17px;
        border-radius:11px;
        font-size:10px;
        font-weight:850;
        text-decoration:none;
        border:0;
        cursor:pointer;
        transition:.2s;
    }

    .btn-primary-custom{
        background:linear-gradient(135deg,#164c96,#1c64b5);
        color:#fff;
        box-shadow:0 8px 18px rgba(22,76,150,.16);
    }

    .btn-secondary-custom{
        background:#edf2f5;
        color:#3d596f;
    }

    .summary-grid{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:14px;
        margin-bottom:20px;
    }

    .summary-card{
        position:relative;
        overflow:hidden;
        padding:18px;
        border:1px solid #e0e8ee;
        border-radius:17px;
        background:#fff;
        box-shadow:0 6px 20px rgba(20,57,83,.035);
    }

    .summary-card:before{
        content:"";
        position:absolute;
        top:0;
        left:0;
        width:100%;
        height:3px;
        background:#164c96;
    }

    .summary-card.certified:before{background:#00a651}
    .summary-card.pending:before{background:#d99a21}
    .summary-card.cancelled:before{background:#b74747}

    .summary-card span{
        display:block;
        color:#758697;
        font-size:9px;
        font-weight:850;
        letter-spacing:.35px;
        text-transform:uppercase;
    }

    .summary-card strong{
        display:block;
        margin-top:7px;
        color:#082d55;
        font-size:24px;
        line-height:1;
    }

    .filter-card{
        margin-bottom:20px;
        padding:18px;
        border:1px solid #e0e8ee;
        border-radius:17px;
        background:#fff;
        box-shadow:0 6px 20px rgba(20,57,83,.03);
    }

    .filter-grid{
        display:grid;
        grid-template-columns:minmax(260px,1.5fr) 210px 210px 170px 170px;
        gap:10px;
    }

    .filter-control{
        width:100%;
        min-height:42px;
        padding:0 12px;
        border:1px solid #ced9e1;
        border-radius:10px;
        background:#fff;
        color:#24455f;
        font-size:11px;
        outline:none;
        box-sizing:border-box;
    }

    .filter-control:focus{
        border-color:#6f9bc1;
        box-shadow:0 0 0 3px rgba(22,76,150,.08);
    }

    .filter-actions{
        display:flex;
        justify-content:flex-end;
        gap:10px;
        margin-top:12px;
    }

    .table-card{
        overflow:hidden;
        border:1px solid #e0e8ee;
        border-radius:18px;
        background:#fff;
        box-shadow:0 7px 22px rgba(20,57,83,.035);
    }

    .table-scroll{
        overflow-x:auto;
    }

    .history-table{
        width:100%;
        min-width:1150px;
        border-collapse:collapse;
    }

    .history-table thead tr{
        background:#f7f9fb;
    }

    .history-table th{
        padding:12px 14px;
        color:#5d7182;
        font-size:9px;
        font-weight:850;
        letter-spacing:.25px;
        text-align:left;
        text-transform:uppercase;
        white-space:nowrap;
    }

    .history-table td{
        padding:13px 14px;
        border-top:1px solid #edf1f4;
        color:#334f64;
        font-size:10px;
        vertical-align:middle;
    }

    .history-table td strong{
        color:#0a3158;
    }

    .agent-cell strong{
        display:block;
        margin-bottom:3px;
        font-size:10px;
    }

    .agent-cell span{
        display:block;
        max-width:260px;
        color:#728493;
        font-size:9px;
    }

    .status-badge,.result-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:25px;
        padding:0 9px;
        border-radius:999px;
        font-size:8px;
        font-weight:850;
        text-transform:uppercase;
        white-space:nowrap;
    }

    .status-certified{
        background:#eaf8f0;
        color:#167746;
    }

    .status-pending{
        background:#fff6df;
        color:#9a6913;
    }

    .status-cancelled{
        background:#fff0ef;
        color:#a83c38;
    }

    .status-default{
        background:#edf2f5;
        color:#50687a;
    }

    .difference-value{
        display:block;
        margin-bottom:4px;
        color:#17364f;
        font-size:11px;
        font-weight:850;
        white-space:nowrap;
    }

    .result-faltante{
        background:#fff0ef;
        color:#ad3f3b;
    }

    .result-sobrante{
        background:#eaf8f0;
        color:#167746;
    }

    .result-exacto{
        background:#edf4fa;
        color:#315f86;
    }

    .actions-cell{
        display:flex;
        align-items:center;
        gap:7px;
        white-space:nowrap;
    }

    .action-btn{
        width:36px;
        height:36px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border:1px solid #dce5eb;
        border-radius:10px;
        text-decoration:none;
        transition:.18s;
    }

    .action-btn svg{
        width:16px;
        height:16px;
        stroke:currentColor;
    }

    .action-view{
        background:#fff;
        color:#31536e;
    }

    .action-print{
        background:#eaf4fb;
        border-color:#cce0ef;
        color:#176398;
    }

    .action-btn:hover{
        transform:translateY(-1px);
        box-shadow:0 5px 12px rgba(20,57,83,.09);
    }

    .empty-state{
        padding:42px 20px !important;
        text-align:center;
        color:#7a8c99 !important;
    }

    .pagination-wrapper{
        padding:16px 18px;
        border-top:1px solid #edf1f4;
    }

    @media(max-width:1100px){
        .filter-grid{
            grid-template-columns:repeat(2,minmax(0,1fr));
        }

        .filter-grid > :first-child{
            grid-column:span 2;
        }
    }

    @media(max-width:850px){
        .summary-grid{
            grid-template-columns:repeat(2,minmax(0,1fr));
        }
    }

    @media(max-width:650px){
        .page-actions{
            width:100%;
            margin-top:14px;
        }

        .page-actions .btn-primary-custom{
            width:100%;
        }

        .summary-grid,.filter-grid{
            grid-template-columns:1fr;
        }

        .filter-grid > :first-child{
            grid-column:span 1;
        }

        .filter-actions{
            flex-direction:column-reverse;
        }

        .filter-actions a,.filter-actions button{
            width:100%;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h2>Historial de Auditorías</h2>
        <p>Consulte los arqueos de Auditoría realizados por su usuario.</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('auditoria.arqueos.create') }}" class="btn-primary-custom">
            Realizar Arqueo
        </a>
    </div>
</div>

<div class="summary-grid">
    <article class="summary-card">
        <span>Total</span>
        <strong>{{ $resumen->total }}</strong>
    </article>

    <article class="summary-card certified">
        <span>Certificados</span>
        <strong>{{ $resumen->certificados }}</strong>
    </article>

    <article class="summary-card pending">
        <span>Pendientes</span>
        <strong>{{ $resumen->pendientes }}</strong>
    </article>

    <article class="summary-card cancelled">
        <span>Anulados</span>
        <strong>{{ $resumen->anulados }}</strong>
    </article>
</div>

<form method="GET" action="{{ route('auditoria.arqueos.index') }}" class="filter-card">
    <div class="filter-grid">
        <input
            type="text"
            name="buscar"
            value="{{ $buscar }}"
            placeholder="Número, Agente, Ruta o Región"
            class="filter-control"
        >

        <select name="estado" class="filter-control">
            <option value="">Todos los Estados</option>
            <option value="PENDIENTE_CERTIFICACION" @selected($estado === 'PENDIENTE_CERTIFICACION')>
                Pendiente
            </option>
            <option value="CERTIFICADO" @selected($estado === 'CERTIFICADO')>
                Certificado
            </option>
            <option value="ANULADO" @selected($estado === 'ANULADO')>
                Anulado
            </option>
        </select>

        <select name="resultado" class="filter-control">
            <option value="">Todos los Resultados</option>
            <option value="FALTANTE" @selected($resultado === 'FALTANTE')>
                Faltante
            </option>
            <option value="SOBRANTE" @selected($resultado === 'SOBRANTE')>
                Sobrante
            </option>
            <option value="EXACTO" @selected($resultado === 'EXACTO')>
                Exacto
            </option>
        </select>

        <input type="date" name="desde" value="{{ $desde }}" class="filter-control">
        <input type="date" name="hasta" value="{{ $hasta }}" class="filter-control">
    </div>

    <div class="filter-actions">
        <a href="{{ route('auditoria.arqueos.index') }}" class="btn-secondary-custom">
            Limpiar
        </a>

        <button type="submit" class="btn-primary-custom">
            Aplicar Filtros
        </button>
    </div>
</form>

<div class="table-card">
    <div class="table-scroll">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Agente</th>
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
                        $diferencia = (float) $arqueo->diferencia;

                        $estadoClase = match($arqueo->estado) {
                            'CERTIFICADO' => 'status-certified',
                            'PENDIENTE_CERTIFICACION' => 'status-pending',
                            'ANULADO' => 'status-cancelled',
                            default => 'status-default',
                        };

                        $estadoTexto = match($arqueo->estado) {
                            'PENDIENTE_CERTIFICACION' => 'Pendiente',
                            'CERTIFICADO' => 'Certificado',
                            'ANULADO' => 'Anulado',
                            default => str_replace('_',' ',$arqueo->estado),
                        };

                        if ($diferencia < -0.004) {
                            $resultadoTexto = 'Faltante';
                            $resultadoClase = 'result-faltante';
                        } elseif ($diferencia > 0.004) {
                            $resultadoTexto = 'Sobrante';
                            $resultadoClase = 'result-sobrante';
                        } else {
                            $resultadoTexto = 'Exacto';
                            $resultadoClase = 'result-exacto';
                        }
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $arqueo->numero_arqueo }}</strong>
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}
                        </td>

                        <td class="agent-cell">
                            <strong>{{ $arqueo->codigo_agente_historico }}</strong>
                            <span>{{ $arqueo->nombre_negocio_historico }}</span>
                        </td>

                        <td>{{ $arqueo->region_historica }}</td>
                        <td>{{ $arqueo->ruta_historica }}</td>

                        <td>
                            <span class="status-badge {{ $estadoClase }}">
                                {{ $estadoTexto }}
                            </span>
                        </td>

                        <td>
                            <span class="difference-value">
                                Q {{ number_format($diferencia,2) }}
                            </span>

                            <span class="result-badge {{ $resultadoClase }}">
                                {{ $resultadoTexto }}
                            </span>
                        </td>

                        <td>
                            <div class="actions-cell">
                                <a
                                    href="{{ route('auditoria.arqueos.show',$arqueo->id) }}"
                                    class="action-btn action-view"
                                    title="Ver arqueo"
                                    aria-label="Ver arqueo"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8">
                                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                                        <circle cx="12" cy="12" r="2.8"/>
                                    </svg>
                                </a>

                                <a
                                    target="_blank"
                                    href="{{ route('auditoria.arqueos.imprimir',$arqueo->id) }}"
                                    class="action-btn action-print"
                                    title="Imprimir PDF"
                                    aria-label="Imprimir PDF"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8">
                                        <path d="M6 9V3h12v6"/>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                        <path d="M6 14h12v7H6z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            No hay arqueos de Auditoría registrados.
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
</div>
@endsection
