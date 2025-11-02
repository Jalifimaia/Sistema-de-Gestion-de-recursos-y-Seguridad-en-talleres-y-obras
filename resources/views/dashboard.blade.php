@extends('layouts.app')

@section('title', 'Resumen Taller')

@section('content')
<div class="container py-4">
  <header class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <p class="text-muted mb-1">Bienvenida, {{ auth()->user()->name }} (Rol: {{ auth()->user()->rol->nombre_rol }})</p>
        <h1 class="h4 fw-bold mb-1">Resumen del Taller</h1>
        <p class="text-muted small">Estado general de herramientas y seguridad</p>
    </div>
    <div class="text-muted small">Fecha: <strong id="today"></strong></div>
  </header>

 <!-- Estadísticas principales -->
<div class="row g-4 mb-5">
  <!-- Trabajadores activos -->
  <div class="col-md-4">
    <div class="card shadow-sm h-100 text-center" style="border-left: 4px solid #f57c00;">
      <div class="card-body d-flex flex-column justify-content-center">
        <button class="btn btn-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalUsuarios">
          <div class="mb-2"><i class="bi bi-person-badge fs-2"></i></div>
          <h2 class="fw-bold mb-1">{{ $usuariosActivos }}</h2>
          <p class="mb-0">Trabajadores Activos</p>
          <small class="text-muted">Registrados en el sistema</small>
        </button>
      </div>
    </div>
  </div>

  <!-- Herramientas en uso -->
  <div class="col-md-4">
    <div class="card shadow-sm h-100 text-center" style="border-left: 4px solid #f57c00;">
      <div class="card-body d-flex flex-column justify-content-center">
        <button class="btn btn-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalHerramientas">
          <div class="mb-2"><i class="bi bi-wrench fs-2"></i></div>
          <h2 class="fw-bold mb-1">{{ $herramientasEnUso }}</h2>
          <p class="mb-0">Herramientas en Uso</p>
          <small class="text-muted">de {{ $herramientasTotales }} disponibles</small>
        </button>
      </div>
    </div>
  </div>

  <!-- Alertas activas -->
  <div class="col-md-4">
    <div class="card shadow-sm h-100 text-center" style="border-left: 4px solid #f57c00;">
      <div class="card-body d-flex flex-column justify-content-center">
        <button class="btn btn-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalAlertas">
          <div class="mb-2"><i class="bi bi-exclamation-circle fs-2"></i></div>
          <h2 class="fw-bold mb-1">{{ $alertasActivas }}</h2>
          <p class="mb-0">Alertas Activas</p>
          <small class="text-muted">Requieren atención</small>
        </button>
      </div>
    </div>
  </div>
</div>

 <!-- Modal: Trabajadores Activos -->
