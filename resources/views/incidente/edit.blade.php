@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Editar incidente</h2>

  <form method="POST" action="{{ route('incidente.update', $incidente->id) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label for="id_recurso">Recurso</label>
      <select name="id_recurso" class="form-select" required>
        @foreach($recursos as $recurso)
          <option value="{{ $recurso->id }}" {{ $incidente->id_recurso == $recurso->id ? 'selected' : '' }}>
            {{ $recurso->nombre }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label for="id_estado_incidente">Estado del incidente</label>
      <select name="id_estado_incidente" class="form-select" required>
        @foreach($estados as $estado)
          <option value="{{ $estado->id }}" {{ $incidente->id_estado_incidente == $estado->id ? 'selected' : '' }}>
            {{ $estado->nombre_estado }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label for="descripcion">Descripción general</label>
      <input type="text" name="descripcion" class="form-control" value="{{ $incidente->descripcion }}">
    </div>

    <div class="mb-3">
      <label for="fecha_incidente">Fecha del incidente</label>
      <input type="datetime-local" name="fecha_incidente" class="form-control"
        value="{{ \Carbon\Carbon::parse($incidente->fecha_incidente)->format('Y-m-d\TH:i') }}" required>
    </div>

    <div class="mb-3">
      <label for="resolucion">Resolución</label>
      <input type="text" name="resolucion" class="form-control" value="{{ $incidente->resolucion }}">
    </div>

    <input type="hidden" name="id_supervisor" value="{{ $incidente->id_supervisor }}">

    <button type="submit" class="btn btn-success">Actualizar incidente</button>
    <a href="{{ route('incidente.index') }}" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
@endsection
