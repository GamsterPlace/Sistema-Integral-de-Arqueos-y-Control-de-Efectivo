<?php

namespace Tests\Feature\Pdf;

use App\Models\Arqueo;
use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArqueosPdfTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $agenteUsuario;
    private Usuario $otroAgenteUsuario;
    private Usuario $promotor;
    private Usuario $otroPromotor;
    private Usuario $auditor;
    private Usuario $otroAuditor;

    private int $agenteId;
    private int $otroAgenteId;
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

        /** @var Role $rolAuditoria */
        $rolAuditoria = Role::factory()->create([
            'nombre' => 'Auditoria',
            'estado' => true,
        ]);

        $this->agenteUsuario = $this->crearUsuario(
            rol: $rolAgente,
            usuario: 'agente.pdf',
            nombres: 'Agente',
            apellidos: 'PDF'
        );

        $this->otroAgenteUsuario = $this->crearUsuario(
            rol: $rolAgente,
            usuario: 'otro.agente.pdf',
            nombres: 'Otro',
            apellidos: 'Agente PDF'
        );

        $this->promotor = $this->crearUsuario(
            rol: $rolPromotor,
            usuario: 'promotor.pdf',
            nombres: 'Promotor',
            apellidos: 'PDF'
        );

        $this->otroPromotor = $this->crearUsuario(
            rol: $rolPromotor,
            usuario: 'otro.promotor.pdf',
            nombres: 'Otro',
            apellidos: 'Promotor PDF'
        );

        $this->auditor = $this->crearUsuario(
            rol: $rolAuditoria,
            usuario: 'auditor.pdf',
            nombres: 'Auditor',
            apellidos: 'PDF'
        );

        $this->otroAuditor = $this->crearUsuario(
            rol: $rolAuditoria,
            usuario: 'otro.auditor.pdf',
            nombres: 'Otro',
            apellidos: 'Auditor PDF'
        );

        $regionId = DB::table('regiones')->insertGetId([
            'nombre' => 'Región PDF',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->rutaId = DB::table('rutas')->insertGetId([
            'region_id' => $regionId,
            'codigo' => 'RUTA-PDF-01',
            'nombre' => 'Ruta PDF',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->agenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $this->agenteUsuario->id,
            'ruta_id' => $this->rutaId,
            'codigo_agente' => 'AG-PDF-001',
            'nombre_negocio' => 'Negocio PDF Principal',
            'nombre_propietario' => 'Propietario PDF Principal',
            'direccion' => 'Dirección PDF Principal',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->otroAgenteId = DB::table('agentes')->insertGetId([
            'usuario_id' => $this->otroAgenteUsuario->id,
            'ruta_id' => $this->rutaId,
            'codigo_agente' => 'AG-PDF-002',
            'nombre_negocio' => 'Negocio PDF Secundario',
            'nombre_propietario' => 'Propietario PDF Secundario',
            'direccion' => 'Dirección PDF Secundaria',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_agente_puede_generar_pdf_de_su_propio_arqueo_diario(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            agenteId: $this->agenteId,
            creadoPor: $this->agenteUsuario->id
        );

        $this->crearFirma(
            arqueo: $arqueo,
            usuario: $this->agenteUsuario,
            tipoFirma: 'REALIZADOR',
            rolFirmante: 'agente'
        );

        $response = $this
            ->actingAs($this->agenteUsuario)
            ->get(route('agente.arqueos.imprimir', $arqueo));

        $this->assertRespuestaPdfValida(
            $response,
            $arqueo->numero_arqueo . '.pdf'
        );
    }

    public function test_otro_agente_no_puede_imprimir_arqueo_que_no_le_pertenece(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            agenteId: $this->agenteId,
            creadoPor: $this->agenteUsuario->id
        );

        $response = $this
            ->actingAs($this->otroAgenteUsuario)
            ->get(route('agente.arqueos.imprimir', $arqueo));

        $response->assertForbidden();
    }



    public function test_promotor_puede_generar_pdf_de_su_propia_visita(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'VISITA_PROMOTOR',
            agenteId: $this->agenteId,
            creadoPor: $this->promotor->id
        );

        $this->crearFirma(
            arqueo: $arqueo,
            usuario: $this->promotor,
            tipoFirma: 'REALIZADOR',
            rolFirmante: 'Promotor'
        );

        $response = $this
            ->actingAs($this->promotor)
            ->get(route('promotor.arqueos.imprimir', $arqueo));

        $this->assertRespuestaPdfValida(
            $response,
            $arqueo->numero_arqueo . '.pdf'
        );
    }

    public function test_otro_promotor_no_puede_imprimir_visita_que_no_creo(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'VISITA_PROMOTOR',
            agenteId: $this->agenteId,
            creadoPor: $this->promotor->id
        );

        $response = $this
            ->actingAs($this->otroPromotor)
            ->get(route('promotor.arqueos.imprimir', $arqueo));

        $response->assertForbidden();
    }

    public function test_promotor_no_puede_imprimir_desde_su_endpoint_un_tipo_distinto(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            agenteId: $this->agenteId,
            creadoPor: $this->promotor->id
        );

        $response = $this
            ->actingAs($this->promotor)
            ->get(route('promotor.arqueos.imprimir', $arqueo));

        $response->assertNotFound();
    }

    public function test_auditoria_puede_generar_pdf_de_su_propia_visita(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'VISITA_AUDITORIA',
            agenteId: $this->agenteId,
            creadoPor: $this->auditor->id
        );

        $response = $this
            ->actingAs($this->auditor)
            ->get(route('auditoria.arqueos.imprimir', $arqueo));

        $this->assertRespuestaPdfValida(
            $response,
            $arqueo->numero_arqueo . '.pdf'
        );
    }

    public function test_otro_auditor_no_puede_imprimir_visita_que_no_le_pertenece(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'VISITA_AUDITORIA',
            agenteId: $this->agenteId,
            creadoPor: $this->auditor->id
        );

        $response = $this
            ->actingAs($this->otroAuditor)
            ->get(route('auditoria.arqueos.imprimir', $arqueo));

        $response->assertNotFound();
    }

    public function test_usuario_de_otro_rol_no_puede_usar_impresion_de_auditoria(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'VISITA_AUDITORIA',
            agenteId: $this->agenteId,
            creadoPor: $this->auditor->id
        );

        $response = $this
            ->actingAs($this->promotor)
            ->get(route('auditoria.arqueos.imprimir', $arqueo));

        $response->assertForbidden();
    }

    public function test_auditoria_no_puede_imprimir_desde_su_endpoint_un_tipo_distinto(): void
    {
        $arqueo = $this->crearArqueo(
            tipo: 'DIARIO_AGENTE',
            agenteId: $this->agenteId,
            creadoPor: $this->auditor->id
        );

        $response = $this
            ->actingAs($this->auditor)
            ->get(route('auditoria.arqueos.imprimir', $arqueo));

        $response->assertNotFound();
    }

    private function crearUsuario(
        Role $rol,
        string $usuario,
        string $nombres,
        string $apellidos
    ): Usuario {
        /** @var Usuario $nuevoUsuario */
        $nuevoUsuario = Usuario::factory()->create([
            'rol_id' => $rol->id,
            'usuario' => $usuario,
            'password' => 'Password123!',
            'estado' => 'ACTIVO',
            'requiere_cambio_password' => false,
            'fecha_ultimo_cambio_password' => now(),
        ]);

        DB::table('datos_personales')->insert([
            'usuario_id' => $nuevoUsuario->id,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => null,
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $nuevoUsuario;
    }

    private function crearArqueo(
        string $tipo,
        int $agenteId,
        int $creadoPor,
        string $estado = 'PENDIENTE_CERTIFICACION'
    ): Arqueo {
        $agente = DB::table('agentes as a')
            ->join('rutas as r', 'r.id', '=', 'a.ruta_id')
            ->join('regiones as reg', 'reg.id', '=', 'r.region_id')
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
            'numero_arqueo' => 'ARQ-PDF-' . Str::upper(Str::random(12)),
            'agente_id' => $agenteId,
            'creado_por' => $creadoPor,
            'habilitacion_atrasada_id' => null,
            'tipo' => $tipo,
            'estado' => $estado,
            'fecha_arqueo' => today(),
            'hora_inicio' => now()->subMinutes(10),
            'hora_fin' => now(),
            'fuera_fecha_ordinaria' => false,
            'codigo_agente_historico' => $agente->codigo_agente,
            'nombre_negocio_historico' => $agente->nombre_negocio,
            'nombre_propietario_historico' => $agente->nombre_propietario,
            'direccion_historica' => $agente->direccion,
            'ruta_historica' => $agente->ruta_nombre,
            'region_historica' => $agente->region_nombre,
            'total_billetes' => 250.00,
            'total_monedas' => 1.50,
            'total_arqueado' => 251.50,
            'saldo_sistema' => 250.00,
            'diferencia' => 1.50,
            'certificacion' => 'Certificación de prueba para generación de PDF.',
            'observaciones' => 'Observaciones de prueba para generación de PDF.',
            'pendiente_certificacion_at' =>
                $estado === 'PENDIENTE_CERTIFICACION' ? now() : null,
            'certificado_at' =>
                $estado === 'CERTIFICADO' ? now() : null,
            'anulado_at' =>
                $estado === 'ANULADO' ? now() : null,
        ]);

        DB::table('arqueo_detalles')->insert([
            [
                'arqueo_id' => $arqueo->id,
                'tipo' => 'BILLETE',
                'denominacion' => 100.00,
                'cantidad' => 2,
                'subtotal' => 200.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arqueo_id' => $arqueo->id,
                'tipo' => 'BILLETE',
                'denominacion' => 50.00,
                'cantidad' => 1,
                'subtotal' => 50.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arqueo_id' => $arqueo->id,
                'tipo' => 'MONEDA',
                'denominacion' => 1.00,
                'cantidad' => 1,
                'subtotal' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'arqueo_id' => $arqueo->id,
                'tipo' => 'MONEDA',
                'denominacion' => 0.50,
                'cantidad' => 1,
                'subtotal' => 0.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return $arqueo;
    }

    private function crearFirma(
        Arqueo $arqueo,
        Usuario $usuario,
        string $tipoFirma,
        string $rolFirmante
    ): void {
        $datosPersonales = DB::table('datos_personales')
            ->where('usuario_id', $usuario->id)
            ->first();

        DB::table('firmas_arqueos')->insert([
            'arqueo_id' => $arqueo->id,
            'usuario_id' => $usuario->id,
            'tipo_firma' => $tipoFirma,
            'rol_firmante' => $rolFirmante,
            'nombres_historicos' => $datosPersonales->nombres,
            'apellidos_historicos' => $datosPersonales->apellidos,
            'hash_documento' => hash(
                'sha256',
                'documento-pdf-' . $arqueo->id . '-' . $tipoFirma
            ),
            'firma_electronica' => hash_hmac(
                'sha256',
                'firma-pdf-' . $arqueo->id . '-' . $tipoFirma,
                (string) config('app.key')
            ),
            'algoritmo' => 'HMAC-SHA256',
            'version_firma' => 1,
            'fecha_firma' => now(),
            'valida' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assertRespuestaPdfValida(
        $response,
        string $nombreEsperado
    ): void {
        $response->assertOk();

        $contentType = (string) $response->headers->get('Content-Type');

        $this->assertStringContainsString(
            'application/pdf',
            strtolower($contentType),
            'La respuesta no tiene Content-Type application/pdf.'
        );

        $contenido = $response->getContent();

        $this->assertIsString(
            $contenido,
            'La respuesta del PDF no contiene datos binarios.'
        );

        $this->assertStringStartsWith(
            '%PDF',
            $contenido,
            'La respuesta HTTP no contiene un archivo PDF válido.'
        );

        $contentDisposition = (string) $response->headers->get(
            'Content-Disposition'
        );

        $this->assertStringContainsString(
            'inline',
            strtolower($contentDisposition),
            'El PDF debería mostrarse en línea mediante stream().'
        );

        $this->assertStringContainsString(
            $nombreEsperado,
            $contentDisposition,
            'El nombre del PDF generado no coincide con el número de arqueo.'
        );

        $this->assertGreaterThan(
            1000,
            strlen($contenido),
            'El PDF generado parece estar vacío o incompleto.'
        );
    }
}
