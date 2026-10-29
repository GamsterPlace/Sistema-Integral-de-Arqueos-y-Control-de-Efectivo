@extends('layouts.receptor')

@section('title', 'Mis Jornadas')

@push('styles')
<style>
    .jr-page{display:grid;gap:22px;color:#19344d}
    .jr-page *{box-sizing:border-box}
    .jr-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .jr-heading h1{margin:0;color:#06284f;font-size:clamp(24px,3vw,30px);font-weight:800}
    .jr-heading p{margin:8px 0 0;color:#687e92;font-size:14px;line-height:1.65}
    .jr-back{display:inline-flex;align-items:center;gap:8px;padding:10px 15px;border:1px solid #d8e5f1;border-radius:10px;background:#fff;color:#164c96;font-size:13px;font-weight:750;text-decoration:none}
    .jr-back:hover{background:#eef5fc}
    .jr-panel{background:#fff;border:1px solid #dce7f1;border-radius:17px;box-shadow:0 6px 24px rgba(6,40,79,.045);overflow:hidden}
    .jr-panel-head{padding:21px 24px;border-bottom:1px solid #e5edf5;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .jr-panel-head h2{margin:0;color:#123c64;font-size:17px}
    .jr-count{border-radius:99px;padding:6px 11px;background:#eaf3fc;color:#164c96;font-size:12px;font-weight:800}
    .jr-table-wrap{overflow-x:auto}
    .jr-table{width:100%;border-collapse:collapse;min-width:760px;text-align:left}
    .jr-table th{padding:13px 18px;background:#f6f9fc;color:#61788d;text-transform:uppercase;letter-spacing:.05em;font-size:11px;font-weight:800;white-space:nowrap}
    .jr-table td{padding:17px 18px;border-top:1px solid #e9eff5;font-size:13px;vertical-align:middle}
    .jr-table tbody tr:hover{background:#fbfdff}
    .jr-primary{font-weight:800;color:#173d61}
    .jr-secondary{display:block;color:#718499;font-size:12px;margin-top:4px}
    .jr-status{display:inline-flex;align-items:center;padding:6px 11px;border-radius:99px;background:#edf3fa;color:#315d86;font-size:11px;font-weight:800;white-space:nowrap}
    .jr-detail{display:inline-flex;align-items:center;justify-content:center;padding:9px 13px;background:#eaf3fc;border:1px solid #cfe0f2;border-radius:9px;color:#164c96;font-size:12px;font-weight:800;text-decoration:none;white-space:nowrap}
    .jr-detail:hover{background:#dcecff}
    .jr-empty{padding:46px 24px;text-align:center}
    .jr-empty strong{display:block;color:#244765;font-size:16px}
    .jr-empty p{margin:9px auto 0;max-width:430px;color:#73879b;font-size:13px;line-height:1.7}
    .jr-pagination{padding:18px 24px;border-top:1px solid #e9eff5}
    @media(max-width:650px){.jr-panel-head{padding:18px}.jr-table th,.jr-table td{padding:13px}.jr-pagination{padding:14px}}
</style>
@endpush

@section('content')
<div class="jr-page">
    <div class="jr-heading">
        <div>
            <h1>Mis Jornadas</h1>
            <p>Consulta tus jornadas de trabajo, tanto actuales como históricas, y revisa los detalles de cada asignación.</p>
        </div>
        <a class="jr-back" href="{{ route('receptor.dashboard') }}">← Volver al Dashboard</a>
    </div>

    <section class="jr-panel" aria-labelledby="jr-list-title">
        <div class="jr-panel-head">
            <h2 id="jr-list-title">Jornadas asignadas</h2>
            <span class="jr-count">{{ $jornadas->total() }} {{ $jornadas->total() === 1 ? 'registro' : 'registros' }}</span>
        </div>

        @if($jornadas->isEmpty())
            <div class="jr-empty">
                <strong>No hay jornadas registradas</strong>
                <p>Actualmente no se encontraron jornadas asociadas a tu cuenta. Cuando se te asigne una jornada, aparecerá aquí.</p>
            </div>
        @else
            <div class="jr-table-wrap">
                <table class="jr-table">
                    <thead>
                        <tr>
                            <th scope="col">Fecha operativa</th>
                            <th scope="col">Agencia</th>
                            <th scope="col">Ventanilla</th>
                            <th scope="col">Turno y horario</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jornadas as $jornada)
                            <tr>
                                <td class="jr-primary">{{ $jornada->fecha_operativa?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    <span class="jr-primary">{{ $jornada->agencia?->nombre ?? ('Agencia #'.$jornada->agencia_id) }}</span>
                                    @if($jornada->agencia?->codigo)
                                        <span class="jr-secondary">Código: {{ $jornada->agencia->codigo }}</span>
                                    @endif
                                </td>
                                <td>{{ $jornada->ventanilla?->codigo ?? '—' }}</td>
                                <td>
                                    <span class="jr-primary">{{ $jornada->turno?->nombre ?? '—' }}</span>
                                    <span class="jr-secondary">{{ $jornada->inicio_programado?->format('H:i') ?? '—' }} a {{ $jornada->fin_programado?->format('H:i') ?? '—' }}</span>
                                </td>
                                <td><span class="jr-status">{{ $jornada->estado ?? 'Sin estado' }}</span></td>
                                <td><a class="jr-detail" href="{{ route('receptor.jornadas.show', ['jornada' => $jornada->id]) }}">Ver detalle</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($jornadas->hasPages())
                <div class="jr-pagination">{{ $jornadas->links() }}</div>
            @endif
        @endif
    </section>
</div>
@endsection
