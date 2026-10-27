<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Panel del Administrador')
        | Sistema de Arqueos
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .app-layout {
            min-height: 100vh;
        }

        /*
        |--------------------------------------------------------------------------
        | Sidebar
        |--------------------------------------------------------------------------
        */

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
            box-shadow:
                10px 0 30px rgba(4, 36, 70, 0.13);
            transition: transform 0.25s ease;
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
            border: 52px solid rgba(255, 255, 255, 0.04);
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
            background: rgba(255, 255, 255, 0.035);
        }

        .sidebar-header {
            position: relative;
            z-index: 2;
            padding: 24px 22px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
        }

        .sidebar-logo-box {
            display: flex;
            justify-content: center;
            padding: 10px 13px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.14);
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
            letter-spacing: 0.1px;
        }

        .sidebar-system-name span {
            display: block;
            margin-top: 5px;
            color: rgba(255, 255, 255, 0.60);
            font-size: 11px;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .sidebar-user {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 19px 16px 4px;
            padding: 13px;
            border: 1px solid rgba(255, 255, 255, 0.11);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.08);
        }

        .sidebar-user-avatar {
            flex: 0 0 auto;
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.14);
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
            color: rgba(255, 255, 255, 0.57);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .sidebar-navigation {
            position: relative;
            z-index: 2;
            flex: 1;
            padding: 18px 14px 22px;
        }

        .navigation-label {
            margin: 17px 11px 10px;
            color: rgba(255, 255, 255, 0.43);
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
            color: rgba(255, 255, 255, 0.73);
            font-size: 13px;
            font-weight: 680;
            transition:
                color 0.2s ease,
                background 0.2s ease,
                transform 0.2s ease;
        }

        .navigation-link:hover {
            transform: translateX(3px);
            background: rgba(255, 255, 255, 0.09);
            color: var(--blanco);
        }

        .navigation-link.active {
            background:
                linear-gradient(
                    135deg,
                    rgba(255, 255, 255, 0.18),
                    rgba(255, 255, 255, 0.10)
                );
            color: var(--blanco);
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.10);
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
            background: rgba(255, 255, 255, 0.08);
        }

        .navigation-link.active .navigation-icon {
            background: rgba(112, 223, 159, 0.16);
            color: #7be4aa;
        }

        .navigation-icon svg {
            width: 19px;
            height: 19px;
        }

        .sidebar-footer {
            position: relative;
            z-index: 2;
            padding: 17px 18px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.09);
        }

        .logout-button {
            width: 100%;
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            border: 1px solid rgba(255, 255, 255, 0.13);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.07);
            color: rgba(255, 255, 255, 0.78);
            font-size: 12px;
            font-weight: 750;
            cursor: pointer;
            transition:
                background 0.2s ease,
                color 0.2s ease;
        }

        .logout-button:hover {
            background: rgba(255, 255, 255, 0.14);
            color: var(--blanco);
        }

        .logout-button svg {
            width: 18px;
            height: 18px;
        }

        /*
        |--------------------------------------------------------------------------
        | Main
        |--------------------------------------------------------------------------
        */

        .main-area {
            min-height: 100vh;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.25s ease;
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
            border-bottom: 1px solid rgba(219, 228, 236, 0.92);
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(16px);
            box-shadow: 0 3px 13px rgba(22, 58, 85, 0.035);
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
            letter-spacing: -0.35px;
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

        /*
        |--------------------------------------------------------------------------
        | Shared content
        |--------------------------------------------------------------------------
        */

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
            letter-spacing: -0.7px;
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
            box-shadow: 0 0 0 4px rgba(0, 166, 81, 0.11);
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

        .alert-message.warning {
            border: 1px solid #f0d9a4;
            background: #fff9e9;
            color: #8a6511;
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
            background: rgba(4, 27, 49, 0.52);
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
    $authName = $authUser?->usuario ?? 'Administrador';

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
                <strong>
                    Sistema de Arqueos
                </strong>

                <span>
                    Agentes MICOOPE
                </span>
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
                    Administrador
                </span>
            </div>
        </div>

        <nav class="sidebar-navigation">
            <p class="navigation-label">
                Menú principal
            </p>

            <div class="navigation-list">
                <a
                    href="{{ route('administrador.dashboard') }}"
                    class="navigation-link {{ request()->routeIs('administrador.dashboard') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                    </span>
                    Dashboard
                </a>

                <a
                    href="{{ route('administrador.cumplimientos.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.cumplimientos.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M16 3v4"/>
                            <path d="M8 3v4"/>
                            <path d="M3 10h18"/>
                            <path d="m9 16 2 2 4-4"/>
                        </svg>
                    </span>
                    Cumplimiento de Arqueos
                </a>
            </div>

            <p class="navigation-label">
                Administración
            </p>

            <div class="navigation-list">
                <a
                    href="{{ route('administrador.usuarios.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.usuarios.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M19 8v6"/>
                            <path d="M22 11h-6"/>
                        </svg>
                    </span>
                    Usuarios
                </a>

                <a
                    href="{{ route('administrador.agentes.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.agentes.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/>
                            <path d="M5 21V7l7-4 7 4v14"/>
                            <path d="M9 21v-6h6v6"/>
                            <path d="M8 10h.01"/>
                            <path d="M12 10h.01"/>
                            <path d="M16 10h.01"/>
                        </svg>
                    </span>
                    Agentes
                </a>
            </div>

            <p class="navigation-label">
                Arqueos
            </p>

            <div class="navigation-list">
                <a
                    href="{{ route('administrador.arqueos.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.arqueos.index', 'administrador.arqueos.show', 'administrador.arqueos.imprimir') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M7 9h10"/>
                            <path d="M7 13h5"/>
                            <path d="M17 13h.01"/>
                        </svg>
                    </span>
                    Todos los Arqueos
                </a>

                <a
                    href="{{ route('administrador.arqueos-agentes.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.arqueos-agentes.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="8" r="4"/>
                            <path d="M3 21a6 6 0 0 1 12 0"/>
                            <path d="M16 11h5"/>
                            <path d="M18.5 8.5v5"/>
                        </svg>
                    </span>
                    Arqueos de Agentes
                </a>

                <a
                    href="{{ route('administrador.arqueos-promotores.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.arqueos-promotores.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="m16 11 2 2 4-4"/>
                        </svg>
                    </span>
                    Arqueos de Promotores
                </a>

                <a
                    href="{{ route('administrador.arqueos-anulados.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.arqueos-anulados.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="m6.5 6.5 11 11"/>
                        </svg>
                    </span>
                    Arqueos Anulados
                </a>
            </div>

            <p class="navigation-label">
                Auditoría y control
            </p>

            <div class="navigation-list">
                <a
                    href="{{ route('administrador.auditoria-agentes.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.auditoria-agentes.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 4h18v16H3z"/>
                            <path d="M7 8h10"/>
                            <path d="M7 12h6"/>
                            <path d="m15 16 2 2 4-4"/>
                        </svg>
                    </span>
                    Auditoría de Agentes
                </a>

                <a
                    href="{{ route('administrador.auditoria-sistema.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.auditoria-sistema.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3 4 6v6c0 5 3.4 8.5 8 9 4.6-.5 8-4 8-9V6l-8-3Z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </span>
                    Auditoría del Sistema
                </a>

                <a
                    href="{{ route('administrador.reportes.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.reportes.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19V9"/>
                            <path d="M10 19V5"/>
                            <path d="M16 19v-7"/>
                            <path d="M22 19H2"/>
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
                    href="{{ route('administrador.perfil.index') }}"
                    class="navigation-link {{ request()->routeIs('administrador.perfil.*') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21a8 8 0 0 1 16 0"/>
                        </svg>
                    </span>
                    Perfil
                </a>

                <a
                    href="{{ route('password.cambiar') }}"
                    class="navigation-link {{ request()->routeIs('password.cambiar') ? 'active' : '' }}"
                >
                    <span class="navigation-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="10" width="14" height="10" rx="2"/>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                            <path d="M12 14v2"/>
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
                        @yield('module-title', 'Panel del Administrador')
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

                    {{ now()->locale('es')->translatedFormat('d \d\e F \d\e Y') }}
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
        const sidebar =
            document.getElementById('sidebar');

        const overlay =
            document.getElementById('sidebarOverlay');

        const menuButton =
            document.getElementById('mobileMenuButton');

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

        menuButton?.addEventListener(
            'click',
            openSidebar
        );

        overlay?.addEventListener(
            'click',
            closeSidebar
        );

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
