
@extends('layouts.administrador')

@section('title', 'Auditoría de Agencias')
@section('module-title', 'Auditoría de Agencias ECOSABA')

@push('styles')
<style>
.aud-page{max-width:1400px;margin:auto;color:#23425b}
.aud-page *{box-sizing:border-box}
.aud-header{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:24px}
.aud-header h2{margin:0 0 8px;color:#092f57;font-size:26px}
.aud-header p{margin:0;color:#6d8193;font-size:13px;line-height:1.6}
.aud-breadcrumb{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:20px;font-size:12px;color:#72869a}
.aud-breadcrumb a{color:#17558d;font-weight:800;text-decoration:none}
.aud-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:24px}
.aud-stat{background:white;border:1px solid #dce7ef;border-radius:14px;padding:19px;box-shadow:0 6px 20px #173f6010}
.aud-stat small{display:block;color:#718598;font-size:11px;font-weight:700;margin-bottom:9px}
.aud-stat strong{font-size:26px;color:#164c96}
.aud-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:17px}
.aud-card{background:white;border:1px solid #dce7ef;border-radius:16px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 8px 24px #173f6010}
.aud-card-header{padding:20px;background:linear-gradient(120deg,#f2f8fe,#fff);border-bottom:1px solid #e5edf4}
.aud-card-header small{color:#527f9f;font-size:11px;font-weight:800}
.aud-card-header h3{color:#153f66;font-size:18px;margin:8px 0}
.aud-card-header p{color:#728598;font-size:12px;margin:0}
.aud-card-body{padding:17px;flex:1}
.aud-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.aud-metric{background:#f5f9fc;border:1px solid #e8eef4;border-radius:10px;padding:12px}
.aud-metric small{display:block;font-size:10px;color:#718598;margin-bottom:7px}
.aud-metric strong{font-size:18px;color:#164c96}
.aud-card-footer{padding:0 17px 17px}
.aud-btn{display:inline-flex;align-items:center;justify-content:center;min-height:39px;padding:9px 14px;border:1px solid #cbdce9;border-radius:9px;background:white;color:#164c96;text-decoration:none;font-size:12px;font-weight:800;cursor:pointer}
.aud-btn.primary{background:#164c96;border-color:#164c96;color:white}
.aud-card-footer .aud-btn{width:100%}
.aud-panel{background:white;border:1px solid #dce7ef;border-radius:15px;overflow:hidden}
.aud-panel-title{padding:18px 20px;border-bottom:1px solid #e6eef4;font-weight:800;color:#16446d}
.aud-filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;padding:18px}
.aud-field label{display:block;font-size:11px;font-weight:800;color:#647c90;margin-bottom:7px}
.aud-field input,.aud-field select{width:100%;min-height:40px;padding:9px;border:1px solid #d2e0e9;border-radius:9px;background:white;color:#2d4b63}
.aud-filter-actions{display:flex;gap:8px;align-items:end}
.aud-table-wrap{overflow-x:auto}
.aud-table{width:100%;min-width:1050px;border-collapse:collapse}
.aud-table th,.aud-table td{padding:13px;border-bottom:1px solid #eaf0f4;text-align:left;font-size:12px}
.aud-table th{background:#f5f9fc;color:#647e91;font-size:11px}
.aud-table td{color:#34546c;vertical-align:top}
.aud-table td strong{color:#16446d}
.aud-right{text-align:right!important}
.aud-actions{display:flex;gap:7px}
.aud-tag{display:inline-block;padding:5px 9px;border-radius:7px;background:#fff5e6;color:#90651b;font-size:10px;font-weight:800}
.aud-tag.ok{background:#eaf8ef;color:#177547}
.aud-tag.danger{background:#fff0f0;color:#a53b3b}
.aud-empty{text-align:center;padding:35px;color:#778b9c;font-size:13px}
.aud-pagination{padding:15px}
@media(max-width:1100px){.aud-grid{grid-template-columns:repeat(2,1fr)}.aud-filters{grid-template-columns:repeat(2,1fr)}}
@media(max-width:700px){.aud-grid,.aud-stats,.aud-filters{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
    $nombreReceptor = isset($receptor) && $receptor
        ? (trim(($receptor->nombres ?? '').' '.($receptor->apellidos ?? '')) ?: $receptor->usuario)
        : 'Auditoría general de agencia';
@endphp

<div class="aud-page">

    <nav class="aud-breadcrumb">
        <a href="{{ route($ruta) }}">Agencias</a>

        @if($nivel !== 'agencias')
            <span>/</span>

            @if($nivel === 'receptores')
                <strong>{{ $agencia->nombre }}</strong>
            @else
                <a href="{{ route($ruta, ['agencia_id' => $agencia->id]) }}">
                    {{ $agencia->nombre }}
                </a>
                <span>/</span>
                <strong>{{ $nombreReceptor }}</strong>
            @endif
        @endif
    </nav>

    <header class="aud-header">
        <div>
            @if($nivel === 'agencias')
                <h2>Auditoría de Agencias ECOSABA</h2>
                <p>Arqueos realizados por el Departamento de Auditoría.</p>
            @elseif($nivel === 'receptores')
                <h2>{{ $agencia->nombre }}</h2>
                <p>Seleccione un receptor auditado para consultar su historial.</p>
            @else
                <h2>{{ $nombreReceptor }}</h2>
                <p>{{ $agencia->nombre }} · Historial de arqueos de auditoría.</p>
            @endif
        </div>

        @if($nivel === 'historial')
            <a class="aud-btn" href="{{ route($ruta, ['agencia_id' => $agencia->id]) }}">
                Volver a receptores
            </a>
        @elseif($nivel === 'receptores')
            <a class="aud-btn" href="{{ route($ruta) }}">
                Volver a agencias
            </a>
        @else
            <a class="aud-btn" href="{{ route('administrador.arqueos-agencias.index') }}">
                Todos los arqueos
            </a>
        @endif
    </header>

    @if($nivel === 'agencias')

        <div class="aud-stats">
            <div class="aud-stat">
                <small>Agencias registradas</small>
                <strong>{{ number_format($totales->agencias) }}</strong>
            </div>
            <div class="aud-stat">
                <small>Arqueos de auditoría</small>
                <strong>{{ number_format($totales->arqueos) }}</strong>
            </div>
            <div class="aud-stat">
                <small>Receptores auditados por agencia</small>
                <strong>{{ number_format($totales->receptores) }}</strong>
            </div>
            <div class="aud-stat">
                <small>Arqueos con diferencias</small>
                <strong>{{ number_format($totales->diferencias) }}</strong>
            </div>
        </div>

        <div class="aud-grid">
            @forelse($agencias as $item)
                <article class="aud-card">
                    <div class="aud-card-header">
                        <small>AGENCIA · {{ $item->codigo }}</small>
                        <h3>{{ $item->nombre }}</h3>
                        <p>{{ $item->estado ?? 'Sin estado' }}</p>
                    </div>

                    <div class="aud-card-body">
                        <div class="aud-metrics">
                            <div class="aud-metric">
                                <small>Total arqueos</small>
                                <strong>{{ number_format($item->total) }}</strong>
                            </div>
                            <div class="aud-metric">
                                <small>Receptores auditados</small>
                                <strong>{{ number_format($item->receptores) }}</strong>
                            </div>
                            <div class="aud-metric">
                                <small>Certificados</small>
                                <strong>{{ number_format($item->certificados) }}</strong>
                            </div>
                            <div class="aud-metric">
                                <small>Pendientes</small>
                                <strong>{{ number_format($item->pendientes) }}</strong>
                            </div>
                            <div class="aud-metric">
                                <small>Anulados</small>
                                <strong>{{ number_format($item->anulados) }}</strong>
                            </div>
                            <div class="aud-metric">
                                <small>Con diferencias</small>
                                <strong>{{ number_format($item->diferencias) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="aud-card-footer">
                        <a class="aud-btn primary"
                           href="{{ route($ruta, ['agencia_id' => $item->id]) }}">
                            Ver receptores auditados
                        </a>
                    </div>
                </article>
            @empty
                <div class="aud-empty">No hay agencias registradas.</div>
            @endforelse
        </div>

    @elseif($nivel === 'receptores')

        <div class="aud-stats">
            <div class="aud-stat">
                <small>Receptores auditados</small>
                <strong>{{ number_format($resumen->receptores) }}</strong>
            </div>
            <div class="aud-stat">
                <small>Arqueos de auditoría</small>
                <strong>{{ number_format($resumen->arqueos) }}</strong>
            </div>
            <div class="aud-stat">
                <small>Auditorías generales sin receptor</small>
                <strong>{{ number_format($resumen->generales) }}</strong>
            </div>
            <div class="aud-stat">
                <small>Arqueos con diferencias</small>
                <strong>{{ number_format($resumen->diferencias) }}</strong>
            </div>
        </div>

        <div class="aud-grid">
            @foreach($receptores as $item)
                @php
                    $nombre = trim(
                        ($item->nombres ?? '').' '.($item->apellidos ?? '')
                    ) ?: (
                        $item->nombre_historico
                        ?: ($item->usuario ?? 'Receptor')
                    );
                @endphp

                <article class="aud-card">
                    <div class="aud-card-header">
                        <small>RECEPTOR · {{ $item->usuario ?? 'Sin usuario' }}</small>
                        <h3>{{ $nombre }}</h3>
                        <p>
                            Último arqueo:
                            {{ $item->ultima_fecha
                                ? \Carbon\Carbon::parse($item->ultima_fecha)->format('d/m/Y')
                                : 'Sin fecha' }}
                        </p>
                    </div>

                    <div class="aud-card-body">
                        <div class="aud-metrics">
                            <div class="aud-metric">
                                <small>Total auditorías</small>
                                <strong>{{ number_format($item->total) }}</strong>
                            </div>
                            <div class="aud-metric">
                                <small>Certificados</small>
                                <strong>{{ number_format($item->certificados) }}</strong>
                            </div>
                            <div class="aud-metric">
                                <small>Anulados</small>
                                <strong>{{ number_format($item->anulados) }}</strong>
                            </div>
                            <div class="aud-metric">
                                <small>Con diferencias</small>
                                <strong>{{ number_format($item->diferencias) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="aud-card-footer">
                        <a class="aud-btn primary"
                           href="{{ route($ruta, [
                               'agencia_id' => $agencia->id,
                               'receptor_id' => $item->id
                           ]) }}">
                            Ver historial completo
                        </a>
                    </div>
                </article>
            @endforeach

            @if($resumen->generales > 0)
                <article class="aud-card">
                    <div class="aud-card-header">
                        <small>AUDITORÍA INSTITUCIONAL</small>
                        <h3>Arqueos generales de agencia</h3>
                        <p>Sin receptor individual asociado</p>
                    </div>

                    <div class="aud-card-body">
                        <div class="aud-metric">
                            <small>Arqueos</small>
                            <strong>{{ number_format($resumen->generales) }}</strong>
                        </div>
                    </div>

                    <div class="aud-card-footer">
                        <a class="aud-btn primary"
                           href="{{ route($ruta, [
                               'agencia_id' => $agencia->id,
                               'grupo' => 'agencia'
                           ]) }}">
                            Ver historial
                        </a>
                    </div>
                </article>
            @endif
        </div>

        @if($receptores->isEmpty() && !$resumen->generales)
            <div class="aud-panel">
                <div class="aud-empty">
                    Esta agencia todavía no tiene arqueos de auditoría.
                </div>
            </div>
        @endif

    @else

        <div class="aud-stats">
            <div class="aud-stat">
                <small>Total arqueos</small>
                <strong>{{ number_format($resumen->total) }}</strong>
            </div>
            <div class="aud-stat">
                <small>Certificados</small>
                <strong>{{ number_format($resumen->certificados) }}</strong>
            </div>
            <div class="aud-stat">
                <small>Anulados</small>
                <strong>{{ number_format($resumen->anulados) }}</strong>
            </div>
            <div class="aud-stat">
                <small>Con diferencias</small>
                <strong>{{ number_format($resumen->diferencias) }}</strong>
            </div>
        </div>

        <section class="aud-panel">
            <div class="aud-panel-title">
                Historial completo de arqueos de auditoría
            </div>

            <form method="GET"
                  action="{{ route($ruta) }}"
                  class="aud-filters">

                <input type="hidden"
                       name="agencia_id"
                       value="{{ $agencia->id }}">

                @if($grupo === 'agencia')
                    <input type="hidden" name="grupo" value="agencia">
                @else
                    <input type="hidden"
                           name="receptor_id"
                           value="{{ $receptor->id }}">
                @endif

                <div class="aud-field">
                    <label>Estado</label>
                    <select name="estado">
                        <option value="">Todos</option>
                        @foreach($estados as $opcion)
                            <option value="{{ $opcion }}"
                                @selected($estado === $opcion)>
                                {{ str_replace('_', ' ', $opcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="aud-field">
                    <label>Desde</label>
                    <input type="date" name="desde" value="{{ $desde }}">
                </div>

                <div class="aud-field">
                    <label>Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta }}">
                </div>

                <div class="aud-field">
                    <label>Buscar arqueo o auditor</label>
                    <input type="search"
                           name="buscar"
                           value="{{ $buscar }}"
                           maxlength="120"
                           placeholder="Número, ventanilla, auditor...">
                </div>

                <div class="aud-filter-actions">
                    <button class="aud-btn primary" type="submit">
                        Filtrar
                    </button>

                    <a class="aud-btn"
                       href="{{ route($ruta,
                           $grupo === 'agencia'
                           ? [
                               'agencia_id' => $agencia->id,
                               'grupo' => 'agencia'
                             ]
                           : [
                               'agencia_id' => $agencia->id,
                               'receptor_id' => $receptor->id
                             ]
                       ) }}">
                        Limpiar
                    </a>
                </div>
            </form>

            <div class="aud-table-wrap">
                <table class="aud-table">
                    <thead>
                        <tr>
                            <th>Arqueo</th>
                            <th>Fecha / Turno</th>
                            <th>Auditor</th>
                            <th>Estado</th>
                            <th class="aud-right">Arqueado</th>
                            <th class="aud-right">Corte</th>
                            <th class="aud-right">Diferencia</th>
                            <th>Hallazgos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($arqueos as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->numero_arqueo }}</strong>
                                    <div>
                                        {{ $item->ventanilla_codigo_historico ?: 'Sin ventanilla' }}
                                    </div>
                                </td>

                                <td>
                                    {{ $item->fecha_operativa
                                        ? \Carbon\Carbon::parse($item->fecha_operativa)->format('d/m/Y')
                                        : '—' }}
                                    <div>
                                        {{ $item->turno_nombre_historico ?: 'Sin turno' }}
                                    </div>
                                </td>

                                <td>
                                    {{ trim($item->auditor_nombre ?? '')
                                        ?: 'Sin firma de auditor registrada' }}
                                </td>

                                <td>
                                    <span class="aud-tag
                                        {{ $item->estado === 'ANULADO'
                                            ? 'danger'
                                            : ($item->estado === 'CERTIFICADO' ? 'ok' : '') }}">
                                        {{ str_replace('_', ' ', $item->estado) }}
                                    </span>
                                </td>

                                <td class="aud-right">
                                    Q {{ number_format((float) $item->total_arqueado, 2) }}
                                </td>

                                <td class="aud-right">
                                    {{ $item->total_corte === null
                                        ? '—'
                                        : 'Q '.number_format((float) $item->total_corte, 2) }}
                                </td>

                                <td class="aud-right">
                                    <strong>
                                        Q {{ number_format((float) $item->diferencia, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    {{ number_format($item->total_hallazgos) }}
                                </td>

                                <td>
                                    <div class="aud-actions">
                                        <a class="aud-btn"
                                           href="{{ route(
                                               'administrador.arqueos-agencias.show',
                                               ['arqueo' => $item->id]
                                           ) }}">
                                            Detalle
                                        </a>

                                        <a class="aud-btn"
                                           href="{{ route(
                                               'administrador.arqueos-agencias.imprimir',
                                               ['arqueo' => $item->id]
                                           ) }}"
                                           target="_blank"
                                           rel="noopener">
                                            PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="aud-empty">
                                    No hay arqueos que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($arqueos->hasPages())
                <div class="aud-pagination">
                    {{ $arqueos->links() }}
                </div>
            @endif
        </section>

    @endif
</div>
@endsection
