<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
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
    $usuarios = Usuario::where('id_estado', 1)->get();

    $usuariosActivos = Usuario::where('id_estado', 1)->count();

    $usuariosActivosLista = Usuario::where('id_estado', 1)
        ->paginate(6, ['*'], 'usuarios_activos_page');

    $herramientasEnUso = SerieRecurso::where('id_estado', 3)->count();

    $herramientasEnUsoLista = SerieRecurso::with('recurso.subcategoria.categoria')
        ->whereHas('recurso.subcategoria.categoria', function ($query) {
            $query->where('nombre_categoria', 'Herramienta');
        })
        ->where('id_estado', 3)
        ->paginate(6, ['*'], 'herramientas_uso_page');


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

    $eppStock = DB::table('serie_recurso')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->where('categoria.nombre_categoria', 'EPP')
        ->where('serie_recurso.id_estado', 1)
        ->count();

    $eppTotales = DB::table('serie_recurso')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->where('categoria.nombre_categoria', 'EPP')
        ->count();

    $elementosReparacion = DB::table('serie_recurso')
        ->where('id_estado', 6)
        ->count();

    $totalTrabajadores = DB::table('usuario')
        ->where('id_rol', 3)
        ->count();

    $eppEntregados = DB::table('serie_recurso')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->where('categoria.nombre_categoria', 'EPP')
        ->where('serie_recurso.id_estado', 4)
        ->count();

    $eppEntregadosLista = DB::table('detalle_prestamo')
        ->join('serie_recurso', 'detalle_prestamo.id_serie', '=', 'serie_recurso.id')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->join('prestamo', 'detalle_prestamo.id_prestamo', '=', 'prestamo.id')
        ->join('usuario', 'prestamo.id_usuario', '=', 'usuario.id')
        ->where('categoria.nombre_categoria', 'EPP')
        ->where('detalle_prestamo.id_estado_prestamo', 2)
        ->select(
            'usuario.name as trabajador',
            'recurso.nombre as elemento',
            'prestamo.fecha_prestamo as fecha_entrega'
        )
        ->get();

    $alertasVencidas = SerieRecurso::with('recurso')
        ->whereNotNull('fecha_vencimiento')
        ->where('fecha_vencimiento', '<', now())
        ->paginate(6, ['*'], 'vencidos_page');

    $stockBajo = DB::table('serie_recurso')
    ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
    ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id') 
    ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
    ->select(
        'recurso.nombre as recurso',
        'subcategoria.nombre as subcategoria',   
        DB::raw('COUNT(*) as cantidad')
    )
    ->where('categoria.nombre_categoria', 'EPP')
    ->where('serie_recurso.id_estado', 1)
    ->groupBy('recurso.nombre', 'subcategoria.nombre') 
    ->having('cantidad', '<', 5)
    ->paginate(6, ['*'], 'stock_page');


    $herramientasNoDevueltas = DB::table('detalle_prestamo')
    ->join('serie_recurso', 'detalle_prestamo.id_serie', '=', 'serie_recurso.id')
    ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
    ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
    ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
    ->join('prestamo', 'detalle_prestamo.id_prestamo', '=', 'prestamo.id')
    ->join('usuario', 'prestamo.id_usuario', '=', 'usuario.id')
    ->where('detalle_prestamo.id_estado_prestamo', 2)
    ->where('categoria.nombre_categoria', 'Herramienta')
    ->select(
        'recurso.nombre as recurso',
        'subcategoria.nombre as subcategoria',  
        'serie_recurso.nro_serie',
        'usuario.name as trabajador'
    )
    ->paginate(6, ['*'], 'herramientas_page');


    $alertasActivas = $alertasVencidas->total()
                    + $stockBajo->total()
                    + $herramientasNoDevueltas->total();

    $herramientasUsadas = SerieRecurso::with('recurso.subcategoria')
        ->whereHas('recurso.subcategoria.categoria', function ($query) {
            $query->where('nombre_categoria', 'Herramienta');
        })
        ->where('id_estado', 3)
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
        ->whereDate('fecha', Carbon::today())
        ->take(5)
        ->get();

    $porcentajeEntregado = $totalTrabajadores > 0
        ? round(($eppEntregados / $totalTrabajadores) * 100)
        : 0;

    return view('dashboard', compact(
        'usuariosActivos',
        'usuariosActivosLista',
        'usuarios',
        'herramientasEnUso',
        'herramientasEnUsoLista',
        'herramientasTotales',
        'herramientasDisponibles',
        'eppStock',
        'eppTotales',
        'elementosReparacion',
        'totalTrabajadores',
        'eppEntregados',
        'eppEntregadosLista',
        'porcentajeEntregado',
        'herramientasUsadas',
        'eppVencidos',
        'elementosDañados',
        'estadoLabels',
        'labels',
        'valores',
        'checklistsHoy',
        'alertasVencidas',
        'stockBajo',
        'herramientasNoDevueltas',
        'alertasActivas'
    ));
}

}