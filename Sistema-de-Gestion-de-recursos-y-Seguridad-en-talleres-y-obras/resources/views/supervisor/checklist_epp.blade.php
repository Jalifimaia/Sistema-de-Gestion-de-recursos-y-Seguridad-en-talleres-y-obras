<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Checklist EPP - Panel Supervisor</title>
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

      <h4 class="mb-3">Checklist de Seguridad - Turno Mañana</h4>
      <p>Verifica que cada trabajador tenga su EPP completo</p>

      <div class="row">
        <div class="col-md-4 mb-3">
          <div class="card">
            <div class="card-body">
              <h5>Juan Pérez</h5>
              <p>Turno: Mañana</p>
              <span class="badge bg-success">Completo</span>
              <ul class="mt-2">
                <li>Casco</li>
                <li>Chaleco</li>
                <li>Guantes</li>
                <li>Calzado</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card">
            <div class="card-body">
              <h5>María García</h5>
              <p>Turno: Mañana</p>
              <span class="badge bg-danger">Incompleto</span>
              <ul class="mt-2">
                <li>Casco</li>
                <li>Chaleco</li>
                <li>Guantes</li>
                <li>Calzado</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card">
            <div class="card-body">
              <h5>Carlos López</h5>
              <p>Turno: Mañana</p>
              <span class="badge bg-success">Completo</span>
              <ul class="mt-2">
                <li>Casco</li>
                <li>Chaleco</li>
                <li>Guantes</li>
                <li>Calzado</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </main>
  </body>
</html>
