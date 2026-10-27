<?php

namespace Tests\Feature\Agente;

use App\Models\Role;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArqueoExtemporaneoTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuarioAgente;
    private Usuario $usuarioAutorizador;
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
            'usuario' => 'agente.extemporaneo',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        /** @var Usuario $usuarioAutorizador */
        $usuarioAutorizador = Usuario::factory()->create([
            'rol_id' => $rolPromotor->id,
            'usuario' => 'promotor.autorizador',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        $this->usuarioAgente = $usuarioAgente;
        $this->usuarioAutorizador = $usuarioAutorizador;

        DB::table('datos_personales')->insert([
            [
                'usuario_id' => $usuarioAgente->id,
                'nombres' => 'Agente',
                'apellidos' => 'Extemporaneo',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $usuarioAutorizador->id,
                'nombres' => 'Promotor',
                'apellidos' => 'Autorizador',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $regionId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región Extemporánea',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rutaId = DB::table('rutas')->insertGetId([
            'region_id' => $regionId,
            'codigo' => 'RUTA-EXT-01',
            'nombre' => 'Ruta Extemporánea',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->agenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $usuarioAgente->id,
            'ruta_id' => $rutaId,
            'codigo_agente' => 'AG-EXT-001',
            'nombre_negocio' => 'Negocio Extemporáneo',
            'nombre_propietario' => 'Propietario Extemporáneo',
            'direccion' => 'Dirección Extemporánea',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /** @var Usuario $otroUsuario */
        $otroUsuario = Usuario::factory()->create([
            'rol_id' => $rolAgente->id,
            'usuario' => 'otro.agente',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        DB::table('datos_personales')->insert([
            'usuario_id' => $otroUsuario->id,
            'nombres' => 'Otro',
            'apellidos' => 'Agente',
            'telefono' => null,
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->otroAgenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $otroUsuario->id,
            'ruta_id' => $rutaId,
            'codigo_agente' => 'AG-EXT-002',
            'nombre_negocio' => 'Otro Negocio',
            'nombre_propietario' => 'Otro Propietario',
            'direccion' => 'Otra Dirección',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_habilitacion_pendiente_permite_crear_arqueo_extemporaneo(): void
    {
        $fechaAutorizada = today()
            ->subDays(3)
            ->toDateString();

        $habilitacionId = $this->crearHabilitacion(
            agenteId: $this->agenteId,
            fechaAutorizada: $fechaAutorizada
        );

        $responseCreate = $this
            ->actingAs($this->usuarioAgente)
            ->get(
                route(
                    'agente.arqueos.create',
                    [
                        'habilitacion' => $habilitacionId,
                    ]
                )
            );

        $responseCreate->assertOk();

        $response = $this
            ->actingAs($this->usuarioAgente)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 200.00,
                    habilitacionId: $habilitacionId
                )
            );

        $response->assertRedirect(
            route('agente.arqueos-extemporaneos.index')
        );

        $this->assertDatabaseHas('arqueos', [
            'agente_id' => $this->agenteId,
            'creado_por' => $this->usuarioAgente->id,
            'tipo' => 'DIARIO_AGENTE',
            'estado' => 'PENDIENTE_CERTIFICACION',
            'fuera_fecha_ordinaria' => true,
            'total_arqueado' => 200.00,
            'saldo_sistema' => 200.00,
            'diferencia' => 0.00,
        ]);

        $arqueo = DB::table('arqueos')
            ->where('agente_id', $this->agenteId)
            ->where('tipo', 'DIARIO_AGENTE')
            ->first();

        $this->assertNotNull($arqueo);

        $this->assertSame(
            $fechaAutorizada,
            Carbon::parse($arqueo->fecha_arqueo)
                ->format('Y-m-d')
        );

        $this->assertDatabaseHas(
            'habilitaciones_arqueos_atrasados',
            [
                'id' => $habilitacionId,
                'estado' => 'UTILIZADA',
            ]
        );

        $habilitacion = DB::table(
            'habilitaciones_arqueos_atrasados'
        )
            ->where('id', $habilitacionId)
            ->first();

        $this->assertNotNull($habilitacion);
        $this->assertNotNull($habilitacion->utilizado_at);
    }

    public function test_arqueo_extemporaneo_usa_exactamente_la_fecha_autorizada(): void
    {
        $fechaAutorizada = today()
            ->subDays(10)
            ->toDateString();

        $habilitacionId = $this->crearHabilitacion(
            agenteId: $this->agenteId,
            fechaAutorizada: $fechaAutorizada
        );

        $this->iniciarArqueoExtemporaneo(
            $habilitacionId
        );

        $this
            ->actingAs($this->usuarioAgente)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 200.00,
                    habilitacionId: $habilitacionId
                )
            );

        $arqueo = DB::table('arqueos')
            ->where('agente_id', $this->agenteId)
            ->where('fuera_fecha_ordinaria', true)
            ->first();

        $this->assertNotNull($arqueo);

        $this->assertSame(
            $fechaAutorizada,
            Carbon::parse($arqueo->fecha_arqueo)
                ->format('Y-m-d')
        );

        $this->assertSame(
            0,
            DB::table('arqueos')
                ->where('agente_id', $this->agenteId)
                ->where('fuera_fecha_ordinaria', true)
                ->whereDate('fecha_arqueo', today())
                ->count()
        );
    }

    public function test_no_permite_reutilizar_habilitacion_utilizada(): void
    {
        $fechaAutorizada = today()
            ->subDays(5)
            ->toDateString();

        $habilitacionId = $this->crearHabilitacion(
            agenteId: $this->agenteId,
            fechaAutorizada: $fechaAutorizada
        );

        $this->iniciarArqueoExtemporaneo(
            $habilitacionId
        );

        $this
            ->actingAs($this->usuarioAgente)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 200.00,
                    habilitacionId: $habilitacionId
                )
            );

        $segundoIntento = $this
            ->actingAs($this->usuarioAgente)
            ->get(
                route(
                    'agente.arqueos.create',
                    [
                        'habilitacion' => $habilitacionId,
                    ]
                )
            );

        $segundoIntento->assertRedirect(
            route('agente.arqueos-extemporaneos.index')
        );

        $segundoIntento->assertSessionHasErrors([
            'habilitacion',
        ]);

        $this->assertSame(
            1,
            DB::table('arqueos')
                ->where('agente_id', $this->agenteId)
                ->whereDate(
                    'fecha_arqueo',
                    $fechaAutorizada
                )
                ->where('estado', '!=', 'ANULADO')
                ->count()
        );
    }

    public function test_no_permite_usar_habilitacion_cancelada(): void
    {
        $habilitacionId = $this->crearHabilitacion(
            agenteId: $this->agenteId,
            fechaAutorizada: today()
                ->subDays(4)
                ->toDateString(),
            estado: 'CANCELADA'
        );

        $response = $this
            ->actingAs($this->usuarioAgente)
            ->get(
                route(
                    'agente.arqueos.create',
                    [
                        'habilitacion' => $habilitacionId,
                    ]
                )
            );

        $response->assertRedirect(
            route('agente.arqueos-extemporaneos.index')
        );

        $response->assertSessionHasErrors([
            'habilitacion',
        ]);

        $this->assertDatabaseCount(
            'arqueos',
            0
        );
    }

    public function test_no_permite_usar_habilitacion_de_otro_agente(): void
    {
        $habilitacionId = $this->crearHabilitacion(
            agenteId: $this->otroAgenteId,
            fechaAutorizada: today()
                ->subDays(2)
                ->toDateString()
        );

        $response = $this
            ->actingAs($this->usuarioAgente)
            ->get(
                route(
                    'agente.arqueos.create',
                    [
                        'habilitacion' => $habilitacionId,
                    ]
                )
            );

        $response->assertRedirect(
            route('agente.arqueos-extemporaneos.index')
        );

        $response->assertSessionHasErrors([
            'habilitacion',
        ]);

        $this->assertDatabaseCount(
            'arqueos',
            0
        );
    }

    public function test_no_permite_habilitacion_inexistente(): void
    {
        $response = $this
            ->actingAs($this->usuarioAgente)
            ->get(
                route(
                    'agente.arqueos.create',
                    [
                        'habilitacion' => 999999,
                    ]
                )
            );

        $response->assertRedirect(
            route('agente.arqueos-extemporaneos.index')
        );

        $response->assertSessionHasErrors([
            'habilitacion',
        ]);
    }

    public function test_no_permite_duplicado_en_fecha_autorizada(): void
    {
        $fechaAutorizada = today()
            ->subDays(7)
            ->toDateString();

        DB::table('arqueos')->insert([
            'numero_arqueo' => 'ARQ-EXT-DUP-001',
            'agente_id' => $this->agenteId,
            'creado_por' => $this->usuarioAgente->id,
            'habilitacion_atrasada_id' => null,
            'tipo' => 'DIARIO_AGENTE',
            'estado' => 'PENDIENTE_CERTIFICACION',
            'fecha_arqueo' => $fechaAutorizada,
            'hora_inicio' => now(),
            'hora_fin' => now(),
            'fuera_fecha_ordinaria' => true,
            'codigo_agente_historico' => 'AG-EXT-001',
            'nombre_negocio_historico' =>
                'Negocio Extemporáneo',
            'nombre_propietario_historico' =>
                'Propietario Extemporáneo',
            'direccion_historica' =>
                'Dirección Extemporánea',
            'ruta_historica' =>
                'Ruta Extemporánea',
            'region_historica' =>
                'Región Extemporánea',
            'total_billetes' => 200.00,
            'total_monedas' => 0.00,
            'total_arqueado' => 200.00,
            'saldo_sistema' => 200.00,
            'diferencia' => 0.00,
            'certificacion' =>
                'Arqueo previo para validar duplicado.',
            'observaciones' => null,
            'pendiente_certificacion_at' => now(),
            'certificado_at' => null,
            'anulado_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $habilitacionId = $this->crearHabilitacion(
            agenteId: $this->agenteId,
            fechaAutorizada: $fechaAutorizada
        );

        $response = $this
            ->actingAs($this->usuarioAgente)
            ->get(
                route(
                    'agente.arqueos.create',
                    [
                        'habilitacion' => $habilitacionId,
                    ]
                )
            );

        $response->assertRedirect(
            route('agente.arqueos-extemporaneos.index')
        );

        $response->assertSessionHas(
            'warning'
        );

        $this->assertSame(
            1,
            DB::table('arqueos')
                ->where('agente_id', $this->agenteId)
                ->whereDate(
                    'fecha_arqueo',
                    $fechaAutorizada
                )
                ->where('estado', '!=', 'ANULADO')
                ->count()
        );
    }

    private function crearHabilitacion(
        int $agenteId,
        string $fechaAutorizada,
        string $estado = 'PENDIENTE'
    ): int {
        return DB::table(
            'habilitaciones_arqueos_atrasados'
        )->insertGetId([
            'agente_id' => $agenteId,
            'fecha_autorizada' => $fechaAutorizada,
            'motivo' =>
                'Habilitación creada mediante prueba automatizada.',
            'autorizado_por' =>
                $this->usuarioAutorizador->id,
            'autorizado_at' => now(),
            'estado' => $estado,
            'utilizado_at' =>
                $estado === 'UTILIZADA'
                    ? now()
                    : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function iniciarArqueoExtemporaneo(
        int $habilitacionId
    ): void {
        $this
            ->actingAs($this->usuarioAgente)
            ->get(
                route(
                    'agente.arqueos.create',
                    [
                        'habilitacion' =>
                            $habilitacionId,
                    ]
                )
            )
            ->assertOk();
    }

    private function datosArqueo(
        float $saldoSistema,
        int $habilitacionId
    ): array {
        return [
            'nombre_propietario' =>
                'Propietario Extemporáneo',

            'saldo_sistema' =>
                $saldoSistema,

            'detalle' => json_encode(
                [
                    [
                        'tipo' => 'BILLETE',
                        'denominacion' => 100,
                        'cantidad' => 2,
                    ],
                ],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ),

            'certificacion' =>
                'Certifico que la información del arqueo extemporáneo es correcta.',

            'observaciones' =>
                'Arqueo extemporáneo generado mediante prueba automatizada.',

            'habilitacion_id' =>
                $habilitacionId,
        ];
    }
}
