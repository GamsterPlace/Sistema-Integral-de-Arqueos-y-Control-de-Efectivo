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
.jc-page{max-width:1250px;margin:auto;display:grid;gap:20px;color:#173653}.jc-page *{box-sizing:border-box}.jc-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px}.jc-head h1{font-size:27px;color:#092f58;margin:0 0 7px}.jc-muted{color:#698098;font-size:13px;line-height:1.6;margin:0}.jc-card{background:#fff;border:1px solid #dce7f1;border-radius:16px;padding:22px;box-shadow:0 6px 24px #12345a0b}.jc-btn{display:inline-flex;align-items:center;justify-content:center;background:#145a99;color:#fff!important;border:0;border-radius:10px;padding:11px 16px;text-decoration:none;font-size:13px;font-weight:750;cursor:pointer}.jc-btn-light{background:#edf4fc;color:#145a99!important}.jc-tablewrap{overflow-x:auto}.jc-table{border-collapse:collapse;width:100%;min-width:750px}.jc-table th{background:#f2f7fc;font-size:11px;text-transform:uppercase;color:#52718c;letter-spacing:.04em}.jc-table td,.jc-table th{padding:12px;text-align:left;border-bottom:1px solid #e4ecf4}.jc-table td{font-size:13px}.jc-table a{color:#145a99;font-weight:750;text-decoration:none}.jc-pill{display:inline-block;background:#edf3fa;color:#27547c;border-radius:30px;padding:5px 10px;font-size:11px;font-weight:750}.jc-positive{color:#13864e;font-weight:800}.jc-negative{color:#b43838;font-weight:800}.jc-neutral{color:#52677b;font-weight:800}.jc-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.jc-info{border:1px solid #e1eaf2;border-radius:12px;background:#f8fbff;padding:14px;min-width:0;overflow-wrap:anywhere}.jc-info small{display:block;color:#6c8194;margin-bottom:6px;font-size:11px;font-weight:700;text-transform:uppercase}.jc-info strong{font-size:14px}.jc-section-title{font-size:18px;color:#0d355c;margin:0 0 16px}.jc-alert{background:#fff4f4;border:1px solid #f0caca;color:#a33131;padding:14px;border-radius:10px}.jc-success{background:#edf9f2;border:1px solid #bce3ca;color:#176c42;padding:14px;border-radius:10px}.jc-field{display:grid;gap:7px;font-size:13px;font-weight:700}.jc-field input,.jc-field select,.jc-field textarea{border:1px solid #bbccdc;border-radius:9px;padding:11px;width:100%;background:#fff;color:#173653}.jc-two{display:grid;grid-template-columns:1fr 1fr;gap:16px}.jc-total{font-size:21px;font-weight:850;color:#0b4d87}.jc-sign{display:flex;gap:10px;align-items:flex-start;margin:14px 0;font-size:13px;line-height:1.5}.jc-sign input{margin-top:3px}.jc-actions{display:flex;flex-wrap:wrap;gap:10px}.jc-empty{padding:25px;text-align:center;color:#72869a}.jc-print{font-size:12px;color:#60778b}@media(max-width:750px){.jc-grid,.jc-two{grid-template-columns:1fr}.jc-card{padding:16px}.jc-head h1{font-size:23px}}
</style>
@endpush
@section('content')
<div class="jc-page">
 <header class="jc-head"><div><h1>Arqueos de Jefatura de Caja</h1><p class="jc-muted">{{ $esReceptor ? 'Consulta tus arqueos realizados por Jefatura de Caja, revisa el detalle y firma los pendientes.' : 'Consulta los arqueos de tus agencias y realiza nuevos controles de Jefatura de Caja.' }}</p></div>
 <div class="jc-actions"><a class="jc-btn jc-btn-light" href="{{ route($ruta.'.dashboard') }}">Volver al panel</a>@unless($esReceptor)<a class="jc-btn" href="{{ route($ruta.'.sorpresivos.create') }}">Nuevo arqueo de Jefatura</a>@endunless</div></header>
 <section class="jc-card"><h2 class="jc-section-title">Historial de arqueos</h2><div class="jc-tablewrap"><table class="jc-table"><thead><tr><th>Número</th><th>Fecha</th><th>Agencia</th><th>Receptor</th><th>Diferencia</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
 @forelse($arqueos as $a)
 <tr><td>{{ $a->numero_arqueo }}</td><td>{{ $a->fecha_operativa?->format('d/m/Y') ?? '—' }}</td><td>{{ $a->agencia_nombre_historico ?: '—' }}</td><td>{{ $a->receptor_nombre_historico ?: '—' }}</td><td><span class="{{ (float)$a->diferencia > 0 ? 'jc-positive' : ((float)$a->diferencia < 0 ? 'jc-negative' : 'jc-neutral') }}">{{ (float)$a->diferencia > 0 ? '+' : '' }}Q {{ number_format((float)$a->diferencia,2) }}</span></td><td><span class="jc-pill">{{ $a->estado === 'PENDIENTE_FIRMA_RECEPTOR' ? 'Pendiente de firma del receptor' : str_replace('_',' ',$a->estado) }}</span></td><td><a href="{{ route($ruta.'.sorpresivos.show',$a->id) }}">Consultar {{ $esReceptor && $a->estado === 'PENDIENTE_FIRMA_RECEPTOR' ? 'y firmar' : 'detalle' }}</a></td></tr>
 @empty<tr><td colspan="7" class="jc-empty">No hay arqueos de Jefatura de Caja registrados.</td></tr>@endforelse
 </tbody></table></div><div style="margin-top:18px">{{ $arqueos->links() }}</div></section>
</div>
@endsection
