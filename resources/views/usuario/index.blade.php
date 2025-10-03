@extends('layouts.app')

@section('title', 'Gestión de Usuarios y Roles')

@section('content')
  <!-- Título -->
  <div class="mb-4">
    <h2>Gestión de Usuarios y Roles</h2>
    <p class="text-muted">Administración de usuarios, permisos y roles del sistema</p>
  </div>

  <!-- Resumen -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h6 class="card-title">Nuevo Usuario</h6>
          <p class="card-text">Total Usuarios</p>
          <h3>5</h3>
          <small class="text-success">+1 este mes</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h6 class="card-title">Usuarios Activos</h6>
          <h3>4</h3>
          <small class="text-muted">80% del total</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h6 class="card-title">Administradores</h6>
          <h3>1</h3>
          <small class="text-muted">Acceso completo</small>
        </div>
      </div>
    </div>

      @if ($ultimoUsuarioActivo)
        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-body">
              <h6 class="card-title">Último acceso</h6>
              <p class="card-text">{{ $ultimoUsuarioActivo->name }}</p>
              <small class="text-muted">
                {{ \Carbon\Carbon::parse($ultimoUsuarioActivo->ultimo_acceso)->diffForHumans() }}
              </small>
            </div>
          </div>
        </div>
      @endif


  </div>

  @auth
    @if (Auth::user()->rol->nombre_rol === 'Administrador')
      <a href="{{ route('usuarios.create') }}" class="btn btn-success mb-3">
        + Crear nuevo usuario
      </a>
    @endif
  @endauth

  <!-- Componente Livewire -->
  @livewire('user-tabs')
@endsection

