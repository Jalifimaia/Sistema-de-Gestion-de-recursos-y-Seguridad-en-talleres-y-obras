@extends('layouts.app')

@section('title', 'Editar préstamo')

@section('content')
<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-editar text-center">
      <h4 class="mb-0">Editar préstamo</h4>
    </div>
    <div class="card-body bg-white">

      <form method="POST" action="{{ route('prestamos.update', $prestamo->id) }}">
        @csrf
        @method('PUT')

        {{-- Fechas (visuales readonly + hidden para envío) --}}
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="fecha_prestamo" class="form-label">Fecha de Préstamo</label>
            <input type="text" class="form-control" value="{{ $prestamo->fecha_prestamo ? \Carbon\Carbon::parse($prestamo->fecha_prestamo)->setTimezone(config('app.timezone'))->format('d/m/Y') : '' }}" readonly>
            <input type="hidden" name="fecha_prestamo" value="{{ $prestamo->fecha_prestamo ? \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('Y-m-d') : '' }}">
          </div>

          <div class="col-md-6">
            <label for="fecha_devolucion" class="form-label">Fecha de Devolución</label>
            <input type="text" class="form-control" value="{{ $prestamo->fecha_devolucion ? \Carbon\Carbon::parse($prestamo->fecha_devolucion)->setTimezone(config('app.timezone'))->format('d/m/Y') : '' }}" readonly>
            <input type="hidden" name="fecha_devolucion" value="{{ $prestamo->fecha_devolucion ? \Carbon\Carbon::parse($prestamo->fecha_devolucion)->format('Y-m-d') : '' }}">
          </div>
        </div>

        {{-- Trabajador (select deshabilitado + hidden enviado) --}}
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="id_trabajador" class="form-label">Trabajador</label>
            <select class="form-select" disabled aria-disabled="true">
              @foreach($trabajadores as $t)
                <option value="{{ $t->id }}" {{ $prestamo->id_usuario == $t->id ? 'selected' : '' }}>
                  {{ $t->name }}
                </option>
              @endforeach
            </select>

            {{-- Hidden que usa el JS; tiene id y name --}}
            <input type="hidden" id="id_trabajador_hidden" name="id_trabajador" value="{{ $prestamo->id_usuario }}">
          </div>
        </div>

        <input type="hidden" name="estado" value="{{ $prestamo->estado }}">

        {{-- Selects para agregar recursos (categoría, subcategoria, recurso, serie) --}}
        <!--<div class="row mb-3">

          <div class="col-md-4">
            <label for="categoria" class="form-label">Categoría</label>
            <select id="categoria" class="form-select">
              <option selected disabled>Seleccione una categoría</option>
              foreach($categorias as $cat)
                <option value="{ $cat->id }}">{ $cat->nombre_categoria }}</option>
              endforeach
            </select> 
          </div>

          <div class="col-md-4">
            <label for="subcategoria" class="form-label">Subcategoría</label>
            <select id="subcategoria" class="form-select">
              <option selected disabled>Seleccione una subcategoría</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="recurso" class="form-label">Recurso</label>
            <select id="recurso" class="form-select">
              <option selected disabled>Seleccione un recurso</option>
            </select>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-10">
            <label for="serie" class="form-label">Serie del Recurso</label>
            <select id="serie" class="form-select">
              <option selected disabled>Seleccione una serie</option>
            </select>
          </div>
          <div class="col-md-2 text-end">
            <button type="button" class="btn btn-success w-100 mt-4" id="agregar">Agregar</button>
          </div>
        </div>-->

        <hr class="hrClase">

        <h5 class="mb-3">Recurso</h5>

        {{-- Contenedor donde el JS agregará tarjetas; las existentes las renderizamos con data-id-serie y hidden inputs --}}
  <div id="contenedorSeries" class="row g-3">
  @foreach ($prestamo->detallePrestamos as $detalle)
    @php
      $serieId = $detalle->id_serie;
      $recursoNombre = $detalle->serieRecurso->recurso->nombre ?? '-';
      $nroSerie = $detalle->serieRecurso->nro_serie ?? '-';

      // Estado desde la relación
      $estadoNombre = $detalle->estadoPrestamo->nombre ?? 'Desconocido';

      // Forzar que "Cancelado" se muestre como "Devuelto"
      if ($estadoNombre === 'Cancelado') {
          $estadoNombre = 'Devuelto';
      }

      // Badge por estado
      switch ($estadoNombre) {
        case 'Activo':   $badgeClass = 'success'; break;
        case 'Vencido':  $badgeClass = 'warning'; break;
        case 'Devuelto': $badgeClass = 'secondary';    break;
        default:         $badgeClass = 'secondary';
      }
    @endphp

    <div class="col-md-4" data-id-serie="{{ $serieId }}">
      <div class="card border {{ $badgeClass === 'danger' ? 'border-danger' : 'border-secondary' }}">
        <div class="card-body">
          <h6 class="card-title mb-1">{{ $recursoNombre }}</h6>
          <p class="card-text mb-2">Serie: <strong>{{ $nroSerie }}</strong></p>

          <span class="badge bg-{{ $badgeClass }}">
            {{ $estadoNombre }}
          </span>

          {{-- Hidden para que el form siga enviando las series ya existentes --}}
          <input type="hidden" name="series[]" value="{{ $serieId }}">
          <input type="hidden" name="detalle_id[]" value="{{ $detalle->id }}">

          {{-- Mostrar botón "Devolver" solo si el estado es Activo o Vencido --}}
          @if (in_array($estadoNombre, ['Activo','Vencido']))
            <button type="button"
                    class="btn btn-sm btn-dev w-100 dar-baja mt-2"
                    data-id="{{ $detalle->id }}">
              Devolver
            </button>
          @endif
        </div>
      </div>
    </div>
  @endforeach
