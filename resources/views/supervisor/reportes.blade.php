<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reportes y Auditorías</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

  <div class="container py-4">
    @extends('layouts.app')
    <h1 class="mb-3">Reportes y Auditorías</h1>
    <p class="text-muted">Análisis de datos y cumplimiento normativo</p>

    <div class="mb-3 d-flex gap-2">
      <button class="btn btn-primary">Generar Reporte</button>
      <button class="btn btn-secondary">Exportar Datos</button>
    </div>

    <div class="mb-4">
      <label class="form-label">Seleccionar rango de fechas</label>
      <input type="date" class="form-control mb-2">
      <input type="date" class="form-control mb-2">
      <select class="form-select mb-2">
        <option>Todos</option>
      </select>
      <select class="form-select">
        <option>Todos</option>
      </select>
    </div>



    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6>Cumplimiento SRT</h6>
            <h4>91.6%</h4>
            <small class="text-success">+2.3% vs mes anterior</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6>Auditorías Realizadas</h6>
            <h4>12</h4>
            <small class="text-muted">Este mes</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6>Incidentes Reportados</h6>
            <h4>3</h4>
            <small class="text-danger">-2 vs mes anterior</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h6>Reportes Generados</h6>
            <h4>8</h4>
            <small class="text-muted">Este mes</small>
          </div>
        </div>
      </div>
    </div>

  <div class="row g-4">
    <div class="col-12">
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">Recomendaciones (IA)</h5>
          <p class="text-muted">Consejos automáticos generados por reglas: vencimientos, inventario y predicciones.</p>
          <div id="recomendacionesContainer" class="d-flex flex-column gap-2">
            @if(isset($recomendaciones) && count($recomendaciones) > 0)
              @foreach($recomendaciones as $r)
                <div class="card p-2 {{ $r['nivel'] == 'danger' ? 'border-danger' : ($r['nivel'] == 'warning' ? 'border-warning' : 'border-info') }}">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <strong>{{ $r['titulo'] }}</strong>
                      <div class="small text-muted">{{ $r['mensaje'] }}</div>
                    </div>
                  </div>

                  {{-- Formatos específicos según tipo de recomendación --}}
                  @if(isset($r['detalles']) && is_array($r['detalles']) && isset($r['titulo']) && str_contains(strtolower($r['titulo']), 'vencim'))
                    <div class="table-responsive mt-2">
                      <table class="table table-sm">
                        <thead><tr><th>Recurso</th><th>Serie</th><th>Vencimiento</th></tr></thead>
                        <tbody>
                        @foreach($r['detalles'] as $d)
                          <tr>
                            <td>{{ $d['recurso'] ?? '-' }}</td>
                            <td>{{ $d['nro_serie'] ?? '-' }}</td>
                            <td>{{ isset($d['fecha_vencimiento']) ? \Carbon\Carbon::parse($d['fecha_vencimiento'])->format('d/m/Y') : '-' }}</td>
                          </tr>
                        @endforeach
                        </tbody>
                      </table>
                    </div>
                  @elseif(isset($r['detalles']) && is_array($r['detalles']) && isset($r['titulo']) && str_contains(strtolower($r['titulo']), 'inventario'))
                    <ul class="list-group list-group-flush mt-2">
                      @foreach($r['detalles'] as $d)
                        <li class="list-group-item p-1">{{ $d['nombre'] ?? 'Recurso' }} — <strong>{{ $d['cantidad_series'] ?? 0 }}</strong> series</li>
                      @endforeach
                    </ul>
                  @elseif(isset($r['detalles']))
                    <ul class="mt-2 small">
                      @foreach($r['detalles'] as $k => $v)
                        <li>{{ is_scalar($v) ? $k . ': ' . $v : json_encode($v) }}</li>
                      @endforeach
                    </ul>
                  @endif
                </div>
              @endforeach
            @else
              <div class="text-muted">Cargando recomendaciones...</div>
            @endif
          </div>
        </div>
      </div>
    </div>
  <div class="col-md-6">
    <div class="card p-3">
      <h5 class="mb-3">Evolución del Inventario</h5>
      <canvas id="inventarioChart"></canvas>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h5 class="mb-3">Cumplimiento por Sector</h5>
      <canvas id="cumplimientoChart"></canvas>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h5 class="mb-3">Distribución de EPP</h5>
      <canvas id="distribucionChart"></canvas>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h5 class="mb-3">Incidentes y Accidentes</h5>
      <canvas id="incidentesChart"></canvas>
    </div>
  </div>
