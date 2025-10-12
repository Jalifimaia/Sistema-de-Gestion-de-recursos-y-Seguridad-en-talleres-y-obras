<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel del trabajador - SafeWork</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <header class="bg-light border-bottom mb-4">
      <div class="container py-3 d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0">Panel del trabajador</h4>
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
        <a href="{{ url('/operario/solicitar') }}" class="btn {{ request()->is('operario/solicitar') ? 'btn-primary' : 'btn-outline-primary' }}">Solicitar</a>
        <a href="{{ url('/operario/mis-herramientas') }}" class="btn {{ request()->is('operario/mis-herramientas') ? 'btn-primary' : 'btn-outline-secondary' }}">Mis Herramientas</a>
        <a href="{{ url('/operario/epp') }}" class="btn {{ request()->is('operario/epp') ? 'btn-primary' : 'btn-outline-secondary' }}">Mi EPP</a>
        <a href="{{ url('/operario/devolver') }}" class="btn {{ request()->is('operario/devolver') ? 'btn-primary' : 'btn-outline-primary' }}">Devolver</a>
      </div>

      <section>
        <h5>Herramientas Disponibles</h5>
        <p class="text-muted">Selecciona las herramientas que necesitas</p>

        <div class="row row-cols-1 row-cols-md-2 g-3">

          <div class="col">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="card-title">Amoladora Angular</h6>
                <p class="mb-1">Categoria: <small class="text-muted">Herramientas Eléctricas</small></p>
                <p class="mb-2"><strong>3 disponibles</strong></p>
                <a href="#" class="btn btn-sm btn-success">Solicitar</a>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="card-title">Destornillador Eléctrico</h6>
                <p class="mb-1">Categoria: <small class="text-muted">Herramientas Eléctricas</small></p>
                <p class="mb-2"><strong>5 disponibles</strong></p>
                <a href="#" class="btn btn-sm btn-success">Solicitar</a>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="card-title">Llave Inglesa 12"</h6>
                <p class="mb-1">Categoria: <small class="text-muted">Herramientas Manuales</small></p>
                <p class="mb-2"><strong>8 disponibles</strong></p>
                <a href="#" class="btn btn-sm btn-success">Solicitar</a>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="card-title">Nivel de Burbuja</h6>
                <p class="mb-1">Categoria: <small class="text-muted">Instrumentos de Medición</small></p>
                <p class="mb-2"><strong>2 disponibles</strong></p>
                <a href="#" class="btn btn-sm btn-success">Solicitar</a>
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
