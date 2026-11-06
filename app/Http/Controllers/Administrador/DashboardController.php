<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Roles exclusivos de cada módulo. Los roles compartidos (Administrador,
     * Auditoría y Gerencia) se muestran solamente en la vista general.
     * Ajustar aquí si el catálogo institucional usa otros nombres.
     */
    private const ROLES_AGENTES = [
        'AGENTE',
        'PROMOTOR',
        'JEFEDEAGENTES',
    ];

    private const ROLES_AGENCIAS = [
        'RECEPTOR',
        'JEFECAJA',
        'SUBJEFECAJA',
        'JEFEAGENCIA',
    ];

    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

        $vista = $request->query('vista', 'general');
        if (! in_array($vista, ['general', 'agentes', 'agencias'], true)) {
            $vista = 'general';
        }

        // Se filtra por el rol principal (usuarios.rol_id) utilizado por el sistema.
        // En la vista general no se excluye ningún rol.
        $rolesPermitidos = match ($vista) {
            'agentes' => self::ROLES_AGENTES,
            'agencias' => self::ROLES_AGENCIAS,
            default => null,
        };

        $consultaUsuarios = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id');
        if ($rolesPermitidos !== null) {
            $consultaUsuarios->whereIn(DB::raw('UPPER(TRIM(rol.nombre))'), $rolesPermitidos);
        }

        $resumen = (object) [
            'total_usuarios' => (clone $consultaUsuarios)->count('u.id'),
            'usuarios_activos' => (clone $consultaUsuarios)->where('u.estado', 'ACTIVO')->count('u.id'),
            'usuarios_inactivos' => (clone $consultaUsuarios)->where('u.estado', 'INACTIVO')->count('u.id'),
        ];

        $usuariosPorRol = (clone $consultaUsuarios)
            ->select('rol.nombre')
            ->selectRaw('COUNT(u.id) as total')
            ->groupBy('rol.id', 'rol.nombre')
            ->orderByDesc('total')
            ->get();

        $agentes = null;
        $agencias = null;
        $arqueosRecientes = collect();
        $arqueosAgenciaRecientes = collect();

        if ($vista !== 'agencias') {
            $agentes = (object) [
                'total_agentes' => DB::table('agentes')->count(),
                'agentes_activos' => DB::table('agentes')->where('estado', 'ACTIVO')->count(),
                'total_promotores' => DB::table('usuarios as u')->join('roles as rol', 'rol.id', '=', 'u.rol_id')->whereRaw('UPPER(TRIM(rol.nombre)) = ?', ['PROMOTOR'])->count(),
                'rutas_activas' => DB::table('rutas')->where('estado', true)->count(),
                'regiones_activas' => DB::table('regiones')->where('estado', true)->count(),
                'arqueos_hoy' => DB::table('arqueos')->whereDate('fecha_arqueo', today())->where('estado', '!=', 'ANULADO')->count(),
                'pendientes_certificacion' => DB::table('arqueos')->whereIn('estado', ['PENDIENTE', 'PENDIENTE_CERTIFICACION'])->count(),
                'arqueos_anulados' => DB::table('arqueos')->where('estado', 'ANULADO')->count(),
                'extemporaneos' => DB::table('arqueos')->where('fuera_fecha_ordinaria', true)->where('estado', '!=', 'ANULADO')->count(),
            ];

            $arqueosRecientes = DB::table('arqueos as arq')
                ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
                ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
                ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
                ->leftJoin('usuarios as uc', 'uc.id', '=', 'arq.creado_por')
                ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'uc.id')
                ->select(['arq.id', 'arq.numero_arqueo', 'arq.fecha_arqueo', 'arq.tipo', 'arq.estado', 'arq.diferencia', 'arq.fuera_fecha_ordinaria', 'a.codigo_agente', 'a.nombre_negocio', 'r.nombre as ruta_nombre', 'reg.nombre as region_nombre', 'uc.usuario as responsable_usuario', 'dp.nombres as responsable_nombres', 'dp.apellidos as responsable_apellidos'])
                ->orderByDesc('arq.fecha_arqueo')->orderByDesc('arq.id')->limit(10)->get();
        }

        if ($vista !== 'agentes') {
            $existenAgencias = Schema::hasTable('agencias');
            $existenArqueos = Schema::hasTable('arqueos_agencia');
            $colFecha = $existenArqueos ? $this->primeraColumna('arqueos_agencia', ['fecha_operativa', 'fecha_arqueo']) : null;
            $colDiferencia = $existenArqueos ? $this->primeraColumna('arqueos_agencia', ['diferencia']) : null;
            $colEstadoAgencia = $existenAgencias ? $this->primeraColumna('agencias', ['estado', 'activo']) : null;
            $colNombreAgencia = $existenAgencias ? $this->primeraColumna('agencias', ['nombre', 'nombre_agencia', 'descripcion']) : null;

            $agencias = (object) [
                'total_agencias' => $existenAgencias ? DB::table('agencias')->count() : 0,
                'agencias_activas' => $existenAgencias && $colEstadoAgencia
                    ? DB::table('agencias')->whereIn($colEstadoAgencia, ['ACTIVO', 1])->count() : null,
                'total_arqueos' => $existenArqueos ? DB::table('arqueos_agencia')->count() : 0,
                'arqueos_hoy' => $existenArqueos && $colFecha
                    ? DB::table('arqueos_agencia')->whereDate($colFecha, today())->where('estado', '!=', 'ANULADO')->count() : 0,
                'pendientes_certificacion' => $existenArqueos
                    ? DB::table('arqueos_agencia')->whereIn('estado', ['PENDIENTE', 'PENDIENTE_CERTIFICACION'])->count() : 0,
                'arqueos_anulados' => $existenArqueos
                    ? DB::table('arqueos_agencia')->where('estado', 'ANULADO')->count() : 0,
                'arqueos_con_diferencia' => $existenArqueos && $colDiferencia
                    ? DB::table('arqueos_agencia')->where('estado', '!=', 'ANULADO')->whereNotNull($colDiferencia)->where($colDiferencia, '!=', 0)->count() : null,
            ];

            if ($existenArqueos) {
                $consulta = DB::table('arqueos_agencia as arq');
                $columnas = ['arq.id', 'arq.numero_arqueo', 'arq.tipo', 'arq.estado'];
                $columnas[] = $colFecha ? 'arq.'.$colFecha.' as fecha' : DB::raw('NULL as fecha');
                $columnas[] = $colDiferencia ? 'arq.'.$colDiferencia.' as diferencia' : DB::raw('NULL as diferencia');
                if ($existenAgencias && Schema::hasColumn('arqueos_agencia', 'agencia_id')) {
                    $consulta->leftJoin('agencias as ag', 'ag.id', '=', 'arq.agencia_id');
                    $columnas[] = $colNombreAgencia ? 'ag.'.$colNombreAgencia.' as agencia_nombre' : DB::raw('NULL as agencia_nombre');
                } else {
                    $columnas[] = DB::raw('NULL as agencia_nombre');
                }
                $arqueosAgenciaRecientes = $consulta->select($columnas)
                    ->orderByDesc($colFecha ? 'arq.'.$colFecha : 'arq.id')
                    ->orderByDesc('arq.id')->limit(10)->get();
            }
        }

        return view('administrador.dashboard', compact(
            'vista', 'resumen', 'agentes', 'agencias', 'arqueosRecientes',
            'arqueosAgenciaRecientes', 'usuariosPorRol'
        ));
    }

    private function primeraColumna(string $tabla, array $candidatas): ?string
    {
        foreach ($candidatas as $columna) {
            if (Schema::hasColumn($tabla, $columna)) {
                return $columna;
            }
        }
        return null;
    }

    private function validarAdministrador(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');
        abort_if(! $usuario->rol || $usuario->rol->nombre !== 'Administrador', 403,
            'No tiene autorización para acceder a esta sección.');
    }
}
