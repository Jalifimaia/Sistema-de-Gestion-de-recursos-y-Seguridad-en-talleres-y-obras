@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-orange">📋 Préstamos registrados</h2>

    <form method="GET" action="{{ route('reportes.prestamos') }}" class="row g-3 align-items-end mb-4">
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
            <a href="{{ url('/reportes/prestamos/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}" class="btn btn-danger">
                🧾 Exportar a PDF
            </a>
        </div>
    </form>

    @if($prestamos->count())
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr class="text-orange">
                    <th>Fecha préstamo</th>
                    <th>Trabajador</th>
                    <th>Estado</th>
                    <th>Duración</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prestamos as $p)
                <tr>
                    <td>{{ $p->fecha_prestamo }}</td>
                    <td>{{ $p->trabajador }}</td>
                    <td>{{ $p->estado }}</td>
                    <td>{{ $p->duracion }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
    <div class="alert alert-info">No se encontraron préstamos recientes.</div>
    @endif

</div>
@endsection


@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const prestamos = @json($prestamos);

    const fechas = prestamos.map(p => p.fecha_prestamo.split(' ')[0]);
    const estados = prestamos.map(p => p.estado);

    const fechaCount = fechas.reduce((acc, f) => {
        acc[f] = (acc[f] || 0) + 1;
        return acc;
    }, {});

    const estadoCount = estados.reduce((acc, e) => {
        acc[e] = (acc[e] || 0) + 1;
        return acc;
    }, {});

    new Chart(document.getElementById('graficoBarras'), {
        type: 'bar',
        data: {
            labels: Object.keys(fechaCount),
            datasets: [{
                label: 'Préstamos por día',
                data: Object.values(fechaCount),
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });

    new Chart(document.getElementById('graficoTorta'), {
        type: 'pie',
        data: {
            labels: Object.keys(estadoCount),
            datasets: [{
                label: 'Distribución por estado',
                data: Object.values(estadoCount),
                backgroundColor: ['#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d']
            }]
        },
        options: {
            responsive: true
        }
    });
</script>



@endsection
