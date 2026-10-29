<?php

namespace App\Services\Agencias;

use App\Models\JornadaReceptor;

/**
 * Clasificación compartida de cumplimiento de arqueos ordinarios.
 * Mantiene las reglas existentes del controlador de Agencias.
 */
class CumplimientoJornadaService
{
    public function clasificar(
        JornadaReceptor $j
    ): JornadaReceptor {
        $hoy = now()->toDateString();

        $vigente = $j->arqueos->first(
            fn ($a) =>
                $a->estado !== 'ANULADO'
                && $a->firmado_receptor_at !== null
        );

        $anulado = $j->arqueos->contains(
            fn ($a) => $a->estado === 'ANULADO'
        );

        $fecha = $j->fecha_operativa->toDateString();

        $j->cumplimiento_arqueo = $vigente
            ? 'CON_ARQUEO'
            : (
                $fecha > $hoy
                    ? 'PROGRAMADO'
                    : (
                        $fecha === $hoy
                            ? 'PENDIENTE_HOY'
                            : (
                                $anulado
                                    ? 'ANULADO_SIN_REEMPLAZO'
                                    : 'SIN_ARQUEO'
                            )
                    )
            );

        $j->certificacion_arqueo = $vigente
            ? (
                $vigente->estado === 'CERTIFICADO'
                    ? 'CERTIFICADO'
                    : 'PENDIENTE_CERTIFICACION'
            )
            : 'NO_APLICA';

        $j->arqueo_ordinario_vigente = $vigente;

        return $j;
    }

}
