@extends('layouts.app')

@section('title', 'Control de EPP y Seguridad')

@section('content')
<div class="container py-4">
  <!-- Fecha arriba a la derecha -->
  <div class="row mb-2">
      <div class="col-12 text-end text-muted small pt-1">
          Fecha: <strong id="today" class="text-nowrap"></strong>
      </div>
  </div>

  <!-- Título -->  
  <div class="row mb-4">
    <div class="col-12">
      <h1 class="text-center text-orange">🛡️ Control de EPP y Seguridad</h1>
      <p class="text-center text-muted small">Gestión de equipos de protección personal y checklist de seguridad</p>
    </div>
  </div>


  <!-- 🔶 Tarjetas resumen -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">Checklist Diario</h6>
          <p class="fw-bold mb-0">Asignar EPP</p>
          <small class="text-muted">Cumplimiento General</small>
          <h4 class="mt-2 text-primary">{{ $porcentajeChecklist }}%</h4>
          <small class="text-muted">Promedio del taller</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">EPP Vencidos</h6>
          <h4 class="text-danger">{{ $eppVencidos }}</h4>
          <small class="text-muted">Requieren reemplazo</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">Checklist Hoy</h6>
          <h4 class="text-warning">{{ $checklistHoyTotal }}</h4>
          <small class="text-muted">Trabajadores verificados</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <h6 class="card-title text-muted">Próximos Vencimientos</h6>
          <h4 class="text-orange">{{ $proximosVencimientos }}</h4>
          <small class="text-muted">En los próximos 30 días</small>
        </div>
      </div>
    </div>
  </div>

  <!-- 🔶 Botón para ver tabla completa del checklist -->
<div class="text-start mb-4">
  <a href="{{ route('checklist.epp.tabla') }}" class="btn btn-outline-secondary">
    <i class="bi bi-table"></i> Ver tabla de checklist diario
  </a>
</div>


  <!-- 🔶 Cards funcionales estilo reportes -->
  <div class="row g-4 mb-5">
    <!-- Checklist Diario -->
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f57c00;">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><i class="bi bi-clipboard-check card-icon"></i> Checklist Diario</h5>
          <p class="card-text">Registrar cumplimiento de EPP por trabajador.</p>
          <a href="{{ route('checklist.epp') }}" class="btn btn-outline-primary btn-sm mt-auto w-100">Ir a checklist</a>
        </div>
      </div>
    </div>

    <!-- Asignar EPP -->
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f57c00;">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><i class="bi bi-person-plus card-icon"></i> Asignar EPP</h5>
          <p class="card-text">Asignar recursos a trabajadores.</p>
          <a href="{{ route('epp.asignar.create') }}" class="btn btn-outline-success btn-sm mt-auto w-100">Asignar EPP</a>
        </div>
      </div>
    </div>

    <!-- Recursos faltantes -->
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f57c00;">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><i class="bi bi-exclamation-circle card-icon"></i> Recursos Faltantes</h5>
          <p class="card-text">Trabajadores sin todos los EPP obligatorios asignados.</p>
          <a href="{{ route('epp.faltantes') }}" class="btn btn-outline-warning btn-sm mt-auto w-100">Ver faltantes</a>
        </div>
      </div>
    </div>

    <!-- Checklist no registrado -->
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f57c00;">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><i class="bi bi-calendar-x card-icon"></i> Checklist No Registrado</h5>
          <p class="card-text">Trabajadores sin registro de checklist en el día.</p>
          <a href="{{ route('controlEPP.sinChecklist') }}" class="btn btn-outline-danger btn-sm mt-auto w-100">Ver pendientes</a>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@section('styles')
<style>
  .card-icon {
    font-size: 1.6rem;
    margin-right: 0.5rem;
    color: #f57c00;
  }
  .text-orange {
    color: #f57c00;
  }
</style>
@endsection
