@extends('layouts.jefe-agencia')
@section('title', 'Detalle de Arqueo')
@section('content')
<div class="page-header"><div class="page-title"><h2>Detalle de Arqueo {{ $arqueo->numero_arqueo }}</h2><p>Información de solo lectura.</p></div><a href="{{ url()->previous() }}" style="color:#164c96;font-weight:700">Volver</a></div>
<div style="margin-bottom:16px"><a href="{{ route('jefeagencia.historial-arqueos.imprimir', $arqueo->id) }}" target="_blank" style="display:inline-block;background:#164c96;color:white;padding:11px 18px;border-radius:9px;text-decoration:none;font-weight:700">Ver / imprimir PDF</a></div>
<div style="background:white;border:1px solid #dfe7ee;border-radius:15px;padding:24px"><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:18px">
@foreach(['Número' => $arqueo->numero_arqueo,'Fecha' => $arqueo->fecha_operativa?->format('d/m/Y'),'Agencia' => ($arqueo->agencia?->nombre ?? $arqueo->agencia_nombre_historico),'Ventanilla' => ($arqueo->ventanilla?->codigo ?? $arqueo->ventanilla_codigo_historico),'Tipo' => $arqueo->tipo,'Estado' => $arqueo->estado,'Receptor' => ($arqueo->receptor_nombre_historico ?: ($arqueo->receptor?->nombre_completo ?? '—')),'Total arqueado' => 'Q '.number_format((float)$arqueo->total_arqueado,2),'Total corte' => 'Q '.number_format((float)$arqueo->total_corte,2),'Diferencia' => 'Q '.number_format((float)$arqueo->diferencia,2),'Firmado receptor' => ($arqueo->firmado_receptor_at?->format('d/m/Y H:i') ?? 'Pendiente'),'Certificado' => ($arqueo->certificado_at?->format('d/m/Y H:i') ?? 'Pendiente')] as $etiqueta => $valor)<div><div style="color:#6d7d8a;font-size:12px">{{ $etiqueta }}</div><strong>{{ $valor }}</strong></div>@endforeach
</div>
@if($arqueo->observaciones)<hr style="margin:20px 0;border:0;border-top:1px solid #ddd"><h3>Observaciones</h3><p>{{ $arqueo->observaciones }}</p>@endif
@if($arqueo->justificacion_diferencia)<h3>Justificación de diferencia</h3><p>{{ $arqueo->justificacion_diferencia }}</p>@endif
@if($arqueo->detalles->isNotEmpty())<h3>Detalle de denominaciones</h3><div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse"><thead><tr><th>Denominación</th><th>Cantidad</th><th>Subtotal</th></tr></thead><tbody>@foreach($arqueo->detalles as $detalle)<tr style="border-top:1px solid #eee"><td style="padding:10px">{{ $detalle->denominacion ?? '—' }}</td><td>{{ $detalle->cantidad ?? '—' }}</td><td>Q {{ number_format((float)($detalle->subtotal ?? 0),2) }}</td></tr>@endforeach</tbody></table></div>@endif
</div>
@endsection
