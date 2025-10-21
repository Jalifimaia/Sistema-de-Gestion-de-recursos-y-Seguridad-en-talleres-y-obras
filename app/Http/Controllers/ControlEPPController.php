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
use Carbon\Carbon;

class ControlEPPController extends Controller
{
    public function index()
    {
        // Checklist Diario: porcentaje de trabajadores con checklist completo hoy
        $hoy = Carbon::today();
        $trabajadores = Usuario::whereHas('rol', function($q){ $q->where('nombre_rol', 'Trabajador'); })->get();
        $totalTrabajadores = $trabajadores->count();
        // Simulación: cantidad de checklist completos hoy (debería venir de una tabla checklist)
        $checklistHoy = 3; // TODO: Reemplazar por consulta real
        $porcentajeChecklist = $totalTrabajadores ? round(($checklistHoy / $totalTrabajadores) * 100) : 0;

        // EPP Vencidos: contar serie_recurso con fecha_vencimiento < hoy
        $eppVencidos = SerieRecurso::whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', $hoy)
            ->count();

        // Checklist Hoy: cantidad de trabajadores con checklist hoy
        // Simulación: 3 de 4 trabajadores (debería venir de tabla checklist)
        $checklistHoyTotal = "$checklistHoy/$totalTrabajadores";

        // Próximos Vencimientos: serie_recurso con fecha_vencimiento en los próximos 30 días
        $proximosVencimientos = SerieRecurso::whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$hoy, $hoy->copy()->addDays(30)])
            ->count();

        // Tabla de estado de EPP por trabajador (simulación, debe ajustarse a tu modelo real)
        $estadoEPP = [
            [
                'trabajador' => 'Carlos Mendez',
                'cargo' => 'Soldador - Producción A',
                'casco' => true,
                'guantes' => false,
                'anteojos' => true,
                'arnes' => null,
                'chaleco' => true,
                'cumplimiento' => 'Bueno',
                'porcentaje' => 80,
                'ultimo_checklist' => '2024-12-08',
            ],
            // ...agrega lógica real aquí
        ];

// Obtener todos los EPP de la categoría 'EPP'
        $epps = Recurso::whereHas('subcategoria.categoria', function ($q) {
            $q->where('nombre_categoria', 'EPP');
        })->get();

// Obtener trabajadores con al menos un EPP asignado
        $trabajadores = Usuario::whereHas('usuarioRecursos')->with('usuarioRecursos.recurso')->get();

// Construir matriz
        $matriz = [];
        foreach ($trabajadores as $usuario) {
            $fila = ['trabajador' => $usuario->name];
            foreach ($epps as $epp) {
                $tiene = $usuario->usuarioRecursos->contains(function ($ur) use ($epp) {
                    return $ur->id_recurso === $epp->id;
                });
                $fila[$epp->nombre] = $tiene ? '✅' : '❌';
            }
            $matriz[] = $fila;
        }

        
        return view('controlEPP', [
            'porcentajeChecklist' => $porcentajeChecklist,
            'eppVencidos' => $eppVencidos,
            'checklistHoyTotal' => $checklistHoyTotal,
            'proximosVencimientos' => $proximosVencimientos,
            'estadoEPP' => $estadoEPP,
            'epps' => $epps,
            'matriz' => $matriz,
            'trabajadores' => $trabajadores,
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
                $q->where('name', 'like', "%$nombre%");
            })
            ->get();

        // Si no hay resultados, devolver mensaje
        if ($recursos->isEmpty()) {
            return response()->json(['success' => true, 'data' => [], 'message' => 'No se encuentran resultados']);
        }

        // Formatear datos para la tabla (puedes personalizar según tu vista)
        $data = [];
        foreach ($recursos as $recurso) {
            // Puede haber varios usuarios por recurso
            if ($recurso->usuarioRecursos && count($recurso->usuarioRecursos)) {
                foreach ($recurso->usuarioRecursos as $ur) {
                    $usuario = $ur->usuario;
                    $data[] = [
                        'trabajador' => $usuario ? $usuario->name : 'Sin asignar',
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

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function matrizChecklist(Request $request)
    {
        $nombre = $request->input('nombre');

        // Buscar trabajadores que tengan al menos un EPP asignado
        $trabajadores = Usuario::whereHas('usuarioRecursos.recurso', function ($q) use ($nombre) {
            $q->where('nombre', 'like', "%$nombre%");
        })
        ->orWhere('name', 'like', "%$nombre%")
        ->with('usuarioRecursos.recurso')
        ->get();

        // Obtener todos los EPP de la categoría 'EPP'
        $epps = Recurso::whereHas('subcategoria.categoria', function ($q) {
            $q->where('nombre_categoria', 'EPP');
        })->get();

        // Construir matriz
        $matriz = [];
        foreach ($trabajadores as $usuario) {
            $fila = ['trabajador' => $usuario->name];
            foreach ($epps as $epp) {
                $tiene = $usuario->usuarioRecursos->contains(function ($ur) use ($epp) {
                    return $ur->id_recurso === $epp->id;
                });
                $fila[$epp->nombre] = $tiene ? '✅' : '❌';
            }
            $matriz[] = $fila;
        }

        return response()->json([
            'success' => true,
            'epps' => $epps->pluck('nombre')->toArray(),
            'data' => $matriz
        ]);
    }

    public function detalleEpp($id)
    {
        $usuario = Usuario::with('usuarioRecursos.recurso')->findOrFail($id);

        return response()->json([
            'trabajador' => [
                'nombre' => $usuario->name,
                'sector' => $usuario->sector->nombre ?? 'Sin sector',
            ],
            'epps' => $usuario->usuarioRecursos->map(fn($ur) => [
                'nombre' => $ur->recurso->nombre,
                'vencimiento' => $ur->fecha_vencimiento,
            ])
        ]);
    }

}
