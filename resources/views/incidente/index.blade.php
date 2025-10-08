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
      </tr>
    @endforeach
  </tbody>
</table>


</div>
@endsection
