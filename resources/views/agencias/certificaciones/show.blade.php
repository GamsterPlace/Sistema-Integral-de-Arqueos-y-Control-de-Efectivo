
@php
    $layout = collect(
        $rutaBase === 'jefecaja'
            ? ['layouts.jefecaja', 'layouts.jefe-caja', 'layouts.jefe_caja']
            : ['layouts.subjefecaja', 'layouts.subjefe-caja', 'layouts.subjefe_caja']
    )->first(fn ($nombre) => view()->exists($nombre));

    if (!$layout) {
        throw new \RuntimeException(
            'No se encontró el layout del rol. Verifique el nombre del archivo en resources/views/layouts.'
        );
    }
@endphp

@extends($layout)

@section('title', 'Revisión y Certificación de Arqueo')

@push('styles')
<style>
    .cr-page{display:grid;gap:20px}
    .cr-header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
    .cr-header h1{margin:0;color:#06284f;font-size:27px}
    .cr-header p{margin:8px 0 0;color:#708398;font-size:13px}
    .cr-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .cr-print{background:#164c96;color:#fff;border-color:#164c96}
    .cr-print:hover{background:#0c3976;color:#fff}
    .cr-back{display:inline-flex;padding:10px 14px;border:1px solid #d9e5f0;border-radius:10px;background:#fff;color:#164c96;text-decoration:none;font-size:12px;font-weight:800}
    .cr-panel{background:#fff;border:1px solid #dfe7ee;border-radius:16px;padding:22px;box-shadow:0 5px 20px rgba(6,40,79,.045)}
    .cr-panel h2{margin:0 0 18px;color:#06284f;font-size:18px}
    .cr-info{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:17px}
    .cr-info-item span{display:block;font-size:11px;color:#728599;font-weight:700;margin-bottom:6px}
    .cr-info-item strong{display:block;color:#193b5c;font-size:13px;line-height:1.5;overflow-wrap:anywhere}
    .cr-totals{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}
    .cr-total{padding:17px;border-radius:12px;border:1px solid #dce7f2;background:#f5f9fe}
    .cr-total span{display:block;color:#698198;font-size:12px;font-weight:700}
    .cr-total strong{display:block;margin-top:9px;color:#06284f;font-size:22px}
    .cr-total.highlight{background:#eaf4ff;border-color:#c8dff6}
    .cr-total.warning{background:#fff8e8;border-color:#f2dca8}
    .cr-total.warning strong{color:#a46b18}
    .cr-total.danger{background:#fff1f1;border-color:#efcaca}
    .cr-total.danger strong{color:#b53f3f}
    .cr-table-wrap{overflow-x:auto}
    .cr-table{width:100%;border-collapse:collapse;text-align:left;font-size:13px}
    .cr-table th{padding:12px;background:#f4f8fc;color:#60768c;font-size:12px}
    .cr-table td{padding:12px;border-bottom:1px solid #eaf0f5}
    .cr-table tr:last-child td{border-bottom:0}
    .cr-right{text-align:right;white-space:nowrap}
    .cr-notes{display:grid;gap:14px}
    .cr-note{padding:15px;border-radius:11px;background:#f7fafc;border:1px solid #e1e9f0}
    .cr-note span{display:block;color:#718398;font-size:11px;font-weight:800;margin-bottom:7px}
    .cr-note p{margin:0;color:#274663;font-size:13px;line-height:1.65;white-space:pre-wrap}
    .cr-signatures{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .cr-signature{padding:17px;border-radius:12px;border:1px solid #dce6ee;background:#fafcfe}
    .cr-signature span{display:block;color:#6b8094;font-size:12px;font-weight:750}
    .cr-signature strong{display:block;margin-top:8px;font-size:14px}
    .cr-valid{color:#16814c}
    .cr-invalid{color:#b53f3f}
    .cr-pending{color:#a16d20}
    .cr-alert{padding:15px 17px;border-radius:11px;font-size:13px;line-height:1.65}
    .cr-alert.warning{background:#fff7e8;border:1px solid #f0dba8;color:#93631d}
    .cr-alert.error{background:#fff2f2;border:1px solid #efcaca;color:#aa3535}
    .cr-alert.success{background:#ebf8f0;border:1px solid #bde5cd;color:#167344}
    .cr-confirm{padding:20px;border:1px solid #cbdff2;border-radius:13px;background:#f4f9ff}
    .cr-confirm h3{margin:0 0 10px;color:#0b3d78;font-size:17px}
    .cr-confirm p{color:#5e7690;font-size:13px;line-height:1.65}
    .cr-checkbox{display:flex;align-items:flex-start;gap:11px;margin:18px 0;color:#254969;font-size:13px;line-height:1.6;cursor:pointer}
    .cr-checkbox input{margin-top:4px;width:17px;height:17px;accent-color:#164c96;flex:0 0 auto}
    .cr-submit{display:inline-flex;align-items:center;justify-content:center;padding:13px 20px;border:0;border-radius:10px;background:#164c96;color:#fff;font-size:13px;font-weight:800;cursor:pointer}
    .cr-submit:hover{background:#0a3977}
    .cr-submit:disabled{opacity:.6;cursor:not-allowed}
    .cr-meta{color:#718398;font-size:12px;margin-top:12px}
    @media(max-width:900px){.cr-info,.cr-totals{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:600px){.cr-info,.cr-totals,.cr-signatures{grid-template-columns:1fr}.cr-panel{padding:16px}.cr-header h1{font-size:23px}}
</style>
@endpush

@section('content')
@php
    $diferencia = (float)$arqueo->diferencia;

    $tieneDiferencia = abs($diferencia) > 0.00001;

    $tieneJustificacion = trim(
        (string)$arqueo->justificacion_diferencia
    ) !== '';

    $esPendiente = $arqueo->estado === 'PENDIENTE';

    $esCertificado = $arqueo->estado === 'CERTIFICADO';

    $puedeCertificar =
        $esPendiente &&
        $arqueo->certificado_at === null &&
        $arqueo->anulado_at === null &&
        $arqueo->firmado_receptor_at !== null &&
        $firmaReceptorValida &&
        (!$tieneDiferencia || $tieneJustificacion);
@endphp

<div class="cr-page">

    <header class="cr-header">
        <div>
            <h1>Revisión de Arqueo</h1>
            <p>
                Número:
                <strong>{{ $arqueo->numero_arqueo ?: '#'.$arqueo->id }}</strong>
                · Estado: <strong>{{ $arqueo->estado }}</strong>
            </p>
        </div>

        <div class="cr-actions">
            <a href="{{ route($rutaBase.'.certificaciones.imprimir', $arqueo->id) }}"
               class="cr-back cr-print" target="_blank" rel="noopener noreferrer">
                Imprimir PDF
            </a>
            <a href="{{ route($rutaBase.'.certificaciones.index') }}" class="cr-back">
                ← Volver a Certificaciones
            </a>
        </div>
    </header>

    @if($errors->any())
        <div class="cr-alert error" role="alert">
            <strong>No se pudo completar la operación.</strong>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <section class="cr-panel">
        <h2>Información General</h2>

        <div class="cr-info">
            <div class="cr-info-item">
                <span>Agencia</span>
                <strong>
                    {{ $arqueo->agencia_nombre_historico ?: 'Agencia #'.$arqueo->agencia_id }}
                </strong>
            </div>

            <div class="cr-info-item">
                <span>Ventanilla</span>
                <strong>
                    {{ $arqueo->ventanilla_codigo_historico ?: 'No especificada' }}
                </strong>
            </div>

            <div class="cr-info-item">
                <span>Fecha operativa</span>
                <strong>
                    {{ $arqueo->fecha_operativa?->format('d/m/Y') ?? 'No disponible' }}
                </strong>
            </div>

            <div class="cr-info-item">
                <span>Receptor</span>
                <strong>
                    {{ $arqueo->receptor_nombre_historico ?: 'No disponible' }}
                </strong>
            </div>

            <div class="cr-info-item">
                <span>Turno</span>
                <strong>
                    {{ $arqueo->turno_nombre_historico ?: 'No especificado' }}
                </strong>
            </div>

            <div class="cr-info-item">
                <span>Tipo de arqueo</span>
                <strong>
                    {{ str_replace('_', ' ', $arqueo->tipo) }}
                </strong>
            </div>
        </div>
    </section>

    <section class="cr-panel">
        <h2>Resumen Económico</h2>

        <div class="cr-totals">
            <div class="cr-total">
                <span>Total en billetes</span>
                <strong>
                    Q {{ number_format((float)$arqueo->total_billetes, 2) }}
                </strong>
            </div>

            <div class="cr-total">
                <span>Total en monedas</span>
                <strong>
                    Q {{ number_format((float)$arqueo->total_monedas, 2) }}
                </strong>
            </div>

            <div class="cr-total">
                <span>Otros valores</span>
                <strong>
                    Q {{ number_format((float)$arqueo->total_otros_valores, 2) }}
                </strong>
            </div>

            <div class="cr-total highlight">
                <span>Total arqueado</span>
                <strong>
                    Q {{ number_format((float)$arqueo->total_arqueado, 2) }}
                </strong>
            </div>

            <div class="cr-total">
                <span>Total del corte</span>
                <strong>
                    Q {{ number_format((float)$arqueo->total_corte, 2) }}
                </strong>
            </div>

            <div class="cr-total {{ $tieneDiferencia ? ($diferencia < 0 ? 'danger' : 'warning') : 'highlight' }}">
                <span>Diferencia registrada</span>
                <strong>
                    Q {{ number_format($diferencia, 2) }}
                </strong>
            </div>
        </div>
    </section>

    <section class="cr-panel">
        <h2>Desglose del Arqueo</h2>

        <div class="cr-table-wrap">
            <table class="cr-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Denominación / Concepto</th>
                        <th class="cr-right">Cantidad</th>
                        <th class="cr-right">Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($arqueo->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->tipo }}</td>

                            <td>
                                {{ $detalle->denominacion ?? $detalle->concepto ?? '—' }}
                            </td>

                            <td class="cr-right">
                                {{ $detalle->cantidad }}
                            </td>

                            <td class="cr-right">
                                Q {{ number_format((float)$detalle->subtotal, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;color:#718398">
                                No hay detalles registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="cr-panel">
        <h2>Observaciones y Diferencias</h2>

        <div class="cr-notes">
            <div class="cr-note">
                <span>Justificación de diferencia</span>
                <p>
                    {{ $arqueo->justificacion_diferencia ?: 'No se registró una justificación.' }}
                </p>
            </div>

            <div class="cr-note">
                <span>Observaciones del arqueo</span>
                <p>
                    {{ $arqueo->observaciones ?: 'Sin observaciones registradas.' }}
                </p>
            </div>
        </div>

        @if($tieneDiferencia && !$tieneJustificacion)
            <div class="cr-alert warning" style="margin-top:16px">
                <strong>Diferencia pendiente de justificación.</strong>
                Este arqueo presenta un faltante o sobrante.
                No podrá certificarse hasta que se complete
                el procedimiento autorizado de justificación.
            </div>
        @elseif($tieneDiferencia)
            <div class="cr-alert warning" style="margin-top:16px">
                Este arqueo presenta una diferencia registrada.
                Revise cuidadosamente la justificación antes de certificar.
            </div>
        @endif
    </section>

    <section class="cr-panel">
        <h2>Verificación de Firmas Electrónicas</h2>

        <div class="cr-signatures">
            <div class="cr-signature">
                <span>Firma del receptor</span>

                @if($firmaReceptorValida)
                    <strong class="cr-valid">
                        Íntegra y verificada
                    </strong>
                @else
                    <strong class="cr-invalid">
                        No válida o no disponible
                    </strong>
                @endif

                @if($arqueo->firmado_receptor_at)
                    <div class="cr-meta">
                        Firmado:
                        {{ $arqueo->firmado_receptor_at->format('d/m/Y H:i:s') }}
                    </div>
                @endif
            </div>

            <div class="cr-signature">
                <span>Firma del certificador</span>

                @if($esCertificado)
                    @if($firmaCertificadorValida)
                        <strong class="cr-valid">
                            Íntegra y verificada
                        </strong>
                    @else
                        <strong class="cr-invalid">
                            No válida o no disponible
                        </strong>
                    @endif

                    @if($arqueo->certificado_at)
                        <div class="cr-meta">
                            Certificado:
                            {{ $arqueo->certificado_at->format('d/m/Y H:i:s') }}
                        </div>
                    @endif
                @else
                    <strong class="cr-pending">
                        Pendiente de certificación
                    </strong>
                @endif
            </div>
        </div>
    </section>

    @if($esCertificado)
        <div class="cr-alert {{ $firmaCertificadorValida ? 'success' : 'error' }}">
            @if($firmaCertificadorValida)
                <strong>Arqueo certificado.</strong>
                La firma electrónica del certificador ha sido verificada.
            @else
                <strong>Advertencia de integridad.</strong>
                El arqueo figura como certificado, pero no se pudo
                verificar correctamente su firma electrónica.
                Debe revisarse antes de utilizarlo como documento validado.
            @endif
        </div>

    @elseif($puedeCertificar)
        <section class="cr-confirm">
            <h3>Certificación Electrónica</h3>

            <p>
                Al confirmar esta operación, el sistema registrará
                su firma electrónica como responsable de caja.
                Revise los importes y el desglose antes de continuar.
            </p>

            <form
                method="POST"
                action="{{ route($rutaBase.'.certificaciones.store', $arqueo->id) }}"
                id="formCertificacion"
            >
                @csrf

                <label class="cr-checkbox">
                    <input
                        type="checkbox"
                        name="confirmar_certificacion"
                        value="1"
                        required
                    >

                    <span>
                        He revisado el arqueo, sus denominaciones,
                        importes, diferencia, observaciones y firma
                        electrónica del receptor. Confirmo que deseo
                        certificar electrónicamente este registro.
                    </span>
                </label>

                <button type="submit" class="cr-submit" id="btnCertificar">
                    Firmar y Certificar Arqueo
                </button>
            </form>
        </section>

    @else
        <div class="cr-alert warning">
            <strong>Certificación no disponible.</strong>
            Este arqueo no cumple actualmente todas las condiciones
            necesarias para realizar la certificación electrónica.
            Verifique su estado, la firma del receptor y las
            justificaciones correspondientes.
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formCertificacion');
    const boton = document.getElementById('btnCertificar');

    if (!form || !boton) return;

    form.addEventListener('submit', function () {
        if (!form.checkValidity()) return;

        boton.disabled = true;
        boton.textContent = 'Procesando certificación...';
    });
});
</script>
@endpush
