<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Operario - Mis Herramientas</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <header class="bg-light border-bottom mb-4">
      <div class="container py-3 d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0">Panel de Operario</h4>
          <small class="text-muted">SafeWork - Sistema de Gestión de Recursos y Seguridad</small>
        </div>
        <div class="text-end">
          <div>Juan Pérez - <strong>ID: OP-001</strong></div>
          <div class="small text-muted">Turno: Mañana · Hora: 09:30 AM</div>
        </div>
      </div>
    </header>

    <main class="container">
      <div class="mb-3 d-flex gap-2">
        <a class="btn btn-primary" href="#">Solicitar</a>
        <a class="btn btn-outline-secondary" href="#">Mis Herramientas</a>
        <a class="btn btn-outline-secondary" href="#">Mi EPP</a>
        <a class="btn btn-outline-danger" href="#">Devolver</a>
      </div>

      <section>
        <h5>Herramientas en Mi Poder</h5>
        <p class="text-muted">Herramientas que tienes asignadas actualmente</p>

        <div class="row row-cols-1 row-cols-md-2 g-3">

          <div class="col">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="card-title">Taladro Bosch</h6>
                <p class="mb-1">Serie: <strong>TB-001</strong></p>
                <p class="mb-1">Prestado: <span class="text-muted">2024-01-15</span></p>
                <p class="mb-2"><span class="badge bg-success">En uso</span></p>
                <a href="#" class="btn btn-sm btn-outline-primary">Ver detalle</a>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="card-title">Martillo Neumático</h6>
                <p class="mb-1">Serie: <strong>MN-005</strong></p>
                <p class="mb-1">Prestado: <span class="text-muted">2024-01-14</span></p>
                <p class="mb-2"><span class="badge bg-warning text-dark">Vence hoy</span></p>
                <a href="#" class="btn btn-sm btn-outline-primary">Ver detalle</a>
              </div>
            </div>
          </div>

        </div>
      </section>

    </main>

    <footer class="mt-5 py-3 bg-light border-top">
      <div class="container text-center small text-muted">SafeWork · Gestión de Recursos y Seguridad</div>
    </footer>

  </body>
</html>
