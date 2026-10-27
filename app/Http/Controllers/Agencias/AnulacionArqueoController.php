<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\AnulacionArqueoAgencia;
use App\Models\ArqueoAgencia;
use App\Services\Agencias\AccesoAgenciaService;
use App\Services\Agencias\FirmaArqueoService;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnulacionArqueoController extends Controller
{
    private function contexto(Request $request, AccesoAgenciaService $acceso): array
    {
        $usuario = $request->user();
        $usuario->loadMissing('rol');
        $rol = $usuario->rol?->nombre;
        abort_unless(in_array($rol, ['JefeCaja', 'SubjefeCaja'], true), 403);
        $agencias = $acceso->asignacionesVigentes($usuario)
            ->filter(fn ($a) => (bool) $a->agencia?->estado)
            ->pluck('agencia_id')->map(fn ($id) => (int) $id)->unique()->all();
        return [$rol, $agencias, $rol === 'JefeCaja' ? 'jefecaja' : 'subjefecaja'];
    }

    public function index(Request $request, AccesoAgenciaService $acceso): View
    {
        [$rol, $agencias, $ruta] = $this->contexto($request, $acceso);
        $arqueos = ArqueoAgencia::query()->whereIn('agencia_id', $agencias)
            ->where('tipo', 'ORDINARIO')->whereIn('estado', ['PENDIENTE', 'CERTIFICADO', 'ANULADO'])
            ->with(['anulacion'])->orderByDesc('id')->paginate(20);
        return view('agencias.anulaciones.index', compact('arqueos', 'ruta', 'rol'));
    }

    public function show(Request $request, int $arqueo, AccesoAgenciaService $acceso, FirmaArqueoService $firmas): View
    {
        [$rol, $agencias, $ruta] = $this->contexto($request, $acceso);
        $registro = ArqueoAgencia::query()->whereIn('agencia_id', $agencias)
            ->where('tipo', 'ORDINARIO')->with(['detalles', 'firmas', 'anulacion'])->findOrFail($arqueo);
        $verificaciones = [];
        foreach ($registro->firmas as $firma) {
            $verificaciones[$firma->tipo_firma] = $firmas->verificar($registro, $firma);
        }
        $puedeAnular = $this->puedeAnular($registro, $rol);
        return view('agencias.anulaciones.show', compact('registro', 'verificaciones', 'puedeAnular', 'ruta', 'rol'));
    }

    private function puedeAnular(ArqueoAgencia $registro, string $rol): bool
    {
        return $registro->anulado_at === null && $registro->anulacion === null
            && ($registro->estado === 'PENDIENTE' || ($registro->estado === 'CERTIFICADO' && $rol === 'JefeCaja'));
    }

    public function store(Request $request, int $arqueo, AccesoAgenciaService $acceso, FirmaArqueoService $firmas, AuditoriaService $auditoria): RedirectResponse
    {
        $datos = $request->validate([
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
            'confirmar_anulacion' => ['accepted'],
        ]);
        [$rol, $agencias, $ruta] = $this->contexto($request, $acceso);
        DB::transaction(function () use ($request, $arqueo, $firmas, $auditoria, $rol, $agencias, $datos) {
            $registro = ArqueoAgencia::query()->whereIn('agencia_id', $agencias)
                ->where('tipo', 'ORDINARIO')->lockForUpdate()->findOrFail($arqueo);
            $registro->load(['detalles', 'firmas', 'anulacion']);
            if (!$this->puedeAnular($registro, $rol)) {
                throw ValidationException::withMessages(['arqueo' => 'El arqueo no puede anularse en su estado actual o ya fue anulado.']);
            }
            $firmaReceptor = $registro->firmas->firstWhere('tipo_firma', 'RECEPTOR');
            if (!$firmaReceptor || !$firmas->verificar($registro, $firmaReceptor)) {
                throw ValidationException::withMessages(['arqueo' => 'La firma del receptor no es válida.']);
            }
            if ($registro->estado === 'CERTIFICADO') {
                $certificador = $registro->firmas->firstWhere('tipo_firma', 'CERTIFICADOR');
                if (!$certificador || !$firmas->verificar($registro, $certificador)) {
                    throw ValidationException::withMessages(['arqueo' => 'La firma de certificación no es válida.']);
                }
            }
            $estadoAnterior = $registro->estado;
            $firmaAnulador = $firmas->firmarAnulador($registro, $request->user());
            AnulacionArqueoAgencia::query()->create([
                'arqueo_agencia_id' => $registro->id,
                'anulado_por' => $request->user()->id,
                'firma_id' => $firmaAnulador->id,
                'motivo' => trim($datos['motivo']),
                'fecha_anulacion' => now(),
            ]);
            $registro->estado = 'ANULADO';
            $registro->anulado_at = now();
            $registro->save();
            $auditoria->registrar($request->user(), 'ARQUEOS_AGENCIA', 'ANULAR_ARQUEO_ORDINARIO',
                'arqueos_agencia', $registro->id, 'Anulación irreversible del arqueo ordinario.',
                ['estado' => $estadoAnterior], ['estado' => 'ANULADO', 'numero_arqueo' => $registro->numero_arqueo,
                    'motivo' => trim($datos['motivo']), 'firma_anulador_id' => $firmaAnulador->id]);
        }, 3);
        return redirect()->route($ruta.'.anulaciones.show', $arqueo)->with('success', 'Arqueo anulado; se conservan los datos económicos y las firmas originales.');
    }
}
