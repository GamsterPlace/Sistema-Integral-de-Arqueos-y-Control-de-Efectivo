@extends($layout)

@section('title', 'Mi Perfil')
@section('module-title', 'Mi Perfil')

@push('styles')
<style>
.ap-page{display:grid;gap:22px;color:#173650}.ap-header h2{margin:0;font-size:27px;color:#092f55}.ap-header p{margin:7px 0 0;color:#718494;font-size:13px}.ap-grid{display:grid;grid-template-columns:330px minmax(0,1fr);gap:22px;align-items:start}.ap-card{background:#fff;border:1px solid #dfe8ef;border-radius:17px;overflow:hidden;box-shadow:0 8px 24px rgba(20,57,83,.05)}.ap-profile-top{background:linear-gradient(145deg,#0b315f,#164c96);padding:27px 20px;color:#fff;text-align:center}.ap-avatar{width:88px;height:88px;margin:0 auto 13px;display:grid;place-items:center;border:3px solid #ffffff40;border-radius:23px;background:#ffffff20;font-size:27px;font-weight:850}.ap-profile-top h3{margin:0;font-size:19px;overflow-wrap:anywhere}.ap-profile-top p{margin:6px 0 0;font-size:11px;color:#d5e4f5}.ap-profile-body{padding:17px}.ap-row{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid #edf1f4;padding:12px 0;font-size:12px}.ap-row:last-child{border:0}.ap-row span{color:#778b9a;font-weight:750}.ap-row strong{text-align:right;color:#1d4568;overflow-wrap:anywhere}.ap-section+.ap-section{margin-top:20px}.ap-section-head{padding:17px 20px;background:#f8fafc;border-bottom:1px solid #edf1f4}.ap-section-head h3{margin:0;color:#0c345d;font-size:16px}.ap-section-head p{margin:5px 0 0;color:#7b8b9b;font-size:11px}.ap-section-body{padding:20px}.ap-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.ap-field-wide{grid-column:1/-1}.ap-field label{display:block;margin-bottom:7px;font-size:11px;color:#637b90;font-weight:800}.ap-input{box-sizing:border-box;width:100%;min-height:44px;border:1px solid #cedae4;border-radius:10px;padding:0 13px;background:#fff;color:#284760;font-size:13px}.ap-input:focus{outline:none;border-color:#2b72b8;box-shadow:0 0 0 3px #2b72b820}.ap-error{display:block;margin-top:5px;color:#b43a3a;font-size:11px}.ap-actions{display:flex;justify-content:flex-end;margin-top:19px}.ap-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 17px;border:0;border-radius:10px;background:#164c96;color:#fff;font-size:12px;font-weight:800;text-decoration:none;cursor:pointer}.ap-btn-alt{background:#fff;color:#164c96;border:1px solid #d2dfe9}.ap-assignments{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.ap-assignment{border:1px solid #e1e9f0;background:#fafcfd;border-radius:12px;padding:14px}.ap-assignment span{display:block;color:#778b9b;font-size:10px;font-weight:800;text-transform:uppercase}.ap-assignment strong{display:block;margin-top:6px;font-size:13px;color:#1b4267}.ap-assignment small{display:block;margin-top:6px;color:#708394;font-size:11px;line-height:1.5}.ap-info{font-size:12px;line-height:1.7;color:#697e90}.ap-alert{border:1px solid #b9e1c8;background:#eaf8ef;color:#236b47;border-radius:11px;padding:13px 16px;font-size:12px}.ap-alert-error{border-color:#edc3c3;background:#fff1f1;color:#a43838}.ap-security{display:flex;align-items:center;justify-content:space-between;gap:15px;background:#f8fafc;border:1px solid #e0e8ee;border-radius:12px;padding:15px}.ap-security strong{font-size:12px;color:#1c4669}.ap-security p{font-size:11px;color:#7b8b9b;line-height:1.6;margin:5px 0 0}@media(max-width:950px){.ap-grid{grid-template-columns:1fr}}@media(max-width:650px){.ap-fields,.ap-assignments{grid-template-columns:1fr}.ap-field-wide{grid-column:auto}.ap-security{align-items:stretch;flex-direction:column}.ap-btn{width:100%;box-sizing:border-box}}
</style>
@endpush

@section('content')
@php
    $persona = $usuario->datosPersonales;
    $nombreCompleto = trim(($persona?->nombres ?? '') . ' ' . ($persona?->apellidos ?? ''));
    $nombreVisible = $nombreCompleto !== '' ? $nombreCompleto : $usuario->usuario;
    $partes = preg_split('/\s+/u', $nombreVisible, -1, PREG_SPLIT_NO_EMPTY);
    $iniciales = collect($partes)->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp
<div class="ap-page">
    <header class="ap-header">
        <h2>Mi Perfil</h2>
        <p>Consulte sus datos institucionales y administre la información permitida de su cuenta.</p>
    </header>

    @if(session('success'))
        <div class="ap-alert" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="ap-alert ap-alert-error" role="alert">Revise los campos indicados para continuar.</div>
    @endif

    <div class="ap-grid">
        <aside class="ap-card">
            <div class="ap-profile-top">
                <div class="ap-avatar">{{ $iniciales !== '' ? $iniciales : 'US' }}</div>
                <h3>{{ $nombreVisible }}</h3>
                <p>{{ $cargo }} · Sistema de Arqueos de Agencias</p>
            </div>
            <div class="ap-profile-body">
                <div class="ap-row"><span>Usuario</span><strong>{{ $usuario->usuario }}</strong></div>
                <div class="ap-row"><span>Cargo</span><strong>{{ $cargo }}</strong></div>
                <div class="ap-row"><span>Estado</span><strong>{{ $usuario->estado }}</strong></div>
                <div class="ap-row"><span>Teléfono</span><strong>{{ $persona?->telefono ?: 'No registrado' }}</strong></div>
            </div>
        </aside>

        <main>
            <section class="ap-card ap-section">
                <header class="ap-section-head"><h3>Información personal</h3><p>Puede actualizar los datos asociados a su cuenta institucional.</p></header>
                <div class="ap-section-body">
                    <form method="POST" action="{{ route('agencias.perfil.actualizar') }}">
                        @csrf
                        @method('PUT')
                        <div class="ap-fields">
                            <div class="ap-field">
                                <label for="nombres">Nombres</label>
                                <input class="ap-input" id="nombres" name="nombres" type="text" maxlength="120" required autocomplete="given-name" value="{{ old('nombres', $persona?->nombres) }}">
                                @error('nombres')<span class="ap-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="ap-field">
                                <label for="apellidos">Apellidos</label>
                                <input class="ap-input" id="apellidos" name="apellidos" type="text" maxlength="120" required autocomplete="family-name" value="{{ old('apellidos', $persona?->apellidos) }}">
                                @error('apellidos')<span class="ap-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="ap-field">
                                <label for="telefono">Teléfono</label>
                                <input class="ap-input" id="telefono" name="telefono" type="text" maxlength="30" autocomplete="tel" value="{{ old('telefono', $persona?->telefono) }}">
                                @error('telefono')<span class="ap-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="ap-field">
                                <label for="nombre_usuario">Nombre de usuario</label>
                                <input class="ap-input" id="nombre_usuario" name="nombre_usuario" type="text" maxlength="80" required autocomplete="username" value="{{ old('nombre_usuario', $usuario->usuario) }}">
                                @error('nombre_usuario')<span class="ap-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="ap-actions"><button type="submit" class="ap-btn">Guardar cambios</button></div>
                    </form>
                </div>
            </section>

            <section class="ap-card ap-section">
                <header class="ap-section-head"><h3>Información institucional</h3><p>Datos administrados por la institución; no se pueden modificar desde el perfil.</p></header>
                <div class="ap-section-body">
                    <div class="ap-assignments">
                        <div class="ap-assignment"><span>Cargo institucional</span><strong>{{ $cargo }}</strong></div>
                        <div class="ap-assignment"><span>Estado de cuenta</span><strong>{{ $usuario->estado }}</strong></div>
                        @foreach($asignaciones as $asignacion)
                            <div class="ap-assignment">
                                <span>Agencia asignada</span>
                                <strong>{{ $asignacion->agencia?->nombre ?? 'Agencia no disponible' }}</strong>
                                <small>{{ str_replace('_', ' ', $asignacion->cargo) }} · {{ $asignacion->tipo }}</small>
                                <small>Desde {{ $asignacion->fecha_inicio?->format('d/m/Y') ?? '—' }}{{ $asignacion->fecha_fin ? ' hasta ' . $asignacion->fecha_fin->format('d/m/Y') : ' · Sin fecha de finalización' }}</small>
                            </div>
                        @endforeach
                    </div>
                    @if($asignaciones->isEmpty())
                        <p class="ap-info">@if(in_array($rol, ['Receptor','JefeCaja','SubjefeCaja','JefeAgencia'], true)) No hay asignaciones de agencia vigentes para su cargo. @else Este cargo no requiere una asignación individual de agencia para consultar su perfil. @endif</p>
                    @endif
                    @if($rol === 'Receptor')
                        <p class="ap-info">La ventanilla y el turno pueden variar por jornada; por ello se consultan desde el módulo de Jornadas y no se muestran como asignaciones permanentes.</p>
                    @endif
                </div>
            </section>

            <section class="ap-card ap-section">
                <header class="ap-section-head"><h3>Seguridad de la cuenta</h3></header>
                <div class="ap-section-body">
                    <div class="ap-security">
                        <div><strong>Cambio de contraseña</strong><p>Proteja su acceso actualizando su contraseña institucional cuando corresponda.</p></div>
                        <a class="ap-btn ap-btn-alt" href="{{ route('password.cambiar') }}">Cambiar contraseña</a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>
@endsection
