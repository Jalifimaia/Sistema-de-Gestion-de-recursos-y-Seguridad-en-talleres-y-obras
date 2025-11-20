@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<div class="container py-4">
  
  <!-- 🔶 Encabezado -->
<header class="row mb-4 align-items-center">
  <div class="col-md-8">
    <h1 class="h4 fw-bold mb-1 d-flex align-items-center gap-2">
      <img src="{{ asset('images/estadistica.svg') }}" alt="Icono Estadísticas" style="height: 35px;">
      Panel de Reportes
    </h1>
    <p class="text-muted">Visualización de movimientos, asignaciones e incidentes registrados en el sistema</p>
  </div>

 <!-- <div class="col-md-4 text-md-end fecha-destacada d-flex align-items-center justify-content-md-end">
    <strong id="today" class="valor-fecha text-nowrap"></strong>
  </div>-->
</header>



  <div class="row g-4">
  <!-- Tarjeta: Préstamos registrados -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-report shadow-sm h-100 d-flex flex-column justify-content-between">
      <div class="card-header text-center bg-white border-0 pb-0">
        <img src="{{ asset('images/prestamos.svg') }}" alt="Préstamos" class="banner-card-img">
      </div>
      <div class="card-body d-flex flex-column">
        <h5 class="card-title fw-bold text-center mt-2">Préstamos registrados</h5>
        <p class="card-text text-center">Visualizá los movimientos registrados en el sistema.</p>
        <a href="{{ route('reportes.prestamos') }}" class="btn btn-naranja btn-sm mt-auto w-100">Ver reporte</a>
      </div>
    </div>
  </div>

  <!-- Tarjeta: Recursos más prestados -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-report shadow-sm h-100 d-flex flex-column justify-content-between">
      <div class="card-header text-center bg-white border-0 pb-0">
        <img src="{{ asset('images/prestados.svg') }}" alt="Más prestados" class="banner-card-img">
      </div>
      <div class="card-body d-flex flex-column">
        <h5 class="card-title fw-bold text-center mt-2">Recursos más prestados</h5>
        <p class="card-text text-center">Ranking de recursos por cantidad de préstamos registrados en el sistema.</p>
        <a href="{{ route('reportes.masPrestados') }}" class="btn btn-naranja btn-sm mt-auto w-100">Ver reporte</a>
      </div>
    </div>
  </div>

  <!-- Tarjeta: Recursos en reparación -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-report shadow-sm h-100 d-flex flex-column justify-content-between">
      <div class="card-header text-center bg-white border-0 pb-0">
        <img src="{{ asset('images/reparacion.svg') }}" alt="Reparación" class="banner-card-img">
      </div>
      <div class="card-body d-flex flex-column">
        <h5 class="card-title fw-bold text-center mt-2">Recursos en reparación</h5>
        <p class="card-text text-center">Listado de recursos que están actualmente en estado de reparación.</p>
        <a href="{{ route('reportes.enReparacion') }}" class="btn btn-naranja btn-sm mt-auto w-100">Ver reporte</a>
      </div>
    </div>
  </div>

  <!-- Tarjeta: Herramientas por trabajador -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-report shadow-sm h-100 d-flex flex-column justify-content-between">
      <div class="card-header text-center bg-white border-0 pb-0">
        <img src="{{ asset('images/herram.svg') }}" alt="Herramientas" class="banner-card-img">
      </div>
      <div class="card-body d-flex flex-column">
        <h5 class="card-title fw-bold text-center mt-2">Herramientas por trabajador</h5>
        <p class="card-text text-center">Asignaciones de herramientas por usuario para trazabilidad y control.</p>
        <a href="{{ route('reportes.herramientasPorTrabajador') }}" class="btn btn-naranja btn-sm mt-auto w-100">Ver reporte</a>
      </div>
    </div>
  </div>

  <!-- Tarjeta: Incidentes por tipo de recurso -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-report shadow-sm h-100 d-flex flex-column justify-content-between">
      <div class="card-header text-center bg-white border-0 pb-0">
        <img src="{{ asset('images/incidentes.svg') }}" alt="Incidentes" class="banner-card-img">
      </div>
      <div class="card-body d-flex flex-column">
        <h5 class="card-title fw-bold text-center mt-2">Incidentes por tipo de recurso</h5>
        <p class="card-text text-center">Análisis de incidentes agrupados por categoría de recurso.</p>
        <a href="{{ route('reportes.incidentesPorTipo') }}" class="btn btn-naranja btn-sm mt-auto w-100">Ver reporte</a>
      </div>
    </div>
  </div>
</div>


</div>
@endsection

@push('styles')
<link href="{{ asset('css/reportes.css') }}" rel="stylesheet">
@endpush

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const today = new Date();
    const dia = String(today.getDate()).padStart(2, '0');
    const mes = String(today.getMonth() + 1).padStart(2, '0');
    const año = today.getFullYear();
    const hora = String(today.getHours()).padStart(2, '0');
    const minutos = String(today.getMinutes()).padStart(2, '0');

    document.getElementById('today').textContent = `${dia}/${mes}/${año} ${hora}:${minutos}`;
  });
</script>

