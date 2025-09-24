<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Usuarios y Roles</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    @include('partials.barra_navegacion')
  <div class="container my-4">

    <!-- Título -->
    <div class="mb-4">
      <h2>Gestión de Usuarios y Roles</h2>
      <p class="text-muted">Administración de usuarios, permisos y roles del sistema</p>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6 class="card-title">Nuevo Usuario</h6>
            <p class="card-text">Total Usuarios</p>
            <h3>5</h3>
            <small class="text-success">+1 este mes</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6 class="card-title">Usuarios Activos</h6>
            <h3>4</h3>
            <small class="text-muted">80% del total</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6 class="card-title">Administradores</h6>
            <h3>1</h3>
            <small class="text-muted">Acceso completo</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6 class="card-title">Último Acceso</h6>
            <p class="mb-0">Hoy</p>
            <small>14:30 - Juan Díaz</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Buscar -->
    <div class="mb-4">
      <input type="text" class="form-control" placeholder="Buscar por nombre o email...">
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <a class="nav-link active" href="#">Todos</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Usuarios (5)</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Roles y Permisos</a>
      </li>
    </ul>

    <!-- Tabla de usuarios -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Usuario</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Puesto/Sector</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Último Acceso</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Juan Díaz</td>
            <td>juan.diaz@empresa.com</td>
            <td>+54 11 1234-5678</td>
            <td>Administrador General<br>Administración</td>
            <td>Administrador</td>
            <td><span class="badge bg-success">Activo</span></td>
            <td>9/12/2024<br>14:30:00</td>
            <td>
              <button class="btn btn-sm btn-primary">Editar</button>
              <button class="btn btn-sm btn-danger">Eliminar</button>
            </td>
          </tr>
          <tr>
            <td>María López</td>
            <td>maria.lopez@empresa.com</td>
            <td>+54 11 2345-6789</td>
            <td>Supervisora de Calidad<br>Calidad</td>
            <td>Supervisor</td>
            <td><span class="badge bg-success">Activo</span></td>
            <td>9/12/2024<br>13:15:00</td>
            <td>
              <button class="btn btn-sm btn-primary">Editar</button>
              <button class="btn btn-sm btn-danger">Eliminar</button>
            </td>
          </tr>
          <tr>
            <td>Carlos Mendez</td>
            <td>carlos.mendez@empresa.com</td>
            <td>+54 11 3456-7890</td>
            <td>Soldador Senior<br>Producción A</td>
            <td>Trabajador</td>
            <td><span class="badge bg-success">Activo</span></td>
            <td>9/12/2024<br>12:45:00</td>
            <td>
              <button class="btn btn-sm btn-primary">Editar</button>
              <button class="btn btn-sm btn-danger">Eliminar</button>
            </td>
          </tr>
          <tr>
            <td>Ana García</td>
            <td>ana.garcia@empresa.com</td>
            <td>+54 11 4567-8901</td>
            <td>Técnica Mecánica<br>Mantenimiento</td>
            <td>Trabajador</td>
            <td><span class="badge bg-success">Activo</span></td>
            <td>9/12/2024<br>11:20:00</td>
            <td>
              <button class="btn btn-sm btn-primary">Editar</button>
              <button class="btn btn-sm btn-danger">Eliminar</button>
            </td>
          </tr>
          <tr>
            <td>Roberto Silva</td>
            <td>roberto.silva@empresa.com</td>
            <td>+54 11 5678-9012</td>
            <td>Operario General<br>Producción B</td>
            <td>Trabajador</td>
            <td><span class="badge bg-secondary">Inactivo</span></td>
            <td>5/12/2024<br>16:30:00</td>
            <td>
              <button class="btn btn-sm btn-primary">Editar</button>
              <button class="btn btn-sm btn-danger">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
