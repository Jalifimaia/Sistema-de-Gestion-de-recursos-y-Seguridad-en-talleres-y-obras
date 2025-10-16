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

  <!-- Acciones -->
    <div class="d-flex flex-wrap gap-2 mb-3">
      <a href="{{ route('recursos.create') }}" class="btn btn-orange">Agregar Elemento</a>
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

  <!-- Tabla -->
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
                          $vence = \Carbon\Carbon::parse($serie->fecha_vencimiento);
                          $dias_restantes = $vence->diffInDays(now(), false);
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
@endsection