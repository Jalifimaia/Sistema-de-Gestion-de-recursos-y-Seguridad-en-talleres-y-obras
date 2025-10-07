@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
  <h2 class="mb-4">Iniciar sesión</h2>

  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
      <label for="email">Email</label>
      <input type="email" name="email" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
      <label for="password">Contraseña</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Ingresar</button>
  </form>
@endsection
