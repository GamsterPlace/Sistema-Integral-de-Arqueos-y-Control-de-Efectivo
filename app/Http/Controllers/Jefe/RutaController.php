<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RutaController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $regionId = $request->integer('region_id');
        $estado = trim((string) $request->string('estado'));

        $regiones = DB::table('regiones')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
                'estado',
            ]);

        $rutas = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->when($buscar !== '', function ($query) use ($buscar): void {
                $query->where(function ($subquery) use ($buscar): void {
                    $subquery
                        ->where('r.codigo', 'like', '%' . $buscar . '%')
                        ->orWhere('r.nombre', 'like', '%' . $buscar . '%')
                        ->orWhere('reg.nombre', 'like', '%' . $buscar . '%');
                });
            })
            ->when(
                $regionId > 0,
                fn ($query) => $query->where('r.region_id', $regionId)
            )
            ->when(
                $estado === 'ACTIVA',
                fn ($query) => $query->where('r.estado', true)
            )
            ->when(
                $estado === 'INACTIVA',
                fn ($query) => $query->where('r.estado', false)
            )
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.region_id',
                'r.estado',
                'reg.nombre as region_nombre',
            ])
            ->selectSub(
                DB::table('agentes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('agentes.ruta_id', 'r.id'),
                'total_agentes'
            )
            ->selectSub(
                DB::table('agentes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('agentes.ruta_id', 'r.id')
                    ->where('agentes.estado', 'ACTIVO'),
                'agentes_activos'
            )
            ->selectSub(
                DB::table('agentes as a2')
                    ->join(
                        'arqueos as arq2',
                        'arq2.agente_id',
                        '=',
                        'a2.id'
                    )
                    ->selectRaw('COUNT(DISTINCT a2.id)')
                    ->whereColumn('a2.ruta_id', 'r.id')
                    ->where('a2.estado', 'ACTIVO')
                    ->where('arq2.tipo', 'DIARIO_AGENTE')
                    ->whereDate('arq2.fecha_arqueo', today())
                    ->where('arq2.estado', '!=', 'ANULADO'),
                'arqueados_hoy'
            )
            ->selectSub(
                DB::table('asignaciones_promotor_ruta as apr')
                    ->selectRaw(
                        'COUNT(DISTINCT apr.promotor_usuario_id)'
                    )
                    ->whereColumn('apr.ruta_id', 'r.id')
                    ->where('apr.estado', true)
                    ->whereDate('apr.fecha_inicio', '<=', today())
                    ->where(function ($query): void {
                        $query
                            ->whereNull('apr.fecha_fin')
                            ->orWhereDate(
                                'apr.fecha_fin',
                                '>=',
                                today()
                            );
                    }),
                'promotores_asignados'
            )
            ->selectSub(
                DB::table('asignaciones_promotor_ruta as apr')
                    ->selectRaw(
                        'COUNT(DISTINCT apr.promotor_usuario_id)'
                    )
                    ->whereColumn('apr.ruta_id', 'r.id')
                    ->where('apr.estado', true)
                    ->where(
                        'apr.tipo_asignacion',
                        'PERMANENTE'
                    )
                    ->whereDate('apr.fecha_inicio', '<=', today())
                    ->where(function ($query): void {
                        $query
                            ->whereNull('apr.fecha_fin')
                            ->orWhereDate(
                                'apr.fecha_fin',
                                '>=',
                                today()
                            );
                    }),
                'promotores_permanentes'
            )
            ->selectSub(
                DB::table('asignaciones_promotor_ruta as apr')
                    ->selectRaw(
                        'COUNT(DISTINCT apr.promotor_usuario_id)'
                    )
                    ->whereColumn('apr.ruta_id', 'r.id')
                    ->where('apr.estado', true)
                    ->where(
                        'apr.tipo_asignacion',
                        'TEMPORAL'
                    )
                    ->whereDate('apr.fecha_inicio', '<=', today())
                    ->where(function ($query): void {
                        $query
                            ->whereNull('apr.fecha_fin')
                            ->orWhereDate(
                                'apr.fecha_fin',
                                '>=',
                                today()
                            );
                    }),
                'promotores_temporales'
            )
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->paginate(15)
            ->withQueryString();

        $totalRutas = DB::table('rutas')->count();

        $rutasActivas = DB::table('rutas')
            ->where('estado', true)
            ->count();

        $rutasInactivas = DB::table('rutas')
            ->where('estado', false)
            ->count();

        $rutasSinPromotor = DB::table('rutas as r')
            ->where('r.estado', true)
            ->whereNotExists(function ($query): void {
                $query
                    ->selectRaw('1')
                    ->from('asignaciones_promotor_ruta as apr')
                    ->whereColumn('apr.ruta_id', 'r.id')
                    ->where('apr.estado', true)
                    ->whereDate('apr.fecha_inicio', '<=', today())
                    ->where(function ($subquery): void {
                        $subquery
                            ->whereNull('apr.fecha_fin')
                            ->orWhereDate(
                                'apr.fecha_fin',
                                '>=',
                                today()
                            );
                    });
            })
            ->count();

        return view('jefe.rutas.index', compact(
            'rutas',
            'regiones',
            'buscar',
            'regionId',
            'estado',
            'totalRutas',
            'rutasActivas',
            'rutasInactivas',
            'rutasSinPromotor'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $datos = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rutas', 'codigo'),
            ],
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'region_id' => [
                'required',
                'integer',
                Rule::exists('regiones', 'id'),
            ],
        ]);

        $regionActiva = DB::table('regiones')
            ->where('id', $datos['region_id'])
            ->where('estado', true)
            ->exists();

        if (! $regionActiva) {
            return back()
                ->withInput()
                ->with(
                    'warning',
                    'La región seleccionada está inactiva.'
                );
        }

        DB::transaction(function () use ($datos, $usuario): void {
            $rutaId = DB::table('rutas')->insertGetId([
                'codigo' => trim($datos['codigo']),
                'nombre' => trim($datos['nombre']),
                'region_id' => (int) $datos['region_id'],
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $nuevos = $this->obtenerValoresAuditoriaRuta(
                $rutaId
            );

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Rutas',
                accion: 'CREAR_RUTA',
                tablaAfectada: 'rutas',
                registroId: $rutaId,
                descripcion:
                    'Se creó la ruta '
                    . ($nuevos['codigo'] ?? ('#' . $rutaId))
                    . ' — '
                    . ($nuevos['nombre'] ?? 'Sin nombre')
                    . ' en la región '
                    . ($nuevos['region_nombre'] ?? 'Sin región')
                    . '.',
                valoresAnteriores: null,
                valoresNuevos: $nuevos
            );
        });

        return redirect()
            ->route('jefe.rutas.index')
            ->with(
                'success',
                'La ruta fue creada correctamente.'
            );
    }

    public function update(
        Request $request,
        int $ruta
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $actual = DB::table('rutas')
            ->where('id', $ruta)
            ->first();

        abort_if(
            ! $actual,
            404,
            'La ruta solicitada no existe.'
        );

        $datos = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rutas', 'codigo')->ignore($ruta),
            ],
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'region_id' => [
                'required',
                'integer',
                Rule::exists('regiones', 'id'),
            ],
        ]);

        $regionActiva = DB::table('regiones')
            ->where('id', $datos['region_id'])
            ->where('estado', true)
            ->exists();

        if (! $regionActiva) {
            return back()
                ->withInput()
                ->with(
                    'warning',
                    'La región seleccionada está inactiva.'
                );
        }

        DB::transaction(
            function () use ($ruta, $datos, $usuario): void {
                $anteriores =
                    $this->obtenerValoresAuditoriaRuta($ruta);

                DB::table('rutas')
                    ->where('id', $ruta)
                    ->update([
                        'codigo' => trim($datos['codigo']),
                        'nombre' => trim($datos['nombre']),
                        'region_id' => (int) $datos['region_id'],
                        'updated_at' => now(),
                    ]);

                $nuevos =
                    $this->obtenerValoresAuditoriaRuta($ruta);

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Rutas',
                    accion: 'EDITAR_RUTA',
                    tablaAfectada: 'rutas',
                    registroId: $ruta,
                    descripcion:
                        'Se actualizó la ruta '
                        . ($nuevos['codigo'] ?? ('#' . $ruta))
                        . ' — '
                        . ($nuevos['nombre'] ?? 'Sin nombre')
                        . '.',
                    valoresAnteriores: $anteriores,
                    valoresNuevos: $nuevos
                );
            }
        );

        return redirect()
            ->route('jefe.rutas.index')
            ->with(
                'success',
                'La ruta fue actualizada correctamente.'
            );
    }

    public function agentes(
        Request $request,
        int $ruta
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $rutaActual = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('r.id', $ruta)
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.estado',
                'r.region_id',
                'reg.nombre as region_nombre',
            ])
            ->first();

        abort_if(
            ! $rutaActual,
            404,
            'La ruta solicitada no existe.'
        );

        $buscar = trim((string) $request->string('buscar'));

        $agentes = DB::table('agentes as a')
            ->leftJoin(
                'usuarios as u',
                'u.id',
                '=',
                'a.usuario_id'
            )
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->where('a.ruta_id', $ruta)
            ->when($buscar !== '', function ($query) use ($buscar): void {
                $query->where(
                    function ($subquery) use ($buscar): void {
                        $subquery
                            ->where(
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
                                'a.nombre_propietario',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'u.usuario',
                                'like',
                                '%' . $buscar . '%'
                            );
                    }
                );
            })
            ->select([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'a.estado',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->orderBy('a.nombre_negocio')
            ->paginate(20)
            ->withQueryString();

        $rutasDestino = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('r.estado', true)
            ->where('reg.estado', true)
            ->where('r.id', '!=', $ruta)
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get([
                'r.id',
                'r.codigo',
                'r.nombre',
                'reg.nombre as region_nombre',
            ]);

        return view('jefe.rutas.agentes', compact(
            'rutaActual',
            'agentes',
            'rutasDestino',
            'buscar'
        ));
    }

    public function reasignarAgentes(
        Request $request,
        int $ruta
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        abort_if(
            ! DB::table('rutas')->where('id', $ruta)->exists(),
            404,
            'La ruta solicitada no existe.'
        );

        $datos = $request->validate([
            'agentes' => [
                'required',
                'array',
                'min:1',
            ],
            'agentes.*' => [
                'integer',
                Rule::exists('agentes', 'id'),
            ],
            'ruta_destino_id' => [
                'required',
                'integer',
                Rule::exists('rutas', 'id'),
            ],
        ]);

        if ((int) $datos['ruta_destino_id'] === $ruta) {
            return back()->with(
                'warning',
                'La ruta de destino debe ser diferente de la ruta actual.'
            );
        }

        $destino = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('r.id', $datos['ruta_destino_id'])
            ->where('r.estado', true)
            ->where('reg.estado', true)
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'reg.nombre as region_nombre',
            ])
            ->first();

        if (! $destino) {
            return back()->with(
                'warning',
                'La ruta de destino o su región se encuentra inactiva.'
            );
        }

        $ids = collect($datos['agentes'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $validos = DB::table('agentes')
            ->where('ruta_id', $ruta)
            ->whereIn('id', $ids)
            ->pluck('id');

        if ($validos->count() !== $ids->count()) {
            return back()->with(
                'warning',
                'Uno o más agentes ya no pertenecen a la ruta seleccionada.'
            );
        }

        DB::transaction(
            function () use (
                $validos,
                $destino,
                $usuario
            ): void {
                $agentesAnteriores = [];

                foreach ($validos as $agenteId) {
                    $agenteId = (int) $agenteId;

                    $agentesAnteriores[$agenteId] =
                        $this->obtenerValoresAuditoriaAgente(
                            $agenteId
                        );
                }

                DB::table('agentes')
                    ->whereIn('id', $validos)
                    ->update([
                        'ruta_id' => $destino->id,
                        'updated_at' => now(),
                    ]);

                foreach ($validos as $agenteId) {
                    $agenteId = (int) $agenteId;

                    $anteriores =
                        $agentesAnteriores[$agenteId] ?? [];

                    $nuevos =
                        $this->obtenerValoresAuditoriaAgente(
                            $agenteId
                        );

                    app(AuditoriaService::class)->registrar(
                        usuario: $usuario,
                        modulo: 'Rutas',
                        accion: 'REASIGNAR_AGENTE_RUTA',
                        tablaAfectada: 'agentes',
                        registroId: $agenteId,
                        descripcion:
                            'Se reasignó el agente '
                            . (
                                $nuevos['codigo_agente']
                                ?? ('#' . $agenteId)
                            )
                            . ' — '
                            . (
                                $nuevos['nombre_negocio']
                                ?? 'Sin nombre'
                            )
                            . ' de la ruta '
                            . (
                                $anteriores['ruta_codigo']
                                ?? '—'
                            )
                            . ' — '
                            . (
                                $anteriores['ruta_nombre']
                                ?? 'Sin ruta'
                            )
                            . ' a la ruta '
                            . (
                                $nuevos['ruta_codigo']
                                ?? '—'
                            )
                            . ' — '
                            . (
                                $nuevos['ruta_nombre']
                                ?? $destino->nombre
                            )
                            . '.',
                        valoresAnteriores: $anteriores,
                        valoresNuevos: $nuevos
                    );
                }
            }
        );

        return redirect()
            ->route('jefe.rutas.agentes', $ruta)
            ->with(
                'success',
                $validos->count()
                . ' agente(s) fueron reasignados correctamente a '
                . $destino->nombre
                . '.'
            );
    }

    public function promotor(
        Request $request,
        int $ruta
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $rutaActual = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('r.id', $ruta)
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.estado',
                'r.region_id',
                'reg.nombre as region_nombre',
                'reg.estado as region_estado',
            ])
            ->first();

        abort_if(
            ! $rutaActual,
            404,
            'La ruta solicitada no existe.'
        );

        $promotoresAsignados =
            DB::table('asignaciones_promotor_ruta as apr')
                ->join(
                    'usuarios as u',
                    'u.id',
                    '=',
                    'apr.promotor_usuario_id'
                )
                ->join(
                    'roles as rol',
                    'rol.id',
                    '=',
                    'u.rol_id'
                )
                ->leftJoin(
                    'datos_personales as dp',
                    'dp.usuario_id',
                    '=',
                    'u.id'
                )
                ->where('apr.ruta_id', $ruta)
                ->where('rol.nombre', 'Promotor')
                ->where('apr.estado', true)
                ->whereDate(
                    'apr.fecha_inicio',
                    '<=',
                    today()
                )
                ->where(function ($query): void {
                    $query
                        ->whereNull('apr.fecha_fin')
                        ->orWhereDate(
                            'apr.fecha_fin',
                            '>=',
                            today()
                        );
                })
                ->select([
                    'apr.id as asignacion_id',
                    'apr.promotor_usuario_id',
                    'apr.fecha_inicio',
                    'apr.fecha_fin',
                    'apr.tipo_asignacion',
                    'apr.motivo',
                    'u.usuario',
                    'u.estado as usuario_estado',
                    'dp.nombres',
                    'dp.apellidos',
                ])
                ->orderByRaw(
                    "CASE
                        WHEN apr.tipo_asignacion = 'PERMANENTE'
                        THEN 1
                        ELSE 2
                    END"
                )
                ->orderBy('dp.nombres')
                ->orderBy('dp.apellidos')
                ->orderBy('u.usuario')
                ->get();

        $promotores = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->where('rol.nombre', 'Promotor')
            ->where('u.estado', 'ACTIVO')
            ->select([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->orderBy('u.usuario')
            ->get();

        $rutasDestino = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('r.id', '!=', $ruta)
            ->where('r.estado', true)
            ->where('reg.estado', true)
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'reg.nombre as region_nombre',
            ])
            ->orderBy('reg.nombre')
            ->orderBy('r.nombre')
            ->get();

        $historial =
            DB::table('asignaciones_promotor_ruta as apr')
                ->join(
                    'usuarios as u',
                    'u.id',
                    '=',
                    'apr.promotor_usuario_id'
                )
                ->leftJoin(
                    'datos_personales as dp',
                    'dp.usuario_id',
                    '=',
                    'u.id'
                )
                ->where('apr.ruta_id', $ruta)
                ->select([
                    'apr.id',
                    'apr.fecha_inicio',
                    'apr.fecha_fin',
                    'apr.estado',
                    'apr.tipo_asignacion',
                    'apr.motivo',
                    'u.usuario',
                    'dp.nombres',
                    'dp.apellidos',
                ])
                ->orderByDesc('apr.fecha_inicio')
                ->orderByDesc('apr.id')
                ->limit(20)
                ->get();

        return view('jefe.rutas.promotor', compact(
            'rutaActual',
            'promotoresAsignados',
            'promotores',
            'rutasDestino',
            'historial'
        ));
    }

    public function asignarPromotor(
        Request $request,
        int $ruta
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $rutaActual =
            $this->obtenerRutaActivaParaAsignacion($ruta);

        if (! $rutaActual) {
            return back()->with(
                'warning',
                'La ruta o su región se encuentra inactiva.'
            );
        }

        $datos = $request->validate([
            'promotor_usuario_id' => [
                'required',
                'integer',
                Rule::exists('usuarios', 'id'),
            ],
        ]);

        $promotor = $this->obtenerPromotorActivo(
            (int) $datos['promotor_usuario_id']
        );

        if (! $promotor) {
            return back()->with(
                'warning',
                'El usuario seleccionado no es un Promotor activo.'
            );
        }

        $duplicada =
            DB::table('asignaciones_promotor_ruta')
                ->where(
                    'promotor_usuario_id',
                    $promotor->id
                )
                ->where('ruta_id', $ruta)
                ->where(
                    'tipo_asignacion',
                    'PERMANENTE'
                )
                ->where('estado', true)
                ->whereDate(
                    'fecha_inicio',
                    '<=',
                    today()
                )
                ->where(function ($query): void {
                    $query
                        ->whereNull('fecha_fin')
                        ->orWhereDate(
                            'fecha_fin',
                            '>=',
                            today()
                        );
                })
                ->exists();

        if ($duplicada) {
            return back()->with(
                'warning',
                'El Promotor ya tiene una asignación permanente vigente en esta ruta.'
            );
        }

        DB::transaction(
            function () use (
                $usuario,
                $promotor,
                $rutaActual
            ): void {
                $asignacionId =
                    DB::table('asignaciones_promotor_ruta')
                        ->insertGetId([
                            'promotor_usuario_id' =>
                                $promotor->id,
                            'ruta_id' =>
                                $rutaActual->id,
                            'fecha_inicio' =>
                                today()->toDateString(),
                            'fecha_fin' => null,
                            'estado' => true,
                            'tipo_asignacion' =>
                                'PERMANENTE',
                            'motivo' => null,
                            'asignado_por' =>
                                $usuario->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Rutas',
                    accion: 'ASIGNAR_PROMOTOR_RUTA',
                    tablaAfectada:
                        'asignaciones_promotor_ruta',
                    registroId: $asignacionId,
                    descripcion:
                        'Se asignó permanentemente al Promotor '
                        . $this->nombrePromotor($promotor)
                        . ' a la ruta '
                        . $rutaActual->codigo
                        . ' — '
                        . $rutaActual->nombre
                        . '.',
                    valoresAnteriores: null,
                    valoresNuevos: [
                        'id' => $asignacionId,
                        'promotor_usuario_id' =>
                            (int) $promotor->id,
                        'promotor' =>
                            $this->nombrePromotor(
                                $promotor
                            ),
                        'ruta_id' =>
                            (int) $rutaActual->id,
                        'ruta_codigo' =>
                            $rutaActual->codigo,
                        'ruta_nombre' =>
                            $rutaActual->nombre,
                        'region_nombre' =>
                            $rutaActual->region_nombre,
                        'tipo_asignacion' =>
                            'PERMANENTE',
                        'fecha_inicio' =>
                            today()->toDateString(),
                        'fecha_fin' => null,
                        'estado' => true,
                    ]
                );
            }
        );

        return redirect()
            ->route('jefe.rutas.promotor', $ruta)
            ->with(
                'success',
                'El Promotor fue asignado correctamente a la ruta.'
            );
    }

    public function reasignarPromotor(
        Request $request,
        int $ruta
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        abort_if(
            ! DB::table('rutas')
                ->where('id', $ruta)
                ->exists(),
            404,
            'La ruta solicitada no existe.'
        );

        $datos = $request->validate([
            'asignacion_id' => [
                'required',
                'integer',
                Rule::exists(
                    'asignaciones_promotor_ruta',
                    'id'
                ),
            ],
            'ruta_destino_id' => [
                'required',
                'integer',
                Rule::exists('rutas', 'id'),
            ],
        ]);

        if ((int) $datos['ruta_destino_id'] === $ruta) {
            return back()->with(
                'warning',
                'La ruta de destino debe ser diferente de la ruta actual.'
            );
        }

        $asignacion =
            DB::table('asignaciones_promotor_ruta as apr')
                ->join(
                    'usuarios as u',
                    'u.id',
                    '=',
                    'apr.promotor_usuario_id'
                )
                ->leftJoin(
                    'datos_personales as dp',
                    'dp.usuario_id',
                    '=',
                    'u.id'
                )
                ->where(
                    'apr.id',
                    $datos['asignacion_id']
                )
                ->where('apr.ruta_id', $ruta)
                ->where(
                    'apr.tipo_asignacion',
                    'PERMANENTE'
                )
                ->where('apr.estado', true)
                ->whereDate(
                    'apr.fecha_inicio',
                    '<=',
                    today()
                )
                ->where(function ($query): void {
                    $query
                        ->whereNull('apr.fecha_fin')
                        ->orWhereDate(
                            'apr.fecha_fin',
                            '>=',
                            today()
                        );
                })
                ->select([
                    'apr.id',
                    'apr.promotor_usuario_id',
                    'apr.ruta_id',
                    'apr.fecha_inicio',
                    'apr.fecha_fin',
                    'u.usuario',
                    'dp.nombres',
                    'dp.apellidos',
                ])
                ->first();

        if (! $asignacion) {
            return back()->with(
                'warning',
                'La asignación permanente seleccionada ya no se encuentra vigente.'
            );
        }

        $destino =
            $this->obtenerRutaActivaParaAsignacion(
                (int) $datos['ruta_destino_id']
            );

        if (! $destino) {
            return back()->with(
                'warning',
                'La ruta de destino o su región se encuentra inactiva.'
            );
        }

        $yaAsignadoDestino =
            DB::table('asignaciones_promotor_ruta')
                ->where(
                    'promotor_usuario_id',
                    $asignacion->promotor_usuario_id
                )
                ->where('ruta_id', $destino->id)
                ->where(
                    'tipo_asignacion',
                    'PERMANENTE'
                )
                ->where('estado', true)
                ->whereDate(
                    'fecha_inicio',
                    '<=',
                    today()
                )
                ->where(function ($query): void {
                    $query
                        ->whereNull('fecha_fin')
                        ->orWhereDate(
                            'fecha_fin',
                            '>=',
                            today()
                        );
                })
                ->exists();

        if ($yaAsignadoDestino) {
            return back()->with(
                'warning',
                'El Promotor ya tiene una asignación permanente vigente en la ruta de destino.'
            );
        }

        $origen = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('r.id', $ruta)
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'reg.nombre as region_nombre',
            ])
            ->first();

        abort_if(
            ! $origen,
            404,
            'La ruta de origen ya no existe.'
        );

        DB::transaction(
            function () use (
                $usuario,
                $asignacion,
                $origen,
                $destino
            ): void {
                $fechaInicioAnterior = Carbon::parse(
                    $asignacion->fecha_inicio
                )->startOfDay();

                $fechaFinAnterior =
                    $fechaInicioAnterior->isToday()
                        ? today()
                        : today()->copy()->subDay();

                DB::table('asignaciones_promotor_ruta')
                    ->where('id', $asignacion->id)
                    ->update([
                        'fecha_fin' =>
                            $fechaFinAnterior->toDateString(),
                        'estado' => false,
                        'updated_at' => now(),
                    ]);

                $nuevaAsignacionId =
                    DB::table('asignaciones_promotor_ruta')
                        ->insertGetId([
                            'promotor_usuario_id' =>
                                $asignacion->promotor_usuario_id,
                            'ruta_id' => $destino->id,
                            'fecha_inicio' =>
                                today()->toDateString(),
                            'fecha_fin' => null,
                            'estado' => true,
                            'tipo_asignacion' =>
                                'PERMANENTE',
                            'motivo' => null,
                            'asignado_por' =>
                                $usuario->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Rutas',
                    accion: 'REASIGNAR_PROMOTOR_RUTA',
                    tablaAfectada:
                        'asignaciones_promotor_ruta',
                    registroId: $nuevaAsignacionId,
                    descripcion:
                        'Se reasignó al Promotor '
                        . $this->nombrePromotor(
                            $asignacion
                        )
                        . ' de la ruta '
                        . $origen->codigo
                        . ' — '
                        . $origen->nombre
                        . ' a la ruta '
                        . $destino->codigo
                        . ' — '
                        . $destino->nombre
                        . '.',
                    valoresAnteriores: [
                        'asignacion_id' =>
                            (int) $asignacion->id,
                        'promotor_usuario_id' =>
                            (int) $asignacion
                                ->promotor_usuario_id,
                        'promotor' =>
                            $this->nombrePromotor(
                                $asignacion
                            ),
                        'ruta_id' =>
                            (int) $origen->id,
                        'ruta_codigo' =>
                            $origen->codigo,
                        'ruta_nombre' =>
                            $origen->nombre,
                        'region_nombre' =>
                            $origen->region_nombre,
                        'tipo_asignacion' =>
                            'PERMANENTE',
                        'fecha_inicio' =>
                            (string) $asignacion
                                ->fecha_inicio,
                        'fecha_fin' =>
                            $fechaFinAnterior
                                ->toDateString(),
                        'estado' => false,
                    ],
                    valoresNuevos: [
                        'asignacion_id' =>
                            $nuevaAsignacionId,
                        'promotor_usuario_id' =>
                            (int) $asignacion
                                ->promotor_usuario_id,
                        'promotor' =>
                            $this->nombrePromotor(
                                $asignacion
                            ),
                        'ruta_id' =>
                            (int) $destino->id,
                        'ruta_codigo' =>
                            $destino->codigo,
                        'ruta_nombre' =>
                            $destino->nombre,
                        'region_nombre' =>
                            $destino->region_nombre,
                        'tipo_asignacion' =>
                            'PERMANENTE',
                        'fecha_inicio' =>
                            today()->toDateString(),
                        'fecha_fin' => null,
                        'estado' => true,
                    ]
                );
            }
        );

        return redirect()
            ->route('jefe.rutas.promotor', $ruta)
            ->with(
                'success',
                'El Promotor fue reasignado correctamente.'
            );
    }

    public function asignarPromotorTemporal(
        Request $request,
        int $ruta
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $rutaActual =
            $this->obtenerRutaActivaParaAsignacion($ruta);

        if (! $rutaActual) {
            return back()->with(
                'warning',
                'La ruta o su región se encuentra inactiva.'
            );
        }

        $datos = $request->validate([
            'promotor_usuario_id' => [
                'required',
                'integer',
                Rule::exists('usuarios', 'id'),
            ],
            'fecha_inicio' => [
                'required',
                'date',
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after_or_equal:fecha_inicio',
            ],
            'motivo' => [
                'required',
                'string',
                'min:10',
                'max:500',
            ],
        ]);

        $promotor = $this->obtenerPromotorActivo(
            (int) $datos['promotor_usuario_id']
        );

        if (! $promotor) {
            return back()->with(
                'warning',
                'El usuario seleccionado no es un Promotor activo.'
            );
        }

        $fechaInicio = Carbon::parse(
            $datos['fecha_inicio']
        )->startOfDay();

        $fechaFin = Carbon::parse(
            $datos['fecha_fin']
        )->startOfDay();

        $temporalSolapada =
            DB::table('asignaciones_promotor_ruta')
                ->where(
                    'promotor_usuario_id',
                    $promotor->id
                )
                ->where('ruta_id', $ruta)
                ->where(
                    'tipo_asignacion',
                    'TEMPORAL'
                )
                ->where('estado', true)
                ->whereDate(
                    'fecha_inicio',
                    '<=',
                    $fechaFin->toDateString()
                )
                ->where(
                    function ($query) use ($fechaInicio): void {
                        $query
                            ->whereNull('fecha_fin')
                            ->orWhereDate(
                                'fecha_fin',
                                '>=',
                                $fechaInicio->toDateString()
                            );
                    }
                )
                ->exists();

        if ($temporalSolapada) {
            return back()->with(
                'warning',
                'El Promotor ya posee una asignación temporal que coincide con el período indicado.'
            );
        }

        $permanenteVigente =
            DB::table('asignaciones_promotor_ruta')
                ->where(
                    'promotor_usuario_id',
                    $promotor->id
                )
                ->where('ruta_id', $ruta)
                ->where(
                    'tipo_asignacion',
                    'PERMANENTE'
                )
                ->where('estado', true)
                ->whereDate(
                    'fecha_inicio',
                    '<=',
                    $fechaInicio->toDateString()
                )
                ->where(
                    function ($query) use ($fechaFin): void {
                        $query
                            ->whereNull('fecha_fin')
                            ->orWhereDate(
                                'fecha_fin',
                                '>=',
                                $fechaFin->toDateString()
                            );
                    }
                )
                ->exists();

        if ($permanenteVigente) {
            return back()->with(
                'warning',
                'El Promotor ya tiene una asignación permanente que cubre el período indicado.'
            );
        }

        DB::transaction(
            function () use (
                $usuario,
                $promotor,
                $rutaActual,
                $fechaInicio,
                $fechaFin,
                $datos
            ): void {
                $asignacionId =
                    DB::table('asignaciones_promotor_ruta')
                        ->insertGetId([
                            'promotor_usuario_id' =>
                                $promotor->id,
                            'ruta_id' =>
                                $rutaActual->id,
                            'fecha_inicio' =>
                                $fechaInicio->toDateString(),
                            'fecha_fin' =>
                                $fechaFin->toDateString(),
                            'estado' => true,
                            'tipo_asignacion' =>
                                'TEMPORAL',
                            'motivo' =>
                                trim($datos['motivo']),
                            'asignado_por' =>
                                $usuario->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Rutas',
                    accion:
                        'ASIGNAR_PROMOTOR_TEMPORAL',
                    tablaAfectada:
                        'asignaciones_promotor_ruta',
                    registroId: $asignacionId,
                    descripcion:
                        'Se asignó temporalmente al Promotor '
                        . $this->nombrePromotor($promotor)
                        . ' a la ruta '
                        . $rutaActual->codigo
                        . ' — '
                        . $rutaActual->nombre
                        . ' del '
                        . $fechaInicio->format('d/m/Y')
                        . ' al '
                        . $fechaFin->format('d/m/Y')
                        . '. Motivo: '
                        . trim($datos['motivo'])
                        . '.',
                    valoresAnteriores: null,
                    valoresNuevos: [
                        'id' => $asignacionId,
                        'promotor_usuario_id' =>
                            (int) $promotor->id,
                        'promotor' =>
                            $this->nombrePromotor(
                                $promotor
                            ),
                        'ruta_id' =>
                            (int) $rutaActual->id,
                        'ruta_codigo' =>
                            $rutaActual->codigo,
                        'ruta_nombre' =>
                            $rutaActual->nombre,
                        'region_nombre' =>
                            $rutaActual->region_nombre,
                        'tipo_asignacion' =>
                            'TEMPORAL',
                        'fecha_inicio' =>
                            $fechaInicio->toDateString(),
                        'fecha_fin' =>
                            $fechaFin->toDateString(),
                        'motivo' =>
                            trim($datos['motivo']),
                        'estado' => true,
                    ]
                );
            }
        );

        return redirect()
            ->route('jefe.rutas.promotor', $ruta)
            ->with(
                'success',
                'La asignación temporal fue registrada correctamente.'
            );
    }

    public function finalizarAsignacionPromotor(
        Request $request,
        int $ruta,
        int $asignacion
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $registro =
            DB::table('asignaciones_promotor_ruta as apr')
                ->join(
                    'usuarios as u',
                    'u.id',
                    '=',
                    'apr.promotor_usuario_id'
                )
                ->leftJoin(
                    'datos_personales as dp',
                    'dp.usuario_id',
                    '=',
                    'u.id'
                )
                ->join(
                    'rutas as r',
                    'r.id',
                    '=',
                    'apr.ruta_id'
                )
                ->where('apr.id', $asignacion)
                ->where('apr.ruta_id', $ruta)
                ->where('apr.estado', true)
                ->select([
                    'apr.id',
                    'apr.promotor_usuario_id',
                    'apr.ruta_id',
                    'apr.fecha_inicio',
                    'apr.fecha_fin',
                    'apr.tipo_asignacion',
                    'apr.motivo',
                    'u.usuario',
                    'dp.nombres',
                    'dp.apellidos',
                    'r.codigo as ruta_codigo',
                    'r.nombre as ruta_nombre',
                ])
                ->first();

        if (! $registro) {
            return back()->with(
                'warning',
                'La asignación seleccionada ya no se encuentra activa.'
            );
        }

        DB::transaction(
            function () use (
                $usuario,
                $registro
            ): void {
                $fechaInicio = Carbon::parse(
                    $registro->fecha_inicio
                )->startOfDay();

                $fechaFin = $fechaInicio->isToday()
                    ? today()
                    : today()->copy()->subDay();

                DB::table('asignaciones_promotor_ruta')
                    ->where('id', $registro->id)
                    ->update([
                        'fecha_fin' =>
                            $fechaFin->toDateString(),
                        'estado' => false,
                        'updated_at' => now(),
                    ]);

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Rutas',
                    accion:
                        $registro->tipo_asignacion
                            === 'TEMPORAL'
                            ? 'CANCELAR_ASIGNACION_TEMPORAL'
                            : 'FINALIZAR_ASIGNACION_PROMOTOR',
                    tablaAfectada:
                        'asignaciones_promotor_ruta',
                    registroId: $registro->id,
                    descripcion:
                        'Se finalizó la asignación '
                        . strtolower(
                            $registro->tipo_asignacion
                        )
                        . ' del Promotor '
                        . $this->nombrePromotor($registro)
                        . ' en la ruta '
                        . $registro->ruta_codigo
                        . ' — '
                        . $registro->ruta_nombre
                        . '.',
                    valoresAnteriores: [
                        'promotor_usuario_id' =>
                            (int) $registro
                                ->promotor_usuario_id,
                        'promotor' =>
                            $this->nombrePromotor(
                                $registro
                            ),
                        'ruta_id' =>
                            (int) $registro->ruta_id,
                        'ruta_codigo' =>
                            $registro->ruta_codigo,
                        'ruta_nombre' =>
                            $registro->ruta_nombre,
                        'estado' => true,
                        'fecha_inicio' =>
                            (string) $registro
                                ->fecha_inicio,
                        'fecha_fin' =>
                            $registro->fecha_fin,
                        'tipo_asignacion' =>
                            $registro->tipo_asignacion,
                        'motivo' =>
                            $registro->motivo,
                    ],
                    valoresNuevos: [
                        'promotor_usuario_id' =>
                            (int) $registro
                                ->promotor_usuario_id,
                        'promotor' =>
                            $this->nombrePromotor(
                                $registro
                            ),
                        'ruta_id' =>
                            (int) $registro->ruta_id,
                        'ruta_codigo' =>
                            $registro->ruta_codigo,
                        'ruta_nombre' =>
                            $registro->ruta_nombre,
                        'estado' => false,
                        'fecha_inicio' =>
                            (string) $registro
                                ->fecha_inicio,
                        'fecha_fin' =>
                            $fechaFin->toDateString(),
                        'tipo_asignacion' =>
                            $registro->tipo_asignacion,
                        'motivo' =>
                            $registro->motivo,
                    ]
                );
            }
        );

        return redirect()
            ->route('jefe.rutas.promotor', $ruta)
            ->with(
                'success',
                'La asignación del Promotor fue finalizada correctamente.'
            );
    }

    public function cambiarEstado(
        Request $request,
        int $ruta
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $registro = DB::table('rutas')
            ->where('id', $ruta)
            ->first();

        abort_if(
            ! $registro,
            404,
            'La ruta solicitada no existe.'
        );

        $nuevoEstado = ! (bool) $registro->estado;

        if (! $nuevoEstado) {
            $tieneAgentesActivos = DB::table('agentes')
                ->where('ruta_id', $ruta)
                ->where('estado', 'ACTIVO')
                ->exists();

            if ($tieneAgentesActivos) {
                return redirect()
                    ->route('jefe.rutas.index')
                    ->with(
                        'warning',
                        'No se puede desactivar la ruta porque tiene agentes activos asignados.'
                    );
            }

            $asignacionActiva =
                DB::table('asignaciones_promotor_ruta')
                    ->where('ruta_id', $ruta)
                    ->where('estado', true)
                    ->whereDate(
                        'fecha_inicio',
                        '<=',
                        today()
                    )
                    ->where(function ($query): void {
                        $query
                            ->whereNull('fecha_fin')
                            ->orWhereDate(
                                'fecha_fin',
                                '>=',
                                today()
                            );
                    })
                    ->exists();

            if ($asignacionActiva) {
                return redirect()
                    ->route('jefe.rutas.index')
                    ->with(
                        'warning',
                        'No se puede desactivar la ruta mientras tenga promotores con asignaciones vigentes.'
                    );
            }
        } else {
            $regionActiva = DB::table('regiones')
                ->where('id', $registro->region_id)
                ->where('estado', true)
                ->exists();

            if (! $regionActiva) {
                return redirect()
                    ->route('jefe.rutas.index')
                    ->with(
                        'warning',
                        'No se puede activar la ruta porque su región está inactiva.'
                    );
            }
        }

        DB::transaction(
            function () use (
                $ruta,
                $nuevoEstado,
                $usuario
            ): void {
                $anteriores =
                    $this->obtenerValoresAuditoriaRuta($ruta);

                DB::table('rutas')
                    ->where('id', $ruta)
                    ->update([
                        'estado' => $nuevoEstado,
                        'updated_at' => now(),
                    ]);

                $nuevos =
                    $this->obtenerValoresAuditoriaRuta($ruta);

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Rutas',
                    accion:
                        $nuevoEstado
                            ? 'ACTIVAR_RUTA'
                            : 'DESACTIVAR_RUTA',
                    tablaAfectada: 'rutas',
                    registroId: $ruta,
                    descripcion:
                        'Se '
                        . (
                            $nuevoEstado
                                ? 'activó'
                                : 'desactivó'
                        )
                        . ' la ruta '
                        . (
                            $nuevos['codigo']
                            ?? ('#' . $ruta)
                        )
                        . ' — '
                        . (
                            $nuevos['nombre']
                            ?? 'Sin nombre'
                        )
                        . '.',
                    valoresAnteriores: [
                        'estado' =>
                            $anteriores['estado'] ?? null,
                    ],
                    valoresNuevos: [
                        'estado' =>
                            $nuevos['estado'] ?? null,
                    ]
                );
            }
        );

        return redirect()
            ->route('jefe.rutas.index')
            ->with(
                'success',
                $nuevoEstado
                    ? 'La ruta fue activada correctamente.'
                    : 'La ruta fue desactivada correctamente.'
            );
    }

    private function obtenerValoresAuditoriaRuta(
        int $rutaId
    ): array {
        $registro = DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('r.id', $rutaId)
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.region_id',
                'r.estado',
                'reg.nombre as region_nombre',
            ])
            ->first();

        if (! $registro) {
            return [];
        }

        return [
            'id' => (int) $registro->id,
            'codigo' => $registro->codigo,
            'nombre' => $registro->nombre,
            'region_id' => (int) $registro->region_id,
            'region_nombre' => $registro->region_nombre,
            'estado' => (bool) $registro->estado,
        ];
    }

    private function obtenerValoresAuditoriaAgente(
        int $agenteId
    ): array {
        $registro = DB::table('agentes as a')
            ->leftJoin(
                'rutas as r',
                'r.id',
                '=',
                'a.ruta_id'
            )
            ->leftJoin(
                'regiones as reg',
                'reg.id',
                '=',
                'r.region_id'
            )
            ->where('a.id', $agenteId)
            ->select([
                'a.id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.ruta_id',
                'r.codigo as ruta_codigo',
                'r.nombre as ruta_nombre',
                'r.region_id',
                'reg.nombre as region_nombre',
            ])
            ->first();

        if (! $registro) {
            return [];
        }

        return [
            'id' => (int) $registro->id,
            'codigo_agente' =>
                $registro->codigo_agente,
            'nombre_negocio' =>
                $registro->nombre_negocio,
            'ruta_id' =>
                $registro->ruta_id !== null
                    ? (int) $registro->ruta_id
                    : null,
            'ruta_codigo' =>
                $registro->ruta_codigo,
            'ruta_nombre' =>
                $registro->ruta_nombre,
            'region_id' =>
                $registro->region_id !== null
                    ? (int) $registro->region_id
                    : null,
            'region_nombre' =>
                $registro->region_nombre,
        ];
    }

    private function obtenerRutaActivaParaAsignacion(
        int $rutaId
    ): ?object {
        return DB::table('rutas as r')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('r.id', $rutaId)
            ->where('r.estado', true)
            ->where('reg.estado', true)
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.region_id',
                'reg.nombre as region_nombre',
            ])
            ->first();
    }

    private function obtenerPromotorActivo(
        int $usuarioId
    ): ?object {
        return DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'u.id'
            )
            ->where('u.id', $usuarioId)
            ->where('u.estado', 'ACTIVO')
            ->where('rol.nombre', 'Promotor')
            ->select([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();
    }

    private function nombrePromotor(
        object $promotor
    ): string {
        $nombre = trim(
            ($promotor->nombres ?? '')
            . ' '
            . ($promotor->apellidos ?? '')
        );

        return $nombre !== ''
            ? $nombre
            : ($promotor->usuario ?? 'Promotor');
    }

    private function validarJefe(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
                || $usuario->rol->nombre
                    !== 'jefedeAgentes',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
