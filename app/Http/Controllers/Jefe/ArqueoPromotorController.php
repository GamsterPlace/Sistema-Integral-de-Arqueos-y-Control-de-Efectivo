<?php
namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ArqueoPromotorController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $promotorId = $request->integer('promotor_id');
        $buscar = trim((string) $request->string('buscar'));
        $estado = trim((string) $request->string('estado'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $promotores = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('rol.nombre', 'Promotor')
            ->orderBy('dp.nombres')
            ->orderBy('dp.apellidos')
            ->get([
                'u.id',
                'u.usuario',
                'u.estado',
                'dp.nombres',
                'dp.apellidos',
            ]);

        $arqueos = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->join('usuarios as up', 'up.id', '=', 'arq.creado_por')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'up.id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->where('arq.tipo', 'VISITA_PROMOTOR')
            ->when($promotorId > 0, fn ($q) => $q->where('arq.creado_por', $promotorId))
            ->when($buscar !== '', function ($q) use ($buscar): void {
                $q->where(function ($s) use ($buscar): void {
                    $s->where('arq.numero_arqueo', 'like', '%' . $buscar . '%')
                      ->orWhere('a.codigo_agente', 'like', '%' . $buscar . '%')
                      ->orWhere('a.nombre_negocio', 'like', '%' . $buscar . '%')
                      ->orWhere('up.usuario', 'like', '%' . $buscar . '%')
                      ->orWhere('dp.nombres', 'like', '%' . $buscar . '%')
                      ->orWhere('dp.apellidos', 'like', '%' . $buscar . '%');
                });
            })
            ->when($estado !== '', fn ($q) => $q->where('arq.estado', $estado))
            ->when($desde, fn ($q) => $q->whereDate('arq.fecha_arqueo', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('arq.fecha_arqueo', '<=', $hasta))
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.estado',
                'arq.fuera_fecha_ordinaria',
                'arq.total_arqueado',
                'arq.saldo_sistema',
                'arq.diferencia',
                'arq.creado_por as promotor_id',
                'a.id as agente_id',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'up.usuario as promotor_usuario',
                'dp.nombres as promotor_nombres',
                'dp.apellidos as promotor_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(15)
            ->withQueryString();

        $totalArqueos = DB::table('arqueos')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $arqueosHoy = DB::table('arqueos')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->whereDate('fecha_arqueo', today())
            ->where('estado', '!=', 'ANULADO')
            ->count();

        $pendientes = DB::table('arqueos')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', 'PENDIENTE_CERTIFICACION')
            ->count();

        $certificados = DB::table('arqueos')
            ->where('tipo', 'VISITA_PROMOTOR')
            ->where('estado', 'CERTIFICADO')
            ->count();

        return view('jefe.arqueos-promotores.index', compact(
            'promotores',
            'arqueos',
            'promotorId',
            'buscar',
            'estado',
            'desde',
            'hasta',
            'totalArqueos',
            'arqueosHoy',
            'pendientes',
            'certificados'
        ));
    }

    public function show(Request $request, Arqueo $arqueo): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        abort_if(
            $arqueo->tipo !== 'VISITA_PROMOTOR',
            404,
            'El arqueo solicitado no corresponde a un arqueo de Promotor.'
        );

        $arqueo->loadMissing(['detalles', 'firmas']);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        $promotor = DB::table('usuarios as u')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('u.id', $arqueo->creado_por)
            ->select([
                'u.id',
                'u.usuario',
                'u.estado',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();

        return view('jefe.arqueos-promotores.show', compact(
            'arqueo',
            'billetes',
            'monedas',
            'promotor'
        ));
    }

    public function imprimir(Request $request, Arqueo $arqueo): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        abort_if(
            $arqueo->tipo !== 'VISITA_PROMOTOR',
            404,
            'El arqueo solicitado no corresponde a un arqueo de Promotor.'
        );

        $arqueo->loadMissing(['detalles', 'firmas']);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        $pdf = Pdf::loadView(
            'jefe.arqueos-promotores.pdf',
            compact('arqueo', 'billetes', 'monedas')
        )->setPaper('letter', 'portrait');

        return $pdf->stream($arqueo->numero_arqueo . '.pdf');
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
