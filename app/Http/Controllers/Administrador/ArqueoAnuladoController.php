<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ArqueoAnuladoController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $tipo = trim((string) $request->string('tipo'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $arqueos = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('arq.estado', 'ANULADO')
            ->when(
                $tipo !== '',
                fn ($q) => $q->where('arq.tipo', $tipo)
            )
            ->when(
                $desde,
                fn ($q) => $q->whereDate(
                    'arq.fecha_arqueo',
                    '>=',
                    $desde
                )
            )
            ->when(
                $hasta,
                fn ($q) => $q->whereDate(
                    'arq.fecha_arqueo',
                    '<=',
                    $hasta
                )
            )
            ->when(
                $buscar !== '',
                function ($q) use ($buscar): void {
                    $q->where(function ($s) use ($buscar): void {
                        $s->where(
                            'arq.numero_arqueo',
                            'like',
                            '%' . $buscar . '%'
                        )
                            ->orWhere(
                                'a.codigo_agente',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'a.nombre_negocio',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'r.nombre',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'reg.nombre',
                                'like',
                                '%' . $buscar . '%'
                            );
                    });
                }
            )
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'arq.estado',
                'arq.diferencia',
                'arq.fuera_fecha_ordinaria',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(20)
            ->withQueryString();

        $resumen = (object) [
            'total' => DB::table('arqueos')
                ->where('estado', 'ANULADO')
                ->count(),

            'agente' => DB::table('arqueos')
                ->where('estado', 'ANULADO')
                ->where('tipo', 'DIARIO_AGENTE')
                ->count(),

            'promotor' => DB::table('arqueos')
                ->where('estado', 'ANULADO')
                ->where('tipo', 'VISITA_PROMOTOR')
                ->count(),

            'extemporaneos' => DB::table('arqueos')
                ->where('estado', 'ANULADO')
                ->where('fuera_fecha_ordinaria', true)
                ->count(),
        ];

        return view(
            'administrador.arqueos-anulados.index',
            compact(
                'arqueos',
                'buscar',
                'tipo',
                'desde',
                'hasta',
                'resumen'
            )
        );
    }

    public function anular(
        Request $request,
        Arqueo $arqueo
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

        abort_if(
            $arqueo->estado === 'ANULADO',
            422,
            'El arqueo ya se encuentra anulado.'
        );

        $validated = $request->validate([
            'motivo' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
        ]);

        DB::transaction(function () use (
            $arqueo,
            $usuario,
            $validated
        ): void {
            $registroBloqueado = DB::table('arqueos')
                ->where('id', $arqueo->id)
                ->lockForUpdate()
                ->first();

            abort_if(
                ! $registroBloqueado,
                404,
                'El arqueo solicitado no existe.'
            );

            abort_if(
                $registroBloqueado->estado === 'ANULADO',
                422,
                'El arqueo ya se encuentra anulado.'
            );

            $valoresAnteriores =
                $this->obtenerValoresAuditoriaArqueo(
                    $arqueo->id
                );

            $update = [
                'estado' => 'ANULADO',
                'updated_at' => now(),
            ];

            if (
                Schema::hasColumn(
                    'arqueos',
                    'motivo_anulacion'
                )
            ) {
                $update['motivo_anulacion'] =
                    trim($validated['motivo']);
            }

            if (
                Schema::hasColumn(
                    'arqueos',
                    'anulado_por'
                )
            ) {
                $update['anulado_por'] =
                    $usuario->id;
            }

            if (
                Schema::hasColumn(
                    'arqueos',
                    'anulado_at'
                )
            ) {
                $update['anulado_at'] =
                    now();
            }

            DB::table('arqueos')
                ->where('id', $arqueo->id)
                ->update($update);

            $valoresNuevos =
                $this->obtenerValoresAuditoriaArqueo(
                    $arqueo->id
                );

            $valoresNuevos['motivo_anulacion_registrado'] =
                trim($validated['motivo']);

            $valoresNuevos['anulado_por_usuario_id'] =
                (int) $usuario->id;

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Arqueos Anulados',
                accion: 'ANULAR_ARQUEO',
                tablaAfectada: 'arqueos',
                registroId: $arqueo->id,
                descripcion:
                    'El Administrador anuló el arqueo '
                    . ($valoresNuevos['numero_arqueo']
                        ?? ('#' . $arqueo->id))
                    . '. Motivo: '
                    . trim($validated['motivo']),
                valoresAnteriores:
                    $valoresAnteriores,
                valoresNuevos:
                    $valoresNuevos
            );
        });

        return back()->with(
            'success',
            'Arqueo anulado correctamente.'
        );
    }

    private function obtenerValoresAuditoriaArqueo(
        int $arqueoId
    ): array {
        $columnas = [
            'arq.id',
            'arq.numero_arqueo',
            'arq.agente_id',
            'arq.creado_por',
            'arq.tipo',
            'arq.estado',
            'arq.fecha_arqueo',
            'arq.total_billetes',
            'arq.total_monedas',
            'arq.total_arqueado',
            'arq.saldo_sistema',
            'arq.diferencia',
            'arq.observaciones',
            'arq.fuera_fecha_ordinaria',
            'arq.habilitacion_atrasada_id',
            'arq.codigo_agente_historico',
            'arq.nombre_negocio_historico',
            'arq.nombre_propietario_historico',
            'arq.direccion_historica',
            'arq.ruta_historica',
            'arq.region_historica',
            'arq.pendiente_certificacion_at',
            'arq.certificado_at',
            'arq.anulado_at',
        ];

        if (
            Schema::hasColumn(
                'arqueos',
                'motivo_anulacion'
            )
        ) {
            $columnas[] = 'arq.motivo_anulacion';
        }

        if (
            Schema::hasColumn(
                'arqueos',
                'anulado_por'
            )
        ) {
            $columnas[] = 'arq.anulado_por';
        }

        $registro = DB::table('arqueos as arq')
            ->where('arq.id', $arqueoId)
            ->select($columnas)
            ->first();

        if (! $registro) {
            return [];
        }

        $resultado = [
            'id' => (int) $registro->id,
            'numero_arqueo' =>
                $registro->numero_arqueo,
            'agente_id' =>
                (int) $registro->agente_id,
            'creado_por' =>
                (int) $registro->creado_por,
            'tipo' => $registro->tipo,
            'estado' => $registro->estado,
            'fecha_arqueo' =>
                (string) $registro->fecha_arqueo,
            'total_billetes' =>
                (float) $registro->total_billetes,
            'total_monedas' =>
                (float) $registro->total_monedas,
            'total_arqueado' =>
                (float) $registro->total_arqueado,
            'saldo_sistema' =>
                (float) $registro->saldo_sistema,
            'diferencia' =>
                (float) $registro->diferencia,
            'observaciones' =>
                $registro->observaciones,
            'fuera_fecha_ordinaria' =>
                (bool) $registro->fuera_fecha_ordinaria,
            'habilitacion_atrasada_id' =>
                $registro->habilitacion_atrasada_id !== null
                    ? (int) $registro->habilitacion_atrasada_id
                    : null,
            'codigo_agente_historico' =>
                $registro->codigo_agente_historico,
            'nombre_negocio_historico' =>
                $registro->nombre_negocio_historico,
            'nombre_propietario_historico' =>
                $registro->nombre_propietario_historico,
            'direccion_historica' =>
                $registro->direccion_historica,
            'ruta_historica' =>
                $registro->ruta_historica,
            'region_historica' =>
                $registro->region_historica,
            'pendiente_certificacion_at' =>
                $registro->pendiente_certificacion_at !== null
                    ? (string) $registro->pendiente_certificacion_at
                    : null,
            'certificado_at' =>
                $registro->certificado_at !== null
                    ? (string) $registro->certificado_at
                    : null,
            'anulado_at' =>
                $registro->anulado_at !== null
                    ? (string) $registro->anulado_at
                    : null,
        ];

        if (
            property_exists(
                $registro,
                'motivo_anulacion'
            )
        ) {
            $resultado['motivo_anulacion'] =
                $registro->motivo_anulacion;
        }

        if (
            property_exists(
                $registro,
                'anulado_por'
            )
        ) {
            $resultado['anulado_por'] =
                $registro->anulado_por !== null
                    ? (int) $registro->anulado_por
                    : null;
        }

        return $resultado;
    }

    private function validarAdministrador(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Administrador',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
