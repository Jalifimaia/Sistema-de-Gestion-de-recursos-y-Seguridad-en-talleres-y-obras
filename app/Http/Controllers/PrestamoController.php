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
use Illuminate\Http\Request;


class PrestamoController extends Controller
{
public function index(): View
{
    $search = request('search');
    $estado = request('estado');
    $creador = request('creador');
    $fechaInicio = request('fecha_inicio');
    $fechaFin = request('fecha_fin');

    $query = request('search');

    $base = DB::table('prestamo')
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
            'prestamo.fecha_creacion',
            'estado_prestamo.nombre as estado'
        )
        ->whereIn('prestamo.estado', [1, 2, 3])

        ->when($estado, function ($q) use ($estado) {
            $q->where('estado_prestamo.nombre', $estado);
        })
        ->when($creador, function ($q) use ($creador) {
            $q->where('creador.name', $creador);
        })
        ->when($fechaInicio, function ($q) use ($fechaInicio) {
            $q->whereDate('prestamo.fecha_creacion', '>=', $fechaInicio);
        })
        ->when($fechaFin, function ($q) use ($fechaFin) {
            $q->whereDate('prestamo.fecha_creacion', '<=', $fechaFin);
        })
        ->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('recurso.nombre', 'like', "%{$search}%")
                    ->orWhere('serie_recurso.nro_serie', 'like', "%{$search}%")
                    ->orWhere('trabajador.name', 'like', "%{$search}%")
                    ->orWhere('creador.name', 'like', "%{$search}%");
            });
        })


        ->when($query, function ($q) use ($query) {
        $q->where(function ($sub) use ($query) {
            $sub->where('recurso.nombre', 'like', "%{$query}%")
                ->orWhere('serie_recurso.nro_serie', 'like', "%{$query}%")
                ->orWhere('trabajador.name', 'like', "%{$query}%")
                ->orWhere('creador.name', 'like', "%{$query}%");
        });
    })

        ->groupBy(
            'prestamo.id',
            'trabajador.name',
            'creador.name',
            'recurso.nombre',
            'serie_recurso.nro_serie',
            'prestamo.fecha_prestamo',
            'prestamo.fecha_devolucion',
            'prestamo.fecha_creacion',
            'estado_prestamo.nombre'
        )


        ->orderByDesc('prestamo.fecha_creacion');

    $prestamos = DB::table(DB::raw("({$base->toSql()}) as sub"))
        ->mergeBindings($base)
        ->paginate(18)
        ->onEachSide(1)
        ->withQueryString();

    return view('prestamo.index', compact('prestamos'));
}


public function ultimosPrestamos(Request $request)
{
    $query = DB::table('prestamo')
        ->leftJoin('usuario', 'prestamo.id_usuario', '=', 'usuario.id')
        ->select(
            'prestamo.id',
            'prestamo.fecha_prestamo',
            'prestamo.fecha_devolucion',
            'prestamo.estado',
            'usuario.name as trabajador'
        );

    if ($request->filled('fecha_inicio')) {
        $query->where('prestamo.fecha_prestamo', '>=', $request->fecha_inicio);
    }

    if ($request->filled('fecha_fin')) {
        $query->where('prestamo.fecha_prestamo', '<=', $request->fecha_fin);
    }

    $prestamos = $query->orderBy('prestamo.fecha_prestamo', 'desc')
                       ->limit(3)
                       ->get();

    return view('reportes.reportePrestamos', compact('prestamos'));
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

        SerieRecurso::where('id', $detalle->id_serie)->update(['id_estado' => 1]);

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
    $adminId = Auth::id();
    $series = $validated['series'] ?? [];

    if (empty($series)) {
        return back()->withErrors(['series' => 'No se enviaron series para prestar.']);
    }

    DB::beginTransaction();
    try {
        $workerId = $validated['id_trabajador'];
        $fechaPrestamo = Carbon::today();
        $fechaDevolucion = Carbon::tomorrow();

        $idPrestamo = DB::table('prestamo')->insertGetId([
            'id_usuario'              => $workerId,
            'id_usuario_creacion'     => $adminId,
            'id_usuario_modificacion' => $adminId,
            'fecha_prestamo'          => $fechaPrestamo,
            'fecha_devolucion'        => $fechaDevolucion,
            'estado'                  => 2, // Activo
            'fecha_creacion'          => now(),
            'fecha_modificacion'      => now(),
        ]);

        foreach ($series as $idSerie) {
            // Validar que la serie esté disponible
            $serie = SerieRecurso::where('id', $idSerie)->where('id_estado', 1)->first();
            if (! $serie) {
                throw new \Exception("La serie con id {$idSerie} ya no está disponible.");
            }

            // Registrar el detalle del préstamo
            $detalle = DetallePrestamo::create([
                'id_prestamo'        => $idPrestamo,
                'id_serie'           => $idSerie,
                'id_recurso'         => $serie->id_recurso,
                'id_estado_prestamo' => 2, // Asignado / Activo
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            if (! $detalle) {
                throw new \Exception("No se pudo registrar el préstamo para la serie {$idSerie}.");
            }

            // Marcar la serie como prestada
            $serie->update(['id_estado' => 3]);

            // Actualizar stock
            DB::table('stock')->updateOrInsert(
                ['id_serie_recurso' => $idSerie],
                [
                    'id_recurso'        => $serie->id_recurso,
                    'id_estado_recurso' => 3,
                    'id_usuario'        => $workerId,
                ]
            );
        }

        DB::commit();
        return Redirect::route('prestamos.index')->with('success', 'Préstamo registrado correctamente.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::warning('Error al registrar préstamo: ' . $e->getMessage(), [
            'user' => $adminId,
            'request_series' => $series
        ]);
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

        // 🔄 Si el préstamo fue cancelado, liberar las series
        if ($request->estado == 1) {
            $detalles = DetallePrestamo::where('id_prestamo', $id)->get();

            foreach ($detalles as $detalle) {
                SerieRecurso::where('id', $detalle->id_serie)->update(['id_estado' => 1]);

                DB::table('stock')->where('id_serie_recurso', $detalle->id_serie)->update([
                    'id_estado_recurso' => 1,
                    'id_usuario' => null,
                ]);
            }
        }

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
        return redirect()->route('prestamos.edit', $id)->with('success', 'Préstamo actualizado correctamente.');    } catch (\Exception $e) {
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
            // Marcar el detalle como devuelto
            $detalle->update([
                'id_estado_prestamo' => 3, // Devuelto
                'updated_at' => now(),
                'id_usuario_modificacion' => Auth::id(),
            ]);

                SerieRecurso::where('id', $detalle->id_serie)->update(['id_estado' => 1]); // Disponible

            // Actualizar el stock
            DB::table('stock')->where('id_serie_recurso', $detalle->id_serie)->update([
                    'id_estado_recurso' => 1,
                'id_usuario' => null,
            ]);
        }

        // Si todos los detalles están devueltos, actualizar el estado del préstamo
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
