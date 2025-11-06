@extends('layouts.app')

@section('title', 'Gestión de Inventario')

@section('content')
<div class="container py-4">

@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
  </div>
@endif

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
        <p class="text-muted small mb-0">Resumen general de las herramientas y del equipo de protección personal</p>
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

  <!-- Filtro -->
  <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
  <label for="buscador" class="form-label mb-0 me-2 fw-semibold">Filtrar por:</label>

  <input type="text" id="buscador"
         class="form-control"
         placeholder="Buscar recurso por nombre..."
         style="width: 280px; max-width: 100%;">

  <select id="filtroInventario" class="form-select w-auto">
    <option value="todos">Todos</option>
    <option value="herramienta">Herramientas</option>
    <option value="epp">EPP</option>
    <option value="reparacion">En reparación</option>
    <option value="baja">Dado de baja</option>
    <option value="devueltos">Devueltos</option>
    <option value="sin-series">Sin series</option>
  </select>
</div>


  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title fw-bold">Listado de Recursos</h5>
      <p class="text-muted small">Recursos registrados en el sistema</p>

      <div class="table-responsive">
        <table class="table-naranja align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Nombre</th>
              <th>Serie</th>
              <th>Estado</th>
              <th>Categoría</th>
              <th>Descripción</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($recursos as $recurso)
            <tr>
              <td>{{ $recurso->nombre }}</td>
              <td>
                @if ($recurso->serieRecursos->count())
                  <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm w-auto"
                            onchange="mostrarEstado(this)"
                            data-id="{{ $recurso->id }}">
                      <option value="">Seleccionar serie</option>
                      @foreach ($recurso->serieRecursos as $serie)
                        @php
                          $estadoNombre = $serie->estado->nombre_estado ?? 'Sin estado';
                          $esEPP = strtolower(optional($recurso->categoria)->nombre_categoria ?? '') === 'epp';
                        @endphp
                        <option 
                          value="{{ $serie->id }}"
                          data-estado="{{ $estadoNombre }}"
                          data-talle="{{ $esEPP ? $serie->talle : '' }}"
                        >
                          {{ $serie->codigo->codigo_base ?? 'SIN-CODIGO' }}-{{ str_pad($serie->correlativo, 2, '0', STR_PAD_LEFT) }}{{ $esEPP && $serie->talle ? ' T:' . $serie->talle : '' }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                @else
                  <span class="text-muted">Sin series</span>
                @endif
              </td>
              <td>
                <!-- Estado dinámico -->
                <div id="estado-{{ $recurso->id }}"
                     class="badge estado-vencimiento px-2 py-1 border rounded small fw-semibold"
                     style="min-width: 160px; display: none;"></div>
              </td>
    
              <td>{{ optional($recurso->categoria)->nombre_categoria ?? 'Sin categoría' }}</td>
              <td>{{ $recurso->descripcion }}</td>
              <td class="text-nowrap">
  @php
    // Detectar si todas las series del recurso están dadas de baja
    $estadosSeries = $recurso->serieRecursos->pluck('estado.nombre_estado')->map(fn($e) => strtolower($e ?? ''))->toArray();
    $todasBaja = count($estadosSeries) > 0 && count(array_unique($estadosSeries)) === 1 && in_array('baja', $estadosSeries);
  @endphp

  @if (!$todasBaja)
    <!-- Botones habilitados -->
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
  @else
    <!-- Botones deshabilitados -->
    <button class="btn btn-sm btn-secondary" disabled title="Recurso dado de baja">
      <i class="bi bi-pencil"></i>
    </button>
    <button class="btn btn-sm btn-secondary" disabled title="Recurso dado de baja">
      <i class="bi bi-trash"></i>
    </button>
    <button class="btn btn-sm btn-secondary" disabled title="Recurso dado de baja">
      <i class="bi bi-plus-circle"></i>
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
@endsection

@push('scripts')
<script src="{{ asset('js/inventario.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/filtroBusqueda.js') }}?v={{ time() }}"></script>

<!-- Modal de Éxito -->
<div class="modal fade" id="modalSuccess" tabindex="-1" aria-labelledby="modalSuccessLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border border-success shadow">
      <div class="modal-header bg-light">
        <h5 class="modal-title text-success fw-bold" id="modalSuccessLabel">
          Acción completada
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <div class="text-center mb-2">
          <i class="bi bi-check-circle text-success fs-1"></i>
        </div>
        <p class="text-success fw-semibold mb-0">
          {{ session('success_modal') }}
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

@if (session('success_modal'))
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalSuccess = new bootstrap.Modal(document.getElementById('modalSuccess'));
  modalSuccess.show();
});
</script>
@endif


<!-- Modal de Error -->
<div class="modal fade" id="modalError" tabindex="-1" aria-labelledby="modalErrorLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border border-danger shadow">
      <div class="modal-header bg-light">
        <h5 class="modal-title text-danger fw-bold" id="modalErrorLabel">
          No se puede eliminar
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="text-center text-danger fw-semibold mb-0">
          {{ session('error_modal') }}
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

@if (session('error_modal'))
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalError = new bootstrap.Modal(document.getElementById('modalError'));
  modalError.show();
});
</script>
@endif



<!-- Modal Confirmar Eliminación (global) -->
<div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-labelledby="modalConfirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalConfirmDeleteLabel">Confirmar eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p id="modalConfirmDeleteText" class="mb-0">¿Seguro que quieres eliminar este recurso?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="modalConfirmDeleteBtn" class="btn btn-danger">Sí, eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('modalConfirmDelete');
  const modalText = document.getElementById('modalConfirmDeleteText');
  const modalConfirmBtn = document.getElementById('modalConfirmDeleteBtn');
  const bsModal = new bootstrap.Modal(modalEl);
  let targetForm = null;

  document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', function () {
      const form = this.closest('.eliminar-recurso-form');
      if (!form) return;
      const nombre = form.dataset.nombre || `ID ${form.dataset.id}`;
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
});
</script>
@endpush

