@php
    $tiposReporte = $tiposReporte ?? [];
    $catalogos = $catalogos ?? [];
    $filtros = $filtros ?? [];
    $columnas = $columnas ?? [];
    $metricas = $metricas ?? [];
    $resultados = $resultados ?? collect();

    $reporteActual = $filtros['reporte'] ?? 'resumen';
    $tituloReporte = $titulo ?? ($tiposReporte[$reporteActual] ?? 'Reportes de Agencias');

    $agencias = $catalogos['agencias'] ?? collect();
    $ventanillas = $catalogos['ventanillas'] ?? collect();
    $receptores = $catalogos['receptores'] ?? collect();
    $turnos = $catalogos['turnos'] ?? collect();
    $estados = $catalogos['estados'] ?? collect();
    $tipos = $catalogos['tipos'] ?? [];

    $cantidadResultados = method_exists($resultados, 'total')
        ? $resultados->total()
        : $resultados->count();

    $columnasMonetarias = [
        'total_corte',
        'total_arqueado',
        'diferencia',
        'monto_acumulado',
        'mayor_incidencia',
        'monto_faltantes',
        'monto_sobrantes',
    ];

    $nombresTipos = [
        'ORDINARIO' => 'Ordinario',
        'ENTREGA' => 'Entrega de Ventanilla',
        'SORPRESIVO' => 'Sorpresivo',
        'AUDITORIA' => 'Auditoría',
    ];

    $gruposReportes = [
        'Generales' => [
            'resumen',
            'historial-agencia',
            'historial-receptor',
            'historial-ventanilla',
            'historial-turno',
        ],
        'Control de Efectivo' => [
            'faltantes',
            'sobrantes',
            'exactos',
            'ranking-faltantes',
            'ranking-sobrantes',
            'diferencias-agencia',
        ],
        'Supervisión y Auditoría' => [
            'cumplimiento',
            'anulados',
            'sorpresivos',
            'auditoria',
        ],
    ];

    $descripcionesReportes = [
        'resumen' => 'Vista ejecutiva del período seleccionado.',
        'historial-agencia' => 'Consulta histórica de arqueos por agencia.',
        'historial-receptor' => 'Historial de arqueos por receptor.',
        'historial-ventanilla' => 'Movimientos y arqueos de cada ventanilla.',
        'historial-turno' => 'Arqueos registrados por turno operativo.',
        'faltantes' => 'Detalle de arqueos con diferencia negativa.',
        'sobrantes' => 'Detalle de arqueos con diferencia positiva.',
        'exactos' => 'Arqueos sin diferencias de efectivo.',
        'ranking-faltantes' => 'Mayor recurrencia de faltantes.',
        'ranking-sobrantes' => 'Mayor recurrencia de sobrantes.',
        'diferencias-agencia' => 'Comparativo de diferencias por agencia.',
        'cumplimiento' => 'Seguimiento de arqueos y obligaciones.',
        'anulados' => 'Historial de arqueos anulados.',
        'sorpresivos' => 'Control de arqueos sorpresivos.',
        'auditoria' => 'Arqueos realizados por Auditoría.',
    ];

    $queryActual = request()->query();

   $urlPdf = route('agencias.reportes.pdf');
    $urlCsv = route('agencias.reportes.csv');
@endphp

@extends($layoutReportes)

@section('title', 'Centro de Reportes de Agencias')

