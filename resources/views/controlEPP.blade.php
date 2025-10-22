@extends('layouts.app')

@section('title', 'Control de EPP y Seguridad')

@section('content')
<div class="container py-4">
  <!-- Fecha arriba a la derecha -->
  <div class="row mb-2">
      <div class="col-12 text-end text-muted small pt-1">
          Fecha: <strong id="today" class="text-nowrap"></strong>
      </div>
  </div>

  <!-- Título -->  
  <div class="row mb-4">
    <div class="col-12">
      <h1 class="text-center text-orange">🛡️ Control de EPP y Seguridad</h1>
      <p class="text-center text-muted small">Gestión de equipos de protección personal y checklist de seguridad</p>
    </div>
  </div>

  <!-- Tarjetas resumen -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">Checklist Diario</h6>
          <p class="fw-bold mb-0">Asignar EPP</p>
          <small class="text-muted">Cumplimiento General</small>
          <h4 class="mt-2 text-primary">{{ $porcentajeChecklist }}%</h4>
          <small class="text-muted">Promedio del taller</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">EPP Vencidos</h6>
          <h4 class="text-danger">{{ $eppVencidos }}</h4>
          <small class="text-muted">Requieren reemplazo</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">Checklist Hoy</h6>
          <h4 class="text-warning">{{ $checklistHoyTotal }}</h4>
          <small class="text-muted">Trabajadores verificados</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">Próximos Vencimientos</h6>
          <h4 class="text-orange">{{ $proximosVencimientos }}</h4>
          <small class="text-muted">En los próximos 30 días</small>
        </div>
      </div>
    </div>
  </div>

<!-- ✅ Buscador -->
<div class="mb-4 d-flex gap-2 flex-wrap">
  <input type="text" class="form-control" style="min-width: 240px;" placeholder="Buscar por nombre de Trabajador/EPP...">
  <!--<select class="form-select" style="min-width: 200px;">
    <option>Todos los sectores</option>
  </select>-->
</div>

<!-- ✅ Tabla de checklist diario -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="card-title fw-bold">Checklist Diario</h5>
    <p class="text-muted small">Cumplimiento de EPP por trabajador hoy</p>
    <div class="table-responsive">
      <table id="tablaChecklistDiario" class="table-naranja align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Trabajador</th>
            <th>Anteojos</th>
            <th>Botas</th>
            <th>Chaleco</th>
            <th>Guantes</th>
            <th>Arnés</th>
            <th>Altura</th>
            <th>Observaciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach($checklists as $c)
          <tr>
            <td>{{ $c->trabajador->name }}</td>
            <td>{!! $c->anteojos ? '<span style="color:green;">&#10004;</span>' : '<span style="color:red;">&#10006;</span>' !!}</td>
            <td>{!! $c->botas ? '<span style="color:green;">&#10004;</span>' : '<span style="color:red;">&#10006;</span>' !!}</td>
            <td>{!! $c->chaleco ? '<span style="color:green;">&#10004;</span>' : '<span style="color:red;">&#10006;</span>' !!}</td>
            <td>{!! $c->guantes ? '<span style="color:green;">&#10004;</span>' : '<span style="color:red;">&#10006;</span>' !!}</td>
            <td>{!! $c->arnes ? '<span style="color:green;">&#10004;</span>' : '<span style="color:red;">&#10006;</span>' !!}</td>
            <td>{!! $c->es_en_altura ? '<span class="badge bg-danger">Sí</span>' : '<span class="badge bg-success">No</span>' !!}</td>
            <td>{{ $c->observaciones }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>


<div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle del Trabajador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detalleContenido">
        Cargando...
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const inputBuscar = document.querySelector('input[placeholder="Buscar por nombre de Trabajador/EPP..."]');
  const tablaDiario = document.querySelector('#tablaChecklistDiario tbody');

  inputBuscar.addEventListener('keyup', function () {
    const filtro = inputBuscar.value.toLowerCase().trim();

    // Filtrar tabla checklist diario
    if (tablaDiario) {
      const filas = tablaDiario.querySelectorAll('tr');
      filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
      });
    }

    // Ya existente: búsqueda dinámica para la tabla generada por JS
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      fetch("{{ route('matrizChecklist') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ nombre: filtro })
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) return;

        const epps = data.epps;
        const matriz = data.data;

        let html = `
          <table class="table table-bordered text-center">
            <thead>
              <tr>
                <th style="background-color: #E36137; color: white;">Trabajador</th>
                ${epps.map(eppNombre => `<th style="background-color: #E36137; color: white;">${eppNombre}</th>`).join('')}
                <th style="background-color: #E36137; color: white;"> Acciones </th>
              </tr>
            </thead>
            <tbody>
              ${matriz.map(fila => `
                <tr>
                  <td>${fila.trabajador}</td>
                  ${epps.map(eppNombre => `
                    <td>
                      ${fila[eppNombre] === '✅'
                        ? '<span style="color: green;">&#10004;</span>'
                        : '<span style="color: red;">&#10006;</span>'}
                    </td>                  
                  `).join('')}
                    <td>
                      <button class="btn btn-sm btn-primary ver-detalle" data-id="${fila.id}">
                        VER
                      </button>
                    </td>                        
                </tr>
              `).join('')}
            </tbody>
          </table>
        `;

        document.querySelector('#tablaChecklistContainer').innerHTML = html;
      });
    }, 500);
  });
});
</script>


@endpush
