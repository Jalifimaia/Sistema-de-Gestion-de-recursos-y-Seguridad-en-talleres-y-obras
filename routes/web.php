
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
use App\Http\Controllers\KioskoController;
use App\Models\Subcategoria;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PrestamoTerminalController;


/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'));
Route::get('/herramientas', fn() => view('herramientas'));
Route::get('/dashboard', fn() => view('dashboard'));
//Route::get('/controlEPP', [App\Http\Controllers\ControlEPPController::class, 'index']);
Route::get('/controlEPP', [App\Http\Controllers\ControlEPPController::class, 'index'])->name('controlEPP');

Route::get('/reportes', fn() => view('supervisor.reportes'));
Route::post('/buscar-epp', [App\Http\Controllers\ControlEPPController::class, 'buscarEPP'])->name('buscar.epp');
Route::post('/matriz-checklist', [App\Http\Controllers\ControlEPPController::class, 'matrizChecklist'])->name('matrizChecklist');
Route::get('/trabajador/{id}/detalle-epp', [App\Http\Controllers\ControlEPPController::class, 'detalleEpp']);


Route::get('/reportes/prestamos', [ReporteController::class, 'reportePrestamos'])->name('reportes.prestamos');
Route::get('/reportes/prestamos/pdf', [ReporteController::class, 'exportarPrestamosPDF'])->name('reportes.prestamos.pdf');

Route::get('/reportes', function () {
    return view('reportes.index');
})->name('reportes.index');

/*
|--------------------------------------------------------------------------
| Rutas del Kiosko / Terminal
|--------------------------------------------------------------------------
*/


Route::prefix('terminal')->group(function () {
    Route::get('/', [KioskoController::class, 'index'])->name('terminal.index');

    // Identificación de trabajador
    Route::post('/identificar', [KioskoController::class, 'identificarTrabajador']);

    // Registro manual de préstamo (usa PrestamoService)
    Route::post('/registrar-manual', [KioskoController::class, 'registrarManual']);

    // Solicitud genérica (placeholder)
    Route::post('/solicitar', [KioskoController::class, 'solicitarRecurso']);

    // Flujo jerárquico real
    Route::get('/categorias', [KioskoController::class, 'getCategorias']);
    Route::get('/subcategorias/{categoriaId}', [KioskoController::class, 'getSubcategorias']);
    Route::get('/recursos/{subcategoriaId}', [KioskoController::class, 'getRecursos']);
    Route::get('/recursos-filtrados/{subcategoriaId}', [KioskoController::class, 'getRecursosConSeries']);
    Route::get('/recursos-disponibles/{subcategoriaId}', [KioskoController::class, 'getRecursosConDisponibles']);
    Route::get('/subcategorias-disponibles/{categoriaId}', [KioskoController::class, 'getSubcategoriasConDisponibles']);
    Route::get('/series/{recursoId}', [KioskoController::class, 'getSeries']);

    // Recursos asignados al usuario
    Route::get('/recursos-asignados/{usuarioId}', [KioskoController::class, 'recursosAsignados']);

    // Devolución
    Route::post('/devolver/{detalleId}', [KioskoController::class, 'devolverRecurso']);

    // 🚀 Rutas oficiales de préstamos (PrestamoTerminalController)
    Route::post('/prestamos/{id_usuario}', [PrestamoTerminalController::class, 'store'])
        ->name('terminal.prestamos.store');

    Route::post('/registrar-por-qr', [PrestamoTerminalController::class, 'registrarPorQR']);
});

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

//QR de inventario
Route::get('/series/{id}/qr', [SerieRecursoController::class, 'showQr'])->name('series.qr.show');
Route::get('/series/{id}/qr-snippet', [SerieRecursoController::class, 'qrSnippet']);


//QR
Route::get('/series-qr', [SerieRecursoController::class, 'qrIndex'])->name('series.qr');
Route::get('/series-qr/{id}/pdf', [SerieRecursoController::class, 'exportQrPdf'])->name('series.qr.pdf');
Route::get('/series-qr-lote', [SerieRecursoController::class, 'qrLote'])->name('series.qr.lote');
Route::get('/series-qr-lote/pdf', [SerieRecursoController::class, 'exportQrLotePdf'])
    ->name('series.qr.lote.pdf');

/*
|--------------------------------------------------------------------------
| Rutas AJAX para Préstamos
|--------------------------------------------------------------------------
*/

Route::get('/subcategorias/{categoriaId}', function ($categoriaId) {
    return Subcategoria::where('categoria_id', $categoriaId)->get(['id', 'nombre']);
});


Route::get('/prestamo/subcategorias/{categoriaId}', function ($categoriaId) {
    return Subcategoria::where('categoria_id', $categoriaId)->get();
});

Route::get('/prestamo/recursos/{subcategoriaId}', function ($subcategoriaId) {
    return \App\Models\Recurso::where('id_subcategoria', $subcategoriaId)->get();
});

Route::get('/prestamo/series/{recursoId}', function ($recursoId) {
    return \App\Models\SerieRecurso::where('id_recurso', $recursoId)
        ->where('id_estado', 1)
        ->get();
});

Route::post('/subcategorias', [SubcategoriaController::class, 'store']);
/*
|--------------------------------------------------------------------------
| Rutas AJAX para Incidentes
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
