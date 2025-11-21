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
                <div class="acciones-grupo">
                  <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-sm btn-ver-series btn-accion-compact" title="Ver detalles del usuario">
                    <i class="bi bi-eye"></i>
                  </a>

                  <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-editar btn-accion-compact" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>

                  <form action="{{ route('usuarios.baja', $usuario->id) }}" method="POST"
                        class="d-inline form-baja"
                        data-nombre="{{ $usuario->name }}"
                        data-rol="{{ $usuario->rol->nombre_rol ?? '-' }}">
                    @csrf
                    @php $esBaja = ($usuario->estado->nombre ?? null) === 'Baja'; @endphp
                    <span @if($esBaja) title="Usuario dado de baja" @endif>

                    <button type="button" class="btn btn-sm btn-danger btn-marcar-baja btn-accion-compact btn-confirmar-baja" title="Dar de baja"
                      @if($esBaja) disabled style="opacity:0.5; cursor:not-allowed;" @endif>
                      <i class="bi bi-trash"></i>
                    </button>

                    </span>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

          <div class="d-flex justify-content-between align-items-center mt-3">
              <div id="infoPaginacionUsuarios" class="text-muted small"></div>
              <ul id="paginacionUsuarios" class="pagination mb-0"></ul>
          </div>


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

<!-- Modal de éxito en baja -->
<div class="modal fade" id="modalExitoBaja" tabindex="-1" aria-labelledby="modalExitoBajaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalExitoBajaLabel">Éxito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalExitoBajaContenido">
        <!-- Texto dinámico -->
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
  const filas = Array.from(document.querySelectorAll('#tablaUsuarios tbody tr'));
  const info = document.getElementById('infoPaginacionUsuarios');
  const paginacion = document.getElementById('paginacionUsuarios');
  const filasPorPagina = 10;

  let paginaActual = 1;
  let visibles = filas;

  // Helpers seguros
  const norm = v => (v || '').trim().toLowerCase();
  const esTodos = v => {
    const n = norm(v);
    return n === '' || n === 'todos' || n.startsWith('todos ');
  };

  function recalcularVisibles() {
    const rolSel = typeof filtroRol !== 'undefined' ? filtroRol.value : '';
    const estadoSel = typeof filtroEstado !== 'undefined' ? filtroEstado.value : '';
    const textoSel = typeof buscador !== 'undefined' ? buscador.value : '';

    const rol = norm(rolSel);
    const estado = norm(estadoSel);
    const texto = norm(textoSel);

    visibles = filas.filter(fila => {
      const rolFila = norm(fila.getAttribute('data-rol'));
      const estadoFila = norm(fila.getAttribute('data-estado'));
      const nombre = norm(fila.querySelector('.nombre-completo')?.dataset?.nombre);
      const email = norm(fila.cells[1]?.innerText);

      const okRol = esTodos(rolSel) || rolFila === rol;
      const okEstado = esTodos(estadoSel) || estadoFila === estado;
      const okTexto = texto === '' || nombre.includes(texto) || email.includes(texto);

      return okRol && okEstado && okTexto;
    });

    console.log('visibles:', visibles.length);
  }

  function mostrarPagina(pagina) {
    paginaActual = pagina;

    const totalRegistros = visibles.length;
    const totalPaginas = Math.max(1, Math.ceil(totalRegistros / filasPorPagina));
    paginaActual = Math.min(Math.max(1, paginaActual), totalPaginas);

    filas.forEach(fila => fila.style.display = 'none');

    const inicio = (paginaActual - 1) * filasPorPagina;
    const fin = Math.min(inicio + filasPorPagina, totalRegistros);

    for (let i = inicio; i < fin; i++) {
      const fila = visibles[i];
      if (fila) fila.style.display = 'table-row';
    }

    if (info) {
      const desde = totalRegistros ? inicio + 1 : 0;
      const hasta = totalRegistros ? fin : 0;
      info.textContent = `Mostrando ${desde}-${hasta} de ${totalRegistros} usuarios`;
    }

    if (paginacion) {
      paginacion.innerHTML = '';
      for (let i = 1; i <= totalPaginas; i++) {
        const li = document.createElement('li');
        li.className = 'page-item' + (i === paginaActual ? ' active' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.addEventListener('click', e => {
          e.preventDefault();
          mostrarPagina(i);
        });
        li.appendChild(a);
        paginacion.appendChild(li);
      }
    }

    console.log('paginaActual:', paginaActual, 'rango:', inicio, fin);
  }

  function aplicarFiltros() {
    recalcularVisibles();
    mostrarPagina(1);
  }

  aplicarFiltros();

  if (typeof filtroRol !== 'undefined') filtroRol.addEventListener('change', aplicarFiltros);
  if (typeof filtroEstado !== 'undefined') filtroEstado.addEventListener('change', aplicarFiltros);
  if (typeof buscador !== 'undefined') buscador.addEventListener('input', aplicarFiltros);

  // 🔶 Modal de confirmación de baja
  const modal = new bootstrap.Modal(document.getElementById('modalConfirmarBaja'));
  const textoConfirmacion = document.getElementById('textoConfirmacion');
  let formSeleccionado = null;

  document.querySelectorAll('.btn-confirmar-baja').forEach(boton => {
    boton.addEventListener('click', function() {
      formSeleccionado = this.closest('form');
      const nombre = formSeleccionado.dataset.nombre;
      const rol = formSeleccionado.dataset.rol;
      textoConfirmacion.textContent = `¿Desea dar de baja a ${nombre} [${rol}]?`;
      modal.show();
    });
  });

  document.getElementById('btnConfirmarBaja').addEventListener('click', function() {
  if (!formSeleccionado) return;

  const boton = formSeleccionado.querySelector('button');
  const fila = formSeleccionado.closest('tr');
  const estadoCell = fila.querySelector('.estado');
  const nombre = formSeleccionado.dataset.nombre;
  const rol = formSeleccionado.dataset.rol;

  fetch(formSeleccionado.action, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': formSeleccionado.querySelector('[name=_token]').value }
  }).then(res => {
    if (res.ok) {
      // Actualizar estado visual
      estadoCell.innerText = 'Baja';
      fila.setAttribute('data-estado', 'Baja');
      boton.disabled = true;
      boton.style.opacity = '0.5';
      boton.style.cursor = 'not-allowed';
      aplicarFiltros();

      // ✅ Mostrar modal de éxito
      const modalExitoEl = document.getElementById('modalExitoBaja');
      const contenido = document.getElementById('modalExitoBajaContenido');
      if (modalExitoEl && contenido) {
      contenido.innerHTML = `Usuario <strong>${nombre}</strong> [<em>${rol}</em>] dado de baja correctamente.`;
        const modalExito = new bootstrap.Modal(modalExitoEl);
        modalExito.show();
        setTimeout(() => modalExito.hide(), 4000); // opcional: cerrar solo
      }
    }

    // Cerrar modal de confirmación
    modal.hide();
  });
});


  // 🔶 Estado visual y fecha
  const alerta = document.getElementById('alertaEstado');
  if (alerta) {
    setTimeout(() => {
      alerta.classList.add('fade');
      alerta.classList.remove('show');
      alerta.addEventListener('transitionend', () => alerta.remove(), { once: true });
    }, 5000);
  }

  const today = new Date();
  const dia = String(today.getDate()).padStart(2, '0');
  const mes = String(today.getMonth() + 1).padStart(2, '0');
  const año = today.getFullYear();
  const horas = String(today.getHours()).padStart(2, '0');
  const minutos = String(today.getMinutes()).padStart(2, '0');
  const segundos = String(today.getSeconds()).padStart(2, '0');
  document.getElementById("today").textContent = `${dia}/${mes}/${año} ${horas}:${minutos}:${segundos}`;

  function capitalizar(texto) {
    return texto.split(' ').map(p => p.charAt(0).toUpperCase() + p.slice(1).toLowerCase()).join(' ');
  }

  document.querySelectorAll('[id^="estado-usuario-"]').forEach(div => {
    const estado = (div.dataset.estado || '').toLowerCase();
    if (estado) {
      div.textContent = capitalizar(estado);
      div.style.display = 'inline-block';
    } else {
      div.style.display = 'none';
    }
    div.className = 'badge px-2 py-1 rounded';
    switch (estado) {
      case 'alta': div.classList.add('bg-success','text-white'); break;
      case 'baja': div.classList.add('bg-danger','text-white'); break;
      case 'stand by': div.classList.add('bg-secondary','text-white'); div.style.fontStyle='italic'; break;
      default: div.classList.add('bg-light','text-dark'); break;
    }
    div.style.fontSize = '1rem';
  });
});
</script>
@endpush

@push('styles')
<link href="{{ asset('css/usuariosIndex.css') }}?v={{ time() }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
@endpush

