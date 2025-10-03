<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Resumen Taller</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
   @include('partials.barra_navegacion')
  <div class="container py-4">
    <header class="d-flex justify-content-between align-items-start mb-4">
      <div>
        <p>Bienvenida, {{ auth()->user()->name }} (Rol: {{ auth()->user()->rol->nombre_rol }})</p>
        <h1 class="h3 mb-1">Bienvenido, Juan</h1>
        <p class="text-muted mb-0">Resumen del estado actual del taller</p>
      </div>
      <div class="text-muted small">Fecha: <strong id="today"></strong></div>
    </header>

    {{-- Estadísticas --}}
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <h2 class="fw-bold">24</h2>
            <p class="mb-0">Trabajadores Activos</p>
            <small class="text-success">+2 desde ayer</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <h2 class="fw-bold">18</h2>
            <p class="mb-0">Herramientas en Uso</p>
            <small class="text-muted">de 45 disponibles</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <h2 class="fw-bold text-success">96%</h2>
            <p class="mb-0">EPP Entregados</p>
            <small class="text-muted">23 de 24 trabajadores</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <h2 class="fw-bold text-danger">3</h2>
            <p class="mb-0">Alertas Activas</p>
            <small class="text-muted">Requieren atención</small>
          </div>
        </div>
      </div>
    </div>

    {{-- Alertas + Inventario --}}
    <div class="row g-3 mb-4">
      <div class="col-md-8">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">Alertas Prioritarias</h5>
            <p class="text-muted small">Situaciones que requieren atención inmediata</p>

            <div class="alert alert-warning mb-3">
              <strong>Stock bajo:</strong> Cascos de seguridad <br>
              <small>Quedan 3 unidades disponibles</small>
            </div>
            <div class="alert alert-danger mb-3">
              <strong>Crítico:</strong> EPP vencido: Arnés de seguridad <br>
              <small>Trabajador: Carlos Mendez</small>
            </div>
            <div class="alert alert-info mb-0">
              <strong>Medio:</strong> Herramienta no devuelta <br>
              <small>Taladro #001 - Ana García</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">Estado del Inventario</h5>
            <p class="text-muted small">Resumen de herramientas y EPP</p>

            <ul class="list-group mb-3">
              <li class="list-group-item d-flex justify-content-between">
                Herramientas disponibles <span class="fw-bold">27/45</span>
              </li>
              <li class="list-group-item d-flex justify-content-between">
                EPP en stock <span class="fw-bold">85/120</span>
              </li>
              <li class="list-group-item d-flex justify-content-between">
                Elementos en reparación <span class="fw-bold">8</span>
              </li>
            </ul>
            <div class="d-flex gap-2">
              <button class="btn btn-primary btn-sm flex-fill">Ver Inventario Completo</button>
              <button class="btn btn-outline-secondary btn-sm flex-fill">Exportar</button>
            </div>
          </div>
        </div>
      </div>
    </div>


  <div class="d-flex flex-wrap gap-3 mb-4">

  <!-- Cumplimiento de Seguridad -->
  <div class="card shadow-sm flex-fill">
    <div class="card-body">
      <h5 class="card-title">Cumplimiento de Seguridad</h5>
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

  <!-- Acciones rápidas -->
  <div class="card shadow-sm flex-fill">
    <div class="card-body">
      <h5 class="card-title">Acciones rápidas</h5>
      <div class="d-flex flex-column gap-2">
        <button class="btn btn-primary">Checklist de Seguridad</button>
        <button class="btn btn-primary">Registrar Devolución</button>
        <button class="btn btn-primary btn-sm">Reportar Incidente</button>
        <button class="btn btn-primary btn-sm">Nuevo Trabajador</button>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
