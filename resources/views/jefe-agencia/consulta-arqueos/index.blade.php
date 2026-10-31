@extends('layouts.jefe-agencia')
@section('title', $titulo)
@section('content')
<div class="page-header"><div class="page-title"><h2>{{ $titulo }}</h2><p>Consulta de registros de las agencias bajo su responsabilidad.</p></div></div>
<div style="background:#fff;border:1px solid #dfe7ee;border-radius:15px;padding:22px;margin-bottom:20px">
<form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:end">
<label>Agencia<br><select name="agencia_id" style="padding:10px;border:1px solid #ddd;border-radius:8px"><option value="">Todas</option>@foreach($agencias as $agencia)<option value="{{ $agencia->id }}" @selected(($filtros['agencia_id'] ?? '') == $agencia->id)>{{ $agencia->nombre }}</option>@endforeach</select></label>
<label>Desde<br><input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}" style="padding:10px;border:1px solid #ddd;border-radius:8px"></label>
<label>Hasta<br><input type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" style="padding:10px;border:1px solid #ddd;border-radius:8px"></label>
<label>Estado<br><select name="estado" style="padding:10px;border:1px solid #ddd;border-radius:8px"><option value="">Todos</option>@foreach(['PENDIENTE','CERTIFICADO','ANULADO'] as $estado)<option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ $estado }}</option>@endforeach</select></label>
<label>Número de arqueo<br><input name="numero_arqueo" value="{{ $filtros['numero_arqueo'] ?? '' }}" style="padding:10px;border:1px solid #ddd;border-radius:8px"></label>
<button type="submit" style="padding:11px 18px;background:#164c96;color:white;border:0;border-radius:8px;cursor:pointer">Filtrar</button>
</form></div>
<div style="background:white;border:1px solid #dfe7ee;border-radius:15px;overflow-x:auto"><table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px"><thead style="background:#eef4fa"><tr>@foreach(['Número','Fecha','Agencia','Ventanilla','Tipo','Receptor','Estado','Total','Acción'] as $col)<th style="padding:14px">{{ $col }}</th>@endforeach</tr></thead><tbody>@forelse($arqueos as $arqueo)<tr style="border-top:1px solid #e8edf3"><td style="padding:13px">{{ $arqueo->numero_arqueo }}</td><td>{{ $arqueo->fecha_operativa?->format('d/m/Y') }}</td><td>{{ $arqueo->agencia?->nombre ?? $arqueo->agencia_nombre_historico }}</td><td>{{ $arqueo->ventanilla?->codigo ?? $arqueo->ventanilla_codigo_historico }}</td><td>{{ $arqueo->tipo }}</td><td>{{ $arqueo->receptor_nombre_historico ?: ($arqueo->receptor?->nombre_completo ?? '—') }}</td><td>{{ $arqueo->estado }}</td><td>Q {{ number_format((float)$arqueo->total_arqueado,2) }}</td><td><a style="color:#164c96;font-weight:700" href="{{ route('jefeagencia.historial-arqueos.show',$arqueo->id) }}">Ver detalle</a></td></tr>@empty<tr><td colspan="9" style="padding:25px;text-align:center">No hay arqueos para los filtros seleccionados.</td></tr>@endforelse</tbody></table></div>
<div style="margin-top:20px">{{ $arqueos->links() }}</div>
@endsection
