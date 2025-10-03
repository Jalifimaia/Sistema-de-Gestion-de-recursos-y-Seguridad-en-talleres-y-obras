<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\EstadoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class EstadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $estados = Estado::paginate();

        return view('estado.index', compact('estados'))
            ->with('i', ($request->input('page', 1) - 1) * $estados->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $estado = new Estado();

        return view('estado.create', compact('estado'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EstadoRequest $request): RedirectResponse
    {
        Estado::create($request->validated());

        return Redirect::route('estados.index')
            ->with('success', 'Estado created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $estado = Estado::find($id);

        return view('estado.show', compact('estado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $estado = Estado::find($id);

        return view('estado.edit', compact('estado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EstadoRequest $request, Estado $estado): RedirectResponse
    {
        $estado->update($request->validated());

        return Redirect::route('estados.index')
            ->with('success', 'Estado updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Estado::find($id)->delete();

        return Redirect::route('estados.index')
            ->with('success', 'Estado deleted successfully');
    }
}
