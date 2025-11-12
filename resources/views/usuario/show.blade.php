@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('content')


<!--SEPARADOR-------------------------------------------- -->

<div class="container p-2">
  <div class="row align-items-stretch g-0">
    <!-- Caja grande izquierda -->
    <div class="col-12 col-md-8 col-sm-6 flex-column">
      <div class="border bg-light p-3">
        <h1>{{ $usuario->name }}</h1>
      </div>
      <div class="row g-0">


        <div class="d-flex flex-fill">


          <!-- Dos cajas pequeñas abajo -->
          <div class="col-12 col-md-6 flex-fill">
            <div class="border bg-light p-3">
              <p><strong>Email:</strong> {{ $usuario->email }}</p>
              <p><strong>Rol:</strong> {{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</p>
              <p><strong>Último acceso:</strong> 
                {{ \Carbon\Carbon::parse($usuario->ultimo_acceso)->diffForHumans() ?? 'Nunca' }}
              </p>

              {{-- Estado actual --}}
              <p><strong>Estado:</strong>
                @if ($usuario->estado?->nombre === 'Alta')
                  <span class="badge bg-success">Activo (Alta)</span>
                @elseif ($usuario->estado?->nombre === 'Baja')
                  <span class="badge bg-danger">Inactivo (Baja)</span>
                @elseif ($usuario->estado?->nombre === 'stand by')
                  <span class="badge bg-warning text-dark">Stand by</span>
                @else
                  <span class="badge bg-secondary">Sin estado</span>
                @endif
              </p>
            </div>
          </div>

          <div class="col-12 col-md-6 flex-fill">
            <div class="border bg-light p-3">
              <p><strong>Creado por:</strong> {{ $usuario->creador?->name ?? 'Desconocido' }}</p>
              <p><strong>Modificado por:</strong> {{ $usuario->modificador?->name ?? 'Desconocido' }}</p>
              <p><strong>Creado el:</strong> {{ \Carbon\Carbon::parse($usuario->created_at)->format('d/m/Y H:i') }}</p>  <!--parseo de dato a objeto carbon-->
              <p><strong>Modificado el:</strong> {{ $usuario->updated_at ? \Carbon\Carbon::parse($usuario->updated_at)->format('d/m/Y H:i') : 'N/A' }} </p>   <!--parseo de dato a objeto carbon-->
            </div>
          </div>
        

        </div>
      </div>


    </div>
      


    <!-- Caja alta derecha -->
    <div class="col-12 col-md-4 mb-3">
      <div class="border bg-light p-3">
        @if($usuario->codigo_qr)
        <div class="text-start d-flex flex-column justify-content-start align-items-center">
          <h5>Código QR de identificación</h5>
          {!! QrCode::size(200)->generate($usuario->codigo_qr) !!}
          <p class="text-muted small mt-2" id="codigo-qr-texto">{{ $usuario->codigo_qr }}</p>
          <button class="btn btn-outline-primary btn-sm mt-2" onclick="copiarCodigoQR()">📋 Copiar código</button>
        </div>
        @endif

        {{-- Toast Bootstrap --}}
        <div id="toastQR" class="toast align-items-center text-bg-success border-0" role="alert">
          <div class="d-flex justify-content-center">
            <div class="toast-body">
              ✅ Código QR copiado al portapapeles
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        </div>      
      </div>
    </div>

  </div>
  <hr> <!--SEPARADOR-------------------------------------------- -->
</div>


<!-- NAV TABS -->
<ul class="nav nav-tabs d-flex justify-content-center mb-4" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active tab-loader" data-url="{{ route('usuarios.checklists', $usuario->id) }}" type="button">Checklist <span class="badge bg-secondary">{{ $usuario->checklists_count ?? 0 }}</span></button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link tab-loader" data-url="{{ route('usuarios.incidentes', $usuario->id) }}" type="button">Incidentes <span class="badge bg-secondary">{{ $usuario->incidentes_count ?? 0 }}</span></button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link tab-loader" data-url="{{ route('usuarios.prestamos', $usuario->id) }}" type="button">Préstamos <span class="badge bg-secondary">{{ $usuario->prestamos_count ?? 0 }}</span></button>
  </li>
</ul>

<div id="tab-content" class="tab-content p-3"></div>

<hr> <!--SEPARADOR-------------------------------------------- -->





<script>
function copiarCodigoQR() {
  const texto = document.getElementById('codigo-qr-texto').innerText;
  navigator.clipboard.writeText(texto).then(() => {
    const toastEl = document.getElementById('toastQR');
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
  }).catch(err => {
    console.error('Error al copiar:', err);
    alert('❌ No se pudo copiar el código');
  });
}


    const tabButtons = document.querySelectorAll('.tab-loader');
    const tabContent = document.getElementById('tab-content');

    async function loadTab(url) {
      tabContent.innerHTML = '<div class="text-center py-4">Cargando...</div>';
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) {
        tabContent.innerHTML = '<div class="text-danger">Error al cargar los datos.</div>';
        return;
      }
      const html = await res.text();
      tabContent.innerHTML = html;
      // Delegación para paginación: interceptar clicks en links de paginación
      tabContent.querySelectorAll('.pagination a').forEach(a => {
        a.addEventListener('click', function(e) {
          e.preventDefault();
          loadTab(this.href);
        });
      });
    }

    tabButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        tabButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        loadTab(this.dataset.url);
      });
    });

    // Cargar la pestaña activa al inicio
    document.addEventListener('DOMContentLoaded', () => {
      const active = document.querySelector('.tab-loader.active') || document.querySelector('.tab-loader');
      if (active) loadTab(active.dataset.url);
    });


</script>

    <div class="d-flex align-items-center justify-content-start gap-3 mb-4">
      <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
          ⬅️ Volver
       </a>
    </div>
@endsection


<style>
</style>
