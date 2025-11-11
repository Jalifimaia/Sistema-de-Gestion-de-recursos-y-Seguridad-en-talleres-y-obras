@extends('layouts.app')

@section('template_title')
    Crear Usuario
@endsection

@section('content')
<div class="container py-4">

  <!-- Encabezado -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <a href="{{ url()->previous() }}" class="btn btn-volver d-inline-flex align-items-center">
        <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
        Volver
      </a>

      <h4 class="fw-bold text-orange mb-0 d-flex align-items-center">
        <img src="{{ asset('images/userNuevo.svg') }}" alt="Usuario" class="me-2 icono-volver">
        Crear Usuario
      </h4>
    </div>
  </div>

  <!-- Errores -->
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Formulario -->
  <form method="POST" action="{{ route('usuarios.store') }}">
    @csrf

    <div class="mb-3">
      <label for="name" class="form-label">Nombre</label>
      <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Ingrese su nombre">
    </div>

    <div class="mb-3">
      <label for="dni" class="form-label">DNI</label>
      <input type="text" name="dni" class="form-control" value="{{ old('dni') }}" required placeholder="Ingrese su DNI">
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="Ingrese su dirección de mail">
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <input type="password" name="password" id="password" class="form-control" placeholder="Ingrese una contraseña">
    </div>

    <div class="mb-3">
      <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
      <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirme su contraseña">
    </div>

    <div class="mb-3">
      <label for="id_rol" class="form-label">Rol</label>
      <select name="id_rol" class="form-select" required>
        @foreach ($roles as $rol)
          <option value="{{ $rol->id }}">{{ $rol->nombre_rol }}</option>
        @endforeach
      </select>
    </div>

    {{-- Estado no se muestra en el formulario. Se asigna automáticamente como "Stand by" en el controlador. --}}

    <!-- Botón largo centrado -->
    <div class="text-center mt-4">
      <button type="submit" class="btn btn-guardar w-75">Crear usuario</button>
    </div>
  </form>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/crearUsuario.css') }}" rel="stylesheet">
@endpush
