<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrestamoRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrestamoController extends Controller
{
    public function index()
    {
        $prestamos = DB::table('prestamo')
            ->join('detalle_prestamo', 'prestamo.id', '=', 'detalle_prestamo.id_prestamo')
            ->join('serie_recurso', 'detalle_prestamo.id_serie', '=', 'serie_recurso.id')
            ->join('recurso', 'detalle_prestamo.id_recurso', '=', 'recurso.id')
            ->select(
                'prestamo.id',
                'prestamo.fecha_prestamo',
                'prestamo.fecha_devolucion',
                'prestamo.estado',
                'recurso.nombre as recurso',
                'serie_recurso.nro_serie'
            )
            ->orderByDesc('prestamo.id')
            ->get();

        return view('prestamo.index', compact('prestamos'));
    }

    public function create()
    {
        $series = DB::table('serie_recurso')
            ->join('recurso', 'serie_recurso.id_recurso', '=', 'recurso.id')
            ->join('estado', 'serie_recurso.id_estado', '=', 'estado.id')
            ->where('estado.nombre_estado', '=', 'Disponible')
            ->select('serie_recurso.id', 'serie_recurso.nro_serie', 'recurso.nombre as recurso')
            ->get();

        return view('prestamo.create', compact('series'));
    }

    public function store(PrestamoRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // insertar en prestamo
            $id_prestamo = DB::table('prestamo')->insertGetId([
                'id_usuario' => auth()->id() ?? 5, // usuario actual o admin por defecto
                'id_usuario_creacion' => auth()->id() ?? 5,
                'id_usuario_modificacion' => auth()->id() ?? 5,
                'fecha_prestamo' => $validated['fecha_prestamo'],
                'fecha_devolucion' => $validated['fecha_devolucion'],
                'estado' => $validated['estado'],
                'fecha_creacion' => Carbon::now(),
                'fecha_modificacion' => Carbon::now(),
            ]);

            // obtener el recurso correspondiente a la serie
            $serie = DB::table('serie_recurso')->where('id', $validated['id_serie'])->first();

            // insertar detalle del préstamo
            DB::table('detalle_prestamo')->insert([
                'id_prestamo' => $id_prestamo,
                'id_serie' => $validated['id_serie'],
                'id_recurso' => $serie->id_recurso,
            ]);

            // actualizar estado de la serie a "Prestado"
            DB::table('serie_recurso')
                ->where('id', $validated['id_serie'])
                ->update(['id_estado' => 3]);

            DB::commit();
                return redirect()->route('prestamos.index')->with('success', 'Préstamo registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
