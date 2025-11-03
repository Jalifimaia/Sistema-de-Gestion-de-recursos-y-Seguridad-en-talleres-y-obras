<?php

namespace App\Http\Controllers;

use App\Models\Incidente;
use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Recurso;
use App\Models\EstadoIncidente;
use App\Models\SerieRecurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Estado;
use Carbon\Carbon;

class IncidenteController extends Controller
{
    // =======================
    // LISTAR INCIDENTES
    // =======================
public function index()
{
    $incidentes = Incidente::with([
        'trabajador',
        'recursos.subcategoria.categoria', // cargar recursos con relaciones necesarias
        'estadoIncidente'
    ])->get();

    // pasar mapa de estados para evitar consultas en bucle
    $estados = Estado::all()->keyBy('id')->map(fn($e) => $e->nombre_estado)->toArray();

    return view('incidente.index', compact('incidentes', 'estados'));
}

    // =======================
    // FORMULARIO CREAR NUEVO
    // =======================
    public function create()
    {
        $categorias = Categoria::with([
            'subcategorias' => function($query) {
                $query->with([
                    'recursos' => function($q) {
                        $q->with('serieRecursos');
                    }
                ]);
            }
        ])->get();

        $trabajadores = Usuario::where('id_rol', 3)->get(); // rol 3 = Trabajador
        $estados = Estado::all();

        return view('incidente.create', compact('categorias', 'trabajadores', 'estados'));
    }

