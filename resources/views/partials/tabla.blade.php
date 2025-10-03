<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>Nombre</th>
        <th>Email</th>
        <th>Rol</th>
        <th>Creado por</th>
        <th>Modificado por</th>
        <th>Creado el</th>
        <th>Modificado el</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($usuarios as $usuario)
        <tr>
          <td>{{ $usuario->name }}</td>
          <td>{{ $usuario->email }}</td>
          <td>{{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</td>
          <td>{{ $usuario->usuario_creacion ?? '—' }}</td>
          <td>{{ $usuario->usuario_modificacion ?? '—' }}</td>
          <td>{{ $usuario->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
          <td>{{ $usuario->updated_at?->format('d/m/Y H:i') ?? '—' }}</td>

          <td>
            <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-sm btn-info">Ver</a>
            <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-warning">Editar</a>
            <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" style="display:inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

