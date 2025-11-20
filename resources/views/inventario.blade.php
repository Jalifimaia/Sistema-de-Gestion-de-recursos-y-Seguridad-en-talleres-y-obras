@extends('layouts.app')

@section('title', 'Gestión de Inventario')

@section('content')
<div class="container py-4">

  <!-- Encabezado -->
  <header class="row mb-4 protect-toggle">
    <div class="col-md-8">
      <h1 class="h4 fw-bold mb-1 d-flex align-items-center gap-2">
        <img src="{{ asset('images/inventario.svg') }}" alt="Icono Inventario" style="height: 35px;">
        Gestión de Inventario
      </h1>
      <p class="text-muted">Control de herramientas y equipos de protección personal</p>
    </div>

    <div class="col-md-4 text-md-end fecha-destacada d-flex align-items-center justify-content-md-end">
      <strong id="today" class="valor-fecha text-nowrap">{{ now()->format('d/m/Y H:i') }}</strong>
    </div>
  </header>

  <!-- Estado del Inventario -->
<section id="estado-inventario" class="mb-4">
  <div class="card shadow border custom-margin-top card-estado">
    <!-- Header con botón toggle -->
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <!-- Botón toggle collapse -->
        <button class="btn btn-sm btn-light p-1 d-flex justify-content-center align-items-center"
                style="width: 32px; height: 32px;"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#estadoCollapse"
                aria-expanded="false"
                aria-controls="estadoCollapse">
          <img src="{{ asset('images/down.svg') }}" alt="Toggle Inventario" class="down">
        </button>

        <!-- Título y subtítulo -->
        <div class="d-flex align-items-center gap-2">
          <span class="fw-bold">Estado del Inventario</span>
          <span class="text-muted small">- Resumen general de las herramientas y del equipo de protección personal</span>
        </div>
      </div>
    </div>

    <!-- Contenedor colapsable -->
    <div class="collapse" id="estadoCollapse">
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
            <a href="{{ route('inventario.exportar') }}" class="btn btn-csv btn-sm w-100 btn-csv">
              <i class="bi bi-download me-1"></i> Exportar CSV
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

  <!-- Botones: Agregar + QR -->
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <a href="{{ route('recursos.create') }}" class="btn btn-orange d-flex align-items-center gap-2">
    <img src="{{ asset('images/mas.svg') }}" alt="Icono agregar" style="height: 35px;">
    <span class="texto-boton-grande">Agregar recurso</span>
  </a>

  <a href="{{ url('/series-qr') }}" class="btn btn-outline-orange d-flex align-items-center gap-2">
    <img src="{{ asset('images/qr2.svg') }}" alt="QR icono" style="height: 35px;">
    <span class="texto-boton-grande btn-qr">Códigos QR</span>
  </a>
</div>

<!-- Filtro y buscador -->
<div class="d-flex flex-wrap align-items-center gap-3 mb-3">
  <!-- Buscar -->
  <input type="text" id="buscador"
         class="form-control buscador-destacado"
         style="height: 46px; flex: 1;"
         placeholder="Buscar por nombre, categoria, subcategoria o descripción...">

  <!-- Filtro -->
  <select id="filtroInventario"
          class="form-select filtro-destacado"
          style="height: 46px; width: auto;">
    <option value="todos">Todos</option>
    <option value="herramienta">Herramientas</option>
    <option value="epp">EPP</option>
  </select>
</div>

