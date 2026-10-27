@extends('layouts.administrador')

@section('title', 'Editar Agente')
@section('module-title', 'Agentes')

@push('styles')
<style>
    .create-agent-page {
        max-width: 1180px;
        margin: 0 auto;
    }

    .create-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 22px;
        margin-bottom: 22px;
    }

    .create-title {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .create-title-icon {
        flex: 0 0 auto;
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #fff;
        box-shadow: 0 9px 20px rgba(22,76,150,.17);
    }

    .create-title-icon svg {
        width: 23px;
        height: 23px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .create-title h2 {
        margin: 0;
        color: #06284f;
        font-size: 27px;
        letter-spacing: -.6px;
    }

    .create-title p {
        max-width: 700px;
        margin: 7px 0 0;
        color: #718391;
        font-size: 12px;
        line-height: 1.55;
    }

    .back-link {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 0 0 auto;
        padding: 0 14px;
        border: 1px solid #d6e0e7;
        border-radius: 11px;
        background: #fff;
        color: #526b7d;
        font-size: 10px;
        font-weight: 800;
        transition: .2s ease;
    }

    .back-link:hover {
        transform: translateY(-1px);
        border-color: #a9bdcc;
        color: #164c96;
        box-shadow: 0 7px 16px rgba(20,57,83,.07);
    }

    .back-link svg {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .form-errors {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 18px;
        padding: 14px 16px;
        border: 1px solid #efcaca;
        border-radius: 13px;
        background: #fff6f6;
        color: #9d3838;
    }

    .form-errors-icon {
        flex: 0 0 auto;
        width: 31px;
        height: 31px;
        display: grid;
        place-items: center;
        border-radius: 9px;
        background: #fde8e8;
    }

    .form-errors-icon svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .form-errors strong {
        display: block;
        margin-bottom: 5px;
        font-size: 11px;
    }

    .form-errors ul {
        margin: 0;
        padding-left: 17px;
        font-size: 10px;
        line-height: 1.6;
    }

    .form-shell {
        overflow: hidden;
        border: 1px solid #dce5eb;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 14px 36px rgba(20,57,83,.055);
    }

    .form-section {
        padding: 23px 25px 26px;
    }

    .form-section + .form-section {
        border-top: 1px solid #e6edf1;
    }

    .section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .section-number {
        flex: 0 0 auto;
        width: 37px;
        height: 37px;
        display: grid;
        place-items: center;
        border-radius: 11px;
        background: #edf4fb;
        color: #164c96;
        font-size: 12px;
        font-weight: 900;
    }

    .form-section:nth-child(2) .section-number {
        background: #eff9f3;
        color: #008640;
    }

    .section-heading h3 {
        margin: 0;
        color: #0b315f;
        font-size: 15px;
        font-weight: 850;
    }

    .section-heading p {
        margin: 4px 0 0;
        color: #84929d;
        font-size: 10px;
        line-height: 1.45;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 18px;
    }

    .form-field {
        min-width: 0;
    }

    .form-field.full {
        grid-column: 1 / -1;
    }

    .form-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 7px;
        color: #526a7c;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .55px;
        text-transform: uppercase;
    }

    .required-mark {
        color: #b34a45;
        font-size: 11px;
    }

    .form-control {
        width: 100%;
        min-height: 45px;
        padding: 0 13px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        outline: none;
        background: #fff;
        color: #2f485b;
        font-size: 12px;
        transition: .18s ease;
    }

    textarea.form-control {
        min-height: 94px;
        padding-top: 12px;
        padding-bottom: 12px;
        resize: vertical;
        line-height: 1.5;
    }

    select.form-control {
        cursor: pointer;
    }

    .form-control:hover {
        border-color: #b8c8d3;
    }

    .form-control:focus {
        border-color: #5d8fc6;
        box-shadow: 0 0 0 3px rgba(22,76,150,.09);
    }

    .form-control::placeholder {
        color: #a1adb6;
    }

    .field-help {
        display: block;
        margin-top: 6px;
        color: #8b99a4;
        font-size: 9px;
        line-height: 1.45;
    }

    .password-wrapper {
        position: relative;
    }

    .password-wrapper .form-control {
        padding-right: 45px;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 8px;
        width: 32px;
        height: 32px;
        display: grid;
        place-items: center;
        transform: translateY(-50%);
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #758896;
        cursor: pointer;
    }

    .password-toggle:hover {
        background: #eef4f8;
        color: #164c96;
    }

    .password-toggle svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 18px 25px;
        border-top: 1px solid #e5ecf0;
        background: #fafcfd;
    }

    .form-footer-note {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #788b99;
        font-size: 9px;
        line-height: 1.4;
    }

    .form-footer-note svg {
        flex: 0 0 auto;
        width: 16px;
        height: 16px;
        color: #00a651;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .form-button {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 17px;
        border: 1px solid transparent;
        border-radius: 11px;
        font-size: 10px;
        font-weight: 850;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .form-button svg {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .form-button.secondary {
        border-color: #d5e0e7;
        background: #fff;
        color: #526a7c;
    }

    .form-button.secondary:hover {
        border-color: #afc1cd;
        background: #f5f8fa;
    }

    .form-button.primary {
        border-color: #164c96;
        background: linear-gradient(135deg, #164c96, #1c64b5);
        color: #fff;
        box-shadow: 0 8px 18px rgba(22,76,150,.17);
    }

    .form-button.primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 11px 22px rgba(22,76,150,.22);
    }


    .agent-context {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 10px;
        padding: 7px 10px;
        border: 1px solid #d9e5ed;
        border-radius: 999px;
        background: #f8fbfd;
        color: #587083;
        font-size: 9px;
        font-weight: 800;
    }

    .agent-context-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #00a651;
        box-shadow: 0 0 0 3px rgba(0,166,81,.10);
    }

    @media (max-width: 760px) {
        .create-header {
            flex-direction: column;
        }

        .back-link {
            width: 100%;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-field.full {
            grid-column: auto;
        }

        .form-section {
            padding: 20px 17px 22px;
        }

        .form-footer {
            align-items: stretch;
            flex-direction: column;
            padding: 17px;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .form-button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="create-agent-page">
    <div class="create-header">
        <div class="create-title">
            <div class="create-title-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M3 21h18"/>
                    <path d="M5 21V7l7-4 7 4v14"/>
                    <path d="M9 21v-6h6v6"/>
                    <path d="M19 4v6"/>
                    <path d="M22 7h-6"/>
                </svg>
            </div>

            <div>
                <h2>Editar Agente</h2>
                <p>
                    Actualice la información comercial, la cuenta de acceso y la ruta
                    asignada al Agente MICOOPE.
                </p>

                <div class="agent-context">
                    <span class="agent-context-dot"></span>
                    {{ $agente->codigo_agente }} · {{ $agente->nombre_negocio }}
                </div>
            </div>
        </div>

        <a
            href="{{ route('administrador.agentes.show', $agente->id) }}"
            class="back-link"
        >
            <svg viewBox="0 0 24 24">
                <path d="M19 12H5"/>
                <path d="m11 18-6-6 6-6"/>
            </svg>
            Regresar
        </a>
    </div>

    @if ($errors->any())
        <div class="form-errors">
            <div class="form-errors-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v6"/>
                    <path d="M12 17h.01"/>
                </svg>
            </div>

            <div>
                <strong>Revise la información ingresada</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('administrador.agentes.update', $agente->id) }}"
        class="form-shell"
    >
        @csrf
        @method('PUT')

        <section class="form-section">
            <div class="section-heading">
                <div class="section-number">01</div>
                <div>
                    <h3>Información del Agente</h3>
                    <p>Datos de identificación, negocio y ubicación operativa.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label class="form-label" for="codigo_agente">
                        Código de Agente
                        <span class="required-mark">*</span>
                    </label>
                    <input
                        id="codigo_agente"
                        type="text"
                        name="codigo_agente"
                        value="{{ old('codigo_agente', $agente->codigo_agente) }}"
                        class="form-control"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="form-field">
                    <label class="form-label" for="nombre_negocio">
                        Nombre del Negocio
                        <span class="required-mark">*</span>
                    </label>
                    <input
                        id="nombre_negocio"
                        type="text"
                        name="nombre_negocio"
                        value="{{ old('nombre_negocio', $agente->nombre_negocio) }}"
                        class="form-control"
                        required
                    >
                </div>

                <div class="form-field">
                    <label class="form-label" for="nombre_propietario">
                        Nombre del Propietario
                        <span class="required-mark">*</span>
                    </label>
                    <input
                        id="nombre_propietario"
                        type="text"
                        name="nombre_propietario"
                        value="{{ old('nombre_propietario', $agente->nombre_propietario) }}"
                        class="form-control"
                        required
                    >
                </div>

                <div class="form-field">
                    <label class="form-label" for="ruta_id">
                        Ruta
                        <span class="required-mark">*</span>
                    </label>
                    <select
                        id="ruta_id"
                        name="ruta_id"
                        class="form-control"
                        required
                    >
                        <option value="">Seleccione una ruta</option>

                        @foreach ($rutas as $ruta)
                            <option
                                value="{{ $ruta->id }}"
                                @selected((int) old('ruta_id', $agente->ruta_id) === (int) $ruta->id)
                            >
                                {{ $ruta->region_nombre }} — {{ $ruta->codigo }} — {{ $ruta->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <span class="field-help">
                        La región se determina automáticamente según la ruta seleccionada.
                    </span>
                </div>

                <div class="form-field full">
                    <label class="form-label" for="direccion">
                        Dirección
                        <span class="required-mark">*</span>
                    </label>
                    <textarea
                        id="direccion"
                        name="direccion"
                        class="form-control"
                        required
                    >{{ old('direccion', $agente->direccion) }}</textarea>
                </div>
            </div>
        </section>

        <section class="form-section">
            <div class="section-heading">
                <div class="section-number">02</div>
                <div>
                    <h3>Cuenta de Acceso</h3>
                    <p>Información personal y usuario institucional vinculados al agente.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label class="form-label" for="nombres">
                        Nombres
                        <span class="required-mark">*</span>
                    </label>
                    <input
                        id="nombres"
                        type="text"
                        name="nombres"
                        value="{{ old('nombres', $agente->nombres) }}"
                        class="form-control"
                        required
                    >
                </div>

                <div class="form-field">
                    <label class="form-label" for="apellidos">
                        Apellidos
                        <span class="required-mark">*</span>
                    </label>
                    <input
                        id="apellidos"
                        type="text"
                        name="apellidos"
                        value="{{ old('apellidos', $agente->apellidos) }}"
                        class="form-control"
                        required
                    >
                </div>

                <div class="form-field full">
                    <label class="form-label" for="usuario">
                        Usuario
                        <span class="required-mark">*</span>
                    </label>
                    <input
                        id="usuario"
                        type="text"
                        name="usuario"
                        value="{{ old('usuario', $agente->usuario) }}"
                        class="form-control"
                        autocomplete="username"
                        required
                    >
                    <span class="field-help">
                        Usuario institucional utilizado por el agente para iniciar sesión.
                    </span>
                </div>

                </div>
            </div>
        </section>

        <footer class="form-footer">
            <div class="form-footer-note">
                <svg viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
                Los cambios actualizarán la información vigente del agente.
            </div>

            <div class="form-actions">
                <a
                    href="{{ route('administrador.agentes.show', $agente->id) }}"
                    class="form-button secondary"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="form-button primary"
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                    Guardar Cambios
                </button>
            </div>
        </footer>
    </form>
</div>
@endsection
