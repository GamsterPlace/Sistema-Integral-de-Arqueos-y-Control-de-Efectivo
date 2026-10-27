<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $titulo }}</title>
@php
    $rutaLogoEcosaba=public_path('images/logos/ecosaba.png');
    $rutaLogoAgentes=public_path('images/logos/agentes-micoope.png');
    $logoEcosaba=file_exists($rutaLogoEcosaba)?'data:image/png;base64,'.base64_encode(file_get_contents($rutaLogoEcosaba)):null;
    $logoAgentes=file_exists($rutaLogoAgentes)?'data:image/png;base64,'.base64_encode(file_get_contents($rutaLogoAgentes)):null;
@endphp
<style>
@page{size:letter landscape;margin:24px 28px 28px}
*{box-sizing:border-box}
body{font-family:DejaVu Sans,sans-serif;color:#263d50;font-size:7.2px;margin:0}
.header{width:100%;border-collapse:collapse;margin-bottom:5px}.header td{border:0;vertical-align:middle;padding:0}
.logo{width:120px;max-height:42px}.head-center{text-align:center}.head-center h1{margin:0;color:#12375f;font-size:15px;text-transform:uppercase}.head-center p{margin:3px 0 0;color:#718493;font-size:7px}
.rule{height:3px;background:#00a651;margin:5px 0 0}.yellow{height:3px;width:28%;background:#f0c419;margin-top:-3px;margin-bottom:10px}
.report-title{margin:0 0 8px;padding:7px 9px;background:#164c96;color:#fff;font-size:10px;text-transform:uppercase}
.metrics{width:100%;margin-bottom:10px;border-collapse:collapse;table-layout:fixed}.metrics td{padding:7px;border:1px solid #dce5eb;background:#f8fafb}.metrics span{display:block;color:#718493;font-size:5.7px;font-weight:bold;text-transform:uppercase}.metrics strong{display:block;margin-top:3px;color:#12375f;font-size:10px}
.results{width:100%;border-collapse:collapse}.results th{padding:6px 4px;border:1px solid #d5dfe6;background:#173e6b;color:#fff;font-size:5.8px;text-transform:uppercase}.results td{padding:5px 4px;border:1px solid #dce5eb;vertical-align:top;font-size:6.4px}.results tr:nth-child(even) td{background:#f9fbfc}
.footer{margin-top:9px;padding-top:6px;border-top:1px solid #dce5eb;color:#7b8d9a;font-size:5.5px;text-align:center}
.watermark{position:fixed;top:150px;left:300px;width:190px;opacity:.025;z-index:-1}
</style>
</head>
<body>
@if($logoAgentes)<img class="watermark" src="{{ $logoAgentes }}" alt="">@endif
<table class="header">
<tr>
<td style="width:27%;">@if($logoEcosaba)<img class="logo" src="{{ $logoEcosaba }}" alt="ECOSABA">@endif</td>
<td class="head-center" style="width:46%;"><h1>Sistema de Arqueos</h1><p>Agentes MICOOPE · Reportes Administrativos</p></td>
<td style="width:27%;text-align:right;color:#607586;font-size:6.5px;">Generado<br><strong>{{ now()->format('d/m/Y H:i') }}</strong></td>
</tr>
</table>
<div class="rule"></div><div class="yellow"></div>
<div class="report-title">{{ $titulo }}</div>

@if(count($metricas))
<table class="metrics"><tr>
@foreach($metricas as $etiqueta=>$valor)<td><span>{{ $etiqueta }}</span><strong>{{ $valor }}</strong></td>@endforeach
</tr></table>
@endif

<table class="results">
<thead><tr>@foreach($columnas as $tituloColumna)<th>{{ $tituloColumna }}</th>@endforeach</tr></thead>
<tbody>
@forelse($resultados as $indice=>$fila)
<tr>
@foreach($columnas as $columna=>$tituloColumna)
<td>
@switch($columna)
@case('posicion') {{ $indice+1 }} @break
@case('agente') {{ $fila->codigo_agente }} — {{ $fila->nombre_negocio }} @break
@case('ruta') {{ $fila->ruta_codigo }} — {{ $fila->ruta_nombre }} @break
@case('responsable')
@php $responsable=trim(($fila->responsable_nombres??'').' '.($fila->responsable_apellidos??'')); @endphp
{{ $responsable!==''?$responsable:($fila->responsable_usuario??'—') }}
@break
@case('fecha_arqueo') {{ \Carbon\Carbon::parse($fila->fecha_arqueo)->format('d/m/Y') }} @break
@case('tipo') {{ $fila->tipo==='DIARIO_AGENTE'?'Agente':($fila->tipo==='VISITA_PROMOTOR'?'Promotor':($fila->tipo==='VISITA_AUDITORIA'?'Auditoría':str_replace('_',' ',$fila->tipo))) }} @break
@case('estado') {{ str_replace('_',' ',$fila->estado) }} @break
@case('diferencia')
@php $dif=(float)$fila->diferencia; @endphp
Q {{ number_format(abs($dif),2) }} {{ $dif<0?'Faltante':($dif>0?'Sobrante':'Exacto') }}
@break
@case('saldo_sistema')
@case('total_arqueado')
@case('monto_acumulado')
@case('mayor_incidencia')
Q {{ number_format((float)$fila->{$columna},2) }}
@break
@default {{ $fila->{$columna}??'—' }}
@endswitch
</td>
@endforeach
</tr>
@empty
<tr><td colspan="{{ count($columnas) }}" style="padding:25px;text-align:center;">Sin resultados para los filtros seleccionados.</td></tr>
@endforelse
</tbody>
</table>
<div class="footer">Sistema de Arqueos para Agentes MICOOPE · Reporte administrativo generado automáticamente</div>
</body>
</html>
