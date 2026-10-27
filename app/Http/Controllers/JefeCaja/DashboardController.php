<?php

namespace App\Http\Controllers\JefeCaja;

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
            'titulo' => 'Jefe de Caja',
            'seccion' => 'Supervisión de caja',
            'descripcion' => 'Las certificaciones, asignaciones de turnos y arqueos sorpresa se implementarán en la siguiente etapa.',
            'asignaciones' => $asignaciones,
        ]);
    }
}
