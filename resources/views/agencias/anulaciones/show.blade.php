@extends($ruta === 'jefecaja' ? 'layouts.jefe-caja' : 'layouts.subjefe-caja')

@section('title', 'Detalle de anulación de arqueo')
@section('module-title', 'Anulación de Arqueos')

@push('styles')
<style>
    .an-detalle{display:grid;gap:20px;color:#19344d}
    .an-cabecera{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .an-cabecera h1{margin:0;color:#06284f;font-size:clamp(23px,3vw,29px)}
    .an-cabecera p{margin:7px 0 0;color:#697f94;font-size:13px;line-height:1.6}
    .an-volver{display:inline-flex;align-items:center;justify-content:center;padding:11px 16px;background:#eaf2fc;color:#164c96;border-radius:11px;font-size:13px;font-weight:750}
    .an-panel{background:#fff;border:1px solid #dfe7ee;border-radius:16px;padding:22px;box-shadow:0 5px 22px rgba(6,40,79,.045);min-width:0}
    .an-panel h2{margin:0 0 16px;font-size:17px;color:#06284f}
    .an-datos{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
    .an-dato{min-width:0}.an-dato small{display:block;color:#748699;font-size:12px;margin-bottom:6px}.an-dato strong{display:block;color:#193b5e;font-size:14px;overflow-wrap:anywhere}
    .an-importes{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
    .an-importe{padding:16px;border-radius:12px;background:#f4f8fc;border:1px solid #e1eaf2}
    .an-importe small{display:block;color:#667d93;font-size:12px;margin-bottom:7px}.an-importe strong{font-size:22px;color:#06284f}
    .an-estado{display:inline-flex;padding:6px 11px;border-radius:999px;background:#eaf2fc;color:#235b99;font-size:12px;font-weight:800}
    .an-estado.certificado{background:#e9f8ee;color:#167c46}.an-estado.anulado{background:#fcebec;color:#a72e3a}
    .an-tabla-wrap{overflow-x:auto}.an-tabla{width:100%;border-collapse:collapse;font-size:13px}
    .an-tabla th{background:#f5f8fc;color:#627b91;text-align:left;font-size:12px;padding:12px;white-space:nowrap}
    .an-tabla td{padding:13px 12px;border-bottom:1px solid #edf1f5;vertical-align:top}.an-tabla tr:last-child td{border-bottom:0}
    .an-vacio{padding:17px;background:#f8fafc;border:1px dashed #dce5ee;border-radius:10px;color:#708194;font-size:13px}
    .an-firma{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:13px 0;border-bottom:1px solid #edf1f5}
    .an-firma:last-child{border-bottom:0}.an-firma strong{display:block;font-size:13px;color:#183c62}.an-firma small{display:block;color:#718397;margin-top:5px;font-size:12px}
    .an-verificacion{font-size:12px;font-weight:800;color:#167c46}.an-verificacion.invalida{color:#a72e3a}
    .an-aviso{padding:15px 17px;border:1px solid #efd4a5;background:#fff9ed;border-radius:12px;color:#79591d;font-size:13px;line-height:1.6}
    .an-aviso.peligro{border-color:#f0c9c9;background:#fff4f4;color:#922c35}.an-aviso.exito{border-color:#bce1cb;background:#effaf3;color:#176c40}
    .an-form{display:grid;gap:16px}.an-form label.an-label{font-weight:800;color:#193b5e;font-size:13px}
    .an-form textarea{display:block;width:100%;min-height:125px;resize:vertical;border:1px solid #ccd9e5;border-radius:11px;padding:13px;margin-top:8px;background:#fff;color:#19344d;line-height:1.6}
    .an-form textarea:focus{outline:3px solid rgba(22,76,150,.12);border-color:#164c96}
    .an-check{display:flex;align-items:flex-start;gap:10px;font-size:13px;line-height:1.6;color:#344f69}.an-check input{margin-top:4px;accent-color:#a42330}
    .an-boton{justify-self:start;border:0;border-radius:11px;background:#a42330;color:#fff;padding:13px 20px;font-weight:800;font-size:13px;cursor:pointer}
    .an-boton:hover{background:#8b1c28}.an-error{padding:12px 15px;background:#fff3f3;border:1px solid #f0caca;color:#a12e39;border-radius:10px;font-size:13px}
    .an-nota{font-size:12px;color:#718397;line-height:1.6;margin:0}
    @media(max-width:850px){.an-datos{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:550px){.an-datos,.an-importes{grid-template-columns:1fr}.an-panel{padding:16px}}
</style>
@endpush

@section('content')
@php
    $estado = (string) $registro->estado;
    $anulado = $estado === 'ANULADO' || $registro->anulado_at !== null || $registro->anulacion !== null;
    $integridadCorrecta = $registro->firmas->every(fn ($firma) => (bool) ($verificaciones[$firma->tipo_firma] ?? false));
@endphp
<div class="an-detalle">
    <header class="an-cabecera">
        <div>
            <h1>Arqueo {{ $registro->numero_arqueo ?: '#'.$registro->id }}</h1>
            <p>Consulta del arqueo ordinario, sus firmas y el proceso de anulación.</p>
        </div>
        <a class="an-volver" href="{{ route($ruta.'.anulaciones.index') }}">← Volver a anulaciones</a>
    </header>

    @if(session('success'))
        <div class="an-aviso exito" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="an-error" role="alert">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="an-error" role="alert">
            <strong>No se pudo procesar la solicitud:</strong>
            <ul>
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <section class="an-panel">
        <h2>Información del arqueo</h2>
        <div class="an-datos">
            <div class="an-dato"><small>Estado actual</small><span class="an-estado {{ strtolower($estado) }}">{{ $estado }}</span></div>
            <div class="an-dato"><small>Fecha operativa</small><strong>{{ $registro->fecha_operativa?->format('d/m/Y') ?? 'No disponible' }}</strong></div>
            <div class="an-dato"><small>Tipo</small><strong>{{ str_replace('_', ' ', $registro->tipo) }}</strong></div>
            <div class="an-dato"><small>Agencia</small><strong>{{ $registro->agencia_nombre_historico ?: 'Agencia #'.$registro->agencia_id }}</strong></div>
            <div class="an-dato"><small>Ventanilla</small><strong>{{ $registro->ventanilla_codigo_historico ?: 'No disponible' }}</strong></div>
            <div class="an-dato"><small>Receptor</small><strong>{{ $registro->receptor_nombre_historico ?: 'No disponible' }}</strong></div>
        </div>
    </section>

    <section class="an-panel">
        <h2>Resumen económico original</h2>
        <div class="an-importes">
            <div class="an-importe"><small>Total arqueado</small><strong>Q {{ number_format((float) $registro->total_arqueado, 2) }}</strong></div>
            <div class="an-importe"><small>Total del corte</small><strong>Q {{ number_format((float) $registro->total_corte, 2) }}</strong></div>
            <div class="an-importe"><small>Diferencia</small><strong>Q {{ number_format((float) $registro->diferencia, 2) }}</strong></div>
        </div>
    </section>

    <section class="an-panel">
        <h2>Desglose monetario registrado</h2>
        @if($registro->detalles->isEmpty())
            <div class="an-vacio">Este arqueo no tiene detalles monetarios registrados.</div>
        @else
            <div class="an-tabla-wrap">
                <table class="an-tabla">
                    <thead><tr><th>Tipo</th><th>Denominación / concepto</th><th>Cantidad</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($registro->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->tipo }}</td>
                                <td>{{ $detalle->denominacion ?? $detalle->concepto ?? '—' }}</td>
                                <td>{{ $detalle->cantidad ?? '—' }}</td>
                                <td>Q {{ number_format((float) $detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="an-panel">
        <h2>Firmas electrónicas e integridad</h2>
        @forelse($registro->firmas as $firma)
            <div class="an-firma">
                <div>
                    <strong>{{ str_replace('_', ' ', $firma->tipo_firma) }}</strong>
                    <small>{{ $firma->fecha_firma?->format('d/m/Y H:i:s') ?? 'Fecha no disponible' }}</small>
                </div>
                @if($verificaciones[$firma->tipo_firma] ?? false)
                    <span class="an-verificacion">Integridad verificada</span>
                @else
                    <span class="an-verificacion invalida">Integridad no verificada</span>
                @endif
            </div>
        @empty
            <div class="an-vacio">No hay firmas electrónicas registradas.</div>
        @endforelse
    </section>

    @if($registro->anulacion)
        <section class="an-panel">
            <h2>Constancia de anulación</h2>
            <div class="an-aviso peligro">Este arqueo fue anulado de forma irreversible. Sus importes y firmas originales permanecen conservados.</div>
            <div class="an-datos" style="margin-top:18px">
                <div class="an-dato"><small>Fecha de anulación</small><strong>{{ $registro->anulacion->fecha_anulacion?->format('d/m/Y H:i:s') ?? 'No disponible' }}</strong></div>
                <div class="an-dato"><small>Identificador del responsable</small><strong>#{{ $registro->anulacion->anulado_por }}</strong></div>
                <div class="an-dato"><small>Firma de anulación</small><strong>#{{ $registro->anulacion->firma_id }}</strong></div>
            </div>
            <div class="an-dato" style="margin-top:18px"><small>Motivo registrado</small><strong>{{ $registro->anulacion->motivo }}</strong></div>
        </section>
    @elseif($puedeAnular && !$anulado)
        <section class="an-panel">
            <h2>Anular arqueo ordinario</h2>
            <div class="an-aviso peligro" style="margin-bottom:18px"><strong>Atención:</strong> esta operación es irreversible. Se conservarán el desglose económico, las firmas originales y el motivo de anulación. No se eliminará el arqueo.</div>
            @unless($integridadCorrecta)
                <div class="an-aviso" style="margin-bottom:18px">Una o más firmas no superaron la verificación de integridad. El servidor volverá a verificar las firmas antes de permitir la anulación.</div>
            @endunless
            <form class="an-form" method="POST" action="{{ route($ruta.'.anulaciones.store', $registro->id) }}" id="anulacionForm">
                @csrf
                <label class="an-label" for="motivo">Motivo de anulación (obligatorio, de 10 a 500 caracteres)
                    <textarea id="motivo" name="motivo" minlength="10" maxlength="500" required placeholder="Describa claramente por qué se debe anular este arqueo.">{{ old('motivo') }}</textarea>
                </label>
                <label class="an-check" for="confirmar_anulacion">
                    <input type="checkbox" id="confirmar_anulacion" name="confirmar_anulacion" value="1" required {{ old('confirmar_anulacion') ? 'checked' : '' }}>
                    <span>Confirmo que he revisado los datos y autorizo la anulación irreversible de este arqueo.</span>
                </label>
                <button type="submit" class="an-boton" id="anulacionEnviar">Firmar y anular arqueo</button>
                <p class="an-nota">La autorización y las firmas se verificarán nuevamente en el servidor antes de registrar la operación.</p>
            </form>
        </section>
    @else
        <div class="an-aviso">Este arqueo no puede anularse en su estado actual o ya fue anulado.</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formulario = document.getElementById('anulacionForm');
        const boton = document.getElementById('anulacionEnviar');
        if (!formulario || !boton) return;
        formulario.addEventListener('submit', function () {
            if (!formulario.checkValidity()) return;
            boton.disabled = true;
            boton.textContent = 'Procesando anulación...';
        });
    });
</script>
@endpush
