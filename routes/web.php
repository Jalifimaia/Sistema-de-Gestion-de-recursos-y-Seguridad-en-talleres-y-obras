<?php

require __DIR__.'/auth.php';

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecursoController;
use App\Http\Controllers\SubcategoriaController;
use App\Http\Controllers\SerieRecursoController;
use App\Http\Controllers\DashboardController;
use App\Models\Subcategoria;
use App\Models\Recurso;

// Rutas públicas
Route::get('/', fn() => view('welcome'));
Route::get('/inicio2', fn() => view('inicio2'));
Route::get('/herramientas', fn() => view('herramientas'));
Route::get('/dashboard', fn() => view('dashboard'));
Route::get('/controlEPP', fn() => view('controlEPP'));
Route::get('/reportes', function () {
    // Generar recomendaciones en el servidor como fallback inicial
    $recomendaciones = [];
    try {
        $service = new \App\Services\RecomendacionService();
        $recomendaciones = $service->generar();
    } catch (\Throwable $e) {
        // no bloquear la vista si falla
        logger()->error('Error generando recomendaciones al renderizar /reportes: ' . $e->getMessage(), ['exception' => $e]);
    }
    return view('reportes', compact('recomendaciones'));
});
// Ruta pública alternativa que redirige al endpoint API (evita 404 por auth/session)
Route::get('/recomendaciones-publica', fn() => redirect('/api/recomendaciones'));
// Ruta directa que expone el endpoint API también desde web (evita 404 si api.php no fue recargado o hay caché)
Route::get('/api/recomendaciones', [\App\Http\Controllers\RecomendacionController::class, 'index']);
Route::get('/test', fn() => 'Laravel funciona correctamente');

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    Route::resource('usuarios', UserController::class);
    Route::get('/inventario', [RecursoController::class, 'index'])->name('inventario');
    Route::resource('recursos', RecursoController::class);

    // Recomendaciones IA (reglas simples)
    Route::get('/recomendaciones', [\App\Http\Controllers\RecomendacionController::class, 'index'])->name('recomendaciones');

    // ✅ Ruta personalizada para agregar serie con recurso
    Route::get('/serie_recurso/create/{id}', [SerieRecursoController::class, 'createConRecurso'])->name('serie_recurso.createConRecurso');
    Route::post('/serie_recurso/store-multiple', [SerieRecursoController::class, 'storeMultiple'])->name('serie_recurso.storeMultiple');
    // ✅ Rutas RESTful para serie_recurso (sin create)
    Route::resource('serie_recurso', SerieRecursoController::class)->except(['create']);
});

// API para subcategorías
Route::get('/api/subcategorias/{categoria}', fn($categoriaId) => Subcategoria::where('categoria_id', $categoriaId)->get());
Route::post('/api/subcategorias', [SubcategoriaController::class, 'store']);

// Dashboard real
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
