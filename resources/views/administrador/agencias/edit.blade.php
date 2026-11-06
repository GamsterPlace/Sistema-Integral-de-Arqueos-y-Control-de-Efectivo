@extends('layouts.administrador')
@section('title', 'Editar Agencia')
@section('module-title', 'Agencias')
@push('styles')
<style>

.ag-page{max-width:1500px;margin:auto;color:#173b59}
.ag-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:22px}
.ag-title{display:flex;align-items:center;gap:15px}
.ag-icon{width:49px;height:49px;display:grid;place-items:center;border-radius:14px;background:linear-gradient(135deg,#164c96,#1c64b5);color:white;box-shadow:0 9px 20px #164c9629}
.ag-icon svg,.ag-btn svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.ag-title h2{font-size:26px;color:#06284f;margin:0}.ag-title p{font-size:12px;color:#718391;margin:6px 0 0}
.ag-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 17px;border:1px solid #d5e0e7;border-radius:11px;background:#fff;color:#526a7c;text-decoration:none;font-size:11px;font-weight:800;cursor:pointer}
.ag-btn.primary{background:#164c96;border-color:#164c96;color:#fff}.ag-btn.danger{background:#fff5f5;border-color:#f1cfcf;color:#a33c3c}
.ag-btn:hover{filter:brightness(.96)}
.ag-card{background:#fff;border:1px solid #dfe7ed;border-radius:18px;overflow:hidden;box-shadow:0 9px 25px #1439530a;margin-bottom:20px}
.ag-card-head{padding:17px 21px;background:#fbfcfd;border-bottom:1px solid #e7edf1}
.ag-card-head h3{font-size:15px;margin:0;color:#0b315f}.ag-card-head p{font-size:10px;color:#83919c;margin:5px 0 0}
.ag-card-body{padding:21px}
.ag-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}
.ag-field.full{grid-column:1/-1}
.ag-field label{display:block;margin-bottom:7px;color:#526a7c;font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.3px}
.ag-control{width:100%;box-sizing:border-box;min-height:44px;border:1px solid #ced9e1;border-radius:10px;padding:10px 12px;color:#30485b;background:#fff;font-size:12px}
.ag-control:focus{outline:none;border-color:#5d8fc6;box-shadow:0 0 0 3px #164c9616}
textarea.ag-control{min-height:88px;resize:vertical}
.ag-help{font-size:10px;color:#81919f;margin-top:6px}
.ag-footer{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:18px 21px;background:#fafcfd;border-top:1px solid #e7edf1}
.ag-footer p{font-size:10px;color:#81919f;margin:0}
.ag-actions{display:flex;gap:9px;flex-wrap:wrap}
.ag-error{background:#fff6f6;border:1px solid #efcaca;color:#9d3838;padding:15px 20px;border-radius:12px;margin-bottom:18px;font-size:12px}
.ag-error ul{margin:8px 0 0;padding-left:18px}
.ag-table-wrap{overflow-x:auto}
.ag-table{width:100%;border-collapse:collapse;min-width:650px}
.ag-table th{font-size:10px;text-transform:uppercase;color:#687d8d;background:#f7f9fb;text-align:left;padding:13px 16px}
.ag-table td{padding:13px 16px;border-top:1px solid #edf1f4;font-size:12px;color:#415b6e}
.ag-table tr:hover td{background:#fafcfd}
.ag-tag{display:inline-block;padding:6px 10px;border-radius:20px;background:#effaf3;color:#197245;font-size:10px;font-weight:800}
.ag-tag.off{background:#fff4f4;color:#a33c3c}
.ag-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}
.ag-stat{padding:19px;background:white;border:1px solid #dfe7ed;border-radius:16px}
.ag-stat span{display:block;font-size:10px;color:#758697;text-transform:uppercase;font-weight:800}
.ag-stat strong{display:block;margin-top:8px;font-size:25px;color:#082d55}
.ag-window{display:grid;grid-template-columns:1fr 1.5fr 135px 42px;gap:10px;align-items:end;margin-bottom:12px}
.ag-window .ag-field label{font-size:9px}
.ag-empty{text-align:center;padding:32px;color:#81919f;font-size:12px}
@media(max-width:900px){.ag-stats{grid-template-columns:repeat(2,1fr)}.ag-window{grid-template-columns:1fr 1fr}}
@media(max-width:650px){.ag-head{align-items:flex-start;flex-direction:column}.ag-grid,.ag-stats,.ag-window{grid-template-columns:1fr}.ag-field.full{grid-column:auto}.ag-footer{flex-direction:column;align-items:stretch}.ag-actions{flex-direction:column}.ag-btn{width:100%}}

</style>
@endpush
@section('content')
<div class="ag-page"><header class="ag-head"><div class="ag-title"><div class="ag-icon"><svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M8 10h.01M12 10h.01M16 10h.01"/></svg></div><div><h2>Editar Agencia</h2><p>Actualice la información institucional y administre las ventanillas.</p></div></div><a class="ag-btn" href="{{ route('administrador.agencias.index') }}">Regresar</a></header>@if($errors->any())<div class="ag-error"><strong>Revise la información ingresada:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('administrador.agencias.update', $agencia->id) }}" class="ag-card">
@csrf @method('PUT')
<div class="ag-card-head"><h3>01 · Información institucional</h3><p>Agencia {{ $agencia->codigo }} · {{ $agencia->nombre }}</p></div>
<div class="ag-card-body"><div class="ag-grid">
<div class="ag-field "><label for="codigo">Código de agencia *</label><input id="codigo" name="codigo" class="ag-control" value="{{ old('codigo',$agencia->codigo) }}" maxlength="30" required></div><div class="ag-field "><label for="nombre">Nombre de agencia *</label><input id="nombre" name="nombre" class="ag-control" value="{{ old('nombre',$agencia->nombre) }}" maxlength="150" required></div><div class="ag-field full"><label for="direccion">Dirección</label><textarea id="direccion" name="direccion" class="ag-control" maxlength="255">{{ old('direccion',$agencia->direccion) }}</textarea></div><div class="ag-field"><label for="estado">Estado de la agencia *</label><select id="estado" name="estado" class="ag-control" required><option value="1" @selected((string)(old('estado', (string)(int)$agencia->estado))==='1')>ACTIVA</option><option value="0" @selected((string)(old('estado', (string)(int)$agencia->estado))==='0')>INACTIVA</option></select></div>
</div></div>
<div class="ag-card-head"><h3>02 · Ventanillas registradas</h3><p>Puede actualizar su descripción y estado. El código permanece fijo para conservar las referencias históricas.</p></div>
<div class="ag-card-body">
@if($ventanillas->isEmpty())<p class="ag-help">Esta agencia aún no tiene ventanillas.</p>@endif
@foreach($ventanillas as $v)
<div class="ag-window">
<div class="ag-field"><label>Código</label><input class="ag-control" value="{{ $v->codigo }}" disabled></div>
<div class="ag-field"><label>Descripción</label><input class="ag-control" name="existentes[{{ $v->id }}][descripcion]" maxlength="150" value="{{ old('existentes.'.$v->id.'.descripcion', $v->descripcion) }}"></div>
<div class="ag-field"><label>Estado</label><select class="ag-control" name="existentes[{{ $v->id }}][estado]"><option value="1" @selected((string)old('existentes.'.$v->id.'.estado',(string)(int)$v->estado)==='1')>Activa</option><option value="0" @selected((string)old('existentes.'.$v->id.'.estado',(string)(int)$v->estado)==='0')>Inactiva</option></select></div>
<div></div>
</div>
@endforeach
</div>
<div class="ag-card-head"><h3>03 · Agregar nuevas ventanillas</h3><p>Las ventanillas nuevas se incorporarán sin borrar las anteriores.</p></div>
<div class="ag-card-body">
<div id="ventanillas-contenedor">
@foreach(old('ventanillas',[]) as $i => $v)
<div class="ag-window" data-ventanilla>
<div class="ag-field"><label>Código *</label><input class="ag-control" name="ventanillas[{{ $i }}][codigo]" maxlength="30" value="{{ $v['codigo'] ?? '' }}" required></div>
<div class="ag-field"><label>Descripción</label><input class="ag-control" name="ventanillas[{{ $i }}][descripcion]" maxlength="150" value="{{ $v['descripcion'] ?? '' }}"></div>
<div class="ag-field"><label>Estado</label><select class="ag-control" name="ventanillas[{{ $i }}][estado]"><option value="1" @selected((string)($v['estado'] ?? '1')==='1')>Activa</option><option value="0" @selected((string)($v['estado'] ?? '1')==='0')>Inactiva</option></select></div>
<button type="button" class="ag-btn danger" data-remove>×</button></div>
@endforeach
</div>
<button type="button" class="ag-btn" id="agregar-ventanilla">+ Agregar ventanilla</button>
</div>
<footer class="ag-footer"><p>Los cambios quedarán registrados en auditoría.</p><div class="ag-actions"><a class="ag-btn" href="{{ route('administrador.agencias.show',$agencia->id) }}">Cancelar</a><button class="ag-btn primary" type="submit">Guardar Cambios</button></div></footer>
</form></div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
 const contenedor = document.getElementById('ventanillas-contenedor');
 const agregar = document.getElementById('agregar-ventanilla');
 if (!contenedor || !agregar) return;
 let indice = 1000;
 agregar.addEventListener('click', () => {
   const i = indice++;
   const fila = document.createElement('div');
   fila.className = 'ag-window';
   fila.dataset.ventanilla = '';
   fila.innerHTML = `<div class="ag-field"><label>Código *</label><input class="ag-control" name="ventanillas[${i}][codigo]" maxlength="30" required></div>
    <div class="ag-field"><label>Descripción</label><input class="ag-control" name="ventanillas[${i}][descripcion]" maxlength="150"></div>
    <div class="ag-field"><label>Estado</label><select class="ag-control" name="ventanillas[${i}][estado]"><option value="1">Activa</option><option value="0">Inactiva</option></select></div>
    <button type="button" class="ag-btn danger" data-remove aria-label="Quitar ventanilla">×</button>`;
   contenedor.appendChild(fila);
   fila.querySelector('input').focus();
 });
 contenedor.addEventListener('click', event => {
   const boton = event.target.closest('[data-remove]');
   if (!boton) return;
   const filas = contenedor.querySelectorAll('[data-ventanilla]');
   if (filas.length === 1 && document.body.dataset.agenciaCreacion === '1') {
     alert('Debe registrar al menos una ventanilla.');
     return;
   }
   boton.closest('[data-ventanilla]').remove();
 });
});
</script>
@endpush