</div>


        <div class="text-start mt-4 d-flex gap-2 justify-content-start">
            <a href="{{ route('prestamos.index') }}" class="btn btn-volver d-inline-flex align-items-center">
              <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
              Volver
            </a>

         <!-- <button type="submit" class="btn btn-guardar">Actualizar Préstamo</button>
       --> </div> 
      </form>
    </div>
  </div>
</div>

{{-- Modal mínimo que usa el JS para confirmación/feedback (opcional) --}}
<div class="modal fade" id="modalRecursoAgregado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <p class="mb-0">Recurso agregado al préstamo</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

{{-- Modal que se muestra al guardar/actualizar --}}
<div class="modal fade" id="modalUpdateSuccess" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Préstamo actualizado</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">{{ session('success') ?? 'El préstamo se actualizó correctamente.' }}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir editando</button>
        <a href="{{ route('prestamos.index') }}" class="btn btn-success">Ver préstamos</a>
      </div>
    </div>
  </div>
</div>

{{-- Modal de confirmación para devolución --}}
<div class="modal fade" id="modalConfirmarDevolucion" tabindex="-1" aria-labelledby="modalConfirmarDevolucionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalConfirmarDevolucionLabel">Confirmar devolución</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que querés devolver este recurso?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarDevolucion">Devolver</button>
      </div>
    </div>
  </div>
</div>


@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {
  let recursoIdSeleccionado = null;

  // Al hacer clic en cualquier botón "Devolver"
  document.querySelectorAll('.dar-baja').forEach(btn => {
    btn.addEventListener('click', function () {
      recursoIdSeleccionado = this.dataset.id; // guardamos el id del detalle
      const modal = new bootstrap.Modal(document.getElementById('modalConfirmarDevolucion'));
      modal.show();
    });
  });

  // Al confirmar en el modal
  document.getElementById('btnConfirmarDevolucion').addEventListener('click', function () {
    if (recursoIdSeleccionado) {
      // acá podés disparar tu lógica de devolución (AJAX o submit)
      // ejemplo simple: enviar un form oculto
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/prestamos/${recursoIdSeleccionado}/devolver`; // ajustá la ruta
      form.innerHTML = `
        @csrf
        @method('PUT')
      `;
      document.body.appendChild(form);
      form.submit();
    }
  });
});
</script>


  {{-- Exponemos detalles para que el JS los use si los necesita --}}
  <script>window.detalles = @json($detalles ?? []); window.seriesOcultas = @json($seriesOcultas ?? []);</script>

  {{-- Incluimos el script corregido --}}
  <script src="{{ asset('js/prestamo.js') }}"></script>

  {{-- Mostrar modal de éxito después de actualizar si existe session('success') --}}
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    @if(session('success'))
      const modalEl = document.getElementById('modalUpdateSuccess');
      const msg = @json(session('success'));
      if (modalEl && typeof bootstrap !== 'undefined') {
        // limpiar backdrops previos por seguridad
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      } else if (msg) {
        // fallback simple si bootstrap no está disponible
        alert(msg);
      }
    @endif
  });
  </script>
@endpush

@push('styles')
<link href="{{ asset('css/editarPrestamo.css') }}" rel="stylesheet">



@endpush
