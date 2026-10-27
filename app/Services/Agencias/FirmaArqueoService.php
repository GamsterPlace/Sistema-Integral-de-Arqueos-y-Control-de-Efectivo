<?php

namespace App\Services\Agencias;

use App\Models\ArqueoAgencia;
use App\Models\FirmaArqueoAgencia;
use App\Models\Usuario;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class FirmaArqueoService
{
    /** El hash cubre los campos económicos y todos los detalles en orden estable. */
    public function huella(ArqueoAgencia $arqueo, int $version = 1): string
    {
        $arqueo->load('detalles');
        $detalles = $arqueo->detalles->map(fn ($d) => [
            'tipo' => $d->tipo,
            'denominacion' => $d->denominacion === null ? null : number_format((float) $d->denominacion, 2, '.', ''),
            'concepto' => $d->concepto,
            'cantidad' => (int) $d->cantidad,
            'subtotal' => number_format((float) $d->subtotal, 2, '.', ''),
        ])->sortBy(fn ($d) => implode('|', array_map(fn ($v) => (string) $v, $d)))->values()->all();
        $contenido = [
            'version' => 1,
            'arqueo_id' => $arqueo->id,
            'numero_arqueo' => $arqueo->numero_arqueo,
            'tipo' => $arqueo->tipo,
            'agencia_id' => $arqueo->agencia_id,
            'ventanilla_id' => $arqueo->ventanilla_id,
            'jornada_id' => $arqueo->jornada_id,
            'receptor_usuario_id' => $arqueo->receptor_usuario_id,
            'fecha_operativa' => $arqueo->fecha_operativa?->format('Y-m-d'),
            'total_billetes' => (string) $arqueo->total_billetes,
            'total_monedas' => (string) $arqueo->total_monedas,
            'total_otros_valores' => (string) $arqueo->total_otros_valores,
            'total_arqueado' => (string) $arqueo->total_arqueado,
            'total_corte' => (string) $arqueo->total_corte,
            'diferencia' => (string) $arqueo->diferencia,
            'justificacion_diferencia' => $arqueo->justificacion_diferencia,
            'observaciones' => $arqueo->observaciones,
            'detalles' => $detalles,
        ];
        if ($version === 2) {
            $contenido['version'] = 2;
            $contenido['receptor_entrante_usuario_id'] = $arqueo->receptor_entrante_usuario_id;
            $contenido['jornada_entrante_id'] = $arqueo->jornada_entrante_id;
            $contenido['receptor_entrante_nombre_historico'] = $arqueo->receptor_entrante_nombre_historico;
        }
        return hash('sha256', json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function clave(): string
    {
        $appKey = config('app.key');
        if (!is_string($appKey) || $appKey === '') {
            throw new RuntimeException('APP_KEY no configurada; no se puede firmar.');
        }
        // Derivación separada del uso de APP_KEY para cifrado Laravel.
        return hash_hmac('sha256', 'ECOSABA_ARQUEOS_AGENCIA_FIRMA_V1', $appKey, true);
    }

    public function firmarReceptor(ArqueoAgencia $arqueo, Usuario $usuario): FirmaArqueoAgencia
    {
        $usuario->loadMissing('datosPersonales', 'rol');
        $huella = $this->huella($arqueo);
        $nombre = $usuario->datosPersonales?->nombres ?: $usuario->usuario;
        $apellido = $usuario->datosPersonales?->apellidos ?: '-';
        return FirmaArqueoAgencia::create([
            'arqueo_agencia_id' => $arqueo->id,
            'usuario_id' => $usuario->id,
            'tipo_firma' => 'RECEPTOR',
            'rol_firmante' => $usuario->rol?->nombre ?? 'Receptor',
            'nombres_historicos' => mb_substr($nombre, 0, 100),
            'apellidos_historicos' => mb_substr($apellido, 0, 100),
            'hash_documento' => $huella,
            'firma_electronica' => hash_hmac('sha256', $huella, $this->clave()),
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'fecha_firma' => now(),
            'valida' => true,
        ]);
    }

    public function firmarCertificador(ArqueoAgencia $arqueo, Usuario $usuario): FirmaArqueoAgencia
    {
        $usuario->loadMissing('datosPersonales', 'rol');
        $huella = $this->huella($arqueo);
        $nombre = $usuario->datosPersonales?->nombres ?: $usuario->usuario;
        $apellido = $usuario->datosPersonales?->apellidos ?: '-';
        return FirmaArqueoAgencia::create([
            'arqueo_agencia_id' => $arqueo->id,
            'usuario_id' => $usuario->id,
            'tipo_firma' => 'CERTIFICADOR',
            'rol_firmante' => $usuario->rol?->nombre ?? 'Certificador',
            'nombres_historicos' => mb_substr($nombre, 0, 100),
            'apellidos_historicos' => mb_substr($apellido, 0, 100),
            'hash_documento' => $huella,
            'firma_electronica' => hash_hmac('sha256', $huella, $this->clave()),
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'fecha_firma' => now(),
            'valida' => true,
        ]);
    }

    public function firmarAuditor(ArqueoAgencia $arqueo, Usuario $usuario): FirmaArqueoAgencia
    {
        $usuario->loadMissing('datosPersonales', 'rol');
        $huella = $this->huella($arqueo);
        $nombre = $usuario->datosPersonales?->nombres ?: $usuario->usuario;
        $apellido = $usuario->datosPersonales?->apellidos ?: '-';
        return FirmaArqueoAgencia::create([
            'arqueo_agencia_id' => $arqueo->id,
            'usuario_id' => $usuario->id,
            'tipo_firma' => 'AUDITOR',
            'rol_firmante' => $usuario->rol?->nombre ?? 'Auditoria',
            'nombres_historicos' => mb_substr($nombre, 0, 100),
            'apellidos_historicos' => mb_substr($apellido, 0, 100),
            'hash_documento' => $huella,
            'firma_electronica' => hash_hmac('sha256', $huella, $this->clave()),
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'fecha_firma' => now(),
            'valida' => true,
        ]);
    }

    /** Firma la decisión de anulación sin alterar los datos económicos del arqueo. */
    public function firmarAnulador(ArqueoAgencia $arqueo, Usuario $usuario): FirmaArqueoAgencia
    {
        $usuario->loadMissing('datosPersonales', 'rol');
        $huella = $this->huella($arqueo);
        $nombre = $usuario->datosPersonales?->nombres ?: $usuario->usuario;
        $apellido = $usuario->datosPersonales?->apellidos ?: '-';
        return FirmaArqueoAgencia::create([
            'arqueo_agencia_id' => $arqueo->id,
            'usuario_id' => $usuario->id,
            'tipo_firma' => 'ANULADOR',
            'rol_firmante' => $usuario->rol?->nombre ?? 'Responsable',
            'nombres_historicos' => mb_substr($nombre, 0, 100),
            'apellidos_historicos' => mb_substr($apellido, 0, 100),
            'hash_documento' => $huella,
            'firma_electronica' => hash_hmac('sha256', $huella, $this->clave()),
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'fecha_firma' => now(),
            'valida' => true,
        ]);
    }


    /** Firma de entrega versión 2: protege también la identidad del receptor entrante. */
    public function firmarEntrega(ArqueoAgencia $arqueo, Usuario $usuario, string $tipo): FirmaArqueoAgencia
    {
        if ($arqueo->tipo !== 'ENTREGA' || !in_array($tipo, ['RECEPTOR','ENTRANTE','CERTIFICADOR'], true)) {
            throw new RuntimeException('Tipo de firma de entrega no permitido.');
        }
        $usuario->loadMissing('datosPersonales','rol');
        $huella = $this->huella($arqueo, 2);
        return FirmaArqueoAgencia::create([
            'arqueo_agencia_id' => $arqueo->id,
            'usuario_id' => $usuario->id,
            'tipo_firma' => $tipo,
            'rol_firmante' => $usuario->rol?->nombre ?? 'Sin rol',
            'nombres_historicos' => mb_substr($usuario->datosPersonales?->nombres ?: $usuario->usuario, 0, 100),
            'apellidos_historicos' => mb_substr($usuario->datosPersonales?->apellidos ?: '-', 0, 100),
            'hash_documento' => $huella,
            'firma_electronica' => hash_hmac('sha256', $huella, $this->clave()),
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 2,
            'fecha_firma' => now(),
            'valida' => true,
        ]);
    }

    public function verificar(ArqueoAgencia $arqueo, FirmaArqueoAgencia $firma): bool
    {
        if (!$firma->valida || $firma->algoritmo !== 'HMAC-SHA256' || !in_array((int) $firma->version_firma, [1,2], true)) return false;
        $huella = $this->huella($arqueo, (int) $firma->version_firma);
        return hash_equals($firma->hash_documento, $huella)
            && hash_equals($firma->firma_electronica, hash_hmac('sha256', $huella, $this->clave()));
    }
}
