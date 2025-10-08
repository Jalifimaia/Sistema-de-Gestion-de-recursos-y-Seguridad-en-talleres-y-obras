<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Devolver Herramientas — OP-001</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  </head>
  <body class="bg-light">
    <main class="container py-4">
      <header class="mb-4">
        <h1 class="h4 mb-1">Panel del trabajador</h1>
        <div class="text-muted small">Juan Pérez — ID: <strong>OP-001</strong></div>
        <div class="small text-muted">Turno: <strong>Mañana</strong> | Hora: <strong>09:30 AM</strong></div>
      </header>

      <section class="mb-4">
        <div class="row g-2">
          <div class="col-auto"><button class="btn btn-outline-primary">Solicitar</button></div>
          <div class="col-auto"><button class="btn btn-outline-secondary">Mis Herramientas</button></div>
          <div class="col-auto"><button class="btn btn-outline-secondary">Mi EPP</button></div>
          <div class="col-auto"><button class="btn btn-primary">Devolver</button></div>
        </div>
      </section>

      <section>
        <div class="card">
          <div class="card-body">
            <h2 class="card-title h5 mb-2">Devolver Herramientas</h2>
            <p class="text-muted mb-3">Escanea el código QR de la herramienta para devolverla</p>

            <form>
              <div class="mb-3">
                <label class="form-label">Escanea o ingresa el código QR</label>
                <input type="text" class="form-control" placeholder="Código QR o número de serie" />
              </div>
              <button type="button" class="btn btn-success w-100">Escanear</button>
              <p class="text-muted mt-3 small text-center">Apunta la cámara al código QR de la herramienta</p>
            </form>
          </div>
        </div>
      </section>
    </main>
  </body>
</html>
