<?php

namespace App\Http\Controllers\Administrador;

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
        $this->validarAdministrador($usuario);

        $perfil = DB::table('usuarios as u')
            ->join('roles as rol','rol.id','=','u.rol_id')
            ->leftJoin('datos_personales as dp','dp.usuario_id','=','u.id')
            ->where('u.id',$usuario->id)
            ->select([
                'u.id','u.usuario','u.estado',
                'rol.nombre as rol_nombre',
                'dp.nombres','dp.apellidos'
            ])
            ->first();

        abort_if(!$perfil,404,'No fue posible encontrar la información del perfil.');

        $resumen = (object)[
            'usuarios' => DB::table('usuarios')->count(),
            'agentes' => DB::table('agentes')->count(),
            'arqueos' => DB::table('arqueos')->count(),
            'anulados' => DB::table('arqueos')->where('estado','ANULADO')->count(),
            'rutas' => DB::table('rutas')->where('estado',true)->count(),
            'regiones' => DB::table('regiones')->where('estado',true)->count(),
        ];

        return view('administrador.perfil.index',compact('perfil','resumen'));
    }

    private function validarAdministrador(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');
        abort_if(!$usuario->rol || $usuario->rol->nombre !== 'Administrador',403,'No tiene autorización para acceder a esta sección.');
    }
}
