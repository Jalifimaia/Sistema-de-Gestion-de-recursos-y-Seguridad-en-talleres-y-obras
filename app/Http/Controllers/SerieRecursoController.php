<?php

namespace App\Http\Controllers;

use App\Models\SerieRecurso;
use App\Models\Estado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\SerieRecursoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Http\Controllers\RecursoController;
use App\Models\Recurso;


class SerieRecursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $serieRecursos = SerieRecurso::paginate();

        return view('serie_recurso.index', compact('serieRecursos'))
            ->with('i', ($request->input('page', 1) - 1) * $serieRecursos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */

public function storeMultiple(Request $request): RedirectResponse
{
    $request->validate([
        'id_recurso' => 'required|exists:recurso,id',
        'cantidad' => 'required|integer|min:1|max:100',
        'nro_serie' => 'required|string',
        'talle' => 'nullable|string|max:50',
        'fecha_adquisicion' => 'required|date',
        'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_adquisicion',
        'id_estado' => 'required|exists:estado,id',
    ]);

    for ($i = 1; $i <= $request->cantidad; $i++) {
        SerieRecurso::create([
            'id_recurso' => $request->id_recurso,
            'nro_serie' => $request->nro_serie . ' - ' . str_pad($i, 3, '0', STR_PAD_LEFT),
            'talle' => $request->talle,
            'fecha_adquisicion' => $request->fecha_adquisicion,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'id_estado' => $request->id_estado,
        ]);
    }

    // redirigir a la misma vista de creación con mensaje en sesión para mostrar modal
    return redirect()->route('serie_recurso.createConRecurso', $request->id_recurso)
        ->with('success', 'Serie(s) guardada(s) correctamente.');
}



public function createConRecurso($id)
{
    $recurso = Recurso::findOrFail($id);
    $estados = Estado::all(); 

    return view('serie_recurso.create', compact('recurso', 'estados'));
}




    /**
     * Display the specified resource.
     */
    public function show($id): View
{
    $serieRecurso = SerieRecurso::findOrFail($id);

    return view('serie_recurso.show', compact('serieRecurso'));
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $serieRecurso = SerieRecurso::findOrFail($id);


        return view('serie_recurso.edit', compact('serieRecurso'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SerieRecursoRequest $request, SerieRecurso $serieRecurso): RedirectResponse
    {
        $serieRecurso->update($request->validated());

        return Redirect::route('serie_recurso.index')
            ->with('success', 'SerieRecurso updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        $serie = SerieRecurso::findOrFail($id);
        $serie->delete();


        return Redirect::route('serie_recurso.index')
            ->with('success', 'SerieRecurso deleted successfully');
    }
}
