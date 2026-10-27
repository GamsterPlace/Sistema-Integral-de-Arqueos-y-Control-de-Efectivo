@extends('layouts.agente')

@section('title', 'Perfil')
@section('module-title', 'Perfil')

@push('styles')
<style>
    .profile-grid {
        display: grid;
        grid-template-columns: 330px minmax(0, 1fr);
        gap: 22px;
    }

    .profile-card,
    .content-card {
        overflow: hidden;
        border: 1px solid #e0e8ee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(20, 57, 83, .05);
    }

    .profile-card-header {
        padding: 28px 22px 24px;
        background:
            linear-gradient(
                145deg,
                #0b315f,
                #164c96
            );
        color: #ffffff;
        text-align: center;
    }

    .profile-avatar {
        width: 90px;
        height: 90px;
        display: grid;
        place-items: center;
        margin: 0 auto 15px;
        border: 4px solid rgba(255, 255, 255, .25);
        border-radius: 24px;
        background: rgba(255, 255, 255, .12);
        font-size: 30px;
        font-weight: 800;
        letter-spacing: .5px;
    }

    .profile-card-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
    }

    .profile-card-header p {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .68);
        font-size: 11px;
    }

    .profile-card-body {
        padding: 18px;
    }

    .profile-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #edf1f4;
    }

    .profile-row:last-child {
        border-bottom: 0;
    }

    .profile-row span {
        color: #748596;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .profile-row strong {
        max-width: 58%;
        color: #173f66;
        font-size: 12px;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .status-active {
        border: 1px solid #b9e1c8;
        background: #e8f7ee;
        color: #247048;
    }

    .status-inactive {
        border: 1px solid #e3aaa5;
        background: #ffe8e6;
        color: #a93b35;
    }

    .content-card + .content-card {
        margin-top: 20px;
    }

    .content-card-header {
        padding: 17px 19px;
        border-bottom: 1px solid #edf1f4;
        background: #f8fafb;
    }

    .content-card-header h3 {
        margin: 0;
        color: #0a3158;
        font-size: 16px;
    }

    .content-card-header p {
        margin: 5px 0 0;
        color: #82909a;
        font-size: 11px;
    }

    .content-card-body {
        padding: 20px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        margin-bottom: 7px;
        color: #65788a;
        font-size: 11px;
        font-weight: 800;
    }

    .form-control {
        width: 100%;
        min-height: 44px;
        padding: 0 13px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #ffffff;
        color: #2f4659;
        outline: none;
    }

    .form-control:focus {
        border-color: #2b72b8;
        box-shadow: 0 0 0 3px rgba(43, 114, 184, .12);
    }

    .form-control[readonly] {
        background: #f4f7f9;
        color: #6d7f90;
        cursor: not-allowed;
    }

    .field-help {
        display: block;
        margin-top: 6px;
        color: #86949e;
        font-size: 10px;
        line-height: 1.45;
    }

    .field-error {
        display: block;
        margin-top: 6px;
        color: #a93b35;
        font-size: 11px;
        font-weight: 700;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 17px;
        border: 0;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary {
        background: #164c96;
        color: #ffffff;
    }

    .btn-secondary {
        border: 1px solid #d6e0e7;
        background: #ffffff;
        color: #36556e;
    }

    .institutional-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .institutional-item {
        padding: 14px;
        border: 1px solid #e3e9ee;
        border-radius: 12px;
        background: #fbfcfd;
    }

    .institutional-item.full {
        grid-column: 1 / -1;
    }

    .institutional-item span {
        display: block;
        color: #748596;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .4px;
        text-transform: uppercase;
    }

    .institutional-item strong {
        display: block;
        margin-top: 6px;
        color: #173f66;
        font-size: 12px;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .security-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 15px 16px;
        border: 1px solid #dce6ed;
        border-radius: 12px;
        background: #f8fafb;
    }

    .security-box strong {
        display: block;
        color: #173f66;
        font-size: 12px;
    }

    .security-box p {
        margin: 5px 0 0;
        color: #7b8b97;
        font-size: 10px;
        line-height: 1.45;
    }

    @media (max-width: 980px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .form-grid,
        .institutional-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full,
        .institutional-item.full {
            grid-column: auto;
        }

        .form-actions,
        .security-box {
            align-items: stretch;
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

@php
    $nombreCompleto = trim(
        ($datosPersonales?->nombres ?? '')
        . ' '
        . ($datosPersonales?->apellidos ?? '')
    );

    $iniciales = collect(
        preg_split('/\s+/', $nombreCompleto)
    )
        ->filter()
        ->take(2)
        ->map(
            fn ($parte) => mb_strtoupper(
                mb_substr($parte, 0, 1)
            )
        )
        ->implode('');

    $estadoActivo = $agente->estado === true
        || $agente->estado === 1
        || $agente->estado === '1'
        || $agente->estado === 'ACTIVO';

    $estadoTexto = $estadoActivo
        ? 'ACTIVO'
        : 'INACTIVO';
@endphp

<div class="page-header">
    <div class="page-title">
        <h2>Perfil del Agente</h2>

        <p>
            Consulte su información institucional y administre
            los datos permitidos de su cuenta.
        </p>
    </div>

    <div class="page-badge">
        <span class="page-badge-dot"></span>
        {{ $agente->codigo_agente }}
    </div>
</div>

@if (session('success'))
    <div class="alert-message success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div
        class="alert-message warning"
        style="border-color:#efc1c1;background:#fff3f3;color:#9a302a;"
    >
        {{ $errors->first() }}
    </div>
@endif

<div class="profile-grid">
    <aside>
        <section class="profile-card">
            <div class="profile-card-header">
                <div class="profile-avatar">
                    {{ $iniciales !== '' ? $iniciales : 'AG' }}
                </div>

                <h3>
                    {{ $nombreCompleto !== ''
                        ? $nombreCompleto
                        : $usuario->usuario }}
                </h3>

                <p>
                    Agente MICOOPE · {{ $agente->codigo_agente }}
                </p>
            </div>

            <div class="profile-card-body">
                <div class="profile-row">
                    <span>Usuario</span>

                    <strong>
                        {{ $usuario->usuario }}
                    </strong>
                </div>

                <div class="profile-row">
                    <span>Negocio</span>

                    <strong>
                        {{ $agente->nombre_negocio }}
                    </strong>
                </div>

                <div class="profile-row">
                    <span>Ruta</span>

                    <strong>
                        {{ $ruta?->nombre ?? 'Sin ruta' }}
                    </strong>
                </div>

                <div class="profile-row">
                    <span>Región</span>

                    <strong>
                        {{ $region?->nombre ?? 'Sin región' }}
                    </strong>
                </div>

                <div class="profile-row">
                    <span>Estado</span>

                    <strong>
                        <span
                            class="status-badge {{
                                $estadoActivo
                                    ? 'status-active'
                                    : 'status-inactive'
                            }}"
                        >
                            {{ $estadoTexto }}
                        </span>
                    </strong>
                </div>
            </div>
        </section>
    </aside>

    <main>
        <section class="content-card">
            <header class="content-card-header">
                <h3>Información personal</h3>

                <p>
                    Estos datos corresponden a la cuenta de acceso
                    asociada al agente.
                </p>
            </header>

            <div class="content-card-body">
                <form
                    method="POST"
                    action="{{ route('agente.perfil.actualizar') }}"
                >
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombres">
                                Nombres
                            </label>

                            <input
                                type="text"
                                id="nombres"
                                name="nombres"
                                class="form-control"
                                value="{{ old(
                                    'nombres',
                                    $datosPersonales?->nombres
                                ) }}"
                                maxlength="120"
                                autocomplete="given-name"
                                required
                            >

                            @error('nombres')
                                <span class="field-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="apellidos">
                                Apellidos
                            </label>

                            <input
                                type="text"
                                id="apellidos"
                                name="apellidos"
                                class="form-control"
                                value="{{ old(
                                    'apellidos',
                                    $datosPersonales?->apellidos
                                ) }}"
                                maxlength="120"
                                autocomplete="family-name"
                                required
                            >

                            @error('apellidos')
                                <span class="field-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="form-group full">
                            <label for="nombre_usuario">
                                Nombre de usuario
                            </label>

                            <input
                                type="text"
                                id="nombre_usuario"
                                name="nombre_usuario"
                                class="form-control"
                                value="{{ old(
                                    'nombre_usuario',
                                    $usuario->usuario
                                ) }}"
                                maxlength="80"
                                autocomplete="username"
                                required
                            >

                            <span class="field-help">
                                Este es el usuario utilizado para ingresar
                                al sistema.
                            </span>

                            @error('nombre_usuario')
                                <span class="field-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="content-card">
            <header class="content-card-header">
                <h3>Información institucional</h3>

                <p>
                    Estos datos provienen del registro oficial del agente
                    y no pueden modificarse desde el perfil.
                </p>
            </header>

            <div class="content-card-body">
                <div class="institutional-grid">
                    <div class="institutional-item">
                        <span>Código del agente</span>

                        <strong>
                            {{ $agente->codigo_agente }}
                        </strong>
                    </div>

                    <div class="institutional-item">
                        <span>Nombre del negocio</span>

                        <strong>
                            {{ $agente->nombre_negocio }}
                        </strong>
                    </div>

                    <div class="institutional-item">
                        <span>Propietario / receptor</span>

                        <strong>
                            {{ $agente->propietario ?: 'No registrado' }}
                        </strong>
                    </div>

                    <div class="institutional-item">
                        <span>Ruta</span>

                        <strong>
                            @if ($ruta)
                                {{ $ruta->codigo ?? '' }}

                                @if (
                                    ! empty($ruta->codigo)
                                    && ! empty($ruta->nombre)
                                )
                                    —
                                @endif

                                {{ $ruta->nombre ?? 'Sin ruta asignada' }}
                            @else
                                Sin ruta asignada
                            @endif
                        </strong>
                    </div>

                    <div class="institutional-item">
                        <span>Región</span>

                        <strong>
                            {{ $region?->nombre ?? 'Sin región asignada' }}
                        </strong>
                    </div>

                    <div class="institutional-item full">
                        <span>Dirección</span>

                        <strong>
                            {{ $agente->direccion ?: 'No registrada' }}
                        </strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="content-card">
            <header class="content-card-header">
                <h3>Seguridad de la cuenta</h3>
            </header>

            <div class="content-card-body">
                <div class="security-box">
                    <div>
                        <strong>
                            Cambio de contraseña
                        </strong>

                        <p>
                            Actualice periódicamente su contraseña
                            institucional para proteger el acceso al sistema.
                        </p>
                    </div>

                    <a
                        href="{{ route('password.cambiar') }}"
                        class="btn btn-secondary"
                    >
                        Cambiar contraseña
                    </a>
                </div>
            </div>
        </section>
    </main>
</div>

@endsection
