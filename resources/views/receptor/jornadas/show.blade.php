<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle de jornada | Sistema de Arqueos</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f1f5f9;color:#17324d;font-family:Arial,sans-serif}header{background:#092e57;color:#fff;padding:22px 5%}header h1{margin:0;font-size:23px}main{max-width:950px;margin:28px auto;padding:0 18px}.panel{background:#fff;border:1px solid #dbe5ee;border-radius:13px;padding:25px}a{color:#145c9a}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px}.item{background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:14px}.item strong{display:block;margin-bottom:7px}.muted{color:#61758b}nav{margin-bottom:20px}
    </style>
</head>
<body>
<header><h1>Detalle de jornada #{{ $jornada->id }}</h1><p>Agencias ECOSABA · Receptor</p></header>
<main>
    <nav><a href="{{ route('receptor.jornadas.index') }}">Volver a mis jornadas</a></nav>
    <section class="panel">
        <h2>Información de la jornada</h2>
        <p class="muted">Vista de consulta. Los controles de apertura, cierre y arqueo se incorporarán posteriormente.</p>
        <div class="grid">
            <div class="item"><strong>Agencia</strong>{{ $jornada->agencia?->nombre ?? ('Agencia #'.$jornada->agencia_id) }}</div>
            <div class="item"><strong>Ventanilla</strong>{{ $jornada->ventanilla?->codigo ?? '—' }} — {{ $jornada->ventanilla?->descripcion ?? 'Sin descripción' }}</div>
            <div class="item"><strong>Turno</strong>{{ $jornada->turno?->nombre ?? '—' }}</div>
            <div class="item"><strong>Fecha operativa</strong>{{ $jornada->fecha_operativa?->format('d/m/Y') ?? '—' }}</div>
            <div class="item"><strong>Inicio programado</strong>{{ $jornada->inicio_programado?->format('d/m/Y H:i') ?? '—' }}</div>
            <div class="item"><strong>Fin programado</strong>{{ $jornada->fin_programado?->format('d/m/Y H:i') ?? '—' }}</div>
            <div class="item"><strong>Inicio real</strong>{{ $jornada->inicio_real?->format('d/m/Y H:i') ?? 'Pendiente' }}</div>
            <div class="item"><strong>Fin real</strong>{{ $jornada->fin_real?->format('d/m/Y H:i') ?? 'Pendiente' }}</div>
            <div class="item"><strong>Estado</strong>{{ $jornada->estado }}</div>
            <div class="item"><strong>Tipo de asignación</strong>{{ $jornada->asignacionPersonal?->tipo ?? '—' }}</div>
        </div>
    </section>
</main>
</body>
</html>
