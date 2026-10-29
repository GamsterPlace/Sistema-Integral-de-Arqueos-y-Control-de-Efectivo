
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Arqueo y corte de caja - {{ $registro->numero_arqueo }}</title>
<style>
@page { size:letter portrait; margin:11mm 13mm 12mm; }
*{box-sizing:border-box}
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:9px;color:#202a36;margin:0}
.head{width:100%;border-collapse:collapse;margin-bottom:13px}
.head td{vertical-align:middle}
.logo{width:148px;max-height:48px;object-fit:contain}
.brand{font-size:16px;font-weight:bold;color:#174b80}
.center{text-align:center}.right{text-align:right}
.heading{font-size:14px;font-weight:bold;letter-spacing:.2px}
.agency{font-size:11px;margin-top:3px}
.number{font-size:10px;color:#9c4646;letter-spacing:.7px}
.meta{width:100%;border-collapse:collapse;margin-bottom:12px}
.meta td{padding:5px 5px 5px 0;border-bottom:1px solid #d4dbe2}
.title{font-size:10px;font-weight:bold;margin:11px 0 5px;color:#193d62}
.count{width:77%;border-collapse:collapse;table-layout:fixed}
.count th{font-size:9px;text-align:left;border-bottom:1px solid #697c8d;padding:4px 6px}
.count td{border-bottom:1px solid #e0e5e9;padding:3px 6px;height:18px}
.count .money{text-align:right}
.subtotal{width:77%;text-align:right;font-weight:bold;padding:5px 6px;border-bottom:1px solid #697c8d}
.others{width:77%;border-collapse:collapse}
.others td{border-bottom:1px solid #e0e5e9;padding:5px}
.summary{width:50%;margin-left:50%;margin-top:12px;border-collapse:collapse}
.summary td{padding:5px;border-bottom:1px solid #b7c3ce}
.summary td:last-child{text-align:right;white-space:nowrap}
.cert{width:50%;margin-left:50%;margin-top:12px;min-height:63px;line-height:1.5;white-space:pre-wrap;overflow-wrap:break-word}
.signs{width:100%;border-collapse:collapse;margin-top:42px;page-break-inside:avoid}
.signs td{width:50%;padding:0 22px;vertical-align:bottom;text-align:center}
.sign-line{border-top:1px solid #506273;padding-top:7px;font-size:9px}
.sign-name{font-size:8px;margin-top:4px}
.status{font-size:7px;color:#596d7f;margin-top:4px}
.foot{font-size:7px;color:#5b6e7d;margin-top:13px;line-height:1.5}
</style>
</head>
<body>
@php
    $certificador = $registro->firmas->firstWhere('tipo_firma', 'CERTIFICADOR');
    $receptor = $registro->firmas->firstWhere('tipo_firma', 'RECEPTOR');

    $nombreFirma = fn ($firma) => $firma
        ? trim($firma->nombres_historicos.' '.$firma->apellidos_historicos)
        : 'Pendiente de firma';

    $moneda = fn ($monto) => 'Q '.number_format((float) $monto, 2, '.', ',');
@endphp

<table class="head">
<tr>
<td style="width:26%">
    @if($logoData)
        <img class="logo" src="{{ $logoData }}" alt="ECOSABA">
    @else
        <span class="brand">ECOSABA</span>
    @endif
</td>
<td class="center" style="width:49%">
    <div class="heading">ARQUEO Y CORTE DE CAJA</div>
    <div class="agency">{{ $registro->agencia_nombre_historico }}</div>
    <div style="font-size:8px;margin-top:3px">Jefatura de Caja</div>
</td>
<td class="right" style="width:25%">
    <span class="number">N.º {{ $registro->numero_arqueo }}</span>
</td>
</tr>
</table>

<table class="meta">
<tr>
<td style="width:43%">
    <b>Nombres:</b> {{ $registro->receptor_nombre_historico }}
</td>
<td style="width:22%">
    <b>Fecha:</b> {{ $registro->fecha_operativa?->format('d/m/Y') ?? '—' }}
</td>
<td style="width:17%">
    <b>H.I.:</b> {{ $registro->inicio_at?->format('H:i') ?? '—' }}
</td>
<td style="width:18%">
    <b>H.F.:</b> {{ $registro->finalizado_at?->format('H:i') ?? '—' }}
</td>
</tr>
</table>

<div class="title">Billetes</div>
<table class="count">
<thead>
<tr>
    <th>Denominación</th>
    <th>Cantidad</th>
    <th class="money">Sub-total</th>
</tr>
</thead>
<tbody>
@foreach($billetes as $valor)
    @php
        $detalle = $registro->detalles->first(
            fn ($d) => $d->tipo === 'BILLETE'
                && (int) round((float) $d->denominacion) === (int) $valor
        );
    @endphp
    <tr>
        <td>{{ $moneda($valor) }}</td>
        <td>{{ $detalle?->cantidad ?? 0 }}</td>
        <td class="money">{{ $moneda($detalle?->subtotal ?? 0) }}</td>
    </tr>
@endforeach
</tbody>
</table>

<div class="subtotal">
    Total billetes: {{ $moneda($registro->total_billetes) }}
</div>

<div class="title">Monedas</div>
<table class="count">
<thead>
<tr>
    <th>Denominación</th>
    <th>Cantidad</th>
    <th class="money">Sub-total</th>
</tr>
</thead>
<tbody>
@foreach($monedas as $valor)
    @php
        $detalle = $registro->detalles->first(
            fn ($d) => $d->tipo === 'MONEDA'
                && (int) round((float) $d->denominacion * 100)
                    === (int) round((float) $valor * 100)
        );
    @endphp
    <tr>
        <td>{{ $moneda($valor) }}</td>
        <td>{{ $detalle?->cantidad ?? 0 }}</td>
        <td class="money">{{ $moneda($detalle?->subtotal ?? 0) }}</td>
    </tr>
@endforeach
</tbody>
</table>

<div class="subtotal">
    Total monedas: {{ $moneda($registro->total_monedas) }}
</div>

<div class="title">Otros valores</div>
<table class="others">
@foreach(['Cheques', 'Vales', 'Otros valores'] as $concepto)
    @php
        $detalle = $registro->detalles->first(
            fn ($d) => $d->tipo === 'OTRO'
                && mb_strtolower(trim((string) $d->concepto))
                    === mb_strtolower($concepto)
        );
    @endphp
    <tr>
        <td>{{ $concepto }}</td>
        <td class="right">
            {{ $detalle ? $moneda($detalle->subtotal) : '—' }}
        </td>
    </tr>
@endforeach
<tr>
    <td><b>Total otros valores</b></td>
    <td class="right">
        <b>{{ $moneda($registro->total_otros_valores) }}</b>
    </td>
</tr>
</table>

<table class="summary">
<tr>
    <td><b>Total Arqueado</b></td>
    <td><b>{{ $moneda($registro->total_arqueado) }}</b></td>
</tr>
<tr>
    <td colspan="2" class="center"><b>COMPROBACIÓN</b></td>
</tr>
<tr>
    <td>Total Arqueado</td>
    <td>{{ $moneda($registro->total_arqueado) }}</td>
</tr>
<tr>
    <td>Total Corte</td>
    <td>{{ $moneda($registro->total_corte) }}</td>
</tr>
<tr>
    <td><b>Diferencia</b></td>
    <td><b>{{ $moneda($registro->diferencia) }}</b></td>
</tr>
</table>

<div class="cert">
<b>Certificación / observaciones:</b>
{{ $registro->justificacion_diferencia ?: 'Sin diferencia justificada.' }}
@if($registro->observaciones)
{{ $registro->observaciones }}
@endif
</div>

<table class="signs">
<tr>
<td>
    <div class="sign-line">Receptor - Pagador</div>
    <div class="sign-name">{{ $nombreFirma($receptor) }}</div>
    <div class="status">
        @if($receptor)
            {{ ($verificaciones['RECEPTOR'] ?? false)
                ? 'Firma electrónica verificada'
                : 'Firma electrónica no verificada' }}
            · {{ $receptor->fecha_firma?->format('d/m/Y H:i') }}
        @else
            Pendiente de firma electrónica
        @endif
    </div>
</td>
<td>
    <div class="sign-line">Vo. Bo. Jefe / Subjefe de Caja</div>
    <div class="sign-name">{{ $nombreFirma($certificador) }}</div>
    <div class="status">
        @if($certificador)
            {{ ($verificaciones['CERTIFICADOR'] ?? false)
                ? 'Firma electrónica verificada'
                : 'Firma electrónica no verificada' }}
            · {{ $certificador->fecha_firma?->format('d/m/Y H:i') }}
        @else
            Pendiente de firma electrónica
        @endif
    </div>
</td>
</tr>
</table>

<div class="foot">
Documento electrónico · Estado: {{ str_replace('_', ' ', $registro->estado) }}
· Número: {{ $registro->numero_arqueo }}.
Las firmas se verifican contra los datos económicos almacenados;
este documento no reproduce firmas manuscritas.
</div>

</body>
</html>
