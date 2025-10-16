<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Recurso;

class InventarioController extends Controller
{
    public function index()
    {
        $recursos = Recurso::with(['serieRecursos', 'subcategoria.categoria'])->get();

        return view('inventario', compact('recursos'));
    }
    public function exportarCSV()
    {
        $recursos = Recurso::with('subcategoria.categoria', 'serieRecursos')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventario.csv"',
        ];

        $callback = function () use ($recursos) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, [
                'ID',
                'Nombre',
                'Descripción',
                'Categoría',
                'Subcategoría',
                'Series',
                'Estados de Series',
                'Fechas de Vencimiento'
            ]);

            foreach ($recursos as $recurso) {
                $series = $recurso->serieRecursos->pluck('nro_serie')->implode(', ');
                $estados = $recurso->serieRecursos->pluck('id_estado')->implode(', ');
                $fechas = $recurso->serieRecursos->pluck('fecha_vencimiento')->map(function ($fecha) {
                    return $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : '-';
                })->implode(', ');

                fputcsv($file, [
                    $recurso->id,
                    $recurso->nombre,
                    $recurso->descripcion,
                    $recurso->subcategoria->categoria->nombre_categoria ?? 'Sin categoría',
                    $recurso->subcategoria->nombre ?? 'Sin subcategoría',
                    $series ?: 'Sin series',
                    $estados ?: 'Sin estado',
                    $fechas ?: 'Sin fechas'
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
