@extends('layouts.administrador')

@section('title', 'Perfil')
@section('module-title', 'Perfil')

@push('styles')
<style>
    .admin-profile-page{max-width:1380px;margin:0 auto}
    .module-heading{display:flex;align-items:center;gap:15px}
    .module-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .module-icon svg,.profile-avatar svg,.profile-row-icon svg,.password-btn svg,.summary-icon svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .module-icon svg{width:26px;height:26px}
    .profile-layout{display:grid;grid-template-columns:360px minmax(0,1fr);gap:20px;align-items:start}
    .profile-card,.summary-panel{overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(28,64,92,.05)}
    .profile-cover{height:88px;background:linear-gradient(135deg,var(--azul-profundo),var(--azul-principal) 68%,#0e6d80);position:relative}
    .profile-cover:after{content:"";position:absolute;right:-30px;top:-42px;width:130px;height:130px;border:1px solid rgba(255,255,255,.12);border-radius:50%}
    .profile-main{position:relative;padding:0 20px 20px}
    .profile-avatar{width:76px;height:76px;margin-top:-38px;display:flex;align-items:center;justify-content:center;border:5px solid #fff;border-radius:20px;background:#edf4fb;color:var(--azul-principal);box-shadow:0 8px 20px rgba(24,66,99,.13)}
    .profile-avatar svg{width:32px;height:32px}
    .profile-name{margin:14px 0 3px;color:var(--azul-profundo);font-size:20px;font-weight:800;line-height:1.25}
    .profile-role{margin:0 0 17px;color:var(--texto-secundario);font-size:12px;font-weight:600}
    .profile-divider{height:1px;margin-bottom:4px;background:#edf1f4}
    .profile-row{display:flex;align-items:center;gap:11px;min-height:54px;border-bottom:1px solid #edf1f4}
    .profile-row:last-of-type{border-bottom:0}
    .profile-row-icon{width:34px;height:34px;flex:0 0 34px;display:flex;align-items:center;justify-content:center;border-radius:9px;color:var(--azul-principal);background:#edf4fb}
    .profile-row-icon svg{width:16px;height:16px}
    .profile-row-content{min-width:0;flex:1}
    .profile-label{display:block;margin-bottom:2px;color:var(--texto-secundario);font-size:9.5px;font-weight:800;letter-spacing:.03em;text-transform:uppercase}
    .profile-value{display:block;color:var(--texto);font-size:12.5px;font-weight:700;overflow-wrap:anywhere}
    .status-badge{display:inline-flex;align-items:center;gap:6px;min-height:26px;padding:0 9px;border-radius:999px;font-size:9.5px;font-weight:800;text-transform:uppercase}
    .status-badge:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
    .status-active{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8}
    .status-inactive{color:#b13b3b;background:#fff0f0;border:1px solid #f0cccc}
    .status-neutral{color:#526d82;background:#f1f5f8;border:1px solid #dce5eb}
    .profile-actions{padding-top:17px}
    .password-btn{width:100%;min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 14px;border:1px solid var(--azul-principal);border-radius:11px;background:var(--azul-principal);color:#fff;text-decoration:none;font-size:12px;font-weight:800;transition:.2s ease}
    .password-btn:hover{transform:translateY(-1px);background:var(--azul-secundario);color:#fff;box-shadow:0 8px 18px rgba(22,76,150,.17)}
    .password-btn svg{width:17px;height:17px}
    .summary-panel-header{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:18px 20px;border-bottom:1px solid #e6edf2;background:#fbfcfd}
    .summary-panel-header h3{margin:0;color:var(--azul-profundo);font-size:16px}
    .summary-panel-header p{margin:3px 0 0;color:var(--texto-secundario);font-size:12px}
    .summary-badge{padding:6px 10px;border:1px solid #dce6ed;border-radius:999px;background:#fff;color:#5c7487;font-size:10px;font-weight:800;white-space:nowrap}
    .summary-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;padding:20px}
    .summary-card{min-height:128px;display:flex;align-items:center;gap:15px;padding:18px;border:1px solid #e0e8ee;border-radius:16px;background:#fff;transition:.2s ease}
    .summary-card:hover{transform:translateY(-2px);border-color:#c9d8e2;box-shadow:0 9px 20px rgba(28,64,92,.07)}
    .summary-icon{width:48px;height:48px;flex:0 0 48px;display:flex;align-items:center;justify-content:center;border-radius:14px;color:var(--azul-principal);background:#edf4fb}
    .summary-icon svg{width:22px;height:22px}
    .summary-card.agents .summary-icon{color:var(--verde-oscuro);background:#eaf8f1}
    .summary-card.audit .summary-icon{color:#725aa5;background:#f3effb}
    .summary-card.cancelled .summary-icon{color:#b13b3b;background:#fff0f0}
    .summary-card.routes .summary-icon{color:#9a7100;background:#fff7df}
    .summary-card.regions .summary-icon{color:#0e7184;background:#eaf7f9}
    .summary-label{display:block;margin-bottom:5px;color:var(--texto-secundario);font-size:10px;font-weight:800;letter-spacing:.03em;text-transform:uppercase}
    .summary-value{display:block;color:var(--azul-profundo);font-size:27px;font-weight:800;line-height:1}
    .summary-description{display:block;margin-top:6px;color:#8998a4;font-size:10.5px}
    @media(max-width:1050px){.profile-layout{grid-template-columns:320px minmax(0,1fr)}}
    @media(max-width:850px){.profile-layout{grid-template-columns:1fr}.profile-card{max-width:none}}
    @media(max-width:580px){.summary-grid{grid-template-columns:1fr}.summary-panel-header{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
@php
    $nombre = trim(($perfil->nombres ?? '') . ' ' . ($perfil->apellidos ?? ''));
    $nombreCompleto = $nombre !== '' ? $nombre : $perfil->usuario;

    $estadoTexto = strtoupper((string) ($perfil->estado ?? ''));
    $estadoClase = match ($estadoTexto) {
        'ACTIVO', '1' => 'status-active',
        'INACTIVO', '0' => 'status-inactive',
        default => 'status-neutral',
    };
@endphp

<div class="admin-profile-page">
    <div class="page-header">
        <div class="page-title module-heading">
            <div class="module-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4"></circle>
                    <path d="M4 21a8 8 0 0 1 16 0"></path>
                </svg>
            </div>
            <div>
                <h2>Perfil del Administrador</h2>
                <p>Información de la cuenta y resumen general del sistema.</p>
            </div>
        </div>
    </div>

    <div class="profile-layout">
        <section class="profile-card">
            <div class="profile-cover"></div>

            <div class="profile-main">
                <div class="profile-avatar">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M4 21a8 8 0 0 1 16 0"></path>
                    </svg>
                </div>

                <h3 class="profile-name">{{ $nombreCompleto }}</h3>
                <p class="profile-role">Administrador del Sistema</p>

                <div class="profile-divider"></div>

                <div class="profile-row">
                    <div class="profile-row-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M5 21a7 7 0 0 1 14 0"></path>
                        </svg>
                    </div>
                    <div class="profile-row-content">
                        <span class="profile-label">Usuario</span>
                        <span class="profile-value">{{ $perfil->usuario }}</span>
                    </div>
                </div>

                <div class="profile-row">
                    <div class="profile-row-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </div>
                    <div class="profile-row-content">
                        <span class="profile-label">Rol</span>
                        <span class="profile-value">Administrador</span>
                    </div>
                </div>

                <div class="profile-row">
                    <div class="profile-row-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="m8 12 2.5 2.5L16 9"></path>
                        </svg>
                    </div>
                    <div class="profile-row-content">
                        <span class="profile-label">Estado de la cuenta</span>
                        <span class="profile-value">
                            <span class="status-badge {{ $estadoClase }}">
                                {{ $perfil->estado ?? '—' }}
                            </span>
                        </span>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="{{ url('/cambiar-password') }}" class="password-btn">
                        <svg viewBox="0 0 24 24">
                            <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                            <circle cx="12" cy="15" r="1"></circle>
                            <path d="M12 16v2"></path>
                        </svg>
                        Cambiar contraseña
                    </a>
                </div>
            </div>
        </section>

        <section class="summary-panel">
            <div class="summary-panel-header">
                <div>
                    <h3>Resumen General del Sistema</h3>
                    <p>Vista rápida de los principales registros administrados.</p>
                </div>
                <span class="summary-badge">Panel Administrativo</span>
            </div>

            <div class="summary-grid">
                <article class="summary-card">
                    <div class="summary-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="9" cy="8" r="3"></circle>
                            <path d="M3 20v-2a6 6 0 0 1 12 0v2"></path>
                            <circle cx="17" cy="9" r="2"></circle>
                            <path d="M16 14a5 5 0 0 1 5 5v1"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="summary-label">Usuarios</span>
                        <strong class="summary-value">{{ $resumen->usuarios }}</strong>
                        <span class="summary-description">Cuentas registradas</span>
                    </div>
                </article>

                <article class="summary-card agents">
                    <div class="summary-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 10h16v10H4z"></path>
                            <path d="M3 10 5 4h14l2 6"></path>
                            <path d="M8 20v-5h8v5"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="summary-label">Agentes</span>
                        <strong class="summary-value">{{ $resumen->agentes }}</strong>
                        <span class="summary-description">Agentes MICOOPE</span>
                    </div>
                </article>

                <article class="summary-card audit">
                    <div class="summary-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 3h14v18H5z"></path>
                            <path d="M8 7h8M8 11h8M8 15h5"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="summary-label">Arqueos</span>
                        <strong class="summary-value">{{ $resumen->arqueos }}</strong>
                        <span class="summary-description">Registros de arqueo</span>
                    </div>
                </article>

                <article class="summary-card cancelled">
                    <div class="summary-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="m9 9 6 6M15 9l-6 6"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="summary-label">Anulados</span>
                        <strong class="summary-value">{{ $resumen->anulados }}</strong>
                        <span class="summary-description">Arqueos anulados</span>
                    </div>
                </article>

                <article class="summary-card routes">
                    <div class="summary-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="6" cy="18" r="2"></circle>
                            <circle cx="18" cy="6" r="2"></circle>
                            <path d="M8 18h3a4 4 0 0 0 4-4v-4a4 4 0 0 1 3-4"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="summary-label">Rutas</span>
                        <strong class="summary-value">{{ $resumen->rutas }}</strong>
                        <span class="summary-description">Rutas registradas</span>
                    </div>
                </article>

                <article class="summary-card regions">
                    <div class="summary-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 21s7-5.2 7-12a7 7 0 1 0-14 0c0 6.8 7 12 7 12Z"></path>
                            <circle cx="12" cy="9" r="2.5"></circle>
                        </svg>
                    </div>
                    <div>
                        <span class="summary-label">Regiones</span>
                        <strong class="summary-value">{{ $resumen->regiones }}</strong>
                        <span class="summary-description">Regiones configuradas</span>
                    </div>
                </article>
            </div>
        </section>
    </div>
</div>
@endsection
