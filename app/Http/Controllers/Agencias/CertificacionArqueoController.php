<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\ArqueoAgencia;
use App\Models\AsignacionPersonalAgencia;
use App\Services\Agencias\FirmaArqueoService;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CertificacionArqueoController extends Controller
{
    private function cargo(Request $request): string
    {
        $request->user()->loadMissing('rol');
        return match ($request->user()->rol?->nombre) {
            'JefeCaja' => 'JEFE_CAJA',
            'SubjefeCaja' => 'SUBJEFE_CAJA',
            default => abort(403),
        };
    }

    private function agenciasAutorizadas(Request $request): array
    {
        return AsignacionPersonalAgencia::query()
            ->where('usuario_id', $request->user()->id)
            ->where('cargo', $this->cargo($request))
            ->where('estado', true)
            ->whereIn('tipo', ['PERMANENTE', 'TEMPORAL'])
            ->whereDate('fecha_inicio', '<=', today()->toDateString())
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', today()->toDateString()))
            ->whereHas('agencia', fn ($q) => $q->where('estado', true))
            ->pluck('agencia_id')->map(fn ($id) => (int) $id)->unique()->all();
    }

    private function rutaBase(Request $request): string
    {
        return $this->cargo($request) === 'JEFE_CAJA' ? 'jefecaja' : 'subjefecaja';
    }

    public function index(Request $request): View
    {
        $agencias = $this->agenciasAutorizadas($request);
        $arqueos = ArqueoAgencia::query()
            ->with(['agencia', 'ventanilla', 'receptor'])
            ->whereIn('agencia_id', $agencias)
            ->where('tipo', 'ORDINARIO')
            ->whereIn('estado', ['PENDIENTE', 'CERTIFICADO'])
            ->orderByRaw("CASE WHEN estado = 'PENDIENTE' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->paginate(20);
        return view('agencias.certificaciones.index', [
            'arqueos' => $arqueos,
            'rutaBase' => $this->rutaBase($request),
        ]);
    }

    public function show(Request $request, int $arqueo, FirmaArqueoService $firmas): View
    {
        $registro = ArqueoAgencia::query()
            ->with(['detalles', 'firmas', 'agencia', 'ventanilla'])
            ->whereIn('agencia_id', $this->agenciasAutorizadas($request))
            ->where('tipo', 'ORDINARIO')
            ->findOrFail($arqueo);
        $firmaReceptor = $registro->firmas->firstWhere('tipo_firma', 'RECEPTOR');
        $firmaCertificador = $registro->firmas->firstWhere('tipo_firma', 'CERTIFICADOR');
        return view('agencias.certificaciones.show', [
            'arqueo' => $registro,
            'firmaReceptorValida' => $firmaReceptor !== null && $firmas->verificar($registro, $firmaReceptor),
            'firmaCertificadorValida' => $firmaCertificador !== null && $firmas->verificar($registro, $firmaCertificador),
            'rutaBase' => $this->rutaBase($request),
        ]);
    }

    public function store(Request $request, int $arqueo, FirmaArqueoService $firmas, AuditoriaService $auditoria)
    {
        $request->validate(['confirmar_certificacion' => ['accepted']]);
        $agencias = $this->agenciasAutorizadas($request);
        $cargo = $this->cargo($request);
        $ruta = $this->rutaBase($request);
        DB::transaction(function () use ($request, $arqueo, $firmas, $auditoria, $agencias, $cargo) {
            $registro = ArqueoAgencia::query()->whereIn('agencia_id', $agencias)
                ->where('tipo', 'ORDINARIO')->lockForUpdate()->findOrFail($arqueo);
            if ($registro->estado !== 'PENDIENTE' || $registro->certificado_at !== null || $registro->anulado_at !== null) {
                throw ValidationException::withMessages(['arqueo' => 'El arqueo ya no está pendiente de certificación.']);
            }
            $registro->load(['firmas', 'detalles']);
            $firmaReceptor = $registro->firmas->firstWhere('tipo_firma', 'RECEPTOR');
            if (!$firmaReceptor || (int) $firmaReceptor->usuario_id !== (int) $registro->receptor_usuario_id ||
                !$firmas->verificar($registro, $firmaReceptor) || !$registro->firmado_receptor_at) {
                throw ValidationException::withMessages(['arqueo' => 'La firma del receptor no es válida o el arqueo fue modificado.']);
            }
            if ($registro->firmas->contains('tipo_firma', 'CERTIFICADOR')) {
                throw ValidationException::withMessages(['arqueo' => 'Este arqueo ya tiene una firma de certificación.']);
            }
            if (abs((float) $registro->diferencia) > 0.00001 && trim((string) $registro->justificacion_diferencia) === '') {
                throw ValidationException::withMessages(['arqueo' => 'El arqueo presenta una diferencia sin justificación.']);
            }
            $firmas->firmarCertificador($registro, $request->user());
            $registro->estado = 'CERTIFICADO';
            $registro->certificado_at = now();
            if ($cargo === 'JEFE_CAJA') {
                $registro->jefe_caja_responsable_id = $request->user()->id;
            } else {
                $registro->subjefe_caja_responsable_id = $request->user()->id;
            }
            $registro->save();
            $auditoria->registrar($request->user(), 'ARQUEOS_AGENCIA', 'CERTIFICAR_ARQUEO_ORDINARIO',
                'arqueos_agencia', $registro->id, 'Certificación del arqueo ordinario por responsable de caja.',
                ['estado' => 'PENDIENTE'], ['estado' => 'CERTIFICADO', 'numero_arqueo' => $registro->numero_arqueo,
                    'diferencia' => $registro->diferencia, 'certificador_id' => $request->user()->id]);
        }, 3);
        return redirect()->route($ruta.'.certificaciones.show', $arqueo)
            ->with('success', 'Arqueo certificado correctamente.');
    }
}
