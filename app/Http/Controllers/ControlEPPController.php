<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recurso;
use App\Models\SerieRecurso;
use App\Models\DetallePrestamo;
use App\Models\Prestamo;
use App\Models\Estado;
use App\Models\Subcategoria;
use App\Models\Categoria;
use App\Models\Usuario;
use App\Models\Checklist;
use Carbon\Carbon;
use App\Models\UsuarioRecurso;
use App\Models\EstadoUsuario;

class ControlEPPController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();

        // Trabajadores con rol "Trabajador"
        $trabajadores = Usuario::whereHas('rol', function($q){
            $q->where('nombre_rol', 'Trabajador');
        })->get();

        $totalTrabajadores = $trabajadores->count();

        // Checklist de hoy
        $checklists = Checklist::with('trabajador')
        ->whereDate('fecha', $hoy)
        ->orderByDesc('hora')
        ->get()
        ->unique('trabajador_id')
        ->values();


        // Validar cumplimiento
        $checklistCompletos = $checklists->filter(function ($c) {
            $basicos = $c->anteojos && $c->botas && $c->chaleco && $c->guantes;
            return $c->es_en_altura ? $basicos && $c->arnes : $basicos;
        })->count();

        $porcentajeChecklist = $totalTrabajadores
            ? round(($checklistCompletos / $totalTrabajadores) * 100)
            : 0;

        $checklistHoyTotal = "{$checklists->count()}/{$totalTrabajadores}";

        // EPP vencidos
        $eppVencidos = SerieRecurso::whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', $hoy)
            ->count();

        // Próximos vencimientos
        $proximosVencimientos = SerieRecurso::whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$hoy, $hoy->copy()->addDays(30)])
            ->count();

        // Subcategorías de EPP
        $subcategoriasEpp = Subcategoria::whereHas('categoria', function ($q) {
            $q->where('nombre_categoria', 'EPP');
        })->get();

        // Trabajadores con EPP asignado
        $trabajadoresConEpp = Usuario::whereHas('usuarioRecursos')
            ->with('usuarioRecursos.recurso')
            ->get();

        // Matriz por subcategoría
        $matriz = [];
        foreach ($trabajadoresConEpp as $usuario) {
            $fila = ['trabajador' => $usuario->name, 'id' => $usuario->id];
            foreach ($subcategoriasEpp as $subcat) {
                $tiene = $usuario->usuarioRecursos->contains(function ($ur) use ($subcat) {
                    return $ur->recurso && $ur->recurso->id_subcategoria === $subcat->id;
                });
                $fila[$subcat->nombre] = $tiene ? '✅' : '❌';
            }
            $matriz[] = $fila;
        }

        
        return view('controlEPP', [
    'porcentajeChecklist' => $porcentajeChecklist,
    'eppVencidos' => $eppVencidos,
    'checklistHoyTotal' => $checklistHoyTotal,
    'proximosVencimientos' => $proximosVencimientos,
    'epps' => $subcategoriasEpp,
    'matriz' => $matriz,
    'trabajadores' => $trabajadoresConEpp,
    'checklistsHoy' => $checklists, // <-- clave que la vista usa
    // opcional: mantener compatibilidad con código que consulte 'checklists'
    'checklists' => $checklists,
]);

    }

    
    public function buscarEPP(Request $request)
{
    $nombre = $request->input('nombre');

    $recursos = Recurso::with(['subcategoria.categoria', 'usuarioRecursos.usuario'])
        ->where(function ($q) use ($nombre) {
            $q->where('nombre', 'like', "%$nombre%")
              ->orWhere('descripcion', 'like', "%$nombre%");
        })
        ->orWhereHas('usuarioRecursos.usuario', function ($q) use ($nombre) {
            $q->where('nombre_usuario', 'like', "%$nombre%");
        })
        ->get();

    $data = [];

    foreach ($recursos as $recurso) {
        // Verificamos si usuarioRecursos es una colección y tiene elementos
        if ($recurso->usuarioRecursos instanceof \Illuminate\Support\Collection && $recurso->usuarioRecursos->isNotEmpty()) {
            foreach ($recurso->usuarioRecursos as $ur) {
                $usuario = $ur->usuario;
                $data[] = [
                    'trabajador' => $usuario ? $usuario->nombre_usuario : 'Sin asignar',
                    'nombre' => $recurso->nombre,
                    'descripcion' => $recurso->descripcion,
                    'subcategoria' => $recurso->subcategoria->nombre ?? '',
                    'categoria' => $recurso->subcategoria->categoria->nombre_categoria ?? '',
                    'costo_unitario' => $recurso->costo_unitario,
                ];
            }
        } else {
            $data[] = [
                'trabajador' => 'Sin asignar',
                'nombre' => $recurso->nombre,
                'descripcion' => $recurso->descripcion,
                'subcategoria' => $recurso->subcategoria->nombre ?? '',
                'categoria' => $recurso->subcategoria->categoria->nombre_categoria ?? '',
                'costo_unitario' => $recurso->costo_unitario,
            ];
        }
    }

    return response()->json([
        'success' => true,
        'data' => $data,
        'message' => $data ? null : 'No se encuentran resultados'
    ]);
}


    public function matrizChecklist(Request $request)
{
    $nombre = $request->input('nombre');

    $trabajadores = Usuario::whereHas('usuarioRecursos.recurso', function ($q) use ($nombre) {
        $q->where('nombre', 'like', "%$nombre%");
    })
    ->orWhere('name', 'like', "%$nombre%")
    ->with('usuarioRecursos.recurso')
    ->get();

    $subcategoriasEpp = Subcategoria::whereHas('categoria', function ($q) {
        $q->where('nombre_categoria', 'EPP');
    })->get();

    $matriz = [];
    foreach ($trabajadores as $usuario) {
        $fila = ['trabajador' => $usuario->name, 'id' => $usuario->id];
        foreach ($subcategoriasEpp as $subcat) {
            $tiene = $usuario->usuarioRecursos->contains(function ($ur) use ($subcat) {
                return $ur->recurso && $ur->recurso->id_subcategoria === $subcat->id;
            });
            $fila[$subcat->nombre] = $tiene ? '✅' : '❌';
        }
        $matriz[] = $fila;
    }

    // ✅ Este es el return que envía los datos al frontend
    return response()->json([
        'success' => true,
        'epps' => $subcategoriasEpp->pluck('nombre')->toArray(),
        'data' => $matriz
    ]);
}

