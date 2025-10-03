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
      <!--<button class="btn btn-primary">Agregar Elemento</button>-->
      <a href="{{ route('recursos.create') }}" class="btn btn-primary">Agregar Elemento</a>

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
            <th>Categoría</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($recursos as $recurso)
        <tr>
            <td>{{ $recurso->id }}</td>
            <td>{{ $recurso->nombre }}</td>
            <td>
              @forelse ($recurso->serieRecursos as $serie)
                <span class="badge bg-info text-dark">{{ $serie->codigo_serie }}</span>
              @empty
                <span class="text-muted">Sin series</span>
              @endforelse
            </td>

            <td>{{ $recurso->categoria->nombre_categoria ?? 'Sin categoría' }}</td>
            <td>{{ $recurso->descripcion }}</td>
            <td>
              <a href="{{ route('recursos.edit', $recurso->id) }}" class="btn btn-sm btn-primary">Editar</a>

              <form action="{{ route('recursos.destroy', $recurso->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que quieres eliminar este EPP?')">Eliminar</button>
              </form>

              <a href="{{ route('serie_recurso.create', $recurso->id) }}" class="btn btn-sm btn-info">
                Agregar Serie
              </a>
            </td>

        </tr>
        @endforeach
    </tbody>
</table>

    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
