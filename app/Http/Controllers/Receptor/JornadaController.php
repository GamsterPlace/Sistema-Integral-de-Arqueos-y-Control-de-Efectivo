<?php

namespace App\Http\Controllers\Receptor;

use App\Http\Controllers\Controller;
use App\Models\JornadaReceptor;
use App\Services\Agencias\GeneracionJornadasService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JornadaController extends Controller
{
    /**
     * Listado de jornadas del receptor.
     *
     * Genera automáticamente las jornadas correspondientes
     * al día actual según sus asignaciones operativas vigentes.
     *
     * Conserva las jornadas históricas para consulta.
     */
    public function index(
        Request $request,
        GeneracionJornadasService $generacionJornadas
    ): View {
        $usuarioId = (int) $request->user()->getKey();

        /*
         * Generar las jornadas del día que correspondan
         * a las asignaciones operativas vigentes.
         *
         * El servicio valida:
         * - Vigencia de la asignación operativa.
         * - Asignación de personal en la agencia.
         * - Agencia, ventanilla y turno activos.
         * - Conflictos de horarios.
         * - Existencia previa de la jornada.
         */
        $generacionJornadas->jornadasDelReceptor(
            $usuarioId
        );

        /*
         * Consultar jornadas propias, incluyendo
         * las generadas y el historial existente.
         */
        $jornadas = $this->jornadasPropias($usuarioId)
            ->with([
                'agencia',
                'ventanilla',
                'turno',
            ])
            ->orderByDesc('fecha_operativa')
            ->orderByDesc('inicio_programado')
            ->paginate(15);

        return view(
            'receptor.jornadas.index',
            compact('jornadas')
        );
    }

    /**
     * Mostrar una jornada específica.
     *
     * El identificador de la jornada nunca
     * autoriza el acceso por sí solo.
     */
    public function show(
        Request $request,
        int $jornada
    ): View {
        $usuarioId = (int) $request->user()->getKey();

        $registro = $this->jornadasPropias($usuarioId)
            ->with([
                'agencia',
                'ventanilla',
                'turno',
                'asignacionPersonal',
            ])
            ->findOrFail($jornada);

        return view(
            'receptor.jornadas.show',
            [
                'jornada' => $registro,
            ]
        );
    }

    /**
     * Consulta restringida a jornadas del receptor
     * autenticado.
     *
     * No depende del estado actual de la asignación
     * operativa para permitir consultar el historial.
     */
    private function jornadasPropias(
        int $usuarioId
    ): Builder {
        return JornadaReceptor::query()
            ->where(
                'receptor_usuario_id',
                $usuarioId
            )
            ->whereHas(
                'asignacionPersonal',
                function (Builder $query) use ($usuarioId): void {
                    $query
                        ->where('usuario_id', $usuarioId)
                        ->where('cargo', 'RECEPTOR');
                }
            );
    }
}
