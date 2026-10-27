<?php

namespace App\Http\Controllers\Receptor;

use App\Http\Controllers\Controller;
use App\Models\JornadaReceptor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JornadaController extends Controller
{
    /**
     * Consulta jornadas propias, incluidas las históricas.
     * La vigencia de la asignación no limita la lectura del historial.
     */
    public function index(Request $request): View
    {
        $jornadas = $this->jornadasPropias((int) $request->user()->getKey())
            ->with(['agencia', 'ventanilla', 'turno'])
            ->orderByDesc('fecha_operativa')
            ->orderByDesc('inicio_programado')
            ->paginate(15);

        return view('receptor.jornadas.index', compact('jornadas'));
    }

    /**
     * El identificador por sí solo nunca autoriza el acceso.
     */
    public function show(Request $request, int $jornada): View
    {
        $registro = $this->jornadasPropias((int) $request->user()->getKey())
            ->with(['agencia', 'ventanilla', 'turno', 'asignacionPersonal'])
            ->findOrFail($jornada);

        return view('receptor.jornadas.show', ['jornada' => $registro]);
    }

    private function jornadasPropias(int $usuarioId): Builder
    {
        return JornadaReceptor::query()
            ->where('receptor_usuario_id', $usuarioId)
            ->whereHas('asignacionPersonal', function (Builder $query) use ($usuarioId): void {
                $query->where('usuario_id', $usuarioId)
                    ->where('cargo', 'RECEPTOR');
            });
    }
}
