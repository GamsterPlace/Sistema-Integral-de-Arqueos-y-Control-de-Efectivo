<?php

namespace App\Http\Controllers\Auditoria;

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
        $this->validarAuditoria($usuario);

        $perfil = DB::table('usuarios as u')
            ->join('roles as rol', 'rol.id', '=', 'u.rol_id')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'u.id')
            ->where('u.id', $usuario->id)
            ->select(
                'u.id',
                'u.usuario',
                'u.estado',
                'rol.nombre as rol_nombre',
                'dp.nombres',
                'dp.apellidos'
            )
            ->first();

        abort_if(!$perfil, 404, 'No fue posible encontrar la información del perfil.');

        $base = DB::table('arqueos')
            ->where('tipo', 'VISITA_AUDITORIA')
            ->where('creado_por', $usuario->id);

        $resumen = (object) [
            'total' => (clone $base)->count(),
            'hoy' => (clone $base)->whereDate('fecha_arqueo', today())->where('estado', '!=', 'ANULADO')->count(),
            'certificados' => (clone $base)->where('estado', 'CERTIFICADO')->count(),
            'pendientes' => (clone $base)->where('estado', 'PENDIENTE_CERTIFICACION')->count(),
            'faltantes' => (clone $base)->where('estado', '!=', 'ANULADO')->where('diferencia', '<', 0)->count(),
            'sobrantes' => (clone $base)->where('estado', '!=', 'ANULADO')->where('diferencia', '>', 0)->count(),
        ];

        return view('auditoria.perfil.index', compact('perfil', 'resumen'));
    }

    private function validarAuditoria(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol || $usuario->rol->nombre !== 'Auditoria',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
