<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Inventario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    @include('partials.barra_navegacion')

  <div class="container my-4">

    <!-- Título -->
    <div class="mb-4">
      <h2>Gestión de Inventario</h2>
      <p class="text-muted">Control de herramientas y equipos de protección personal</p>
    </div>

    <!-- Acciones -->
    <div class="d-flex flex-wrap gap-2 mb-3">
      <button class="btn btn-primary">Agregar Elemento</button>
      <button class="btn btn-secondary">Exportar</button>
      <input type="text" class="form-control w-auto" placeholder="Buscar por nombre o serie...">
    </div>



    <!-- Tabla de inventario -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Serie</th>
            <th>Estado</th>
            <th>Asignado a</th>
            <th>Ubicación</th>
            <th>Valor</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>T001</td>
            <td>Taladro Eléctrico<br><small class="text-muted">Herramientas Eléctricas</small></td>
            <td>TD-2024-001</td>
            <td><span class="badge bg-success">Disponible</span></td>
            <td>No asignada</td>
            <td>Almacén A-1</td>
            <td>$25.000</td>
            <td>
              <button class="btn btn-sm btn-primary">Editar</button>
              <button class="btn btn-sm btn-danger">Eliminar</button>
            </td>
          </tr>
          <tr>
            <td>T002</td>
            <td>Amoladora Angular<br><small class="text-muted">Herramientas Eléctricas</small></td>
            <td>AM-2024-002</td>
            <td><span class="badge bg-warning text-dark">Prestada</span></td>
            <td>Carlos Mendez</td>
            <td>Sector B</td>
            <td>$18.000</td>
            <td>
              <button class="btn btn-sm btn-primary">Editar</button>
              <button class="btn btn-sm btn-danger">Eliminar</button>
            </td>
          </tr>
          <tr>
            <td>T003</td>
            <td>Martillo Neumático<br><small class="text-muted">Herramientas Neumáticas</small></td>
            <td>MN-2023-015</td>
            <td><span class="badge bg-danger">En Reparación</span></td>
            <td>No asignada</td>
            <td>Taller de Reparación</td>
            <td>$45.000</td>
            <td>
              <button class="btn btn-sm btn-primary">Editar</button>
              <button class="btn btn-sm btn-danger">Eliminar</button>
            </td>
          </tr>
          <tr>
            <td>T004</td>
            <td>Soldadora MIG<br><small class="text-muted">Equipos de Soldadura</small></td>
            <td>SM-2024-003</td>
            <td><span class="badge bg-success">Disponible</span></td>
            <td>No asignada</td>
            <td>Almacén B-2</td>
            <td>$85.000</td>
            <td>
              <button class="btn btn-sm btn-primary">Editar</button>
              <button class="btn btn-sm btn-danger">Eliminar</button>
            </td>
          </tr>
          <tr>
            <td>T005</td>
            <td>Compresor de Aire<br><small class="text-muted">Equipos Auxiliares</small></td>
            <td>CA-2023-008</td>
            <td><span class="badge bg-warning text-dark">Prestada</span></td>
            <td>Ana García</td>
            <td>Sector C</td>
            <td>$32.000</td>
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