/*
    public function detalleEpp($id)
    {
        $usuario = Usuario::with('usuarioRecursos.recurso')->findOrFail($id);

        return response()->json([
            'trabajador' => [
                'nombre' => $usuario->name,
            ],
            'epps' => $usuario->usuarioRecursos->map(fn($ur) => [
                'nombre' => $ur->recurso->nombre,
                'vencimiento' => $ur->fecha_vencimiento,
            ])
        ]);
    }*/
        public function detalleEpp($id)
{
    $usuario = Usuario::with('usuarioRecursos.serieRecurso.recurso.subcategoria')->findOrFail($id);

    $epps = $usuario->usuarioRecursos->map(function ($ur) {
        return [
            'tipo'  => strtolower(trim(str_replace(['de protección', 'reflectivo'], '', $ur->tipo_epp ?? ($ur->recurso->subcategoria->nombre ?? 'sin tipo')))),
            'serie' => $ur->serieRecurso->nro_serie ?? $ur->serie_recurso->nro_serie ?? 'Sin serie',
            'fecha' => optional($ur->fecha_asignacion)->format('d/m/Y'),
        ];
    });

    return response()->json($epps);
}


    public function create(Request $request)
{
    $trabajadores = \App\Models\Usuario::whereHas('rol', function($q){
        $q->where('nombre_rol', 'Trabajador');
    })->get();

    $preseleccionado = $request->input('trabajador_id');

    return view('checklistEPP', compact('trabajadores', 'preseleccionado'));
}

