<?php

namespace App\Http\Controllers;

use App\Models\EstadoIncidente;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\EstadoIncidenteRequest;

class EstadoIncidenteController extends Controller
{
    public function index(): View
    {
        $estados = EstadoIncidente::all();
        return view('estado_incidente.index', compact('estados'));
    }

    public function create(): View
{
    return view('estado_incidente.create');
}

    public function store(EstadoIncidenteRequest $request): RedirectResponse
{
    EstadoIncidente::create($request->validated());

    return redirect()->route('estado_incidente.index')
        ->with('success', 'Estado creado correctamente.');
}

    public function edit($id): View
{
    $estado = EstadoIncidente::findOrFail($id);
    return view('estado_incidente.edit', compact('estado'));
}



    public function update(EstadoIncidenteRequest $request, $id): RedirectResponse
{
    $estado = EstadoIncidente::findOrFail($id);
    $estado->update($request->validated());

    return redirect()->route('estado_incidente.index')
        ->with('success', 'Estado actualizado correctamente.');
}

    public function destroy($id): RedirectResponse
    {
        EstadoIncidente::findOrFail($id)->delete();

        return redirect()->route('estado_incidente.index')
            ->with('success', 'Estado eliminado correctamente.');
    }
}
