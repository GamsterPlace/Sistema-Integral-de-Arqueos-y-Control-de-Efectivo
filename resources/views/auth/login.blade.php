<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sistema de Arqueos para Agentes MICOOPE</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --azul-principal: #0d4f8b;
            --azul-secundario: #1266ac;
            --azul-oscuro: #063765;
            --azul-profundo: #03294d;
            --verde-principal: #00a651;
            --verde-oscuro: #00873f;
            --blanco: #ffffff;
            --gris-fondo: #f4f7fa;
            --gris-borde: #dce4eb;
            --gris-texto: #687784;
            --texto-principal: #17324a;
            --rojo-error: #c62828;
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
            background: var(--gris-fondo);
            color: var(--texto-principal);
        }

        button,
        input {
            font: inherit;
        }

        .login-page {
            position: relative;
            min-height: 100vh;
            display: grid;
            grid-template-columns:
                minmax(0, 1.18fr)
                minmax(430px, 0.82fr);
            overflow: hidden;
        }

        .login-information {
            position: relative;
            display: flex;
            align-items: center;
            padding: 58px clamp(42px, 6vw, 96px);
            overflow: hidden;
            background:
                linear-gradient(
                    135deg,
                    rgba(3, 41, 77, 0.99),
                    rgba(13, 79, 139, 0.96) 56%,
                    rgba(0, 135, 63, 0.91)
                );
            color: var(--blanco);
        }

        .login-information::before {
            content: "";
            position: absolute;
            width: 560px;
            height: 560px;
            top: -290px;
            right: -160px;
            border: 95px solid rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .login-information::after {
            content: "";
            position: absolute;
            width: 430px;
            height: 430px;
            bottom: -250px;
            left: -140px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
        }

        .pattern-grid {
            position: absolute;
            inset: 0;
            opacity: 0.16;
            background-image:
                linear-gradient(
                    rgba(255, 255, 255, 0.12) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255, 255, 255, 0.12) 1px,
                    transparent 1px
                );
            background-size: 55px 55px;
            mask-image:
                linear-gradient(
                    to bottom right,
                    rgba(0, 0, 0, 0.75),
                    transparent
                );
        }

        .information-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 750px;
        }

        .institutional-logo-container {
            width: min(470px, 100%);
            margin-bottom: 46px;
            padding: 14px 18px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
        }

        .institutional-logo {
            display: block;
            width: 100%;
            height: auto;
        }

        .system-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 9px 14px;
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(12px);
            color: rgba(255, 255, 255, 0.93);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .system-label-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #55d48a;
            box-shadow:
                0 0 0 5px rgba(85, 212, 138, 0.14);
        }

        .information-content h1 {
            margin: 0;
            max-width: 760px;
            color: var(--blanco);
            font-size: clamp(38px, 4.2vw, 62px);
            line-height: 1.08;
            letter-spacing: -1.5px;
            text-transform: uppercase;
        }

        .information-description {
            max-width: 620px;
            margin: 24px 0 0;
            color: rgba(255, 255, 255, 0.79);
            font-size: 18px;
            line-height: 1.72;
        }

        .feature-list {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 15px 28px;
            margin-top: 38px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.92);
            font-size: 14px;
            font-weight: 650;
        }

        .feature-icon {
            flex: 0 0 auto;
            width: 31px;
            height: 31px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.11);
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.13);
            color: #78e5a8;
        }

        .feature-icon svg {
            width: 17px;
            height: 17px;
        }

        .login-panel {
            position: relative;
            z-index: 3;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 38px;
            background:
                radial-gradient(
                    circle at top right,
                    rgba(0, 166, 81, 0.09),
                    transparent 34%
                ),
                radial-gradient(
                    circle at bottom left,
                    rgba(13, 79, 139, 0.07),
                    transparent 32%
                ),
                var(--gris-fondo);
        }

        .login-card {
            width: 100%;
            max-width: 470px;
            padding: 34px 38px 36px;
            border: 1px solid rgba(219, 228, 236, 0.92);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow:
                0 24px 60px rgba(20, 56, 83, 0.12),
                0 4px 14px rgba(20, 56, 83, 0.05);
        }

        .agent-logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 12px;
        }

        .agent-logo {
            display: block;
            width: 225px;
            height: 225px;
            object-fit: contain;
            filter:
                drop-shadow(
                    0 12px 22px rgba(13, 79, 139, 0.14)
                );
        }

        .login-heading {
            margin-bottom: 28px;
            text-align: center;
        }

        .login-heading h2 {
            margin: 0;
            color: var(--azul-profundo);
            font-size: 30px;
            letter-spacing: -0.7px;
        }

        .login-heading p {
            margin: 10px 0 0;
            color: var(--gris-texto);
            font-size: 15px;
            line-height: 1.55;
        }

        .status-message {
            margin-bottom: 20px;
            padding: 13px 15px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.45;
        }

        .status-message.success {
            border: 1px solid #b7dfc6;
            background: #edf9f1;
            color: #17663a;
        }

        .status-message.error {
            border: 1px solid #efc3c3;
            background: #fff2f2;
            color: var(--rojo-error);
        }

        .form-field {
            margin-bottom: 19px;
        }

        .form-field label {
            display: block;
            margin-bottom: 8px;
            color: #2b4154;
            font-size: 14px;
            font-weight: 700;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 16px;
            width: 20px;
            height: 20px;
            transform: translateY(-50%);
            color: #81909c;
            pointer-events: none;
        }

        .input-icon svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .form-input {
            width: 100%;
            min-height: 54px;
            padding: 0 48px;
            border: 1px solid var(--gris-borde);
            border-radius: 14px;
            outline: none;
            background: #fbfcfd;
            color: var(--texto-principal);
            font-size: 15px;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .form-input::placeholder {
            color: #a1adb7;
        }

        .form-input:focus {
            border-color: var(--azul-principal);
            background: var(--blanco);
            box-shadow:
                0 0 0 4px rgba(13, 79, 139, 0.10);
        }

        .form-input.has-error {
            border-color: #d9534f;
            background: #fffafa;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 14px;
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #748390;
            cursor: pointer;
        }

        .password-toggle:hover {
            background: #eef3f6;
            color: var(--azul-principal);
        }

        .password-toggle svg {
            width: 19px;
            height: 19px;
        }

        .field-error {
            display: block;
            margin-top: 7px;
            color: var(--rojo-error);
            font-size: 13px;
        }

        .secure-message-row {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 8px 0 23px;
        }

        .secure-access {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #75838e;
            font-size: 12px;
            font-weight: 650;
        }

        .secure-access svg {
            width: 16px;
            height: 16px;
            color: var(--verde-principal);
        }

        .login-button {
            position: relative;
            width: 100%;
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 14px;
            overflow: hidden;
            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-secundario)
                );
            color: var(--blanco);
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.25px;
            cursor: pointer;
            box-shadow:
                0 12px 24px rgba(13, 79, 139, 0.22);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                filter 0.2s ease;
        }

        .login-button::before {
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

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow:
                0 16px 30px rgba(13, 79, 139, 0.28);
            filter: brightness(1.03);
        }

        .login-button:hover::before {
            transform: translateX(100%);
        }

        .login-button svg {
            width: 18px;
            height: 18px;
        }

        .login-footer {
            margin-top: 26px;
            padding-top: 22px;
            border-top: 1px solid #edf1f4;
            text-align: center;
        }

        .login-footer p {
            margin: 0;
            color: #87949e;
            font-size: 12px;
            line-height: 1.6;
        }

        .version {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: 8px;
            color: #75838e;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .version-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--verde-principal);
        }

        @media (max-width: 1080px) {
            .login-page {
                grid-template-columns:
                    minmax(0, 1fr)
                    460px;
            }

            .login-information {
                padding: 50px 42px;
            }

            .information-content h1 {
                font-size: 46px;
            }

            .institutional-logo-container {
                margin-bottom: 38px;
            }
        }

        @media (max-width: 900px) {
            .login-page {
                display: block;
                min-height: 100vh;
                background:
                    linear-gradient(
                        135deg,
                        var(--azul-profundo),
                        var(--azul-principal)
                    );
            }

            .login-information {
                min-height: auto;
                padding: 30px 24px 24px;
                background: transparent;
            }

            .information-content {
                max-width: 520px;
                margin: 0 auto;
                text-align: center;
            }

            .institutional-logo-container {
                width: min(340px, 100%);
                margin: 0 auto 23px;
            }

            .system-label {
                margin-bottom: 14px;
            }

            .information-content h1 {
                font-size: 34px;
                letter-spacing: -1px;
            }

            .information-description,
            .feature-list {
                display: none;
            }

            .login-panel {
                min-height: auto;
                padding: 22px 18px 40px;
                background: transparent;
            }

            .login-card {
                max-width: 500px;
                padding: 30px 28px 32px;
                border-radius: 24px;
            }

            .agent-logo {
                width: 190px;
                height: 190px;
            }
        }

        @media (max-width: 480px) {
            .login-information {
                padding: 22px 18px 15px;
            }

            .institutional-logo-container {
                padding: 10px 12px;
                border-radius: 14px;
            }

            .information-content h1 {
                font-size: 28px;
            }

            .login-panel {
                padding: 14px 12px 28px;
            }

            .login-card {
                padding: 25px 20px 28px;
                border-radius: 20px;
            }

            .agent-logo {
                width: 165px;
                height: 165px;
            }

            .login-heading h2 {
                font-size: 26px;
            }

            .secure-message-row {
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <main class="login-page">
        <section class="login-information">
            <div class="pattern-grid"></div>

            <div class="information-content">
                <div class="institutional-logo-container">
                    <img
                        src="{{ asset('images/logos/ecosaba.png') }}"
                        alt="Ecosaba MICOOPE"
                        class="institutional-logo"
                    >
                </div>

                <div class="system-label">
                    <span class="system-label-dot"></span>
                    Plataforma institucional segura
                </div>

                <h1>
                    Sistema de Arqueos para Agentes MICOOPE
                </h1>

                <p class="information-description">
                    Plataforma digital para el registro, control,
                    supervisión y validación de los arqueos realizados
                    a los agentes MICOOPE.
                </p>

                <div class="feature-list">
                    <div class="feature-item">
                        <span class="feature-icon">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="16"
                                    rx="2"
                                />
                                <path d="M7 8h10"/>
                                <path d="M7 12h6"/>
                                <path d="M7 16h4"/>
                            </svg>
                        </span>

                        Registro digital de arqueos
                    </div>

                    <div class="feature-item">
                        <span class="feature-icon">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M4 19h16"/>
                                <path d="M6 16V9"/>
                                <path d="M12 16V5"/>
                                <path d="M18 16v-4"/>
                            </svg>
                        </span>

                        Control y seguimiento
                    </div>

                    <div class="feature-item">
                        <span class="feature-icon">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="12" cy="12" r="9"/>
                                <path d="m8.5 12 2.2 2.2 4.8-5"/>
                            </svg>
                        </span>

                        Validación de arqueos
                    </div>

                    <div class="feature-item">
                        <span class="feature-icon">
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
                        </span>

                        Información protegida
                    </div>
                </div>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-card">
                <div class="agent-logo-container">
                    <img
                        src="{{ asset('images/logos/agentes-micoope.png') }}"
                        alt="Agentes MICOOPE"
                        class="agent-logo"
                    >
                </div>

                <div class="login-heading">
                    <h2>Bienvenido</h2>

                    <p>
                        Ingrese sus credenciales institucionales para
                        acceder al sistema.
                    </p>
                </div>

                @if (session('success'))
                    <div class="status-message success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="status-message error">
                        Verifique los datos ingresados e intente nuevamente.
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('login.iniciar') }}"
                    autocomplete="off"
                >
                    @csrf

                    <div class="form-field">
                        <label for="usuario">
                            Usuario
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
                                    <path
                                        d="M20 21a8 8 0 0 0-16 0"
                                    />
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>

                            <input
                                id="usuario"
                                name="usuario"
                                type="text"
                                value="{{ old('usuario') }}"
                                placeholder="Ingrese su usuario"
                                class="form-input @error('usuario') has-error @enderror"
                                autocomplete="username"
                                maxlength="50"
                                autofocus
                                required
                            >
                        </div>

                        @error('usuario')
                            <span class="field-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="password">
                            Contraseña
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
                                    <path
                                        d="M8 10V7a4 4 0 0 1 8 0v3"
                                    />
                                </svg>
                            </span>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Ingrese su contraseña"
                                class="form-input @error('password') has-error @enderror"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="password-toggle"
                                aria-label="Mostrar contraseña"
                                title="Mostrar contraseña"
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

                    <div class="secure-message-row">
                        <span class="secure-access">
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

                            Acceso institucional seguro
                        </span>
                    </div>

                    <button
                        type="submit"
                        class="login-button"
                    >
                        Ingresar al sistema

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>
                    </button>
                </form>

                <div class="login-footer">
                    <p>
                        El acceso está restringido exclusivamente a
                        usuarios autorizados. Las acciones realizadas
                        dentro del sistema quedan registradas.
                    </p>

                    <span class="version">
                        <span class="version-dot"></span>
                        Sistema de Arqueos · Versión 1.0
                    </span>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput =
                document.getElementById('password');

            const toggleButton =
                document.getElementById('togglePassword');

            if (!passwordInput || !toggleButton) {
                return;
            }

            toggleButton.addEventListener('click', function () {
                const passwordVisible =
                    passwordInput.type === 'text';

                passwordInput.type =
                    passwordVisible ? 'password' : 'text';

                const text =
                    passwordVisible
                        ? 'Mostrar contraseña'
                        : 'Ocultar contraseña';

                toggleButton.setAttribute('aria-label', text);
                toggleButton.setAttribute('title', text);
            });
        });
    </script>
</body>
</html>
