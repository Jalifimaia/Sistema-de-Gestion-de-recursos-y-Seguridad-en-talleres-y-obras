<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrestamoTerminalRequest;
use App\Services\PrestamoService;
use App\Models\Usuario;
use App\Models\SerieRecurso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\DetallePrestamo;
use Illuminate\Support\Facades\Log;

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

    public function devolverPorQR(Request $request)
    {
        try {
            $detalle = DetallePrestamo::findOrFail($request->input('id_detalle'));

            if ($detalle->id_estado_prestamo != 2) {
                return response()->json([
                    'success' => false,
                    'estado' => 'ya_devuelto',
                     'message' => ''
                   // 'message' => 'El recurso ya fue devuelto o no está asignado',
                ]);
            }

            // Actualizar estado del detalle
            $detalle->update([
                'id_estado_prestamo' => 3,
                'updated_at' => now(),
            ]);

            // Liberar la serie
            $detalle->serieRecurso->update(['id_estado' => 1]);

            // Liberar el stock
            \DB::table('stock')->where('id_serie_recurso', $detalle->id_serie)->update([
                'id_estado_recurso' => 1,
                'id_usuario' => null,
            ]);

            // Actualizar préstamo si todos los detalles fueron devueltos
            $prestamo = $detalle->prestamo;
            $prestamo->fecha_devolucion = now();

            $todosDevueltos = $prestamo->detalles()->where('id_estado_prestamo', '!=', 3)->doesntExist();
            if ($todosDevueltos) {
                $prestamo->estado = 3;
            }

            $prestamo->save();

            return response()->json([
                'success' => true,
                'message' => '✅ Recurso devuelto correctamente',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error en devolverPorQR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Error al devolver el recurso',
            ], 500);
        }
    }



    public function validarQRDevolucion(Request $request)
    {
        $codigoQR = $request->input('codigo_qr');
        $idUsuario = $request->input('id_usuario');
        $serieEsperada = $request->input('serie_esperada');

        if (! $codigoQR || ! $idUsuario || ! $serieEsperada) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan datos para validar el QR'
            ]);
        }

        $serie = SerieRecurso::where('codigo_qr', $codigoQR)->first();

        if (! $serie) {
            return response()->json([
                'success' => false,
                'message' => 'QR no corresponde a ninguna serie registrada'
            ]);
        }

        $detalle = DetallePrestamo::where('id_serie', $serie->id)
            ->where('id_estado_prestamo', 2)
            ->whereHas('prestamo', function ($q) use ($idUsuario) {
                $q->where('id_usuario', $idUsuario)
                ->where('estado', 2);
            })
            ->first();

        if (! $detalle) {
            return response()->json([
                'success' => true,
                'coincide' => false,
                //'message' => 'El recurso ya fue devuelto o no está asignado a este usuario',
                'estado' => 'ya_devuelto'
            ]);
        }


        if ($detalle->serieRecurso->nro_serie !== $serieEsperada) {
            return response()->json([
                'success' => false,
                'message' => 'El QR escaneado no coincide con el recurso que se está devolviendo'
            ]);
        }

        return response()->json([
            'success' => true,
            'coincide' => true,
            'id_detalle' => $detalle->id
        ]);
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