@section('content')
<div class="reportes-agencias">

    <header class="ra-header">
        <div>
            <h1>Centro de Reportes</h1>
            <p>Analice faltantes, sobrantes, exactitud, historiales y cumplimiento operativo por agencia, receptor, ventanilla y turno.</p>
        </div>
    </header>

    <section class="ra-catalogo">
        <div class="ra-report-grid">
            @foreach($gruposReportes as $grupo => $reportesGrupo)
                @foreach($reportesGrupo as $codigo)
                    @if(isset($tiposReporte[$codigo]))
                        <a
                            href="{{ url()->current() . '?' . http_build_query(array_merge($queryActual, ['reporte' => $codigo, 'page' => 1])) }}"
                            class="ra-report-card {{ $reporteActual === $codigo ? 'is-active' : '' }}"
                        >
                            <strong>{{ $tiposReporte[$codigo] }}</strong>
                            <span>{{ $descripcionesReportes[$codigo] ?? 'Consulte el detalle del reporte.' }}</span>
                        </a>
                    @endif
                @endforeach
            @endforeach
        </div>
    </section>

    <section class="ra-section">
        <form
            action="{{ url()->current() }}"
            method="GET"
            class="ra-filter-form"
        >
            <input
                type="hidden"
                name="reporte"
                value="{{ $reporteActual }}"
            >

            <div class="ra-filter-grid">
                <div class="ra-field">
                    <label for="ra_agencia">Agencia</label>

                    <select id="ra_agencia" name="agencia_id">
                        <option value="">Todas las autorizadas</option>

                        @foreach($agencias as $agencia)
                            <option
                                value="{{ $agencia->id }}"
                                @selected((int) ($filtros['agencia_id'] ?? 0) === (int) $agencia->id)
                            >
                                {{ $agencia->codigo }} - {{ $agencia->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ra-field">
                    <label for="ra_receptor">Receptor</label>

                    <select id="ra_receptor" name="receptor_id">
                        <option value="">Todos los receptores</option>

                        @foreach($receptores as $receptor)
                            <option
                                value="{{ $receptor->id }}"
                                @selected((int) ($filtros['receptor_id'] ?? 0) === (int) $receptor->id)
                            >
                                {{ $receptor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ra-field">
                    <label for="ra_ventanilla">Ventanilla</label>

                    <select id="ra_ventanilla" name="ventanilla_id">
                        <option value="">Todas las ventanillas</option>

                        @foreach($ventanillas as $ventanilla)
                            <option
                                value="{{ $ventanilla->id }}"
                                data-agencia="{{ $ventanilla->agencia_id }}"
                                @selected((int) ($filtros['ventanilla_id'] ?? 0) === (int) $ventanilla->id)
                            >
                                {{ $ventanilla->codigo }}
                                @if($ventanilla->descripcion)
                                    - {{ $ventanilla->descripcion }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ra-field">
                    <label for="ra_turno">Turno</label>

                    <select id="ra_turno" name="turno">
                        <option value="">Todos los turnos</option>

                        @foreach($turnos as $turno)
                            <option
                                value="{{ $turno }}"
                                @selected(($filtros['turno'] ?? '') === $turno)
                            >
                                {{ $turno }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ra-field">
                    <label for="ra_tipo">Tipo de arqueo</label>

                    <select id="ra_tipo" name="tipo">
                        <option value="">Todos los tipos</option>

                        @foreach($tipos as $codigo => $nombre)
                            <option
                                value="{{ $codigo }}"
                                @selected(($filtros['tipo'] ?? '') === $codigo)
                            >
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ra-field">
                    <label for="ra_estado">Estado</label>

                    <select id="ra_estado" name="estado">
                        <option value="">Todos los estados</option>

                        @foreach($estados as $estado)
                            <option
                                value="{{ $estado }}"
                                @selected(($filtros['estado'] ?? '') === $estado)
                            >
                                {{ str_replace('_', ' ', $estado) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ra-field">
                    <label for="ra_desde">Fecha desde</label>

                    <input
                        type="date"
                        id="ra_desde"
                        name="desde"
                        value="{{ $filtros['desde'] ?? '' }}"
                    >
                </div>

                <div class="ra-field">
                    <label for="ra_hasta">Fecha hasta</label>

                    <input
                        type="date"
                        id="ra_hasta"
                        name="hasta"
                        value="{{ $filtros['hasta'] ?? '' }}"
                    >
                </div>

                <div class="ra-field">
                    <label for="ra_por_pagina">Registros por página</label>

                    <select id="ra_por_pagina" name="por_pagina">
                        @foreach([10, 20, 50, 100] as $cantidad)
                            <option
                                value="{{ $cantidad }}"
                                @selected((int) ($filtros['por_pagina'] ?? 20) === $cantidad)
                            >
                                {{ $cantidad }} registros
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="ra-filter-actions">
                <a
                    href="{{ url()->current() . '?reporte=' . urlencode($reporteActual) }}"
                    class="ra-btn ra-btn-light"
                >
                    Limpiar filtros
                </a>

                <a href="{{ $urlPdf . '?' . http_build_query($queryActual) }}" target="_blank" rel="noopener" class="ra-btn ra-btn-pdf">Imprimir PDF</a>
                <button type="submit" class="ra-btn ra-btn-primary">
                    Generar reporte
                </button>
            </div>
        </form>
    </section>

    <div class="ra-summary-metrics">
        @if(count($metricas) > 0)
            <div class="ra-metrics-grid">
                @foreach($metricas as $nombre => $valor)
                    <div class="ra-metric">
                        <span class="ra-metric-label">
                            {{ $nombre }}
                        </span>

                        <strong class="ra-metric-value">
                            {{ is_numeric($valor) ? number_format((float) $valor, 0) : $valor }}
                        </strong>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <section class="ra-section">
        <div class="ra-results-header">
            <div>


                <h2>{{ $tituloReporte }}</h2>

                <p>
                    {{ number_format($cantidadResultados) }}
                    {{ $cantidadResultados === 1 ? 'registro encontrado' : 'registros encontrados' }}
                </p>
            </div>

            <div class="ra-export-actions">
                <a
                    href="{{ $urlPdf . '?' . http_build_query($queryActual) }}"
                    target="_blank"
                    rel="noopener"
                    class="ra-btn ra-btn-pdf"
                >
                    Exportar PDF
                </a>

                <a
                    href="{{ $urlCsv . '?' . http_build_query($queryActual) }}"
                    class="ra-btn ra-btn-csv"
                >
                    Exportar CSV
                </a>
            </div>
        </div>

        <div class="ra-table-container">
            <table class="ra-table">
                <thead>
                    <tr>
                        @foreach($columnas as $campo => $encabezado)
                            <th>{{ $encabezado }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($resultados as $indice => $registro)
                        <tr>
                            @foreach($columnas as $campo => $encabezado)
                                @php
                                    $valor = data_get($registro, $campo);

                                    if ($campo === 'posicion') {
                                        $valor = method_exists($resultados, 'firstItem')
                                            ? ($resultados->firstItem() + $indice)
                                            : ($indice + 1);
                                    }

                                    if ($campo === 'tipo') {
                                        $valor = $nombresTipos[$valor] ?? $valor;
                                    }

                                    if (
                                        $campo === 'fecha_operativa'
                                        && !empty($valor)
                                    ) {
                                        try {
                                            $valor = \Illuminate\Support\Carbon::parse($valor)
                                                ->format('d/m/Y');
                                        } catch (\Throwable $e) {
                                            // Mantener el valor original.
                                        }
                                    }

                                    if (
                                        in_array($campo, $columnasMonetarias, true)
                                        && $valor !== null
                                        && $valor !== ''
                                    ) {
                                        $valor = 'Q ' . number_format(
                                            (float) $valor,
                                            2,
                                            '.',
                                            ','
                                        );
                                    }

                                    $esDiferencia = $campo === 'diferencia';
                                    $diferencia = (float) data_get($registro, 'diferencia', 0);
                                @endphp

                                <td>
                                    @if($campo === 'estado')
                                        <span class="ra-status">
                                            {{ str_replace('_', ' ', (string) $valor) }}
                                        </span>
                                    @elseif($campo === 'cumplimiento')
                                        <span class="ra-status {{ $valor === 'CUMPLIDO' ? 'ra-status-success' : 'ra-status-warning' }}">
                                            {{ str_replace('_', ' ', (string) $valor) }}
                                        </span>
                                    @elseif($esDiferencia)
                                        <span class="{{ $diferencia < 0 ? 'ra-negative' : ($diferencia > 0 ? 'ra-positive' : 'ra-neutral') }}">
                                            {{ $valor }}
                                        </span>
                                    @else
                                        {{ $valor ?? '—' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ max(count($columnas), 1) }}">
                                <div class="ra-empty">

                                    <h3>No se encontraron registros</h3>

                                    <p>
                                        Modifique los filtros o seleccione otro
                                        período para realizar una nueva consulta.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($resultados, 'hasPages') && $resultados->hasPages())
            <div class="ra-pagination">
                <div>
                    Mostrando {{ $resultados->firstItem() }}
                    al {{ $resultados->lastItem() }}
                    de {{ $resultados->total() }} registros
                </div>

                <div class="ra-pagination-links">
                    @if($resultados->onFirstPage())
                        <span class="ra-page-disabled">Anterior</span>
                    @else
                        <a href="{{ $resultados->previousPageUrl() }}">
                            Anterior
                        </a>
                    @endif

                    <span class="ra-current-page">
                        Página {{ $resultados->currentPage() }}
                        de {{ $resultados->lastPage() }}
                    </span>

                    @if($resultados->hasMorePages())
                        <a href="{{ $resultados->nextPageUrl() }}">
                            Siguiente
                        </a>
                    @else
                        <span class="ra-page-disabled">Siguiente</span>
                    @endif
                </div>
            </div>
        @endif
    </section>
</div>
@endsection

@push('styles')
<style>
.reportes-agencias{max-width:1600px;margin:0 auto;padding:24px 22px 40px;color:#183454;font-family:inherit}
.reportes-agencias *{box-sizing:border-box}
.ra-header{margin:0 0 22px}
.ra-header h1{font-size:clamp(23px,2.5vw,30px);font-weight:700;color:#10365e;margin:0 0 8px}
.ra-header p{font-size:12px;color:#738697;line-height:1.55;margin:0}
.ra-catalogo{margin-bottom:20px}
.ra-report-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.ra-report-card{display:block;min-height:78px;padding:17px 15px;border:1px solid #dfe7ee;border-radius:15px;background:#fff;text-decoration:none!important;transition:.18s ease}
.ra-report-card:hover{transform:translateY(-2px);border-color:#b9cfe4;box-shadow:0 8px 22px rgba(21,68,112,.07)}
.ra-report-card.is-active{border-color:#164c96;background:#f2f7fd;box-shadow:inset 0 0 0 1px #164c96}
.ra-report-card strong{display:block;font-size:12px;font-weight:800;color:#0a3158;line-height:1.35}
.ra-report-card span{display:block;font-size:10px;color:#7b8b98;margin-top:7px;line-height:1.45}
.ra-section{background:#fff;border:1px solid #e0e8ee;border-radius:17px;margin-bottom:20px;box-shadow:0 8px 24px rgba(20,57,83,.045)}
.ra-filter-form{padding:18px}
.ra-filter-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px 14px}
.ra-field{min-width:0}
.ra-field label{display:none}
.ra-field select,.ra-field input{display:block;width:100%;min-height:42px;padding:0 12px;border:1px solid #ced9e1;border-radius:10px;background:#fff;color:#30485b;font:inherit;font-size:12px}
.ra-field select:focus,.ra-field input:focus{outline:2px solid #b9d5f1;outline-offset:0}
.ra-filter-actions{display:flex;justify-content:flex-end;align-items:center;gap:10px;flex-wrap:wrap;margin-top:14px}
.ra-btn{display:inline-flex;justify-content:center;align-items:center;min-height:40px;padding:0 15px;border:0;border-radius:9px;font-size:11px;font-weight:800;text-decoration:none!important;cursor:pointer}
.ra-btn-primary{background:#164c96;color:#fff!important}
.ra-btn-light{background:#edf2f5;color:#3d596f!important}
.ra-btn-pdf{background:#16834f;color:#fff!important}
.ra-btn-csv{background:#e8f5ed;color:#16834f!important;border:1px solid #c5e8d1}
.ra-summary-metrics:empty{display:none}
.ra-metrics-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;margin-bottom:20px}
.ra-metric{padding:15px;min-height:80px;background:#fff;border:1px solid #e1e8ed;border-radius:14px}
.ra-metric-label{display:block;color:#758697;font-size:9px;font-weight:800;text-transform:uppercase;line-height:1.45}
.ra-metric-value{display:block;color:#082d55;font-size:19px;font-weight:800;margin-top:7px;overflow-wrap:anywhere}
.ra-results-header{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap;padding:18px 20px;background:#fafcfd;border-bottom:1px solid #edf1f4}
.ra-results-header h2{font-size:15px;color:#0a3158;font-weight:700;margin:0 0 5px}
.ra-results-header p{font-size:10px;color:#82909a;margin:0}
.ra-export-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
.ra-table-container{overflow-x:auto}
.ra-table{width:100%;min-width:900px;border-collapse:collapse}
.ra-table th,.ra-table td{padding:12px 13px;border-bottom:1px solid #edf1f4;text-align:left;vertical-align:middle;font-size:11px;color:#30485b}
.ra-table th{background:#f7f9fb;color:#687b8b;font-size:9px;text-transform:uppercase;font-weight:800}
.ra-table tbody tr:hover{background:#f8fbff}
.ra-status{display:inline-block;padding:4px 8px;background:#eef3f8;color:#53677d;border-radius:6px;font-size:10px;font-weight:800}
.ra-status-success{background:#e5f6ed;color:#217a49}.ra-status-warning{background:#fff3dd;color:#9a6815}
.ra-negative{color:#b33a34;font-weight:800}.ra-positive{color:#16834f;font-weight:800}.ra-neutral{color:#607586;font-weight:800}
.ra-empty{text-align:center;padding:35px 15px}.ra-empty h3{font-size:14px;color:#30485b}.ra-empty p{font-size:11px;color:#758697}
.ra-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:16px 18px;font-size:11px;color:#6c8095}
.ra-pagination-links{display:flex;align-items:center;gap:7px}.ra-pagination-links a,.ra-page-disabled,.ra-current-page{padding:8px 10px;border:1px solid #dfe7ee;border-radius:7px;text-decoration:none;color:#164c96}.ra-page-disabled{color:#9aa8b6}
@media(max-width:1200px){.ra-report-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.ra-metrics-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:800px){.ra-report-grid,.ra-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.reportes-agencias{padding:16px 12px 30px}.ra-report-grid,.ra-filter-grid,.ra-metrics-grid{grid-template-columns:1fr}.ra-filter-actions{flex-direction:column;align-items:stretch}.ra-filter-actions .ra-btn{width:100%}}
@media print{.ra-catalogo,.ra-filter-form,.ra-export-actions,.ra-pagination{display:none!important}.reportes-agencias{padding:0}.ra-section{border:0;box-shadow:none}}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const agencia = document.getElementById('ra_agencia');
    const ventanilla = document.getElementById('ra_ventanilla');

    if (!agencia || !ventanilla) {
        return;
    }

    function actualizarVentanillas() {
        const agenciaSeleccionada = agencia.value;

        Array.from(ventanilla.options).forEach(function (opcion) {
            if (!opcion.value) {
                return;
            }

            const agenciaOpcion = opcion.dataset.agencia;

            opcion.hidden = Boolean(
                agenciaSeleccionada &&
                agenciaOpcion !== agenciaSeleccionada
            );
        });

        const opcionActual = ventanilla.selectedOptions[0];

        if (opcionActual && opcionActual.hidden) {
            ventanilla.value = '';
        }
    }

    agencia.addEventListener('change', actualizarVentanillas);

    actualizarVentanillas();
});
</script>
@endpush
