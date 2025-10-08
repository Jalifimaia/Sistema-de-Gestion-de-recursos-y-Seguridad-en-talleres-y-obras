<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrestamoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Carbon\Carbon;

class PrestamoController extends Controller
{
    /**
     * Muestra la lista de préstamos registrados.
     */
    public function index(): View
    {
        $prestamos = DB::table('prestamo')
            ->join('detalle_prestamo', 'prestamo.id', '=', 'detalle_prestamo.id_prestamo')
            ->join('serie_recurso', 'detalle_prestamo.id_serie', '=', 'serie_recurso.id')
            ->join('recurso', 'detalle_prestamo.id_recurso', '=', 'recurso.id')
            ->join('usuario', 'prestamo.id_usuario', '=', 'usuario.id')
            ->select(
                'prestamo.id',
                'usuario.name as operario',
                'recurso.nombre as recurso',
                'serie_recurso.nro_serie',
                'prestamo.fecha_prestamo',
                'prestamo.fecha_devolucion',
                'prestamo.estado'
            )
            ->orderByDesc('prestamo.id')
            ->get();

        return view('prestamo.index', compact('prestamos'));
    }

    /**
     * Muestra el formulario para registrar un nuevo préstamo.
     */
    public function create(): View
    {
        $series = DB::table('serie_recurso')
            ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
            ->join('estado', 'serie_recurso.id_estado', '=', 'estado.id')
            ->where('estado.nombre_estado', '=', 'Disponible')
            ->select('serie_recurso.id', 'serie_recurso.nro_serie', 'recurso.nombre as recurso')
            ->get();

        $estadosPrestamo = DB::table('estado_prestamo')->get();

        return view('prestamo.create', compact('series', 'estadosPrestamo'));
    }

    /**
     * Guarda un nuevo préstamo en la base de datos.
     */
    public function store(PrestamoRequest $request)
    {
        $validated = $request->validated();

        // Validar que el usuario esté autenticado
        $usuarioId = Auth::id();
        if (!$usuarioId) {
            return back()->withErrors(['error' => 'Debe iniciar sesión para registrar un préstamo.']);
        }

        DB::beginTransaction();
        try {
            // Insertar en la tabla prestamo
            $idPrestamo = DB::table('prestamo')->insertGetId([
                'id_usuario' => $usuarioId,
                'id_usuario_creacion' => $usuarioId,
                'id_usuario_modificacion' => $usuarioId,
                'fecha_prestamo' => $validated['fecha_prestamo'],
                'fecha_devolucion' => $validated['fecha_devolucion'],
                'estado' => $validated['estado'],
                'fecha_creacion' => Carbon::now(),
                'fecha_modificacion' => Carbon::now(),
            ]);

            // Obtener el recurso vinculado a la serie
            $serie = DB::table('serie_recurso')->where('id', $validated['id_serie'])->first();
            if (!$serie) {
                throw new \Exception('La serie seleccionada no existe.');
            }

            // Insertar en detalle_prestamo
            DB::table('detalle_prestamo')->insert([
                'id_prestamo' => $idPrestamo,
                'id_serie' => $validated['id_serie'],
                'id_recurso' => $serie->id_recurso,
                'id_estado_prestamo' => $validated['id_estado_prestamo'],
            ]);

            // Actualizar estado de la serie a "Prestado" (id_estado = 3)
            DB::table('serie_recurso')
                ->where('id', $validated['id_serie'])
                ->update(['id_estado' => 3]);

            DB::commit();
            return Redirect::route('prestamos.index')->with('success', 'Préstamo registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar préstamo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'No se pudo registrar el préstamo. ' . $e->getMessage()]);
        }
    }
}
