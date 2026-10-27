<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cumplimiento de arqueos por turno</title>
<style>
*{box-sizing:border-box}body{margin:0;padding:28px;background:#f3f6fa;color:#183654;font-family:system-ui,-apple-system,sans-serif}
main{max-width:1250px;margin:auto;background:#fff;border-radius:14px;padding:26px;box-shadow:0 3px 20px #14324a12}
h1{font-size:26px;margin:0 0 7px}p{line-height:1.5;color:#52667b}.filtros{display:flex;gap:12px;flex-wrap:wrap;align-items:end;margin:22px 0}
label{display:block;font-weight:600;font-size:13px;margin-bottom:5px}input,select{padding:10px;border:1px solid #aebfd0;border-radius:7px;background:#fff}
button{padding:11px 20px;border:0;border-radius:7px;background:#145b9d;color:white;cursor:pointer}table{width:100%;border-collapse:collapse;font-size:14px}
th,td{text-align:left;padding:11px 9px;border-bottom:1px solid #e7edf3;vertical-align:top}th{background:#f5f8fc}.scroll{overflow-x:auto}
.estado{display:inline-block;border-radius:7px;padding:4px 7px;font-size:12px;font-weight:700;background:#edf1f6;color:#344b64}
.ok{background:#e3f4e8;color:#166a39}.warn{background:#fff2d8;color:#855800}.bad{background:#fde8e8;color:#9b2525}
a{color:#115b9e}.note{font-size:13px;margin-top:20px}.links{margin-top:18px}
</style></head><body><main>
<h1>Cumplimiento de arqueos por turno</h1>
<p>Control de arqueos ordinarios asociados a turnos programados. Un arqueo firmado cuenta como realizado, aunque todavía no esté certificado.</p>
<form method="GET" action="{{ route($ruta.'.cumplimiento-agencias.index') }}" class="filtros">
<div><label for="desde">Desde</label><input id="desde" type="date" name="desde" value="{{ $desde }}" required></div>
<div><label for="hasta">Hasta</label><input id="hasta" type="date" name="hasta" value="{{ $hasta }}" required></div>
@if($rol!=='Receptor')<div><label for="agencia">Agencia</label><select name="agencia_id" id="agencia"><option value="">Todas las autorizadas</option>
@foreach($agencias as $agencia)<option value="{{ $agencia->id }}" @selected((string)request('agencia_id')===(string)$agencia->id)>{{ $agencia->codigo }} — {{ $agencia->nombre }}</option>@endforeach
</select></div>@endif
<button type="submit">Consultar</button></form>
<div class="scroll"><table><thead><tr><th>Fecha</th><th>Agencia</th><th>Ventanilla</th><th>Receptor</th><th>Turno</th><th>Cumplimiento</th><th>Certificación</th><th>Arqueo</th></tr></thead><tbody>
@forelse($jornadas as $j)
@php
 $cumplimientos=['CON_ARQUEO'=>'Con arqueo firmado','PROGRAMADO'=>'Programado','PENDIENTE_HOY'=>'Pendiente hoy','ANULADO_SIN_REEMPLAZO'=>'Anulado sin reemplazo','SIN_ARQUEO'=>'Sin arqueo registrado'];
 $clase=$j->cumplimiento_arqueo==='CON_ARQUEO'?'ok':($j->cumplimiento_arqueo==='PROGRAMADO'||$j->cumplimiento_arqueo==='PENDIENTE_HOY'?'warn':'bad');
@endphp
<tr><td>{{ $j->fecha_operativa?->format('d/m/Y') }}</td><td>{{ $j->agencia?->nombre ?? '—' }}</td><td>{{ $j->ventanilla?->codigo ?? '—' }}</td><td>{{ $j->receptor?->nombre_completo ?? '—' }}</td><td>{{ $j->turno?->nombre ?? '—' }}</td>
<td><span class="estado {{ $clase }}">{{ $cumplimientos[$j->cumplimiento_arqueo] ?? $j->cumplimiento_arqueo }}</span></td>
<td>@if($j->certificacion_arqueo==='CERTIFICADO')<span class="estado ok">Certificado</span>@elseif($j->certificacion_arqueo==='PENDIENTE_CERTIFICACION')<span class="estado warn">Pendiente</span>@else — @endif</td>
<td>@if($j->arqueo_ordinario_vigente){{ $j->arqueo_ordinario_vigente->numero_arqueo }}@else — @endif</td></tr>
@empty<tr><td colspan="8">No se encontraron turnos programados para el período y los filtros seleccionados.</td></tr>@endforelse
</tbody></table></div><div class="links">{{ $jornadas->links() }}</div>
<p class="note"><strong>Alcance:</strong> “Sin arqueo registrado” significa que existe un turno programado sin arqueo ordinario firmado vigente; no demuestra por sí mismo que el receptor haya trabajado o incumplido. La certificación se muestra por separado. No se requieren aperturas ni cierres de jornada.</p>
</main></body></html>
