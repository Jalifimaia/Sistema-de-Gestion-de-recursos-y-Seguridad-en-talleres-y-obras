@extends('layouts.app')

@section('title', 'Agregar Usuario')

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
        Agregar Usuario
      </h4>
    </div>
  </div>

  <!-- Formulario -->
  <form id="crearUsuarioForm" method="POST" action="{{ route('usuarios.store') }}" novalidate>
    @csrf

    <div class="mb-3">
      <label for="name" class="form-label">Nombre</label>
      <input type="text" name="name" id="name" class="form-control"
       value="{{ old('name') }}"
       placeholder="Ingrese su nombre"
       maxlength="255">
       <div class="invalid-feedback d-block" id="error-name"></div>
    </div>

    <div class="mb-3">
      <label for="dni" class="form-label">DNI</label>
      <input type="number"
            name="dni"
            id="dni"
            class="form-control"
            value="{{ old('dni') }}"
            placeholder="Ingrese su DNI"
            min="1"
            oninput="if(this.value.length>15) this.value=this.value.slice(0,15)">
            <div class="invalid-feedback d-block" id="error-dni"></div>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" name="email" id="email" class="form-control"
       value="{{ old('email') }}"
       placeholder="Ingrese su dirección de mail"
       maxlength="255">
       <div class="invalid-feedback d-block" id="error-email"></div>
    </div>

    <!-- Contraseña -->
    <div class="mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <div class="input-group">
        <input type="password"
              name="password"
              id="password"
              class="form-control"
              placeholder="Ingrese una contraseña"
              maxlength="255">
        <button type="button" class="btn btn-ojoa" id="togglePassword">
          <img src="{{ asset('images/ojocerrado.svg') }}"
              alt="Mostrar/Ocultar"
              id="iconPassword"
              style="width:20px; height:20px;">
        </button>
        <div class="invalid-feedback d-block" id="error-password"></div>
      </div>
    </div>

    <!-- Confirmar contraseña -->
    <div class="mb-3">
      <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
      <div class="input-group">
        <input type="password"
              name="password_confirmation"
              id="password_confirmation"
              class="form-control"
              placeholder="Confirme su contraseña"
              maxlength="255">
        <button type="button" class="btn btn-ojoa" id="togglePasswordConfirm">
          <img src="{{ asset('images/ojocerrado.svg') }}"
              alt="Mostrar/Ocultar"
              id="iconPasswordConfirm"
              style="width:20px; height:20px;">
        </button>
        <div class="invalid-feedback d-block" id="error-password-confirm"></div>
      </div>
    </div>



    <div class="mb-3">
      <label for="id_rol" class="form-label">Rol</label>
      <select name="id_rol" class="form-select">
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

<!-- Modal de error -->
<div class="modal fade" id="modalErrorCampos" tabindex="-1" aria-labelledby="modalErrorCamposLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalErrorCamposLabel">Error</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Hay campos obligatorios sin completar o con formato inválido.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal de éxito -->
<div class="modal fade" id="usuarioCreadoModal" tabindex="-1" aria-labelledby="usuarioCreadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="usuarioCreadoLabel">Usuario creado correctamente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <p>¿Desea agregar otro usuario o volver a la lista?</p>
      </div>
      <div class="modal-footer d-flex justify-content-center">
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
          <i class="bi bi-person-plus"></i> Agregar otro
        </a>
        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
          <i class="bi bi-list"></i> Volver a usuarios
        </a>
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<link href="{{ asset('css/crearUsuario.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  function toggleVisibility(inputId, buttonId, iconId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    const icon = document.getElementById(iconId);

    if (!input || !button || !icon) return;

    button.addEventListener('click', (e) => {
      e.preventDefault();
      if (input.type === 'password') {
        input.type = 'text';
        icon.src = "{{ asset('images/ojoabierto.svg') }}"; 
      } else {
        input.type = 'password';
        icon.src = "{{ asset('images/ojocerrado.svg') }}"; 
      }
    });
  }

  toggleVisibility('password', 'togglePassword', 'iconPassword');
  toggleVisibility('password_confirmation', 'togglePasswordConfirm', 'iconPasswordConfirm');

  const form = document.getElementById('crearUsuarioForm');

  const modalErrorEl = document.getElementById('modalErrorCampos');

  form.addEventListener('submit', function(e) {
    let hasErrors = false;

    // Resetear mensajes previos
    document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

    // Nombre
    const name = form.querySelector('[name="name"]');
    if (!name.value.trim()) {
      document.getElementById('error-name').textContent = 'El nombre es obligatorio';
      hasErrors = true;
    }

    // DNI
    const dni = form.querySelector('[name="dni"]');
    if (!dni.value.trim()) {
      document.getElementById('error-dni').textContent = 'El DNI es obligatorio';
      hasErrors = true;
    } else if (!/^\d+$/.test(dni.value)) {
      document.getElementById('error-dni').textContent = 'El DNI debe contener solo números';
      hasErrors = true;
    }

    // Email
    const email = form.querySelector('[name="email"]');
    if (!email.value.trim()) {
      document.getElementById('error-email').textContent = 'El email es obligatorio';
      hasErrors = true;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
      document.getElementById('error-email').textContent = 'El formato de email es inválido';
      hasErrors = true;
    }

    // Contraseña
    const password = form.querySelector('[name="password"]');
    const confirm = form.querySelector('[name="password_confirmation"]');
    if (!password.value.trim()) {
      document.getElementById('error-password').textContent = 'La contraseña es obligatoria';
      hasErrors = true;
    } else if (password.value !== confirm.value) {
      document.getElementById('error-password-confirm').textContent = 'Las contraseñas no coinciden';
      hasErrors = true;
    }

    if (hasErrors) {
      e.preventDefault(); // Cancela el envío si hay errores
    }
  });

  @if(session('usuario_creado'))
    const modal = new bootstrap.Modal(document.getElementById('usuarioCreadoModal'));
    modal.show();
  @endif
});
</script>

@endpush

