@extends('layouts.app')

@section('title', 'Resumen Taller')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@endpush

@section('content')
<div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-4 protect-toggle">
      <div>
        <p class="text-muted mb-1 subir-bienvenida">
          Bienvenido, {{ auth()->user()->name }} <em>[{{ auth()->user()->rol->nombre_rol }}]</em>
        </p>

        <h1 class="h4 fw-bold mb-1 d-flex align-items-center gap-2">
          <img src="{{ asset('images/resumen.svg') }}" alt="Icono Resumen" style="height: 30px;">
          Resumen diario del taller
        </h1>

        <p class="text-muted texto-descriptivo-alerta">
          Estado general de las herramientas y de la seguridad de los trabajadores.
        </p>
      </div>
    <div class="col-md-4 text-md-end fecha-destacada d-flex align-items-center justify-content-md-end">
      <strong id="today" class="valor-fecha text-nowrap">07/11/2023 09:20:17</strong>
    </div>
  </header>

  <!-- Checklists del Día (desplegable) -->
<div class="accordion mb-4 mover-acordeon-arriba" id="accordionChecklists">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingChecklists">
      <button class="accordion-button collapsed encabezado-checklist" type="button" data-bs-toggle="collapse" data-bs-target="#collapseChecklists" aria-expanded="false" aria-controls="collapseChecklists">
        <img src="{{ asset('images/checkk.svg') }}" alt="Checklist Diario" class="icono-checklist me-2">
        <span>Checklists del día</span>
      </button>
    </h2>
    <div id="collapseChecklists" class="accordion-collapse collapse show" aria-labelledby="headingChecklists">
      <div class="accordion-body">
        @if($checklistsHoy->isEmpty())
          <div class="mensaje-checklist-vacio d-flex align-items-center">
            <img src="{{ asset('images/checknot.svg') }}" alt="Sin checklist" class="icono-checknot me-2">
            <span>No se registraron checklists hoy.</span>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-bordered text-center table-checklist">
              <thead>
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
                    <td>{!! $c->anteojos ? '<span class="check-si">✔️</span>' : '<span class="check-no">❌</span>' !!}</td>
                    <td>{!! $c->botas ? '<span class="check-si">✔️</span>' : '<span class="check-no">❌</span>' !!}</td>
                    <td>{!! $c->chaleco ? '<span class="check-si">✔️</span>' : '<span class="check-no">❌</span>' !!}</td>
                    <td>{!! $c->guantes ? '<span class="check-si">✔️</span>' : '<span class="check-no">❌</span>' !!}</td>
                    <td>{!! $c->arnes ? '<span class="check-si">✔️</span>' : '<span class="check-no">❌</span>' !!}</td>
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
            <a href="{{ route('controlEPP') }}" class="btn btn-sm btn-ver-todo">
              Ver todos los checklist
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>


  <!-- Estadísticas principales -->
<div class="row g-4 mb-4 subir-cards">

    <div class="row g-4 mt-1 mb-4">
            <div class="col-md-4">
              <div class="card card-resumen card-resumen-cuadrada">
                <div class="card-body">
                  <button class="btn btn-link text-decoration-none p-0 m-0 w-100 h-100" data-bs-toggle="modal" data-bs-target="#modalUsuarios">
                    <img src="{{ asset('images/workers.svg') }}" alt="Trabajadores Activos" class="icono-card">
                    <h2 class="fw-bold mb-1">{{ $usuariosActivos }}</h2>
                    <p class="mb-0 titulo-card">Trabajadores Activos</p>
                    <small class="text-muted subtitulo-card">Registrados en el sistema</small>
                  </button>
                </div>
              </div>
            </div>


      <!-- Herramientas en Uso -->
      <div class="col-md-4">
        <div class="card card-resumen card-resumen-cuadrada">
          <div class="card-body">
            <button class="btn btn-link text-decoration-none p-0 m-0 w-100 h-100" data-bs-toggle="modal" data-bs-target="#modalHerramientas">
              <img src="{{ asset('images/herra.svg') }}" alt="Herramientas en Uso" class="icono-card">
              <h2 class="fw-bold mb-1">{{ $herramientasEnUso }}</h2>
              <p class="mb-0 titulo-card">Herramientas en Uso</p>
              <small class="text-muted subtitulo-card">de {{ $herramientasTotales }} disponibles</small>
            </button>
          </div>
        </div>
      </div>


      <!-- Alertas Activas -->
      <div class="col-md-4">
        <div class="card card-resumen card-resumen-cuadrada">
          <div class="card-body">
            <button class="btn btn-link text-decoration-none p-0 m-0 w-100 h-100" data-bs-toggle="modal" data-bs-target="#modalAlertas">
              <img src="{{ asset('images/alertas.svg') }}" alt="Alertas Activas" class="icono-card">
              <h2 class="fw-bold mb-1">{{ $alertasActivas }}</h2>
              <p class="mb-0 titulo-card">Alertas Activas</p>
              <small class="text-muted subtitulo-card">Requieren atención</small>
            </button>
          </div>
        </div>
      </div>
        </div>
</div>

  <!-- Modales -->
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


  <!-- Alertas y gráfico -->
  <div class="row g-4 mt-2 subir-alertas">
  <!-- Alertas Prioritarias -->
  <div class="col-md-6">
    <div class="card card-resumen h-100 card-alerta">
      <div class="card-body">
        <h5 class="card-title fw-bold mb-2 d-flex align-items-center titulo-alerta">
          <img src="{{ asset('images/alertaP.svg') }}" alt="Alerta Prioritaria" class="icono-alertaP me-2 animar-pulso">
          <span>Alertas Prioritarias</span>
        </h5>
        <p class="text-muted small mb-3">Situaciones que requieren atención inmediata</p>

        <div class="accordion" id="alertasPrioritarias">
          <!-- Stock Bajo -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingStock">
              <button class="accordion-button collapsed alerta-naranja" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStock" aria-expanded="false" aria-controls="collapseStock">
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
              <button class="accordion-button collapsed alerta-gris" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVencidos" aria-expanded="false" aria-controls="collapseVencidos">
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
              <button class="accordion-button collapsed alerta-rojo" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDevolucion" aria-expanded="false" aria-controls="collapseDevolucion">
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
    <div class="card card-resumen h-100 card-alerta">
      <div class="card-body">
        <h5 class="card-title fw-bold mb-2 titulo-alerta">Estado General del Inventario</h5>
        <canvas id="graficoInventario" class="grafico-inventario"></canvas>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const fechaElemento = document.getElementById('today');
    if (fechaElemento) {
      const ahora = new Date();

      const dia = String(ahora.getDate()).padStart(2, '0');
      const mes = String(ahora.getMonth() + 1).padStart(2, '0');
      const año = ahora.getFullYear();

      const horas = String(ahora.getHours()).padStart(2, '0');
      const minutos = String(ahora.getMinutes()).padStart(2, '0');
      const segundos = String(ahora.getSeconds()).padStart(2, '0');

      fechaElemento.textContent = `${dia}/${mes}/${año} ${horas}:${minutos}:${segundos}`;
    }

    // Gráfico de barras
    const canvas = document.getElementById('graficoInventario');
    if (canvas) {
      const ctx = canvas.getContext('2d');
      new Chart(ctx, {
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
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1 }
            }
          }
        }
      });
    }
  });
</script>
@endpush
