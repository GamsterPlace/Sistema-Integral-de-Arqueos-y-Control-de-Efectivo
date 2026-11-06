@extends('layouts.administrador')
@section('title', 'Arqueos de Agencias')
@section('module-title', 'Arqueos de Agencias')

@push('styles')
<style>
.aa-page{max-width:1550px;margin:auto}.aa-header{display:flex;align-items:center;gap:15px;margin-bottom:22px}.aa-symbol{width:54px;height:54px;display:grid;place-items:center;border-radius:16px;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));color:white;font-size:26px}.aa-header h2{margin:0;color:var(--azul-profundo);font-size:25px}.aa-header p{margin:4px 0;color:var(--texto-secundario)}.aa-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:18px}.aa-stat{background:white;border:1px solid #dfe7ee;border-radius:15px;padding:17px;box-shadow:0 7px 20px rgba(28,64,92,.045)}.aa-stat span{display:block;font-size:12px;color:#60788b;font-weight:700}.aa-stat strong{display:block;margin-top:7px;font-size:27px;color:var(--azul-profundo)}.aa-panel{background:#fff;border:1px solid #dfe7ee;border-radius:17px;overflow:hidden;margin-bottom:18px;box-shadow:0 8px 24px rgba(28,64,92,.045)}.aa-panel-head{padding:16px 19px;border-bottom:1px solid #e6edf2;background:#fbfcfd;display:flex;justify-content:space-between;align-items:center;gap:10px}.aa-panel-head h3{margin:0;color:var(--azul-profundo);font-size:16px}.aa-panel-head small{color:#647b8e}.aa-filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;padding:18px}.aa-field label{display:block;font-size:12px;font-weight:800;color:#567086;margin-bottom:6px}.aa-field input,.aa-field select{width:100%;min-height:40px;border:1px solid #d5e0e8;border-radius:9px;padding:9px 10px;background:white;color:#29455a}.aa-filter-actions{grid-column:1/-1;display:flex;gap:9px}.aa-btn{display:inline-flex;align-items:center;justify-content:center;min-height:39px;padding:0 15px;border-radius:9px;border:1px solid #d4dfe7;background:white;color:#31536e;text-decoration:none;font-size:12px;font-weight:800;cursor:pointer}.aa-btn.primary{background:var(--azul-principal);border-color:var(--azul-principal);color:white}.aa-scroll{overflow-x:auto}.aa-table{border-collapse:collapse;width:100%;min-width:1220px}.aa-table th{background:#f7f9fb;padding:13px;text-align:left;color:#60788b;font-size:11px;text-transform:uppercase;white-space:nowrap}.aa-table td{padding:13px;border-top:1px solid #edf1f4;font-size:12px;color:#334d60}.aa-table tr:hover td{background:#fbfcfe}.aa-number{color:var(--azul-principal);font-weight:800}.aa-sub{display:block;margin-top:3px;font-size:11px;color:#73899a}.aa-badge{display:inline-block;border-radius:30px;padding:6px 9px;background:#eef3f7;color:#486277;font-size:10px;font-weight:800;white-space:nowrap}.aa-badge.ok{background:#e9f8f0;color:#08783d}.aa-badge.warn{background:#fff8e3;color:#936b00}.aa-badge.bad{background:#fff0f0;color:#b13b3b}.aa-money{font-weight:800;white-space:nowrap}.aa-footer{padding:14px 18px;border-top:1px solid #e9eef2}.aa-empty{padding:35px!important;text-align:center;color:#60788b!important}@media(max-width:1100px){.aa-summary{grid-template-columns:repeat(3,1fr)}.aa-filters{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.aa-summary{grid-template-columns:repeat(2,1fr)}.aa-filters{grid-template-columns:1fr}.aa-header h2{font-size:21px}}
</style>
@endpush

@section('content')
<div class="aa-page">
 <div class="aa-header"><div class="aa-symbol">▤</div><div><h2>Arqueos de Agencias</h2><p>Supervisión y consulta institucional de los arqueos de caja de ECOSABA.</p></div></div>
 <div class="aa-summary">
  <div class="aa-stat"><span>Total de arqueos</span><strong>{{ number_format((int)$resumen->total) }}</strong></div>
  <div class="aa-stat"><span>Certificados</span><strong>{{ number_format((int)$resumen->certificados) }}</strong></div>
  <div class="aa-stat"><span>Pendientes</span><strong>{{ number_format((int)$resumen->pendientes) }}</strong></div>
  <div class="aa-stat"><span>Anulados</span><strong>{{ number_format((int)$resumen->anulados) }}</strong></div>
  <div class="aa-stat"><span>Con diferencias</span><strong>{{ number_format((int)$resumen->con_diferencia) }}</strong></div>
 </div>
 <section class="aa-panel">
  <div class="aa-panel-head"><h3>Filtros de búsqueda</h3><small>Los indicadores también respetan los filtros aplicados.</small></div>
  <form method="GET" action="{{ route('administrador.arqueos-agencias.index') }}" class="aa-filters">
   <div class="aa-field"><label for="agencia_id">Agencia</label><select name="agencia_id" id="agencia_id"><option value="">Todas las agencias</option>@foreach($agencias as $agencia)<option value="{{ $agencia->id }}" @selected($agenciaId===$agencia->id)>{{ $agencia->codigo }} — {{ $agencia->nombre }}</option>@endforeach</select></div>
   <div class="aa-field"><label for="ventanilla_id">Ventanilla</label><select name="ventanilla_id" id="ventanilla_id"><option value="">Todas las ventanillas</option>@foreach($ventanillas as $ventanilla)<option value="{{ $ventanilla->id }}" @selected($ventanillaId===$ventanilla->id)>{{ $ventanilla->codigo }}{{ $ventanilla->descripcion ? ' — '.$ventanilla->descripcion : '' }}</option>@endforeach</select></div>
   <div class="aa-field"><label for="receptor_id">Receptor</label><select name="receptor_id" id="receptor_id"><option value="">Todos los receptores</option>@foreach($receptores as $receptor)<option value="{{ $receptor->id }}" @selected($receptorId===$receptor->id)>{{ trim(($receptor->nombres ?? '').' '.($receptor->apellidos ?? '')) ?: $receptor->usuario }}</option>@endforeach</select></div>
   <div class="aa-field"><label for="tipo">Tipo</label><select name="tipo" id="tipo"><option value="">Todos los tipos</option>@foreach(['ORDINARIO'=>'Ordinario','ENTREGA'=>'Entrega de ventanilla','SORPRESIVO'=>'Sorpresivo','AUDITORIA'=>'Auditoría'] as $clave=>$nombre)<option value="{{ $clave }}" @selected($tipo===$clave)>{{ $nombre }}</option>@endforeach</select></div>
   <div class="aa-field"><label for="estado">Estado</label><select name="estado" id="estado"><option value="">Todos los estados</option>@foreach($estados as $e)<option value="{{ $e }}" @selected($estado===$e)>{{ str_replace('_',' ',$e) }}</option>@endforeach</select></div>
   <div class="aa-field"><label for="desde">Desde</label><input type="date" id="desde" name="desde" value="{{ $desde }}"></div>
   <div class="aa-field"><label for="hasta">Hasta</label><input type="date" id="hasta" name="hasta" value="{{ $hasta }}"></div>
   <div class="aa-field"><label for="buscar">Búsqueda general</label><input id="buscar" name="buscar" maxlength="120" value="{{ $buscar }}" placeholder="Número, agencia, receptor..."></div>
   <div class="aa-filter-actions"><button class="aa-btn primary" type="submit">Aplicar filtros</button><a class="aa-btn" href="{{ route('administrador.arqueos-agencias.index') }}">Limpiar</a></div>
  </form>
 </section>
 <section class="aa-panel">
  <div class="aa-panel-head"><h3>Registro de arqueos</h3><small>{{ $arqueos->total() }} registros encontrados</small></div>
  <div class="aa-scroll"><table class="aa-table"><thead><tr><th>Número / Fecha</th><th>Agencia</th><th>Ventanilla / Turno</th><th>Receptor</th><th>Tipo</th><th>Estado</th><th>Total arqueado</th><th>Diferencia</th><th>Acciones</th></tr></thead><tbody>
  @forelse($arqueos as $a)
   @php $clase=match($a->estado){'CERTIFICADO'=>'ok','ANULADO'=>'bad','PENDIENTE_CERTIFICACION','PENDIENTE','PENDIENTE_RECTIFICACION'=>'warn',default=>''}; @endphp
   <tr><td><span class="aa-number">{{ $a->numero_arqueo }}</span><span class="aa-sub">{{ \Carbon\Carbon::parse($a->fecha_operativa)->format('d/m/Y') }}</span></td><td><strong>{{ $a->agencia_nombre_historico }}</strong><span class="aa-sub">{{ $a->agencia_codigo_historico }}</span></td><td>{{ $a->ventanilla_codigo_historico }}<span class="aa-sub">{{ $a->turno_nombre_historico ?: 'Sin turno' }}</span></td><td>{{ $a->receptor_nombre_historico }}</td><td><span class="aa-badge">{{ str_replace('_',' ',$a->tipo) }}</span></td><td><span class="aa-badge {{ $clase }}">{{ str_replace('_',' ',$a->estado) }}</span>@if($a->fuera_fecha_ordinaria)<span class="aa-sub">Extemporáneo</span>@endif</td><td class="aa-money">Q {{ number_format((float)$a->total_arqueado,2) }}</td><td class="aa-money">Q {{ number_format((float)$a->diferencia,2) }}</td><td><a class="aa-btn" href="{{ route('administrador.arqueos-agencias.show',$a->id) }}">Ver</a> <a class="aa-btn" target="_blank" rel="noopener" href="{{ route('administrador.arqueos-agencias.imprimir',$a->id) }}">PDF</a></td></tr>
  @empty<tr><td colspan="9" class="aa-empty">No se encontraron arqueos de agencias con los filtros seleccionados.</td></tr>@endforelse
  </tbody></table></div>
  @if($arqueos->hasPages())<div class="aa-footer">{{ $arqueos->links() }}</div>@endif
 </section>
</div>
@endsection
