@extends('layouts.app')

@section('content')
<div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('reportes.index') }}" class="btn btn-volver d-inline-flex align-items-center">
                <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
                Volver
                </a>

                <h4 class="fw-bold text-orange mb-0 d-flex align-items-center">
                <img src="{{ asset('images/herramienta3.svg') }}" alt="Herramientas" class="me-2" style="width: 28px; height: 28px;">
                Herramientas por trabajador
                </h4>
            </div>

            <button type="button" class="btn btn-outline-secondary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalGrafico">
                Ver gráfico
                <img src="{{ asset('images/grafico.svg') }}" alt="Gráfico" class="ms-2" style="width: 18px; height: 18px;">
            </button>
        </div>


    <form method="GET" action="{{ route('reportes.herramientasPorTrabajador') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
            <label for="fecha_inicio" class="form-label">Desde</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
        </div>
        <div class="col-md-3">
            <label for="fecha_fin" class="form-label">Hasta</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
        </div>
        <div class="col-md-6 d-flex gap-3">
            <button type="submit" class="btn btn-filtro d-flex align-items-center justify-content-center flex-grow-1">
                <img src="{{ asset('images/filter.svg') }}" alt="Filtrar" class="me-2" style="width: 18px; height: 18px;">
                Aplicar filtros
            </button>

            <a href="{{ url('/reportes/herramientas-por-trabajador/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}"
                class="btn btn-pdf d-flex align-items-center justify-content-center flex-grow-1">
                <img src="{{ asset('images/pdf2.svg') }}" alt="PDF" class="me-2" style="width: 18px; height: 18px;">
                Exportar a PDF
            </a>
        </div>

    </form>

    @if($herramientas->count())
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle">
            <thead>
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
                    <td>{{ \Carbon\Carbon::parse($item->fecha_adquisicion)->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @else
    <div class="alert alert-info">No se encontraron asignaciones en el rango seleccionado.</div>
    @endif
</div>

<!-- Modal -->
<div class="modal fade" id="modalGrafico" tabindex="-1" aria-labelledby="modalGraficoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-orange" id="modalGraficoLabel">📊 Herramientas asignadas por trabajador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div style="width: 100%; height: 300px;">
          <canvas id="graficoHerramientasModal"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const labels = {!! json_encode($labels) !!};
  const valores = {!! json_encode($valores) !!};

  new Chart(document.getElementById('graficoHerramientasModal'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Cantidad de herramientas',
        data: valores,
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
@push('styles')
<link href="{{ asset('css/herramientasPorTrabajador.css') }}" rel="stylesheet">
@endpush
