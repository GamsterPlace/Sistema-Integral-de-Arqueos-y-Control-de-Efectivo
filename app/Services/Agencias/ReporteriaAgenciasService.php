<?php

namespace App\Services\Agencias;

use App\Models\Usuario;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReporteriaAgenciasService
{
    private const REPORTES = [
        'resumen' => 'Resumen Ejecutivo',
        'faltantes' => 'Arqueos con Faltantes',
        'sobrantes' => 'Arqueos con Sobrantes',
        'ranking-faltantes' => 'Ranking de Faltantes',
        'ranking-sobrantes' => 'Ranking de Sobrantes',
        'exactos' => 'Arqueos Exactos',
        'historial-receptor' => 'Historial por Receptor',
        'historial-agencia' => 'Historial por Agencia',
        'historial-ventanilla' => 'Historial por Ventanilla',
        'historial-turno' => 'Historial por Turno',
        'cumplimiento' => 'Cumplimiento de Arqueos',
        'anulados' => 'Arqueos Anulados',
        'sorpresivos' => 'Arqueos Sorpresivos',
        'auditoria' => 'Arqueos de Auditoría',
        'diferencias-agencia' => 'Diferencias por Agencia',
    ];

    private const TIPOS = [
        'ORDINARIO' => 'Ordinario',
        'ENTREGA' => 'Entrega de Ventanilla',
        'SORPRESIVO' => 'Jefatura de Caja',
        'AUDITORIA' => 'Auditoría',
    ];

    private const ROLES_GLOBALES = [
        'Gerencia',
        'Auditoria',
        'Administrador',
    ];

    private const ROLES_AGENCIA = [
        'JefeCaja',
        'SubjefeCaja',
        'JefeAgencia',
    ];

    public function __construct(
        private readonly AccesoAgenciaService $acceso
    ) {
    }

    public function tiposReporte(): array
    {
        return self::REPORTES;
    }

    public function filtros(Request $request): array
    {
        $datos = Validator::make(
            $request->query(),
            [
                'reporte' => [
                    'nullable',
                    Rule::in(array_keys(self::REPORTES)),
                ],
                'agencia_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'receptor_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'ventanilla_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'turno' => [
                    'nullable',
                    'string',
                    'max:80',
                ],
                'tipo' => [
                    'nullable',
                    Rule::in(array_keys(self::TIPOS)),
                ],
                'estado' => [
                    'nullable',
                    'string',
                    'max:35',
                ],
                'desde' => [
                    'nullable',
                    'date_format:Y-m-d',
                ],
                'hasta' => [
                    'nullable',
                    'date_format:Y-m-d',
                    'after_or_equal:desde',
                ],
                'por_pagina' => [
                    'nullable',
                    'integer',
                    Rule::in([10, 20, 50, 100]),
                ],
            ]
        )->validate();

        return [
            'reporte' => $datos['reporte'] ?? 'resumen',
            'agencia_id' => (int) ($datos['agencia_id'] ?? 0),
            'receptor_id' => (int) ($datos['receptor_id'] ?? 0),
            'ventanilla_id' => (int) ($datos['ventanilla_id'] ?? 0),
            'turno' => trim((string) ($datos['turno'] ?? '')),
            'tipo' => $datos['tipo'] ?? '',
            'estado' => trim((string) ($datos['estado'] ?? '')),
            'desde' => $datos['desde'] ?? null,
            'hasta' => $datos['hasta'] ?? null,
            'por_pagina' => (int) ($datos['por_pagina'] ?? 20),
        ];
    }

    public function catalogos(
        Usuario $usuario,
        array $filtros
    ): array {
        $agenciasPermitidas = $this->agenciasPermitidas($usuario);

        $agencias = DB::table('agencias')
            ->when(
                $agenciasPermitidas !== null,
                fn (Builder $q) => $q->whereIn(
                    'id',
                    $agenciasPermitidas
                )
            )
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre']);

        $ventanillas = DB::table('ventanillas_agencia')
            ->when(
                $agenciasPermitidas !== null,
                fn (Builder $q) => $q->whereIn(
                    'agencia_id',
                    $agenciasPermitidas
                )
            )
            ->when(
                $filtros['agencia_id'] > 0,
                fn (Builder $q) => $q->where(
                    'agencia_id',
                    $filtros['agencia_id']
                )
            )
            ->orderBy('codigo')
            ->get([
                'id',
                'agencia_id',
                'codigo',
                'descripcion',
            ]);

        $base = $this->consultaBase($usuario, [
            ...$filtros,
            'receptor_id' => 0,
            'ventanilla_id' => 0,
            'turno' => '',
            'tipo' => '',
            'estado' => '',
            'desde' => null,
            'hasta' => null,
        ]);

        $receptores = (clone $base)
            ->select([
                'arq.receptor_usuario_id as id',
                'arq.receptor_nombre_historico as nombre',
            ])
            ->distinct()
            ->orderBy('nombre')
            ->get();

        $turnos = (clone $base)
            ->whereNotNull('arq.turno_nombre_historico')
            ->where('arq.turno_nombre_historico', '!=', '')
            ->select('arq.turno_nombre_historico as nombre')
            ->distinct()
            ->orderBy('nombre')
            ->pluck('nombre');

        $estados = (clone $base)
            ->select('arq.estado')
            ->distinct()
            ->orderBy('arq.estado')
            ->pluck('estado');

        return [
            'agencias' => $agencias,
            'ventanillas' => $ventanillas,
            'receptores' => $receptores,
            'turnos' => $turnos,
            'estados' => $estados,
            'tipos' => self::TIPOS,
        ];
    }

    public function generar(
        Usuario $usuario,
        array $filtros,
        bool $paginar = true
    ): array {
        $base = $this->consultaBase($usuario, $filtros);

        return match ($filtros['reporte']) {
            'faltantes' => $this->incidencias(
                $base,
                $filtros,
                $paginar,
                'FALTANTE'
            ),

            'sobrantes' => $this->incidencias(
                $base,
                $filtros,
                $paginar,
                'SOBRANTE'
            ),

            'ranking-faltantes' => $this->rankingReceptores(
                $base,
                $filtros,
                $paginar,
                'FALTANTE'
            ),

            'ranking-sobrantes' => $this->rankingReceptores(
                $base,
                $filtros,
                $paginar,
                'SOBRANTE'
            ),

            'exactos' => $this->listado(
                $base->where('arq.diferencia', 0)
                    ->whereNotNull('arq.certificado_at')
                    ->where('arq.estado', '!=', 'ANULADO'),
                $filtros,
                $paginar,
                'exactos'
            ),

            'historial-receptor',
            'historial-agencia',
            'historial-ventanilla',
            'historial-turno' => $this->listado(
                $base,
                $filtros,
                $paginar,
                $filtros['reporte']
            ),

            'cumplimiento' => $this->cumplimiento(
                $usuario,
                $filtros,
                $paginar
            ),

            'anulados' => $this->listado(
                $base->where('arq.estado', 'ANULADO'),
                $filtros,
                $paginar,
                'anulados'
            ),

            'sorpresivos' => $this->listado(
                $base->where('arq.tipo', 'SORPRESIVO'),
                $filtros,
                $paginar,
                'sorpresivos'
            ),

            'auditoria' => $this->listado(
                $base->where('arq.tipo', 'AUDITORIA'),
                $filtros,
                $paginar,
                'auditoria'
            ),

            'diferencias-agencia' => $this->diferenciasAgencia(
                $base,
                $filtros,
                $paginar
            ),

            default => $this->resumen(
                $base,
                $filtros,
                $paginar
            ),
        };
    }

    private function agenciasPermitidas(Usuario $usuario): ?array
    {
        $usuario->loadMissing('rol');

        abort_unless(
            $usuario->estado === 'ACTIVO'
                && $usuario->rol
                && $usuario->rol->estado,
            403,
            'Usuario sin autorización para consultar reportes.'
        );

        $rol = $usuario->rol->nombre;

        if (in_array($rol, self::ROLES_GLOBALES, true)) {
            return null;
        }

        abort_unless(
            in_array($rol, self::ROLES_AGENCIA, true),
            403,
            'Su rol no tiene acceso a la reportería de agencias.'
        );

        return $this->acceso
            ->asignacionesVigentes($usuario)
            ->pluck('agencia_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function consultaBase(
        Usuario $usuario,
        array $filtros
    ): Builder {
        $agencias = $this->agenciasPermitidas($usuario);

        if (
            $agencias !== null
            && $filtros['agencia_id'] > 0
        ) {
            abort_unless(
                in_array(
                    $filtros['agencia_id'],
                    $agencias,
                    true
                ),
                403,
                'No tiene acceso a la agencia seleccionada.'
            );
        }

        return DB::table('arqueos_agencia as arq')
            ->when(
                $agencias !== null,
                fn (Builder $q) => $q->whereIn(
                    'arq.agencia_id',
                    $agencias
                )
            )
            ->when(
                $filtros['agencia_id'] > 0,
                fn (Builder $q) => $q->where(
                    'arq.agencia_id',
                    $filtros['agencia_id']
                )
            )
            ->when(
                $filtros['receptor_id'] > 0,
                fn (Builder $q) => $q->where(
                    'arq.receptor_usuario_id',
                    $filtros['receptor_id']
                )
            )
            ->when(
                $filtros['ventanilla_id'] > 0,
                fn (Builder $q) => $q->where(
                    'arq.ventanilla_id',
                    $filtros['ventanilla_id']
                )
            )
            ->when(
                $filtros['turno'] !== '',
                fn (Builder $q) => $q->where(
                    'arq.turno_nombre_historico',
                    $filtros['turno']
                )
            )
            ->when(
                $filtros['tipo'] !== '',
                fn (Builder $q) => $q->where(
                    'arq.tipo',
                    $filtros['tipo']
                )
            )
            ->when(
                $filtros['estado'] !== '',
                fn (Builder $q) => $q->where(
                    'arq.estado',
                    $filtros['estado']
                )
            )
            ->when(
                $filtros['desde'],
                fn (Builder $q) => $q->whereDate(
                    'arq.fecha_operativa',
                    '>=',
                    $filtros['desde']
                )
            )
            ->when(
                $filtros['hasta'],
                fn (Builder $q) => $q->whereDate(
                    'arq.fecha_operativa',
                    '<=',
                    $filtros['hasta']
                )
            );
    }

    private function columnasDetalle(): array
    {
        return [
            'numero_arqueo' => 'Número de Arqueo',
            'fecha_operativa' => 'Fecha',
            'agencia' => 'Agencia',
            'ventanilla' => 'Ventanilla',
            'receptor' => 'Receptor',
            'turno' => 'Turno',
            'tipo' => 'Tipo',
            'estado' => 'Estado',
            'total_corte' => 'Total Corte',
            'total_arqueado' => 'Total Arqueado',
            'diferencia' => 'Diferencia',
        ];
    }

    private function seleccionarDetalle(Builder $query): Builder
    {
        return $query->select([
            'arq.id',
            'arq.numero_arqueo',
            'arq.agencia_id',
            'arq.ventanilla_id',
            'arq.receptor_usuario_id',
            'arq.fecha_operativa',
            'arq.agencia_nombre_historico as agencia',
            'arq.ventanilla_codigo_historico as ventanilla',
            'arq.receptor_nombre_historico as receptor',
            'arq.turno_nombre_historico as turno',
            'arq.tipo',
            'arq.estado',
            'arq.total_corte',
            'arq.total_arqueado',
            'arq.diferencia',
            'arq.justificacion_diferencia',
            'arq.observaciones',
            'arq.certificado_at',
            'arq.anulado_at',
        ]);
    }

    private function listado(
        Builder $base,
        array $filtros,
        bool $paginar,
        string $reporte
    ): array {
        $query = $this->seleccionarDetalle($base)
            ->orderByDesc('arq.fecha_operativa')
            ->orderByDesc('arq.id');

        $total = (clone $query)->count();

        return [
            'resultados' => $this->resolver(
                $query,
                $paginar,
                $filtros['por_pagina']
            ),
            'columnas' => $this->columnasDetalle(),
            'metricas' => [
                'Registros encontrados' => $total,
            ],
            'titulo' => self::REPORTES[$reporte],
        ];
    }

    private function resumen(
        Builder $base,
        array $filtros,
        bool $paginar
    ): array {
        $vigentes = (clone $base)
            ->where('arq.estado', '!=', 'ANULADO');

        $estadisticas = (clone $vigentes)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                "SUM(CASE WHEN arq.certificado_at IS NOT NULL
                    THEN 1 ELSE 0 END) as certificados"
            )
            ->selectRaw(
                "SUM(CASE WHEN arq.certificado_at IS NOT NULL
                    AND arq.diferencia = 0
                    THEN 1 ELSE 0 END) as exactos"
            )
            ->selectRaw(
                "SUM(CASE WHEN arq.certificado_at IS NOT NULL
                    AND arq.diferencia < 0
                    THEN 1 ELSE 0 END) as faltantes"
            )
            ->selectRaw(
                "SUM(CASE WHEN arq.certificado_at IS NOT NULL
                    AND arq.diferencia > 0
                    THEN 1 ELSE 0 END) as sobrantes"
            )
            ->selectRaw(
                "SUM(CASE WHEN arq.certificado_at IS NOT NULL
                    AND arq.diferencia < 0
                    THEN ABS(arq.diferencia)
                    ELSE 0 END) as monto_faltantes"
            )
            ->selectRaw(
                "SUM(CASE WHEN arq.certificado_at IS NOT NULL
                    AND arq.diferencia > 0
                    THEN arq.diferencia
                    ELSE 0 END) as monto_sobrantes"
            )
            ->first();

        $resultados = $this->seleccionarDetalle($base)
            ->orderByDesc('arq.fecha_operativa')
            ->orderByDesc('arq.id');

        return [
            'resultados' => $this->resolver(
                $resultados,
                $paginar,
                $filtros['por_pagina']
            ),
            'columnas' => $this->columnasDetalle(),
            'metricas' => [
                'Arqueos vigentes' => (int) ($estadisticas->total ?? 0),
                'Certificados' => (int) ($estadisticas->certificados ?? 0),
                'Exactos' => (int) ($estadisticas->exactos ?? 0),
                'Faltantes' => (int) ($estadisticas->faltantes ?? 0),
                'Sobrantes' => (int) ($estadisticas->sobrantes ?? 0),
                'Monto faltantes' => $this->moneda(
                    $estadisticas->monto_faltantes ?? 0
                ),
                'Monto sobrantes' => $this->moneda(
                    $estadisticas->monto_sobrantes ?? 0
                ),
            ],
            'titulo' => self::REPORTES['resumen'],
        ];
    }

    private function incidencias(
        Builder $base,
        array $filtros,
        bool $paginar,
        string $tipo
    ): array {
        $operador = $tipo === 'FALTANTE' ? '<' : '>';

        $query = $base
            ->where('arq.estado', '!=', 'ANULADO')
            ->whereNotNull('arq.certificado_at')
            ->where('arq.diferencia', $operador, 0);

        $resumen = (clone $query)
            ->selectRaw('COUNT(*) as casos')
            ->selectRaw(
                'COALESCE(SUM(ABS(arq.diferencia)),0) as monto'
            )
            ->first();

        $resultados = $this->seleccionarDetalle($query)
            ->orderByRaw('ABS(arq.diferencia) DESC')
            ->orderByDesc('arq.fecha_operativa');

        return [
            'resultados' => $this->resolver(
                $resultados,
                $paginar,
                $filtros['por_pagina']
            ),
            'columnas' => $this->columnasDetalle(),
            'metricas' => [
                'Casos encontrados' => (int) ($resumen->casos ?? 0),
                'Monto acumulado' => $this->moneda(
                    $resumen->monto ?? 0
                ),
            ],
            'titulo' => self::REPORTES[
                $tipo === 'FALTANTE'
                    ? 'faltantes'
                    : 'sobrantes'
            ],
        ];
    }

    private function rankingReceptores(
        Builder $base,
        array $filtros,
        bool $paginar,
        string $tipo
    ): array {
        $operador = $tipo === 'FALTANTE' ? '<' : '>';

        $query = $base
            ->where('arq.estado', '!=', 'ANULADO')
            ->whereNotNull('arq.certificado_at')
            ->where('arq.diferencia', $operador, 0)
            ->select([
                'arq.receptor_usuario_id',
                'arq.receptor_nombre_historico as receptor',
            ])
            ->selectRaw(
                'COUNT(*) as cantidad_incidencias'
            )
            ->selectRaw(
                'SUM(ABS(arq.diferencia)) as monto_acumulado'
            )
            ->selectRaw(
                'MAX(ABS(arq.diferencia)) as mayor_incidencia'
            )
            ->groupBy(
                'arq.receptor_usuario_id',
                'arq.receptor_nombre_historico'
            )
            ->orderByDesc('cantidad_incidencias')
            ->orderByDesc('monto_acumulado');

        return [
            'resultados' => $this->resolver(
                $query,
                $paginar,
                $filtros['por_pagina']
            ),
            'columnas' => [
                'posicion' => '#',
                'receptor' => 'Receptor',
                'cantidad_incidencias' => 'Incidencias',
                'monto_acumulado' => 'Monto Acumulado',
                'mayor_incidencia' => 'Mayor Incidencia',
            ],
            'metricas' => [],
            'titulo' => self::REPORTES[
                $tipo === 'FALTANTE'
                    ? 'ranking-faltantes'
                    : 'ranking-sobrantes'
            ],
        ];
    }

    private function diferenciasAgencia(
        Builder $base,
        array $filtros,
        bool $paginar
    ): array {
        $query = $base
            ->where('arq.estado', '!=', 'ANULADO')
            ->whereNotNull('arq.certificado_at')
            ->select([
                'arq.agencia_id',
                'arq.agencia_codigo_historico',
                'arq.agencia_nombre_historico as agencia',
            ])
            ->selectRaw(
                'COUNT(*) as total_arqueos'
            )
            ->selectRaw(
                'SUM(CASE WHEN arq.diferencia < 0
                    THEN 1 ELSE 0 END) as faltantes'
            )
            ->selectRaw(
                'SUM(CASE WHEN arq.diferencia > 0
                    THEN 1 ELSE 0 END) as sobrantes'
            )
            ->selectRaw(
                'SUM(CASE WHEN arq.diferencia = 0
                    THEN 1 ELSE 0 END) as exactos'
            )
            ->selectRaw(
                'SUM(CASE WHEN arq.diferencia < 0
                    THEN ABS(arq.diferencia)
                    ELSE 0 END) as monto_faltantes'
            )
            ->selectRaw(
                'SUM(CASE WHEN arq.diferencia > 0
                    THEN arq.diferencia
                    ELSE 0 END) as monto_sobrantes'
            )
            ->groupBy(
                'arq.agencia_id',
                'arq.agencia_codigo_historico',
                'arq.agencia_nombre_historico'
            )
            ->orderByDesc('monto_faltantes');

        return [
            'resultados' => $this->resolver(
                $query,
                $paginar,
                $filtros['por_pagina']
            ),
            'columnas' => [
                'agencia' => 'Agencia',
                'total_arqueos' => 'Arqueos',
                'exactos' => 'Exactos',
                'faltantes' => 'Faltantes',
                'sobrantes' => 'Sobrantes',
                'monto_faltantes' => 'Monto Faltantes',
                'monto_sobrantes' => 'Monto Sobrantes',
            ],
            'metricas' => [],
            'titulo' => self::REPORTES['diferencias-agencia'],
        ];
    }

    /**
     * Cumplimiento calculado desde asignaciones operativas persistentes.
     * No depende de jornadas_receptor.
     * Sin calendario laboral, los días de vigencia se consideran programados.
     */
    private function cumplimiento(Usuario $usuario, array $filtros, bool $paginar): array
    {
        $permitidas = $this->agenciasPermitidas($usuario);
        if ($permitidas !== null && $filtros['agencia_id'] > 0) {
            abort_unless(in_array($filtros['agencia_id'], $permitidas, true), 403);
        }

        $desde = \Illuminate\Support\Carbon::parse($filtros['desde'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $hasta = \Illuminate\Support\Carbon::parse($filtros['hasta'] ?? today()->toDateString())->startOfDay();
        abort_if($desde->gt($hasta), 422, 'La fecha inicial debe ser anterior a la final.');
        abort_if($desde->diffInDays($hasta) > 366, 422, 'El reporte de cumplimiento admite un máximo de 367 días por consulta.');
        $hasta = $hasta->min(today());

        $asignaciones = DB::table('asignaciones_operativas_receptor as ao')
            ->join('agencias as a', 'a.id', '=', 'ao.agencia_id')
            ->join('ventanillas_agencia as v', 'v.id', '=', 'ao.ventanilla_id')
            ->join('turnos_agencia as t', 't.id', '=', 'ao.turno_id')
            ->join('usuarios as u', 'u.id', '=', 'ao.receptor_usuario_id')
            ->when($permitidas !== null, fn (Builder $q) => $q->whereIn('ao.agencia_id', $permitidas))
            ->when($filtros['agencia_id'] > 0, fn (Builder $q) => $q->where('ao.agencia_id', $filtros['agencia_id']))
            ->when($filtros['receptor_id'] > 0, fn (Builder $q) => $q->where('ao.receptor_usuario_id', $filtros['receptor_id']))
            ->when($filtros['ventanilla_id'] > 0, fn (Builder $q) => $q->where('ao.ventanilla_id', $filtros['ventanilla_id']))
            ->when($filtros['turno'] !== '', fn (Builder $q) => $q->where('t.nombre', $filtros['turno']))
            ->whereDate('ao.fecha_inicio', '<=', $hasta->toDateString())
            ->where(fn (Builder $q) => $q->whereNull('ao.fecha_fin')->orWhereDate('ao.fecha_fin', '>=', $desde->toDateString()))
            ->get(['ao.id', 'ao.agencia_id', 'ao.receptor_usuario_id', 'ao.ventanilla_id',
                'ao.turno_id', 'ao.fecha_inicio', 'ao.fecha_fin', 'a.nombre as agencia',
                'v.codigo as ventanilla', 't.nombre as turno', 't.hora_fin',
                't.cruza_medianoche', 'u.usuario as receptor']);

        $arqueos = DB::table('arqueos_agencia')
            ->where('tipo', 'ORDINARIO')
            ->whereBetween('fecha_operativa', [$desde->toDateString(), $hasta->toDateString()])
            ->when($permitidas !== null, fn (Builder $q) => $q->whereIn('agencia_id', $permitidas))
            ->when($filtros['agencia_id'] > 0, fn (Builder $q) => $q->where('agencia_id', $filtros['agencia_id']))
            ->get(['id', 'asignacion_operativa_id', 'agencia_id', 'receptor_usuario_id',
                'ventanilla_id', 'fecha_operativa', 'estado', 'certificado_at']);

        $porAsignacion = $arqueos->groupBy(fn ($arq) => ($arq->asignacion_operativa_id ?? 0).'|'.$arq->fecha_operativa);
        $filas = collect();
        $cumplidos = 0;
        $pendientes = 0;
        $noVencidos = 0;

        foreach ($asignaciones as $asignacion) {
            $inicio = $desde->copy()->max(\Illuminate\Support\Carbon::parse($asignacion->fecha_inicio));
            $fin = $hasta->copy();
            if ($asignacion->fecha_fin) {
                $fin = $fin->min(\Illuminate\Support\Carbon::parse($asignacion->fecha_fin));
            }
            for ($dia = $inicio->copy(); $dia->lte($fin); $dia->addDay()) {
                $fecha = $dia->toDateString();
                $registros = $porAsignacion->get($asignacion->id.'|'.$fecha, collect());
                // Compatibilidad con arqueos anteriores que no tenían asignación_operativa_id.
                if ($registros->isEmpty()) {
                    $registros = $arqueos->filter(fn ($arq) => !$arq->asignacion_operativa_id
                        && $arq->fecha_operativa === $fecha
                        && (int)$arq->agencia_id === (int)$asignacion->agencia_id
                        && (int)$arq->receptor_usuario_id === (int)$asignacion->receptor_usuario_id
                        && (int)$arq->ventanilla_id === (int)$asignacion->ventanilla_id);
                }
                $certificado = $registros->contains(fn ($arq) => $arq->estado !== 'ANULADO' && $arq->certificado_at !== null);
                $enProceso = $registros->contains(fn ($arq) => $arq->estado !== 'ANULADO');
                $horaFin = substr((string)$asignacion->hora_fin, 0, 8);
                $vencimiento = \Illuminate\Support\Carbon::parse($fecha.' '.$horaFin);
                if ($asignacion->cruza_medianoche) $vencimiento->addDay();
                $estado = $certificado ? 'CUMPLIDO' : ($enProceso ? 'PENDIENTE_CERTIFICACION' : ($vencimiento->isFuture() ? 'NO_VENCIDO' : 'SIN_ARQUEO_CERTIFICADO'));
                if ($estado === 'CUMPLIDO') $cumplidos++;
                if ($estado === 'PENDIENTE_CERTIFICACION') $pendientes++;
                if ($estado === 'NO_VENCIDO') $noVencidos++;
                $filas->push((object)[
                    'fecha_operativa' => $fecha,
                    'agencia' => $asignacion->agencia,
                    'ventanilla' => $asignacion->ventanilla,
                    'receptor' => $asignacion->receptor,
                    'turno' => $asignacion->turno,
                    'cumplimiento' => $estado,
                ]);
            }
        }
        $filas = $filas->sortByDesc('fecha_operativa')->values();
        $total = $filas->count();
        $resultados = $filas;
        if ($paginar) {
            $pagina = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $resultados = new \Illuminate\Pagination\LengthAwarePaginator(
                $filas->forPage($pagina, $filtros['por_pagina'])->values(),
                $total, $filtros['por_pagina'], $pagina,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => request()->query()]
            );
        }
        $vencidos = $total - $noVencidos;
        return [
            'resultados' => $resultados,
            'columnas' => [
                'fecha_operativa' => 'Fecha', 'agencia' => 'Agencia',
                'ventanilla' => 'Ventanilla', 'receptor' => 'Receptor',
                'turno' => 'Turno', 'cumplimiento' => 'Cumplimiento',
            ],
            'metricas' => [
                'Turnos evaluados' => $total,
                'Con arqueo certificado' => $cumplidos,
                'Pendientes de certificación' => $pendientes,
                'Sin arqueo certificado' => $vencidos - $cumplidos - $pendientes,
                'Turnos no vencidos' => $noVencidos,
                'Porcentaje de cumplimiento' => $vencidos > 0 ? number_format($cumplidos / $vencidos * 100, 2).'%' : '0.00%',
            ],
            'titulo' => self::REPORTES['cumplimiento'],
        ];
    }

    private function resolver(
        Builder $query,
        bool $paginar,
        int $porPagina
    ) {
        if ($paginar) {
            return $query
                ->paginate($porPagina)
                ->withQueryString();
        }

        return $query->get();
    }

    private function moneda(mixed $valor): string
    {
        return 'Q '.number_format(
            (float) $valor,
            2,
            '.',
            ','
        );
    }
}
