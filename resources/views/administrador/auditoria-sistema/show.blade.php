@extends('layouts.administrador')

@section('title', 'Detalle Auditoría del Sistema')
@section('module-title', 'Auditoría del Sistema')

@push('styles')
<style>
    .audit-system-detail{max-width:1380px;margin:0 auto}
    .detail-header{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:22px}
    .detail-heading{display:flex;align-items:center;gap:15px;min-width:0}
    .detail-heading-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .detail-heading-icon svg,.btn-detail svg,.panel-icon svg,.trace-icon svg,.empty-icon svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .detail-heading-icon svg{width:26px;height:26px}
    .detail-heading h2{margin:0 0 4px;color:var(--azul-profundo);font-size:25px;line-height:1.15}
    .detail-heading p{margin:0;color:var(--texto-secundario);font-size:14px}
    .btn-detail{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 15px;border:1px solid #d6e1e9;border-radius:11px;background:#fff;color:#31536e;font-size:13px;font-weight:700;text-decoration:none;transition:.2s ease;white-space:nowrap}
    .btn-detail:hover{transform:translateY(-1px);border-color:#a9becd;color:var(--azul-principal);box-shadow:0 7px 18px rgba(24,66,99,.09)}
    .btn-detail svg{width:17px;height:17px}
    .immutable-notice{position:relative;display:flex;align-items:center;gap:13px;margin-bottom:18px;padding:14px 17px;border:1px solid #d9e4eb;border-radius:14px;background:#f8fafc;color:#536d80}
    .immutable-notice:before{content:"";position:absolute;top:0;left:0;width:4px;height:100%;border-radius:14px 0 0 14px;background:var(--verde-principal)}
    .trace-icon{width:35px;height:35px;flex:0 0 35px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:var(--verde-oscuro);background:#e9f8f0}
    .trace-icon svg{width:18px;height:18px}
    .immutable-notice strong{display:block;margin-bottom:2px;color:var(--azul-profundo);font-size:12px}
    .immutable-notice span{font-size:11.5px}
    .detail-panel{overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(28,64,92,.05)}
    .general-panel{margin-bottom:18px}
    .panel-heading{display:flex;align-items:center;gap:11px;padding:17px 19px;border-bottom:1px solid #e6edf2;background:#fbfcfd}
    .panel-icon{width:37px;height:37px;flex:0 0 37px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:var(--azul-principal);background:#edf4fb}
    .panel-icon.previous{color:#936b00;background:#fff7df}
    .panel-icon.new{color:var(--verde-oscuro);background:#eaf8f1}
    .panel-icon svg{width:19px;height:19px}
    .panel-heading h3{margin:0;color:var(--azul-profundo);font-size:15px}
    .panel-heading p{margin:2px 0 0;color:var(--texto-secundario);font-size:12px}
    .general-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:0;padding:5px 19px 8px}
    .info-item{min-height:74px;padding:14px 15px;border-bottom:1px solid #edf1f4;border-right:1px solid #edf1f4}
    .info-item:nth-child(4n){border-right:0}
    .info-label{display:block;margin-bottom:6px;color:var(--texto-secundario);font-size:10px;font-weight:800;letter-spacing:.035em;text-transform:uppercase}
    .info-value{display:block;color:var(--texto);font-size:13px;font-weight:700;overflow-wrap:anywhere}
    .info-value.primary{color:var(--azul-principal)}
    .role-badge,.module-badge,.action-badge,.table-badge{display:inline-flex;align-items:center;min-height:27px;padding:0 9px;border-radius:999px;font-size:10px;font-weight:800;white-space:nowrap}
    .role-badge{color:#526d82;background:#f1f5f8;border:1px solid #dce5eb}
    .module-badge{color:#164c96;background:#edf4fb;border:1px solid #d5e4f0}
    .action-badge{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8;text-transform:uppercase}
    .table-badge{color:#725aa5;background:#f3effb;border:1px solid #dfd5f0}
    .description-box{margin:0 19px 18px;padding:15px 16px;border:1px solid #e0e8ee;border-radius:13px;background:#f8fafc}
    .description-box .info-label{margin-bottom:7px}
    .description-text{margin:0;color:#344f64;font-size:13px;line-height:1.6;white-space:pre-line;overflow-wrap:anywhere}
    .values-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    .values-body{padding:7px 18px 12px}
    .value-row{display:grid;grid-template-columns:minmax(150px,.42fr) minmax(0,1fr);gap:15px;align-items:start;padding:12px 0;border-bottom:1px solid #edf1f4}
    .value-row:last-child{border-bottom:0}
    .value-key{color:#526d82;font-size:11px;font-weight:800;overflow-wrap:anywhere}
    .value-content{padding:8px 10px;border:1px solid #e3eaf0;border-radius:9px;background:#f8fafc;color:#243f54;font-family:DejaVu Sans Mono,Consolas,monospace;font-size:11px;line-height:1.5;white-space:pre-wrap;overflow-wrap:anywhere}
    .empty-values{padding:35px 20px;text-align:center}
    .empty-icon{width:46px;height:46px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;border-radius:13px;color:#7890a2;background:#f1f5f8}
    .empty-icon svg{width:21px;height:21px}
    .empty-values strong{display:block;margin-bottom:3px;color:var(--azul-profundo);font-size:13px}
    .empty-values span{color:var(--texto-secundario);font-size:11.5px}
    @media(max-width:1050px){.general-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.info-item:nth-child(4n){border-right:1px solid #edf1f4}.info-item:nth-child(2n){border-right:0}}
    @media(max-width:850px){.values-grid{grid-template-columns:1fr}}
    @media(max-width:650px){.detail-header{align-items:flex-start;flex-direction:column}.general-grid{grid-template-columns:1fr}.info-item,.info-item:nth-child(2n),.info-item:nth-child(4n){border-right:0}.value-row{grid-template-columns:1fr;gap:6px}.btn-detail{width:100%}}
</style>
@endpush

@section('content')
@php
    $nombre = trim(
        ($registro->nombres ?? '') . ' ' . ($registro->apellidos ?? '')
    );

    $nombreUsuario = $nombre !== ''
        ? $nombre
        : ($registro->usuario ?? 'Sistema');

    $fechaRegistro = \Carbon\Carbon::parse($registro->created_at);

    $formatearValor = function ($valor) {
        if (is_array($valor) || is_object($valor)) {
            return json_encode(
                $valor,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            );
        }

        if (is_bool($valor)) {
            return $valor ? 'true' : 'false';
        }

        if (is_null($valor)) {
            return 'null';
        }

        return (string) $valor;
    };
@endphp

<div class="audit-system-detail">
    <div class="detail-header">
        <div class="detail-heading">
            <div class="detail-heading-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path>
                    <path d="M9 9h6M9 12h6M9 15h4"></path>
                </svg>
            </div>
            <div>
                <h2>Registro de Auditoría #{{ $registro->id }}</h2>
                <p>Detalle inalterable de la acción registrada por el sistema.</p>
            </div>
        </div>

        <a href="{{ route('administrador.auditoria-sistema.index') }}" class="btn-detail">
            <svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"></path></svg>
            Regresar
        </a>
    </div>

    <div class="immutable-notice">
        <div class="trace-icon">
            <svg viewBox="0 0 24 24">
                <path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path>
                <path d="m9 12 2 2 4-4"></path>
            </svg>
        </div>
        <div>
            <strong>Registro de trazabilidad</strong>
            <span>Esta información corresponde al historial de auditoría del sistema y se presenta únicamente para consulta.</span>
        </div>
    </div>

    <section class="detail-panel general-panel">
        <div class="panel-heading">
            <div class="panel-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M5 3h14v18H5z"></path>
                    <path d="M8 7h8M8 11h8M8 15h5"></path>
                </svg>
            </div>
            <div>
                <h3>Información General</h3>
                <p>Identificación de la acción y del usuario que generó el registro.</p>
            </div>
        </div>

        <div class="general-grid">
            <div class="info-item">
                <span class="info-label">Fecha</span>
                <span class="info-value">{{ $fechaRegistro->format('d/m/Y H:i:s') }}</span>
            </div>

            <div class="info-item">
                <span class="info-label">Usuario</span>
                <span class="info-value primary">{{ $nombreUsuario }}</span>
            </div>

            <div class="info-item">
                <span class="info-label">Rol</span>
                <span class="info-value">
                    <span class="role-badge">{{ $registro->rol_nombre ?? '—' }}</span>
                </span>
            </div>

            <div class="info-item">
                <span class="info-label">Módulo</span>
                <span class="info-value">
                    <span class="module-badge">{{ $registro->modulo }}</span>
                </span>
            </div>

            <div class="info-item">
                <span class="info-label">Acción</span>
                <span class="info-value">
                    <span class="action-badge">{{ $registro->accion }}</span>
                </span>
            </div>

            <div class="info-item">
                <span class="info-label">Tabla afectada</span>
                <span class="info-value">
                    <span class="table-badge">{{ $registro->tabla_afectada ?? '—' }}</span>
                </span>
            </div>

            <div class="info-item">
                <span class="info-label">ID del registro</span>
                <span class="info-value primary">{{ $registro->registro_id ?? '—' }}</span>
            </div>

            <div class="info-item">
                <span class="info-label">ID de auditoría</span>
                <span class="info-value">#{{ $registro->id }}</span>
            </div>
        </div>

        <div class="description-box">
            <span class="info-label">Descripción</span>
            <p class="description-text">{{ $registro->descripcion ?: 'Sin descripción registrada.' }}</p>
        </div>
    </section>

    <div class="values-grid">
        <section class="detail-panel">
            <div class="panel-heading">
                <div class="panel-icon previous">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 12a9 9 0 1 0 3-6.7"></path>
                        <path d="M3 4v6h6"></path>
                    </svg>
                </div>
                <div>
                    <h3>Valores Anteriores</h3>
                    <p>Información existente antes de ejecutar la acción.</p>
                </div>
            </div>

            @forelse($anteriores as $clave => $valor)
                @if($loop->first)
                    <div class="values-body">
                @endif

                <div class="value-row">
                    <div class="value-key">{{ $clave }}</div>
                    <div class="value-content">{{ $formatearValor($valor) }}</div>
                </div>

                @if($loop->last)
                    </div>
                @endif
            @empty
                <div class="empty-values">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 12h8"></path></svg>
                    </div>
                    <strong>Sin valores anteriores</strong>
                    <span>No se registraron valores previos para esta acción.</span>
                </div>
            @endforelse
        </section>

        <section class="detail-panel">
            <div class="panel-heading">
                <div class="panel-icon new">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 3h14v18H5z"></path>
                        <path d="m8 12 2.5 2.5L16 9"></path>
                    </svg>
                </div>
                <div>
                    <h3>Valores Nuevos</h3>
                    <p>Información registrada después de ejecutar la acción.</p>
                </div>
            </div>

            @forelse($nuevos as $clave => $valor)
                @if($loop->first)
                    <div class="values-body">
                @endif

                <div class="value-row">
                    <div class="value-key">{{ $clave }}</div>
                    <div class="value-content">{{ $formatearValor($valor) }}</div>
                </div>

                @if($loop->last)
                    </div>
                @endif
            @empty
                <div class="empty-values">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 12h8"></path></svg>
                    </div>
                    <strong>Sin valores nuevos</strong>
                    <span>No se registraron valores posteriores para esta acción.</span>
                </div>
            @endforelse
        </section>
    </div>
</div>
@endsection
