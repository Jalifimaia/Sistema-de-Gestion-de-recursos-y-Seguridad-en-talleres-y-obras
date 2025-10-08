@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Incidentes registrados</h2>
  <a href="{{ route('incidente.create') }}" class="btn btn-primary mb-3">Registrar nuevo incidente</a>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Recurso</th>
        <th>Descripción</th>
        <th>Estado</th>
        <th>Resolución</th>
        <th>Fecha</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @foreach($incidentes as $incidente)
        <tr>
          <td>{{ $incidente->id }}</td>
          <td>{{ $incidente->recurso->nombre ?? '-' }}</td>
          <td>{{ $incidente->descripcion ?? '-' }}</td>
          <td>{{ $incidente->estado->nombre_estado ?? '-' }}</td>
          <td>{{ $incidente->resolucion ?? '-' }}</td>
          <td>{{ $incidente->fecha_incidente }}</td>
          <td>
            <a href="{{ route('incidente.edit', $incidente->id) }}" class="btn btn-sm btn-warning">Editar</a>
            <form action="{{ route('incidente.destroy', $incidente->id) }}" method="POST" style="display:inline-block;">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este incidente?')">Eliminar</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
