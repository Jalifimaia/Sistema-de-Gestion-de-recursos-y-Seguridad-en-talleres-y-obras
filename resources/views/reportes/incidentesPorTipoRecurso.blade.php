
@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex align-items-center justify-content-start gap-3 mb-4">
            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
                ⬅️ Volver
            </a>
        <h2 class="mb-4 text-orange">🚨 Incidentes por tipo de recurso</h2>
            </div>

    <form method="GET" action="{{ route('reportes.incidentesPorTipo') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
            <label for="fecha_inicio" class="form-label">Desde</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
        </div>
        <div class="col-md-3">
            <label for="fecha_fin" class="form-label">Hasta</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
        </div>
        <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-orange">🔍 Aplicar filtros</button>
        </div>
        <div class="col-md-3 d-grid">
            <a href="{{ url('/reportes/incidentes-por-tipo/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}" class="btn btn-danger">
                🧾 Exportar a PDF
            </a>
        </div>
    </form>

    @if($incidentes->count())
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr class="text-orange">
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
                    'rgba(255, 206, 86, 0.7)', // amarillo para EPP
                    'rgba(54, 162, 235, 0.7)', // azul para otros
                    'rgba(255, 99, 132, 0.7)', // rojo para críticos
                    'rgba(75, 192, 192, 0.7)'  // verde para leves
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
