@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('reportes.index') }}" class="btn btn-volver d-flex align-items-center">
            <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
            Volver
            </a>

            <h4 class="fw-bold text-orange mb-0 d-flex align-items-center">
            <img src="{{ asset('images/prestamos.svg') }}" alt="Recursos" class="me-2" style="width: 28px; height: 28px;">
            Recursos más prestados
            </h4>
        </div>

        <button type="button" class="btn btn-outline-secondary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalGrafico">
            Ver gráfico
            <img src="{{ asset('images/grafico.svg') }}" alt="Gráfico" class="ms-2" style="width: 24px; height: 24px;">
        </button>
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
        <div class="col-md-6 d-flex gap-3">
            <button type="submit" class="btn btn-filtro d-flex align-items-center justify-content-center flex-grow-1">
                <img src="{{ asset('images/filter.svg') }}" alt="Filtrar" class="me-2" style="width: 18px; height: 18px;">
                Aplicar filtros
            </button>

            <a href="{{ url('/reportes/recursos-mas-prestados/pdf') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}"
                class="btn btn-pdf d-flex align-items-center justify-content-center flex-grow-1">
                <img src="{{ asset('images/pdf2.svg') }}" alt="PDF" class="me-2" style="width: 18px; height: 18px;">
                Exportar a PDF
            </a>
        </div>

    </form>

    @if($recursos->count())
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle">
            <thead>
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
                    <td>
                      {{ \Carbon\Carbon::parse($r->ultima_fecha)->format('d/m/Y H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div id="infoPaginacionRecursos" class="text-muted small"></div>
          <ul id="paginacionRecursos" class="pagination mb-0"></ul>
        </div>
    @else
    <div class="alert alert-info">No se encontraron préstamos en el rango seleccionado.</div>
    @endif
</div>

<!-- Modal -->
<div class="modal fade" id="modalGrafico" tabindex="-1" aria-labelledby="modalGraficoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-orange" id="modalGraficoLabel">📊 Recursos más prestados</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div style="width: 100%; height: 300px;">
          <canvas id="graficoPrestamosModal"></canvas>
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
  const info = document.getElementById('infoPaginacionRecursos');
  const paginacion = document.getElementById('paginacionRecursos');
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

    info.textContent = `Mostrando ${total === 0 ? 0 : inicio + 1}-${fin} de ${total} recursos`;

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

  const configGrafico = {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Cantidad de préstamos',
        data: valores,
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
  };

  new Chart(document.getElementById('graficoPrestamosModal'), configGrafico);
});
</script>

@endsection

@push('styles')
<link href="{{ asset('css/recursosMasPrestados.css') }}" rel="stylesheet">
@endpush
