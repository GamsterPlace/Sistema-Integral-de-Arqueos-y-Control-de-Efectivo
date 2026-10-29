@extends('layouts.jefe-agencia')
@section('title', 'Arqueos del Receptor')
@section('content')
<div class="page-header"><div class="page-title"><h2>{{ $usuarioReceptor->nombre_completo }}</h2><p>Historial completo de arqueos del receptor en las agencias autorizadas.</p></div><a href="{{ route('jefeagencia.historial-arqueos.index') }}" style="color:#164c96;font-weight:700">Volver a fichas</a></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(295px,1fr));gap:16px">
@foreach($arqueos as $arqueo)
<article style="background:white;border:1px solid #dce6ef;border-radius:15px;padding:20px">
<div style="display:flex;justify-content:space-between;gap:12px;align-items:start"><div><strong style="color:#173f67">{{ $arqueo->numero_arqueo }}</strong><div style="font-size:12px;color:#728499;margin-top:6px">{{ $arqueo->fecha_operativa?->format('d/m/Y') }} · {{ $arqueo->tipo }}</div></div><span style="font-size:11px;font-weight:700;color:#245d91">{{ $arqueo->estado }}</span></div>
<div style="margin-top:14px;font-size:13px;color:#405b73">{{ $arqueo->agencia?->nombre ?? $arqueo->agencia_nombre_historico }} · Ventanilla {{ $arqueo->ventanilla?->codigo ?? $arqueo->ventanilla_codigo_historico }}</div>
<div style="margin-top:10px;font-weight:800;font-size:19px;color:#173f67">Q {{ number_format((float)$arqueo->total_arqueado,2) }}</div>
<div style="display:flex;gap:16px;margin-top:18px;font-size:13px;font-weight:700"><a href="{{ route('jefeagencia.historial-arqueos.show',$arqueo->id) }}" style="color:#164c96">Ver detalle</a><a href="{{ route('jefeagencia.historial-arqueos.imprimir',$arqueo->id) }}" target="_blank" style="color:#164c96">PDF</a></div>
</article>
@endforeach
</div>
<div style="margin-top:24px">{{ $arqueos->links() }}</div>
@endsection
