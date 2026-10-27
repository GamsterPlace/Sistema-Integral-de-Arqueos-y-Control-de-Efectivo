@extends('layouts.administrador')

@section('title', 'Auditoría del Sistema')
@section('module-title', 'Auditoría del Sistema')

@push('styles')
<style>
    .system-audit-page{max-width:1550px;margin:0 auto}
    .module-heading{display:flex;align-items:center;gap:15px}
    .module-icon{width:54px;height:54px;flex:0 0 54px;display:flex;align-items:center;justify-content:center;border-radius:16px;color:#fff;background:linear-gradient(145deg,var(--azul-principal),var(--azul-profundo));box-shadow:0 10px 24px rgba(22,76,150,.18)}
    .module-icon svg,.summary-icon svg,.filter-icon svg,.panel-icon svg,.action-btn svg,.empty-icon svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
    .module-icon svg{width:26px;height:26px}
    .summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}
    .summary-card{min-height:108px;display:flex;align-items:center;gap:14px;padding:17px;border:1px solid #dfe7ee;border-radius:16px;background:#fff;box-shadow:0 7px 20px rgba(28,64,92,.045)}
    .summary-icon{width:44px;height:44px;flex:0 0 44px;display:flex;align-items:center;justify-content:center;border-radius:13px;color:var(--azul-principal);background:#edf4fb}
    .summary-card.today .summary-icon{color:var(--verde-oscuro);background:#eaf8f1}
    .summary-card.users .summary-icon{color:#725aa5;background:#f3effb}
    .summary-card.modules .summary-icon{color:#9a7100;background:#fff7df}
    .summary-icon svg{width:21px;height:21px}
    .summary-label{display:block;margin-bottom:4px;color:var(--texto-secundario);font-size:10px;font-weight:800;letter-spacing:.03em;text-transform:uppercase}
    .summary-value{display:block;color:var(--azul-profundo);font-size:24px;font-weight:800;line-height:1}
    .filter-panel{margin-bottom:20px;overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(28,64,92,.045)}
    .filter-heading{display:flex;align-items:center;gap:11px;padding:15px 18px;border-bottom:1px solid #e6edf2;background:#fbfcfd}
    .filter-icon,.panel-icon{width:37px;height:37px;flex:0 0 37px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:var(--azul-principal);background:#edf4fb}
    .filter-icon svg,.panel-icon svg{width:18px;height:18px}
    .filter-heading h3,.panel-title h3{margin:0;color:var(--azul-profundo);font-size:15px}
    .filter-heading p,.panel-title p{margin:2px 0 0;color:var(--texto-secundario);font-size:12px}
    .filter-body{padding:17px 18px}
    .filter-grid{display:grid;grid-template-columns:1.35fr repeat(3,1fr);gap:11px}
    .filter-grid-secondary{display:grid;grid-template-columns:1fr 1fr 1fr;gap:11px;margin-top:11px}
    .field-group label{display:block;margin:0 0 6px;color:#536c7f;font-size:10px;font-weight:800;letter-spacing:.025em;text-transform:uppercase}
    .form-control{width:100%;min-height:42px;padding:0 12px;border:1px solid #ced9e1;border-radius:10px;outline:none;background:#fff;color:var(--texto);font:inherit;font-size:12px;transition:.18s ease}
    .form-control:focus{border-color:#82a9cc;box-shadow:0 0 0 3px rgba(22,76,150,.08)}
    .filter-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:14px;padding-top:14px;border-top:1px solid #edf1f4}
    .btn-filter{min-height:39px;display:inline-flex;align-items:center;justify-content:center;padding:0 14px;border:1px solid #d7e1e7;border-radius:10px;background:#f3f6f8;color:#466277;text-decoration:none;font-size:11px;font-weight:800;cursor:pointer}
    .btn-filter.primary{border-color:var(--azul-principal);background:var(--azul-principal);color:#fff}
    .table-panel{overflow:hidden;border:1px solid #dfe7ee;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(28,64,92,.05)}
    .table-panel-header{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:17px 19px;border-bottom:1px solid #e5edf2;background:#fbfcfd}
    .panel-title{display:flex;align-items:center;gap:11px}
    .records-count{padding:6px 10px;border:1px solid #dce6ed;border-radius:999px;background:#fff;color:#5c7487;font-size:11px;font-weight:800;white-space:nowrap}
    .table-responsive{overflow-x:auto}
    .data-table{width:100%;min-width:1250px;border-collapse:collapse}
    .data-table th{padding:12px 13px;border-bottom:1px solid #dfe7ee;background:#f7f9fb;color:#60788b;font-size:10.5px;font-weight:800;letter-spacing:.035em;text-align:left;text-transform:uppercase;white-space:nowrap}
    .data-table td{padding:13px;border-bottom:1px solid #edf1f4;color:var(--texto);font-size:12px;vertical-align:middle}
    .data-table tbody tr:hover{background:#fafcfd}.data-table tbody tr:last-child td{border-bottom:0}
    .date-value{display:block;color:var(--texto);font-weight:700;white-space:nowrap}
    .date-time{display:block;margin-top:2px;color:var(--texto-secundario);font-size:10.5px}
    .user-name{display:block;min-width:145px;font-weight:700}
    .role-badge,.module-badge,.action-badge,.table-badge{display:inline-flex;align-items:center;min-height:26px;padding:0 9px;border-radius:999px;font-size:9.5px;font-weight:800;white-space:nowrap}
    .role-badge{color:#526d82;background:#f1f5f8;border:1px solid #dce5eb}
    .module-badge{color:#164c96;background:#edf4fb;border:1px solid #d5e4f0}
    .action-badge{color:#08783d;background:#e9f8f0;border:1px solid #c7ecd8;text-transform:uppercase}
    .table-badge{color:#725aa5;background:#f3effb;border:1px solid #dfd5f0}
    .record-id{color:var(--azul-principal);font-weight:800}
    .description{display:block;max-width:320px;color:#4a6275;line-height:1.45}
    .actions{white-space:nowrap}
    .action-btn{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #d5dfe5;border-radius:9px;background:#fff;color:#31536e;text-decoration:none;transition:.2s ease}
    .action-btn:hover{transform:translateY(-1px);border-color:#9db6c9;background:#f4f8fb;color:var(--azul-principal);box-shadow:0 6px 14px rgba(24,66,99,.1)}
    .action-btn svg{width:17px;height:17px}
    .empty-state{padding:48px 20px!important;text-align:center}
    .empty-icon{width:52px;height:52px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;border-radius:15px;color:#7890a2;background:#f1f5f8}
    .empty-icon svg{width:23px;height:23px}
    .empty-state strong{display:block;margin-bottom:4px;color:var(--azul-profundo);font-size:14px}
    .empty-state span{color:var(--texto-secundario);font-size:12px}
    .pagination-wrapper{
    padding:16px 18px;
    border-top:1px solid #edf1f4;
    background:#fbfcfd;
}

.audit-pagination{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:18px;
    width:100%;
}

.audit-pagination-info{
    color:#60788b;
    font-size:11px;
    font-weight:600;
    white-space:nowrap;
}

.audit-pagination-info strong{
    color:var(--azul-profundo);
    font-weight:800;
}

.audit-pagination-nav{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:6px;
}

.audit-page-btn{
    height:36px;
    min-width:36px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:5px;
    padding:0 11px;
    border:1px solid #d5e0e7;
    border-radius:9px;
    background:#ffffff;
    color:#466277;
    text-decoration:none !important;
    font-size:11px;
    font-weight:800;
    line-height:1;
    box-sizing:border-box;
    transition:
        border-color .18s ease,
        background .18s ease,
        color .18s ease,
        transform .18s ease;
}

.audit-page-btn:hover{
    transform:translateY(-1px);
    border-color:#9db6c9;
    background:#edf4fb;
    color:var(--azul-principal);
}

.audit-page-btn.number{
    padding:0;
}

.audit-page-btn.active{
    border-color:var(--azul-principal);
    background:var(--azul-principal);
    color:#ffffff;
    box-shadow:0 4px 10px rgba(22,76,150,.16);
}

.audit-page-btn.disabled{
    border-color:#e1e7eb;
    background:#f3f5f7;
    color:#a5b1ba;
    cursor:not-allowed;
    pointer-events:none;
}

.audit-page-dots{
    width:26px;
    height:36px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    color:#8295a4;
    font-size:12px;
    font-weight:800;
}

.audit-page-arrow{
    font-size:16px;
    line-height:1;
}

@media(max-width:700px){
    .audit-pagination{
        align-items:flex-start;
        flex-direction:column;
    }

    .audit-pagination-nav{
        width:100%;
        justify-content:flex-start;
        flex-wrap:wrap;
    }

    .audit-pagination-info{
        white-space:normal;
    }
}
    @media(max-width:1100px){.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:760px){.filter-grid,.filter-grid-secondary{grid-template-columns:1fr}}
    @media(max-width:560px){.summary-grid{grid-template-columns:1fr}.table-panel-header{align-items:flex-start;flex-direction:column}.filter-actions{flex-direction:column}.btn-filter{width:100%}}
</style>
@endpush

@section('content')
<div class="system-audit-page">
    <div class="page-header">
        <div class="page-title module-heading">
            <div class="module-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3 4.5 6v5.5c0 4.8 3.2 8 7.5 9.5 4.3-1.5 7.5-4.7 7.5-9.5V6L12 3Z"></path>
                    <path d="M9 9h6M9 12h6M9 15h4"></path>
                </svg>
            </div>
            <div>
                <h2>Auditoría del Sistema</h2>
                <p>Consulte la trazabilidad de acciones realizadas dentro del sistema.</p>
            </div>
        </div>
    </div>

    <div class="summary-grid">
        <article class="summary-card">
            <div class="summary-icon"><svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 7h8M8 11h8M8 15h5"></path></svg></div>
            <div><span class="summary-label">Total Registros</span><strong class="summary-value">{{ $resumen->total }}</strong></div>
        </article>

        <article class="summary-card today">
            <div class="summary-icon"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M8 3v4M16 3v4M3 10h18"></path></svg></div>
            <div><span class="summary-label">Registros Hoy</span><strong class="summary-value">{{ $resumen->hoy }}</strong></div>
        </article>

        <article class="summary-card users">
            <div class="summary-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 20v-2a6 6 0 0 1 12 0v2"></path><circle cx="17" cy="9" r="2"></circle><path d="M16 14a5 5 0 0 1 5 5v1"></path></svg></div>
            <div><span class="summary-label">Usuarios con Actividad</span><strong class="summary-value">{{ $resumen->usuarios }}</strong></div>
        </article>

        <article class="summary-card modules">
            <div class="summary-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect></svg></div>
            <div><span class="summary-label">Módulos Auditados</span><strong class="summary-value">{{ $resumen->modulos }}</strong></div>
        </article>
    </div>

    <form method="GET" action="{{ route('administrador.auditoria-sistema.index') }}" class="filter-panel">
        <div class="filter-heading">
            <div class="filter-icon"><svg viewBox="0 0 24 24"><path d="M4 5h16l-6 7v5l-4 2v-7L4 5Z"></path></svg></div>
            <div>
                <h3>Filtros de Trazabilidad</h3>
                <p>Busque actividad por usuario, módulo, acción, tabla o período.</p>
            </div>
        </div>

        <div class="filter-body">
            <div class="filter-grid">
                <div class="field-group">
                    <label for="buscar">Buscar</label>
                    <input id="buscar" class="form-control" name="buscar" value="{{ $buscar }}" placeholder="Descripción, usuario, módulo, acción o tabla">
                </div>

                <div class="field-group">
                    <label for="usuario_id">Usuario</label>
                    <select id="usuario_id" class="form-control" name="usuario_id">
                        <option value="">Todos los Usuarios</option>
                        @foreach($usuarios as $usuario)
                            @php $nombre = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? '')); @endphp
                            <option value="{{ $usuario->id }}" @selected($usuarioId === (int) $usuario->id)>
                                {{ $nombre !== '' ? $nombre : $usuario->usuario }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label for="modulo">Módulo</label>
                    <select id="modulo" class="form-control" name="modulo">
                        <option value="">Todos los Módulos</option>
                        @foreach($modulos as $opcion)
                            <option value="{{ $opcion }}" @selected($modulo === $opcion)>{{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label for="accion">Acción</label>
                    <select id="accion" class="form-control" name="accion">
                        <option value="">Todas las Acciones</option>
                        @foreach($acciones as $opcion)
                            <option value="{{ $opcion }}" @selected($accion === $opcion)>{{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filter-grid-secondary">
                <div class="field-group">
                    <label for="tabla">Tabla</label>
                    <select id="tabla" class="form-control" name="tabla">
                        <option value="">Todas las Tablas</option>
                        @foreach($tablas as $opcion)
                            <option value="{{ $opcion }}" @selected($tabla === $opcion)>{{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label for="desde">Desde</label>
                    <input id="desde" type="date" class="form-control" name="desde" value="{{ $desde }}">
                </div>

                <div class="field-group">
                    <label for="hasta">Hasta</label>
                    <input id="hasta" type="date" class="form-control" name="hasta" value="{{ $hasta }}">
                </div>
            </div>

            <div class="filter-actions">
                <a href="{{ route('administrador.auditoria-sistema.index') }}" class="btn-filter">Limpiar</a>
                <button type="submit" class="btn-filter primary">Aplicar Filtros</button>
            </div>
        </div>
    </form>

    <section class="table-panel">
        <div class="table-panel-header">
            <div class="panel-title">
                <div class="panel-icon"><svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 7h8M8 11h8M8 15h5"></path></svg></div>
                <div>
                    <h3>Registro de Actividad</h3>
                    <p>Historial de acciones registradas para control y trazabilidad.</p>
                </div>
            </div>
            <span class="records-count">{{ $registros->total() }} {{ $registros->total() === 1 ? 'registro' : 'registros' }}</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Tabla</th>
                        <th>Registro</th>
                        <th>Descripción</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $registro)
                        @php
                            $nombre = trim(($registro->nombres ?? '') . ' ' . ($registro->apellidos ?? ''));
                            $fechaRegistro = \Carbon\Carbon::parse($registro->created_at);
                        @endphp
                        <tr>
                            <td>
                                <span class="date-value">{{ $fechaRegistro->format('d/m/Y') }}</span>
                                <span class="date-time">{{ $fechaRegistro->format('H:i:s') }}</span>
                            </td>
                            <td><span class="user-name">{{ $nombre !== '' ? $nombre : ($registro->usuario ?? 'Sistema') }}</span></td>
                            <td><span class="role-badge">{{ $registro->rol_nombre ?? '—' }}</span></td>
                            <td><span class="module-badge">{{ $registro->modulo }}</span></td>
                            <td><span class="action-badge">{{ $registro->accion }}</span></td>
                            <td><span class="table-badge">{{ $registro->tabla_afectada ?? '—' }}</span></td>
                            <td><span class="record-id">{{ $registro->registro_id ?? '—' }}</span></td>
                            <td><span class="description">{{ \Illuminate\Support\Str::limit($registro->descripcion, 90) }}</span></td>
                            <td class="actions">
                                <a
                                    href="{{ route('administrador.auditoria-sistema.show', $registro->id) }}"
                                    class="action-btn"
                                    title="Ver detalle"
                                    aria-label="Ver detalle"
                                >
                                    <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.8"></circle></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state">
                                <div class="empty-icon"><svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 8h8M8 12h8M8 16h5"></path></svg></div>
                                <strong>No existen registros de auditoría</strong>
                                <span>La actividad registrada por el sistema aparecerá en esta sección.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registros->hasPages())
            @php
                $paginaActual = $registros->currentPage();
                $ultimaPagina = $registros->lastPage();

                $inicio = max(1, $paginaActual - 2);
                $fin = min($ultimaPagina, $paginaActual + 2);
            @endphp

            <div class="pagination-wrapper">
                <div class="audit-pagination">

                    <div class="audit-pagination-info">
                        Mostrando
                        <strong>{{ $registros->firstItem() }}</strong>
                        a
                        <strong>{{ $registros->lastItem() }}</strong>
                        de
                        <strong>{{ $registros->total() }}</strong>
                        registros
                    </div>

                    <nav
                        class="audit-pagination-nav"
                        aria-label="Paginación de auditoría"
                    >
                        @if($registros->onFirstPage())
                            <span class="audit-page-btn disabled">
                                <span class="audit-page-arrow">‹</span>
                                Anterior
                            </span>
                        @else
                            <a
                                href="{{ $registros->previousPageUrl() }}"
                                class="audit-page-btn"
                            >
                                <span class="audit-page-arrow">‹</span>
                                Anterior
                            </a>
                        @endif

                        @if($inicio > 1)
                            <a
                                href="{{ $registros->url(1) }}"
                                class="audit-page-btn number"
                            >
                                1
                            </a>

                            @if($inicio > 2)
                                <span class="audit-page-dots">
                                    …
                                </span>
                            @endif
                        @endif

                        @for($pagina = $inicio; $pagina <= $fin; $pagina++)
                            @if($pagina === $paginaActual)
                                <span
                                    class="audit-page-btn number active"
                                    aria-current="page"
                                >
                                    {{ $pagina }}
                                </span>
                            @else
                                <a
                                    href="{{ $registros->url($pagina) }}"
                                    class="audit-page-btn number"
                                >
                                    {{ $pagina }}
                                </a>
                            @endif
                        @endfor

                        @if($fin < $ultimaPagina)
                            @if($fin < $ultimaPagina - 1)
                                <span class="audit-page-dots">
                                    …
                                </span>
                            @endif

                            <a
                                href="{{ $registros->url($ultimaPagina) }}"
                                class="audit-page-btn number"
                            >
                                {{ $ultimaPagina }}
                            </a>
                        @endif

                        @if($registros->hasMorePages())
                            <a
                                href="{{ $registros->nextPageUrl() }}"
                                class="audit-page-btn"
                            >
                                Siguiente
                                <span class="audit-page-arrow">›</span>
                            </a>
                        @else
                            <span class="audit-page-btn disabled">
                                Siguiente
                                <span class="audit-page-arrow">›</span>
                            </span>
                        @endif
                    </nav>

                </div>
            </div>
        @endif
    </section>
</div>
@endsection
