<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Inventario')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Estilos base -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="{{ asset('css/estilos.css') }}" rel="stylesheet">

  <!-- Livewire y estilos inyectados -->
  @livewireStyles
  @stack('styles')
</head>

<body class="d-flex">

  <!-- Sidebar -->
  <nav id="sidebar" class="sidebar d-flex flex-column text-white p-3 invisible no-transition">
    <div class="sidebar-header d-flex justify-content-between align-items-center mb-4">
      <div class="d-flex align-items-center">
        <i class="bi bi-tools fs-4 text-warning me-2"></i>
        <span class="fs-4 fw-bold">Inventario</span>
      </div>
      <button id="closeSidebar" class="btn btn-secondary btn-sm ms-2">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <ul class="nav flex-column mb-3">
      <li class="nav-item"><a href="{{ url('dashboard') }}" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}"><i class="bi bi-house me-2"></i> Dashboard</a></li>
      <li class="nav-item"><a href="{{ url('inventario') }}" class="nav-link {{ Request::is('inventario*') ? 'active' : '' }}"><i class="bi bi-box-seam me-2"></i> Inventario</a></li>
      <li class="nav-item"><a href="{{ url('controlEPP') }}" class="nav-link {{ Request::is('controlEPP*') ? 'active' : '' }}"><i class="bi bi-shield-check me-2"></i> Control EPP</a></li>
      <li class="nav-item"><a href="{{ url('reportes') }}" class="nav-link {{ Request::is('reportes*') ? 'active' : '' }}"><i class="bi bi-bar-chart me-2"></i> Reportes</a></li>
      <li class="nav-item"><a href="{{ url('usuarios') }}" class="nav-link {{ Request::is('usuarios*') ? 'active' : '' }}"><i class="bi bi-people me-2"></i> Usuarios</a></li>
      <li class="nav-item"><a href="{{ route('incidente.index') }}" class="nav-link {{ Request::is('incidente*') ? 'active' : '' }}"><i class="bi bi-exclamation-circle me-2"></i> Incidentes</a></li>
      <li class="nav-item"><a href="{{ route('prestamos.index') }}" class="nav-link {{ Request::is('prestamos*') ? 'active' : '' }}"><i class="bi bi-journal-arrow-down me-2"></i> Préstamos</a></li>
    </ul>

    <!-- Usuario con dropdown -->
    <div class="border-top pt-3 mt-auto">
      <div class="dropdown w-100">
        <button class="btn btn-sm text-white d-flex align-items-center justify-content-between w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="d-flex align-items-center">
            <img src="https://i.pravatar.cc/150?u=demo"
                alt="Foto de perfil"
                class="rounded-circle border shadow-sm me-2"
                style="width: 40px; height: 40px; object-fit: cover;">
            @auth
              <span class="fw-semibold">{{ auth()->user()->name }}</span>
            @endauth
          </div>
        </button>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a href="{{ url('perfil') }}" class="dropdown-item">
              <i class="bi bi-person me-2"></i> Ver perfil
            </a>
          </li>
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item text-danger">
                <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
              </button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Botón de apertura -->
  <button id="toggleSidebar" class="btn btn-secondary toggle-btn">
    <i class="bi bi-list"></i>
  </button>

  <!-- Contenido principal -->
  <main id="main" class="flex-grow-1 transition">
    @yield('content')
  </main>

  <!-- Scripts base -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Scripts personalizados -->
  <script src="{{ asset('js/asignar.js') }}"></script>
  <script src="{{ asset('js/filtroBusqueda.js') }}"></script>
  <script src="{{ asset('js/formatoFecha.js') }}"></script>

  <!-- Sidebar logic -->
  <script>
    const toggleBtn = document.getElementById('toggleSidebar');
    const closeBtn = document.getElementById('closeSidebar');
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main');

    function abrirSidebar() {
      sidebar.classList.add('active');
      main.classList.add('shifted');
      toggleBtn.classList.add('hidden');
      localStorage.setItem('sidebarAbierto', 'true');
    }

    function cerrarSidebar() {
      sidebar.classList.remove('active');
      main.classList.remove('shifted');
      toggleBtn.classList.remove('hidden');
      localStorage.setItem('sidebarAbierto', 'false');
    }

    window.addEventListener('DOMContentLoaded', () => {
      sidebar.classList.add('no-transition', 'invisible');

      const estado = localStorage.getItem('sidebarAbierto');
      if (estado === 'true') {
        sidebar.classList.add('active');
        main.classList.add('shifted');
        toggleBtn.classList.add('hidden');
      } else {
        sidebar.classList.remove('active');
        main.classList.remove('shifted');
        toggleBtn.classList.remove('hidden');
      }

      setTimeout(() => {
        sidebar.classList.remove('no-transition', 'invisible');
      }, 50);
    });

    toggleBtn.addEventListener('click', abrirSidebar);
    closeBtn.addEventListener('click', cerrarSidebar);
  </script>

  <!-- Livewire y scripts inyectados -->
  @livewireScripts
  @stack('scripts')
  
</body>
</html>
