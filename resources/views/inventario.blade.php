@extends('layouts.app')

@section('title', 'Gestión de Inventario')

@section('content')
<div class="container py-4">

  <!-- Encabezado -->
  <header class="row mb-4">
    <div class="col-md-8">
      <h1 class="h4 fw-bold mb-1">Gestión de Inventario</h1>
      <p class="text-muted small">Control de herramientas y equipos de protección personal</p>
    </div>
    <div class="col-md-4 text-md-end text-muted small">
      Fecha: <strong id="today" class="text-nowrap"></strong>
    </div>
  </header>

  <!-- Estado del Inventario -->
  <section id="estado-inventario" class="mb-4">
    <div class="card shadow border mt-4">
      <div class="card-header bg-white border-bottom">
        <h5 class="fw-bold mb-0">📊 Estado del Inventario</h5>
        <p class="text-muted small mb-0">Resumen general de herramientas y EPP</p>
      </div>
      <div class="card-body">
        <div class="row g-3">
          @php
            $estadoItems = [
              ['label' => 'Herramientas disponibles', 'valor' => "$herramientasDisponibles/$herramientasTotales"],
              ['label' => 'EPP en stock', 'valor' => "$eppStock/$eppTotales"],
              ['label' => 'En reparación', 'valor' => $elementosReparacion],
              ['label' => 'EPP vencidos', 'valor' => $eppVencidos],
              ['label' => 'Elementos dañados', 'valor' => $elementosDañados],
            ];
          @endphp

          @foreach ($estadoItems as $item)
            <div class="col-6 col-md-4 col-lg-2">
              <div class="border rounded p-3 text-center h-100 bg-light d-flex flex-column justify-content-center shadow-sm" style="border-left: 5px solid #f57c00;">
                <div class="fw-semibold text-muted small">{{ $item['label'] }}</div>
                <div class="fs-5 fw-bold text-orange">{{ $item['valor'] }}</div>
              </div>
            </div>
          @endforeach

          <!-- Botón exportar -->
          <div class="col-6 col-md-4 col-lg-2 d-flex align-items-center justify-content-center">
            <a href="{{ route('inventario.exportar') }}" class="btn btn-orange btn-sm w-100">
              <i class="bi bi-download me-1"></i> Exportar CSV
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Botones superiores -->
  <div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('recursos.create') }}" class="btn btn-orange">Agregar recurso</a>
    <a href="{{ url('/series-qr') }}" class="btn btn-outline-secondary">📑 Ver todos los códigos QR</a>
  </div>

  <!-- Filtro y buscador -->
  <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <label class="form-label mb-0">Filtrar por:</label>
    <select id="filtroInventario" class="form-select w-auto">
      <option value="todos">Todos</option>
      <option value="herramienta">Herramientas</option>
      <option value="epp">EPP</option>
    </select>

    <input type="text" id="buscador" class="form-control w-auto ms-3" placeholder="Buscar por nombre...">
  </div>

  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title fw-bold">Listado de Recursos</h5>
      <p class="text-muted small">Elementos registrados en el sistema</p>

      <div class="table-responsive">
        <table class="table-naranja align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Descripción</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($recursos as $recurso)
            <tr>
              <td>{{ $recurso->nombre }}</td>
              <td>{{ optional($recurso->categoria)->nombre_categoria ?? 'Sin categoría' }}</td>
              <td>{{ $recurso->descripcion }}</td>
              <td class="text-nowrap">
                <a href="{{ route('recursos.edit', $recurso->id) }}" class="btn btn-sm btn-orange">
                  <i class="bi bi-pencil"></i>
                </a>

                <form action="{{ route('recursos.destroy', $recurso->id) }}" method="POST" class="d-inline eliminar-recurso-form" data-nombre="{{ $recurso->nombre }}" data-id="{{ $recurso->id }}">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="{{ $recurso->id }}">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>

                <a href="{{ route('serie_recurso.createConRecurso', $recurso->id) }}" class="btn btn-sm btn-secondary">
                  <i class="bi bi-plus-circle"> Agregar serie</i>
                </a>

                @if ($recurso->serieRecursos->count())
                  <button type="button"
                    class="btn btn-sm btn-info btn-ver-series"
                    data-nombre="{{ $recurso->nombre }}"
                    data-series='@json($recurso->serieRecursos)'
                    data-bs-toggle="modal"
                    data-bs-target="#modalSeries">
                    <i class="bi bi-eye"></i> Ver series
                  </button>
                @else
                  <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    disabled
                    style="opacity: 0.6; cursor: not-allowed;">
                    <i class="bi bi-eye-slash"></i> Sin series
                  </button>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ver Series -->
