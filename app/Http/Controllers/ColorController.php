<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Color;

class ColorController extends Controller
{
    /**
     * Guarda un nuevo color desde el frontend (AJAX).
     */
    public function storeAjax(Request $request)
    {
        $nombre = trim($request->input('nombre'));

        if (!$nombre) {
            return response()->json(['error' => 'Nombre vacío'], 400);
        }

        // Normalizar: capitalizar y eliminar espacios duplicados
        $nombreNormalizado = preg_replace('/\s+/', ' ', ucfirst(strtolower($nombre)));

        // Buscar o crear el color
        $color = Color::firstOrCreate(['nombre' => $nombreNormalizado]);

        return response()->json([
            'id' => $color->id,
            'nombre' => $color->nombre
        ]);
    }
}
