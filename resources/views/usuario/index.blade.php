@extends('layouts.app')

@section('title', 'Gestión de Usuarios y Roles')

@section('content')
<div class="container py-4">  


<!-- 🔶 Encabezado -->
<header class="row mb-4 align-items-center">
  <div class="col-md-8">
    <h1 class="h4 fw-bold mb-1 d-flex align-items-center gap-2">
      <img src="{{ asset('images/user.svg') }}" alt="Icono Usuario" style="height: 35px;">
      Gestión de Usuarios y Roles
    </h1>
    <p class="text-muted small">Administración de usuarios, permisos y roles del sistema</p>
  </div>

  <div class="col-md-4 text-md-end fecha-destacada d-flex align-items-center justify-content-md-end mt-3">
        <strong id="today" class="valor-fecha text-nowrap">07/11/2023 09:20:17</strong>
      </div>
</header>

 
<div class="row mb-4 subir-cards-usuarios">
  <!-- Card: Crear usuario -->
  @auth
    @if (Auth::user()->rol->nombre_rol === 'Administrador')
      <div class="col-md-3">
        <a href="{{ route('usuarios.create') }}" class="card card-resumen card-crear text-center text-decoration-none h-100">
          <div class="card-body">
            <h6 class="card-title card-crear d-flex align-items-center justify-content-center gap-2">
              <img src="{{ asset('images/useraddd.svg') }}" alt="Crear usuario" class="icono-card-titulo">
              Crear usuario
            </h6>
            <small class="text-muted">Alta de nuevo usuario</small>
          </div>
        </a>
      </div>
    @endif
  @endauth

  <!-- Card: Administradores -->
  <div class="col-md-3">
    <div class="card card-resumen text-center h-100">
      <div class="card-body">
        <h6 class="card-title d-flex align-items-center justify-content-center gap-2">
          <img src="{{ asset('images/admin.svg') }}" alt="Administradores" class="icono-card-titulo">
          Administradores
        </h6>
        <h3 class="mb-1">{{ $usuarios->where('rol.nombre_rol', 'Administrador')->count() }}</h3>
        <small class="text-muted">Acceso completo</small>
      </div>
    </div>
  </div>

  <!-- Card: Último acceso -->
  @if ($ultimoUsuarioActivo)
    <div class="col-md-3">
      <div class="card card-resumen text-center h-100">
        <div class="card-body">
          <h6 class="card-title d-flex align-items-center justify-content-center gap-2">
            <img src="{{ asset('images/access.svg') }}" alt="Último acceso" class="icono-card-titulo">
            Último acceso
          </h6>
          <p class="card-text mb-1">{{ $ultimoUsuarioActivo->name }}</p>
          <small class="text-muted">
            {{ \Carbon\Carbon::parse($ultimoUsuarioActivo->ultimo_acceso)->diffForHumans() }}
          </small>
        </div>
      </div>
    </div>
  @endif
</div>

<div class="seccion-usuarios">

  <!-- Filtros -->
  <!-- 🔶 Filtro de usuarios -->
<div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
  <label class="form-label mb-0 fw-semibold filtrar-por">Filtrar por:</label>

  <select id="filtroRol" class="form-select filtro-destacado w-auto">
    <option value="todos">Todos los roles</option>
    @foreach($roles as $rol)
      <option value="{{ $rol->nombre_rol }}">{{ $rol->nombre_rol }}</option>
    @endforeach
  </select>

  <select id="filtroEstado" class="form-select filtro-destacado w-auto">
    <option value="todos">Todos los estados</option>
    @foreach($estados as $estado)
      <option value="{{ $estado->nombre }}">{{ $estado->nombre }}</option>
    @endforeach
  </select>

  <input type="text" id="buscador" class="form-control buscador-destacado" placeholder="Buscar por nombre o email..." style="width: 280px; max-width: 100%;">
