@extends('layouts.app')

@section('title', 'Préstamos')

@section('content')
<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white text-center">
      <h4 class="mb-0">Préstamos Registrados</h4>
    </div>

    <div class="card-body bg-white">
      <div class="mb-3 text-start">
        <a href="{{ route('prestamos.create') }}" class="btn btn-naranja">
          Nuevo Préstamo
        </a>
      </div>

      {{-- Filtros --}}
      <form method="GET" action="{{ route('prestamos.index') }}">
        <div class="row g-3 align-items-end mb-3">
          <!-- Fechas -->
          <div class="col-md-2">
            <label for="fecha-inicio" class="form-label fw-bold">Desde</label>
            <input type="date" id="fecha-inicio" name="fecha_inicio" class="form-control filtro-naranja" value="{{ request('fecha_inicio') }}">
          </div>

          <div class="col-md-2">
            <label for="fecha-fin" class="form-label fw-bold">Hasta</label>
            <input type="date" id="fecha-fin" name="fecha_fin" class="form-control filtro-naranja" value="{{ request('fecha_fin') }}">
          </div>

          <!-- Botón aplicar filtros (envía al backend) -->
          <div class="col-md-2">
            <button type="submit" class="btn btn-naranja btn-sm w-100 d-flex align-items-center justify-content-center text-nowrap">
              <img src="{{ asset('images/filter.svg') }}" alt="Buscar" class="me-2" style="width: 16px; height: 16px;">
              Aplicar filtros
            </button>
          </div>

          <!-- Botón limpiar -->
          <div class="col-auto">
            <a href="{{ route('prestamos.index') }}"
               class="btn btn-secondary btn-sm d-flex align-items-center justify-content-center"
               style="width: 42px; height: 42px; padding: 0;">
              <img src="{{ asset('images/clear.svg') }}" alt="Limpiar" style="width: 22px; height: 22px;">
            </a>
          </div>
        </div>

        <div class="row g-3 align-items-end">
          <!-- Buscar texto -->
          <div class="col-md-6">
            <label for="busqueda" class="form-label fw-bold">Buscar</label>
            <div class="input-group" style="height: 46px;">
              <input type="text" name="search" id="busqueda"
                     class="form-control filtro-naranja"
                     style="height: 100%;"
                     value="{{ request('search') }}"
                     placeholder="Buscar por recurso, serie, trabajador o creador">
              <button class="btn btn-naranja" type="submit">
                <img src="{{ asset('images/lupa.svg') }}" alt="Buscar" style="width: 16px; height: 16px;">
              </button>
            </div>
          </div>

          <!-- Estado -->
          <div class="col-md-3">
            <label for="filtro-estado" class="form-label fw-bold">Estado</label>
            <select id="filtro-estado" name="estado" onchange="this.form.submit()" class="form-select filtro-naranja" style="height: 46px;">
              <option value="">Todos</option>
              <option value="Cancelado" {{ request('estado') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
              <option value="Activo" {{ request('estado') == 'Activo' ? 'selected' : '' }}>Activo</option>
              <option value="Vencido" {{ request('estado') == 'Vencido' ? 'selected' : '' }}>Vencido</option>
              <option value="Devuelto" {{ request('estado') == 'Devuelto' ? 'selected' : '' }}>Devuelto</option>
            </select>
          </div>

          <!-- Creado por -->
          <div class="col-md-3">
            <label for="filtro-creador" class="form-label fw-bold">Creado por</label>
            <select id="filtro-creador" name="creador" onchange="this.form.submit()" class="form-select filtro-naranja" style="height: 46px;">
              <option value="">Todos</option>
              @foreach($usuarios as $nombre)
                <option value="{{ $nombre }}" {{ request('creador') == $nombre ? 'selected' : '' }}>
                  {{ $nombre }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </form>

      <br>

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
     data-texto="{{ strtolower(($p->subcategoria ?? '') . ' ' . $p->nro_serie . ' ' . $p->asignado . ' ' . $p->creado_por) }}"
     data-fecha="{{ $p->fecha_prestamo }}">
  <div class="card border-secondary shadow-sm h-100 p-1">
    <div class="card-body p-2">
      <h6 class="card-title mb-1 fs-6">
        {{ $p->subcategoria }}
        <small class="text-muted">
          ({{ $p->recurso ?? 'Sin marca' }})
        </small>
      </h6>
      <p class="card-text mb-1 small">Serie: <strong>{{ $p->nro_serie }}</strong></p>
      <p class="card-text mb-1 small">Asignado a: {{ $p->asignado }}</p>
      <p class="card-text mb-1 small">Creado por: {{ $p->creado_por }}</p>
      <p class="card-text mb-1 small">
        Fecha: {{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('d/m/Y H:i') }}
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

        {{-- Mensaje único de "sin resultados" --}}
        <div id="noResultadosMsg"
             class="col-12 alert alert-info text-center w-100 mt-3"
             style="{{ $prestamos->isEmpty() ? '' : 'display:none;' }}">
          No hay préstamos que coincidan con tu búsqueda
        </div>
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
    const contenedor = document.getElementById('contenedorPrestamos');
    const items = document.querySelectorAll('.prestamo-item');

    // Reutilizamos el mismo elemento de mensaje existente en el DOM
    let msg = document.getElementById('noResultadosMsg');

    function filtrar() {
      const estado = (estadoSelect?.value || '').toLowerCase();
      const creador = (creadorSelect?.value || '').toLowerCase();
      const texto = (busquedaInput?.value || '').toLowerCase();
      const inicio = fechaInicioInput?.value ? new Date(fechaInicioInput.value) : null;
      const fin = fechaFinInput?.value ? new Date(fechaFinInput.value + 'T23:59:59') : null;

      let visibles = 0;

      items.forEach(item => {
        const matchEstado = !estado || item.dataset.estado === estado;
        const matchCreador = !creador || item.dataset.creador.includes(creador);
        const matchTexto = !texto || item.dataset.texto.includes(texto);

        const fechaItem = item.dataset.fecha ? new Date(item.dataset.fecha) : null;
        const matchFecha =
          (!inicio || !fechaItem || fechaItem >= inicio) &&
          (!fin || !fechaItem || fechaItem <= fin);

        const mostrar = matchEstado && matchCreador && matchTexto && matchFecha;
        item.style.display = mostrar ? '' : 'none';
        if (mostrar) visibles++;
      });

      // Si no existe el elemento por alguna razón, lo creamos solo una vez
      if (!msg) {
        msg = document.createElement('div');
        msg.id = 'noResultadosMsg';
        msg.className = 'col-12 alert alert-info text-center w-100 mt-3';
        msg.textContent = 'No hay préstamos que coincidan con tu búsqueda';
        contenedor.appendChild(msg);
      }

      msg.style.display = (visibles === 0) ? '' : 'none';
    }

    // Búsqueda en vivo
    busquedaInput?.addEventListener('input', filtrar);
    // Fechas en vivo (opcional)
    fechaInicioInput?.addEventListener('change', filtrar);
    fechaFinInput?.addEventListener('change', filtrar);

    // Inicial: si el servidor ya devolvió 0 items, el mensaje queda visible; si hay items, lo ocultamos hasta que los filtros lo muestren
    filtrar();
  });
</script>
@endpush