    // =======================
    // GUARDAR NUEVO INCIDENTE
    // =======================

    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuario,id',
            'recursos'   => 'required|array|min:1',
            'recursos.*.id_categoria'     => 'required|exists:categoria,id',
            'recursos.*.id_subcategoria'  => 'required|exists:subcategoria,id',
            'recursos.*.id_recurso'       => 'required|exists:recurso,id',
            'recursos.*.id_serie_recurso' => 'required|exists:serie_recurso,id',
            'recursos.*.id_estado'        => 'required|exists:estado,id',
            'descripcion'                 => 'required|string|max:255',
            'fecha_incidente'             => 'required|date',
        ]);

        // justo después de $request->validate([...]);
        //dd($request->input('recursos'));

        $usuario = Usuario::where('id', $request->id_usuario)
                        ->where('id_rol', 3)
                        ->firstOrFail();

        $estadoRevision = EstadoIncidente::where('nombre_estado', 'En revisión')->first();

        DB::transaction(function() use ($request, $usuario, $estadoRevision, &$incidente) {
            $incidente = Incidente::create([
                'id_trabajador'       => $usuario->id,
                'id_supervisor'       => auth()->id(),
                'descripcion'         => $request->descripcion,
                'fecha_incidente'     => $request->fecha_incidente,
                'id_estado_incidente' => $estadoRevision?->id,
                'fecha_creacion'      => \Carbon\Carbon::now('UTC')->toDateTimeString(),
                'fecha_modificacion'  => \Carbon\Carbon::now('UTC')->toDateTimeString(),
            ]);

            // Preparar array para sync(): [recurso_id => pivotColsArray]
            $attach = [];
            foreach ($request->recursos as $r) {
                $attach[$r['id_recurso']] = [
                    'id_serie_recurso' => $r['id_serie_recurso'],
                    'id_estado' => $r['id_estado'],
                    'created_at' => \Carbon\Carbon::now('UTC')->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now('UTC')->toDateTimeString(),
                ];
            }

            $incidente->recursos()->sync($attach);
        });

        return redirect()->route('incidente.create')->with('success', '✅ Incidente registrado correctamente.');
    }




    // =======================
    // MOSTRAR UN INCIDENTE
    // =======================
    public function show($id)
    {
        $incidente = Incidente::with([
            'trabajador',
            'recursos.subcategoria.categoria',
            'estadoIncidente'
        ])->findOrFail($id);

        $estados = Estado::all()->keyBy('id')->map(fn($e) => $e->nombre_estado)->toArray();

        return view('incidente.show', compact('incidente', 'estados'));
    }

    // =======================
    // FORMULARIO EDITAR INCIDENTE
    // =======================
    public function edit($id)
    {
        $incidente = Incidente::with([
            'trabajador',
            'recurso.subcategoria.categoria',
            'estadoIncidente',
            'serieRecurso'
        ])->findOrFail($id);

        $categorias     = Categoria::all();
        $subcategorias  = Subcategoria::all();
        $recursos       = Recurso::all();
        $estados        = EstadoIncidente::all(); // para el select del estado del incidente
        $trabajadores   = Usuario::where('id_rol', 3)->get();
        $estadosRecurso = Estado::all(); // <-- esto faltaba: estados de recursos

        return view('incidente.edit', compact(
            'incidente',
            'categorias',
            'subcategorias',
            'recursos',
            'estados',
            'estadosRecurso',
            'trabajadores'
        ));
    }



    // =======================
    // ACTUALIZAR INCIDENTE
    // =======================
  
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_trabajador' => 'required|exists:usuario,id',
            'descripcion' => 'required|string|max:255',
            'id_estado_incidente' => 'required|exists:estado_incidente,id',
            'fecha_incidente' => 'required|date',
            'resolucion' => 'nullable|string|max:255',
            'recursos' => 'required|array|min:1',
            'recursos.*.id_recurso' => 'required|exists:recurso,id',
            'recursos.*.id_serie_recurso' => 'required|exists:serie_recurso,id',
            'recursos.*.id_estado' => 'required|exists:estado,id',
        ]);
        // justo después de $request->validate([...]);
        //dd($request->input('recursos'));



        $incidente = Incidente::findOrFail($id);

        DB::transaction(function() use ($request, $incidente) {
            // actualizar campos principales y fecha_modificacion
            $incidente->update([
                'id_trabajador' => $request->id_trabajador,
                'descripcion' => $request->descripcion,
                'id_estado_incidente' => $request->id_estado_incidente,
                'resolucion' => $request->resolucion,
                'fecha_incidente' => $request->fecha_incidente,
                'fecha_modificacion' => \Carbon\Carbon::now('UTC')->toDateTimeString(),
            ]);

            // Si se marcó Resuelto ahora y no tenía fecha_cierre_incidente, setearla
            $estadoResuelto = EstadoIncidente::where('nombre_estado', 'Resuelto')->first();
            if ($estadoResuelto && $request->id_estado_incidente == $estadoResuelto->id && !$incidente->fecha_cierre_incidente) {
                $incidente->update(['fecha_cierre_incidente' =>  \Carbon\Carbon::now('UTC')->toDateTimeString()]);
            }

            // Preparar array para sync: reemplaza pivot con nuevos estados/series y timestamps
            $attach = [];
            foreach ($request->recursos as $r) {
                $attach[$r['id_recurso']] = [
                    'id_serie_recurso' => $r['id_serie_recurso'],
                    'id_estado' => $r['id_estado'],
                    'updated_at' => \Carbon\Carbon::now('UTC')->toDateTimeString(),
                    'created_at' => \Carbon\Carbon::now('UTC')->toDateTimeString(),
                ];
            }
            $incidente->recursos()->sync($attach);
        });

        return redirect()->route('incidente.edit', $id)->with('success', '✅ Incidente actualizado correctamente.');
    }


    // =======================
    // ELIMINAR INCIDENTE
    // =======================
    public function destroy($id)
    {
        $incidente = Incidente::findOrFail($id);
        $incidente->delete();

        return redirect()->route('incidente.index')->with('success', '🗑️ Incidente eliminado correctamente.');
    }

    // =======================
    // AJAX: BUSCAR USUARIO POR DNI
    // =======================
public function buscarUsuarioPorDni($dni)
{
    $dni = str_replace(['.', ' '], '', $dni);

    $usuario = \App\Models\Usuario::where('id_rol', 3) // solo trabajadores
        ->whereRaw("REPLACE(REPLACE(dni, '.', ''), ' ', '') = ?", [$dni])
        ->first();

    if ($usuario) {
        return response()->json([
            'id'     => $usuario->id,
            'nombre' => $usuario->name,
        ]);
    }

    return response()->json(['error' => 'Trabajador no encontrado o no es rol válido'], 404);
}


    // Obtener subcategorías por categoría
    public function getSubcategorias($categoriaId)
    {
        $subcategorias = Subcategoria::where('categoria_id', $categoriaId)
                            ->select('id', 'nombre as nombre')
                            ->get();
        return response()->json($subcategorias);
    }

    // Obtener recursos por subcategoría
    public function getRecursos($subcategoriaId)
    {
        $recursos = Recurso::where('id_subcategoria', $subcategoriaId)
                        ->select('id', 'nombre')
                        ->get();
        return response()->json($recursos);
    }

    // Obtener series por recurso
    public function getSeries($recursoId)
    {
        $series = SerieRecurso::where('id_recurso', $recursoId)
                        ->select('id', 'nro_serie')
                        ->get();
        return response()->json($series);
    }
}
