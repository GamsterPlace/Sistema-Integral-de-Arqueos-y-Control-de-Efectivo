<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $this->validarJefe($usuario);

        $perfil = DB::table('usuarios as u')
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
            ->where('u.id', $usuario->id)
            ->select([
                'u.id',
                'u.usuario',
                'u.estado',
                'rol.nombre as rol_nombre',
                'dp.nombres',
                'dp.apellidos',
            ])
            ->first();

        abort_if(
            ! $perfil,
            404,
            'No fue posible encontrar la información del perfil.'
        );

        $resumen = (object) [
            'total_agentes' =>
                DB::table('agentes')
                    ->count(),

            'agentes_activos' =>
                DB::table('agentes')
                    ->where(
                        'estado',
                        'ACTIVO'
                    )
                    ->count(),

            'rutas_activas' =>
                DB::table('rutas')
                    ->where(
                        'estado',
                        true
                    )
                    ->count(),

            'regiones_activas' =>
                DB::table('regiones')
                    ->where(
                        'estado',
                        true
                    )
                    ->count(),

            'arqueos_hoy' =>
                DB::table('arqueos')
                    ->whereDate(
                        'fecha_arqueo',
                        today()
                    )
                    ->where(
                        'estado',
                        '!=',
                        'ANULADO'
                    )
                    ->count(),

            'pendientes_certificacion' =>
                DB::table('arqueos')
                    ->where(
                        'estado',
                        'PENDIENTE_CERTIFICACION'
                    )
                    ->count(),
        ];

        return view(
            'jefe.perfil.index',
            [
                'perfil' => $perfil,
                'resumen' => $resumen,
            ]
        );
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
