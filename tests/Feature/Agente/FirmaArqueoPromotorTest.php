<?php

namespace Tests\Feature\Agente;

use App\Models\Arqueo;
use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FirmaArqueoPromotorTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuarioAgente;
    private Usuario $usuarioPromotor;
    private Usuario $otroUsuarioAgente;

    private int $agenteId;
    private int $otroAgenteId;

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

        /** @var Usuario $usuarioAgente */
        $usuarioAgente = Usuario::factory()->create([
            'rol_id' => $rolAgente->id,
            'usuario' => 'agente.validador',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        /** @var Usuario $usuarioPromotor */
        $usuarioPromotor = Usuario::factory()->create([
            'rol_id' => $rolPromotor->id,
            'usuario' => 'promotor.realizador',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        /** @var Usuario $otroUsuarioAgente */
        $otroUsuarioAgente = Usuario::factory()->create([
            'rol_id' => $rolAgente->id,
            'usuario' => 'otro.agente.validador',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        $this->usuarioAgente = $usuarioAgente;
        $this->usuarioPromotor = $usuarioPromotor;
        $this->otroUsuarioAgente = $otroUsuarioAgente;

        DB::table('datos_personales')->insert([
            [
                'usuario_id' => $usuarioAgente->id,
                'nombres' => 'Agente',
                'apellidos' => 'Validador',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $usuarioPromotor->id,
                'nombres' => 'Promotor',
                'apellidos' => 'Realizador',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $otroUsuarioAgente->id,
                'nombres' => 'Otro',
                'apellidos' => 'Agente',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $regionId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región Firma',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rutaId = DB::table('rutas')->insertGetId([
            'region_id' => $regionId,
            'codigo' => 'RUTA-FIRMA-01',
            'nombre' => 'Ruta Firma',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->agenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $usuarioAgente->id,
            'ruta_id' => $rutaId,
            'codigo_agente' => 'AG-FIRMA-001',
            'nombre_negocio' => 'Negocio Firma',
            'nombre_propietario' => 'Propietario Firma',
            'direccion' => 'Dirección Firma',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->otroAgenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $otroUsuarioAgente->id,
            'ruta_id' => $rutaId,
            'codigo_agente' => 'AG-FIRMA-002',
            'nombre_negocio' => 'Otro Negocio Firma',
            'nombre_propietario' => 'Otro Propietario Firma',
            'direccion' => 'Otra Dirección Firma',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_agente_puede_firmar_arqueo_de_promotor_como_validador(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            agenteId: $this->agenteId
        );

        $response = $this
            ->actingAs($this->usuarioAgente)
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

        $this->assertDatabaseHas('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $this->usuarioAgente->id,
            'tipo_firma' => 'VALIDADOR',
            'rol_firmante' => 'agente',
            'nombres_historicos' => 'Agente',
            'apellidos_historicos' => 'Validador',
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'valida' => true,
        ]);
    }

    public function test_firma_validador_genera_hash_y_firma_electronica_validos(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            agenteId: $this->agenteId
        );

        $this
            ->actingAs($this->usuarioAgente)
            ->post(
                route(
                    'agente.arqueos-promotor.firmar',
                    $arqueo
                )
            );

        $firma = DB::table('firmas_arqueos')
            ->where('arqueo_id', $arqueo->id)
            ->where('tipo_firma', 'VALIDADOR')
            ->first();

        $this->assertNotNull($firma);
        $this->assertSame(64, strlen($firma->hash_documento));
        $this->assertSame(64, strlen($firma->firma_electronica));
        $this->assertSame('HMAC-SHA256', $firma->algoritmo);
        $this->assertSame(1, (int) $firma->version_firma);
        $this->assertSame(1, (int) $firma->valida);
        $this->assertNotNull($firma->fecha_firma);
    }

    public function test_firmar_no_cambia_el_estado_del_arqueo(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            agenteId: $this->agenteId
        );

        $this
            ->actingAs($this->usuarioAgente)
            ->post(
                route(
                    'agente.arqueos-promotor.firmar',
                    $arqueo
                )
            );

        $this->assertDatabaseHas('arqueos', [
            'id' => $arqueo->id,
            'estado' => 'PENDIENTE_CERTIFICACION',
        ]);
    }

    public function test_no_permite_firmar_dos_veces_el_mismo_arqueo(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            agenteId: $this->agenteId
        );

        $this
            ->actingAs($this->usuarioAgente)
            ->post(
                route(
                    'agente.arqueos-promotor.firmar',
                    $arqueo
                )
            );

        $segundoIntento = $this
            ->actingAs($this->usuarioAgente)
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

        $segundoIntento->assertRedirect(
            route(
                'agente.arqueos-promotor.show',
                $arqueo
            )
        );

        $segundoIntento->assertSessionHas('warning');

        $this->assertSame(
            1,
            DB::table('firmas_arqueos')
                ->where('arqueo_id', $arqueo->id)
                ->where('tipo_firma', 'VALIDADOR')
                ->where('valida', true)
                ->count()
        );
    }

    public function test_no_permite_firmar_arqueo_de_otro_agente(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            agenteId: $this->otroAgenteId
        );

        $response = $this
            ->actingAs($this->usuarioAgente)
            ->post(
                route(
                    'agente.arqueos-promotor.firmar',
                    $arqueo
                )
            );

        $response->assertForbidden();

        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $this->usuarioAgente->id,
            'tipo_firma' => 'VALIDADOR',
        ]);
    }

    public function test_no_permite_firmar_arqueo_que_no_sea_visita_promotor(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            agenteId: $this->agenteId,
            tipo: 'DIARIO_AGENTE'
        );

        $response = $this
            ->actingAs($this->usuarioAgente)
            ->post(
                route(
                    'agente.arqueos-promotor.firmar',
                    $arqueo
                )
            );

        $response->assertNotFound();

        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $this->usuarioAgente->id,
            'tipo_firma' => 'VALIDADOR',
        ]);
    }

    public function test_no_permite_firmar_arqueo_certificado(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            agenteId: $this->agenteId,
            estado: 'CERTIFICADO'
        );

        $response = $this
            ->actingAs($this->usuarioAgente)
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

        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $this->usuarioAgente->id,
            'tipo_firma' => 'VALIDADOR',
        ]);
    }

    public function test_no_permite_firmar_arqueo_anulado(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            agenteId: $this->agenteId,
            estado: 'ANULADO'
        );

        $response = $this
            ->actingAs($this->usuarioAgente)
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

        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $this->usuarioAgente->id,
            'tipo_firma' => 'VALIDADOR',
        ]);
    }

    public function test_firma_validador_genera_registro_de_auditoria(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            agenteId: $this->agenteId
        );

        $this
            ->actingAs($this->usuarioAgente)
            ->post(
                route(
                    'agente.arqueos-promotor.firmar',
                    $arqueo
                )
            );

        $firmaId = DB::table('firmas_arqueos')
            ->where('arqueo_id', $arqueo->id)
            ->where('tipo_firma', 'VALIDADOR')
            ->value('id');

        $this->assertNotNull($firmaId);

        $this->assertDatabaseHas('auditoria', [
            'usuario_id' => $this->usuarioAgente->id,
            'modulo' => 'Firmas Arqueos',
            'accion' => 'FIRMAR_ARQUEO',
            'tabla_afectada' => 'firmas_arqueos',
            'registro_id' => $firmaId,
        ]);
    }

    private function crearArqueoPromotor(
        int $agenteId,
        string $tipo = 'VISITA_PROMOTOR',
        string $estado = 'PENDIENTE_CERTIFICACION'
    ): Arqueo {
        /** @var Arqueo $arqueo */
        $arqueo = Arqueo::query()->create([
            'numero_arqueo' =>
                'ARQ-FIRMA-' . strtoupper(
                    substr(md5(uniqid('', true)), 0, 10)
                ),

            'agente_id' => $agenteId,
            'creado_por' => $this->usuarioPromotor->id,
            'habilitacion_atrasada_id' => null,

            'tipo' => $tipo,
            'estado' => $estado,

            'fecha_arqueo' => today(),
            'hora_inicio' => now()->subMinutes(10),
            'hora_fin' => now(),

            'fuera_fecha_ordinaria' => false,

            'codigo_agente_historico' =>
                $agenteId === $this->agenteId
                    ? 'AG-FIRMA-001'
                    : 'AG-FIRMA-002',

            'nombre_negocio_historico' =>
                $agenteId === $this->agenteId
                    ? 'Negocio Firma'
                    : 'Otro Negocio Firma',

            'nombre_propietario_historico' =>
                $agenteId === $this->agenteId
                    ? 'Propietario Firma'
                    : 'Otro Propietario Firma',

            'direccion_historica' =>
                $agenteId === $this->agenteId
                    ? 'Dirección Firma'
                    : 'Otra Dirección Firma',

            'ruta_historica' => 'Ruta Firma',
            'region_historica' => 'Región Firma',

            'total_billetes' => 200.00,
            'total_monedas' => 0.00,
            'total_arqueado' => 200.00,
            'saldo_sistema' => 200.00,
            'diferencia' => 0.00,

            'certificacion' =>
                'Arqueo realizado por Promotor para prueba automatizada.',

            'observaciones' =>
                'Prueba de firma electrónica del Agente.',

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
            'arqueo_id' => $arqueo->id,
            'tipo' => 'BILLETE',
            'denominacion' => 100.00,
            'cantidad' => 2,
            'subtotal' => 200.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $arqueo;
    }
}
