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


    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
{
    $recurso = new Recurso();
    $categorias = Categoria::all(); // o Categoria si lo renombraste
    $estados = Estado::all();

    return view('recurso.create', compact('recurso', 'categorias', 'estados'));
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(RecursoRequest $request): RedirectResponse
{
    $data = $request->validated();
    $data['id_usuario_creacion'] = auth()->id();
    $data['id_usuario_modificacion'] = auth()->id();

    Recurso::create($data);

    return Redirect::route('recursos.index')
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

        return view('recurso.edit', compact('recurso'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RecursoRequest $request, Recurso $recurso): RedirectResponse
    {
        $recurso->update($request->validated());

        return Redirect::route('recursos.index')
            ->with('success', 'Recurso updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Recurso::find($id)->delete();

        return Redirect::route('recursos.index')
            ->with('success', 'Recurso deleted successfully');
    }
}
