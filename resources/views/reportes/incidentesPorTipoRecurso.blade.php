@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Incidentes por tipo de recurso</h2>

    <div class="mb-3 text-end">
        <a href="{{ url('/reportes/incidentes-por-tipo/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}" class="btn btn-danger">
            🧾 Exportar a PDF
        </a>
    </div>

    <form method="GET" action="{{ route('reportes.incidentesPorTipo') }}" class="row g-3 mb-4">
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

    @if($incidentes->count())
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Tipo de recurso</th>
                <th>Cantidad de incidentes</th>
                <th>Última fecha de incidente</th>

            </tr>
        </thead>
        <tbody>
            @foreach($incidentes as $item)
            <tr>
                <td>{{ $item->nombre_categoria }}</td>
                <td>{{ $item->cantidad_incidentes }}</td>
                <td>{{ $item->ultima_fecha }}</td>

            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="alert alert-info">No se encontraron incidentes en el rango seleccionado.</div>
    @endif
</div>

<div style="max-width: 100%; height: 220px;">
    <canvas id="graficoIncidentes"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoIncidentes').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [{
                data: {!! json_encode($valores) !!},
                backgroundColor: [
                    'rgba(255, 140, 0, 0.8)', // naranja para Herramienta
                    'rgba(255, 206, 86, 0.7)' // amarillo para EPP
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#333',
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Incidentes por tipo de recurso',
                    color: '#ff6600',
                    font: { size: 16 }
                }
            }
        }
    });
</script>



@endsection
