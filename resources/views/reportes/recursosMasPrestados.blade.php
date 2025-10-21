@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-start gap-3 mb-4">
    <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
        ⬅️ Volver
    </a>
    <h2 class="mb-4 text-orange">📦 Recursos más prestados</h2>
    </div>



    <form method="GET" action="{{ route('reportes.masPrestados') }}" class="row g-3 align-items-end mb-4">
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
            <a href="{{ url('/reportes/recursos-mas-prestados/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}" class="btn btn-danger">
                🧾 Exportar a PDF
            </a>
        </div>
    </form>

    @if($recursos->count())
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr class="text-orange">
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
                backgroundColor: 'rgba(255, 140, 0, 0.7)',
                borderColor: 'rgba(255, 140, 0, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Recursos más prestados',
                    color: '#ff6600',
                    font: { size: 16 }
                },
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
