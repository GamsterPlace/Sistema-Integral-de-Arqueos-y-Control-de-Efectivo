<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HabilitacionArqueoAtrasadoController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $estado = trim((string) $request->string('estado'));
        $agenteId = $request->integer('agente_id');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $agentes = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('a.estado', 'ACTIVO')
            ->where('r.estado', true)
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->orderBy('a.nombre_negocio')
            ->get([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ]);

        $habilitaciones = DB::table('habilitaciones_arqueos_atrasados as h')
            ->join('agentes as a', 'a.id', '=', 'h.agente_id')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->join('usuarios as u', 'u.id', '=', 'h.autorizado_por')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where('a.codigo_agente', 'like', '%' . $buscar . '%')
                            ->orWhere('a.nombre_negocio', 'like', '%' . $buscar . '%')
                            ->orWhere('a.nombre_propietario', 'like', '%' . $buscar . '%')
                            ->orWhere('r.codigo', 'like', '%' . $buscar . '%')
                            ->orWhere('r.nombre', 'like', '%' . $buscar . '%')
                            ->orWhere('reg.nombre', 'like', '%' . $buscar . '%')
                            ->orWhere('h.motivo', 'like', '%' . $buscar . '%')
                            ->orWhere('u.usuario', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.nombres', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.apellidos', 'like', '%' . $buscar . '%');
                    });
                }
            )
            ->when(
                $agenteId > 0,
                fn ($query) => $query->where('h.agente_id', $agenteId)
            )
            ->when(
                $estado !== '',
                fn ($query) => $query->where('h.estado', $estado)
            )
            ->when(
                $desde,
                fn ($query) => $query->whereDate(
                    'h.fecha_autorizada',
                    '>=',
                    $desde
                )
            )
            ->when(
                $hasta,
                fn ($query) => $query->whereDate(
                    'h.fecha_autorizada',
                    '<=',
                    $hasta
                )
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
                'h.created_at',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'u.usuario as autorizador_usuario',
                'dp.nombres as autorizador_nombres',
                'dp.apellidos as autorizador_apellidos',
            ])
            ->selectSub(
                DB::table('arqueos')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'arqueos.habilitacion_atrasada_id',
                        'h.id'
                    )
                    ->where('arqueos.estado', '!=', 'ANULADO'),
                'arqueos_generados'
            )
            ->orderByDesc('h.autorizado_at')
            ->orderByDesc('h.id')
            ->paginate(15)
            ->withQueryString();

        $pendientes = DB::table('habilitaciones_arqueos_atrasados')
            ->where('estado', 'PENDIENTE')
            ->count();

        $utilizadas = DB::table('habilitaciones_arqueos_atrasados')
            ->where('estado', 'UTILIZADA')
            ->count();

        $canceladas = DB::table('habilitaciones_arqueos_atrasados')
            ->where('estado', 'CANCELADA')
            ->count();

        $total = DB::table('habilitaciones_arqueos_atrasados')->count();

        return view('jefe.habilitaciones-atrasadas.index', [
            'habilitaciones' => $habilitaciones,
            'agentes' => $agentes,
            'buscar' => $buscar,
            'estado' => $estado,
            'agenteId' => $agenteId,
            'desde' => $desde,
            'hasta' => $hasta,
            'pendientes' => $pendientes,
            'utilizadas' => $utilizadas,
            'canceladas' => $canceladas,
            'total' => $total,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $datos = $request->validate(
            [
                'agente_id' => [
                    'required',
                    'integer',
                    Rule::exists('agentes', 'id'),
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
                'agente_id.required' =>
                    'Debe seleccionar un Agente.',
                'agente_id.exists' =>
                    'El Agente seleccionado no existe.',
                'fecha_autorizada.required' =>
                    'Debe seleccionar la fecha que desea habilitar.',
                'fecha_autorizada.before' =>
                    'La fecha habilitada debe ser anterior al día de hoy.',
                'motivo.required' =>
                    'Debe indicar el motivo de la habilitación.',
                'motivo.min' =>
                    'El motivo debe contener al menos 10 caracteres.',
                'motivo.max' =>
                    'El motivo no puede superar los 500 caracteres.',
            ]
        );

        $agenteId = (int) $datos['agente_id'];
        $fechaAutorizada = $datos['fecha_autorizada'];

        $agente = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->where('a.id', $agenteId)
            ->where('a.estado', 'ACTIVO')
            ->where('r.estado', true)
            ->select('a.id')
            ->first();

        if (! $agente) {
            return back()
                ->withInput()
                ->with(
                    'warning',
                    'El Agente seleccionado no se encuentra activo o su Ruta está inactiva.'
                );
        }

        $arqueoExistente = DB::table('arqueos')
            ->where('agente_id', $agenteId)
            ->where('tipo', 'DIARIO_AGENTE')
            ->whereDate('fecha_arqueo', $fechaAutorizada)
            ->where('estado', '!=', 'ANULADO')
            ->exists();

        if ($arqueoExistente) {
            return back()
                ->withInput()
                ->with(
                    'warning',
                    'El Agente ya posee un arqueo válido para la fecha seleccionada.'
                );
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
                ->withInput()
                ->with(
                    'warning',
                    'Ya existe una habilitación pendiente para ese Agente y fecha.'
                );
        }

        DB::transaction(function () use (
            $agenteId,
            $fechaAutorizada,
            $datos,
            $usuario
        ): void {
            $habilitacionId = DB::table(
                'habilitaciones_arqueos_atrasados'
            )->insertGetId([
                'agente_id' => $agenteId,
                'fecha_autorizada' => $fechaAutorizada,
                'motivo' => trim($datos['motivo']),
                'autorizado_por' => $usuario->id,
                'autorizado_at' => now(),
                'estado' => 'PENDIENTE',
                'utilizado_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $nuevos =
                $this->obtenerValoresAuditoriaHabilitacion(
                    $habilitacionId
                );

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Arqueos Fuera de Tiempo',
                accion: 'HABILITAR_ARQUEO_ATRASADO',
                tablaAfectada:
                    'habilitaciones_arqueos_atrasados',
                registroId: $habilitacionId,
                descripcion:
                    'Se habilitó un arqueo fuera de tiempo para el Agente '
                    . ($nuevos['codigo_agente'] ?? ('#' . $agenteId))
                    . ' — '
                    . ($nuevos['nombre_negocio'] ?? 'Sin nombre')
                    . ' correspondiente a la fecha '
                    . ($nuevos['fecha_autorizada'] ?? $fechaAutorizada)
                    . '.',
                valoresAnteriores: null,
                valoresNuevos: $nuevos
            );
        });

        return redirect()
            ->route('jefe.habilitaciones-atrasadas.index')
            ->with(
                'success',
                'El arqueo fuera de tiempo fue habilitado correctamente.'
            );
    }

    public function cancelar(
        Request $request,
        int $habilitacion
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $registro = DB::table('habilitaciones_arqueos_atrasados')
            ->where('id', $habilitacion)
            ->first();

        abort_if(
            ! $registro,
            404,
            'La habilitación solicitada no existe.'
        );

        if ($registro->estado !== 'PENDIENTE') {
            return redirect()
                ->route('jefe.habilitaciones-atrasadas.index')
                ->with(
                    'warning',
                    'Solo se pueden cancelar habilitaciones pendientes.'
                );
        }

        $arqueoGenerado = DB::table('arqueos')
            ->where('habilitacion_atrasada_id', $habilitacion)
            ->where('estado', '!=', 'ANULADO')
            ->exists();

        if ($arqueoGenerado) {
            DB::transaction(function () use (
                $habilitacion,
                $registro,
                $usuario
            ): void {
                $anteriores =
                    $this->obtenerValoresAuditoriaHabilitacion(
                        $habilitacion
                    );

                DB::table('habilitaciones_arqueos_atrasados')
                    ->where('id', $habilitacion)
                    ->update([
                        'estado' => 'UTILIZADA',
                        'utilizado_at' =>
                            $registro->utilizado_at ?: now(),
                        'updated_at' => now(),
                    ]);

                $nuevos =
                    $this->obtenerValoresAuditoriaHabilitacion(
                        $habilitacion
                    );

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Arqueos Fuera de Tiempo',
                    accion:
                        'MARCAR_HABILITACION_UTILIZADA',
                    tablaAfectada:
                        'habilitaciones_arqueos_atrasados',
                    registroId: $habilitacion,
                    descripcion:
                        'La habilitación del Agente '
                        . ($nuevos['codigo_agente']
                            ?? ('#' . ($registro->agente_id ?? '')))
                        . ' fue marcada como UTILIZADA porque ya existe un arqueo generado con esta autorización.',
                    valoresAnteriores: $anteriores,
                    valoresNuevos: $nuevos
                );
            });

            return redirect()
                ->route('jefe.habilitaciones-atrasadas.index')
                ->with(
                    'warning',
                    'La habilitación ya fue utilizada y no puede cancelarse.'
                );
        }

        DB::transaction(function () use (
            $habilitacion,
            $usuario
        ): void {
            $anteriores =
                $this->obtenerValoresAuditoriaHabilitacion(
                    $habilitacion
                );

            DB::table('habilitaciones_arqueos_atrasados')
                ->where('id', $habilitacion)
                ->update([
                    'estado' => 'CANCELADA',
                    'updated_at' => now(),
                ]);

            $nuevos =
                $this->obtenerValoresAuditoriaHabilitacion(
                    $habilitacion
                );

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Arqueos Fuera de Tiempo',
                accion: 'CANCELAR_HABILITACION_ATRASADA',
                tablaAfectada:
                    'habilitaciones_arqueos_atrasados',
                registroId: $habilitacion,
                descripcion:
                    'Se canceló la habilitación de arqueo fuera de tiempo del Agente '
                    . ($nuevos['codigo_agente'] ?? ('#' . $habilitacion))
                    . ' — '
                    . ($nuevos['nombre_negocio'] ?? 'Sin nombre')
                    . ' correspondiente a la fecha '
                    . ($nuevos['fecha_autorizada'] ?? 'sin fecha')
                    . '.',
                valoresAnteriores: $anteriores,
                valoresNuevos: $nuevos
            );
        });

        return redirect()
            ->route('jefe.habilitaciones-atrasadas.index')
            ->with(
                'success',
                'La habilitación fue cancelada correctamente.'
            );
    }

    private function obtenerValoresAuditoriaHabilitacion(
        int $habilitacionId
    ): array {
        $registro = DB::table(
            'habilitaciones_arqueos_atrasados as h'
        )
            ->join(
                'agentes as a',
                'a.id',
                '=',
                'h.agente_id'
            )
            ->join(
                'rutas as r',
                'r.id',
                '=',
                'a.ruta_id'
            )
            ->join(
                'regiones as reg',
                'reg.id',
                '=',
                'r.region_id'
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
            ->where('h.id', $habilitacionId)
            ->select([
                'h.id',
                'h.agente_id',
                'h.fecha_autorizada',
                'h.motivo',
                'h.autorizado_por',
                'h.autorizado_at',
                'h.estado',
                'h.utilizado_at',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'r.id as ruta_id',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'reg.id as region_id',
                'reg.nombre as region_nombre',
                'u.usuario as autorizador_usuario',
                'dp.nombres as autorizador_nombres',
                'dp.apellidos as autorizador_apellidos',
            ])
            ->first();

        if (! $registro) {
            return [];
        }

        $autorizador = trim(
            ($registro->autorizador_nombres ?? '')
            . ' '
            . ($registro->autorizador_apellidos ?? '')
        );

        return [
            'id' => (int) $registro->id,
            'agente_id' => (int) $registro->agente_id,
            'codigo_agente' => $registro->codigo_agente,
            'nombre_negocio' => $registro->nombre_negocio,
            'nombre_propietario' =>
                $registro->nombre_propietario,
            'ruta_id' => (int) $registro->ruta_id,
            'ruta_codigo' => $registro->ruta_codigo,
            'ruta_nombre' => $registro->ruta_nombre,
            'region_id' => (int) $registro->region_id,
            'region_nombre' => $registro->region_nombre,
            'fecha_autorizada' =>
                (string) $registro->fecha_autorizada,
            'motivo' => $registro->motivo,
            'autorizado_por' =>
                (int) $registro->autorizado_por,
            'autorizador' => $autorizador !== ''
                ? $autorizador
                : ($registro->autorizador_usuario ?? null),
            'autorizado_at' =>
                $registro->autorizado_at !== null
                    ? (string) $registro->autorizado_at
                    : null,
            'estado' => $registro->estado,
            'utilizado_at' =>
                $registro->utilizado_at !== null
                    ? (string) $registro->utilizado_at
                    : null,
        ];
    }

    private function validarJefe(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'jefedeAgentes',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
