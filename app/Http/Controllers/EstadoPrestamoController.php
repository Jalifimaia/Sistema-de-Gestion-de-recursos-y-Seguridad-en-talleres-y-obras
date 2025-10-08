<?php

namespace App\Http\Controllers;

use App\Models\EstadoPrestamo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\EstadoPrestamoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class EstadoPrestamoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $estadoPrestamos = EstadoPrestamo::paginate();

        return view('estado-prestamo.index', compact('estadoPrestamos'))
            ->with('i', ($request->input('page', 1) - 1) * $estadoPrestamos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $estadoPrestamo = new EstadoPrestamo();

        return view('estado-prestamo.create', compact('estadoPrestamo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EstadoPrestamoRequest $request): RedirectResponse
    {
        EstadoPrestamo::create($request->validated());

        return Redirect::route('estado-prestamos.index')
            ->with('success', 'EstadoPrestamo created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $estadoPrestamo = EstadoPrestamo::find($id);

        return view('estado-prestamo.show', compact('estadoPrestamo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $estadoPrestamo = EstadoPrestamo::find($id);

        return view('estado-prestamo.edit', compact('estadoPrestamo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EstadoPrestamoRequest $request, EstadoPrestamo $estadoPrestamo): RedirectResponse
    {
        $estadoPrestamo->update($request->validated());

        return Redirect::route('estado-prestamos.index')
            ->with('success', 'EstadoPrestamo updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        EstadoPrestamo::find($id)->delete();

        return Redirect::route('estado-prestamos.index')
            ->with('success', 'EstadoPrestamo deleted successfully');
    }
}
