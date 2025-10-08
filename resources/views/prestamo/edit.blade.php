@extends('layouts.app')

@section('template_title')
  Editar Préstamo #{{ $prestamo->id }}
@endsection

@section('content')
<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-warning text-dark text-center">
      <h4 class="mb-0">Editar Préstamo #{{ $prestamo->id }}</h4>
    </div>
    <div class="card-body bg-white">
      <form method="POST" action="{{ route('prestamos.update', $prestamo->id) }}">
        @csrf
        @method('PATCH')

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="fecha_prestamo" class="form-label">Fecha de Préstamo</label>
            <input type="date" name="fecha_prestamo" class="form-control" value="{{ $prestamo->fecha_prestamo->format('Y-m-d') }}" required>
          </div>
          <div class="col-md-6">
            <label for="fecha_devolucion" class="form-label">Fecha de Devolución</label>
            <input type="date" name="fecha_devolucion" class="form-control" value="{{ optional($prestamo->fecha_devolucion)->format('Y-m-d') }}">
          </div>
        </div>

        <input type="hidden" name="estado" value="2"> {{-- Activo --}}

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

        <div class="row mb-3">
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
        <h5>Recursos ya prestados</h5>
        <table class="table table-bordered text-center mb-4">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Recurso</th>
              <th>Serie</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($prestamo->detallePrestamos as $i => $detalle)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ optional($detalle->serieRecurso->recurso)->nombre ?? '—' }}</td>
                <td>{{ optional($detalle->serieRecurso)->nro_serie ?? '—' }}</td>
                <td>{{ optional($detalle->estadoPrestamo)->nombre ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <h5>Recursos nuevos a agregar</h5>
        <table class="table table-bordered text-center" id="tablaPrestamos">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Recurso</th>
              <th>Serie</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>

        <div class="text-end mt-3">
          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
          <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
  <script>
    window.detalles = @json($detalles);
  </script>
  <script src="{{ asset('js/prestamo.js') }}"></script>
@endsection

