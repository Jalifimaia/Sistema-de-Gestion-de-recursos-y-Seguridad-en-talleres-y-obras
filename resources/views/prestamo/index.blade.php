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
            <input type="date" id="fecha-inicio" name="fecha_inicio" class="form-control filtro-destacado" value="{{ request('fecha_inicio') }}">
          </div>

          <div class="col-md-2">
            <label for="fecha-fin" class="form-label fw-bold">Hasta</label>
            <input type="date" id="fecha-fin" name="fecha_fin" class="form-control filtro-destacado" value="{{ request('fecha_fin') }}">
          </div>

          <!-- Botón aplicar filtros (envía al backend) -->
          <div class="col-md-2">
            <button type="submit" class="btn btn-naranja btn-filtro btn-sm w-100 d-flex align-items-center justify-content-center text-nowrap">
              <img src="{{ asset('images/filter.svg') }}" alt="Buscar" class="me-2" style="width: 16px; height: 16px;">
              Aplicar filtros
            </button>
          </div>

          <!-- Botón limpiar -->
          <div class="col-auto">
            <a href="{{ route('prestamos.index') }}"
              class="btn btn-secondary btn-limpiar btn-sm d-flex align-items-center justify-content-center"">
              <img src="{{ asset('images/clear.svg') }}" alt="Limpiar" style="width: 22px; height: 22px;" class="me-2">
              Limpiar filtro
            </a>
          </div>
        </div>

        <div class="row g-3 align-items-end">
          <!-- Buscar texto -->
          <div class="col-md-6">
            <div class="input-group" style="height: 46px;">
              <input
                type="text"
                id="busqueda"
                class="form-control buscador-destacado buscador-con-icono"
                placeholder="Buscar por nombre del recurso, subcategoría, serie, trabajador o creador">
            </div>
          </div>

          <!-- Estado -->
          <div class="col-md-3">
            <select id="filtro-estado" name="estado" onchange="this.form.submit()" class="form-select filtro-destacado" style="height: 46px;">
              <option value="">Todos los estados</option>
              <option value="Activo" {{ request('estado') == 'Activo' ? 'selected' : '' }}>Activo</option>
              <option value="Vencido" {{ request('estado') == 'Vencido' ? 'selected' : '' }}>Vencido</option>
              <option value="Devuelto" {{ request('estado') == 'Devuelto' ? 'selected' : '' }}>Devuelto</option>
            </select>
          </div>

          <!-- Creado por -->
          <div class="col-md-3">
            <select id="filtro-creador" name="creador" onchange="this.form.submit()" class="form-select filtro-destacado" style="height: 46px;">
              <option value="">Todos los usuarios</option>
              @foreach($usuarios as $usuario)
                <option value="{{ strtolower($usuario->name) }}" {{ request('creador') == strtolower($usuario->name) ? 'selected' : '' }}>
                  {{ $usuario->name }}
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
    // Forzar que "Cancelado" se muestre como "Devuelto"
    $estadoNombre = $p->estado === 'Cancelado' ? 'Devuelto' : $p->estado;

    // Badge por estado
    switch ($estadoNombre) {
      case 'Activo':   $color = 'success'; break;
      case 'Vencido':  $color = 'warning'; break;
      case 'Devuelto': $color = 'secondary'; break;
      default:         $color = 'secondary';
    }
  @endphp

<div class="col prestamo-item"
     data-estado="{{ strtolower($estadoNombre) }}"
     data-creador="{{ strtolower($p->creado_por) }}"
     data-asignado="{{ strtolower($p->asignado) }}"
     data-texto="{{ strtolower(($p->subcategoria ?? '') . ' ' . ($p->recurso ?? '') . ' ' . $p->nro_serie . ' ' . $p->asignado . ' ' . $p->creado_por) }}"
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
        Estado: <span class="badge bg-{{ $color }}">{{ $estadoNombre }}</span>
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


      <div class="mt-4 d-flex justify-content-between align-items-center">
        <div class="text-muted small">
          Mostrando {{ $prestamos->firstItem() }} a {{ $prestamos->lastItem() }} de {{ $prestamos->total() }} préstamos
        </div>
        <div>
          {{ $prestamos->links() }}
        </div>
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
        const creador = (creadorSelect?.value || '').toLowerCase();

const matchCreador = !creador ||
  item.dataset.creador.includes(creador) ||
  item.dataset.asignado.includes(creador);
        const matchEstado = !estado || item.dataset.estado === estado;
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
