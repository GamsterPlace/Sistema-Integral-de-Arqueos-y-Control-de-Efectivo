<?php
namespace App\Http\Controllers\Agencias;

use App\Http\Controllers\Controller;
use App\Models\Agencia;
use App\Models\AsignacionOperativaReceptor;
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
        return $this->cargo($request) === 'JEFE_CAJA'
            ? 'jefecaja'
            : 'subjefecaja';
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
                    ->orWhereDate(
                        'fecha_fin',
                        '>=',
                        today()->toDateString()
                    );
            })
            ->whereHas(
                'agencia',
                fn (Builder $query) => $query->where('estado', true)
            )
            ->pluck('agencia_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();
    }

    private function asignacionesAutorizadas(Request $request): Builder
    {
        return AsignacionOperativaReceptor::query()
            ->whereIn(
                'agencia_id',
                $this->agenciasAutorizadas($request)
            );
    }

    private function datosFormulario(Request $request): array
    {
        $agenciasIds = $this->agenciasAutorizadas($request);

        return [
            'agencias' => Agencia::query()
                ->whereIn('id', $agenciasIds)
                ->where('estado', true)
                ->orderBy('id')
                ->get(),

            'asignaciones' => AsignacionPersonalAgencia::query()
                ->with(['usuario.datosPersonales', 'agencia'])
                ->whereIn('agencia_id', $agenciasIds)
                ->where('cargo', 'RECEPTOR')
                ->where('estado', true)
                ->whereIn('tipo', ['PERMANENTE', 'TEMPORAL'])
                ->orderBy('agencia_id')
                ->orderBy('usuario_id')
                ->get(),

            'ventanillas' => VentanillaAgencia::query()
                ->whereIn('agencia_id', $agenciasIds)
                ->where('estado', true)
                ->orderBy('codigo')
                ->get(),

            'turnos' => TurnoAgencia::query()
                ->where('estado', true)
                ->orderBy('hora_inicio')
                ->get(),

            'rutaBase' => $this->rutaBase($request),
        ];
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'agencia_id' => ['nullable', 'integer'],
            'receptor_usuario_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'in:VIGENTE,FUTURA,FINALIZADA'],
        ]);

        $query = $this->asignacionesAutorizadas($request)
            ->with([
                'agencia',
                'ventanilla',
                'turno',
                'receptor.datosPersonales',
                'asignacionPersonal',
                'asignador',
            ]);

        if (isset($filtros['agencia_id'])) {
            $query->where(
                'agencia_id',
                $filtros['agencia_id']
            );
        }

        if (isset($filtros['receptor_usuario_id'])) {
            $query->where(
                'receptor_usuario_id',
                $filtros['receptor_usuario_id']
            );
        }

        $hoy = today()->toDateString();

        if (($filtros['estado'] ?? null) === 'VIGENTE') {
            $query->vigentes();
        } elseif (($filtros['estado'] ?? null) === 'FUTURA') {
            $query->where('estado', true)
                ->whereDate('fecha_inicio', '>', $hoy);
        } elseif (($filtros['estado'] ?? null) === 'FINALIZADA') {
            $query->where(function (Builder $q) use ($hoy): void {
                $q->where('estado', false)
                    ->orWhereDate('fecha_fin', '<', $hoy);
            });
        }

        return view(
            'agencias.jornadas.index',
            array_merge($this->datosFormulario($request), [
                'asignacionesOperativas' => $query
                    ->orderByDesc('fecha_inicio')
                    ->orderByDesc('id')
                    ->paginate(20)
                    ->withQueryString(),

                'filtros' => $filtros,
            ])
        );
    }

    public function create(Request $request): View
    {
        return view(
            'agencias.jornadas.create',
            $this->datosFormulario($request)
        );
    }

    public function show(Request $request, int $jornada): View
    {
        $registro = $this->asignacionesAutorizadas($request)
            ->with([
                'agencia',
                'ventanilla',
                'turno',
                'receptor.datosPersonales',
                'asignacionPersonal',
                'asignador',
            ])
            ->findOrFail($jornada);

        $jornadas = JornadaReceptor::query()
            ->with(['arqueos', 'arqueosEntrantes'])
            ->where(
                'asignacion_personal_id',
                $registro->asignacion_personal_id
            )
            ->where(
                'receptor_usuario_id',
                $registro->receptor_usuario_id
            )
            ->where('agencia_id', $registro->agencia_id)
            ->where('ventanilla_id', $registro->ventanilla_id)
            ->where('turno_id', $registro->turno_id)
            ->whereDate(
                'fecha_operativa',
                '>=',
                $registro->fecha_inicio->toDateString()
            )
            ->when(
                $registro->fecha_fin,
                fn (Builder $query) => $query->whereDate(
                    'fecha_operativa',
                    '<=',
                    $registro->fecha_fin->toDateString()
                )
            )
            ->orderByDesc('fecha_operativa')
            ->paginate(15);

        return view('agencias.jornadas.show', [
            'asignacionOperativa' => $registro,
            'jornadas' => $jornadas,
            'rutaBase' => $this->rutaBase($request),
        ]);
    }

    public function edit(Request $request, int $jornada): View
    {
        $registro = $this->asignacionesAutorizadas($request)
            ->findOrFail($jornada);

        if (!$registro->estaVigente()) {
            throw ValidationException::withMessages([
                'asignacion' => 'Solamente pueden modificarse asignaciones vigentes.',
            ]);
        }

        return view(
            'agencias.jornadas.edit',
            array_merge($this->datosFormulario($request), [
                'asignacionOperativa' => $registro,
            ])
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarEntrada($request);

        $registro = DB::transaction(function () use ($request, $datos) {
            $valores = $this->prepararAsignacion(
                $request,
                $datos
            );

            $valores['asignado_por'] = $request->user()->getKey();
            $valores['estado'] = true;
            $valores['fecha_fin'] = null;

            return AsignacionOperativaReceptor::query()
                ->create($valores);
        }, 3);

        return redirect()
            ->route(
                $this->rutaBase($request) . '.jornadas.show',
                $registro->getKey()
            )
            ->with(
                'success',
                'Asignación operativa registrada correctamente. Permanecerá vigente hasta que sea modificada.'
            );
    }

    public function update(
        Request $request,
        int $jornada
    ): RedirectResponse {
        $datos = $this->validarEntrada($request, true);

        $nuevoRegistro = DB::transaction(function () use (
            $request,
            $datos,
            $jornada
        ) {
            $actual = $this->asignacionesAutorizadas($request)
                ->lockForUpdate()
                ->findOrFail($jornada);

            if (!$actual->estaVigente()) {
                throw ValidationException::withMessages([
                    'asignacion' => 'La asignación ya no está vigente.',
                ]);
            }

            $hoy = today()->toDateString();

            /*
             * No se modifica el registro histórico.
             * La asignación anterior se finaliza y
             * se crea una nueva.
             */
            $valores = $this->prepararAsignacion(
                $request,
                $datos,
                $actual->getKey()
            );

            /*
             * Los cambios de receptor, ventanilla o
             * turno no pueden alterar una jornada
             * que ya tenga actividad registrada hoy.
             */
            $actividadHoy = JornadaReceptor::query()
                ->where('receptor_usuario_id', $actual->receptor_usuario_id)
                ->where('agencia_id', $actual->agencia_id)
                ->where('ventanilla_id', $actual->ventanilla_id)
                ->where('turno_id', $actual->turno_id)
                ->whereDate('fecha_operativa', $hoy)
                ->where(function (Builder $query): void {
                    $query->whereNotNull('inicio_real')
                        ->orWhereNotNull('fin_real')
                        ->orWhereHas('arqueos')
                        ->orWhereHas('arqueosEntrantes')
                        ->orWhereHas('habilitaciones');
                })
                ->exists();

            if ($actividadHoy) {
                throw ValidationException::withMessages([
                    'asignacion' => 'La jornada actual ya tiene actividad. Debe completarse el procedimiento de entrega antes de cambiar la asignación.',
                ]);
            }

            $actual->update([
                'fecha_fin' => today()->copy()->subDay()->toDateString(),
                'estado' => false,
                'motivo_cambio' => $datos['motivo_cambio'],
            ]);

            $valores['asignado_por'] = $request->user()->getKey();
            $valores['estado'] = true;
            $valores['fecha_fin'] = null;

            return AsignacionOperativaReceptor::query()
                ->create($valores);
        }, 3);

        return redirect()
            ->route(
                $this->rutaBase($request) . '.jornadas.show',
                $nuevoRegistro->getKey()
            )
            ->with(
                'success',
                'Asignación modificada correctamente. Se conservó el historial anterior.'
            );
    }

    private function validarEntrada(
        Request $request,
        bool $actualizacion = false
    ): array {
        $reglas = [
            'agencia_id' => ['required', 'integer'],
            'asignacion_personal_id' => ['required', 'integer'],
            'ventanilla_id' => ['required', 'integer'],
            'turno_id' => ['required', 'integer'],
            'fecha_inicio' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
        ];

        if ($actualizacion) {
            $reglas['motivo_cambio'] = [
                'required',
                'string',
                'min:10',
                'max:500',
            ];
        }

        return $request->validate($reglas);
    }

    private function prepararAsignacion(
        Request $request,
        array $datos,
        ?int $exceptoId = null
    ): array {
        $agenciaId = (int) $datos['agencia_id'];

        if (!in_array(
            $agenciaId,
            $this->agenciasAutorizadas($request),
            true
        )) {
            abort(403, 'No está autorizado para administrar esta agencia.');
        }

        /*
         * Serializa las operaciones sobre la agencia.
         */
        Agencia::query()
            ->whereKey($agenciaId)
            ->where('estado', true)
            ->lockForUpdate()
            ->firstOrFail();

        $fecha = Carbon::createFromFormat(
            '!Y-m-d',
            $datos['fecha_inicio']
        );

        $personal = AsignacionPersonalAgencia::query()
            ->whereKey($datos['asignacion_personal_id'])
            ->where('agencia_id', $agenciaId)
            ->where('cargo', 'RECEPTOR')
            ->where('estado', true)
            ->whereIn('tipo', ['PERMANENTE', 'TEMPORAL'])
            ->whereDate(
                'fecha_inicio',
                '<=',
                $fecha->toDateString()
            )
            ->where(function (Builder $query) use ($fecha): void {
                $query->whereNull('fecha_fin')
                    ->orWhereDate(
                        'fecha_fin',
                        '>=',
                        $fecha->toDateString()
                    );
            })
            ->first();

        if (!$personal) {
            throw ValidationException::withMessages([
                'asignacion_personal_id' => 'El receptor no tiene una asignación de personal vigente en esta agencia.',
            ]);
        }

        $ventanilla = VentanillaAgencia::query()
            ->whereKey($datos['ventanilla_id'])
            ->where('agencia_id', $agenciaId)
            ->where('estado', true)
            ->first();

        if (!$ventanilla) {
            throw ValidationException::withMessages([
                'ventanilla_id' => 'La ventanilla no está activa o no pertenece a esta agencia.',
            ]);
        }

        $turno = TurnoAgencia::query()
            ->whereKey($datos['turno_id'])
            ->where('estado', true)
            ->first();

        if (!$turno) {
            throw ValidationException::withMessages([
                'turno_id' => 'El turno seleccionado no está activo.',
            ]);
        }

        $inicio = Carbon::parse(
            $fecha->toDateString() . ' ' .
            substr((string) $turno->hora_inicio, 0, 8)
        );

        $fin = Carbon::parse(
            $fecha->toDateString() . ' ' .
            substr((string) $turno->hora_fin, 0, 8)
        );

        if ($turno->cruza_medianoche) {
            $fin->addDay();
        }

        if ($fin->lessThanOrEqualTo($inicio)) {
            throw ValidationException::withMessages([
                'turno_id' => 'El horario del turno es inválido.',
            ]);
        }

        /*
         * Comprobar todas las asignaciones con
         * vigencia que alcance la nueva fecha.
         */
        $otras = AsignacionOperativaReceptor::query()
            ->where('estado', true)
            ->where(function (Builder $query) use (
                $personal,
                $agenciaId,
                $ventanilla
            ): void {
                $query->where(
                    'receptor_usuario_id',
                    $personal->usuario_id
                )->orWhere(function (Builder $subquery) use (
                    $agenciaId,
                    $ventanilla
                ): void {
                    $subquery->where('agencia_id', $agenciaId)
                        ->where('ventanilla_id', $ventanilla->getKey());
                });
            })
            ->where(function (Builder $query) use ($fecha): void {
                $query->whereNull('fecha_fin')
                    ->orWhereDate(
                        'fecha_fin',
                        '>=',
                        $fecha->toDateString()
                    );
            })
            ->with('turno');

        if ($exceptoId !== null) {
            $otras->whereKeyNot($exceptoId);
        }

        foreach ($otras->get() as $otra) {
            if (!$otra->turno) {
                continue;
            }

            $otroInicio = Carbon::parse(
                $fecha->toDateString() . ' ' .
                substr((string) $otra->turno->hora_inicio, 0, 8)
            );

            $otroFin = Carbon::parse(
                $fecha->toDateString() . ' ' .
                substr((string) $otra->turno->hora_fin, 0, 8)
            );

            if ($otra->turno->cruza_medianoche) {
                $otroFin->addDay();
            }

            if (
                $inicio->lessThan($otroFin)
                && $fin->greaterThan($otroInicio)
            ) {
                throw ValidationException::withMessages([
                    'turno_id' => 'Existe una asignación vigente con horario superpuesto para este receptor o ventanilla.',
                ]);
            }
        }

        return [
            'asignacion_personal_id' => $personal->getKey(),
            'receptor_usuario_id' => $personal->usuario_id,
            'agencia_id' => $agenciaId,
            'ventanilla_id' => $ventanilla->getKey(),
            'turno_id' => $turno->getKey(),
            'fecha_inicio' => $fecha->toDateString(),
        ];
    }
}
