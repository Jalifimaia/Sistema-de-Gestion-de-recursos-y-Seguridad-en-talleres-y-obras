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
        <div class="input-group">
          <input id="password" type="password"
                 name="password"
                 class="form-control @error('password') is-invalid @enderror"
                 required
                 aria-describedby="passwordHelp"
                 aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
          <button type="button" class="btn btn-ojoa" id="togglePassword">
            <img src="{{ asset('images/ojocerrado.svg') }}" alt="Mostrar/Ocultar" id="iconPassword" class="icono-btn">
          </button>
        </div>

        @error('password')
          <div id="passwordHelp" class="invalid-feedback d-block">
            {{ $message }}
          </div>
        @enderror
      </div>

      <div class="d-grid mb-3">
        <button type="submit" class="btn btn-orange btn-login">Ingresar</button>
      </div>
    </form>

    <div class="d-grid mt-3">
      <a href="{{ url('/terminal') }}" class="btn btn-secondary btn-login">Ir a la Terminal</a>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/login.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('password');
  const button = document.getElementById('togglePassword');
  const icon = document.getElementById('iconPassword');

  button.addEventListener('click', () => {
    if (input.type === 'password') {
      input.type = 'text';
      icon.src = "{{ asset('images/ojoabierto.svg') }}";
    } else {
      input.type = 'password';
      icon.src = "{{ asset('images/ojocerrado.svg') }}";
    }
  });
});
</script>
@endpush
