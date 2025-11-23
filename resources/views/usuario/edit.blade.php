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
  <form id="formEditarUsuario" method="POST" action="{{ route('usuarios.update', $usuario->id) }} novalidate">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label for="name" class="form-label">Nombre</label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $usuario->name) }}" required placeholder="Ingrese un nombre">
    </div>

    <div class="mb-3">
      <label for="dni" class="form-label">DNI</label>
      <input type="text" name="dni" class="form-control" value="{{ old('dni', $usuario->dni) }}" required placeholder="Ingrese su DNI">
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="{{ old('email', $usuario->email) }}" required placeholder="Ingrese su email">
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <input type="password" name="password" id="password" class="form-control" placeholder="Dejar vacío si no se cambia">
      <small class="form-text text-muted">Dejá este campo vacío si no querés cambiar la contraseña.</small>
    </div>

    <div class="mb-3">
      <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
      <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Repetí la contraseña">
    </div>

    <div class="mb-3">
      <label for="id_rol" class="form-label">Rol</label>
      <select name="id_rol" class="form-select" required>
        <option value="" disabled>-- Seleccionar rol --</option>
        @foreach ($roles as $rol)
          <option value="{{ $rol->id }}" {{ $usuario->id_rol == $rol->id ? 'selected' : '' }}>
            {{ $rol->nombre_rol }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Estado actual</label>
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
  <div class="d-flex justify-content-center gap-2 mt-4">
    @php $estado = $usuario->estado?->nombre; @endphp

    <form method="POST" action="{{ route('usuarios.activarConEPP', $usuario->id) }}" class="form-estado" data-nombre="{{ $usuario->name }}" data-rol="{{ $usuario->rol->nombre_rol }}" data-accion="alta">
      @csrf
      <button type="button"
        class="btn btn-success btn-confirmar-estado {{ $estado === 'Alta' ? 'opacity-50' : '' }}"
        {{ $estado === 'Alta' ? 'disabled' : ($estado === 'Baja' ? 'disabled' : '') }}
        title="{{ $estado === 'Baja' ? 'Usuario en Baja: primero pasar a Stand by para asignar EPP' : ($estado === 'Alta' ? 'Ya está activo' : 'Cambiar a estado Alta') }}">
        Dar de alta
      </button>
    </form>

    <form method="POST" action="{{ route('usuarios.baja', $usuario->id) }}" class="form-estado" data-nombre="{{ $usuario->name }}" data-rol="{{ $usuario->rol->nombre_rol }}" data-accion="baja">
      @csrf
      <button type="button" class="btn btn-danger btn-confirmar-estado {{ $estado === 'Baja' ? 'opacity-50' : '' }}" {{ $estado === 'Baja' ? 'disabled' : '' }} title="{{ $estado === 'Baja' ? 'Ya está dado de baja' : 'Cambiar a estado Baja' }}">
        Dar de baja
      </button>
    </form>

    <form method="POST" action="{{ route('usuarios.standby', $usuario->id) }}" class="form-estado" data-nombre="{{ $usuario->name }}" data-rol="{{ $usuario->rol->nombre_rol }}" data-accion="stand by">
      @csrf
      <button type="button" class="btn btn-warning btn-confirmar-estado {{ $estado === 'stand by' ? 'opacity-50' : '' }}"
              {{ $estado === 'stand by' ? 'disabled' : '' }}
              title="{{ $estado === 'stand by' ? 'Ya está en stand by' : 'Cambiar a estado Stand by' }}">
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

<!-- Modal de mensaje -->
<div class="modal fade" id="modalMensajeSistema" tabindex="-1" aria-labelledby="modalMensajeSistemaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white" id="modalMensajeHeader">
        <h5 class="modal-title" id="modalMensajeSistemaLabel">Mensaje del sistema</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalMensajeContenido">
        <!-- contenido dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
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
  // 🔔 Ocultar alerta antigua (si existiera)
  (function hideOldAlert() {
    const alerta = document.getElementById('alertaEstado');
    if (!alerta) return;
    setTimeout(() => {
      alerta.classList.add('fade');
      alerta.classList.remove('show');
      alerta.addEventListener('transitionend', () => alerta.remove(), { once: true });
    }, 5000);
  })();

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

        let mensaje;
        if (accion === 'alta' && estadoActual.toLowerCase() === 'baja') {
          mensaje = 'El usuario está en Baja. Primero debe pasarse a stand by para asignarle EPP; luego podrá activarse. ¿Desea continuar?';
        } else {
          mensaje = `¿Desea dar de ${accion} a ${nombre}${rol ? ' (' + rol + ')' : ''}?`;
        }

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
            const modalMsgEl = document.getElementById('modalMensajeSistema');
            if (modalMsgEl) {
              document.getElementById('modalMensajeHeader')?.classList.remove('bg-success');
              document.getElementById('modalMensajeHeader')?.classList.add('bg-danger', 'text-white');
              document.getElementById('modalMensajeSistemaLabel').textContent = 'Error';
              document.getElementById('modalMensajeContenido').textContent = 'No se puede realizar la acción. Faltan: ' + faltantes;
              new bootstrap.Modal(modalMsgEl).show();
            }
            return;
          }
        }

        formEstadoSeleccionado.submit();
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEstado'));
        if (modal) modal.hide();
      }
    });
  }

  // 💾 Modal de confirmación para guardar cambios
  (function setupGuardarModal() {
    const btnAbrirModalGuardar = document.getElementById('btnAbrirModalGuardar');
    const formEditarUsuario = document.getElementById('formEditarUsuario');

    if (!btnAbrirModalGuardar || !formEditarUsuario) return;

    btnAbrirModalGuardar.addEventListener('click', function () {
      const modalGuardar = new bootstrap.Modal(document.getElementById('modalConfirmarGuardar'));
      modalGuardar.show();
    });
  })();

  // 🚨 Validación personalizada de campos requeridos
  (function setupValidacionCampos() {
    const formEditarUsuario = document.getElementById('formEditarUsuario');
    const btnConfirmarGuardar = document.getElementById('btnConfirmarGuardar');

    if (!formEditarUsuario || !btnConfirmarGuardar) return;

    // Interceptar el submit del formulario
    formEditarUsuario.addEventListener('submit', function (e) {
      if (!formEditarUsuario.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();

        const modalMsgEl = document.getElementById('modalMensajeSistema');
        if (modalMsgEl) {
          const header = document.getElementById('modalMensajeHeader');
          header.classList.remove('bg-success');
          header.classList.add('bg-danger', 'text-white');
          document.getElementById('modalMensajeSistemaLabel').textContent = 'Error';
          document.getElementById('modalMensajeContenido').textContent =
            'Por favor, completá todos los campos obligatorios antes de guardar.';
          new bootstrap.Modal(modalMsgEl).show();
        }
      }
    });

    // Confirmar guardar → validar antes de enviar
    btnConfirmarGuardar.addEventListener('click', function () {
  if (formEditarUsuario.checkValidity()) {
    formEditarUsuario.requestSubmit(); // dispara el submit sin validación nativa
  } else {
    // 🔴 Cerrar el modal de confirmación de guardar si está abierto
    const modalConfirmarGuardarEl = document.getElementById('modalConfirmarGuardar');
    const modalConfirmarGuardar = bootstrap.Modal.getInstance(modalConfirmarGuardarEl);
    if (modalConfirmarGuardar) {
      modalConfirmarGuardar.hide();
    }

    // Mostrar el modal de error
    const modalMsgEl = document.getElementById('modalMensajeSistema');
    if (modalMsgEl) {
      const header = document.getElementById('modalMensajeHeader');
      header.classList.remove('bg-success');
      header.classList.add('bg-danger', 'text-white');
      document.getElementById('modalMensajeSistemaLabel').textContent = 'Error';
      document.getElementById('modalMensajeContenido').textContent =
        'Tenés campos sin completar. Revisalos antes de continuar.';
      new bootstrap.Modal(modalMsgEl).show();
    }
  }
});

  })();

  // Modal automático para mensajes del sistema
(function autocloseSystemModal() {
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
    (function () {
      const modalEl = document.getElementById('modalMensajeSistema');
      if (!modalEl) return;
      const header = document.getElementById('modalMensajeHeader');
      header?.classList.remove('bg-success');
      header?.classList.add('bg-danger', 'text-white');
      document.getElementById('modalMensajeSistemaLabel').textContent = 'Error';
      const errores = @json($errors->all());
      document.getElementById('modalMensajeContenido').textContent = errores.join('\n');
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    })();
  @endif
})();

});
</script>

@endpush

@push('styles')
<link href="{{ asset('css/editarUsuario.css') }}" rel="stylesheet">
@endpush



