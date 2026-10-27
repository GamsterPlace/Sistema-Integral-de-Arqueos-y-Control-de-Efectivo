<?php

namespace Tests\Feature\Arqueos;

use App\Models\Arqueo;
use App\Models\FirmaArqueo;
use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InmutabilidadArqueoCerradoTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $agenteUsuario;
    private Usuario $promotor;
    private Usuario $jefe;

    private int $agenteId;
    private int $rutaId;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Role $rolAgente */
        $rolAgente = Role::factory()->create([
            'nombre' => 'agente',
            'estado' => true,
        ]);

        /** @var Role $rolPromotor */
        $rolPromotor = Role::factory()->create([
            'nombre' => 'Promotor',
            'estado' => true,
        ]);

        /** @var Role $rolJefe */
        $rolJefe = Role::factory()->create([
            'nombre' => 'jefedeAgentes',
            'estado' => true,
        ]);

        /** @var Usuario $agente */
        $agente = Usuario::factory()->create([
            'rol_id' => $rolAgente->id,
            'usuario' => 'agente.inmutabilidad',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        /** @var Usuario $promotor */
        $promotor = Usuario::factory()->create([
            'rol_id' => $rolPromotor->id,
            'usuario' => 'promotor.inmutabilidad',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        /** @var Usuario $jefe */
        $jefe = Usuario::factory()->create([
            'rol_id' => $rolJefe->id,
            'usuario' => 'jefe.inmutabilidad',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        $this->agenteUsuario = $agente;
        $this->promotor = $promotor;
        $this->jefe = $jefe;

        DB::table('datos_personales')->insert([
            [
                'usuario_id' => $agente->id,
                'nombres' => 'Agente',
                'apellidos' => 'Inmutabilidad',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $promotor->id,
                'nombres' => 'Promotor',
                'apellidos' => 'Inmutabilidad',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $jefe->id,
                'nombres' => 'Jefe',
                'apellidos' => 'Inmutabilidad',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $regionId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región Inmutabilidad',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->rutaId = DB::table('rutas')->insertGetId([
            'region_id' => $regionId,
            'codigo' => 'RUTA-INM-01',
            'nombre' => 'Ruta Inmutabilidad',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('asignaciones_promotor_ruta')->insert([
            'promotor_usuario_id' => $promotor->id,
            'ruta_id' => $this->rutaId,
            'fecha_inicio' => today()->subDay()->toDateString(),
            'fecha_fin' => null,
            'estado' => true,
            'asignado_por' => $jefe->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->agenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $agente->id,
            'ruta_id' => $this->rutaId,
            'codigo_agente' => 'AG-INM-001',
            'nombre_negocio' => 'Negocio Inmutabilidad',
            'nombre_propietario' => 'Propietario Inmutabilidad',
            'direccion' => 'Dirección Inmutabilidad',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_arqueo_diario_certificado_no_puede_certificarse_nuevamente(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            estado: 'CERTIFICADO'
        );

        $this->crearFirma(
            $arqueo,
            $this->agenteUsuario,
            'REALIZADOR',
            'agente'
        );

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.certificar',
                    $arqueo
                )
            );

        $response->assertStatus(422);

        $arqueo->refresh();

        $this->assertSame(
            'CERTIFICADO',
            $arqueo->estado
        );

        $this->assertSame(
            '350.00',
            $arqueo->total_arqueado
        );

        $this->assertSame(
            '300.00',
            $arqueo->saldo_sistema
        );

        $this->assertSame(
            '50.00',
            $arqueo->diferencia
        );
    }

    public function test_arqueo_anulado_no_puede_certificarse(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            estado: 'ANULADO'
        );

        $this->crearFirma(
            $arqueo,
            $this->agenteUsuario,
            'REALIZADOR',
            'agente'
        );

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.certificar',
                    $arqueo
                )
            );

        $response->assertStatus(422);

        $arqueo->refresh();

        $this->assertSame(
            'ANULADO',
            $arqueo->estado
        );

        $this->assertSame(
            '350.00',
            $arqueo->total_arqueado
        );

        $this->assertSame(
            'Observación original.',
            $arqueo->observaciones
        );
    }

    public function test_promotor_no_puede_anular_arqueo_certificado(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            estado: 'CERTIFICADO'
        );

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Intento de anulación sobre registro certificado.',
                    'password' =>
                        'Password123!',
                ]
            );

        $response->assertStatus(422);

        $arqueo->refresh();

        $this->assertSame(
            'CERTIFICADO',
            $arqueo->estado
        );

        $this->assertSame(
            '350.00',
            $arqueo->total_arqueado
        );

        $this->assertSame(
            'Observación original.',
            $arqueo->observaciones
        );
    }

    public function test_visita_promotor_certificada_no_puede_recibir_firma_validador(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'VISITA_PROMOTOR',
            estado: 'CERTIFICADO',
            creadoPor: $this->promotor->id
        );

        $response = $this
            ->actingAs($this->agenteUsuario)
            ->from(
                route(
                    'agente.arqueos-promotor.show',
                    $arqueo
                )
            )
            ->post(
                route(
                    'agente.arqueos-promotor.firmar',
                    $arqueo
                )
            );

        $response->assertRedirect(
            route(
                'agente.arqueos-promotor.show',
                $arqueo
            )
        );

        $response->assertSessionHasErrors([
            'firma',
        ]);

        $this->assertDatabaseMissing(
            'firmas_arqueos',
            [
                'arqueo_id' => $arqueo->id,
                'usuario_id' => $this->agenteUsuario->id,
                'tipo_firma' => 'VALIDADOR',
            ]
        );

        $arqueo->refresh();

        $this->assertSame(
            'CERTIFICADO',
            $arqueo->estado
        );
    }

    public function test_visita_promotor_anulada_no_puede_recibir_firma_validador(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'VISITA_PROMOTOR',
            estado: 'ANULADO',
            creadoPor: $this->promotor->id
        );

        $response = $this
            ->actingAs($this->agenteUsuario)
            ->from(
                route(
                    'agente.arqueos-promotor.show',
                    $arqueo
                )
            )
            ->post(
                route(
                    'agente.arqueos-promotor.firmar',
                    $arqueo
                )
            );

        $response->assertRedirect(
            route(
                'agente.arqueos-promotor.show',
                $arqueo
            )
        );

        $response->assertSessionHasErrors([
            'firma',
        ]);

        $this->assertDatabaseMissing(
            'firmas_arqueos',
            [
                'arqueo_id' => $arqueo->id,
                'usuario_id' => $this->agenteUsuario->id,
                'tipo_firma' => 'VALIDADOR',
            ]
        );

        $arqueo->refresh();

        $this->assertSame(
            'ANULADO',
            $arqueo->estado
        );
    }

    public function test_jefe_puede_anular_certificado_sin_modificar_datos_economicos(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            estado: 'CERTIFICADO'
        );

        $detalleAntes = DB::table('arqueo_detalles')
            ->where('arqueo_id', $arqueo->id)
            ->first();

        $response = $this
            ->actingAs($this->jefe)
            ->post(
                route(
                    'jefe.arqueos.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Anulación autorizada por Jefatura para prueba.',
                    'password' =>
                        'Password123!',
                ]
            );

        $response->assertRedirect(
            route(
                'jefe.arqueos.show',
                $arqueo
            )
        );

        $arqueo->refresh();

        $this->assertSame(
            'ANULADO',
            $arqueo->estado
        );

        $this->assertNotNull(
            $arqueo->anulado_at
        );

        $this->assertSame(
            '200.00',
            $arqueo->total_billetes
        );

        $this->assertSame(
            '150.00',
            $arqueo->total_monedas
        );

        $this->assertSame(
            '350.00',
            $arqueo->total_arqueado
        );

        $this->assertSame(
            '300.00',
            $arqueo->saldo_sistema
        );

        $this->assertSame(
            '50.00',
            $arqueo->diferencia
        );

        $detalleDespues = DB::table('arqueo_detalles')
            ->where('arqueo_id', $arqueo->id)
            ->first();

        $this->assertNotNull(
            $detalleAntes
        );

        $this->assertNotNull(
            $detalleDespues
        );

        $this->assertSame(
            (int) $detalleAntes->cantidad,
            (int) $detalleDespues->cantidad
        );

        $this->assertSame(
            (float) $detalleAntes->denominacion,
            (float) $detalleDespues->denominacion
        );

        $this->assertSame(
            (float) $detalleAntes->subtotal,
            (float) $detalleDespues->subtotal
        );
    }

    public function test_arqueo_anulado_no_puede_anularse_nuevamente_por_jefe(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            estado: 'ANULADO'
        );

        $fechaAnulacionOriginal =
            $arqueo->anulado_at;

        $observacionOriginal =
            $arqueo->observaciones;

        $response = $this
            ->actingAs($this->jefe)
            ->post(
                route(
                    'jefe.arqueos.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Segundo intento de anulación del mismo registro.',
                    'password' =>
                        'Password123!',
                ]
            );

        $response->assertStatus(422);

        $arqueo->refresh();

        $this->assertSame(
            'ANULADO',
            $arqueo->estado
        );

        $this->assertSame(
            $fechaAnulacionOriginal?->format('Y-m-d H:i:s'),
            $arqueo->anulado_at?->format('Y-m-d H:i:s')
        );

        $this->assertSame(
            $observacionOriginal,
            $arqueo->observaciones
        );

        $this->assertSame(
            '350.00',
            $arqueo->total_arqueado
        );
    }

    private function crearArqueo(
        string $tipo,
        string $estado,
        ?int $creadoPor = null
    ): Arqueo {
        $creadoPor ??=
            $tipo === 'VISITA_PROMOTOR'
                ? $this->promotor->id
                : $this->agenteUsuario->id;

        /** @var Arqueo $arqueo */
        $arqueo = Arqueo::query()->create([
            'numero_arqueo' =>
                'ARQ-INM-' . strtoupper(
                    substr(
                        md5(uniqid('', true)),
                        0,
                        10
                    )
                ),

            'agente_id' =>
                $this->agenteId,

            'creado_por' =>
                $creadoPor,

            'habilitacion_atrasada_id' =>
                null,

            'tipo' =>
                $tipo,

            'estado' =>
                $estado,

            'fecha_arqueo' =>
                today(),

            'hora_inicio' =>
                now()->subMinutes(10),

            'hora_fin' =>
                now(),

            'fuera_fecha_ordinaria' =>
                false,

            'codigo_agente_historico' =>
                'AG-INM-001',

            'nombre_negocio_historico' =>
                'Negocio Inmutabilidad',

            'nombre_propietario_historico' =>
                'Propietario Inmutabilidad',

            'direccion_historica' =>
                'Dirección Inmutabilidad',

            'ruta_historica' =>
                'Ruta Inmutabilidad',

            'region_historica' =>
                'Región Inmutabilidad',

            'total_billetes' =>
                200.00,

            'total_monedas' =>
                150.00,

            'total_arqueado' =>
                350.00,

            'saldo_sistema' =>
                300.00,

            'diferencia' =>
                50.00,

            'certificacion' =>
                'Certificación original del arqueo.',

            'observaciones' =>
                'Observación original.',

            'pendiente_certificacion_at' =>
                $estado === 'PENDIENTE_CERTIFICACION'
                    ? now()
                    : null,

            'certificado_at' =>
                $estado === 'CERTIFICADO'
                    ? now()
                    : null,

            'anulado_at' =>
                $estado === 'ANULADO'
                    ? now()
                    : null,
        ]);

        DB::table('arqueo_detalles')->insert([
            'arqueo_id' =>
                $arqueo->id,

            'tipo' =>
                'BILLETE',

            'denominacion' =>
                100.00,

            'cantidad' =>
                2,

            'subtotal' =>
                200.00,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

        return $arqueo;
    }

    private function crearFirma(
        Arqueo $arqueo,
        Usuario $usuario,
        string $tipoFirma,
        string $rolFirmante
    ): FirmaArqueo {
        $datosPersonales = DB::table(
            'datos_personales'
        )
            ->where(
                'usuario_id',
                $usuario->id
            )
            ->first();

        /** @var FirmaArqueo $firma */
        $firma = FirmaArqueo::query()->create([
            'arqueo_id' =>
                $arqueo->id,

            'usuario_id' =>
                $usuario->id,

            'tipo_firma' =>
                $tipoFirma,

            'rol_firmante' =>
                $rolFirmante,

            'nombres_historicos' =>
                $datosPersonales->nombres,

            'apellidos_historicos' =>
                $datosPersonales->apellidos,

            'hash_documento' =>
                hash(
                    'sha256',
                    'documento-inmutabilidad-'
                    . $arqueo->id
                    . '-'
                    . $tipoFirma
                ),

            'firma_electronica' =>
                hash_hmac(
                    'sha256',
                    'firma-inmutabilidad-'
                    . $arqueo->id
                    . '-'
                    . $tipoFirma,
                    config('app.key')
                ),

            'algoritmo' =>
                'HMAC-SHA256',

            'version_firma' =>
                1,

            'fecha_firma' =>
                now(),

            'valida' =>
                true,
        ]);

        return $firma;
    }
}
