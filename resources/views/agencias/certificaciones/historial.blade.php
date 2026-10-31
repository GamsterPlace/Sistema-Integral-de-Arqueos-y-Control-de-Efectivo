
@php
    $layout = collect(
        $rutaBase === 'jefecaja'
            ? ['layouts.jefecaja', 'layouts.jefe-caja', 'layouts.jefe_caja']
            : ['layouts.subjefecaja', 'layouts.subjefe-caja', 'layouts.subjefe_caja']
    )->first(fn ($nombre) => view()->exists($nombre));

    if (!$layout) {
        throw new \RuntimeException('No se encontró el layout del rol.');
    }

    $moneda = fn ($valor) =>
        'Q '.number_format((float) ($valor ?? 0), 2, '.', ',');
@endphp

@extends($layout)

@section('title', 'Historial de arqueos por ventanilla')

@push('styles')
<style>
    .ah-page {
        display: grid;
        gap: 22px;
        color: #193b5c;
    }

    .ah-page * {
        box-sizing: border-box;
    }

    .ah-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        flex-wrap: wrap;
    }

    .ah-header h1 {
        margin: 0;
        color: #06284f;
        font-size: 27px;
        font-weight: 800;
    }

    .ah-header p {
        margin: 8px 0 0;
        color: #718398;
        font-size: 13px;
        line-height: 1.6;
    }

    .ah-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 11px 16px;
        border-radius: 9px;
        border: 0;
        background: #164c96;
        color: #fff !important;
        text-decoration: none;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
    }

    .ah-button:hover {
        background: #0c3976;
    }

    .ah-button.secondary {
        background: #fff;
        border: 1px solid #d7e3ed;
        color: #164c96 !important;
    }

    .ah-panel {
        background: #fff;
        border: 1px solid #dfe7ee;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 5px 18px rgba(6, 40, 79, .04);
    }

    .ah-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .ah-panel-head h2 {
        margin: 0;
        font-size: 19px;
        color: #06284f;
    }

    .ah-panel-head p {
        margin: 6px 0 0;
        color: #728599;
        font-size: 12px;
    }

    .ah-filters {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr)) auto auto;
        gap: 12px;
        align-items: end;
    }

    .ah-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 11px;
        font-weight: 800;
        color: #526d83;
    }

    .ah-field input {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #cfdeeb;
        border-radius: 8px;
        background: #fff;
        color: #25465f;
        font-size: 13px;
    }

    .ah-help {
        margin: 14px 0 0;
        color: #74889a;
        font-size: 12px;
        line-height: 1.6;
    }

    .ah-error {
        padding: 12px 14px;
        margin-bottom: 15px;
        background: #fff2f2;
        border: 1px solid #efcaca;
        border-radius: 9px;
        color: #aa3535;
        font-size: 12px;
    }

    .ah-table-wrap {
        overflow-x: auto;
    }

    .ah-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 12px;
    }

    .ah-table th {
        padding: 13px 11px;
        background: #f3f7fb;
        color: #597187;
        font-size: 11px;
        white-space: nowrap;
    }

    .ah-table td {
        padding: 14px 11px;
        border-bottom: 1px solid #e9eff5;
        vertical-align: middle;
    }

    .ah-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .ah-table tbody tr:hover {
        background: #fafcff;
    }

    .ah-number {
        font-weight: 800;
        color: #164c96;
    }

    .ah-money {
        font-weight: 800;
        white-space: nowrap;
    }

    .ah-money.negative {
        color: #ae3939;
    }

    .ah-money.positive {
        color: #a46b18;
    }

    .ah-tag {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        background: #edf3fa;
        color: #526d88;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .ah-tag.pending {
        background: #fff2d9;
        color: #98600f;
    }

    .ah-tag.certified {
        background: #e7f6ed;
        color: #167344;
    }

    .ah-tag.cancelled {
        background: #fdecec;
        color: #a33838;
    }

    .ah-empty {
        padding: 30px 16px !important;
        text-align: center;
        color: #718398;
        font-size: 13px;
    }

    .ah-pagination {
        margin-top: 20px;
    }

    @media (max-width: 1000px) {
        .ah-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .ah-filters {
            grid-template-columns: 1fr;
        }

        .ah-panel {
            padding: 16px;
        }

        .ah-header h1 {
            font-size: 23px;
        }
    }
</style>
@endpush