public function store(Request $request)
{
    $request->validate([
        'trabajador_id' => 'required|exists:usuario,id',
        'observaciones' => 'nullable|string',
        'es_en_altura' => 'nullable|boolean',
        'casco' => 'nullable|boolean',
        'anteojos' => 'nullable|boolean',
        'botas' => 'nullable|boolean',
        'chaleco' => 'nullable|boolean',
        'guantes' => 'nullable|boolean',
        'arnes' => 'nullable|boolean',
    ]);

    $request->merge([
        'observaciones' => $request->observaciones ?: 'Sin observaciones',
    ]);

    // 🔎 Validar que los EPP marcados como usados estén asignados
    $eppsMarcados = collect([
        'casco' => $request->boolean('casco'),
        'guantes' => $request->boolean('guantes'),
        'lentes' => $request->boolean('anteojos'),
        'botas' => $request->boolean('botas'),
        'chaleco' => $request->boolean('chaleco'),
        'arnes' => $request->boolean('arnes'),
    ]);

    $tiposMarcados = $eppsMarcados->filter()->keys()->map(fn($epp) => strtolower(trim($epp)))->toArray();

    $tiposAsignados = UsuarioRecurso::where('id_usuario', $request->trabajador_id)
        ->pluck('tipo_epp')
        ->filter()
        ->map(fn($t) => strtolower(trim($t)))
        ->unique()
        ->toArray();

    $faltantes = array_diff($tiposMarcados, $tiposAsignados);

    if (count($faltantes) > 0) {
        return back()->withErrors([
            'epp_asignacion' => 'No se puede registrar el checklist: hay EPP marcados como usados pero no asignados.'
        ])->withInput();
    }

    // 🔎 Buscar si ya existe un checklist del mismo trabajador hoy
    $checklist = Checklist::where('trabajador_id', $request->trabajador_id)
        ->whereDate('fecha', Carbon::today())
        ->first();

    if ($checklist) {
        // ✅ Si existe, lo actualiza
        $checklist->update([
            'hora' => Carbon::now()->format('H:i'),
            'es_en_altura' => $request->boolean('es_en_altura'),
            'casco' => $request->boolean('casco'),
            'anteojos' => $request->boolean('anteojos'),
            'botas' => $request->boolean('botas'),
            'chaleco' => $request->boolean('chaleco'),
            'guantes' => $request->boolean('guantes'),
            'arnes' => $request->boolean('arnes'),
            'observaciones' => $request->observaciones,
            'critico' => $request->boolean('es_en_altura') && !$request->boolean('arnes'),
        ]);

        return redirect()->route('controlEPP')->with('success', 'Checklist actualizado correctamente.');
    }

    // 🆕 Si no existe, crea uno nuevo
    Checklist::create([
        'trabajador_id' => $request->trabajador_id,
        'supervisor_id' => auth()->id(),
        'fecha' => Carbon::today(),
        'hora' => Carbon::now()->format('H:i'),
        'es_en_altura' => $request->boolean('es_en_altura'),
        'casco' => $request->boolean('casco'),
        'anteojos' => $request->boolean('anteojos'),
        'botas' => $request->boolean('botas'),
        'chaleco' => $request->boolean('chaleco'),
        'guantes' => $request->boolean('guantes'),
        'arnes' => $request->boolean('arnes'),
        'observaciones' => $request->observaciones,
        'critico' => $request->boolean('es_en_altura') && !$request->boolean('arnes'),
    ]);

    return redirect()->route('controlEPP')->with('success', 'Checklist registrado correctamente.');
}



    public function createAsignacionEPP()
{
    $usuarios = Usuario::where('id_rol', 3)
        ->whereHas('estado', function ($q) {
            $q->where('nombre', 'stand by');
        })
        ->whereDoesntHave('usuarioRecursos') // 🔒 Excluir los que ya tienen EPP
        ->get();

    $tiposEpp = ['casco', 'guantes', 'lentes', 'botas', 'chaleco', 'arnes'];
    $seriesDisponibles = [];

    foreach ($tiposEpp as $tipo) {
        $seriesDisponibles[$tipo] = SerieRecurso::whereDoesntHave('usuarioRecurso')
            ->whereHas('recurso.subcategoria', function ($q) use ($tipo) {
                $q->where('nombre', $tipo);
            })
            ->with('recurso.subcategoria')
            ->get();
    }

    return view('asignarEPP', compact('usuarios', 'seriesDisponibles'));
}



