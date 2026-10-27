<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Auditoría') | Sistema de Arqueos MICOOPE</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        :root {
            --azul-principal: #164c96;
            --azul-secundario: #1c64b5;
            --azul-oscuro: #0b315f;
            --azul-profundo: #06284f;
            --verde-principal: #00a651;
            --verde-oscuro: #008640;
            --blanco: #ffffff;
            --fondo: #f3f6f9;
            --fondo-suave: #f8fafc;
            --borde: #dfe7ee;
            --texto: #19344d;
            --texto-secundario: #6d7d8a;
            --texto-claro: #8c9aa5;
            --sidebar-width: 278px;
            --header-height: 74px;
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
            overflow-x: hidden;
            background: var(--fondo);
            color: var(--texto);
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        .app-layout {
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            z-index: 50;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background:
                linear-gradient(
                    165deg,
                    var(--azul-profundo),
                    var(--azul-principal) 62%,
                    #0e5f79
                );
            color: var(--blanco);
            box-shadow: 10px 0 30px rgba(4, 36, 70, .13);
            transition: transform .25s ease;
        }

        .sidebar-decoration {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .sidebar-decoration::before {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            top: -170px;
            right: -150px;
            border: 52px solid rgba(255, 255, 255, .04);
            border-radius: 50%;
        }

        .sidebar-decoration::after {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            bottom: -150px;
            left: -110px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .035);
        }

        .sidebar-header,
        .sidebar-user,
        .sidebar-navigation,
        .sidebar-footer {
            position: relative;
            z-index: 2;
        }

        .sidebar-header {
            padding: 24px 22px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .10);
        }

        .sidebar-logo-box {
            display: flex;
            justify-content: center;
            padding: 10px 13px;
            border-radius: 15px;
            background: rgba(255, 255, 255, .97);
            box-shadow: 0 12px 26px rgba(0, 0, 0, .14);
        }

        .sidebar-logo {
            display: block;
            width: 100%;
            max-width: 190px;
            height: 62px;
            object-fit: contain;
        }

        .sidebar-system-name {
            margin-top: 17px;
            text-align: center;
        }

        .sidebar-system-name strong {
            display: block;
            font-size: 14px;
            line-height: 1.4;
            letter-spacing: .1px;
        }

        .sidebar-system-name span {
            display: block;
            margin-top: 5px;
            color: rgba(255, 255, 255, .60);
            font-size: 11px;
            letter-spacing: .7px;
            text-transform: uppercase;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 19px 16px 4px;
            padding: 13px;
            border: 1px solid rgba(255, 255, 255, .11);
            border-radius: 15px;
            background: rgba(255, 255, 255, .08);
        }

        .sidebar-user-avatar {
            flex: 0 0 auto;
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: rgba(255, 255, 255, .14);
            color: #7be4aa;
        }

        .sidebar-user-avatar svg {
            width: 23px;
            height: 23px;
        }

        .sidebar-user-info {
            min-width: 0;
        }

        .sidebar-user-info strong {
            display: block;
            overflow: hidden;
            color: var(--blanco);
            font-size: 13px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sidebar-user-info span {
            display: block;
            margin-top: 4px;
            color: rgba(255, 255, 255, .57);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .7px;
            text-transform: uppercase;
        }

        .sidebar-navigation {
            flex: 1;
            padding: 18px 14px 22px;
        }

        .navigation-label {
            margin: 17px 11px 9px;
            color: rgba(255, 255, 255, .43);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .navigation-label:first-child {
            margin-top: 0;
        }

        .navigation-list {
            display: grid;
            gap: 7px;
            margin-bottom: 17px;
        }

        .navigation-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 13px;
            min-height: 51px;
            padding: 11px 14px;
            overflow: hidden;
            border-radius: 13px;
            color: rgba(255, 255, 255, .73);
            font-size: 13px;
            font-weight: 680;
            transition:
                color .2s ease,
                background .2s ease,
                transform .2s ease;
        }

        .navigation-link:hover {
            transform: translateX(3px);
            background: rgba(255, 255, 255, .09);
            color: var(--blanco);
        }

        .navigation-link.active {
            background:
                linear-gradient(
                    135deg,
                    rgba(255, 255, 255, .18),
                    rgba(255, 255, 255, .10)
                );
            color: var(--blanco);
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, .10);
        }

        .navigation-link.active::before {
            content: "";
            position: absolute;
            top: 10px;
            bottom: 10px;
            left: 0;
            width: 4px;
            border-radius: 0 5px 5px 0;
            background: #70df9f;
        }

        .navigation-icon {
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: rgba(255, 255, 255, .08);
        }

        .navigation-link.active .navigation-icon {
            background: rgba(112, 223, 159, .16);
            color: #7be4aa;
        }

        .navigation-icon svg {
            width: 19px;
            height: 19px;
        }

        .sidebar-footer {
            padding: 17px 18px 20px;
            border-top: 1px solid rgba(255, 255, 255, .09);
        }

        .logout-button {
            width: 100%;
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            border: 1px solid rgba(255, 255, 255, .13);
            border-radius: 12px;
            background: rgba(255, 255, 255, .07);
            color: rgba(255, 255, 255, .78);
            font-size: 12px;
            font-weight: 750;
            cursor: pointer;
            transition: background .2s ease, color .2s ease;
        }

        .logout-button:hover {
            background: rgba(255, 255, 255, .14);
            color: var(--blanco);
        }

        .logout-button svg {
            width: 18px;
            height: 18px;
        }

        .main-area {
            min-height: 100vh;
            margin-left: var(--sidebar-width);
            transition: margin-left .25s ease;
        }

        .topbar {
            position: sticky;
            z-index: 30;
            top: 0;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
            padding: 0 30px;
            border-bottom: 1px solid rgba(219, 228, 236, .92);
            background: rgba(255, 255, 255, .94);
            backdrop-filter: blur(16px);
            box-shadow: 0 3px 13px rgba(22, 58, 85, .035);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .mobile-menu-button {
            width: 42px;
            height: 42px;
            display: none;
            place-items: center;
            border: 1px solid var(--borde);
            border-radius: 12px;
            background: var(--blanco);
            color: var(--azul-principal);
            cursor: pointer;
        }

        .mobile-menu-button svg {
            width: 22px;
            height: 22px;
        }

        .topbar-title {
            min-width: 0;
        }

        .topbar-title h1 {
            margin: 0;
            overflow: hidden;
            color: var(--azul-profundo);
            font-size: 20px;
            letter-spacing: -.35px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .topbar-title p {
            margin: 4px 0 0;
            overflow: hidden;
            color: var(--texto-secundario);
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .current-date {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--texto-secundario);
            font-size: 12px;
            font-weight: 650;
        }

        .current-date svg {
            width: 17px;
            height: 17px;
            color: var(--verde-principal);
        }

        .topbar-logo {
            width: 66px;
            height: 54px;
            object-fit: contain;
        }

        .content-wrapper {
            width: 100%;
            max-width: 1550px;
            margin: 0 auto;
            padding: 28px 30px 45px;
        }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 25px;
            margin-bottom: 25px;
        }

        .page-title h2 {
            margin: 0;
            color: var(--azul-profundo);
            font-size: 28px;
            letter-spacing: -.7px;
        }

        .page-title p {
            margin: 8px 0 0;
            color: var(--texto-secundario);
            font-size: 14px;
            line-height: 1.55;
        }

        .page-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 13px;
            border: 1px solid #cfe9d9;
            border-radius: 999px;
            background: #effaf3;
            color: #197245;
            font-size: 11px;
            font-weight: 750;
        }

        .page-badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--verde-principal);
            box-shadow: 0 0 0 4px rgba(0, 166, 81, .11);
        }

        .alert-message {
            margin-bottom: 22px;
            padding: 14px 16px;
            border-radius: 13px;
            font-size: 13px;
            line-height: 1.5;
        }

        .alert-message.success {
            border: 1px solid #b9e1c8;
            background: #effaf3;
            color: #176c40;
        }

        .alert-message.error {
            border: 1px solid #f0c9c9;
            background: #fff7f7;
            color: #a93636;
        }

        .sidebar-overlay {
            position: fixed;
            z-index: 45;
            inset: 0;
            display: none;
            background: rgba(4, 27, 49, .52);
            backdrop-filter: blur(2px);
        }

        @media (max-width: 1020px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay.visible {
                display: block;
            }

            .main-area {
                margin-left: 0;
            }

            .mobile-menu-button {
                display: grid;
            }
        }

        @media (max-width: 700px) {
            .topbar {
                height: 68px;
                padding: 0 17px;
            }

            .topbar-title h1 {
                font-size: 17px;
            }

            .topbar-title p,
            .current-date {
                display: none;
            }

            .topbar-logo {
                width: 53px;
                height: 45px;
            }

            .content-wrapper {
                padding: 22px 16px 35px;
            }

            .page-header {
                display: block;
            }

            .page-badge {
                margin-top: 15px;
            }

            .page-title h2 {
                font-size: 24px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
@php
    $authUser = auth()->user();
    $authName = $authUser?->usuario ?? 'Auditoría';

    if ($authUser) {
        $personal = \Illuminate\Support\Facades\DB::table('datos_personales')
            ->where('usuario_id', $authUser->id)
            ->first(['nombres', 'apellidos']);

        if ($personal) {
            $fullName = trim(
                ($personal->nombres ?? '')
                . ' '
                . ($personal->apellidos ?? '')
            );

            if ($fullName !== '') {
                $authName = $fullName;
            }
        }
    }

    $routeOrUrl = fn (
        string $name,
        string $fallback
    ) =>
        \Illuminate\Support\Facades\Route::has($name)
            ? route($name)
            : url($fallback);
@endphp

<div class="app-layout">
    <aside
        class="sidebar"
        id="sidebar"
    >
        <div class="sidebar-decoration"></div>

        <header class="sidebar-header">
            <div class="sidebar-logo-box">
                <img
                    src="{{ asset('images/logos/ecosaba.png') }}"
                    alt="Ecosaba MICOOPE"
                    class="sidebar-logo"
                >
            </div>

            <div class="sidebar-system-name">
                <strong>Sistema de Arqueos</strong>
                <span>Agentes MICOOPE</span>
            </div>
        </header>

        <div class="sidebar-user">
            <span class="sidebar-user-avatar">
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

            <div class="sidebar-user-info">
                <strong>
                    {{ $authName }}
                </strong>

                <span>
                    Auditoría
                </span>
            </div>
        </div>

        <nav class="sidebar-navigation">
            <p class="navigation-label">
                General
            </p>

            <div class="navigation-list">
                <a
                    href="{{ $routeOrUrl(
                        'auditoria.dashboard',
                        '/auditoria/dashboard'
                    ) }}"
                    class="navigation-link {{
                        request()->routeIs('auditoria.dashboard')
                            ? 'active'
                            : ''
                    }}"
                >
                    <span class="navigation-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                    </span>

                    Dashboard
                </a>

                <a
                    href="{{ $routeOrUrl(
                        'auditoria.cumplimientos.index',
                        '/auditoria/cumplimientos'
                    ) }}"
                    class="navigation-link {{
                        request()->routeIs(
                            'auditoria.cumplimientos.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <span class="navigation-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M16 3v4"/>
                            <path d="M8 3v4"/>
                            <path d="M3 10h18"/>
                            <path d="m9 16 2 2 4-4"/>
                        </svg>
                    </span>

                    Cumplimientos
                </a>
            </div>

            <p class="navigation-label">
                Arqueos de Auditoría
            </p>

            <div class="navigation-list">
                <a
                    href="{{ $routeOrUrl(
                        'auditoria.arqueos.create',
                        '/auditoria/arqueos/crear'
                    ) }}"
                    class="navigation-link {{
                        request()->routeIs(
                            'auditoria.arqueos.create'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <span class="navigation-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M6 2h9l4 4v16H6z"/>
                            <path d="M14 2v5h5"/>
                            <path d="M12 11v6"/>
                            <path d="M9 14h6"/>
                        </svg>
                    </span>

                    Realizar Arqueo
                </a>

                <a
                    href="{{ $routeOrUrl(
                        'auditoria.arqueos.index',
                        '/auditoria/arqueos'
                    ) }}"
                    class="navigation-link {{
                        request()->routeIs(
                            'auditoria.arqueos.index'
                        )
                        || request()->routeIs(
                            'auditoria.arqueos.show'
                        )
                        || request()->routeIs(
                            'auditoria.arqueos.imprimir'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <span class="navigation-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M6 2h9l4 4v16H6z"/>
                            <path d="M14 2v5h5"/>
                            <path d="M9 13h6"/>
                            <path d="M9 17h6"/>
                        </svg>
                    </span>

                    Historial
                </a>
            </div>

            <p class="navigation-label">
                Análisis
            </p>

            <div class="navigation-list">
                <a
                    href="{{ $routeOrUrl(
                        'auditoria.reportes.index',
                        '/auditoria/reportes'
                    ) }}"
                    class="navigation-link {{
                        request()->routeIs(
                            'auditoria.reportes.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <span class="navigation-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M4 19h16"/>
                            <path d="M6 16V9"/>
                            <path d="M12 16V5"/>
                            <path d="M18 16v-4"/>
                        </svg>
                    </span>

                    Reportes
                </a>
            </div>

            <p class="navigation-label">
                Cuenta
            </p>

            <div class="navigation-list">
                <a
                    href="{{ $routeOrUrl(
                        'auditoria.perfil.index',
                        '/auditoria/perfil'
                    ) }}"
                    class="navigation-link {{
                        request()->routeIs(
                            'auditoria.perfil.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <span class="navigation-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21a8 8 0 0 1 16 0"/>
                        </svg>
                    </span>

                    Perfil
                </a>

                <a
                    href="{{ url('/cambiar-password') }}"
                    class="navigation-link {{
                        request()->is(
                            'cambiar-password'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <span class="navigation-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect x="5" y="10" width="14" height="11" rx="2"/>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                            <circle cx="12" cy="15" r="1"/>
                        </svg>
                    </span>

                    Cambiar Contraseña
                </a>
            </div>
        </nav>

        <footer class="sidebar-footer">
            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="logout-button"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M10 17l5-5-5-5"/>
                        <path d="M15 12H3"/>
                        <path d="M21 19V5a2 2 0 0 0-2-2h-6"/>
                    </svg>

                    Cerrar sesión
                </button>
            </form>
        </footer>
    </aside>

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>

    <main class="main-area">
        <header class="topbar">
            <div class="topbar-left">
                <button
                    type="button"
                    class="mobile-menu-button"
                    id="mobileMenuButton"
                    aria-label="Abrir menú"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    >
                        <path d="M4 6h16"/>
                        <path d="M4 12h16"/>
                        <path d="M4 18h16"/>
                    </svg>
                </button>

                <div class="topbar-title">
                    <h1>
                        @yield(
                            'module-title',
                            'Auditoría'
                        )
                    </h1>

                    <p>
                        Sistema de Arqueos para Agentes MICOOPE
                    </p>
                </div>
            </div>

            <div class="topbar-right">
                <div class="current-date">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M16 3v4"/>
                        <path d="M8 3v4"/>
                        <path d="M3 10h18"/>
                    </svg>

                    {{ now()->locale('es')->translatedFormat(
                        'd \d\e F \d\e Y'
                    ) }}
                </div>

                <img
                    src="{{ asset('images/logos/agentes-micoope.png') }}"
                    alt="Agentes MICOOPE"
                    class="topbar-logo"
                >
            </div>
        </header>

        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>
</div>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const sidebar =
                document.getElementById(
                    'sidebar'
                );

            const overlay =
                document.getElementById(
                    'sidebarOverlay'
                );

            const menuButton =
                document.getElementById(
                    'mobileMenuButton'
                );

            function openSidebar() {
                sidebar?.classList.add(
                    'open'
                );

                overlay?.classList.add(
                    'visible'
                );

                document.body.style.overflow =
                    'hidden';
            }

            function closeSidebar() {
                sidebar?.classList.remove(
                    'open'
                );

                overlay?.classList.remove(
                    'visible'
                );

                document.body.style.overflow =
                    '';
            }

            menuButton?.addEventListener(
                'click',
                openSidebar
            );

            overlay?.addEventListener(
                'click',
                closeSidebar
            );

            window.addEventListener(
                'resize',
                function () {
                    if (
                        window.innerWidth
                        > 1020
                    ) {
                        closeSidebar();
                    }
                }
            );
        }
    );
</script>

@stack('scripts')
</body>
</html>
