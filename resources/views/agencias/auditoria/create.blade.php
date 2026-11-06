
@php
    $layout = match ($ruta) {
        'receptor' => 'layouts.receptor',
        'jefecaja' => 'layouts.jefe-caja',
        'subjefecaja' => 'layouts.subjefe-caja',
        default => 'layouts.auditoria',
    };

    $opcionesAgencias = $agencias->map(fn ($a) => [
        'id' => $a->id,
        'codigo' => $a->codigo,
        'nombre' => $a->nombre,
    ])->values();

    $opcionesAsignaciones = $asignaciones->map(fn ($a) => [
        'id' => $a->id,
        'agencia_id' => $a->agencia_id,
        'agencia_nombre' => $a->agencia?->nombre,
        'receptor_id' => $a->receptor_usuario_id,
        'receptor_nombre' => $a->receptor?->nombre_completo ?: $a->receptor?->usuario,
        'ventanilla' => $a->ventanilla?->codigo,
        'turno' => $a->turno?->nombre,
    ])->values();
@endphp

@extends($layout)

@section('title', 'Realizar arqueo de agencia')
@section('module-title', 'Realizar arqueo de agencia')

@push('styles')
<style>
.aa-page{max-width:1140px;margin:0 auto 35px;color:#203447}
.aa-page *{box-sizing:border-box}
.aa-hidden{display:none!important}
.aa-step-card{background:#fff;border:1px solid #d4e0ea;border-radius:14px;padding:27px;box-shadow:0 12px 36px rgba(15,45,75,.06);max-width:800px;margin:20px auto}
.aa-step-card h2{color:#123d67;margin:0 0 9px;font-size:25px}
.aa-step-card p{font-size:13px;color:#6a8093;line-height:1.6}
.aa-step-card select{width:100%;padding:16px;border:1px solid #b9c9d8;border-radius:9px;background:#fff;color:#193652;margin:10px 0 18px}
.aa-step-card label{font-weight:750;color:#34536e;font-size:13px}
.aa-step-actions{display:flex;gap:12px;flex-wrap:wrap}
.aa-empty{padding:14px;background:#f8fafc;border:1px dashed #c6d5e5;border-radius:9px;color:#63788e;font-size:13px}
.aa-step-card .ec-btn{font-family:inherit}


.ec-arqueo{max-width:1120px;margin:0 auto 35px;color:#203447;font-family:Arial,Helvetica,sans-serif}

.ec-arqueo *{box-sizing:border-box}

.ec-page-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:22px}

.ec-page-head h1{margin:0 0 6px;font-size:26px;color:#123d67}

.ec-page-head p{margin:0;color:#6a8093;font-size:13px}

.ec-paper{background:#fff;border:1px solid #d4e0ea;border-radius:14px;box-shadow:0 12px 36px rgba(15,45,75,.08);overflow:hidden}

.ec-paper-header{display:grid;grid-template-columns:180px 1fr 145px;align-items:center;gap:20px;padding:27px 30px;border-bottom:2px solid #dce5ed}

.ec-logo{max-width:175px;max-height:75px;object-fit:contain}

.ec-title{text-align:center}

.ec-title h2{margin:0;color:#163f68;font-size:25px;text-transform:uppercase;letter-spacing:.3px}

.ec-title p{margin:8px 0 0;font-size:15px;font-weight:800;color:#40586e;text-transform:uppercase}

.ec-number{text-align:right;color:#8292a1;font-size:11px;font-weight:800}

.ec-number strong{display:block;margin-top:8px;color:#a84a4a;font-size:16px}

.ec-body{padding:28px 34px 34px}

.ec-section{margin-bottom:27px}

.ec-section h3{font-size:16px;color:#173f67;margin:0 0 13px;padding-bottom:9px;border-bottom:1px solid #d9e2ea}

.ec-info{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:16px 20px}

.ec-info-item{grid-column:span 3;min-width:0}

.ec-info-item.wide{grid-column:span 6}

.ec-info-item label{display:block;font-size:11px;font-weight:800;color:#597084;margin-bottom:6px}

.ec-info-item div{border-bottom:1px solid #9aa9b7;min-height:31px;padding:7px 3px;font-size:13px;font-weight:750;overflow-wrap:anywhere}

.ec-count-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:30px}

.ec-count-grid h3{font-size:18px;font-family:Georgia,'Times New Roman',serif}

.ec-table{width:100%;border-collapse:collapse;table-layout:fixed}

.ec-table th{font-size:11px;text-align:left;color:#4b657b;border-bottom:1px solid #bacbd9;padding:10px 6px}

.ec-table td{border-bottom:1px solid #edf1f5;padding:6px;font-size:12px}

.ec-table th:nth-child(1){width:33%}

.ec-table th:nth-child(2){width:26%;text-align:center}

.ec-table th:nth-child(3){width:41%;text-align:right}

.ec-table td:nth-child(2){text-align:center}

.ec-table td:nth-child(3){text-align:right}

.ec-quantity,.ec-money-input{width:100%;max-width:120px;border:1px solid #c2d1df;border-radius:7px;background:#fff;padding:10px 8px;text-align:center;color:#154b7b;font-weight:800;font-size:13px}

.ec-money-input{text-align:right;max-width:200px}

.ec-quantity:focus,.ec-money-input:focus,.ec-textarea:focus{outline:none;border-color:#1764a1;box-shadow:0 0 0 3px rgba(23,100,161,.12)}

.ec-subtotal{display:block;padding:9px 0 6px;border-bottom:1px solid #91a5b6;font-weight:800;color:#153f68;white-space:nowrap}

.ec-count-total{display:flex;justify-content:flex-end;align-items:center;gap:15px;border-top:1px solid #bdcbd8;padding:15px 0;margin-top:15px;font-size:12px;font-weight:800}

.ec-count-total strong{font-size:18px;color:#0f426d;white-space:nowrap}

.ec-other-table{width:100%;max-width:780px;border-collapse:collapse}

.ec-other-table th{text-align:left;font-size:11px;color:#536d83;padding:9px;border-bottom:1px solid #bdcbd8}

.ec-other-table td{padding:9px;border-bottom:1px solid #e5ecf2;font-size:13px}

.ec-other-table td:nth-child(2){width:220px;text-align:right}

.ec-other-table td:nth-child(3){width:175px;text-align:right;font-weight:800;color:#17476f}

.ec-note{font-size:12px;color:#708397;line-height:1.6;margin:10px 0 0}

.ec-grand-total{display:flex;align-items:center;justify-content:flex-end;gap:20px;flex-wrap:wrap;background:#edf5fc;border:1px solid #c4d9e9;border-radius:10px;padding:18px 22px;margin:18px 0 30px;font-size:14px;font-weight:800;color:#173e64}

.ec-grand-total strong{font-size:26px;color:#0d4a7d}

.ec-check-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(310px,.9fr);gap:32px}

.ec-textarea{width:100%;min-height:120px;padding:10px;border:1px solid #ccd9e4;border-radius:8px;resize:vertical;font:13px/1.6 Arial,Helvetica,sans-serif;color:#24465e}

.ec-field-label{display:block;margin:14px 0 8px;font-size:12px;font-weight:800;color:#4c667c}

.ec-check-box{background:#f8fbfe;border:1px solid #d5e2ed;border-radius:12px;padding:20px}

.ec-check-box h3{text-align:center;border-bottom:0;margin-bottom:18px}

.ec-check-line{display:grid;grid-template-columns:1fr 180px;gap:12px;align-items:center;margin-bottom:15px;font-size:12px;font-weight:800;color:#4b6378}

.ec-check-line strong{display:block;text-align:right;font-size:16px;color:#173e65;padding:9px 0;border-bottom:1px solid #91a7b9}

.ec-check-line.difference{padding-top:13px;border-top:2px solid #c6d6e3}

.ec-result{text-align:right;font-size:12px;font-weight:800;color:#155e8f}

.ec-signatures{display:grid;grid-template-columns:1fr 1fr;gap:50px;margin:40px 0 25px}

.ec-signature{text-align:center;padding-top:45px}

.ec-signature small{display:block;color:#8496a6;margin-bottom:15px;font-size:11px}

.ec-signature strong{display:block;border-top:1px solid #71889b;padding-top:10px;color:#405c72;font-size:12px}

.ec-confirmation{padding:20px;border:1px solid #d6e5f0;background:#f5f9fd;border-radius:11px}

.ec-confirmation h3{border:0;padding:0;margin:0 0 8px}

.ec-confirmation p{font-size:12px;line-height:1.6;color:#647b90}

.ec-checkbox{display:flex;gap:11px;align-items:flex-start;font-size:13px;font-weight:700;line-height:1.6}

.ec-checkbox input{width:18px;height:18px;margin-top:3px;accent-color:#155b98}

.ec-actions{display:flex;justify-content:flex-end;gap:12px;flex-wrap:wrap;margin-top:25px}

.ec-btn{display:inline-flex;justify-content:center;align-items:center;min-height:44px;padding:12px 18px;border:0;border-radius:9px;background:#155b98;color:#fff!important;text-decoration:none;font-size:13px;font-weight:800;cursor:pointer}

.ec-btn.secondary{background:#e6f0f9;color:#15517e!important}

.ec-btn:hover{filter:brightness(.94)}

.ec-btn:disabled{opacity:.6;cursor:wait}

.ec-errors{margin-bottom:20px;padding:16px 20px;background:#fff3f3;border:1px solid #eabbbb;border-radius:10px;color:#923333;font-size:13px}

.ec-modal[hidden]{display:none!important}

.ec-modal{position:fixed;inset:0;z-index:99999;background:rgba(8,25,45,.76);display:flex;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(4px)}

.ec-dialog{width:100%;max-width:510px;max-height:calc(100vh - 40px);overflow-y:auto;background:#fff;border-radius:16px;box-shadow:0 25px 75px rgba(0,0,0,.3)}

.ec-dialog-head{padding:25px 26px;border-bottom:1px solid #e2eaf1}

.ec-dialog-head h2{margin:0 0 9px;color:#123e66;font-size:21px}

.ec-dialog-head p{margin:0;color:#667d90;font-size:13px;line-height:1.6}

.ec-dialog-body{padding:23px 26px}

.ec-dialog-row{display:flex;justify-content:space-between;gap:12px;padding:13px 0;border-bottom:1px solid #e5edf3;font-size:13px}

.ec-dialog-row strong{color:#12456e}

.ec-warning{margin-top:17px;padding:14px;background:#fff8e9;border:1px solid #eed9a5;border-radius:9px;color:#795c20;font-size:13px;line-height:1.6}

.ec-dialog-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 26px 25px}

body.ec-modal-open{overflow:hidden}

@media(max-width:900px){

.ec-paper-header{grid-template-columns:1fr;text-align:center}

.ec-logo{margin:auto}

.ec-number{text-align:center}

.ec-count-grid,.ec-check-grid{grid-template-columns:1fr}

}

@media(max-width:650px){

.ec-body{padding:20px 15px}

.ec-paper-header{padding:20px 15px}

.ec-info-item,.ec-info-item.wide{grid-column:span 12}

.ec-signatures{grid-template-columns:1fr;gap:10px}

.ec-check-line{grid-template-columns:1fr;gap:4px}

.ec-other-table td:nth-child(2){width:145px}

.ec-other-table td:nth-child(3){width:115px}

.ec-money-input{max-width:130px}

.ec-actions,.ec-dialog-actions{flex-direction:column}

.ec-actions .ec-btn,.ec-dialog-actions .ec-btn{width:100%}

}

@media print{

.ec-page-head,.ec-actions,.ec-confirmation,.ec-modal{display:none!important}

.ec-paper{border:0;box-shadow:none}

}


.ec-modal[hidden]{display:none!important}
@media print{.aa-step-card{display:none!important}}
</style>
@endpush
@section('content')
<div class="aa-page ec-arqueo">
 <div class="ec-page-head">
  <div><h1>Realizar arqueo de Auditoría</h1><p>Seleccione la agencia y el receptor antes de registrar el conteo.</p></div>
  <a class="ec-btn secondary" href="{{ route('auditoria.auditoria-agencias.index') }}">Volver al historial</a>
 </div>
 @if($errors->any())
 <div class="ec-errors" role="alert"><strong>Corrija los siguientes datos:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
 @endif
 <form id="aa-form" method="POST" action="{{ route('auditoria.auditoria-agencias.store') }}">
 @csrf
 <section id="aa-paso-agencia" class="aa-step-card" aria-label="Paso 1: selección de agencia">
  <h2>Seleccionar agencia</h2><p>Seleccione la agencia donde se realizará el arqueo. Al seleccionarla, avanzará automáticamente a los receptores.</p>
  <label for="aa-agencia">Agencia</label>
  <select id="aa-agencia"><option value="">Seleccione una agencia</option>@foreach($agencias as $agencia)<option value="{{ $agencia->id }}">{{ $agencia->codigo }} — {{ $agencia->nombre }}</option>@endforeach</select>
  @if($agencias->isEmpty())<div class="aa-empty">No existen agencias activas registradas.</div>@endif
 </section>
 <section id="aa-paso-receptor" class="aa-step-card aa-hidden" aria-label="Paso 2: selección de receptor">
  <h2>Seleccionar receptor</h2><p>Agencia seleccionada: <strong id="aa-agencia-nombre"></strong>. Al elegir un receptor con una sola asignación, se abrirá directamente el formato de arqueo.</p>
  <label for="aa-receptor">Receptor asignado</label>
  <select id="aa-receptor" disabled><option value="">Seleccione un receptor</option></select>
  <div id="aa-sin-receptores" class="aa-empty aa-hidden">No hay receptores con asignación operativa vigente en esta agencia.</div>
  <div id="aa-paso-asignacion" class="aa-hidden">
   <label for="aa-asignacion">Ventanilla y turno</label>
   <select id="aa-asignacion" name="asignacion_operativa_id" required disabled><option value="">Seleccione ventanilla y turno</option></select>
  </div>
  <div class="aa-step-actions"><button type="button" class="ec-btn secondary" id="aa-cambiar-agencia">Cambiar agencia</button></div>
 </section>
 <div id="aa-paso-arqueo" class="aa-hidden" aria-label="Paso 3: arqueo institucional">
  <div class="ec-paper">
   <header class="ec-paper-header">
    <div><img class="ec-logo" src="{{ asset('images/logos/ecosaba.png') }}" alt="ECOSABA"></div>
    <div class="ec-title"><h2>Arqueo y corte de caja</h2><p id="aa-paper-agencia">Agencia</p></div>
    <div class="ec-number">DOCUMENTO EN PREPARACIÓN<strong>ARQUEO DE AUDITORÍA</strong></div>
   </header>
   <div class="ec-body">
    <div class="ec-actions" style="margin:0 0 25px"><button type="button" class="ec-btn secondary" id="aa-cambiar-receptor">Cambiar receptor</button><button type="button" class="ec-btn secondary" id="aa-cambiar-agencia-arqueo">Cambiar agencia</button></div>
    <section class="ec-section"><div class="ec-info">
     <div class="ec-info-item wide"><label>Nombre del receptor</label><div id="aa-info-receptor">—</div></div>
     <div class="ec-info-item"><label>Fecha operativa</label><div>{{ now()->format('d/m/Y') }}</div></div>
     <div class="ec-info-item"><label>Turno</label><div id="aa-info-turno">—</div></div>
     <div class="ec-info-item"><label>Agencia</label><div id="aa-info-agencia">—</div></div>
     <div class="ec-info-item"><label>Ventanilla</label><div id="aa-info-ventanilla">—</div></div>
     <div class="ec-info-item"><label>Hora de inicio</label><div id="aa-hora-inicio">—</div></div>
     <div class="ec-info-item"><label>Hora de finalización</label><div>Pendiente de finalizar</div></div>
    </div></section>
    <section class="ec-section"><div class="ec-count-grid">
     <div><h3>Billetes</h3><table class="ec-table"><thead><tr><th>Denominación</th><th>Cantidad</th><th>Sub-total</th></tr></thead><tbody>
      @foreach($billetes as $v)<tr><td>Q {{ number_format((float)$v,2) }}</td><td><input type="number" class="ec-quantity aa-denom" name="billetes[{{ $v }}]" value="{{ old('billetes.'.$v,0) }}" min="0" max="10000000" step="1" data-centavos="{{ (int)round((float)$v*100) }}" required></td><td><span class="ec-subtotal aa-sub">Q 0.00</span></td></tr>@endforeach
     </tbody></table><div class="ec-count-total"><span>Total billetes</span><strong id="aa-total-billetes">Q 0.00</strong></div></div>
     <div><h3>Monedas</h3><table class="ec-table"><thead><tr><th>Denominación</th><th>Cantidad</th><th>Sub-total</th></tr></thead><tbody>
      @foreach($monedas as $v)<tr><td>Q {{ number_format((float)$v,2) }}</td><td><input type="number" class="ec-quantity aa-denom" name="monedas[{{ $v }}]" value="{{ old('monedas.'.$v,0) }}" min="0" max="10000000" step="1" data-centavos="{{ (int)round((float)$v*100) }}" required></td><td><span class="ec-subtotal aa-sub">Q 0.00</span></td></tr>@endforeach
     </tbody></table><div class="ec-count-total"><span>Total monedas</span><strong id="aa-total-monedas">Q 0.00</strong></div></div>
    </div></section>
    <div class="ec-grand-total"><span>TOTAL ARQUEADO</span><strong id="aa-total">Q 0.00</strong></div>
    <section class="ec-section"><div class="ec-check-grid">
     <div><h3>Justificación / Observaciones</h3><label class="ec-field-label" for="aa-just">Justificación de la diferencia</label><textarea id="aa-just" class="ec-textarea" name="justificacion_diferencia" rows="4" maxlength="2000" placeholder="Describa el motivo del sobrante o faltante.">{{ old('justificacion_diferencia') }}</textarea><p class="ec-note">Obligatoria cuando exista sobrante o faltante.</p><label class="ec-field-label" for="aa-observaciones">Observaciones</label><textarea id="aa-observaciones" class="ec-textarea" name="observaciones" rows="4" maxlength="5000">{{ old('observaciones') }}</textarea></div>
     <div class="ec-check-box"><h3>COMPROBACIÓN</h3><div class="ec-check-line"><span>Total arqueado</span><strong id="aa-comprobacion">Q 0.00</strong></div><div class="ec-check-line"><label for="aa-corte">Total corte bancario</label><input id="aa-corte" class="ec-money-input" type="number" name="total_corte" value="{{ old('total_corte','0.00') }}" min="0" max="999999999999.99" step="0.01" required></div><div class="ec-check-line difference"><span>Diferencia</span><strong id="aa-diff">Q 0.00</strong></div><div class="ec-result" id="aa-resultado">Cuadrado</div></div>
    </div></section>
    <section class="ec-section"><div class="ec-signatures"><div class="ec-signature"><small>Se firma al registrar el arqueo</small><strong>Auditoría</strong></div><div class="ec-signature"><small>Pendiente de firma electrónica</small><strong>Receptor - Pagador</strong></div><div class="ec-signature"><small>Pendiente de certificación</small><strong>Jefe / Subjefe de Caja</strong></div></div></section>
    <section class="ec-confirmation"><h3>Firma electrónica de Auditoría</h3><p>Al confirmar se registrará el conteo y la firma electrónica del auditor. Posteriormente firmará el receptor y certificará Jefatura de Caja.</p><label class="ec-checkbox"><input type="checkbox" name="confirmar" value="1" required @checked(old('confirmar'))><span>Confirmo que he realizado y revisado el conteo y autorizo mi firma electrónica.</span></label></section>
    <div class="ec-actions"><button type="button" class="ec-btn secondary" id="aa-cancelar">Volver a seleccionar receptor</button><button type="submit" id="aa-submit" class="ec-btn">Registrar y firmar arqueo</button></div>
   </div>
  </div>
 </div>
 </form>
</div>
<div id="aa-modal" class="ec-modal" role="dialog" aria-modal="true" aria-labelledby="aa-modal-title" hidden><div class="ec-dialog"><div class="ec-dialog-head"><h2 id="aa-modal-title">Confirmar firma electrónica</h2><p>Revise los importes antes de registrar definitivamente el arqueo.</p></div><div class="ec-dialog-body"><div class="ec-dialog-row"><span>Agencia</span><strong id="aa-modal-agencia">—</strong></div><div class="ec-dialog-row"><span>Receptor</span><strong id="aa-modal-receptor">—</strong></div><div class="ec-dialog-row"><span>Total billetes</span><strong id="aa-modal-billetes">Q 0.00</strong></div><div class="ec-dialog-row"><span>Total monedas</span><strong id="aa-modal-monedas">Q 0.00</strong></div><div class="ec-dialog-row"><span>Total arqueado</span><strong id="aa-modal-total">Q 0.00</strong></div><div class="ec-dialog-row"><span>Corte bancario</span><strong id="aa-modal-corte">Q 0.00</strong></div><div class="ec-dialog-row"><span>Diferencia</span><strong id="aa-modal-diff">Q 0.00</strong></div><div class="ec-warning"><strong>Importante:</strong> el arqueo y su firma electrónica quedarán registrados. Los importes no podrán modificarse posteriormente.</div></div><div class="ec-dialog-actions"><button type="button" class="ec-btn secondary" id="aa-modal-volver">Volver a revisar</button><button type="button" class="ec-btn" id="aa-modal-confirmar">Confirmar y firmar</button></div></div></div>
@endsection
@push('scripts')
<script>
(function(){
'use strict';
const asignaciones=@json($opcionesAsignaciones);
const anterior=@json((string)old('asignacion_operativa_id',''));
const $=id=>document.getElementById(id);
const agencia=$('aa-agencia'), receptor=$('aa-receptor'), asignacion=$('aa-asignacion');
const pa=$('aa-paso-agencia'),pr=$('aa-paso-receptor'),ps=$('aa-paso-asignacion'),pc=$('aa-paso-arqueo');
const form=$('aa-form'),modal=$('aa-modal');
let elegido=null,confirmado=false,enviando=false;
function paso(n){pa.classList.toggle('aa-hidden',n!==1);pr.classList.toggle('aa-hidden',n!==2);pc.classList.toggle('aa-hidden',n!==3);if(n===2){receptor.focus();}if(n===3){pc.scrollIntoView({behavior:'smooth',block:'start'});}}
function limpiar(sel,texto){sel.replaceChildren(new Option(texto,''));sel.value='';}
function cambiarAgencia(){elegido=null;limpiar(receptor,'Seleccione un receptor');limpiar(asignacion,'Seleccione ventanilla y turno');asignacion.disabled=true;ps.classList.add('aa-hidden');const id=agencia.value;if(!id){paso(1);return;}const nombre=agencia.options[agencia.selectedIndex].textContent;$('aa-agencia-nombre').textContent=nombre;const unicos=new Map();asignaciones.filter(a=>String(a.agencia_id)===id).forEach(a=>unicos.set(String(a.receptor_id),a.receptor_nombre||`Receptor ${a.receptor_id}`));[...unicos].sort((a,b)=>a[1].localeCompare(b[1],'es')).forEach(([id,nombre])=>receptor.add(new Option(nombre,id)));receptor.disabled=unicos.size===0;$('aa-sin-receptores').classList.toggle('aa-hidden',unicos.size!==0);paso(2);}
function cambiarReceptor(){elegido=null;limpiar(asignacion,'Seleccione ventanilla y turno');const disponibles=asignaciones.filter(a=>String(a.agencia_id)===agencia.value&&String(a.receptor_id)===receptor.value);disponibles.forEach(a=>asignacion.add(new Option(`Ventanilla ${a.ventanilla||'—'} · ${a.turno||'—'}`,a.id)));asignacion.disabled=disponibles.length===0;ps.classList.toggle('aa-hidden',disponibles.length===0);if(disponibles.length===1){asignacion.value=String(disponibles[0].id);cambiarAsignacion();}}
function cambiarAsignacion(){elegido=asignaciones.find(a=>String(a.id)===asignacion.value&&String(a.agencia_id)===agencia.value&&String(a.receptor_id)===receptor.value)||null;if(!elegido){paso(2);return;} $('aa-paper-agencia').textContent=elegido.agencia_nombre||'Agencia';$('aa-info-agencia').textContent=elegido.agencia_nombre||'—';$('aa-info-receptor').textContent=elegido.receptor_nombre||'—';$('aa-info-ventanilla').textContent=elegido.ventanilla||'—';$('aa-info-turno').textContent=elegido.turno||'—';$('aa-hora-inicio').textContent=new Date().toLocaleTimeString('es-GT',{hour12:false});paso(3);calcular();}
function cambiarReceptorVolver(){elegido=null;receptor.value='';limpiar(asignacion,'Seleccione ventanilla y turno');asignacion.disabled=true;ps.classList.add('aa-hidden');paso(2);receptor.focus();}
function cambiarAgenciaVolver(){elegido=null;agencia.value='';limpiar(receptor,'Seleccione un receptor');receptor.disabled=true;limpiar(asignacion,'Seleccione ventanilla y turno');asignacion.disabled=true;ps.classList.add('aa-hidden');paso(1);agencia.focus();}
function dinero(c){return 'Q '+(Math.abs(c)/100).toLocaleString('es-GT',{minimumFractionDigits:2,maximumFractionDigits:2});}
function centavos(valor){const s=String(valor??'').trim();if(!/^\d+(\.\d{1,2})?$/.test(s))return 0;const p=s.split('.');return Number(p[0])*100+Number((p[1]||'').padEnd(2,'0'));}
let resumen={billetes:0,monedas:0,total:0,corte:0,diff:0};
function calcular(){let billetes=0,monedas=0;form.querySelectorAll('.aa-denom').forEach(el=>{const n=Number(el.value),c=Number(el.dataset.centavos);const subtotal=Number.isSafeInteger(n)&&n>=0&&Number.isSafeInteger(n*c)?n*c:0;el.closest('tr').querySelector('.aa-sub').textContent=dinero(subtotal);if(el.name.startsWith('billetes'))billetes+=subtotal;else monedas+=subtotal;});const total=billetes+monedas,corte=centavos($('aa-corte').value),diff=total-corte;resumen={billetes,monedas,total,corte,diff};$('aa-total-billetes').textContent=dinero(billetes);$('aa-total-monedas').textContent=dinero(monedas);$('aa-total').textContent=dinero(total);$('aa-comprobacion').textContent=dinero(total);$('aa-diff').textContent=(diff<0?'-':'')+dinero(diff);$('aa-resultado').textContent=diff>0?'Sobrante':diff<0?'Faltante':'Cuadrado';$('aa-resultado').style.color=diff===0?'#157347':'#a04427';$('aa-just').required=diff!==0;}
function abrirModal(){calcular();$('aa-modal-agencia').textContent=elegido.agencia_nombre;$('aa-modal-receptor').textContent=elegido.receptor_nombre;$('aa-modal-billetes').textContent=dinero(resumen.billetes);$('aa-modal-monedas').textContent=dinero(resumen.monedas);$('aa-modal-total').textContent=dinero(resumen.total);$('aa-modal-corte').textContent=dinero(resumen.corte);$('aa-modal-diff').textContent=(resumen.diff<0?'-':'')+dinero(resumen.diff);modal.hidden=false;document.body.classList.add('ec-modal-open');$('aa-modal-volver').focus();}
function cerrarModal(){if(enviando)return;modal.hidden=true;document.body.classList.remove('ec-modal-open');$('aa-submit').focus();}
agencia.addEventListener('change',cambiarAgencia);receptor.addEventListener('change',cambiarReceptor);asignacion.addEventListener('change',cambiarAsignacion);
$('aa-cambiar-agencia').addEventListener('click',cambiarAgenciaVolver);$('aa-cambiar-agencia-arqueo').addEventListener('click',cambiarAgenciaVolver);$('aa-cambiar-receptor').addEventListener('click',cambiarReceptorVolver);$('aa-cancelar').addEventListener('click',cambiarReceptorVolver);
form.querySelectorAll('.aa-denom,#aa-corte').forEach(el=>el.addEventListener('input',calcular));
form.addEventListener('submit',e=>{if(confirmado)return;e.preventDefault();if(!elegido||asignacion.disabled||!asignacion.value){alert('Seleccione una agencia, receptor y asignación operativa.');return;}if(!form.reportValidity())return;abrirModal();});
$('aa-modal-volver').addEventListener('click',cerrarModal);modal.addEventListener('click',e=>{if(e.target===modal)cerrarModal();});document.addEventListener('keydown',e=>{if(!modal.hidden&&e.key==='Escape')cerrarModal();});
$('aa-modal-confirmar').addEventListener('click',()=>{if(enviando)return;if(!form.reportValidity()){cerrarModal();return;}enviando=true;confirmado=true;$('aa-modal-confirmar').disabled=true;$('aa-modal-volver').disabled=true;$('aa-submit').disabled=true;form.requestSubmit();});
window.addEventListener('pageshow',()=>{enviando=false;confirmado=false;$('aa-modal-confirmar').disabled=false;$('aa-modal-volver').disabled=false;$('aa-submit').disabled=false;modal.hidden=true;document.body.classList.remove('ec-modal-open');});
calcular();if(anterior){const a=asignaciones.find(x=>String(x.id)===anterior);if(a){agencia.value=String(a.agencia_id);cambiarAgencia();receptor.value=String(a.receptor_id);cambiarReceptor();asignacion.value=anterior;cambiarAsignacion();}}else{paso(1);}
})();
</script>
@endpush
