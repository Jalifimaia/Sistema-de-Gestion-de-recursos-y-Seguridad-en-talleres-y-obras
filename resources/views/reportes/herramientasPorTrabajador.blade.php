@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-orange">🧰 Herramientas asignadas por trabajador</h2>

    <form method="GET" action="{{ route('reportes.herramientasPorTrabajador') }}" class="row g-3 align-items-end mb-4">
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
            <a href="{{ url('/reportes/herramientas-por-trabajador/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}" class="btn btn-danger">
                🧾 Exportar a PDF
            </a>
        </div>
    </form>

    @if($herramientas->count())
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr class="text-orange">
                    <th>Trabajador</th>
                    <th>Herramienta</th>
                    <th>Subcategoría</th>
                    <th>Número de serie</th>
                    <th>Fecha de adquisición</th>
                </tr>
            </thead>
            <tbody>
                @foreach($herramientas as $item)
                <tr>
                    <td>{{ $item->trabajador }}</td>
                    <td>{{ $item->herramienta }}</td>
                    <td>{{ $item->subcategoria }}</td>
                    <td>{{ $item->nro_serie }}</td>
                    <td>{{ $item->fecha_adquisicion }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @else
    <div class="alert alert-info">No se encontraron asignaciones en el rango seleccionado.</div>
    @endif
</div>

<div style="max-width: 100%; height: 220px;">
    <canvas id="graficoHerramientas"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoHerramientas').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [{
                label: 'Cantidad de herramientas',
                data: {!! json_encode($valores) !!},
                backgroundColor: 'rgba(255, 140, 0, 0.7)',
                borderColor: 'rgba(255, 140, 0, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Herramientas asignadas por trabajador',
                    color: '#ff6600',
                    font: { size: 16 }
                },
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { color: '#333' },
                    grid: { color: '#eee' }
                },
                y: {
                    ticks: { color: '#ff6600' },
                    grid: { display: false }
                }
            }
        }
    });
</script>


@endsection
