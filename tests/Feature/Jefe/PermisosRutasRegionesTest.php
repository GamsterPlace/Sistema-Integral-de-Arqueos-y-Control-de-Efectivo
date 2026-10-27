<?php

namespace Tests\Feature\Jefe;

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PermisosRutasRegionesTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $jefe;
    private Usuario $promotor;
    private Usuario $agenteUsuario;

    private int $regionOrigenId;
    private int $regionDestinoId;
    private int $rutaOrigenId;
    private int $rutaDestinoId;
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
            'usuario' => 'jefe.rutas.regiones',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        /** @var Usuario $promotor */
        $promotor = Usuario::factory()->create([
            'rol_id' => $rolPromotor->id,
            'usuario' => 'promotor.rutas.regiones',
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        /** @var Usuario $agenteUsuario */
        $agenteUsuario = Usuario::factory()->create([
            'rol_id' => $rolAgente->id,
            'usuario' => 'agente.rutas.regiones',
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
                'apellidos' => 'Rutas Regiones',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $promotor->id,
                'nombres' => 'Promotor',
                'apellidos' => 'Rutas Regiones',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => $agenteUsuario->id,
                'nombres' => 'Agente',
                'apellidos' => 'Rutas Regiones',
                'telefono' => null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->regionOrigenId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región Origen Test',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->regionDestinoId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región Destino Test',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->rutaOrigenId = DB::table('rutas')->insertGetId([
            'region_id' => $this->regionOrigenId,
            'codigo' => 'RUTA-ORIGEN-TEST',
            'nombre' => 'Ruta Origen Test',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->rutaDestinoId = DB::table('rutas')->insertGetId([
            'region_id' => $this->regionDestinoId,
            'codigo' => 'RUTA-DESTINO-TEST',
            'nombre' => 'Ruta Destino Test',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->agenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $agenteUsuario->id,
            'ruta_id' => $this->rutaOrigenId,
            'codigo_agente' => 'AG-RR-001',
            'nombre_negocio' => 'Negocio Rutas Regiones',
            'nombre_propietario' => 'Propietario Test',
            'direccion' => 'Dirección Test',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_jefe_puede_crear_ruta_en_region_activa(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.rutas.store'),
                [
                    'region_id' => $this->regionOrigenId,
                    'codigo' => 'RUTA-NUEVA-001',
                    'nombre' => 'Ruta Nueva Automatizada',
                ]
            );

        $response->assertRedirect(
            route('jefe.rutas.index')
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rutas', [
            'region_id' => $this->regionOrigenId,
            'codigo' => 'RUTA-NUEVA-001',
            'nombre' => 'Ruta Nueva Automatizada',
            'estado' => true,
        ]);
    }

    public function test_jefe_puede_editar_y_cambiar_region_de_ruta(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->put(
                route(
                    'jefe.rutas.update',
                    $this->rutaOrigenId
                ),
                [
                    'region_id' => $this->regionDestinoId,
                    'codigo' => 'RUTA-ORIGEN-EDITADA',
                    'nombre' => 'Ruta Origen Editada',
                ]
            );

        $response->assertRedirect(
            route('jefe.rutas.index')
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rutas', [
            'id' => $this->rutaOrigenId,
            'region_id' => $this->regionDestinoId,
            'codigo' => 'RUTA-ORIGEN-EDITADA',
            'nombre' => 'Ruta Origen Editada',
        ]);
    }

    public function test_no_permite_crear_ruta_en_region_inactiva(): void
    {
        $regionInactivaId = DB::table('regiones')
            ->insertGetId([
                'nombre' => 'Región Inactiva Crear Ruta',
                'estado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $response = $this
            ->actingAs($this->jefe)
            ->from(route('jefe.rutas.index'))
            ->post(
                route('jefe.rutas.store'),
                [
                    'region_id' => $regionInactivaId,
                    'codigo' => 'RUTA-INACTIVA-001',
                    'nombre' => 'Ruta Región Inactiva',
                ]
            );

        $response->assertRedirect(
            route('jefe.rutas.index')
        );

        $response->assertSessionHas('warning');

        $this->assertDatabaseMissing('rutas', [
            'codigo' => 'RUTA-INACTIVA-001',
        ]);
    }

    public function test_jefe_puede_reasignar_agente_entre_rutas_activas(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->patch(
                route(
                    'jefe.rutas.agentes.reasignar',
                    $this->rutaOrigenId
                ),
                [
                    'agentes' => [
                        $this->agenteId,
                    ],
                    'ruta_destino_id' =>
                        $this->rutaDestinoId,
                ]
            );

        $response->assertRedirect(
            route(
                'jefe.rutas.agentes',
                $this->rutaOrigenId
            )
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('agentes', [
            'id' => $this->agenteId,
            'ruta_id' => $this->rutaDestinoId,
        ]);
    }

    public function test_no_permite_reasignar_agente_a_ruta_inactiva(): void
    {
        $rutaInactivaId = DB::table('rutas')
            ->insertGetId([
                'region_id' => $this->regionDestinoId,
                'codigo' => 'RUTA-INACTIVA-DEST',
                'nombre' => 'Ruta Inactiva Destino',
                'estado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $response = $this
            ->actingAs($this->jefe)
            ->from(
                route(
                    'jefe.rutas.agentes',
                    $this->rutaOrigenId
                )
            )
            ->patch(
                route(
                    'jefe.rutas.agentes.reasignar',
                    $this->rutaOrigenId
                ),
                [
                    'agentes' => [
                        $this->agenteId,
                    ],
                    'ruta_destino_id' =>
                        $rutaInactivaId,
                ]
            );

        $response->assertRedirect(
            route(
                'jefe.rutas.agentes',
                $this->rutaOrigenId
            )
        );

        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('agentes', [
            'id' => $this->agenteId,
            'ruta_id' => $this->rutaOrigenId,
        ]);
    }

    public function test_no_permite_reasignar_agente_que_no_pertenece_a_ruta_origen(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->from(
                route(
                    'jefe.rutas.agentes',
                    $this->rutaDestinoId
                )
            )
            ->patch(
                route(
                    'jefe.rutas.agentes.reasignar',
                    $this->rutaDestinoId
                ),
                [
                    'agentes' => [
                        $this->agenteId,
                    ],
                    'ruta_destino_id' =>
                        $this->rutaOrigenId,
                ]
            );

        $response->assertRedirect(
            route(
                'jefe.rutas.agentes',
                $this->rutaDestinoId
            )
        );

        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('agentes', [
            'id' => $this->agenteId,
            'ruta_id' => $this->rutaOrigenId,
        ]);
    }

    public function test_no_permite_desactivar_ruta_con_agentes_activos(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->patch(
                route(
                    'jefe.rutas.estado',
                    $this->rutaOrigenId
                )
            );

        $response->assertRedirect(
            route('jefe.rutas.index')
        );

        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('rutas', [
            'id' => $this->rutaOrigenId,
            'estado' => true,
        ]);
    }

    public function test_no_permite_desactivar_ruta_con_promotor_asignado(): void
    {
        DB::table('agentes')
            ->where('id', $this->agenteId)
            ->update([
                'estado' => 'INACTIVO',
                'updated_at' => now(),
            ]);

        DB::table('asignaciones_promotor_ruta')->insert([
            'promotor_usuario_id' => $this->promotor->id,
            'ruta_id' => $this->rutaOrigenId,
            'fecha_inicio' => today()->subDay()->toDateString(),
            'fecha_fin' => null,
            'estado' => true,
            'asignado_por' => $this->jefe->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($this->jefe)
            ->patch(
                route(
                    'jefe.rutas.estado',
                    $this->rutaOrigenId
                )
            );

        $response->assertRedirect(
            route('jefe.rutas.index')
        );

        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('rutas', [
            'id' => $this->rutaOrigenId,
            'estado' => true,
        ]);
    }

    public function test_jefe_puede_crear_region(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.regiones.store'),
                [
                    'nombre' =>
                        'Región Nueva Automatizada',
                ]
            );

        $response->assertRedirect(
            route('jefe.regiones.index')
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('regiones', [
            'nombre' => 'Región Nueva Automatizada',
            'estado' => true,
        ]);
    }

    public function test_jefe_puede_editar_region(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->put(
                route(
                    'jefe.regiones.update',
                    $this->regionOrigenId
                ),
                [
                    'nombre' =>
                        'Región Origen Modificada',
                ]
            );

        $response->assertRedirect(
            route('jefe.regiones.index')
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('regiones', [
            'id' => $this->regionOrigenId,
            'nombre' => 'Región Origen Modificada',
        ]);
    }

    public function test_jefe_puede_reasignar_ruta_entre_regiones_activas(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->patch(
                route(
                    'jefe.regiones.rutas.reasignar',
                    $this->regionOrigenId
                ),
                [
                    'rutas' => [
                        $this->rutaOrigenId,
                    ],
                    'region_destino_id' =>
                        $this->regionDestinoId,
                ]
            );

        $response->assertRedirect(
            route(
                'jefe.regiones.rutas',
                $this->regionOrigenId
            )
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rutas', [
            'id' => $this->rutaOrigenId,
            'region_id' => $this->regionDestinoId,
        ]);
    }

    public function test_no_permite_reasignar_ruta_a_region_inactiva(): void
    {
        $regionInactivaId = DB::table('regiones')
            ->insertGetId([
                'nombre' =>
                    'Región Inactiva Destino',
                'estado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $response = $this
            ->actingAs($this->jefe)
            ->from(
                route(
                    'jefe.regiones.rutas',
                    $this->regionOrigenId
                )
            )
            ->patch(
                route(
                    'jefe.regiones.rutas.reasignar',
                    $this->regionOrigenId
                ),
                [
                    'rutas' => [
                        $this->rutaOrigenId,
                    ],
                    'region_destino_id' =>
                        $regionInactivaId,
                ]
            );

        $response->assertRedirect(
            route(
                'jefe.regiones.rutas',
                $this->regionOrigenId
            )
        );

        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('rutas', [
            'id' => $this->rutaOrigenId,
            'region_id' => $this->regionOrigenId,
        ]);
    }

    public function test_no_permite_reasignar_ruta_que_no_pertenece_a_region_origen(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->from(
                route(
                    'jefe.regiones.rutas',
                    $this->regionOrigenId
                )
            )
            ->patch(
                route(
                    'jefe.regiones.rutas.reasignar',
                    $this->regionOrigenId
                ),
                [
                    'rutas' => [
                        $this->rutaDestinoId,
                    ],
                    'region_destino_id' =>
                        $this->regionDestinoId,
                ]
            );

        $response->assertRedirect(
            route(
                'jefe.regiones.rutas',
                $this->regionOrigenId
            )
        );

        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('rutas', [
            'id' => $this->rutaDestinoId,
            'region_id' => $this->regionDestinoId,
        ]);
    }

    public function test_no_permite_desactivar_region_con_rutas_activas(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->patch(
                route(
                    'jefe.regiones.estado',
                    $this->regionOrigenId
                )
            );

        $response->assertRedirect(
            route('jefe.regiones.index')
        );

        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('regiones', [
            'id' => $this->regionOrigenId,
            'estado' => true,
        ]);
    }

    public function test_operaciones_de_rutas_generan_auditoria(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.rutas.store'),
                [
                    'region_id' => $this->regionOrigenId,
                    'codigo' => 'RUTA-AUD-001',
                    'nombre' => 'Ruta Auditoría',
                ]
            );

        $response->assertRedirect(
            route('jefe.rutas.index')
        );

        $rutaId = DB::table('rutas')
            ->where('codigo', 'RUTA-AUD-001')
            ->value('id');

        $this->assertNotNull($rutaId);

        $this->assertDatabaseHas('auditoria', [
            'usuario_id' => $this->jefe->id,
            'modulo' => 'Rutas',
            'accion' => 'CREAR_RUTA',
            'tabla_afectada' => 'rutas',
            'registro_id' => $rutaId,
        ]);
    }

    public function test_operaciones_de_regiones_generan_auditoria(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->post(
                route('jefe.regiones.store'),
                [
                    'nombre' => 'Región Auditoría',
                ]
            );

        $response->assertRedirect(
            route('jefe.regiones.index')
        );

        $regionId = DB::table('regiones')
            ->where(
                'nombre',
                'Región Auditoría'
            )
            ->value('id');

        $this->assertNotNull($regionId);

        $this->assertDatabaseHas('auditoria', [
            'usuario_id' => $this->jefe->id,
            'modulo' => 'Regiones',
            'accion' => 'CREAR_REGION',
            'tabla_afectada' => 'regiones',
            'registro_id' => $regionId,
        ]);
    }

    public function test_usuario_no_jefe_no_puede_gestionar_rutas(): void
    {
        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route('jefe.rutas.store'),
                [
                    'region_id' => $this->regionOrigenId,
                    'codigo' => 'RUTA-NO-AUTORIZADA',
                    'nombre' => 'Ruta No Autorizada',
                ]
            );

        $response->assertForbidden();

        $this->assertDatabaseMissing('rutas', [
            'codigo' => 'RUTA-NO-AUTORIZADA',
        ]);
    }

    public function test_usuario_no_jefe_no_puede_gestionar_regiones(): void
    {
        $response = $this
            ->actingAs($this->promotor)
            ->post(
                route('jefe.regiones.store'),
                [
                    'nombre' =>
                        'Región No Autorizada',
                ]
            );

        $response->assertForbidden();

        $this->assertDatabaseMissing('regiones', [
            'nombre' => 'Región No Autorizada',
        ]);
    }
}
