<!-- resources/views/partials/sidebar.blade.php -->
<div id="sidebar">
  <h2>Panel de Control</h2>
  <ul>
    <li onclick="location.href='{{ url('dashboard') }}'">Dashboard</li>
    <li onclick="location.href='{{ url('inventario') }}'">Inventario</li>
    <li onclick="location.href='{{ url('controlEPP') }}'">Control EPP</li>
    <li onclick="location.href='{{ url('reportes') }}'">Reportes</li>
    <li onclick="location.href='{{ url('usuarios') }}'">Usuarios</li>
    <li onclick="location.href='{{ route('incidente.index') }}'">Incidentes</li>
    <li onclick="location.href='{{ route('prestamos.index') }}'">Préstamos</li>
  </ul>

  <div style="position: absolute; bottom: 20px; width: 100%; text-align: center;">
    <a href="{{ url('perfil') }}" class="btn btn-outline-light btn-sm mb-2">👤 Ver mi perfil</a>
    @auth
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-outline-danger btn-sm">🔒 Cerrar sesión</button>
    </form>
    @endauth
  </div>
</div>
.sidebar {
  position: fixed;            /* Fijo en pantalla */
  top: 0;
  left: 0;
  width: 250px;
  height: 100vh;              /* Ocupa todo el alto */
  background-color: #212529;
  color: white;
  padding: 20px;
  overflow-y: auto !important;  /* Scroll vertical garantizado */
  overflow-x: hidden;
  z-index: 1050;
  display: flex;
  flex-direction: column;
}

/* Estilo de scrollbar */
.sidebar::-webkit-scrollbar {
  width: 8px;
}
.sidebar::-webkit-scrollbar-thumb {
  background-color: #f57c00;
  border-radius: 10px;
}
.sidebar::-webkit-scrollbar-track {
  background-color: #333;
}

/* Aseguramos que el bloque de usuario quede abajo */
.sidebar .border-top.pt-3.mt-auto {
  margin-top: auto !important;
}

<style>
