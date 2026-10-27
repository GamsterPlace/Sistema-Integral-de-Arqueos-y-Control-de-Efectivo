<?php

namespace App\Http\Controllers\Jefe;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\FirmaArqueo;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CertificacionController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $buscar = trim((string) $request->string('buscar'));

        $arqueos = DB::table('arqueos as arq')
            ->join('agentes as a', 'a.id', '=', 'arq.agente_id')
            ->join('usuarios as up', 'up.id', '=', 'arq.creado_por')
            ->leftJoin('datos_personales as dp', 'dp.usuario_id', '=', 'up.id')
            ->leftJoin('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->leftJoin('regiones as reg', 'reg.id', '=', 'r.region_id')
            ->whereIn('arq.tipo', ['VISITA_PROMOTOR', 'VISITA_AUDITORIA'])
            ->where('arq.estado', 'PENDIENTE_CERTIFICACION')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('firmas_arqueos as fr')
                    ->whereColumn('fr.arqueo_id', 'arq.id')
                    ->where('fr.tipo_firma', 'REALIZADOR')
                    ->where('fr.valida', true);
            })
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('firmas_arqueos as fv')
                    ->whereColumn('fv.arqueo_id', 'arq.id')
                    ->where('fv.tipo_firma', 'VALIDADOR')
                    ->where('fv.valida', true);
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('firmas_arqueos as fc')
                    ->whereColumn('fc.arqueo_id', 'arq.id')
                    ->where('fc.tipo_firma', 'CERTIFICADOR')
                    ->where('fc.valida', true);
            })
            ->when($buscar !== '', function ($query) use ($buscar): void {
                $query->where(function ($subquery) use ($buscar): void {
                    $subquery
                        ->where('arq.numero_arqueo', 'like', '%' . $buscar . '%')
                        ->orWhere('a.codigo_agente', 'like', '%' . $buscar . '%')
                        ->orWhere('a.nombre_negocio', 'like', '%' . $buscar . '%')
                        ->orWhere('up.usuario', 'like', '%' . $buscar . '%')
                        ->orWhere('dp.nombres', 'like', '%' . $buscar . '%')
                        ->orWhere('dp.apellidos', 'like', '%' . $buscar . '%');
                });
            })
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.fecha_arqueo',
                'arq.estado',
                'arq.tipo',
                'a.codigo_agente',
                'a.nombre_negocio',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
                'up.usuario as promotor_usuario',
                'dp.nombres as promotor_nombres',
                'dp.apellidos as promotor_apellidos',
            ])
            ->orderByDesc('arq.fecha_arqueo')
            ->orderByDesc('arq.id')
            ->paginate(15)
            ->withQueryString();

        $totalPendientes = DB::table('arqueos as arq')
            ->whereIn('arq.tipo', ['VISITA_PROMOTOR', 'VISITA_AUDITORIA'])
            ->where('arq.estado', 'PENDIENTE_CERTIFICACION')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('firmas_arqueos as fr')
                    ->whereColumn('fr.arqueo_id', 'arq.id')
                    ->where('fr.tipo_firma', 'REALIZADOR')
                    ->where('fr.valida', true);
            })
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('firmas_arqueos as fv')
                    ->whereColumn('fv.arqueo_id', 'arq.id')
                    ->where('fv.tipo_firma', 'VALIDADOR')
                    ->where('fv.valida', true);
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('firmas_arqueos as fc')
                    ->whereColumn('fc.arqueo_id', 'arq.id')
                    ->where('fc.tipo_firma', 'CERTIFICADOR')
                    ->where('fc.valida', true);
            })
            ->count();

        return view('jefe.certificaciones.index', compact(
            'arqueos',
            'buscar',
            'totalPendientes'
        ));
    }

    public function show(Request $request, Arqueo $arqueo): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        abort_if(
            ! in_array($arqueo->tipo, ['VISITA_PROMOTOR', 'VISITA_AUDITORIA'], true),
            404,
            'Este arqueo no corresponde a una visita certificable.'
        );

        $arqueo->loadMissing(['detalles', 'firmas']);

        $billetes = $arqueo->detalles
            ->where('tipo', 'BILLETE')
            ->sortByDesc('denominacion')
            ->values();

        $monedas = $arqueo->detalles
            ->where('tipo', 'MONEDA')
            ->sortByDesc('denominacion')
            ->values();

        $firmaRealizador = $arqueo->firmas
            ->first(fn ($firma) =>
                $firma->tipo_firma === 'REALIZADOR' && (bool) $firma->valida
            );

        $firmaAgente = $arqueo->firmas
            ->first(fn ($firma) =>
                $firma->tipo_firma === 'VALIDADOR' && (bool) $firma->valida
            );

        $firmaJefe = $arqueo->firmas
            ->first(fn ($firma) =>
                $firma->tipo_firma === 'CERTIFICADOR' && (bool) $firma->valida
            );

        $puedeCertificar =
            $arqueo->estado === 'PENDIENTE_CERTIFICACION'
            && $firmaRealizador !== null
            && $firmaAgente !== null
            && $firmaJefe === null;

        return view('jefe.certificaciones.show', compact(
            'arqueo',
            'billetes',
            'monedas',
            'firmaRealizador',
            'firmaAgente',
            'firmaJefe',
            'puedeCertificar'
        ));
    }

    public function certificar(
        Request $request,
        Arqueo $arqueo
    ): RedirectResponse {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $this->validarJefe($usuario);

        $datos = $request->validate(
            ['password' => ['required', 'string']],
            ['password.required' =>
                'Debe ingresar su contraseña para confirmar la certificación.']
        );

        if (! Hash::check($datos['password'], $usuario->password)) {
            return back()->withErrors([
                'password' => 'La contraseña ingresada es incorrecta.',
            ]);
        }

        return DB::transaction(function () use ($arqueo, $usuario): RedirectResponse {
            $arqueoBloqueado = Arqueo::query()
                ->lockForUpdate()
                ->findOrFail($arqueo->id);

            abort_if(
                ! in_array($arqueoBloqueado->tipo, ['VISITA_PROMOTOR', 'VISITA_AUDITORIA'], true),
                422,
                'Este arqueo no corresponde a un arqueo certificable.'
            );

            abort_if(
                $arqueoBloqueado->estado !== 'PENDIENTE_CERTIFICACION',
                422,
                'El arqueo ya no se encuentra pendiente de certificación.'
            );

            $firmaRealizador = FirmaArqueo::query()
                ->where('arqueo_id', $arqueoBloqueado->id)
                ->where('tipo_firma', 'REALIZADOR')
                ->where('valida', true)
                ->first();

            abort_if(
                ! $firmaRealizador,
                422,
                'El realizador todavía no ha firmado electrónicamente este arqueo.'
            );

            $firmaAgente = FirmaArqueo::query()
                ->where('arqueo_id', $arqueoBloqueado->id)
                ->where('tipo_firma', 'VALIDADOR')
                ->where('valida', true)
                ->first();

            abort_if(
                ! $firmaAgente,
                422,
                'El Agente todavía no ha validado y firmado este arqueo.'
            );

            $firmaExistente = FirmaArqueo::query()
                ->where('arqueo_id', $arqueoBloqueado->id)
                ->where('tipo_firma', 'CERTIFICADOR')
                ->where('valida', true)
                ->exists();

            abort_if(
                $firmaExistente,
                422,
                'Este arqueo ya posee una certificación válida.'
            );

            $usuario->loadMissing('datosPersonales');
            $datosPersonales = $usuario->datosPersonales;

            $nombres = trim((string) (
                $datosPersonales?->nombres
                ?? $datosPersonales?->nombre
                ?? $usuario->usuario
                ?? 'Jefe de Agentes'
            ));

            $apellidos = trim((string) (
                $datosPersonales?->apellidos
                ?? $datosPersonales?->apellido
                ?? ''
            ));

            $contenidoDocumento = json_encode([
                'arqueo_id' => $arqueoBloqueado->id,
                'numero_arqueo' => $arqueoBloqueado->numero_arqueo,
                'agente_id' => $arqueoBloqueado->agente_id,
                'fecha_arqueo' => optional($arqueoBloqueado->fecha_arqueo)->format('Y-m-d'),
                'total_arqueado' => $arqueoBloqueado->total_arqueado,
                'saldo_sistema' => $arqueoBloqueado->saldo_sistema,
                'diferencia' => $arqueoBloqueado->diferencia,
                'certificado_por' => $usuario->id,
                'fecha_firma' => now()->toIso8601String(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $hashDocumento = hash('sha256', (string) $contenidoDocumento);
            $firmaElectronica = hash_hmac(
                'sha256',
                $hashDocumento,
                config('app.key')
            );

            $valoresAnterioresArqueo =
                $this->obtenerValoresAuditoriaArqueo(
                    $arqueoBloqueado->id
                );

            $firmaJefe = FirmaArqueo::create([
                'arqueo_id' => $arqueoBloqueado->id,
                'usuario_id' => $usuario->id,
                'tipo_firma' => 'CERTIFICADOR',
                'rol_firmante' => 'jefedeAgentes',
                'nombres_historicos' => $nombres,
                'apellidos_historicos' => $apellidos,
                'hash_documento' => $hashDocumento,
                'firma_electronica' => $firmaElectronica,
                'algoritmo' => 'HMAC-SHA256',
                'version_firma' => 1,
                'fecha_firma' => now(),
                'valida' => true,
            ]);

            $arqueoBloqueado->update([
                'estado' => 'CERTIFICADO',
                'certificado_at' => now(),
            ]);

            $valoresNuevosArqueo =
                $this->obtenerValoresAuditoriaArqueo(
                    $arqueoBloqueado->id
                );

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Certificaciones',
                accion: 'FIRMAR_ARQUEO_CERTIFICADOR',
                tablaAfectada: 'firmas_arqueos',
                registroId: (int) $firmaJefe->id,
                descripcion:
                    'El Jefe de Agentes registró su firma electrónica '
                    . 'como CERTIFICADOR en el arqueo '
                    . $arqueoBloqueado->numero_arqueo
                    . '.',
                valoresAnteriores: null,
                valoresNuevos: [
                    'id' => (int) $firmaJefe->id,
                    'arqueo_id' => (int) $firmaJefe->arqueo_id,
                    'numero_arqueo' =>
                        $arqueoBloqueado->numero_arqueo,
                    'usuario_id' => (int) $firmaJefe->usuario_id,
                    'tipo_firma' => $firmaJefe->tipo_firma,
                    'rol_firmante' => $firmaJefe->rol_firmante,
                    'nombres_historicos' =>
                        $firmaJefe->nombres_historicos,
                    'apellidos_historicos' =>
                        $firmaJefe->apellidos_historicos,
                    'algoritmo' => $firmaJefe->algoritmo,
                    'version_firma' =>
                        (int) $firmaJefe->version_firma,
                    'fecha_firma' =>
                        optional($firmaJefe->fecha_firma)
                            ->format('Y-m-d H:i:s'),
                    'valida' => (bool) $firmaJefe->valida,
                ]
            );

            app(AuditoriaService::class)->registrar(
                usuario: $usuario,
                modulo: 'Certificaciones',
                accion: 'CERTIFICAR_ARQUEO',
                tablaAfectada: 'arqueos',
                registroId: $arqueoBloqueado->id,
                descripcion:
                    'El Jefe de Agentes certificó el arqueo '
                    . $arqueoBloqueado->numero_arqueo
                    . ' correspondiente al Agente '
                    . ($valoresNuevosArqueo['codigo_agente_historico']
                        ?? ('#' . $arqueoBloqueado->agente_id))
                    . '.',
                valoresAnteriores: $valoresAnterioresArqueo,
                valoresNuevos: $valoresNuevosArqueo
            );

            return redirect()
                ->route('jefe.certificaciones.index')
                ->with(
                    'success',
                    'El arqueo fue certificado correctamente por el Jefe de Agentes.'
                );
        });
    }

    private function obtenerValoresAuditoriaArqueo(
        int $arqueoId
    ): array {
        $registro = DB::table('arqueos as arq')
            ->leftJoin(
                'agentes as a',
                'a.id',
                '=',
                'arq.agente_id'
            )
            ->where('arq.id', $arqueoId)
            ->select([
                'arq.id',
                'arq.numero_arqueo',
                'arq.agente_id',
                'arq.creado_por',
                'arq.tipo',
                'arq.estado',
                'arq.fecha_arqueo',
                'arq.total_billetes',
                'arq.total_monedas',
                'arq.total_arqueado',
                'arq.saldo_sistema',
                'arq.diferencia',
                'arq.pendiente_certificacion_at',
                'arq.certificado_at',
                'arq.anulado_at',
                'arq.codigo_agente_historico',
                'arq.nombre_negocio_historico',
                'arq.nombre_propietario_historico',
                'arq.ruta_historica',
                'arq.region_historica',
                'a.codigo_agente as codigo_agente_actual',
            ])
            ->first();

        if (! $registro) {
            return [];
        }

        return [
            'id' => (int) $registro->id,
            'numero_arqueo' => $registro->numero_arqueo,
            'agente_id' => (int) $registro->agente_id,
            'creado_por' => (int) $registro->creado_por,
            'tipo' => $registro->tipo,
            'estado' => $registro->estado,
            'fecha_arqueo' => (string) $registro->fecha_arqueo,
            'total_billetes' =>
                (float) $registro->total_billetes,
            'total_monedas' =>
                (float) $registro->total_monedas,
            'total_arqueado' =>
                (float) $registro->total_arqueado,
            'saldo_sistema' =>
                (float) $registro->saldo_sistema,
            'diferencia' =>
                (float) $registro->diferencia,
            'pendiente_certificacion_at' =>
                $registro->pendiente_certificacion_at !== null
                    ? (string) $registro->pendiente_certificacion_at
                    : null,
            'certificado_at' =>
                $registro->certificado_at !== null
                    ? (string) $registro->certificado_at
                    : null,
            'anulado_at' =>
                $registro->anulado_at !== null
                    ? (string) $registro->anulado_at
                    : null,
            'codigo_agente_historico' =>
                $registro->codigo_agente_historico,
            'nombre_negocio_historico' =>
                $registro->nombre_negocio_historico,
            'nombre_propietario_historico' =>
                $registro->nombre_propietario_historico,
            'ruta_historica' =>
                $registro->ruta_historica,
            'region_historica' =>
                $registro->region_historica,
        ];
    }

    private function validarJefe(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');

        abort_if(
            ! $usuario->rol || $usuario->rol->nombre !== 'jefedeAgentes',
            403,
            'No tiene autorización para acceder a esta sección.'
        );
    }
}
