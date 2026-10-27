@extends('layouts.administrador')

@section('title', 'Crear Usuario')
@section('module-title', 'Usuarios')

@push('styles')
<style>
    .user-form-page{max-width:1120px;margin:0 auto}
    .form-heading{display:flex;align-items:center;gap:15px}
    .heading-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .heading-icon svg,.section-icon svg,.btn svg,.error-icon svg,.password-toggle svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
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
    .section-icon.security{color:var(--verde-oscuro);background:#eaf8f1}
    .section-icon svg{width:18px;height:18px}
    .section-title h3{margin:0;color:var(--azul-profundo);font-size:15px}.section-title p{margin:3px 0 0;color:var(--texto-secundario);font-size:11px}
    .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
    .field label{display:block;margin-bottom:7px;color:#536c7f;font-size:10px;font-weight:800;letter-spacing:.025em;text-transform:uppercase}
    .required{color:#b13b3b}
    .field input,.field select{width:100%;min-height:44px;padding:0 12px;border:1px solid #ced9e1;border-radius:10px;outline:none;background:#fff;color:#30485b;font:inherit;font-size:12px;box-sizing:border-box;transition:.18s ease}
    .field input:focus,.field select:focus{border-color:#82a9cc;box-shadow:0 0 0 3px rgba(22,76,150,.08)}
    .field-help{display:block;margin-top:6px;color:#8a99a5;font-size:9.5px;line-height:1.4}
    .password-wrap{position:relative}.password-wrap input{padding-right:44px}
    .password-toggle{position:absolute;right:5px;top:5px;width:34px;height:34px;display:flex;align-items:center;justify-content:center;border:0;border-radius:8px;background:transparent;color:#718696;cursor:pointer}
    .password-toggle:hover{background:#f0f4f7;color:var(--azul-principal)}
    .password-toggle svg{width:17px;height:17px}
    .form-footer{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:17px 20px;background:#fbfcfd}
    .footer-note{color:#7c8d99;font-size:10px}.actions{display:flex;justify-content:flex-end;gap:10px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:41px;padding:0 15px;border:0;border-radius:10px;font-size:10.5px;font-weight:800;text-decoration:none;cursor:pointer;transition:.2s ease}
    .btn svg{width:16px;height:16px}.primary{background:var(--azul-principal);color:#fff}.primary:hover{background:var(--azul-secundario);color:#fff;box-shadow:0 7px 16px rgba(22,76,150,.16)}.secondary{background:#edf2f5;color:#3d596f}.secondary:hover{background:#e3eaf0;color:#29485f}
    @media(max-width:700px){.form-grid{grid-template-columns:1fr}.form-intro,.form-footer{align-items:flex-start;flex-direction:column}.actions{width:100%;flex-direction:column}.btn{width:100%}}
</style>
@endpush

@section('content')
<div class="user-form-page">
    <div class="page-header">
        <div class="page-title form-heading">
            <div class="heading-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="9" cy="8" r="3"></circle>
                    <path d="M3 20v-2a6 6 0 0 1 12 0v2"></path>
                    <path d="M18 8v6M15 11h6"></path>
                </svg>
            </div>
            <div>
                <h2>Crear Usuario</h2>
                <p>Registre una nueva cuenta y asigne su rol de acceso dentro del sistema.</p>
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

    <form method="POST" action="{{ route('administrador.usuarios.store') }}" class="form-card">
        @csrf

        <div class="form-intro">
            <div>
                <strong>Nueva cuenta de acceso</strong>
                <span>Complete los datos personales, permisos y credenciales del usuario.</span>
            </div>
            <span class="account-badge">Registro de Usuario</span>
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
                    <p>Datos que identificarán al usuario dentro del sistema.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="nombres">Nombres <span class="required">*</span></label>
                    <input id="nombres" type="text" name="nombres" value="{{ old('nombres') }}" autocomplete="given-name" required>
                </div>

                <div class="field">
                    <label for="apellidos">Apellidos <span class="required">*</span></label>
                    <input id="apellidos" type="text" name="apellidos" value="{{ old('apellidos') }}" autocomplete="family-name" required>
                </div>
            </div>
        </section>

        <section class="form-section">
            <div class="section-title">
                <div class="section-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 4h14v16H5z"></path>
                        <path d="M9 8h6M9 12h6"></path>
                    </svg>
                </div>
                <div>
                    <h3>Cuenta y Permisos</h3>
                    <p>Configure el usuario, rol institucional y estado de acceso.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="usuario">Usuario <span class="required">*</span></label>
                    <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" autocomplete="username" required>
                    <span class="field-help">Identificador utilizado para iniciar sesión.</span>
                </div>

                <div class="field">
                    <label for="rol_id">Rol <span class="required">*</span></label>
                    <select id="rol_id" name="rol_id" required>
                        <option value="">Seleccione un rol</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" @selected(old('rol_id') == $rol->id)>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <span class="field-help">Determina los módulos y acciones disponibles para la cuenta.</span>
                </div>

                <div class="field">
                    <label for="estado">Estado <span class="required">*</span></label>
                    <select id="estado" name="estado" required>
                        <option value="ACTIVO" @selected(old('estado', 'ACTIVO') === 'ACTIVO')>ACTIVO</option>
                        <option value="INACTIVO" @selected(old('estado') === 'INACTIVO')>INACTIVO</option>
                    </select>
                    <span class="field-help">Las cuentas inactivas no deben tener acceso operativo al sistema.</span>
                </div>
            </div>
        </section>

        <section class="form-section">
            <div class="section-title">
                <div class="section-icon security">
                    <svg viewBox="0 0 24 24">
                        <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                        <circle cx="12" cy="15" r="1"></circle>
                    </svg>
                </div>
                <div>
                    <h3>Credenciales de Acceso</h3>
                    <p>Defina la contraseña inicial para la nueva cuenta.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="password">Contraseña <span class="required">*</span></label>
                    <div class="password-wrap">
                        <input id="password" type="password" name="password" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-target="password" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                            <svg viewBox="0 0 24 24">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                <circle cx="12" cy="12" r="2.8"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirmar Contraseña <span class="required">*</span></label>
                    <div class="password-wrap">
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                            <svg viewBox="0 0 24 24">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                <circle cx="12" cy="12" r="2.8"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="form-footer">
            <span class="footer-note"><span class="required">*</span> Campos obligatorios.</span>
            <div class="actions">
                <a href="{{ route('administrador.usuarios.index') }}" class="btn secondary">
                    <svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"></path></svg>
                    Cancelar
                </a>
                <button type="submit" class="btn primary">
                    <svg viewBox="0 0 24 24"><path d="M5 4h14v16H5z"></path><path d="M8 4v6h8V4"></path><path d="M8 20v-6h8v6"></path></svg>
                    Guardar Usuario
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.password-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;

            const mostrar = input.type === 'password';
            input.type = mostrar ? 'text' : 'password';
            this.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
            this.setAttribute('title', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });
});
</script>
@endpush