public function activarConEPP($id)
{
    $usuario = Usuario::with('usuarioRecursos', 'estado')->findOrFail($id);

    // Solo trabajadores (ajustá id_rol si corresponde)
    if ($usuario->id_rol !== 3) {
        return back()->withErrors(['rol' => 'Solo se pueden activar trabajadores con EPP.']);
    }

    // Solo permitir activar si está en stand by o ya en Alta
    $estadoActual = optional($usuario->estado)->nombre;
    if (! in_array($estadoActual, ['stand by', 'Alta'])) {
        return back()->withErrors(['estado' => "No se puede dar de alta desde el estado '{$estadoActual}'. Primero pase a stand by."]);
    }

    // Normalizar y recolectar tipos asignados
    $tiposAsignados = $usuario->usuarioRecursos
        ->pluck('tipo_epp')
        ->filter()
        ->map(fn($t) => strtolower(trim($t)))
        ->unique()
        ->values()
        ->toArray();

    $tiposObligatorios = ['casco','guantes','lentes','botas','chaleco','arnes'];
    $faltantes = array_diff($tiposObligatorios, $tiposAsignados);

    if (count($faltantes) > 0) {
        return back()->withErrors([
            'faltantes' => 'No se puede dar de alta. Faltan: ' . implode(', ', $faltantes)
        ])->withInput();
    }

    $estadoAlta = EstadoUsuario::where('nombre', 'Alta')->first();
    if (!$estadoAlta) {
        return back()->withErrors(['estado' => 'No se encontró el estado "Alta".']);
    }

    $usuario->id_estado = $estadoAlta->id;
    $usuario->usuario_modificacion = auth()->id();
    $usuario->save();

    \Log::info("Usuario {$usuario->id} activado a Alta por usuario " . auth()->id());

    return redirect()->route('usuarios.edit', $usuario->id)->with('success', 'Trabajador dado de alta correctamente.');
}





public function storeAsignacionEPP(Request $request)
{
    $request->validate([
        'usuario_id' => 'required|exists:usuario,id',
        'casco' => 'required|integer|exists:serie_recurso,id',
        'guantes' => 'required|integer|exists:serie_recurso,id',
        'lentes' => 'required|integer|exists:serie_recurso,id',
        'botas' => 'required|integer|exists:serie_recurso,id',
        'chaleco' => 'required|integer|exists:serie_recurso,id',
        'arnes' => 'required|integer|exists:serie_recurso,id',
        'fecha_asignacion' => 'required|date',
    ]);

    $usuario = Usuario::with('estado', 'usuarioRecursos')->findOrFail($request->usuario_id);

    // El usuario debe estar en 'stand by' para poder asignarle EPP
    $estadoNombre = optional($usuario->estado)->nombre;
    if ($estadoNombre !== 'stand by') {
        return back()->withErrors(['usuario_id' => 'El usuario debe estar en stand by para asignarle EPP.'])->withInput();
    }

    $tipos = ['casco', 'guantes', 'lentes', 'botas', 'chaleco', 'arnes'];

    // Evitar asignaciones duplicadas del mismo tipo al mismo usuario
    $tiposExistentes = $usuario->usuarioRecursos
        ->pluck('tipo_epp')
        ->filter()
        ->map(fn($t) => strtolower(trim($t)))
        ->toArray();

    foreach ($tipos as $t) {
        if (in_array($t, $tiposExistentes)) {
            return back()->withErrors([$t => "El usuario ya tiene asignado un {$t}."])->withInput();
        }
    }

    try {
        \DB::beginTransaction();

        foreach ($tipos as $epp) {
            $serieId = $request->input($epp);

            // Verificar existencia y estado de la serie (disponible y no asignada)
            $serie = SerieRecurso::with('recurso')->lockForUpdate()->findOrFail($serieId);

            // Si la serie ya está marcada como prestada u asignada en usuario_recurso, abortar
            $yaAsignadaEnUsuarioRecurso = UsuarioRecurso::where('id_serie_recurso', $serieId)->exists();
            if ($yaAsignadaEnUsuarioRecurso) {
                \DB::rollBack();
                return back()->withErrors([
                    $epp => "La serie {$serie->nro_serie} ya está asignada a otro trabajador."
                ])->withInput();
            }

            // Comprobar estado de la serie (asumiendo id_estado 1 = Disponible)
            if ($serie->id_estado !== 1) {
                \DB::rollBack();
                return back()->withErrors([
                    $epp => "La serie {$serie->nro_serie} no está disponible para asignación."
                ])->withInput();
            }

            // Crear asignación
            UsuarioRecurso::create([
                'id_usuario' => $request->usuario_id,
                'id_serie_recurso' => $serieId,
                'id_recurso' => $serie->recurso->id,
                'fecha_asignacion' => $request->fecha_asignacion,
                'tipo_epp' => strtolower(trim($epp)),
            ]);

            // Actualizar estado de la serie a "Prestado" (id_estado = 3)
            $serie->update(['id_estado' => 3]);
        }

        \DB::commit();

        return redirect()->route('controlEPP')->with('success', 'EPP asignado correctamente. El trabajador sigue en stand by.');
    } catch (\Throwable $e) {
        \DB::rollBack();
        \Log::error('Error asignando EPP: ' . $e->getMessage(), [
            'usuario_id' => $request->usuario_id,
            'input' => $request->all()
        ]);
        return back()->withErrors(['error' => 'Ocurrió un error al asignar EPP. Intentá nuevamente.'])->withInput();
    }
}


