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
                <th>Última fecha de préstamo</th>

            </tr>
        </thead>
        <tbody>
            @foreach($recursos as $r)
            <tr>
                <td>{{ $r->nombre }}</td>
                <td>{{ $r->cantidad_prestamos }}</td>
                <td>{{ $r->ultima_fecha }}</td>

            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="alert alert-info">No se encontraron préstamos en el rango seleccionado.</div>
    @endif
</div>
<div style="width: 100%; max-width: 100%; height: 220px;">
    <canvas id="graficoPrestamos"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoPrestamos').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [{
                label: 'Cantidad de préstamos',
                data: {!! json_encode($valores) !!},
                backgroundColor: 'rgba(255, 140, 0, 0.7)', // naranja suave
                borderColor: 'rgba(255, 140, 0, 1)',       // naranja fuerte
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#ff6600',
                    bodyColor: '#333',
                    borderColor: '#ff6600',
                    borderWidth: 1
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#ff6600',
                        font: { size: 12 }
                    },
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#333',
                        font: { size: 12 }
                    },
                    grid: { color: '#eee' }
                }
            }
        }
    });
</script>



@endsection
