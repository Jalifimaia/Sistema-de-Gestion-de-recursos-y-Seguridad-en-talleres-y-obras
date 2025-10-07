@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Préstamos Registrados</h3>
    <a href="{{ route('prestamos.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Nuevo Préstamo
    </a>
  </div>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Operario</th>
        <th>Recurso</th>
        <th>N° Serie</th>
        <th>Fecha Préstamo</th>
        <th>Fecha Devolución</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($prestamos as $p)
      <tr>
        <td>{{ $p->id }}</td>
        <td>{{ $p->operario }}</td>
        <td>{{ $p->recurso }}</td>
        <td>{{ $p->nro_serie }}</td>
        <td>{{ $p->fecha_prestamo }}</td>
        <td>{{ $p->fecha_devolucion ?? 'Pendiente' }}</td>
        <td>{{ $p->estado }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
