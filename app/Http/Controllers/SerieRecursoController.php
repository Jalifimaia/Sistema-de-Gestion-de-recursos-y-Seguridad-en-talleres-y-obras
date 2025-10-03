<?php

namespace App\Http\Controllers;

use App\Models\SerieRecurso;
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
    public function create($id): View
{
    $recurso = Recurso::findOrFail($id);
    return view('serie_recurso.create', compact('recurso'));
}

public function store(Request $request, $id): RedirectResponse
{
    $request->validate([
        'nro_serie' => 'required|string|unique:serie_recurso,nro_serie',
        'talle' => 'nullable|string|max:50',
        'fecha_adquisicion' => 'required|date',
        'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_adquisicion',
    ]);

    SerieRecurso::create([
        'id_recurso' => $id,
        'nro_serie' => $request->nro_serie,
        'talle' => $request->talle,
        'fecha_adquisicion' => $request->fecha_adquisicion,
        'fecha_vencimiento' => $request->fecha_vencimiento,
    ]);

    return redirect()->route('recursos.index')->with('success', 'Serie agregada correctamente.');
}



    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $serieRecurso = SerieRecurso::find($id);

        return view('serie_recurso.show', compact('serieRecurso'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $serieRecurso = SerieRecurso::find($id);

        return view('serie_recurso.edit', compact('serieRecurso'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SerieRecursoRequest $request, SerieRecurso $serieRecurso): RedirectResponse
    {
        $serieRecurso->update($request->validated());

        return Redirect::route('serie_recursos.index')
            ->with('success', 'SerieRecurso updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        SerieRecurso::find($id)->delete();

        return Redirect::route('serie_recursos.index')
            ->with('success', 'SerieRecurso deleted successfully');
    }
}
