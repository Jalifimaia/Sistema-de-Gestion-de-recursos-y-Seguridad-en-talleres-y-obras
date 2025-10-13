<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>Nombre</th>
        <th>Estado</th>
        <th>Email</th>
        <th>Rol</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($usuarios as $usuario)
        <tr>
          <td>{{ $usuario->name }}</td>
          <td>{{ optional($usuario->estado)->nombre }}</td>
          <td>{{ $usuario->email }}</td>
          <td>{{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</td>

          <td>
            <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-sm btn-info">Ver</a>
            <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-warning">Editar</a>
   
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

