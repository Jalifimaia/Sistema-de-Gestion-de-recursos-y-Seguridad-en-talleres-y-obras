<!-- resources/views/partials/nav.blade.php -->
<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('dashboard') }}">Dashboard</a>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ request()->is('inventario') ? 'active' : '' }}" href="{{ url('inventario') }}">Inventario</a>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ request()->is('controlEPP') ? 'active' : '' }}" href="{{ url('controlEPP') }}">ControlEPP</a>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ request()->is('reportes') ? 'active' : '' }}" href="{{ url('reportes') }}">Reportes</a>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ request()->is('usuarios') ? 'active' : '' }}" href="{{ url('usuarios') }}">Usuarios</a>
  </li>

  <li class="nav-item">
    <a class="nav-link {{ request()->is('incidente*') ? 'active' : '' }}" href="{{ route('incidente.index') }}">
        <i class="bi bi-exclamation-triangle-fill"></i> Incidentes
    </a>
</li>

<li class="nav-item">
  <a class="nav-link {{ request()->is('prestamos*') ? 'active' : '' }}" href="{{ route('prestamos.index') }}">
      <i class="bi bi-box-arrow-down"></i> Préstamos
  </a>
</li>



  @auth
  <li class="nav-item ms-auto">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="nav-link btn btn-link text-danger" style="text-decoration: none;">Cerrar sesión</button>
    </form>
  </li>
  @endauth
</ul>

