<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\PrestamoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PrestamoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $prestamos = Prestamo::paginate();

        return view('prestamo.index', compact('prestamos'))
            ->with('i', ($request->input('page', 1) - 1) * $prestamos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $prestamo = new Prestamo();

        return view('prestamo.create', compact('prestamo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PrestamoRequest $request): RedirectResponse
    {
        Prestamo::create($request->validated());

        return Redirect::route('prestamos.index')
            ->with('success', 'Prestamo created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $prestamo = Prestamo::find($id);

        return view('prestamo.show', compact('prestamo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $prestamo = Prestamo::find($id);

        return view('prestamo.edit', compact('prestamo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PrestamoRequest $request, Prestamo $prestamo): RedirectResponse
    {
        $prestamo->update($request->validated());

        return Redirect::route('prestamos.index')
            ->with('success', 'Prestamo updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Prestamo::find($id)->delete();

        return Redirect::route('prestamos.index')
            ->with('success', 'Prestamo deleted successfully');
    }
}
