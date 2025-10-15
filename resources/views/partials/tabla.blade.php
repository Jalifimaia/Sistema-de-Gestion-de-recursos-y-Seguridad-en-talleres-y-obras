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
          <td>
            @if ($usuario->estado?->nombre === 'Baja')
              <span >Baja</span>
            @elseif ($usuario->estado?->nombre === 'Alta')
              <span>Alta</span>
            @elseif ($usuario->estado?->nombre === 'stand by')
              <span">Stand by</span>
            @else
              <span>Sin estado</span>
            @endif
          </td>
          <td>{{ $usuario->email }}</td>
          <td>{{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</td>
          <td>
          <a href="{{ route('usuarios.show', $usuario->id) }}" class="btn btn-sm btn-info">Ver</a>
          <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-warning">Editar</a>

          @if ($usuario->estado?->nombre === 'Baja')
            <button class="btn btn-sm btn-danger opacity-50 pointer-events-none" disabled>
              Dar de baja
            </button>
          @else
            <button wire:click="darDeBaja({{ $usuario->id }})" class="btn btn-sm btn-danger">
              Dar de baja
            </button>
          @endif
        </td>

        </tr>
      @endforeach
    </tbody>
  </table>
</div>
