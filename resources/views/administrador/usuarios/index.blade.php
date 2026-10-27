@extends('layouts.administrador')

@section('title', 'Usuarios')
@section('module-title', 'Usuarios')

@push('styles')
<style>
    .users-page{max-width:1550px;margin:0 auto}
    .module-heading{display:flex;align-items:center;gap:15px}
    .heading-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .heading-icon svg,.metric-icon svg,.filter-icon svg,.btn svg,.action-btn svg,.empty-icon svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .heading-icon svg{width:26px;height:26px}
    .header-action{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 16px;border-radius:11px;background:var(--azul-principal);color:#fff;font-size:10.5px;font-weight:800;text-decoration:none;box-shadow:0 7px 17px rgba(22,76,150,.15);transition:.2s ease}
    .header-action:hover{transform:translateY(-1px);background:var(--azul-secundario);color:#fff}
    .header-action svg{width:17px;height:17px}
    .metrics-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}
    .metric-card{display:flex;align-items:center;gap:14px;min-height:105px;padding:17px;border:1px solid #e0e8ee;border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(20,57,83,.05)}
    .metric-icon{width:46px;height:46px;flex:0 0 46px;display:flex;align-items:center;justify-content:center;border-radius:13px;color:var(--azul-principal);background:#edf4fb}
    .metric-icon svg{width:21px;height:21px}
    .metric-card.active .metric-icon{color:var(--verde-oscuro);background:#eaf8f1}
    .metric-card.inactive .metric-icon{color:#b13b3b;background:#fff0f0}
    .metric-card.admin .metric-icon{color:#725aa5;background:#f3effb}
    .metric-label{display:block;color:#758697;font-size:8.5px;font-weight:800;text-transform:uppercase}
    .metric-value{display:block;margin-top:6px;color:var(--azul-profundo);font-size:24px;font-weight:800;line-height:1}
    .filters-card,.table-card{border:1px solid #e0e8ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(20,57,83,.05)}
    .filters-card{padding:18px;margin-bottom:20px}
    .filters-title{display:flex;align-items:center;gap:11px;margin-bottom:15px;padding-bottom:14px;border-bottom:1px solid #edf1f4}
    .filter-icon{width:38px;height:38px;flex:0 0 38px;display:flex;align-items:center;justify-content:center;border-radius:11px;color:var(--azul-principal);background:#edf4fb}
    .filter-icon svg{width:18px;height:18px}
    .filters-title h3{margin:0;color:var(--azul-profundo);font-size:14px}.filters-title p{margin:3px 0 0;color:var(--texto-secundario);font-size:10px}
    .filters-grid{display:grid;grid-template-columns:minmax(260px,1fr) 230px 200px;gap:10px}
    .form-control{width:100%;min-height:42px;padding:0 12px;border:1px solid #ced9e1;border-radius:10px;outline:none;background:#fff;color:#30485b;font-size:11px;box-sizing:border-box;transition:.18s ease}
    .form-control:focus{border-color:#82a9cc;box-shadow:0 0 0 3px rgba(22,76,150,.08)}
    .filter-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:12px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:0 14px;border:0;border-radius:10px;font-size:10px;font-weight:800;text-decoration:none;cursor:pointer;transition:.2s ease}
    .btn svg{width:15px;height:15px}.btn-primary{background:var(--azul-principal);color:#fff}.btn-primary:hover{background:var(--azul-secundario);color:#fff}.btn-secondary{background:#edf2f5;color:#3d596f}.btn-secondary:hover{background:#e4ebf0;color:#29485f}
    .table-card{overflow:hidden}.table-header{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:18px 20px;border-bottom:1px solid #edf1f4;background:#fafcfd}
    .table-header h3{margin:0;color:#0a3158;font-size:15px}.table-header p{margin:4px 0 0;color:#82909a;font-size:10px}
    .record-count{padding:6px 10px;border:1px solid #dce6ed;border-radius:999px;background:#fff;color:#607586;font-size:9px;font-weight:800;white-space:nowrap}
    .table-responsive{overflow-x:auto}.users-table{width:100%;min-width:980px;border-collapse:collapse}
    .users-table th,.users-table td{padding:12px 14px;border-bottom:1px solid #edf1f4;text-align:left;vertical-align:middle}
    .users-table th{background:#f7f9fb;color:#687b8b;font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:.025em}
    .users-table td{color:#40596c;font-size:10.5px}.users-table tbody tr:hover{background:#fbfdfe}
    .user-name{display:block;color:#173c5c;font-size:11px;font-weight:800}.user-id{display:block;margin-top:3px;color:#8a99a5;font-size:8.5px}
    .role-badge,.status-badge{display:inline-flex;align-items:center;min-height:26px;padding:0 9px;border-radius:999px;font-size:9px;font-weight:800}
    .role-badge{color:#315f86;background:#edf4fb;border:1px solid #d5e4f0}
    .status-badge{gap:6px;text-transform:uppercase}.status-badge:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
    .status-active{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8}.status-inactive{color:#b13b3b;background:#fff0f0;border:1px solid #f0cccc}
    .actions-cell{white-space:nowrap}.action-group{display:flex;align-items:center;gap:6px}
    .action-btn{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:9px;text-decoration:none;cursor:pointer;transition:.18s ease}
    .action-btn svg{width:16px;height:16px}
    .action-view{color:#31536e;background:#edf2f5;border-color:#e1e8ed}.action-view:hover{background:#e3eaf0;color:#24475f}
    .action-edit{color:#164c96;background:#edf4fb;border-color:#d5e4f0}.action-edit:hover{background:#dfeefa;color:#0b3d76}
    .action-state{color:#8b6500;background:#fff7df;border-color:#f0e2ae}.action-state.activate{color:#08783d;background:#e9f8f0;border-color:#c7ecd8}
    .inline-form{display:inline;margin:0}.empty-state{padding:40px 20px;text-align:center;color:#7a8b98}
    .empty-icon{width:50px;height:50px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;border-radius:14px;background:#f0f4f7;color:#7a8fa0}.empty-icon svg{width:23px;height:23px}
    .empty-state strong{display:block;color:#456074;font-size:12px}.empty-state span{display:block;margin-top:4px;font-size:10px}
    .pagination{padding:16px 18px}
    @media(max-width:1050px){.metrics-grid{grid-template-columns:repeat(2,1fr)}.filters-grid{grid-template-columns:1fr 1fr}.filters-grid .form-control:first-child{grid-column:1/-1}}
    @media(max-width:700px){.metrics-grid,.filters-grid{grid-template-columns:1fr}.filters-grid .form-control:first-child{grid-column:auto}.filter-actions{flex-direction:column}.btn{width:100%}.page-header{align-items:flex-start}.header-action{width:100%}}
</style>
@endpush

@section('content')
<div class="users-page">
    <div class="page-header">
        <div class="page-title module-heading">
            <div class="heading-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="9" cy="8" r="3"></circle>
                    <path d="M3 20v-2a6 6 0 0 1 12 0v2"></path>
                    <circle cx="17" cy="9" r="2"></circle>
                    <path d="M16 14a5 5 0 0 1 5 5v1"></path>
                </svg>
            </div>
            <div>
                <h2>Gestión de Usuarios</h2>
                <p>Administre las cuentas que tienen acceso al Sistema de Arqueos.</p>
            </div>
        </div>

        <a href="{{ route('administrador.usuarios.create') }}" class="header-action">
            <svg viewBox="0 0 24 24">
                <circle cx="9" cy="8" r="3"></circle>
                <path d="M3 20v-2a6 6 0 0 1 12 0v2"></path>
                <path d="M18 8v6M15 11h6"></path>
            </svg>
            Crear Usuario
        </a>
    </div>

    <section class="metrics-grid">
        <article class="metric-card">
            <div class="metric-icon">
                <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 20v-2a6 6 0 0 1 12 0v2"></path><circle cx="17" cy="9" r="2"></circle><path d="M16 14a5 5 0 0 1 5 5v1"></path></svg>
            </div>
            <div><span class="metric-label">Total Usuarios</span><strong class="metric-value">{{ $resumen->total }}</strong></div>
        </article>

        <article class="metric-card active">
            <div class="metric-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m8 12 2.5 2.5L16 9"></path></svg>
            </div>
            <div><span class="metric-label">Activos</span><strong class="metric-value">{{ $resumen->activos }}</strong></div>
        </article>

        <article class="metric-card inactive">
            <div class="metric-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m9 9 6 6M15 9l-6 6"></path></svg>
            </div>
            <div><span class="metric-label">Inactivos</span><strong class="metric-value">{{ $resumen->inactivos }}</strong></div>
        </article>

        <article class="metric-card admin">
            <div class="metric-icon">
                <svg viewBox="0 0 24 24"><path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path><path d="m9 12 2 2 4-4"></path></svg>
            </div>
            <div><span class="metric-label">Administradores</span><strong class="metric-value">{{ $resumen->administradores }}</strong></div>
        </article>
    </section>

    <form method="GET" action="{{ route('administrador.usuarios.index') }}" class="filters-card">
        <div class="filters-title">
            <div class="filter-icon">
                <svg viewBox="0 0 24 24"><path d="M4 5h16l-6 7v5l-4 2v-7L4 5Z"></path></svg>
            </div>
            <div>
                <h3>Filtros de Usuarios</h3>
                <p>Localice cuentas por nombre, usuario, rol o estado.</p>
            </div>
        </div>

        <div class="filters-grid">
            <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Usuario, nombre, apellido o rol" class="form-control">

            <select name="rol_id" class="form-control">
                <option value="">Todos los Roles</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol->id }}" @selected($rolId === (int) $rol->id)>{{ $rol->nombre }}</option>
                @endforeach
            </select>

            <select name="estado" class="form-control">
                <option value="">Todos los Estados</option>
                <option value="ACTIVO" @selected($estado === 'ACTIVO')>Activos</option>
                <option value="INACTIVO" @selected($estado === 'INACTIVO')>Inactivos</option>
            </select>
        </div>

        <div class="filter-actions">
            <a href="{{ route('administrador.usuarios.index') }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24"><path d="M4 4v6h6"></path><path d="M5.5 15a7 7 0 1 0 .5-7"></path></svg>
                Limpiar
            </a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24"><path d="M4 5h16l-6 7v5l-4 2v-7L4 5Z"></path></svg>
                Aplicar Filtros
            </button>
        </div>
    </form>

    <section class="table-card">
        <header class="table-header">
            <div>
                <h3>Usuarios Registrados</h3>
                <p>Cuentas y permisos registrados actualmente en el sistema.</p>
            </div>
            <span class="record-count">{{ $usuarios->total() }} registros</span>
        </header>

        <div class="table-responsive">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        @php
                            $nombre = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? ''));
                        @endphp
                        <tr>
                            <td>
                                <strong class="user-name">{{ $nombre !== '' ? $nombre : 'Sin nombre registrado' }}</strong>
                                <span class="user-id">ID #{{ $usuario->id }}</span>
                            </td>
                            <td><strong>{{ $usuario->usuario }}</strong></td>
                            <td><span class="role-badge">{{ $usuario->rol_nombre }}</span></td>
                            <td>
                                <span class="status-badge {{ $usuario->estado === 'ACTIVO' ? 'status-active' : 'status-inactive' }}">
                                    {{ $usuario->estado }}
                                </span>
                            </td>
                            <td class="actions-cell">
                                <div class="action-group">
                                    <a href="{{ route('administrador.usuarios.show', $usuario->id) }}" class="action-btn action-view" title="Ver usuario" aria-label="Ver usuario">
                                        <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.8"></circle></svg>
                                    </a>

                                    <a href="{{ route('administrador.usuarios.edit', $usuario->id) }}" class="action-btn action-edit" title="Editar usuario" aria-label="Editar usuario">
                                        <svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 10-10-3.5-3.5-10 10L4 20Z"></path><path d="m13.5 7 3.5 3.5"></path></svg>
                                    </a>

                                    @if(auth()->id() !== $usuario->id)
                                        <form method="POST" action="{{ route('administrador.usuarios.estado', $usuario->id) }}" class="inline-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="action-btn action-state {{ $usuario->estado === 'INACTIVO' ? 'activate' : '' }}" title="{{ $usuario->estado === 'ACTIVO' ? 'Desactivar usuario' : 'Activar usuario' }}" aria-label="{{ $usuario->estado === 'ACTIVO' ? 'Desactivar usuario' : 'Activar usuario' }}">
                                                @if($usuario->estado === 'ACTIVO')
                                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M8 12h8"></path></svg>
                                                @else
                                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M8 12h8M12 8v8"></path></svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 20v-2a6 6 0 0 1 12 0v2"></path><path d="M17 11h5"></path></svg>
                                    </div>
                                    <strong>No se encontraron usuarios</strong>
                                    <span>Pruebe modificando los filtros de búsqueda.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usuarios->hasPages())
            <div class="pagination">{{ $usuarios->links() }}</div>
        @endif
    </section>
</div>
@endsection
