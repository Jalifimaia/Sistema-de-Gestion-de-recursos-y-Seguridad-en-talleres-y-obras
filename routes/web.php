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
use App\Models\Subcategoria;
use App\Http\Controllers\InventarioController;
/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'));
Route::get('/herramientas', fn() => view('herramientas'));
Route::get('/dashboard', fn() => view('dashboard'));
Route::get('/controlEPP', fn() => view('controlEPP'));
Route::get('/reportes', fn() => view('supervisor.reportes'));

/*
|--------------------------------------------------------------------------
| Rutas del rol Operario
|--------------------------------------------------------------------------
*/

Route::get('/operario/solicitar', fn() => view('operario.solicitar'));
Route::get('/operario/mis-herramientas', [App\Http\Controllers\OperarioHerramientaController::class, 'index']);
Route::get('/operario/devolver', fn() => view('operario.devolver'));
Route::get('/operario/epp', fn() => view('operario.epp'));

/*
|--------------------------------------------------------------------------
| Rutas del rol Supervisor
|--------------------------------------------------------------------------
*/

Route::get('/supervisor/control-herramientas', fn() => view('supervisor.control_herramientas'));
Route::get('/supervisor/checklist-epp', fn() => view('supervisor.checklist_epp'));

//opciones de cambio de estado de la edicion de usuarios
Route::post('/usuarios/{id}/baja', [UserController::class, 'darDeBaja'])->name('usuarios.baja');
Route::post('/usuarios/{id}/alta', [UserController::class, 'darDeAlta'])->name('usuarios.alta');


Route::middleware(['auth'])->group(function () {

    // Usuarios
    Route::resource('usuarios', UserController::class);

    // Recursos
    Route::resource('recursos', RecursoController::class);

    // Estado incidente
    Route::resource('estado_incidente', EstadoIncidenteController::class);

    // Prestamos
    Route::resource('prestamos', PrestamoController::class);

    // Dashboard real
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Serie recurso
    Route::get('/serie_recurso/create/{id}', [SerieRecursoController::class, 'createConRecurso'])
        ->name('serie_recurso.createConRecurso');
    Route::post('/serie_recurso/store-multiple', [SerieRecursoController::class, 'storeMultiple'])
        ->name('serie_recurso.storeMultiple');
    Route::resource('serie_recurso', SerieRecursoController::class)->except(['create']);

    /*
    |--------------------------------------------------------------------------
    | Rutas de Incidentes
    |--------------------------------------------------------------------------
    */

    Route::get('/incidente', [IncidenteController::class, 'index'])->name('incidente.index');
    Route::get('/incidente/create', [IncidenteController::class, 'create'])->name('incidente.create');
    Route::post('/incidente', [IncidenteController::class, 'store'])->name('incidente.store');
    Route::get('/incidente/{id}/edit', [IncidenteController::class, 'edit'])->name('incidente.edit');
    Route::put('/incidente/{id}', [IncidenteController::class, 'update'])->name('incidente.update');
    Route::delete('/incidente/{id}', [IncidenteController::class, 'destroy'])->name('incidente.destroy');

    /*
    |--------------------------------------------------------------------------
    | AJAX para Incidentes (Selects dependientes y búsqueda por DNI)
    |--------------------------------------------------------------------------
    */
    Route::get('/buscar-usuario/{dni}', [IncidenteController::class, 'buscarUsuarioPorDni'])->name('usuario.buscar');
    Route::get('/subcategorias/{categoriaId}', [IncidenteController::class, 'getSubcategorias'])->name('subcategorias.getByCategoria');
    Route::get('/recursos/{subcategoriaId}', [IncidenteController::class, 'getRecursos'])->name('recursos.getBySubcategoria');
    Route::get('/series/{recursoId}', [IncidenteController::class, 'getSeries'])->name('series.get');
});

/*
|--------------------------------------------------------------------------
| API simples
|--------------------------------------------------------------------------
*/

Route::get('/api/subcategorias/{categoria}', fn($categoriaId) =>
    Subcategoria::where('categoria_id', $categoriaId)->get()
);

Route::post('/api/subcategorias', [SubcategoriaController::class, 'store']);

Route::get('/api/recursos/{subcategoriaId}', function ($subcategoriaId) {
    return DB::table('recurso')
        ->where('id_subcategoria', $subcategoriaId)
        ->select('id', 'nombre')
        ->get();
});



Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario');
Route::get('/inventario/exportar', [InventarioController::class, 'exportarCSV'])->name('inventario.exportar');


/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
