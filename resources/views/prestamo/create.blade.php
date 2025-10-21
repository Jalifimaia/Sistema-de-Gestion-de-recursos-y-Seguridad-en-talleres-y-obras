@extends('layouts.app')

@section('template_title')
  Registrar Préstamo
@endsection

@section('content')
<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white text-center">
      <h4 class="mb-0">Registrar Préstamo</h4>
    </div>
    <div class="card-body bg-white">

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('prestamos.store') }}">
        @csrf

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="fecha_prestamo" class="form-label">Fecha de Préstamo</label>
            <input type="date" name="fecha_prestamo" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label for="fecha_devolucion" class="form-label">Fecha de Devolución (opcional)</label>
            <input type="date" name="fecha_devolucion" class="form-control">
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="id_trabajador" class="form-label">Trabajador</label>
            <select name="id_trabajador" class="form-select" required>
              <option selected disabled>Seleccione un trabajador</option>
              @foreach($trabajadores as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <input type="hidden" name="estado" value="2">

        <div class="row mb-3">
          <div class="col-md-4">
            <label for="categoria" class="form-label">Categoría</label>
            <select id="categoria" class="form-select" required>
              <option selected disabled>Seleccione una categoría</option>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nombre_categoria }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label for="subcategoria" class="form-label">Subcategoría</label>
            <select id="subcategoria" class="form-select" required>
              <option selected disabled>Seleccione una subcategoría</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="recurso" class="form-label">Recurso</label>
            <select id="recurso" class="form-select" required>
              <option selected disabled>Seleccione un recurso</option>
            </select>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-10">
            <label for="serie" class="form-label">Serie del Recurso</label>
            <select id="serie" class="form-select" required>
              <option selected disabled>Seleccione una serie</option>
            </select>
          </div>
          <div class="col-md-2 text-end">
            <button type="button" class="btn btn-success w-100 mt-4" id="agregar">Agregar</button>
          </div>
        </div>

        <hr>
        <h5 class="mb-3">Recursos seleccionados</h5>
        <div id="contenedorSeries" class="row g-3">
          {{-- Tarjetas dinámicas --}}
        </div>

        <div class="text-end mt-4">
          <a href="{{ route('prestamos.index') }}" class="btn btn-outline-secondary">
          ⬅️ Volver
        </a>

          <button type="submit" class="btn btn-primary">Guardar Préstamo</button>
        </div>
      </form>
    </div>
  </div>
  <!-- ✅ Modal: Recurso agregado -->
<div class="modal fade" id="modalRecursoAgregado" tabindex="-1" aria-labelledby="modalRecursoAgregadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalRecursoAgregadoLabel">✅ Recurso agregado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        El recurso fue agregado correctamente a la lista de préstamo.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Modal: Préstamo guardado -->
@if(session('success'))
<div class="modal fade" id="modalPrestamoGuardado" tabindex="-1" aria-labelledby="modalPrestamoGuardadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalPrestamoGuardadoLabel">🎉 Préstamo guardado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        {{ session('success') }}
      </div>
      <div class="modal-footer">
        <a href="{{ route('prestamos.index') }}" class="btn btn-outline-primary">Ver préstamos</a>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>
@endif

</div>
@endsection

@push('scripts')
  <script src="{{ asset('js/prestamo.js') }}"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    // Mostrar modal de recurso agregado
    document.getElementById('agregar').addEventListener('click', function () {
      const modal = new bootstrap.Modal(document.getElementById('modalRecursoAgregado'));
      modal.show();
    });

    // Mostrar modal de préstamo guardado si hay sesión
    @if(session('success'))
      const modal = new bootstrap.Modal(document.getElementById('modalPrestamoGuardado'));
      modal.show();
    @endif
  });
</script>
@endpush
