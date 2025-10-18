<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Recurso;
use App\Models\SerieRecurso;
use App\Models\Prestamo;
use App\Models\DetallePrestamo;
use App\Services\PrestamoService;
use Illuminate\Support\Facades\DB;

class KioskoController extends Controller
{
    public function index()
    {
        return view('terminal.index');
    }

    // ✅ Categorías
    public function getCategorias()
    {
        return Categoria::all();
    }

    // ✅ Identificación de trabajador
    public function identificarTrabajador(Request $request)
    {
        try {
            $dni = $request->input('dni');
            $usuario = Usuario::where('dni', $dni)->first();

            if ($usuario && $usuario->id_rol == 3) {
                return response()->json(['success' => true, 'usuario' => $usuario]);
            }

            return response()->json(['success' => false, 'message' => 'Usuario no válido']);
        } catch (\Exception $e) {
            \Log::error('Error en identificarTrabajador: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno'], 500);
        }
    }

    // ✅ Recursos asignados al trabajador
    public function recursosAsignados($usuarioId)
    {
        try {
            $detalles = DetallePrestamo::with('serieRecurso.recurso.subcategoria.categoria', 'prestamo')
                ->whereHas('prestamo', function ($q) use ($usuarioId) {
                    $q->where('id_usuario', $usuarioId)
                    ->where('estado', 2); // solo activos
                })
                ->where('id_estado_prestamo', 2) // asignado
                ->get()
                ->map(function($d) {
                    return [
                        'detalle_id'       => $d->id,
                        'categoria'        => $d->serieRecurso->recurso->subcategoria->categoria->nombre_categoria ?? '-',
                        'subcategoria'     => $d->serieRecurso->recurso->subcategoria->nombre ?? '-',
                        'recurso'          => $d->serieRecurso->recurso->nombre ?? '-',
                        'serie'            => $d->serieRecurso->nro_serie ?? '-',
                        'fecha_prestamo'   => $d->prestamo->fecha_prestamo ? \Carbon\Carbon::parse($d->prestamo->fecha_prestamo)->format('Y-m-d') : '-',
                        'fecha_devolucion' => $d->prestamo->fecha_devolucion ? \Carbon\Carbon::parse($d->prestamo->fecha_devolucion)->format('Y-m-d') : '-',
                    ];
                });

            return response()->json($detalles);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno: '.$e->getMessage()], 500);
        }
    }


    // ✅ Registrar manualmente (usa PrestamoService)
    public function registrarManual(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_usuario' => 'required|exists:usuario,id',
                'serie_id'   => 'required|exists:serie_recurso,id',
            ]);

            $prestamo = app(PrestamoService::class)->crearPrestamo(
                $validated['id_usuario'],
                [$validated['serie_id']],
                'terminal'
            );

            return response()->json(['success' => true, 'prestamo' => $prestamo->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ✅ Devolver recurso
    public function devolverRecurso($detalleId)
    {
        try {
            $detalle = DetallePrestamo::with('serieRecurso', 'prestamo')->findOrFail($detalleId);

            $detalle->update([
                'id_estado_prestamo'     => 3, // Devuelto
                'updated_at'             => now(),
                'id_usuario_modificacion'=> auth()->id() ?? $detalle->prestamo->id_usuario,
            ]);

            $detalle->serieRecurso->update(['id_estado' => 1]);

            DB::table('stock')->where('id_serie_recurso', $detalle->id_serie)->update([
                'id_estado_recurso' => 1,
                'id_usuario'        => null,
            ]);

            $todosDevueltos = DetallePrestamo::where('id_prestamo', $detalle->id_prestamo)
                ->where('id_estado_prestamo', '!=', 3)
                ->doesntExist();

            if ($todosDevueltos) {
                $detalle->prestamo->update(['estado' => 3]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error al devolver recurso desde terminal: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ✅ Subcategorías
    public function getSubcategorias($categoriaId)
    {
        return Subcategoria::where('categoria_id', $categoriaId)->get();
    }

    public function getSubcategoriasConDisponibles($categoriaId)
    {
        $subcategorias = Subcategoria::where('categoria_id', $categoriaId)
            ->withCount(['recursos as disponibles' => function ($q) {
                $q->whereHas('series', function ($s) {
                    $s->where('id_estado', 1);
                });
            }])
            ->get();

        return response()->json($subcategorias);
    }

    // ✅ Recursos
    public function getRecursos($subcategoriaId)
    {
        return Recurso::where('id_subcategoria', $subcategoriaId)->get();
    }

    public function getRecursosConSeries($subcategoriaId)
    {
        $recursos = Recurso::where('id_subcategoria', $subcategoriaId)
            ->whereHas('series', function ($query) {
                $query->where('id_estado', 1);
            })
            ->get();

        return response()->json($recursos);
    }

    public function getRecursosConDisponibles($subcategoriaId)
    {
        $recursos = Recurso::where('id_subcategoria', $subcategoriaId)
            ->withCount(['series as disponibles' => function ($q) {
                $q->where('id_estado', 1);
            }])
            ->get();

        return response()->json($recursos);
    }

    // ✅ Series
    public function getSeries($recursoId)
    {
        return SerieRecurso::where('id_recurso', $recursoId)
            ->where('id_estado', 1)
            ->get();
    }

    // Placeholder para solicitudes genéricas
    public function solicitarRecurso(Request $request)
    {
        return response()->json(['message' => 'Solicitud recibida']);
    }

public function registrarPorQR(Request $request)
{
    $codigoQR = $request->input('codigo_qr');
    $dni      = $request->input('dni');

    if (!$codigoQR || !$dni) {
        return response()->json(['success' => false, 'message' => 'Datos incompletos']);
    }

    // Buscar al trabajador por DNI
    $usuario = \App\Models\Usuario::where('dni', $dni)
        ->where('id_rol', 3)
        ->first();

    if (!$usuario) {
        return response()->json(['success' => false, 'message' => 'Usuario no encontrado']);
    }

    // Buscar la serie por código QR
    $serie = \App\Models\SerieRecurso::where('codigo_qr', $codigoQR)->first();
    if (!$serie) {
        return response()->json(['success' => false, 'message' => 'QR no válido']);
    }

    // Validar que esté disponible
    if ($serie->id_estado != 1) {
        return response()->json([
            'success' => false,
            'message' => 'Este recurso ya está asignado',
            'recurso' => $serie->recurso->nombre ?? '',
            'serie'   => $serie->nro_serie ?? ''
        ]);
    }

    // ✅ Usar PrestamoService para crear el préstamo
    $prestamo = app(\App\Services\PrestamoService::class)->crearPrestamo(
        $usuario->id,
        [$serie->id],
        'terminal'
    );

    return response()->json([
        'success'     => true,
        'message'     => '✅ Recurso registrado por QR',
        'prestamo_id' => $prestamo->id,
        'recurso'     => $serie->recurso->nombre ?? '',
        'serie'       => $serie->nro_serie ?? ''
    ]);
}


}
