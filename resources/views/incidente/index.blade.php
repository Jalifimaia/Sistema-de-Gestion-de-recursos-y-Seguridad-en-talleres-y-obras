@extends('layouts.app')

@section('content')

<div class="container py-4">
    <header class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Incidentes registrados</h1>
        </div>
        <div class="text-muted small">
            Fecha actual: <strong id="today"></strong>
        </div>

            
    </header>
        
        
    
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

            <td>{{ $incidente->descripcion ?? 'Sin motivo' }}</td>
            <td>{{ $incidente->estadoIncidente?->nombre_estado ?? '-' }}</td>
            <td>{{ $incidente->resolucion ?? 'Sin resolución' }}</td>
            <td>
                {{ $incidente->fecha_incidente 
                    ? \Carbon\Carbon::parse($incidente->fecha_incidente)->format('d/m/Y H:i') 
                    : '-' }}
            </td>

            <td>
                <a href="{{ route('incidente.edit', $incidente->id) }}" class="btn btn-sm btn-warning">Editar</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>



@endsection
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const today = new Date();
    const dia = String(today.getDate()).padStart(2, '0');
    const mes = String(today.getMonth() + 1).padStart(2, '0');
    const año = today.getFullYear();
    document.getElementById('today').textContent = `${dia}/${mes}/${año}`;
  });
</script>




