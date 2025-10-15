@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Incidentes registrados</h2>
    <a href="{{ route('incidente.create') }}" class="btn btn-primary mb-3">Registrar nuevo incidente</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

   <table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Trabajador</th>
            <th>Categoría</th>
            <th>Subcategoría</th>
            <th>Recurso</th>
            <th>Motivo</th>
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
            <td>{{ $incidente->trabajador?->name ?? '-' }}</td>

            <!-- Categorías -->
            <td>
                @foreach($incidente->recursos as $recurso)
                    {{ $recurso->subcategoria?->categoria?->nombre_categoria ?? '-' }}<br>
                @endforeach
            </td>

            <!-- Subcategorías -->
            <td>
                @foreach($incidente->recursos as $recurso)
                    {{ $recurso->subcategoria?->nombre ?? '-' }}<br>
                @endforeach
            </td>

            <!-- Recursos -->
            <td>
                @foreach($incidente->recursos as $recurso)
                    {{ $recurso->nombre ?? '-' }}
                    @if($recurso->pivot?->id_serie_recurso)
                        (Serie: {{ $recurso->pivot->id_serie_recurso }})
                    @endif
                    <br>
                @endforeach
            </td>

            <td>{{ $incidente->descripcion ?? '-' }}</td>
            <td>{{ $incidente->estadoIncidente?->nombre_estado ?? '-' }}</td>
            <td>{{ $incidente->resolucion ?? '-' }}</td>
            <td>{{ $incidente->fecha_incidente ?? '-' }}</td>
            <td>
                <a href="{{ route('incidente.edit', $incidente->id) }}" class="btn btn-sm btn-warning">Editar</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>
@endsection
