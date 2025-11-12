@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
<div class="container py-5">
  <div class="login-card">
    <div class="login-logo">
      <img src="{{ asset('images/SafeStock.png') }}" alt="SafeStock">
      <h1>SafeStock</h1>
    </div>

    <h5 class="text-center mb-4">Iniciar sesión</h5>

    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>

      </div>

      <div class="mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <input id="password" type="password"
            name="password"
            class="form-control @error('password') is-invalid @enderror"
            required
            aria-describedby="passwordHelp"
            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">

      {{-- Mensaje de error específico para password --}}
      @error('password')
        <div id="passwordHelp" class="invalid-feedback d-block">
          {{ $message }}
        </div>
      @enderror
    </div>


      <div class="d-grid mb-3">
        <button type="submit" class="btn btn-orange">Ingresar</button>
      </div>
    </form>

    {{-- Botón para ir directo a la terminal --}}
    <div class="d-grid mt-3">
      <a href="{{ url('/terminal') }}" class="btn btn-secondary">Ir a la Terminal</a>
    </div>
  </div>
</div>
@endsection
