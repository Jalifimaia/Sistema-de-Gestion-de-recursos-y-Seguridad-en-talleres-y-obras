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

</div>
@endsection

