@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Usuario
@endsection

@section('content')
<section class="content container-fluid">
    <div class="">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <span class="card-title">Editar Usuario</span>
                </div>

                <div class="card-body bg-white">

                    {{-- Formulario de actualización --}}
                    <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" 
                                   value="{{ old('name', $usuario->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" name="dni" class="form-control" 
                                   value="{{ old('dni', $usuario->dni) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="{{ old('email', $usuario->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" 
                                   class="form-control">
                            <small class="form-text text-muted">
                                Dejá este campo vacío si no querés cambiar la contraseña.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="form-control" >
                        </div>

                        <div class="mb-3">
                            <label for="id_rol" class="form-label">Rol</label>
                            <select name="id_rol" class="form-select" required>
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}" 
                                        {{ $usuario->id_rol == $rol->id ? 'selected' : '' }}>
                                        {{ $rol->nombre_rol }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Estado actual (solo lectura, último campo) --}}
                        <div class="mb-3">
                            <label class="form-label">Estado actual</label>
                            <div>
                                @if ($usuario->estado?->nombre === 'Alta')
                                    <span class="badge bg-success">Activo (Alta)</span>
                                @elseif ($usuario->estado?->nombre === 'Baja')
                                    <span class="badge bg-danger">Inactivo (Baja)</span>
                                @elseif ($usuario->estado?->nombre === 'stand by')
                                    <span class="badge bg-warning text-dark">Stand by</span>
                                @else
                                    <span class="badge bg-secondary">Sin estado</span>
                                @endif
                            </div>
                        </div>
                        

                        
                        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                            ⬅️ Volver
                        </a>
                        
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </form>

                    {{-- Bloque de acciones de estado --}}
                    <div class="d-flex gap-2 mt-4">

                        {{-- Dar de Alta --}}
                        <form method="POST" action="{{ route('usuarios.alta', $usuario->id) }}">
                            @csrf
                            <button type="submit"
                                    class="btn btn-success {{ $usuario->estado->nombre === 'Alta' ? 'opacity-50' : '' }}"
                                    {{ $usuario->estado->nombre === 'Alta' ? 'disabled' : '' }}
                                    title="{{ $usuario->estado->nombre === 'Alta' ? 'Ya está activo' : 'Cambiar a estado Alta' }}">
                                Dar de alta
                            </button>
                        </form>

                        {{-- Dar de Baja --}}
                        <form method="POST" action="{{ route('usuarios.baja', $usuario->id) }}">
                            @csrf
                            <button type="submit"
                                    class="btn btn-danger {{ $usuario->estado->nombre === 'Baja' ? 'opacity-50' : '' }}"
                                    {{ $usuario->estado->nombre === 'Baja' ? 'disabled' : '' }}
                                    title="{{ $usuario->estado->nombre === 'Baja' ? 'Ya está dado de baja' : 'Cambiar a estado Baja' }}">
                                Dar de baja
                            </button>
                        </form>

                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
