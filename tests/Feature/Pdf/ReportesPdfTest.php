<?php

namespace Tests\Feature\Pdf;

use App\Models\Arqueo;
use App\Models\Role;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportesPdfTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $jefe;
    private Usuario $auditor;
    private Usuario $otroAuditor;
    private Usuario $gerencia;
    private Usuario $administrador;
    private Usuario $promotor;
    private Usuario $agenteUsuario;

    private int $regionId;
    private int $rutaId;
    private int $agenteId;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(
            Carbon::create(2026, 9, 10, 16, 30, 0)
        );

        $rolJefe = Role::factory()->create([
            'nombre' => 'jefedeAgentes',
            'estado' => true,
        ]);

        $rolAuditoria = Role::factory()->create([
            'nombre' => 'Auditoria',
            'estado' => true,
        ]);

        $rolGerencia = Role::factory()->create([
            'nombre' => 'Gerencia',
            'estado' => true,
        ]);

        $rolAdministrador = Role::factory()->create([
            'nombre' => 'Administrador',
            'estado' => true,
        ]);

        $rolPromotor = Role::factory()->create([
            'nombre' => 'Promotor',
            'estado' => true,
        ]);

        $rolAgente = Role::factory()->create([
            'nombre' => 'agente',
            'estado' => true,
        ]);

        $this->jefe = $this->crearUsuario(
            $rolJefe,
            'jefe.reportes',
            'Jefe',
            'Reportes'
        );

        $this->auditor = $this->crearUsuario(
            $rolAuditoria,
            'auditor.reportes',
            'Auditor',
            'Reportes'
        );

        $this->otroAuditor = $this->crearUsuario(
            $rolAuditoria,
            'otro.auditor.reportes',
            'Otro',
            'Auditor'
        );

        $this->gerencia = $this->crearUsuario(
            $rolGerencia,
            'gerencia.reportes',
            'Gerencia',
            'Reportes'
        );

        $this->administrador = $this->crearUsuario(
            $rolAdministrador,
            'administrador.reportes',
            'Administrador',
            'Reportes'
        );

        $this->promotor = $this->crearUsuario(
            $rolPromotor,
            'promotor.reportes',
            'Promotor',
            'Reportes'
        );

        $this->agenteUsuario = $this->crearUsuario(
            $rolAgente,
            'agente.reportes',
            'Agente',
            'Reportes'
        );

        $this->regionId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región Reportes PDF',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->rutaId = DB::table('rutas')->insertGetId([
            'region_id' => $this->regionId,
            'codigo' => 'R-PDF-01',
            'nombre' => 'Ruta Reportes PDF',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->agenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $this->agenteUsuario->id,
            'ruta_id' => $this->rutaId,
            'codigo_agente' => 'AG-REP-001',
            'nombre_negocio' => 'Agente Reportes PDF',
            'nombre_propietario' => 'Propietario Reportes PDF',
            'direccion' => 'Dirección Reportes PDF',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            creadoPor: $this->agenteUsuario->id,
            diferencia: 0.00,
            fecha: '2026-09-08'
        );

        $this->crearArqueo(
            tipo: 'VISITA_PROMOTOR',
            creadoPor: $this->promotor->id,
            diferencia: -25.50,
            fecha: '2026-09-09'
        );

        $this->crearArqueo(
            tipo: 'VISITA_PROMOTOR',
            creadoPor: $this->promotor->id,
            diferencia: 30.75,
            fecha: '2026-09-10'
        );

        $this->crearArqueo(
            tipo: 'VISITA_AUDITORIA',
            creadoPor: $this->auditor->id,
            diferencia: -10.00,
            fecha: '2026-09-10'
        );

        /*
         * Esta visita pertenece a otro Auditor y permite verificar
         * que los reportes de Auditoría no mezclen visitas ajenas.
         */
        $this->crearArqueo(
            tipo: 'VISITA_AUDITORIA',
            creadoPor: $this->otroAuditor->id,
            diferencia: 50.00,
            fecha: '2026-09-10'
        );

        /*
         * Los reportes generales excluyen arqueos ANULADO.
         */
        $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            creadoPor: $this->agenteUsuario->id,
            diferencia: -100.00,
            fecha: '2026-09-10',
            estado: 'ANULADO'
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /*
    |--------------------------------------------------------------------------
    | JEFE DE AGENTES
    |--------------------------------------------------------------------------
    */

    public function test_jefe_puede_generar_reporte_pdf_resumen(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->get(route('jefe.reportes.imprimir', [
                'reporte' => 'resumen',
            ]));

        $this->assertPdfReal(
            $response,
            'reporte-resumen-20260910-163000.pdf'
        );
    }

    public function test_jefe_puede_generar_pdf_aplicando_filtros_reales(): void
    {
        $response = $this
            ->actingAs($this->jefe)
            ->get(route('jefe.reportes.imprimir', [
                'reporte' => 'faltantes',
                'agente_id' => $this->agenteId,
                'promotor_id' => $this->promotor->id,
                'region_id' => $this->regionId,
                'ruta_id' => $this->rutaId,
                'tipo' => 'VISITA_PROMOTOR',
                'desde' => '2026-09-01',
                'hasta' => '2026-09-10',
            ]));

        $this->assertPdfReal(
            $response,
            'reporte-faltantes-20260910-163000.pdf'
        );
    }

    public function test_otro_rol_no_puede_generar_reporte_pdf_de_jefe(): void
    {
        $response = $this
            ->actingAs($this->promotor)
            ->get(route('jefe.reportes.imprimir'));

        $response->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | AUDITORÍA
    |--------------------------------------------------------------------------
    */

    public function test_auditoria_puede_generar_su_reporte_pdf(): void
    {
        $response = $this
            ->actingAs($this->auditor)
            ->get(route('auditoria.reportes.imprimir', [
                'reporte' => 'resumen',
            ]));

        $this->assertPdfReal(
            $response,
            'reporte-resumen-20260910-163000.pdf'
        );
    }

    public function test_auditoria_puede_generar_reporte_pdf_con_filtros(): void
    {
        $response = $this
            ->actingAs($this->auditor)
            ->get(route('auditoria.reportes.imprimir', [
                'reporte' => 'faltantes',
                'agente_id' => $this->agenteId,
                'region_id' => $this->regionId,
                'ruta_id' => $this->rutaId,
                'desde' => '2026-09-01',
                'hasta' => '2026-09-10',
            ]));

        $this->assertPdfReal(
            $response,
            'reporte-faltantes-20260910-163000.pdf'
        );
    }

    public function test_otro_rol_no_puede_generar_reporte_pdf_de_auditoria(): void
    {
        $response = $this
            ->actingAs($this->promotor)
            ->get(route('auditoria.reportes.imprimir'));

        $response->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | GERENCIA
    |--------------------------------------------------------------------------
    */

    public function test_gerencia_puede_generar_reporte_pdf_resumen_ejecutivo(): void
    {
        $response = $this
            ->actingAs($this->gerencia)
            ->get(route('gerencia.reportes.imprimir', [
                'tipo_reporte' => 'RESUMEN_EJECUTIVO',
            ]));

        $this->assertPdfReal(
            $response,
            'reporte-gerencia-resumen-ejecutivo.pdf'
        );
    }

    public function test_gerencia_puede_generar_reporte_pdf_con_filtros(): void
    {
        $response = $this
            ->actingAs($this->gerencia)
            ->get(route('gerencia.reportes.imprimir', [
                'tipo_reporte' => 'FALTANTES',
                'desde' => '2026-09-01',
                'hasta' => '2026-09-10',
                'region_id' => $this->regionId,
                'ruta_id' => $this->rutaId,
                'agente_id' => $this->agenteId,
                'promotor_id' => $this->promotor->id,
                'tipo_arqueo' => 'VISITA_PROMOTOR',
            ]));

        $this->assertPdfReal(
            $response,
            'reporte-gerencia-faltantes.pdf'
        );
    }

    public function test_gerencia_tipo_de_reporte_invalido_usa_resumen_ejecutivo(): void
    {
        $response = $this
            ->actingAs($this->gerencia)
            ->get(route('gerencia.reportes.imprimir', [
                'tipo_reporte' => 'REPORTE_QUE_NO_EXISTE',
            ]));

        $this->assertPdfReal(
            $response,
            'reporte-gerencia-resumen-ejecutivo.pdf'
        );
    }

    public function test_otro_rol_no_puede_generar_reporte_pdf_de_gerencia(): void
    {
        $response = $this
            ->actingAs($this->promotor)
            ->get(route('gerencia.reportes.imprimir'));

        $response->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRADOR
    |--------------------------------------------------------------------------
    */

    public function test_administrador_tiene_ruta_para_imprimir_reportes_pdf(): void
    {
        $this->assertTrue(
            Route::has('administrador.reportes.imprimir'),
            'El ReporteController de Administrador implementa imprimir(), '
            . 'pero no existe la ruta administrador.reportes.imprimir.'
        );
    }

    public function test_administrador_puede_generar_reporte_pdf(): void
    {
        if (! Route::has('administrador.reportes.imprimir')) {
            $this->fail(
                'No existe la ruta administrador.reportes.imprimir. '
                . 'El controlador sí contiene el método imprimir().'
            );
        }

        $response = $this
            ->actingAs($this->administrador)
            ->get(route('administrador.reportes.imprimir', [
                'reporte' => 'resumen',
            ]));

        $this->assertPdfReal(
            $response,
            'reporte-administrativo-resumen-20260910-163000.pdf'
        );
    }

    public function test_administrador_puede_generar_reporte_pdf_con_filtros(): void
    {
        if (! Route::has('administrador.reportes.imprimir')) {
            $this->fail(
                'No existe la ruta administrador.reportes.imprimir. '
                . 'No es posible probar la impresión con filtros.'
            );
        }

        $response = $this
            ->actingAs($this->administrador)
            ->get(route('administrador.reportes.imprimir', [
                'reporte' => 'faltantes',
                'agente_id' => $this->agenteId,
                'promotor_id' => $this->promotor->id,
                'region_id' => $this->regionId,
                'ruta_id' => $this->rutaId,
                'tipo' => 'VISITA_PROMOTOR',
                'desde' => '2026-09-01',
                'hasta' => '2026-09-10',
            ]));

        $this->assertPdfReal(
            $response,
            'reporte-administrativo-faltantes-20260910-163000.pdf'
        );
    }

    public function test_administrador_reporte_invalido_usa_resumen(): void
    {
        if (! Route::has('administrador.reportes.imprimir')) {
            $this->fail(
                'No existe la ruta administrador.reportes.imprimir. '
                . 'No es posible probar el fallback a resumen.'
            );
        }

        $response = $this
            ->actingAs($this->administrador)
            ->get(route('administrador.reportes.imprimir', [
                'reporte' => 'reporte-que-no-existe',
            ]));

        $this->assertPdfReal(
            $response,
            'reporte-administrativo-resumen-20260910-163000.pdf'
        );
    }

    public function test_otro_rol_no_puede_generar_reporte_pdf_de_administrador(): void
    {
        if (! Route::has('administrador.reportes.imprimir')) {
            $this->fail(
                'No existe la ruta administrador.reportes.imprimir. '
                . 'Primero debe registrarse la ruta para validar permisos.'
            );
        }

        $response = $this
            ->actingAs($this->promotor)
            ->get(route('administrador.reportes.imprimir'));

        $response->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function crearUsuario(
        Role $rol,
        string $usuario,
        string $nombres,
        string $apellidos
    ): Usuario {
        /** @var Usuario $nuevo */
        $nuevo = Usuario::factory()->create([
            'rol_id' => $rol->id,
            'usuario' => $usuario,
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        DB::table('datos_personales')->insert([
            'usuario_id' => $nuevo->id,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => null,
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $nuevo;
    }

    private function crearArqueo(
        string $tipo,
        int $creadoPor,
        float $diferencia,
        string $fecha,
        string $estado = 'PENDIENTE_CERTIFICACION'
    ): Arqueo {
        $saldoSistema = 500.00;
        $totalArqueado = $saldoSistema + $diferencia;

        /** @var Arqueo $arqueo */
        $arqueo = Arqueo::query()->create([
            'numero_arqueo' =>
                'REP-' . Str::upper(Str::random(14)),

            'agente_id' => $this->agenteId,
            'creado_por' => $creadoPor,
            'habilitacion_atrasada_id' => null,

            'tipo' => $tipo,
            'estado' => $estado,

            'fecha_arqueo' => $fecha,
            'hora_inicio' => now()->subMinutes(15),
            'hora_fin' => now(),

            'fuera_fecha_ordinaria' => false,

            'codigo_agente_historico' => 'AG-REP-001',
            'nombre_negocio_historico' => 'Agente Reportes PDF',
            'nombre_propietario_historico' => 'Propietario Reportes PDF',
            'direccion_historica' => 'Dirección Reportes PDF',
            'ruta_historica' => 'Ruta Reportes PDF',
            'region_historica' => 'Región Reportes PDF',

            'total_billetes' => $totalArqueado,
            'total_monedas' => 0,
            'total_arqueado' => $totalArqueado,
            'saldo_sistema' => $saldoSistema,
            'diferencia' => $diferencia,

            'certificacion' => null,
            'observaciones' => 'Registro de prueba para reportes PDF.',

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

        return $arqueo;
    }

    private function assertPdfReal(
        $response,
        string $nombreEsperado
    ): void {
        $response->assertOk();

        $contentType = (string) $response
            ->headers
            ->get('Content-Type');

        $this->assertStringContainsString(
            'application/pdf',
            strtolower($contentType),
            'La respuesta no tiene Content-Type application/pdf.'
        );

        $contenido = $response->getContent();

        $this->assertIsString(
            $contenido,
            'La respuesta no contiene datos binarios.'
        );

        $this->assertStringStartsWith(
            '%PDF',
            $contenido,
            'La respuesta no comienza con la firma %PDF.'
        );

        $contentDisposition = (string) $response
            ->headers
            ->get('Content-Disposition');

        $this->assertStringContainsString(
            'inline',
            strtolower($contentDisposition),
            'El controlador debería devolver el PDF mediante stream().'
        );

        $this->assertStringContainsString(
            $nombreEsperado,
            $contentDisposition,
            'El nombre del archivo PDF no coincide con el esperado.'
        );

        $this->assertGreaterThan(
            1000,
            strlen($contenido),
            'El PDF generado parece estar vacío o incompleto.'
        );
    }
}
