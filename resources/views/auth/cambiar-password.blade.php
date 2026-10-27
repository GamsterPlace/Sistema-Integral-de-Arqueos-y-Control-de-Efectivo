<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Cambiar contraseña | Sistema de Arqueos</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --azul-principal: #0d4f8b;
            --azul-secundario: #1266ac;
            --azul-profundo: #03294d;
            --verde-principal: #00a651;
            --verde-oscuro: #00873f;
            --blanco: #ffffff;
            --gris-fondo: #f3f6f9;
            --gris-borde: #dce4eb;
            --gris-texto: #6c7b87;
            --texto-principal: #17324a;
            --rojo-error: #c62828;
            --amarillo: #e6a700;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            color: var(--texto-principal);
            background:
                radial-gradient(
                    circle at top left,
                    rgba(13, 79, 139, 0.13),
                    transparent 34%
                ),
                radial-gradient(
                    circle at bottom right,
                    rgba(0, 166, 81, 0.11),
                    transparent 33%
                ),
                var(--gris-fondo);
        }

        button,
        input {
            font: inherit;
        }

        .password-page {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px 24px;
            overflow: hidden;
        }

        .password-page::before {
            content: "";
            position: absolute;
            width: 520px;
            height: 520px;
            top: -320px;
            right: -210px;
            border-radius: 50%;
            border: 80px solid rgba(13, 79, 139, 0.05);
        }

        .password-page::after {
            content: "";
            position: absolute;
            width: 430px;
            height: 430px;
            bottom: -300px;
            left: -190px;
            border-radius: 50%;
            background: rgba(0, 166, 81, 0.04);
        }

        .password-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1120px;
            display: grid;
            grid-template-columns:
                minmax(310px, 0.84fr)
                minmax(470px, 1.16fr);
            overflow: hidden;
            border: 1px solid rgba(215, 225, 233, 0.95);
            border-radius: 30px;
            background: var(--blanco);
            box-shadow:
                0 28px 70px rgba(17, 54, 82, 0.15),
                0 5px 16px rgba(17, 54, 82, 0.06);
        }

        .security-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 46px 40px;
            overflow: hidden;
            color: var(--blanco);
            background:
                linear-gradient(
                    145deg,
                    var(--azul-profundo),
                    var(--azul-principal) 62%,
                    var(--verde-oscuro)
                );
        }

        .security-panel::before {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            top: -180px;
            right: -170px;
            border-radius: 50%;
            border: 60px solid rgba(255, 255, 255, 0.05);
        }

        .security-panel::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            bottom: -170px;
            left: -130px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
        }

        .security-content,
        .security-footer {
            position: relative;
            z-index: 2;
        }

        .institutional-logo-box {
            width: 100%;
            max-width: 310px;
            margin-bottom: 45px;
            padding: 12px 16px;
            border-radius: 17px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 15px 34px rgba(0, 0, 0, 0.16);
        }

        .institutional-logo {
            display: block;
            width: 100%;
            height: auto;
        }

        .security-icon {
            width: 66px;
            height: 66px;
            display: grid;
            place-items: center;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
        }

        .security-icon svg {
            width: 34px;
            height: 34px;
        }

        .security-content h1 {
            margin: 0;
            font-size: 34px;
            line-height: 1.12;
            letter-spacing: -1px;
        }

        .security-description {
            margin: 20px 0 0;
            color: rgba(255, 255, 255, 0.79);
            font-size: 15px;
            line-height: 1.72;
        }

        .security-information {
            display: grid;
            gap: 13px;
            margin-top: 34px;
        }

        .security-item {
            display: flex;
            align-items: center;
            gap: 11px;
            color: rgba(255, 255, 255, 0.91);
            font-size: 13px;
            font-weight: 650;
        }

        .security-item-icon {
            flex: 0 0 auto;
            width: 29px;
            height: 29px;
            display: grid;
            place-items: center;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.12);
            color: #75e3a6;
        }

        .security-item-icon svg {
            width: 16px;
            height: 16px;
        }

        .security-footer {
            margin-top: 44px;
            padding-top: 22px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        .security-footer p {
            margin: 0;
            color: rgba(255, 255, 255, 0.57);
            font-size: 11px;
            line-height: 1.6;
        }

        .form-panel {
            padding: 44px 50px 42px;
        }

        .form-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 25px;
            margin-bottom: 29px;
        }

        .form-title h2 {
            margin: 0;
            color: var(--azul-profundo);
            font-size: 30px;
            line-height: 1.2;
            letter-spacing: -0.7px;
        }

        .form-title p {
            margin: 10px 0 0;
            color: var(--gris-texto);
            font-size: 14px;
            line-height: 1.6;
        }

        .agent-logo {
            flex: 0 0 auto;
            width: 105px;
            height: 105px;
            object-fit: contain;
        }

        .user-information {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 25px;
            padding: 13px 15px;
            border: 1px solid #dce8f0;
            border-radius: 14px;
            background: #f7fafc;
        }

        .user-icon {
            flex: 0 0 auto;
            width: 39px;
            height: 39px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: rgba(13, 79, 139, 0.10);
            color: var(--azul-principal);
        }

        .user-icon svg {
            width: 21px;
            height: 21px;
        }

        .user-details span {
            display: block;
            color: #798793;
            font-size: 11px;
            font-weight: 750;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .user-details strong {
            display: block;
            margin-top: 3px;
            color: var(--texto-principal);
            font-size: 15px;
        }

        .status-message {
            margin-bottom: 20px;
            padding: 13px 15px;
            border: 1px solid #efc3c3;
            border-radius: 12px;
            background: #fff2f2;
            color: var(--rojo-error);
            font-size: 13px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d4355;
            font-size: 13px;
            font-weight: 750;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            width: 20px;
            height: 20px;
            transform: translateY(-50%);
            color: #86949f;
            pointer-events: none;
        }

        .input-icon svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .form-input {
            width: 100%;
            min-height: 52px;
            padding: 0 49px;
            border: 1px solid var(--gris-borde);
            border-radius: 13px;
            outline: none;
            background: #fbfcfd;
            color: var(--texto-principal);
            font-size: 14px;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .form-input::placeholder {
            color: #a3afb8;
        }

        .form-input:focus {
            border-color: var(--azul-principal);
            background: var(--blanco);
            box-shadow:
                0 0 0 4px rgba(13, 79, 139, 0.09);
        }

        .form-input.has-error {
            border-color: #d9534f;
            background: #fffafa;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 13px;
            width: 33px;
            height: 33px;
            display: grid;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #74838f;
            cursor: pointer;
        }

        .password-toggle:hover {
            background: #edf2f5;
            color: var(--azul-principal);
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
        }

        .field-error {
            display: block;
            margin-top: 7px;
            color: var(--rojo-error);
            font-size: 12px;
        }

        .password-strength {
            margin: 7px 0 18px;
        }

        .strength-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #798793;
            font-size: 11px;
            font-weight: 700;
        }

        .strength-status {
            color: var(--rojo-error);
        }

        .strength-bar {
            height: 7px;
            overflow: hidden;
            border-radius: 999px;
            background: #e8edf1;
        }

        .strength-progress {
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: var(--rojo-error);
            transition:
                width 0.25s ease,
                background 0.25s ease;
        }

        .requirements {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 10px 14px;
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #e3e9ee;
            border-radius: 14px;
            background: #fafcfd;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #83909a;
            font-size: 11px;
            font-weight: 650;
            transition: color 0.2s ease;
        }

        .requirement-icon {
            flex: 0 0 auto;
            width: 20px;
            height: 20px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #e7ecef;
            color: #93a0a9;
            transition:
                color 0.2s ease,
                background 0.2s ease;
        }

        .requirement-icon svg {
            width: 12px;
            height: 12px;
        }

        .requirement.valid {
            color: #207849;
        }

        .requirement.valid .requirement-icon {
            background: #ddf5e6;
            color: var(--verde-principal);
        }

        .actions {
            display: flex;
            justify-content: flex-end;
        }

        .submit-button {
            position: relative;
            width: 100%;
            min-height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            overflow: hidden;
            border: 0;
            border-radius: 14px;
            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-secundario)
                );
            color: var(--blanco);
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.2px;
            cursor: pointer;
            box-shadow:
                0 12px 24px rgba(13, 79, 139, 0.22);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                filter 0.2s ease;
        }

        .submit-button::before {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background:
                linear-gradient(
                    90deg,
                    transparent,
                    rgba(255, 255, 255, 0.14),
                    transparent
                );
            transition: transform 0.5s ease;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            filter: brightness(1.03);
            box-shadow:
                0 16px 29px rgba(13, 79, 139, 0.27);
        }

        .submit-button:hover::before {
            transform: translateX(100%);
        }

        .submit-button svg {
            width: 18px;
            height: 18px;
        }

        .form-note {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 18px;
            color: #84919a;
            font-size: 11px;
            line-height: 1.55;
        }

        .form-note svg {
            flex: 0 0 auto;
            width: 16px;
            height: 16px;
            margin-top: 1px;
            color: var(--verde-principal);
        }

        @media (max-width: 900px) {
            .password-page {
                align-items: flex-start;
                padding: 24px 16px;
            }

            .password-container {
                max-width: 650px;
                grid-template-columns: 1fr;
                border-radius: 25px;
            }

            .security-panel {
                padding: 30px;
            }

            .institutional-logo-box {
                max-width: 270px;
                margin-bottom: 28px;
            }

            .security-icon {
                width: 55px;
                height: 55px;
                margin-bottom: 18px;
            }

            .security-content h1 {
                font-size: 29px;
            }

            .security-information,
            .security-footer {
                display: none;
            }

            .form-panel {
                padding: 35px 34px;
            }
        }

        @media (max-width: 520px) {
            .password-page {
                padding: 12px;
            }

            .password-container {
                border-radius: 20px;
            }

            .security-panel {
                padding: 24px 21px;
            }

            .institutional-logo-box {
                max-width: 240px;
                margin: 0 auto 24px;
            }

            .security-content {
                text-align: center;
            }

            .security-icon {
                margin-right: auto;
                margin-left: auto;
            }

            .security-description {
                font-size: 13px;
            }

            .form-panel {
                padding: 28px 20px 30px;
            }

            .form-header {
                display: block;
                text-align: center;
            }

            .agent-logo {
                width: 115px;
                height: 115px;
                margin-top: 17px;
            }

            .form-title h2 {
                font-size: 26px;
            }

            .requirements {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <main class="password-page">
        <section class="password-container">
            <aside class="security-panel">
                <div class="security-content">
                    <div class="institutional-logo-box">
                        <img
                            src="{{ asset('images/logos/ecosaba.png') }}"
                            alt="Ecosaba MICOOPE"
                            class="institutional-logo"
                        >
                    </div>

                    <div class="security-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect
                                x="4"
                                y="10"
                                width="16"
                                height="11"
                                rx="2"
                            />

                            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>

                            <path d="M12 14v3"/>
                        </svg>
                    </div>

                    <h1>
                        Seguridad de la cuenta
                    </h1>

                    <p class="security-description">
                        Por políticas de seguridad institucional debe
                        actualizar su contraseña antes de continuar
                        utilizando el sistema.
                    </p>

                    <div class="security-information">
                        <div class="security-item">
                            <span class="security-item-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="m5 12 4 4L19 6"/>
                                </svg>
                            </span>

                            Contraseña personal e intransferible
                        </div>

                        <div class="security-item">
                            <span class="security-item-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"
                                    />

                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                            </span>

                            Información protegida
                        </div>

                        <div class="security-item">
                            <span class="security-item-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M12 8v4l3 2"/>
                                    <circle cx="12" cy="12" r="9"/>
                                </svg>
                            </span>

                            Cambio periódico obligatorio
                        </div>
                    </div>
                </div>

                <div class="security-footer">
                    <p>
                        El acceso y las modificaciones de seguridad
                        realizadas dentro del sistema quedan registradas.
                    </p>
                </div>
            </aside>

            <section class="form-panel">
                <div class="form-header">
                    <div class="form-title">
                        <h2>
                            Actualizar contraseña
                        </h2>

                        <p>
                            Cree una contraseña segura que no haya
                            utilizado anteriormente.
                        </p>
                    </div>

                    <img
                        src="{{ asset('images/logos/agentes-micoope.png') }}"
                        alt="Agentes MICOOPE"
                        class="agent-logo"
                    >
                </div>

                <div class="user-information">
                    <span class="user-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M20 21a8 8 0 0 0-16 0"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>

                    <div class="user-details">
                        <span>Usuario autenticado</span>

                        <strong>
                            {{ auth()->user()->usuario }}
                        </strong>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="status-message">
                        Verifique la información ingresada y corrija
                        los campos indicados.
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('password.actualizar') }}"
                    autocomplete="off"
                    id="passwordForm"
                >
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="password_actual">
                            Contraseña actual
                        </label>

                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <rect
                                        x="4"
                                        y="10"
                                        width="16"
                                        height="11"
                                        rx="2"
                                    />

                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                id="password_actual"
                                name="password_actual"
                                type="password"
                                class="form-input @error('password_actual') has-error @enderror"
                                placeholder="Ingrese su contraseña actual"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-password-target="password_actual"
                                aria-label="Mostrar contraseña"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                    />

                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>

                        @error('password_actual')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">
                            Nueva contraseña
                        </label>

                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <rect
                                        x="4"
                                        y="10"
                                        width="16"
                                        height="11"
                                        rx="2"
                                    />

                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                    <path d="M12 14v3"/>
                                </svg>
                            </span>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-input @error('password') has-error @enderror"
                                placeholder="Ingrese su nueva contraseña"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-password-target="password"
                                aria-label="Mostrar contraseña"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                    />

                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="password-strength">
                        <div class="strength-header">
                            <span>Fortaleza de la contraseña</span>

                            <span
                                id="strengthStatus"
                                class="strength-status"
                            >
                                Sin contraseña
                            </span>
                        </div>

                        <div class="strength-bar">
                            <div
                                id="strengthProgress"
                                class="strength-progress"
                            ></div>
                        </div>
                    </div>

                    <div class="requirements">
                        <div
                            id="requirementLength"
                            class="requirement"
                        >
                            <span class="requirement-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m6 12 4 4 8-8"/>
                                </svg>
                            </span>

                            Mínimo 8 caracteres
                        </div>

                        <div
                            id="requirementUppercase"
                            class="requirement"
                        >
                            <span class="requirement-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m6 12 4 4 8-8"/>
                                </svg>
                            </span>

                            Una letra mayúscula
                        </div>

                        <div
                            id="requirementLowercase"
                            class="requirement"
                        >
                            <span class="requirement-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m6 12 4 4 8-8"/>
                                </svg>
                            </span>

                            Una letra minúscula
                        </div>

                        <div
                            id="requirementNumber"
                            class="requirement"
                        >
                            <span class="requirement-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m6 12 4 4 8-8"/>
                                </svg>
                            </span>

                            Un número
                        </div>

                        <div
                            id="requirementSymbol"
                            class="requirement"
                        >
                            <span class="requirement-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m6 12 4 4 8-8"/>
                                </svg>
                            </span>

                            Un carácter especial
                        </div>

                        <div
                            id="requirementConfirmation"
                            class="requirement"
                        >
                            <span class="requirement-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m6 12 4 4 8-8"/>
                                </svg>
                            </span>

                            Contraseñas coinciden
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">
                            Confirmar nueva contraseña
                        </label>

                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <rect
                                        x="4"
                                        y="10"
                                        width="16"
                                        height="11"
                                        rx="2"
                                    />

                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                    <path d="m9 16 2 2 4-4"/>
                                </svg>
                            </span>

                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="form-input @error('password_confirmation') has-error @enderror"
                                placeholder="Repita la nueva contraseña"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-password-target="password_confirmation"
                                aria-label="Mostrar contraseña"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                    />

                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>

                        @error('password_confirmation')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="actions">
                        <button
                            type="submit"
                            class="submit-button"
                        >
                            Actualizar contraseña

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                        </button>
                    </div>

                    <div class="form-note">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"
                            />

                            <path d="m9 12 2 2 4-4"/>
                        </svg>

                        <span>
                            Por seguridad, no comparta su contraseña con
                            ninguna otra persona.
                        </span>
                    </div>
                </form>
            </section>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput =
                document.getElementById('password');

            const confirmationInput =
                document.getElementById('password_confirmation');

            const strengthProgress =
                document.getElementById('strengthProgress');

            const strengthStatus =
                document.getElementById('strengthStatus');

            const requirements = {
                length: document.getElementById('requirementLength'),
                uppercase: document.getElementById('requirementUppercase'),
                lowercase: document.getElementById('requirementLowercase'),
                number: document.getElementById('requirementNumber'),
                symbol: document.getElementById('requirementSymbol'),
                confirmation:
                    document.getElementById('requirementConfirmation')
            };

            function updateRequirement(element, isValid) {
                if (!element) {
                    return;
                }

                element.classList.toggle('valid', isValid);
            }

            function evaluatePassword() {
                const password = passwordInput.value;
                const confirmation = confirmationInput.value;

                const checks = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    symbol: /[^A-Za-z0-9]/.test(password),
                    confirmation:
                        password.length > 0 &&
                        confirmation.length > 0 &&
                        password === confirmation
                };

                updateRequirement(
                    requirements.length,
                    checks.length
                );

                updateRequirement(
                    requirements.uppercase,
                    checks.uppercase
                );

                updateRequirement(
                    requirements.lowercase,
                    checks.lowercase
                );

                updateRequirement(
                    requirements.number,
                    checks.number
                );

                updateRequirement(
                    requirements.symbol,
                    checks.symbol
                );

                updateRequirement(
                    requirements.confirmation,
                    checks.confirmation
                );

                const securityChecks = [
                    checks.length,
                    checks.uppercase,
                    checks.lowercase,
                    checks.number,
                    checks.symbol
                ];

                const score = securityChecks.filter(Boolean).length;

                if (password.length === 0) {
                    strengthProgress.style.width = '0%';
                    strengthProgress.style.background = '#c62828';
                    strengthStatus.textContent = 'Sin contraseña';
                    strengthStatus.style.color = '#c62828';

                    return;
                }

                if (score <= 2) {
                    strengthProgress.style.width = '30%';
                    strengthProgress.style.background = '#c62828';
                    strengthStatus.textContent = 'Débil';
                    strengthStatus.style.color = '#c62828';

                    return;
                }

                if (score <= 4) {
                    strengthProgress.style.width = '65%';
                    strengthProgress.style.background = '#e6a700';
                    strengthStatus.textContent = 'Media';
                    strengthStatus.style.color = '#b78100';

                    return;
                }

                strengthProgress.style.width = '100%';
                strengthProgress.style.background = '#00a651';
                strengthStatus.textContent = 'Segura';
                strengthStatus.style.color = '#00873f';
            }

            passwordInput.addEventListener(
                'input',
                evaluatePassword
            );

            confirmationInput.addEventListener(
                'input',
                evaluatePassword
            );

            document
                .querySelectorAll('[data-password-target]')
                .forEach(function (button) {
                    button.addEventListener('click', function () {
                        const targetId =
                            button.getAttribute(
                                'data-password-target'
                            );

                        const input =
                            document.getElementById(targetId);

                        if (!input) {
                            return;
                        }

                        const isVisible =
                            input.type === 'text';

                        input.type =
                            isVisible ? 'password' : 'text';

                        button.setAttribute(
                            'aria-label',
                            isVisible
                                ? 'Mostrar contraseña'
                                : 'Ocultar contraseña'
                        );
                    });
                });
        });
    </script>
</body>
</html>
