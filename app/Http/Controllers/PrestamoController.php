<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrestamoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Prestamo;
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
            ->join('estado_prestamo', 'detalle_prestamo.id_estado_prestamo', '=', 'estado_prestamo.id')
            ->select(
                'prestamo.id',
                'usuario.name as operario',
                'recurso.nombre as recurso',
                'serie_recurso.nro_serie',
                'prestamo.fecha_prestamo',
                'prestamo.fecha_devolucion',
                'estado_prestamo.nombre as estado'
            )
            ->orderByDesc('prestamo.id')
            ->get();

        return view('prestamo.index', compact('prestamos'));
    }

    public function update(PrestamoRequest $request, $id)
    {
        $request->validate([
            'fecha_prestamo' => 'required|date',
            'fecha_devolucion' => 'nullable|date|after_or_equal:fecha_prestamo',
            'series' => 'nullable|array',
            'series.*' => 'integer|exists:serie_recurso,id',
        ]);

        DB::beginTransaction();
        try {
            DB::table('prestamo')->where('id', $id)->update([
                'fecha_prestamo' => $request->fecha_prestamo,
                'fecha_devolucion' => $request->fecha_devolucion,
                'estado' => $request->estado,
                'fecha_modificacion' => now(),
                'id_usuario_modificacion' => Auth::id(),
            ]);

            if ($request->filled('series')) {
                foreach ($request->series as $idSerie) {
                    $serie = DB::table('serie_recurso')->where('id', $idSerie)->first();

                    $yaPrestada = DB::table('detalle_prestamo')
                        ->where('id_serie', $idSerie)
                        ->where('id_estado_prestamo', 2)
                        ->exists();

                    if (!$serie || $serie->id_estado != 1 || $yaPrestada) {
                        throw new \Exception("La serie $idSerie no está disponible o ya está prestada.");
                    }

                    DB::table('detalle_prestamo')->insert([
                        'id_prestamo' => $id,
                        'id_serie' => $idSerie,
                        'id_recurso' => $serie->id_recurso,
                        'id_estado_prestamo' => 2,
                    ]);

                    DB::table('serie_recurso')->where('id', $idSerie)->update(['id_estado' => 3]);
                }
            }

            DB::commit();
            return redirect()->route('prestamos.index')->with('success', 'Préstamo actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar préstamo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'No se pudo actualizar el préstamo. ' . $e->getMessage()]);
        }
    }

    public function edit($id): View
    {
        $prestamo = Prestamo::with([
            'detallePrestamos.serieRecurso.recurso.subcategoria.categoria',
            'detallePrestamos.estadoPrestamo'
        ])->findOrFail($id);

        $categorias = DB::table('categoria')->get();
        $detalles = [];

        foreach ($prestamo->detallePrestamos as $d) {
            $detalles[] = [
                'categoria_id' => optional($d->serieRecurso->recurso->subcategoria->categoria)->id,
                'subcategoria_id' => optional($d->serieRecurso->recurso->subcategoria)->id,
                'recurso_id' => optional($d->serieRecurso->recurso)->id,
                'serie_id' => optional($d->serieRecurso)->id,
                'serie_nro' => optional($d->serieRecurso)->nro_serie,
                'recurso_nombre' => optional($d->serieRecurso->recurso)->nombre,
            ];
        }

        return view('prestamo.edit', compact('prestamo', 'categorias', 'detalles'));
    }

    public function create(): View
    {
        $categorias = DB::table('categoria')->get();
        return view('prestamo.create', compact('categorias'));
    }

    public function store(PrestamoRequest $request)
    {
        $validated = $request->validated();
        $usuarioId = Auth::id();

        if (!$usuarioId) {
            return back()->withErrors(['error' => 'Debe iniciar sesión para registrar un préstamo.']);
        }

        DB::beginTransaction();
        try {
            $idPrestamo = DB::table('prestamo')->insertGetId([
                'id_usuario' => $usuarioId,
                'id_usuario_creacion' => $usuarioId,
                'id_usuario_modificacion' => $usuarioId,
                'fecha_prestamo' => $validated['fecha_prestamo'],
                'fecha_devolucion' => $validated['fecha_devolucion'],
                'estado' => 2,
                'fecha_creacion' => Carbon::now(),
                'fecha_modificacion' => Carbon::now(),
            ]);

            foreach ($validated['series'] as $idSerie) {
                $serie = DB::table('serie_recurso')->where('id', $idSerie)->first();

                if (!$serie) {
                    throw new \Exception("La serie con ID $idSerie no existe.");
                }

                if ($serie->id_estado != 1) {
                    throw new \Exception("La serie $serie->nro_serie no está disponible.");
                }

                DB::table('detalle_prestamo')->insert([
                    'id_prestamo' => $idPrestamo,
                    'id_serie' => $idSerie,
                    'id_recurso' => $serie->id_recurso,
                    'id_estado_prestamo' => 2,
                ]);

                DB::table('serie_recurso')
                    ->where('id', $idSerie)
                    ->update(['id_estado' => 3]);
            }

            DB::commit();
            return Redirect::route('prestamos.index')->with('success', 'Préstamo registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar préstamo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'No se pudo registrar el préstamo. ' . $e->getMessage()]);
        }
    }
}