<!-- Tabla -->
<div class="card-body">
  <div class="table-responsive" style="overflow-x: auto; min-width: 100%;">
    <table class="table-naranja align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Categoría</th>
          <th>Subcategoría</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($recursos as $recurso)

        @php
          $estadoRecurso = strtolower(optional($recurso->estado)->nombre_estado ?? '');
          $estadosSeries = $recurso->serieRecursos->pluck('estado.nombre_estado')->map(fn($e) => strtolower($e ?? ''))->toArray();
          $todasBaja = count($estadosSeries) > 0 && count(array_unique($estadosSeries)) === 1 && in_array('baja', $estadosSeries);
        @endphp

        <tr data-recurso-id="{{ $recurso->id }}">
          <td class="recurso-nombre">{{ $recurso->nombre }}</td>
          <td class="recurso-categoria">{{ optional($recurso->categoria)->nombre_categoria ?? 'Sin categoría' }}</td>
          <td class="recurso-subcategoria">{{ optional($recurso->subcategoria)->nombre ?? 'Sin subcategoría' }}</td>
          <td class="recurso-descripcion">{{ $recurso->descripcion }}</td>
          <td class="text-nowrap acciones-cell">
            @if ($estadoRecurso === 'baja' || $todasBaja)
              <span class="badge bg-secondary fw-semibold">Dado de baja</span>
            @else
              <div class="d-flex align-items-center gap-2 flex-nowrap">
                <!-- Editar -->
                <a href="{{ route('recursos.edit', $recurso->id) }}" class="btn btn-sm btn-editar btn-accion-compact" title="Editar">
                  <i class="bi bi-pencil"></i>
                </a>

                <!-- Agregar serie -->
                <a href="{{ route('serie_recurso.createConRecurso', $recurso->id) }}" class="btn btn-sm btn-agregar-serie btn-accion-compact" title="Agregar serie">
                  <i class="bi bi-plus-circle"></i>
                </a>

                <!-- Ver series -->
                @if ($recurso->serieRecursos->count())
                  <button type="button"
                          class="btn btn-sm btn-ver-series btn-accion-compact"
                          data-nombre="{{ $recurso->nombre }}"
                          data-series="{{ $recurso->serieRecursos->toJson() }}"
                          data-bs-toggle="modal"
                          data-bs-target="#modalSeries"
                          title="Ver series">
                    <i class="bi bi-eye"></i>
                  </button>
                @else
                  <button type="button" class="btn btn-sm btn-outline-secondary btn-accion-compact" disabled title="Sin series">
                    <i class="bi bi-eye-slash"></i>
                  </button>
                @endif

                <!-- Dar de baja -->
                <form method="POST" action="{{ route('recursos.baja', $recurso->id) }}" class="marcar-baja-form" data-nombre="{{ $recurso->nombre }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-danger btn-marcar-baja btn-accion-compact" title="Dar de baja">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
              </div>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>


      <div class="d-flex justify-content-between align-items-center mt-3">
        <div id="infoPaginacion" class="text-muted small"></div>
        <ul id="paginacion" class="pagination mb-0"></ul>
      </div>
    </div>
  </div>

  <!-- Modal Confirmación baja (global para recurso) -->
  <div class="modal fade" id="modalConfirmBajaRecurso" tabindex="-1" aria-labelledby="modalConfirmBajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalConfirmBajaLabel">Confirmar baja del recurso</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p id="modalConfirmBajaText" class="mb-0">¿Seguro que querés marcar como baja este recurso?</p>
        </div>
        <div class="modal-footer">
          <button type="button" id="modalBajaCancel" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="modalBajaConfirm" class="btn btn-danger">Sí, marcar baja</button>
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
        <div class="mb-3 d-flex gap-2 align-items-center flex-wrap">
          <label for="buscadorSerie" class="form-label mb-0">Buscar serie:</label>
          <input type="text" id="buscadorSerie" class="form-control w-auto" placeholder="Ej: T123">
          <label for="filtroEstado" class="form-label mb-0 ms-2">Filtrar por estado:</label>
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
                <th>Color</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaSeriesBody"></tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <div id="infoPaginacionSeries" class="text-muted small"></div>
          <ul id="paginacionSeries" class="pagination mb-0"></ul>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection

@push('scripts')
<script src="{{ asset('js/filtroBusqueda.js') }}"></script>
<script src="{{ asset('js/cargarSeriesRecurso.js') }}"></script>
<script src="{{ asset('js/inventario-actions.js') }}"></script>


<!--<script>
document.addEventListener('DOMContentLoaded', function () {
  // Modal global para confirmar "marcar baja" de recurso
  const modalEl = document.getElementById('modalConfirmDelete');
  const modalText = document.getElementById('modalConfirmDeleteText');
  const modalConfirmBtn = document.getElementById('modalConfirmDeleteBtn');
  let bsModal;
  if (modalEl) bsModal = new bootstrap.Modal(modalEl);
  let targetForm = null;

  // Botón que dispara "marcar baja" (no borra)
  document.querySelectorAll('.btn-marcar-baja').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      const form = this.closest('.marcar-baja-form');
      if (!form) return console.warn('Formulario para marcar baja no encontrado', id);

      const nombre = form.dataset.nombre || `ID ${id}`;
      if (modalText && bsModal) {
        modalText.textContent = `¿Seguro que querés marcar como baja el recurso "${nombre}"?`;
        targetForm = form;
        bsModal.show();
      } else {
        // fallback: submit directo
        form.submit();
      }
    });
  });

  if (modalConfirmBtn) {
    modalConfirmBtn.addEventListener('click', function () {
      if (!targetForm) return;
      modalConfirmBtn.disabled = true;
      // submit: backend debe cambiar estado a "baja"
      targetForm.submit();
    });
  }

  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', function () {
      targetForm = null;
      if (modalConfirmBtn) modalConfirmBtn.disabled = false;
      if (modalText) modalText.textContent = '¿Seguro que quieres eliminar este recurso?';
    });
  }

  // Rehabilitar botones visuales (por si hay overlays)
  document.querySelectorAll('a.btn, button').forEach(btn => {
    btn.style.pointerEvents = 'auto';
    btn.style.position = 'relative';
    btn.style.zIndex = '10';
  });

  // Inicial trigger para que filtroBusqueda.js aplique estado inicial
  const filtroSelect = document.getElementById('filtroInventario');
  if (filtroSelect) {
    filtroSelect.dispatchEvent(new Event('change'));
  }
});
</script>-->
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('css/inventario.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@endpush
