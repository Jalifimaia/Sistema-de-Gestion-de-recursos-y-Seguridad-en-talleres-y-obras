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


public function store(RecursoRequest $request)
{
    $validated = $request->validated();

    $recurso = Recurso::create([
        'id_subcategoria' => $validated['id_subcategoria'],
        'nombre' => $validated['nombre'],
        'descripcion' => $validated['descripcion'] ?? null,
        'costo_unitario' => $validated['costo_unitario'],
        'id_usuario_creacion' => auth()->id(),
        'id_usuario_modificacion' => auth()->id(),
    ]);

    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Recurso creado correctamente.',
            'recurso' => $recurso
        ]);
    }

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
    $categorias = Categoria::all();
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
