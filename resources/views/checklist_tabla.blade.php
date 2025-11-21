@extends('layouts.app')

@section('title', 'Checklist diario de EPP')

@section('content')
<div class="container py-4">

  <!-- 🔶 Encabezado -->
  <header class="mb-5 py-3 px-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
  <a href="#"
   class="btn btn-volver d-flex align-items-center"
   onclick="handleBackClick()">
    <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
    Volver
</a>
      <div class="text-center w-100 mt-3 d-flex justify-content-center align-items-center gap-2">
        <img src="{{ asset('images/check.svg') }}" alt="Checklist" class="icono-titulo">
        <h1 class="titulo-checklist mb-0">Checklist diario de cumplimiento de EPP</h1>
      </div>
    </div>
  </header>

  <!-- 🔶 Buscador -->
  <div class="mb-4 d-flex gap-2 flex-wrap">
    <input type="text" id="buscadorTrabajador" class="form-control" style="min-width: 240px;" placeholder="Buscar por nombre del trabajador...">

  </div>

  <!-- 🔶 Tabla checklist diario -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <!--<h5 class="card-title fw-bold text-center">Registro de hoy</h5>-->
      <div class="table-responsive">
  <table id="tablaChecklistDiario" class="table table-bordered text-center tabla-epp">
    <thead>
      <tr>
        <th>Trabajador</th>
        <th>Anteojos</th>
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
        <td>{!! $c->anteojos ? '<span style="color:green;">✔️</span>' : '<span style="color:red;">❌</span>' !!}</td>
        <td>{!! $c->botas ? '<span style="color:green;">✔️</span>' : '<span style="color:red;">❌</span>' !!}</td>
        <td>{!! $c->chaleco ? '<span style="color:green;">✔️</span>' : '<span style="color:red;">❌</span>' !!}</td>
        <td>{!! $c->guantes ? '<span style="color:green;">✔️</span>' : '<span style="color:red;">❌</span>' !!}</td>
        <td>{!! $c->arnes ? '<span style="color:green;">✔️</span>' : '<span style="color:red;">❌</span>' !!}</td>
        <td>{!! $c->es_en_altura ? '<span class="badge bg-danger">Sí</span>' : '<span class="badge bg-success">No</span>' !!}</td>
        <td>
          @if($c->critico)
            <span class="badge bg-danger">Crítico</span>
          @else
            <span class="badge bg-success">OK</span>
          @endif
        </td>
        <td>{{ \Carbon\Carbon::parse($c->hora)->format('d/m/Y H:i') }}</td>
        <td>{{ $c->observaciones ?? '—' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

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