public function buscarSeriesEPP(Request $request)
{
    $tipo = $request->input('tipo');
    $query = $request->input('q');

    // 🔒 Mapeo fijo entre frontend y nombres reales
    $mapa = [
        'casco' => 'Casco',
        'guantes' => 'guantes',
        'lentes' => 'lentes',
        'botas' => 'botas',
        'chaleco' => 'Chaleco',
        'arnes' => 'Arnes',
    ];

    $nombreSubcat = $mapa[$tipo] ?? null;

    if (!$nombreSubcat) {
        return response()->json([]);
    }

    $baseQuery = SerieRecurso::whereHas('recurso.subcategoria', function ($q) use ($nombreSubcat) {
        $q->where('nombre', $nombreSubcat);
    })
    ->whereHas('estado', function ($q) {
        $q->where('nombre_estado', 'Disponible');
    })
    ->whereDoesntHave('usuarioRecurso')
    ->with(['recurso', 'estado']);

    if (!empty($query)) {
        $baseQuery->whereRaw('LOWER(TRIM(nro_serie)) LIKE ?', ['%' . strtolower(trim($query)) . '%']);
    }

    $series = $baseQuery->limit(20)->get();

    if ($tipo === 'guantes') {
    \Log::info('Guantes disponibles:', $series->pluck('nro_serie')->toArray());
}


    return response()->json($series->map(function ($serie) {
        return [
            'id' => $serie->id,
            'nro_serie' => $serie->nro_serie,
            'recurso' => $serie->recurso->nombre,
            'vencimiento' => Carbon::parse($serie->fecha_vencimiento)->format('d/m/Y'),
        ];
    }));
}

