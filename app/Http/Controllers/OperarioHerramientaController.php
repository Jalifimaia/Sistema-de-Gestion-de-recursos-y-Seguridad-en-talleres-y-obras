<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prestamo;

class OperarioHerramientaController extends Controller
{
    public function index()
    {
        // Cargar los datos igual que la página de préstamos
        $herramientas = \DB::table('prestamo')
            ->join('detalle_prestamo', 'prestamo.id', '=', 'detalle_prestamo.id_prestamo')
            ->join('serie_recurso', 'detalle_prestamo.id_serie', '=', 'serie_recurso.id')
            ->join('recurso', 'detalle_prestamo.id_recurso', '=', 'recurso.id')
            ->join('usuario', 'prestamo.id_usuario', '=', 'usuario.id')
            ->join('estado_prestamo', 'detalle_prestamo.id_estado_prestamo', '=', 'estado_prestamo.id')
            ->select(
                'prestamo.id',
                'usuario.name as operario',
                'recurso.nombre as recurso',
                'serie_recurso.nro_serie',
                'prestamo.fecha_prestamo',
                'prestamo.fecha_devolucion',
                'estado_prestamo.nombre as estado'
            )
            ->where('prestamo.id_usuario', Auth::id())
            ->orderByDesc('prestamo.id')
            ->get();

        return view('operario.mis_herramientas', compact('herramientas'));
    }
}
