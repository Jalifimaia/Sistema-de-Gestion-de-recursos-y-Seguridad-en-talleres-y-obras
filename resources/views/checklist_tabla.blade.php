@extends('layouts.app')

@section('title', 'Checklist diario de EPP')

@section('content')
<div class="container py-4">

  <!-- 🔶 Encabezado -->
<header class="mb-5 py-3 px-4">
  <div class="d-flex align-items-center gap-3 flex-wrap">
    <a href="#"
       class="btn btn-volver d-flex align-items-center"
       onclick="handleBackClick()">
      <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
      Volver
    </a>

    <div class="d-flex align-items-center gap-2">
      <img src="{{ asset('images/checkk.svg') }}" alt="Checklist" class="icono-titulo">
      <h1 class="titulo-checklist fw-bold mb-0">Checklist diario de cumplimiento de EPP</h1>
    </div>
  </div>
</header>


  <!-- 🔶 Buscador -->
  <div class="mb-4 d-flex gap-2 flex-wrap">
    <input type="text" id="buscadorTrabajador" class="form-control" style="min-width: 240px;" placeholder="Buscar por nombre del trabajador...">

  </div>

  <!-- 🔶 Tabla checklist diario -->

    <div class="card-body">
      <!--<h5 class="card-title fw-bold text-center">Registro de hoy</h5>-->
      <div class="table-responsive">
  <table id="tablaChecklistDiario" class="table table-bordered table-striped text-center tabla-epp">
    <thead>
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

    </div>


  <!-- 🔶 Modal detalle -->
  <div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalle del Trabajador</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="detalleContenido">
          Cargando...
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const inputBuscar = document.getElementById('buscadorTrabajador');
  const tablaDiario = document.querySelector('#tablaChecklistDiario tbody');

  if (!inputBuscar || !tablaDiario) return;

  inputBuscar.addEventListener('keyup', function () {
    const filtro = inputBuscar.value.toLowerCase().trim();

    const filas = tablaDiario.querySelectorAll('tr');
    filas.forEach(fila => {
      const celdaNombre = fila.cells[0]; // primera columna
      if (!celdaNombre) return;

      const nombre = celdaNombre.textContent.toLowerCase().trim();
      fila.style.display = nombre.startsWith(filtro) ? '' : 'none';
    });
  });
});
</script>

<script>
function handleBackClick() {
    const fromDashboard = sessionStorage.getItem('fromDashboard');
    if (fromDashboard) {
        // Limpio el flag para no arrastrarlo
        sessionStorage.removeItem('fromDashboard');
        window.location.href = "{{ route('dashboard') }}";
    } else {
        window.location.href = "{{ route('controlEPP') }}";
    }
}
</script>

@push('styles')
<link href="{{ asset('css/checklistTabla.css') }}" rel="stylesheet">
@endpush

@endpush
