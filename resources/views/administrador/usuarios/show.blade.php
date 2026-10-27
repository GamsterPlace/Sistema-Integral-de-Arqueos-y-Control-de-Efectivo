@extends('layouts.administrador')

@section('title', 'Detalle de Usuario')
@section('module-title', 'Usuarios')

@push('styles')
<style>
    .user-detail-page{max-width:1180px;margin:0 auto}
    .module-heading{display:flex;align-items:center;gap:15px}
    .heading-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .heading-icon svg,.btn svg,.panel-icon svg,.password-toggle svg,.info-icon svg,.error-icon svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .heading-icon svg{width:26px;height:26px}
    .header-actions{display:flex;align-items:center;gap:9px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:41px;padding:0 15px;border:0;border-radius:10px;font-size:10.5px;font-weight:800;text-decoration:none;cursor:pointer;transition:.2s ease}
    .btn svg{width:16px;height:16px}
    .btn-primary{background:var(--azul-principal);color:#fff;box-shadow:0 7px 17px rgba(22,76,150,.15)}.btn-primary:hover{background:var(--azul-secundario);color:#fff;transform:translateY(-1px)}
    .btn-secondary{background:#edf2f5;color:#3d596f}.btn-secondary:hover{background:#e3eaf0;color:#29485f}
    .errors{display:flex;gap:12px;margin-bottom:18px;padding:14px 16px;border:1px solid #efc5c5;border-radius:14px;background:#fff8f8;color:#a13b3b}
    .error-icon{width:34px;height:34px;flex:0 0 34px;display:flex;align-items:center;justify-content:center;border-radius:9px;background:#ffe8e8}.error-icon svg{width:17px;height:17px}
    .errors strong{display:block;margin-bottom:4px;font-size:12px}.errors ul{margin:0;padding-left:17px;font-size:11px;line-height:1.6}
    .profile-banner{position:relative;overflow:hidden;display:flex;align-items:center;gap:17px;margin-bottom:18px;padding:20px;border:1px solid #dce6ed;border-radius:18px;background:linear-gradient(110deg,#f7fbff,#fff 58%,#f2fbf6);box-shadow:0 8px 24px rgba(20,57,83,.04)}
    .profile-banner:before{content:"";position:absolute;left:0;top:0;width:5px;height:100%;background:var(--verde-principal)}
    .avatar{width:62px;height:62px;flex:0 0 62px;display:flex;align-items:center;justify-content:center;border-radius:17px;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));color:#fff;font-size:21px;font-weight:800;box-shadow:0 8px 18px rgba(22,76,150,.16)}
    .profile-main{min-width:0;flex:1}.profile-main h3{margin:0;color:var(--azul-profundo);font-size:18px}.profile-main p{margin:5px 0 0;color:var(--texto-secundario);font-size:10.5px}
    .status-badge{display:inline-flex;align-items:center;gap:6px;min-height:27px;padding:0 10px;border-radius:999px;font-size:9px;font-weight:800;text-transform:uppercase;white-space:nowrap}.status-badge:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
    .status-active{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8}.status-inactive{color:#b13b3b;background:#fff0f0;border:1px solid #f0cccc}
    .detail-grid{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(360px,.92fr);gap:18px}
    .panel{overflow:hidden;border:1px solid #e0e8ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(20,57,83,.05)}
    .panel-header{display:flex;align-items:center;gap:11px;padding:17px 18px;border-bottom:1px solid #edf1f4;background:#fafcfd}
    .panel-icon{width:38px;height:38px;flex:0 0 38px;display:flex;align-items:center;justify-content:center;border-radius:11px;color:var(--azul-principal);background:#edf4fb}.panel-icon.security{color:var(--verde-oscuro);background:#eaf8f1}.panel-icon svg{width:18px;height:18px}
    .panel-header h3{margin:0;color:var(--azul-profundo);font-size:14px}.panel-header p{margin:3px 0 0;color:#82909a;font-size:9.5px}
    .panel-body{padding:18px}
    .info-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}
    .info-item{display:flex;align-items:center;gap:11px;min-height:67px;padding:12px;border:1px solid #e6edf2;border-radius:12px;background:#fbfcfd}
    .info-icon{width:34px;height:34px;flex:0 0 34px;display:flex;align-items:center;justify-content:center;border-radius:9px;color:#55738a;background:#eef3f6}.info-icon svg{width:16px;height:16px}
    .info-content{min-width:0}.info-content span{display:block;color:#83919c;font-size:8px;font-weight:800;text-transform:uppercase}.info-content strong{display:block;margin-top:4px;color:#23445e;font-size:11px;overflow-wrap:anywhere}
    .role-badge{display:inline-flex;align-items:center;min-height:25px;padding:0 9px;border:1px solid #d5e4f0;border-radius:999px;background:#edf4fb;color:#315f86;font-size:9px;font-weight:800}
    .password-note{margin:0 0 15px;padding:11px 12px;border:1px solid #dce8f0;border-radius:11px;background:#f6fafc;color:#667d8e;font-size:9.5px;line-height:1.5}
    .field{margin-bottom:12px}.field label{display:block;margin-bottom:7px;color:#536c7f;font-size:9px;font-weight:800;text-transform:uppercase}.required{color:#b13b3b}
    .password-wrap{position:relative}.field input{width:100%;min-height:43px;padding:0 45px 0 12px;border:1px solid #ced9e1;border-radius:10px;outline:none;background:#fff;color:#30485b;font-size:11px;box-sizing:border-box}.field input:focus{border-color:#82a9cc;box-shadow:0 0 0 3px rgba(22,76,150,.08)}
    .password-toggle{position:absolute;right:5px;top:5px;width:33px;height:33px;display:flex;align-items:center;justify-content:center;border:0;border-radius:8px;background:transparent;color:#718696;cursor:pointer}.password-toggle:hover{background:#f0f4f7;color:var(--azul-principal)}.password-toggle svg{width:16px;height:16px}
    .password-submit{width:100%;margin-top:4px}
    .security-foot{margin-top:13px;padding-top:12px;border-top:1px solid #edf1f4;color:#8a99a5;font-size:8.8px;line-height:1.5}
    @media(max-width:900px){.detail-grid{grid-template-columns:1fr}}
    @media(max-width:700px){.info-list{grid-template-columns:1fr}.profile-banner{align-items:flex-start;flex-wrap:wrap}.header-actions{width:100%;flex-direction:column}.header-actions .btn{width:100%}}
</style>
@endpush

@section('content')
@php
    $nombre = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? ''));
    $nombreMostrar = $nombre !== '' ? $nombre : $usuario->usuario;
    $iniciales = collect(preg_split('/\s+/', $nombreMostrar))
        ->filter()
        ->take(2)
        ->map(fn($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
@endphp

<div class="user-detail-page">
    <div class="page-header">
        <div class="page-title module-heading">
            <div class="heading-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4"></circle>
                    <path d="M4 21a8 8 0 0 1 16 0"></path>
                </svg>
            </div>
            <div>
                <h2>Detalle de Usuario</h2>
                <p>Información y administración de la cuenta seleccionada.</p>
            </div>
        </div>

        <div class="header-actions">
            <a href="{{ route('administrador.usuarios.index') }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"></path></svg>
                Regresar
            </a>

            <a href="{{ route('administrador.usuarios.edit', $usuario->id) }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 10-10-3.5-3.5-10 10L4 20Z"></path><path d="m13.5 7 3.5 3.5"></path></svg>
                Editar Usuario
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="errors">
            <div class="error-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v6M12 17h.01"></path></svg>
            </div>
            <div>
                <strong>No fue posible actualizar la contraseña</strong>
                <ul>
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <section class="profile-banner">
        <div class="avatar">{{ $iniciales !== '' ? $iniciales : 'U' }}</div>
        <div class="profile-main">
            <h3>{{ $nombreMostrar }}</h3>
            <p>{{ '@' . $usuario->usuario }} · {{ $usuario->rol_nombre }} · ID #{{ $usuario->id }}</p>
        </div>
        <span class="status-badge {{ $usuario->estado === 'ACTIVO' ? 'status-active' : 'status-inactive' }}">
            {{ $usuario->estado }}
        </span>
    </section>

    <div class="detail-grid">
        <section class="panel">
            <header class="panel-header">
                <div class="panel-icon">
                    <svg viewBox="0 0 24 24"><path d="M5 4h14v16H5z"></path><path d="M9 8h6M9 12h6M9 16h4"></path></svg>
                </div>
                <div>
                    <h3>Datos de la Cuenta</h3>
                    <p>Información registrada para este usuario.</p>
                </div>
            </header>

            <div class="panel-body">
                <div class="info-list">
                    <div class="info-item">
                        <div class="info-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3"></circle><path d="M5 20a7 7 0 0 1 14 0"></path></svg></div>
                        <div class="info-content"><span>Usuario</span><strong>{{ $usuario->usuario }}</strong></div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"></path></svg></div>
                        <div class="info-content"><span>Nombres</span><strong>{{ $usuario->nombres ?: '—' }}</strong></div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"></path></svg></div>
                        <div class="info-content"><span>Apellidos</span><strong>{{ $usuario->apellidos ?: '—' }}</strong></div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><svg viewBox="0 0 24 24"><path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path></svg></div>
                        <div class="info-content"><span>Rol</span><strong><span class="role-badge">{{ $usuario->rol_nombre }}</span></strong></div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m8 12 2.5 2.5L16 9"></path></svg></div>
                        <div class="info-content">
                            <span>Estado</span>
                            <strong><span class="status-badge {{ $usuario->estado === 'ACTIVO' ? 'status-active' : 'status-inactive' }}">{{ $usuario->estado }}</span></strong>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><svg viewBox="0 0 24 24"><path d="M5 4h14v16H5z"></path><path d="M9 8h6"></path></svg></div>
                        <div class="info-content"><span>ID de Usuario</span><strong>#{{ $usuario->id }}</strong></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <header class="panel-header">
                <div class="panel-icon security">
                    <svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path><circle cx="12" cy="15" r="1"></circle></svg>
                </div>
                <div>
                    <h3>Restablecer Contraseña</h3>
                    <p>Administración de las credenciales de acceso.</p>
                </div>
            </header>

            <div class="panel-body">
                <p class="password-note">
                    Defina una nueva contraseña para esta cuenta. La nueva credencial entrará en vigencia después de guardar el cambio.
                </p>

                <form method="POST" action="{{ route('administrador.usuarios.password', $usuario->id) }}">
                    @csrf
                    @method('PATCH')

                    <div class="field">
                        <label for="password">Nueva Contraseña <span class="required">*</span></label>
                        <div class="password-wrap">
                            <input id="password" type="password" name="password" placeholder="Ingrese la nueva contraseña" autocomplete="new-password" required>
                            <button type="button" class="password-toggle" data-target="password" title="Mostrar contraseña" aria-label="Mostrar contraseña">
                                <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.8"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirmar Contraseña <span class="required">*</span></label>
                        <div class="password-wrap">
                            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirme la nueva contraseña" autocomplete="new-password" required>
                            <button type="button" class="password-toggle" data-target="password_confirmation" title="Mostrar contraseña" aria-label="Mostrar contraseña">
                                <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.8"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary password-submit">
                        <svg viewBox="0 0 24 24"><path d="M5 4h14v16H5z"></path><path d="M8 4v6h8V4"></path><path d="M8 20v-6h8v6"></path></svg>
                        Actualizar Contraseña
                    </button>
                </form>

                <div class="security-foot">
                    El restablecimiento de contraseña modifica únicamente las credenciales de esta cuenta; el rol, estado y demás datos del usuario no se alteran.
                </div>
            </div>
        </section>
    </div>
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
            this.setAttribute('title', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
            this.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });
});
</script>
@endpush
