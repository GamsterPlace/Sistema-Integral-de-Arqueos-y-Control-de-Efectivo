<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AgenciaController extends Controller
{
    private function autorizar(Request $request): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        abort_unless($usuario, 401);
        $usuario->loadMissing('rol');
        abort_unless($usuario->rol && mb_strtolower(trim($usuario->rol->nombre)) === 'administrador', 403);
        return $usuario;
    }

    public function index(Request $request): View
    {
        $this->autorizar($request);
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = (string) $request->input('estado', '');
        $agencias = DB::table('agencias as a')
            ->select('a.*')
            ->selectSub(
                DB::table('ventanillas_agencia as v')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('v.agencia_id', 'a.id'),
                'total_ventanillas'
            )
            ->selectSub(
                DB::table('ventanillas_agencia as v')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('v.agencia_id', 'a.id')
                    ->where('v.estado', true),
                'ventanillas_activas'
            )
            ->when($buscar !== '', fn ($q) => $q->where(fn ($s) =>
                $s->where('a.codigo', 'like', "%{$buscar}%")
                  ->orWhere('a.nombre', 'like', "%{$buscar}%")
                  ->orWhere('a.direccion', 'like', "%{$buscar}%")
            ))
            ->when(in_array($estado, ['1', '0'], true),
                fn ($q) => $q->where('a.estado', $estado === '1'))
            ->orderBy('a.nombre')
            ->paginate(15)->withQueryString();

        $resumen = (object) [
            'total' => DB::table('agencias')->count(),
            'activas' => DB::table('agencias')->where('estado', true)->count(),
            'inactivas' => DB::table('agencias')->where('estado', false)->count(),
            'ventanillas' => DB::table('ventanillas_agencia')->count(),
        ];
        return view('administrador.agencias.index', compact('agencias', 'resumen', 'buscar', 'estado'));
    }

    public function create(Request $request): View
    {
        $this->autorizar($request);
        return view('administrador.agencias.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $admin = $this->autorizar($request);
        $data = $request->validate($this->reglas());
        $this->validarCodigosVentanilla($data['ventanillas'] ?? []);

        $id = DB::transaction(function () use ($data, $admin): int {
            $id = DB::table('agencias')->insertGetId([
                'codigo' => trim($data['codigo']),
                'nombre' => trim($data['nombre']),
                'direccion' => $data['direccion'] ?? null,
                'estado' => (bool) $data['estado'],
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->insertarVentanillas($id, $data['ventanillas'] ?? []);
            $this->auditar($admin, 'CREAR_AGENCIA', $id, 'Se creó la agencia y sus ventanillas.', null,
                ['agencia' => $data, 'agencia_id' => $id]);
            return $id;
        });

        return redirect()->route('administrador.agencias.show', $id)
            ->with('success', 'Agencia y ventanillas registradas correctamente.');
    }

    public function show(Request $request, int $agencia): View
    {
        $this->autorizar($request);
        $registro = $this->buscar($agencia);
        $ventanillas = DB::table('ventanillas_agencia')
            ->where('agencia_id', $agencia)->orderBy('codigo')->get();
        $resumen = (object) [
            'ventanillas' => $ventanillas->count(),
            'ventanillas_activas' => $ventanillas->where('estado', 1)->count(),
            'personal' => DB::table('asignaciones_personal_agencia')
                ->where('agencia_id', $agencia)->where('estado', true)->count(),
            'arqueos' => DB::table('arqueos_agencia')->where('agencia_id', $agencia)->count(),
        ];
        return view('administrador.agencias.show', [
            'agencia' => $registro, 'ventanillas' => $ventanillas, 'resumen' => $resumen,
        ]);
    }

    public function edit(Request $request, int $agencia): View
    {
        $this->autorizar($request);
        $registro = $this->buscar($agencia);
        $ventanillas = DB::table('ventanillas_agencia')
            ->where('agencia_id', $agencia)->orderBy('codigo')->get();
        return view('administrador.agencias.edit', [
            'agencia' => $registro, 'ventanillas' => $ventanillas,
        ]);
    }

    public function update(Request $request, int $agencia): RedirectResponse
    {
        $admin = $this->autorizar($request);
        $registro = $this->buscar($agencia);
        $data = $request->validate($this->reglas($agencia));
        $this->validarCodigosVentanilla($data['ventanillas'] ?? [], $agencia);
        $this->validarVentanillasExistentes($data['existentes'] ?? [], $agencia);

        DB::transaction(function () use ($data, $admin, $registro, $agencia): void {
            if ((int) $registro->estado === 1 && (int) $data['estado'] === 0) {
                $this->verificarDesactivacion($agencia);
            }
            DB::table('agencias')->where('id', $agencia)->update([
                'codigo' => trim($data['codigo']),
                'nombre' => trim($data['nombre']),
                'direccion' => $data['direccion'] ?? null,
                'estado' => (bool) $data['estado'],
                'updated_at' => now(),
            ]);
            foreach (($data['existentes'] ?? []) as $id => $ventanilla) {
                $actual = DB::table('ventanillas_agencia')->where('agencia_id', $agencia)
                    ->where('id', $id)->first();
                if ($actual && (int) $actual->estado === 1 && (int) $ventanilla['estado'] === 0) {
                    $this->verificarDesactivacionVentanilla((int) $id);
                }
                DB::table('ventanillas_agencia')->where('agencia_id', $agencia)->where('id', $id)
                    ->update([
                        'descripcion' => $ventanilla['descripcion'] ?? null,
                        'estado' => (bool) $ventanilla['estado'],
                        'updated_at' => now(),
                    ]);
            }
            $this->insertarVentanillas($agencia, $data['ventanillas'] ?? []);
            $this->auditar($admin, 'EDITAR_AGENCIA', $agencia,
                'Se actualizaron los datos y ventanillas de la agencia.',
                (array) $registro, $data);
        });
        return redirect()->route('administrador.agencias.show', $agencia)
            ->with('success', 'Agencia actualizada correctamente.');
    }

    public function cambiarEstado(Request $request, int $agencia): RedirectResponse
    {
        $admin = $this->autorizar($request);
        $registro = $this->buscar($agencia);
        $nuevo = ! (bool) $registro->estado;
        DB::transaction(function () use ($admin, $agencia, $registro, $nuevo): void {
            if (! $nuevo) {
                $this->verificarDesactivacion($agencia);
            }
            DB::table('agencias')->where('id', $agencia)
                ->update(['estado' => $nuevo, 'updated_at' => now()]);
            $this->auditar($admin, $nuevo ? 'ACTIVAR_AGENCIA' : 'DESACTIVAR_AGENCIA',
                $agencia, 'Se cambió el estado de la agencia.',
                ['estado' => (bool) $registro->estado], ['estado' => $nuevo]);
        });
        return back()->with('success', 'Estado de agencia actualizado.');
    }

    private function reglas(?int $agencia = null): array
    {
        return [
            'codigo' => ['required', 'string', 'max:30',
                Rule::unique('agencias', 'codigo')->ignore($agencia)],
            'nombre' => ['required', 'string', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['0', '1'])],
            'ventanillas' => [$agencia === null ? 'required' : 'sometimes', 'array', ...($agencia === null ? ['min:1'] : [])],
            'ventanillas.*.codigo' => ['required', 'string', 'max:30'],
            'ventanillas.*.descripcion' => ['nullable', 'string', 'max:150'],
            'ventanillas.*.estado' => ['required', Rule::in(['0', '1'])],
            'existentes' => ['sometimes', 'array'],
            'existentes.*.descripcion' => ['nullable', 'string', 'max:150'],
            'existentes.*.estado' => ['required', Rule::in(['0', '1'])],
        ];
    }

    private function validarCodigosVentanilla(array $ventanillas, ?int $agencia = null): void
    {
        $codigos = [];
        foreach ($ventanillas as $indice => $v) {
            $codigo = mb_strtoupper(trim($v['codigo']));
            if (in_array($codigo, $codigos, true)) {
                throw ValidationException::withMessages([
                    "ventanillas.{$indice}.codigo" => 'El código de ventanilla está repetido en el formulario.',
                ]);
            }
            $codigos[] = $codigo;
            if ($agencia && DB::table('ventanillas_agencia')->where('agencia_id', $agencia)
                ->whereRaw('UPPER(codigo) = ?', [$codigo])->exists()) {
                throw ValidationException::withMessages([
                    "ventanillas.{$indice}.codigo" => 'Ya existe una ventanilla con este código en la agencia.',
                ]);
            }
        }
    }

    private function validarVentanillasExistentes(array $existentes, int $agencia): void
    {
        foreach (array_keys($existentes) as $id) {
            if (! ctype_digit((string) $id) ||
                ! DB::table('ventanillas_agencia')->where('id', $id)
                    ->where('agencia_id', $agencia)->exists()) {
                throw ValidationException::withMessages([
                    'existentes' => 'Se recibió una ventanilla que no pertenece a esta agencia.',
                ]);
            }
        }
    }

    private function insertarVentanillas(int $agencia, array $ventanillas): void
    {
        foreach ($ventanillas as $v) {
            DB::table('ventanillas_agencia')->insert([
                'agencia_id' => $agencia,
                'codigo' => trim($v['codigo']),
                'descripcion' => $v['descripcion'] ?? null,
                'estado' => (bool) $v['estado'],
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function verificarDesactivacion(int $agencia): void
    {
        if (DB::table('asignaciones_personal_agencia')->where('agencia_id', $agencia)
            ->where('estado', true)->exists()) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede desactivar una agencia con asignaciones de personal activas.',
            ]);
        }
    }

    private function verificarDesactivacionVentanilla(int $ventanilla): void
    {
        if (DB::table('jornadas_receptor')->where('ventanilla_id', $ventanilla)
            ->whereNull('fin_real')->exists()) {
            throw ValidationException::withMessages([
                'existentes' => 'No se puede desactivar una ventanilla con jornadas sin finalizar.',
            ]);
        }
    }

    private function buscar(int $id): object
    {
        $registro = DB::table('agencias')->where('id', $id)->first();
        abort_unless($registro, 404, 'La agencia solicitada no existe.');
        return $registro;
    }

    private function auditar(Usuario $admin, string $accion, int $id, string $descripcion,
        ?array $anteriores, array $nuevos): void
    {
        app(AuditoriaService::class)->registrar(
            usuario: $admin, modulo: 'Agencias', accion: $accion,
            tablaAfectada: 'agencias', registroId: $id,
            descripcion: $descripcion,
            valoresAnteriores: $anteriores, valoresNuevos: $nuevos
        );
    }
}
