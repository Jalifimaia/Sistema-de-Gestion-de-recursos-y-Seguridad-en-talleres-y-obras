<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrestamoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Prestamo;
use App\Models\DetallePrestamo;
use App\Models\SerieRecurso;
use Carbon\Carbon;

class PrestamoController extends Controller
{
 public function index(): View
{
    $prestamos = DB::table('prestamo')
        ->join('detalle_prestamo', 'prestamo.id', '=', 'detalle_prestamo.id_prestamo')
        ->join('serie_recurso', 'detalle_prestamo.id_serie', '=', 'serie_recurso.id')
        ->join('recurso', 'detalle_prestamo.id_recurso', '=', 'recurso.id')
        ->join('usuario as trabajador', 'prestamo.id_usuario', '=', 'trabajador.id')
        ->join('usuario as creador', 'prestamo.id_usuario_creacion', '=', 'creador.id')
        ->join('estado_prestamo', 'detalle_prestamo.id_estado_prestamo', '=', 'estado_prestamo.id')
        ->select(
            'prestamo.id',
            'trabajador.name as asignado',
            'creador.name as creado_por',
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


public function darDeBaja($id)
{
    DB::beginTransaction();
    try {
        $detalle = DetallePrestamo::findOrFail($id);

        Log::info("Dando de baja recurso ID: {$detalle->id} en préstamo {$detalle->id_prestamo}");

        $detalle->update([
            'id_estado_prestamo' => 5,
            'updated_at' => now(),
            'id_usuario_modificacion' => Auth::id(),
        ]);

        SerieRecurso::where('id', $detalle->id_serie)->update(['id_estado' => 4]);

        DB::commit();
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al dar de baja recurso: ' . $e->getMessage());
        return response()->json(['error' => 'No se pudo dar de baja el recurso.'], 500);
    }
}


    public function create(): View
    {
        $categorias = DB::table('categoria')->get();

        $trabajadores = DB::table('usuario')
        ->where('id_rol', 3) // 3 = trabajador
        ->where('id_estado', 1)
        ->get();


        return view('prestamo.create', compact('categorias', 'trabajadores'));
    }

    public function store(PrestamoRequest $request)
    {
        $validated = $request->validated();
        $usuarioId = Auth::id();

        DB::beginTransaction();
        try {
            $idPrestamo = DB::table('prestamo')->insertGetId([
                'id_usuario' => $validated['id_trabajador'],
                'id_usuario_creacion' => $usuarioId,
                'id_usuario_modificacion' => $usuarioId,
                'fecha_prestamo' => $validated['fecha_prestamo'],
                'fecha_devolucion' => $validated['fecha_devolucion'],
                'estado' => 2,
                'fecha_creacion' => Carbon::now(),
                'fecha_modificacion' => Carbon::now(),
            ]);

            foreach ($validated['series'] as $idSerie) {
                $serie = SerieRecurso::findOrFail($idSerie);

                if ($serie->id_estado != 1) {
                    throw new \Exception("La serie $serie->nro_serie no está disponible.");
                }

                DetallePrestamo::create([
                    'id_prestamo' => $idPrestamo,
                    'id_serie' => $idSerie,
                    'id_recurso' => $serie->id_recurso,
                    'id_estado_prestamo' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $serie->update(['id_estado' => 3]);

                DB::table('stock')->updateOrInsert(
                    ['id_serie_recurso' => $idSerie],
                    [
                        'id_recurso' => $serie->id_recurso,
                        'id_estado_recurso' => 3,
                        'id_usuario' => $validated['id_trabajador'],
                    ]
                );
            }

            DB::commit();
            return Redirect::route('prestamos.index')->with('success', 'Préstamo registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar préstamo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'No se pudo registrar el préstamo. ' . $e->getMessage()]);
        }
    }

public function edit($id): View
{
    $prestamo = Prestamo::with([
        'detallePrestamos.serieRecurso.recurso.subcategoria.categoria',
        'detallePrestamos.estadoPrestamo'
    ])->findOrFail($id);

    $categorias = DB::table('categoria')->get();
    $trabajadores = DB::table('usuario')
        ->where('id_rol', 3)
        ->where('id_estado', 1)
        ->get();

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

    return view('prestamo.edit', compact('prestamo', 'categorias', 'detalles', 'trabajadores'));
}


    public function update(PrestamoRequest $request, $id)
    {
        $request->validate([
            'id_trabajador' => 'required|integer|exists:usuario,id',
            'fecha_prestamo' => 'required|date',
            'fecha_devolucion' => 'nullable|date|after_or_equal:fecha_prestamo',
            'series' => 'nullable|array',
            'series.*' => 'integer|exists:serie_recurso,id',
        ]);

        DB::beginTransaction();
        try {
            DB::table('prestamo')->where('id', $id)->update([
                'id_usuario' => $request->id_trabajador,
                'fecha_prestamo' => $request->fecha_prestamo,
                'fecha_devolucion' => $request->fecha_devolucion,
                'estado' => $request->estado,
                'fecha_modificacion' => now(),
                'id_usuario_modificacion' => Auth::id(),
            ]);

            if ($request->filled('series')) {
                $seriesExistentes = DetallePrestamo::where('id_prestamo', $id)->pluck('id_serie')->toArray();

                foreach ($request->series as $idSerie) {
                    if (in_array($idSerie, $seriesExistentes)) {
                        continue;
                    }

                    $serie = SerieRecurso::findOrFail($idSerie);

                    $yaPrestada = DetallePrestamo::where('id_serie', $idSerie)
                        ->where('id_estado_prestamo', 2)
                        ->where('id_prestamo', '!=', $id)
                        ->exists();

                    if ($serie->id_estado != 1 || $yaPrestada) {
                        throw new \Exception("La serie $serie->nro_serie no está disponible o ya está prestada.");
                    }

                    DetallePrestamo::create([
                        'id_prestamo' => $id,
                        'id_serie' => $idSerie,
                        'id_recurso' => $serie->id_recurso,
                        'id_estado_prestamo' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $serie->update(['id_estado' => 3]);

                    DB::table('stock')->updateOrInsert(
                        ['id_serie_recurso' => $idSerie],
                        [
                            'id_recurso' => $serie->id_recurso,
                            'id_estado_recurso' => 3,
                            'id_usuario' => $request->id_trabajador,
                        ]
                    );
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

      public function devolver($id)
    {
        DB::beginTransaction();
        try {
            $prestamo = Prestamo::findOrFail($id);
            $detalles = DetallePrestamo::where('id_prestamo', $id)->get();

            foreach ($detalles as $detalle) {
                $detalle->update([
                    'id_estado_prestamo' => 3, // Devuelto
                    'updated_at' => now(),
                    'id_usuario_modificacion' => Auth::id(),
                ]);

                SerieRecurso::where('id', $detalle->id_serie)->update(['id_estado' => 1]); // Disponible

                DB::table('stock')->where('id_serie_recurso', $detalle->id_serie)->update([
                    'id_estado_recurso' => 1,
                    'id_usuario' => null,
                ]);
            }

            $todosDevueltos = DetallePrestamo::where('id_prestamo', $id)
                ->where('id_estado_prestamo', '!=', 3)
                ->doesntExist();

            if ($todosDevueltos) {
                $prestamo->update(['estado' => 3]); // Estado préstamo: Devuelto
            }

            DB::commit();
            return redirect()->route('prestamos.index')->with('success', 'Préstamo devuelto correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al devolver préstamo: ' . $e->getMessage());
            return back()->withErrors(['error' => 'No se pudo devolver el préstamo.']);
        }
    }
}
