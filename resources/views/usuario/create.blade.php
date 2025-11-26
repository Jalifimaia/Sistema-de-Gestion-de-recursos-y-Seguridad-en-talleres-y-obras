@extends('layouts.app')

@section('title', 'Agregar Usuario')

@section('content')
<div class="container py-4">

  <!-- Encabezado -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <a href="{{ route('usuarios.index') }}" class="btn btn-volver d-inline-flex align-items-center">
        <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
        Volver
      </a>

      <h4 class="fw-bold text-orange mb-0 d-flex align-items-center">
        Agregar Usuario
      </h4>
    </div>
  </div>

<!-- Formulario -->
<form id="crearUsuarioForm" method="POST" action="{{ route('usuarios.store') }}" novalidate>
  @csrf

  <!-- Primera fila: Nombre y DNI -->
  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="name" class="form-label">Nombre</label>
      <input type="text" name="name" id="name" class="form-control"
        value="{{ old('name') }}"
        placeholder="Ingrese su nombre"
        maxlength="255">
      <div class="invalid-feedback d-block" id="error-name"></div>
    </div>

    <div class="col-md-6 mb-3">
      <label for="dni" class="form-label">DNI</label>
      <input type="number" name="dni" id="dni" class="form-control"
        value="{{ old('dni') }}"
        placeholder="Ingrese su DNI"
        min="1"
        oninput="if(this.value.length>15) this.value=this.value.slice(0,15)">
      <div class="invalid-feedback d-block" id="error-dni"></div>
    </div>
  </div>

  <!-- Segunda fila: Email y Rol -->
  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="text" name="email" id="email" class="form-control"
        value="{{ old('email') }}"
        placeholder="Ingrese su dirección de mail"
        maxlength="255">
      <div class="invalid-feedback d-block" id="error-email"></div>
    </div>

    <div class="col-md-6 mb-3">
      <label for="id_rol" class="form-label">Rol</label>
      <select name="id_rol" class="form-select">
        @foreach ($roles as $rol)
          <option value="{{ $rol->id }}">{{ $rol->nombre_rol }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <!-- Tercera fila: Contraseña y Confirmar contraseña -->
  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <div class="input-group">
        <input type="password" name="password" id="password" class="form-control"
          placeholder="Ingrese una contraseña (mínimo 6 caracteres)"
          maxlength="255">
        <button type="button" class="btn btn-ojoa" id="togglePassword">
          <img src="{{ asset('images/ojocerrado.svg') }}" alt="Mostrar/Ocultar"
            id="iconPassword" style="width:20px; height:20px;">
        </button>
      </div>
      <div class="invalid-feedback d-block" id="error-password"></div>
    </div>

    <div class="col-md-6 mb-3">
      <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
      <div class="input-group">
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
          placeholder="Confirme su contraseña" maxlength="255">
        <button type="button" class="btn btn-ojoa" id="togglePasswordConfirm">
          <img src="{{ asset('images/ojocerrado.svg') }}" alt="Mostrar/Ocultar"
            id="iconPasswordConfirm" style="width:20px; height:20px;">
        </button>
      </div>
      <div class="invalid-feedback d-block" id="error-password-confirm"></div>
    </div>
  </div>

      <!-- Botón largo centrado -->
      <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-guardar px-4">
          Crear usuario
        </button>
      </div>
</form>

</div>

<!-- Modal de error de validación frontend -->
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
        <button type="button" class="btn btn-danger text-white" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de errores del servidor -->
<div class="modal fade" id="modalErroresServidor" tabindex="-1" aria-labelledby="modalErroresServidorLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalErroresServidorLabel">Error al crear usuario</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <strong>Se encontraron los siguientes errores:</strong>
        <ul class="mb-0 mt-2">
          @if ($errors->any())
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          @endif
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger text-white" data-bs-dismiss="modal">Cerrar</button>
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
      <div class="modal-body justify-content-start">
        <p>¿Desea agregar otro usuario o volver a la lista?</p>
      </div>
      <div class="modal-footer d-flex justify-content-end">
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-success">
          Volver a usuarios
        </a>
        <a href="{{ route('usuarios.create') }}" class="btn btn-success">
          Agregar otro usuario
        </a>
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<link href="{{ asset('css/crearUsuario.css') }}" rel="stylesheet">

<style>
  label::after {
  content: " *";
  color: red;
}
</style>
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

  // Función para limpiar errores de un campo específico
  function limpiarError(inputElement, errorId) {
    inputElement.addEventListener('input', function() {
      this.classList.remove('is-invalid');
      const errorDiv = document.getElementById(errorId);
      if (errorDiv) {
        errorDiv.textContent = '';
      }
    });
  }

  // Aplicar limpieza de errores a todos los campos
  limpiarError(document.getElementById('name'), 'error-name');
  limpiarError(document.getElementById('dni'), 'error-dni');
  limpiarError(document.getElementById('email'), 'error-email');
  limpiarError(document.getElementById('password'), 'error-password');
  limpiarError(document.getElementById('password_confirmation'), 'error-password-confirm');

  // Validación en tiempo real del email
  const emailInput = document.getElementById('email');
  
  emailInput.addEventListener('input', function() {
    let value = this.value;
    
    // Eliminar espacios
    value = value.replace(/\s/g, '');
    
    // Permitir solo caracteres válidos para emails
    value = value.replace(/[^\w@.-]/g, '');
    
    // Solo permitir una @
    const atCount = (value.match(/@/g) || []).length;
    if (atCount > 1) {
      const firstAt = value.indexOf('@');
      value = value.substring(0, firstAt + 1) + value.substring(firstAt + 1).replace(/@/g, '');
    }
    
    // No permitir @ al inicio
    if (value.startsWith('@')) {
      value = value.substring(1);
    }
    
    // No permitir punto al inicio
    if (value.startsWith('.')) {
      value = value.substring(1);
    }
    
    // No permitir puntos consecutivos
    value = value.replace(/\.{2,}/g, '.');
    
    // Si hay @, procesar las partes
    if (value.includes('@')) {
      const parts = value.split('@');
      let localPart = parts[0];
      let domain = parts[1] || '';
      
      // Parte local: no puede terminar en punto
      if (localPart.endsWith('.')) {
        localPart = localPart.slice(0, -1);
      }
      
      // Parte local: no puede empezar con punto
      if (localPart.startsWith('.')) {
        localPart = localPart.substring(1);
      }
      
      // Dominio: no puede empezar con punto, guión o guión bajo
      domain = domain.replace(/^[.\-_]+/, '');
      
      // Si el dominio tiene punto, validar la extensión
      if (domain.includes('.')) {
        const domainParts = domain.split('.');
        
        // La última parte (extensión) solo puede tener letras
        const lastIndex = domainParts.length - 1;
        if (domainParts[lastIndex]) {
          domainParts[lastIndex] = domainParts[lastIndex].replace(/[^a-zA-Z]/g, '');
        }
        
        // Reconstruir sin partes vacías (excepto si es el último y está escribiendo)
        domain = domainParts
          .map((part, idx) => {
            // Permitir parte vacía en la última posición (está escribiendo)
            if (idx === lastIndex && part === '') return part;
            return part;
          })
          .join('.');
      }
      
      value = localPart + '@' + domain;
    } else {
      // Si no hay @, no permitir que termine en punto
      if (value.endsWith('.')) {
        value = value.slice(0, -1);
      }
    }
    
    this.value = value;
  });

  const form = document.getElementById('crearUsuarioForm');

  form.addEventListener('submit', function(e) {
    e.preventDefault(); // Prevenir envío por defecto
    let hasErrors = false;

    // Resetear mensajes previos y clases de error
    document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    document.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));

    // Nombre
    const name = form.querySelector('[name="name"]');
    if (!name.value.trim()) {
      document.getElementById('error-name').textContent = 'El nombre es obligatorio';
      name.classList.add('is-invalid');
      hasErrors = true;
    }

    // DNI
    const dni = form.querySelector('[name="dni"]');
    if (!dni.value.trim()) {
      document.getElementById('error-dni').textContent = 'El DNI es obligatorio';
      dni.classList.add('is-invalid');
      hasErrors = true;
    } else if (!/^\d+$/.test(dni.value)) {
      document.getElementById('error-dni').textContent = 'El DNI debe contener solo números';
      dni.classList.add('is-invalid');
      hasErrors = true;
    }

    // Email - Validación con regex específico
    const email = form.querySelector('[name="email"]');
    const emailRegex = /^((?!\.)[\w\-_.]*[^.])(@\w+)(\.\w+(\.\w+)?[^.\W])$/;
    
    if (!email.value.trim()) {
      document.getElementById('error-email').textContent = 'El email es obligatorio';
      email.classList.add('is-invalid');
      hasErrors = true;
    } else if (!emailRegex.test(email.value)) {
      document.getElementById('error-email').textContent = 'El formato de email es inválido';
      email.classList.add('is-invalid');
      hasErrors = true;
    }

    // Contraseña - Validación con mínimo 6 caracteres
    const password = form.querySelector('[name="password"]');
    const confirm = form.querySelector('[name="password_confirmation"]');
    
    if (!password.value.trim()) {
      document.getElementById('error-password').textContent = 'La contraseña es obligatoria';
      password.classList.add('is-invalid');
      hasErrors = true;
    } else if (password.value.length < 6) {
      document.getElementById('error-password').textContent = 'La contraseña debe tener al menos 6 caracteres';
      password.classList.add('is-invalid');
      hasErrors = true;
    }
    
    if (!confirm.value.trim()) {
      document.getElementById('error-password-confirm').textContent = 'La confirmación de contraseña es obligatoria';
      confirm.classList.add('is-invalid');
      hasErrors = true;
    } else if (password.value !== confirm.value) {
      document.getElementById('error-password-confirm').textContent = 'Las contraseñas no coinciden';
      confirm.classList.add('is-invalid');
      hasErrors = true;
    }

    if (hasErrors) {
      // Mostrar modal de error
      const modalError = new bootstrap.Modal(document.getElementById('modalErrorCampos'));
      modalError.show();
      return false;
    } else {
      // Si todo está bien, enviar el formulario
      form.submit();
    }
  });

  @if(session('usuario_creado'))
    const modal = new bootstrap.Modal(document.getElementById('usuarioCreadoModal'));
    modal.show();
  @endif

  @if($errors->any())
    // Traducir mensajes de error al español
    const traducciones = {
      'The email has already been taken.': 'El email ya está en uso.',
      'The dni has already been taken.': 'El DNI ya está en uso.',
      'The email must be a valid email address.': 'El email debe ser una dirección válida.',
      'The email field is required.': 'El campo email es obligatorio.',
      'The name field is required.': 'El campo nombre es obligatorio.',
      'The dni field is required.': 'El campo DNI es obligatorio.',
      'The password field is required.': 'El campo contraseña es obligatorio.',
      'The password must be at least 6 characters.': 'La contraseña debe tener al menos 6 caracteres.',
      'The password confirmation does not match.': 'La confirmación de contraseña no coincide.',
      'The dni must be a number.': 'El DNI debe ser un número.',
      'The dni must be at least 1.': 'El DNI debe ser al menos 1.',
    };

    // Mapeo de errores a campos
    const erroresACampos = {
      'The email has already been taken.': 'email',
      'The dni has already been taken.': 'dni',
      'The email must be a valid email address.': 'email',
      'The email field is required.': 'email',
      'The name field is required.': 'name',
      'The dni field is required.': 'dni',
      'The password field is required.': 'password',
      'The password must be at least 6 characters.': 'password',
      'The password confirmation does not match.': 'password_confirmation',
      'The dni must be a number.': 'dni',
      'The dni must be at least 1.': 'dni',
    };

    // Traducir todos los mensajes en el modal y marcar campos con error
    const modalBody = document.querySelector('#modalErroresServidor .modal-body ul');
    if (modalBody) {
      const items = modalBody.querySelectorAll('li');
      items.forEach(item => {
        const textoOriginal = item.textContent.trim();
        
        // Traducir el mensaje
        if (traducciones[textoOriginal]) {
          item.textContent = traducciones[textoOriginal];
        }

        // Marcar el campo correspondiente en rojo
        if (erroresACampos[textoOriginal]) {
          const nombreCampo = erroresACampos[textoOriginal];
          const inputCampo = document.getElementById(nombreCampo);
          const errorDiv = document.getElementById(`error-${nombreCampo}`);
          
          if (inputCampo) {
            inputCampo.classList.add('is-invalid');
          }
          
          if (errorDiv) {
            const mensajeTraducido = traducciones[textoOriginal] || textoOriginal;
            errorDiv.textContent = mensajeTraducido;
            errorDiv.style.display = 'block';
          }
        }
      });
    }

    const modalErroresServidor = new bootstrap.Modal(document.getElementById('modalErroresServidor'));
    modalErroresServidor.show();
  @endif
});
</script>

@endpush