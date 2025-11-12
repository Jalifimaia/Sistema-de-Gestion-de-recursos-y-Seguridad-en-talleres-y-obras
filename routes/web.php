
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
use App\Http\Controllers\ControlEPPController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\UsuarioController;
use App\Models\Recurso;
use App\Models\SerieRecurso;


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
//Route::get('/usuarios/{id}', [UserController::class, 'show'])
     //->where('id', '[0-9]+')
     //->name('usuarios.show');
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

/*actualizacion del token para la terminal*/
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});


//URls de la terminal
Route::prefix('terminal')->group(function () {
    Route::get('/', [KioskoController::class, 'index'])->name('terminal.index');

    Route::post('/identificar', [KioskoController::class, 'identificarTrabajador']);
    Route::post('/identificar-qr', [KioskoController::class, 'identificarPorQR']);

    Route::post('/registrar-manual', [KioskoController::class, 'registrarManual']);
    Route::post('/solicitar', [KioskoController::class, 'solicitarRecurso']);

    Route::get('/categorias', [KioskoController::class, 'getCategorias']);
    Route::get('/subcategorias/{categoriaId}', [KioskoController::class, 'getSubcategorias']);
    Route::get('/recursos/{subcategoriaId}', [KioskoController::class, 'getRecursos']);
    Route::get('/recursos-filtrados/{subcategoriaId}', [KioskoController::class, 'getRecursosConSeries']);
    Route::get('/recursos-disponibles/{subcategoriaId}', [KioskoController::class, 'getRecursosConDisponibles']);
    Route::get('/subcategorias-disponibles/{categoriaId}', [KioskoController::class, 'getSubcategoriasConDisponibles']);
    Route::get('/series/{recursoId}', [KioskoController::class, 'getSeries']);
    Route::get('/recursos-asignados/{usuarioId}', [KioskoController::class, 'recursosAsignados']);
    

    // ✅ Devolución
    Route::post('/validar-qr-devolucion', [PrestamoTerminalController::class, 'validarQRDevolucion']);
    Route::post('/devolver/{detalleId}', [PrestamoTerminalController::class, 'devolverRecurso']);
    Route::post('/devolver-recurso', [PrestamoTerminalController::class, 'devolverPorQR']);

    // 🚀 Préstamos
    Route::post('/prestamos/{id_usuario}', [PrestamoTerminalController::class, 'store'])->name('terminal.prestamos.store');
    Route::post('/registrar-por-qr', [PrestamoTerminalController::class, 'registrarPorQR']);
});


/*
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

Route::get('/checklist-epp', [ControlEPPController::class, 'create'])->name('checklist.epp.create');
Route::post('/checklist-epp', [ControlEPPController::class, 'store'])->name('checklist.epp.store');

Route::get('/asignar-epp', [ControlEPPController::class, 'createAsignacionEPP'])->name('epp.asignar.create');
Route::post('/asignar-epp', [ControlEPPController::class, 'storeAsignacionEPP'])->name('epp.asignar.store');

// Trabajadores con recursos faltantes
Route::get('/control-epp/faltantes', [ControlEPPController::class, 'faltantes'])->name('controlEPP.faltantes');

// Trabajadores sin checklist diario
Route::get('/control-epp/sin-checklist', [ControlEPPController::class, 'sinChecklist'])->name('controlEPP.sinChecklist');

Route::get('/checklist-epp/tabla', [ControlEPPController::class, 'index'])->name('checklist.epp.tabla');

Route::get('/checklist-epp/tabla', [ControlEPPController::class, 'verSoloChecklist'])->name('checklist.epp.tabla');

Route::get('/checklist-epp', [ControlEPPController::class, 'create'])->name('checklist.epp');
Route::post('/checklist-epp', [ControlEPPController::class, 'store'])->name('checklist.epp.store');

//Color
Route::post('/colores/crear', [ColorController::class, 'storeAjax'])->name('colores.storeAjax');


Route::post('/usuarios/{id}/activar', [ControlEPPController::class, 'activarTrabajador'])->name('usuarios.activar');

Route::get('/epp/faltantes', [ControlEPPController::class, 'faltantes'])->name('epp.faltantes');
Route::get('/epp/sin-checklist', [ControlEPPController::class, 'sinChecklist'])->name('epp.sin_checklist');

Route::get('/epp/asignados/{id}', function ($id) {
    try {
        $usuario = \App\Models\Usuario::with('usuarioRecursos.serieRecurso.recurso.subcategoria')->findOrFail($id);

        $epps = $usuario->usuarioRecursos->map(function ($ur) {
            return [
                'tipo' => $ur->tipo_epp ?? ($ur->recurso->subcategoria->nombre ?? 'Sin tipo'),
                'serie' => $ur->serieRecurso->nro_serie ?? 'Sin serie',
                'fecha' => optional($ur->fecha_asignacion)->format('d/m/Y'),
            ];
        });

        return response()->json($epps);
    } catch (\Throwable $e) {
        \Log::error("Error en /epp/asignados/{$id}: " . $e->getMessage());
        return response()->json(['error' => 'Error interno del servidor'], 500);
    }
});



Route::get('/trabajadores/por-estado/{estado}', [UserController::class, 'porEstado']);
Route::get('/epp/disponibles/{tipo}', [ControlEPPController::class, 'buscarSeriesEPP']);

/*
|--------------------------------------------------------------------------
| Rutas de Inventario
|--------------------------------------------------------------------------
*/
Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
Route::delete('/recursos/{id}', [RecursoController::class, 'destroy'])->name('recursos.destroy');
//Route::get('/inventario', [RecursoController::class, 'index'])->name('inventario');
Route::get('/inventario/subcategorias/{categoriaId}', [SubcategoriaController::class, 'byCategoria']);
Route::get('/inventario/ajax/subcategorias/{categoriaId}', [SubcategoriaController::class, 'byCategoria']);
Route::resource('recursos', RecursoController::class);
Route::patch('/recursos/{recurso}/baja', [RecursoController::class, 'darDeBaja'])->name('recursos.darDeBaja');

