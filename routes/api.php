<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubcategoriaController;
use App\Http\Controllers\RecomendacionController;
use App\Models\Subcategoria;
use App\Models\Recurso;
use App\Models\SerieRecurso;

/*
|--------------------------------------------------------------------------
| Rutas API para Inventario
|--------------------------------------------------------------------------
*/

Route::get('/subcategorias/{categoriaId}', function ($categoriaId) {
    return Subcategoria::where('categoria_id', $categoriaId)->get();
});

Route::post('/subcategorias', [SubcategoriaController::class, 'store']);

Route::get('/recomendaciones', [RecomendacionController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Rutas API para Préstamos
|--------------------------------------------------------------------------
*/

Route::get('/prestamo/subcategorias/{categoriaId}', function ($categoriaId) {
    return Subcategoria::where('categoria_id', $categoriaId)->get();
});

Route::get('/prestamo/recursos/{subcategoriaId}', function ($subcategoriaId) {
    return Recurso::where('id_subcategoria', $subcategoriaId)->get();
});

Route::get('/prestamo/series/{recursoId}', function ($recursoId) {
    return SerieRecurso::where('id_recurso', $recursoId)
        ->where('id_estado', 1) // solo disponibles
        ->get();
});
