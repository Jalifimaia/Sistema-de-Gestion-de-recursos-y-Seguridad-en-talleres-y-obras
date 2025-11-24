@extends('layouts.app')

@section('title', 'Editar Usuario')

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
        <img src="{{ asset('images/userNuevo.svg') }}" alt="Usuario" class="me-2 icono-volver">
        Editar Usuario
      </h4>
    </div>
  </div>

  <!-- Formulario -->
  <form id="formEditarUsuario" method="POST" action="{{ route('usuarios.update', $usuario->id) }}" novalidate>
    @csrf
    @method('PUT')

    <!-- Primera fila: Nombre y DNI -->
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" name="name" id="name" class="form-control"
          value="{{ old('name', $usuario->name) }}"
          placeholder="Ingrese su nombre"
          maxlength="255">
        <div class="invalid-feedback d-block" id="error-name"></div>
      </div>

      <div class="col-md-6 mb-3">
        <label for="dni" class="form-label">DNI</label>
        <input type="number" name="dni" id="dni" class="form-control"
          value="{{ old('dni', $usuario->dni) }}"
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
          value="{{ old('email', $usuario->email) }}"
          placeholder="Ingrese su dirección de mail"
          maxlength="255">
        <div class="invalid-feedback d-block" id="error-email"></div>
      </div>

      <div class="col-md-6 mb-3">
        <label for="id_rol" class="form-label">Rol</label>
        <select name="id_rol" id="id_rol" class="form-select">
          @foreach ($roles as $rol)
            <option value="{{ $rol->id }}" {{ $usuario->id_rol == $rol->id ? 'selected' : '' }}>
              {{ $rol->nombre_rol }}
            </option>
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
            placeholder="Ingrese la nueva contraseña"
            maxlength="255">
          <button type="button" class="btn btn-ojoa" id="togglePassword">
            <img src="{{ asset('images/ojocerrado.svg') }}" alt="Mostrar/Ocultar"
              id="iconPassword" style="width:20px; height:20px;">
          </button>
        </div>
        <small class="form-text text-muted">Dejá este campo vacío si no querés cambiar la contraseña.</small>
        <div class="invalid-feedback d-block" id="error-password"></div>
      </div>

      <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
        <div class="input-group">
          <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
            placeholder="Repita la contraseña" maxlength="255">
          <button type="button" class="btn btn-ojoa" id="togglePasswordConfirm">
            <img src="{{ asset('images/ojocerrado.svg') }}" alt="Mostrar/Ocultar"
              id="iconPasswordConfirm" style="width:20px; height:20px;">
          </button>
        </div>
        <div class="invalid-feedback d-block" id="error-password-confirm"></div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label no-asterisk">Estado actual</label>
      <div>
        @if ($usuario->estado?->nombre === 'Alta')
          <span class="badge badge-estado bg-success text-white">Activo (Alta)</span>
        @elseif ($usuario->estado?->nombre === 'Baja')
          <span class="badge badge-estado bg-danger">Inactivo (Baja)</span>
        @elseif ($usuario->estado?->nombre === 'stand by')
          <span class="badge badge-estado bg-secondary text-white">Stand by</span>
        @else
          <span class="badge badge-estado bg-secondary">Sin estado</span>
        @endif
      </div>
    </div>

    <!-- Botón largo centrado -->
    <div class="text-center mt-4">
      <button type="button" class="btn btn-guardar w-75" id="btnAbrirModalGuardar">Guardar cambios</button>
    </div>
  </form>

  <!-- Acciones de estado -->
