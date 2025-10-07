@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Usuario
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Update') }} Usuario</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $usuario->name) }}">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $usuario->email) }}">
                        </div>

                        <div class="mb-3">
                            <label for="id_rol" class="form-label">Rol</label>
                            <select name="id_rol" class="form-select">
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}" {{ $usuario->id_rol == $rol->id ? 'selected' : '' }}>
                                {{ $rol->nombre_rol }}
                                </option>
                            @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
