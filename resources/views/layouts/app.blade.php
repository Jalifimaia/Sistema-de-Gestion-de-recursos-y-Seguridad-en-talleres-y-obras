<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Inventario')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  @livewireStyles

  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
      position: relative;
    }

    /* Sidebar */
    #sidebar {
      position: fixed;
      top: 0;
      left: -250px;
      width: 250px;
      height: 100%;
      background-color: #2c3e50;
      color: white;
      transition: left 0.3s ease;
      z-index: 1000;

      display: flex;
      flex-direction: column;
    }

    #sidebar.active {
      left: 0;
    }

    #sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
      flex-grow: 1;
      overflow-y: auto;
      scrollbar-width: thin;
    }

    #sidebar ul li {
      padding: 15px 20px;
      cursor: pointer;
      border-bottom: 1px solid #34495e;
    }

    #sidebar ul li:hover {
      background-color: #34495e;
    }

    #sidebar .bottom-actions {
      padding: 15px 0;
      text-align: center;
      border-top: 1px solid #34495e;
    }

    #sidebar .bottom-actions a,
    #sidebar .bottom-actions form {
      display: block;
      margin: 5px auto;
      width: 80%;
    }

    /* Botón de apertura/cierre */
    #toggleBtn {
      position: fixed;
      top: 20px;
      left: 20px;
      background-color: #3498db;
      color: white;
      border: none;
      padding: 10px 15px;
      cursor: pointer;
      z-index: 1100;
      border-radius: 5px;
    }

    #sidebar .close-btn {
      background-color: #3498db;
      color: white;
      border: none;
      padding: 10px 15px;
      cursor: pointer;
      border-radius: 5px;
      margin: 10px auto;
      width: 90%;
    }

    #main {
      transition: margin-left 0.3s ease;
      padding: 20px;
    }

    #main.shifted {
      margin-left: 250px;
    }
  </style>
</head>
<body>

  <!-- Botón de apertura (fuera del menu, solo visible cuando está cerrado) -->
  <button id="toggleBtn">☰ </button>

  <!-- menu lateral -->
  <div id="sidebar">
    <!-- Botón de cierre dentro del menu -->
    <button class="close-btn" id="closeBtn">☰</button>

    <ul>
      <li onclick="location.href='{{ url('dashboard') }}'">Dashboard</li>
      <li onclick="location.href='{{ url('inventario') }}'">Inventario</li>
      <li onclick="location.href='{{ url('controlEPP') }}'">Control EPP</li>
      <li onclick="location.href='{{ url('reportes') }}'">Reportes</li>
      <li onclick="location.href='{{ url('usuarios') }}'">Usuarios</li>
      <li onclick="location.href='{{ route('incidente.index') }}'">Incidentes</li>
      <li onclick="location.href='{{ route('prestamos.index') }}'">Préstamos</li>
    </ul>

    <div class="bottom-actions">
      <a href="{{ url('perfil') }}" class="btn btn-outline-light btn-sm mb-2">👤 Ver mi perfil</a>
      @auth
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-danger btn-sm">🔒 Cerrar sesión</button>
      </form>
      @endauth
    </div>
  </div>

  <!-- Contenido principal -->
  <div id="main">
    @yield('content')
  </div>

  @livewireScripts
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @yield('scripts')

  <script>
    const toggleBtn = document.getElementById('toggleBtn');
    const closeBtn = document.getElementById('closeBtn');
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.add('active');
      main.classList.add('shifted');
      toggleBtn.style.display = 'none'; // ocultar botón externo
    });

    closeBtn.addEventListener('click', () => {
      sidebar.classList.remove('active');
      main.classList.remove('shifted');
      toggleBtn.style.display = 'block'; // volver a mostrar botón externo
    });

    // Opcional: cerrar menu al hacer clic en un ítem
    sidebar.querySelectorAll('li').forEach(item => {
      item.addEventListener('click', () => {
        sidebar.classList.remove('active');
        main.classList.remove('shifted');
        toggleBtn.style.display = 'block';
      });
    });
  </script>

</body>
</html>
