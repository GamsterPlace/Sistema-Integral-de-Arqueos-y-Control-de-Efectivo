<?php

namespace App\Http\Controllers\Agente;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArqueoExtemporaneoController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing('agente');

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        $estado = trim((string) $request->string('estado'));

        $habilitaciones = DB::table(
            'habilitaciones_arqueos_atrasados as h'
        )
            ->leftJoin(
                'usuarios as u',
                'u.id',
                '=',
                'h.autorizado_por'
            )
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->where('h.agente_id', $agente->id)
            ->when(
                $estado !== '',
                fn ($query) => $query->where('h.estado', $estado)
            )
            ->select([
                'h.id',
                'h.agente_id',
                'h.fecha_autorizada',
                'h.motivo',
                'h.autorizado_por',
                'h.autorizado_at',
                'h.estado',
                'h.utilizado_at',
                'dp.nombres as autorizado_nombres',
                'dp.apellidos as autorizado_apellidos',
            ])
            ->orderByRaw(
                "CASE
                    WHEN h.estado = 'PENDIENTE' THEN 1
                    WHEN h.estado = 'UTILIZADA' THEN 2
                    WHEN h.estado = 'CANCELADA' THEN 3
                    ELSE 4
                END"
            )
            ->orderByDesc('h.fecha_autorizada')
            ->paginate(12)
            ->withQueryString();

        $pendientes = DB::table(
            'habilitaciones_arqueos_atrasados'
        )
            ->where('agente_id', $agente->id)
            ->where('estado', 'PENDIENTE')
            ->count();

        $utilizadas = DB::table(
            'habilitaciones_arqueos_atrasados'
        )
            ->where('agente_id', $agente->id)
            ->where('estado', 'UTILIZADA')
            ->count();

        $canceladas = DB::table(
            'habilitaciones_arqueos_atrasados'
        )
            ->where('agente_id', $agente->id)
            ->where('estado', 'CANCELADA')
            ->count();

        return view(
            'agente.arqueos-extemporaneos.index',
            [
                'agente' => $agente,
                'habilitaciones' => $habilitaciones,
                'pendientes' => $pendientes,
                'utilizadas' => $utilizadas,
                'canceladas' => $canceladas,
                'estado' => $estado,
            ]
        );
    }
}
