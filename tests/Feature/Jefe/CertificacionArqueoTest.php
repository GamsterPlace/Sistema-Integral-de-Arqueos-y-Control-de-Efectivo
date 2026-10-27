<?php
namespace Tests\Feature\Jefe;
use App\Models\Arqueo;
use App\Models\FirmaArqueo;
use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class CertificacionArqueoTest extends TestCase
{
    use RefreshDatabase;
    private Usuario $jefe;
    private Usuario $promotor;
    private Usuario $agenteUsuario;
    private int $agenteId;
    protected function setUp(): void
    {
        parent::setUp();
        /** @var Role $rolJefe */
        $rolJefe = Role::factory()->create([
            'nombre' => 'jefedeAgentes',
            'estado' => true,
        ]);
        /** @var Role $rolPromotor */
        $rolPromotor = Role::factory()->create([
            'nombre' => 'Promotor',
            'estado' => true,
        ]);
        /** @var Role $rolAgente */
        $rolAgente = Role::factory()->create([
            'nombre' => 'agente',
            'estado' => true,
        ]);
        /** @var Usuario $jefe */
        $jefe = Usuario::factory()->create([
            'rol_id' => $rolJefe->id,
            'usuario' => 'jefe.certificador',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);
        /** @var Usuario $promotor */
        $promotor = Usuario::factory()->create([
            'rol_id' => $rolPromotor->id,
            'usuario' => 'promotor.certificacion',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);
        /** @var Usuario $agenteUsuario */
        $agenteUsuario = Usuario::factory()->create([
            'rol_id' => $rolAgente->id,
            'usuario' => 'agente.certificacion',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);
        $this->jefe = $jefe;
        $this->promotor = $promotor;
        $this->agenteUsuario = $agenteUsuario;
        DB::table('datos_personales')->insert([
            [
                'usuario_id' => $jefe->id,
                'nombres' => 'Jefe',
                'apellidos' => 'Certificador',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $promotor->id,
                'nombres' => 'Promotor',
                'apellidos' => 'Certificacion',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $agenteUsuario->id,
                'nombres' => 'Agente',
                'apellidos' => 'Certificacion',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $regionId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región Certificación',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $rutaId = DB::table('rutas')->insertGetId([
            'region_id' => $regionId,
            'codigo' => 'RUTA-CERT-01',
            'nombre' => 'Ruta Certificación',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->agenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $agenteUsuario->id,
            'ruta_id' => $rutaId,
            'codigo_agente' => 'AG-CERT-001',
            'nombre_negocio' => 'Negocio Certificación',
            'nombre_propietario' => 'Propietario Certificación',
            'direccion' => 'Dirección Certificación',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    public function test_jefe_puede_certificar_arqueo_de_promotor_validado_por_agente(): void
    {
        $arqueo = $this->crearArqueoPromotor();
        $this->crearFirmaValidador($arqueo);
        $response = $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.certificaciones.certificar', $arqueo),
                [
                    'password' => 'Password123!',
                ]
            );
        $response->assertRedirect(
            route('jefe.certificaciones.index')
        );
        $this->assertDatabaseHas('arqueos', [
            'id' => $arqueo->id,
            'estado' => 'CERTIFICADO',
        ]);
        $arqueo->refresh();
        $this->assertNotNull($arqueo->certificado_at);
    }
    public function test_certificacion_crea_firma_certificador_del_jefe(): void
    {
        $arqueo = $this->crearArqueoPromotor();
        $this->crearFirmaValidador($arqueo);
        $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.certificaciones.certificar', $arqueo),
                [
                    'password' => 'Password123!',
                ]
            );
        $this->assertDatabaseHas('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $this->jefe->id,
            'tipo_firma' => 'CERTIFICADOR',
            'rol_firmante' => 'jefedeAgentes',
            'nombres_historicos' => 'Jefe',
            'apellidos_historicos' => 'Certificador',
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'valida' => true,
        ]);
        $firma = DB::table('firmas_arqueos')
            ->where('arqueo_id', $arqueo->id)
            ->where('tipo_firma', 'CERTIFICADOR')
            ->first();
        $this->assertNotNull($firma);
        $this->assertSame(64, strlen($firma->hash_documento));
        $this->assertSame(64, strlen($firma->firma_electronica));
    }
    public function test_no_permite_certificar_con_password_incorrecta(): void
    {
        $arqueo = $this->crearArqueoPromotor();
        $this->crearFirmaValidador($arqueo);
        $response = $this
            ->actingAs($this->jefe)
            ->from(route('jefe.certificaciones.index'))
            ->post(
                route('jefe.certificaciones.certificar', $arqueo),
                [
                    'password' => 'Incorrecta123!',
                ]
            );
        $response->assertRedirect(
            route('jefe.certificaciones.index')
        );
        $response->assertSessionHasErrors([
            'password',
        ]);
        $this->assertDatabaseHas('arqueos', [
            'id' => $arqueo->id,
            'estado' => 'PENDIENTE_CERTIFICACION',
        ]);
        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'tipo_firma' => 'CERTIFICADOR',
        ]);
    }
    public function test_no_permite_certificar_sin_firma_validador_del_agente(): void
    {
        $arqueo = $this->crearArqueoPromotor();
        $response = $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.certificaciones.certificar', $arqueo),
                [
                    'password' => 'Password123!',
                ]
            );
        $response->assertStatus(422);
        $this->assertDatabaseHas('arqueos', [
            'id' => $arqueo->id,
            'estado' => 'PENDIENTE_CERTIFICACION',
        ]);
        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'tipo_firma' => 'CERTIFICADOR',
        ]);
    }
    public function test_no_permite_certificar_arqueo_que_no_sea_visita_promotor(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            tipo: 'DIARIO_AGENTE'
        );
        $this->crearFirmaValidador($arqueo);
        $response = $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.certificaciones.certificar', $arqueo),
                [
                    'password' => 'Password123!',
                ]
            );
        $response->assertStatus(422);
        $this->assertDatabaseHas('arqueos', [
            'id' => $arqueo->id,
            'estado' => 'PENDIENTE_CERTIFICACION',
        ]);
    }
    public function test_no_permite_certificar_arqueo_ya_certificado(): void
    {
        $arqueo = $this->crearArqueoPromotor();
        $this->crearFirmaValidador($arqueo);
        $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.certificaciones.certificar', $arqueo),
                [
                    'password' => 'Password123!',
                ]
            )
            ->assertRedirect(
                route('jefe.certificaciones.index')
            );
        $segundoIntento = $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.certificaciones.certificar', $arqueo),
                [
                    'password' => 'Password123!',
                ]
            );
        $segundoIntento->assertStatus(422);
        $this->assertSame(
            1,
            DB::table('firmas_arqueos')
                ->where('arqueo_id', $arqueo->id)
                ->where('tipo_firma', 'CERTIFICADOR')
                ->where('valida', true)
                ->count()
        );
    }
    public function test_arqueo_certificado_no_puede_volver_a_estado_pendiente_por_certificacion(): void
    {
        $arqueo = $this->crearArqueoPromotor(
            estado: 'CERTIFICADO'
        );
        $this->crearFirmaValidador($arqueo);
        $response = $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.certificaciones.certificar', $arqueo),
                [
                    'password' => 'Password123!',
                ]
            );
        $response->assertStatus(422);
        $this->assertDatabaseHas('arqueos', [
            'id' => $arqueo->id,
            'estado' => 'CERTIFICADO',
        ]);
    }
    private function crearArqueoPromotor(
        string $tipo = 'VISITA_PROMOTOR',
        string $estado = 'PENDIENTE_CERTIFICACION'
    ): Arqueo {
        /** @var Arqueo $arqueo */
        $arqueo = Arqueo::query()->create([
            'numero_arqueo' =>
                'ARQ-CERT-' . strtoupper(
                    substr(md5(uniqid('', true)), 0, 10)
                ),
            'agente_id' => $this->agenteId,
            'creado_por' => $this->promotor->id,
            'habilitacion_atrasada_id' => null,
            'tipo' => $tipo,
            'estado' => $estado,
            'fecha_arqueo' => today(),
            'hora_inicio' => now()->subMinutes(10),
            'hora_fin' => now(),
            'fuera_fecha_ordinaria' => false,
            'codigo_agente_historico' => 'AG-CERT-001',
            'nombre_negocio_historico' => 'Negocio Certificación',
            'nombre_propietario_historico' => 'Propietario Certificación',
            'direccion_historica' => 'Dirección Certificación',
            'ruta_historica' => 'Ruta Certificación',
            'region_historica' => 'Región Certificación',
            'total_billetes' => 200.00,
            'total_monedas' => 0.00,
            'total_arqueado' => 200.00,
            'saldo_sistema' => 200.00,
            'diferencia' => 0.00,
            'certificacion' =>
                'Arqueo de Promotor para prueba de certificación.',
            'observaciones' => null,
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
    private function crearFirmaValidador(
        Arqueo $arqueo
    ): FirmaArqueo {
        FirmaArqueo::query()->create([
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $this->promotor->id,
            'tipo_firma' => 'REALIZADOR',
            'rol_firmante' => 'Promotor',
            'nombres_historicos' => 'Promotor',
            'apellidos_historicos' => 'Certificacion',
            'hash_documento' => hash('sha256', 'documento-realizador-' . $arqueo->id),
            'firma_electronica' => hash('sha256', 'firma-realizador-' . $arqueo->id),
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'fecha_firma' => now(),
            'valida' => true,
        ]);
        /** @var FirmaArqueo $firma */
        $firma = FirmaArqueo::query()->create([
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $this->agenteUsuario->id,
            'tipo_firma' => 'VALIDADOR',
            'rol_firmante' => 'agente',
            'nombres_historicos' => 'Agente',
            'apellidos_historicos' => 'Certificacion',
            'hash_documento' => hash(
                'sha256',
                'documento-validador-' . $arqueo->id
            ),
            'firma_electronica' => hash(
                'sha256',
                'firma-validador-' . $arqueo->id
            ),
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'fecha_firma' => now(),
            'valida' => true,
        ]);
        return $firma;
    }
}
