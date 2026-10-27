@extends('layouts.promotor')

@section('title', 'Perfil')
@section('module-title', 'Perfil')

@section('content')
<style>
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .page-title h2 {
        margin: 0;
        color: #123d69;
        font-size: 28px;
        font-weight: 800;
    }

    .page-title p {
        margin: 7px 0 0;
        color: #6b7c8d;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 20px;
    }

    .card {
        overflow: hidden;
        border: 1px solid #dfe7ed;
        border-radius: 16px;
        background: #ffffff;
    }

    .profile-card {
        text-align: center;
    }

    .profile-header {
        padding: 28px 20px 22px;
        background:
            linear-gradient(
                145deg,
                #0b315f,
                #164c96
            );
        color: #ffffff;
    }

    .profile-avatar {
        width: 90px;
        height: 90px;
        display: grid;
        place-items: center;
        margin: 0 auto 16px;
        border: 4px solid rgba(255, 255, 255, .3);
        border-radius: 24px;
        background: rgba(255, 255, 255, .14);
        font-size: 30px;
        font-weight: 800;
    }

    .profile-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
    }

    .profile-header p {
        margin: 7px 0 0;
        color: rgba(255, 255, 255, .72);
        font-size: 13px;
    }

    .profile-details {
        padding: 18px;
        text-align: left;
    }

    .detail-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 0;
        border-bottom: 1px solid #edf1f4;
    }

    .detail-row:last-child {
        border-bottom: 0;
    }

    .detail-row span {
        color: #718394;
        font-size: 12px;
        font-weight: 800;
    }

    .detail-row strong {
        color: #173f66;
        text-align: right;
    }

    .content-card + .content-card {
        margin-top: 20px;
    }

    .card-header {
        padding: 16px 18px;
        border-bottom: 1px solid #e7edf1;
        background: #f8fafb;
    }

    .card-header h3 {
        margin: 0;
        color: #173f66;
        font-size: 16px;
        font-weight: 800;
    }

    .card-body {
        padding: 18px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        margin-bottom: 7px;
        color: #65788a;
        font-size: 12px;
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
    }

    .field-error {
        display: block;
        margin-top: 6px;
        color: #a93b35;
        font-size: 12px;
        font-weight: 700;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 18px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 17px;
        border: 0;
        border-radius: 10px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary {
        background: #174f8a;
        color: #ffffff;
    }

    .btn-secondary {
        background: #eef3f7;
        color: #38556d;
    }

    .routes-list {
        display: grid;
        gap: 10px;
    }

    .route-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 13px 14px;
        border: 1px solid #e2e9ee;
        border-radius: 11px;
        background: #fbfcfd;
    }

    .route-item strong {
        color: #173f66;
    }

    .route-item span {
        color: #718394;
        font-size: 12px;
        font-weight: 800;
    }

    .empty-state {
        padding: 25px;
        color: #758697;
        text-align: center;
    }

    @media (max-width: 950px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: auto;
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>

<div class="page-header">
    <div class="page-title">
        <h2>Mi Perfil</h2>

        <p>
            Consulte su información personal y actualice los datos
            permitidos de su cuenta.
        </p>
    </div>
</div>

<div class="profile-grid">
    <aside>
        <div class="card profile-card">
            <div class="profile-header">
                @php
                    $nombreCompleto = trim(
                        ($datosPersonales?->nombres ?? '')
                        . ' '
                        . ($datosPersonales?->apellidos ?? '')
                    );

                    $iniciales = collect(
                        preg_split(
                            '/\s+/',
                            $nombreCompleto
                        )
                    )
                        ->filter()
                        ->take(2)
                        ->map(
                            fn ($parte) => mb_strtoupper(
                                mb_substr($parte, 0, 1)
                            )
                        )
                        ->implode('');
                @endphp

                <div class="profile-avatar">
                    {{ $iniciales !== '' ? $iniciales : 'PR' }}
                </div>

                <h3>
                    {{ $nombreCompleto !== ''
                        ? $nombreCompleto
                        : $usuario->nombre_usuario }}
                </h3>

                <p>Promotor de Agentes MICOOPE</p>
            </div>

            <div class="profile-details">
                <div class="detail-row">
                    <span>Usuario</span>
                    <strong>{{ $usuario->nombre_usuario }}</strong>
                </div>

                <div class="detail-row">
                    <span>Rol</span>
                    <strong>{{ $usuario->rol?->nombre }}</strong>
                </div>

                <div class="detail-row">
                    <span>Rutas asignadas</span>
                    <strong>{{ $rutasAsignadas->count() }}</strong>
                </div>

                <div class="detail-row">
                    <span>Agentes activos</span>
                    <strong>{{ $totalAgentes }}</strong>
                </div>
            </div>
        </div>
    </aside>

    <section>
        <div class="card content-card">
            <div class="card-header">
                <h3>Información personal</h3>
            </div>

            <div class="card-body">
                <form
                    method="POST"
                    action="{{ route('promotor.perfil.actualizar') }}"
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
                                    $usuario->nombre_usuario
                                ) }}"
                                required
                            >

                            @error('nombre_usuario')
                                <span class="field-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Rol institucional</label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{ $usuario->rol?->nombre }}"
                                readonly
                            >
                        </div>

                        <div class="form-group">
                            <label>Estado de la cuenta</label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{ $usuario->estado
                                    ? 'Activa'
                                    : 'Inactiva' }}"
                                readonly
                            >
                        </div>
                    </div>

                    <div class="form-actions">
                        <a
                            href="{{ route('password.cambiar') }}"
                            class="btn btn-secondary"
                        >
                            Cambiar contraseña
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h3>Rutas asignadas actualmente</h3>
            </div>

            <div class="card-body">
                <div class="routes-list">
                    @forelse ($rutasAsignadas as $ruta)
                        <div class="route-item">
                            <strong>{{ $ruta->nombre }}</strong>
                            <span>{{ $ruta->codigo }}</span>
                        </div>
                    @empty
                        <div class="empty-state">
                            No tiene rutas asignadas actualmente.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
