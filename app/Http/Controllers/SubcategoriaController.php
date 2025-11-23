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
            'nombre' => 'required|string|max:100',
            'categoria_id' => 'required|exists:categoria,id',
        ]);

        // Normalizar el nombre: convertir a minúsculas y quitar espacios extras
        $nombreNormalizado = strtolower(trim($validated['nombre']));

        // Verificar si ya existe una subcategoría con el mismo nombre en la misma categoría
        $existe = Subcategoria::whereRaw('LOWER(TRIM(nombre)) = ?', [$nombreNormalizado])
            ->where('categoria_id', $validated['categoria_id'])
            ->first();

        if ($existe) {
            return response()->json([
                'error' => 'Ya existe una subcategoría con ese nombre en esta categoría.'
            ], 409);
        }

        // Crear la subcategoría con el nombre original (sin normalizar)
        $subcategoria = Subcategoria::create($validated);
        
        return response()->json($subcategoria, 201);
    }

    public function edit(Subcategoria $subcategoria)
    {
        $categorias = Categoria::all();
        return view('subcategorias.edit', compact('subcategoria', 'categorias'));
    }

    public function update(Request $request, Subcategoria $subcategoria)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'categoria_id' => 'required|exists:categoria,id',
        ]);

        // Normalizar el nombre para verificar duplicados
        $nombreNormalizado = strtolower(trim($validated['nombre']));

        // Verificar si ya existe otra subcategoría con el mismo nombre en la misma categoría
        // (excluyendo la subcategoría actual)
        $existe = Subcategoria::whereRaw('LOWER(TRIM(nombre)) = ?', [$nombreNormalizado])
            ->where('categoria_id', $validated['categoria_id'])
            ->where('id', '!=', $subcategoria->id)
            ->first();

        if ($existe) {
            return redirect()->back()
                ->withErrors(['nombre' => 'Ya existe una subcategoría con ese nombre en esta categoría.'])
                ->withInput();
        }

        $subcategoria->update($validated);

        return redirect()->route('subcategorias.index')
            ->with('success', 'Subcategoría actualizada correctamente.');
    }

    public function destroy(Subcategoria $subcategoria)
    {
        $subcategoria->delete();
        return redirect()->route('subcategorias.index')
            ->with('success', 'Subcategoría eliminada.');
    }
    
    public function byCategoria($categoriaId)
    {
        return Subcategoria::where('categoria_id', $categoriaId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }
}