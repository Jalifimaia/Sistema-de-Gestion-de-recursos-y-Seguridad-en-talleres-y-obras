<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\RecursoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Categoria;
use App\Models\Estado;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class RecursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
{
    $recursos = Recurso::with(['serieRecursos.estado', 'categoria'])->paginate();


        return view('inventario', compact('recursos'))
            ->with('i', ($request->input('page', 1) - 1) * $recursos->perPage());
    }

    public function recursosMasPrestados(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        

        $query = DB::table('detalle_prestamo')
        ->join('prestamo', 'detalle_prestamo.id_prestamo', '=', 'prestamo.id')
        ->join('recurso', 'detalle_prestamo.id_recurso', '=', 'recurso.id')
        ->select(
            'recurso.nombre',
            DB::raw('COUNT(*) as cantidad_prestamos'),
            DB::raw('MAX(prestamo.fecha_prestamo) as ultima_fecha')
        );

        if ($fecha_inicio) {
            $query->where('prestamo.fecha_prestamo', '>=', $fecha_inicio);
        }

        if ($fecha_fin) {
            $query->where('prestamo.fecha_prestamo', '<=', Carbon::parse($fecha_fin)->endOfDay());
        }

        $recursos = $query
            ->groupBy('recurso.id', 'recurso.nombre')
            ->orderByDesc('ultima_fecha')
            ->get();

        $labels = $recursos->pluck('nombre');
        $valores = $recursos->pluck('cantidad_prestamos');


        return view('reportes.recursosMasPrestados', compact('recursos', 'fecha_inicio', 'fecha_fin', 'labels', 'valores'));
    }
    public function recursosMasPrestadosPDF(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');

        $query = DB::table('detalle_prestamo')
            ->join('prestamo', 'detalle_prestamo.id_prestamo', '=', 'prestamo.id')
            ->join('recurso', 'detalle_prestamo.id_recurso', '=', 'recurso.id')
            ->select(
        'recurso.nombre',
        DB::raw('COUNT(*) as cantidad_prestamos'),
        DB::raw('MAX(prestamo.fecha_prestamo) as ultima_fecha')
    );


        if ($fecha_inicio) {
            $query->where('prestamo.fecha_prestamo', '>=', $fecha_inicio);
        }

        if ($fecha_fin) {
            $query->where('prestamo.fecha_prestamo', '<=', $fecha_fin);
        }

        $recursos = $query
            ->groupBy('recurso.id', 'recurso.nombre')
            ->orderByDesc('ultima_fecha')
            ->get();

        $total = $recursos->sum('cantidad_prestamos');

        $pdf = Pdf::loadView('reportes.recursosMasPrestadosPDF', compact('recursos', 'fecha_inicio', 'fecha_fin', 'total'));
        return $pdf->download('reporte_recursos_mas_prestados.pdf');
    }

    public function recursosEnReparacion(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin    = $request->input('fecha_fin');
        $estadoId     = 6; // id "En reparación"

        $query = DB::table('serie_recurso as sr')
            ->join('recurso as r', 'sr.id_recurso', '=', 'r.id')
            ->join('subcategoria as sc', 'r.id_subcategoria', '=', 'sc.id')
            ->join('categoria as c', 'sc.categoria_id', '=', 'c.id')
            ->join('incidente_recurso as ir', function($join) {
                $join->on('ir.id_recurso', '=', 'r.id')
                    ->on('ir.id_serie_recurso', '=', 'sr.id');
            })
            ->join('incidente as i', 'i.id', '=', 'ir.id_incidente')
            ->where('ir.id_estado', $estadoId)
            ->select(
                'c.nombre_categoria as categoria',
                'sc.nombre as subcategoria',
                'r.nombre as recurso',
                'sr.nro_serie',
                'sr.fecha_adquisicion',
                'ir.updated_at as estado_actualizado_en',
                'i.id as incidente_id'
            )
            ->distinct();

        if ($fecha_inicio) {
            $query->whereDate('ir.updated_at', '>=', $fecha_inicio);
        }
        
        if ($fecha_fin) {
            $query->where('prestamo.fecha_prestamo', '<=', Carbon::parse($fecha_fin)->endOfDay());
        }

        $recursos = $query->orderByDesc('ir.updated_at')->get();

        // Opcional: eliminar duplicados por nro_serie
        $recursos = $recursos->unique('nro_serie')->values();

        // Agrupar por categoría para el gráfico
        $agrupado = $recursos->groupBy('categoria')->map(function ($items, $nombre) {
            return [
                'tipo' => $nombre,
                'cantidad' => $items->count()
            ];
        })->values();

        $labels = $agrupado->pluck('tipo');
        $valores = $agrupado->pluck('cantidad');

        return view('reportes.recursosEnReparacion', compact('recursos', 'fecha_inicio', 'fecha_fin', 'labels', 'valores'));
    }




    public function recursosEnReparacionPDF(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin    = $request->input('fecha_fin');
        $estadoId     = 6; // id "En reparación"

        $query = DB::table('serie_recurso as sr')
            ->join('recurso as r', 'sr.id_recurso', '=', 'r.id')
            ->join('subcategoria as sc', 'r.id_subcategoria', '=', 'sc.id')
            ->join('categoria as c', 'sc.categoria_id', '=', 'c.id') // ajustá si tu columna es id_categoria
            ->join('incidente_recurso as ir', function($join) {
                $join->on('ir.id_recurso', '=', 'r.id')
                    ->on('ir.id_serie_recurso', '=', 'sr.id');
            })
            ->join('incidente as i', 'i.id', '=', 'ir.id_incidente')
            ->where('ir.id_estado', $estadoId)
            ->select(
                'c.nombre_categoria as categoria',
                'sc.nombre as subcategoria',
                'r.nombre as recurso',
                'sr.nro_serie',
                'sr.fecha_adquisicion',
                'ir.updated_at as estado_actualizado_en',
                'i.id as incidente_id'
            )
            ->distinct();

        if ($fecha_inicio) {
            $query->whereDate('ir.updated_at', '>=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $query->whereDate('ir.updated_at', '<=', $fecha_fin);
        }

        $recursos = $query->orderByDesc('ir.updated_at')->get();

        // eliminar duplicados por serie si hiciera falta
        $recursos = $recursos->unique('nro_serie')->values();

        $total = $recursos->count();

        $pdf = Pdf::loadView('reportes.recursosEnReparacionPDF', compact('recursos', 'fecha_inicio', 'fecha_fin', 'total'));

        // forzar descarga
        return $pdf->download('reporte_recursos_en_reparacion.pdf');
    }




    public function herramientasPorTrabajador(Request $request)
{
    $fecha_inicio = $request->input('fecha_inicio');
    $fecha_fin = $request->input('fecha_fin');

    $query = DB::table('serie_recurso')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->join('usuario', 'recurso.id_usuario_creacion', '=', 'usuario.id')
        ->where('categoria.nombre_categoria', 'Herramienta')
        ->select(
            'usuario.name as trabajador',
            'recurso.nombre as herramienta',
            'serie_recurso.nro_serie',
            'subcategoria.nombre as subcategoria',
            'serie_recurso.fecha_adquisicion'
        );

    if ($fecha_inicio) {
        $query->where('serie_recurso.fecha_adquisicion', '>=', $fecha_inicio);
    }

    if ($fecha_fin) {
     $query->where('serie_recurso.fecha_adquisicion', '<=', Carbon::parse($fecha_fin)->endOfDay());
    }

    $herramientas = $query->orderByDesc('serie_recurso.fecha_adquisicion')->get();

    // 🔧 Agrupar por trabajador y contar herramientas
    $agrupado = $herramientas->groupBy('trabajador')->map(function ($items, $trabajador) {
        return [
            'trabajador' => $trabajador,
            'cantidad' => $items->count()
        ];
    })->values();

    // 🔧 Preparar datos para el gráfico
    $labels = $agrupado->pluck('trabajador');
    $valores = $agrupado->pluck('cantidad');

    return view('reportes.herramientasPorTrabajador', compact('herramientas', 'fecha_inicio', 'fecha_fin', 'labels', 'valores'));
}

    public function herramientasPorTrabajadorPDF(Request $request)
{
    $fecha_inicio = $request->input('fecha_inicio');
    $fecha_fin = $request->input('fecha_fin');

    $query = DB::table('serie_recurso')
        ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->join('usuario', 'recurso.id_usuario_creacion', '=', 'usuario.id')
        ->where('categoria.nombre_categoria', 'Herramienta')
        ->select(
            'usuario.name as trabajador',
            'recurso.nombre as herramienta',
            'subcategoria.nombre as subcategoria',
            'serie_recurso.nro_serie',
            'serie_recurso.fecha_adquisicion'
        );

    if ($fecha_inicio) {
        $query->where('serie_recurso.fecha_adquisicion', '>=', $fecha_inicio);
    }

    if ($fecha_fin) {
        $query->where('serie_recurso.fecha_adquisicion', '<=', $fecha_fin);
    }

    $herramientas = $query->orderByDesc('serie_recurso.fecha_adquisicion')->get();
    $total = $herramientas->count();

    $pdf = Pdf::loadView('reportes.herramientasPorTrabajadorPDF', compact('herramientas', 'fecha_inicio', 'fecha_fin', 'total'));
    return $pdf->download('reporte_herramientas_por_trabajador.pdf');
}



    public function incidentesPorTipo(Request $request)
{
    $fecha_inicio = $request->input('fecha_inicio');
    $fecha_fin = $request->input('fecha_fin');

    $query = DB::table('incidente_recurso')
        ->join('recurso', 'incidente_recurso.id_recurso', '=', 'recurso.id')
        ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
        ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
        ->join('incidente', 'incidente_recurso.id_incidente', '=', 'incidente.id')
        ->select(
            'categoria.nombre_categoria',
            DB::raw('COUNT(*) as cantidad_incidentes'),
            DB::raw('MAX(incidente.fecha_incidente) as ultima_fecha')
        );

    if ($fecha_inicio) {
        $query->where('incidente.fecha_incidente', '>=', Carbon::parse($fecha_inicio)->startOfDay());
    }

    if ($fecha_fin) {
        $query->where('incidente.fecha_incidente', '<=', Carbon::parse($fecha_fin)->endOfDay());
    }

    $incidentes = $query
        ->groupBy('categoria.nombre_categoria')
        ->orderByDesc('cantidad_incidentes')
        ->get();

    // 🔧 Filtrar solo categorías relevantes para el gráfico
    $filtrados = $incidentes->filter(function ($item) {
        return in_array($item->nombre_categoria, ['Herramienta', 'EPP']);
    });

    $labels = $filtrados->pluck('nombre_categoria');
    $valores = $filtrados->pluck('cantidad_incidentes');

    return view('reportes.incidentesPorTipoRecurso', compact(
        'incidentes',
        'fecha_inicio',
        'fecha_fin',
        'labels',
        'valores'
    ));
}

    public function incidentesPorTipoPDF(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');

        $query = DB::table('incidente_recurso')
            ->join('recurso', 'incidente_recurso.id_recurso', '=', 'recurso.id')
            ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
            ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
            ->join('incidente', 'incidente_recurso.id_incidente', '=', 'incidente.id')
            ->select(
            'categoria.nombre_categoria',
            DB::raw('COUNT(*) as cantidad_incidentes'),
            DB::raw('MAX(incidente.fecha_incidente) as ultima_fecha')
        );

        if ($fecha_inicio) {
            $query->where('incidente.fecha_incidente', '>=', $fecha_inicio);
        }

        if ($fecha_fin) {
            $query->where('incidente.fecha_incidente', '<=', $fecha_fin);
        }

        $incidentes = $query
            ->groupBy('categoria.nombre_categoria')
            ->orderByDesc('cantidad_incidentes')
            ->get();

        $total = $incidentes->sum('cantidad_incidentes');

        $pdf = Pdf::loadView('reportes/incidentesPorTipoPDF', compact('incidentes', 'fecha_inicio', 'fecha_fin', 'total'));
        return $pdf->download('reporte_incidentes_por_tipo.pdf');
    }

    

    public function store(RecursoRequest $request)
{
    $validated = $request->validated();

    $costo = str_replace('.', '', $validated['costo_unitario']); 
    $costo = str_replace(',', '.', $costo);

    $recurso = Recurso::create([
        'id_subcategoria' => $validated['id_subcategoria'],
        'nombre' => $validated['nombre'],
        'descripcion' => $validated['descripcion'] ?? null,
        'costo_unitario' => $costo,
        'id_usuario_creacion' => auth()->id(),
        'id_usuario_modificacion' => auth()->id(),
    ]);

    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Recurso creado correctamente.',
            'recurso' => $recurso,
        ]);
    }

    return redirect()->route('recursos.create')
        ->with('success', 'Recurso creado correctamente.');
}

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $recurso = Recurso::find($id);

        return view('recurso.show', compact('recurso'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $recurso = Recurso::find($id);
        $categorias = Categoria::all();
        $subcategorias = [];

        if ($recurso && $recurso->id_subcategoria) {
            $subcategoria = \App\Models\Subcategoria::find($recurso->id_subcategoria);
            if ($subcategoria) {
                $subcategorias = \App\Models\Subcategoria::where('categoria_id', $subcategoria->categoria_id)->get();
            }
        }

        return view('recurso.edit', compact('recurso', 'categorias', 'subcategorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RecursoRequest $request, $id)
{
    $validated = $request->validated();

    // Normalizar costo_unitario: quitar separadores de miles y convertir coma a punto
    $costo = str_replace('.', '', $validated['costo_unitario']); // quita puntos de miles
    $costo = str_replace(',', '.', $costo); // convierte coma decimal en punto

    $recurso = Recurso::findOrFail($id);
    $recurso->update([
        'nombre' => $validated['nombre'],
        'descripcion' => $validated['descripcion'] ?? null,
        'costo_unitario' => $costo,
        'id_usuario_modificacion' => auth()->id(),
    ]);

    return redirect()->route('recursos.edit', $recurso->id)
        ->with('success', 'Recurso actualizado correctamente.');
}



public function destroy($id)
{
    Log::info("Entró a destroy con ID: $id");

    $recurso = Recurso::with('serieRecursos.detallePrestamos')->findOrFail($id);

    // Caso 2: alguna serie tiene préstamos activos → no se puede dar de baja
    $tienePrestamos = $recurso->serieRecursos->some(function ($serie) {
        return $serie->detallePrestamos()->exists();
    });

    if ($tienePrestamos) {
        return request()->expectsJson()
            ? response()->json(['error' => 'Este recurso tiene series con préstamos registrados. No se puede dar de baja.'], 422)
            : redirect()->route('inventario.index')->with('error_modal', 'Este recurso tiene series con préstamos registrados. No se puede dar de baja.');
    }

    // Obtener el ID del estado "Baja"
    $estadoBajaId = Estado::whereRaw('LOWER(nombre_estado) = ?', ['baja'])->value('id');

    // Caso 1: no tiene series → igual se puede dar de baja
    if ($recurso->serieRecursos->isEmpty()) {
        $recurso->id_estado  = $estadoBajaId;
        $recurso->updated_at = now('UTC');
        $recurso->save();
    } else {
        // Caso 3: tiene series pero ninguna comprometida → se marcan todas como baja
        $recurso->serieRecursos()->update([
            'id_estado'   => $estadoBajaId,
            'updated_at'  => now('UTC'),
        ]);

        // También marcamos el recurso como baja
        $recurso->id_estado  = $estadoBajaId;
        $recurso->updated_at = now('UTC');
        $recurso->save();
    }

    // Respuesta según tipo de petición
    return request()->expectsJson()
        ? response()->json(['ok' => true, 'message' => 'Recurso marcado como dado de baja'])
        : redirect()->route('inventario.index')->with('success_modal', 'Recurso marcado como dado de baja.');
}






public function create()
{
    $categorias = Categoria::all();
    return view('recurso.create', compact('categorias'));
}

   public function getSubcategorias($categoriaId)
{
    $subcategorias = \App\Models\Subcategoria::where('categoria_id', $categoriaId)->get();
    return response()->json($subcategorias);
}




    public function getRecursos($subcategoriaId)
    {
        return DB::table('recurso')->where('id_subcategoria', $subcategoriaId)->get();
    }

    public function getSeries($recursoId)
    {
        return DB::table('serie_recurso')
            ->where('id_recurso', $recursoId)
            ->where('id_estado', 1) // solo disponibles
            ->get();
    }

public function darDeBaja(Request $request, Recurso $recurso)
{
    $estadoBajaId = Estado::whereRaw('LOWER(nombre_estado) = ?', ['baja'])->value('id');

    if (!$estadoBajaId) {
        return response()->json(['error' => 'Estado "Baja" no encontrado'], 422);
    }

    // Marcar todas las series como baja
    $recurso->serieRecursos()->update([
        'id_estado' => $estadoBajaId,
        'updated_at' => now(),
    ]);

    return response()->json(['ok' => true, 'message' => 'Recurso marcado como baja']);
}

}
