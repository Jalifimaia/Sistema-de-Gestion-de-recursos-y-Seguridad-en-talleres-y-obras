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
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body d-flex flex-column justify-content-center">
          <h2 class="fw-bold">{{ $usuariosActivos }}</h2>
          <p class="mb-0">Trabajadores Activos</p>
          <small class="text-muted">Registrados en el sistema</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
    <div class="card shadow-sm text-center h-100">
      <div class="card-body d-flex flex-column justify-content-center">
        <h2 class="fw-bold">{{ $herramientasEnUso }}</h2>
        <p class="mb-0">Herramientas en Uso</p>
        <small class="text-muted">de {{ $herramientasTotales }} disponibles</small>
      </div>
    </div>
  </div>

    <div class="col-md-3">
    <div class="card shadow-sm text-center h-100">
      <div class="card-body d-flex flex-column justify-content-center">
        <h2 class="fw-bold text-success">{{ $porcentajeEntregado }}%</h2>
        <p class="mb-0">EPP Entregados</p>
        <small class="text-muted">{{ $eppEntregados }} de {{ $totalTrabajadores }} trabajadores</small>
      </div>
    </div>
  </div>

    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body d-flex flex-column justify-content-center">
          <h2 class="fw-bold text-danger">{{ $alertasActivas }}</h2>
          <p class="mb-0">Alertas Activas</p>
          <small class="text-muted">Requieren atención</small>
        </div>
      </div>
    </div>



  <!-- Alertas + Inventario -->
  <div class="row g-3 mb-4">
    <div class="col-md-8">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title fw-bold">Alertas Prioritarias</h5>
          <p class="text-muted small">Situaciones que requieren atención inmediata</p>

          @foreach($stockBajo as $stock)
            <div class="alert alert-warning mb-3">
              <strong>Stock bajo:</strong> {{ $stock->nombre }} <br>
              <small>Quedan {{ $stock->cantidad }} unidades disponibles</small>
            </div>
          @endforeach

          @foreach($alertasVencidos as $alerta)
            <div class="alert alert-danger mb-3">
              <strong>Vencido:</strong> {{ $alerta->recurso->nombre }} (Serie: {{ $alerta->nro_serie }}) <br>
              <small>Venció el {{ \Carbon\Carbon::parse($alerta->fecha_vencimiento)->format('d/m/Y') }}</small>
            </div>
          @endforeach

          @foreach($herramientasNoDevueltas as $item)
            <div class="alert alert-info mb-3">
              <strong>Herramienta no devuelta:</strong> {{ $item->recurso }} <br>
              <small>Serie {{ $item->nro_serie }} - {{ $item->trabajador }}</small>
            </div>
          @endforeach

        </div>
      </div>
    </div>


    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title fw-bold">Estado del Inventario</h5>
          <p class="text-muted small">Resumen de herramientas y EPP</p>

          <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between">
              Herramientas disponibles <span class="fw-bold">{{ $herramientasDisponibles }}/{{ $herramientasTotales }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              EPP en stock <span class="fw-bold">{{ $eppStock }}/{{ $eppTotales }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              Elementos en reparación <span class="fw-bold">{{ $elementosReparacion }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              EPP vencidos <span class="fw-bold">{{ $eppVencidos }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              Elementos dañados <span class="fw-bold">{{ $elementosDañados }}</span>
            </li>
          </ul>
          <div class="d-flex gap-2">
            <a href="{{ route('inventario') }}" class="btn btn-orange btn-sm flex-fill">
              <i class="bi bi-box-seam me-1"></i> Ver Inventario
            </a>
              <a href="{{ route('inventario.exportar') }}" class="btn btn-outline-secondary btn-sm flex-fill">
              <i class="bi bi-download me-1"></i> Exportar
            </a>
          </div>
        </div>
      </div>
    </div>


  <!-- Seguridad + Acciones -->
  <div class="d-flex flex-wrap gap-3 mb-4">
    <div class="card shadow-sm flex-fill">
      <div class="card-body">
        <h5 class="card-title fw-bold">Cumplimiento de Seguridad</h5>
        <p class="text-muted small">Estado actual de EPP por trabajador</p>

        <div class="row align-items-center mb-3">
          <div class="col-md-2 text-center">
            <h2 class="fw-bold text-success">96%</h2>
            <div class="text-muted small">Cumplimiento General</div>
          </div>
          <div class="col-md-10">
            <div class="row text-center">
              <div class="col">
                <h5 class="fw-bold">23</h5>
                <div class="text-muted small">Con EPP completo</div>
              </div>
              <div class="col">
                <h5 class="fw-bold text-danger">1</h5>
                <div class="text-muted small">EPP incompleto</div>
              </div>
              <div class="col-12 mt-2">
                <div class="progress">
                  <div class="progress-bar bg-success" style="width:96%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm flex-fill">
      <div class="card-body">
        <h5 class="card-title fw-bold">Acciones rápidas</h5>
        <div class="d-flex flex-column gap-2">
          <button class="btn btn-orange">
            <i class="bi bi-check2-circle me-1"></i> Checklist de Seguridad
          </button>
          <button class="btn btn-orange">
            <i class="bi bi-arrow-return-left me-1"></i> Registrar Devolución
          </button>
          <button class="btn btn-orange">
            <i class="bi bi-exclamation-triangle me-1"></i> Reportar Incidente
          </button>
          <button class="btn btn-orange">
            <i class="bi bi-person-plus me-1"></i> Nuevo Trabajador
          </button>
        </div>
      </div>
    </div>
  </div>

  <footer class="text-center text-muted small">
    Panel generado estáticamente. Integrar con backend para datos dinámicos.
  </footer>
</div>

<script>
  const today = new Date();
  document.getElementById('today').textContent =
    today.toLocaleDateString('es-AR',{year:'numeric',month:'short',day:'numeric'});
</script>
@endsection
