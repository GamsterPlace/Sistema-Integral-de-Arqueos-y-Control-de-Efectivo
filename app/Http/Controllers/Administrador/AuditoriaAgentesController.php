<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AuditoriaAgentesController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        $auditorId = $request->integer('auditor_id');
        $agenteId = $request->integer('agente_id');
        $estado = trim((string) $request->string('estado'));
        $resultado = trim((string) $request->string('resultado'));
        $buscar = trim((string) $request->string('buscar'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $auditores = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('rol.nombre', 'Auditoria')
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->get([
                'u.id',
                'u.usuario',
                'u.estado',
                'dp.nombres',
                'dp.apellidos',
            ]);

        $agentes = DB::table('agentes')
            ->orderBy('nombre_negocio')
            ->get([
                'id',
                'codigo_agente',
                'nombre_negocio',
            ]);

        $arqueos = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->join('usuarios as ua', 'ua.id', '=', 'arq.creado_por')
            ->join('roles as rol_auditor', 'rol_auditor.id', '=', 'ua.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'ua.id')
            ->where('arq.tipo', 'VISITA_AUDITORIA')
            ->where('rol_auditor.nombre', 'Auditoria')
            ->when(
                $auditorId > 0,
                fn ($query) => $query->where('arq.creado_por', $auditorId)
            )
            ->when(
                $agenteId > 0,
                fn ($query) => $query->where('arq.agente_id', $agenteId)
            )
            ->when(
                $estado !== '',
                fn ($query) => $query->where('arq.estado', $estado)
            )
            ->when(
                $resultado === 'FALTANTE',
                fn ($query) => $query->where('arq.diferencia', '<', 0)
            )
            ->when(
                $resultado === 'SOBRANTE',
                fn ($query) => $query->where('arq.diferencia', '>', 0)
            )
            ->when(
                $resultado === 'EXACTO',
                fn ($query) => $query->where('arq.diferencia', '=', 0)
            )
            ->when(
                $desde,
                fn ($query) => $query->whereDate('arq.fecha_arqueo', '>=', $desde)
            )
            ->when(
                $hasta,
                fn ($query) => $query->whereDate('arq.fecha_arqueo', '<=', $hasta)
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where('arq.numero_arqueo', 'like', '%' . $buscar . '%')
                            ->orWhere('arq.codigo_agente_historico', 'like', '%' . $buscar . '%')
                            ->orWhere('arq.nombre_negocio_historico', 'like', '%' . $buscar . '%')
                            ->orWhere('arq.nombre_propietario_historico', 'like', '%' . $buscar . '%')
                            ->orWhere('arq.ruta_historica', 'like', '%' . $buscar . '%')
                            ->orWhere('arq.region_historica', 'like', '%' . $buscar . '%')
                            ->orWhere('ua.usuario', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.nombres', 'like', '%' . $buscar . '%')
                            ->orWhere('dp.apellidos', 'like', '%' . $buscar . '%');
                    });
                }
            )
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.estado',
                'arq.fuera_fecha_ordinaria',
                'arq.codigo_agente_historico',
                'arq.nombre_negocio_historico',
                'arq.nombre_propietario_historico',
                'arq.ruta_historica',
                'arq.region_historica',
                'arq.total_arqueado',
                'arq.saldo_sistema',
                'arq.diferencia',
                'arq.certificacion',
                'arq.observaciones',
                'ua.id as auditor_id',
                'ua.usuario as auditor_usuario',
                'dp.nombres as auditor_nombres',
                'dp.apellidos as auditor_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(20)
            ->withQueryString();

        $baseResumen = DB::table('arqueos')
            ->where('tipo', 'VISITA_AUDITORIA');

        $resumen = (object) [
            'total' => (clone $baseResumen)->count(),

            'certificados' => (clone $baseResumen)
                ->where('estado', 'CERTIFICADO')
                ->count(),

            'pendientes' => (clone $baseResumen)
                ->where('estado', 'PENDIENTE_CERTIFICACION')
                ->count(),

            'anulados' => (clone $baseResumen)
                ->where('estado', 'ANULADO')
                ->count(),

            'faltantes' => (clone $baseResumen)
                ->where('estado', '!=', 'ANULADO')
                ->where('diferencia', '<', 0)
                ->count(),

            'sobrantes' => (clone $baseResumen)
                ->where('estado', '!=', 'ANULADO')
                ->where('diferencia', '>', 0)
                ->count(),
        ];

        return view(
            'administrador.auditoria-agentes.index',
            compact(
                'arqueos',
                'auditores',
                'agentes',
                'auditorId',
                'agenteId',
                'estado',
                'resultado',
                'buscar',
                'desde',
                'hasta',
                'resumen'
            )
        );
    }

    public function show(
        Request $request,
        Arqueo $arqueo
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        abort_if(
            $arqueo->tipo !== 'VISITA_AUDITORIA',
            404,
            'El arqueo solicitado no corresponde a una Auditoría de Agente.'
        );

        $arqueo->loadMissing([
            'detalles',
            'firmas',
        ]);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        $auditor = DB::table('usuarios as u')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('u.id', $arqueo->creado_por)
            ->select([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();

        return view(
            'administrador.auditoria-agentes.show',
            compact(
                'arqueo',
                'billetes',
                'monedas',
                'auditor'
            )
        );
    }

    public function imprimir(
        Request $request,
        Arqueo $arqueo
    ): Response {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarAdministrador($usuario);

        abort_if(
            $arqueo->tipo !== 'VISITA_AUDITORIA',
            404,
            'El arqueo solicitado no corresponde a una Auditoría de Agente.'
        );

        $arqueo->loadMissing([
            'detalles',
            'firmas',
        ]);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        $auditor = DB::table('usuarios as u')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('u.id', $arqueo->creado_por)
            ->select([
                'u.id',
                'u.usuario',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();

        $pdf = Pdf::loadView(
            'administrador.auditoria-agentes.pdf',
            compact(
                'arqueo',
                'billetes',
                'monedas',
                'auditor'
            )
        )->setPaper(
            'letter',
            'portrait'
        );

        return $pdf->stream(
            $arqueo->numero_arqueo . '.pdf'
        );
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
