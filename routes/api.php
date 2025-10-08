<?php

use App\Http\Controllers\SubcategoriaController;


Route::post('/subcategorias', [SubcategoriaController::class, 'store']);
Route::get('/recomendaciones', [\App\Http\Controllers\RecomendacionController::class, 'index']);
