<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Receptor') | Sistema de Arqueos y Control de Efectivo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{--azul-principal:#164c96;--azul-profundo:#06284f;--verde-principal:#00a651;--blanco:#fff;--fondo:#f3f6f9;--borde:#dfe7ee;--texto:#19344d;--texto-secundario:#6d7d8a;--sidebar-width:278px}
        *{box-sizing:border-box}
        html,body{min-height:100%}
        body{margin:0;min-height:100vh;overflow-x:hidden;background:var(--fondo);color:var(--texto);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
        a{color:inherit;text-decoration:none}button,input,select,textarea{font:inherit}
        .app-layout{min-height:100vh}
        .sidebar{position:fixed;z-index:50;inset:0 auto 0 0;width:var(--sidebar-width);height:100vh;display:flex;flex-direction:column;overflow-y:auto;background:linear-gradient(165deg,#06284f,#164c96 62%,#0e5f79);color:#fff;box-shadow:10px 0 30px rgba(4,36,70,.13);transition:transform .25s ease}
        .sidebar-decoration{position:absolute;inset:0;overflow:hidden;pointer-events:none}
        .sidebar-decoration:before{content:"";position:absolute;width:280px;height:280px;top:-170px;right:-150px;border:52px solid rgba(255,255,255,.04);border-radius:50%}
        .sidebar-decoration:after{content:"";position:absolute;width:230px;height:230px;bottom:-150px;left:-110px;border-radius:50%;background:rgba(255,255,255,.035)}
        .sidebar-header,.sidebar-user,.sidebar-navigation,.sidebar-footer{position:relative;z-index:2}
        .sidebar-header{padding:24px 22px 20px;border-bottom:1px solid rgba(255,255,255,.10)}
        .sidebar-logo-box{display:flex;justify-content:center;padding:10px 13px;border-radius:15px;background:rgba(255,255,255,.97);box-shadow:0 12px 26px rgba(0,0,0,.14)}
        .sidebar-logo{display:block;width:100%;max-width:190px;height:62px;object-fit:contain}
        .sidebar-system-name{margin-top:17px;text-align:center}
        .sidebar-system-name strong{display:block;font-size:14px;line-height:1.4}
        .sidebar-system-name span{display:block;margin-top:5px;color:rgba(255,255,255,.60);font-size:10px;letter-spacing:.7px;text-transform:uppercase}
        .sidebar-user{display:flex;align-items:center;gap:12px;margin:19px 16px 4px;padding:13px;border:1px solid rgba(255,255,255,.11);border-radius:15px;background:rgba(255,255,255,.08)}
        .sidebar-user-avatar{flex:0 0 auto;width:43px;height:43px;display:grid;place-items:center;border-radius:13px;background:rgba(255,255,255,.14);color:#7be4aa}
        .sidebar-user-avatar svg{width:23px;height:23px}
        .sidebar-user-info{min-width:0}.sidebar-user-info strong{display:block;overflow:hidden;font-size:13px;text-overflow:ellipsis;white-space:nowrap}.sidebar-user-info span{display:block;margin-top:4px;color:rgba(255,255,255,.57);font-size:10px;font-weight:700;letter-spacing:.7px;text-transform:uppercase}
        .sidebar-navigation{flex:1;padding:18px 14px 22px}.navigation-label{margin:17px 11px 9px;color:rgba(255,255,255,.43);font-size:10px;font-weight:800;letter-spacing:1px;text-transform:uppercase}.navigation-label:first-child{margin-top:0}.navigation-list{display:grid;gap:7px}
        .navigation-link{position:relative;display:flex;align-items:center;gap:13px;min-height:51px;padding:11px 14px;overflow:hidden;border-radius:13px;color:rgba(255,255,255,.73);font-size:13px;font-weight:680;transition:color .2s ease,background .2s ease,transform .2s ease}
        .navigation-link.is-unavailable{opacity:.65;cursor:not-allowed}.navigation-note{margin-left:auto;font-size:10px;color:#d5e1ec}.navigation-link:hover{transform:translateX(3px);background:rgba(255,255,255,.09);color:#fff}.navigation-link.active{background:linear-gradient(135deg,rgba(255,255,255,.18),rgba(255,255,255,.10));color:#fff;box-shadow:inset 0 0 0 1px rgba(255,255,255,.10)}.navigation-link.active:before{content:"";position:absolute;top:10px;bottom:10px;left:0;width:4px;border-radius:0 5px 5px 0;background:#70df9f}
        .navigation-icon{flex:0 0 auto;width:34px;height:34px;display:grid;place-items:center;border-radius:10px;background:rgba(255,255,255,.08)}.navigation-icon svg{width:19px;height:19px;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}.navigation-link.active .navigation-icon{background:rgba(112,223,159,.16);color:#7be4aa}
        .sidebar-footer{padding:17px 18px 20px;border-top:1px solid rgba(255,255,255,.09)}.logout-button{width:100%;min-height:45px;display:flex;align-items:center;justify-content:center;gap:9px;border:1px solid rgba(255,255,255,.13);border-radius:12px;background:rgba(255,255,255,.07);color:rgba(255,255,255,.78);font-size:12px;font-weight:750;cursor:pointer}.logout-button:hover{background:rgba(255,255,255,.14);color:#fff}.logout-button svg{width:18px;height:18px}
        .main-area{min-height:100vh;margin-left:var(--sidebar-width)}.content-wrapper{width:100%;max-width:1550px;margin:0 auto;padding:30px 30px 45px}
        .page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:25px;margin-bottom:25px}.page-title h2{margin:0;color:var(--azul-profundo);font-size:28px;letter-spacing:-.7px}.page-title p{margin:8px 0 0;color:var(--texto-secundario);font-size:14px;line-height:1.55}
        .alert-message{margin-bottom:22px;padding:14px 16px;border-radius:13px;font-size:13px;line-height:1.5}.alert-message.success{border:1px solid #b9e1c8;background:#effaf3;color:#176c40}.alert-message.error{border:1px solid #f0c9c9;background:#fff7f7;color:#a93636}.alert-message.warning{border:1px solid #f0d9a4;background:#fff9e9;color:#8a6511}
        .sidebar-overlay{position:fixed;z-index:45;inset:0;display:none;background:rgba(4,27,49,.52);backdrop-filter:blur(2px)}
        .mobile-menu-button{display:none;position:fixed;z-index:40;top:14px;left:14px;width:44px;height:44px;place-items:center;border:1px solid var(--borde);border-radius:12px;background:#fff;color:var(--azul-principal);box-shadow:0 5px 18px rgba(4,36,70,.12);cursor:pointer}.mobile-menu-button svg{width:23px;height:23px}
        .sidebar-close-button{display:none;position:absolute;right:8px;top:8px;z-index:5;width:30px;height:30px;border:0;border-radius:8px;background:rgba(0,0,0,.13);color:#fff;cursor:pointer}
        @media(max-width:1020px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.sidebar-overlay.visible{display:block}.main-area{margin-left:0}.mobile-menu-button{display:grid}.sidebar-close-button{display:block}.content-wrapper{padding:75px 24px 35px}}
        @media(max-width:700px){.content-wrapper{padding:75px 16px 35px}.page-header{display:block}.page-title h2{font-size:24px}}
    </style>
    @stack('styles')
</head>
<body>

@php
   $authUser = auth()->user();
   $authName = $authUser?->nombre_completo ?: ($authUser?->usuario ?? 'Receptor');
    $menuGroups = [
       'General' => [
           ['route' => 'receptor.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'active' => 'receptor.dashboard'],
       ],
        'Operaciones de Caja' => [
            ['route' => 'receptor.jornadas.index', 'label' => 'Mis Jornadas', 'icon' => 'calendar', 'active' => 'receptor.jornadas.*'],
            ['route' => 'receptor.arqueos.index', 'label' => 'Arqueo Diario', 'icon' => 'document', 'active' => 'receptor.arqueos.*'],
            ['route' => 'receptor.sorpresivos.index', 'label' => 'Arqueos de Jefatura de Caja', 'icon' => 'document', 'active' => 'receptor.sorpresivos.*'],
            ['route' => 'receptor.auditoria-agencias.index', 'label' => 'Arqueos de Auditoría', 'icon' => 'document', 'active' => 'receptor.auditoria-agencias.*'],
            ['route' => 'receptor.cumplimiento-agencias.index', 'label' => 'Cumplimiento de Arqueos', 'icon' => 'calendar', 'active' => 'receptor.cumplimiento-agencias.*'],
        ],
        'Cuenta' => [
            ['route' => 'agencias.perfil.index', 'label' => 'Mi Perfil', 'icon' => 'user', 'active' => 'agencias.perfil.*'],
            ['route' => 'password.cambiar', 'label' => 'Cambiar Contraseña', 'icon' => 'lock', 'active' => 'password.cambiar'],
        ],
    ];
@endphp

<div class="app-layout">
    <aside class="sidebar" id="sidebar" aria-label="Menú del receptor">
        <div class="sidebar-decoration"></div>
        <button type="button" class="sidebar-close-button" id="sidebarCloseButton" aria-label="Cerrar menú">&times;</button>
        <header class="sidebar-header">
            <div class="sidebar-logo-box">
                <img src="{{ asset('images/logos/ecosaba.png') }}" alt="ECOSABA" class="sidebar-logo">
            </div>
            <div class="sidebar-system-name">
                <strong>Sistema de Arqueos y Control de Efectivo</strong>
                <span>Agencias ECOSABA</span>
            </div>
        </header>
        <div class="sidebar-user">
            <span class="sidebar-user-avatar" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg></span>
            <div class="sidebar-user-info"><strong>{{ $authName }}</strong><span>Receptor</span></div>
        </div>
        <nav class="sidebar-navigation" aria-label="Navegación principal">
           @foreach($menuGroups as $group => $items)
                <p class="navigation-label">{{ $group }}</p>
                <div class="navigation-list">
                    @foreach($items as $item)
                        @php
                            $available = !empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']);
                            $active = $available && request()->routeIs($item['active'] ?? $item['route']);
                        @endphp
                        @if($available)
                            <a href="{{ route($item['route']) }}" class="navigation-link {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif>
                        @else
                            <div class="navigation-link is-unavailable" aria-disabled="true">
                        @endif
                            <span class="navigation-icon" aria-hidden="true">
                                @switch($item['icon'])
                                    @case('dashboard')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>@break
                                    @case('calendar')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>@break
                                    @case('document')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 2h9l4 4v16H6zM14 2v5h5M9 13h6M9 17h6"/></svg>@break
                                    @case('cash')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/></svg>@break
                                    @case('user')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>@break
                                    @case('lock')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>@break
                                @endswitch
                            </span>
                            <span>{{ $item['label'] }}</span>
                            @unless($available)
                                <span class="navigation-note">{{ !empty($item['future']) ? 'Futuro' : 'Pendiente' }}</span>
                            @endunless
                        @if($available)</a>@else</div>@endif
                    @endforeach
                </div>
            @endforeach
        </nav>

        <footer class="sidebar-footer">
            @if(\Illuminate\Support\Facades\Route::has('logout'))
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/></svg>Cerrar sesión</button>
                </form>
            @endif
        </footer>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <button type="button" class="mobile-menu-button" id="mobileMenuButton" aria-label="Abrir menú" aria-expanded="false" aria-controls="sidebar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
    <main class="main-area">
        <div class="content-wrapper">
            @if(session('success'))<div class="alert-message success" role="status">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert-message error" role="alert">{{ session('error') }}</div>@endif
            @if(session('warning'))<div class="alert-message warning" role="status">{{ session('warning') }}</div>@endif
            @yield('content')
        </div>
    </main>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openButton = document.getElementById('mobileMenuButton');
        const closeButton = document.getElementById('sidebarCloseButton');
        if (!sidebar || !overlay || !openButton || !closeButton) return;
        function toggleMenu(open) {
            sidebar.classList.toggle('open', open);
            overlay.classList.toggle('visible', open);
            openButton.setAttribute('aria-expanded', String(open));
            document.body.style.overflow = open ? 'hidden' : '';
        }
        openButton.addEventListener('click', () => toggleMenu(true));
        closeButton.addEventListener('click', () => toggleMenu(false));
        overlay.addEventListener('click', () => toggleMenu(false));
        document.addEventListener('keydown', event => { if (event.key === 'Escape') toggleMenu(false); });
        window.addEventListener('resize', () => { if (window.innerWidth > 1020) toggleMenu(false); });
    });
</script>
@stack('scripts')
</body>
</html>
