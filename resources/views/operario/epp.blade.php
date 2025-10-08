<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel de Operario — Juan Pérez (OP-001)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  </head>
  <body class="bg-light">
    <header class="bg-white shadow-sm py-3 mb-4">
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h1 class="h4 mb-0">Panel de Operario</h1>
            <div class="text-muted small">Juan Pérez — ID: <strong>OP-001</strong></div>
          </div>
          <div class="text-end">
            <div class="small text-muted">Turno: <strong>Mañana</strong></div>
            <div class="small text-muted">Hora: <strong>09:30 AM</strong></div>
          </div>
        </div>
      </div>
    </header>

    <main class="container">
      <section class="mb-4">
        <div class="row g-2">
          <div class="col-auto">
            <button class="btn btn-primary">Solicitar</button>
          </div>
          <div class="col-auto">
            <button class="btn btn-outline-primary">Mis Herramientas</button>
          </div>
          <div class="col-auto">
            <button class="btn btn-outline-secondary">Mi EPP</button>
          </div>
          <div class="col-auto">
            <button class="btn btn-outline-danger">Devolver</button>
          </div>
        </div>
      </section>

      <section class="mb-4">
        <div class="card">
          <div class="card-body">
            <h2 class="card-title h6">Mi Equipo de Protección Personal</h2>
            <p class="text-muted mb-3">EPP asignado y su estado actual</p>

            <div class="row g-3">
              <div class="col-12 col-md-4">
                <div class="card h-100">
                  <div class="card-body">
                    <h3 class="h6 mb-1">Casco de Seguridad</h3>
                    <div class="small text-muted">Serie: <strong>CS-012</strong></div>
                    <div class="small text-muted">Entregado: <strong>2024-01-10</strong></div>
                    <span class="badge bg-success">Activo</span>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="card h-100">
                  <div class="card-body">
                    <h3 class="h6 mb-1">Guantes de Trabajo</h3>
                    <div class="small text-muted">Serie: <strong>GT-008</strong></div>
                    <div class="small text-muted">Entregado: <strong>2024-01-12</strong></div>
                    <span class="badge bg-success">Activo</span>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="card h-100">
                  <div class="card-body">
                    <h3 class="h6 mb-1">Chaleco Reflectivo</h3>
                    <div class="small text-muted">Serie: <strong>CR-003</strong></div>
                    <div class="small text-muted">Entregado: <strong>2024-01-10</strong></div>
                    <span class="badge bg-success">Activo</span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </section>

      <footer class="text-muted small text-center mt-4 mb-5">
        <div>SafeWork — Sistema de Gestión de Recursos y Seguridad</div>
      </footer>
    </main>
  </body>
</html>
