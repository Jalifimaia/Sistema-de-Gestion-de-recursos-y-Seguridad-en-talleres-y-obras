<?php

namespace App\Http\Controllers;
use App\Models\User;

class DashboardController extends Controller
{

public function index()
{
    $usuariosActivos = User::whereHas('estado', function ($query) {
        $query->where('nombre', 'Alta'); // o 'Activo', según tu tabla estado_usuario
    })->count();

    return view('dashboard', compact('usuariosActivos'));
}
}