@section('content')
<div class="ah-page">

    <header class="ah-header">
        <div>
            <h1>Historial de arqueos</h1>
            <p>
                Ventanilla {{ $ventanilla->codigo ?? $ventanilla->id }}
                ·
                {{ data_get($ventanilla, 'agencia.nombre') ?? 'Agencia no disponible' }}
            </p>
        </div>

        <a
            class="ah-button secondary"
            href="{{ route($rutaBase.'.certificaciones.index') }}"
        >
            ← Volver a arqueos y certificaciones
        </a>
    </header>

    <section class="ah-panel">
        <div class="ah-panel-head">
            <div>
                <h2>Buscar arqueos</h2>
                <p>
                    Consulte los registros históricos de esta ventanilla.
                </p>
            </div>
        </div>

        @if($errors->any())
            <div class="ah-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form
            method="GET"
            action="{{ route($rutaBase.'.certificaciones.historial', ['ventanilla' => $ventanilla->id]) }}"
            class="ah-filters"
        >
            <div class="ah-field">
                <label for="fecha">Fecha específica</label>
                <input
                    type="date"
                    id="fecha"
                    name="fecha"
                    value="{{ request('fecha') }}"
                >
            </div>

            <div class="ah-field">
                <label for="desde">Desde</label>
                <input
                    type="date"
                    id="desde"
                    name="desde"
                    value="{{ request('desde') }}"
                >
            </div>

            <div class="ah-field">
                <label for="hasta">Hasta</label>
                <input
                    type="date"
                    id="hasta"
                    name="hasta"
                    value="{{ request('hasta') }}"
                >
            </div>

            <button type="submit" class="ah-button">
                Buscar
            </button>

            <a
                class="ah-button secondary"
                href="{{ route($rutaBase.'.certificaciones.historial', ['ventanilla' => $ventanilla->id]) }}"
            >
                Limpiar
            </a>
        </form>

        <p class="ah-help">
            Para consultar un solo día, utilice Fecha específica.
            Para consultar un período, indique Desde y Hasta.
            La fecha específica tiene prioridad cuando se utilizan ambos filtros.
        </p>
    </section>

    <section class="ah-panel">
        <div class="ah-panel-head">
            <div>
                <h2>Arqueos registrados</h2>
                <p>
                    Resultados encontrados: {{ $arqueos->total() }}
                </p>
            </div>
        </div>

        <div class="ah-table-wrap">
            <table class="ah-table">
                <thead>
                    <tr>
                        <th>Número de arqueo</th>
                        <th>Fecha</th>
                        <th>Receptor</th>
                        <th>Total arqueado</th>
                        <th>Diferencia</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($arqueos as $arqueo)
                        @php
                            $diferencia = (float) ($arqueo->diferencia ?? 0);

                            $claseEstado = match ($arqueo->estado) {
                                'PENDIENTE' => 'pending',
                                'CERTIFICADO' => 'certified',
                                'ANULADO' => 'cancelled',
                                default => '',
                            };
                        @endphp

                        <tr>
                            <td>
                                <span class="ah-number">
                                    {{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}
                                </span>
                            </td>

                            <td>
                                {{ $arqueo->fecha_operativa
                                    ? \Illuminate\Support\Carbon::parse($arqueo->fecha_operativa)->format('d/m/Y')
                                    : '—' }}
                            </td>

                            <td>
                                {{ $arqueo->receptor_nombre_historico ?: 'No disponible' }}
                            </td>

                            <td>
                                <span class="ah-money">
                                    {{ $moneda($arqueo->total_arqueado) }}
                                </span>
                            </td>

                            <td>
                                <span class="ah-money {{ $diferencia < 0 ? 'negative' : ($diferencia > 0 ? 'positive' : '') }}">
                                    {{ $diferencia < 0 ? '-' : '' }}{{ $moneda(abs($diferencia)) }}
                                </span>
                            </td>

                            <td>
                                <span class="ah-tag {{ $claseEstado }}">
                                    {{ $arqueo->estado ?? 'Sin estado' }}
                                </span>
                            </td>

                            <td>
                                <a
                                    class="ah-button"
                                    href="{{ route($rutaBase.'.certificaciones.show', $arqueo->id) }}"
                                >
                                    Consultar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ah-empty">
                                No se encontraron arqueos registrados
                                para los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($arqueos->hasPages())
            <div class="ah-pagination">
                {{ $arqueos->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
