<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Usuario;
use App\Models\Recurso;
use App\Models\SerieRecurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Checklist;


class DashboardController extends Controller
{

public function index()
{
    $usuariosActivos = Usuario::where('id_estado', 1)->count();
    $usuarios = Usuario::where('id_estado', 1)->get();


    /*$usuariosEnLinea = DB::table('users')
    ->where('last_activity', '>=', now()->subMinutes(10))
    ->count();*/


    // Herramientas en uso (estado_prestamo = Activo)
    $herramientasEnUso = DB::table('serie_recurso')
    ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
    ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
    ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
    ->where('serie_recurso.id_estado', 3) // Prestado
    ->where('categoria.nombre_categoria', 'Herramienta')
    ->count();


    // Herramientas totales (todas las series de recursos que son herramientas)
    $herramientasTotales = DB::table('serie_recurso')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->where('categoria.nombre_categoria', 'Herramienta')
        ->count();

    // Herramientas disponibles
    $herramientasDisponibles = DB::table('serie_recurso')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->where('categoria.nombre_categoria', 'Herramienta')
        ->where('serie_recurso.id_estado', 1) // Disponible
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

    $stockBajo = DB::table('serie_recurso')
    ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
    ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
    ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
    ->select('recurso.nombre', DB::raw('COUNT(*) as cantidad'))
    ->where('categoria.nombre_categoria', 'EPP')
    ->where('serie_recurso.id_estado', 1) // Disponible
    ->groupBy('recurso.nombre')
    ->having('cantidad', '<', 5)
    ->get();

        

    // Total de trabajadores
    $totalTrabajadores = DB::table('usuario')
        ->where('id_rol', 3)
        ->count();

    // Cantidad de EPP devueltos por trabajadores
    $eppEntregados = DB::table('serie_recurso')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->where('categoria.nombre_categoria', 'EPP')
        ->where('serie_recurso.id_estado', 4) // Devuelto
        ->count();

    $eppEntregadosLista = DB::table('detalle_prestamo')
        ->join('serie_recurso', 'detalle_prestamo.id_serie', '=', 'serie_recurso.id')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->join('prestamo', 'detalle_prestamo.id_prestamo', '=', 'prestamo.id')
        ->join('usuario', 'prestamo.id_usuario', '=', 'usuario.id')
        ->where('categoria.nombre_categoria', 'EPP')
        ->where('detalle_prestamo.id_estado_prestamo', 2) // Activo
        ->select(
            'usuario.name as trabajador',
            'recurso.nombre as elemento',
            'prestamo.fecha_prestamo as fecha_entrega'
        )

        ->get();

    $alertasVencidos = SerieRecurso::with('recurso')
        ->whereNotNull('fecha_vencimiento')
        ->where('fecha_vencimiento', '<', now())
        ->get();



     // Porcentaje entregado
    $porcentajeEntregado = $totalTrabajadores > 0
        ? round(($eppEntregados / $totalTrabajadores) * 100)
        : 0;




    $herramientasNoDevueltas = DB::table('detalle_prestamo')
    ->join('serie_recurso', 'detalle_prestamo.id_serie', '=', 'serie_recurso.id')
    ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
    ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
    ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
    ->join('prestamo', 'detalle_prestamo.id_prestamo', '=', 'prestamo.id')
    ->join('usuario', 'prestamo.id_usuario', '=', 'usuario.id')
    ->where('detalle_prestamo.id_estado_prestamo', 2) // Activo
    ->where('categoria.nombre_categoria', 'Herramienta')
    ->select('recurso.nombre as recurso', 'serie_recurso.nro_serie', 'usuario.name as trabajador')
    ->get();

    $herramientasUsadas = SerieRecurso::with('recurso.subcategoria')
        ->whereHas('recurso.subcategoria.categoria', function ($query) {
            $query->where('nombre_categoria', 'Herramienta');
        })
        ->where('id_estado', 3) // En uso
        ->get();



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

    $estadoRecursos = DB::table('serie_recurso')
        ->select('id_estado', DB::raw('count(*) as total'))
        ->groupBy('id_estado')
        ->pluck('total', 'id_estado');

    $estadoLabels = DB::table('estado')
        ->pluck('nombre_estado', 'id');

    $labels = [];
    $valores = [];

    foreach ($estadoLabels as $id => $nombre) {
        $labels[] = $nombre;
        $valores[] = $estadoRecursos[$id] ?? 0;
    }

    $checklistsHoy = Checklist::with(['trabajador', 'supervisor'])
        ->whereDate('fecha', now()->toDateString())
        ->get();





    $alertasActivas = count($alertasVencidos) + count($stockBajo) + count($herramientasNoDevueltas);



    

    $alertasLista = collect();

        // Vencidos
        foreach ($alertasVencidos as $item) {
            $alertasLista->push((object)[
                'tipo' => 'Vencido',
                'descripcion' => $item->recurso->nombre . ' (Serie: ' . $item->nro_serie . ') vencido el ' . \Carbon\Carbon::parse($item->fecha_vencimiento)->format('d/m/Y')
            ]);
        }

        // Stock bajo
        foreach ($stockBajo as $item) {
            $alertasLista->push((object)[
                'tipo' => 'Stock bajo',
                'descripcion' => $item->nombre . ' - Quedan ' . $item->cantidad . ' unidades'
            ]);
        }

        // Sin devolución
        foreach ($herramientasNoDevueltas as $item) {
            $alertasLista->push((object)[
                'tipo' => 'Sin devolución',
                'descripcion' => $item->recurso . ' (Serie: ' . $item->nro_serie . ') - ' . $item->trabajador
            ]);
        }


   
    return view('dashboard', compact(
    'usuariosActivos',
    'herramientasEnUso',
    'herramientasTotales',
    'herramientasDisponibles',
    'alertasVencidos',
    'eppEntregados',
    'totalTrabajadores',
    'porcentajeEntregado',
    'stockBajo',
    'herramientasNoDevueltas',
    'alertasActivas',
    'usuarios',
    'eppStock',
    'eppTotales',
    'elementosDañados',
    'eppVencidos',
    'usuarios',
    'herramientasUsadas',
    'eppEntregadosLista',
    'alertasLista',
    'alertasVencidos',
    'stockBajo',
    'herramientasNoDevueltas',
    'estadoLabels',
    'labels',
    'valores',
    'checklistsHoy',
    'elementosReparacion'
));




}
}