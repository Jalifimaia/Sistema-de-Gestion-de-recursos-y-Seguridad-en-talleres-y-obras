@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Usuario
@endsection

@section('content')
<section class="container py-4">
    <div class="">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <span class="card-title">Editar Usuario</span>
                </div>

                <!-- para alertas -->
                @if (session('success'))
                <div id="alertaEstado" class="alert alert-success alert-dismissible fade show" role="alert">
                <span id="mensajeAlertaEstado">{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
                @endif

                <div class="card-body bg-white">

                    {{-- Formulario de actualización --}}
                    <form id="formEditarUsuario" method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" 
                                   value="{{ old('name', $usuario->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" name="dni" class="form-control" 
                                   value="{{ old('dni', $usuario->dni) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="{{ old('email', $usuario->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" 
                                   class="form-control">
                            <small class="form-text text-muted">
                                Dejá este campo vacío si no querés cambiar la contraseña.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="form-control" >
                        </div>

                        <div class="mb-3">
                            <label for="id_rol" class="form-label">Rol</label>
                            <select name="id_rol" class="form-select" required>
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}" 
                                        {{ $usuario->id_rol == $rol->id ? 'selected' : '' }}>
                                        {{ $rol->nombre_rol }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Estado actual (solo lectura, último campo) --}}
                        <div class="mb-3">
                            <label class="form-label">Estado actual</label>
                            <div>
                                @if ($usuario->estado?->nombre === 'Alta')
                                    <span class="badge bg-success">Activo (Alta)</span>
                                @elseif ($usuario->estado?->nombre === 'Baja')
                                    <span class="badge bg-danger">Inactivo (Baja)</span>
                                @elseif ($usuario->estado?->nombre === 'stand by')
                                    <span class="badge bg-warning text-dark">Stand by</span>
                                @else
                                    <span class="badge bg-secondary">Sin estado</span>
                                @endif
                            </div>
                        </div>
                        
                        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                            ⬅️ Volver
                        </a>
                        
                        <button type="button" class="btn btn-primary" id="btnAbrirModalGuardar">
                            Guardar cambios
                        </button>
                    </form>

                    <div class="mt-3">
                        <h6>EPP asignado:</h6>
                        <ul>
                           @foreach (['casco', 'guantes', 'lentes', 'botas', 'chaleco', 'arnes'] as $tipo)
                                <li>
                                    {{ ucfirst($tipo) }}:
                                    @if (optional($usuario->usuarioRecursos)->where('tipo_epp', $tipo)->isNotEmpty())
                                        ✅
                                    @else
                                        ❌
                                    @endif
                                </li>
                            @endforeach

                        </ul>
                        </div>


                    {{-- Bloque de acciones de estado --}}
                    <div class="d-flex gap-2 mt-4">

                        {{-- Dar de Alta --}}
                        <form method="POST" action="{{ route('usuarios.alta', $usuario->id) }}" class="form-estado" data-nombre="{{ $usuario->name }}" data-rol="{{ $usuario->rol->nombre_rol }}" data-accion="alta">
                        @csrf
                        <button type="button" class="btn btn-success btn-confirmar-estado {{ $usuario->estado->nombre === 'Alta' ? 'opacity-50' : '' }}" {{ $usuario->estado->nombre === 'Alta' ? 'disabled' : '' }} title="{{ $usuario->estado->nombre === 'Alta' ? 'Ya está activo' : 'Cambiar a estado Alta' }}">
                            Dar de alta
                        </button>
                        </form>

                        {{-- Dar de Baja --}}
                        <form method="POST" action="{{ route('usuarios.baja', $usuario->id) }}" class="form-estado" data-nombre="{{ $usuario->name }}" data-rol="{{ $usuario->rol->nombre_rol }}" data-accion="baja">
                        @csrf
                        <button type="button" class="btn btn-danger btn-confirmar-estado {{ $usuario->estado->nombre === 'Baja' ? 'opacity-50' : '' }}" {{ $usuario->estado->nombre === 'Baja' ? 'disabled' : '' }} title="{{ $usuario->estado->nombre === 'Baja' ? 'Ya está dado de baja' : 'Cambiar a estado Baja' }}">
                            Dar de baja
                        </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

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

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // 🔔 Ocultar alerta automáticamente
  const alerta = document.getElementById('alertaEstado');
  if (alerta) {
    setTimeout(() => {
      alerta.classList.add('fade');
      alerta.classList.remove('show');
      alerta.addEventListener('transitionend', () => {
        alerta.remove();
      }, { once: true });
    }, 5000);
  }

  // 🟢 Modal de confirmación de estado (alta/baja)
  let formEstadoSeleccionado = null;

  document.querySelectorAll('.btn-confirmar-estado').forEach(boton => {
    boton.addEventListener('click', function () {
      formEstadoSeleccionado = this.closest('form');
      const nombre = formEstadoSeleccionado.getAttribute('data-nombre');
      const rol = formEstadoSeleccionado.getAttribute('data-rol');
      const accion = formEstadoSeleccionado.getAttribute('data-accion');

      const texto = document.getElementById('textoConfirmacionEstado');
      texto.textContent = `¿Desea dar de ${accion} a ${nombre} (${rol})?`;

      const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEstado'));
      modal.show();
    });
  });

  document.getElementById('btnConfirmarEstado').addEventListener('click', function () {
    if (formEstadoSeleccionado) {
      formEstadoSeleccionado.submit();
      const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEstado'));
      modal.hide();
    }
  });

  // 💾 Modal de confirmación para guardar cambios
  const btnAbrirModalGuardar = document.getElementById('btnAbrirModalGuardar');
  const btnConfirmarGuardar = document.getElementById('btnConfirmarGuardar');
  const formEditarUsuario = document.getElementById('formEditarUsuario');

  if (btnAbrirModalGuardar && btnConfirmarGuardar && formEditarUsuario) {
    btnAbrirModalGuardar.addEventListener('click', function () {
      const modalGuardar = new bootstrap.Modal(document.getElementById('modalConfirmarGuardar'));
      modalGuardar.show();
    });

    btnConfirmarGuardar.addEventListener('click', function () {
      formEditarUsuario.submit();
    });
  }
});

</script>
@endpush
