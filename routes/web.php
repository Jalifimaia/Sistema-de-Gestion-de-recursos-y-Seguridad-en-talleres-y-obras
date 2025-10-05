<?php

require __DIR__.'/auth.php';
use App\Models\Recurso;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecursoController;
use App\Http\Controllers\SubcategoriaController;
use App\Http\Controllers\SerieRecursoController;
use App\Http\Controllers\DashboardController;

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
    Route::post('/recursos', [RecursoController::class, 'store']);
    Route::get('/inventario', [RecursoController::class, 'index'])->name('inventario');
    Route::get('/recursos/create', [RecursoController::class, 'create'])->name('recursos.create'); // 👈 movido acá
    Route::resource('recursos', RecursoController::class);
});

Route::resource('serie_recurso', SerieRecursoController::class);


Route::get('/api/subcategorias/{categoria}', function ($categoriaId) {
    return \App\Models\Subcategoria::where('categoria_id', $categoriaId)->get();
});

Route::post('/api/subcategorias', [SubcategoriaController::class, 'store']);



Route::get('/test', function () {
    return 'Laravel funciona correctamente';
});

Route::get('/api/subcategorias/{categoria}', function ($categoriaId) {
    return \App\Models\Subcategoria::where('categoria_id', $categoriaId)->get();
});


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
