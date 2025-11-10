
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
            <img src="{{ asset('images/siren.svg') }}" alt="Incidentes" class="me-2 icono-volver">
            Incidentes por tipo de recurso
            </h4>
        </div>

        <div class="ms-auto">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalGrafico">
                Ver gráfico
                <img src="{{ asset('images/grafico.svg') }}" alt="Gráfico" class="ms-2 icono-volver">
            </button>
        </div>

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
        <div class="col-md-6 d-flex gap-3">
            <button type="submit" class="btn btn-filtro d-flex align-items-center justify-content-center flex-grow-1">
            <img src="{{ asset('images/filter.svg') }}" alt="Filtrar" class="me-2 icono-volver">
            Aplicar filtros
            </button>

            <a href="{{ url('/reportes/incidentes-por-tipo/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}"
            class="btn btn-pdf d-flex align-items-center justify-content-center flex-grow-1">
            <img src="{{ asset('images/pdf2.svg') }}" alt="PDF" class="me-2 icono-volver">
            Exportar a PDF
            </a>
        </div>
    </form>


    @if($incidentes->count())
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle">
            <thead>
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

<!-- Modal -->
<div class="modal fade" id="modalGrafico" tabindex="-1" aria-labelledby="modalGraficoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-orange" id="modalGraficoLabel">📊 Incidentes por tipo de recurso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div style="width: 100%; height: 300px;">
          <canvas id="graficoIncidentesModal"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const labels = {!! json_encode($labels) !!};
  const valores = {!! json_encode($valores) !!};

  new Chart(document.getElementById('graficoIncidentesModal'), {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{
        data: valores,
        backgroundColor: [
          'rgba(255, 140, 0, 0.8)',
          'rgba(255, 206, 86, 0.7)',
          'rgba(54, 162, 235, 0.7)',
          'rgba(255, 99, 132, 0.7)',
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
          text: 'Incidentes por tipo de recurso',
          color: '#ff6600',
          font: { size: 16 }
        }
      }
    }
  });
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/incidentesPorTipoRecurso.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@endpush
