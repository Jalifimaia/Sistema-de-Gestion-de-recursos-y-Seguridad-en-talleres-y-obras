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
        <div class="col-md-2">
          <label for="filtro-estado" class="form-label">Estado</label>
          <select id="filtro-estado" class="form-select">
            <option value="">Todos</option>
            <option value="Cancelado">Cancelado</option>
            <option value="Activo">Activo</option>
            <option value="Vencido">Vencido</option>
            <option value="Devuelto">Devuelto</option>
          </select>
        </div>
        <div class="col-md-2">
          <label for="filtro-creador" class="form-label">Creado por</label>
          <select id="filtro-creador" class="form-select">
            <option value="">Todos</option>
            @foreach($prestamos->pluck('creado_por')->unique() as $nombre)
              <option value="{{ $nombre }}">{{ $nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label for="fecha-inicio" class="form-label">Desde</label>
          <input type="date" id="fecha-inicio" class="form-control">
        </div>
        <div class="col-md-2">
          <label for="fecha-fin" class="form-label">Hasta</label>
          <input type="date" id="fecha-fin" class="form-control">
        </div>
        <div class="col-md-4">
          <label for="busqueda" class="form-label">Buscar por recurso, serie, trabajador o creador</label>
          <input type="text" id="busqueda" class="form-control" placeholder="Ej: taladro, XP-001, David, Admin">
        </div>
      </div>

      {{-- Tarjetas de préstamos --}}
      <div id="contenedorPrestamos" class="row g-3">
        @foreach ($prestamos as $p)
          @php
            $color = match($p->estado) {
              'Cancelado' => 'danger',
              'Activo' => 'success',
              'Vencido' => 'warning',
              default => 'secondary',
            };
          @endphp

          <div class="prestamo-item col-md-4"
               data-estado="{{ $p->estado }}"
               data-creador="{{ $p->creado_por }}"
               data-texto="{{ strtolower($p->recurso . ' ' . $p->nro_serie . ' ' . $p->asignado . ' ' . $p->creado_por) }}"
               data-fecha="{{ $p->fecha_creacion }}">
            <div class="card border-secondary shadow-sm h-100">
              <div class="card-body">
                <h6 class="card-title mb-1">{{ $p->recurso }}</h6>
                <p class="card-text mb-1">Serie: <strong>{{ $p->nro_serie }}</strong></p>
                <p class="card-text mb-1">Asignado a: {{ $p->asignado }}</p>
                <p class="card-text mb-1">Creado por: {{ $p->creado_por }}</p>
                <p class="card-text mb-1">
                  Fecha de creación: {{ \Carbon\Carbon::parse($p->fecha_creacion)->format('d/m/Y H:i') }}
                </p>
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
  const creadorSelect = document.getElementById('filtro-creador');
  const busquedaInput = document.getElementById('busqueda');
  const fechaInicioInput = document.getElementById('fecha-inicio');
  const fechaFinInput = document.getElementById('fecha-fin');
  const items = document.querySelectorAll('.prestamo-item');

  function filtrar() {
    const estado = estadoSelect.value.toLowerCase();
    const creador = creadorSelect.value.toLowerCase();
    const texto = busquedaInput.value.toLowerCase();
    const inicio = fechaInicioInput.value ? new Date(fechaInicioInput.value) : null;
    const fin = fechaFinInput.value? new Date(fechaFinInput.value + 'T23:59:59') : null;


    items.forEach(item => {
      const matchEstado = !estado || item.dataset.estado.toLowerCase() === estado;
      const matchCreador = !creador || item.dataset.creador.toLowerCase().includes(creador);
      const matchTexto = !texto || item.dataset.texto.includes(texto);

      const fechaItem = item.dataset.fecha ? new Date(item.dataset.fecha) : null;
      const matchFecha = (!inicio || !fechaItem || fechaItem >= inicio) &&
                         (!fin || !fechaItem || fechaItem <= fin);

      item.style.display = (matchEstado && matchCreador && matchTexto && matchFecha) ? '' : 'none';
    });
  }

  estadoSelect.addEventListener('change', filtrar);
  creadorSelect.addEventListener('change', filtrar);
  busquedaInput.addEventListener('input', filtrar);
  fechaInicioInput.addEventListener('change', filtrar);
  fechaFinInput.addEventListener('change', filtrar);
</script>
@endpush
