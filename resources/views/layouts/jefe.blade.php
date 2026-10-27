<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Jefe de Agentes') | Sistema de Arqueos
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --azul-principal: #164c96;
            --azul-secundario: #1c64b5;
            --azul-oscuro: #0b315f;
            --azul-profundo: #06284f;
            --verde-principal: #00a651;
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

        * { box-sizing: border-box; }

        html,
        body { min-height: 100%; }

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
                    #06284f,
                    #164c96 62%,
                    #0e5f79
                );
            color: #ffffff;
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
        }

        .sidebar-system-name span {
            display: block;
            margin-top: 5px;
            color: rgba(255, 255, 255, .60);
            font-size: 10px;
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
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: rgba(255, 255, 255, .14);
            color: #7be4aa;
        }

        .sidebar-user-info {
            min-width: 0;
        }

        .sidebar-user-info strong {
            display: block;
            overflow: hidden;
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
            text-transform: uppercase;
        }

        .sidebar-navigation {
            flex: 1;
            padding: 18px 14px 22px;
        }

        .navigation-label {
            margin: 17px 11px 9px;
            color: rgba(255, 255, 255, .43);
            font-size: 9px;
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
        }

        .navigation-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 13px;
            min-height: 51px;
            padding: 11px 14px;
            border-radius: 12px;
            color: rgba(255, 255, 255, .74);
            font-size: 13px;
            font-weight: 680;
            transition: .2s ease;
        }

        .navigation-link:hover {
            transform: translateX(3px);
            background: rgba(255, 255, 255, .09);
            color: #ffffff;
        }

        .navigation-link.active {
            background: linear-gradient(135deg, rgba(255, 255, 255, .18), rgba(255, 255, 255, .10));
            color: #ffffff;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .10);
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
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: rgba(255, 255, 255, .08);
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 45px;
            border: 1px solid rgba(255, 255, 255, .13);
            border-radius: 12px;
            background: rgba(255, 255, 255, .07);
            color: rgba(255, 255, 255, .80);
            font-size: 12px;
            font-weight: 750;
            cursor: pointer;
        }

        .logout-button svg {
            width: 18px;
            height: 18px;
        }

        .main-area {
            min-height: 100vh;
            margin-left: var(--sidebar-width);
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
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .mobile-menu-button {
            width: 42px;
            height: 42px;
            display: none;
            place-items: center;
            border: 1px solid var(--borde);
            border-radius: 12px;
            background: #ffffff;
            color: var(--azul-principal);
            cursor: pointer;
        }

        .mobile-menu-button svg {
            width: 22px;
            height: 22px;
        }

        .topbar-title h1 {
            margin: 0;
            color: var(--azul-profundo);
            font-size: 20px;
        }

        .topbar-title p {
            margin: 4px 0 0;
            color: var(--texto-secundario);
            font-size: 11px;
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
        }

        .page-title p {
            margin: 8px 0 0;
            color: var(--texto-secundario);
            font-size: 14px;
            line-height: 1.55;
        }

        .alert-message {
            margin-bottom: 22px;
            padding: 14px 16px;
            border-radius: 13px;
            font-size: 13px;
        }

        .alert-message.success {
            border: 1px solid #b9e1c8;
            background: #effaf3;
            color: #176c40;
        }

        .alert-message.warning {
            border: 1px solid #f0d9a4;
            background: #fff9e9;
            color: #8a6511;
        }

        .sidebar-overlay {
            position: fixed;
            z-index: 45;
            inset: 0;
            display: none;
            background: rgba(4, 27, 49, .52);
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

            .logout-button svg {
            width: 18px;
            height: 18px;
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
                padding: 0 17px;
            }

            .topbar-title p,
            .current-date {
                display: none;
            }

            .content-wrapper {
                padding: 22px 16px 35px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
<div class="app-layout">
    <aside class="sidebar" id="sidebar">
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
                >
                    <path d="M20 21a8 8 0 0 0-16 0"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </span>

            <div class="sidebar-user-info">
                <strong>{{ auth()->user()->nombre_completo }}</strong>
                <span>Jefe de Agentes</span>
            </div>
        </div>

        <nav class="sidebar-navigation">
            <p class="navigation-label">Supervisión</p>

            <div class="navigation-list">
                <a
                    href="{{ route('jefe.dashboard') }}"
                    class="navigation-link {{
                        request()->routeIs('jefe.dashboard')
                            ? 'active'
                            : ''
                    }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                    </span>
                    Dashboard
                </a>

                <a href="{{ url('/jefe-agentes/estado-arqueos') }}" class="navigation-link {{ request()->is('jefe-agentes/estado-arqueos*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><path d="M6 16V9"/><path d="M12 16V5"/><path d="M18 16v-4"/></svg></span>
                    Estado de Arqueos
                </a>
            </div>

            <p class="navigation-label">Agentes</p>

            <div class="navigation-list">
                <a href="{{ url('/jefe-agentes/agentes') }}" class="navigation-link {{ request()->is('jefe-agentes/agentes*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg></span>
                    Listado de Agentes
                </a>

                <a href="{{ url('/jefe-agentes/arqueos-agentes') }}" class="navigation-link {{ request()->is('jefe-agentes/arqueos-agentes*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l4 4v16H6z"/><path d="M14 2v5h5"/><path d="M9 13h6"/><path d="M9 17h6"/></svg></span>
                    Ver Arqueos por Agente
                </a>

                <a href="{{ url('/jefe-agentes/agentes-ruta') }}" class="navigation-link {{ request()->is('jefe-agentes/agentes-ruta*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19c4-8 12-8 16-14"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="5" r="2"/></svg></span>
                    Agentes por Ruta
                </a>

                <a href="{{ url('/jefe-agentes/agentes-region') }}" class="navigation-link {{ request()->is('jefe-agentes/agentes-region*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18"/><path d="M12 3a15 15 0 0 0 0 18"/></svg></span>
                    Agentes por Región
                </a>
            </div>

            <p class="navigation-label">Promotores</p>

            <div class="navigation-list">
                <a href="{{ url('/jefe-agentes/promotores') }}" class="navigation-link {{ request()->is('jefe-agentes/promotores*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M17 11h5"/><path d="M19.5 8.5v5"/></svg></span>
                    Listado de Promotores
                </a>

                <a href="{{ url('/jefe-agentes/arqueos-promotores') }}" class="navigation-link {{ request()->is('jefe-agentes/arqueos-promotores*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l4 4v16H6z"/><path d="M14 2v5h5"/><path d="M9 13h6"/><path d="M9 17h6"/></svg></span>
                    Ver Arqueos por Promotor
                </a>

                <a href="{{ url('/jefe-agentes/rutas-promotores') }}" class="navigation-link {{ request()->is('jefe-agentes/rutas-promotores*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19c4-8 12-8 16-14"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="5" r="2"/></svg></span>
                    Rutas Asignadas
                </a>
            </div>

            <p class="navigation-label">Rutas y Regiones</p>

            <div class="navigation-list">
                <a href="{{ url('/jefe-agentes/rutas') }}" class="navigation-link {{ request()->is('jefe-agentes/rutas*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19c4-8 12-8 16-14"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="5" r="2"/></svg></span>
                    Gestión de Rutas
                </a>

                <a href="{{ url('/jefe-agentes/regiones') }}" class="navigation-link {{ request()->is('jefe-agentes/regiones*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18"/><path d="M12 3a15 15 0 0 0 0 18"/></svg></span>
                    Gestión de Regiones
                </a>
            </div>

            <p class="navigation-label">Arqueos</p>

            <div class="navigation-list">
                <a
                    href="{{ route('jefe.arqueos.index') }}"
                    class="navigation-link {{ request()->routeIs('jefe.arqueos.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l4 4v16H6z"/><path d="M14 2v5h5"/><path d="M9 13h6"/><path d="M9 17h6"/></svg></span>
                    Ver Arqueos
                </a>

                <a href="{{ url('/jefe-agentes/certificaciones') }}" class="navigation-link {{ request()->is('jefe-agentes/certificaciones*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg></span>
                    Certificar Arqueos
                </a>

                <a href="{{ url('/jefe-agentes/anulaciones') }}" class="navigation-link {{ request()->is('jefe-agentes/anulaciones*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6"/><path d="m15 9-6 6"/></svg></span>
                    Anular Arqueos
                </a>

                <a href="{{ url('/jefe-agentes/habilitaciones-atrasadas') }}" class="navigation-link {{ request()->is('jefe-agentes/habilitaciones-atrasadas*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M3 10h18"/><path d="M12 14v4"/><path d="M10 16h4"/></svg></span>
                    Habilitar Arqueo Atrasado
                </a>
            </div>

            <p class="navigation-label">Sistema</p>

            <div class="navigation-list">
                <a href="{{ url('/jefe-agentes/reportes') }}" class="navigation-link {{ request()->is('jefe-agentes/reportes*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><path d="M6 16V9"/><path d="M12 16V5"/><path d="M18 16v-4"/></svg></span>
                    Reportes
                </a>

                <a href="{{ url('/jefe-agentes/perfil') }}" class="navigation-link {{ request()->is('jefe-agentes/perfil*') ? 'active' : '' }}">
                    <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></span>
                    Perfil
                </a>
            </div>
        </nav>

        <footer class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="logout-button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 17l5-5-5-5"/>
                        <path d="M15 12H3"/>
                        <path d="M21 19V5a2 2 0 0 0-2-2h-6"/>
                    </svg>
                    Cerrar sesión
                </button>
            </form>
        </footer>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-area">
        <header class="topbar">
            <div class="topbar-left">
                <button
                    type="button"
                    class="mobile-menu-button"
                    id="mobileMenuButton"
                    aria-label="Abrir menú"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M4 6h16"/>
                        <path d="M4 12h16"/>
                        <path d="M4 18h16"/>
                    </svg>
                </button>

                <div class="topbar-title">
                    <h1>@yield('module-title', 'Jefe de Agentes')</h1>
                    <p>Sistema de Arqueos para Agentes MICOOPE</p>
                </div>
            </div>

            <div class="topbar-right">
                <div class="current-date">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
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
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const menuButton = document.getElementById('mobileMenuButton');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('visible');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('visible');
            document.body.style.overflow = '';
        }

        menuButton?.addEventListener('click', openSidebar);
        overlay?.addEventListener('click', closeSidebar);

        window.addEventListener('resize', function () {
            if (window.innerWidth > 1020) {
                closeSidebar();
            }
        });
    });
</script>

@stack('scripts')
</body>
</html>
