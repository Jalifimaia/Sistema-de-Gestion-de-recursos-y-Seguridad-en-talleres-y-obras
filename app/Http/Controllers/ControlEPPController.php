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
}
