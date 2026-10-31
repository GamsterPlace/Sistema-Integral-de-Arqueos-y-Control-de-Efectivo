@php
    $esReceptor = ($ruta === 'receptor');
    $layout = match ($ruta) {
        'receptor' => 'layouts.receptor',
        'jefecaja' => 'layouts.jefe-caja',
        'subjefecaja' => 'layouts.subjefe-caja',
        default => abort(403),
    };
@endphp
@extends($layout)
@section('title', 'Arqueos de Jefatura de Caja')
@push('styles')
<style>
.jc-page{max-width:1250px;margin:auto;display:grid;gap:20px;color:#173653}.jc-page *{box-sizing:border-box}.jc-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px}.jc-head h1{font-size:27px;color:#092f58;margin:0 0 7px}.jc-muted{color:#698098;font-size:13px;line-height:1.6;margin:0}.jc-card{background:#fff;border:1px solid #dce7f1;border-radius:16px;padding:22px;box-shadow:0 6px 24px #12345a0b}.jc-btn{display:inline-flex;align-items:center;justify-content:center;background:#145a99;color:#fff!important;border:0;border-radius:10px;padding:11px 16px;text-decoration:none;font-size:13px;font-weight:750;cursor:pointer}.jc-btn-light{background:#edf4fc;color:#145a99!important}.jc-tablewrap{overflow-x:auto}.jc-table{border-collapse:collapse;width:100%;min-width:720px}.jc-table th{background:#f2f7fc;font-size:11px;text-transform:uppercase;color:#52718c;letter-spacing:.04em}.jc-table td,.jc-table th{padding:12px;text-align:left;border-bottom:1px solid #e4ecf4}.jc-table td{font-size:13px}.jc-table a{color:#145a99;font-weight:750;text-decoration:none}.jc-pill{display:inline-block;background:#edf3fa;color:#27547c;border-radius:30px;padding:5px 10px;font-size:11px;font-weight:750}.jc-positive{color:#13864e;font-weight:800}.jc-negative{color:#b43838;font-weight:800}.jc-neutral{color:#52677b;font-weight:800}.jc-actions{display:flex;flex-wrap:wrap;gap:10px}.jc-empty{padding:25px;text-align:center;color:#72869a}.jc-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.jc-receptor{min-width:0}.jc-receptor-head{display:flex;gap:12px;align-items:center;margin-bottom:17px}.jc-avatar{display:grid;place-items:center;flex-shrink:0;width:44px;height:44px;border-radius:13px;background:#e8f1fc;color:#145a99;font-size:20px;font-weight:800}.jc-receptor h2{margin:0 0 5px;color:#0d355c;font-size:17px;overflow-wrap:anywhere}.jc-receptor small{color:#72869a;font-size:12px}.jc-history{display:grid;gap:9px}.jc-entry{display:flex;justify-content:space-between;align-items:center;gap:10px;border:1px solid #e3edf6;border-radius:11px;padding:12px;flex-wrap:wrap}.jc-entry strong{font-size:12px;color:#163c60;display:block;margin-bottom:5px}.jc-entry span{font-size:12px}.jc-entry a{color:#145a99;font-size:12px;font-weight:800;text-decoration:none}.jc-card-footer{margin-top:17px;padding-top:14px;border-top:1px solid #e6edf5}.jc-card-footer a{font-size:12px;font-weight:800;color:#145a99;text-decoration:none}.jc-alert{background:#edf4fc;border:1px solid #d3e2f2;border-radius:12px;padding:15px;color:#49667f;font-size:13px}@media(max-width:850px){.jc-grid{grid-template-columns:1fr}}@media(max-width:600px){.jc-card{padding:16px}.jc-head h1{font-size:23px}}
</style>
@endpush
@section('content')
<div class="jc-page">
    <header class="jc-head">
        <div>
            <h1>Arqueos de Jefatura de Caja</h1>
            <p class="jc-muted">{{ $esReceptor ? 'Consulta tus arqueos de Jefatura, revisa sus detalles y firma los pendientes.' : 'Consulta los últimos tres arqueos de Jefatura de cada receptor asignado a tu agencia.' }}</p>
        </div>
        <div class="jc-actions">
            <a class="jc-btn jc-btn-light" href="{{ route($ruta.'.dashboard') }}">Volver al panel</a>
            @unless($esReceptor)
                <a class="jc-btn" href="{{ route($ruta.'.sorpresivos.create') }}">Nuevo arqueo de Jefatura</a>
            @endunless
        </div>
    </header>

    @if($esReceptor)
        <section class="jc-card">
            <h2>Mis arqueos de Jefatura</h2>
            <div class="jc-tablewrap">
                <table class="jc-table">
                    <thead><tr><th>Número</th><th>Fecha</th><th>Agencia</th><th>Diferencia</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @forelse($arqueos as $a)
                        <tr>
                            <td>{{ $a->numero_arqueo }}</td>
                            <td>{{ $a->fecha_operativa ? \Illuminate\Support\Carbon::parse($a->fecha_operativa)->format('d/m/Y') : '—' }}</td>
                            <td>{{ $a->agencia_nombre_historico ?: '—' }}</td>
                            <td>Q {{ number_format((float)$a->diferencia, 2) }}</td>
                            <td><span class="jc-pill">{{ $a->estado === 'PENDIENTE_FIRMA_RECEPTOR' ? 'Pendiente de firma' : str_replace('_', ' ', $a->estado) }}</span></td>
                            <td><a href="{{ route('receptor.sorpresivos.show', $a->id) }}">{{ $a->estado === 'PENDIENTE_FIRMA_RECEPTOR' ? 'Consultar y firmar' : 'Consultar detalle' }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="jc-empty">No tienes arqueos de Jefatura registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:18px">{{ $arqueos->links() }}</div>
        </section>
    @else
        @if($receptores->isEmpty())
            <div class="jc-alert">No hay receptores con asignación vigente en las agencias bajo tu responsabilidad.</div>
        @else
            <div class="jc-grid">
                @foreach($receptores as $receptor)
                    <section class="jc-card jc-receptor">
                        <div class="jc-receptor-head">
                            <div class="jc-avatar">{{ mb_strtoupper(mb_substr($receptor->nombre, 0, 1)) }}</div>
                            <div>
                                <h2>{{ $receptor->nombre }}</h2>
                                <small>Receptor · Agencia #{{ $receptor->agencia_id }}</small>
                            </div>
                        </div>
                        <div class="jc-history">
                            @forelse($receptor->arqueos as $a)
                                <div class="jc-entry">
                                    <div>
                                        <strong>{{ $a->numero_arqueo }}</strong>
                                        <span>{{ $a->fecha_operativa ? \Illuminate\Support\Carbon::parse($a->fecha_operativa)->format('d/m/Y') : '—' }}</span>
                                        <span> · </span>
                                        <span class="{{ (float)$a->diferencia > 0 ? 'jc-positive' : ((float)$a->diferencia < 0 ? 'jc-negative' : 'jc-neutral') }}">{{ (float)$a->diferencia > 0 ? '+' : '' }}Q {{ number_format((float)$a->diferencia, 2) }}</span>
                                        <div style="margin-top:5px"><span class="jc-pill">{{ $a->estado === 'PENDIENTE_FIRMA_RECEPTOR' ? 'Pendiente de firma del receptor' : str_replace('_', ' ', $a->estado) }}</span></div>
                                    </div>
                                    <a href="{{ route($ruta.'.sorpresivos.show', $a->id) }}">Consultar</a>
                                </div>
                            @empty
                                <div class="jc-empty">Este receptor todavía no tiene arqueos de Jefatura.</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
