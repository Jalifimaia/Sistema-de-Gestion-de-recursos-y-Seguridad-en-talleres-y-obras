@extends('layouts.app')

@section('title', 'Gestión de Inventario')

@section('content')
<div class="container py-4">
  <!-- Encabezado -->
  <header class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <h1 class="h4 fw-bold mb-1">Gestión de Inventario</h1>
      <p class="text-muted small">Control de herramientas y equipos de protección personal</p>
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

    // Ocultar opción "Seleccionar serie" después de elegir
    if (select.selectedIndex > 0) {
      select.options[0].disabled = true;
      select.options[0].hidden = true;
    }

    if (!estado || !fecha || !cuadro) {
      cuadro.style.display = 'none';
      cuadro.textContent = '';
      return;
    }

    const fechaV = new Date(fecha);
    const hoy = new Date();
    const diffDays = Math.ceil((fechaV - hoy) / (1000 * 60 * 60 * 24));

    const dd = String(fechaV.getDate()).padStart(2, '0');
    const mm = String(fechaV.getMonth() + 1).padStart(2, '0');
    const yyyy = fechaV.getFullYear();
    const fechaFormateada = `${dd}-${mm}-${yyyy}`;

    let texto = '';
    cuadro.style.color = '';
    cuadro.style.display = 'inline-block';

    if (estado === 'Vencido' || diffDays < 0) {
      texto = `Vencido - ${fechaFormateada}`;
      cuadro.style.color = 'red';
    } else if (estado === 'Por vencer' || diffDays <= 7) {
      texto = `Por vencer - ${fechaFormateada}`;
      cuadro.style.color = 'orange';
    } else {
      texto = `Vigente - ${fechaFormateada}`;
      cuadro.style.color = 'inherit';
    }

    cuadro.textContent = texto;
  }

  // Mostrar fecha actual en encabezado
  document.addEventListener('DOMContentLoaded', function () {
    const today = new Date();
    const fechaFormateada = today.toLocaleDateString('es-AR', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
    const fechaSpan = document.getElementById('today');
    if (fechaSpan) {
      fechaSpan.textContent = fechaFormateada;
    }
  });

</script>
@endpush

@endsection
