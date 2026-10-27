<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis jornadas | Sistema de Arqueos</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f1f5f9;color:#17324d;font-family:Arial,sans-serif}header{background:#092e57;color:#fff;padding:22px 5%}header h1{margin:0;font-size:23px}main{max-width:1150px;margin:28px auto;padding:0 18px}.panel{background:#fff;border:1px solid #dbe5ee;border-radius:13px;padding:24px;overflow-x:auto}a{color:#145c9a}nav{margin-bottom:20px}nav a{margin-right:20px}table{border-collapse:collapse;width:100%;min-width:740px}th,td{padding:13px;text-align:left;border-bottom:1px solid #e2e8f0}th{background:#f8fafc}.muted{color:#61758b}.pagination{margin-top:22px}
    </style>
</head>
<body>
<header><h1>Mis jornadas de trabajo</h1><p>Agencias ECOSABA · Receptor</p></header>
<main>
    <nav><a href="{{ route('receptor.dashboard') }}">Volver al panel</a></nav>
    <section class="panel">
        <h2>Jornadas asignadas</h2>
        <p class="muted">Consulta de jornadas propias, actuales e históricas. No permite iniciar ni cerrar jornadas.</p>
        @if ($jornadas->isEmpty())
            <p>No se encontraron jornadas registradas para su cuenta.</p>
        @else
            <table>
                <thead><tr><th>Fecha</th><th>Agencia</th><th>Ventanilla</th><th>Turno</th><th>Estado</th><th>Detalle</th></tr></thead>
                <tbody>
                @foreach ($jornadas as $jornada)
                    <tr>
                        <td>{{ $jornada->fecha_operativa?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $jornada->agencia?->nombre ?? ('Agencia #'.$jornada->agencia_id) }}</td>
                        <td>{{ $jornada->ventanilla?->codigo ?? '—' }}</td>
                        <td>{{ $jornada->turno?->nombre ?? '—' }}</td>
                        <td>{{ $jornada->estado }}</td>
                        <td><a href="{{ route('receptor.jornadas.show', $jornada->id) }}">Ver detalle</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="pagination">{{ $jornadas->links() }}</div>
        @endif
    </section>
</main>
</body>
</html>
