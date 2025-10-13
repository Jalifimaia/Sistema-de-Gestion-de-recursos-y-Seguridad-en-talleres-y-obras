<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Inventario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  @livewireStyles
</head>
<body class="bg-light">
  

  <div class="container my-4">
  @extends('layouts.app')

    <!-- Título -->
  <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
      <h2 class="mb-0">Gestión de Inventario</h2>
      <p class="text-muted mb-0">Control de herramientas y equipos de protección personal</p>
    </div>
    <div class="d-flex gap-3">
      <div class="card text-center shadow-sm" style="width: 10rem;">
        <div class="card-body p-2">
          <h5 class="fw-bold text-success mb-1">96%</h5>
          <small class="d-block">EPP Entregados</small>
          <small class="text-muted">23 de 24</small>
        </div>
      </div>
      <div class="card text-center shadow-sm" style="width: 10rem;">
        <div class="card-body p-2">
          <h5 class="fw-bold text-danger mb-1">3</h5>
          <small class="d-block">Alertas Activas</small>
          <small class="text-muted">Requieren atención</small>
        </div>
      </div>
    </div>
  </div>


    <!-- Acciones -->
    <div class="d-flex flex-wrap gap-2 mb-3">
      <a href="{{ route('recursos.create') }}" class="btn btn-primary">Agregar Elemento</a>
      <input type="text" id="buscador" class="form-control w-auto" placeholder="Buscar por nombre o serie...">
    </div>

    <!-- Filtro por categoría y estado -->
    <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
      <label class="form-label mb-0">Filtrar por:</label>
      <select id="filtroInventario" class="form-select w-auto">
        <option value="todos">Todos</option>
        <option value="herramienta">Herramientas</option>
        <option value="epp">EPP</option>
        <option value="reparacion">En reparación</option>
      </select>
    </div>


    <!-- Tabla de inventario -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Serie</th>
            <th>Categoría</th>
            <th>Descripción</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($recursos as $recurso)
          <tr>
            <td>{{ $recurso->id }}</td>
            <td>{{ $recurso->nombre }}</td>
            <td>
              @if ($recurso->serieRecursos->count())
                <select class="form-select w-auto d-inline-block me-2" onchange="mostrarEstado(this)">
                  <option value="">Seleccionar serie</option>
                  @foreach ($recurso->serieRecursos as $serie)
                    @php
                      $hoy = \Carbon\Carbon::today();
                      $vence = \Carbon\Carbon::parse($serie->fecha_vencimiento);
                      $dias_restantes = $vence->diffInDays($hoy, false);

                      $estado = match ($serie->id_estado) {
                          4 => 'Reparación',
                          default => $vence->isPast() ? 'Vencido' : ($dias_restantes <= 7 ? 'Por vencer' : 'Vigente'),
                      };
                    @endphp

                    <option value="{{ $estado }}" data-serie="{{ $serie->nro_serie }}" data-fecha-vencimiento="{{ $serie->fecha_vencimiento }}">
                      {{ $serie->nro_serie }}
                    </option>
                  @endforeach
                </select>
                <span class="badge estado-vencimiento"></span>
              @else
                <span class="text-muted">Sin series</span>
              @endif
            </td>
            <td>{{ $recurso->categoria->nombre_categoria ?? 'Sin categoría' }}</td>
            <td>{{ $recurso->descripcion }}</td>
            <td>
              <a href="{{ route('recursos.edit', $recurso->id) }}" class="btn btn-sm btn-primary">Editar</a>
              <form action="{{ route('recursos.destroy', $recurso->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que quieres eliminar este EPP?')">Eliminar</button>
              </form>
              <a href="{{ route('serie_recurso.createConRecurso', $recurso->id) }}" class="btn btn-sm btn-info">Agregar Serie</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="text-muted small">Fecha: <strong id="today"></strong></div>
  </header>

  <!-- Acciones -->
  <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
    <a href="{{ route('recursos.create') }}" class="btn btn-orange">
      <i class="bi bi-plus-circle me-1"></i> Agregar Elemento
    </a>
    <button class="btn btn-outline-secondary">
      <i class="bi bi-download me-1"></i> Exportar
    </button>
    <input type="text" class="form-control form-control-sm" style="min-width: 240px;" placeholder="Buscar por nombre o serie...">
  </div>

  <!-- Tabla de inventario -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title fw-bold">Listado de Recursos</h5>
      <p class="text-muted small">Elementos registrados en el sistema</p>

      <div class="table-responsive">
        <table class="table-naranja align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Serie</th>
              <th>Categoría</th>
              <th>Descripción</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($recursos as $recurso)
            <tr>
              <td>{{ $recurso->id }}</td>
              <td>{{ $recurso->nombre }}</td>
              <td>
                @if ($recurso->serieRecursos->count())
                  <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm w-auto"
                            onchange="mostrarEstado(this)"
                            data-id="{{ $recurso->id }}">
                      <option value="" data-fecha="">Seleccionar serie</option>
                      @foreach ($recurso->serieRecursos as $serie)
                        @php
                          $hoy = \Carbon\Carbon::today();
                          $vence = \Carbon\Carbon::parse($serie->fecha_vencimiento);
                          $dias_restantes = $vence->diffInDays($hoy, false);
                          $estado = $vence->isPast() ? 'Vencido' : ($dias_restantes <= 7 ? 'Por vencer' : 'Vigente');
                        @endphp
                        <option value="{{ $estado }}" data-fecha="{{ $serie->fecha_vencimiento }}">
                          {{ $serie->nro_serie }}
                        </option>
                      @endforeach
                    </select>
                    <div id="estado-{{ $recurso->id }}"
                         class="px-2 py-1 border rounded small fw-semibold"
                         style="min-width: 160px; display: none;"></div>
                  </div>
                @else
                  <span class="text-muted">Sin series</span>
                @endif
              </td>
              <td>{{ $recurso->categoria->nombre_categoria ?? 'Sin categoría' }}</td>
              <td>{{ $recurso->descripcion }}</td>
              <td class="text-nowrap">
                <a href="{{ route('recursos.edit', $recurso->id) }}" class="btn btn-sm btn-orange">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('recursos.destroy', $recurso->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que quieres eliminar este EPP?')">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
                <a href="{{ route('serie_recurso.createConRecurso', $recurso->id) }}" class="btn btn-sm btn-outline-info">
                  <i class="bi bi-plus-circle"> Agregar serie</i>
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function mostrarEstado(select) {
    const selectedOption = select.options[select.selectedIndex];
    const estado = selectedOption.value;
    const fecha = selectedOption.dataset.fecha;
    const recursoId = select.dataset.id;
    const cuadro = document.getElementById('estado-' + recursoId);

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @livewireScripts
  <script src="{{ asset('js/filtroBusqueda.js') }}"></script>

</body>
</html>
