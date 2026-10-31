<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\Agencia;
use App\Models\AnulacionArqueoAgencia;
use App\Models\ArqueoAgencia;
use App\Models\AsignacionPersonalAgencia;
use App\Models\Usuario;
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
            ->filter(fn ($asignacion) => (bool) $asignacion->agencia?->estado)
            ->pluck('agencia_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        return [$rol, $agencias, $rol === 'JefeCaja' ? 'jefecaja' : 'subjefecaja'];
    }

    public function index(Request $request, AccesoAgenciaService $acceso): View
    {
        [$rol, $agencias, $ruta] = $this->contexto($request, $acceso);

        $asignaciones = AsignacionPersonalAgencia::query()
            ->whereIn('agencia_id', $agencias)
            ->where('cargo', 'RECEPTOR')
            ->where('estado', true)
            ->whereDate('fecha_inicio', '<=', today()->toDateString())
            ->where(function ($query) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', today()->toDateString());
            })
            ->get()
            ->unique(fn ($a) => $a->agencia_id . '-' . $a->usuario_id)
            ->values();

        $usuarios = Usuario::query()
            ->with('datosPersonales')
            ->whereIn('id', $asignaciones->pluck('usuario_id')->unique())
            ->get()
            ->keyBy('id');

        $nombresAgencias = Agencia::query()
            ->whereIn('id', $agencias)
            ->pluck('nombre', 'id');

        // La ficha muestra tres arqueos, pero el historial no se limita a tres.
        $receptores = $asignaciones->map(function ($asignacion) use ($usuarios, $nombresAgencias, $agencias) {
            $usuario = $usuarios->get($asignacion->usuario_id);
            if (!$usuario) {
                return null;
            }

            $base = ArqueoAgencia::query()
                ->whereIn('agencia_id', $agencias)
                ->where('agencia_id', $asignacion->agencia_id)
                ->where('receptor_usuario_id', $asignacion->usuario_id)
                ->where('tipo', 'ORDINARIO')
                ->whereIn('estado', ['PENDIENTE', 'CERTIFICADO', 'ANULADO']);

            return (object) [
                'id' => (int) $asignacion->usuario_id,
                'agencia_id' => (int) $asignacion->agencia_id,
                'nombre' => trim($usuario->nombre_completo) ?: $usuario->usuario,
                'agencia_nombre' => $nombresAgencias->get($asignacion->agencia_id) ?: 'Agencia no disponible',
                'total_arqueos' => (clone $base)->count(),
                'arqueos' => (clone $base)
                    ->orderByDesc('fecha_operativa')
                    ->orderByDesc('id')
                    ->limit(3)
                    ->get(),
            ];
        })->filter()->sortBy('nombre')->values();

        // Historial completo: misma ruta, sin registrar una ruta adicional.
        $historial = null;
        $receptorSeleccionado = null;
        $receptorId = $request->integer('receptor');
        $agenciaId = $request->integer('agencia');

        if ($request->filled('receptor') || $request->filled('agencia')) {
            abort_unless($receptorId > 0 && $agenciaId > 0, 404);
            $receptorSeleccionado = $receptores->first(
                fn ($r) => $r->id === $receptorId && $r->agencia_id === $agenciaId
            );
            abort_unless($receptorSeleccionado !== null, 404);

            $historial = ArqueoAgencia::query()
                ->whereIn('agencia_id', $agencias)
                ->where('agencia_id', $agenciaId)
                ->where('receptor_usuario_id', $receptorId)
                ->where('tipo', 'ORDINARIO')
                ->whereIn('estado', ['PENDIENTE', 'CERTIFICADO', 'ANULADO'])
                ->orderByDesc('fecha_operativa')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString();
        }

        return view('agencias.anulaciones.index', compact(
            'receptores', 'historial', 'receptorSeleccionado', 'ruta', 'rol'
        ));
    }

    public function show(
        Request $request,
        int $arqueo,
        AccesoAgenciaService $acceso,
        FirmaArqueoService $firmas
    ): View {
        [$rol, $agencias, $ruta] = $this->contexto(
            $request,
            $acceso
        );

        $registro = ArqueoAgencia::query()
            ->whereIn('agencia_id', $agencias)
            ->where('tipo', 'ORDINARIO')
            ->with([
                'detalles',
                'firmas',
                'anulacion',
            ])
            ->findOrFail($arqueo);

        $verificaciones = [];

        foreach ($registro->firmas as $firma) {
            $verificaciones[$firma->tipo_firma] =
                $firmas->verificar($registro, $firma);
        }

        $puedeAnular = $this->puedeAnular($registro);

        return view(
            'agencias.anulaciones.show',
            compact(
                'registro',
                'verificaciones',
                'puedeAnular',
                'ruta',
                'rol'
            )
        );
    }

    /**
     * Jefe de Caja y Subjefe de Caja tienen la misma autorización:
     * anular arqueos ordinarios pendientes o certificados
     * de sus agencias autorizadas.
     */
    private function puedeAnular(ArqueoAgencia $registro): bool
    {
        return $registro->tipo === 'ORDINARIO'
            && $registro->anulado_at === null
            && $registro->anulacion === null
            && in_array(
                $registro->estado,
                ['PENDIENTE', 'CERTIFICADO'],
                true
            );
    }

    public function store(
        Request $request,
        int $arqueo,
        AccesoAgenciaService $acceso,
        FirmaArqueoService $firmas,
        AuditoriaService $auditoria
    ): RedirectResponse {
        $datos = $request->validate([
            'motivo' => [
                'required',
                'string',
                'min:10',
                'max:500',
            ],
            'confirmar_anulacion' => ['accepted'],
        ]);

        [, $agencias, $ruta] = $this->contexto(
            $request,
            $acceso
        );

        DB::transaction(function () use (
            $request,
            $arqueo,
            $firmas,
            $auditoria,
            $agencias,
            $datos
        ): void {
            $registro = ArqueoAgencia::query()
                ->whereIn('agencia_id', $agencias)
                ->where('tipo', 'ORDINARIO')
                ->lockForUpdate()
                ->findOrFail($arqueo);

            $registro->load([
                'detalles',
                'firmas',
                'anulacion',
            ]);

            if (!$this->puedeAnular($registro)) {
                throw ValidationException::withMessages([
                    'arqueo' =>
                        'El arqueo no puede anularse en su estado actual o ya fue anulado.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Verificar firma del receptor
            |--------------------------------------------------------------------------
            */

            $firmaReceptor = $registro->firmas
                ->firstWhere('tipo_firma', 'RECEPTOR');

            if (
                !$firmaReceptor ||
                !$firmas->verificar($registro, $firmaReceptor)
            ) {
                throw ValidationException::withMessages([
                    'arqueo' => 'La firma del receptor no es válida.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Verificar firma del certificador
            |--------------------------------------------------------------------------
            */

            if ($registro->estado === 'CERTIFICADO') {
                $certificador = $registro->firmas
                    ->firstWhere('tipo_firma', 'CERTIFICADOR');

                if (
                    !$certificador ||
                    !$firmas->verificar($registro, $certificador)
                ) {
                    throw ValidationException::withMessages([
                        'arqueo' => 'La firma de certificación no es válida.',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Firma electrónica de anulación
            |--------------------------------------------------------------------------
            */

            $estadoAnterior = $registro->estado;

            $firmaAnulador = $firmas->firmarAnulador(
                $registro,
                $request->user()
            );

            /*
            |--------------------------------------------------------------------------
            | Registro de anulación
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Registro de auditoría
            |--------------------------------------------------------------------------
            */

            $auditoria->registrar(
                $request->user(),
                'ARQUEOS_AGENCIA',
                'ANULAR_ARQUEO_ORDINARIO',
                'arqueos_agencia',
                $registro->id,
                'Anulación irreversible del arqueo ordinario.',
                [
                    'estado' => $estadoAnterior,
                ],
                [
                    'estado' => 'ANULADO',
                    'numero_arqueo' => $registro->numero_arqueo,
                    'motivo' => trim($datos['motivo']),
                    'firma_anulador_id' => $firmaAnulador->id,
                ]
            );
        }, 3);

        return redirect()
            ->route($ruta . '.anulaciones.show', $arqueo)
            ->with(
                'success',
                'Arqueo anulado; se conservan los datos económicos y las firmas originales.'
            );
    }
}
