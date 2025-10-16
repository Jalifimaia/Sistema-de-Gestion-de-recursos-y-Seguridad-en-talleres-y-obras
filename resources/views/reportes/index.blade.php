@extends('layouts.app')

@section('content')
<div class="container py-5">
  <h1 class="mb-4 text-center">📊 Panel de Reportes</h1>

  <div class="row g-4">

    <!-- Tarjeta: Préstamos registrados -->
    <div class="col-md-6 col-lg-4">
      <div class="card card-report shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-clock-history card-icon"></i> Préstamos registrados</h5>
          <p class="card-text">Visualizá los movimientos registrados en el sistema.</p>
          <a href="{{ route('reportes.prestamos') }}" class="btn btn-outline-primary btn-sm">Ver reporte</a>
        </div>
      </div>
    </div>

    <!-- Tarjeta: Herramientas más prestadas -->
    <div class="col-md-6 col-lg-4">
      <div class="card card-report shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-box-arrow-down card-icon"></i> Herramientas más prestadas</h5>
          <p class="card-text">Ranking por frecuencia de uso, ideal para detectar sobreuso o alta demanda.</p>
          <button class="btn btn-outline-primary btn-sm" onclick="abrirModal('Herramientas más prestadas')">Ver reporte</button>
        </div>
      </div>
    </div>

    <!-- Tarjeta: Herramientas dañadas -->
    <div class="col-md-6 col-lg-4">
      <div class="card card-report shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-tools card-icon"></i> Herramientas dañadas o fuera de servicio</h5>
          <p class="card-text">Recursos con incidentes, roturas o en reparación.</p>
          <button class="btn btn-outline-danger btn-sm" onclick="abrirModal('Herramientas dañadas o fuera de servicio')">Ver reporte</button>
        </div>
      </div>
    </div>

    <!-- Tarjeta: Herramientas por trabajador -->
    <div class="col-md-6 col-lg-4">
      <div class="card card-report shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-person-lines-fill card-icon"></i> Herramientas por trabajador</h5>
          <p class="card-text">Trazabilidad completa de asignaciones por persona.</p>
          <button class="btn btn-outline-secondary btn-sm" onclick="abrirModal('Herramientas por trabajador')">Ver reporte</button>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal de rango de fechas y exportación -->
<div class="modal fade" id="modalReporte" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalReporteLabel">📅 Seleccionar rango de fechas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <h4 id="tituloReporte" class="mb-4 text-center"></h4>
        <form>
          <div class="row mb-4">
            <div class="col-md-6">
              <label for="fechaDesde" class="form-label">Desde</label>
              <input type="date" id="fechaDesde" class="form-control">
            </div>
            <div class="col-md-6">
              <label for="fechaHasta" class="form-label">Hasta</label>
              <input type="date" id="fechaHasta" class="form-control">
            </div>
          </div>

          <div class="d-grid mb-4">
            <button type="button" class="btn btn-primary btn-lg" onclick="generarReporte()">📄 Generar reporte</button>
          </div>

          <div class="d-grid gap-3 col-6 mx-auto">
            <button id="btnCSV" class="btn btn-outline-success" disabled>📁 Exportar a CSV</button>
            <button id="btnExcel" class="btn btn-outline-primary" disabled>📊 Exportar a Excel</button>
            <button id="btnPDF" class="btn btn-outline-danger" disabled>🧾 Exportar a PDF</button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS de interacción -->
<script>
  function abrirModal(titulo) {
    document.getElementById("tituloReporte").innerText = titulo;
    document.getElementById("fechaDesde").value = "";
    document.getElementById("fechaHasta").value = "";
    document.getElementById("btnCSV").disabled = true;
    document.getElementById("btnExcel").disabled = true;
    document.getElementById("btnPDF").disabled = true;
    const modal = new bootstrap.Modal(document.getElementById("modalReporte"));
    modal.show();
  }

  function generarReporte() {
    const desde = document.getElementById("fechaDesde").value;
    const hasta = document.getElementById("fechaHasta").value;
    if (!desde || !hasta) {
      alert("Seleccioná ambas fechas.");
      return;
    }
    alert(`✅ Generando reporte desde ${desde} hasta ${hasta}`);
    document.getElementById("btnCSV").disabled = false;
    document.getElementById("btnExcel").disabled = false;
    document.getElementById("btnPDF").disabled = false;
  }

  function exportarReporte(formato) {
    alert(`📤 Exportando reporte en formato: ${formato.toUpperCase()}`);
  }
</script>

<style>
  .card-report {
    min-height: 200px;
    transition: transform 0.2s ease;
  }
  .card-report:hover {
    transform: scale(1.02);
  }
  .card-icon {
    font-size: 2rem;
    margin-right: 0.5rem;
  }
</style>
@endsection
