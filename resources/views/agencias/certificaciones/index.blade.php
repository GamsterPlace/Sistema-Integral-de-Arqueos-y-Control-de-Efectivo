
@php
    $layout = collect(
        $rutaBase === 'jefecaja'
            ? ['layouts.jefecaja', 'layouts.jefe-caja', 'layouts.jefe_caja']
            : ['layouts.subjefecaja', 'layouts.subjefe-caja', 'layouts.subjefe_caja']
    )->first(fn ($nombre) => view()->exists($nombre));

    if (!$layout) {
        throw new \RuntimeException(
            'No se encontró el layout del rol. Verifique el nombre del archivo en resources/views/layouts.'
        );
    }
@endphp

@extends($layout)

@section('title', 'Certificaciones de Arqueos')

@push('styles')
<style>
    .cert-page{display:grid;gap:22px}
    .cert-header{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap}
    .cert-header h1{margin:0;color:#06284f;font-size:28px;font-weight:800}
    .cert-header p{margin:8px 0 0;color:#687e93;font-size:14px;line-height:1.6}
    .cert-back{display:inline-flex;align-items:center;padding:10px 15px;border:1px solid #d8e5f2;border-radius:10px;background:#fff;color:#164c96;font-size:13px;font-weight:700;text-decoration:none}
    .cert-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}
    .cert-stat{background:#fff;border:1px solid #dfe7ee;border-radius:15px;padding:20px;box-shadow:0 5px 18px rgba(6,40,79,.04)}
    .cert-stat span{display:block;color:#718398;font-size:12px;font-weight:700}
    .cert-stat strong{display:block;margin-top:10px;color:#06284f;font-size:29px}
    .cert-stat.pending strong{color:#b07818}
    .cert-stat.done strong{color:#15804b}
    .cert-panel{background:#fff;border:1px solid #dfe7ee;border-radius:16px;padding:22px;box-shadow:0 5px 18px rgba(6,40,79,.04)}
    .cert-panel h2{margin:0 0 7px;color:#06284f;font-size:18px}
    .cert-panel p{color:#718398;font-size:13px;line-height:1.6}
    .cert-table-wrap{overflow-x:auto;margin-top:20px}
    .cert-table{width:100%;border-collapse:collapse;text-align:left;font-size:12px}
    .cert-table th{padding:13px 11px;background:#f3f7fb;color:#5b7187;font-weight:800;white-space:nowrap}
    .cert-table td{padding:14px 11px;border-bottom:1px solid #eaf0f5;vertical-align:middle}
    .cert-table tbody tr:hover{background:#fafcff}
    .cert-table tbody tr:last-child td{border-bottom:0}
    .cert-number{font-weight:800;color:#164c96}
    .cert-secondary{display:block;margin-top:4px;color:#8292a2;font-size:11px}
    .cert-money{font-weight:750;white-space:nowrap;color:#193b5c}
    .cert-money.negative{color:#b64040}
    .cert-money.positive{color:#b07818}
    .cert-badge{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:800;white-space:nowrap}
    .cert-badge.pending{background:#fff3db;color:#996315}
    .cert-badge.done{background:#e8f8ef;color:#147a47}
    .cert-badge.other{background:#edf3fa;color:#526d88}
    .cert-button{display:inline-flex;align-items:center;justify-content:center;background:#164c96;color:#fff;padding:9px 13px;border-radius:9px;text-decoration:none;font-size:12px;font-weight:750;white-space:nowrap}
    .cert-button:hover{background:#0b3973;color:#fff}
    .cert-empty{margin-top:20px;padding:35px 20px;text-align:center;border:1px dashed #d6e2ed;border-radius:12px;background:#f8fbfe}
    .cert-empty strong{display:block;color:#28496a;font-size:15px}
    .cert-empty p{margin-bottom:0}
    .cert-pagination{margin-top:20px}
    @media(max-width:850px){.cert-stats{grid-template-columns:1fr}}
    @media(max-width:600px){.cert-header h1{font-size:24px}.cert-panel{padding:16px}}
</style>
@endpush

@section('content')
@php
    $pendientesPagina = $arqueos->getCollection()
        ->where('estado', 'PENDIENTE')
        ->count();

    $certificadosPagina = $arqueos->getCollection()
        ->where('estado', 'CERTIFICADO')
        ->count();
@endphp

<div class="cert-page">

    <header class="cert-header">
        <div>
            <h1>Certificaciones de Arqueos</h1>
            <p>
                Supervise y certifique los arqueos ordinarios
                correspondientes a las agencias bajo su responsabilidad.
            </p>
        </div>

        <a href="{{ route($rutaBase.'.dashboard') }}" class="cert-back">
            ← Volver al Dashboard
        </a>
    </header>

    <div class="cert-stats">
        <div class="cert-stat">
            <span>Arqueos registrados</span>
            <strong>{{ $arqueos->total() }}</strong>
        </div>

        <div class="cert-stat pending">
            <span>Arqueos Pendientes de Certificación</span>
            <strong>{{ $pendientesPagina }}</strong>
        </div>

        <div class="cert-stat done">
            <span>Arqueos Certificados</span>
            <strong>{{ $certificadosPagina }}</strong>
        </div>
    </div>

    <section class="cert-panel">
        <h2>Control de Certificaciones</h2>
        <p>
            Los arqueos pendientes se presentan primero.
            Seleccione un registro para verificar sus importes,
            denominaciones, observaciones y firmas electrónicas.
        </p>

        @if($arqueos->isEmpty())
            <div class="cert-empty">
                <strong>No hay arqueos disponibles</strong>
                <p>
                    No se encontraron arqueos ordinarios pendientes
                    o certificados en sus agencias asignadas.
                </p>
            </div>
        @else
            <div class="cert-table-wrap">
                <table class="cert-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Agencia</th>
                            <th>Receptor</th>
                            <th>Total arqueado</th>
                            <th>Diferencia</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($arqueos as $arqueo)
                            <tr>
                                <td>
                                    <span class="cert-number">
                                        {{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}
                                    </span>
                                </td>

                                <td>
                                    {{ $arqueo->fecha_operativa?->format('d/m/Y') ?? '—' }}
                                </td>

                                <td>
                                    {{ $arqueo->agencia_nombre_historico ?: 'Agencia #'.$arqueo->agencia_id }}
                                </td>

                                <td>
                                    {{ $arqueo->receptor_nombre_historico ?: ($arqueo->receptor?->nombre_completo ?? 'No disponible') }}
                                </td>

                                <td>
                                    <span class="cert-money">
                                        Q {{ number_format((float)$arqueo->total_arqueado, 2) }}
                                    </span>
                                </td>

                                <td>
                                    @php
                                        $diferencia = (float)$arqueo->diferencia;
                                    @endphp

                                    <span class="cert-money {{ $diferencia < 0 ? 'negative' : ($diferencia > 0 ? 'positive' : '') }}">
                                        Q {{ number_format($diferencia, 2) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="cert-badge {{ $arqueo->estado === 'PENDIENTE' ? 'pending' : ($arqueo->estado === 'CERTIFICADO' ? 'done' : 'other') }}">
                                        {{ $arqueo->estado === 'PENDIENTE' ? 'Pendiente' : 'Certificado' }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="{{ route($rutaBase.'.certificaciones.show', $arqueo->id) }}"
                                        class="cert-button"
                                    >
                                        {{ $arqueo->estado === 'PENDIENTE' ? 'Revisar' : 'Consultar' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="cert-pagination">
                {{ $arqueos->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
