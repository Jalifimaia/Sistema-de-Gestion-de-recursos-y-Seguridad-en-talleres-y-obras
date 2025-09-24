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
    @include('partials.barra_navegacion')

  <div class="container py-4">
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
