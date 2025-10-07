<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Registrar Incidente - Panel Supervisor</title>
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


      <h4 class="mb-3">Registrar Incidente</h4>
      <p>Documenta cualquier incidente o accidente ocurrido</p>

      <form>
        <div class="mb-3">
          <label for="trabajador" class="form-label">Trabajador Involucrado</label>
          <input type="text" class="form-control" id="trabajador" placeholder="Nombre del trabajador">
        </div>

        <div class="mb-3">
          <label for="tipo" class="form-label">Tipo de Incidente</label>
          <input type="text" class="form-control" id="tipo" placeholder="Ej: Accidente menor, Daño a herramienta">
        </div>

        <div class="mb-3">
          <label for="descripcion" class="form-label">Descripción del Incidente</label>
          <textarea class="form-control" id="descripcion" rows="4" placeholder="Describe detalladamente lo ocurrido..."></textarea>
        </div>

        <div class="mb-3">
          <label for="foto" class="form-label">Adjuntar Foto</label>
          <input class="form-control" type="file" id="foto">
        </div>

        <button type="submit" class="btn btn-primary">Registrar Incidente</button>
      </form>
    </main>
  </body>
</html>
