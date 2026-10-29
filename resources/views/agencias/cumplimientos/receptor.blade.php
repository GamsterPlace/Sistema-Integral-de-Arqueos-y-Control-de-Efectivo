
@extends('layouts.receptor')

@section('title', 'Mis Cumplimientos')
@section('module-title', 'Mis Cumplimientos')

@push('styles')
<style>
.rc-page{display:grid;gap:20px;color:#183654}
.rc-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap}
.rc-heading h2{font-size:27px;margin:0;color:#06284f}
.rc-heading p{margin:7px 0 0;color:#6b7f90;font-size:13px;line-height:1.6}
.rc-label{border:1px solid #d9e5ee;border-radius:99px;background:#fff;padding:9px 14px;color:#315779;font-size:11px;font-weight:800}
.rc-toolbar{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}
.rc-nav{display:flex;gap:8px;flex-wrap:wrap}
.rc-btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border:1px solid #d5e1eb;border-radius:10px;background:#fff;color:#164c96;text-decoration:none;font-size:12px;font-weight:800;min-height:40px}
.rc-btn:hover{background:#f3f8fc}
.rc-btn-primary{background:#164c96;color:#fff;border-color:#164c96}
.rc-btn-primary:hover{background:#103d79}
.rc-month{font-size:22px;font-weight:850;color:#06284f;text-transform:capitalize;margin:0}
.rc-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}
.rc-summary article{background:#fff;border:1px solid #e0e8ee;border-radius:14px;padding:16px;box-shadow:0 7px 18px rgba(20,57,83,.04)}
.rc-summary span{display:block;font-size:10px;text-transform:uppercase;font-weight:800;letter-spacing:.35px;color:#718594}
.rc-summary strong{display:block;font-size:26px;color:#092f54;margin-top:7px}
.rc-calendar-wrap{overflow-x:auto;border:1px solid #dfe7ee;border-radius:16px;background:#fff;box-shadow:0 8px 22px rgba(20,57,83,.04)}
.rc-calendar{min-width:910px;display:grid;grid-template-columns:repeat(7,minmax(130px,1fr))}
.rc-weekday{text-align:center;background:#f5f8fb;color:#5a7184;font-size:10px;font-weight:850;padding:13px 6px;text-transform:uppercase;border-bottom:1px solid #e1e8ee}
.rc-blank,.rc-day{min-height:150px;border-right:1px solid #edf1f5;border-bottom:1px solid #edf1f5}
.rc-blank{background:#fafbfd}
.rc-day{padding:11px;background:#fff}
.rc-day.today{background:#f7fbff;box-shadow:inset 0 0 0 2px rgba(22,76,150,.18)}
.rc-day.future{background:#fcfdfe}
.rc-day-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.rc-day-number{font-size:13px;font-weight:850;color:#183c5b}
.rc-today{font-size:9px;color:#164c96;background:#e7f0ff;border-radius:99px;padding:3px 7px;font-weight:800}
.rc-turnos{display:grid;gap:8px}
.rc-turno{border:1px solid #e1e8ee;border-radius:10px;padding:9px;background:#fff}
.rc-turno-name{font-size:10px;font-weight:850;color:#294d6c;line-height:1.5;margin-bottom:7px;overflow-wrap:anywhere}
.rc-pill{display:block;padding:6px 7px;border-radius:7px;font-size:10px;font-weight:800;text-align:center;line-height:1.35}
.rc-ok{background:#e7f6ec;color:#177045}
.rc-warn{background:#fff3dc;color:#91630d}
.rc-bad{background:#fceaea;color:#a13939}
.rc-neutral{background:#eef2f6;color:#5a6e80}
.rc-future{background:#f4f6f8;color:#8796a3}
.rc-secondary{display:block;margin-top:5px;font-size:9px;color:#617689;line-height:1.4}
.rc-arqueo{margin-top:6px;font-size:10px;font-weight:750;color:#174f83;overflow-wrap:anywhere}
.rc-empty{font-size:10px;color:#9aa8b3;text-align:center;padding:17px 3px}
.rc-legend{display:flex;gap:13px;flex-wrap:wrap;font-size:11px;color:#617689}
.rc-legend span{display:inline-flex;gap:6px;align-items:center}
.rc-dot{height:9px;width:9px;border-radius:50%;display:inline-block}
.rc-dot.ok{background:#41aa70}
.rc-dot.warn{background:#e5ad42}
.rc-dot.bad{background:#d36a6a}
.rc-dot.neutral{background:#8494a3}
.rc-note{background:#fff;border:1px solid #e0e8ee;border-radius:12px;padding:15px 18px;font-size:12px;line-height:1.7;color:#64798a}
@media(max-width:1050px){
    .rc-summary{grid-template-columns:repeat(3,minmax(0,1fr))}
}
@media(max-width:650px){
    .rc-summary{grid-template-columns:repeat(2,minmax(0,1fr))}
    .rc-toolbar{align-items:stretch}
    .rc-nav{width:100%}
    .rc-btn{flex:1}
    .rc-month{font-size:19px}
}
</style>
@endpush

@section('content')
@php
    $espaciosIniciales = $inicioMes->dayOfWeekIso - 1;

    $etiquetas = [
        'CON_ARQUEO' => 'Con arqueo firmado',
        'PROGRAMADO' => 'Turno programado',
        'PENDIENTE_HOY' => 'Pendiente hoy',
        'ANULADO_SIN_REEMPLAZO' => 'Anulado sin reemplazo',
        'SIN_ARQUEO' => 'Sin arqueo registrado',
    ];

    $clases = [
        'CON_ARQUEO' => 'rc-ok',
        'PROGRAMADO' => 'rc-future',
        'PENDIENTE_HOY' => 'rc-warn',
        'ANULADO_SIN_REEMPLAZO' => 'rc-bad',
        'SIN_ARQUEO' => 'rc-bad',
    ];
@endphp

<div class="rc-page">

    <header class="rc-heading">
        <div>
            <h2>Mis Cumplimientos</h2>
            <p>
                Consulte sus arqueos ordinarios y el cumplimiento
                individual de cada turno programado.
            </p>
        </div>

        <span class="rc-label">Consulta personal</span>
    </header>

    <div class="rc-toolbar">
        <nav class="rc-nav" aria-label="Navegación mensual">
            <a
                class="rc-btn"
                href="{{ route('receptor.cumplimiento-agencias.index', ['mes' => $mesAnterior]) }}"
            >
                &#8592; Mes anterior
            </a>

            <a
                class="rc-btn rc-btn-primary"
                href="{{ route('receptor.cumplimiento-agencias.index', ['mes' => $mesActual]) }}"
            >
                Hoy
            </a>

            <a
                class="rc-btn"
                href="{{ route('receptor.cumplimiento-agencias.index', ['mes' => $mesSiguiente]) }}"
            >
                Mes siguiente &#8594;
            </a>
        </nav>

        <h3 class="rc-month">
            {{ $inicioMes->locale('es')->translatedFormat('F Y') }}
        </h3>
    </div>

    <section class="rc-summary" aria-label="Resumen mensual">
        <article>
            <span>Con arqueo</span>
            <strong>{{ $resumenMes['CON_ARQUEO'] }}</strong>
        </article>

        <article>
            <span>Sin arqueo</span>
            <strong>{{ $resumenMes['SIN_ARQUEO'] }}</strong>
        </article>

        <article>
            <span>Pendientes hoy</span>
            <strong>{{ $resumenMes['PENDIENTE_HOY'] }}</strong>
        </article>

        <article>
            <span>Anulados sin reemplazo</span>
            <strong>{{ $resumenMes['ANULADO_SIN_REEMPLAZO'] }}</strong>
        </article>

        <article>
            <span>Pendientes de certificar</span>
            <strong>{{ $resumenMes['PENDIENTE_CERTIFICACION'] }}</strong>
        </article>
    </section>

    <section class="rc-calendar-wrap" aria-label="Calendario de cumplimiento">
        <div class="rc-calendar">

            @foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $diaSemana)
                <div class="rc-weekday">
                    {{ $diaSemana }}
                </div>
            @endforeach

            @for($i = 0; $i < $espaciosIniciales; $i++)
                <div class="rc-blank" aria-hidden="true"></div>
            @endfor

            @foreach($calendario as $dia)
                <article
                    class="rc-day {{ $dia['es_hoy'] ? 'today' : '' }} {{ $dia['es_futuro'] ? 'future' : '' }}"
                >
                    <div class="rc-day-head">
                        <span class="rc-day-number">
                            {{ $dia['dia'] }}
                        </span>

                        @if($dia['es_hoy'])
                            <span class="rc-today">Hoy</span>
                        @endif
                    </div>

                    @if($dia['turnos']->isEmpty())
                        <div class="rc-empty">
                            Sin turnos programados
                        </div>
                    @else
                        <div class="rc-turnos">
                            @foreach($dia['turnos'] as $j)
                                <div class="rc-turno">
                                    <div class="rc-turno-name">
                                        {{ $j->turno?->nombre ?? 'Turno sin nombre' }}
                                        <br>
                                        {{ $j->agencia?->nombre ?? 'Agencia no disponible' }}

                                        @if($j->ventanilla?->codigo)
                                            · Vent. {{ $j->ventanilla->codigo }}
                                        @endif
                                    </div>

                                    <span class="rc-pill {{ $clases[$j->cumplimiento_arqueo] ?? 'rc-neutral' }}">
                                        {{ $etiquetas[$j->cumplimiento_arqueo] ?? $j->cumplimiento_arqueo }}
                                    </span>

                                    @if($j->certificacion_arqueo === 'CERTIFICADO')
                                        <span class="rc-secondary">
                                            Certificación: Certificado
                                        </span>
                                    @elseif($j->certificacion_arqueo === 'PENDIENTE_CERTIFICACION')
                                        <span class="rc-secondary">
                                            Certificación: Pendiente
                                        </span>
                                    @endif

                                    @if($j->arqueo_ordinario_vigente)
                                        <div class="rc-arqueo">
                                            N.º {{ $j->arqueo_ordinario_vigente->numero_arqueo }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach

        </div>
    </section>

    <div class="rc-legend">
        <span>
            <i class="rc-dot ok"></i>
            Con arqueo firmado
        </span>

        <span>
            <i class="rc-dot warn"></i>
            Pendiente hoy
        </span>

        <span>
            <i class="rc-dot bad"></i>
            Sin arqueo / anulado sin reemplazo
        </span>

        <span>
            <i class="rc-dot neutral"></i>
            Turno futuro
        </span>
    </div>

    <div class="rc-note">
        <strong>Importante:</strong>
        los indicadores contabilizan turnos, no días.
        Si tiene dos turnos en una misma fecha, ambos aparecen por separado.
        Un arqueo ordinario firmado cuenta como realizado aunque todavía
        no esté certificado. La ausencia de arqueo no demuestra por sí sola
        una falta laboral. Los días sin turnos programados no se consideran
        incumplimientos.
    </div>

</div>
@endsection
