<?php

namespace App\Http\Controllers;

use App\Models\Subcategoria;
use App\Models\Categoria;
use Illuminate\Http\Request;

class SubcategoriaController extends Controller
{
    public function index()
    {
        $subcategorias = Subcategoria::with('categoria')->get();
        return view('subcategorias.index', compact('subcategorias'));
    }

    public function create()
    {
        $categorias = Categoria::all();
        return view('subcategorias.create', compact('categorias'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'nombre' => 'required|string|max:50',
        'categoria_id' => 'required|exists:categoria,id',
    ]);

    // Evitar duplicados
    $existing = Subcategoria::where('nombre', $validated['nombre'])
        ->where('categoria_id', $validated['categoria_id'])
        ->first();

    if ($existing) {
        return response()->json($existing);
    }

    $subcategoria = Subcategoria::create($validated);
    return response()->json($subcategoria);
}


    public function edit(Subcategoria $subcategoria)
    {
        $categorias = Categoria::all();
        return view('subcategorias.edit', compact('subcategoria', 'categorias'));
    }

    public function update(Request $request, Subcategoria $subcategoria)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'categoria_id' => 'required|exists:categoria,id',
        ]);

        $subcategoria->update($request->all());

        return redirect()->route('subcategorias.index')->with('success', 'Subcategoría actualizada correctamente.');
    }

    public function destroy(Subcategoria $subcategoria)
    {
        $subcategoria->delete();
        return redirect()->route('subcategorias.index')->with('success', 'Subcategoría eliminada.');
    }
}
