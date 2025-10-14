@extends('layouts.app')

@section('title', 'Gestión de Usuarios y Roles')

@section('content')
  <!-- Título -->
  <div class="mb-4">
    <h2>Gestión de Usuarios y Roles</h2>
    <p class="text-muted">Administración de usuarios, permisos y roles del sistema</p>
  </div>

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
      <a href="{{ route('usuarios.create') }}" class="btn btn-success mb-3">
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
    <input type="text" id="buscador" class="form-control w-auto" placeholder="Buscar por nombre o email...">
  </div>

  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title fw-bold">Listado de Usuarios</h5>
      <p class="text-muted small">Usuarios registrados en el sistema</p>

      <div class="table-responsive">
        <table class="table align-middle mb-0" id="tablaUsuarios">
          <thead>
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
                  <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-sm btn-outline-info">Ver</a>
                  <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-outline-primary">Editar</a>

                  <form action="{{ route('usuarios.baja', $usuario->id) }}" method="POST" class="d-inline form-baja">
                    @csrf
                    <button class="btn btn-sm btn-outline-warning"
                            @if(($usuario->estado->nombre ?? null) === 'Baja')
                              disabled style="opacity:0.5; cursor:not-allowed;"
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
</script>
@endpush
