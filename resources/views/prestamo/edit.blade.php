@extends('layouts.app')

@section('template_title')
  Editar Préstamo
@endsection

@section('content')
<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-warning text-dark text-center">
      <h4 class="mb-0">Editar Préstamo</h4>
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

      <form method="POST" action="{{ route('prestamos.update', $prestamo->id) }}">
        @csrf
        @method('PUT')

        {{-- Fechas --}}
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="fecha_prestamo" class="form-label">Fecha de Préstamo</label>
            <input type="date" name="fecha_prestamo" class="form-control"
              value="{{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('Y-m-d') }}"
              {{ $prestamo->estado == 3 ? 'readonly' : '' }} required>
          </div>
          <div class="col-md-6">
            <label for="fecha_devolucion" class="form-label">Fecha de Devolución</label>
            <input type="date" name="fecha_devolucion" class="form-control"
              value="{{ $prestamo->fecha_devolucion ? \Carbon\Carbon::parse($prestamo->fecha_devolucion)->format('Y-m-d') : '' }}"
              {{ $prestamo->estado == 3 ? 'readonly' : '' }}>
          </div>
        </div>

        {{-- Trabajador asignado --}}
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="id_trabajador" class="form-label">Trabajador</label>
            <select name="id_trabajador" class="form-select" required>
              <option selected disabled>Seleccione un trabajador</option>
              @foreach($trabajadores as $t)
                <option value="{{ $t->id }}"
                  {{ $prestamo->id_usuario == $t->id ? 'selected' : '' }}>
                  {{ $t->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <input type="hidden" name="estado" value="{{ $prestamo->estado }}">

        {{-- Filtros para agregar recursos --}}
        <div class="row mb-3">
          <div class="col-md-4">
            <label for="categoria" class="form-label">Categoría</label>
            <select id="categoria" class="form-select">
              <option selected disabled>Seleccione una categoría</option>
              @foreach($categorias as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nombre_categoria }}</option>
              @endforeach
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
        </div>

    <hr>
<h5 class="mb-3">Recursos prestados</h5>
<div id="contenedorSeries" class="row g-3">
  @foreach ($prestamo->detallePrestamos as $detalle)
    @php
      $estado = $detalle->id_estado_prestamo;
      $baja = $estado == 4;
    @endphp
    <div class="col-md-4">
      <div class="card border {{ $baja ? 'border-danger' : 'border-secondary' }}">
        <div class="card-body">
          <h6 class="card-title mb-1">{{ $detalle->serieRecurso->recurso->nombre }}</h6>
          <p class="card-text mb-2">Serie: <strong>{{ $detalle->serieRecurso->nro_serie }}</strong></p>
          <span class="badge bg-{{ $baja ? 'danger' : 'secondary' }}">
            {{ $baja ? 'Dado de baja' : 'Asignado' }}
          </span>

          @if (!$baja)
            <button type="button"
                    class="btn btn-sm btn-outline-danger w-100 dar-baja mt-2"
                    data-id="{{ $detalle->id }}">
              Dar de baja
            </button>
          @endif
        </div>
      </div>
    </div>
  @endforeach
</div>


        <div class="text-end mt-4">
          <button type="submit" class="btn btn-warning">Actualizar Préstamo</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script>
    window.detalles = @json($detalles);
  </script>
  <script src="{{ asset('js/prestamo.js') }}"></script>
@endpush
