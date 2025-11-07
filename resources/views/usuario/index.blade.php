@extends('layouts.app')

@section('title', 'Gestión de Usuarios y Roles')

@section('content')
<div class="container py-4">  
<!--para alertas-->
  @if (session('success'))
  <div id="alertaEstado" class="alert alert-success alert-dismissible fade show" role="alert">
  <span id="mensajeAlertaEstado">{{ session('success') }}</span>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
  </div>
  @endif

<!-- Título -->
  <header class="row mb-4">
    <div class="col-md-8">
      <h1 class="h4 fw-bold mb-1">Gestión de Usuarios y Roles</h1>
      <p class="text-muted small">Administración de usuarios, permisos y roles del sistema</p>
    </div>
    <div class="col-md-4 text-md-end fecha-destacada">
      <span class="etiqueta-fecha">Fecha:</span>
      <strong id="today" class="valor-fecha text-nowrap"></strong>
    </div>
  </header>

  <!-- Resumen -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h6 class="card-title">Administradores</h6>
          <h3>{{ $usuarios->where('rol.nombre_rol', 'Administrador')->count() }}</h3>
          <small class="text-muted">Acceso completo</small>
        </div>
      </div>
    </div>

    @if ($ultimoUsuarioActivo)
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6 class="card-title">Último acceso</h6>
            <p class="card-text">{{ $ultimoUsuarioActivo->name }}</p>
            <small class="text-muted">
              {{ \Carbon\Carbon::parse($ultimoUsuarioActivo->ultimo_acceso)->diffForHumans() }}
            </small>
          </div>
        </div>
      </div>
    @endif
  </div>

  @auth
    @if (Auth::user()->rol->nombre_rol === 'Administrador')
      <a href="{{ route('usuarios.create') }}" class="btn btn-orange mb-3">
        + Crear nuevo usuario
      </a>
    @endif
  @endauth

  <!-- Filtros -->
  <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <label class="form-label mb-0">Filtrar por:</label>

    <select id="filtroRol" class="form-select w-auto">
      <option value="todos">Todos los roles</option>
      @foreach($roles as $rol)
        <option value="{{ $rol->nombre_rol }}">{{ $rol->nombre_rol }}</option>
      @endforeach
    </select>

    <select id="filtroEstado" class="form-select w-auto">
      <option value="todos">Todos los estados</option>
      @foreach($estados as $estado)
        <option value="{{ $estado->nombre }}">{{ $estado->nombre }}</option>
      @endforeach
    </select>

    <!-- 🔍 Barra de búsqueda -->
    <input type="text" id="buscador" class="form-control w-25" placeholder="Buscar por nombre o email...">
  </div>

  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title fw-bold">Listado de Usuarios</h5>
      <p class="text-muted small">Usuarios registrados en el sistema</p>

      <div class="table-responsive">
        <table class="table-naranja align-middle mb-0" id="tablaUsuarios">
          <thead class="table-light">
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($usuarios as $usuario)
              <tr data-rol="{{ $usuario->rol->nombre_rol ?? '' }}"
                  data-estado="{{ $usuario->estado->nombre ?? '' }}">
                <td>{{ $usuario->name }}</td>
                <td>{{ $usuario->email }}</td>
                <td>{{ $usuario->rol->nombre_rol ?? '-' }}</td>
                <td class="estado">{{ $usuario->estado->nombre ?? '-' }}</td>
                <td>
                  <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-sm btn-info">Ver</a>
                  <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-orange">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form action="{{ route('usuarios.baja', $usuario->id) }}" method="POST" class="d-inline form-baja" data-nombre="{{ $usuario->name }}" data-rol="{{ $usuario->rol->nombre_rol ?? '-' }}">
                    @csrf
                    <button type="button" class="btn btn-sm btn-warning btn-confirmar-baja" @if(($usuario->estado->nombre ?? null) === 'Baja') disabled style="opacity:0.5; cursor:not-allowed;"
                    @endif>
                    Dar de baja
                    </button>
                  </form>    
                </td>
              </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal de confirmación de baja -->
<div class="modal fade" id="modalConfirmarBaja" tabindex="-1" aria-labelledby="modalConfirmarBajaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalConfirmarBajaLabel">Confirmar baja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p id="textoConfirmacion">¿Desea dar de baja a ...?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarBaja">Sí</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  const filtroRol = document.getElementById('filtroRol');
  const filtroEstado = document.getElementById('filtroEstado');
  const buscador = document.getElementById('buscador');
  const filas = document.querySelectorAll('#tablaUsuarios tbody tr');

  function filtrar() {
    const rol = filtroRol.value.toLowerCase();
    const estado = filtroEstado.value.toLowerCase();
    const texto = buscador.value.toLowerCase();

    filas.forEach(fila => {
      const rolFila = (fila.getAttribute('data-rol') || '').toLowerCase();
      const estadoFila = (fila.getAttribute('data-estado') || '').toLowerCase();
      const nombre = fila.cells[0].innerText.toLowerCase();
      const email = fila.cells[1].innerText.toLowerCase();

      const coincideRol = (rol === 'todos' || rolFila === rol);
      const coincideEstado = (estado === 'todos' || estadoFila === estado);
      const coincideTexto = (texto === '' || nombre.includes(texto) || email.includes(texto));

      fila.style.display = (coincideRol && coincideEstado && coincideTexto) ? '' : 'none';
    });
  }

  filtroRol.addEventListener('change', filtrar);
  filtroEstado.addEventListener('change', filtrar);
  buscador.addEventListener('input', filtrar);

  // Interceptar formularios de baja para evitar redirect
  document.querySelectorAll('.form-baja').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const boton = this.querySelector('button');
      const fila = this.closest('tr');
      const estadoCell = fila.querySelector('.estado');

      fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': this.querySelector('[name=_token]').value
        }
      }).then(res => {
        if (res.ok) {
          // Actualizar solo la fila
          estadoCell.innerText = 'Baja';
          fila.setAttribute('data-estado', 'Baja');
          boton.disabled = true;
          boton.style.opacity = '0.5';
          boton.style.cursor = 'not-allowed';
        }
      });
    });
  });

  // Modal para confirmación de baja
  let formSeleccionado = null;
  document.querySelectorAll('.btn-confirmar-baja').forEach(boton => {
    boton.addEventListener('click', function () {
      formSeleccionado = this.closest('form');
      const nombre = formSeleccionado.getAttribute('data-nombre');
      const rol = formSeleccionado.getAttribute('data-rol');
      const texto = document.getElementById('textoConfirmacion');
      texto.textContent = `¿Desea dar de baja a ${nombre} (${rol})?`;
      const modal = new bootstrap.Modal(document.getElementById('modalConfirmarBaja'));
      modal.show();
    });
  });

  document.getElementById('btnConfirmarBaja').addEventListener('click', function () {
    if (formSeleccionado) {
      formSeleccionado.dispatchEvent(new Event('submit', { cancelable: true }));
      const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarBaja'));
      modal.hide();
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
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
  });

</script>
@endpush
