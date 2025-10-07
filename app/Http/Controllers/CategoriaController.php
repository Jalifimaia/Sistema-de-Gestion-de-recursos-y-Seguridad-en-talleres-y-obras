<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\CategoriaRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $categoria = Categoria::paginate();

        return view('categoria.index', compact('categoria'))
            ->with('i', ($request->input('page', 1) - 1) * $categoria->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categoria = new Categoria();

        return view('categoria.create', compact('categoria'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoriaRequest $request): RedirectResponse
    {
        Categoria::create($request->validated());

        return Redirect::route('categoria.index')
            ->with('success', 'Categorium created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $categoria = Categoria::find($id);

        return view('categoria.show', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $categoria = Categoria::find($id);

        return view('categoria.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return Redirect::route('categoria.index')
            ->with('success', 'Categorium updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Categoria::find($id)->delete();

        return Redirect::route('categoria.index')
            ->with('success', 'Categorium deleted successfully');
    }
}
