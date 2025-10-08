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

}
