<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HerramientaController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inicio2', function () {
    return view('inicio2');
});

Route::get('/herramientas', function () {
    return view('herramientas');
});


Route::get('/herramientas', [HerramientaController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/controlEPP', function () {
    return view('controlEPP');
});


Route::get('/reportes', function () {
    return view('reportes');
});

Route::get('/usuarios', function () {
    return view('usuarios');
});

Route::get('/inventario', function () {
    return view('inventario');
});
