<?php

use App\Http\Controllers\SubcategoriaController;


Route::post('/subcategorias', [SubcategoriaController::class, 'store']);
