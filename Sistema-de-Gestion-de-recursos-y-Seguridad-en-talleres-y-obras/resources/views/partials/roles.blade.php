<ul class="list-group">
  @foreach ($roles as $rol)
    <li class="list-group-item d-flex justify-content-between align-items-center">
      {{ $rol->nombre_rol }}
      <span class="badge bg-primary rounded-pill">{{ $rol->usuarios->count() }}</span>
    </li>
  @endforeach
</ul>
