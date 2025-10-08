@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Registrar nuevo incidente</h2>

  <form method="POST" action="{{ route('incidente.store') }}">
    @csrf

    <div class="mb-3">
      <label for="id_recurso">Recurso</label>
      <select name="id_recurso" class="form-select" required>
        <option value="">-- Seleccionar recurso --</option>
        @foreach($recursos as $recurso)
          <option value="{{ $recurso->id }}">{{ $recurso->nombre }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label for="id_estado_incidente">Estado del incidente</label>
      <select name="id_estado_incidente" class="form-select" required>
        <option value="">-- Seleccionar estado --</option>
        @foreach($estados as $estado)
          <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label for="descripcion">Descripción general</label>
      <input type="text" name="descripcion" class="form-control">
    </div>

    <div class="mb-3">
      <label for="fecha_incidente">Fecha del incidente</label>
      <input type="datetime-local" name="fecha_incidente" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="resolucion">Resolución</label>
      <input type="text" name="resolucion" class="form-control">
    </div>


    <input type="hidden" name="id_supervisor" value="{{ auth()->id() }}">

    <button type="submit" class="btn btn-success">Registrar incidente</button>
  </form>
</div>
@endsection
