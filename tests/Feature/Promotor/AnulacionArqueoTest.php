<?php

namespace Tests\Feature\Promotor;

use App\Models\Arqueo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnulacionArqueoTest extends TestCase
{
    use RefreshDatabase;

    private string $password = 'PromotorPrueba123*';

    private Usuario $promotor;
    private Usuario $agenteUsuario;

    private int $rutaId;
    private int $agenteId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crearEscenarioBase();
    }

    public function test_promotor_puede_anular_arqueo_pendiente(): void
    {
        $arqueo = $this->crearArqueo();

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Se detectó un error en la información registrada.',
                    'password' => $this->password,
                ]
            );

        $response->assertRedirect(
            route(
                'promotor.arqueos-agentes.show',
                $arqueo
            )
        );

        $response->assertSessionHas(
            'success',
            'El arqueo fue anulado correctamente.'
        );

        $arqueo->refresh();

        $this->assertSame(
            'ANULADO',
            $arqueo->estado
        );

        $this->assertNotNull(
            $arqueo->anulado_at
        );
    }

    public function test_anulacion_conserva_observacion_anterior_y_agrega_motivo(): void
    {
        $arqueo = $this->crearArqueo([
            'observaciones' =>
                'Observación original del arqueo.',
        ]);

        $motivo =
            'Se encontró una inconsistencia en los datos registrados.';

        $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' => $motivo,
                    'password' => $this->password,
                ]
            )
            ->assertRedirect();

        $arqueo->refresh();

        $this->assertStringContainsString(
            'Observación original del arqueo.',
            (string) $arqueo->observaciones
        );

        $this->assertStringContainsString(
            '[ANULACIÓN',
            (string) $arqueo->observaciones
        );

        $this->assertStringContainsString(
            'Promotor:',
            (string) $arqueo->observaciones
        );

        $this->assertStringContainsString(
            $motivo,
            (string) $arqueo->observaciones
        );
    }

    public function test_no_permite_anular_con_password_incorrecta(): void
    {
        $arqueo = $this->crearArqueo();

        $response = $this
            ->actingAs($this->promotor)
            ->from(
                route(
                    'promotor.arqueos-agentes.show',
                    $arqueo
                )
            )
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Se detectó un error que requiere anulación.',
                    'password' => 'PasswordIncorrecta123*',
                ]
            );

        $response->assertRedirect(
            route(
                'promotor.arqueos-agentes.show',
                $arqueo
            )
        );

        $response->assertSessionHasErrors([
            'password',
        ]);

        $arqueo->refresh();

        $this->assertSame(
            'PENDIENTE_CERTIFICACION',
            $arqueo->estado
        );

        $this->assertNull(
            $arqueo->anulado_at
        );
    }

    public function test_motivo_de_anulacion_es_obligatorio(): void
    {
        $arqueo = $this->crearArqueo();

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'password' => $this->password,
                ]
            );

        $response->assertSessionHasErrors([
            'motivo_anulacion',
        ]);

        $arqueo->refresh();

        $this->assertSame(
            'PENDIENTE_CERTIFICACION',
            $arqueo->estado
        );

        $this->assertNull(
            $arqueo->anulado_at
        );
    }

    public function test_motivo_debe_tener_al_menos_diez_caracteres(): void
    {
        $arqueo = $this->crearArqueo();

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' => 'Error',
                    'password' => $this->password,
                ]
            );

        $response->assertSessionHasErrors([
            'motivo_anulacion',
        ]);

        $arqueo->refresh();

        $this->assertSame(
            'PENDIENTE_CERTIFICACION',
            $arqueo->estado
        );
    }

    public function test_motivo_no_puede_superar_quinientos_caracteres(): void
    {
        $arqueo = $this->crearArqueo();

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        str_repeat('A', 501),
                    'password' => $this->password,
                ]
            );

        $response->assertSessionHasErrors([
            'motivo_anulacion',
        ]);

        $arqueo->refresh();

        $this->assertSame(
            'PENDIENTE_CERTIFICACION',
            $arqueo->estado
        );
    }

    public function test_no_permite_anular_arqueo_en_borrador(): void
    {
        $arqueo = $this->crearArqueo([
            'estado' => 'BORRADOR',
            'pendiente_certificacion_at' => null,
        ]);

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Se intenta anular un arqueo todavía en borrador.',
                    'password' => $this->password,
                ]
            );

        $response->assertStatus(422);

        $arqueo->refresh();

        $this->assertSame(
            'BORRADOR',
            $arqueo->estado
        );

        $this->assertNull(
            $arqueo->anulado_at
        );
    }

    public function test_no_permite_anular_arqueo_certificado(): void
    {
        $arqueo = $this->crearArqueo([
            'estado' => 'CERTIFICADO',
            'certificado_at' => now(),
        ]);

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Se intenta anular un arqueo que ya fue certificado.',
                    'password' => $this->password,
                ]
            );

        $response->assertStatus(422);

        $arqueo->refresh();

        $this->assertSame(
            'CERTIFICADO',
            $arqueo->estado
        );

        $this->assertNull(
            $arqueo->anulado_at
        );
    }

    public function test_no_permite_anular_dos_veces_el_mismo_arqueo(): void
    {
        $arqueo = $this->crearArqueo();

        $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Primera anulación válida del arqueo registrado.',
                    'password' => $this->password,
                ]
            )
            ->assertRedirect();

        $arqueo->refresh();

        $this->assertSame(
            'ANULADO',
            $arqueo->estado
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
                        'Segundo intento de anulación del mismo arqueo.',
                    'password' => $this->password,
                ]
            );

        $response->assertStatus(422);

        $arqueo->refresh();

        $this->assertSame(
            'ANULADO',
            $arqueo->estado
        );
    }

    public function test_no_permite_anular_arqueo_que_no_sea_diario_agente(): void
    {
        $arqueo = $this->crearArqueo([
            'tipo' => 'VISITA_PROMOTOR',
        ]);

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Intento sobre un tipo de arqueo no permitido.',
                    'password' => $this->password,
                ]
            );

        $response->assertStatus(404);

        $arqueo->refresh();

        $this->assertSame(
            'PENDIENTE_CERTIFICACION',
            $arqueo->estado
        );
    }

    public function test_no_permite_anular_arqueo_de_agente_no_asignado(): void
    {
        $otraRegionId = DB::table('regiones')
            ->insertGetId([
                'nombre' => 'Región No Asignada',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $otraRutaId = DB::table('rutas')
            ->insertGetId([
                'region_id' => $otraRegionId,
                'codigo' => 'RUTA-NO-ASIGNADA',
                'nombre' => 'Ruta No Asignada',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $rolAgenteId = DB::table('roles')
            ->where('nombre', 'agente')
            ->value('id');

        $otroUsuarioId = DB::table('usuarios')
            ->insertGetId([
                'rol_id' => $rolAgenteId,
                'usuario' => 'agente_no_asignado',
                'password' =>
                    Hash::make('AgentePrueba123*'),
                'estado' => 'ACTIVO',
                'requiere_cambio_password' => false,
                'fecha_ultimo_cambio_password' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('datos_personales')
            ->insert([
                'usuario_id' => $otroUsuarioId,
                'nombres' => 'Otro',
                'apellidos' => 'Agente',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $otroAgenteId = DB::table('agentes')
            ->insertGetId([
                'usuario_id' => $otroUsuarioId,
                'ruta_id' => $otraRutaId,
                'codigo_agente' => 'AG-NO-ASIGNADO',
                'nombre_negocio' => 'Negocio No Asignado',
                'nombre_propietario' => 'Otro Propietario',
                'direccion' => 'Dirección de prueba',
                'estado' => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $arqueo = $this->crearArqueo([
            'agente_id' => $otroAgenteId,
            'creado_por' => $otroUsuarioId,
            'codigo_agente_historico' =>
                'AG-NO-ASIGNADO',
            'nombre_negocio_historico' =>
                'Negocio No Asignado',
            'nombre_propietario_historico' =>
                'Otro Propietario',
            'ruta_historica' =>
                'Ruta No Asignada',
            'region_historica' =>
                'Región No Asignada',
        ]);

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Intento de anulación sobre un agente no asignado.',
                    'password' => $this->password,
                ]
            );

        $response->assertStatus(403);

        $arqueo->refresh();

        $this->assertSame(
            'PENDIENTE_CERTIFICACION',
            $arqueo->estado
        );
    }

    public function test_no_permite_anular_si_ya_existe_firma_certificador(): void
    {
        $arqueo = $this->crearArqueo();

        DB::table('firmas_arqueos')
            ->insert([
                'arqueo_id' => $arqueo->id,
                'usuario_id' => $this->promotor->id,
                'tipo_firma' => 'CERTIFICADOR',
                'rol_firmante' => 'Promotor',
                'nombres_historicos' => 'Promotor',
                'apellidos_historicos' => 'Prueba',
                'hash_documento' => str_repeat('a', 64),
                'firma_electronica' => str_repeat('b', 64),
                'algoritmo' => 'HMAC-SHA256',
                'version_firma' => 1,
                'fecha_firma' => now(),
                'valida' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' =>
                        'Intento de anulación después de firma del promotor.',
                    'password' => $this->password,
                ]
            );

        $response->assertStatus(422);

        $arqueo->refresh();

        $this->assertSame(
            'PENDIENTE_CERTIFICACION',
            $arqueo->estado
        );

        $this->assertNull(
            $arqueo->anulado_at
        );
    }

    public function test_anulacion_genera_registro_de_auditoria(): void
    {
        $arqueo = $this->crearArqueo();

        $motivo =
            'Registro anulado para comprobar correctamente la auditoría.';

        $this
            ->actingAs($this->promotor)
            ->post(
                route(
                    'promotor.arqueos-agentes.anular',
                    $arqueo
                ),
                [
                    'motivo_anulacion' => $motivo,
                    'password' => $this->password,
                ]
            )
            ->assertRedirect();

        $registro = DB::table('auditoria')
            ->where(
                'usuario_id',
                $this->promotor->id
            )
            ->where(
                'modulo',
                'Arqueos de Agentes'
            )
            ->where(
                'accion',
                'ANULAR_ARQUEO'
            )
            ->where(
                'tabla_afectada',
                'arqueos'
            )
            ->where(
                'registro_id',
                $arqueo->id
            )
            ->first();

        $this->assertNotNull(
            $registro
        );

        $this->assertStringContainsString(
            $motivo,
            (string) $registro->descripcion
        );

        $valoresAnteriores = json_decode(
            (string) $registro->valores_anteriores,
            true
        );

        $valoresNuevos = json_decode(
            (string) $registro->valores_nuevos,
            true
        );

        $this->assertSame(
            'PENDIENTE_CERTIFICACION',
            $valoresAnteriores['estado']
        );

        $this->assertSame(
            'ANULADO',
            $valoresNuevos['estado']
        );

        $this->assertSame(
            $motivo,
            $valoresNuevos['motivo_anulacion']
        );
    }

    private function crearEscenarioBase(): void
    {
        $rolPromotorId = DB::table('roles')
            ->insertGetId([
                'nombre' => 'Promotor',
                'descripcion' => 'Promotor de Agentes MICOOPE',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $rolAgenteId = DB::table('roles')
            ->insertGetId([
                'nombre' => 'agente',
                'descripcion' =>
                    'Usuario responsable del arqueo diario',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $promotorId = DB::table('usuarios')
            ->insertGetId([
                'rol_id' => $rolPromotorId,
                'usuario' => 'promotor_prueba',
                'password' =>
                    Hash::make($this->password),
                'estado' => 'ACTIVO',
                'requiere_cambio_password' => false,
                'fecha_ultimo_cambio_password' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('datos_personales')
            ->insert([
                'usuario_id' => $promotorId,
                'nombres' => 'Promotor',
                'apellidos' => 'Prueba',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $this->promotor =
            Usuario::findOrFail($promotorId);

        $regionId = DB::table('regiones')
            ->insertGetId([
                'nombre' => 'Región Prueba',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $this->rutaId = DB::table('rutas')
            ->insertGetId([
                'region_id' => $regionId,
                'codigo' => 'RUTA-TEST-01',
                'nombre' => 'Ruta Prueba',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('asignaciones_promotor_ruta')
            ->insert([
                'promotor_usuario_id' =>
                    $this->promotor->id,

                'ruta_id' =>
                    $this->rutaId,

                'fecha_inicio' =>
                    today()
                        ->subDay()
                        ->toDateString(),

                'fecha_fin' => null,

                'estado' => true,

                /*
                 * Campo obligatorio de la tabla.
                 * Representa al usuario que realizó
                 * la asignación.
                 */
                'asignado_por' =>
                    $this->promotor->id,

                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $agenteUsuarioId = DB::table('usuarios')
            ->insertGetId([
                'rol_id' => $rolAgenteId,
                'usuario' => 'agente_prueba',
                'password' =>
                    Hash::make('AgentePrueba123*'),
                'estado' => 'ACTIVO',
                'requiere_cambio_password' => false,
                'fecha_ultimo_cambio_password' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('datos_personales')
            ->insert([
                'usuario_id' => $agenteUsuarioId,
                'nombres' => 'Agente',
                'apellidos' => 'Prueba',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $this->agenteUsuario =
            Usuario::findOrFail(
                $agenteUsuarioId
            );

        $this->agenteId = DB::table('agentes')
            ->insertGetId([
                'usuario_id' =>
                    $this->agenteUsuario->id,

                'ruta_id' =>
                    $this->rutaId,

                'codigo_agente' =>
                    'AG-TEST-001',

                'nombre_negocio' =>
                    'Agencia de Prueba',

                'nombre_propietario' =>
                    'Propietario Prueba',

                'direccion' =>
                    'Dirección de prueba',

                'estado' => 'ACTIVO',

                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function crearArqueo(
        array $overrides = []
    ): Arqueo {
        static $contador = 1;

        $datos = array_merge(
            [
                'numero_arqueo' =>
                    'TEST-ANUL-'
                    . str_pad(
                        (string) $contador++,
                        4,
                        '0',
                        STR_PAD_LEFT
                    ),

                'agente_id' =>
                    $this->agenteId,

                'creado_por' =>
                    $this->agenteUsuario->id,

                'habilitacion_atrasada_id' =>
                    null,

                'tipo' =>
                    'DIARIO_AGENTE',

                'estado' =>
                    'PENDIENTE_CERTIFICACION',

                'fecha_arqueo' =>
                    today(),

                'hora_inicio' =>
                    now()->subMinutes(10),

                'hora_fin' =>
                    now(),

                'fuera_fecha_ordinaria' =>
                    false,

                'codigo_agente_historico' =>
                    'AG-TEST-001',

                'nombre_negocio_historico' =>
                    'Agencia de Prueba',

                'nombre_propietario_historico' =>
                    'Propietario Prueba',

                'direccion_historica' =>
                    'Dirección de prueba',

                'ruta_historica' =>
                    'Ruta Prueba',

                'region_historica' =>
                    'Región Prueba',

                'total_billetes' =>
                    1000,

                'total_monedas' =>
                    50,

                'total_arqueado' =>
                    1050,

                'saldo_sistema' =>
                    1050,

                'diferencia' =>
                    0,

                'certificacion' =>
                    null,

                'observaciones' =>
                    null,

                'pendiente_certificacion_at' =>
                    now(),

                'certificado_at' =>
                    null,

                'anulado_at' =>
                    null,
            ],
            $overrides
        );

        return Arqueo::create($datos);
    }
}
