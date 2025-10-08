<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Services\RecomendacionService;

class RecomendacionController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $service = new RecomendacionService();
            $recomendaciones = $service->generar();

            return response()->json(['ok' => true, 'recomendaciones' => $recomendaciones]);
        } catch (\Exception $e) {
            // Log error and return a friendly JSON payload so frontend can show a message
            logger()->error('Error generando recomendaciones: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'ok' => false,
                'message' => 'No se pudieron generar las recomendaciones: ' . $e->getMessage(),
            ]);
        }
    }
}
