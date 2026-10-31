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

    private function cumplimiento(
        Usuario $usuario,
        array $filtros,
        bool $paginar
    ): array {
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
                'No tiene acceso a esta agencia.'
            );
        }

        /*
         * Una jornada programada se considera cumplida
         * cuando tiene un arqueo ordinario certificado
         * y no anulado.
         *
         * Las jornadas futuras no se clasifican como
         * incumplimientos.
         */

        $query = DB::table('jornadas_receptor as j')
            ->join(
                'agencias as a',
                'a.id',
                '=',
                'j.agencia_id'
            )
            ->leftJoin(
                'ventanillas_agencia as v',
                'v.id',
                '=',
                'j.ventanilla_id'
            )
            ->leftJoin(
                'usuarios as u',
                'u.id',
                '=',
                'j.receptor_usuario_id'
            )
            ->whereDate(
                'j.fecha_operativa',
                '<=',
                now()->toDateString()
            )
            ->when(
                $agencias !== null,
                fn (Builder $q) => $q->whereIn(
                    'j.agencia_id',
                    $agencias
                )
            )
            ->when(
                $filtros['agencia_id'] > 0,
                fn (Builder $q) => $q->where(
                    'j.agencia_id',
                    $filtros['agencia_id']
                )
            )
            ->when(
                $filtros['receptor_id'] > 0,
                fn (Builder $q) => $q->where(
                    'j.receptor_usuario_id',
                    $filtros['receptor_id']
                )
            )
            ->when(
                $filtros['ventanilla_id'] > 0,
                fn (Builder $q) => $q->where(
                    'j.ventanilla_id',
                    $filtros['ventanilla_id']
                )
            )
            ->when(
                $filtros['desde'],
                fn (Builder $q) => $q->whereDate(
                    'j.fecha_operativa',
                    '>=',
                    $filtros['desde']
                )
            )
            ->when(
                $filtros['hasta'],
                fn (Builder $q) => $q->whereDate(
                    'j.fecha_operativa',
                    '<=',
                    $filtros['hasta']
                )
            )
            ->select([
                'j.id',
                'j.fecha_operativa',
                'j.agencia_id',
                'j.receptor_usuario_id',
                'a.nombre as agencia',
                'v.codigo as ventanilla',
                'u.usuario as receptor',
                'j.estado as estado_jornada',
            ])
            ->selectRaw(
                "CASE WHEN EXISTS (
                    SELECT 1
                    FROM arqueos_agencia arq
                    WHERE arq.jornada_id = j.id
                      AND arq.tipo = 'ORDINARIO'
                      AND arq.estado <> 'ANULADO'
                      AND arq.certificado_at IS NOT NULL
                ) THEN 'CUMPLIDO'
                ELSE 'SIN_ARQUEO_CERTIFICADO'
                END as cumplimiento"
            )
            ->orderByDesc('j.fecha_operativa')
            ->orderBy('a.nombre');

        $total = (clone $query)->count();

        $cumplidos = DB::table('jornadas_receptor as j')
            ->whereIn(
                'j.id',
                (clone $query)
                    ->reorder()
                    ->select('j.id')
            )
            ->whereExists(function (Builder $sub) {
                $sub->selectRaw('1')
                    ->from('arqueos_agencia as arq')
                    ->whereColumn(
                        'arq.jornada_id',
                        'j.id'
                    )
                    ->where('arq.tipo', 'ORDINARIO')
                    ->where('arq.estado', '!=', 'ANULADO')
                    ->whereNotNull('arq.certificado_at');
            })
            ->count();

        return [
            'resultados' => $this->resolver(
                $query,
                $paginar,
                $filtros['por_pagina']
            ),
            'columnas' => [
                'fecha_operativa' => 'Fecha',
                'agencia' => 'Agencia',
                'ventanilla' => 'Ventanilla',
                'receptor' => 'Usuario Receptor',
                'estado_jornada' => 'Estado Jornada',
                'cumplimiento' => 'Cumplimiento',
            ],
            'metricas' => [
                'Jornadas registradas' => $total,
                'Con arqueo certificado' => $cumplidos,
                'Sin arqueo certificado' => max(
                    0,
                    $total - $cumplidos
                ),
                'Porcentaje de cumplimiento' => $total > 0
                    ? number_format(
                        ($cumplidos / $total) * 100,
                        2
                    ).'%'
                    : '0.00%',
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
