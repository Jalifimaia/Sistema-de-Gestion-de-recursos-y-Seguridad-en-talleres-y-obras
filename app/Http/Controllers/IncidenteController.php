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
            'recursos.subcategoria.categoria',
            'recursos', // contiene pivot con id_estado y id_serie_recurso
            'estadoIncidente',
            'serieRecurso'
        ])->findOrFail($id);

        // IDs de los estados de recurso permitidos para cerrar (Disponible, Baja)
        $estadosPermitidos = Estado::whereIn('nombre_estado', ['Disponible', 'Baja'])->pluck('id')->toArray();

        // calcular si todos los recursos asociados están en estados permitidos
        $puedeMarcarResuelto = $incidente->recursos->every(function ($r) use ($estadosPermitidos) {
            return in_array($r->pivot->id_estado ?? null, $estadosPermitidos);
        });

        // cargar estados de incidente pero EXCLUIR Reportado y Escalado
        $estados = EstadoIncidente::whereNotIn('nombre_estado', ['Reportado', 'Escalado'])->get();

        // obtener el registro "Resuelto"
        $resueltoEstado = EstadoIncidente::where('nombre_estado', 'Resuelto')->first();

        $categorias     = Categoria::all();
        $subcategorias  = Subcategoria::all();
        $recursos       = Recurso::all();
        $trabajadores   = Usuario::where('id_rol', 3)->get();
        $estadosRecurso = Estado::all();

        return view('incidente.edit', compact(
            'incidente',
            'categorias',
            'subcategorias',
            'recursos',
            'estados',
            'trabajadores',
            'estadosRecurso',
            'puedeMarcarResuelto',
            'estadosPermitidos',
            'resueltoEstado'
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

        $incidente = Incidente::findOrFail($id);

        // Si ya está resuelto, no permitimos editar
        $estadoResuelto = EstadoIncidente::where('nombre_estado', 'Resuelto')->first();
        if ($estadoResuelto && $incidente->id_estado_incidente == $estadoResuelto->id) {
            return redirect()->route('incidente.edit', $id)
                ->withErrors(['error' => 'El incidente ya está resuelto y no puede editarse.']);
        }

        
        $estadoResuelto = EstadoIncidente::where('nombre_estado', 'Resuelto')->first();
        $permitidosIds = Estado::whereIn('nombre_estado', ['Disponible', 'Baja'])->pluck('id')->toArray();

        // Verificar si todos los recursos están en estado permitido
        $puedeMarcarResuelto = collect($request->recursos)->every(function ($r) use ($permitidosIds) {
            return in_array($r['id_estado'], $permitidosIds);
        });

        // Si todos los recursos están en estado permitido, y hay resolución, forzar estado a Resuelto
        if ($puedeMarcarResuelto) {
            if (empty($request->resolucion)) {
                return back()->withErrors(['resolucion' => 'Debe ingresar una resolución para cerrar el incidente.'])->withInput();
            }
            $request->merge([
                'id_estado_incidente' => optional($estadoResuelto)->id
            ]);
        }


        try {
            DB::beginTransaction();

            // Actualizar incidente
            $incidente->update([
                'id_trabajador' => $request->id_trabajador,
                'descripcion' => $request->descripcion,
                'id_estado_incidente' => $request->id_estado_incidente,
                'resolucion' => $request->resolucion,
                'fecha_incidente' => Carbon::parse($request->fecha_incidente)->format('Y-m-d H:i:s'),
                'fecha_modificacion' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
            ]);

            // Actualizar recursos pivot
            $attach = [];
            foreach ($request->recursos as $r) {
                $attach[$r['id_recurso']] = [
                    'id_serie_recurso' => $r['id_serie_recurso'],
                    'id_estado' => $r['id_estado'],
                    'updated_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                    'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                ];
            }
            $incidente->recursos()->sync($attach);

            
            // Si el estado final es Resuelto y aún no tenía fecha de cierre, setearla
            if ($request->id_estado_incidente == optional($estadoResuelto)->id && !$incidente->fecha_cierre_incidente) {
                $incidente->update(['fecha_cierre_incidente' => Carbon::now('UTC')->format('Y-m-d H:i:s')]);
            }



            DB::commit();

            return redirect()->route('incidente.edit', $id)->with('success', '✅ Incidente actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar incidente: ' . $e->getMessage(), [
                'incidente_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return back()->withErrors(['error' => 'No se pudo actualizar el incidente. ' . $e->getMessage()]);
        }
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
