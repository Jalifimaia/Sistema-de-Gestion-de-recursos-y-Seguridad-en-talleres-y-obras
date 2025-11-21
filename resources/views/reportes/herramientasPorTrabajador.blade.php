@extends('layouts.app')

@section('title', 'Herramientas por trabajador')

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

            <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-ver-grafico d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalGrafico">
                <img src="{{ asset('images/grafico.svg') }}" alt="Gráfico" class="me-2" style="width: 18px; height: 18px;">
                Ver gráfico
            </button>

            <a href="{{ url('/reportes/herramientas-por-trabajador/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}"
               class="btn btn-pdf d-flex align-items-center">
                <img src="{{ asset('images/pdf2.svg') }}" alt="PDF" class="me-2" style="width: 18px; height: 18px;">
                Exportar a PDF
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('reportes.herramientasPorTrabajador') }}" class="row g-3 align-items-end mb-4">
        <!-- Fechas -->
        <div class="col-md-2">
            <label for="fecha_inicio" class="form-label fw-bold">Desde</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
        </div>

        <div class="col-md-2">
            <label for="fecha_fin" class="form-label fw-bold">Hasta</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
        </div>

        <!-- Botón aplicar -->
        <div class="col-md-2">
            <button type="submit" class="btn btn-filtro btn-sm w-100 d-flex align-items-center justify-content-center text-nowrap">
                <img src="{{ asset('images/filter.svg') }}" alt="Filtrar" class="me-2" style="width: 16px; height: 16px;">
                Aplicar filtros
            </button>
        </div>

        <!-- Botón limpiar cuadrado -->
        <div class="col-auto">
            <a href="{{ route('reportes.herramientasPorTrabajador') }}" 
               class="btn btn-secondary btn-sm d-flex align-items-center justify-content-center"
               style="width: 40px; height: 40px; padding: 0;">
                <img src="{{ asset('images/clear.svg') }}" alt="Limpiar" style="width: 20px; height: 20px;">
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
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div id="infoPaginacionHerramientas" class="text-muted small"></div>
          <ul id="paginacionHerramientas" class="pagination mb-0"></ul>
        </div>
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
document.addEventListener('DOMContentLoaded', function () {
  // 🔹 Paginación de la tabla
  const filas = Array.from(document.querySelectorAll('table tbody tr'));
  const info = document.getElementById('infoPaginacionHerramientas');
  const paginacion = document.getElementById('paginacionHerramientas');
  const filasPorPagina = 10;
  let paginaActual = 1;

  function mostrarPagina(pagina) {
    paginaActual = pagina;
    const total = filas.length;
    const totalPaginas = Math.ceil(total / filasPorPagina);

    filas.forEach(f => f.style.display = 'none');

    const inicio = (pagina - 1) * filasPorPagina;
    const fin = Math.min(inicio + filasPorPagina, total);
    for (let i = inicio; i < fin; i++) {
      filas[i].style.display = 'table-row';
    }

    info.textContent = `Mostrando ${total === 0 ? 0 : inicio + 1}-${fin} de ${total} herramientas`;

    paginacion.innerHTML = '';
    for (let i = 1; i <= totalPaginas; i++) {
      const li = document.createElement('li');
      li.className = 'page-item' + (i === paginaActual ? ' active' : '');
      const a = document.createElement('a');
      a.className = 'page-link';
      a.href = '#';
      a.textContent = i;
      a.addEventListener('click', e => {
        e.preventDefault();
        mostrarPagina(i);
      });
      li.appendChild(a);
      paginacion.appendChild(li);
    }
  }

  mostrarPagina(1);

  // 🔹 Gráfico con Chart.js
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
});
</script>

@endsection
@push('styles')
<link href="{{ asset('css/herramientasPorTrabajador.css') }}" rel="stylesheet">
@endpush
