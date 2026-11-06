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
                    Agentes MICOOPE y Agencias ECOSABA
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
    @php
        // Se mantiene el acceso a las rutas existentes y se desactivan las nuevas aún no registradas.
        $adminMenu = [
            ['titulo' => 'Menú principal', 'opciones' => [
                ['texto' => 'Dashboard principal', 'ruta' => 'administrador.dashboard', 'icono' => 'layout-dashboard'],
                ['texto' => 'Cumplimiento de Arqueos de Agentes MICOOPE', 'ruta' => 'administrador.cumplimientos.index', 'icono' => 'calendar-check'],
                ['texto' => 'Cumplimiento de Arqueos de Agencias ECOSABA', 'ruta' => 'administrador.cumplimientos-agencias.index', 'icono' => 'calendar-days'],
            ]],
            ['titulo' => 'Administración', 'opciones' => [
                ['texto' => 'Usuarios', 'ruta' => 'administrador.usuarios.index', 'icono' => 'users'],
                ['texto' => 'Agentes', 'ruta' => 'administrador.agentes.index', 'icono' => 'building-2'],
                ['texto' => 'Agencias', 'ruta' => 'administrador.agencias.index', 'icono' => 'landmark'],
            ]],
            ['titulo' => 'Arqueos de Agentes MICOOPE', 'opciones' => [
                ['texto' => 'Todos los arqueos', 'ruta' => 'administrador.arqueos.index', 'icono' => 'files'],
                ['texto' => 'Arqueos de agentes', 'ruta' => 'administrador.arqueos-agentes.index', 'icono' => 'user-round'],
                ['texto' => 'Arqueos de Promotores', 'ruta' => 'administrador.arqueos-promotores.index', 'icono' => 'clipboard-check'],
                ['texto' => 'Arqueos Anulados', 'ruta' => 'administrador.arqueos-anulados.index', 'icono' => 'ban'],
            ]],
            ['titulo' => 'Arqueos de Agencias ECOSABA', 'opciones' => [
                ['texto' => 'Todos los arqueos', 'ruta' => 'administrador.arqueos-agencias.index', 'icono' => 'files'],
                ['texto' => 'Arqueos Ordinarios por Agencia', 'ruta' => 'administrador.arqueos-agencias.ordinarios', 'icono' => 'clipboard-list'],
                ['texto' => 'Arqueos de jefatura de caja', 'ruta' => 'administrador.arqueos-agencias.jefatura', 'icono' => 'shield-check'],
                ['texto' => 'Arqueos Anulados', 'ruta' => 'administrador.arqueos-agencias.anulados', 'icono' => 'ban'],
            ]],
            ['titulo' => 'Auditoría y control', 'opciones' => [
                ['texto' => 'Auditoría de agentes', 'ruta' => 'administrador.auditoria-agentes.index', 'icono' => 'scan-search'],
                ['texto' => 'Auditoría de Agencias', 'ruta' => 'administrador.auditoria-agencias.index', 'icono' => 'clipboard-check'],
                ['texto' => 'Auditoría de Sistema', 'ruta' => 'administrador.auditoria-sistema.index', 'icono' => 'shield'],
            ]],
            ['titulo' => 'Reportes', 'opciones' => [
                ['texto' => 'Reportes de Agentes', 'ruta' => 'administrador.reportes.index', 'icono' => 'chart-column'],
                ['texto' => 'Reportes de Agencias', 'ruta' => 'agencias.reportes.index', 'icono' => 'chart-no-axes-combined'],
            ]],
            ['titulo' => 'Cuenta', 'opciones' => [
                ['texto' => 'Perfil', 'ruta' => 'administrador.perfil.index', 'icono' => 'circle-user-round'],
                ['texto' => 'Cambiar Contraseña', 'ruta' => 'password.cambiar', 'icono' => 'lock-keyhole'],
            ]],
        ];
        $adminIconos = [
            'layout-dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'calendar-check' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18m6 6 2 2 4-4"/>',
            'calendar-days' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18M7 14h2m3 0h2m3 0h1M7 18h2"/>',
            'users' => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M17 4a4 4 0 0 1 0 8m2 4a6 6 0 0 1 3 5"/>',
            'building-2' => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>',
            'landmark' => '<path d="m3 9 9-6 9 6M4 10h16M6 10v9m4-9v9m4-9v9m4-9v9M3 21h18"/>',
            'files' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 9h8M8 13h8"/>',
            'user-round' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'clipboard-check' => '<rect x="5" y="4" width="14" height="18" rx="2"/><path d="M9 4V2h6v2m-6 10 2 2 4-4"/>',
            'ban' => '<circle cx="12" cy="12" r="9"/><path d="m6 6 12 12"/>',
            'clipboard-list' => '<rect x="5" y="4" width="14" height="18" rx="2"/><path d="M9 4V2h6v2M9 10h6M9 14h6M9 18h6"/>',
            'shield-check' => '<path d="M12 3 4 6v6c0 5 3 8 8 9 5-1 8-4 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
            'scan-search' => '<path d="M4 8V4h4m8 0h4v4M4 16v4h4m8 0h4v-4"/><circle cx="11" cy="11" r="4"/><path d="m14 14 3 3"/>',
            'shield' => '<path d="M12 3 4 6v6c0 5 3 8 8 9 5-1 8-4 8-9V6l-8-3Z"/>',
            'chart-column' => '<path d="M3 21h18M6 18V9m6 9V4m6 14v-7"/>',
            'chart-no-axes-combined' => '<path d="M3 21h18M5 17V9m6 8V5m6 12v-6M4 7l6-4 5 4 5-3"/>',
            'circle-user-round' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'lock-keyhole' => '<rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v2"/>',
        ];
    @endphp
    @foreach ($adminMenu as $grupo)
        <p class="navigation-label">{{ $grupo['titulo'] }}</p>
        <div class="navigation-list">
            @foreach ($grupo['opciones'] as $opcion)
                @php
                    $existeRuta = \Illuminate\Support\Facades\Route::has($opcion['ruta']);
                    $rutaBase = preg_replace('/\.index$/', '', $opcion['ruta']);
                    $activo = request()->routeIs($opcion['ruta']) || request()->routeIs($rutaBase . '.*');
                @endphp
                @if ($existeRuta)
                    <a href="{{ route($opcion['ruta']) }}" class="navigation-link {{ $activo ? 'active' : '' }}">
                        <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $adminIconos[$opcion['icono']] !!}</svg></span>
                        <span>{{ $opcion['texto'] }}</span>
                    </a>
                @else
                    <span class="navigation-link navigation-link-pending" title="Módulo pendiente de implementación">
                        <span class="navigation-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $adminIconos[$opcion['icono']] !!}</svg></span>
                        <span>{{ $opcion['texto'] }}</span>
                    </span>
                @endif
            @endforeach
        </div>
    @endforeach
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
                        Sistema Integral de Arqueos y Control de Efectivo
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