<?php

namespace App\Http\Controllers;

use App\Models\DetallePrestamo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\DetallePrestamoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class DetallePrestamoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $detallePrestamos = DetallePrestamo::paginate();

        return view('detalle-prestamo.index', compact('detallePrestamos'))
            ->with('i', ($request->input('page', 1) - 1) * $detallePrestamos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $detallePrestamo = new DetallePrestamo();

        return view('detalle-prestamo.create', compact('detallePrestamo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DetallePrestamoRequest $request): RedirectResponse
    {
        DetallePrestamo::create($request->validated());

        return Redirect::route('detalle-prestamos.index')
            ->with('success', 'DetallePrestamo created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $detallePrestamo = DetallePrestamo::find($id);

        return view('detalle-prestamo.show', compact('detallePrestamo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $detallePrestamo = DetallePrestamo::find($id);

        return view('detalle-prestamo.edit', compact('detallePrestamo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DetallePrestamoRequest $request, DetallePrestamo $detallePrestamo): RedirectResponse
    {
        $detallePrestamo->update($request->validated());

        return Redirect::route('detalle-prestamos.index')
            ->with('success', 'DetallePrestamo updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        DetallePrestamo::find($id)->delete();

        return Redirect::route('detalle-prestamos.index')
            ->with('success', 'DetallePrestamo deleted successfully');
    }
}
