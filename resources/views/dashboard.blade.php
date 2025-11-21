@extends('layouts.app')

@section('title', 'Resumen del taller')

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
    <!--  <div class="col-md-4 text-md-end fecha-destacada d-flex align-items-center justify-content-md-end">
      <strong id="today" class="valor-fecha text-nowrap">07/11/2023 09:20:17</strong>
    </div>-->
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
        <a href="{{ route('checklist.epp.tabla') }}" class="btn btn-sm btn-ver-todo">
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
                  <!-- Trabajadores Activos -->
                  <button class="btn btn-link text-decoration-none p-0 m-0 w-100 h-100" 
                  data-bs-toggle="modal" data-bs-target="#modalUsuariosActivos">
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
            <button class="btn btn-link text-decoration-none p-0 m-0 w-100 h-100" 
            data-bs-toggle="modal" data-bs-target="#modalHerramientasUso">
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

<!-- Modal: Alertas Activas -->
<div class="modal fade" id="modalAlertas" tabindex="-1" aria-labelledby="modalAlertasLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-header bg-modall text-white justify-content-center">
        <h5 class="modal-title" id="modalAlertasLabel">Alertas Activas</h5>
      </div>
      
      <div class="modal-body text-center">
        <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#modalVencidos">
          Vencidos
        </button>
        <button class="btn btn-warning mb-2" data-bs-toggle="modal" data-bs-target="#modalStock">
          Stock bajo
        </button>
        <button class="btn btn-info mb-2" data-bs-toggle="modal" data-bs-target="#modalDevoluciones">
          Sin devolución
        </button>
      </div>
      
    </div>
  </div>
</div>

<!-- Modal: Alertas Vencidas -->
<div class="modal fade" id="modalVencidos" tabindex="-1" aria-labelledby="modalVencidosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="modalVencidosLabel">Alertas Vencidas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <div class="modal-body">
        <div class="row g-3">
          @forelse($alertasVencidas as $alerta)
            <div class="col-md-6">
              <div class="alert alert-danger shadow-sm h-100">
                <strong>{{ $alerta->recurso->subcategoria->nombre }} - {{ $alerta->recurso->nombre }}</strong><br>
                <small>
                  Serie: {{ $alerta->nro_serie }} vencido el 
                  {{ \Carbon\Carbon::parse($alerta->fecha_vencimiento)->format('d/m/Y') }}
                </small>
              </div>
            </div>
          @empty
            <div class="text-center text-muted">No hay alertas vencidas.</div>
          @endforelse
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
          {{ $alertasVencidas->links() }}
        </div>
      </div>
      
    </div>
  </div>
</div>

<!-- Modal: Stock bajo -->
<div class="modal fade" id="modalStock" tabindex="-1" aria-labelledby="modalStockLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="modalStockLabel">Alertas de Stock bajo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <div class="modal-body">
        <div class="row g-3">
          @forelse($stockBajo as $item)
            <div class="col-md-6">
              <div class="alert alert-warning shadow-sm h-100">
                <strong>{{ $item->subcategoria }} - {{ $item->recurso }}</strong><br>
                <small>Quedan {{ $item->cantidad }} unidades</small>
              </div>
            </div>
          @empty
            <div class="text-center text-muted">No hay alertas de stock bajo.</div>
          @endforelse
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
          {{ $stockBajo->links() }}
        </div>
      </div>
      
    </div>
  </div>
</div>

<!-- Modal: Sin devolución -->
<div class="modal fade" id="modalDevoluciones" tabindex="-1" aria-labelledby="modalDevolucionesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="modalDevolucionesLabel">Alertas de Herramientas sin devolución</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <div class="modal-body">
        <div class="row g-3">
          @forelse($herramientasNoDevueltas as $item)
            <div class="col-md-6">
              <div class="alert alert-info shadow-sm h-100">
                <strong>{{ $item->subcategoria }} - {{ $item->recurso }}</strong>
                <small>Serie: {{ $item->nro_serie }} - {{ $item->trabajador }}</small>
              </div>
            </div>
          @empty
            <div class="text-center text-muted">No hay herramientas sin devolución.</div>
          @endforelse
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
          {{ $herramientasNoDevueltas->links() }}
        </div>
      </div>
      
    </div>
  </div>
