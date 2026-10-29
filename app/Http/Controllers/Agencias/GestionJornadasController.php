<?php

namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\Agencia;
use App\Models\AsignacionPersonalAgencia;
use App\Models\JornadaReceptor;
use App\Models\TurnoAgencia;
use App\Models\VentanillaAgencia;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GestionJornadasController extends Controller
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

    private function rutaBase(Request $request): string
    {
        return $this->cargo($request) === 'JEFE_CAJA' ? 'jefecaja' : 'subjefecaja';
    }

    private function agenciasAutorizadas(Request $request): array
    {
        return AsignacionPersonalAgencia::query()
            ->where('usuario_id', $request->user()->getKey())
            ->where('cargo', $this->cargo($request))
            ->where('estado', true)
            ->whereIn('tipo', ['PERMANENTE', 'TEMPORAL'])
            ->whereDate('fecha_inicio', '<=', today()->toDateString())
            ->where(function (Builder $query): void {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', today()->toDateString());
            })
            ->whereHas('agencia', fn (Builder $query) => $query->where('estado', true))
            ->pluck('agencia_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();
    }

    private function jornadasAutorizadas(Request $request): Builder
    {
        return JornadaReceptor::query()
            ->whereIn('agencia_id', $this->agenciasAutorizadas($request));
    }

    private function datosFormulario(Request $request): array
    {
        $agenciasIds = $this->agenciasAutorizadas($request);

        return [
            'agencias' => Agencia::query()->whereIn('id', $agenciasIds)->orderBy('id')->get(),
            'asignaciones' => AsignacionPersonalAgencia::query()
                ->with('usuario.datosPersonales', 'agencia')
                ->whereIn('agencia_id', $agenciasIds)
                ->where('cargo', 'RECEPTOR')
                ->where('estado', true)
                ->whereIn('tipo', ['PERMANENTE', 'TEMPORAL'])
                ->orderBy('agencia_id')->orderBy('usuario_id')->get(),
            'ventanillas' => VentanillaAgencia::query()
                ->whereIn('agencia_id', $agenciasIds)
                ->where('estado', true)
                ->orderBy('codigo')->get(),
            'turnos' => TurnoAgencia::query()->where('estado', true)->orderBy('hora_inicio')->get(),
            'rutaBase' => $this->rutaBase($request),
        ];
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'agencia_id' => ['nullable', 'integer'],
            'fecha' => ['nullable', 'date_format:Y-m-d'],
            'receptor_usuario_id' => ['nullable', 'integer'],
        ]);

        $query = $this->jornadasAutorizadas($request)
            ->with(['agencia', 'ventanilla', 'turno', 'receptor.datosPersonales', 'asignador']);

        if (isset($filtros['agencia_id'])) {
            $query->where('agencia_id', $filtros['agencia_id']);
        }
        if (isset($filtros['fecha'])) {
            $query->whereDate('fecha_operativa', $filtros['fecha']);
        }
        if (isset($filtros['receptor_usuario_id'])) {
            $query->where('receptor_usuario_id', $filtros['receptor_usuario_id']);
        }

        return view('agencias.jornadas.index', array_merge($this->datosFormulario($request), [
            'jornadas' => $query->orderByDesc('fecha_operativa')
                ->orderBy('inicio_programado')->paginate(20)->withQueryString(),
            'filtros' => $filtros,
        ]));
    }

    public function create(Request $request): View
    {
        return view('agencias.jornadas.create', $this->datosFormulario($request));
    }

    public function show(Request $request, int $jornada): View
    {
        $registro = $this->jornadasAutorizadas($request)
            ->with(['agencia', 'ventanilla', 'turno', 'receptor.datosPersonales',
                'asignacionPersonal', 'asignador', 'arqueos', 'arqueosEntrantes', 'habilitaciones'])
            ->findOrFail($jornada);

        return view('agencias.jornadas.show', [
            'jornada' => $registro,
            'rutaBase' => $this->rutaBase($request),
        ]);
    }

    public function edit(Request $request, int $jornada): View
    {
        $registro = $this->jornadasAutorizadas($request)->findOrFail($jornada);
        $this->verificarEditable($registro);

        return view('agencias.jornadas.edit', array_merge($this->datosFormulario($request), [
            'jornada' => $registro,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarEntrada($request);
        $id = DB::transaction(function () use ($request, $datos): int {
            $valores = $this->prepararJornada($request, $datos);
            $valores['asignado_por'] = $request->user()->getKey();
            $valores['estado'] = 'PROGRAMADA';
            return (int) JornadaReceptor::query()->create($valores)->getKey();
        }, 3);

        return redirect()->route($this->rutaBase($request).'.jornadas.show', $id)
            ->with('success', 'Jornada programada correctamente.');
    }

    public function update(Request $request, int $jornada): RedirectResponse
    {
        $datos = $this->validarEntrada($request);
        DB::transaction(function () use ($request, $datos, $jornada): void {
            $registro = $this->jornadasAutorizadas($request)->lockForUpdate()->findOrFail($jornada);
            $this->verificarEditable($registro);
            $valores = $this->prepararJornada($request, $datos, $registro->getKey());
            $valores['asignado_por'] = $request->user()->getKey();
            $registro->update($valores);
        }, 3);

        return redirect()->route($this->rutaBase($request).'.jornadas.show', $jornada)
            ->with('success', 'Jornada actualizada correctamente.');
    }

    private function validarEntrada(Request $request): array
    {
        return $request->validate([
            'agencia_id' => ['required', 'integer'],
            'asignacion_personal_id' => ['required', 'integer'],
            'ventanilla_id' => ['required', 'integer'],
            'turno_id' => ['required', 'integer'],
            'fecha_operativa' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);
    }

    private function verificarEditable(JornadaReceptor $jornada): void
    {
        if ($jornada->fecha_operativa->startOfDay()->lt(today())
            || $jornada->estado !== 'PROGRAMADA'
            || $jornada->inicio_real !== null || $jornada->fin_real !== null
            || $jornada->arqueos()->exists() || $jornada->arqueosEntrantes()->exists()
            || $jornada->habilitaciones()->exists()) {
            throw ValidationException::withMessages([
                'jornada' => 'Esta jornada ya no puede modificarse porque tiene actividad o registros asociados.',
            ]);
        }
    }

    private function prepararJornada(Request $request, array $datos, ?int $exceptoId = null): array
    {
        $agenciaId = (int) $datos['agencia_id'];
        if (!in_array($agenciaId, $this->agenciasAutorizadas($request), true)) {
            abort(403, 'No está autorizado para administrar esta agencia.');
        }

        // Bloqueo por agencia: serializa asignaciones concurrentes hechas por este módulo.
        Agencia::query()->whereKey($agenciaId)->lockForUpdate()->firstOrFail();

        $fecha = Carbon::createFromFormat('!Y-m-d', $datos['fecha_operativa']);
        $asignacion = AsignacionPersonalAgencia::query()
            ->whereKey($datos['asignacion_personal_id'])
            ->where('agencia_id', $agenciaId)
            ->where('cargo', 'RECEPTOR')
            ->where('estado', true)
            ->whereIn('tipo', ['PERMANENTE', 'TEMPORAL'])
            ->whereDate('fecha_inicio', '<=', $fecha->toDateString())
            ->where(function (Builder $query) use ($fecha): void {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $fecha->toDateString());
            })->first();

        if (!$asignacion) {
            throw ValidationException::withMessages([
                'asignacion_personal_id' => 'El receptor no tiene una asignación vigente en esa agencia para la fecha indicada.',
            ]);
        }

        $ventanilla = VentanillaAgencia::query()->whereKey($datos['ventanilla_id'])
            ->where('agencia_id', $agenciaId)->where('estado', true)->first();
        if (!$ventanilla) {
            throw ValidationException::withMessages([
                'ventanilla_id' => 'La ventanilla no está activa o no pertenece a la agencia seleccionada.',
            ]);
        }

        $turno = TurnoAgencia::query()->whereKey($datos['turno_id'])
            ->where('estado', true)->first();
        if (!$turno) {
            throw ValidationException::withMessages(['turno_id' => 'El turno seleccionado no está activo.']);
        }

        $inicio = Carbon::parse($fecha->toDateString().' '.substr((string) $turno->hora_inicio, 0, 8));
        $fin = Carbon::parse($fecha->toDateString().' '.substr((string) $turno->hora_fin, 0, 8));
        if ($turno->cruza_medianoche) {
            $fin->addDay();
        }
        if ($fin->lessThanOrEqualTo($inicio)) {
            throw ValidationException::withMessages([
                'turno_id' => 'El horario del turno es inválido. Revise sus horas y la configuración de cruce de medianoche.',
            ]);
        }

        $conflictos = JornadaReceptor::query()
            ->where('inicio_programado', '<', $fin->toDateTimeString())
            ->where('fin_programado', '>', $inicio->toDateTimeString())
            ->where(function (Builder $query) use ($asignacion, $agenciaId, $ventanilla): void {
                $query->where('receptor_usuario_id', $asignacion->usuario_id)
                    ->orWhere(function (Builder $subquery) use ($agenciaId, $ventanilla): void {
                        $subquery->where('agencia_id', $agenciaId)
                            ->where('ventanilla_id', $ventanilla->getKey());
                    });
            });
        if ($exceptoId !== null) {
            $conflictos->whereKeyNot($exceptoId);
        }
        // No se presupone un catálogo de estados de cancelación; cualquier jornada ocupa su horario.
        if ($conflictos->exists()) {
            throw ValidationException::withMessages([
                'turno_id' => 'Existe una jornada con horario superpuesto para este receptor o esta ventanilla.',
            ]);
        }

        return [
            'asignacion_personal_id' => $asignacion->getKey(),
            'receptor_usuario_id' => $asignacion->usuario_id,
            'agencia_id' => $agenciaId,
            'ventanilla_id' => $ventanilla->getKey(),
            'turno_id' => $turno->getKey(),
            'fecha_operativa' => $fecha->toDateString(),
            'inicio_programado' => $inicio->toDateTimeString(),
            'fin_programado' => $fin->toDateTimeString(),
        ];
    }
}
