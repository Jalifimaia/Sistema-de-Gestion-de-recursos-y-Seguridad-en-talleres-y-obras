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
            $basicos = $c->lentes && $c->botas && $c->chaleco && $c->guantes;
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

    $recursos = Recurso::with([
            'subcategoria.categoria',
            'usuarioRecursos.usuario',
            'serieRecursos.color',
            'serieRecursos.talleModel'
        ])
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
        $subcategoria = strtolower($recurso->subcategoria->nombre ?? '');
        $requiereTalle = in_array($subcategoria, ['chaleco', 'botas']);

        if ($recurso->usuarioRecursos instanceof \Illuminate\Support\Collection && $recurso->usuarioRecursos->isNotEmpty()) {
            foreach ($recurso->usuarioRecursos as $ur) {
                $usuario = $ur->usuario;
                foreach ($recurso->serieRecursos as $serie) {
                    $item = [
                        'trabajador'   => $usuario ? $usuario->nombre_usuario : 'Sin asignar',
                        'nombre'       => $recurso->nombre,
                        'descripcion'  => $recurso->descripcion,
                        'subcategoria' => $recurso->subcategoria->nombre ?? '',
                        'categoria'    => $recurso->subcategoria->categoria->nombre_categoria ?? '',
                        'costo_unitario' => $recurso->costo_unitario,
                        'color'        => $serie->color ? $serie->color->nombre : 'Sin color',
                    ];
                    if ($requiereTalle) {
                        $item['talle'] = $serie->talleModel ? $serie->talleModel->nombre : ($serie->talle ?? 'Sin talle');
                    }
                    $data[] = $item;
                }
            }
        } else {
            foreach ($recurso->serieRecursos as $serie) {
                $item = [
                    'trabajador'   => 'Sin asignar',
                    'nombre'       => $recurso->nombre,
                    'descripcion'  => $recurso->descripcion,
                    'subcategoria' => $recurso->subcategoria->nombre ?? '',
                    'categoria'    => $recurso->subcategoria->categoria->nombre_categoria ?? '',
                    'costo_unitario' => $recurso->costo_unitario,
                    'color'        => $serie->color ? $serie->color->nombre : 'Sin color',
                ];
                if ($requiereTalle) {
                    $item['talle'] = $serie->talleModel ? $serie->talleModel->nombre : ($serie->talle ?? 'Sin talle');
                }
                $data[] = $item;
            }
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
        'lentes' => 'nullable|boolean',
        'botas' => 'nullable|boolean',
        'chaleco' => 'nullable|boolean',
        'guantes' => 'nullable|boolean',
        'arnes' => 'nullable|boolean',
    ]);

    $request->merge([
        'observaciones' => $request->observaciones ?: 'Sin observaciones',
        'lentes' => $request->boolean('lentes'),
    ]);

    // 🔎 Validar que los EPP marcados como usados estén asignados
    $eppsMarcados = collect([
        'casco' => $request->boolean('casco'),
        'guantes' => $request->boolean('guantes'),
        'lentes' => $request->boolean('lentes'),
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
            'lentes' => $request->boolean('lentes'),
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
        'lentes' => $request->boolean('lentes'),
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
    ->with('usuarioRecursos') // traer relación para usarla en la vista/JS si hace falta
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

    // Solo permitir activar si está en stand by o ya en Alta
    $estadoActual = optional($usuario->estado)->nombre;
    if (! in_array($estadoActual, ['stand by', 'Alta'])) {
        return back()->withErrors(['estado' => "No se puede dar de alta desde el estado '{$estadoActual}'. Primero pase a stand by."]);
    }

    // Validar EPP solo si es trabajador
    if ($usuario->id_rol === 3) {
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
    }

    $estadoAlta = EstadoUsuario::where('nombre', 'Alta')->first();
    if (!$estadoAlta) {
        return back()->withErrors(['estado' => 'No se encontró el estado "Alta".']);
    }

    $usuario->id_estado = $estadoAlta->id;
    $usuario->usuario_modificacion = auth()->id();
    $usuario->save();

    \Log::info("Usuario {$usuario->id} activado a Alta por usuario " . auth()->id());

    return redirect()->route('usuarios.edit', $usuario->id)->with('success', 'Usuario dado de alta correctamente.');
}


public function validarHoy($id)
{
    $existe = Checklist::where('trabajador_id', $id)
                ->whereDate('created_at', now()->toDateString())
                ->exists();


    return response()->json(['existe' => $existe]);
}

public function storeAsignacionEPP(Request $request)
{
    $messages = [
    'usuario_id.required' => 'Seleccioná un trabajador.',
    'usuario_id.exists' => 'El trabajador seleccionado no existe.',
    'casco.required' => 'Seleccioná un casco.',
    'casco.integer' => 'Valor inválido para casco.',
    'casco.exists' => 'La serie de casco seleccionada no existe.',
    'guantes.required' => 'Seleccioná guantes.',
    'guantes.integer' => 'Valor inválido para guantes.',
    'guantes.exists' => 'La serie de guantes seleccionada no existe.',
    'lentes.required' => 'Seleccioná lentes.',
    'lentes.integer' => 'Valor inválido para lentes.',
    'lentes.exists' => 'La serie de lentes seleccionada no existe.',
    'botas.required' => 'Seleccioná botas.',
    'botas.integer' => 'Valor inválido para botas.',
    'botas.exists' => 'La serie de botas seleccionada no existe.',
    'chaleco.required' => 'Seleccioná chaleco.',
    'chaleco.integer' => 'Valor inválido para chaleco.',
    'chaleco.exists' => 'La serie de chaleco seleccionada no existe.',
    'arnes.required' => 'Seleccioná arnés.',
    'arnes.integer' => 'Valor inválido para arnés.',
    'arnes.exists' => 'La serie de arnés seleccionada no existe.',
    'fecha_asignacion.required' => 'Seleccioná la fecha de asignación.',
    'fecha_asignacion.date' => 'La fecha de asignación no es válida.',
];

$attributes = [
    'usuario_id' => 'trabajador',
    'casco' => 'casco',
    'guantes' => 'guantes',
    'lentes' => 'lentes',
    'botas' => 'botas',
    'chaleco' => 'chaleco',
    'arnes' => 'arnés',
    'fecha_asignacion' => 'fecha de asignación',
];

$request->validate([
    'usuario_id' => 'required|exists:usuario,id',
    'casco' => 'required|integer|exists:serie_recurso,id',
    'guantes' => 'required|integer|exists:serie_recurso,id',
    'lentes' => 'required|integer|exists:serie_recurso,id',
    'botas' => 'required|integer|exists:serie_recurso,id',
    'chaleco' => 'required|integer|exists:serie_recurso,id',
    'arnes' => 'required|integer|exists:serie_recurso,id',
    'fecha_asignacion' => 'required|date',
], $messages, $attributes);


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


public function getDisponibles($tipo)
{
    // Mapeo de tipo a subcategoría (normalizado)
    $mapa = [
        'casco'   => 'Casco',
        'guantes' => 'Guantes',
        'lentes'  => 'Lentes',
        'botas'   => 'Botas',
        'chaleco' => 'Chaleco',
        'arnes'   => 'Arnes',
    ];

    $nombreSubcat = $mapa[$tipo] ?? null;

    if (!$nombreSubcat) {
        return response()->json([]);
    }

    // 👇 Cargar relaciones de color y talleModel
    $series = SerieRecurso::with(['color', 'talleModel', 'recurso.subcategoria', 'estado'])
        ->whereHas('recurso.subcategoria', function ($q) use ($nombreSubcat) {
            $q->where('nombre', $nombreSubcat);
        })
        ->whereHas('estado', function ($q) {
            $q->where('nombre_estado', 'Disponible'); // 👈 Cambié "nombre_estado" a "nombre"
        })
        ->whereDoesntHave('usuarioRecurso')
        ->get();

    // 👇 Formatear respuesta con color y talle desde relaciones
    return response()->json($series->map(function ($serie) {
        return [
            'id' => $serie->id,
            'nro_serie' => $serie->nro_serie,
            'color' => $serie->color ? $serie->color->nombre : null,  // 👈 Desde relación
            'talle' => $serie->talleModel ? $serie->talleModel->nombre : $serie->talle, // 👈 Prioriza talleModel
        ];
    }));
}

/**
 * Obtiene EPP ya asignados a un usuario (para mostrar en la tabla)
 * Ruta esperada: GET /epp/asignados/{userId}
 */
public function getAsignados($userId)
{
    // 👇 Eager loading de relaciones necesarias
    $asignados = UsuarioRecurso::with(['serieRecurso.recurso.subcategoria', 'serieRecurso.color', 'serieRecurso.talleModel'])
        ->where('id_usuario', $userId)
        ->whereNull('fecha_devolucion')  // Solo asignados actualmente
        ->get();

    return response()->json($asignados->map(function ($asignacion) {
        $serie = $asignacion->serieRecurso;
        $subcategoria = $serie->recurso->subcategoria->nombre ?? 'Desconocido';

        return [
            'tipo' => strtolower($subcategoria),
            'serie' => $serie->nro_serie,
            'color' => $serie->color ? $serie->color->nombre : null,
            'talle' => $serie->talleModel ? $serie->talleModel->nombre : $serie->talle,
        ];
    }));
}

// 🔧 TAMBIÉN CORRIJO EL MÉTODO buscarSeriesEPP (línea ~481)
public function buscarSeriesEPP(Request $request)
{
    $tipo = $request->input('tipo');
    $query = $request->input('q');

    $mapa = [
        'casco'   => 'Casco',
        'guantes' => 'Guantes',
        'lentes'  => 'Lentes',
        'botas'   => 'Botas',
        'chaleco' => 'Chaleco',
        'arnes'   => 'Arnes',
    ];

    $nombreSubcat = $mapa[$tipo] ?? null;

    if (!$nombreSubcat) {
        return response()->json([]);
    }

    $series = SerieRecurso::whereHas('recurso.subcategoria', function ($q) use ($nombreSubcat) {
            $q->where('nombre', $nombreSubcat);
        })
        ->whereHas('estado', function ($q) {
            $q->where('nombre_estado', 'Disponible'); // 👈 CORRECCIÓN: era "nombre_estado"
        })
        ->whereDoesntHave('usuarioRecurso')
        ->with(['recurso.subcategoria', 'color', 'talleModel', 'estado'])
        ->when($query, function ($q) use ($query) {
            $q->whereRaw('LOWER(TRIM(nro_serie)) LIKE ?', ['%' . strtolower(trim($query)) . '%']);
        })
        ->limit(20)
        ->get();

    return response()->json($series->map(function ($serie) {
        return [
            'id'        => $serie->id,
            'nro_serie' => $serie->nro_serie,
            'recurso'   => optional($serie->recurso)->nombre ?? 'Sin recurso',
            'color'     => $serie->color ? $serie->color->nombre : null, // 👈 Desde relación
            'talle'     => $serie->talleModel ? $serie->talleModel->nombre : $serie->talle,
        ];
    }));
}

public function getDisponiblesPorQuery(Request $request)
{
    $tipo = $request->input('tipo');
    
    if (!$tipo) {
        return response()->json([]);
    }
    
    // Reutilizar el método getDisponibles
    return $this->getDisponibles($tipo);
}




public function faltantes()
{
    // Subcategorías EPP por nombre normalizado
    $subcategoriasEpp = Subcategoria::whereHas('categoria', function ($q) {
        $q->where('nombre_categoria', 'EPP');
    })->get()->keyBy(fn($s) => mb_strtolower(trim($s->nombre)));

    // Mapeo por ID de subcategoría (más robusto)
    $mapaEpp = [
        'lentes' => $subcategoriasEpp['lentes']->id ?? null,
        'botas'    => $subcategoriasEpp['botas']->id ?? null,
        'chaleco'  => $subcategoriasEpp['chaleco']->id ?? null,
        'guantes'  => $subcategoriasEpp['guantes']->id ?? null,
        'arnes'    => $subcategoriasEpp['arnes']->id ?? null,
        'casco'    => $subcategoriasEpp['casco']->id ?? null,
    ];

    // Trabajadores activos o en standby
    $estadosConsiderados = [1, 3];
    $trabajadores = Usuario::whereIn('id_estado', $estadosConsiderados)
        ->whereHas('rol', fn($q) => $q->where('nombre_rol', 'Trabajador'))
        ->get(['id', 'name']);

    if ($trabajadores->isEmpty()) {
        return view('epp.faltantes', ['faltantes' => [], 'usuarios' => []]);
    }

    $usuarioIds = $trabajadores->pluck('id')->all();

    // Checklist más reciente por trabajador
    $checklists = Checklist::whereIn('trabajador_id', $usuarioIds)
        ->orderByDesc('fecha')
        ->get()
        ->groupBy('trabajador_id')
        ->map(fn($group) => $group->first());

    // Asignaciones sin filtros extra
    $asignados = UsuarioRecurso::with('recurso.subcategoria')
        ->whereIn('id_usuario', $usuarioIds)
        ->get()
        ->groupBy('id_usuario');

    $faltantes = [];

    foreach ($trabajadores as $usuario) {
        $usuarioId = $usuario->id;
        $check = $checklists->get($usuarioId) ?? null;
        if (!$check) continue;

        $necesita = array_keys($mapaEpp);
        if (!$check->es_en_altura) {
            $necesita = array_filter($necesita, fn($tipo) => $tipo !== 'arnes');
        }

        $tieneAsignaciones = $asignados->get($usuarioId) ?? collect();
        $tieneIdsSubcat = $tieneAsignaciones
            ->filter(fn($ur) => $ur->recurso && $ur->recurso->subcategoria)
            ->map(fn($ur) => (int)$ur->recurso->subcategoria->id)
            ->unique()
            ->values()
            ->all();

        $faltante = [];

        foreach ($necesita as $campo) {
            $subcatId = $mapaEpp[$campo] ?? null;
            if (!$subcatId) {
                $faltante[] = "(Falta configurar subcategoría para: {$campo})";
                continue;
            }

            if (!in_array($subcatId, $tieneIdsSubcat, true)) {
                $nombreSubcat = $subcategoriasEpp->firstWhere('id', $subcatId)?->nombre ?? 'Desconocido';
                $faltante[] = $nombreSubcat;
            }
        }

        if ($check->es_en_altura && empty($check->arnes)) {
            $faltante[] = 'Arnés (no marcado en checklist)';
        }

        if (!empty($faltante)) {
            $faltantes[$usuarioId] = $faltante;
        }
    }

    $usuarios = Usuario::whereIn('id', array_keys($faltantes))->pluck('name', 'id')->toArray();

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