</div>

  </div>

  <script>
    // --- Recomendaciones IA: fetch a /recomendaciones ---
    async function cargarRecomendaciones() {
      const container = document.getElementById('recomendacionesContainer');
      container.innerHTML = '<div class="text-muted">Cargando recomendaciones...</div>';
      try {
  const res = await fetch("{{ url('/api/recomendaciones') }}");
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        if (!data.ok) {
          container.innerHTML = '<div class="text-danger">' + (data.message || 'Respuesta inválida del servidor') + '</div>';
          return;
        }

        const read = JSON.parse(localStorage.getItem('recomendaciones_leidas') || '[]');

        if (!data.recomendaciones || data.recomendaciones.length === 0) {
          container.innerHTML = '<div class="text-success">No hay recomendaciones por ahora.</div>';
          return;
        }

        container.innerHTML = '';
        data.recomendaciones.forEach((r, idx) => {
          const id = r.titulo + '_' + idx;
          const leida = read.includes(id);
          const nivelClass = r.nivel === 'danger' ? 'border-danger' : (r.nivel === 'warning' ? 'border-warning' : 'border-info');

          const card = document.createElement('div');
          card.className = 'card p-2 ' + nivelClass;
          const header = document.createElement('div');
          header.className = 'd-flex justify-content-between align-items-start';
          header.innerHTML = `
            <div>
              <strong>${r.titulo}</strong>
              <div class="small text-muted">${r.mensaje}</div>
            </div>
            <div class="text-end">
              <button class="btn btn-sm btn-outline-secondary me-2" data-id="${id}">${leida ? 'Marcada' : 'Marcar leída'}</button>
            </div>
          `;
          card.appendChild(header);

          // detalles: formato según tipo
          if (r.detalles && Array.isArray(r.detalles) && r.titulo.toLowerCase().includes('vencim')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive mt-2';
            const table = document.createElement('table');
            table.className = 'table table-sm';
            table.innerHTML = '<thead><tr><th>Recurso</th><th>Serie</th><th>Vencimiento</th></tr></thead>';
            const tbody = document.createElement('tbody');
            r.detalles.forEach(d => {
              const tr = document.createElement('tr');
              const fecha = d.fecha_vencimiento ? new Date(d.fecha_vencimiento).toLocaleDateString() : '-';
              tr.innerHTML = `<td>${d.recurso || '-'}</td><td>${d.nro_serie || '-'}</td><td>${fecha}</td>`;
              tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            wrapper.appendChild(table);
            card.appendChild(wrapper);
          } else if (r.detalles && Array.isArray(r.detalles) && r.titulo.toLowerCase().includes('inventario')) {
            const ul = document.createElement('ul');
            ul.className = 'list-group list-group-flush mt-2';
            r.detalles.forEach(d => {
              const li = document.createElement('li');
              li.className = 'list-group-item p-1';
              li.textContent = `${d.nombre || 'Recurso'} — ${d.cantidad_series || 0} series`;
              ul.appendChild(li);
            });
            card.appendChild(ul);
          } else if (r.detalles) {
            const ul = document.createElement('ul');
            ul.className = 'mt-2 small';
            Object.keys(r.detalles).forEach(k => {
              const li = document.createElement('li');
              const v = r.detalles[k];
              li.textContent = typeof v === 'object' ? `${k}: ${JSON.stringify(v)}` : `${k}: ${v}`;
              ul.appendChild(li);
            });
            card.appendChild(ul);
          }

          container.appendChild(card);

          const btn = card.querySelector('button');
          btn.addEventListener('click', () => {
            const arr = JSON.parse(localStorage.getItem('recomendaciones_leidas') || '[]');
            if (!arr.includes(id)) arr.push(id);
            localStorage.setItem('recomendaciones_leidas', JSON.stringify(arr));
            btn.textContent = 'Marcada';
            btn.disabled = true;
          });
        });
      } catch (e) {
        container.innerHTML = '<div class="text-danger">Error cargando recomendaciones: ' + e.message + '</div>';
      }
    }

    // cargar al inicio y cada 5 minutos
    cargarRecomendaciones();
    setInterval(cargarRecomendaciones, 1000 * 60 * 5);

    // Evolución del Inventario
    const inventarioCtx = document.getElementById('inventarioChart');
    new Chart(inventarioCtx, {
      type: 'bar',
      data: {
        labels: ['Ene','Feb','Mar','Abr','May','Jun'],
        datasets: [{
          label: 'Herramientas y EPP',
          data: [0,45,90,135,180,180],
          backgroundColor: 'rgba(54,162,235,0.5)',
          borderColor: 'rgba(54,162,235,1)',
          borderWidth: 1
        }]
      },
      options: { responsive: true, scales: { y: { beginAtZero:true } } }
    });

    // Cumplimiento por Sector
    const cumplimientoCtx = document.getElementById('cumplimientoChart');
    new Chart(cumplimientoCtx, {
      type: 'bar',
      data: {
        labels: ['Producción A','Mantenimiento','Administración'],
        datasets: [{
          label: 'Cumplimiento EPP %',
          data: [85,90,95],
          backgroundColor: ['rgba(75,192,192,0.5)','rgba(153,102,255,0.5)','rgba(255,159,64,0.5)'],
          borderColor: ['rgba(75,192,192,1)','rgba(153,102,255,1)','rgba(255,159,64,1)'],
          borderWidth: 1
        }]
      },
      options: { responsive: true, scales: { y: { beginAtZero:true, max:100 } } }
    });

    // Distribución de EPP
    const distribucionCtx = document.getElementById('distribucionChart');
    new Chart(distribucionCtx, {
      type: 'doughnut',
      data: {
        labels: ['Cascos','Guantes','Anteojos','Arneses','Chalecos'],
        datasets: [{
          label: 'EPP %',
          data: [22,26,20,14,18],
          backgroundColor: ['#36A2EB','#FF6384','#FFCE56','#4BC0C0','#9966FF']
        }]
      },
      options: { responsive: true }
    });

    // Incidentes y Accidentes
    const incidentesCtx = document.getElementById('incidentesChart');
    new Chart(incidentesCtx, {
      type: 'line',
      data: {
        labels: ['Ene','Feb','Mar','Abr','May','Jun'],
        datasets: [{
          label: 'Eventos de Seguridad',
          data: [2,3,1,4,3,2],
          fill: false,
          borderColor: 'rgba(255,99,132,1)',
          tension: 0.1
        }]
      },
      options: { responsive: true, scales: { y: { beginAtZero:true } } }
    });
  </script>
</body>
</html>
