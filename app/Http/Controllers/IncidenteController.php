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

class IncidenteController extends Controller
{
    // =======================
    // LISTAR INCIDENTES
    // =======================
    public function index()
    {
        $incidentes = Incidente::with([
            'usuarioCreacion',        // trabajador
            'recurso.subcategoria.categoria', // categoría y subcategoría
            'estadoIncidente'         // estado
        ])->get();

        return view('incidente.index', compact('incidentes'));
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

        return view('incidente.create', compact('categorias'));
    }


    // =======================
    // GUARDAR NUEVO INCIDENTE
    // =======================
    public function store(Request $request)
    {
        $request->validate([
            'dni_usuario' => 'required|exists:usuario,dni',
            'id_categoria' => 'required|exists:categoria,id',
            'id_subcategoria' => 'required|exists:subcategoria,id',
            'id_recurso' => 'required|exists:recurso,id',
            'id_serie_recurso' => 'required|exists:serie_recurso,id',
            'descripcion' => 'required|string|max:255',
            'fecha_incidente' => 'required|date',
        ]);

        // Buscar usuario por DNI
        $usuario = Usuario::where('dni', $request->dni_usuario)->first();

        // Obtener estado "En revisión"
        $estadoRevision = EstadoIncidente::where('nombre_estado', 'En revisión')->first();

        Incidente::create([
            'id_usuario' => $usuario->id,
            'id_recurso' => $request->id_recurso,
            'id_serie_recurso' => $request->id_serie_recurso,
            'descripcion' => $request->descripcion,
            'fecha_incidente' => $request->fecha_incidente,
            'id_estado' => $estadoRevision ? $estadoRevision->id : null,
        ]);

        return redirect()->route('incidente.index')->with('success', '✅ Incidente registrado correctamente.');
    }

    // =======================
    // MOSTRAR UN INCIDENTE
    // =======================
    public function show($id)
    {
        $incidente = Incidente::with(['usuarioCreacion', 'recurso', 'estado'])->findOrFail($id);
        return view('incidente.show', compact('incidente'));
    }

    // =======================
    // FORMULARIO EDITAR INCIDENTE
    // =======================
    public function edit($id)
    {
        $incidente = Incidente::with([
        'usuarioCreacion',
        'recurso.subcategoria.categoria',
        'estadoIncidente',
        'serieRecurso' // reemplazamos 'serie'
    ])->findOrFail($id);


        $categorias = Categoria::all();
        $subcategorias = Subcategoria::all();
        $recursos = Recurso::all();
        $estados = EstadoIncidente::all();

        return view('incidente.edit', compact('incidente', 'categorias', 'subcategorias', 'recursos', 'estados'));
    }


    // =======================
    // ACTUALIZAR INCIDENTE
    // =======================
    public function update(Request $request, $id)
    {
        $incidente = Incidente::findOrFail($id);

        $incidente->update([
            'descripcion' => $request->descripcion,
            'id_estado_incidente' => $request->id_estado_incidente,
            'resolucion' => $request->resolucion,
            'fecha_incidente' => $request->fecha_incidente,
            // otros campos si querés permitir cambios
        ]);

        return redirect()->route('incidente.index')->with('success', 'Incidente actualizado correctamente');
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
        // Limpiamos puntos y espacios
        $dni = str_replace(['.', ' '], '', $dni);

        $usuario = \App\Models\Usuario::whereRaw("REPLACE(REPLACE(dni, '.', ''), ' ', '') = ?", [$dni])->first();

        if ($usuario) {
            return response()->json([
                'id' => $usuario->id,
                'nombre' => $usuario->name,
            ]);
        }

        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }

    // Obtener subcategorías por categoría
    public function getSubcategorias($categoriaId)
    {
        $subcategorias = \App\Models\Subcategoria::where('categoria_id', $categoriaId)
                            ->select('id', 'nombre as nombre')
                            ->get();
        return response()->json($subcategorias);
    }

    // Obtener recursos por subcategoría
    public function getRecursos($subcategoriaId)
    {
        $recursos = \App\Models\Recurso::where('id_subcategoria', $subcategoriaId)
                        ->select('id', 'nombre')
                        ->get();
        return response()->json($recursos);
    }

    // Obtener series por recurso
    public function getSeries($recursoId)
    {
        $series = \App\Models\SerieRecurso::where('id_recurso', $recursoId)
                        ->select('id', 'nro_serie')
                        ->get();
        return response()->json($series);
    }
}