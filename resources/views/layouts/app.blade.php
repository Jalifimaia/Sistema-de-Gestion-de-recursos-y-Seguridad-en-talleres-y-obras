<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="{{ asset('images/icono.svg') }}" type="image/svg+xml">
  <title>@yield('title', 'Inventario')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Estilos base -->
  <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
  <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
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
            <img src="https://cdn-icons-png.flaticon.com/512/12225/12225881.png"
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
            <a href="{{ route('usuarios.show', auth()->user()->id) }}" class="dropdown-item">
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
  <button id="toggleSidebar" class="btn btn-secondary toggle-btn toggle-square" aria-label="Abrir menú">
    <i class="bi bi-list" aria-hidden="true"></i>
  </button>

  <!-- Contenido principal -->
  <main id="main" class="flex-grow-1 transition">
    @yield('content')
  </main>

  <!-- Scripts base -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- Scripts personalizados -->
  <script src="{{ asset('js/asignar.js') }}"></script>
  <script src="{{ asset('js/filtroBusqueda.js') }}"></script>
  <script src="{{ asset('js/formatoFecha.js') }}"></script>

  <!-- Sidebar logic -->
  <script>
  (function () {
    const toggleBtn = document.getElementById('toggleSidebar');
    const closeBtn = document.getElementById('closeSidebar');
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main');

    function abrirSidebar() {
  if (!sidebar || !main) return;
  sidebar.classList.add('active');
  main.classList.add('shifted');
  document.body.setAttribute('data-sidebar', 'open'); // <- línea nueva
  localStorage.setItem('sidebarAbierto', 'true');
}

function cerrarSidebar() {
  if (!sidebar || !main) return;
  sidebar.classList.remove('active');
  main.classList.remove('shifted');
  document.body.setAttribute('data-sidebar', 'closed'); // <- línea nueva
  localStorage.setItem('sidebarAbierto', 'false');
}


    // Delegación global por si el nodo se reemplaza o hay overlays
    document.addEventListener('click', function (e) {
      const tToggle = e.target.closest && e.target.closest('#toggleSidebar');
      const tClose = e.target.closest && e.target.closest('#closeSidebar');
      if (tToggle) {
        e.preventDefault();
        abrirSidebar();
      } else if (tClose) {
        e.preventDefault();
        cerrarSidebar();
      }
    });

    window.addEventListener('DOMContentLoaded', () => {
      try {
        // Aplicar estado guardado
        const estado = localStorage.getItem('sidebarAbierto');
        if (estado === 'true') {
          abrirSidebar();
        } else {
          cerrarSidebar();
        }

        // Quitar posibles clases iniciales "invisible/no-transition" si las pones
        sidebar?.classList.remove('no-transition', 'invisible');
      } catch (err) {
        console.error('Sidebar init error', err);
      }
    });

    // Exponer funciones para pruebas manuales en consola (opcional)
    window.abrirSidebar = abrirSidebar;
    window.cerrarSidebar = cerrarSidebar;
  })();
</script>


<script>
/*(function () {
  const TOGGLE_SELECTOR = '#toggleSidebar';
  const PROTECT_SELECTOR = '[data-protect-toggle], .protect-toggle';
  const TOGGLE_SIZE = 56; // ancho/alto del toggle en px (ajustá a tu .toggle-square)
  const GAP = 12; // separación en px entre toggle y header

  function isVisible(el) { return el && el.offsetParent !== null; }

  function rectsOverlap(a, b) {
    return !(a.right <= b.left || a.left >= b.right || a.bottom <= b.top || a.top >= b.bottom);
  }

  function adjustProtectedHeaders() {
    const toggle = document.querySelector(TOGGLE_SELECTOR);
    if (!toggle) return;
    const toggleRect = toggle.getBoundingClientRect();

    document.querySelectorAll(PROTECT_SELECTOR).forEach(header => {
      if (!isVisible(header)) {
        header.classList.remove('protect-toggle--shifted');
        return;
      }
      const hRect = header.getBoundingClientRect();

      // consideramos solapamiento solo en la zona superior (títulos)
      const overlapping = rectsOverlap(toggleRect, hRect);
      if (overlapping) {
        // aplicar clase que empuja contenido a la derecha
        header.classList.add('protect-toggle--shifted');
      } else {
        header.classList.remove('protect-toggle--shifted');
      }
    });
  }

  // throttle simple con RAF
  let raf = null;
  function scheduleAdjust() {
    if (raf) cancelAnimationFrame(raf);
    raf = requestAnimationFrame(() => { adjustProtectedHeaders(); raf = null; });
  }

  // re-evaluar en eventos habituales y cuando el sidebar cambia
  window.addEventListener('load', scheduleAdjust);
  window.addEventListener('resize', scheduleAdjust);
  window.addEventListener('orientationchange', scheduleAdjust);
  window.addEventListener('scroll', scheduleAdjust);

  // observar mutaciones en el body por Livewire/Alpine
  const mo = new MutationObserver(scheduleAdjust);
  mo.observe(document.body, { subtree: true, childList: true, attributes: true });

  // también re-evaluar cuando cambie el estado del sidebar (data-sidebar)
  const bodyObs = new MutationObserver(mutations => {
    for (const m of mutations) {
      if (m.attributeName === 'data-sidebar') { scheduleAdjust(); break; }
    }
  });
  bodyObs.observe(document.body, { attributes: true });

  // ejecución inicial
  scheduleAdjust();
})();*/
</script>

  <!-- Livewire y scripts inyectados -->
  @livewireScripts
  @stack('scripts')
  
</body>
</html>
