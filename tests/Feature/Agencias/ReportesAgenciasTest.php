<?php

namespace Tests\Feature\Agencias;

use App\Models\Role;
use App\Models\Usuario;
use App\Services\Agencias\ReporteriaAgenciasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReportesAgenciasTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $gerencia;
    private Usuario $receptor;
    private int $agenciaA;
    private int $agenciaB;
    private int $ventanillaA;
    private int $ventanillaB;
    private int $jornadaA;
    private int $jornadaB;
    private int $jornadaPendiente;
    private int $turno;

    public static function reportes(): array
    {
        return array_combine(
            ['resumen','historial-agencia','historial-receptor','historial-ventanilla','historial-turno',
             'faltantes','sobrantes','exactos','ranking-faltantes','ranking-sobrantes',
             'diferencias-agencia','cumplimiento','anulados','sorpresivos','auditoria'],
            array_map(static fn ($v) => [$v],
                ['resumen','historial-agencia','historial-receptor','historial-ventanilla','historial-turno',
                 'faltantes','sobrantes','exactos','ranking-faltantes','ranking-sobrantes',
                 'diferencias-agencia','cumplimiento','anulados','sorpresivos','auditoria'])
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->gerencia = $this->crearUsuario('Gerencia', 'gerencia.reportes');
        $this->receptor = $this->crearUsuario('Receptor', 'receptor.reportes');
        $this->agenciaA = $this->insertar('agencias', ['codigo'=>'TEST-A','nombre'=>'Agencia Prueba A','estado'=>true]);
        $this->agenciaB = $this->insertar('agencias', ['codigo'=>'TEST-B','nombre'=>'Agencia Prueba B','estado'=>true]);
        $this->ventanillaA = $this->insertar('ventanillas_agencia', ['agencia_id'=>$this->agenciaA,'codigo'=>'V-01','estado'=>true]);
        $this->ventanillaB = $this->insertar('ventanillas_agencia', ['agencia_id'=>$this->agenciaB,'codigo'=>'V-02','estado'=>true]);
        $this->turno = $this->insertar('turnos_agencia', ['nombre'=>'Matutino','hora_inicio'=>'08:00:00','hora_fin'=>'16:00:00','estado'=>true]);
        $asignacionA = $this->insertar('asignaciones_personal_agencia', [
            'usuario_id'=>$this->receptor->id,'agencia_id'=>$this->agenciaA,'cargo'=>'RECEPTOR',
            'tipo'=>'PERMANENTE','fecha_inicio'=>'2026-01-01','estado'=>true,'asignado_por'=>$this->gerencia->id,
        ]);
        $asignacionB = $this->insertar('asignaciones_personal_agencia', [
            'usuario_id'=>$this->receptor->id,'agencia_id'=>$this->agenciaB,'cargo'=>'RECEPTOR',
            'tipo'=>'TEMPORAL','fecha_inicio'=>'2026-01-01','estado'=>true,'asignado_por'=>$this->gerencia->id,
        ]);
        $this->jornadaA = $this->crearJornada($asignacionA, $this->agenciaA, $this->ventanillaA, '2026-10-20');
        $this->jornadaB = $this->crearJornada($asignacionB, $this->agenciaB, $this->ventanillaB, '2026-10-21');
        $this->jornadaPendiente = $this->crearJornada($asignacionA, $this->agenciaA, $this->ventanillaA, '2026-10-22');

        $this->crearArqueo('TEST-EXACTO', $this->agenciaA, $this->ventanillaA, $this->jornadaA, 'ORDINARIO', 'CERTIFICADO', 1000, 1000, true, '2026-10-20');
        $this->crearArqueo('TEST-FALTANTE', $this->agenciaA, $this->ventanillaA, $this->jornadaA, 'ORDINARIO', 'CERTIFICADO', 1000, 950, true, '2026-10-20');
        $this->crearArqueo('TEST-SOBRANTE', $this->agenciaB, $this->ventanillaB, $this->jornadaB, 'ORDINARIO', 'CERTIFICADO', 1000, 1025, true, '2026-10-21');
        $this->crearArqueo('TEST-ANULADO', $this->agenciaA, $this->ventanillaA, $this->jornadaA, 'ORDINARIO', 'ANULADO', 1000, 900, false, '2026-10-20');
        $this->crearArqueo('TEST-SORPRESIVO', $this->agenciaB, $this->ventanillaB, null, 'SORPRESIVO', 'CERTIFICADO', 200, 200, true, '2026-10-21');
        $this->crearArqueo('TEST-AUDITORIA', $this->agenciaA, $this->ventanillaA, null, 'AUDITORIA', 'CERTIFICADO', 300, 300, true, '2026-10-22');
    }

    private function crearUsuario(string $rol, string $nombre): Usuario
    {
        $role = Role::factory()->create(['nombre'=>$rol,'estado'=>true]);
        return Usuario::factory()->create([
            'rol_id'=>$role->id,'usuario'=>$nombre,'estado'=>'ACTIVO',
            'password'=>'Password123!','requiere_cambio_password'=>false,
            'fecha_ultimo_cambio_password'=>now(),
        ]);
    }

    private function insertar(string $tabla, array $campos): int
    {
        return (int) DB::table($tabla)->insertGetId([
            ...$campos,'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function crearJornada(int $asignacion, int $agencia, int $ventanilla, string $fecha): int
    {
        return $this->insertar('jornadas_receptor', [
            'asignacion_personal_id'=>$asignacion,'receptor_usuario_id'=>$this->receptor->id,
            'agencia_id'=>$agencia,'ventanilla_id'=>$ventanilla,'turno_id'=>$this->turno,
            'fecha_operativa'=>$fecha,'inicio_programado'=>$fecha.' 08:00:00',
            'fin_programado'=>$fecha.' 16:00:00','estado'=>'FINALIZADA',
            'asignado_por'=>$this->gerencia->id,
        ]);
    }

    private function crearArqueo(string $numero, int $agencia, int $ventanilla, ?int $jornada,
        string $tipo, string $estado, float $corte, float $arqueado, bool $certificado, string $fecha): void
    {
        $esA = $agencia === $this->agenciaA;
        $this->insertar('arqueos_agencia', [
            'numero_arqueo'=>$numero,'agencia_id'=>$agencia,'ventanilla_id'=>$ventanilla,
            'jornada_id'=>$jornada,'receptor_usuario_id'=>$this->receptor->id,
            'creado_por'=>$this->gerencia->id,'tipo'=>$tipo,'estado'=>$estado,
            'fecha_operativa'=>$fecha,'inicio_at'=>$fecha.' 12:00:00',
            'agencia_codigo_historico'=>$esA ? 'TEST-A' : 'TEST-B',
            'agencia_nombre_historico'=>$esA ? 'Agencia Prueba A' : 'Agencia Prueba B',
            'ventanilla_codigo_historico'=>$esA ? 'V-01' : 'V-02',
            'receptor_nombre_historico'=>'Receptor Prueba',
            'turno_nombre_historico'=>'Matutino',
            'total_corte'=>$corte,'total_arqueado'=>$arqueado,
            'diferencia'=>$arqueado-$corte,
            'certificado_at'=>$certificado ? $fecha.' 15:00:00' : null,
            'anulado_at'=>$estado === 'ANULADO' ? $fecha.' 16:00:00' : null,
        ]);
    }

    private function generar(string $reporte, array $extra = []): array
    {
        $request = Request::create('/reportes', 'GET', ['reporte'=>$reporte, ...$extra]);
        $servicio = app(ReporteriaAgenciasService::class);
        return $servicio->generar($this->gerencia, $servicio->filtros($request), false);
    }

    private function numeros(array $datos): array
    {
        return collect($datos['resultados'])->pluck('numero_arqueo')->all();
    }

    #[DataProvider('reportes')]
    public function test_los_quince_reportes_generan_resultados_consultables(string $reporte): void
    {
        $datos = $this->generar($reporte);
        $this->assertNotEmpty($datos['titulo']);
        $this->assertNotEmpty($datos['columnas']);
        $this->assertArrayHasKey('metricas', $datos);
        $this->assertGreaterThan(0, $datos['resultados']->count(), $reporte);
    }

    #[DataProvider('reportes')]
    public function test_los_quince_reportes_renderizan_su_vista_html(string $reporte): void
    {
        $this->actingAs($this->gerencia)
            ->get(route('agencias.reportes.index', ['reporte'=>$reporte]))
            ->assertOk()->assertSee('Centro de Reportes');
    }

    #[DataProvider('reportes')]
    public function test_los_quince_reportes_exportan_csv_con_encabezados_y_datos(string $reporte): void
    {
        $datos = $this->generar($reporte);
        $respuesta = $this->actingAs($this->gerencia)
            ->get(route('agencias.reportes.csv', ['reporte'=>$reporte]));
        $respuesta->assertOk();
        $this->assertStringContainsString('text/csv', (string) $respuesta->headers->get('Content-Type'));
        ob_start();
        $respuesta->baseResponse->sendContent();
        $contenido = (string) ob_get_clean();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $contenido);
        $lineas = preg_split('/\r\n|\r|\n/', trim(substr($contenido, 3)));
        $this->assertCount($datos['resultados']->count() + 1, $lineas, $reporte);
        $this->assertSame(array_values($datos['columnas']), str_getcsv($lineas[0], ';'));
    }

    #[DataProvider('reportes')]
    public function test_los_quince_reportes_generan_pdf_valido(string $reporte): void
    {
        $respuesta = $this->actingAs($this->gerencia)
            ->get(route('agencias.reportes.pdf', ['reporte'=>$reporte]));
        $respuesta->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $respuesta->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $respuesta->getContent());
    }

    public function test_resumen_calcula_cantidades_y_montos_correctamente(): void
    {
        $m = $this->generar('resumen')['metricas'];
        $this->assertSame(5, $m['Arqueos vigentes']);
        $this->assertSame(5, $m['Certificados']);
        $this->assertSame(3, $m['Exactos']);
        $this->assertSame(1, $m['Faltantes']);
        $this->assertSame(1, $m['Sobrantes']);
        $this->assertSame('Q 50.00', $m['Monto faltantes']);
        $this->assertSame('Q 25.00', $m['Monto sobrantes']);
    }

    public function test_reportes_clasifican_correctamente_los_arqueos(): void
    {
        $this->assertSame(['TEST-FALTANTE'], $this->numeros($this->generar('faltantes')));
        $this->assertSame(['TEST-SOBRANTE'], $this->numeros($this->generar('sobrantes')));
        $this->assertEqualsCanonicalizing(['TEST-EXACTO','TEST-SORPRESIVO','TEST-AUDITORIA'], $this->numeros($this->generar('exactos')));
        $this->assertSame(['TEST-ANULADO'], $this->numeros($this->generar('anulados')));
        $this->assertSame(['TEST-SORPRESIVO'], $this->numeros($this->generar('sorpresivos')));
        $this->assertSame(['TEST-AUDITORIA'], $this->numeros($this->generar('auditoria')));
        $this->assertSame(1, $this->generar('faltantes')['metricas']['Casos encontrados']);
        $this->assertSame('Q 50.00', $this->generar('faltantes')['metricas']['Monto acumulado']);
    }

    public function test_rankings_y_diferencias_por_agencia_calculan_importes(): void
    {
        $faltante = $this->generar('ranking-faltantes')['resultados']->sole();
        $this->assertEquals(1, $faltante->cantidad_incidencias);
        $this->assertEquals(50, $faltante->monto_acumulado);
        $sobrante = $this->generar('ranking-sobrantes')['resultados']->sole();
        $this->assertEquals(25, $sobrante->monto_acumulado);
        $agencias = $this->generar('diferencias-agencia')['resultados']->keyBy('agencia_id');
        $this->assertEquals(50, $agencias[$this->agenciaA]->monto_faltantes);
        $this->assertEquals(25, $agencias[$this->agenciaB]->monto_sobrantes);
    }

    public function test_historiales_respetan_filtros_de_agencia_receptor_ventanilla_turno_y_fechas(): void
    {
        foreach (['historial-agencia','historial-receptor','historial-ventanilla','historial-turno'] as $reporte) {
            $datos = $this->generar($reporte, [
                'agencia_id'=>$this->agenciaB,'receptor_id'=>$this->receptor->id,
                'ventanilla_id'=>$this->ventanillaB,'turno'=>'Matutino',
                'desde'=>'2026-10-21','hasta'=>'2026-10-21',
            ]);
            $this->assertEqualsCanonicalizing(['TEST-SOBRANTE','TEST-SORPRESIVO'], $this->numeros($datos), $reporte);
        }
    }

    public function test_cumplimiento_identifica_jornadas_con_y_sin_arqueo_certificado(): void
    {
        $datos = $this->generar('cumplimiento');
        $m = $datos['metricas'];
        $this->assertSame(3, $m['Jornadas registradas']);
        $this->assertSame(2, $m['Con arqueo certificado']);
        $this->assertSame(1, $m['Sin arqueo certificado']);
        $this->assertSame('66.67%', $m['Porcentaje de cumplimiento']);
        $estados = $datos['resultados']->keyBy('id');
        $this->assertSame('CUMPLIDO', $estados[$this->jornadaA]->cumplimiento);
        $this->assertSame('CUMPLIDO', $estados[$this->jornadaB]->cumplimiento);
        $this->assertSame('SIN_ARQUEO_CERTIFICADO', $estados[$this->jornadaPendiente]->cumplimiento);
    }

    public function test_jefe_de_caja_solo_puede_consultar_su_agencia(): void
    {
        $jefe = $this->crearUsuario('JefeCaja', 'jefe.reportes');
        $this->insertar('asignaciones_personal_agencia', [
            'usuario_id'=>$jefe->id,'agencia_id'=>$this->agenciaA,'cargo'=>'JEFE_CAJA',
            'tipo'=>'PERMANENTE','fecha_inicio'=>'2026-01-01','estado'=>true,
            'asignado_por'=>$this->gerencia->id,
        ]);
        $servicio = app(ReporteriaAgenciasService::class);
        $request = Request::create('/', 'GET', ['reporte'=>'historial-agencia']);
        $datos = $servicio->generar($jefe, $servicio->filtros($request), false);
        $this->assertCount(4, $datos['resultados']);
        $this->assertEqualsCanonicalizing(
            ['TEST-EXACTO','TEST-FALTANTE','TEST-ANULADO','TEST-AUDITORIA'],
            $this->numeros($datos)
        );
        $this->actingAs($jefe)->get(route('agencias.reportes.index', [
            'reporte'=>'historial-agencia','agencia_id'=>$this->agenciaB,
        ]))->assertForbidden();
    }

    public function test_receptor_no_puede_consultar_reportes(): void
    {
        $this->actingAs($this->receptor)
            ->get(route('agencias.reportes.index'))
            ->assertForbidden();
    }

    public function test_catalogo_contiene_los_quince_reportes(): void
    {
        $this->assertCount(15, app(ReporteriaAgenciasService::class)->tiposReporte());
    }
}
