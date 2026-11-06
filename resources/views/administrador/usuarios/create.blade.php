
@extends('layouts.administrador')

@section('title', 'Crear Usuario')
@section('module-title', 'Usuarios')

@push('styles')
<style>
    .user-form-page {
        max-width: 1120px;
        margin: 0 auto;
    }

    .user-form-page * {
        box-sizing: border-box;
    }

    .user-heading {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 24px;
    }

    .user-heading-icon {
        width: 54px;
        height: 54px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: linear-gradient(
            145deg,
            var(--azul-principal),
            var(--azul-profundo)
        );
        color: #fff;
    }

    .user-heading-icon svg {
        width: 27px;
        height: 27px;
    }

    .user-heading h2 {
        margin: 0 0 5px;
        font-size: 24px;
        color: var(--azul-profundo);
    }

    .user-heading p {
        margin: 0;
        color: #718396;
        font-size: 12px;
    }

    .user-form-card {
        background: #fff;
        border: 1px solid #dfe7ee;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(20,57,83,.05);
    }

    .user-form-intro {
        padding: 20px 24px;
        background: linear-gradient(
            105deg,
            rgba(22,76,150,.06),
            rgba(0,166,81,.03)
        );
        border-bottom: 1px solid #e5edf2;
        border-left: 5px solid var(--verde-principal);
    }

    .user-form-intro strong {
        display: block;
        font-size: 15px;
        color: var(--azul-profundo);
    }

    .user-form-intro p {
        margin: 5px 0 0;
        color: #718396;
        font-size: 11px;
    }

    .user-section {
        padding: 23px 24px;
        border-bottom: 1px solid #edf1f4;
    }

    .user-section-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 19px;
    }

    .user-section-icon {
        width: 39px;
        height: 39px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #edf4fb;
        color: var(--azul-principal);
    }

    .user-section-icon svg {
        width: 19px;
        height: 19px;
    }

    .user-section-title h3 {
        margin: 0;
        font-size: 15px;
        color: var(--azul-profundo);
    }

    .user-section-title p {
        margin: 4px 0 0;
        font-size: 11px;
        color: #718396;
    }

    .user-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px;
    }

    .user-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .025em;
        color: #536c7f;
    }

    .user-field input,
    .user-field select,
    .user-field textarea {
        width: 100%;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid #ced9e1;
        border-radius: 10px;
        background: #fff;
        color: #30485b;
        font: inherit;
        font-size: 12px;
        outline: none;
        transition: .18s;
    }

    .user-field textarea {
        min-height: 86px;
        resize: vertical;
    }

    .user-field input:focus,
    .user-field select:focus,
    .user-field textarea:focus {
        border-color: #82a9cc;
        box-shadow: 0 0 0 3px rgba(22,76,150,.08);
    }

    .user-field small {
        display: block;
        margin-top: 6px;
        color: #8192a0;
        font-size: 10px;
        line-height: 1.5;
    }

    .user-required {
        color: #b13b3b;
    }

    .user-full {
        grid-column: 1 / -1;
    }

    .user-module-info {
        margin-top: 12px;
        padding: 12px 15px;
        border-radius: 10px;
        background: #edf4fb;
        color: #345a7b;
        font-size: 11px;
        line-height: 1.6;
    }

    .user-agencia-section {
        background: #fcfefd;
    }

    .user-agencia-notice {
        padding: 12px 15px;
        margin-bottom: 20px;
        border: 1px solid #cfe8d9;
        border-radius: 11px;
        background: #f0faf4;
        color: #35694d;
        font-size: 11px;
        line-height: 1.6;
    }

    .user-password-wrap {
        position: relative;
    }

    .user-password-wrap input {
        padding-right: 55px;
    }

    .user-password-toggle {
        position: absolute;
        right: 6px;
        top: 6px;
        min-height: 32px;
        padding: 0 10px;
        border: 0;
        border-radius: 7px;
        background: #edf4fb;
        color: #315e88;
        cursor: pointer;
        font-size: 10px;
        font-weight: 700;
    }

    .user-errors {
        padding: 15px 18px;
        margin-bottom: 20px;
        background: #fff6f6;
        border: 1px solid #efc5c5;
        border-radius: 12px;
        color: #a13b3b;
        font-size: 12px;
    }

    .user-errors ul {
        margin: 8px 0 0;
        padding-left: 19px;
    }

    .user-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 20px 24px;
        background: #fbfcfd;
    }

    .user-footer-note {
        font-size: 10px;
        color: #8192a0;
    }

    .user-actions {
        display: flex;
        gap: 10px;
    }

    .user-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 19px;
        border: 0;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .user-btn-secondary {
        background: #edf2f5;
        color: #3d596f;
    }

    .user-btn-primary {
        background: var(--azul-principal);
        color: #fff;
    }

    .user-btn-primary:hover {
        background: var(--azul-secundario);
    }

    [hidden] {
        display: none !important;
    }

    @media(max-width: 700px) {
        .user-grid {
            grid-template-columns: 1fr;
        }

        .user-full {
            grid-column: auto;
        }

        .user-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .user-actions {
            flex-direction: column;
        }

        .user-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="user-form-page">

    <div class="user-heading">
        <div class="user-heading-icon">
            <svg viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 stroke-linecap="round"
                 stroke-linejoin="round">
                <circle cx="9" cy="8" r="3"/>
                <path d="M3 20v-2a6 6 0 0 1 12 0v2"/>
                <path d="M18 8v6M15 11h6"/>
            </svg>
        </div>

        <div>
            <h2>Crear Usuario</h2>
            <p>
                Administración institucional de usuarios
                para Agentes MICOOPE y Agencias ECOSABA.
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="user-errors">
            <strong>Revise la información ingresada.</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('administrador.usuarios.store') }}"
        class="user-form-card"
        id="formCrearUsuario"
    >
        @csrf

        <div class="user-form-intro">
            <strong>Nueva cuenta institucional</strong>
            <p>
                Registre los datos personales, configure el
                acceso y, cuando corresponda, asigne
                al colaborador a una agencia.
            </p>
        </div>

        {{-- INFORMACIÓN PERSONAL --}}

        <section class="user-section">

            <div class="user-section-title">
                <div class="user-section-icon">
                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                    </svg>
                </div>

                <div>
                    <h3>Información Personal</h3>
                    <p>Identificación del colaborador.</p>
                </div>
            </div>

            <div class="user-grid">

                <div class="user-field">
                    <label for="nombres">
                        Nombres
                        <span class="user-required">*</span>
                    </label>

                    <input
                        type="text"
                        id="nombres"
                        name="nombres"
                        value="{{ old('nombres') }}"
                        maxlength="150"
                        autocomplete="given-name"
                        required
                    >
                </div>

                <div class="user-field">
                    <label for="apellidos">
                        Apellidos
                        <span class="user-required">*</span>
                    </label>

                    <input
                        type="text"
                        id="apellidos"
                        name="apellidos"
                        value="{{ old('apellidos') }}"
                        maxlength="150"
                        autocomplete="family-name"
                        required
                    >
                </div>

            </div>

        </section>

        {{-- CUENTA Y PERMISOS --}}

        <section class="user-section">

            <div class="user-section-title">
                <div class="user-section-icon">
                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <rect x="4" y="4" width="16" height="16" rx="2"/>
                        <path d="M8 9h8M8 13h8"/>
                    </svg>
                </div>

                <div>
                    <h3>Cuenta y Permisos</h3>
                    <p>
                        Usuario, rol institucional y estado.
                    </p>
                </div>
            </div>

            <div class="user-grid">

                <div class="user-field">
                    <label for="usuario">
                        Nombre de Usuario
                        <span class="user-required">*</span>
                    </label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        value="{{ old('usuario') }}"
                        maxlength="50"
                        autocomplete="username"
                        required
                    >

                    <small>
                        Identificador utilizado para iniciar sesión.
                    </small>
                </div>

                <div class="user-field">
                    <label for="rol_id">
                        Rol Institucional
                        <span class="user-required">*</span>
                    </label>

                    <select
                        id="rol_id"
                        name="rol_id"
                        required
                    >
                        <option value="">
                            Seleccione un rol
                        </option>

                        @foreach($roles as $rol)
                            @php
                                $codigoRol = strtoupper(
                                    trim($rol->nombre)
                                );

                                $areaRol = match($codigoRol) {
                                    'AGENTE',
                                    'PROMOTOR',
                                    'JEFEDEAGENTES'
                                        => 'AGENTES',

                                    'RECEPTOR',
                                    'JEFECAJA',
                                    'SUBJEFECAJA',
                                    'JEFEAGENCIA'
                                        => 'AGENCIAS',

                                    default => 'INSTITUCIONAL',
                                };

                                $nombreRol = match($codigoRol) {
                                    'JEFEDEAGENTES' => 'Jefe de Agentes',
                                    'JEFECAJA' => 'Jefe de Caja',
                                    'SUBJEFECAJA' => 'Subjefe de Caja',
                                    'JEFEAGENCIA' => 'Jefe de Agencia',
                                    'AUDITORIA' => 'Auditoría',
                                    default => $rol->nombre,
                                };
                            @endphp

                            <option
                                value="{{ $rol->id }}"
                                data-area="{{ $areaRol }}"
                                @selected(old('rol_id') == $rol->id)
                            >
                                {{ $nombreRol }}
                                —
                                {{
                                    match($areaRol) {
                                        'AGENTES' => 'MICOOPE',
                                        'AGENCIAS' => 'ECOSABA',
                                        default => 'Institucional'
                                    }
                                }}
                            </option>
                        @endforeach
                    </select>

                    <small>
                        Determina los módulos y permisos
                        disponibles para la cuenta.
                    </small>
                </div>

                <div class="user-field">
                    <label for="estado">
                        Estado de la Cuenta
                        <span class="user-required">*</span>
                    </label>

                    <select
                        id="estado"
                        name="estado"
                        required
                    >
                        <option
                            value="ACTIVO"
                            @selected(old('estado', 'ACTIVO') === 'ACTIVO')
                        >
                            ACTIVO
                        </option>

                        <option
                            value="INACTIVO"
                            @selected(old('estado') === 'INACTIVO')
                        >
                            INACTIVO
                        </option>
                    </select>

                    <small>
                        Las cuentas inactivas no pueden operar
                        en el sistema.
                    </small>
                </div>

                <div class="user-field">
                    <label>Área del Sistema</label>

                    <input
                        type="text"
                        id="area_sistema"
                        value="Seleccione un rol"
                        readonly
                    >

                    <small>
                        Se determina automáticamente según el rol.
                    </small>
                </div>

            </div>

            <div class="user-module-info" id="informacionRol">
                Seleccione un rol para identificar el módulo
                al que tendrá acceso el usuario.
            </div>

        </section>

        {{-- ASIGNACIÓN A AGENCIA --}}

        <section
            class="user-section user-agencia-section"
            id="seccionAgencia"
            hidden
        >

            <div class="user-section-title">
                <div class="user-section-icon">
                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M8 21V10h8v11M3 10h18"/>
                    </svg>
                </div>

                <div>
                    <h3>Asignación a Agencia ECOSABA</h3>
                    <p>
                        Vinculación institucional del personal
                        con su agencia de trabajo.
                    </p>
                </div>
            </div>

            <div class="user-agencia-notice">
                Esta sección registra la asignación del
                colaborador a una agencia. Las ventanillas
                y los turnos de los receptores se administran
                posteriormente desde las funciones
                correspondientes de Jefatura de Caja.
            </div>

            <div class="user-grid">

                <div class="user-field">
                    <label for="agencia_id">
                        Agencia
                        <span class="user-required">*</span>
                    </label>

                    <select
                        id="agencia_id"
                        name="agencia_id"
                    >
                        <option value="">
                            Seleccione una agencia
                        </option>

                        @foreach($agencias as $agencia)
                            <option
                                value="{{ $agencia->id }}"
                                @selected(old('agencia_id') == $agencia->id)
                            >
                                {{ $agencia->codigo }}
                                —
                                {{ $agencia->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="user-field">
                    <label for="tipo_asignacion">
                        Tipo de Asignación
                        <span class="user-required">*</span>
                    </label>

                    <select
                        id="tipo_asignacion"
                        name="tipo_asignacion"
                    >
                        <option
                            value="PERMANENTE"
                            @selected(old('tipo_asignacion', 'PERMANENTE') === 'PERMANENTE')
                        >
                            Permanente
                        </option>

                        <option
                            value="TEMPORAL"
                            @selected(old('tipo_asignacion') === 'TEMPORAL')
                        >
                            Temporal
                        </option>
                    </select>

                    <small>
                        Las asignaciones temporales requieren
                        fecha de finalización.
                    </small>
                </div>

                <div class="user-field">
                    <label for="fecha_inicio">
                        Fecha de Inicio
                        <span class="user-required">*</span>
                    </label>

                    <input
                        type="date"
                        id="fecha_inicio"
                        name="fecha_inicio"
                        value="{{ old('fecha_inicio', now()->toDateString()) }}"
                    >
                </div>

                <div class="user-field">
                    <label for="fecha_fin">
                        Fecha de Finalización
                        <span
                            class="user-required"
                            id="indicadorFechaFin"
                            hidden
                        >*</span>
                    </label>

                    <input
                        type="date"
                        id="fecha_fin"
                        name="fecha_fin"
                        value="{{ old('fecha_fin') }}"
                    >

                    <small id="ayudaFechaFin">
                        Opcional para asignaciones permanentes.
                    </small>
                </div>

                <div class="user-field user-full">
                    <label for="motivo_asignacion">
                        Motivo u Observaciones
                    </label>

                    <textarea
                        id="motivo_asignacion"
                        name="motivo_asignacion"
                        maxlength="2000"
                        placeholder="Indique el motivo de la asignación, si corresponde."
                    >{{ old('motivo_asignacion') }}</textarea>

                    <small>
                        Puede indicar si corresponde a una
                        cobertura temporal, traslado u otra
                        circunstancia administrativa.
                    </small>
                </div>

            </div>

        </section>

        {{-- CREDENCIALES --}}

        <section class="user-section">

            <div class="user-section-title">
                <div class="user-section-icon">
                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <rect x="4" y="10" width="16" height="11" rx="2"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                    </svg>
                </div>

                <div>
                    <h3>Credenciales de Acceso</h3>
                    <p>
                        Contraseña inicial para el nuevo usuario.
                    </p>
                </div>
            </div>

            <div class="user-grid">

                <div class="user-field">
                    <label for="password">
                        Contraseña
                        <span class="user-required">*</span>
                    </label>

                    <div class="user-password-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="user-password-toggle"
                            data-target="password"
                        >
                            Mostrar
                        </button>
                    </div>
                </div>

                <div class="user-field">
                    <label for="password_confirmation">
                        Confirmar Contraseña
                        <span class="user-required">*</span>
                    </label>

                    <div class="user-password-wrap">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="user-password-toggle"
                            data-target="password_confirmation"
                        >
                            Mostrar
                        </button>
                    </div>
                </div>

            </div>

        </section>

        <div class="user-footer">

            <span class="user-footer-note">
                <span class="user-required">*</span>
                Campos obligatorios.
            </span>

            <div class="user-actions">

                <a
                    href="{{ route('administrador.usuarios.index') }}"
                    class="user-btn user-btn-secondary"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="user-btn user-btn-primary"
                >
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

    const rol = document.getElementById('rol_id');
    const area = document.getElementById('area_sistema');
    const informacion = document.getElementById('informacionRol');

    const seccionAgencia = document.getElementById('seccionAgencia');

    const agencia = document.getElementById('agencia_id');
    const tipo = document.getElementById('tipo_asignacion');
    const inicio = document.getElementById('fecha_inicio');
    const fin = document.getElementById('fecha_fin');

    const indicadorFin = document.getElementById('indicadorFechaFin');
    const ayudaFin = document.getElementById('ayudaFechaFin');

    function actualizarAsignacion() {
        const temporal = tipo.value === 'TEMPORAL';

        fin.required = temporal;
        indicadorFin.hidden = !temporal;

        ayudaFin.textContent = temporal
            ? 'Obligatoria para una asignación temporal.'
            : 'Opcional para asignaciones permanentes.';
    }

    function actualizarRol() {
        const opcion = rol.options[rol.selectedIndex];

        const modulo = opcion
            ? opcion.dataset.area || ''
            : '';

        const esAgencia = modulo === 'AGENCIAS';

        seccionAgencia.hidden = !esAgencia;

        agencia.required = esAgencia;
        tipo.required = esAgencia;
        inicio.required = esAgencia;

        agencia.disabled = !esAgencia;
        tipo.disabled = !esAgencia;
        inicio.disabled = !esAgencia;
        fin.disabled = !esAgencia;

        document.getElementById('motivo_asignacion').disabled =
            !esAgencia;

        if (!esAgencia) {
            fin.required = false;
        } else {
            actualizarAsignacion();
        }

        switch (modulo) {
            case 'AGENCIAS':
                area.value = 'Agencias ECOSABA';
                informacion.textContent =
                    'Este usuario tendrá acceso al módulo de ' +
                    'Agencias ECOSABA según su rol. ' +
                    'Debe registrar su asignación institucional.';
                break;

            case 'AGENTES':
                area.value = 'Agentes MICOOPE';
                informacion.textContent =
                    'Este usuario pertenece al módulo de ' +
                    'Agentes MICOOPE. No requiere asignación ' +
                    'a una agencia ECOSABA.';
                break;

            case 'INSTITUCIONAL':
                area.value = 'Institucional / Compartido';
                informacion.textContent =
                    'Este rol tiene funciones institucionales ' +
                    'compartidas según los permisos configurados.';
                break;

            default:
                area.value = 'Seleccione un rol';
                informacion.textContent =
                    'Seleccione un rol para identificar ' +
                    'el módulo correspondiente.';
        }
    }

    rol.addEventListener('change', actualizarRol);
    tipo.addEventListener('change', actualizarAsignacion);

    actualizarRol();

    document.querySelectorAll('.user-password-toggle')
        .forEach(function (boton) {

            boton.addEventListener('click', function () {

                const input = document.getElementById(
                    boton.dataset.target
                );

                if (!input) return;

                const visible = input.type === 'password';

                input.type = visible ? 'text' : 'password';

                boton.textContent = visible
                    ? 'Ocultar'
                    : 'Mostrar';
            });
        });

});
</script>
@endpush
