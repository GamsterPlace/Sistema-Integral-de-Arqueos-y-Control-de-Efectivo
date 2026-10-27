@extends('layouts.jefe')

@section('title', 'Detalle del Arqueo del Promotor')
@section('module-title', 'Arqueos por Promotor')

@section('content')
@php
    $nombrePromotor = trim(($promotor->nombres ?? '') . ' ' . ($promotor->apellidos ?? ''));
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Detalle del Arqueo del Promotor</h2>
        <p>Consulte el arqueo realizado por el Promotor durante la visita al Agente.</p>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('jefe.arqueos-promotores.index') }}" style="display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 15px;border:1px solid #d5dfe5;border-radius:10px;background:#fff;color:#31536e;text-decoration:none;font-size:10px;font-weight:800;">Regresar</a>

        <a href="{{ route('jefe.arqueos-promotores.imprimir', $arqueo->id) }}" target="_blank" style="display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 15px;border-radius:10px;background:#164c96;color:#fff;text-decoration:none;font-size:10px;font-weight:800;">Imprimir PDF</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1.5fr) minmax(300px,.7fr);gap:20px;">
    <div>
        <section style="overflow:hidden;border:1px solid #e0e8ee;border-radius:18px;background:#fff;">
            <div style="padding:17px 19px;border-bottom:1px solid #edf1f4;background:#fafcfd;">
                <h3 style="margin:0;color:#0a3158;font-size:15px;">Información general</h3>
            </div>

            <div style="padding:19px;display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                @foreach ([
                    'Número de arqueo' => $arqueo->numero_arqueo,
                    'Fecha' => $arqueo->fecha_arqueo->format('d/m/Y'),
                    'Estado' => str_replace('_', ' ', $arqueo->estado),
                    'Promotor' => ($nombrePromotor !== '' ? $nombrePromotor : ($promotor->nombre_usuario ?? 'No disponible')),
                    'Código del agente' => $arqueo->codigo_agente_historico,
                    'Negocio' => $arqueo->nombre_negocio_historico,
                    'Propietario' => $arqueo->nombre_propietario_historico,
                    'Ruta' => $arqueo->ruta_historica,
                    'Región' => $arqueo->region_historica,
                ] as $label => $value)
                    <div style="padding:13px;border:1px solid #e2e9ee;border-radius:11px;background:#fbfcfd;">
                        <span style="display:block;color:#758697;font-size:8px;font-weight:800;text-transform:uppercase;">{{ $label }}</span>
                        <strong style="display:block;margin-top:6px;color:#173b59;font-size:11px;">{{ $value }}</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <section style="margin-top:20px;overflow:hidden;border:1px solid #e0e8ee;border-radius:18px;background:#fff;">
            <div style="padding:17px 19px;border-bottom:1px solid #edf1f4;background:#fafcfd;">
                <h3 style="margin:0;color:#0a3158;font-size:15px;">Conteo de efectivo</h3>
            </div>

            <div style="padding:19px;display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <h4>Billetes</h4>
                    <table style="width:100%;border-collapse:collapse;">
                        @foreach ($billetes as $detalle)
                            <tr>
                                <td>Q {{ number_format((float) $detalle->denominacion, 2) }}</td>
                                <td>{{ $detalle->cantidad }}</td>
                                <td>Q {{ number_format((float) $detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                <div>
                    <h4>Monedas</h4>
                    <table style="width:100%;border-collapse:collapse;">
                        @foreach ($monedas as $detalle)
                            <tr>
                                <td>Q {{ number_format((float) $detalle->denominacion, 2) }}</td>
                                <td>{{ $detalle->cantidad }}</td>
                                <td>Q {{ number_format((float) $detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </section>
    </div>

    <aside>
        <section style="overflow:hidden;border:1px solid #e0e8ee;border-radius:18px;background:#fff;">
            <div style="padding:17px 19px;border-bottom:1px solid #edf1f4;background:#fafcfd;">
                <h3 style="margin:0;color:#0a3158;font-size:15px;">Resumen</h3>
            </div>

            <div style="padding:19px;">
                @foreach ([
                    'Total billetes' => $arqueo->total_billetes,
                    'Total monedas' => $arqueo->total_monedas,
                    'Total arqueado' => $arqueo->total_arqueado,
                    'Saldo sistema' => $arqueo->saldo_sistema,
                    'Diferencia' => $arqueo->diferencia,
                ] as $label => $value)
                    <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #edf1f4;">
                        <span>{{ $label }}</span>
                        <strong>Q {{ number_format((float) $value, 2) }}</strong>
                    </div>
                @endforeach
            </div>
        </section>
    </aside>
</div>
@endsection
