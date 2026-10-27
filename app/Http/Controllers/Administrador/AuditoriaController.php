<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarAdministrador($usuario);

        $buscar = trim((string)$request->string('buscar'));

        $registros = collect();
        $tablaDisponible = false;

        if (Schema::hasTable('auditoria')) {
            $tablaDisponible = true;

            $query = DB::table('auditoria');

            if ($buscar !== '') {
                $columnas = Schema::getColumnListing('auditoria');

                $query->where(function($q) use ($buscar,$columnas) {
                    foreach (['accion','descripcion','tabla','ip','modulo'] as $columna) {
                        if (in_array($columna,$columnas,true)) {
                            $q->orWhere($columna,'like','%'.$buscar.'%');
                        }
                    }
                });
            }

            $columnas = Schema::getColumnListing('auditoria');

            $order = in_array('created_at',$columnas,true)
                ? 'created_at'
                : (in_array('id',$columnas,true) ? 'id' : null);

            if ($order) {
                $query->orderByDesc($order);
            }

            $registros = $query->paginate(25)->withQueryString();
        }

        return view('administrador.auditoria.index',compact(
            'registros','tablaDisponible','buscar'
        ));
    }

    private function validarAdministrador(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');
        abort_if(!$usuario->rol || $usuario->rol->nombre !== 'Administrador',403,'No tiene autorización para acceder a esta sección.');
    }
}
