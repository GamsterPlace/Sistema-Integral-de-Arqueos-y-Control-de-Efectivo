@extends('layouts.jefe')

@section('title', 'Detalle del Arqueo')
@section('module-title', 'Todos los Arqueos')

@push('styles')
<style>
    .detail-wrapper{width:calc(100% - 32px);max-width:1080px;margin:0 auto;padding-bottom:28px}
    .detail-toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:16px}
    .detail-toolbar-copy h2{margin:0;color:#0a3158;font-size:24px;letter-spacing:-.45px}
    .detail-toolbar-copy p{margin:6px 0 0;color:#748592;font-size:12px;line-height:1.5}
    .toolbar-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap;justify-content:flex-end}
    .action-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:0 17px;border:1px solid #ccd7de;border-radius:9px;background:#fff;color:#4f6575;font-size:11px;font-weight:850;text-decoration:none;cursor:pointer}
    .action-button svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
    .print-button{border-color:#164c96;background:linear-gradient(135deg,#164c96,#1c64b5);color:#fff;box-shadow:0 8px 18px rgba(22,76,150,.18)}
    .danger-button{border-color:#b33a34;background:#b33a34;color:#fff}.danger-button:hover{background:#982f2a}

    .alert{margin-bottom:16px;padding:13px 15px;border-radius:11px;font-size:10px;font-weight:800}
    .alert-success{border:1px solid #a9d8bc;background:#ebf8f0;color:#216b45}
    .alert-error{border:1px solid #e3aaa5;background:#fff0ef;color:#9a302a}

    .detail-card{position:relative;overflow:hidden;border:1px solid #d5dce1;border-radius:18px;background:#fff;box-shadow:0 18px 48px rgba(12,49,82,.10)}
    .detail-card::after{content:"";position:absolute;z-index:0;top:310px;left:50%;width:390px;height:430px;opacity:.045;pointer-events:none;transform:translateX(-50%);background:url("{{ asset('images/logos/agentes-micoope.png') }}") center/contain no-repeat}
    .detail-header,.detail-body{position:relative;z-index:2}
    .detail-header{display:grid;grid-template-columns:180px minmax(350px,1fr) 260px;align-items:start;gap:18px;padding:20px 28px 24px;border-bottom:1px solid #e5eaee}
    .detail-header::after{content:"";position:absolute;top:66px;right:28px;width:calc(100% - 210px);height:3px;background:linear-gradient(to right,#4d693f 0%,#4d693f 66.666%,#d8b427 66.666%,#d8b427 100%)}
    .detail-brand{width:170px;height:58px;overflow:hidden;color:transparent;font-size:0;background:url("{{ asset('images/logos/ecosaba.png') }}") left center/contain no-repeat}
    .detail-title{min-width:0;padding-top:64px;text-align:center}
    .detail-title h2{margin:0;color:#111;font-family:Georgia,"Times New Roman",serif;font-size:clamp(21px,1.85vw,28px);font-weight:700;line-height:1.08;text-transform:uppercase;white-space:nowrap}
    .detail-title p{margin:8px 0 0;color:#111;font-family:Georgia,"Times New Roman",serif;font-size:16px;font-weight:700;text-transform:uppercase}
    .detail-reference{align-self:start;min-width:0;padding-top:47px;color:#7d8a94;font-size:9px;font-weight:800;text-align:right;letter-spacing:.55px;text-transform:uppercase}
    .detail-reference strong{display:block;margin-top:5px;color:#a31d17;font-family:Georgia,"Times New Roman",serif;font-size:13px;line-height:1.2;letter-spacing:.2px;white-space:nowrap}
    .detail-body{padding:24px 28px}

    .section{margin-bottom:25px}.section:last-child{margin-bottom:0}
    .section-title{margin:0 0 15px;padding-bottom:6px;border-bottom:1px solid #dce4e9;color:#111;font-family:Georgia,"Times New Roman",serif;font-size:16px;font-weight:700}
    .information-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:17px 22px}
    .field{min-width:0}.field-3{grid-column:span 3}.field-4{grid-column:span 4}.field-5{grid-column:span 5}.field-6{grid-column:span 6}.field-7{grid-column:span 7}.field-8{grid-column:span 8}.field-12{grid-column:span 12}
    .field-label{display:block;margin-bottom:3px;color:#333;font-size:10px;font-weight:800}
    .field-value{display:block;width:100%;min-width:0;min-height:34px;padding:7px 3px 4px;overflow:hidden;border-bottom:1px solid #4f5961;color:#111;font-size:12px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}

    .badge{display:inline-flex;align-items:center;padding:7px 11px;border-radius:999px;font-size:9px;font-weight:850;letter-spacing:.3px;text-transform:uppercase}
    .badge.pending{border:1px solid #c9dcf4;background:#eef5ff;color:#285b9b}
    .badge.certified{border:1px solid #c7e4d2;background:#effaf3;color:#197247}
    .badge.annulled{border:1px solid #edcaca;background:#fff1f0;color:#ae342f}

    .count-layout{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(300px,.65fr);gap:26px;align-items:start}
    .denominations-grid{display:grid;grid-template-columns:1fr;gap:23px}
    .card-heading{padding:6px 0 9px;color:#111;font-family:Georgia,"Times New Roman",serif;font-size:17px;font-weight:700}
    .count-table{width:100%;table-layout:fixed;border-collapse:collapse}
    .count-table th:nth-child(1),.count-table td:nth-child(1){width:31%}.count-table th:nth-child(2),.count-table td:nth-child(2){width:28%;text-align:center}.count-table th:nth-child(3),.count-table td:nth-child(3){width:41%;text-align:right}
    .count-table th{padding:7px 8px;color:#333;font-size:9px;font-weight:800;text-align:left}
    .count-table td{padding:7px 8px;color:#111;font-size:12px}
    .denomination-label{font-weight:800;white-space:nowrap}
    .quantity-value{display:inline-flex;align-items:center;justify-content:center;min-width:72px;min-height:34px;padding:5px 8px;border:1px solid #cfd8de;border-bottom-color:#687782;border-radius:6px;background:#fff;color:#111;font-weight:800}
    .subtotal-value{display:block;min-height:28px;padding:6px 3px 3px;border-bottom:1px solid #4f5961;font-weight:800;text-align:right;white-space:nowrap}

    .totals-card{position:sticky;top:96px;overflow:hidden;border:1px solid #d3dce2;border-radius:14px;background:rgba(250,252,253,.96);box-shadow:0 10px 26px rgba(20,57,83,.06)}
    .totals-heading{padding:14px 16px;border-bottom:1px solid #dce4e9;background:#f7f9fa;color:#0a3158;font-size:12px;font-weight:900;text-transform:uppercase}
    .totals-content{padding:17px}
    .total-line{display:grid;grid-template-columns:minmax(0,1fr) 135px;align-items:center;gap:10px;min-height:48px;border-bottom:1px solid #e2e8ec}
    .total-line span:first-child{color:#313c45;font-size:11px;font-weight:800}
    .money-value{display:block;min-height:35px;padding:7px 3px 4px;border-bottom:1px solid #4f5961;color:#111;font-size:12px;font-weight:850;text-align:right}
    .difference-card{margin-top:17px;padding:15px 14px;border:2px solid #9eb6c9;border-radius:10px;background:#eff5f9;text-align:center}
    .difference-card span{display:block;color:#4f6574;font-size:9px;font-weight:900;letter-spacing:.6px;text-transform:uppercase}
    .difference-card strong{display:block;margin-top:7px;color:#174f80;font-size:22px}.difference-card small{display:block;margin-top:4px;color:#174f80;font-size:10px;font-weight:900;text-transform:uppercase}
    .difference-card.sobrante{border-color:#75b993;background:#edf9f2}.difference-card.sobrante strong,.difference-card.sobrante small{color:#197247}
    .difference-card.faltante{border-color:#dda19d;background:#fff1f0}.difference-card.faltante strong,.difference-card.faltante small{color:#ae342f}

    .text-label{margin:0 0 7px;color:#333;font-size:10px;font-weight:800}
    .text-box{min-height:88px;padding:8px 4px;border:0;background:repeating-linear-gradient(to bottom,transparent 0,transparent 27px,#5f6971 28px);color:#111;font-size:12px;line-height:28px;white-space:pre-wrap}
    .status-box{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:15px 17px;border:1px solid #dce4e9;border-radius:11px;background:#f8fafb}.status-box strong{color:#0a3158;font-size:12px}

    .modal-backdrop{position:fixed;inset:0;z-index:2200;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(10,24,37,.65);backdrop-filter:blur(3px)}
    .modal-backdrop.is-open{display:flex}.modal-dialog{width:min(580px,100%);overflow:hidden;border-radius:18px;background:#fff;box-shadow:0 24px 70px rgba(0,0,0,.28)}
    .modal-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px 16px;border-bottom:1px solid #e7edf1}.modal-header h3{margin:0;color:#8f2f2a;font-size:20px;font-weight:800}.modal-header p{margin:6px 0 0;color:#6c7f90;font-size:10px;line-height:1.45}
    .modal-close{width:36px;height:36px;border:0;border-radius:10px;background:#eef3f7;color:#415d72;font-size:22px;cursor:pointer}.modal-body{padding:20px 22px}
    .modal-warning{margin-bottom:16px;padding:14px;border:1px solid #e3aaa5;border-radius:11px;background:#fff0ef;color:#8f2f2a;font-size:10px;line-height:1.5}
    .modal-form-group+.modal-form-group{margin-top:15px}.modal-form-group label{display:block;margin-bottom:7px;color:#52697b;font-size:9px;font-weight:800;text-transform:uppercase}
    .modal-form-control{width:100%;padding:11px 12px;border:1px solid #ced9e1;border-radius:10px;background:#fff;color:#2f4659;outline:none;box-sizing:border-box}textarea.modal-form-control{min-height:110px;resize:vertical}
    .modal-form-control:focus{border-color:#b33a34;box-shadow:0 0 0 3px rgba(179,58,52,.12)}
    .modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:16px 22px 20px;border-top:1px solid #e7edf1;background:#fbfcfd}
    body.modal-open{overflow:hidden}

    @media(max-width:1050px){.count-layout{grid-template-columns:1fr}.totals-card{position:static}.detail-header{grid-template-columns:155px minmax(280px,1fr) 220px}}
    @media(max-width:850px){.detail-toolbar{align-items:flex-start;flex-direction:column}.toolbar-actions{justify-content:flex-start}.detail-header{grid-template-columns:1fr;text-align:center}.detail-header::after{position:static;display:block;grid-column:1;width:100%;margin-top:8px}.detail-brand{margin:0 auto}.detail-title,.detail-reference{padding-top:0}.detail-title h2{white-space:normal}.detail-reference{text-align:center}.detail-reference strong{white-space:normal}.information-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.field-3,.field-4,.field-5,.field-6,.field-7,.field-8{grid-column:span 1}.field-12{grid-column:1/-1}}
    @media(max-width:620px){.detail-body,.detail-header{padding-left:16px;padding-right:16px}.information-grid{grid-template-columns:1fr}.field-3,.field-4,.field-5,.field-6,.field-7,.field-8,.field-12{grid-column:span 1}.total-line{grid-template-columns:1fr;gap:4px;padding:8px 0}.money-value{text-align:left}.toolbar-actions{width:100%}.action-button{flex:1}.modal-footer{flex-direction:column}.modal-footer .action-button{width:100%}}
</style>
@endpush

@section('content')
@php
    $nombreCreador = trim(($creador->nombres ?? '') . ' ' . ($creador->apellidos ?? ''));

    $estadoClase = match ($arqueo->estado) {
        'CERTIFICADO' => 'certified',
        'ANULADO' => 'annulled',
        default => 'pending',
    };

    $estadoTexto = match ($arqueo->estado) {
        'PENDIENTE_CERTIFICACION' => 'Pendiente de certificación',
        'CERTIFICADO' => 'Certificado',
        'ANULADO' => 'Anulado',
        default => str_replace('_', ' ', $arqueo->estado),
    };

    $tipoTexto = match ($arqueo->tipo) {
        'DIARIO_AGENTE' => 'Arqueo del Agente',
        'VISITA_PROMOTOR' => 'Arqueo del Promotor',
        'VISITA_AUDITORIA' => 'Arqueo de Auditoría',
        default => str_replace('_', ' ', $arqueo->tipo),
    };

    $rutaImpresion = match ($arqueo->tipo) {
        'VISITA_PROMOTOR' => route('jefe.arqueos-promotores.imprimir', $arqueo->id),
        'DIARIO_AGENTE' => route('jefe.arqueos.imprimir', $arqueo->id),
        default => route('jefe.arqueos.imprimir', $arqueo->id),
    };

    $diferencia = (float) $arqueo->diferencia;
    $diferenciaClase = $diferencia > 0.009 ? 'sobrante' : ($diferencia < -0.009 ? 'faltante' : '');
    $diferenciaTexto = $diferencia > 0.009 ? 'Sobrante' : ($diferencia < -0.009 ? 'Faltante' : 'Cuadrado');
@endphp

<div class="detail-wrapper">
    <div class="detail-toolbar">
        <div class="detail-toolbar-copy">
            <h2>Detalle del Arqueo</h2>
            <p>Visualización completa del documento registrado y sus datos operativos.</p>
        </div>

        <div class="toolbar-actions">
            <a href="{{ route('jefe.arqueos.index') }}" class="action-button">
                <svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m11 18-6-6 6-6"/></svg>
                Regresar
            </a>

            <a href="{{ $rutaImpresion }}" target="_blank" class="action-button print-button">
                <svg viewBox="0 0 24 24"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir PDF
            </a>

            @if ($puedeAnular)
                <button type="button" class="action-button danger-button" id="open-annulment-modal">
                    <svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
                    Anular arqueo
                </button>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <article class="detail-card">
        <header class="detail-header">
            <div class="detail-brand">ECOSABA</div>

            <div class="detail-title">
                <h2>Arqueo y Corte de Caja</h2>
                <p>Agente MICOOPE</p>
            </div>

            <div class="detail-reference">
                Número de arqueo
                <strong>{{ $arqueo->numero_arqueo }}</strong>
            </div>
        </header>

        <div class="detail-body">
            <section class="section">
                <h3 class="section-title">Información general</h3>

                <div class="information-grid">
                    <div class="field field-7"><span class="field-label">Nombre del negocio</span><span class="field-value">{{ $arqueo->nombre_negocio_historico }}</span></div>
                    <div class="field field-5"><span class="field-label">Dirección</span><span class="field-value">{{ $arqueo->direccion_historica }}</span></div>
                    <div class="field field-7"><span class="field-label">Nombre del propietario o receptor pagador</span><span class="field-value">{{ $arqueo->nombre_propietario_historico }}</span></div>
                    <div class="field field-5"><span class="field-label">Ruta</span><span class="field-value">{{ $arqueo->ruta_historica }}</span></div>
                    <div class="field field-3"><span class="field-label">Agente No.</span><span class="field-value">{{ $arqueo->codigo_agente_historico }}</span></div>
                    <div class="field field-3"><span class="field-label">Fecha</span><span class="field-value">{{ $arqueo->fecha_arqueo->format('d/m/Y') }}</span></div>
                    <div class="field field-3"><span class="field-label">Hora de inicio</span><span class="field-value">{{ $arqueo->hora_inicio ? \Carbon\Carbon::parse($arqueo->hora_inicio)->format('H:i') : '—' }}</span></div>
                    <div class="field field-3"><span class="field-label">Hora de finalización</span><span class="field-value">{{ $arqueo->hora_fin ? \Carbon\Carbon::parse($arqueo->hora_fin)->format('H:i') : '—' }}</span></div>
                    <div class="field field-4"><span class="field-label">Región</span><span class="field-value">{{ $arqueo->region_historica }}</span></div>
                    <div class="field field-4"><span class="field-label">Tipo</span><span class="field-value">{{ $tipoTexto }}</span></div>
                    <div class="field field-4"><span class="field-label">Extemporáneo</span><span class="field-value">{{ $arqueo->fuera_fecha_ordinaria ? 'Sí' : 'No' }}</span></div>
                    <div class="field field-8"><span class="field-label">Responsable</span><span class="field-value">{{ $nombreCreador !== '' ? $nombreCreador : ($creador->usuario ?? 'No disponible') }}</span></div>
                    <div class="field field-4"><span class="field-label">Rol</span><span class="field-value">{{ $creador->rol_nombre ?? 'No disponible' }}</span></div>
                </div>
            </section>

            <section class="section">
                <h3 class="section-title">Conteo de efectivo</h3>

                <div class="count-layout">
                    <div class="denominations-grid">
                        <div>
                            <div class="card-heading">Billetes</div>
                            <table class="count-table">
                                <thead><tr><th>Denominación</th><th>Cantidad</th><th>Subtotal</th></tr></thead>
                                <tbody>
                                    @forelse ($billetes as $detalle)
                                        <tr>
                                            <td class="denomination-label">Q {{ number_format((float) $detalle->denominacion, 2) }}</td>
                                            <td><span class="quantity-value">{{ $detalle->cantidad }}</span></td>
                                            <td><span class="subtotal-value">Q {{ number_format((float) $detalle->subtotal, 2) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3">No se registraron billetes.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div>
                            <div class="card-heading">Monedas</div>
                            <table class="count-table">
                                <thead><tr><th>Denominación</th><th>Cantidad</th><th>Subtotal</th></tr></thead>
                                <tbody>
                                    @forelse ($monedas as $detalle)
                                        <tr>
                                            <td class="denomination-label">Q {{ number_format((float) $detalle->denominacion, 2) }}</td>
                                            <td><span class="quantity-value">{{ $detalle->cantidad }}</span></td>
                                            <td><span class="subtotal-value">Q {{ number_format((float) $detalle->subtotal, 2) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3">No se registraron monedas.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <aside class="totals-card">
                        <div class="totals-heading">Totales</div>
                        <div class="totals-content">
                            <div class="total-line"><span>Total billetes</span><span class="money-value">Q {{ number_format((float) $arqueo->total_billetes, 2) }}</span></div>
                            <div class="total-line"><span>Total monedas</span><span class="money-value">Q {{ number_format((float) $arqueo->total_monedas, 2) }}</span></div>
                            <div class="total-line"><span>Total arqueado</span><span class="money-value">Q {{ number_format((float) $arqueo->total_arqueado, 2) }}</span></div>
                            <div class="total-line"><span>Saldo del sistema</span><span class="money-value">Q {{ number_format((float) $arqueo->saldo_sistema, 2) }}</span></div>

                            <div class="difference-card {{ $diferenciaClase }}">
                                <span>Diferencia</span>
                                <strong>Q {{ number_format($diferencia, 2) }}</strong>
                                <small>{{ $diferenciaTexto }}</small>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            <section class="section">
                <h3 class="section-title">Certificación y observaciones</h3>
                <p class="text-label">Certificación</p>
                <div class="text-box">{{ $arqueo->certificacion ?: 'Sin certificación registrada.' }}</div>

                <div style="height:18px"></div>

                <p class="text-label">Observaciones</p>
                <div class="text-box">{{ $arqueo->observaciones ?: 'Sin observaciones registradas.' }}</div>
            </section>

            <section class="section">
                <h3 class="section-title">Estado del arqueo</h3>
                <div class="status-box">
                    <strong>Estado actual del registro</strong>
                    <span class="badge {{ $estadoClase }}">{{ $estadoTexto }}</span>
                </div>
            </section>
        </div>
    </article>
</div>

@if ($puedeAnular)
<div class="modal-backdrop" id="annulment-modal" aria-hidden="true">
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="annulment-modal-title">
        <form method="POST" action="{{ route('jefe.arqueos.anular', $arqueo->id) }}">
            @csrf
            <header class="modal-header">
                <div>
                    <h3 id="annulment-modal-title">Anular arqueo</h3>
                    <p>La anulación es irreversible. El arqueo y sus firmas se conservarán como evidencia histórica.</p>
                </div>
                <button type="button" class="modal-close" id="close-annulment-modal" aria-label="Cerrar">×</button>
            </header>

            <div class="modal-body">
                <div class="modal-warning">
                    Está por anular el arqueo <strong>{{ $arqueo->numero_arqueo }}</strong>.
                    Debe registrar el motivo y confirmar con su contraseña institucional.
                </div>

                <div class="modal-form-group">
                    <label for="motivo_anulacion">Motivo de la anulación</label>
                    <textarea name="motivo_anulacion" id="motivo_anulacion" class="modal-form-control" minlength="10" maxlength="500" required placeholder="Explique claramente el motivo de la anulación...">{{ old('motivo_anulacion') }}</textarea>
                </div>

                <div class="modal-form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" class="modal-form-control" required autocomplete="current-password" placeholder="Ingrese su contraseña">
                </div>
            </div>

            <footer class="modal-footer">
                <button type="button" class="action-button" id="cancel-annulment-modal">Cancelar</button>
                <button type="submit" class="action-button danger-button">Confirmar anulación</button>
            </footer>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('annulment-modal');
    const openButton = document.getElementById('open-annulment-modal');
    const closeButton = document.getElementById('close-annulment-modal');
    const cancelButton = document.getElementById('cancel-annulment-modal');

    if (!modal) return;

    const openModal = () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        const motivo = document.getElementById('motivo_anulacion');
        if (motivo) setTimeout(() => motivo.focus(), 100);
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    };

    openButton?.addEventListener('click', openModal);
    closeButton?.addEventListener('click', closeModal);
    cancelButton?.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });

    @if ($errors->has('motivo_anulacion') || $errors->has('password'))
        openModal();
    @endif
});
</script>
@endpush
@endsection
