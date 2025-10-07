<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Control de Herramientas - Panel Supervisor</title>
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
    

      <h4 class="mb-3">Control de Herramientas</h4>
      <p>Herramientas pendientes de devolución y próximas a vencer</p>

      <div class="row">
        <div class="col-md-4 mb-3">
          <div class="card border-warning">
            <div class="card-body">
              <h5>Taladro Bosch TB-001</h5>
              <p>Trabajador: Juan Pérez</p>
              <p>Vence: 2024-01-16</p>
              <span class="badge bg-warning text-dark">Vence hoy</span>
              <div class="mt-2">
                <a href="#" class="btn btn-sm btn-outline-secondary">Notificar</a>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card border-info">
            <div class="card-body">
              <h5>Amoladora AG-003</h5>
              <p>Trabajador: Carlos López</p>
              <p>Vence: 2024-01-17</p>
              <span class="badge bg-info text-dark">Próximo a vencer</span>
              <div class="mt-2">
                <a href="#" class="btn btn-sm btn-outline-secondary">Notificar</a>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card border-danger">
            <div class="card-body">
              <h5>Martillo MN-005</h5>
              <p>Trabajador: María García</p>
              <p>Vence: 2024-01-14</p>
              <span class="badge bg-danger">Vencido</span>
              <div class="mt-2">
                <a href="#" class="btn btn-sm btn-outline-secondary">Notificar</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </body>
</html>
