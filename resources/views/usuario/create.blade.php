@extends('layouts.app')

@section('template_title')
    Crear Usuario
@endsection

@section('content')
<section class="content container-fluid">
    <div class="col-md-8 offset-md-2">
        <div class="card card-default">
            <div class="card-header">
                <span class="card-title">Crear Usuario</span>
            </div>
            <div class="card-body bg-white">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('usuarios.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" value="{{ old('dni') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="id_rol" class="form-label">Rol</label>
                        <select name="id_rol" class="form-select" required>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre_rol }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Estado no se muestra en el formulario.
                         Se asigna automáticamente como "Stand by" en el controlador. --}}

                    <button type="submit" class="btn btn-success">Crear</button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Volver atrás</a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
