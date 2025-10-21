@extends('layouts.app')

@section('content')
<div class="container py-4">


    <div class="d-flex align-items-center justify-content-start gap-3 mb-4">
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
            ⬅️ Volver
        </a>
        <h2 class="mb-4 text-orange">🔧 Recursos en reparación</h2>
        </div>


    <form method="GET" action="{{ route('reportes.enReparacion') }}" class="row g-3 align-items-end mb-4">
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
            <a href="{{ url('/reportes/recursos-en-reparacion/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}" class="btn btn-danger">
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
                    <th>Número de serie</th>
                    <th>Fecha adquisición</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recursos as $r)
                <tr>
                    <td>{{ $r->nombre }}</td>
                    <td>{{ $r->nro_serie }}</td>
                    <td>{{ $r->fecha_adquisicion }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
    <div class="alert alert-info">No se encontraron recursos en reparación en el rango seleccionado.</div>
    @endif
</div>

<div style="max-width: 100%; height: 220px;">
    <canvas id="graficoReparacion"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoReparacion').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [{
                data: {!! json_encode($valores) !!},
                backgroundColor: [
                    'rgba(255, 140, 0, 0.8)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
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
                    text: 'Recursos en reparación por tipo',
                    color: '#ff6600',
                    font: { size: 16 }
                }
            }
        }
    });
</script>


@endsection
