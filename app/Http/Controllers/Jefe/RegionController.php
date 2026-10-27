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

class RegionController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $estado = trim((string) $request->string('estado'));

        $regiones = DB::table('regiones as reg')
            ->when(
                $buscar !== '',
                fn ($q) => $q->where(
                    'reg.nombre',
                    'like',
                    '%' . $buscar . '%'
                )
            )
            ->when(
                $estado === 'ACTIVA',
                fn ($q) => $q->where('reg.estado', true)
            )
            ->when(
                $estado === 'INACTIVA',
                fn ($q) => $q->where('reg.estado', false)
            )
            ->select([
                'reg.id',
                'reg.nombre',
                'reg.estado',
            ])
            ->selectSub(
                DB::table('rutas')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'rutas.region_id',
                        'reg.id'
                    ),
                'total_rutas'
            )
            ->selectSub(
                DB::table('rutas')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'rutas.region_id',
                        'reg.id'
                    )
                    ->where('rutas.estado', true),
                'rutas_activas'
            )
            ->selectSub(
                DB::table('agentes as a')
                    ->join(
                        'rutas as r',
                        'r.id',
                        '=',
                        'a.ruta_id'
                    )
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'r.region_id',
                        'reg.id'
                    )
                    ->where(
                        'a.estado',
                        'ACTIVO'
                    ),
                'agentes_activos'
            )
            ->selectSub(
                DB::table('agentes as a2')
                    ->join(
                        'rutas as r2',
                        'r2.id',
                        '=',
                        'a2.ruta_id'
                    )
                    ->join(
                        'arqueos as arq',
                        'arq.agente_id',
                        '=',
                        'a2.id'
                    )
                    ->selectRaw(
                        'COUNT(DISTINCT a2.id)'
                    )
                    ->whereColumn(
                        'r2.region_id',
                        'reg.id'
                    )
                    ->where(
                        'a2.estado',
                        'ACTIVO'
                    )
                    ->where(
                        'arq.tipo',
                        'DIARIO_AGENTE'
                    )
                    ->whereDate(
                        'arq.fecha_arqueo',
                        today()
                    )
                    ->where(
                        'arq.estado',
                        '!=',
                        'ANULADO'
                    ),
                'agentes_arqueados_hoy'
            )
            ->orderBy('reg.nombre')
            ->paginate(12)
            ->withQueryString();

        $totalRegiones = DB::table('regiones')
            ->count();

        $regionesActivas = DB::table('regiones')
            ->where('estado', true)
            ->count();

        $regionesInactivas = DB::table('regiones')
            ->where('estado', false)
            ->count();

        $regionesSinRutas = DB::table('regiones as reg')
            ->whereNotExists(
                function ($q): void {
                    $q->selectRaw('1')
                        ->from('rutas as r')
                        ->whereColumn(
                            'r.region_id',
                            'reg.id'
                        );
                }
            )
            ->count();

        return view(
            'jefe.regiones.index',
            compact(
                'regiones',
                'buscar',
                'estado',
                'totalRegiones',
                'regionesActivas',
                'regionesInactivas',
                'regionesSinRutas'
            )
        );
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique(
                    'regiones',
                    'nombre'
                ),
            ],
        ]);

        DB::transaction(
            function () use (
                $datos,
                $usuario
            ): void {
                $regionId = DB::table('regiones')
                    ->insertGetId([
                        'nombre' => trim(
                            $datos['nombre']
                        ),
                        'estado' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                $nuevos =
                    $this->obtenerValoresAuditoriaRegion(
                        $regionId
                    );

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Regiones',
                    accion: 'CREAR_REGION',
                    tablaAfectada: 'regiones',
                    registroId: $regionId,
                    descripcion:
                        'Se creó la región '
                        . ($nuevos['nombre']
                            ?? ('#' . $regionId))
                        . '.',
                    valoresAnteriores: null,
                    valoresNuevos: $nuevos
                );
            }
        );

        return redirect()
            ->route('jefe.regiones.index')
            ->with(
                'success',
                'La región fue creada correctamente.'
            );
    }

    public function update(
        Request $request,
        int $region
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        abort_if(
            ! DB::table('regiones')
                ->where('id', $region)
                ->exists(),
            404,
            'La región solicitada no existe.'
        );

        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique(
                    'regiones',
                    'nombre'
                )->ignore($region),
            ],
        ]);

        DB::transaction(
            function () use (
                $region,
                $datos,
                $usuario
            ): void {
                $anteriores =
                    $this->obtenerValoresAuditoriaRegion(
                        $region
                    );

                DB::table('regiones')
                    ->where('id', $region)
                    ->update([
                        'nombre' => trim(
                            $datos['nombre']
                        ),
                        'updated_at' => now(),
                    ]);

                $nuevos =
                    $this->obtenerValoresAuditoriaRegion(
                        $region
                    );

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Regiones',
                    accion: 'EDITAR_REGION',
                    tablaAfectada: 'regiones',
                    registroId: $region,
                    descripcion:
                        'Se actualizó la región '
                        . ($nuevos['nombre']
                            ?? ('#' . $region))
                        . '.',
                    valoresAnteriores: $anteriores,
                    valoresNuevos: $nuevos
                );
            }
        );

        return redirect()
            ->route('jefe.regiones.index')
            ->with(
                'success',
                'La región fue actualizada correctamente.'
            );
    }

    public function rutas(
        Request $request,
        int $region
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $regionActual = DB::table('regiones')
            ->where('id', $region)
            ->first();

        abort_if(
            ! $regionActual,
            404,
            'La región solicitada no existe.'
        );

        $buscar = trim(
            (string) $request->string('buscar')
        );

        $rutas = DB::table('rutas as r')
            ->where('r.region_id', $region)
            ->when(
                $buscar !== '',
                function ($q) use ($buscar): void {
                    $q->where(
                        function ($s) use ($buscar): void {
                            $s->where(
                                'r.codigo',
                                'like',
                                '%' . $buscar . '%'
                            )
                                ->orWhere(
                                    'r.nombre',
                                    'like',
                                    '%' . $buscar . '%'
                                );
                        }
                    );
                }
            )
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.estado',
            ])
            ->selectSub(
                DB::table('agentes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'agentes.ruta_id',
                        'r.id'
                    ),
                'total_agentes'
            )
            ->orderBy('r.nombre')
            ->paginate(20)
            ->withQueryString();

        $regionesDestino = DB::table('regiones')
            ->where('estado', true)
            ->where('id', '!=', $region)
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
            ]);

        return view(
            'jefe.regiones.rutas',
            compact(
                'regionActual',
                'rutas',
                'regionesDestino',
                'buscar'
            )
        );
    }

    public function reasignarRutas(
        Request $request,
        int $region
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        abort_if(
            ! DB::table('regiones')
                ->where('id', $region)
                ->exists(),
            404
        );

        $datos = $request->validate([
            'rutas' => [
                'required',
                'array',
                'min:1',
            ],
            'rutas.*' => [
                'integer',
                Rule::exists(
                    'rutas',
                    'id'
                ),
            ],
            'region_destino_id' => [
                'required',
                'integer',
                Rule::exists(
                    'regiones',
                    'id'
                ),
            ],
        ]);

        $destino = DB::table('regiones')
            ->where(
                'id',
                $datos['region_destino_id']
            )
            ->where('estado', true)
            ->first();

        if (
            ! $destino
            || (int) $destino->id === $region
        ) {
            return back()->with(
                'warning',
                'Seleccione una región de destino activa y diferente.'
            );
        }

        $ids = collect($datos['rutas'])
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values();

        $validas = DB::table('rutas')
            ->where('region_id', $region)
            ->whereIn('id', $ids)
            ->pluck('id');

        if (
            $validas->count()
            !== $ids->count()
        ) {
            return back()->with(
                'warning',
                'Una o más rutas ya no pertenecen a la región seleccionada.'
            );
        }

        DB::transaction(
            function () use (
                $validas,
                $destino,
                $usuario
            ): void {
                $rutasAnteriores = [];

                foreach ($validas as $rutaId) {
                    $rutasAnteriores[
                        (int) $rutaId
                    ] = $this
                        ->obtenerValoresAuditoriaRuta(
                            (int) $rutaId
                        );
                }

                DB::table('rutas')
                    ->whereIn('id', $validas)
                    ->update([
                        'region_id' => $destino->id,
                        'updated_at' => now(),
                    ]);

                foreach ($validas as $rutaId) {
                    $rutaId = (int) $rutaId;

                    $anteriores =
                        $rutasAnteriores[$rutaId]
                        ?? [];

                    $nuevos =
                        $this->obtenerValoresAuditoriaRuta(
                            $rutaId
                        );

                    app(
                        AuditoriaService::class
                    )->registrar(
                        usuario: $usuario,
                        modulo: 'Regiones',
                        accion:
                            'REASIGNAR_RUTA_REGION',
                        tablaAfectada: 'rutas',
                        registroId: $rutaId,
                        descripcion:
                            'Se trasladó la ruta '
                            . ($nuevos['codigo']
                                ?? ('#' . $rutaId))
                            . ' — '
                            . ($nuevos['nombre']
                                ?? 'Sin nombre')
                            . ' de la región '
                            . ($anteriores[
                                'region_nombre'
                            ] ?? 'Sin región')
                            . ' a la región '
                            . ($nuevos[
                                'region_nombre'
                            ] ?? $destino->nombre)
                            . '.',
                        valoresAnteriores:
                            $anteriores,
                        valoresNuevos:
                            $nuevos
                    );
                }
            }
        );

        return redirect()
            ->route(
                'jefe.regiones.rutas',
                $region
            )
            ->with(
                'success',
                $validas->count()
                . ' ruta(s) fueron trasladadas correctamente a '
                . $destino->nombre
                . '.'
            );
    }

    public function cambiarEstado(
        Request $request,
        int $region
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $registro = DB::table('regiones')
            ->where('id', $region)
            ->first();

        abort_if(
            ! $registro,
            404,
            'La región solicitada no existe.'
        );

        $nuevoEstado =
            ! (bool) $registro->estado;

        if (! $nuevoEstado) {
            if (
                DB::table('rutas')
                    ->where(
                        'region_id',
                        $region
                    )
                    ->where(
                        'estado',
                        true
                    )
                    ->exists()
            ) {
                return redirect()
                    ->route(
                        'jefe.regiones.index'
                    )
                    ->with(
                        'warning',
                        'No se puede desactivar la región porque tiene rutas activas.'
                    );
            }
        }

        DB::transaction(
            function () use (
                $region,
                $nuevoEstado,
                $usuario
            ): void {
                $anteriores =
                    $this->obtenerValoresAuditoriaRegion(
                        $region
                    );

                DB::table('regiones')
                    ->where('id', $region)
                    ->update([
                        'estado' => $nuevoEstado,
                        'updated_at' => now(),
                    ]);

                $nuevos =
                    $this->obtenerValoresAuditoriaRegion(
                        $region
                    );

                $accion = $nuevoEstado
                    ? 'ACTIVAR_REGION'
                    : 'DESACTIVAR_REGION';

                app(AuditoriaService::class)->registrar(
                    usuario: $usuario,
                    modulo: 'Regiones',
                    accion: $accion,
                    tablaAfectada: 'regiones',
                    registroId: $region,
                    descripcion:
                        'Se '
                        . ($nuevoEstado
                            ? 'activó'
                            : 'desactivó')
                        . ' la región '
                        . ($nuevos['nombre']
                            ?? ('#' . $region))
                        . '.',
                    valoresAnteriores: [
                        'estado' =>
                            $anteriores[
                                'estado'
                            ] ?? null,
                    ],
                    valoresNuevos: [
                        'estado' =>
                            $nuevos[
                                'estado'
                            ] ?? null,
                    ]
                );
            }
        );

        return redirect()
            ->route(
                'jefe.regiones.index'
            )
            ->with(
                'success',
                $nuevoEstado
                    ? 'La región fue activada correctamente.'
                    : 'La región fue desactivada correctamente.'
            );
    }

    private function obtenerValoresAuditoriaRegion(
        int $regionId
    ): array {
        $registro = DB::table('regiones')
            ->where('id', $regionId)
            ->first([
                'id',
                'nombre',
                'estado',
            ]);

        if (! $registro) {
            return [];
        }

        return [
            'id' => (int) $registro->id,
            'nombre' => $registro->nombre,
            'estado' => (bool) $registro->estado,
        ];
    }

    private function obtenerValoresAuditoriaRuta(
        int $rutaId
    ): array {
        $registro = DB::table('rutas as r')
            ->join(
                'regiones as reg',
                'reg.id',
                '=',
                'r.region_id'
            )
            ->where('r.id', $rutaId)
            ->select([
                'r.id',
                'r.codigo',
                'r.nombre',
                'r.estado',
                'r.region_id',
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
            'estado' => (bool) $registro->estado,
            'region_id' =>
                (int) $registro->region_id,
            'region_nombre' =>
                $registro->region_nombre,
        ];
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
