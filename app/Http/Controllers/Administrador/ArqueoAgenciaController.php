<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ArqueoAgenciaController extends Controller
{
    public function index(Request $request): View
    {
        $this->validarAdministrador($request->user());

        $filtros = $request->validate([
            'agencia_id' => ['nullable', 'integer', 'min:1', 'exists:agencias,id'],
            'ventanilla_id' => ['nullable', 'integer', 'min:1', 'exists:ventanillas_agencia,id'],
            'receptor_id' => ['nullable', 'integer', 'min:1', 'exists:usuarios,id'],
            'tipo' => ['nullable', Rule::in(['ORDINARIO', 'ENTREGA', 'SORPRESIVO', 'AUDITORIA'])],
            'estado' => ['nullable', 'string', 'max:35'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);

        $agenciaId = (int) ($filtros['agencia_id'] ?? 0);
        $ventanillaId = (int) ($filtros['ventanilla_id'] ?? 0);
        $receptorId = (int) ($filtros['receptor_id'] ?? 0);
        $tipo = $filtros['tipo'] ?? '';
        $estado = trim($filtros['estado'] ?? '');
        $desde = $filtros['desde'] ?? '';
        $hasta = $filtros['hasta'] ?? '';
        $buscar = trim($filtros['buscar'] ?? '');

        $agencias = DB::table('agencias')->orderBy('nombre')->get(['id', 'codigo', 'nombre']);
        $ventanillas = DB::table('ventanillas_agencia')
            ->when($agenciaId, fn ($q) => $q->where('agencia_id', $agenciaId))
            ->orderBy('codigo')->get(['id', 'agencia_id', 'codigo', 'descripcion']);
        $receptores = DB::table('usuarios as u')
            ->join('asignaciones_personal_agencia as ap', 'ap.usuario_id', '=', 'u.id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('ap.cargo', 'RECEPTOR')
            ->when($agenciaId, fn ($q) => $q->where('ap.agencia_id', $agenciaId))
            ->select('u.id', 'u.usuario', 'dp.nombres', 'dp.apellidos')->distinct()
            ->orderBy('u.usuario')->get();

        $estados = DB::table('arqueos_agencia')->select('estado')->distinct()->orderBy('estado')->pluck('estado');

        $consulta = DB::table('arqueos_agencia as arq')
            ->when($agenciaId, fn ($q) => $q->where('arq.agencia_id', $agenciaId))
            ->when($ventanillaId, fn ($q) => $q->where('arq.ventanilla_id', $ventanillaId))
            ->when($receptorId, fn ($q) => $q->where('arq.receptor_usuario_id', $receptorId))
            ->when($tipo !== '', fn ($q) => $q->where('arq.tipo', $tipo))
            ->when($estado !== '', fn ($q) => $q->where('arq.estado', $estado))
            ->when($desde !== '', fn ($q) => $q->whereDate('arq.fecha_operativa', '>=', $desde))
            ->when($hasta !== '', fn ($q) => $q->whereDate('arq.fecha_operativa', '<=', $hasta))
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($s) use ($buscar) {
                    $s->where('arq.numero_arqueo', 'like', '%'.$buscar.'%')
                      ->orWhere('arq.agencia_nombre_historico', 'like', '%'.$buscar.'%')
                      ->orWhere('arq.agencia_codigo_historico', 'like', '%'.$buscar.'%')
                      ->orWhere('arq.receptor_nombre_historico', 'like', '%'.$buscar.'%')
                      ->orWhere('arq.ventanilla_codigo_historico', 'like', '%'.$buscar.'%');
                });
            });

        $arqueos = (clone $consulta)->select([
            'arq.id', 'arq.numero_arqueo', 'arq.fecha_operativa', 'arq.tipo', 'arq.estado',
            'arq.agencia_codigo_historico', 'arq.agencia_nombre_historico',
            'arq.ventanilla_codigo_historico', 'arq.receptor_nombre_historico',
            'arq.turno_nombre_historico', 'arq.total_arqueado', 'arq.total_corte',
            'arq.diferencia', 'arq.fuera_fecha_ordinaria',
        ])->orderByDesc('arq.fecha_operativa')->orderByDesc('arq.id')
          ->paginate(15)->withQueryString();

        $resumen = (clone $consulta)->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN estado = 'CERTIFICADO' THEN 1 ELSE 0 END) as certificados")
            ->selectRaw("SUM(CASE WHEN estado IN ('PENDIENTE_CERTIFICACION', 'PENDIENTE', 'PENDIENTE_RECTIFICACION') THEN 1 ELSE 0 END) as pendientes")
            ->selectRaw("SUM(CASE WHEN estado = 'ANULADO' THEN 1 ELSE 0 END) as anulados")
            ->selectRaw("SUM(CASE WHEN diferencia <> 0 THEN 1 ELSE 0 END) as con_diferencia")
            ->first();

        return view('administrador.arqueos-agencias.index', compact(
            'arqueos', 'resumen', 'agencias', 'ventanillas', 'receptores', 'estados',
            'agenciaId', 'ventanillaId', 'receptorId', 'tipo', 'estado', 'desde', 'hasta', 'buscar'
        ));
    }

    public function show(Request $request, int $arqueo): View
    {
        $this->validarAdministrador($request->user());
        $datos = $this->obtenerDetalle($arqueo);
        return view('administrador.arqueos-agencias.show', $datos);
    }

    public function imprimir(Request $request, int $arqueo): Response
    {
        $this->validarAdministrador($request->user());
        $datos = $this->obtenerDetalle($arqueo);
        $pdf = Pdf::loadView('administrador.arqueos-agencias.pdf', $datos)->setPaper('letter', 'portrait');
        $nombre = preg_replace('/[^A-Za-z0-9_-]/', '_', $datos['arqueo']->numero_arqueo);
        return $pdf->stream($nombre.'.pdf');
    }

    private function obtenerDetalle(int $id): array
    {
        $arqueo = DB::table('arqueos_agencia')->where('id', $id)->first();
        abort_if(!$arqueo, 404, 'No se encontró el arqueo de agencia.');

        $detalles = DB::table('arqueo_agencia_detalles')
            ->where('arqueo_agencia_id', $id)->orderByDesc('denominacion')->get();
        $billetes = $detalles->where('tipo', 'BILLETE')->values();
        $monedas = $detalles->where('tipo', 'MONEDA')->values();
        $otros = $detalles->where('tipo', 'OTRO')->values();
        $firmas = DB::table('firmas_arqueos_agencia')
            ->where('arqueo_agencia_id', $id)->orderBy('fecha_firma')->get();
        $anulacion = DB::table('anulaciones_arqueos_agencia')
            ->where('arqueo_agencia_id', $id)->first();
        $observaciones = DB::table('observaciones_arqueos_agencia')
            ->where('arqueo_agencia_id', $id)->orderByDesc('id')->get();

        return compact('arqueo', 'billetes', 'monedas', 'otros', 'firmas', 'anulacion', 'observaciones');
    }

    private function validarAdministrador(?Usuario $usuario): void
    {
        abort_unless($usuario, 403);
        $usuario->loadMissing('rol');
        abort_if(!$usuario->rol || $usuario->rol->nombre !== 'Administrador', 403,
            'No tiene autorización para acceder a esta sección.');
    }
}
