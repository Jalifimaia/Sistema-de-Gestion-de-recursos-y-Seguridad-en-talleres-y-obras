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
</ul>
