<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Recurso;
use App\Models\SerieRecurso;
use App\Models\DetallePrestamo;
use App\Services\PrestamoService;
use Illuminate\Support\Facades\DB;
use App\Models\checklist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class KioskoController extends Controller
{
    public function index()
    {
        return view('terminal.index');
    }

    // ✅ Categorías
    public function getCategorias()
    {
        return Categoria::all();
    }

    // ✅ Identificación de trabajador (DNI o QR)

public function identificarTrabajador(Request $request)
{
    $clave = $request->input('clave');
    \Log::info('🔍 identificando trabajador por clave', ['clave' => $clave]);

    if (! $clave) {
        return response()->json([
            'success' => false,
            'message' => 'No se recibió ninguna clave'
        ]);
    }

    $usuarios = Usuario::whereNotNull('password')->get();

    $usuario = $usuarios->first(function ($u) use ($clave) {
        return \Hash::check($clave, $u->password);
    });

    if (! $usuario) {
        return response()->json([
            'success' => false,
            'message' => 'Clave inválida o usuario no encontrado'
        ]);
    }

    if ($usuario->id_rol != 3) {
        return response()->json([
            'success' => false,
            'message' => 'Este usuario no tiene permisos para usar el kiosco'
        ]);
    }

    if ($usuario->id_estado != 1) {
        return response()->json([
            'success' => false,
            'message' => 'El usuario no está en estado de "alta" y no puede usar el kiosco'
        ]);
    }

    return response()->json([
        'success' => true,
        'usuario' => $usuario->only(['id','name','dni','email','codigo_qr'])
    ]);
}

public function identificarPorQR(Request $request)
{
    $codigoQR = $request->input('codigo_qr');
    \Log::info('🔍 identificando por QR', ['codigo_qr' => $codigoQR]);

    if (! $codigoQR) {
        return response()->json([
            'success' => false,
            'message' => 'No se recibió ningún código QR'
        ]);
    }

    $usuario = Usuario::where('codigo_qr', $codigoQR)->first();

    if (! $usuario) {
        return response()->json([
            'success' => false,
            'message' => 'QR no encontrado en el sistema'
        ]);
    }

    if ($usuario->id_rol != 3) {
        return response()->json([
            'success' => false,
            'message' => 'Este usuario no tiene permisos para usar el kiosco'
        ]);
    }

    if ($usuario->id_estado != 1) {
        return response()->json([
            'success' => false,
            'message' => 'El usuario no está en estado de "alta" y no puede usar el kiosco'
        ]);
    }

    return response()->json([
        'success' => true,
        'usuario' => $usuario->only(['id','name','dni','email','codigo_qr'])
    ]);
}


    // ✅ Recursos asignados al trabajador
    public function recursosAsignados($usuarioId)
    {
        try {
            $detalles = DetallePrestamo::with('serieRecurso.recurso.subcategoria.categoria', 'prestamo')
                ->whereHas('prestamo', function ($q) use ($usuarioId) {
                    $q->where('id_usuario', $usuarioId)
                      ->where('estado', 2); // solo activos
                })
                ->where('id_estado_prestamo', 2) // asignado
                ->get()
                ->map(function($d) {
                    return [
                        'detalle_id'       => $d->id,
                        'categoria'        => $d->serieRecurso->recurso->subcategoria->categoria->nombre_categoria ?? '-',
                        'subcategoria'     => $d->serieRecurso->recurso->subcategoria->nombre ?? '-',
                        'recurso'          => $d->serieRecurso->recurso->nombre ?? '-',
                        'serie'            => $d->serieRecurso->nro_serie ?? '-',
                        'fecha_prestamo'   => $d->prestamo->fecha_prestamo ? \Carbon\Carbon::parse($d->prestamo->fecha_prestamo)->format('Y-m-d') : '-',
                        'fecha_devolucion' => $d->prestamo->fecha_devolucion ? \Carbon\Carbon::parse($d->prestamo->fecha_devolucion)->format('Y-m-d') : '-',
                    ];
                });

            return response()->json($detalles);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno: '.$e->getMessage()], 500);
        }
    }

    // ✅ Registrar manualmente (usa PrestamoService)
    public function registrarManual(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_usuario' => 'required|exists:usuario,id',
                'serie_id'   => 'required|exists:serie_recurso,id',
            ]);

            $prestamo = app(PrestamoService::class)->crearPrestamo(
                $validated['id_usuario'],
                [$validated['serie_id']],
                'terminal'
            );

            return response()->json(['success' => true, 'prestamo' => $prestamo->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ✅ Devolver recurso

public function devolverRecurso($id)
{
    DB::beginTransaction();
    try {
        $detalle = DetallePrestamo::find($id);

        if (! $detalle) {
            return response()->json(['success' => false, 'message' => 'Detalle no encontrado']);
        }

        if ($detalle->id_estado_prestamo != 2) {
            return response()->json(['success' => false, 'message' => '']);
            // o directamente: return response()->json(['success' => false]);
        }


        // Marcar detalle como devuelto
        $detalle->id_estado_prestamo = 4;
        $detalle->fecha_devolucion = now();
        $detalle->save();

        // Liberar serie
        if ($detalle->id_serie) {
            SerieRecurso::where('id', $detalle->id_serie)
                ->update(['id_estado' => 1, 'updated_at' => now()]);

            Stock::where('id_serie_recurso', $detalle->id_serie)
                ->update(['id_estado_recurso' => 1, 'id_usuario' => null, 'updated_at' => now()]);
        }

        // Cerrar préstamo si todos los detalles están devueltos
        $prestamo = Prestamo::find($detalle->id_prestamo);
        if ($prestamo && ! $prestamo->detalles()->where('id_estado_prestamo', '!=', 4)->exists()) {
            $prestamo->estado = 3;
            $prestamo->fecha_devolucion = $prestamo->fecha_devolucion ?? now();
            $prestamo->save();
        }

        DB::commit();
        return response()->json(['success' => true]);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Error al devolver recurso', 'error' => $e->getMessage()]);
    }
}


    // ✅ Subcategorías
    public function getSubcategorias($categoriaId)
    {
        return Subcategoria::where('categoria_id', $categoriaId)->get();
    }

    public function getSubcategoriasConDisponibles($categoriaId)
    {
        $subcategorias = Subcategoria::where('categoria_id', $categoriaId)
            ->withCount(['recursos as disponibles' => function ($q) {
                $q->whereHas('series', function ($s) {
                    $s->where('id_estado', 1);
                });
            }])
            ->get();

        return response()->json($subcategorias);
    }

    // ✅ Recursos
    public function getRecursos($subcategoriaId)
    {
        return Recurso::where('id_subcategoria', $subcategoriaId)->get();
    }

    public function getRecursosConSeries($subcategoriaId)
    {
        $recursos = Recurso::where('id_subcategoria', $subcategoriaId)
            ->whereHas('series', function ($query) {
                $query->where('id_estado', 1);
            })
            ->get();

        return response()->json($recursos);
    }

    public function getRecursosConDisponibles($subcategoriaId)
    {
        $recursos = Recurso::where('id_subcategoria', $subcategoriaId)
            ->withCount(['series as disponibles' => function ($q) {
                $q->where('id_estado', 1);
            }])
            ->get();

        return response()->json($recursos);
    }

    // ✅ Series
    public function getSeries($recursoId)
    {
        return SerieRecurso::where('id_recurso', $recursoId)
            ->where('id_estado', 1)
            ->get();
    }

    // Placeholder para solicitudes genéricas
    /*public function solicitarRecurso(Request $request)
    {
        return response()->json(['message' => 'Solicitud recibida']);
    }*/

        private function tieneEppCompleto($usuarioId)
{
    $checklist = Checklist::where('trabajador_id', $usuarioId)
        ->whereDate('fecha', Carbon::today())
        ->latest()
        ->first();

    if (!$checklist) return false;

    $basicos = $checklist->anteojos && $checklist->botas && $checklist->chaleco && $checklist->guantes;

    return $checklist->es_en_altura ? $basicos && $checklist->arnes : $basicos;
}

    public function solicitarRecurso(Request $request)
{
    $usuarioId = $request->input('id_usuario');

    /*if (!$this->tieneEppCompleto($usuarioId)) {
        return response()->json([
            'success' => false,
            'message' => 'No se puede solicitar herramientas sin EPP completo.'
        ], 403);
    }*/

    // Lógica de solicitud real (si la tenés)
    return response()->json([
        'success' => true,
        'message' => 'Solicitud permitida'
    ]);
}

}
