@extends('layouts.app')

@section('title', 'Control de EPP y Seguridad')

@section('content')
<div class="container py-4">
  <!-- Encabezado -->
  <header class="row mb-4">
    <div class="col-md-8">
      <h1 class="h4 fw-bold mb-1">Control de EPP y Seguridad</h1>
      <p class="text-muted small">Gestión de equipos de protección personal y checklist de seguridad</p>
    </div>
    <div class="col-md-4 text-md-end text-muted small">
      Fecha: <strong id="today" class="text-nowrap"></strong>
    </div>
  </header>

  <!-- Tarjetas resumen -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">Checklist Diario</h6>
          <p class="fw-bold mb-0">Asignar EPP</p>
          <small class="text-muted">Cumplimiento General</small>
          <h4 class="mt-2 text-primary">85%</h4>
          <small class="text-muted">Promedio del taller</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">EPP Vencidos</h6>
          <h4 class="text-danger">8</h4>
          <small class="text-muted">Requieren reemplazo</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">Checklist Hoy</h6>
          <h4 class="text-warning">3/4</h4>
          <small class="text-muted">Trabajadores verificados</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">Próximos Vencimientos</h6>
          <h4 class="text-orange">12</h4>
          <small class="text-muted">En los próximos 30 días</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="mb-4 d-flex gap-2 flex-wrap">
    <input type="text" class="form-control" style="min-width: 240px;" placeholder="Buscar por nombre...">
    <select class="form-select" style="min-width: 200px;">
      <option>Todos los sectores</option>
    </select>
  </div>

  <!-- Tabla de estado -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title fw-bold">Estado de EPP por Trabajador (4)</h5>
      <p class="text-muted small">Control detallado de equipos de protección personal asignados</p>

      <div class="table-responsive">
        <table class="table-naranja align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Trabajador</th>
              <th>Cargo</th>
              <th>Casco</th>
              <th>Guantes</th>
              <th>Anteojos</th>
              <th>Arnés</th>
              <th>Chaleco</th>
              <th>Cumplimiento</th>
              <th>%</th>
              <th>Último Checklist</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Carlos Mendez</td>
              <td>Soldador - Producción A</td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-danger fw-bold" style="font-size: 1.2rem;">✗</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-secondary fw-bold" style="font-size: 1.2rem;">–</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td><span class="badge bg-success">Bueno</span></td>
              <td>80%</td>
              <td>8/12/2024</td>
              <td><button class="btn btn-sm btn-orange">Ver</button></td>
            </tr>
            <tr>
              <td>Ana García</td>
              <td>Técnica Mecánica - Mantenimiento</td>
              <td class="text-center"><span class="text-danger fw-bold" style="font-size: 1.2rem;">✗</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-danger fw-bold" style="font-size: 1.2rem;">✗</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-danger fw-bold" style="font-size: 1.2rem;">✗</span></td>
              <td><span class="badge bg-warning">Deficiente</span></td>
              <td>60%</td>
              <td>7/12/2024</td>
              <td><button class="btn btn-sm btn-orange">Ver</button></td>
            </tr>
            <tr>
              <td>Roberto Silva</td>
              <td>Operario General - Producción B</td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-secondary fw-bold" style="font-size: 1.2rem;">–</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td><span class="badge bg-primary">Excelente</span></td>
              <td>100%</td>
              <td>8/12/2024</td>
              <td><button class="btn btn-sm btn-orange">Ver</button></td>
            </tr>
            <tr>
              <td>María López</td>
              <td>Supervisora - Calidad</td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td class="text-center"><span class="text-success fw-bold" style="font-size: 1.2rem;">✓</span></td>
              <td><span class="badge bg-primary">Excelente</span></td>
              <td>100%</td>
              <td>8/12/2024</td>
              <td><button class="btn btn-sm btn-orange">Ver</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
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
