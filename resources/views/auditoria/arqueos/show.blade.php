@extends('layouts.auditoria')

@section('title', 'Detalle de Arqueo')
@section('module-title', 'Historial')

@push('styles')
<style>
    .page-actions{
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .btn-primary-custom,.btn-secondary-custom{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:42px;
        padding:0 17px;
        border-radius:11px;
        font-size:10px;
        font-weight:850;
        text-decoration:none;
        transition:.2s;
    }

    .btn-primary-custom{
        border:0;
        background:linear-gradient(135deg,#164c96,#1c64b5);
        color:#fff;
        box-shadow:0 8px 18px rgba(22,76,150,.16);
    }

    .btn-secondary-custom{
        border:1px solid #d8e2e8;
        background:#fff;
        color:#31536e;
    }

    .btn-primary-custom svg,.btn-secondary-custom svg{
        width:15px;
        height:15px;
        margin-right:7px;
        stroke:currentColor;
    }

    .detail-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:18px;
        margin-bottom:18px;
    }

    .detail-card{
        padding:20px;
        border:1px solid #e0e8ee;
        border-radius:18px;
        background:#fff;
        box-shadow:0 6px 20px rgba(20,57,83,.035);
    }

    .detail-card h3{
        margin:0 0 17px;
        padding-bottom:10px;
        border-bottom:1px solid #edf1f4;
        color:#0a3158;
        font-size:14px;
    }

    .info-list{
        display:grid;
        gap:11px;
    }

    .info-row{
        display:grid;
        grid-template-columns:135px minmax(0,1fr);
        gap:12px;
        align-items:start;
    }

    .info-label{
        color:#748695;
        font-size:9px;
        font-weight:850;
        text-transform:uppercase;
    }

    .info-value{
        color:#263f53;
        font-size:11px;
        font-weight:650;
        word-break:break-word;
    }

    .status-badge,.result-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:26px;
        padding:0 10px;
        border-radius:999px;
        font-size:8px;
        font-weight:850;
        text-transform:uppercase;
    }

    .status-certified{background:#eaf8f0;color:#167746}
    .status-pending{background:#fff6df;color:#9a6913}
    .status-cancelled{background:#fff0ef;color:#a83c38}
    .status-default{background:#edf2f5;color:#50687a}

    .result-faltante{background:#fff0ef;color:#ad3f3b}
    .result-sobrante{background:#eaf8f0;color:#167746}
    .result-exacto{background:#edf4fa;color:#315f86}

    .money-value{
        color:#0a3158;
        font-weight:850;
    }

    .difference-value{
        display:inline-block;
        margin-right:8px;
        color:#0a3158;
        font-size:12px;
        font-weight:900;
    }

    .denominations-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:18px;
        margin-bottom:18px;
    }

    .denomination-table{
        width:100%;
        border-collapse:collapse;
    }


   .denomination-table th{
        padding:10px 9px;
        background:#f7f9fb;
        color:#667b8c;
        font-size:8px;
        font-weight:850;
        text-align:right;
        text-transform:uppercase;
    }

    .denomination-table th:first-child{
        text-align:left;
    }

    .denomination-table td{
        padding:10px 9px;
        border-top:1px solid #edf1f4;
        color:#334f64;
        font-size:10px;
        text-align:right;
    }

    .denomination-table td:first-child{
        text-align:left;
        font-weight:800;
        color:#17364f;
    }

    .denomination-table tfoot td{
        background:#fbfcfd;
        color:#0a3158;
        font-weight:900;
    }

    .observations-card{
        margin-bottom:18px;
    }

    .observations-text{
        margin:0;
        color:#4e6576;
        font-size:10px;
        line-height:1.7;
        white-space:pre-line;
    }

    .signatures-card{
        margin-bottom:10px;
    }

    .signatures-grid{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:18px;
    }

    .signature-box{
        min-height:125px;
        padding:16px;
        border:1px solid #e1e8ed;
        border-radius:14px;
        background:#fbfcfd;
        text-align:center;
    }

    .signature-role{
        display:block;
        margin-bottom:14px;
        color:#758697;
        font-size:8px;
        font-weight:850;
        letter-spacing:.35px;
        text-transform:uppercase;
    }

    .signature-name{
        min-height:32px;
        display:flex;
        align-items:flex-end;
        justify-content:center;
        padding-bottom:6px;
        border-bottom:1px solid #81909b;
        color:#17364f;
        font-size:10px;
        font-weight:850;
    }

    .signature-meta{
        margin:7px 0 0;
        color:#718391;
        font-size:8px;
        line-height:1.5;
    }

    .signature-pending{
        color:#a07019;
        font-style:italic;
    }

    @media(max-width:900px){
        .detail-grid,.denominations-grid{
            grid-template-columns:1fr;
        }

        .signatures-grid{
            grid-template-columns:1fr;
        }
    }

    @media(max-width:650px){
        .page-actions{
            width:100%;
            margin-top:14px;
        }

        .page-actions a{
            width:100%;
        }

        .info-row{
            grid-template-columns:1fr;
            gap:3px;
        }
    }

    .btn-danger-custom{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 17px;border:0;border-radius:11px;background:linear-gradient(135deg,#b83232,#d64545);color:#fff;font-size:10px;font-weight:850;cursor:pointer;box-shadow:0 8px 18px rgba(184,50,50,.16);transition:.2s}
    .btn-danger-custom:hover{transform:translateY(-1px);box-shadow:0 10px 22px rgba(184,50,50,.22)}
    .btn-danger-custom svg{width:15px;height:15px;margin-right:7px;fill:none;stroke:currentColor}
    .modal-backdrop-custom{position:fixed;inset:0;z-index:9998;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(7,29,48,.58);backdrop-filter:blur(3px)}
    .modal-backdrop-custom.is-open{display:flex}
    .modal-card-custom{width:min(100%,520px);overflow:hidden;border:1px solid #e0e8ee;border-radius:18px;background:#fff;box-shadow:0 24px 70px rgba(7,29,48,.25)}
    .modal-header-custom{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding:20px 22px 16px;border-bottom:1px solid #edf1f4}
    .modal-header-custom h3{margin:0;color:#8f2929;font-size:16px;font-weight:900}
    .modal-header-custom p{margin:6px 0 0;color:#6d8190;font-size:10px;line-height:1.6}
    .modal-close-custom{width:34px;height:34px;flex:0 0 34px;border:1px solid #dde6eb;border-radius:10px;background:#fff;color:#667b8c;font-size:20px;line-height:1;cursor:pointer}
    .modal-body-custom{padding:20px 22px}
    .warning-box-custom{margin-bottom:18px;padding:13px 14px;border:1px solid #f0d2cf;border-radius:12px;background:#fff6f5;color:#8d3a35;font-size:10px;line-height:1.65}
    .form-group-custom{margin-bottom:16px}
    .form-group-custom label{display:block;margin-bottom:7px;color:#31536e;font-size:9px;font-weight:850;text-transform:uppercase}
    .form-control-custom{width:100%;box-sizing:border-box;border:1px solid #d7e1e7;border-radius:11px;background:#fff;color:#263f53;font:inherit;font-size:11px;outline:none;transition:.2s}
    input.form-control-custom{min-height:42px;padding:0 12px}
    textarea.form-control-custom{min-height:110px;padding:11px 12px;resize:vertical;line-height:1.55}
    .form-control-custom:focus{border-color:#7ea7cb;box-shadow:0 0 0 3px rgba(22,76,150,.08)}
    .form-error-custom{margin-top:6px;color:#b83232;font-size:9px;font-weight:700}
    .modal-footer-custom{display:flex;justify-content:flex-end;gap:10px;padding:16px 22px 20px;border-top:1px solid #edf1f4}
    body.modal-open-custom{overflow:hidden}

</style>

@endpush

@section('content')
@php
    $diferencia = (float) $arqueo->diferencia;

    $estadoClase = match($arqueo->estado) {
        'CERTIFICADO' => 'status-certified',
        'PENDIENTE_CERTIFICACION' => 'status-pending',
        'ANULADO' => 'status-cancelled',
        default => 'status-default',
    };

    $estadoTexto = match($arqueo->estado) {
        'PENDIENTE_CERTIFICACION' => 'Pendiente',
        'CERTIFICADO' => 'Certificado',
        'ANULADO' => 'Anulado',
        default => str_replace('_',' ',$arqueo->estado),
    };

    if ($diferencia < -0.004) {
        $resultadoTexto = 'Faltante';
        $resultadoClase = 'result-faltante';
    } elseif ($diferencia > 0.004) {
        $resultadoTexto = 'Sobrante';
        $resultadoClase = 'result-sobrante';
    } else {
        $resultadoTexto = 'Exacto';
        $resultadoClase = 'result-exacto';
    }

    $firmaRealizador = \Illuminate\Support\Facades\DB::table('firmas_arqueos')
        ->where('arqueo_id',$arqueo->id)
        ->where('tipo_firma','REALIZADOR')
        ->where('valida',1)
        ->first();

    $firmaValidador = \Illuminate\Support\Facades\DB::table('firmas_arqueos')
        ->where('arqueo_id',$arqueo->id)
        ->where('tipo_firma','VALIDADOR')
        ->where('valida',1)
        ->first();

    $firmaCertificador = \Illuminate\Support\Facades\DB::table('firmas_arqueos')
        ->where('arqueo_id',$arqueo->id)
        ->where('tipo_firma','CERTIFICADOR')
        ->where('valida',1)
        ->first();
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>{{ $arqueo->numero_arqueo }}</h2>
        <p>Detalle del arqueo realizado por Auditoría MICOOPE.</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('auditoria.arqueos.index') }}" class="btn-secondary-custom">
            Volver
        </a>

        <a
            target="_blank"
            href="{{ route('auditoria.arqueos.imprimir',$arqueo->id) }}"
            class="btn-primary-custom"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8">
                <path d="M6 9V3h12v6"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <path d="M6 14h12v7H6z"/>
            </svg>
            Imprimir PDF
        </a>
        @if(in_array($arqueo->estado, ['PENDIENTE_CERTIFICACION', 'CERTIFICADO'], true))
           <button type="button" class="btn-danger-custom" id="btnAbrirAnulacion">
               <svg viewBox="0 0 24 24" stroke-width="1.8">
                   <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/>
               </svg>
               Anular arqueo
            </button>
        @endif

   </div>
</div>

<div class="detail-grid">
    <section class="detail-card">
        <h3>Agente Auditado</h3>

        <div class="info-list">
            <div class="info-row">
                <span class="info-label">Código</span>
                <span class="info-value">{{ $arqueo->codigo_agente_historico }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Negocio</span>
                <span class="info-value">{{ $arqueo->nombre_negocio_historico }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Propietario</span>
                <span class="info-value">{{ $arqueo->nombre_propietario_historico }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Dirección</span>
                <span class="info-value">{{ $arqueo->direccion_historica }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Región</span>
                <span class="info-value">{{ $arqueo->region_historica }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Ruta</span>
                <span class="info-value">{{ $arqueo->ruta_historica }}</span>
            </div>
        </div>
    </section>

    <section class="detail-card">
        <h3>Resultado del Arqueo</h3>

        <div class="info-list">

           <div class="info-row">
                <span class="info-label">Fecha</span>
                <span class="info-value">
                    {{ \Carbon\Carbon::parse($arqueo->fecha_arqueo)->format('d/m/Y') }}
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Estado</span>
                <span class="info-value">
                    <span class="status-badge {{ $estadoClase }}">{{ $estadoTexto }}</span>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Total Billetes</span>
                <span class="info-value money-value">
                    Q {{ number_format((float)$arqueo->total_billetes,2) }}
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Total Monedas</span>
                <span class="info-value money-value">
                    Q {{ number_format((float)$arqueo->total_monedas,2) }}
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Total Arqueado</span>
                <span class="info-value money-value">
                    Q {{ number_format((float)$arqueo->total_arqueado,2) }}
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Saldo Sistema</span>
                <span class="info-value money-value">
                    Q {{ number_format((float)$arqueo->saldo_sistema,2) }}
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Diferencia</span>
                <span class="info-value">
                    <span class="difference-value">
                        Q {{ number_format($diferencia,2) }}
                    </span>

                    <span class="result-badge {{ $resultadoClase }}">
                        {{ $resultadoTexto }}
                    </span>
                </span>
            </div>
        </div>
    </section>
</div>

<div class="denominations-grid">
    <section class="detail-card">
        <h3>Billetes</h3>

        <table class="denomination-table">
            <thead>
                <tr>
                    <th>Denominación</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($billetes as $detalle)
                    <tr>
                        <td>Q {{ number_format((float)$detalle->denominacion,2) }}</td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>Q {{ number_format((float)$detalle->subtotal,2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Sin detalle de billetes.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total Billetes</td>
                    <td>Q {{ number_format((float)$arqueo->total_billetes,2) }}</td>
                </tr>
           </tfoot>
       </table>
    </section>

    <section class="detail-card">
        <h3>Monedas</h3>


       <table class="denomination-table">
            <thead>
                <tr>
                    <th>Denominación</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>

           </thead>
            <tbody>
                @forelse($monedas as $detalle)
                    <tr>
                        <td>Q {{ number_format((float)$detalle->denominacion,2) }}</td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>Q {{ number_format((float)$detalle->subtotal,2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Sin detalle de monedas.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total Monedas</td>
                    <td>Q {{ number_format((float)$arqueo->total_monedas,2) }}</td>
                </tr>
            </tfoot>
        </table>
    </section>
</div>

<section class="detail-card observations-card">

    <h3>Certificación</h3>

    <p class="observations-text">{{ $arqueo->certificacion ?: 'Sin certificación registrada.' }}</p>

</section>


<section class="detail-card observations-card">
    <h3>Observaciones</h3>
    <p class="observations-text">{{ $arqueo->observaciones ?: 'Sin observaciones registradas.' }}</p>
</section>

<section class="detail-card signatures-card">
    <h3>Firmas Electrónicas</h3>

    <div class="signatures-grid">
        <article class="signature-box">
            <span class="signature-role">Realizador · Auditoría MICOOPE</span>

            @if($firmaRealizador)
                <div class="signature-name">
                    {{ trim($firmaRealizador->nombres_historicos.' '.$firmaRealizador->apellidos_historicos) }}
                </div>
                <p class="signature-meta">
                    Firmado electrónicamente<br>
                    {{ \Carbon\Carbon::parse($firmaRealizador->fecha_firma)->format('d/m/Y H:i') }}
                </p>
            @else
                <div class="signature-name signature-pending">Pendiente</div>
                <p class="signature-meta">Firma electrónica no registrada.</p>
            @endif
        </article>

        <article class="signature-box">
            <span class="signature-role">Validador · Propietario o Receptor Pagador</span>

            @if($firmaValidador)
                <div class="signature-name">
                    {{ trim($firmaValidador->nombres_historicos.' '.$firmaValidador->apellidos_historicos) }}
                </div>
                <p class="signature-meta">
                    Validado electrónicamente<br>
                    {{ \Carbon\Carbon::parse($firmaValidador->fecha_firma)->format('d/m/Y H:i') }}
                </p>
            @else
                <div class="signature-name signature-pending">Pendiente de validación</div>
                <p class="signature-meta">El Agente MICOOPE aún no ha validado el arqueo.</p>
            @endif
        </article>

        <article class="signature-box">
            <span class="signature-role">Certificador · Jefe de Agentes MICOOPE</span>

            @if($firmaCertificador)
                <div class="signature-name">
                    {{ trim($firmaCertificador->nombres_historicos.' '.$firmaCertificador->apellidos_historicos) }}
                </div>
                <p class="signature-meta">
                    Certificado electrónicamente<br>
                    {{ \Carbon\Carbon::parse($firmaCertificador->fecha_firma)->format('d/m/Y H:i') }}
                </p>

           @else
                <div class="signature-name signature-pending">Pendiente de certificación</div>
                <p class="signature-meta">Pendiente de firma del Jefe de Agentes MICOOPE.</p>
            @endif
        </article>
    </div>
</section>
@if(in_array($arqueo->estado, ['PENDIENTE_CERTIFICACION', 'CERTIFICADO'], true))
<div class="modal-backdrop-custom" id="modalAnulacion" aria-hidden="true">
   <div class="modal-card-custom" role="dialog" aria-modal="true" aria-labelledby="tituloModalAnulacion">
       <div class="modal-header-custom">
           <div>
               <h3 id="tituloModalAnulacion">Anular arqueo</h3>
               <p>Arqueo {{ $arqueo->numero_arqueo }} · Esta acción dejará el registro en estado anulado.</p>
           </div>
           <button type="button" class="modal-close-custom" id="btnCerrarAnulacion" aria-label="Cerrar">&times;</button>
       </div>
        <form method="POST" action="{{ route('auditoria.arqueos.anular', $arqueo->id) }}" id="formAnulacion">
           @csrf
           <div class="modal-body-custom">
               <div class="warning-box-custom">
                    La anulación es irreversible. Las firmas electrónicas y el historial del arqueo se conservarán como evidencia de la operación.
                </div>

                <div class="form-group-custom">
                    <label for="motivo_anulacion">Motivo de la anulación</label>
                    <textarea id="motivo_anulacion" name="motivo_anulacion" class="form-control-custom" minlength="10" maxlength="500" required placeholder="Describa el motivo de la anulación...">{{ old('motivo_anulacion') }}</textarea>
                    @error('motivo_anulacion')
                        <div class="form-error-custom">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group-custom">
                    <label for="password">Contraseña institucional</label>
                    <input type="password" id="password" name="password" class="form-control-custom" required autocomplete="current-password" placeholder="Ingrese su contraseña para confirmar">
                    @error('password')
                        <div class="form-error-custom">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-custom" id="btnCancelarAnulacion">Cancelar</button>
                <button type="submit" class="btn-danger-custom">Confirmar anulación</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalAnulacion');
    const abrir = document.getElementById('btnAbrirAnulacion');
    const cerrar = document.getElementById('btnCerrarAnulacion');
    const cancelar = document.getElementById('btnCancelarAnulacion');

    if (!modal) return;

    const abrirModal = function () {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open-custom');
        const motivo = document.getElementById('motivo_anulacion');
        if (motivo) setTimeout(function () { motivo.focus(); }, 50);
    };

    const cerrarModal = function () {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open-custom');
    };

    if (abrir) abrir.addEventListener('click', abrirModal);
    if (cerrar) cerrar.addEventListener('click', cerrarModal);
    if (cancelar) cancelar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) cerrarModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) cerrarModal();
    });

    @if($errors->has('motivo_anulacion') || $errors->has('password'))
        abrirModal();
    @endif
});
</script>
@endpush

@endsection