<!-- Acciones de estado -->
<div class="d-flex justify-content-center gap-2 mt-4">
  @php $estado = $usuario->estado?->nombre; @endphp

  <form method="POST" action="{{ route('usuarios.activarConEPP', $usuario->id) }}"
        class="form-estado"
        data-nombre="{{ $usuario->name }}"
        data-rol="{{ $usuario->rol->nombre_rol }}"
        data-accion="alta"
        data-estado="{{ $estado }}">
    @csrf
    <button type="button"
      class="btn btn-success btn-confirmar-estado {{ $estado === 'Alta' ? 'opacity-50' : '' }}"
      {{ $estado === 'Alta' ? 'disabled' : '' }}
      title="{{ $estado === 'Baja' ? 'Usuario en Baja: primero pasar a Stand by para asignar EPP' : ($estado === 'Alta' ? 'Ya está activo' : 'Cambiar a estado Alta') }}">
      Dar de alta
    </button>
  </form>

  <form method="POST" action="{{ route('usuarios.baja', $usuario->id) }}"
        class="form-estado"
        data-nombre="{{ $usuario->name }}"
        data-rol="{{ $usuario->rol->nombre_rol }}"
        data-accion="baja"
        data-estado="{{ $estado }}">
    @csrf
    <button type="button"
      class="btn btn-danger btn-confirmar-estado {{ $estado === 'Baja' ? 'opacity-50' : '' }}"
      {{ $estado === 'Baja' ? 'disabled' : '' }}
      title="{{ $estado === 'Baja' ? 'Ya está dado de baja' : 'Cambiar a estado Baja' }}">
      Dar de baja
    </button>
  </form>

  <form method="POST" action="{{ route('usuarios.standby', $usuario->id) }}"
        class="form-estado"
        data-nombre="{{ $usuario->name }}"
        data-rol="{{ $usuario->rol->nombre_rol }}"
        data-accion="stand by"
        data-estado="{{ $estado }}">
    @csrf
    <button type="button"
      class="btn btn-warning btn-confirmar-estado {{ strtolower($estado) === 'stand by' ? 'opacity-50' : '' }}"
      {{ strtolower($estado) === 'stand by' ? 'disabled' : '' }}
      title="{{ strtolower($estado) === 'stand by' ? 'Ya está en stand by' : 'Cambiar a estado Stand by' }}">
      Poner en <em>stand by</em>
    </button>
  </form>
</div>

</div>


