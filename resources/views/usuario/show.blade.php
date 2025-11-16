@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('content')


<!--SEPARADOR-------------------------------------------- -->

<div class="container p-2">
  <div class="row align-items-stretch g-0">
    <div class="container py-4">
  <div class="row g-4 align-items-start">
    
    <!-- Columna izquierda: datos -->
    <div class="col-md-8">
      <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('usuarios.index') }}" class="btn btn-volver d-flex align-items-center">
          <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icon-volver me-2">
          Volver
        </a>
        <h1 class="titulo-con-linea mb-0">{{ $usuario->name }}</h1>
      </div>


      <p><strong>Rol:</strong> {{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</p>
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
      <p><strong>Email:</strong> {{ $usuario->email }}</p>
      <p><strong>Último acceso:</strong> {{ \Carbon\Carbon::parse($usuario->ultimo_acceso)->diffForHumans() ?? 'Nunca' }}</p>

      <!-- Acordeón de registro -->
      <div class="accordion mb-4" id="registroAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingRegistro">
            <button class="accordion-button collapsed registro-btn" type="button"
                    data-bs-toggle="collapse" data-bs-target="#collapseRegistro"
                    aria-expanded="false" aria-controls="collapseRegistro">
              Información de registro
            </button>
          </h2>
          <div id="collapseRegistro" class="accordion-collapse collapse"
              aria-labelledby="headingRegistro" data-bs-parent="#registroAccordion">
            <div class="accordion-body registro-body">
              <p><strong>Creado por:</strong> <em>{{ $usuario->creador?->name ?? 'Desconocido' }}</em></p>
              <p><strong>Modificado por:</strong> <em>{{ $usuario->modificador?->name ?? 'Desconocido' }}</em></p>
              <p><strong>Fecha de creación:</strong> <em>{{ \Carbon\Carbon::parse($usuario->created_at)->format('d/m/Y H:i') }}</em></p>
              <p><strong>Última modificación:</strong> <em>{{ $usuario->updated_at ? \Carbon\Carbon::parse($usuario->updated_at)->format('d/m/Y H:i') : 'N/A' }}</em></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Columna derecha: QR -->
    <div class="col-md-4">
      @if($usuario->codigo_qr)
        <div class="card qr-card text-center mx-auto">
          <div class="card-body">
            <h6 class="card-title mb-3">Código QR</h6>
            {!! QrCode::size(140)->generate($usuario->codigo_qr) !!}
            <p class="text-muted small mt-2" id="codigo-qr-texto">{{ $usuario->codigo_qr }}</p>
            <button class="btn btn-copiar btn-sm mt-2" onclick="copiarCodigoQR()">
              <img src="{{ asset('images/copiar.svg') }}" alt="Copiar" class="icon-copy me-2">
              Copiar código
            </button>
          </div>
        </div>
      @endif
    </div>

  </div>
</div>


  </div>
</div>


<ul class="nav nav-tabs custom-tabs justify-content-center mb-4" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active tab-loader" data-url="{{ route('usuarios.checklists', $usuario->id) }}" type="button">
      Checklist 
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link tab-loader" data-url="{{ route('usuarios.incidentes', $usuario->id) }}" type="button">
      Incidentes 
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link tab-loader" data-url="{{ route('usuarios.prestamos', $usuario->id) }}" type="button">
      Préstamos 
    </button>
  </li>
</ul>

<div id="tab-content" class="tab-content p-3"></div>



<div id="toastQR" class="toast position-fixed bottom-0 end-0 m-3 text-bg-success" role="alert" aria-live="assertive" aria-atomic="true">
  <div class="d-flex">
    <div class="toast-body">✅ Código QR copiado al portapapeles</div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
  </div>
</div>




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
@endsection


<style>
</style>

@push('styles')
<link href="{{ asset('css/usuariosShow.css') }}" rel="stylesheet">
@endpush

