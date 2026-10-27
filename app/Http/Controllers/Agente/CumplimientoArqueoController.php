<?php

namespace App\Http\Controllers\Agente;

use App\Http\Controllers\Controller;
use App\Models\Arqueo;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CumplimientoArqueoController extends Controller
{
    private const ESTADOS_FINALIZADOS = [
        'PENDIENTE_CERTIFICACION',
        'CERTIFICADO',
    ];

    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $usuario->loadMissing([
            'agente.ruta.region',
        ]);

        $agente = $usuario->agente;

        abort_if(
            ! $agente,
            403,
            'La cuenta no tiene un agente asociado.'
        );

        $mes = $this->resolverMes($request);

        $inicioMes = $mes->copy()->startOfMonth();
        $finMes = $mes->copy()->endOfMonth();

        $arqueos = Arqueo::query()
            ->where('agente_id', $agente->id)
            ->where('tipo', 'DIARIO_AGENTE')
            ->whereBetween('fecha_arqueo', [
                $inicioMes->toDateString(),
                $finMes->toDateString(),
            ])
            ->orderBy('fecha_arqueo')
            ->orderBy('id')
            ->get();

        $controles = DB::table('controles_diarios')
            ->where('agente_id', $agente->id)
            ->where('tipo', 'NO_ATENDIO')
            ->where('vigente', true)
            ->whereBetween('fecha', [
                $inicioMes->toDateString(),
                $finMes->toDateString(),
            ])
            ->orderBy('fecha')
            ->get();

        $arqueosPorFecha = $arqueos->groupBy(
            fn (Arqueo $arqueo): string => $arqueo->fecha_arqueo->format('Y-m-d')
        );

        $controlesPorFecha = $controles->keyBy(
            fn (object $control): string => Carbon::parse($control->fecha)->format('Y-m-d')
        );

        $calendario = [];
        $resumen = [
            'cumplidos' => 0,
            'sin_arqueo' => 0,
            'no_atendio' => 0,
            'extemporaneos' => 0,
            'anulados' => 0,
        ];

        $hoy = today();

        for (
            $fecha = $inicioMes->copy();
            $fecha->lte($finMes);
            $fecha->addDay()
        ) {
            $fechaActual = $fecha->copy();
            $clave = $fechaActual->format('Y-m-d');

            if ($fechaActual->gt($hoy)) {
                $calendario[] = [
                    'fecha' => $clave,
                    'dia' => $fechaActual->day,
                    'es_hoy' => false,
                    'es_futuro' => true,
                    'categoria' => 'FUTURO',
                    'estado_texto' => 'Próximo',
                    'arqueo_id' => null,
                    'numero_arqueo' => null,
                    'url_arqueo' => null,
                ];

                continue;
            }

            /** @var Collection<int, Arqueo> $arqueosDelDia */
            $arqueosDelDia = $arqueosPorFecha->get(
                $clave,
                collect()
            );

            $clasificacion = $this->clasificarDia(
                $arqueosDelDia,
                $controlesPorFecha->get($clave)
            );

            if (array_key_exists(
                $clasificacion['resumen_clave'],
                $resumen
            )) {
                $resumen[
                    $clasificacion['resumen_clave']
                ]++;
            }

            $calendario[] = [
                'fecha' => $clave,
                'dia' => $fechaActual->day,
                'es_hoy' => $fechaActual->isSameDay($hoy),
                'es_futuro' => false,
                'categoria' => $clasificacion['categoria'],
                'estado_texto' => $clasificacion['estado_texto'],
                'arqueo_id' => $clasificacion['arqueo']?->id,
                'numero_arqueo' => $clasificacion['arqueo']?->numero_arqueo,
                'url_arqueo' => $clasificacion['arqueo']
                    ? route(
                        'agente.arqueos.show',
                        $clasificacion['arqueo']->id
                    )
                    : null,
            ];
        }

        return view('agente.cumplimientos.index', [
            'agente' => $agente,
            'mes' => $mes->format('Y-m'),
            'mesActual' => today()->format('Y-m'),
            'mesAnterior' => $mes->copy()
                ->subMonthNoOverflow()
                ->format('Y-m'),
            'mesSiguiente' => $mes->copy()
                ->addMonthNoOverflow()
                ->format('Y-m'),
            'inicioMes' => $inicioMes,
            'finMes' => $finMes,
            'calendario' => $calendario,
            'resumenMes' => $resumen,
        ]);
    }

    private function resolverMes(Request $request): Carbon
    {
        $mesSolicitado = trim(
            (string) $request->query('mes', '')
        );

        if (
            $mesSolicitado !== ''
            && preg_match(
                '/^\d{4}-(0[1-9]|1[0-2])$/',
                $mesSolicitado
            ) === 1
        ) {
            try {
                return Carbon::createFromFormat(
                    '!Y-m',
                    $mesSolicitado
                )->startOfMonth();
            } catch (\Throwable) {
                // Si el mes no puede interpretarse, se usa el actual.
            }
        }

        return today()->startOfMonth();
    }

    /**
     * @param Collection<int, Arqueo> $arqueosDelDia
     *
     * @return array{
     *     categoria:string,
     *     estado_texto:string,
     *     resumen_clave:string,
     *     arqueo:?Arqueo
     * }
     */
    private function clasificarDia(
        Collection $arqueosDelDia,
        ?object $control
    ): array {
        /** @var Arqueo|null $arqueoFinalizado */
        $arqueoFinalizado = $arqueosDelDia
            ->filter(
                fn (Arqueo $arqueo): bool => in_array(
                    $arqueo->estado,
                    self::ESTADOS_FINALIZADOS,
                    true
                )
            )
            ->sortByDesc('id')
            ->first();

        if ($arqueoFinalizado) {
            if ((bool) $arqueoFinalizado->fuera_fecha_ordinaria) {
                return [
                    'categoria' => 'EXTEMPORANEO',
                    'estado_texto' => 'Extemporáneo',
                    'resumen_clave' => 'extemporaneos',
                    'arqueo' => $arqueoFinalizado,
                ];
            }

            return [
                'categoria' => 'CUMPLIDO',
                'estado_texto' => 'Cumplido',
                'resumen_clave' => 'cumplidos',
                'arqueo' => $arqueoFinalizado,
            ];
        }

        if ($control !== null) {
            return [
                'categoria' => 'NO_ATENDIO',
                'estado_texto' => 'No atendió',
                'resumen_clave' => 'no_atendio',
                'arqueo' => null,
            ];
        }

        /** @var Arqueo|null $arqueoAnulado */
        $arqueoAnulado = $arqueosDelDia
            ->where('estado', 'ANULADO')
            ->sortByDesc('id')
            ->first();

        if ($arqueoAnulado) {
            return [
                'categoria' => 'ANULADO',
                'estado_texto' => 'Anulado',
                'resumen_clave' => 'anulados',
                'arqueo' => $arqueoAnulado,
            ];
        }

        return [
            'categoria' => 'SIN_ARQUEO',
            'estado_texto' => 'Sin arqueo',
            'resumen_clave' => 'sin_arqueo',
            'arqueo' => null,
        ];
    }
}
