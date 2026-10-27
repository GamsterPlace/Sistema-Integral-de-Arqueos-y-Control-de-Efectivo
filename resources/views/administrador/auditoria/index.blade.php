@extends('layouts.administrador')
@section('title','Auditoría')
@section('module-title','Auditoría')
@section('content')
<div class="page-header"><div class="page-title"><h2>Auditoría del Sistema</h2><p>Consulta de actividad y trazabilidad administrativa.</p></div></div>
@if(!$tablaDisponible)
<div style="padding:18px;border:1px solid #efd99f;border-radius:14px;background:#fffdf6;color:#7a5b00;">La tabla <strong>auditoria</strong> aún no está disponible o su estructura debe revisarse. El módulo queda preparado y lo ajustaremos en la fase de correcciones.</div>
@else
<form method="GET" action="{{ route('administrador.auditoria.index') }}" style="padding:18px;border:1px solid #e0e8ee;border-radius:18px;background:#fff;margin-bottom:20px;"><input name="buscar" value="{{ $buscar }}" placeholder="Buscar en auditoría" style="width:100%;min-height:42px;padding:0 12px;border:1px solid #ced9e1;border-radius:10px;"></form>
<div style="overflow:auto;border:1px solid #e0e8ee;border-radius:18px;background:#fff;"><table style="width:100%;border-collapse:collapse;"><tbody>@forelse($registros as $registro)<tr style="border-bottom:1px solid #edf1f4;"><td style="padding:12px;"><pre style="margin:0;white-space:pre-wrap;font-family:inherit;">{{ json_encode($registro, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></td></tr>@empty<tr><td style="padding:30px;text-align:center;">Sin registros.</td></tr>@endforelse</tbody></table>@if(method_exists($registros,'hasPages') && $registros->hasPages())<div style="padding:16px;">{{ $registros->links() }}</div>@endif</div>
@endif
@endsection
