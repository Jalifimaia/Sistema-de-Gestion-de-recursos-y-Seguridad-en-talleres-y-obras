<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Control de EPP y Seguridad</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">


  <div class="container py-4">
        @extends('layouts.app')
    <h1 class="mb-3">Control de EPP y Seguridad</h1>
    <p class="text-muted">Gestión de equipos de protección personal y checklist de seguridad</p>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6 class="card-title">Checklist Diario</h6>
            <p class="fw-bold">Asignar EPP</p>
            <p class="mb-1">Cumplimiento General</p>
            <h4>85%</h4>
            <small class="text-muted">Promedio del taller</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6 class="card-title">EPP Vencidos</h6>
            <h4>8</h4>
            <small class="text-muted">Requieren reemplazo</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6 class="card-title">Checklist Hoy</h6>
            <h4>3/4</h4>
            <small class="text-muted">Trabajadores verificados</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6 class="card-title">Próximos Vencimientos</h6>
            <h4>12</h4>
            <small class="text-muted">En los próximos 30 días</small>
          </div>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <input type="text" class="form-control mb-2" placeholder="Buscar por nombre...">
      <select class="form-select">
        <option>Todos los sectores</option>
      </select>
    </div>

    <h5 class="mb-2">Estado de EPP por Trabajador (4)</h5>
    <p class="text-muted">Control detallado de equipos de protección personal asignados</p>


  <div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead class="table-dark">
      <tr>
        <th>Trabajador</th>
        <th>Cargo</th>
        <th>Casco</th>
        <th>Guantes</th>
        <th>Anteojos</th>
        <th>Arnés</th>
        <th>Chaleco</th>
        <th>Cumplimiento</th>
        <th>%</th>
        <th>Último Checklist</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Carlos Mendez</td>
        <td>Soldador - Producción A</td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-danger">Vencido</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-secondary">No asignado</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td>80%</td>
        <td>8/12/2024</td>
        <td><button class="btn btn-sm btn-primary">Ver</button></td>
      </tr>
      <tr>
        <td>Ana García</td>
        <td>Técnica Mecánica - Mantenimiento</td>
        <td><span class="badge bg-danger">Vencido</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-danger">Vencido</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-danger">Vencido</span></td>
        <td><span class="badge bg-warning">Deficiente</span></td>
        <td>60%</td>
        <td>7/12/2024</td>
        <td><button class="btn btn-sm btn-primary">Ver</button></td>
      </tr>
      <tr>
        <td>Roberto Silva</td>
        <td>Operario General - Producción B</td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-secondary">No asignado</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-primary">Excelente</span></td>
        <td>100%</td>
        <td>8/12/2024</td>
        <td><button class="btn btn-sm btn-primary">Ver</button></td>
      </tr>
      <tr>
        <td>María López</td>
        <td>Supervisora - Calidad</td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-success">Bueno</span></td>
        <td><span class="badge bg-primary">Excelente</span></td>
        <td>100%</td>
        <td>8/12/2024</td>
        <td><button class="btn btn-sm btn-primary">Ver</button></td>
      </tr>
    </tbody>
  </table>
</div>

  </div>
</body>
</html>
