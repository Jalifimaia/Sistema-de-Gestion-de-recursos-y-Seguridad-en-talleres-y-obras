<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecursoController;
use App\Http\Controllers\SubcategoriaController;
use App\Http\Controllers\EstadoIncidenteController;
use App\Http\Controllers\SerieRecursoController;
use App\Http\Controllers\IncidenteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrestamoController;

// Rutas públicas
Route::get('/', fn() => view('welcome'));
Route::get('/herramientas', fn() => view('herramientas'));
Route::get('/dashboard', fn() => view('dashboard'));
Route::get('/controlEPP', fn() => view('controlEPP'));
Route::get('/reportes', fn() => view('supervisor.reportes'));

// Vistas estáticas para Operario
Route::get('/operario/solicitar', fn() => view('operario.solicitar'));
Route::get('/operario/mis-herramientas', fn() => view('operario.mis_herramientas'));
Route::get('/operario/devolver', fn() => view('operario.devolver'));
Route::get('/operario/epp', fn() => view('operario.epp'));

// Vistas estáticas para Supervisor
Route::get('/supervisor/control-herramientas', fn() => view('supervisor.control_herramientas'));
Route::get('/supervisor/checklist-epp', fn() => view('supervisor.checklist_epp'));

// Cambios de estado de usuario
Route::post('/usuarios/{id}/baja', [UserController::class, 'darDeBaja'])->name('usuarios.baja');
Route::post('/usuarios/{id}/alta', [UserController::class, 'darDeAlta'])->name('usuarios.alta');

// Rutas AJAX para selects dinámicos (usadas por prestamo.js)
Route::get('/subcategorias/{categoriaId}', function ($categoriaId) {
    return DB::table('subcategoria')
        ->where('categoria_id', $categoriaId)
        ->select('id', 'nombre')
        ->get();
});

Route::get('/recursos/{subcategoriaId}', function ($subcategoriaId) {
    return DB::table('recurso')
        ->where('id_subcategoria', $subcategoriaId)
        ->select('id', 'nombre')
        ->get();
});

Route::get('/series/{recursoId}', function ($recursoId) {
    return DB::table('serie_recurso')
        ->where('id_recurso', $recursoId)
        ->where('id_estado', 1) // Disponible
        ->select('id', 'nro_serie')
        ->get();
});

// Rutas de incidente
Route::get('/incidente', [IncidenteController::class, 'index'])->name('incidente.index');
Route::get('/incidente/create', [IncidenteController::class, 'create'])->name('incidente.create');
Route::post('/incidente', [IncidenteController::class, 'store'])->name('incidente.store');

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/supervisor/registrar-incidente', [IncidenteController::class, 'create'])->name('incidente.create');
    Route::post('/supervisor/registrar-incidente', [IncidenteController::class, 'store'])->name('incidente.store');

    Route::resource('usuarios', UserController::class);
    Route::get('/inventario', [RecursoController::class, 'index'])->name('inventario');
    Route::resource('recursos', RecursoController::class);
    Route::resource('incidente', IncidenteController::class);
    Route::resource('estado_incidente', EstadoIncidenteController::class);
    Route::resource('prestamos', PrestamoController::class);
    Route::patch('/prestamos/detalle/{id}/baja', [PrestamoController::class, 'darDeBaja'])->name('prestamos.bajaDetalle');


    // Serie recurso personalizado
    Route::get('/serie_recurso/create/{id}', [SerieRecursoController::class, 'createConRecurso'])->name('serie_recurso.createConRecurso');
    Route::post('/serie_recurso/store-multiple', [SerieRecursoController::class, 'storeMultiple'])->name('serie_recurso.storeMultiple');
    Route::resource('serie_recurso', SerieRecursoController::class)->except(['create']);
});

// Autenticación
require __DIR__.'/auth.php';
