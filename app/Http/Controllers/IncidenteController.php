<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use App\Models\EstadoIncidente;
use Illuminate\Http\Request;
use App\Models\Incidente;

class IncidenteController extends Controller
{
    public function index()
    {
        $incidentes = Incidente::with(['estado', 'recurso'])->orderBy('id', 'asc')->get();
        return view('incidente.index', compact('incidentes'));
    }

    public function create()
    {
        $recursos = Recurso::all();
        $estados = EstadoIncidente::all();
        return view('incidente.create', compact('recursos', 'estados'));
    }

    public function store(Request $request)
    {
        Incidente::create($request->all());
        return redirect()->route('incidente.index')->with('success', 'Incidente registrado correctamente.');
    }

    public function edit($id)
    {
        $incidente = Incidente::findOrFail($id);
        $recursos = Recurso::all();
        $estados = EstadoIncidente::all();
        return view('incidente.edit', compact('incidente', 'recursos', 'estados'));
    }

    public function update(Request $request, $id)
    {
        $incidente = Incidente::findOrFail($id);
        $incidente->update($request->all());
        return redirect()->route('incidente.index')->with('success', 'Incidente actualizado correctamente.');
    }

    public function destroy($id)
    {
        Incidente::findOrFail($id)->delete();
        return redirect()->route('incidente.index')->with('success', 'Incidente eliminado correctamente.');
    }
}
