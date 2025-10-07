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
Route::get('/reportes', fn() => view('reportes'));
Route::get('/test', fn() => 'Laravel funciona correctamente');

// Vistas para el rol Operario (estáticas por ahora)
Route::get('/operario/solicitar', fn() => view('operario.solicitar'));
Route::get('/operario/mis-herramientas', fn() => view('operario.mis_herramientas'));
Route::get('/operario/devolver', fn() => view('operario.devolver'));
Route::get('/operario/epp', fn() => view('operario.epp'));

// Vistas para el rol Supervisor (estáticas por ahora)
Route::get('/supervisor/control-herramientas', fn() => view('supervisor.control_herramientas'));
Route::get('/supervisor/registrar-incidente', fn() => view('supervisor.registrar_incidente'));
Route::get('/supervisor/reportes', fn() => view('supervisor.reportes'));
Route::get('/supervisor/checklist-epp', fn() => view('supervisor.checklist_epp'));

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    Route::resource('usuarios', UserController::class);
    Route::get('/inventario', [RecursoController::class, 'index'])->name('inventario');
    Route::resource('recursos', RecursoController::class);

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
