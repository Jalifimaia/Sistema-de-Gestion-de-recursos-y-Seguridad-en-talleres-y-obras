@extends('layouts.app')

@section('title', 'Incidentes')

@section('content')
<div class="container py-4">
    <!-- Fecha arriba a la derecha -->
    <div class="row mb-2">
        <div class="col-12 text-end text-muted small pt-1">
            Fecha: <strong id="today" class="text-nowrap"></strong>
        </div>
    </div>

    <!-- Título -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="text-center text-orange">📋 Incidentes registrados</h1>
        </div>
    </div>

    <div class="mb-3 text-center">
        <a href="{{ route('incidente.create') }}" class="btn btn-orange">+ Registrar nuevo incidente</a>
    </div>

    @if(session('success'))
        <div id="alertaEstado" class="alert alert-success alert-dismissible fade show" role="alert">
            <span id="mensajeAlertaEstado">{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-naranja align-middle mb-0">
                    <thead>
                        <tr>
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
                            <td>{{ $incidente->trabajador?->name ?? '-' }}</td>
                            <td>
                                @foreach($incidente->recursos as $recurso)
                                    {{ $recurso->subcategoria?->categoria?->nombre_categoria ?? '-' }}<br>
                                @endforeach
                            </td>
                            <td>
                                @foreach($incidente->recursos as $recurso)
                                    {{ $recurso->subcategoria?->nombre ?? '-' }}<br>
                                @endforeach
                            </td>
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
                            <td>{{ \Carbon\Carbon::parse($incidente->fecha_incidente)->format('d/m/Y H:i') }}</td>

                            <td>
                                <a href="{{ route('incidente.edit', $incidente->id) }}" class="btn btn-sm btn-orange">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const today = document.getElementById('today');
    const alerta = document.getElementById('alertaEstado');

    const ahora = new Date();
    const opciones = { day: '2-digit', month: 'long', year: 'numeric' };
    today.textContent = ahora.toLocaleDateString('es-AR', opciones);

    if (alerta) {
        setTimeout(() => {
            alerta.classList.add('fade');
            alerta.classList.remove('show');
            alerta.addEventListener('transitionend', () => {
                alerta.remove();
            }, { once: true });
        }, 5000);
    }
});
</script>
@endpush