//QR de inventario
Route::get('/series/{id}/qr', [SerieRecursoController::class, 'showQr'])->name('series.qr.show');
Route::get('/series/{id}/qr-snippet', [SerieRecursoController::class, 'qrSnippet']);


//QR
Route::get('/series-qr', [SerieRecursoController::class, 'qrIndex'])->name('series.qr.index');
Route::get('/series-qr/{id}/pdf', [SerieRecursoController::class, 'exportQrPdf'])->name('series.qr.pdf');
Route::get('/series-qr-lote', [SerieRecursoController::class, 'qrLote'])->name('series.qr.lote');
Route::get('/series-qr-lote/pdf', [SerieRecursoController::class, 'exportQrLotePdf'])
    ->name('series.qr.lote.pdf');


// SERIES
Route::post('/serie-recurso/store-multiple', [SerieRecursoController::class, 'storeMultiple'])->name('serie_recurso.storeMultiple');
Route::get('/serie-recurso/create/{id}', [SerieRecursoController::class, 'createConRecurso'])->name('serie_recurso.createConRecurso');

/*
|--------------------------------------------------------------------------
| Rutas AJAX para Préstamos
|--------------------------------------------------------------------------
*/

//Para Préstamos
// 🔹 Subcategorías por categoría (para préstamos)
Route::get('/prestamo/subcategorias/{categoriaId}', function ($categoriaId) {
    return Subcategoria::where('categoria_id', $categoriaId)->get(['id', 'nombre']);
});

// 🔹 Recursos por subcategoría (para préstamos)
Route::get('/prestamo/recursos/{subcategoriaId}', function ($subcategoriaId) {
    return Recurso::where('id_subcategoria', $subcategoriaId)->get(['id', 'nombre']);
});

// 🔹 Series disponibles por recurso (para préstamos)
Route::get('/prestamo/series/{recursoId}', function ($recursoId) {
    return SerieRecurso::where('id_recurso', $recursoId)
        ->where('id_estado', 1)
        ->get(['id', 'nro_serie']);
});

Route::post('/subcategorias', [SubcategoriaController::class, 'store']);
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

    
    // Rutas para cargar las pestañas (AJAX / partials)
    Route::get('usuarios/{id}/checklists', [UsuarioController::class, 'checklists'])->name('usuarios.checklists');
    Route::get('usuarios/{id}/incidentes', [UsuarioController::class, 'incidentes'])->name('usuarios.incidentes');
    Route::get('usuarios/{id}/prestamos', [UsuarioController::class, 'prestamos'])->name('usuarios.prestamos');

    Route::resource('usuarios', UsuarioController::class); // <--- ruta de usuarios de UsuarioController
    //Route::resource('usuarios', UserController::class);    

    
    Route::resource('estado_incidente', EstadoIncidenteController::class);
    Route::resource('prestamos', PrestamoController::class);
    Route::patch('/prestamos/detalle/{id}/baja', [PrestamoController::class, 'darDeBaja'])->name('prestamos.bajaDetalle');

    Route::get('/serie_recurso/create/{id}', [SerieRecursoController::class, 'createConRecurso'])->name('serie_recurso.createConRecurso');
    Route::post('/serie_recurso/store-multiple', [SerieRecursoController::class, 'storeMultiple'])->name('serie_recurso.storeMultiple');
    Route::resource('serie_recurso', SerieRecursoController::class)->except(['create']);
});



Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');

Route::get('/inventario/exportar', [InventarioController::class, 'exportarCSV'])->name('inventario.exportar');


/*
|--------------------------------------------------------------------------
| Cambios de estado de usuario
|--------------------------------------------------------------------------
*/

Route::post('/usuarios/{id}/baja', [UserController::class, 'darDeBaja'])->name('usuarios.baja');
Route::post('usuarios/{id}/activar-con-epp', [ControlEPPController::class, 'activarConEPP'])->name('usuarios.activarConEPP');
Route::post('usuarios/{id}/standby', [UserController::class, 'standby'])->name('usuarios.standby');

Route::post('/asignarEPP', [ControlEPPController::class, 'store'])->name('asignarEPP.store');
Route::get('/series-epp', [ControlEPPController::class, 'buscarSeriesEPP']);


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