<!-- Modal de confirmación de estado -->
<div class="modal fade" id="modalConfirmarEstado" tabindex="-1" aria-labelledby="modalConfirmarEstadoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalConfirmarEstadoLabel">Confirmar acción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p id="textoConfirmacionEstado">¿Desea continuar?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <button type="button" class="btn btn-primary" id="btnConfirmarEstado">Sí</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de confirmación de guardar -->
<div class="modal fade" id="modalConfirmarGuardar" tabindex="-1" aria-labelledby="modalConfirmarGuardarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalConfirmarGuardarLabel">Confirmar cambios</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p>¿Desea guardar los cambios realizados en este usuario?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <button type="button" class="btn btn-primary" id="btnConfirmarGuardar">Sí</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de error de validación -->
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
        <h5 class="modal-title" id="modalErroresServidorLabel">Error al editar usuario</h5>
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
<div class="modal fade" id="modalExitoUsuario" tabindex="-1" aria-labelledby="modalExitoUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalExitoUsuarioLabel">Éxito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Usuario editado correctamente.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  
  // 👁️ Toggle de visibilidad de contraseñas
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

  // 🧹 Función para limpiar errores de un campo específico
  function limpiarError(inputElement, errorId) {
    if (!inputElement) return;
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

  // 📧 Validación en tiempo real del email
  const emailInput = document.getElementById('email');
  
  if (emailInput) {
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
  }

  // ✅ Validación del formulario
  const formEditarUsuario = document.getElementById('formEditarUsuario');
  const btnAbrirModalGuardar = document.getElementById('btnAbrirModalGuardar');
  const btnConfirmarGuardar = document.getElementById('btnConfirmarGuardar');

  function validarFormulario() {
    let hasErrors = false;

    // Resetear mensajes previos y clases de error
    document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    document.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));

    // Nombre
    const name = formEditarUsuario.querySelector('[name="name"]');
    if (!name.value.trim()) {
      document.getElementById('error-name').textContent = 'El nombre es obligatorio';
      name.classList.add('is-invalid');
      hasErrors = true;
    }

    // DNI
    const dni = formEditarUsuario.querySelector('[name="dni"]');
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
    const email = formEditarUsuario.querySelector('[name="email"]');
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

    // Contraseña - Solo validar si se está cambiando
    const password = formEditarUsuario.querySelector('[name="password"]');
    const confirm = formEditarUsuario.querySelector('[name="password_confirmation"]');
    
    if (password.value.trim()) {
      // Solo validar si hay algo escrito
      if (password.value.length < 6) {
        document.getElementById('error-password').textContent = 'La contraseña debe tener al menos 6 caracteres';
        password.classList.add('is-invalid');
        hasErrors = true;
      }
      
      if (password.value !== confirm.value) {
        document.getElementById('error-password-confirm').textContent = 'Las contraseñas no coinciden';
        confirm.classList.add('is-invalid');
        hasErrors = true;
      }
    } else if (confirm.value.trim()) {
      // Si confirmación tiene algo pero password no
      document.getElementById('error-password').textContent = 'Debe ingresar la contraseña';
      password.classList.add('is-invalid');
      hasErrors = true;
    }

    return !hasErrors;
  }

  // Abrir modal de confirmación
  if (btnAbrirModalGuardar && formEditarUsuario) {
    btnAbrirModalGuardar.addEventListener('click', function () {
      if (validarFormulario()) {
        const modalGuardar = new bootstrap.Modal(document.getElementById('modalConfirmarGuardar'));
        modalGuardar.show();
      } else {
        const modalError = new bootstrap.Modal(document.getElementById('modalErrorCampos'));
        modalError.show();
      }
    });
  }

  // Confirmar guardar cambios
  if (btnConfirmarGuardar) {
    btnConfirmarGuardar.addEventListener('click', function () {
      if (validarFormulario()) {
        formEditarUsuario.submit();
      } else {
        // Cerrar modal de confirmación
        const modalConfirmarGuardarEl = document.getElementById('modalConfirmarGuardar');
        const modalConfirmarGuardar = bootstrap.Modal.getInstance(modalConfirmarGuardarEl);
        if (modalConfirmarGuardar) {
          modalConfirmarGuardar.hide();
        }
        
        // Mostrar modal de error
        const modalError = new bootstrap.Modal(document.getElementById('modalErrorCampos'));
        modalError.show();
      }
    });
  }

  // 🟢 Modal de confirmación de estado (alta/baja/stand by)
  let formEstadoSeleccionado = null;

  function getAttrSafe(el, name, fallback = '') {
    try {
      return el ? el.getAttribute(name) ?? fallback : fallback;
    } catch {
      return fallback;
    }
  }

  const botonesEstado = document.querySelectorAll('.btn-confirmar-estado');
  if (botonesEstado.length) {
botonesEstado.forEach(boton => {
  boton.addEventListener('click', function () {
    formEstadoSeleccionado = this.closest('form');

    const nombre = getAttrSafe(formEstadoSeleccionado, 'data-nombre', 'Usuario');
    const rol = getAttrSafe(formEstadoSeleccionado, 'data-rol', '');
    const accion = getAttrSafe(formEstadoSeleccionado, 'data-accion', 'cambiar');
    const estadoActual = getAttrSafe(formEstadoSeleccionado, 'data-estado', '');

    // 👉 Siempre mensaje genérico, sin validación especial
    let mensaje = `¿Desea dar de ${accion} a ${nombre}${rol ? ' (' + rol + ')' : ''}?`;

    const texto = document.getElementById('textoConfirmacionEstado');
    if (texto) texto.textContent = mensaje;

    const modalEl = document.getElementById('modalConfirmarEstado');
    if (modalEl) new bootstrap.Modal(modalEl).show();
  });
});

  }

  // Confirmación del modal de estado (submit)
  const btnConfirmarEstado = document.getElementById('btnConfirmarEstado');
  if (btnConfirmarEstado) {
    btnConfirmarEstado.addEventListener('click', function () {
      if (formEstadoSeleccionado) {
        const blockIfMissing = getAttrSafe(formEstadoSeleccionado, 'data-disable-if-missing-epp', 'false') === 'true';
        if (blockIfMissing) {
          const faltantes = getAttrSafe(formEstadoSeleccionado, 'data-faltantes', '');
          if (faltantes) {
            alert('No se puede realizar la acción. Faltan: ' + faltantes);
            return;
          }
        }

        formEstadoSeleccionado.submit();
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEstado'));
        if (modal) modal.hide();
      }
    });
  }

  // ✅ Modal automático para mensajes del sistema
  @if(session('success'))
    (function () {
      const modalEl = document.getElementById('modalExitoUsuario');
      if (!modalEl) return;
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
      setTimeout(() => modal.hide(), 4000);
    })();
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

@push('styles')
<link href="{{ asset('css/editarUsuario.css') }}" rel="stylesheet">


<style>
  label::after {
  content: " *";
  color: red;
}

label.no-asterisk::after {
  content: ""; /* anula el asterisco */
}
</style>
@endpush