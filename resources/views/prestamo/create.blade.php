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

      {{-- Mensajes de error --}}
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
        <option value="{{ $t->id }}"
          {{ isset($prestamo) && $prestamo->id_usuario == $t->id ? 'selected' : '' }}>
          {{ $t->name }}
        </option>
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
          {{-- Aquí se insertan las tarjetas dinámicamente --}}
        </div>

        <div class="text-end mt-4">
          <button type="submit" class="btn btn-primary">Guardar Préstamo</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('js/prestamo.js') }}"></script>
@endpush
