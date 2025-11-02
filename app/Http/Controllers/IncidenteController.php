<?php

namespace App\Http\Controllers;

use App\Models\Incidente;
use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Recurso;
use App\Models\EstadoIncidente;
use App\Models\SerieRecurso;
use Illuminate\Http\Request;

class IncidenteController extends Controller
{
    // =======================
    // LISTAR INCIDENTES
    // =======================
public function index()
{
    $incidentes = Incidente::with([
        'trabajador',                  // 👈 este es el trabajador afectado
        'recurso.subcategoria.categoria',
        'estadoIncidente'
    ])->get();

    return view('incidente.index', compact('incidentes'));
}


    // =======================
    // FORMULARIO CREAR NUEVO
    // =======================
    public function create()
    {
        $categorias = Categoria::with([
            'subcategorias' => function($query) {
                $query->with([
                    'recursos' => function($q) {
                        $q->with('serieRecursos');
                    }
                ]);
            }
        ])->get();

        $trabajadores = Usuario::where('id_rol', 3)->get(); // rol 3 = Trabajador

        return view('incidente.create', compact('categorias', 'trabajadores'));
    }

    // =======================
    // GUARDAR NUEVO INCIDENTE
    // =======================
public function store(Request $request)
{
    $request->validate([
        'id_usuario' => 'required|exists:usuario,id',
        'recursos'   => 'required|array|min:1',
        'recursos.*.id_categoria'    => 'required|exists:categoria,id',
        'recursos.*.id_subcategoria' => 'required|exists:subcategoria,id',
        'recursos.*.id_recurso'      => 'required|exists:recurso,id',
        'recursos.*.id_serie_recurso'=> 'required|exists:serie_recurso,id',
        'descripcion' => 'required|string|max:255',
        'fecha_incidente' => 'required|date',
    ]);

    $usuario = Usuario::where('id', $request->id_usuario)
                      ->where('id_rol', 3)
                      ->firstOrFail();

    $estadoRevision = EstadoIncidente::where('nombre_estado', 'En revisión')->first();

    $incidente = Incidente::create([
        'id_trabajador'       => $usuario->id,
        'id_supervisor'       => auth()->id(),
        'descripcion'         => $request->descripcion,
        'fecha_incidente'     => $request->fecha_incidente,
        'id_estado_incidente' => $estadoRevision?->id,
    ]);

    // Guardar recursos asociados
    foreach ($request->recursos as $recurso) {
        $incidente->recursos()->attach($recurso['id_recurso'], [
            'id_serie_recurso' => $recurso['id_serie_recurso'],
        ]);
    }

    return redirect()->route('incidente.create')->with('success', '✅ Incidente registrado correctamente.');
}




    // =======================
    // MOSTRAR UN INCIDENTE
    // =======================
    public function show($id)
    {
        $incidente = Incidente::with(['trabajador', 'recurso', 'estadoIncidente'])->findOrFail($id);
        return view('incidente.show', compact('incidente'));
    }

    // =======================
    // FORMULARIO EDITAR INCIDENTE
    // =======================
    public function edit($id)
    {
        $incidente = Incidente::with([
            'trabajador',
            'recurso.subcategoria.categoria',
            'estadoIncidente',
            'serieRecurso'
        ])->findOrFail($id);

        $categorias   = Categoria::all();
        $subcategorias= Subcategoria::all();
        $recursos     = Recurso::all();
        $estados      = EstadoIncidente::all();
        $trabajadores = Usuario::where('id_rol', 3)->get();

        return view('incidente.edit', compact('incidente', 'categorias', 'subcategorias', 'recursos', 'estados', 'trabajadores'));
    }

    // =======================
    // ACTUALIZAR INCIDENTE
    // =======================
  public function update(Request $request, $id)
{
    $request->validate([
        'id_trabajador'                  => 'required|exists:usuario,id',
        'descripcion'                    => 'required|string|max:255',
        'id_estado_incidente'            => 'required|exists:estado_incidente,id',
        'fecha_incidente'                => 'required|date',
        'recursos'                       => 'required|array|min:1',
        'recursos.*.id_recurso'          => 'required|exists:recurso,id',
        'recursos.*.id_serie_recurso'    => 'required|exists:serie_recurso,id',
    ]);

    $incidente = Incidente::findOrFail($id);

    // Actualizar datos principales del incidente
    $incidente->update([
        'id_trabajador'       => $request->id_trabajador,
        'descripcion'         => $request->descripcion,
        'id_estado_incidente' => $request->id_estado_incidente,
        'resolucion'          => $request->resolucion,
        'fecha_incidente'     => $request->fecha_incidente,
    ]);

    // 🔹 Actualizar recursos asociados (limpia y vuelve a insertar)
    $incidente->recursos()->detach();

    foreach ($request->recursos as $recurso) {
        $incidente->recursos()->attach($recurso['id_recurso'], [
            'id_serie_recurso' => $recurso['id_serie_recurso'],
        ]);
    }

return redirect()->route('incidente.edit', $id)->with('success', '✅ Incidente actualizado correctamente.');
}


    // =======================
    // ELIMINAR INCIDENTE
    // =======================
    public function destroy($id)
    {
        $incidente = Incidente::findOrFail($id);
        $incidente->delete();

        return redirect()->route('incidente.index')->with('success', '🗑️ Incidente eliminado correctamente.');
    }

    // =======================
    // AJAX: BUSCAR USUARIO POR DNI
    // =======================
public function buscarUsuarioPorDni($dni)
{
    $dni = str_replace(['.', ' '], '', $dni);

    $usuario = \App\Models\Usuario::where('id_rol', 3) // solo trabajadores
        ->whereRaw("REPLACE(REPLACE(dni, '.', ''), ' ', '') = ?", [$dni])
        ->first();

    if ($usuario) {
        return response()->json([
            'id'     => $usuario->id,
            'nombre' => $usuario->name,
        ]);
    }

    return response()->json(['error' => 'Trabajador no encontrado o no es rol válido'], 404);
}


    // Obtener subcategorías por categoría
    public function getSubcategorias($categoriaId)
    {
        $subcategorias = Subcategoria::where('categoria_id', $categoriaId)
                            ->select('id', 'nombre as nombre')
                            ->get();
        return response()->json($subcategorias);
    }

    // Obtener recursos por subcategoría
    public function getRecursos($subcategoriaId)
    {
        $recursos = Recurso::where('id_subcategoria', $subcategoriaId)
                        ->select('id', 'nombre')
                        ->get();
        return response()->json($recursos);
    }

    // Obtener series por recurso
    public function getSeries($recursoId)
    {
        $series = SerieRecurso::where('id_recurso', $recursoId)
                        ->select('id', 'nro_serie')
                        ->get();
        return response()->json($series);
    }
}
