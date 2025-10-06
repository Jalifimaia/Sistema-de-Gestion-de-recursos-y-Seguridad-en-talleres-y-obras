<?php

namespace App\Http\Controllers;

use App\Models\IncidenteDetalle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\IncidenteDetalleRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class IncidenteDetalleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $incidenteDetalles = IncidenteDetalle::paginate();

        return view('incidente-detalle.index', compact('incidenteDetalles'))
            ->with('i', ($request->input('page', 1) - 1) * $incidenteDetalles->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $incidenteDetalle = new IncidenteDetalle();

        return view('incidente-detalle.create', compact('incidenteDetalle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncidenteDetalleRequest $request): RedirectResponse
    {
        IncidenteDetalle::create($request->validated());

        return Redirect::route('incidente-detalles.index')
            ->with('success', 'IncidenteDetalle created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $incidenteDetalle = IncidenteDetalle::find($id);

        return view('incidente-detalle.show', compact('incidenteDetalle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $incidenteDetalle = IncidenteDetalle::find($id);

        return view('incidente-detalle.edit', compact('incidenteDetalle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IncidenteDetalleRequest $request, IncidenteDetalle $incidenteDetalle): RedirectResponse
    {
        $incidenteDetalle->update($request->validated());

        return Redirect::route('incidente-detalles.index')
            ->with('success', 'IncidenteDetalle updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        IncidenteDetalle::find($id)->delete();

        return Redirect::route('incidente-detalles.index')
            ->with('success', 'IncidenteDetalle deleted successfully');
    }
}
