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
                        <label for="name">Nombre</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password">Contraseña</label>
                        <input type="password" name="password">
                        <input type="password" name="password_confirmation">

                    </div>

                    <div class="mb-3">
                        <label for="id_rol">Rol</label>
                        <select name="id_rol" class="form-select" required>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre_rol }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="id_estado">Estado</label>
                        <select name="id_estado" class="form-select" required>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>


                    <button type="submit" class="btn btn-success">Crear</button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Volver atrás</a>

                </form>
            </div>
        </div>
    </div>
</section>
@endsection
