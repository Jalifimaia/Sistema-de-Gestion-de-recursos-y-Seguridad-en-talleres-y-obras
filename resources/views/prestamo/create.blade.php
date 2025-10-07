@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Registrar Préstamo</h3>

  <form method="POST" action="{{ route('prestamos.store') }}">
    @csrf

    <div class="mb-3">
      <label for="fecha_prestamo" class="form-label">Fecha de Préstamo</label>
      <input type="date" name="fecha_prestamo" id="fecha_prestamo" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="fecha_devolucion" class="form-label">Fecha de Devolución (opcional)</label>
      <input type="date" name="fecha_devolucion" id="fecha_devolucion" class="form-control">
    </div>

    <div class="mb-3">
      <label for="estado" class="form-label">Estado</label>
      <select name="estado" id="estado" class="form-select" required>
        <option value="3">Prestado</option>
        <option value="4">Devuelto</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="id_serie" class="form-label">Serie de Recurso</label>
      <select name="id_serie" id="id_serie" class="form-select" required>
        <option value="">Seleccione una serie</option>
        @foreach($series as $serie)
          <option value="{{ $serie->id }}">
            {{ $serie->recurso }} - Serie {{ $serie->nro_serie }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label for="id_estado_prestamo" class="form-label">Estado del Préstamo</label>
      <select name="id_estado_prestamo" id="id_estado_prestamo" class="form-select" required>
        <option value="">Seleccione estado</option>
        @foreach($estadosPrestamo as $estado)
          <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
        @endforeach
      </select>
    </div>


    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
@endsection
