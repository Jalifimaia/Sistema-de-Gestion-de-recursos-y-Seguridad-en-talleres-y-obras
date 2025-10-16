<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\Usuario;
use App\Models\Recurso;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{


public function exportarPrestamosPDF(Request $request)
{
    $fecha_inicio = $request->input('fecha_inicio');
    $fecha_fin = $request->input('fecha_fin');

    $query = DB::table('prestamo')
        ->leftJoin('usuario', 'prestamo.id_usuario', '=', 'usuario.id')
        ->leftJoin('estado_prestamo', 'prestamo.estado', '=', 'estado_prestamo.id')
        ->select(
            'prestamo.fecha_prestamo',
            'prestamo.fecha_devolucion',
            'estado_prestamo.nombre as estado',
            'usuario.name as trabajador'
        );

    if ($fecha_inicio) {
        $query->where('prestamo.fecha_prestamo', '>=', $fecha_inicio);
    }

    if ($fecha_fin) {
        $query->where('prestamo.fecha_prestamo', '<=', $fecha_fin);
    }

    $prestamos = $query->orderBy('prestamo.fecha_prestamo', 'desc')->get();

    foreach ($prestamos as $p) {
        $p->duracion = $p->fecha_devolucion
            ? Carbon::parse($p->fecha_prestamo)->diffInDays(Carbon::parse($p->fecha_devolucion))
            : '—';
    }

    $total = $prestamos->count();

    $pdf = Pdf::loadView('reportes.reportePrestamosPDF', compact('prestamos', 'fecha_inicio', 'fecha_fin', 'total'));
    return $pdf->download('reporte_prestamos.pdf');
}



    
public function reportePrestamos(Request $request)
{
    $fecha_inicio = $request->input('fecha_inicio');
    $fecha_fin = $request->input('fecha_fin');

    $query = DB::table('prestamo')
        ->leftJoin('usuario', 'prestamo.id_usuario', '=', 'usuario.id')
        ->leftJoin('estado_prestamo', 'prestamo.estado', '=', 'estado_prestamo.id')
        ->select(
            'prestamo.fecha_prestamo',
            'prestamo.fecha_devolucion',
            'estado_prestamo.nombre as estado',
            'usuario.name as trabajador'
        );

    if ($fecha_inicio) {
        $query->where('prestamo.fecha_prestamo', '>=', $fecha_inicio);
    }

    if ($fecha_fin) {
        $query->where('prestamo.fecha_prestamo', '<=', $fecha_fin);
    }

    $prestamos = $query->orderBy('prestamo.fecha_prestamo', 'desc')->get();

    foreach ($prestamos as $p) {
        $p->duracion = $p->fecha_devolucion
            ? Carbon::parse($p->fecha_prestamo)->diffInDays(Carbon::parse($p->fecha_devolucion))
            : '—';
    }

    return view('reportes.reportePrestamos', compact('prestamos'));
}




}