</div>
<!-- Modal: Herramientas en uso -->
<div class="modal fade" id="modalHerramientasUso" tabindex="-1" aria-labelledby="modalHerramientasUsoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-header bg-modall text-white justify-content-center">
        <h5 class="modal-title" id="modalHerramientasUsoLabel">Herramientas en uso</h5>
        <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <div class="modal-body">
        <div class="row g-3">
          @forelse($herramientasEnUsoLista as $herramienta)
            <div class="col-md-6">
              <div class="alert alert-info shadow-sm h-100">
                <strong>{{ $herramienta->recurso->subcategoria->nombre }} - {{ $herramienta->recurso->nombre }}</strong><br>
                <small>Serie: {{ $herramienta->nro_serie }}</small>
              </div>
            </div>
          @empty
            <div class="text-center text-muted">No hay herramientas en uso.</div>
          @endforelse
        </div>

        <!-- Paginación -->
        <div class="mt-3">
          {{ $herramientasEnUsoLista->appends(['modal' => 'herramientasUso'])->links() }}
        </div>
      </div>
      
    </div>
  </div>
</div>

<!-- Modal: Trabajadores activos -->
<div class="modal fade" id="modalUsuariosActivos" tabindex="-1" aria-labelledby="modalUsuariosActivosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <!-- Encabezado gris con texto centrado -->
      <div class="modal-header bg-modall text-white justify-content-center position-relative">
        <h5 class="modal-title" id="modalUsuariosActivosLabel">Trabajadores activos</h5>
        <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          @forelse($usuariosActivosLista as $usuario)
            <div class="col-md-6">
              <div class="alert alert-success shadow-sm h-100">
                <strong>{{ $usuario->name }}</strong><br>
                <small>Email: {{ $usuario->email }}</small><br>
                <small>Rol: {{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</small>
              </div>
            </div>
          @empty
            <div class="text-center text-muted">No hay trabajadores activos.</div>
          @endforelse
        </div>

        <!-- Paginación -->
        <div class="mt-3">
          {{ $usuariosActivosLista->appends(['modal' => 'usuariosActivos'])->links() }}
        </div>
      </div>

    </div>
  </div>
</div>




  <!-- Alertas y gráfico -->
  <div class="row g-4 mt-2 subir-alertas">

  <!-- Gráfico de estado general del inventario -->
  <div class="row mt-4">
  <div class="col-12">
    <div class="card card-resumen h-100 card-alerta w-100">
      <div class="card-body">
        <h5 class="card-title fw-bold mb-2 titulo-alerta">Estado General del Inventario</h5>
        <canvas id="graficoInventario" class="grafico-inventario w-100" style="max-height:250px;"></canvas>
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

    const urlParams = new URLSearchParams(window.location.search);

    // Si la URL trae el parámetro de paginación de vencidos
    if (urlParams.has('vencidos_page')) {
        new bootstrap.Modal(document.getElementById('modalVencidos')).show();
    }

    // Si la URL trae el parámetro de paginación de stock bajo
    if (urlParams.has('stock_page')) {
        new bootstrap.Modal(document.getElementById('modalStock')).show();
    }

    // Si la URL trae el parámetro de paginación de herramientas sin devolución
    if (urlParams.has('herramientas_page')) {
        new bootstrap.Modal(document.getElementById('modalDevoluciones')).show();
    }

    // Si la URL trae el parámetro de paginación de trabajadores activos
    if (urlParams.has('usuarios_activos_page')) {
        new bootstrap.Modal(document.getElementById('modalUsuariosActivos')).show();
    }

    // Si la URL trae el parámetro de paginación de herramientas en uso
    if (urlParams.has('herramientas_uso_page')) {
        new bootstrap.Modal(document.getElementById('modalHerramientasUso')).show();
    }

  });
</script>
@endpush
