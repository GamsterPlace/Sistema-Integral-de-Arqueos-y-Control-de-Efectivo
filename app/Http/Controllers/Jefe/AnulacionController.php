<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnulacionController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $buscar = trim((string) $request->string('buscar'));
        $estado = trim((string) $request->string('estado'));

        $arqueos = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->leftJoin('usuarios as uc', 'uc.id', '=', 'arq.creado_por')
            ->leftJoin(
                'datos_personales as dp',
                'dp.usuario_id',
                '=',
                'uc.id'
            )
            ->whereIn(
                'arq.estado',
                [
                    'PENDIENTE_CERTIFICACION',
                    'CERTIFICADO',
                ]
            )
            ->when(
                $estado !== '',
                fn ($query) => $query->where(
                    'arq.estado',
                    $estado
                )
            )
            ->when(
                $buscar !== '',
                function ($query) use ($buscar): void {
                    $query->where(function ($subquery) use ($buscar): void {
                        $subquery
                            ->where(
                                'arq.numero_arqueo',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
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
                                'uc.usuario',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'dp.nombres',
                                'like',
                                '%' . $buscar . '%'
                            )
                            ->orWhere(
                                'dp.apellidos',
                                'like',
                                '%' . $buscar . '%'
                            );
                    });
                }
            )
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.tipo',
                'arq.estado',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'uc.usuario as creador_usuario',
                'dp.nombres as creador_nombres',
                'dp.apellidos as creador_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(15)
            ->withQueryString();

        return view('jefe.anulaciones.index', [
            'arqueos' => $arqueos,
            'buscar' => $buscar,
            'estado' => $estado,
        ]);
    }

    public function show(Request $request, int $arqueo): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $registro = DB::table('arqueos as arq')
            ->where('arq.id', $arqueo)
            ->whereIn(
                'arq.estado',
                [
                    'PENDIENTE_CERTIFICACION',
                    'CERTIFICADO',
                ]
            )
            ->first();

        abort_if(
            ! $registro,
            404,
            'El arqueo no existe o ya no está disponible para anulación.'
        );

        $detalles = DB::table('arqueo_detalles')
            ->where('arqueo_id', $registro->id)
            ->orderByDesc('denominacion')
            ->get();

        $billetes = $detalles
            ->where('tipo', 'BILLETE')
            ->values();

        $monedas = $detalles
            ->where('tipo', 'MONEDA')
            ->values();

        return view('jefe.anulaciones.show', [
            'arqueo' => $registro,
            'billetes' => $billetes,
            'monedas' => $monedas,
        ]);
    }

    private function validarJefe(
        Usuario $usuario
    ): void {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol
            || $usuario->rol->nombre !== 'jefedeAgentes',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
