<?php

namespace App\Services;

use App\Models\Arqueo;
use App\Models\Usuario;
use Illuminate\Support\Carbon;
use JsonException;
use RuntimeException;

class FirmaElectronicaService
{
    /**
     * @throws JsonException
     */
    public function calcularHashDocumento(Arqueo $arqueo): string
    {
        $arqueo->loadMissing('detalles');

        $contenido = [
            'numero_arqueo' => $arqueo->numero_arqueo,
            'tipo' => $arqueo->tipo,
            'fecha_arqueo' => $arqueo->fecha_arqueo->format('Y-m-d'),
            'agente' => [
                'codigo' => $arqueo->codigo_agente_historico,
                'negocio' => $arqueo->nombre_negocio_historico,
                'propietario' => $arqueo->nombre_propietario_historico,
                'direccion' => $arqueo->direccion_historica,
                'ruta' => $arqueo->ruta_historica,
                'region' => $arqueo->region_historica,
            ],
            'detalles' => $arqueo->detalles
                ->sortBy([
                    ['tipo', 'asc'],
                    ['denominacion', 'desc'],
                ])
                ->map(fn ($detalle) => [
                    'tipo' => $detalle->tipo,
                    'denominacion' => number_format(
                        (float) $detalle->denominacion,
                        2,
                        '.',
                        ''
                    ),
                    'cantidad' => (int) $detalle->cantidad,
                    'subtotal' => number_format(
                        (float) $detalle->subtotal,
                        2,
                        '.',
                        ''
                    ),
                ])
                ->values()
                ->all(),
            'total_billetes' => number_format(
                (float) $arqueo->total_billetes,
                2,
                '.',
                ''
            ),
            'total_monedas' => number_format(
                (float) $arqueo->total_monedas,
                2,
                '.',
                ''
            ),
            'total_arqueado' => number_format(
                (float) $arqueo->total_arqueado,
                2,
                '.',
                ''
            ),
            'saldo_sistema' => number_format(
                (float) $arqueo->saldo_sistema,
                2,
                '.',
                ''
            ),
            'diferencia' => number_format(
                (float) $arqueo->diferencia,
                2,
                '.',
                ''
            ),
            'certificacion' => $arqueo->certificacion,
            'observaciones' => $arqueo->observaciones,
        ];

        $json = json_encode(
            $contenido,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );

        return hash('sha256', $json);
    }

    public function generarFirma(
        string $hashDocumento,
        Arqueo $arqueo,
        Usuario $usuario,
        string $tipoFirma,
        Carbon $fechaFirma,
        int $version = 1
    ): string {
        $clave = (string) config('services.signatures.key');

        if ($clave === '') {
            throw new RuntimeException(
                'La variable SIGNATURE_KEY no está configurada.'
            );
        }

        $rol = $usuario->rol->nombre;

        $datos = implode('|', [
            $hashDocumento,
            (string) $arqueo->id,
            (string) $usuario->id,
            $tipoFirma,
            $rol,
            $fechaFirma->format('Y-m-d H:i:s.u'),
            (string) $version,
        ]);

        return hash_hmac('sha256', $datos, $clave);
    }
}
