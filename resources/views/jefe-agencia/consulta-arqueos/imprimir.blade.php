<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Arqueo {{ $registro->numero_arqueo }}</title>
<style>@page{size:letter portrait;margin:15mm}body{font-family:DejaVu Sans,sans-serif;font-size:10px;color:#23374c}h1{font-size:17px;color:#164c96;margin-bottom:4px}h2{font-size:12px;margin-top:22px;color:#164c96}.meta,.detalles{width:100%;border-collapse:collapse;margin-top:14px}.meta td{padding:8px;border-bottom:1px solid #dce4ec;width:50%}.detalles th,.detalles td{padding:8px;border-bottom:1px solid #dce4ec;text-align:left}.detalles th{background:#edf3f9}.right{text-align:right!important}.small{font-size:9px;color:#62758b}.total{font-size:15px;font-weight:bold;text-align:right;margin-top:20px}</style></head>
<body>
@if($logoData)<img src="{{ $logoData }}" style="height:52px;max-width:160px">@endif
<h1>ARQUEO DE {{ $registro->tipo === 'AUDITORIA' ? 'AUDITORÍA' : 'JEFATURA DE CAJA' }}</h1>
<div class="small">Documento de consulta · N.º {{ $registro->numero_arqueo }}</div>
<table class="meta"><tr><td><b>Agencia:</b> {{ $registro->agencia_nombre_historico ?: $registro->agencia?->nombre }}</td><td><b>Fecha:</b> {{ $registro->fecha_operativa?->format('d/m/Y') }}</td></tr>
<tr><td><b>Receptor:</b> {{ $registro->receptor_nombre_historico ?: $registro->receptor?->nombre_completo }}</td><td><b>Estado:</b> {{ $registro->estado }}</td></tr>
<tr><td><b>Ventanilla:</b> {{ $registro->ventanilla_codigo_historico ?: $registro->ventanilla?->codigo }}</td><td><b>Inicio:</b> {{ $registro->inicio_at?->format('d/m/Y H:i') ?? '—' }}</td></tr></table>
<h2>Detalle del efectivo y otros valores</h2>
<table class="detalles"><thead><tr><th>Tipo</th><th>Denominación / concepto</th><th class="right">Cantidad</th><th class="right">Subtotal</th></tr></thead><tbody>
@foreach($registro->detalles as $detalle)<tr><td>{{ $detalle->tipo }}</td><td>{{ $detalle->concepto ?: ('Q '.number_format((float)$detalle->denominacion,2)) }}</td><td class="right">{{ $detalle->cantidad }}</td><td class="right">Q {{ number_format((float)$detalle->subtotal,2) }}</td></tr>@endforeach
</tbody></table>
<div class="total">Total arqueado: Q {{ number_format((float)$registro->total_arqueado,2) }}</div>
<table class="meta"><tr><td><b>Total de corte:</b> Q {{ number_format((float)$registro->total_corte,2) }}</td><td><b>Diferencia:</b> Q {{ number_format((float)$registro->diferencia,2) }}</td></tr></table>
@if($registro->observaciones)<h2>Observaciones</h2><p>{{ $registro->observaciones }}</p>@endif
@if($registro->justificacion_diferencia)<h2>Justificación de diferencia</h2><p>{{ $registro->justificacion_diferencia }}</p>@endif
<h2>Firmas electrónicas registradas</h2>
@forelse($registro->firmas as $firma)<p>{{ $firma->tipo_firma }} — {{ trim(($firma->nombres_historicos ?? '').' '.($firma->apellidos_historicos ?? '')) }} — {{ $firma->fecha_firma?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>@empty<p>No hay firmas registradas.</p>@endforelse
<p class="small" style="margin-top:25px">Documento generado desde el Sistema Integral de Arqueos. Consulta de solo lectura.</p>
</body></html>
