<?php

require __DIR__.'/auth.php';
use App\Models\Recurso;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecursoController;
use App\Http\Controllers\SerieRecursoController;

Route::get('/', function () {
   return view('welcome');
});

Route::get('/inicio2', function () {
    return view('inicio2');
});

Route::get('/herramientas', function () {
    return view('herramientas');
});

Route::resource('usuarios', UserController::class);


Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/controlEPP', function () {
    return view('controlEPP');
});


Route::get('/reportes', function () {
    return view('reportes');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('usuarios', UserController::class);
    Route::resource('recursos', RecursoController::class);
    Route::get('/inventario', [RecursoController::class, 'index'])->name('inventario');
});


Route::get('/test', function () {
    return 'Laravel funciona correctamente';
});

Route::get('recursos/{id}/series/create', [SerieRecursoController::class, 'create'])->name('serie_recurso.create');
Route::post('recursos/{id}/series', [SerieRecursoController::class, 'store'])->name('serie_recurso.store');


