<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\EstadoUsuario;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index(): \Illuminate\View\View
{
    $usuarios = \App\Models\User::with(['rol', 'estado'])->get();
    $roles = \App\Models\Rol::all();
    $estados = \App\Models\EstadoUsuario::all();

    $ultimoUsuarioActivo = \App\Models\User::whereNotNull('ultimo_acceso')
        ->orderByDesc('ultimo_acceso')
        ->first();

    return view('usuario.index', compact('usuarios', 'roles', 'estados', 'ultimoUsuarioActivo'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $usuario = new User();
        $roles = Rol::all();
        $estados = EstadoUsuario::all();

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

    $estadoStandBy = EstadoUsuario::where('nombre', 'stand by')->first();
    if ($estadoStandBy) {
        $data['id_estado'] = $estadoStandBy->id;
    }

    $data['codigo_qr'] = 'USR-' . Str::uuid(); // ✅ QR único

    User::create($data);

    return Redirect::back()->with('usuario_creado', true);


}



    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $usuario = User::with(['rol', 'creador', 'modificador', 'estado'])->findOrFail($id);

        return view('usuario.show', compact('usuario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $usuario = User::with(['estado', 'usuarioRecursos.serieRecurso', 'usuarioRecursos.recurso', 'usuarioRecursos.serie.codigo'])->findOrFail($id);

        $roles = Rol::all();
        $estados = EstadoUsuario::all();

        if (auth()->user()->rol->nombre_rol !== 'Administrador') {
            abort(403, 'No tenés permiso para editar usuarios');
        }

        return view('usuario.edit', compact('usuario', 'roles', 'estados'));
    }

    public function standby($id)
{
    $usuario = User::findOrFail($id);
    $estadoStandBy = EstadoUsuario::where('nombre', 'stand by')->first();

    if (!$estadoStandBy) {
        return back()->with('error', 'No se encontró el estado "stand by".');
    }

    $usuario->id_estado = $estadoStandBy->id;
    $usuario->save();

    return back()->with('success', 'Usuario puesto en estado "stand by".');
}

    /**
     * Update the specified resource in storage.
     */
    
public function update(UserRequest $request, $id): RedirectResponse
{
    try {
        // Buscar usuario
        $usuario = User::findOrFail($id);

        // Validar datos
        $data = $request->validated();

        // Manejo de contraseña:
        // - Si password_confirmation está vacío → ignorar cualquier valor en password
        // - Si ambos vienen llenos → actualizar con bcrypt
        // - Si ambos vacíos → no tocar la contraseña
        if (empty($data['password_confirmation'])) {
            unset($data['password'], $data['password_confirmation']);
        } elseif (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password'], $data['password_confirmation']);
        }

        // Registrar quién modificó
        $data['usuario_modificacion'] = auth()->id();

        // Actualizar usuario
        $usuario->update($data);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');

    } catch (\Exception $e) {
        \Log::error('Error al actualizar usuario: ' . $e->getMessage());

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Error al actualizar: ' . $e->getMessage());
    }
}


    /**
     * Remove the specified resource from storage.
     */
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

    /**
     * Cambiar estado a Baja.
     */
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

    /**
     * Cambiar estado a Alta.
     */
    public function porEstado($estado)
{
    // Mapear el valor recibido desde el frontend al nombre exacto en la base
    $estadoNombre = match($estado) {
        'alta' => 'Alta',
        'standby' => 'stand by',
        default => null,
    };

    if (! $estadoNombre) {
        return response()->json([], 404);
    }

    // Buscar el modelo de estado por nombre exacto
    $estadoModelo = EstadoUsuario::where('nombre', $estadoNombre)->first();

    if (! $estadoModelo) {
        return response()->json([], 404);
    }

    // Filtrar solo trabajadores con ese estado
    $usuarios = User::where('id_estado', $estadoModelo->id)
        ->where('id_rol', 3) // Solo trabajadores
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    return response()->json($usuarios);
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