</div>


  <!-- Tabla -->

    <div class="card-body">

      <div class="table-responsive">
        <table class="table-naranja align-middle mb-0" id="tablaUsuarios">
          <thead class="table-light">
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              <!--<th>Rol</th>-->
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($usuarios as $usuario)
              <tr data-rol="{{ $usuario->rol->nombre_rol ?? '' }}"
                  data-estado="{{ $usuario->estado->nombre ?? '' }}">
                <td class="nombre-completo" data-nombre="{{ $usuario->name }}">
                  {{ $usuario->name }} [<strong>{{ $usuario->rol->nombre_rol ?? '-' }}</strong>]
                </td>
                <td>{{ $usuario->email }}</td>
                <!--<td>{{ $usuario->rol->nombre_rol ?? '-' }}</td>-->
                <td class="estado text-center">
                  <div id="estado-usuario-{{ $usuario->id }}"
                      class="badge estado-usuario px-3 py-2 rounded"
                      data-estado="{{ $usuario->estado->nombre ?? '-' }}">
                  </div>
                </td>
                <td class="acciones">
                  <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-ver">Ver</a>
                    <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-editar">
                      <i class="bi bi-pencil"></i>
                     Editar</a>

                    <form action="{{ route('usuarios.baja', $usuario->id) }}" method="POST" class="d-inline form-baja" data-nombre="{{ $usuario->name }}" data-rol="{{ $usuario->rol->nombre_rol ?? '-' }}">
                      @csrf
                      @php $esBaja = ($usuario->estado->nombre ?? null) === 'Baja'; @endphp
                      <span @if($esBaja) title="Usuario dado de baja" @endif>
                        <button type="button"
                                class="btn btn-baja btn-confirmar-baja"
                                @if($esBaja) disabled style="opacity:0.5; cursor:not-allowed;" @endif>
                          Dar de baja
                        </button>
                      </span>
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
    const nombre = fila.querySelector('.nombre-completo').dataset.nombre.toLowerCase();

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

    const today = new Date();

    const dia = String(today.getDate()).padStart(2, '0');
    const mes = String(today.getMonth() + 1).padStart(2, '0');
    const año = today.getFullYear();

    const horas = String(today.getHours()).padStart(2, '0');
    const minutos = String(today.getMinutes()).padStart(2, '0');
    const segundos = String(today.getSeconds()).padStart(2, '0');

    const fechaFormateada = `${dia}/${mes}/${año} ${horas}:${minutos}:${segundos}`;
    document.getElementById("today").textContent = fechaFormateada;

    function capitalizar(texto) {
      return texto
        .split(' ')
        .map(palabra => palabra.charAt(0).toUpperCase() + palabra.slice(1).toLowerCase())
        .join(' ');
    }

    document.querySelectorAll('[id^="estado-usuario-"]').forEach(div => {
    const estado = (div.dataset.estado || '').toLowerCase();

    if (estado) {
      div.textContent = capitalizar(estado); 
      div.style.display = 'inline-block';
    } else {
      div.style.display = 'none';
    }

    // Resetear clases base
    div.className = 'badge px-2 py-1 rounded';

    switch (estado.toLowerCase()) {
      case 'alta':
        div.classList.add('bg-success', 'text-white'); // verde relleno
        div.style.fontSize = '1rem'; // más grande
        break;
      case 'baja':
        div.classList.add('bg-danger', 'text-white'); // rojo relleno
        div.style.fontSize = '1rem';
        break;
      case 'stand by':
        div.classList.add('bg-secondary', 'text-white'); // gris relleno
        div.style.fontSize = '1rem';
        div.style.fontStyle = 'italic'; // itálica
        break;
      default:
        div.classList.add('bg-light', 'text-dark');
        div.style.fontSize = '1rem';
        break;
    }
    
  });
  });

</script>
@endpush

@push('styles')
<link href="{{ asset('css/usuariosIndex.css') }}" rel="stylesheet">
@endpush

