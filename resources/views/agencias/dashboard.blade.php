<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} | Arqueos y Control de Efectivo</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f1f5f9;color:#17324d;font-family:Arial,Helvetica,sans-serif}
        header{background:#092e57;color:white;padding:22px max(20px,calc((100% - 1100px)/2))}
        header h1{font-size:23px;margin:0 0 6px}header p{margin:0;color:#d6e6f6}
        main{max-width:1100px;margin:32px auto;padding:0 20px}.panel{background:white;border:1px solid #dbe5ee;border-radius:14px;padding:25px;margin-bottom:18px;box-shadow:0 5px 18px #102c4410}
        h2{margin:0 0 12px;font-size:20px}p{line-height:1.6}.agencia{padding:15px;border:1px solid #dce5ef;border-radius:9px;margin-top:12px;background:#f8fafc}
        .muted{color:#60758b;font-size:14px}.top{display:flex;justify-content:space-between;gap:18px;align-items:center;flex-wrap:wrap}
        button{background:#fff;color:#0c3763;border:0;padding:10px 16px;border-radius:8px;font-weight:bold;cursor:pointer}
    </style>
</head>
<body>
<header>
    <div class="top">
        <div><h1>Sistema de Arqueos y Control de Efectivo</h1><p>Agencias ECOSABA · {{ $titulo }}</p></div>
        <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Cerrar sesión</button></form>
    </div>
</header>
<main>
    <section class="panel">
        <h2>{{ $seccion }}</h2>
        <p>{{ $descripcion }}</p>
        <p class="muted">Panel inicial de acceso. No se muestran datos económicos ni se permiten operaciones de caja en esta etapa.</p>
    </section>
    <section class="panel">
        <h2>Agencias asignadas actualmente</h2>
        @forelse ($asignaciones as $asignacion)
            <div class="agencia">
                <strong>Agencia #{{ $asignacion->agencia_id }}</strong>
                <p class="muted">Cargo: {{ str_replace('_', ' ', $asignacion->cargo) }} · Tipo: {{ $asignacion->tipo }}</p>
                <p class="muted">Vigencia: {{ $asignacion->fecha_inicio?->format('d/m/Y') }} — {{ $asignacion->fecha_fin?->format('d/m/Y') ?? 'Sin fecha de finalización' }}</p>
            </div>
        @empty
            <p>No tiene asignaciones vigentes para su cargo. Solicite al Administrador que revise sus asignaciones.</p>
        @endforelse
    </section>
</main>
</body>
</html>
