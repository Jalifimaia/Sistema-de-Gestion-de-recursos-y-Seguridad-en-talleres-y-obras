<?php

namespace App\Http\Controllers;
//dd(env('DB_DATABASE'));
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Http\Controllers\EstadoUsuarioController;
use App\Models\EstadoUsuario;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $usuarios = User::with('rol')->get();
    $roles = Rol::with('usuarios')->get();

    $ultimoUsuarioActivo = User::whereNotNull('ultimo_acceso')
        ->orderByDesc('ultimo_acceso')
        ->first();

    return view('usuario.index', compact('usuarios', 'roles', 'ultimoUsuarioActivo'));
}



    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
{
    $usuario = new User();
    $roles = Rol::all();
    $estados = EstadoUsuario::all(); // ← esta línea es clave

    return view('usuario.create', compact('usuario', 'roles', 'estados'));
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request): RedirectResponse
{
    $data = $request->validated();

    if (!empty($data['password'])) {
        $data['password'] = bcrypt($data['password']);
    }

    $data['usuario_creacion'] = auth()->id();
    $data['usuario_modificacion'] = auth()->id();
    $data['ultimo_acceso'] = now();


    User::create($data);

    return Redirect::route('usuarios.index')->with('success', 'Usuario creado correctamente.');
}




    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $usuario = User::find($id);

        return view('usuario.show', compact('usuario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
public function edit($id): View
{
    $usuario = User::findOrFail($id);
    $roles = Rol::all();
    $estados = EstadoUsuario::all(); // ← esta línea es necesaria

    if (auth()->user()->rol->nombre_rol !== 'Administrador') {
        abort(403, 'No tenés permiso para editar usuarios');
    }

    return view('usuario.edit', compact('usuario', 'roles', 'estados'));
}





    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, $id): RedirectResponse
{
    $usuario = User::findOrFail($id);

    $data = $request->validated();

    // Hashear password si se envía
    if (!empty($data['password'])) {
        $data['password'] = bcrypt($data['password']);
    } else {
        unset($data['password']);
    }

    // Registrar quién lo modificó (solo si está autenticado)
    $data['usuario_modificacion'] = auth()->id();

    $usuario->update($data);

    return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente');
}



public function destroy($id): RedirectResponse
{
    $usuario = User::findOrFail($id);

    if (
        $usuario->recursosCreados()->exists() ||
        $usuario->recursosModificados()->exists() ||
        $usuario->incidentesCreados()->exists() ||
        $usuario->incidentesModificados()->exists()
    ) {
        return Redirect::route('usuarios.index')
            ->with('error', 'No se puede eliminar el usuario porque tiene recursos o incidentes asociados.');
    }

    $usuario->delete();

    return Redirect::route('usuarios.index')
        ->with('success', 'Usuario eliminado correctamente.');
}


public function darDeBaja($id): RedirectResponse
{
    $usuario = User::findOrFail($id);
    $estadoBaja = EstadoUsuario::where('nombre', 'Baja')->first();

    if (!$estadoBaja) {
        return redirect()->back()->with('error', 'No se encontró el estado "Baja".');
    }

    $usuario->id_estado = $estadoBaja->id;
    $usuario->usuario_modificacion = auth()->id();
    $usuario->save();

    return redirect()->route('usuarios.edit', $usuario->id)->with('success', 'Usuario dado de baja correctamente.');
}


public function darDeAlta($id): RedirectResponse
{
    $usuario = User::findOrFail($id);
    $estadoAlta = EstadoUsuario::where('nombre', 'Alta')->first();

    if (!$estadoAlta) {
        return redirect()->back()->with('error', 'No se encontró el estado "Alta".');
    }

    $usuario->id_estado = $estadoAlta->id;
    $usuario->usuario_modificacion = auth()->id();
    $usuario->save();

    return redirect()->route('usuarios.edit', $usuario->id)->with('success', 'Usuario dado de alta correctamente.');
}


}