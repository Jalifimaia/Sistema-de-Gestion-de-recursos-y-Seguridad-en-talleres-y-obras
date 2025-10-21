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
use App\Http\Controllers\OperarioHerramientaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;


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
//Route::get('/reportes', fn() => view('supervisor.reportes'));
//Route::get('/reportes/prestamos', [ReporteController::class, 'reportePrestamos'])->name('reportes.prestamos');

Route::get('/reportes/prestamos', [ReporteController::class, 'reportePrestamos'])->name('reportes.prestamos');
Route::get('/reportes/prestamos', [PrestamoController::class, 'ultimosPrestamos'])->name('reportes.prestamos');

Route::get('/reportes/prestamos', [ReporteController::class, 'reportePrestamos'])->name('reportes.prestamos');
Route::get('/reportes/prestamos/pdf', [ReporteController::class, 'exportarPrestamosPDF'])->name('reportes.prestamos.pdf');

Route::get('/reportes', function () {
    return view('reportes.index');
})->name('reportes.index');

/*
|--------------------------------------------------------------------------
| Rutas de Reportes de Recursos
|--------------------------------------------------------------------------
*/

Route::get('/reportes/recursos-mas-prestados', [RecursoController::class, 'recursosMasPrestados'])->name('reportes.masPrestados');
Route::get('/reportes/recursos-en-reparacion', [RecursoController::class, 'recursosEnReparacion'])->name('reportes.enReparacion');
Route::get('/reportes/herramientas-por-trabajador', [RecursoController::class, 'herramientasPorTrabajador'])->name('reportes.herramientasPorTrabajador');
Route::get('/reportes/incidentes-por-tipo', [RecursoController::class, 'incidentesPorTipo'])->name('reportes.incidentesPorTipo');
Route::get('/reportes/recursos-mas-prestados/pdf', [RecursoController::class, 'recursosMasPrestadosPDF'])->name('reportes.masPrestados.pdf');
Route::get('/reportes/recursos-en-reparacion/pdf', [RecursoController::class, 'recursosEnReparacionPDF'])->name('reportes.enReparacion.pdf');
Route::get('/reportes/herramientas-por-trabajador/pdf', [RecursoController::class, 'herramientasPorTrabajadorPDF'])->name('reportes.herramientasPorTrabajador.pdf');
Route::get('/reportes/incidentes-por-tipo/pdf', [RecursoController::class, 'incidentesPorTipoPDF'])->name('reportes.incidentesPorTipo.pdf');



/*
|--------------------------------------------------------------------------
| Rutas del rol Operario
|--------------------------------------------------------------------------
*/

Route::get('/operario/solicitar', fn() => view('operario.solicitar'));
Route::get('/operario/mis-herramientas', [OperarioHerramientaController::class, 'index']);
Route::get('/operario/devolver', fn() => view('operario.devolver'));
Route::get('/operario/epp', fn() => view('operario.epp'));

/*
|--------------------------------------------------------------------------
| Rutas del rol Supervisor
|--------------------------------------------------------------------------
*/

Route::get('/supervisor/control-herramientas', fn() => view('supervisor.control_herramientas'));
Route::get('/supervisor/checklist-epp', fn() => view('supervisor.checklist_epp'));

/*
|--------------------------------------------------------------------------
| Rutas de Inventario
|--------------------------------------------------------------------------
*/
Route::get('/inventario', [RecursoController::class, 'index'])->name('inventario');
Route::resource('recursos', RecursoController::class);

/*
|--------------------------------------------------------------------------
| Rutas AJAX para Préstamos
|--------------------------------------------------------------------------
*/

Route::get('/api/prestamo/subcategorias/{categoriaId}', function ($categoriaId) {
    return \App\Models\Subcategoria::where('categoria_id', $categoriaId)->get();
});

Route::get('/api/prestamo/recursos/{subcategoriaId}', function ($subcategoriaId) {
    return \App\Models\Recurso::where('id_subcategoria', $subcategoriaId)->get();
});

Route::get('/api/prestamo/series/{recursoId}', function ($recursoId) {
    return \App\Models\SerieRecurso::where('id_recurso', $recursoId)
        ->where('id_estado', 1)
        ->get();
});

Route::get('api/subcategorias/{categoriaId}', [RecursoController::class, 'getSubcategorias']);


/*
|--------------------------------------------------------------------------
| Rutas AJAX para Incidentes
|--------------------------------------------------------------------------
*/

Route::get('/inventario', function () { return view('inventario');})->name('inventario');

Route::get('/ajax/incidente/subcategorias/{categoriaId}', [IncidenteController::class, 'getSubcategorias']);
Route::get('/ajax/incidente/recursos/{subcategoriaId}', [IncidenteController::class, 'getRecursos']);
Route::get('/ajax/incidente/series/{recursoId}', [IncidenteController::class, 'getSeries']);
Route::get('/ajax/incidente/buscar-usuario/{dni}', [IncidenteController::class, 'buscarUsuarioPorDni']);

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
| Rutas protegidas por autenticación
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('usuarios', UserController::class);
    Route::resource('estado_incidente', EstadoIncidenteController::class);
    Route::resource('prestamos', PrestamoController::class);
    Route::patch('/prestamos/detalle/{id}/baja', [PrestamoController::class, 'darDeBaja'])->name('prestamos.bajaDetalle');

    Route::get('/serie_recurso/create/{id}', [SerieRecursoController::class, 'createConRecurso'])->name('serie_recurso.createConRecurso');
    Route::post('/serie_recurso/store-multiple', [SerieRecursoController::class, 'storeMultiple'])->name('serie_recurso.storeMultiple');
    Route::resource('serie_recurso', SerieRecursoController::class)->except(['create']);
});



Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario');
Route::get('/inventario/exportar', [InventarioController::class, 'exportarCSV'])->name('inventario.exportar');


/*
|--------------------------------------------------------------------------
| Cambios de estado de usuario
|--------------------------------------------------------------------------
*/

Route::post('/usuarios/{id}/baja', [UserController::class, 'darDeBaja'])->name('usuarios.baja');
Route::post('/usuarios/{id}/alta', [UserController::class, 'darDeAlta'])->name('usuarios.alta');


Route::get('serie_recurso/create-con-recurso/{id}', [SerieRecursoController::class, 'createConRecurso'])
    ->name('serie_recurso.createConRecurso');
Route::post('serie_recurso/store-multiple', [SerieRecursoController::class, 'storeMultiple'])
    ->name('serie_recurso.storeMultiple');


/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