<div class="modal fade" id="modalUsuarios" tabindex="-1" aria-labelledby="modalUsuariosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalUsuariosLabel">Trabajadores Activos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          @foreach($usuarios as $usuario)
            <div class="col-md-6">
              <div class="border rounded p-3 bg-light shadow-sm h-100">
                <h6 class="fw-bold mb-1">{{ $usuario->nombre }}</h6>
                <p class="mb-1 text-muted small">Email: <span class="fw-semibold">{{ $usuario->email }}</span></p>
                <p class="mb-0 text-muted small">Rol: <span class="fw-semibold">{{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</span></p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>


  <!-- Modal: Herramientas -->
<div class="modal fade" id="modalHerramientas" tabindex="-1" aria-labelledby="modalHerramientasLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalHerramientasLabel">Herramientas en Uso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          @foreach($herramientasUsadas as $herramienta)
            <div class="col-md-6">
              <div class="border rounded p-3 bg-light shadow-sm h-100">
                <h6 class="fw-bold mb-1">{{ $herramienta->recurso->nombre }}</h6>
                <p class="mb-1 text-muted small">Subcategoría: <span class="fw-semibold">{{ $herramienta->recurso->subcategoria->nombre }}</span></p>
                <p class="mb-0 text-muted small">Serie: <span class="fw-semibold">{{ $herramienta->nro_serie }}</span></p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>


  <!-- Modal: Alertas Activas -->
<div class="modal fade" id="modalAlertas" tabindex="-1" aria-labelledby="modalAlertasLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAlertasLabel">Alertas Activas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          @foreach($alertasLista as $alerta)
            @php
              $tipo = strtolower($alerta->tipo);
              $color = match(true) {
                str_contains($tipo, 'vencido') => 'danger',
                str_contains($tipo, 'stock') => 'warning',
                str_contains($tipo, 'devolución') => 'info',
                default => 'secondary'
              };
            @endphp
            <div class="col-md-6">
              <div class="alert alert-{{ $color }} shadow-sm mb-0 h-100">
                <strong>{{ $alerta->tipo }}</strong><br>
                <small>{{ $alerta->descripcion }}</small>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

 <div class="card shadow-sm mb-4" style="border-left: 4px solid #f57c00;">
  <div class="card-body">
    <h5 class="card-title fw-bold mb-3">📝 Checklists del Día</h5>

    @if($checklistsHoy->isEmpty())
      <p class="text-muted">No se registraron checklists hoy.</p>
    @else
      <div class="table-responsive">
  <table class="table table-bordered text-center">
    <thead class="table-light">
      <tr>
        <th>Trabajador</th>
        <th>Anteojos</th>
        <th>Botas</th>
        <th>Chaleco</th>
        <th>Guantes</th>
        <th>Arnés</th>
        <th>Altura</th>
        <th>Crítico</th>
        <th>Fecha</th>
        <th>Observaciones</th>
      </tr>
    </thead>
    <tbody>
      @foreach($checklistsHoy->take(5) as $c)
        <tr>
          <td>{{ $c->trabajador->name }}</td>
          <td>{!! $c->anteojos ? '<span style="color:green;">✔️</span>' : '<span style="color:red;">❌</span>' !!}</td>
          <td>{!! $c->botas ? '<span style="color:green;">✔️</span>' : '<span style="color:red;">❌</span>' !!}</td>
          <td>{!! $c->chaleco ? '<span style="color:green;">✔️</span>' : '<span style="color:red;">❌</span>' !!}</td>
          <td>{!! $c->guantes ? '<span style="color:green;">✔️</span>' : '<span style="color:red;">❌</span>' !!}</td>
          <td>{!! $c->arnes ? '<span style="color:green;">✔️</span>' : '<span style="color:red;">❌</span>' !!}</td>
          <td>{!! $c->es_en_altura ? '<span class="badge bg-danger">Sí</span>' : '<span class="badge bg-success">No</span>' !!}</td>
          <td>
            @if($c->critico)
              <span class="badge bg-danger">Crítico</span>
            @else
              <span class="badge bg-success">OK</span>
            @endif
          </td>
          <td>{{ \Carbon\Carbon::parse($c->hora)->format('d/m/Y H:i') }}</td>
          <td>{{ $c->observaciones ?? '—' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>


      <div class="mt-3 text-end">
        <a href="{{ route('controlEPP') }}" class="btn btn-sm btn-outline-primary">
          Ver todos los checklist
        </a>
      </div>
    @endif
  </div>
</div>



 <div class="row g-4 mt-4">
  <!-- Alertas Prioritarias -->
  <div class="col-md-6">
    <div class="card shadow-sm h-100" style="border-left: 4px solid #f57c00;">
      <div class="card-body">
        <h5 class="card-title fw-bold mb-2">Alertas Prioritarias</h5>
        <p class="text-muted small mb-3">Situaciones que requieren atención inmediata</p>

        <div class="accordion" id="alertasPrioritarias">
          <!-- Stock Bajo -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingStock">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStock" aria-expanded="false" aria-controls="collapseStock">
                Stock bajo ({{ $stockBajo->count() }})
              </button>
            </h2>
            <div id="collapseStock" class="accordion-collapse collapse" aria-labelledby="headingStock" data-bs-parent="#alertasPrioritarias">
              <div class="accordion-body">
                @foreach($stockBajo as $stock)
                  <div class="alert alert-warning mb-3">
                    <strong>{{ $stock->nombre }}</strong><br>
                    <small>Quedan {{ $stock->cantidad }} unidades disponibles</small>
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <!-- Vencidos -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingVencidos">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVencidos" aria-expanded="false" aria-controls="collapseVencidos">
                Recursos vencidos ({{ $alertasVencidos->count() }})
              </button>
            </h2>
            <div id="collapseVencidos" class="accordion-collapse collapse" aria-labelledby="headingVencidos" data-bs-parent="#alertasPrioritarias">
              <div class="accordion-body">
                @foreach($alertasVencidos as $alerta)
                  <div class="alert alert-danger mb-3">
                    <strong>{{ $alerta->recurso->nombre }}</strong> (Serie: {{ $alerta->nro_serie }})<br>
                    <small>Venció el {{ \Carbon\Carbon::parse($alerta->fecha_vencimiento)->format('d/m/Y') }}</small>
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <!-- Sin devolución -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingDevolucion">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDevolucion" aria-expanded="false" aria-controls="collapseDevolucion">
                Sin devolución ({{ $herramientasNoDevueltas->count() }})
              </button>
            </h2>
            <div id="collapseDevolucion" class="accordion-collapse collapse" aria-labelledby="headingDevolucion" data-bs-parent="#alertasPrioritarias">
              <div class="accordion-body">
                @foreach($herramientasNoDevueltas as $item)
                  <div class="alert alert-info mb-3">
                    <strong>{{ $item->recurso }}</strong><br>
                    <small>Serie {{ $item->nro_serie }} - {{ $item->trabajador }}</small>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Gráfico de estado general del inventario -->
  <div class="col-md-6">
    <div class="card shadow-sm h-100" style="border-left: 4px solid #f57c00;">
      <div class="card-body">
        <h5 class="card-title fw-bold mb-2">Estado General del Inventario</h5>
        <canvas id="graficoInventario" style="height:300px;"></canvas>
      </div>
    </div>
  </div>
</div>

</div>


<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Fecha actual
    const today = new Date();
    document.getElementById('today').textContent =
      today.toLocaleDateString('es-AR', { year: 'numeric', month: 'short', day: 'numeric' });

    // Gráfico de barras: Estado general del inventario
    const ctx1 = document.getElementById('graficoInventario').getContext('2d');
    new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: @json($labels),
        datasets: [{
          label: 'Cantidad de recursos',
          data: @json($valores),
          backgroundColor: ['#4caf50', '#2196f3', '#ff9800', '#f44336', '#9e9e9e']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'Estado general del inventario' }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        }
      }
    });

  </script>

@endsection
