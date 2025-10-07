<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use App\Models\IncidenteDetalle;
use App\Models\EstadoIncidente;
use App\Models\Incidente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\IncidenteRequest;
use App\Http\Requests\EstadoIncidenteRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class IncidenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    
    public function index()
{
    $incidentes = Incidente::orderByDesc('fecha_creacion')->paginate(10);

    return view('incidente.index', compact('incidentes'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
{
    $recursos = Recurso::all();
    $estados = EstadoIncidente::all();
    return view('incidente.create', compact('recursos', 'estados'));
}



    /**
     * Store a newly created resource in storage.
     */
    public function store(IncidenteRequest $request): RedirectResponse
{
    if (auth()->user()->rol !== 'supervisor') {
        return redirect()->back()->with('error', 'Solo el supervisor puede registrar incidentes.');
    }

    $data = $request->validated();
    $data['id_usuario_creacion'] = auth()->id();
    $data['id_usuario_modificacion'] = auth()->id();
    $data['fecha_creacion'] = now();
    $data['fecha_modificacion'] = now();

    $incidente = Incidente::create($data);

    // Crear el detalle del incidente
    IncidenteDetalle::create([
        'id_incidente' => $incidente->id,
        'descripcion' => $request->detalle_descripcion,
        // Si usás series o recursos específicos, podés agregar más campos acá
    ]);

    return Redirect::route('incidente.index')
        ->with('success', 'Incidente registrado correctamente.');
}




    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $incidente = Incidente::findOrFail($id);


        return view('incidente.show', compact('incidente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
{
    $incidente = Incidente::findOrFail($id);
    return view('incidente.edit', compact('incidente'));
}


    /**
     * Update the specified resource in storage.
     */
    public function update(IncidenteRequest $request, Incidente $incidente): RedirectResponse
    {
        $data = $request->validated();
        $data['id_usuario_modificacion'] = auth()->id();
        $data['fecha_modificacion'] = now();

        $incidente->update($data);


        return Redirect::route('incidente.index')
            ->with('success', 'Incidente updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Incidente::findOrFail($id)->delete();


        return Redirect::route('incidente.index')
            ->with('success', 'Incidente deleted successfully');
    }
}
