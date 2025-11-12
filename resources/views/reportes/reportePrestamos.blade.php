@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-start gap-3 mb-4">
    <a href="{{ route('reportes.index') }}" class="btn btn-volver d-flex align-items-center">

    <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
    Volver
    </a>

    <div class="d-flex align-items-center">
    <img src="{{ asset('images/prestamo.svg') }}" alt="Préstamos" style="width: 28px; height: 28px;" class="me-2">
    <h4 class="fw-bold text-orange mb-0">Préstamos registrados</h4>
  </div>

  <div class="ms-auto">
    <button type="button" class="btn btn-ver-grafico" data-bs-toggle="modal" data-bs-target="#modalGraficos">
      <img src="{{ asset('images/grafico.svg') }}" alt="Gráfico" class="me-2" style="width: 18px; height: 18px;">
      Ver gráficos
    </button>
  </div>
    </div>

    <form method="GET" action="{{ route('reportes.prestamos') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
            <label for="fecha_inicio" class="form-label fw-bold fw-bold">Desde</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
        </div>
        <div class="col-md-3">
            <label for="fecha_fin" class="form-label fw-bold">Hasta</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
        </div>

        <div class="col-md-6 d-flex gap-3">
            <button type="submit" class="btn btn-filtro d-flex align-items-center justify-content-center flex-grow-1">
                <img src="{{ asset('images/filter.svg') }}" alt="Filtrar" class="me-2" style="width: 18px; height: 18px;">
                Aplicar filtros
            </button>

            <a href="{{ url('/reportes/prestamos/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}"
                class="btn btn-pdf d-flex align-items-center justify-content-center flex-grow-1">
                <img src="{{ asset('images/pdf2.svg') }}" alt="PDF" class="me-2" style="width: 18px; height: 18px;">
                Exportar a PDF
            </a>
        </div>

    </form>

    @if($prestamos->count())
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr class="text-orange">
                    <th>Fecha del préstamo</th>
                    <th>Trabajador</th>
                    <th>Estado</th>
                    <th>Duración (Días)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prestamos as $p)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('d/m/Y H:i') }}</td>
                    <td>{{ $p->trabajador }}</td>
                    <td>{{ $p->estado }}</td>
                    <td>
                        @if(empty($p->duracion) || $p->duracion === '-' || $p->duracion === '—')
                            <span class="badge bg-warning text-dark">Sin fecha de devolución</span>
                        @else
                            {{ round($p->duracion) }}
                        @endif
                    </td>   
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

<!-- Modal de gráficos -->
<div class="modal fade" id="modalGraficos" tabindex="-1" aria-labelledby="modalGraficosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-orange text-white">
        <h5 class="modal-title" id="modalGraficosLabel">Visualización de préstamos</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <div class="col-md-6">
            <h6 class="text-center text-orange mb-2">Préstamos por día</h6>
            <div style="width: 100%; height: 240px;">
              <canvas id="graficoBarrasModal"></canvas>
            </div>
          </div>
          <div class="col-md-6">
            <h6 class="text-center text-orange mb-2">Distribución por estado</h6>
            <div style="width: 100%; height: 240px;">
              <canvas id="graficoTortaModal"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


    @else
    <div class="alert alert-info">No se encontraron préstamos en el rango seleccionado.</div>
    @endif
</div>

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


    new Chart(document.getElementById('graficoBarrasModal'), {
  type: 'bar',
  data: {
    labels: Object.keys(fechaCount),
    datasets: [{
      label: 'Préstamos por día',
      data: Object.values(fechaCount),
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
        text: 'Préstamos por día',
        color: '#ff6600',
        font: { size: 16 }
      }
    },
    scales: {
      x: {
        ticks: { color: '#ff6600', font: { size: 12 } },
        grid: { display: false }
      },
      y: {
        beginAtZero: true,
        ticks: { color: '#333', font: { size: 12 } },
        grid: { color: '#eee' }
      }
    }
  }
});

new Chart(document.getElementById('graficoTortaModal'), {
  type: 'pie',
  data: {
    labels: Object.keys(estadoCount),
    datasets: [{
      label: 'Distribución por estado',
      data: Object.values(estadoCount),
      backgroundColor: ['#ff6600', '#ffc107', '#0d6efd', '#6c757d', '#198754'],
      borderColor: '#fff',
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Distribución por estado',
        color: '#ff6600',
        font: { size: 16 }
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
      }
    }
  }
});

</script>
@endsection

@push('styles')
<link href="{{ asset('css/reportePrestamos.css') }}" rel="stylesheet">
@endpush
