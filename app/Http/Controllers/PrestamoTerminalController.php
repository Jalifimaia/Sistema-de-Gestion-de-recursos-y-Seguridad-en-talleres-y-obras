<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrestamoTerminalRequest;
use App\Services\PrestamoService;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;

class PrestamoTerminalController extends Controller
{
    protected $prestamoService;

    public function __construct(PrestamoService $prestamoService)
    {
        $this->prestamoService = $prestamoService;
    }

 public function store(PrestamoTerminalRequest $request, $dni): JsonResponse
{
\Log::info('DNI recibido: '.$dni);
\Log::info('Series recibidas: ', $request->input('series'));


    try {
        // Buscar trabajador por DNI (solo rol trabajador = 3)
        $usuario = Usuario::where('dni', $dni)
            ->where('id_rol', 3)
            ->firstOrFail();

        // Crear préstamo usando el servicio en modo terminal
        $prestamo = $this->prestamoService->crearPrestamo(
            $usuario->id,                  // trabajador
            $request->input('series'),     // series seleccionadas
            'terminal'                     // modo terminal
        );

        return response()->json([
            'success'  => true,
            'message'  => '✅ Préstamo registrado desde terminal',
            'prestamo' => $prestamo->id,
        ]);
    } catch (\Exception $e) {
        \Log::error('Error en préstamo desde terminal: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => '❌ No se pudo registrar el préstamo desde la terminal',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

public function registrarPorQR(Request $request)
{
    $serie = SerieRecurso::where('codigo_qr', $request->codigo_qr)->first();
    if (!$serie) {
        return response()->json(['success' => false, 'message' => 'QR no encontrado']);
    }

    $usuario = Usuario::where('dni', $request->dni)
        ->where('id_rol', 3)
        ->firstOrFail();

    $prestamo = $this->prestamoService->crearPrestamo(
        $usuario->id,
        [$serie->id],
        'terminal'
    );

    return response()->json([
        'success'  => true,
        'message'  => '✅ Préstamo registrado por QR',
        'prestamo' => $prestamo->id,
    ]);
}



}
