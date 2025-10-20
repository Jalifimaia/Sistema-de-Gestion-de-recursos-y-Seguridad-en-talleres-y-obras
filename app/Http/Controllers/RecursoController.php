<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\RecursoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Categoria;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RecursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $recursos = Recurso::paginate();

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
            ->select('recurso.nombre', DB::raw('COUNT(*) as cantidad_prestamos'));

        if ($fecha_inicio) {
            $query->where('prestamo.fecha_prestamo', '>=', $fecha_inicio);
        }

        if ($fecha_fin) {
            $query->where('prestamo.fecha_prestamo', '<=', $fecha_fin);
        }

        $recursos = $query
            ->groupBy('recurso.id', 'recurso.nombre')
            ->orderByDesc('cantidad_prestamos')
            ->get();

        return view('reportes.recursosMasPrestados', compact('recursos', 'fecha_inicio', 'fecha_fin'));
    }
    public function recursosMasPrestadosPDF(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');

        $query = DB::table('detalle_prestamo')
            ->join('prestamo', 'detalle_prestamo.id_prestamo', '=', 'prestamo.id')
            ->join('recurso', 'detalle_prestamo.id_recurso', '=', 'recurso.id')
            ->select('recurso.nombre', DB::raw('COUNT(*) as cantidad_prestamos'));

        if ($fecha_inicio) {
            $query->where('prestamo.fecha_prestamo', '>=', $fecha_inicio);
        }

        if ($fecha_fin) {
            $query->where('prestamo.fecha_prestamo', '<=', $fecha_fin);
        }

        $recursos = $query
            ->groupBy('recurso.id', 'recurso.nombre')
            ->orderByDesc('cantidad_prestamos')
            ->get();

        $total = $recursos->sum('cantidad_prestamos');

        $pdf = Pdf::loadView('reportes.recursosMasPrestadosPDF', compact('recursos', 'fecha_inicio', 'fecha_fin', 'total'));
        return $pdf->download('reporte_recursos_mas_prestados.pdf');
    }

    public function recursosEnReparacion(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');

        $query = DB::table('serie_recurso')
            ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
            ->where('serie_recurso.id_estado', 6) // estado "En reparación"
            ->select('recurso.nombre', 'serie_recurso.nro_serie', 'serie_recurso.fecha_adquisicion');

        if ($fecha_inicio) {
            $query->where('serie_recurso.fecha_adquisicion', '>=', $fecha_inicio);
        }

        if ($fecha_fin) {
            $query->where('serie_recurso.fecha_adquisicion', '<=', $fecha_fin);
        }

        $recursos = $query->orderByDesc('serie_recurso.fecha_adquisicion')->get();

        return view('reportes.recursosEnReparacion', compact('recursos', 'fecha_inicio', 'fecha_fin'));
    }
    public function recursosEnReparacionPDF(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');

        $query = DB::table('serie_recurso')
            ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
            ->where('serie_recurso.id_estado', 6)
            ->select('recurso.nombre', 'serie_recurso.nro_serie', 'serie_recurso.fecha_adquisicion');

        if ($fecha_inicio) {
            $query->where('serie_recurso.fecha_adquisicion', '>=', $fecha_inicio);
        }

        if ($fecha_fin) {
            $query->where('serie_recurso.fecha_adquisicion', '<=', $fecha_fin);
        }

        $recursos = $query->orderByDesc('serie_recurso.fecha_adquisicion')->get();
        $total = $recursos->count();

        $pdf = Pdf::loadView('reportes/recursosEnReparacionPDF', compact('recursos', 'fecha_inicio', 'fecha_fin', 'total'));
        return $pdf->download('reporte_recursos_en_reparacion.pdf');
    }


    public function herramientasPorTrabajador(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');

        $query = DB::table('usuario_recurso')
            ->join('usuario', 'usuario_recurso.id_usuario', '=', 'usuario.id')
            ->join('recurso', 'usuario_recurso.id_recurso', '=', 'recurso.id')
            ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
            ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
            ->where('categoria.nombre_categoria', 'Herramienta')
            ->select(
                'usuario.name as trabajador',
                'recurso.nombre as herramienta',
                'usuario_recurso.fecha_asignacion'
            );

        if ($fecha_inicio) {
            $query->where('usuario_recurso.fecha_asignacion', '>=', $fecha_inicio);
        }

        if ($fecha_fin) {
            $query->where('usuario_recurso.fecha_asignacion', '<=', $fecha_fin);
        }

        $herramientas = $query->orderByDesc('usuario_recurso.fecha_asignacion')->get();

        return view('reportes.herramientasPorTrabajador', compact('herramientas', 'fecha_inicio', 'fecha_fin'));
    }
    public function herramientasPorTrabajadorPDF(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');

        $query = DB::table('usuario_recurso')
            ->join('usuario', 'usuario_recurso.id_usuario', '=', 'usuario.id')
            ->join('recurso', 'usuario_recurso.id_recurso', '=', 'recurso.id')
            ->join('subcategoria', 'recurso.id_subcategoria', '=', 'subcategoria.id')
            ->join('categoria', 'subcategoria.categoria_id', '=', 'categoria.id')
            ->where('categoria.nombre_categoria', 'Herramienta')
            ->select(
                'usuario.name as trabajador',
                'recurso.nombre as herramienta',
                'usuario_recurso.fecha_asignacion'
            );

        if ($fecha_inicio) {
            $query->where('usuario_recurso.fecha_asignacion', '>=', $fecha_inicio);
        }

        if ($fecha_fin) {
            $query->where('usuario_recurso.fecha_asignacion', '<=', $fecha_fin);
        }

        $herramientas = $query->orderByDesc('usuario_recurso.fecha_asignacion')->get();
        $total = $herramientas->count();

        $pdf = Pdf::loadView('reportes/herramientasPorTrabajadorPDF', compact('herramientas', 'fecha_inicio', 'fecha_fin', 'total'));
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
            ->select('categoria.nombre_categoria', DB::raw('COUNT(*) as cantidad_incidentes'));

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

        return view('reportes.incidentesPorTipoRecurso', compact('incidentes', 'fecha_inicio', 'fecha_fin'));
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
            ->select('categoria.nombre_categoria', DB::raw('COUNT(*) as cantidad_incidentes'));

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

        Recurso::create([
            'id_subcategoria' => $validated['id_subcategoria'],
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'costo_unitario' => $validated['costo_unitario'],
            'id_usuario_creacion' => auth()->id(),
            'id_usuario_modificacion' => auth()->id(),
        ]);

        return redirect()->route('inventario')->with('success', 'Recurso creado correctamente.');
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
    public function update(RecursoRequest $request, Recurso $recurso): RedirectResponse
    {
        $recurso->update($request->validated());

        return Redirect::route('inventario')
            ->with('success', 'Recurso updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Recurso::find($id)->delete();

        return Redirect::route('inventario')
            ->with('success', 'Recurso deleted successfully');
    }

    public function create()
    {
        $categorias = \App\Models\Categoria::all();
        return view('recurso.create', compact('categorias'));
    }

    public function getSubcategorias($categoriaId)
    {
        return DB::table('subcategoria')->where('categoria_id', $categoriaId)->get();
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
}
