@extends('layouts.app')

@section('template_title')
  Lista de Préstamos
@endsection

@section('content')

<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white text-center">
      <h4 class="mb-0">Préstamos Registrados</h4>
    </div>
    <div class="card-body bg-white">

  {{-- Encabezado + Botón --}}
  <div class="row mb-3">
    <div class="col d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Listado de Préstamos</h5>
      <a href="{{ route('prestamos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Préstamo
      </a>
    </div>
  </div>

  {{-- Filtros --}}
  <div class="row mb-3 align-items-end">
    <div class="col-md-3">
      <label for="filtro-estado" class="form-label">Estado</label>
      <select id="filtro-estado" class="form-select">
        <option value="">Todos</option>
        <option value="Pendiente">Pendiente</option>
        <option value="Activo">Activo</option>
        <option value="Devuelto">Devuelto</option>
        <option value="Cancelado">Cancelado</option>
      </select>
    </div>
    <div class="col-md-3">
      <label for="filtro-trabajador" class="form-label">Trabajador</label>
      <select id="filtro-trabajador" class="form-select">
        <option value="">Todos</option>
        @foreach($prestamos->pluck('asignado')->unique() as $nombre)
          <option value="{{ $nombre }}">{{ $nombre }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-6">
      <label for="busqueda" class="form-label">Buscar por recurso, serie o creador</label>
      <input type="text" id="busqueda" class="form-control" placeholder="Ej: taladro, 123ABC, Juan Pérez">
    </div>
  </div>

      {{-- Tarjetas de préstamos --}}
      <div id="contenedorPrestamos" class="row g-3">
        @foreach ($prestamos as $p)
  @php
    $color = match($p->estado) {
      'Pendiente' => 'warning',
      'Activo' => 'success',
      'Devuelto' => 'success',
      'Cancelado' => 'danger',
      default => 'secondary',
    };
  @endphp

  <div class="prestamo-item col-md-4"
       data-estado="{{ $p->estado }}"
       data-trabajador="{{ $p->asignado }}"
       data-texto="{{ strtolower($p->recurso . ' ' . $p->nro_serie . ' ' . $p->creado_por) }}">
    <div class="card border-secondary shadow-sm h-100">
      <div class="card-body">
        <h6 class="card-title mb-1">{{ $p->recurso }}</h6>
        <p class="card-text mb-1">Serie: <strong>{{ $p->nro_serie }}</strong></p>
        <p class="card-text mb-1">Asignado a: {{ $p->asignado }}</p>
        <p class="card-text mb-1">Creado por: {{ $p->creado_por }}</p>
        <p class="card-text mb-0">
          Estado: <span class="badge bg-{{ $color }}">{{ $p->estado }}</span>
        </p>
        <a href="{{ route('prestamos.edit', $p->id) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
          Editar
        </a>
      </div>
    </div>
  </div>
@endforeach

      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const estadoSelect = document.getElementById('filtro-estado');
  const trabajadorSelect = document.getElementById('filtro-trabajador');
  const busquedaInput = document.getElementById('busqueda');
  const items = document.querySelectorAll('.prestamo-item');

  function filtrar() {
    const estado = estadoSelect.value.toLowerCase();
    const trabajador = trabajadorSelect.value.toLowerCase();
    const texto = busquedaInput.value.toLowerCase();

    items.forEach(item => {
      const matchEstado = !estado || item.dataset.estado.toLowerCase() === estado;
      const matchTrabajador = !trabajador || item.dataset.trabajador.toLowerCase().includes(trabajador);
      const matchTexto = !texto || item.dataset.texto.includes(texto);

      item.style.display = (matchEstado && matchTrabajador && matchTexto) ? '' : 'none';
    });
  }

  estadoSelect.addEventListener('change', filtrar);
  trabajadorSelect.addEventListener('change', filtrar);
  busquedaInput.addEventListener('input', filtrar);
</script>
@endpush
