<?php

namespace App\Http\Controllers;

use App\Models\Epp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\EppRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class EppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function store(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'descripcion' => 'required|string',
    ]);

    Epp::create($request->all());

    return redirect()->route('epps.index')
        ->with('success', 'Epp created successfully.');
}

public function update(Request $request, Epp $epp)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'descripcion' => 'required|string',
    ]);

    $epp->update($request->all());

    return redirect()->route('epps.index')
        ->with('success', 'Epp updated successfully.');
}

public function destroy(Epp $epp)
{
    $epp->delete();

    return redirect()->route('epps.index')
        ->with('success', 'Epp deleted successfully.');
}

public function index()
{
    $epps = Epp::all(); // Trae todos los EPP

    return view('inventario', compact('epps'));
}


}
