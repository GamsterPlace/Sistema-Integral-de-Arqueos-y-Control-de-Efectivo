
@php
    $layout = collect(
        $rutaBase === 'jefecaja'
            ? ['layouts.jefecaja', 'layouts.jefe-caja', 'layouts.jefe_caja']
            : ['layouts.subjefecaja', 'layouts.subjefe-caja', 'layouts.subjefe_caja']
    )->first(fn ($nombre) => view()->exists($nombre));

    if (!$layout) {
        throw new \RuntimeException('No se encontró el layout del rol.');
    }

    $pendientes = $pendientes ?? null;
    $ventanillas = collect($ventanillas ?? []);

    $moneda = fn ($valor) =>
        'Q '.number_format((float) ($valor ?? 0), 2, '.', ',');
@endphp

@extends($layout)

@section('title', 'Arqueos y Control de Certificaciones')

@push('styles')
<style>
    .acc-page{display:grid;gap:23px;color:#193b5c}
    .acc-page *{box-sizing:border-box}
    .acc-header{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap}
    .acc-header h1{margin:0;color:#06284f;font-size:27px;font-weight:800}
    .acc-header p{margin:8px 0 0;color:#6b8195;font-size:13px;line-height:1.6}
    .acc-back{display:inline-flex;align-items:center;padding:11px 15px;border:1px solid #d7e3ed;border-radius:9px;background:#fff;color:#164c96;text-decoration:none;font-size:12px;font-weight:800}
    .acc-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
    .acc-summary-item{background:#fff;border:1px solid #dfe7ee;border-radius:13px;padding:18px 20px}
    .acc-summary-item span{display:block;color:#728599;font-size:12px;font-weight:700}
    .acc-summary-item strong{display:block;margin-top:8px;color:#06284f;font-size:27px}
    .acc-summary-item.pending strong{color:#a46b18}
    .acc-panel{background:#fff;border:1px solid #dfe7ee;border-radius:14px;padding:22px;box-shadow:0 5px 18px rgba(6,40,79,.04)}
    .acc-panel-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:15px}
    .acc-panel-head h2{margin:0;color:#06284f;font-size:19px}
    .acc-panel-head p{margin:6px 0 0;color:#728599;font-size:12px;line-height:1.6}
    .acc-table-wrap{overflow-x:auto}
    .acc-table{width:100%;border-collapse:collapse;text-align:left;font-size:12px}
    .acc-table th{background:#f3f7fb;padding:12px 10px;color:#597187;font-size:11px;white-space:nowrap}
    .acc-table td{padding:13px 10px;border-bottom:1px solid #e9eff5;vertical-align:middle}
    .acc-table tbody tr:last-child td{border-bottom:0}
    .acc-table tbody tr:hover{background:#fafcff}
    .acc-number{font-weight:800;color:#164c96}
    .acc-muted{display:block;color:#7b8da0;font-size:11px;margin-top:4px}
    .acc-money{font-weight:800;white-space:nowrap}
    .acc-money.negative{color:#ae3939}
    .acc-money.positive{color:#a46b18}
    .acc-tag{display:inline-block;border-radius:999px;padding:5px 9px;font-size:10px;font-weight:800;background:#edf3fa;color:#526d88}
    .acc-tag.pending{background:#fff2d9;color:#98600f}
    .acc-tag.certified{background:#e7f6ed;color:#167344}
    .acc-tag.cancelled{background:#fdecec;color:#a33838}
    .acc-button{display:inline-flex;justify-content:center;align-items:center;padding:9px 13px;border-radius:8px;background:#164c96;color:#fff!important;text-decoration:none;font-size:11px;font-weight:800;white-space:nowrap}
    .acc-button:hover{background:#0c3976}
    .acc-button.secondary{background:#e9f2fb;color:#164c96!important}
    .acc-empty{padding:25px 16px;border:1px dashed #d4e1ec;border-radius:10px;background:#f8fbfe;text-align:center;color:#718398;font-size:13px}
    .acc-empty strong{display:block;color:#274966;margin-bottom:5px}
    .acc-pagination{margin-top:18px}
    .acc-cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}
    .acc-card{border:1px solid #dce6ee;border-radius:13px;background:#fff;overflow:hidden}
    .acc-card-head{background:#f5f9fd;border-bottom:1px solid #e0eaf2;padding:17px 18px}
    .acc-card-head h3{margin:0;color:#123f67;font-size:17px}
    .acc-card-head p{margin:5px 0 0;color:#73879a;font-size:12px}
    .acc-card-body{padding:17px 18px}
    .acc-assignee{padding:12px 14px;background:#f3f8fc;border:1px solid #e1ebf3;border-radius:9px;margin-bottom:17px}
    .acc-assignee span{display:block;color:#708498;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.03em}
    .acc-assignee strong{display:block;margin-top:5px;color:#153f66;font-size:14px;overflow-wrap:anywhere}
    .acc-card-subtitle{margin:0 0 10px;color:#526d83;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
    .acc-recent{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:12px 0;border-bottom:1px solid #edf1f5}
    .acc-recent:last-child{border-bottom:0}
    .acc-recent-main{min-width:0}
    .acc-recent-main strong{display:block;color:#174b78;font-size:12px;overflow-wrap:anywhere}
    .acc-recent-main small{display:block;margin-top:4px;color:#728599;font-size:11px;line-height:1.5}
    .acc-card-footer{padding:0 18px 18px}
    .acc-card-footer .acc-button{width:100%;padding:12px}
    @media(max-width:900px){
        .acc-cards{grid-template-columns:1fr}
    }
    @media(max-width:650px){
        .acc-summary{grid-template-columns:1fr}
        .acc-panel{padding:16px}
        .acc-header h1{font-size:23px}
    }
</style>
@endpush

@section('content')
<div class="acc-page">

    <header class="acc-header">
        <div>
            <h1>Arqueos y Control de Certificaciones</h1>
            <p>
                Revisión de arqueos pendientes y seguimiento
                por ventanilla de las agencias bajo su responsabilidad.
            </p>
        </div>

        <a class="acc-back" href="{{ route($rutaBase.'.dashboard') }}">
            ← Volver al Dashboard
        </a>
    </header>

    <div class="acc-summary">
        <div class="acc-summary-item pending">
            <span>Pendientes de certificación</span>
            <strong>
                {{ $pendientes ? $pendientes->total() : '—' }}
            </strong>
        </div>

        <div class="acc-summary-item">
            <span>Ventanillas supervisadas</span>
            <strong>{{ $ventanillas->count() }}</strong>
        </div>

        <div class="acc-summary-item">
            <span>Últimos arqueos visibles</span>
            <strong>
                {{ $ventanillas->sum(
                    fn ($v) => collect($v->ultimosArqueos ?? [])->count()
                ) }}
            </strong>
        </div>
    </div>

    <section class="acc-panel">
        <div class="acc-panel-head">
            <div>
                <h2>Arqueos pendientes de certificación</h2>
                <p>
                    Únicamente registros pendientes,
                    ordenados para su revisión y firma electrónica.
                </p>
            </div>
        </div>

        @if(!$pendientes)
            <div class="acc-empty">
                <strong>Consulta de pendientes no configurada</strong>
                El controlador debe proporcionar la variable pendientes.
            </div>
        @elseif($pendientes->isEmpty())
            <div class="acc-empty">
                <strong>Sin arqueos pendientes</strong>
                No existen arqueos pendientes de certificación
                en las agencias autorizadas.
            </div>
        @else
            <div class="acc-table-wrap">
                <table class="acc-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Agencia / Ventanilla</th>
                            <th>Receptor</th>
                            <th>Total arqueado</th>
                            <th>Diferencia</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendientes as $arqueo)
                            @php
                                $diferencia = (float) ($arqueo->diferencia ?? 0);
                            @endphp

                            <tr>
                                <td>
                                    <span class="acc-number">
                                        {{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}
                                    </span>
                                </td>

                                <td>
                                    {{ $arqueo->fecha_operativa
                                        ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y')
                                        : '—' }}
                                </td>

                                <td>
                                    {{ $arqueo->agencia_nombre_historico ?: '—' }}
                                    <span class="acc-muted">
                                        Ventanilla
                                        {{ $arqueo->ventanilla_codigo_historico ?: '—' }}
                                    </span>
                                </td>

                                <td>
                                    {{ $arqueo->receptor_nombre_historico ?: 'No disponible' }}
                                </td>

                                <td>
                                    <span class="acc-money">
                                        {{ $moneda($arqueo->total_arqueado) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="acc-money {{ $diferencia < 0 ? 'negative' : ($diferencia > 0 ? 'positive' : '') }}">
                                        {{ $diferencia < 0 ? '-' : '' }}{{ $moneda(abs($diferencia)) }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        class="acc-button"
                                        href="{{ route($rutaBase.'.certificaciones.show', $arqueo->id) }}"
                                    >
                                        Revisar y certificar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="acc-pagination">
                {{ $pendientes->links() }}
            </div>
        @endif
    </section>

    <section class="acc-panel">
        <div class="acc-panel-head">
            <div>
                <h2>Control de arqueos por ventanilla</h2>
                <p>
                    Receptor actualmente asignado, últimos tres arqueos
                    y acceso al historial de cada ventanilla.
                </p>
            </div>
        </div>

        <div class="acc-cards">
            @forelse($ventanillas as $ventanilla)
                @php
                    $receptorAsignado = $ventanilla->receptorAsignado ?? null;

                    $nombreReceptor = $receptorAsignado
                        ? (
                            data_get($receptorAsignado, 'nombre_completo')
                            ?: data_get($receptorAsignado, 'nombre')
                            ?: data_get($receptorAsignado, 'usuario.nombre_completo')
                            ?: data_get($receptorAsignado, 'usuario.nombre')
                        )
                        : null;

                    $recientes = collect(
                        $ventanilla->ultimosArqueos ?? []
                    )->take(3);
                @endphp

                <article class="acc-card">
                    <div class="acc-card-head">
                        <h3>
                            Ventanilla
                            {{ $ventanilla->codigo ?? $ventanilla->id }}
                        </h3>

                        <p>
                            {{ data_get($ventanilla, 'agencia.nombre')
                                ?: ($ventanilla->agencia_nombre ?? 'Agencia no disponible') }}
                        </p>
                    </div>

                    <div class="acc-card-body">
                        <div class="acc-assignee">
                            <span>Receptor asignado actualmente</span>
                            <strong>
                                {{ $nombreReceptor ?: 'Sin receptor asignado' }}
                            </strong>
                        </div>

                        <h4 class="acc-card-subtitle">
                            Últimos tres arqueos
                        </h4>

                        @forelse($recientes as $arqueo)
                            <div class="acc-recent">
                                <div class="acc-recent-main">
                                    <strong>
                                        {{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}
                                    </strong>

                                    <small>
                                        {{ $arqueo->fecha_operativa
                                            ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y')
                                            : '—' }}
                                        ·
                                        {{ $arqueo->receptor_nombre_historico
                                            ?: 'Receptor no disponible' }}
                                    </small>

                                    <small>
                                        {{ $moneda($arqueo->total_arqueado) }}
                                        ·
                                        <span class="acc-tag {{ $arqueo->estado === 'CERTIFICADO' ? 'certified' : ($arqueo->estado === 'PENDIENTE' ? 'pending' : 'cancelled') }}">
                                            {{ $arqueo->estado }}
                                        </span>
                                    </small>
                                </div>

                                <a
                                    class="acc-button secondary"
                                    href="{{ route($rutaBase.'.certificaciones.show', $arqueo->id) }}"
                                >
                                    Consultar
                                </a>
                            </div>
                        @empty
                            <div class="acc-empty">
                                No existen arqueos registrados
                                en esta ventanilla.
                            </div>
                        @endforelse
                    </div>

                    <div class="acc-card-footer">
                        @if(\Illuminate\Support\Facades\Route::has($rutaBase.'.certificaciones.historial'))
                            <a
                                class="acc-button"
                                href="{{ route(
                                    $rutaBase.'.certificaciones.historial',
                                    ['ventanilla' => $ventanilla->id]
                                ) }}"
                            >
                                Historial de arqueos
                            </a>
                        @else
                            <span
                                class="acc-button secondary"
                                style="cursor:not-allowed;opacity:.65"
                            >
                                Historial pendiente de configurar
                            </span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="acc-empty">
                    <strong>Sin ventanillas disponibles</strong>
                    No hay ventanillas cargadas para las agencias
                    bajo su responsabilidad.
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
