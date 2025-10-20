@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Recursos más prestados</h2>

    <div class="mb-3 text-end">
        <a href="{{ url('/reportes/recursos-mas-prestados/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}" class="btn btn-danger">
            🧾 Exportar a PDF
        </a>
    </div>

    <form method="GET" action="{{ route('reportes.masPrestados') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="fecha_inicio" class="form-label">Desde</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
        </div>
        <div class="col-md-4">
            <label for="fecha_fin" class="form-label">Hasta</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Aplicar cambios</button>
        </div>
    </form>

    @if($recursos->count())
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Recurso</th>
                <th>Cantidad de préstamos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recursos as $r)
            <tr>
                <td>{{ $r->nombre }}</td>
                <td>{{ $r->cantidad_prestamos }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="alert alert-info">No se encontraron préstamos en el rango seleccionado.</div>
    @endif
</div>
@endsection
