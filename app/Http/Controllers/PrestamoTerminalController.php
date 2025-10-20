<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrestamoTerminalRequest;
use App\Services\PrestamoService;
use App\Models\Usuario;
use App\Models\SerieRecurso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrestamoTerminalController extends Controller
{
    protected $prestamoService;

    public function __construct(PrestamoService $prestamoService)
    {
        $this->prestamoService = $prestamoService;
    }

    // ✅ Registrar préstamo desde terminal usando id_usuario
    public function store(PrestamoTerminalRequest $request, $id_usuario): JsonResponse
    {
        \Log::info('ID Usuario recibido: '.$id_usuario);
        \Log::info('Series recibidas: ', $request->input('series'));

        try {
            $usuario = Usuario::where('id', $id_usuario)
                ->where('id_rol', 3)
                ->firstOrFail();

            $prestamo = $this->prestamoService->crearPrestamo(
                $usuario->id,
                $request->input('series'),
                'terminal'
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

// ✅ Registrar préstamo por QR (escaneo directo del recurso)
public function registrarPorQR(Request $request)
{
    // Normalizamos el QR (trim + lowercase)
    $codigoQR = trim(strtolower($request->codigo_qr));

    \Log::info('RegistrarPorQR llamado', [
        'id_usuario' => $request->id_usuario,
        'codigo_qr'  => $codigoQR,
    ]);

    // Buscar la serie por código QR
    $serie = SerieRecurso::whereRaw('LOWER(codigo_qr) = ?', [$codigoQR])->first();
    if (!$serie) {
        \Log::warning('QR no encontrado', ['codigo_qr' => $codigoQR]);
        return response()->json(['success' => false, 'message' => 'QR no encontrado']);
    }

    // Validar que la serie esté disponible
    if ($serie->id_estado != 1) {
        \Log::warning('Serie no disponible', [
            'serie_id' => $serie->id,
            'estado'   => $serie->id_estado,
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Este recurso ya está asignado',
            'recurso' => $serie->recurso->nombre ?? '',
            'serie'   => $serie->nro_serie ?? ''
        ]);
    }

    // Buscar al trabajador por ID
    $usuario = Usuario::where('id', $request->id_usuario)
        ->where('id_rol', 3)
        ->firstOrFail();

    // Crear el préstamo
    $prestamo = $this->prestamoService->crearPrestamo(
        $usuario->id,
        [$serie->id],
        'terminal'
    );

    \Log::info('✅ Préstamo registrado por QR', [
        'prestamo_id' => $prestamo->id,
        'usuario_id'  => $usuario->id,
        'serie_id'    => $serie->id,
    ]);

    return response()->json([
        'success'  => true,
        'message'  => '✅ Préstamo registrado por QR',
        'prestamo' => $prestamo->id,
        'recurso'  => $serie->recurso->nombre ?? '',
        'serie'    => $serie->nro_serie ?? '',
    ]);
}

}
