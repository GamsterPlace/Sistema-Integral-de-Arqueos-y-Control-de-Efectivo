<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\JornadaReceptor;
use App\Services\Agencias\AccesoAgenciaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CumplimientoArqueosController extends Controller
{
    private function contexto(
        Request $request,
        AccesoAgenciaService $acceso
    ): array {
        $usuario = $request->user();
        $usuario->loadMissing('rol');

        $rol = $usuario->rol?->nombre;

        abort_unless(
            in_array($rol, [
                'Receptor',
                'JefeCaja',
                'SubjefeCaja',
                'JefeAgencia',
            ], true),
            403
        );

        $ruta = match ($rol) {
            'Receptor' => 'receptor',
            'JefeCaja' => 'jefecaja',
            'SubjefeCaja' => 'subjefecaja',
            'JefeAgencia' => 'jefeagencia',
        };

        $agencias = collect();

        if ($rol !== 'Receptor') {
            $agencias = $acceso
                ->asignacionesVigentes($usuario)
                ->pluck('agencia')
                ->filter(fn ($a) => (bool) $a->estado)
                ->unique('id')
                ->sortBy('nombre')
                ->values();
        }

        return compact(
            'usuario',
            'rol',
            'ruta',
            'agencias'
        );
    }

    private function consulta(
        array $ctx,
        ?int $agenciaId = null,
        ?int $receptorId = null
    ) {
        $query = JornadaReceptor::query()->with([
            'agencia',
            'ventanilla',
            'turno',
            'receptor',
            'arqueos' => fn ($q) => $q
                ->where('tipo', 'ORDINARIO')
                ->orderByDesc('id'),
        ]);

        if ($ctx['rol'] === 'Receptor') {
            $query
                ->where(
                    'receptor_usuario_id',
                    $ctx['usuario']->id
                )
                ->whereHas(
                    'asignacionPersonal',
                    fn ($q) => $q
                        ->where(
                            'usuario_id',
                            $ctx['usuario']->id
                        )
                        ->where('cargo', 'RECEPTOR')
                );
        } else {
            $ids = $ctx['agencias']
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $query->whereIn('agencia_id', $ids);

            if ($agenciaId !== null) {
                abort_unless(
                    in_array($agenciaId, $ids, true),
                    403,
                    'Agencia no autorizada.'
                );

                $query->where(
                    'agencia_id',
                    $agenciaId
                );
            }

            if ($receptorId !== null) {
                $query->where(
                    'receptor_usuario_id',
                    $receptorId
                );
            }
        }

        return $query;
    }

    private function clasificar(
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

    public function index(
        Request $request,
        AccesoAgenciaService $acceso
    ): View {
        $ctx = $this->contexto($request, $acceso);

        /*
         * CUMPLIMIENTO PERSONAL DEL RECEPTOR
         */
        if ($ctx['rol'] === 'Receptor') {
            $f = $request->validate([
                'mes' => [
                    'nullable',
                    'date_format:Y-m',
                ],
            ]);

            $mes = $f['mes'] ?? now()->format('Y-m');

            $inicioMes = Carbon::createFromFormat(
                '!Y-m',
                $mes
            )->startOfMonth();

            $finMes = $inicioMes
                ->copy()
                ->endOfMonth();

            /*
             * Consultar todos los turnos del mes.
             * No utilizar paginación en el calendario.
             */
            $jornadas = $this->consulta($ctx)
                ->whereBetween(
                    'fecha_operativa',
                    [
                        $inicioMes->toDateString(),
                        $finMes->toDateString(),
                    ]
                )
                ->orderBy('fecha_operativa')
                ->orderBy('inicio_programado')
                ->orderBy('id')
                ->get()
                ->map(
                    fn ($j) => $this->clasificar($j)
                );

            /*
             * Indicadores mensuales por turno.
             */
            $resumenMes = [
                'CON_ARQUEO' => 0,
                'SIN_ARQUEO' => 0,
                'PENDIENTE_HOY' => 0,
                'ANULADO_SIN_REEMPLAZO' => 0,
                'PENDIENTE_CERTIFICACION' => 0,
            ];

            foreach ($jornadas as $j) {
                if (
                    array_key_exists(
                        $j->cumplimiento_arqueo,
                        $resumenMes
                    )
                ) {
                    $resumenMes[
                        $j->cumplimiento_arqueo
                    ]++;
                }

                if (
                    $j->certificacion_arqueo
                    === 'PENDIENTE_CERTIFICACION'
                ) {
                    $resumenMes[
                        'PENDIENTE_CERTIFICACION'
                    ]++;
                }
            }

            /*
             * Agrupar jornadas por fecha.
             * Una fecha puede tener múltiples turnos.
             */
            $porFecha = $jornadas->groupBy(
                fn ($j) =>
                    $j->fecha_operativa->toDateString()
            );

            $calendario = [];
            $hoy = now()->toDateString();

            for (
                $dia = $inicioMes->copy();
                $dia->lte($finMes);
                $dia->addDay()
            ) {
                $fecha = $dia->toDateString();

                $calendario[] = [
                    'fecha' => $fecha,
                    'dia' => $dia->day,
                    'es_hoy' => $fecha === $hoy,
                    'es_futuro' => $fecha > $hoy,
                    'turnos' => $porFecha->get(
                        $fecha,
                        collect()
                    ),
                ];
            }

            $mesAnterior = $inicioMes
                ->copy()
                ->subMonth()
                ->format('Y-m');

            $mesSiguiente = $inicioMes
                ->copy()
                ->addMonth()
                ->format('Y-m');

            $mesActual = now()->format('Y-m');

            return view(
                'agencias.cumplimientos.receptor',
                compact(
                    'mes',
                    'inicioMes',
                    'mesAnterior',
                    'mesSiguiente',
                    'mesActual',
                    'resumenMes',
                    'calendario'
                )
            );
        }

        /*
         * CUMPLIMIENTO INSTITUCIONAL
         * Jefe de Caja, Subjefe de Caja y Jefe de Agencia.
         */
        $f = $request->validate([
            'mes' => [
                'nullable',
                'date_format:Y-m',
            ],
            'agencia_id' => [
                'nullable',
                'integer',
            ],
            'receptor_id' => [
                'nullable',
                'integer',
            ],
        ]);

        $mes = $f['mes'] ?? now()->format('Y-m');

        $inicio = Carbon::createFromFormat(
            '!Y-m',
            $mes
        )->startOfMonth();

        $fin = $inicio->copy()->endOfMonth();

        $agenciaId = isset($f['agencia_id'])
            ? (int) $f['agencia_id']
            : null;

        $receptorId = isset($f['receptor_id'])
            ? (int) $f['receptor_id']
            : null;

        $query = $this->consulta(
            $ctx,
            $agenciaId,
            $receptorId
        )->whereBetween(
            'fecha_operativa',
            [
                $inicio->toDateString(),
                $fin->toDateString(),
            ]
        );

        $registros = $query
            ->orderBy('fecha_operativa')
            ->get()
            ->map(
                fn ($j) => $this->clasificar($j)
            );

        $estados = [
            'CON_ARQUEO',
            'PENDIENTE_HOY',
            'SIN_ARQUEO',
            'ANULADO_SIN_REEMPLAZO',
            'PROGRAMADO',
        ];

        $resumen = array_fill_keys(
            $estados,
            0
        );

        $resumen['CERTIFICADO'] = 0;
        $resumen['PENDIENTE_CERTIFICACION'] = 0;

        $calendario = [];

        $porFecha = $registros->groupBy(
            fn ($j) =>
                $j->fecha_operativa->toDateString()
        );

        for (
            $dia = $inicio->copy();
            $dia->lte($fin);
            $dia->addDay()
        ) {
            $fecha = $dia->toDateString();

            $grupo = $porFecha->get(
                $fecha,
                collect()
            );

            $cuentas = array_fill_keys(
                $estados,
                0
            );

            foreach ($grupo as $j) {
                $cuentas[
                    $j->cumplimiento_arqueo
                ]++;

                $resumen[
                    $j->cumplimiento_arqueo
                ]++;

                if (
                    $j->certificacion_arqueo
                    !== 'NO_APLICA'
                ) {
                    $resumen[
                        $j->certificacion_arqueo
                    ]++;
                }
            }

            $calendario[] = [
                'fecha' => $fecha,
                'dia' => $dia->day,
                'es_hoy' => $fecha === now()->toDateString(),
                'es_futuro' => $fecha > now()->toDateString(),
                'total' => $grupo->count(),
                'estados' => $cuentas,
            ];
        }

        $receptores = $this->consulta(
            $ctx,
            $agenciaId
        )
            ->whereBetween(
                'fecha_operativa',
                [
                    $inicio->toDateString(),
                    $fin->toDateString(),
                ]
            )
            ->get()
            ->pluck('receptor')
            ->filter()
            ->unique('id')
            ->sortBy('nombre_completo')
            ->values();

        $ruta = $ctx['ruta'];
        $agencias = $ctx['agencias'];

        $mesAnterior = $inicio
            ->copy()
            ->subMonth()
            ->format('Y-m');

        $mesSiguiente = $inicio
            ->copy()
            ->addMonth()
            ->format('Y-m');

        $mesActual = now()->format('Y-m');

        return view(
            'agencias.cumplimientos.supervision',
            compact(
                'ruta',
                'agencias',
                'receptores',
                'mes',
                'mesAnterior',
                'mesSiguiente',
                'mesActual',
                'agenciaId',
                'receptorId',
                'inicio',
                'calendario',
                'resumen'
            )
        );
    }

    /*
     * DETALLE DE CUMPLIMIENTO PARA SUPERVISIÓN
     */
    public function detalle(
        Request $request,
        AccesoAgenciaService $acceso
    ): JsonResponse {
        $ctx = $this->contexto(
            $request,
            $acceso
        );

        abort_if(
            $ctx['rol'] === 'Receptor',
            403
        );

        $f = $request->validate([
            'fecha' => [
                'required',
                'date_format:Y-m-d',
            ],
            'categoria' => [
                'required',
                'in:CON_ARQUEO,PENDIENTE_HOY,SIN_ARQUEO,ANULADO_SIN_REEMPLAZO,PROGRAMADO',
            ],
            'agencia_id' => [
                'nullable',
                'integer',
            ],
            'receptor_id' => [
                'nullable',
                'integer',
            ],
        ]);

        $registros = $this->consulta(
            $ctx,
            isset($f['agencia_id'])
                ? (int) $f['agencia_id']
                : null,
            isset($f['receptor_id'])
                ? (int) $f['receptor_id']
                : null
        )
            ->whereDate(
                'fecha_operativa',
                $f['fecha']
            )
            ->orderBy('agencia_id')
            ->get()
            ->map(
                fn ($j) => $this->clasificar($j)
            )
            ->filter(
                fn ($j) =>
                    $j->cumplimiento_arqueo
                    === $f['categoria']
            )
            ->values();

        return response()->json([
            'fecha' => $f['fecha'],
            'categoria' => $f['categoria'],
            'total' => $registros->count(),
            'jornadas' => $registros
                ->map(
                    fn ($j) => [
                        'fecha' => $j->fecha_operativa
                            ->format('d/m/Y'),
                        'agencia' => $j->agencia?->nombre
                            ?? '—',
                        'ventanilla' => $j->ventanilla?->codigo
                            ?? '—',
                        'receptor' => $j->receptor?->nombre_completo
                            ?? '—',
                        'turno' => $j->turno?->nombre
                            ?? '—',
                        'arqueo' => $j
                            ->arqueo_ordinario_vigente
                            ?->numero_arqueo
                            ?? '—',
                        'certificacion' => $j
                            ->certificacion_arqueo,
                    ]
                )
                ->all(),
        ]);
    }
}
