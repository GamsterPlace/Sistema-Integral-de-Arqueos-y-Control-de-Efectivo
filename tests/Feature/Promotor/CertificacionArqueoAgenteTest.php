<?php

namespace Tests\Feature\Promotor;

use App\Models\Arqueo;
use App\Models\FirmaArqueo;
use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CertificacionArqueoAgenteTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $promotor;
    private Usuario $agenteUsuario;

    private int $agenteId;
    private int $otroAgenteId;
    private int $rutaId;

    protected function setUp(): void
    {
        parent::setUp();

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

        /** @var Usuario $promotor */
        $promotor = Usuario::factory()->create([
            'rol_id' => $rolPromotor->id,
            'usuario' => 'promotor.certificador',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        /** @var Usuario $agenteUsuario */
        $agenteUsuario = Usuario::factory()->create([
            'rol_id' => $rolAgente->id,
            'usuario' => 'agente.certificado',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        /** @var Usuario $otroAgenteUsuario */
        $otroAgenteUsuario = Usuario::factory()->create([
            'rol_id' => $rolAgente->id,
            'usuario' => 'otro.agente.certificado',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        $this->promotor = $promotor;
        $this->agenteUsuario = $agenteUsuario;

        DB::table('datos_personales')->insert([
            [
                'usuario_id' => $promotor->id,
                'nombres' => 'Promotor',
                'apellidos' => 'Certificador',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $agenteUsuario->id,
                'nombres' => 'Agente',
                'apellidos' => 'Certificado',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $otroAgenteUsuario->id,
                'nombres' => 'Otro',
                'apellidos' => 'Agente',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $regionId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región Certificación Promotor',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->rutaId = DB::table('rutas')->insertGetId([
            'region_id' => $regionId,
            'codigo' => 'RUTA-CERT-PROM-01',
            'nombre' => 'Ruta Certificación Promotor',
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
            'asignado_por' => $promotor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->agenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $agenteUsuario->id,
            'ruta_id' => $this->rutaId,
            'codigo_agente' => 'AG-CERT-PROM-001',
            'nombre_negocio' => 'Negocio Certificación Promotor',
            'nombre_propietario' => 'Propietario Certificación',
            'direccion' => 'Dirección Certificación',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otraRegionId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región No Asignada',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otraRutaId = DB::table('rutas')->insertGetId([
            'region_id' => $otraRegionId,
            'codigo' => 'RUTA-NO-ASIGNADA',
            'nombre' => 'Ruta No Asignada',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->otroAgenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $otroAgenteUsuario->id,
            'ruta_id' => $otraRutaId,
            'codigo_agente' => 'AG-CERT-PROM-002',
            'nombre_negocio' => 'Negocio No Asignado',
            'nombre_propietario' => 'Otro Propietario',
            'direccion' => 'Otra Dirección',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_promotor_puede_certificar_arqueo_diario_firmado_por_agente(): void
    {
        $arqueo = $this->crearArqueo();
        $this->crearFirmaRealizador($arqueo);

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.certificar',
                    $arqueo
                )
            );

        $response->assertRedirect(
            route(
                'promotor.arqueos-agentes.show',
                $arqueo
            )
        );

        $response->assertSessionHas(
            'success',
            'El arqueo fue certificado y firmado electrónicamente.'
        );

        $arqueo->refresh();

        $this->assertSame(
            'CERTIFICADO',
            $arqueo->estado
        );

        $this->assertNotNull(
            $arqueo->certificado_at
        );
    }

    public function test_certificacion_crea_firma_certificador_del_promotor(): void
    {
        $arqueo = $this->crearArqueo();
        $this->crearFirmaRealizador($arqueo);

        $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.certificar',
                    $arqueo
                )
            );

        $this->assertDatabaseHas('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $this->promotor->id,
            'tipo_firma' => 'CERTIFICADOR',
            'rol_firmante' => 'Promotor',
            'nombres_historicos' => 'Promotor',
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

        $this->assertSame(
            64,
            strlen($firma->hash_documento)
        );

        $this->assertSame(
            64,
            strlen($firma->firma_electronica)
        );

        $this->assertNotNull(
            $firma->fecha_firma
        );
    }

    public function test_no_permite_certificar_sin_firma_realizador_del_agente(): void
    {
        $arqueo = $this->crearArqueo();

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
            'PENDIENTE_CERTIFICACION',
            $arqueo->estado
        );

        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'tipo_firma' => 'CERTIFICADOR',
        ]);
    }

    public function test_no_permite_certificar_arqueo_anulado(): void
    {
        $arqueo = $this->crearArqueo(
            estado: 'ANULADO'
        );

        $this->crearFirmaRealizador($arqueo);

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

        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'tipo_firma' => 'CERTIFICADOR',
        ]);
    }

    public function test_no_permite_certificar_arqueo_ya_certificado(): void
    {
        $arqueo = $this->crearArqueo(
            estado: 'CERTIFICADO'
        );

        $this->crearFirmaRealizador($arqueo);

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

        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'tipo_firma' => 'CERTIFICADOR',
        ]);
    }

    public function test_no_permite_certificar_dos_veces_el_mismo_arqueo(): void
    {
        $arqueo = $this->crearArqueo();
        $this->crearFirmaRealizador($arqueo);

        $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.certificar',
                    $arqueo
                )
            )
            ->assertRedirect();

        $segundoIntento = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.certificar',
                    $arqueo
                )
            );

        $segundoIntento->assertStatus(422);

        $arqueo->refresh();

        $this->assertSame(
            'CERTIFICADO',
            $arqueo->estado
        );

        $this->assertSame(
            1,
            DB::table('firmas_arqueos')
                ->where('arqueo_id', $arqueo->id)
                ->where('tipo_firma', 'CERTIFICADOR')
                ->where('valida', true)
                ->count()
        );
    }

    public function test_no_permite_certificar_arqueo_que_no_sea_diario_agente(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'VISITA_PROMOTOR'
        );

        $this->crearFirmaRealizador($arqueo);

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.certificar',
                    $arqueo
                )
            );

        $response->assertNotFound();

        $arqueo->refresh();

        $this->assertSame(
            'PENDIENTE_CERTIFICACION',
            $arqueo->estado
        );

        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'tipo_firma' => 'CERTIFICADOR',
        ]);
    }

    public function test_no_permite_certificar_arqueo_de_agente_no_asignado(): void
    {
        $arqueo = $this->crearArqueo(
            agenteId: $this->otroAgenteId
        );

        $this->crearFirmaRealizador(
            $arqueo,
            $this->obtenerUsuarioDelAgente(
                $this->otroAgenteId
            )
        );

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.certificar',
                    $arqueo
                )
            );

        $response->assertForbidden();

        $arqueo->refresh();

        $this->assertSame(
            'PENDIENTE_CERTIFICACION',
            $arqueo->estado
        );

        $this->assertDatabaseMissing('firmas_arqueos', [
            'arqueo_id' => $arqueo->id,
            'tipo_firma' => 'CERTIFICADOR',
        ]);
    }

    public function test_certificacion_genera_auditoria_de_firma_y_arqueo(): void
    {
        $arqueo = $this->crearArqueo();
        $this->crearFirmaRealizador($arqueo);

        $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.certificar',
                    $arqueo
                )
            )
            ->assertRedirect();

        $firma = DB::table('firmas_arqueos')
            ->where('arqueo_id', $arqueo->id)
            ->where('tipo_firma', 'CERTIFICADOR')
            ->first();

        $this->assertNotNull($firma);

        $this->assertDatabaseHas('auditoria', [
            'usuario_id' => $this->promotor->id,
            'modulo' => 'Firmas de Arqueos',
            'accion' => 'FIRMAR_ARQUEO',
            'tabla_afectada' => 'firmas_arqueos',
            'registro_id' => $firma->id,
        ]);

        $this->assertDatabaseHas('auditoria', [
            'usuario_id' => $this->promotor->id,
            'modulo' => 'Arqueos de Agentes',
            'accion' => 'CERTIFICAR_ARQUEO',
            'tabla_afectada' => 'arqueos',
            'registro_id' => $arqueo->id,
        ]);
    }

    private function crearArqueo(
        string $tipo = 'DIARIO_AGENTE',
        string $estado = 'PENDIENTE_CERTIFICACION',
        ?int $agenteId = null
    ): Arqueo {
        $agenteId ??= $this->agenteId;

        $agente = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join(
                'regiones as reg',
                'reg.id',
                '=',
                'r.region_id'
            )
            ->where('a.id', $agenteId)
            ->select([
                'a.codigo_agente',
                'a.nombre_negocio',
                'a.nombre_propietario',
                'a.direccion',
                'r.nombre as ruta_nombre',
                'reg.nombre as region_nombre',
            ])
            ->first();

        /** @var Arqueo $arqueo */
        $arqueo = Arqueo::query()->create([
            'numero_arqueo' =>
                'ARQ-CERT-PROM-' . strtoupper(
                    substr(
                        md5(uniqid('', true)),
                        0,
                        10
                    )
                ),

            'agente_id' => $agenteId,
            'creado_por' =>
                $this->obtenerUsuarioDelAgente(
                    $agenteId
                )->id,

            'habilitacion_atrasada_id' => null,

            'tipo' => $tipo,
            'estado' => $estado,

            'fecha_arqueo' => today(),
            'hora_inicio' => now()->subMinutes(10),
            'hora_fin' => now(),

            'fuera_fecha_ordinaria' => false,

            'codigo_agente_historico' =>
                $agente->codigo_agente,

            'nombre_negocio_historico' =>
                $agente->nombre_negocio,

            'nombre_propietario_historico' =>
                $agente->nombre_propietario,

            'direccion_historica' =>
                $agente->direccion,

            'ruta_historica' =>
                $agente->ruta_nombre,

            'region_historica' =>
                $agente->region_nombre,

            'total_billetes' => 200.00,
            'total_monedas' => 0.00,
            'total_arqueado' => 200.00,
            'saldo_sistema' => 200.00,
            'diferencia' => 0.00,

            'certificacion' => null,

            'observaciones' =>
                'Prueba de certificación por Promotor.',

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

    private function crearFirmaRealizador(
        Arqueo $arqueo,
        ?Usuario $usuario = null
    ): FirmaArqueo {
        $usuario ??= $this->agenteUsuario;

        $datosPersonales = DB::table('datos_personales')
            ->where('usuario_id', $usuario->id)
            ->first();

        /** @var FirmaArqueo $firma */
        $firma = FirmaArqueo::query()->create([
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $usuario->id,
            'tipo_firma' => 'REALIZADOR',
            'rol_firmante' => 'agente',

            'nombres_historicos' =>
                $datosPersonales->nombres,

            'apellidos_historicos' =>
                $datosPersonales->apellidos,

            'hash_documento' => hash(
                'sha256',
                'documento-realizador-' . $arqueo->id
            ),

            'firma_electronica' => hash_hmac(
                'sha256',
                'firma-realizador-' . $arqueo->id,
                config('app.key')
            ),

            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'fecha_firma' => now(),
            'valida' => true,
        ]);

        return $firma;
    }

    private function obtenerUsuarioDelAgente(
        int $agenteId
    ): Usuario {
        $usuarioId = DB::table('agentes')
            ->where('id', $agenteId)
            ->value('usuario_id');

        return Usuario::query()
            ->findOrFail($usuarioId);
    }
}
