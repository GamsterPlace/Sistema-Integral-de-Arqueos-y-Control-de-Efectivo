<?php

namespace App\Http\Controllers\Receptor;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\JornadaReceptor;
use App\Services\Agencias\AccesoAgenciaService;
use App\Services\Agencias\GeneracionJornadasService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard principal del receptor.
     *
     * Las jornadas del día se generan automáticamente
     * a partir de las asignaciones operativas vigentes.
     *
     * Se conserva la consulta de jornadas históricas,
     * arqueos y los indicadores institucionales.
     */
    public function index(
        Request $request,
        AccesoAgenciaService $acceso,
        GeneracionJornadasService $generacionJornadas
    ): View {
        $usuario = $request->user();

        $usuarioId = (int) $usuario->getKey();

        $fechaActual = now();

        $hoy = $fechaActual->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Asignaciones institucionales vigentes
        |--------------------------------------------------------------------------
        |
        | Se conserva el servicio institucional de acceso.
        | Permite identificar las agencias donde el receptor
        | tiene autorización, incluidas coberturas temporales.
        |
        */

        $asignaciones = $acceso->asignacionesVigentes(
            $usuario
        );

        /*
        |--------------------------------------------------------------------------
        | Generación automática de jornadas
        |--------------------------------------------------------------------------
        |
        | Antes de consultar el dashboard, se generan las
        | jornadas correspondientes a las asignaciones
        | operativas vigentes del receptor.
        |
        | El servicio verifica:
        |
        | - Asignación operativa vigente.
        | - Asignación institucional de personal vigente.
        | - Agencia, ventanilla y turno habilitados.
        | - Conflictos de horarios.
        | - Existencia previa de la jornada.
        |
        | Si la jornada ya existe, no se duplica.
        |
        */

        $generacionJornadas->jornadasDelReceptor(
            $usuarioId,
            $fechaActual
        );

        /*
        |--------------------------------------------------------------------------
        | Jornadas del receptor correspondientes a hoy
        |--------------------------------------------------------------------------
        |
        | Se consultan después de ejecutar el servicio
        | para incluir las jornadas recién generadas.
        |
        | Todo permanece restringido al usuario autenticado.
        |
        */

        $jornadasHoy = JornadaReceptor::query()
            ->with([
                'agencia',
                'ventanilla',
                'turno',
            ])
            ->where(
                'receptor_usuario_id',
                $usuarioId
            )
            ->whereDate(
                'fecha_operativa',
                $hoy
            )
            ->orderBy('inicio_programado')
            ->get();

        $idsJornadas = $jornadasHoy
            ->modelKeys();

        /*
        |--------------------------------------------------------------------------
        | Arqueos del receptor correspondientes a hoy
        |--------------------------------------------------------------------------
        |
        | Se conservan todas las modalidades de arqueo.
        | No se modifican los registros históricos.
        |
        */

        $arqueosHoy = ArqueoAgencia::query()
            ->with([
                'jornada',
                'agencia',
                'ventanilla',
            ])
            ->where(
                'receptor_usuario_id',
                $usuarioId
            )
            ->whereDate(
                'fecha_operativa',
                $hoy
            )
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Arqueos asociados a las jornadas del día
        |--------------------------------------------------------------------------
        */

        $arqueosDeJornadasHoy = $arqueosHoy
            ->filter(
                fn (ArqueoAgencia $arqueo) =>
                    $arqueo->jornada_id !== null
                    && in_array(
                        (int) $arqueo->jornada_id,
                        $idsJornadas,
                        true
                    )
            );

        /*
        |--------------------------------------------------------------------------
        | Arqueos no anulados
        |--------------------------------------------------------------------------
        */

        $vigentes = $arqueosDeJornadasHoy
            ->filter(
                fn (ArqueoAgencia $arqueo) =>
                    $arqueo->anulado_at === null
                    && $arqueo->estado !== 'ANULADO'
            );

        /*
        |--------------------------------------------------------------------------
        | Pendientes de certificación
        |--------------------------------------------------------------------------
        |
        | Arqueos firmados por el receptor que todavía
        | no cuentan con certificación.
        |
        */

        $pendientesCertificacion = $vigentes
            ->filter(
                fn (ArqueoAgencia $arqueo) =>
                    $arqueo->firmado_receptor_at !== null
                    && $arqueo->certificado_at === null
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Arqueos certificados
        |--------------------------------------------------------------------------
        */

        $certificadosHoy = $vigentes
            ->filter(
                fn (ArqueoAgencia $arqueo) =>
                    $arqueo->certificado_at !== null
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Arqueos certificados con diferencia
        |--------------------------------------------------------------------------
        |
        | No se suman montos de efectivo entre modalidades.
        | Únicamente se contabilizan arqueos con diferencia.
        |
        */

        $diferencias = $vigentes
            ->filter(
                fn (ArqueoAgencia $arqueo) =>
                    $arqueo->certificado_at !== null
                    && $arqueo->diferencia !== null
                    && abs((float) $arqueo->diferencia) > 0.00001
            );

        /*
        |--------------------------------------------------------------------------
        | Indicadores del dashboard
        |--------------------------------------------------------------------------
        |
        | Se mantienen exactamente las claves utilizadas
        | por la vista institucional del receptor.
        |
        */

        $indicadores = [
            'jornadas_hoy' => $jornadasHoy->count(),

            'arqueos_hoy' => $arqueosDeJornadasHoy->count(),

            'pendientes_certificacion' =>
                $pendientesCertificacion,

            'certificados_hoy' =>
                $certificadosHoy,

            'con_diferencia' =>
                $diferencias->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Vista del dashboard
        |--------------------------------------------------------------------------
        */

        return view('dashboards.receptor', [
            'usuario' => $usuario,

            'fecha' => $fechaActual,

            'asignaciones' => $asignaciones,

            'jornadasHoy' => $jornadasHoy,

            'arqueosRecientes' => $arqueosHoy->take(6),

            'indicadores' => $indicadores,
        ]);
    }
}
