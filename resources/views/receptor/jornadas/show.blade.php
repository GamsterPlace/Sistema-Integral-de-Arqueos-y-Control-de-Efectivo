@extends('layouts.receptor')

@section('title', 'Detalle de Jornada')

@push('styles')
<style>
    .jd-page{display:grid;gap:22px;color:#19344d}
    .jd-page *{box-sizing:border-box}
    .jd-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .jd-heading h1{margin:0;color:#06284f;font-size:clamp(24px,3vw,30px);font-weight:800}
    .jd-heading p{margin:8px 0 0;color:#6b8094;font-size:14px;line-height:1.6}
    .jd-back{display:inline-flex;align-items:center;gap:8px;padding:10px 15px;border:1px solid #d8e5f1;border-radius:10px;background:#fff;color:#164c96;font-size:13px;font-weight:750;text-decoration:none}
    .jd-back:hover{background:#eef5fc}
    .jd-panel{background:#fff;border:1px solid #dce7f1;border-radius:17px;box-shadow:0 6px 24px rgba(6,40,79,.045);overflow:hidden}
    .jd-panel-head{padding:23px 25px;border-bottom:1px solid #e5edf5;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .jd-panel-head h2{margin:0;color:#123c64;font-size:18px}
    .jd-status{display:inline-flex;padding:7px 12px;border-radius:99px;background:#eaf3fc;color:#164c96;font-size:12px;font-weight:800}
    .jd-section{padding:23px 25px}
    .jd-section+.jd-section{border-top:1px solid #e8eef5}
    .jd-section h3{margin:0 0 17px;color:#244c70;font-size:15px}
    .jd-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
    .jd-field{min-width:0;padding:16px;background:#f7fafc;border:1px solid #e5edf4;border-radius:12px}
    .jd-field span{display:block;margin-bottom:8px;color:#708499;font-size:12px;font-weight:700}
    .jd-field strong{display:block;color:#193d60;font-size:14px;font-weight:800;overflow-wrap:anywhere;line-height:1.5}
    .jd-field small{display:block;margin-top:4px;color:#708499;font-size:12px;line-height:1.5}
    .jd-note{padding:15px 18px;border-radius:12px;background:#eef5fc;border:1px solid #d9e7f6;color:#456884;font-size:13px;line-height:1.65}
    @media(max-width:950px){.jd-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:600px){.jd-grid{grid-template-columns:1fr}.jd-section,.jd-panel-head{padding:18px}}
</style>
@endpush

@section('content')
<div class="jd-page">
    <div class="jd-heading">
        <div>
            <h1>Detalle de Jornada #{{ $jornada->id }}</h1>
            <p>Información de la jornada asignada y sus horarios de trabajo.</p>
        </div>
        <a class="jd-back" href="{{ route('receptor.jornadas.index') }}">← Volver a Mis Jornadas</a>
    </div>

    <section class="jd-panel" aria-labelledby="jd-title">
        <div class="jd-panel-head">
            <h2 id="jd-title">Información de la jornada</h2>
            <span class="jd-status">{{ $jornada->estado ?? 'Sin estado' }}</span>
        </div>

        <div class="jd-section">
            <h3>Asignación y ubicación</h3>
            <div class="jd-grid">
                <div class="jd-field">
                    <span>Agencia</span>
                    <strong>{{ $jornada->agencia?->nombre ?? ('Agencia #'.$jornada->agencia_id) }}</strong>
                    @if($jornada->agencia?->codigo)<small>Código: {{ $jornada->agencia->codigo }}</small>@endif
                </div>
                <div class="jd-field">
                    <span>Ventanilla</span>
                    <strong>{{ $jornada->ventanilla?->codigo ?? '—' }}</strong>
                    @if($jornada->ventanilla?->descripcion)<small>{{ $jornada->ventanilla->descripcion }}</small>@endif
                </div>
                <div class="jd-field">
                    <span>Turno</span>
                    <strong>{{ $jornada->turno?->nombre ?? '—' }}</strong>
                </div>
                <div class="jd-field">
                    <span>Fecha operativa</span>
                    <strong>{{ $jornada->fecha_operativa?->format('d/m/Y') ?? '—' }}</strong>
                </div>
                <div class="jd-field">
                    <span>Tipo de asignación</span>
                    <strong>{{ $jornada->asignacionPersonal?->tipo ?? '—' }}</strong>
                </div>
                <div class="jd-field">
                    <span>Estado</span>
                    <strong>{{ $jornada->estado ?? 'Sin estado' }}</strong>
                </div>
            </div>
        </div>

        <div class="jd-section">
            <h3>Horarios</h3>
            <div class="jd-grid">
                <div class="jd-field">
                    <span>Inicio programado</span>
                    <strong>{{ $jornada->inicio_programado?->format('d/m/Y H:i') ?? '—' }}</strong>
                </div>
                <div class="jd-field">
                    <span>Fin programado</span>
                    <strong>{{ $jornada->fin_programado?->format('d/m/Y H:i') ?? '—' }}</strong>
                </div>
                <div class="jd-field">
                    <span>Inicio real</span>
                    <strong>{{ $jornada->inicio_real?->format('d/m/Y H:i') ?? 'Pendiente' }}</strong>
                </div>
                <div class="jd-field">
                    <span>Fin real</span>
                    <strong>{{ $jornada->fin_real?->format('d/m/Y H:i') ?? 'Pendiente' }}</strong>
                </div>
            </div>
        </div>

        <div class="jd-section">
            <div class="jd-note">Esta sección es únicamente informativa. La creación y consulta de arqueos diarios se realiza desde el módulo <strong>Arqueo Diario</strong>.</div>
        </div>
    </section>
</div>
@endsection
