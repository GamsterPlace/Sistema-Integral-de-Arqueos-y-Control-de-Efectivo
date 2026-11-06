@extends('layouts.administrador')

@section('title', 'Gestión de Usuarios')
@section('module-title', 'Usuarios')

@push('styles')
<style>
.usuarios-panel{max-width:1500px;margin:auto;color:#27445b}.usuarios-panel *{box-sizing:border-box}.usuarios-header{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-bottom:22px}.usuarios-header h2{margin:0;color:var(--azul-profundo,#163d65);font-size:25px}.usuarios-header p{margin:6px 0 0;color:#758799;font-size:12px}.usuarios-btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 17px;border-radius:10px;border:0;font-size:12px;font-weight:700;text-decoration:none;cursor:pointer;background:var(--azul-principal,#164c96);color:white}.usuarios-btn:hover{opacity:.92;color:white}.usuarios-btn-light{background:#eaf0f5;color:#31516b}.usuarios-metricas{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}.usuarios-metrica,.usuarios-filtros,.usuarios-tabla-card{background:white;border:1px solid #dfe8ef;border-radius:15px;box-shadow:0 6px 20px rgba(19,57,85,.04)}.usuarios-metrica{padding:19px}.usuarios-metrica span{display:block;color:#74889a;font-size:11px;font-weight:700;text-transform:uppercase}.usuarios-metrica strong{display:block;font-size:28px;margin-top:8px;color:#163d65}.usuarios-filtros{padding:20px;margin-bottom:20px}.usuarios-filtros h3{font-size:15px;color:#163d65;margin:0 0 5px}.usuarios-filtros p{font-size:11px;color:#788b99;margin:0 0 17px}.usuarios-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}.usuarios-campo label{display:block;font-size:10px;font-weight:800;color:#5b7285;margin-bottom:7px}.usuarios-campo input,.usuarios-campo select{width:100%;height:43px;padding:0 10px;border:1px solid #ccd9e3;border-radius:9px;background:#fff;color:#344f64;font-size:11px}.usuarios-campo input:focus,.usuarios-campo select:focus{outline:2px solid #b7d3ed}.usuarios-acciones-filtro{display:flex;justify-content:flex-end;gap:10px;margin-top:15px}.usuarios-tabla-cabecera{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:18px 20px;border-bottom:1px solid #e8eef3}.usuarios-tabla-cabecera h3{margin:0;color:#163d65;font-size:15px}.usuarios-tabla-cabecera p{margin:5px 0 0;color:#80909e;font-size:11px}.usuarios-contador{font-size:11px;color:#597184;border:1px solid #dce5eb;border-radius:20px;padding:6px 10px}.usuarios-scroll{overflow-x:auto}.usuarios-tabla{width:100%;min-width:1000px;border-collapse:collapse}.usuarios-tabla th,.usuarios-tabla td{padding:14px;border-bottom:1px solid #edf1f4;text-align:left;vertical-align:middle}.usuarios-tabla th{font-size:10px;text-transform:uppercase;letter-spacing:.03em;color:#6d8292;background:#f7fafc}.usuarios-tabla td{font-size:12px;color:#385267}.usuarios-tabla tbody tr:hover{background:#fafcfe}.usuarios-nombre{font-weight:800;color:#163d65}.usuarios-subtexto{display:block;margin-top:4px;font-size:10px;color:#8193a0}.usuarios-etiqueta{display:inline-flex;align-items:center;padding:6px 10px;border-radius:30px;background:#edf4fb;color:#245a85;font-size:10px;font-weight:800}.usuarios-etiqueta.agencia{background:#eaf8f0;color:#187344}.usuarios-etiqueta.institucional{background:#f1eef9;color:#67519b}.usuarios-estado{display:inline-block;padding:6px 10px;border-radius:30px;font-size:10px;font-weight:800}.usuarios-estado.activo{background:#e8f7ee;color:#167640}.usuarios-estado.inactivo{background:#fff0f0;color:#a63737}.usuarios-estado.bloqueado{background:#fff5dc;color:#916400}.usuarios-opciones{display:flex;gap:6px;align-items:center}.usuarios-opciones a,.usuarios-opciones button{border:1px solid #dce6ee;background:#f0f5f9;color:#275777;border-radius:8px;padding:8px 10px;text-decoration:none;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap}.usuarios-opciones form{margin:0}.usuarios-vacio{text-align:center;padding:36px;color:#718597}.usuarios-paginacion{padding:18px}@media(max-width:1100px){.usuarios-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:700px){.usuarios-metricas{grid-template-columns:repeat(2,minmax(0,1fr))}.usuarios-grid{grid-template-columns:1fr}.usuarios-acciones-filtro{flex-wrap:wrap}.usuarios-header h2{font-size:21px}}
</style>
@endpush

@section('content')
@php
    $moduloSeleccionado = $modulo ?? request('modulo', 'todos');
    $agenciaSeleccionada = (int) ($agenciaId ?? request('agencia_id', 0));
    $rolesAgentes = ['AGENTE', 'PROMOTOR', 'JEFEDEAGENTES'];
    $rolesAgencias = ['RECEPTOR', 'JEFECAJA', 'SUBJEFECAJA', 'JEFEAGENCIA'];
@endphp
<div class="usuarios-panel">
    <div class="usuarios-header">
        <div>
            <h2>Gestión de Usuarios</h2>
            <p>Administración de cuentas de Agentes MICOOPE, Agencias ECOSABA y personal institucional.</p>
        </div>
        <a class="usuarios-btn" href="{{ route('administrador.usuarios.create') }}">+ Crear usuario</a>
    </div>

    @if(session('success'))
        <div role="status" style="padding:13px 17px;margin-bottom:16px;border-radius:10px;background:#eaf8f0;color:#176b3e;border:1px solid #cdebd8;font-size:12px">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" style="padding:13px 17px;margin-bottom:16px;border-radius:10px;background:#fff0f0;color:#a63737;border:1px solid #efcaca;font-size:12px">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="usuarios-metricas">
        <div class="usuarios-metrica"><span>Total de usuarios</span><strong>{{ number_format($resumen->total) }}</strong></div>
        <div class="usuarios-metrica"><span>Usuarios activos</span><strong>{{ number_format($resumen->activos) }}</strong></div>
        <div class="usuarios-metrica"><span>Usuarios inactivos</span><strong>{{ number_format($resumen->inactivos) }}</strong></div>
        <div class="usuarios-metrica"><span>Administradores</span><strong>{{ number_format($resumen->administradores) }}</strong></div>
    </div>

    <form method="GET" action="{{ route('administrador.usuarios.index') }}" class="usuarios-filtros">
        <h3>Filtros de búsqueda</h3>
        <p>Puede combinar módulo, agencia, rol, estado y texto de búsqueda.</p>
        <div class="usuarios-grid">
            <div class="usuarios-campo"><label for="buscar">Buscar usuario</label><input id="buscar" type="search" name="buscar" value="{{ $buscar ?? request('buscar', '') }}" placeholder="Nombre, apellido o usuario"></div>
            <div class="usuarios-campo"><label for="modulo">Módulo</label><select id="modulo" name="modulo">
                <option value="todos" @selected($moduloSeleccionado === 'todos')>Todos los módulos</option>
                <option value="agentes" @selected($moduloSeleccionado === 'agentes')>Agentes MICOOPE</option>
                <option value="agencias" @selected($moduloSeleccionado === 'agencias')>Agencias ECOSABA</option>
                <option value="institucional" @selected($moduloSeleccionado === 'institucional')>Institucional</option>
            </select></div>
            <div class="usuarios-campo"><label for="agencia_id">Agencia ECOSABA</label><select id="agencia_id" name="agencia_id">
                <option value="">Todas las agencias</option>
                @foreach($agencias as $agencia)
                    <option value="{{ $agencia->id }}" @selected($agenciaSeleccionada === (int) $agencia->id)>{{ $agencia->codigo }} — {{ $agencia->nombre }}</option>
                @endforeach
            </select></div>
            <div class="usuarios-campo"><label for="rol_id">Rol</label><select id="rol_id" name="rol_id"><option value="">Todos los roles</option>
                @foreach($roles as $rol)<option value="{{ $rol->id }}" @selected((int) ($rolId ?? 0) === (int) $rol->id)>{{ $rol->nombre }}</option>@endforeach
            </select></div>
            <div class="usuarios-campo"><label for="estado">Estado</label><select id="estado" name="estado">
                <option value="">Todos los estados</option>
                <option value="ACTIVO" @selected(($estado ?? '') === 'ACTIVO')>Activo</option>
                <option value="INACTIVO" @selected(($estado ?? '') === 'INACTIVO')>Inactivo</option>
                <option value="BLOQUEADO" @selected(($estado ?? '') === 'BLOQUEADO')>Bloqueado</option>
            </select></div>
        </div>
        <div class="usuarios-acciones-filtro">
            <a class="usuarios-btn usuarios-btn-light" href="{{ route('administrador.usuarios.index') }}">Limpiar filtros</a>
            <button class="usuarios-btn" type="submit">Aplicar filtros</button>
        </div>
    </form>

    <section class="usuarios-tabla-card">
        <div class="usuarios-tabla-cabecera">
            <div><h3>Usuarios registrados</h3><p>Listado unificado de cuentas institucionales.</p></div>
            <span class="usuarios-contador">{{ $usuarios->total() }} registros</span>
        </div>
        <div class="usuarios-scroll"><table class="usuarios-tabla">
            <thead><tr><th>Colaborador</th><th>Usuario</th><th>Módulo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($usuarios as $usuario)
                @php
                    $nombre = trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellidos ?? ''));
                    $rolNormalizado = strtoupper(trim($usuario->rol_nombre));
                    $area = in_array($rolNormalizado, $rolesAgentes, true) ? 'Agentes MICOOPE' : (in_array($rolNormalizado, $rolesAgencias, true) ? 'Agencias ECOSABA' : 'Institucional');
                    $claseArea = $area === 'Agencias ECOSABA' ? 'agencia' : ($area === 'Institucional' ? 'institucional' : '');
                    $claseEstado = match($usuario->estado) {'ACTIVO' => 'activo', 'BLOQUEADO' => 'bloqueado', default => 'inactivo'};
                @endphp
                <tr>
                    <td><span class="usuarios-nombre">{{ $nombre !== '' ? $nombre : 'Sin nombre registrado' }}</span><span class="usuarios-subtexto">ID #{{ $usuario->id }}</span></td>
                    <td>{{ $usuario->usuario }}</td>
                    <td><span class="usuarios-etiqueta {{ $claseArea }}">{{ $area }}</span></td>
                    <td>{{ $usuario->rol_nombre }}</td>
                    <td><span class="usuarios-estado {{ $claseEstado }}">{{ $usuario->estado }}</span></td>
                    <td><div class="usuarios-opciones">
                        <a href="{{ route('administrador.usuarios.show', $usuario->id) }}" title="Consultar usuario">Ver</a>
                        <a href="{{ route('administrador.usuarios.edit', $usuario->id) }}" title="Editar usuario">Editar</a>
                        @if(auth()->id() !== $usuario->id && $usuario->estado !== 'BLOQUEADO')
                            <form method="POST" action="{{ route('administrador.usuarios.estado', $usuario->id) }}" onsubmit="return confirm(@js($usuario->estado === 'ACTIVO' ? '¿Desea desactivar esta cuenta?' : '¿Desea activar esta cuenta?'));">
                                @csrf @method('PATCH')
                                <button type="submit">{{ $usuario->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}</button>
                            </form>
                        @endif
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="6" class="usuarios-vacio">No se encontraron usuarios con los filtros seleccionados.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        @if($usuarios->hasPages())<div class="usuarios-paginacion">{{ $usuarios->links() }}</div>@endif
    </section>
</div>
@endsection