public function faltantes()
{
    // Subcategorías de EPP
    $subcategoriasEpp = Subcategoria::whereHas('categoria', function ($q) {
        $q->where('nombre_categoria', 'EPP');
    })->get()->keyBy('nombre');
   
    // Mapeo de campos del checklist → nombre de subcategoría
    $mapaEpp = [
    'anteojos' => 'lentes',
    'botas' => 'botas',
    'chaleco' => 'Chaleco Test',
    'guantes' => 'guantes',
    'arnes' => 'Arnes',
    'casco' => 'Casco',
];

    // Todos los trabajadores
    $trabajadores = Usuario::whereHas('rol', function ($q) {
        $q->where('nombre_rol', 'Trabajador');
    })->get();

    // Checklist del día actual
    $checklists = Checklist::select('trabajador_id', 'anteojos', 'botas', 'chaleco', 'guantes', 'arnes', 'es_en_altura')
        ->whereDate('fecha', \Carbon\Carbon::today())
        ->get()
        ->keyBy('trabajador_id');

    // Recursos asignados por trabajador
    $asignados = UsuarioRecurso::with('recurso.subcategoria')->get()->groupBy('id_usuario');

    $faltantes = [];

    foreach ($trabajadores as $usuario) {
        $usuarioId = $usuario->id;
        $check = $checklists[$usuarioId] ?? null;
        $tiene = $asignados[$usuarioId] ?? collect();
        $faltante = [];

        // Solo evaluamos si tiene checklist hoy
        if (!$check) {
            continue;
        }

        // Determinar qué EPP necesita
        $necesita = array_keys($mapaEpp);

        if (!$check->es_en_altura) {
            $necesita = array_filter($necesita, fn($tipo) => $tipo !== 'arnes');
        }

        // Evaluar faltantes
        foreach ($necesita as $campo) {
            $nombreSubcat = $mapaEpp[$campo];
            if ($subcategoriasEpp->has($nombreSubcat)) {
                $subcatId = $subcategoriasEpp[$nombreSubcat]->id;

                $tieneEseEpp = $tiene->contains(function ($ur) use ($subcatId) {
                    return $ur->recurso && $ur->recurso->id_subcategoria === $subcatId;
                });

                if (!$tieneEseEpp) {
                    $faltante[] = $nombreSubcat;
                }
            }
        }

        // Extra: si trabaja en altura y no marcó arnés
        if ($check->es_en_altura && !$check->arnes) {
            $faltante[] = 'Arnés (obligatorio en altura)';
        }

        if (count($faltante)) {
            $faltantes[$usuarioId] = $faltante;
        }
    }

    // Obtener nombres de los trabajadores
    $usuarios = Usuario::whereIn('id', array_keys($faltantes))->pluck('name', 'id');

    //dd($faltantes);

    return view('epp.faltantes', compact('faltantes', 'usuarios'));
}



public function sinChecklist()
{
    $hoy = \Carbon\Carbon::today();

    // Usuarios con rol "Trabajador"
    $trabajadores = \App\Models\Usuario::whereHas('rol', function ($q) {
        $q->where('nombre_rol', 'Trabajador');
    })->get();

    // IDs con checklist hoy
    $conChecklistHoy = \App\Models\Checklist::whereDate('fecha', $hoy)
        ->pluck('trabajador_id')
        ->unique();

    // Filtrar los que no tienen checklist
    $sinChecklist = $trabajadores->filter(function ($usuario) use ($conChecklistHoy) {
        return !$conChecklistHoy->contains($usuario->id);
    });

    return view('epp.sin_checklist', compact('sinChecklist'));
}


    public function verTablaChecklist()
{
    $checklists = Checklist::with('trabajador')->whereDate('fecha', today())->get();
    return view('checklist_tabla', compact('checklists'));
}

    public function verSoloChecklist()
{
    $hoy = Carbon::today();

    // Checklist de hoy con relación al trabajador
    $checklists = Checklist::with('trabajador')
        ->whereDate('fecha', $hoy)
        ->get();

    // Subcategorías de EPP
    $subcategoriasEpp = Subcategoria::whereHas('categoria', function ($q) {
        $q->where('nombre_categoria', 'EPP');
    })->get();

    // Trabajadores con EPP asignado
    $trabajadoresConEpp = Usuario::whereHas('usuarioRecursos')
        ->with('usuarioRecursos.recurso')
        ->get();

    // Matriz por subcategoría
    $matriz = [];
    foreach ($trabajadoresConEpp as $usuario) {
        $fila = ['trabajador' => $usuario->name, 'id' => $usuario->id];
        foreach ($subcategoriasEpp as $subcat) {
            $tiene = $usuario->usuarioRecursos->contains(function ($ur) use ($subcat) {
                return $ur->recurso && $ur->recurso->id_subcategoria === $subcat->id;
            });
            $fila[$subcat->nombre] = $tiene ? '✅' : '❌';
        }
        $matriz[] = $fila;
    }

    return view('checklist_tabla', [
        'checklists' => $checklists,
        'epps' => $subcategoriasEpp,
        'matriz' => $matriz,
        'trabajadores' => $trabajadoresConEpp,
    ]);
}


}
