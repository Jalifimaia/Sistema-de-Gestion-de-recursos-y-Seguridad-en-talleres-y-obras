<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Reportes - Panel Supervisor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <main class="container my-4">
      <div class="text-center mb-4">
        <h2>Panel de Supervisor</h2>
        <p class="mb-1"><strong>Ana Rodríguez</strong> - Supervisor de Turno</p>
        <p>Turno: Mañana | Hora: 09:30 AM</p>
      </div>

      <div class="row text-center mb-4">
        <div class="col-md-3 mb-3">
          <div class="card">
            <div class="card-body">
              <h3>15</h3>
              <p>Trabajadores</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card">
            <div class="card-body">
              <h3>13</h3>
              <p>EPP Completo</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card">
            <div class="card-body">
              <h3>28</h3>
              <p>Herramientas en Uso</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card">
            <div class="card-body">
              <h3>0</h3>
              <p>Incidentes Hoy</p>
            </div>
          </div>
        </div>
      </div>


    @include('partials.barra_navegacion_supervisor')


      <h4 class="mb-3">Reportes Rápidos</h4>
      <p>Genera reportes para auditorías</p>

      <div class="d-flex flex-wrap gap-2 mb-5">
        <a href="#" class="btn btn-outline-secondary">Reporte Diario de EPP</a>
        <a href="#" class="btn btn-outline-secondary">Control de Herramientas</a>
        <a href="#" class="btn btn-outline-secondary">Incidentes del Mes</a>
        <a href="#" class="btn btn-outline-secondary">Estadísticas de Seguridad</a>
      </div>

      <h5 class="mb-3">Resumen del Día</h5>
      <div class="card">
        <div class="card-body">
          <p><strong>Estado actual del turno</strong></p>
          <ul class="list-unstyled">
            <li>Trabajadores con EPP completo: <strong>13 / 15</strong></li>
            <li>Herramientas devueltas a tiempo: <strong>25 / 28</strong></li>
            <li>Incidentes registrados: <strong>0</strong></li>
            <li>Cumplimiento de seguridad: <strong>87%</strong></li>
          </ul>
        </div>
      </div>
    </main>
  </body>
</html>
