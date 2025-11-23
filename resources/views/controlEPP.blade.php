@extends('layouts.app')

@section('title', 'Control de EPP y Seguridad')

@push('styles')
  <link href="{{ asset('css/controlepp.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container py-4">

  <!-- 🔶 Encabezado -->
<header class="row mb-1 align-items-center">
  <div class="col-md-8">
    <h1 class="h4 fw-bold mb-1 d-flex align-items-center gap-2">
      <img src="{{ asset('images/escudo.svg') }}" alt="Icono EPP" style="height: 35px;">
      Control de Equipo de protección personal y Seguridad
    </h1>
    <p class="text-muted small mb-0">
      Gestión de equipos de protección personal y checklist de seguridad
    </p>
  </div>
</header>

 <!-- 🔶 Cards funcionales estilo acción urgente -->
<div class="row g-4 mb-1 mt-0 justify-content-center cards-funcionales">
  <!-- Checklist Diario -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-action h-100 d-flex flex-column align-items-center text-center">
      <div class="card-body d-flex flex-column align-items-center text-center">
        <div class="d-flex justify-content-center align-items-center w-100 mb-2 gap-2">
          <img src="{{ asset('images/checklistSI.svg') }}" alt="Checklist" class="icono-action-inline">
          <h5 class="card-title fw-semibold mb-0 ">Checklist Diario</h5>
        </div>
        <p class="card-text small text-muted">Registrar cumplimiento diario de EPP por trabajador.</p>
        <a href="{{ route('checklist.epp') }}" class="btn btn-action btn-naranja mt-auto">Registrar Checklist</a>
      </div>
    </div>
  </div>

  <!-- Asignar EPP -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-action h-100 d-flex flex-column align-items-center text-center">
      <div class="card-body d-flex flex-column align-items-center text-center">
        <div class="d-flex justify-content-center align-items-center w-100 mb-2 gap-2">
          <img src="{{ asset('images/workerepp.svg') }}" alt="Asignar EPP" class="icono-action-inline">
          <h5 class="card-title fw-semibold mb-0">Asignar EPP</h5>
        </div>
        <p class="card-text small text-muted">Asignar recursos a trabajadores.</p>
        <a href="{{ route('epp.asignar.create') }}" class="btn btn-action btn-naranja mt-auto">Asignar EPP</a>
      </div>
    </div>
  </div>

  <!-- Checklist No Registrado -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-action h-100 d-flex flex-column align-items-center text-center">
      <div class="card-body d-flex flex-column align-items-center text-center">
        <div class="d-flex justify-content-center align-items-center w-100 mb-2 gap-2">
          <img src="{{ asset('images/checknot.svg') }}" alt="Pendientes" class="icono-action-inline">
          <h5 class="card-title fw-semibold mb-0">Checklist No Registrado</h5>
        </div>
        <p class="card-text small text-muted">Trabajadores sin registro de checklist en el día.</p>
        <button type="button" class="btn btn-action btn-naranja mt-auto" data-bs-toggle="modal" data-bs-target="#modalChecklist">
          Ver pendientes
        </button>
      </div>
    </div>
  </div>
</div>

<!-- 🔶 Checklist diario de EPP -->
<div class="card mt-4 card-outline">
  <div class="card-header bg-modalll text-white text-center">
    <h5 class="mb-0">Checklist registrados hoy</h5>
  </div>
  <div class="card-body">

    <!-- Buscador -->
    <div class="mb-3 d-flex gap-2 flex-wrap">
      <input type="text" id="buscadorTrabajador" class="form-control" style="min-width: 240px;" placeholder="Buscar por nombre del trabajador...">
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
    <table id="tablaChecklistDiario" class="table table-bordered table-striped text-center tabla-epp">
      <thead class="table-header-orange">
        <tr>
          <th>Trabajador</th>
          <th>Lentes</th>
          <th>Botas</th>
          <th>Chaleco</th>
          <th>Guantes</th>
          <th>Arnés</th>
          <th>Altura</th>
          <th>Crítico</th>
          <th>Fecha</th>
          <th>Observaciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($checklists as $c)
          <tr>
            <td>{{ $c->trabajador->name }}</td>
            <td>@if($c->lentes)<img src="{{ asset('images/checkCheck.svg') }}" class="icono-check">@else<img src="{{ asset('images/crossCross.svg') }}" class="icono-cross">@endif</td>
            <td>@if($c->botas)<img src="{{ asset('images/checkCheck.svg') }}" class="icono-check">@else<img src="{{ asset('images/crossCross.svg') }}" class="icono-cross">@endif</td>
            <td>@if($c->chaleco)<img src="{{ asset('images/checkCheck.svg') }}" class="icono-check">@else<img src="{{ asset('images/crossCross.svg') }}" class="icono-cross">@endif</td>
            <td>@if($c->guantes)<img src="{{ asset('images/checkCheck.svg') }}" class="icono-check">@else<img src="{{ asset('images/crossCross.svg') }}" class="icono-cross">@endif</td>
            <td>@if($c->arnes)<img src="{{ asset('images/checkCheck.svg') }}" class="icono-check">@else<img src="{{ asset('images/crossCross.svg') }}" class="icono-cross">@endif</td>
            <td>{!! $c->es_en_altura ? '<span class="badge bg-danger">Sí</span>' : '<span class="badge bg-success">No</span>' !!}</td>
            <td>{!! $c->critico ? '<span class="badge bg-danger">Crítico</span>' : '<span class="badge bg-success">Normal</span>' !!}</td>
            <td>{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
            <td>{{ $c->observaciones }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

<!-- 🔶 Paginación -->
  @if ($checklists->hasPages())
    <div class="mt-3 d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        Mostrando {{ $checklists->firstItem() }} a {{ $checklists->lastItem() }} de {{ $checklists->total() }} registros
      </div>
      <div>
        {{ $checklists->links() }}
      </div>
    </div>
  @else
    <div class="mt-3 d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        Mostrando {{ $checklists->firstItem() }} a {{ $checklists->lastItem() }} de {{ $checklists->total() }} registros
      </div>
      <div>
        <ul class="pagination mb-0">
          <li class="page-item active"><span class="page-link">1</span></li>
        </ul>
      </div>
    </div>
  @endif



  </div>
</div>

<!-- Modal detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header bg-modalll text-white">
        <h5 class="modal-title">Detalle del Trabajador</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detalleContenido">
        Cargando...
      </div>
    </div>
  </div>
</div>

 
<!-- Modal: Checklist sin registrar -->
<div class="modal fade" id="modalChecklist" tabindex="-1" aria-labelledby="modalChecklistLabel" aria-hidden="true">
  <div class="modal-dialog modal-md"> 
    <div class="modal-content">
      
      <div class="modal-header bg-modalllS text-white justify-content-center">
        <h5 class="modal-title fw-bold" id="modalChecklistLabel">
          Checklist sin registrar
        </h5>
        <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" 
                data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <div class="modal-body">
        @if ($sinChecklist->count())
          <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
              <thead class="table-light">
                <tr class="text-orange">
                  <th>Nombre</th>
                  <th>Estado</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($sinChecklist as $usuario)
                  <tr>
                    <td>{{ $usuario->name }}</td>
                    <td>{{ $usuario->estado->nombre ?? 'Sin estado' }}</td>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('usuarios.show', ['usuario' => $usuario->id, 'from' => 'sinChecklist']) }}" 
                          class="btn btn-primary btn-sm">Ver perfil</a>
                        <a href="{{ route('checklist.epp', ['trabajador_id' => $usuario->id, 'from' => 'sinChecklist']) }}" 
                          class="btn btn-success btn-sm">Registrar</a>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

    <!-- Paginación -->
    <div class="mt-3">
      {{ $sinChecklist->appends(['modal' => 'checklist'])->links() }}
    </div>
  @else
    <div class="text-center text-muted">Todos los trabajadores tienen checklist registrado hoy.</div>
  @endif
