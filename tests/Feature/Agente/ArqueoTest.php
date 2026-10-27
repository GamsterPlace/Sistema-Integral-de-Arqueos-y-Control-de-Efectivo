<?php

namespace Tests\Feature\Agente;

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArqueoTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuario;
    private int $agenteId;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Role $rol */
        $rol = Role::factory()->create([
            'nombre' => 'agente',
            'estado' => true,
        ]);

        /** @var Usuario $usuario */
        $usuario = Usuario::factory()->create([
            'rol_id' => $rol->id,
            'usuario' => 'agente.prueba',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        $this->usuario = $usuario;

        DB::table('datos_personales')->insert([
            'usuario_id' => $usuario->id,
            'nombres' => 'Agente',
            'apellidos' => 'Prueba',
            'telefono' => null,
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regionId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región de Prueba',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rutaId = DB::table('rutas')->insertGetId([
            'region_id' => $regionId,
            'codigo' => 'RUTA-TEST',
            'nombre' => 'Ruta de Prueba',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->agenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $usuario->id,
            'ruta_id' => $rutaId,
            'codigo_agente' => 'AG-TEST-001',
            'nombre_negocio' => 'Agente Comercial de Prueba',
            'nombre_propietario' => 'Propietario Prueba',
            'direccion' => 'Dirección de prueba',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_agente_puede_crear_arqueo_diario(): void
    {
        $responseCreate = $this
            ->actingAs($this->usuario)
            ->get(route('agente.arqueos.create'));

        $responseCreate->assertOk();

        $response = $this
            ->actingAs($this->usuario)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 350.00,
                    detalle: [
                        [
                            'tipo' => 'BILLETE',
                            'denominacion' => 100,
                            'cantidad' => 3,
                        ],
                        [
                            'tipo' => 'MONEDA',
                            'denominacion' => 1,
                            'cantidad' => 50,
                        ],
                    ]
                )
            );

        $response->assertRedirect(
            route('agente.arqueos.index')
        );

        $this->assertDatabaseHas('arqueos', [
            'agente_id' => $this->agenteId,
            'creado_por' => $this->usuario->id,
            'tipo' => 'DIARIO_AGENTE',
            'estado' => 'PENDIENTE_CERTIFICACION',
            'total_billetes' => 300.00,
            'total_monedas' => 50.00,
            'total_arqueado' => 350.00,
            'saldo_sistema' => 350.00,
            'diferencia' => 0.00,
            'fuera_fecha_ordinaria' => false,
        ]);
    }

    public function test_calcula_correctamente_billetes_y_monedas(): void
    {
        $this->iniciarArqueo();

        $this
            ->actingAs($this->usuario)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 682.00,
                    detalle: [
                        [
                            'tipo' => 'BILLETE',
                            'denominacion' => 200,
                            'cantidad' => 2,
                        ],
                        [
                            'tipo' => 'BILLETE',
                            'denominacion' => 100,
                            'cantidad' => 2,
                        ],
                        [
                            'tipo' => 'BILLETE',
                            'denominacion' => 20,
                            'cantidad' => 3,
                        ],
                        [
                            'tipo' => 'MONEDA',
                            'denominacion' => 1,
                            'cantidad' => 10,
                        ],
                        [
                            'tipo' => 'MONEDA',
                            'denominacion' => 0.50,
                            'cantidad' => 20,
                        ],
                        [
                            'tipo' => 'MONEDA',
                            'denominacion' => 0.25,
                            'cantidad' => 8,
                        ],
                    ]
                )
            )
            ->assertRedirect(
                route('agente.arqueos.index')
            );

        $this->assertDatabaseHas('arqueos', [
            'agente_id' => $this->agenteId,
            'total_billetes' => 660.00,
            'total_monedas' => 22.00,
            'total_arqueado' => 682.00,
            'saldo_sistema' => 682.00,
            'diferencia' => 0.00,
        ]);

        $arqueoId = (int) DB::table('arqueos')
            ->where('agente_id', $this->agenteId)
            ->value('id');

        $this->assertDatabaseHas('arqueo_detalles', [
            'arqueo_id' => $arqueoId,
            'tipo' => 'BILLETE',
            'denominacion' => 200.00,
            'cantidad' => 2,
            'subtotal' => 400.00,
        ]);

        $this->assertDatabaseHas('arqueo_detalles', [
            'arqueo_id' => $arqueoId,
            'tipo' => 'MONEDA',
            'denominacion' => 0.25,
            'cantidad' => 8,
            'subtotal' => 2.00,
        ]);
    }

    public function test_arqueo_exacto_genera_diferencia_cero(): void
    {
        $this->iniciarArqueo();

        $this
            ->actingAs($this->usuario)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 500.00,
                    detalle: [
                        [
                            'tipo' => 'BILLETE',
                            'denominacion' => 100,
                            'cantidad' => 5,
                        ],
                    ]
                )
            );

        $this->assertDatabaseHas('arqueos', [
            'agente_id' => $this->agenteId,
            'total_arqueado' => 500.00,
            'saldo_sistema' => 500.00,
            'diferencia' => 0.00,
        ]);
    }

    public function test_arqueo_con_sobrante_genera_diferencia_positiva(): void
    {
        $this->iniciarArqueo();

        $this
            ->actingAs($this->usuario)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 450.00,
                    detalle: [
                        [
                            'tipo' => 'BILLETE',
                            'denominacion' => 100,
                            'cantidad' => 5,
                        ],
                    ]
                )
            );

        $this->assertDatabaseHas('arqueos', [
            'agente_id' => $this->agenteId,
            'total_arqueado' => 500.00,
            'saldo_sistema' => 450.00,
            'diferencia' => 50.00,
        ]);
    }

    public function test_arqueo_con_faltante_genera_diferencia_negativa(): void
    {
        $this->iniciarArqueo();

        $this
            ->actingAs($this->usuario)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 550.00,
                    detalle: [
                        [
                            'tipo' => 'BILLETE',
                            'denominacion' => 100,
                            'cantidad' => 5,
                        ],
                    ]
                )
            );

        $this->assertDatabaseHas('arqueos', [
            'agente_id' => $this->agenteId,
            'total_arqueado' => 500.00,
            'saldo_sistema' => 550.00,
            'diferencia' => -50.00,
        ]);
    }

    public function test_creacion_del_arqueo_genera_firma_realizador(): void
    {
        $this->iniciarArqueo();

        $this
            ->actingAs($this->usuario)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 100.00,
                    detalle: [
                        [
                            'tipo' => 'BILLETE',
                            'denominacion' => 100,
                            'cantidad' => 1,
                        ],
                    ]
                )
            );

        $arqueoId = (int) DB::table('arqueos')
            ->where('agente_id', $this->agenteId)
            ->value('id');

        $this->assertGreaterThan(0, $arqueoId);

        $this->assertDatabaseHas('firmas_arqueos', [
            'arqueo_id' => $arqueoId,
            'usuario_id' => $this->usuario->id,
            'tipo_firma' => 'REALIZADOR',
            'rol_firmante' => 'agente',
            'valida' => true,
        ]);

        $firma = DB::table('firmas_arqueos')
            ->where('arqueo_id', $arqueoId)
            ->where('tipo_firma', 'REALIZADOR')
            ->first();

        $this->assertNotNull($firma);
        $this->assertSame(64, strlen($firma->hash_documento));
        $this->assertSame(64, strlen($firma->firma_electronica));
        $this->assertSame('HMAC-SHA256', $firma->algoritmo);
    }

    public function test_no_permite_crear_dos_arqueos_validos_el_mismo_dia(): void
    {
        $this->iniciarArqueo();

        $this
            ->actingAs($this->usuario)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 100.00,
                    detalle: [
                        [
                            'tipo' => 'BILLETE',
                            'denominacion' => 100,
                            'cantidad' => 1,
                        ],
                    ]
                )
            )
            ->assertRedirect(
                route('agente.arqueos.index')
            );

        $segundoIntento = $this
            ->actingAs($this->usuario)
            ->get(route('agente.arqueos.create'));

        $segundoIntento->assertRedirect(
            route('agente.arqueos.index')
        );

        $segundoIntento->assertSessionHas(
            'warning'
        );

        $this->assertSame(
            1,
            DB::table('arqueos')
                ->where('agente_id', $this->agenteId)
                ->where('tipo', 'DIARIO_AGENTE')
                ->whereDate('fecha_arqueo', today())
                ->where('estado', '!=', 'ANULADO')
                ->count()
        );
    }

    public function test_rechaza_denominacion_no_permitida(): void
    {
        $this->iniciarArqueo();

        $response = $this
            ->actingAs($this->usuario)
            ->post(
                route('agente.arqueos.store'),
                $this->datosArqueo(
                    saldoSistema: 500.00,
                    detalle: [
                        [
                            'tipo' => 'BILLETE',
                            'denominacion' => 500,
                            'cantidad' => 1,
                        ],
                    ]
                )
            );

        $response->assertSessionHasErrors([
            'detalle',
        ]);

        $this->assertDatabaseCount(
            'arqueos',
            0
        );
    }

    private function iniciarArqueo(): void
    {
        $this
            ->actingAs($this->usuario)
            ->get(route('agente.arqueos.create'))
            ->assertOk();
    }

    private function datosArqueo(
        float $saldoSistema,
        array $detalle
    ): array {
        return [
            'nombre_propietario' => 'Propietario Prueba',
            'saldo_sistema' => $saldoSistema,
            'detalle' => json_encode(
                $detalle,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ),
            'certificacion' =>
                'Certifico que los valores registrados son correctos.',
            'observaciones' =>
                'Arqueo generado mediante prueba automatizada.',
            'habilitacion_id' => null,
        ];
    }
}
