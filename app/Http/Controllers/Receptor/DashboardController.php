<?php

namespace App\Http\Controllers\Receptor;

use App\Http\Controllers\Controller;
use App\Services\Agencias\AccesoAgenciaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, AccesoAgenciaService $acceso): View
    {
        $usuario = $request->user();
        $asignaciones = $acceso->asignacionesVigentes($usuario);

        return view('agencias.dashboard', [
            'titulo' => 'Receptor',
            'seccion' => 'Mi jornada y arqueos',
            'descripcion' => 'Las operaciones de jornada y arqueo se habilitarán en la siguiente etapa.',
            'asignaciones' => $asignaciones,
        ]);
    }
}
