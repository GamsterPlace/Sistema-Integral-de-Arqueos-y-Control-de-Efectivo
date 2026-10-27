@extends('layouts.administrador')

@section('title', 'Editar Usuario')
@section('module-title', 'Usuarios')

@push('styles')
<style>
    .user-form-page{max-width:1120px;margin:0 auto}
    .form-heading{display:flex;align-items:center;gap:15px}
    .heading-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .heading-icon svg,.section-icon svg,.btn svg,.error-icon svg,.status-dot svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .heading-icon svg{width:26px;height:26px}
    .errors{display:flex;gap:12px;margin-bottom:18px;padding:14px 16px;border:1px solid #efc5c5;border-radius:14px;background:#fff8f8;color:#a13b3b}
    .error-icon{width:34px;height:34px;flex:0 0 34px;display:flex;align-items:center;justify-content:center;border-radius:9px;background:#ffe8e8}
    .error-icon svg{width:17px;height:17px}
    .errors strong{display:block;margin-bottom:4px;font-size:12px}.errors ul{margin:0;padding-left:17px;font-size:11px;line-height:1.6}
    .form-card{overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(20,57,83,.05)}
    .form-intro{position:relative;display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 20px;border-bottom:1px solid #e5edf2;background:linear-gradient(105deg,rgba(22,76,150,.05),rgba(0,166,81,.025)),#fff}
    .form-intro:before{content:"";position:absolute;left:0;top:0;width:5px;height:100%;background:var(--verde-principal)}
    .form-intro strong{display:block;color:var(--azul-profundo);font-size:14px}.form-intro span{display:block;margin-top:3px;color:var(--texto-secundario);font-size:11px}
    .account-badge{padding:7px 11px;border:1px solid #d5e4f0;border-radius:999px;background:#edf4fb;color:var(--azul-principal);font-size:9.5px;font-weight:800;text-transform:uppercase;white-space:nowrap}
    .form-section{padding:20px;border-bottom:1px solid #edf1f4}.form-section:last-of-type{border-bottom:0}
    .section-title{display:flex;align-items:center;gap:11px;margin-bottom:17px}
    .section-icon{width:38px;height:38px;flex:0 0 38px;display:flex;align-items:center;justify-content:center;border-radius:11px;color:var(--azul-principal);background:#edf4fb}
    .section-icon.access{color:var(--verde-oscuro);background:#eaf8f1}
    .section-icon svg{width:18px;height:18px}
    .section-title h3{margin:0;color:var(--azul-profundo);font-size:15px}.section-title p{margin:3px 0 0;color:var(--texto-secundario);font-size:11px}
    .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
    .field label{display:block;margin-bottom:7px;color:#536c7f;font-size:10px;font-weight:800;letter-spacing:.025em;text-transform:uppercase}
    .required{color:#b13b3b}
    .field input,.field select{width:100%;min-height:44px;padding:0 12px;border:1px solid #ced9e1;border-radius:10px;outline:none;background:#fff;color:#30485b;font:inherit;font-size:12px;box-sizing:border-box;transition:.18s ease}
    .field input:focus,.field select:focus{border-color:#82a9cc;box-shadow:0 0 0 3px rgba(22,76,150,.08)}
    .field-help{display:block;margin-top:6px;color:#8a99a5;font-size:9.5px;line-height:1.4}
    .account-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:17px;padding-top:17px;border-top:1px solid #edf1f4}
    .summary-item{padding:13px 14px;border:1px solid #e1e8ed;border-radius:12px;background:#f9fbfc}
    .summary-item span{display:block;margin-bottom:4px;color:#7b8d9b;font-size:9px;font-weight:800;text-transform:uppercase}
    .summary-item strong{display:block;color:var(--azul-profundo);font-size:12px;overflow-wrap:anywhere}
    .status-badge{display:inline-flex;align-items:center;gap:6px;min-height:25px;padding:0 9px;border-radius:999px;font-size:9px;font-weight:800;text-transform:uppercase}
    .status-badge:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
    .status-active{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8}.status-inactive{color:#b13b3b;background:#fff0f0;border:1px solid #f0cccc}
    .form-footer{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:17px 20px;background:#fbfcfd}
    .footer-note{color:#7c8d99;font-size:10px}.actions{display:flex;justify-content:flex-end;gap:10px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:41px;padding:0 15px;border:0;border-radius:10px;font-size:10.5px;font-weight:800;text-decoration:none;cursor:pointer;transition:.2s ease}
    .btn svg{width:16px;height:16px}.primary{background:var(--azul-principal);color:#fff}.primary:hover{background:var(--azul-secundario);color:#fff;box-shadow:0 7px 16px rgba(22,76,150,.16)}.secondary{background:#edf2f5;color:#3d596f}.secondary:hover{background:#e3eaf0;color:#29485f}
    @media(max-width:700px){.form-grid,.account-summary{grid-template-columns:1fr}.form-intro,.form-footer{align-items:flex-start;flex-direction:column}.actions{width:100%;flex-direction:column}.btn{width:100%}}
</style>
@endpush

@section('content')
@php
    $nombreActual = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? ''));
    $estadoActual = old('estado', $usuario->estado);
@endphp

<div class="user-form-page">
    <div class="page-header">
        <div class="page-title form-heading">
            <div class="heading-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="9" cy="8" r="3"></circle>
                    <path d="M3 20v-2a6 6 0 0 1 12 0v2"></path>
                    <path d="m16 15 4-4 2 2-4 4-3 1 1-3Z"></path>
                </svg>
            </div>
            <div>
                <h2>Editar Usuario</h2>
                <p>Actualice los datos generales, rol y estado de la cuenta.</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="errors">
            <div class="error-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 7v6M12 17h.01"></path>
                </svg>
            </div>
            <div>
                <strong>Revise la información ingresada</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('administrador.usuarios.update', $usuario->id) }}" class="form-card">
        @csrf
        @method('PUT')

        <div class="form-intro">
            <div>
                <strong>{{ $nombreActual !== '' ? $nombreActual : $usuario->usuario }}</strong>
                <span>Edición de la cuenta de usuario #{{ $usuario->id }}.</span>
            </div>
            <span class="account-badge">Cuenta Registrada</span>
        </div>

        <section class="form-section">
            <div class="section-title">
                <div class="section-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M4 21a8 8 0 0 1 16 0"></path>
                    </svg>
                </div>
                <div>
                    <h3>Información Personal</h3>
                    <p>Actualice los datos que identifican al usuario dentro del sistema.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="nombres">Nombres <span class="required">*</span></label>
                    <input id="nombres" type="text" name="nombres" value="{{ old('nombres', $usuario->nombres) }}" autocomplete="given-name" required>
                </div>

                <div class="field">
                    <label for="apellidos">Apellidos <span class="required">*</span></label>
                    <input id="apellidos" type="text" name="apellidos" value="{{ old('apellidos', $usuario->apellidos) }}" autocomplete="family-name" required>
                </div>
            </div>
        </section>

        <section class="form-section">
            <div class="section-title">
                <div class="section-icon access">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                </div>
                <div>
                    <h3>Cuenta y Permisos</h3>
                    <p>Configure el identificador de acceso, rol institucional y estado de la cuenta.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="usuario">Usuario <span class="required">*</span></label>
                    <input id="usuario" type="text" name="usuario" value="{{ old('usuario', $usuario->usuario) }}" autocomplete="username" required>
                    <span class="field-help">Identificador utilizado por esta cuenta para iniciar sesión.</span>
                </div>

                <div class="field">
                    <label for="rol_id">Rol <span class="required">*</span></label>
                    <select id="rol_id" name="rol_id" required>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" @selected((int) old('rol_id', $usuario->rol_id) === (int) $rol->id)>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <span class="field-help">El rol determina los módulos y acciones disponibles.</span>
                </div>

                <div class="field">
                    <label for="estado">Estado <span class="required">*</span></label>
                    <select id="estado" name="estado" required>
                        <option value="ACTIVO" @selected(old('estado', $usuario->estado) === 'ACTIVO')>ACTIVO</option>
                        <option value="INACTIVO" @selected(old('estado', $usuario->estado) === 'INACTIVO')>INACTIVO</option>
                    </select>
                    <span class="field-help">Utilice INACTIVO para restringir el acceso operativo de la cuenta.</span>
                </div>
            </div>

            <div class="account-summary">
                <div class="summary-item">
                    <span>ID de Usuario</span>
                    <strong>#{{ $usuario->id }}</strong>
                </div>

                <div class="summary-item">
                    <span>Usuario Actual</span>
                    <strong>{{ $usuario->usuario }}</strong>
                </div>

                <div class="summary-item">
                    <span>Estado Actual</span>
                    <strong>
                        <span class="status-badge {{ $estadoActual === 'ACTIVO' ? 'status-active' : 'status-inactive' }}">
                            {{ $estadoActual }}
                        </span>
                    </strong>
                </div>
            </div>
        </section>

        <div class="form-footer">
            <span class="footer-note"><span class="required">*</span> Campos obligatorios. La contraseña no se modifica desde este formulario.</span>

            <div class="actions">
                <a href="{{ route('administrador.usuarios.show', $usuario->id) }}" class="btn secondary">
                    <svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"></path></svg>
                    Cancelar
                </a>

                <button type="submit" class="btn primary">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 4h14v16H5z"></path>
                        <path d="M8 4v6h8V4"></path>
                        <path d="M8 20v-6h8v6"></path>
                    </svg>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
