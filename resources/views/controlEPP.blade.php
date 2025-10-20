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

  <!-- Filtros -->
  <div class="mb-4 d-flex gap-2 flex-wrap">
    <input type="text" class="form-control" style="min-width: 240px;" placeholder="Buscar por nombre de Trabajador/EPP...">
    
    <!--<select class="form-select" style="min-width: 200px;">
      <option>Todos los sectores</option>
    </select>-->
  </div>

  <!-- Tabla de búsqueda de EPP -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title fw-bold">Estado de EPP por Trabajador</h5>
      <p class="text-muted small">Control detallado de equipos de protección personal asignados</p>
      <div class="table-responsive">

        <!-- Contenedor dinámico para la tabla generada por JS -->
        <div id="tablaChecklistContainer" class="table-responsive mt-4"></div>
      
      </div>
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
    const tablaContainer = document.querySelector('#tablaChecklistContainer');

    let timeout = null;
    inputBuscar.addEventListener('keyup', function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        const valor = inputBuscar.value.trim();

        fetch("{{ route('matrizChecklist') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ nombre: valor })
        })
        .then(res => res.json())
        .then(data => {
          console.log(data);
          if (!data.success) return;

          const epps = data.epps; // array de strings
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

          tablaContainer.innerHTML = html;
        });
      }, 500);
    });
  });


document.addEventListener('click', function(e) {
  if (e.target.classList.contains('ver-detalle')) {
    const id = e.target.dataset.id;

    fetch(`/trabajador/${id}/detalle-epp`)
      .then(res => res.json())
      .then(data => {
        let html = `
          <p><strong>Nombre:</strong> ${data.trabajador.nombre}</p>
          <p><strong>Sector:</strong> ${data.trabajador.sector}</p>
          <h6>EPP asignados:</h6>
          <ul>
            ${data.epps.map(epp => `<li>${epp.nombre} (vence: ${epp.vencimiento})</li>`).join('')}
          </ul>
        `;
        document.querySelector('#detalleContenido').innerHTML = html;
        new bootstrap.Modal(document.getElementById('detalleModal')).show();
      });
  }
});

</script>

@endpush
