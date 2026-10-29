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
<header class="jc-head"><div><h1>Detalle del arqueo</h1><p class="jc-muted">{{ $registro->numero_arqueo }} · Arqueo de Jefatura de Caja</p></div><a class="jc-btn jc-btn-light" href="{{ route($ruta.'.sorpresivos.index') }}">Volver al historial</a></header>
@if(session('success'))<div class="jc-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="jc-alert"><strong>Revise lo siguiente:</strong><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<section class="jc-card"><h2 class="jc-section-title">Identificación del arqueo</h2><div class="jc-grid">
@foreach([['Agencia',$registro->agencia_nombre_historico],['Ventanilla',$registro->ventanilla_codigo_historico],['Receptor',$registro->receptor_nombre_historico],['Turno',$registro->turno_nombre_historico],['Fecha operativa',$registro->fecha_operativa?->format('d/m/Y')],['Estado',str_replace('_',' ',$registro->estado)]] as [$etiqueta,$valor])
<div class="jc-info"><small>{{ $etiqueta }}</small><strong>{{ $valor ?: '—' }}</strong></div>
@endforeach</div></section>
<section class="jc-card"><h2 class="jc-section-title">Conteo de efectivo</h2><div class="jc-tablewrap"><table class="jc-table"><thead><tr><th>Tipo</th><th>Denominación</th><th>Cantidad</th><th>Subtotal</th></tr></thead><tbody>@foreach($registro->detalles as $d)<tr><td>{{ $d->tipo }}</td><td>{{ $d->denominacion === null ? ($d->concepto ?: '—') : 'Q '.number_format((float)$d->denominacion,2) }}</td><td>{{ number_format((int)$d->cantidad) }}</td><td>Q {{ number_format((float)$d->subtotal,2) }}</td></tr>@endforeach</tbody></table></div></section>
<section class="jc-card"><h2 class="jc-section-title">Comprobación monetaria</h2><div class="jc-grid">
@foreach([['Total billetes',$registro->total_billetes],['Total monedas',$registro->total_monedas],['Otros valores',$registro->total_otros_valores],['Total arqueado',$registro->total_arqueado],['Total corte',$registro->total_corte],['Diferencia',$registro->diferencia]] as [$etiqueta,$monto])
<div class="jc-info"><small>{{ $etiqueta }}</small><strong>Q {{ number_format((float)$monto,2) }}</strong></div>
@endforeach</div><div style="margin-top:17px"><strong>Justificación de diferencia</strong><p class="jc-muted">{{ $registro->justificacion_diferencia ?: 'No aplica' }}</p></div><div style="margin-top:17px"><strong>Observaciones</strong><p class="jc-muted">{{ $registro->observaciones ?: 'Sin observaciones' }}</p></div></section>
<section class="jc-card"><h2 class="jc-section-title">Firmas electrónicas</h2>
@forelse($registro->firmas as $f)<div class="jc-info" style="margin-bottom:10px"><small>{{ $f->tipo_firma }} · {{ $f->rol_firmante }}</small><strong>{{ trim($f->nombres_historicos.' '.$f->apellidos_historicos) }}</strong><p class="jc-muted">{{ $f->fecha_firma?->format('d/m/Y H:i') ?? '—' }} · {{ ($verificaciones[$f->tipo_firma] ?? false) ? 'Integridad verificada' : 'Firma no verificada' }}</p></div>@empty<p class="jc-muted">No hay firmas registradas.</p>@endforelse
@if($esReceptor && $registro->estado === 'PENDIENTE_FIRMA_RECEPTOR' && ($verificaciones['CERTIFICADOR'] ?? false))
<form method="POST" action="{{ route('receptor.sorpresivos.firmar',$registro->id) }}" id="jc-firma-form" style="margin-top:18px">@csrf<div class="jc-success">La firma del supervisor está verificada. Revisa el conteo antes de confirmar tu firma.</div><label class="jc-sign"><input type="checkbox" name="confirmar_firma" value="1" required><span>Confirmo que revisé el arqueo y autorizo mi firma electrónica.</span></label><button type="submit" class="jc-btn" id="jc-firma-btn">Firmar arqueo de Jefatura de Caja</button></form>
@elseif($esReceptor && $registro->estado === 'PENDIENTE_FIRMA_RECEPTOR')<p class="jc-alert">No es posible firmar mientras no se verifique la firma del supervisor.</p>@endif
</section>
@if(\Illuminate\Support\Facades\Route::has($ruta.'.sorpresivos.imprimir'))
<div class="jc-actions"><a class="jc-btn" href="{{ route($ruta.'.sorpresivos.imprimir', $registro->id) }}" target="_blank" rel="noopener">Imprimir arqueo PDF</a></div>
@endif
<p class="jc-print">El PDF indica el estado de las firmas electrónicas y no simula firmas manuscritas.</p>
</div>
@endsection
@push('scripts')<script>document.getElementById('jc-firma-form')?.addEventListener('submit',function(e){if(!confirm('¿Confirma la firma electrónica definitiva de este arqueo?')){e.preventDefault();return;}document.getElementById('jc-firma-btn').disabled=true;});</script>@endpush
