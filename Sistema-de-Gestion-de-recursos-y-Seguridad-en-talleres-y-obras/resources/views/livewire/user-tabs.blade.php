<div>
  <!-- Buscador -->
  <div class="mb-4">
    <input type="text" class="form-control" placeholder="Buscar por nombre o email..." wire:model.live.debounce.300ms="search">
    <p class="text-muted">Buscando: <strong>{{ $search }}</strong></p>

    
  </div>

  <!-- Tabs -->
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'todos' ? 'active' : '' }}" wire:click="setTab('todos')">Todos</a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'roles' ? 'active' : '' }}" wire:click="setTab('roles')">Roles y Permisos</a>
    </li>
  </ul>

  <!-- Contenido dinámico -->
  @if ($tab === 'todos')
    @include('partials.tabla', ['usuarios' => $usuarios])
  @elseif ($tab === 'roles')
    @include('partials.roles', ['roles' => $roles])
  @endif
</div>

