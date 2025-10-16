@extends('layouts.app')

@section('title', 'Gestión de Inventario')

@section('content')
<div class="container py-4">
  <header class="row mb-4">
    <div class="col-md-8">
      <h1 class="h4 fw-bold mb-1">Gestión de Inventario</h1>
      <p class="text-muted small">Control de herramientas y equipos de protección personal</p>
    </div>
    <div class="col-md-4 text-md-end text-muted small">
      Fecha: <strong id="today" class="text-nowrap"></strong>
    </div>
  </header>

  <div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('recursos.create') }}" class="btn btn-orange">Agregar Elemento</a>
  </div>

  <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <label class="form-label mb-0">Filtrar por:</label>
    <select id="filtroInventario" class="form-select w-auto">
      <option value="todos">Todos</option>
      <option value="herramienta">Herramientas</option>
      <option value="epp">EPP</option>
      <option value="reparacion">En reparación</option>
    </select>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title fw-bold">Listado de Recursos</h5>
      <p class="text-muted small">Elementos registrados en el sistema</p>

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
                          $esEPP = strtolower($recurso->categoria->nombre_categoria) === 'epp';
                        @endphp
                        <option 
                          value="{{ $serie->id }}"
                          data-estado="{{ $estadoNombre }}"
                          data-talle="{{ $esEPP ? $serie->talle : '' }}"
                        >
                          {{ $serie->nro_serie }}{{ $esEPP && $serie->talle ? ' T:' . $serie->talle : '' }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                @else
                  <span class="text-muted">Sin series</span>
                @endif
              </td>
              <td>
                <div id="estado-{{ $recurso->id }}"
                     class="badge estado-vencimiento px-2 py-1 border rounded small fw-semibold"
                     style="min-width: 160px; display: none;"></div>
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
@endsection

@section('scripts')
  <script src="{{ asset('js/inventario.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/filtroBusqueda.js') }}?v={{ time() }}"></script>
@endsection

