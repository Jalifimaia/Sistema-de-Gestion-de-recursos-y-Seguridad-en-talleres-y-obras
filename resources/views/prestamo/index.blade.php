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

      <div class="mb-3 text-start">
        <a href="{{ route('prestamos.create') }}" class="btn btn-nuevo-prestamo">
          <i class="bi bi-plus-circle"></i> Nuevo Préstamo
        </a>
      </div>


      {{-- Filtros --}}
      <form method="GET" action="{{ route('prestamos.index') }}">
      <div class="row mb-3 align-items-end">
        <div class="col-md-2">
          <label for="filtro-estado" class="form-label">Estado</label>
          <select id="filtro-estado" name="estado" class="form-select filtro-naranja">
            <option value="">Todos</option>
            <option value="Cancelado" {{ request('estado') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
            <option value="Activo" {{ request('estado') == 'Activo' ? 'selected' : '' }}>Activo</option>
            <option value="Vencido" {{ request('estado') == 'Vencido' ? 'selected' : '' }}>Vencido</option>
            <option value="Devuelto" {{ request('estado') == 'Devuelto' ? 'selected' : '' }}>Devuelto</option>
          </select>
        </div>

        <div class="col-md-2">
          <label for="filtro-creador" class="form-label">Creado por</label>
          <select id="filtro-creador" name="creador" class="form-select filtro-naranja">
            <option value="">Todos</option>
            @foreach($prestamos->pluck('creado_por')->unique() as $nombre)
              <option value="{{ $nombre }}" {{ request('creador') == $nombre ? 'selected' : '' }}>{{ $nombre }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label for="fecha-inicio" class="form-label">Desde</label>
          <input type="date" id="fecha-inicio" name="fecha_inicio" class="form-control filtro-naranja" value="{{ request('fecha_inicio') }}">
        </div>

        <div class="col-md-3 d-flex align-items-end">
          <div class="w-100 me-2">
            <label for="fecha-fin" class="form-label">Hasta</label>
            <input type="date" id="fecha-fin" name="fecha_fin" class="form-control filtro-naranja" value="{{ request('fecha_fin') }}">
          </div>
          <button type="button" id="btn-filtrar" class="btn btn-naranja d-flex align-items-center justify-content-center"
                  style="width: 40px; height: 40px;">
            <i class="bi bi-search"></i>
          </button>
        </div> <br>

        <div class="col-md-6">
          <label for="search" class="form-label">Buscar</label>
          <div class="input-group">
            <input type="text" name="search" id="busqueda" value="{{ request('search') }}" class="form-control filtro-naranja" placeholder="🔍 Buscar por recurso, serie, trabajador o creador">
            <button class="btn btn-naranja" type="submit">
              <i class="bi bi-search"></i>
            </button>
          </div>
        </div>
      </div>

</form>



      {{-- Tarjetas de préstamos --}}
      <div id="contenedorPrestamos" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
  @foreach ($prestamos as $p)
    @php
      $color = match($p->estado) {
        'Cancelado' => 'danger',
        'Activo' => 'success',
        'Vencido' => 'warning',
        default => 'secondary',
      };
    @endphp

    <div class="col prestamo-item"
     data-estado="{{ strtolower($p->estado) }}"
     data-creador="{{ strtolower($p->creado_por) }}"
     data-texto="{{ strtolower($p->recurso . ' ' . $p->nro_serie . ' ' . $p->asignado . ' ' . $p->creado_por) }}"
     data-fecha="{{ $p->fecha_creacion }}">
  <div class="card border-secondary shadow-sm h-100 p-1">
    <div class="card-body p-2">
      <h6 class="card-title mb-1 fs-6">{{ $p->recurso }}</h6>
      <p class="card-text mb-1 small">Serie: <strong>{{ $p->nro_serie }}</strong></p>
      <p class="card-text mb-1 small">Asignado a: {{ $p->asignado }}</p>
      <p class="card-text mb-1 small">Creado por: {{ $p->creado_por }}</p>
      <p class="card-text mb-1 small">
        Fecha: {{ \Carbon\Carbon::parse($p->fecha_creacion)->format('d/m/Y H:i') }}
      </p>
      <p class="card-text mb-0 small">
        Estado: <span class="badge bg-{{ $color }}">{{ $p->estado }}</span>
      </p>
      <a href="{{ route('prestamos.edit', $p->id) }}" class="btn btn-editar w-100 mt-2">
        <i class="bi bi-pencil me-1"></i> Editar
      </a>
    </div>
  </div>
</div>

  @endforeach
</div>

<div class="mt-4 d-flex justify-content-center">
  {{ $prestamos->links() }}
</div>

    </div>
  </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/prestamos.css') }}" rel="stylesheet">
@endpush
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const estadoSelect = document.getElementById('filtro-estado');
    const creadorSelect = document.getElementById('filtro-creador');
    const busquedaInput = document.getElementById('busqueda');
    const fechaInicioInput = document.getElementById('fecha-inicio');
    const fechaFinInput = document.getElementById('fecha-fin');
    const botonFiltrar = document.getElementById('btn-filtrar');
    const items = document.querySelectorAll('.prestamo-item');

    function filtrar() {
      const estado = estadoSelect.value.toLowerCase();
      const creador = creadorSelect.value.toLowerCase();
      const texto = busquedaInput.value.toLowerCase();
      const inicio = fechaInicioInput.value ? new Date(fechaInicioInput.value) : null;
      const fin = fechaFinInput.value ? new Date(fechaFinInput.value + 'T23:59:59') : null;

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

    // Activar búsqueda textual en vivo
    if (busquedaInput) {
      busquedaInput.addEventListener('input', filtrar);
    }

    // Activar filtros solo al hacer clic en el botón
    if (botonFiltrar) {
      botonFiltrar.addEventListener('click', filtrar);
    }
  });
</script>
@endpush

