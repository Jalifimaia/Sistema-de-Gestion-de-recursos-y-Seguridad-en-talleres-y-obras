<?php

namespace App\Services;

use App\Models\SerieRecurso;
use App\Models\Recurso;
use App\Models\Prestamo;
use Carbon\Carbon;

class RecomendacionService
{
    public function generar()
    {
        $hoy = Carbon::today();
        $recomendaciones = [];

        // Vencimientos próximos
        try {
            $proxVencimientos = SerieRecurso::whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '>=', $hoy)
                ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(30))
                ->with('recurso')
                ->get();

            if ($proxVencimientos->isNotEmpty()) {
                $recomendaciones[] = [
                    'titulo' => 'Vencimientos próximos',
                    'mensaje' => "Hay {$proxVencimientos->count()} serie(s) con vencimiento en los próximos 30 días.",
                    'detalles' => $proxVencimientos->map(function($s){
                        return [
                            'id' => $s->id,
                            'recurso' => $s->recurso ? $s->recurso->nombre : null,
                            'nro_serie' => $s->nro_serie,
                            'fecha_vencimiento' => $s->fecha_vencimiento,
                        ];
                    }),
                    'nivel' => 'warning'
                ];
            }
        } catch (\Throwable $e) {
            logger()->error('RecomendacionService: error consultando vencimientos: ' . $e->getMessage(), ['exception' => $e]);
        }

        // Predicción simple por día de semana
        try {
            $prestamos = Prestamo::whereNotNull('fecha_prestamo')->get();
        } catch (\Throwable $e) {
            logger()->error('RecomendacionService: error consultando prestamos: ' . $e->getMessage(), ['exception' => $e]);
            $prestamos = collect();
        }
        $dias = [];
        foreach ($prestamos as $p) {
            try {
                $d = Carbon::parse($p->fecha_prestamo)->format('l');
                if (!isset($dias[$d])) $dias[$d] = 0;
                $dias[$d]++;
            } catch (\Exception $e) {
            }
        }
        if (!empty($dias)) {
            arsort($dias);
            $mas = array_slice($dias, 0, 2, true);
            $recomendaciones[] = [
                'titulo' => 'Predicción de demanda',
                'mensaje' => "Días con mayor uso: " . implode(', ', array_keys($mas)) . ".",
                'detalles' => $mas,
                'nivel' => 'info'
            ];
        }

        // Recursos con pocas series
        try {
            $recursos = Recurso::withCount('serieRecursos')->get();
            $bajos = $recursos->filter(function($r){
                return $r->serie_recursos_count <= 2;
            })->map(function($r){
                return ['id' => $r->id, 'nombre' => $r->nombre, 'cantidad_series' => $r->serie_recursos_count];
            });
            if ($bajos->isNotEmpty()) {
                $recomendaciones[] = [
                    'titulo' => 'Inventario bajo',
                    'mensaje' => "Hay {$bajos->count()} recurso(s) con pocas series (<=2).",
                    'detalles' => $bajos,
                    'nivel' => 'danger'
                ];
            }
        } catch (\Throwable $e) {
            logger()->error('RecomendacionService: error consultando recursos: ' . $e->getMessage(), ['exception' => $e]);
        }

        return $recomendaciones;
    }
}
