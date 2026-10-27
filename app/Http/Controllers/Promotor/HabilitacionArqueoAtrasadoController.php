<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\HabilitacionArqueoAtrasado;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HabilitacionArqueoAtrasadoController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        $busqueda = trim((string) $request->string('buscar'));
        $estado = trim((string) $request->string('estado'));

        $agentesAsignados = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join(
                'asignaciones_promotor_ruta as apr',
                'apr.ruta_id',
                '=',
                'r.id'
            )
            ->where('apr.promotor_usuario_id', $usuario->id)
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', today())
            ->where(function ($query): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', today());
            })
            ->where('a.estado', 'ACTIVO')
            ->where('r.estado', true)
            ->select([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
            ])
            ->distinct()
            ->orderBy('a.nombre_negocio')
            ->get();

        $habilitaciones = DB::table(
            'habilitaciones_arqueos_atrasados as h'
        )
            ->join('agentes as a', 'a.id', '=', 'h.agente_id')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->join(
                'asignaciones_promotor_ruta as apr',
                'apr.ruta_id',
                '=',
                'r.id'
            )
            ->where('apr.promotor_usuario_id', $usuario->id)
            ->where('h.autorizado_por', $usuario->id)
            ->when(
                $estado !== '',
                fn ($query) => $query->where('h.estado', $estado)
            )
            ->when(
                $busqueda !== '',
                function ($query) use ($busqueda): void {
                    $query->where(function ($subquery) use ($busqueda): void {
                        $subquery
                            ->where(
                                'a.codigo_agente',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'a.nombre_negocio',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'r.nombre',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'reg.nombre',
                                'like',
                                '%' . $busqueda . '%'
                            );
                    });
                }
            )
            ->select([
                'h.id',
                'h.fecha_autorizada',
                'h.motivo',
                'h.autorizado_at',
                'h.estado',
                'h.utilizado_at',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->distinct()
            ->orderByDesc('h.autorizado_at')
            ->paginate(12)
            ->withQueryString();

        return view(
            'promotor.habilitaciones-arqueos-atrasados.index',
            [
                'agentesAsignados' => $agentesAsignados,
                'habilitaciones' => $habilitaciones,
                'busqueda' => $busqueda,
                'estado' => $estado,
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        $datosValidados = $request->validate(
            [
                'agente_id' => [
                    'required',
                    'integer',
                    'exists:agentes,id',
                ],
                'fecha_autorizada' => [
                    'required',
                    'date',
                    'before:today',
                ],
                'motivo' => [
                    'required',
                    'string',
                    'min:10',
                    'max:500',
                ],
            ],
            [
                'agente_id.required' => 'Debe seleccionar un agente.',
                'agente_id.exists' => 'El agente seleccionado no existe.',
                'fecha_autorizada.required' => 'Debe seleccionar la fecha que desea habilitar.',
                'fecha_autorizada.before' => 'La fecha habilitada debe ser anterior al día de hoy.',
                'motivo.required' => 'Debe indicar el motivo de la habilitación.',
                'motivo.min' => 'El motivo debe contener al menos 10 caracteres.',
                'motivo.max' => 'El motivo no puede superar los 500 caracteres.',
            ]
        );

        $agenteId = (int) $datosValidados['agente_id'];

        $this->validarAgenteAsignado(
            $usuario->id,
            $agenteId
        );

        $fechaAutorizada = Carbon::parse(
            $datosValidados['fecha_autorizada']
        )->toDateString();

        $arqueoExistente = DB::table('arqueos')
            ->where('agente_id', $agenteId)
            ->where('tipo', 'DIARIO_AGENTE')
            ->whereDate('fecha_arqueo', $fechaAutorizada)
            ->where('estado', '!=', 'ANULADO')
            ->exists();

        if ($arqueoExistente) {
            return back()
                ->withErrors([
                    'fecha_autorizada' =>
                        'El agente ya tiene un arqueo válido registrado para esa fecha.',
                ])
                ->withInput();
        }

        $habilitacionPendiente = DB::table(
            'habilitaciones_arqueos_atrasados'
        )
            ->where('agente_id', $agenteId)
            ->whereDate('fecha_autorizada', $fechaAutorizada)
            ->where('estado', 'PENDIENTE')
            ->exists();

        if ($habilitacionPendiente) {
            return back()
                ->withErrors([
                    'fecha_autorizada' =>
                        'Ya existe una habilitación pendiente para ese agente y fecha.',
                ])
                ->withInput();
        }

        $habilitacion = HabilitacionArqueoAtrasado::create([
            'agente_id' => $agenteId,
            'fecha_autorizada' => $fechaAutorizada,
            'motivo' => trim($datosValidados['motivo']),
            'autorizado_por' => $usuario->id,
            'autorizado_at' => now(),
            'estado' => 'PENDIENTE',
            'utilizado_at' => null,
        ]);

        $agente = DB::table('agentes')
            ->where('id', $agenteId)
            ->first([
                'codigo_agente',
                'nombre_negocio',
            ]);

        app(AuditoriaService::class)->registrar(
            usuario: $usuario,
            modulo: 'Arqueos Extemporáneos',
            accion: 'HABILITAR_ARQUEO_ATRASADO',
            tablaAfectada: 'habilitaciones_arqueos_atrasados',
            registroId: $habilitacion->id,
            descripcion:
                'El Promotor habilitó un arqueo fuera de tiempo para el Agente '
                . ($agente?->codigo_agente ?? $agenteId)
                . ' — '
                . ($agente?->nombre_negocio ?? 'Agente')
                . ', correspondiente a la fecha '
                . $fechaAutorizada
                . '.',
            valoresAnteriores: null,
            valoresNuevos: [
                'id' => (int) $habilitacion->id,
                'agente_id' => (int) $habilitacion->agente_id,
                'fecha_autorizada' =>
                    $habilitacion->fecha_autorizada,
                'motivo' => $habilitacion->motivo,
                'autorizado_por' =>
                    (int) $habilitacion->autorizado_por,
                'autorizado_at' =>
                    $habilitacion->autorizado_at,
                'estado' => $habilitacion->estado,
                'utilizado_at' =>
                    $habilitacion->utilizado_at,
            ]
        );

        return redirect()
            ->route(
                'promotor.habilitaciones-atrasadas.index'
            )
            ->with(
                'success',
                'El arqueo fuera de tiempo fue habilitado correctamente.'
            );
    }

    public function cancelar(
        Request $request,
        HabilitacionArqueoAtrasado $habilitacion
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarPromotor($usuario);

        $this->validarAgenteAsignado(
            $usuario->id,
            (int) $habilitacion->agente_id
        );

        abort_if(
            $habilitacion->autorizado_por !== $usuario->id,
            403,
            'No puede cancelar una habilitación creada por otro usuario.'
        );

        abort_if(
            $habilitacion->estado !== 'PENDIENTE',
            422,
            'Solo se pueden cancelar habilitaciones pendientes.'
        );

        $valoresAnteriores = [
            'id' => (int) $habilitacion->id,
            'agente_id' => (int) $habilitacion->agente_id,
            'fecha_autorizada' => $habilitacion->fecha_autorizada,
            'motivo' => $habilitacion->motivo,
            'autorizado_por' => (int) $habilitacion->autorizado_por,
            'autorizado_at' => $habilitacion->autorizado_at,
            'estado' => $habilitacion->estado,
            'utilizado_at' => $habilitacion->utilizado_at,
        ];

        $habilitacion->update([
            'estado' => 'CANCELADA',
        ]);

        $habilitacion->refresh();

        $agente = DB::table('agentes')
            ->where('id', $habilitacion->agente_id)
            ->first([
                'codigo_agente',
                'nombre_negocio',
            ]);

        app(AuditoriaService::class)->registrar(
            usuario: $usuario,
            modulo: 'Arqueos Extemporáneos',
            accion: 'CANCELAR_HABILITACION_ARQUEO_ATRASADO',
            tablaAfectada: 'habilitaciones_arqueos_atrasados',
            registroId: $habilitacion->id,
            descripcion:
                'El Promotor canceló la habilitación de arqueo fuera de tiempo '
                . 'para el Agente '
                . ($agente?->codigo_agente ?? $habilitacion->agente_id)
                . ' — '
                . ($agente?->nombre_negocio ?? 'Agente')
                . ', correspondiente a la fecha '
                . $habilitacion->fecha_autorizada
                . '.',
            valoresAnteriores: $valoresAnteriores,
            valoresNuevos: [
                'id' => (int) $habilitacion->id,
                'agente_id' => (int) $habilitacion->agente_id,
                'fecha_autorizada' =>
                    $habilitacion->fecha_autorizada,
                'motivo' => $habilitacion->motivo,
                'autorizado_por' =>
                    (int) $habilitacion->autorizado_por,
                'autorizado_at' =>
                    $habilitacion->autorizado_at,
                'estado' => $habilitacion->estado,
                'utilizado_at' =>
                    $habilitacion->utilizado_at,
            ]
        );

        return redirect()
            ->route(
                'promotor.habilitaciones-atrasadas.index'
            )
            ->with(
                'success',
                'La habilitación fue cancelada correctamente.'
            );
    }

    private function validarPromotor(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'Promotor',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }

    private function validarAgenteAsignado(
        int $promotorUsuarioId,
        int $agenteId
    ): void {
        $asignado = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join(
                'asignaciones_promotor_ruta as apr',
                'apr.ruta_id',
                '=',
                'r.id'
            )
            ->where('a.id', $agenteId)
            ->where('a.estado', 'ACTIVO')
            ->where('r.estado', true)
            ->where('apr.promotor_usuario_id', $promotorUsuarioId)
            ->where('apr.estado', true)
            ->whereDate('apr.fecha_inicio', '<=', today())
            ->where(function ($query): void {
                $query
                    ->whereNull('apr.fecha_fin')
                    ->orWhereDate('apr.fecha_fin', '>=', today());
            })
            ->exists();

        abort_unless(
            $asignado,
            403,
            'El agente no pertenece a una ruta asignada al Promotor.'
        );
    }
}
