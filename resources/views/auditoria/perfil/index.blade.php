@extends('layouts.auditoria')

@section('title', 'Perfil')
@section('module-title', 'Perfil')

@push('styles')
<style>
    .profile-layout{
        display:grid;
        grid-template-columns:330px minmax(0,1fr);
        gap:20px;
    }

    .card{
        overflow:hidden;
        border:1px solid #e0e8ee;
        border-radius:18px;
        background:#fff;
        box-shadow:0 8px 24px rgba(20,57,83,.05);
    }

    .identity-header{
        padding:30px 22px 26px;
        background:linear-gradient(145deg,#0b315f,#164c96);
        color:#fff;
        text-align:center;
    }

    .identity-avatar{
        display:grid;
        place-items:center;
        width:88px;
        height:88px;
        margin:0 auto 15px;
        border:4px solid rgba(255,255,255,.22);
        border-radius:24px;
        background:rgba(255,255,255,.13);
        font-size:27px;
        font-weight:800;
    }

    .identity-header h3{
        margin:0;
        font-size:18px;
    }

    .identity-header p{
        margin:7px 0 0;
        color:rgba(255,255,255,.72);
        font-size:10px;
    }

    .identity-body{
        padding:18px;
    }

    .identity-row{
        display:flex;
        justify-content:space-between;
        gap:15px;
        padding:12px 0;
        border-bottom:1px solid #edf1f4;
    }

    .identity-row:last-child{
        border-bottom:0;
    }

    .identity-row > span{
        color:#748596;
        font-size:9px;
        font-weight:800;
        text-transform:uppercase;
    }

    .identity-row > strong{
        color:#173b59;
        font-size:11px;
        text-align:right;
    }

    .badge{
        display:inline-flex;
        padding:6px 9px;
        border-radius:999px;
        background:#eaf8ef;
        color:#1d7b4e;
        font-size:8px;
        font-weight:800;
        text-transform:uppercase;
    }

    .badge.inactive{
        background:#fdecec;
        color:#b13c3c;
    }

    .content-stack{
        display:grid;
        gap:20px;
    }

    .card-header{
        padding:18px 20px;
        border-bottom:1px solid #edf1f4;
        background:#fafcfd;
    }

    .card-header h3{
        margin:0;
        color:#0a3158;
        font-size:15px;
    }

    .card-header p{
        margin:5px 0 0;
        color:#82909a;
        font-size:10px;
    }

    .card-body{
        padding:20px;
    }

    .info-grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:14px;
    }

    .info-item{
        padding:15px;
        border:1px solid #e2e9ee;
        border-radius:12px;
        background:#fbfcfd;
    }

    .info-item span{
        display:block;
        color:#758697;
        font-size:8px;
        font-weight:800;
        text-transform:uppercase;
    }

    .info-item strong{
        display:block;
        margin-top:7px;
        color:#173b59;
        font-size:12px;
    }

    .stats-grid{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:12px;
    }

    .stat-box{
        position:relative;
        overflow:hidden;
        padding:15px;
        border:1px solid #e1e8ed;
        border-radius:12px;
        background:#fbfcfd;
    }

    .stat-box:before{
        content:"";
        position:absolute;
        top:0;
        left:0;
        width:100%;
        height:3px;
        background:#164c96;
    }

    .stat-box.faltante:before{
        background:#b33a34;
    }

    .stat-box.sobrante:before{
        background:#16834f;
    }

    .stat-box.certificado:before{
        background:#16834f;
    }

    .stat-box.pendiente:before{
        background:#c99518;
    }

    .stat-box span{
        display:block;
        color:#748596;
        font-size:8px;
        font-weight:800;
        text-transform:uppercase;
    }

    .stat-box strong{
        display:block;
        margin-top:7px;
        color:#082d55;
        font-size:22px;
    }

    .security-box{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:18px;
        padding:16px;
        border:1px solid #dce7ef;
        border-radius:13px;
        background:#f7fbff;
    }

    .security-box h4{
        margin:0;
        color:#173b59;
        font-size:12px;
    }

    .security-box p{
        margin:5px 0 0;
        color:#708493;
        font-size:9px;
        line-height:1.5;
    }

    .btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:40px;
        padding:0 15px;
        border:0;
        border-radius:10px;
        background:#164c96;
        color:#fff;
        font-size:10px;
        font-weight:800;
        text-decoration:none;
        white-space:nowrap;
    }

    @media(max-width:950px){
        .profile-layout{
            grid-template-columns:1fr;
        }
    }

    @media(max-width:700px){
        .info-grid,
        .stats-grid{
            grid-template-columns:1fr;
        }

        .security-box{
            align-items:stretch;
            flex-direction:column;
        }

        .btn{
            width:100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $nombreCompleto = trim(
        ($perfil->nombres ?? '')
        . ' '
        . ($perfil->apellidos ?? '')
    );

    $nombreMostrar = $nombreCompleto !== ''
        ? $nombreCompleto
        : $perfil->usuario;

    $iniciales = collect(
        preg_split('/\s+/', $nombreMostrar)
    )
        ->filter()
        ->take(2)
        ->map(
            fn ($parte) => mb_strtoupper(
                mb_substr($parte, 0, 1)
            )
        )
        ->implode('');

    $estadoActivo = is_bool($perfil->estado ?? null)
        ? (bool) $perfil->estado
        : strtoupper((string) ($perfil->estado ?? '')) === 'ACTIVO';
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Perfil de Auditoría</h2>
        <p>
            Consulte la información de su cuenta
            y el resumen de su actividad de Auditoría.
        </p>
    </div>
</div>

<div class="profile-layout">
    <aside>
        <section class="card">
            <div class="identity-header">
                <div class="identity-avatar">
                    {{ $iniciales !== '' ? $iniciales : 'AU' }}
                </div>

                <h3>{{ $nombreMostrar }}</h3>

                <p>Auditoría MICOOPE</p>
            </div>

            <div class="identity-body">
                <div class="identity-row">
                    <span>Usuario</span>
                    <strong>{{ $perfil->usuario }}</strong>
                </div>

                <div class="identity-row">
                    <span>Rol</span>
                    <strong>Auditoría</strong>
                </div>

                <div class="identity-row">
                    <span>Estado</span>
                    <strong>
                        <span class="badge {{ $estadoActivo ? '' : 'inactive' }}">
                            {{ $estadoActivo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </strong>
                </div>
            </div>
        </section>
    </aside>

    <main class="content-stack">
        <section class="card">
            <header class="card-header">
                <h3>Información de la cuenta</h3>
                <p>
                    Datos asociados al usuario
                    autenticado actualmente.
                </p>
            </header>

            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span>Nombres</span>
                        <strong>
                            {{ ($perfil->nombres ?? '') !== ''
                                ? $perfil->nombres
                                : 'No registrado' }}
                        </strong>
                    </div>

                    <div class="info-item">
                        <span>Apellidos</span>
                        <strong>
                            {{ ($perfil->apellidos ?? '') !== ''
                                ? $perfil->apellidos
                                : 'No registrado' }}
                        </strong>
                    </div>

                    <div class="info-item">
                        <span>Nombre de usuario</span>
                        <strong>{{ $perfil->usuario }}</strong>
                    </div>

                    <div class="info-item">
                        <span>Rol institucional</span>
                        <strong>Auditoría</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <header class="card-header">
                <h3>Resumen de Auditoría</h3>
                <p>
                    Indicadores correspondientes a los arqueos
                    realizados por su usuario.
                </p>
            </header>

            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-box">
                        <span>Total Auditorías</span>
                        <strong>{{ $resumen->total ?? 0 }}</strong>
                    </div>

                    <div class="stat-box">
                        <span>Auditorías Hoy</span>
                        <strong>{{ $resumen->hoy ?? 0 }}</strong>
                    </div>

                    <div class="stat-box certificado">
                        <span>Certificados</span>
                        <strong>{{ $resumen->certificados ?? 0 }}</strong>
                    </div>

                    <div class="stat-box pendiente">
                        <span>Pendientes</span>
                        <strong>{{ $resumen->pendientes ?? 0 }}</strong>
                    </div>

                    <div class="stat-box faltante">
                        <span>Faltantes</span>
                        <strong>{{ $resumen->faltantes ?? 0 }}</strong>
                    </div>

                    <div class="stat-box sobrante">
                        <span>Sobrantes</span>
                        <strong>{{ $resumen->sobrantes ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <header class="card-header">
                <h3>Seguridad de la cuenta</h3>
            </header>

            <div class="card-body">
                <div class="security-box">
                    <div>
                        <h4>Contraseña institucional</h4>
                        <p>
                            Utilice el módulo de cambio de contraseña
                            para actualizar sus credenciales de acceso.
                        </p>
                    </div>

                    <a
                        href="{{ route('password.cambiar') }}"
                        class="btn"
                    >
                        Cambiar contraseña
                    </a>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
