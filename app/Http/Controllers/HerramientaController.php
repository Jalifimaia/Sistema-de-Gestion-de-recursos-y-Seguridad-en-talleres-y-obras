<?php

namespace App\Http\Controllers;

use App\Models\Herramienta;

class HerramientaController extends Controller
{
    public function index()
    {
        $herramientas = Herramienta::all();
        return view('herramientas', compact('herramientas'));
    }
}
