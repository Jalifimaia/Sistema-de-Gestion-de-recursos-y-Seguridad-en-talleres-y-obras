<?php

namespace App\Http\Controllers;

use App\Models\Incidente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\IncidenteRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class IncidenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $incidentes = Incidente::paginate();

        return view('incidente.index', compact('incidentes'))
            ->with('i', ($request->input('page', 1) - 1) * $incidentes->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $incidente = new Incidente();

        return view('incidente.create', compact('incidente'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncidenteRequest $request): RedirectResponse
    {
        Incidente::create($request->validated());

        return Redirect::route('incidentes.index')
            ->with('success', 'Incidente created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $incidente = Incidente::find($id);

        return view('incidente.show', compact('incidente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $incidente = Incidente::find($id);

        return view('incidente.edit', compact('incidente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IncidenteRequest $request, Incidente $incidente): RedirectResponse
    {
        $incidente->update($request->validated());

        return Redirect::route('incidentes.index')
            ->with('success', 'Incidente updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Incidente::find($id)->delete();

        return Redirect::route('incidentes.index')
            ->with('success', 'Incidente deleted successfully');
    }
}
