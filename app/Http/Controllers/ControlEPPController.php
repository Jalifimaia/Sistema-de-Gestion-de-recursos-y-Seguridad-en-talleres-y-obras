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
            ->get();

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
    }

    public function create()
{
    $trabajadores = Usuario::whereHas('rol', function($q){
        $q->where('nombre_rol', 'Trabajador');
    })->get();

    return view('checklistEPP', compact('trabajadores'));
}

    public function store(Request $request)
{
    $request->validate([
        'trabajador_id' => 'required|exists:usuario,id',
        'observaciones' => 'nullable|string',
        'es_en_altura' => 'nullable|boolean',
        'anteojos' => 'nullable|boolean',
        'botas' => 'nullable|boolean',
        'chaleco' => 'nullable|boolean',
        'guantes' => 'nullable|boolean',
        'arnes' => 'nullable|boolean',
    ]);

    $checklist = Checklist::create([
        'trabajador_id' => $request->trabajador_id,
        'supervisor_id' => auth()->id(),
        'fecha' => Carbon::today(),
        'hora' => Carbon::now()->format('H:i'),
        'es_en_altura' => $request->boolean('es_en_altura'),
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


public function activarTrabajador($id)
{
    $usuario = Usuario::with('usuarioRecursos')->findOrFail($id);

    $tiposObligatorios = ['casco', 'guantes', 'lentes', 'botas', 'chaleco', 'arnes'];

    $tiposAsignados = $usuario->usuarioRecursos->pluck('tipo_epp')->toArray();

    $faltantes = array_diff($tiposObligatorios, $tiposAsignados);

    if (count($faltantes) > 0) {
        return back()->withErrors([
            'faltantes' => 'No se puede dar de alta. Faltan: ' . implode(', ', $faltantes)
        ]);
    }

    $usuario->update(['id_estado' => 1]); // Alta

    return redirect()->route('usuarios.index')->with('success', 'Trabajador dado de alta correctamente.');
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

    foreach (['casco', 'guantes', 'lentes', 'botas', 'chaleco', 'arnes'] as $epp) {
        $serieId = $request->$epp;

        // Validar que la serie no esté ya asignada
        if (UsuarioRecurso::where('id_serie_recurso', $serieId)->exists()) {
            return back()->withErrors([
                $epp => "La serie ya está asignada a otro trabajador."
            ])->withInput();
        }

        // Obtener el recurso desde la serie
        $serie = SerieRecurso::with('recurso')->findOrFail($serieId);

        // Registrar asignación
        UsuarioRecurso::create([
            'id_usuario' => $request->usuario_id,
            'id_serie_recurso' => $serieId,
            'id_recurso' => $serie->recurso->id,
            'fecha_asignacion' => $request->fecha_asignacion,
        ]);
    }

    return redirect()->route('controlEPP')->with('success', 'EPP asignado correctamente. El trabajador sigue en stand by.');
}


    public function buscarSeriesEPP(Request $request)
{
    $tipo = $request->input('tipo');
    $query = $request->input('q');

    $baseQuery = SerieRecurso::whereHas('recurso.subcategoria', function ($q) use ($tipo) {
        $q->where('nombre', $tipo);
    })
    ->whereHas('estado', function ($q) {
        $q->where('nombre_estado', 'Disponible'); // 🔹 Solo estado "Disponible"
    })
    ->whereDoesntHave('usuarioRecurso') // 🔹 No asignadas
    ->with(['recurso', 'estado']); // 🔹 Carga relaciones necesarias

    if (!empty($query)) {
        $baseQuery->whereRaw('LOWER(TRIM(nro_serie)) LIKE ?', ['%' . strtolower(trim($query)) . '%']);
    }

    $series = $baseQuery->limit(20)->get();

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
    // Lógica para detectar trabajadores con EPP incompleto
    return view('supervisor.faltantes'); // creá esta vista
}

public function sinChecklist()
{
    // Lógica para detectar trabajadores sin checklist hoy
    return view('supervisor.sin_checklist'); // creá esta vista
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
