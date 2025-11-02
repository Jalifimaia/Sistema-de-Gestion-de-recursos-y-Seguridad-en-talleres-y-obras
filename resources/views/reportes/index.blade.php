@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<div class="container py-4">
  
  <div class="row mb-2">
    <div class="col-12 text-end text-muted small pt-1">
      Fecha: <strong id="today" class="text-nowrap"></strong>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <h1 class="text-center text-orange">📊 Panel de Reportes</h1>
    </div>
  </div>

  <div class="row g-4">
    <!-- Tarjeta: Préstamos registrados -->
    <div class="col-md-6 col-lg-4">
      <div class="card shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f57c00;">
        <div class="card-body d-flex flex-column">
          <div class="mb-3">
            <h5 class="card-title"><i class="bi bi-clock-history card-icon"></i> Préstamos registrados</h5>
            <p class="card-text">Visualizá los movimientos registrados en el sistema.</p>
          </div>
          <a href="{{ route('reportes.prestamos') }}" class="btn btn-outline-primary btn-sm mt-auto w-100">Ver reporte</a>
        </div>
      </div>
    </div>

    <!-- Tarjeta: Recursos más prestados -->
    <div class="col-md-6 col-lg-4">
      <div class="card shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f57c00;">
        <div class="card-body d-flex flex-column">
          <div class="mb-3">
            <h5 class="card-title"><i class="bi bi-bar-chart-line card-icon"></i> Recursos más prestados</h5>
            <p class="card-text">Ranking de recursos por cantidad de préstamos registrados en el sistema.</p>
          </div>
          <a href="{{ route('reportes.masPrestados') }}" class="btn btn-outline-primary btn-sm mt-auto w-100">Ver reporte</a>
        </div>
      </div>
    </div>

    <!-- Tarjeta: Recursos en reparación -->
    <div class="col-md-6 col-lg-4">
      <div class="card shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f57c00;">
        <div class="card-body d-flex flex-column">
          <div class="mb-3">
            <h5 class="card-title"><i class="bi bi-wrench-adjustable-circle card-icon"></i> Recursos en reparación</h5>
            <p class="card-text">Listado de recursos que están actualmente en estado de reparación.</p>
          </div>
          <a href="{{ route('reportes.enReparacion') }}" class="btn btn-outline-danger btn-sm mt-auto w-100">Ver reporte</a>
        </div>
      </div>
    </div>

    <!-- Tarjeta: Herramientas por trabajador -->
    <div class="col-md-6 col-lg-4">
      <div class="card shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f57c00;">
        <div class="card-body d-flex flex-column">
          <div class="mb-3">
            <h5 class="card-title"><i class="bi bi-person-badge card-icon"></i> Herramientas por trabajador</h5>
            <p class="card-text">Asignaciones de herramientas por usuario para trazabilidad y control.</p>
          </div>
          <a href="{{ route('reportes.herramientasPorTrabajador') }}" class="btn btn-outline-secondary btn-sm mt-auto w-100">Ver reporte</a>
        </div>
      </div>
    </div>

    <!-- Tarjeta: Incidentes por tipo de recurso -->
    <div class="col-md-6 col-lg-4">
      <div class="card shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f57c00;">
        <div class="card-body d-flex flex-column">
          <div class="mb-3">
            <h5 class="card-title"><i class="bi bi-exclamation-triangle card-icon"></i> Incidentes por tipo de recurso</h5>
            <p class="card-text">Análisis de incidentes agrupados por categoría de recurso.</p>
          </div>
          <a href="{{ route('reportes.incidentesPorTipo') }}" class="btn btn-outline-warning btn-sm mt-auto w-100">Ver reporte</a>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('styles')
<style>
  .card-icon {
    font-size: 1.8rem;
    margin-right: 0.5rem;
    color: #f57c00;
  }

  .text-orange {
    color: #f57c00;
  }
</style>
@endsection

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const today = new Date();
    const dia = String(today.getDate()).padStart(2, '0');
    const mes = String(today.getMonth() + 1).padStart(2, '0');
    const año = today.getFullYear();
    document.getElementById('today').textContent = `${dia}/${mes}/${año}`;
  });
</script>
