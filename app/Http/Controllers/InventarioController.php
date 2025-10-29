<?php

namespace App\Http\Controllers;
use App\Models\Checklist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Recurso;
use Illuminate\Support\Facades\DB;


class InventarioController extends Controller
{

    public function index()
    {
        $recursos = Recurso::with(['serieRecursos.codigo', 'subcategoria.categoria'])->get();

        $herramientasTotales = DB::table('serie_recurso')
            ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
            ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
            ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
            ->where('categoria.nombre_categoria', 'Herramienta')
            ->count();

        $herramientasDisponibles = DB::table('serie_recurso')
            ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
            ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
            ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
            ->where('categoria.nombre_categoria', 'Herramienta')
            ->where('serie_recurso.id_estado', 1)
            ->count();

        // EPP en stock
        $eppStock = DB::table('serie_recurso')
            ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
            ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
            ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
            ->where('categoria.nombre_categoria', 'EPP')
            ->where('serie_recurso.id_estado', 1) // Disponible
            ->count();

        // EPP totales
        $eppTotales = DB::table('serie_recurso')
            ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
            ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
            ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
            ->where('categoria.nombre_categoria', 'EPP')
            ->count();

        // Elementos en reparación
        $elementosReparacion = DB::table('serie_recurso')
            ->where('id_estado', 6) // En Reparación
            ->count();

        $eppVencidos = DB::table('serie_recurso')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->where('categoria.nombre_categoria', 'EPP')
        ->whereNotNull('serie_recurso.fecha_vencimiento')
        ->where('serie_recurso.fecha_vencimiento', '<', now())
        ->count();

        $elementosDañados = DB::table('serie_recurso')
        ->where('id_estado', 5)
        ->count();

        return view('inventario', compact(
            'recursos',
            'herramientasTotales',
            'herramientasDisponibles',
            'eppStock',
            'eppTotales',
            'elementosReparacion',
            'eppVencidos',
            'elementosDañados'
        ));
    }

    public function tieneEppCompleto($usuarioId)
{
    $checklist = Checklist::where('trabajador_id', $usuarioId)
        ->whereDate('fecha', Carbon::today())
        ->latest()
        ->first();

    if (!$checklist) return false;

    $basicos = $checklist->anteojos && $checklist->botas && $checklist->chaleco && $checklist->guantes;

    if ($checklist->es_en_altura) {
        return $basicos && $checklist->arnes;
    }

    return $basicos;
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
                    return $fecha ? Carbon::parse($fecha)->format('d/m/Y') : '-';
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
    public function inventario()
{
    return view('usuario.inventario');
}

}