</div>



      
    </div>
  </div>
</div>



@endsection


@push('styles')
  <link href="{{ asset('css/controlepp.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
  // Checklist sin registrar: traducir al abrir y después de cada paginación
  const modalChecklistEl = document.getElementById('modalChecklist');

  // Función para overlay loader
  function mostrarLoaderOverlay(modalBody) {
    const overlay = document.createElement('div');
    overlay.className = 'loader-overlay d-flex justify-content-center align-items-center';
    overlay.innerHTML = `<div class="spinner-border text-secondary" role="status"></div>`;
    modalBody.style.position = 'relative';
    Object.assign(overlay.style, {
      position: 'absolute', top: 0, left: 0, width: '100%', height: '100%',
      background: 'rgba(255,255,255,0.6)', zIndex: 10
    });
    modalBody.appendChild(overlay);
    return overlay;
  }

  // Traducción de paginación
  function traducirPaginacionEnPane(paneEl) {
    if (!paneEl) return;
    const info = paneEl.querySelector('.small.text-muted') || paneEl.querySelector('.small');
    if (!info) return;
    const m = info.textContent.match(/Showing\s+(\d+)\s+to\s+(\d+)\s+of\s+(\d+)\s+results/i);
    if (m) info.textContent = `Mostrando ${m[1]} a ${m[2]} de ${m[3]} resultados`;
  }

  // Al abrir el modal, traducir paginación
  modalChecklistEl?.addEventListener('shown.bs.modal', () => {
    traducirPaginacionEnPane(modalChecklistEl);
  });

  // Interceptar clicks en paginación
  modalChecklistEl?.addEventListener('click', async (e) => {
    const link = e.target.closest('.pagination a');
    if (!link) return;
    e.preventDefault();

    const body = modalChecklistEl.querySelector('.modal-body');
    const oldHtml = body.innerHTML;
    const loader = mostrarLoaderOverlay(body);

    try {
      const url = new URL(link.href, window.location.origin);
      url.searchParams.set('modal', 'checklist');

      const res = await fetch(url.toString(), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const html = await res.text();

      const doc = new DOMParser().parseFromString(html, 'text/html');
      const newBody = doc.querySelector('#modalChecklist .modal-body');
      if (!newBody) {
        console.warn('No se encontró #modalChecklist .modal-body en la respuesta');
        return;
      }

      body.innerHTML = newBody.innerHTML;
      traducirPaginacionEnPane(body);
    } catch (err) {
      console.error('Error en paginación Checklist sin registrar:', err);
      body.innerHTML = oldHtml;
    } finally {
      loader.remove();
    }
  });

  // Buscador de trabajadores en checklist diario
  const inputBuscar = document.getElementById('buscadorTrabajador');
  const tablaDiario = document.querySelector('#tablaChecklistDiario tbody');

  if (inputBuscar && tablaDiario) {
    inputBuscar.addEventListener('keyup', function () {
      const filtro = inputBuscar.value.toLowerCase().trim();
      const filas = tablaDiario.querySelectorAll('tr');
      filas.forEach(fila => {
        const celdaNombre = fila.cells[0];
        if (!celdaNombre) return;
        const nombre = celdaNombre.textContent.toLowerCase().trim();
        fila.style.display = nombre.startsWith(filtro) ? '' : 'none';
      });
    });
  }
});
</script>
@endpush