<div class="modal fade" id="modalSeries" tabindex="-1" aria-labelledby="modalSeriesLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalSeriesLabel">Series del recurso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
          <label for="buscadorSerie" class="form-label mb-0">Buscar serie:</label>
          <input type="text" id="buscadorSerie" class="form-control w-auto" placeholder="Ej: T123">

          <label for="filtroEstado" class="form-label mb-0 ms-3">Filtrar por estado:</label>
          <select id="filtroEstado" class="form-select w-auto">
            <option value="todos">Todos</option>
            <option value="Disponible">Disponible</option>
            <option value="Baja">Baja</option>
            <option value="Prestado">Prestado</option>
            <option value="Devuelto">Devuelto</option>
            <option value="Dañado">Dañado</option>
            <option value="En reparación">En reparación</option>
          </select>
        </div>

        <div class="table-responsive">
          <table class="table-naranja align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Serie</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody id="tablaSeriesBody">
              <!-- Se llena por JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/filtroBusqueda.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('modalConfirmDelete');
  const modalText = document.getElementById('modalConfirmDeleteText');
  const modalConfirmBtn = document.getElementById('modalConfirmDeleteBtn');
  const bsModal = new bootstrap.Modal(modalEl);
  let targetForm = null;

  document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      const form = this.closest('.eliminar-recurso-form');
      if (!form) return console.warn('Formulario de eliminación no encontrado para id', id);

      const nombre = form.dataset.nombre || `ID ${id}`;
      modalText.textContent = `¿Seguro que quieres eliminar este recurso "${nombre}"?`;
      targetForm = form;
      bsModal.show();
    });
  });

  modalConfirmBtn.addEventListener('click', function () {
    if (!targetForm) return;
    modalConfirmBtn.disabled = true;
    targetForm.submit();
  });

  modalEl.addEventListener('hidden.bs.modal', function () {
    targetForm = null;
    modalConfirmBtn.disabled = false;
    modalText.textContent = '¿Seguro que quieres eliminar este recurso?';
  });

  // Filtro por categoría
  const filtroSelect = document.getElementById('filtroInventario');
  if (filtroSelect) {
    filtroSelect.addEventListener('change', function () {
      const filtro = this.value.toLowerCase();
      const filas = document.querySelectorAll('table tbody tr');

      filas.forEach(fila => {
        const categoria = fila.querySelector('td:nth-child(2)').textContent.trim().toLowerCase();
        const mostrar = (filtro === 'todos' || categoria.includes(filtro));
        fila.classList.toggle('oculto', !mostrar);
      });
    });

    filtroSelect.dispatchEvent(new Event('change'));
  }


  // ==========================
  // Buscador por nombre
  // ==========================
  const buscador = document.getElementById('buscador');
  if (buscador) {
    buscador.addEventListener('input', function () {
      const filtro = this.value.toLowerCase();
      const filas = document.querySelectorAll('table tbody tr');

      filas.forEach(fila => {
        const nombre = fila.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
        fila.style.display = nombre.includes(filtro) ? '' : 'none';
      });
    });
  }

  // ==========================
  // Modal Ver Series
  // ==========================
  const modalTitle = document.getElementById('modalSeriesLabel');
  const tablaBody = document.getElementById('tablaSeriesBody');
  const buscadorSerie = document.getElementById('buscadorSerie');
  const filtroEstado = document.getElementById('filtroEstado');

  document.querySelectorAll('.btn-ver-series').forEach(btn => {
    btn.addEventListener('click', function () {
      const nombre = this.dataset.nombre;
      const series = JSON.parse(this.dataset.series || '[]');

      modalTitle.textContent = `Series del recurso: ${nombre}`;
      tablaBody.innerHTML = '';
      buscadorSerie.value = '';
      filtroEstado.value = 'todos';

      if (!series.length) {
        tablaBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No hay series registradas</td></tr>';
        return;
      }

      series.forEach(serie => {
        const nroSerie = serie.nro_serie || (serie.codigo?.codigo_base ?? 'SIN-CODIGO') + '-' + String(serie.correlativo ?? '00').padStart(2, '0');
        const estado = serie.estado?.nombre_estado || serie.nombre_estado || 'Sin estado';

        const fila = document.createElement('tr');
        fila.innerHTML = `<td>${nroSerie}</td><td>${estado}</td>`;
        fila.dataset.serie = nroSerie.toLowerCase();
        fila.dataset.estado = estado.toLowerCase();
        tablaBody.appendChild(fila);
      });
    });
  });

  function aplicarFiltrosModal() {
    const texto = buscadorSerie.value.toLowerCase();
    const estadoSeleccionado = filtroEstado.value.toLowerCase();
    const filas = tablaBody.querySelectorAll('tr');

    filas.forEach(fila => {
      const serie = fila.dataset.serie || '';
      const estado = fila.dataset.estado || '';
      const coincideSerie = serie.includes(texto);
      const coincideEstado = (estadoSeleccionado === 'todos') || (estado === estadoSeleccionado);
      fila.style.display = (coincideSerie && coincideEstado) ? '' : 'none';
    });
  }

  buscadorSerie?.addEventListener('input', aplicarFiltrosModal);
  filtroEstado?.addEventListener('change', aplicarFiltrosModal);
});
</script>
@endpush
