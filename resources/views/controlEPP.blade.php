@extends('layouts.app')

@section('title', 'Control de EPP y Seguridad')

@push('styles')
  <link href="{{ asset('css/controlepp.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container py-4">

  <!-- Encabezado principal -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div class="d-flex align-items-center gap-2">
    <div>
      <h1 class="h4 fw-bold mb-1">
        <img src="{{ asset('images/escudo.svg') }}" alt="Icono EPP" class="icono-epp">
        Control de Equipo de protección personal y Seguridad
      </h1>

      <p class="subtitulo-epp mb-0">Gestión de equipos de protección personal y checklist de seguridad</p>
    </div>
  </div>
  <div class="col-auto fecha-destacada d-flex align-items-center justify-content-end">
      <strong id="today" class="valor-fecha text-nowrap"></strong>
    </div>
</div>

<!-- 🔶 Botón para ver tabla completa del checklist -->
  <div class="text-start mb-4">
    <a href="{{ route('checklist.epp.tabla') }}" class="btn btn-checklist">
      <img src="{{ asset('images/attention.svg') }}" alt="Atención" class="icono-boton-animado me-2">
      Ver tabla de checklist diario
    </a>
  </div>

 <!-- 🔶 Cards funcionales estilo acción urgente -->
<div class="row g-4 mb-5">
  <!-- Checklist Diario -->
  <div class="col-md-6 col-lg-3">
    <div class="card card-action h-100 d-flex flex-column align-items-center text-center">
      <div class="card-body d-flex flex-column align-items-center text-center">
        <div class="d-flex justify-content-start align-items-center w-100 mb-2 gap-2">
          <img src="{{ asset('images/checklistSI.svg') }}" alt="Checklist" class="icono-action-inline">
          <h5 class="card-title fw-semibold mb-0">Checklist Diario</h5>
        </div>
        <p class="card-text small text-muted">Registrar cumplimiento de EPP por trabajador.</p>
        <a href="{{ route('checklist.epp') }}" class="btn btn-action btn-verde mt-auto">Ir a checklist</a>
      </div>
    </div>
  </div>

  <!-- Asignar EPP -->
  <div class="col-md-6 col-lg-3">
    <div class="card card-action h-100 d-flex flex-column align-items-center text-center">
      <div class="card-body d-flex flex-column align-items-center text-center">
        <div class="d-flex justify-content-start align-items-center w-100 mb-2 gap-2">
          <img src="{{ asset('images/workerepp.svg') }}" alt="Asignar EPP" class="icono-action-inline">
          <h5 class="card-title fw-semibold mb-0">Asignar EPP</h5>
        </div>
        <p class="card-text small text-muted">Asignar recursos a trabajadores.</p>
        <a href="{{ route('epp.asignar.create') }}" class="btn btn-action btn-azul mt-auto">Asignar EPP</a>
      </div>
    </div>
  </div>

  <!-- Recursos Faltantes -->
  <div class="col-md-6 col-lg-3">
    <div class="card card-action h-100 d-flex flex-column align-items-center text-center">
      <div class="card-body d-flex flex-column align-items-center text-center">
        <div class="d-flex justify-content-start align-items-center w-100 mb-2 gap-2">
          <img src="{{ asset('images/faltantes.svg') }}" alt="Faltantes" class="icono-action-inline">
          <h5 class="card-title fw-semibold mb-0">Recursos Faltantes</h5>
        </div>
        <p class="card-text small text-muted">Trabajadores sin todos los EPP obligatorios asignados.</p>
        <a href="{{ route('epp.faltantes') }}" class="btn btn-action btn-rojo mt-auto">Ver faltantes</a>
      </div>
    </div>
  </div>

  <!-- Checklist No Registrado -->
  <div class="col-md-6 col-lg-3">
    <div class="card card-action h-100 d-flex flex-column align-items-center text-center">
      <div class="card-body d-flex flex-column align-items-center text-center">
        <div class="d-flex justify-content-start align-items-center w-100 mb-2 gap-2">
          <img src="{{ asset('images/checknot.svg') }}" alt="Pendientes" class="icono-action-inline">
          <h5 class="card-title fw-semibold mb-0">Checklist No Registrado</h5>
        </div>
        <p class="card-text small text-muted">Trabajadores sin registro de checklist en el día.</p>
        <a href="{{ route('controlEPP.sinChecklist') }}" class="btn btn-action btn-naranja mt-auto">Ver pendientes</a>
      </div>
    </div>
  </div>
</div>



  <!-- 🔶 Tarjetas resumen -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card card-resumen shadow-sm text-center card-compact h-100">
        <div class="card-body">
          <img src="{{ asset('images/average.svg') }}" alt="Checklist" class="icono-resumen mb-2">
          <h6 class="card-title title-cards text-muted">Checklist Diario</h6>
          <small class="text-muted">Cumplimiento General</small>
          <h4 class="mt-2 text-primary">{{ $porcentajeChecklist }}%</h4>
          <small class="text-muted">Promedio del taller</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-resumen shadow-sm text-center card-compact h-100">
        <div class="card-body">
          <img src="{{ asset('images/expired.svg') }}" alt="Vencidos" class="icono-resumen mb-2">
          <h6 class="card-title title-cards text-muted">EPP Vencidos</h6>
          <h4 class="text-danger">{{ $eppVencidos }}</h4>
          <small class="text-muted">Requieren reemplazo</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-resumen shadow-sm text-center card-compact h-100">
        <div class="card-body">
          <img src="{{ asset('images/checklist.svg') }}" alt="Checklist Hoy" class="icono-resumen mb-2">
          <h6 class="card-title title-cards text-muted">Checklist Hoy</h6>
          <h4 class="text-warning">{{ $checklistHoyTotal }}</h4>
          <small class="text-muted">Trabajadores verificados</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-resumen shadow-sm text-center card-compact h-100">
        <div class="card-body">
          <img src="{{ asset('images/wait.svg') }}" alt="Próximos Vencimientos" class="icono-resumen mb-2">
          <h6 class="card-title title-cards text-muted">Próximos Vencimientos</h6>
          <h4 class="text-orange">{{ $proximosVencimientos }}</h4>
          <small class="text-muted">En los próximos 30 días</small>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection


@push('styles')
  <link href="{{ asset('css/controlepp.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const today = new Date();

    const dia = String(today.getDate()).padStart(2, '0');
    const mes = String(today.getMonth() + 1).padStart(2, '0');
    const año = today.getFullYear();

    const horas = String(today.getHours()).padStart(2, '0');
    const minutos = String(today.getMinutes()).padStart(2, '0');
    const segundos = String(today.getSeconds()).padStart(2, '0');

    const fechaFormateada = `${dia}/${mes}/${año} ${horas}:${minutos}:${segundos}`;
    document.getElementById("today").textContent = fechaFormateada;
  });
</script>
@endpush



