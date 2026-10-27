<?php

namespace App\Http\Controllers\JefeAgencia;

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
            'titulo' => 'Jefe de Agencia',
            'seccion' => 'Consulta de agencia',
            'descripcion' => 'Este panel será de consulta; no permitirá modificar arqueos ni certificaciones.',
            'asignaciones' => $asignaciones,
        ]);
    }
}
