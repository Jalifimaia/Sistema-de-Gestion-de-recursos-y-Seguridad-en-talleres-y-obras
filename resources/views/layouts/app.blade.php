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



   <!-- CDN Libreria de estilos de DRIVER.js -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>

  <!-- Estilos a boton flotante de guia de uso -->
  <link href="{{ asset('css/botonFlotanteGuia.css') }}" rel="stylesheet">


  
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


  <!-- BOTON FLOTANTE GUIA DE USO -->
  <button id="btnTour" class="btn-tour">
    <strong>?</strong>
  </button>


  <!-- CDN Libreria DRIVER.js -->
  <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>


  <script>

    const driver = window.driver.js.driver;
    
    document.addEventListener('DOMContentLoaded', () => {
      const btnTour = document.getElementById('btnTour');
      document.getElementById("btnTour").style.display = "block";

      btnTour.addEventListener('click', () => {
        //console.log('Iniciando tour...');
        const path = window.location.pathname;      
        console.log(path.toString());
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if (path.includes('/dashboard'))
        {
          document.getElementById("btnTour").style.display = "block";
          const driverObj = driver({
              opacity: 0.75,
              doneBtnText: 'Finalizar',
              nextBtnText: 'Siguiente',
              prevBtnText: 'Anterior',
              steps: 
              [
                { element: 'code .line:nth-child(1)', popover: { title: 'Bienvenido al panel de Dashboard', description: 'En esta guía se le indicará cómo navegar y utilizar las funciones principales del dashboard.', side: "bottom", align: 'start' }},

                { element: '#driver-trabajadores_activos', 
                  popover: 
                  { title: 'Usuarios activos', 
                    description: 'Visualiza los trabajadores dados de alta en el sistema.', 
                    side: "left", 
                    align: 'start' }},

                { element: '#driver-herramientas_uso', 
                  popover: 
                  { title: 'Herramientas en uso', 
                    description: 'Muestra las herramientas que están actualmente prestadas a los usuarios con los detalles principales.', 
                    side: "left", 
                    align: 'start' }},              

                { element: '#driver-alertas_activas', 
                  popover: 
                  { title: 'Alertas activas', 
                    description: 'Puedes revisar las alertas activas relacionadas con herramientas en criterios de stock y plazos de vencimiento y devolución.', 
                    side: "left", 
                    align: 'start' }}, 

                { element: '#driver-estado_general_inventario', 
                  popover: 
                  { title: 'Información de inventario', 
                    description: 'Echa un vistazo al estado general del inventario con gráficos visuales para un análisis rápido.', 
                    side: "left", 
                    align: 'start' }},     
              ]
          });   driverObj.drive();   
        }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
        else if (path.includes('/inventario'))
        {
          document.getElementById("btnTour").style.display = "block";
          const driverObj = driver({
              opacity: 0.75,
              doneBtnText: 'Finalizar',
              nextBtnText: 'Siguiente',
              prevBtnText: 'Anterior',
              steps: 
              [
                { element: 'code .line:nth-child(1)', popover: { title: 'Bienvenido al panel de gestion general de recursos', description: 'En esta guía se le indicará cómo navegar y utilizar las funciones principales del inventario.', side: "bottom", align: 'start' }},

                { element: '#driver-estado_inventario', 
                  popover: 
                  { title: 'Estado del Inventario', 
                    description: 'Un breve contabilizador. Haga click para visualizar el número de herramientas y EPP separados en criterio de stock e integridad material de los mismos.', 
                    side: "left", 
                    align: 'start' }},

                { element: '#driver-agregar_recurso', 
                  popover: 
                  { title: 'Agregar Nuevo Recurso', 
                    description: 'Ingrese los detalles para agregar un nuevo recurso al inventario.', 
                    side: "left", 
                    align: 'start' }},

                { element: '#driver-codigos_qr', 
                  popover: 
                  { title: 'Sección de Códigos QR de Recursos', 
                    description: 'Acceda a los códigos QR generados para cada recurso, facilitando su identificación y gestión.', 
                    side: "left", 
                    align: 'start' }},
                    
                { element: '#driver-filtro_buscador', 
                  popover: 
                  { title: 'Buscador con parametros', 
                    description: 'Utilice el buscador para filtrar recursos por nombre, categoría, subcategoría o descripción, facilitando la localización rápida de resultados de la tabla en tiempo real.', 
                    side: "left", 
                    align: 'start' }},   

                { element: '#driver-crud_recursos', 
                  popover: 
                  { title: 'Acciones sobre Recursos', 
                    description: 'Puede editar (naranja), agregar series (verde), ver series existentes (azul) o dar de baja un recurso desde esta sección (rojo).', 
                    side: "left", 
                    align: 'start' }},
              ]
          });   driverObj.drive();   
        }
    //---------------------------------------------------------------------------------------------------         
        else if (path.includes('/series-qr'))
        {
          document.getElementById("btnTour").style.display = "block";
          const driverObj = driver({
              opacity: 0.75,
              doneBtnText: 'Finalizar',
              nextBtnText: 'Siguiente',
              prevBtnText: 'Anterior',
              steps: 
              [
                { element: 'code .line:nth-child(1)', popover: { title: 'Bienvenido al panel de gestion general de recursos', description: 'En esta guía se le indicará cómo navegar y utilizar las funciones principales del inventario.', side: "bottom", align: 'start' }},

                { element: '#driver-btn_imprimir_lote', 
                  popover: 
                  { title: 'Imprimir todos los códigos QR', 
                    description: 'Con esta opción puede imprimir todos los códigos QR de los recursos en un solo documento PDF.', 
                    side: "left", 
                    align: 'start' }},

                { element: '#driver-filtro_buscador_qr', 
                  popover: 
                  { title: 'Filtrado de QR por parámetros', 
                    description: 'Utilice el buscador para filtrar códigos QR por categoría, subcategoría, nombre del recurso o iniciales del número de serie.', 
                    side: "left", 
                    align: 'start' }},

                { element: '#driver-card_detalles_qr', 
                  popover: 
                  { title: 'Detalles QR por Recurso', 
                    description: 'Vista sencilla de detalles de cada QR de un recurso específico, con opción de copia de código de serie para reporte y descarga en archivo PDF.', 
                    side: "left", 
                    align: 'start' }},
              ]
          });   driverObj.drive();   
        }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// 
        else if (path.includes('/controlEPP'))
        {
          document.getElementById("btnTour").style.display = "block";
          const driverObj = driver({
              opacity: 0.75,
              doneBtnText: 'Finalizar',
              nextBtnText: 'Siguiente',
              prevBtnText: 'Anterior',
              steps: 
              [
                { element: 'code .line:nth-child(1)', popover: { title: 'Bienvenido al panel de checklist', description: 'En esta guía se le indicará los detalles sobre las funciones de consulta y control de asignaciones de Herramientas y EPP en trabajadores.', side: "bottom", align: 'start' }},

                { element: '#driver-card_checklist_diario', 
                  popover: 
                  { title: 'Comprobación diaria de EPP por trabajador', 
                    description: 'Puede supervisar el cumplimiento diario de EPP por cada trabajador. Caso contrario, asignarle los que tiene pendientes.', 
                    side: "left", 
                    align: 'start' }},

                { element: '#driver-card_asignar_epp', 
                  popover: 
                  { title: 'Asignar EPP a nuevos trabajadores', 
                    description: 'Opcion útil para asignar EPP disponibles en stock a los nuevos trabajadores incorporados al sistema.', 
                    side: "left", 
                    align: 'start' }},

                { element: '#driver-card_checklist_no_registrado', 
                  popover: 
                  { title: 'Trabajadores sin ningún checklist', 
                    description: 'Podrá visualizar la tabla de trabajadores cuya asignaciones de EPP es nula, y con opcion de ponerlo al día.', 
                    side: "left", 
                    align: 'start' }},
          
                { element: '#driver-card_checklist_del_dia', 
                  popover: 
                  { title: 'Asignaciones de EPP registradas hoy', 
                    description: 'Un práctico buscador por nombre de trabajador para visualizar el control de asignación de EPP realizados en el día.', 
                    side: "left", 
                    align: 'start' }},
              ]
          });   driverObj.drive();   
        }
    //---------------------------------------------------------------------------------------------------   
        else if (path.includes('/checklist-epp'))
        {
          document.getElementById("btnTour").style.display = "block";
          const driverObj = driver({
              opacity: 0.75,
              doneBtnText: 'Finalizar',
              nextBtnText: 'Siguiente',
              prevBtnText: 'Anterior',
              steps: 
              [
                { element: 'code .line:nth-child(1)', popover: { title: 'Bienvenido, usted a presionado el botón de registrar checklist diario', description: 'En breve se le indicará como registrar un checklist diario de EPP para un trabajador de manera completa o parcial.', side: "bottom", align: 'start' }},

                { element: '#driver-filtro_trabajador', 
                  popover: 
                  { title: 'Paso 1: Seleccionar trabajador', 
                    description: 'Haciendo click en el select, proceda a elegir al trabajador para registrar su checklist diario.', 
                    side: "left", 
                    align: 'start' }},

                { element: '#driver-checklist_epp', 
                  popover: 
                  { title: 'Paso 2: Seleccionar los EPP', 
                    description: 'Elija los EPP que el trabajador va a utilizar en la actividad actual asignada.', 
                    side: "left", 
                    align: 'start' }},

                { element: '#driver-observaciones_checklist', 
                  popover: 
                  { title: 'Detalle opcional: Observaciones', 
                    description: 'En caso que se requiera, puede agregar una observación para dar contexto a la asignación de los EPP (Como por ejemplo, asignar arnés pero indicar que no trabajará en la altura.).', 
                    side: "left", 
                    align: 'start' }},
              ]
          });   driverObj.drive();   
        }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        else
        {
          document.getElementById("btnTour").style.display = "none";
          return;
        }   
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      });
    });  
    
  </script>


  <!-- Livewire y scripts inyectados -->
  @livewireScripts
  @stack('scripts')
  
</body>
</html>
