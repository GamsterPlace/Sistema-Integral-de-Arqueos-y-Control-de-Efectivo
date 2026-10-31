<?php

namespace App\Http\Controllers\JefeAgencia;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\Usuario;
use App\Models\AsignacionPersonalAgencia;
use App\Services\Agencias\FirmaArqueoService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Agencias\AccesoAgenciaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ConsultaArqueosController extends Controller
{
    public function historial(Request $request, AccesoAgenciaService $acceso): View
    {
        return $this->listar($request, $acceso, 'TODOS');
    }

    public function jefatura(Request $request, AccesoAgenciaService $acceso): View
    {
        return $this->listar($request, $acceso, 'SORPRESIVO');
    }

    public function auditoria(Request $request, AccesoAgenciaService $acceso): View
    {
        return $this->listar($request, $acceso, 'AUDITORIA');
    }

    private function usuario(Request $request)
    {
        $usuario = $request->user();
        abort_unless($usuario, 401);
        $usuario->loadMissing('rol');
        abort_unless($usuario->estado === 'ACTIVO'
            && $usuario->rol?->nombre === 'JefeAgencia'
            && (bool) $usuario->rol?->estado, 403);
        return $usuario;
    }

    /** Consulta histórica autorizada: restringe cada arqueo al período de asignación. */
    private function consultaAutorizada(Request $request)
    {
        $usuario = $this->usuario($request);
        $asignaciones = AsignacionPersonalAgencia::query()
            ->with('agencia')
            ->where('usuario_id', $usuario->getKey())
            ->where('cargo', 'JEFE_AGENCIA')
            ->where('estado', true)
            ->whereIn('tipo', ['PERMANENTE', 'TEMPORAL'])
            ->get();

        $consulta = ArqueoAgencia::query()->where(function ($query) use ($asignaciones) {
            foreach ($asignaciones as $asignacion) {
                $query->orWhere(function ($sub) use ($asignacion) {
                    $sub->where('agencia_id', $asignacion->agencia_id)
                        ->whereDate('fecha_operativa', '>=', $asignacion->fecha_inicio->toDateString());
                    if ($asignacion->fecha_fin) {
                        $sub->whereDate('fecha_operativa', '<=', $asignacion->fecha_fin->toDateString());
                    }
                });
            }
            if ($asignaciones->isEmpty()) {
                $query->whereRaw('1 = 0');
            }
        });

        return [$consulta, $asignaciones];
    }

    private function listar(Request $request, AccesoAgenciaService $acceso, string $tipo): View
    {
        [$query, $asignaciones] = $this->consultaAutorizada($request);
        $agencias = $asignaciones->pluck('agencia')->filter(fn ($a) => $a && $a->estado)
            ->unique('id')->sortBy('nombre')->values();
        $filtros = $request->validate([
            'agencia_id' => ['nullable', 'integer', 'min:1'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'estado' => ['nullable', 'in:PENDIENTE,CERTIFICADO,ANULADO'],
            'numero_arqueo' => ['nullable', 'string', 'max:100'],
        ]);
        if (isset($filtros['agencia_id'])) {
            abort_unless($agencias->contains('id', (int) $filtros['agencia_id']), 403);
            $query->where('agencia_id', $filtros['agencia_id']);
        }
        if ($tipo !== 'TODOS') $query->where('tipo', $tipo);
        if (!empty($filtros['desde'])) $query->whereDate('fecha_operativa', '>=', $filtros['desde']);
        if (!empty($filtros['hasta'])) $query->whereDate('fecha_operativa', '<=', $filtros['hasta']);
        if (!empty($filtros['estado'])) $query->where('estado', $filtros['estado']);
        if (!empty($filtros['numero_arqueo'])) $query->where('numero_arqueo', 'like', '%'.$filtros['numero_arqueo'].'%');

        $titulo = match ($tipo) {
            'SORPRESIVO' => 'Arqueos de Jefatura de Caja',
            'AUDITORIA' => 'Arqueos de Auditoría',
            default => 'Historial de Arqueos',
        };
        if ($tipo !== 'TODOS') {
            $arqueos = $query->with(['agencia', 'ventanilla', 'receptor'])
                ->orderByDesc('fecha_operativa')->orderByDesc('id')->paginate(20)->withQueryString();
            return view('jefe-agencia.consulta-arqueos.index', compact('arqueos', 'agencias', 'filtros', 'titulo', 'tipo'));
        }

        // Las fichas se construyen a partir de las asignaciones de receptores,
        // no de los arqueos. Así también aparecen quienes aún no han arqueado.
        $agenciaIds = $agencias->pluck('id')->all();
        $hoy = now()->toDateString();
        $receptorIds = AsignacionPersonalAgencia::query()
            ->whereIn('agencia_id', $agenciaIds)
            ->where('cargo', 'RECEPTOR')
            ->where('estado', true)
            ->whereIn('tipo', ['PERMANENTE', 'TEMPORAL'])
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->select('usuario_id');

        $receptores = Usuario::query()
            ->with('datosPersonales')
            ->whereIn('id', $receptorIds)
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        $ids = $receptores->getCollection()->pluck('id')->all();
        $ultimos = (clone $query)
            ->with('agencia')
            ->whereIn('receptor_usuario_id', $ids)
            ->whereNotNull('firmado_receptor_at')
            ->whereNull('anulado_at')
            ->orderByDesc('fecha_operativa')
            ->orderByDesc('id')
            ->get()
            ->groupBy('receptor_usuario_id')
            ->map(fn ($items) => $items->take(3));

        return view('jefe-agencia.consulta-arqueos.historial', compact('receptores', 'ultimos', 'titulo'));
    }

    public function receptor(Request $request, AccesoAgenciaService $acceso, int $receptor): View
    {
        [$query] = $this->consultaAutorizada($request);
        $usuarioReceptor = Usuario::query()->with('datosPersonales')->findOrFail($receptor);
        $arqueos = $query->where('receptor_usuario_id', $receptor)
            ->with(['agencia', 'ventanilla'])
            ->orderByDesc('fecha_operativa')->orderByDesc('id')
            ->paginate(20);
        // El receptor puede tener una ficha válida sin arqueos todavía.
        // Verificar asignación actual a una agencia autorizada para evitar
        // que la URL permita consultar usuarios ajenos.
        if ($arqueos->total() === 0) {
            [, $asignaciones] = $this->consultaAutorizada($request);
            $agenciaIds = $asignaciones->pluck('agencia_id')->unique()->all();
            $hoy = now()->toDateString();
            $autorizado = AsignacionPersonalAgencia::query()
                ->where('usuario_id', $receptor)
                ->whereIn('agencia_id', $agenciaIds)
                ->where('cargo', 'RECEPTOR')
                ->where('estado', true)
                ->whereDate('fecha_inicio', '<=', $hoy)
                ->where(function ($q) use ($hoy) {
                    $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $hoy);
                })
                ->exists();
            abort_unless($autorizado, 404);
        }
        return view('jefe-agencia.consulta-arqueos.receptor', compact('arqueos', 'usuarioReceptor'));
    }

    public function show(Request $request, AccesoAgenciaService $acceso, int $arqueo): View
    {
        $usuario = $this->usuario($request);
        [$query] = $this->consultaAutorizada($request);
        $registro = $query->with([
            'agencia', 'ventanilla', 'receptor', 'receptorEntrante', 'jornada.turno',
            'detalles', 'firmas', 'anulacion', 'observacionesAuditoria',
        ])->findOrFail($arqueo);
        return view('jefe-agencia.consulta-arqueos.show', ['arqueo' => $registro]);
    }
    public function imprimir(Request $request, AccesoAgenciaService $acceso, FirmaArqueoService $firmas, int $arqueo)
    {
        [$query] = $this->consultaAutorizada($request);
        $registro = $query->with(['agencia', 'ventanilla', 'receptor', 'detalles', 'firmas'])
            ->findOrFail($arqueo);
        $logoData = null;
        $logoPath = public_path('images/logos/ecosaba.png');
        if (is_file($logoPath) && is_readable($logoPath)) {
            $logoData = 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath));
        }
        $verificaciones = [];
        foreach (['RECEPTOR', 'CERTIFICADOR'] as $tipoFirma) {
            $firma = $registro->firmas->firstWhere('tipo_firma', $tipoFirma);
            $verificaciones[$tipoFirma] = $firma !== null && $firmas->verificar($registro, $firma);
        }
        $nombre = preg_replace('/[^a-zA-Z0-9_-]/', '-', (string) $registro->numero_arqueo);
        $vista = $registro->tipo === 'ORDINARIO'
            ? 'receptor.arqueos.imprimir'
            : 'jefe-agencia.consulta-arqueos.imprimir';
        return Pdf::loadView($vista, [
            'registro' => $registro,
            'logoData' => $logoData,
            'verificaciones' => $verificaciones,
            'billetes' => [200, 100, 50, 20, 10, 5, 2, 1],
            'monedas' => ['1.00', '0.50', '0.25', '0.10', '0.05'],
        ])->setPaper('letter', 'portrait')->stream('arqueo-'.($nombre ?: $registro->id).'.pdf');
    }
}
