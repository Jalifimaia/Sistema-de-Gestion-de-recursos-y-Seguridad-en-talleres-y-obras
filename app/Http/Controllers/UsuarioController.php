<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\EstadoUsuario;
use App\Models\Checklist;
use App\Models\Incidente;
use App\Models\Prestamo;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::with(['rol', 'estado']);

        // Si no hay filtros aplicados, mostrar todos
        if ($request->filled('rol')) {
            $query->whereHas('rol', fn($q) => $q->where('nombre_rol', $request->rol));
        }

        if ($request->filled('estado')) {
            $query->whereHas('estado', fn($q) => $q->where('nombre', $request->estado));
        }

        $usuarios = $query->get();

        $ultimoUsuarioActivo = Usuario::whereNotNull('ultimo_acceso')
            ->orderByDesc('ultimo_acceso')
            ->first();

        $roles = Rol::all();
        $estados = EstadoUsuario::all();

        return view('usuario.index', compact('usuarios', 'ultimoUsuarioActivo', 'roles', 'estados'));
    }



public function show($id)
{
    // Cargar usuario con relaciones ligeras y counts
    $usuario = Usuario::with(['rol', 'estado'])
        ->withCount(['checklists', 'incidentes', 'prestamos'])
        ->findOrFail($id);

    return view('usuario.show', compact('usuario'));
}

/** Pestaña: checklists (paginado) */
public function checklists($id)
{
    $items = Checklist::where('trabajador_id', $id)
        ->with('trabajador') // si necesitás datos del trabajador
        ->orderByDesc('fecha')
        ->paginate(10);

    return view('usuario.partials.checklists', compact('items'));
}

/** Pestaña: incidentes (paginado) */
public function incidentes($id)
{
    $items = Incidente::where('id_trabajador', $id)
        ->with(['recurso', 'estadoIncidente'])
        ->orderByDesc('fecha_incidente')
        ->paginate(10);

    return view('usuario.partials.incidentes', compact('items'));
}

/** Pestaña: prestamos (paginado) */
public function prestamos($id)
{
    $items = Prestamo::where('id_usuario', $id)
        ->with(['detallePrestamos.serieRecurso.recurso', 'detallePrestamos.estadoPrestamo'])
        ->orderByDesc('fecha_prestamo')
        ->paginate(10);

    return view('usuario.partials.prestamos', compact('items'));
}



    public function edit($id)
    {
        $usuario = Usuario::with(['rol', 'estado', 'usuarioRecursos'])->findOrFail($id);
        $roles = Rol::all();
        $estados = EstadoUsuario::all();

        return view('usuario.edit', compact('usuario', 'roles', 'estados'));
    }   
    

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $usuario->name = $request->input('name');
        $usuario->dni = $request->input('dni');
        $usuario->email = $request->input('email');

        if ($request->filled('password')) {
            $usuario->password = bcrypt($request->input('password'));
        }

        $usuario->id_rol = $request->input('id_rol');
        $usuario->save();

        return redirect()->route('usuarios.show', $usuario->id)
                        ->with('success', 'Usuario actualizado correctamente.');
    }    


    public function create()
    {
        // Traer roles y estados para el formulario
        $roles = Rol::all();
        $estados = EstadoUsuario::all();

        return view('usuario.create', compact('roles', 'estados'));
    }    
}