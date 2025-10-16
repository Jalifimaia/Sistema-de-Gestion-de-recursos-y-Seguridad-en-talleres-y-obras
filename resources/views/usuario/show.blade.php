@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('content')
<div class="container">
  <h2>{{ $usuario->name }}</h2>

  <p><strong>Email:</strong> {{ $usuario->email }}</p>
  <p><strong>Rol:</strong> {{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</p>
  <p><strong>Último acceso:</strong> 
    {{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->diffForHumans() : 'Nunca' }}
  </p>

  {{-- Estado actual --}}
  <p><strong>Estado:</strong>
    @if ($usuario->estado?->nombre === 'Alta')
      <span class="badge bg-success">Activo (Alta)</span>
    @elseif ($usuario->estado?->nombre === 'Baja')
      <span class="badge bg-danger">Inactivo (Baja)</span>
    @elseif ($usuario->estado?->nombre === 'stand by')
      <span class="badge bg-warning text-dark">Stand by</span>
    @else
      <span class="badge bg-secondary">Sin estado</span>
    @endif
  </p>

  <hr>

  <p><strong>Creado por:</strong> {{ $usuario->creador?->name ?? 'Desconocido' }}</p>
  <p><strong>Modificado por:</strong> {{ $usuario->modificador?->name ?? 'Desconocido' }}</p>
  <p><strong>Creado el:</strong> {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
  <p><strong>Modificado el:</strong> {{ $usuario->updated_at ? $usuario->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
</div>
@endsection
