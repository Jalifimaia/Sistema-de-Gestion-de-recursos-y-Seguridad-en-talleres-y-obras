@extends('layouts.app')

@section('title', 'Registro de Checklist Diario')

@section('content')
<div class="container py-4">
  <h2 class="h4 fw-bold mb-4">Registro de Checklist Diario</h2>

  @if ($errors->any())
    <div class="alert alert-danger">
      <strong>Ups...</strong> Hay errores en el formulario:
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form id="checklist-form" method="POST" action="{{ route('checklist.epp.store') }}">


    @csrf

    <!-- Trabajador -->
    <div class="mb-3">
      <label for="trabajador_id" class="form-label">Trabajador</label>
      <select name="trabajador_id" id="trabajador_id" class="form-select @error('trabajador_id') is-invalid @enderror"
        {{ isset($preseleccionado) ? 'disabled' : '' }} required>
        <option value="">Seleccionar trabajador...</option>
        @foreach($trabajadores as $t)
          <option value="{{ $t->id }}"
            {{ old('trabajador_id', $preseleccionado) == $t->id ? 'selected' : '' }}>
            {{ $t->name }}
          </option>
        @endforeach
      </select>
      @error('trabajador_id')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <!-- EPP asignado -->
    <div id="epp-asignado" class="alert alert-info d-none">
      <strong>EPP asignado:</strong>
      <ul id="epp-lista" class="mb-0"></ul>
    </div>

    @if (isset($preseleccionado))
      <input type="hidden" name="trabajador_id" value="{{ $preseleccionado }}">
    @endif

    <!-- Trabajo en altura -->
    <div class="mb-3 form-check">
      <input type="hidden" name="es_en_altura" value="0">
      <input type="checkbox" name="es_en_altura" id="es_en_altura" class="form-check-input" value="1" {{ old('es_en_altura') ? 'checked' : '' }}>
      <label for="es_en_altura" class="form-check-label">¿Trabaja en altura hoy?</label>
    </div>


    <!-- EPP checklist -->
    <div class="row g-3" id="epp-checklist">
      @foreach(['casco', 'anteojos', 'botas', 'chaleco', 'guantes', 'arnes'] as $epp)
      <div class="col-md-2">
        <div class="form-check">
          <input type="hidden" name="{{ $epp }}" value="0">
          <input type="checkbox" name="{{ $epp }}" id="{{ $epp }}" class="form-check-input" value="1" {{ old($epp) ? 'checked' : '' }}>
          <label for="{{ $epp }}" class="form-check-label text-capitalize">{{ $epp }}</label>
        </div>
        <div class="text-danger small d-none" id="alert-{{ $epp }}">No tiene {{ $epp }} asignado</div>
        @error($epp)
          <div class="text-danger small">{{ $message }}</div>
        @enderror
      </div>
      @endforeach
    </div>

    <!-- Observaciones -->
    <div class="mt-3">
      <label for="observaciones" class="form-label">Observaciones</label>
      <textarea name="observaciones" id="observaciones" class="form-control">{{ old('observaciones') }}</textarea>
      @error('observaciones')
        <div class="text-danger small">{{ $message }}</div>
      @enderror
    </div>

    <!-- Botón -->
    <div class="mt-4 text-end">
      <button type="submit" class="btn btn-primary">Registrar Checklist</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const select = document.getElementById('trabajador_id');
  const eppBox = document.getElementById('epp-asignado');
  const eppList = document.getElementById('epp-lista');
  const checklistItems = ['anteojos', 'botas', 'chaleco', 'guantes', 'arnes', 'casco'];
  const aliasTipos = {
    anteojos: 'lentes',
    botas: 'botas',
    chaleco: 'chaleco',
    guantes: 'guantes',
    arnes: 'arnes',
    casco: 'casco'
  };

  let asignados = [];

  function validarChecklistContraAsignado(data) {
    asignados = data.map(item => item.tipo.toLowerCase().trim().replace(/\s+/g, ''));

    checklistItems.forEach(epp => {
      const checkbox = document.getElementById(epp);
      const alerta = document.getElementById('alert-' + epp);
      const tipoReal = aliasTipos[epp] || epp;
      const tiene = asignados.includes(tipoReal);

      if (!tiene) {
        alerta.classList.remove('d-none');
        checkbox.classList.add('border-danger');
      } else {
        alerta.classList.add('d-none');
        checkbox.classList.remove('border-danger');
      }
    });
  }

  select.addEventListener('change', function () {
    const userId = this.value;
    if (!userId) return;

    fetch(`/epp/asignados/${userId}`)
      .then(res => res.json())
      .then(data => {
        eppList.innerHTML = '';
        if (data.length === 0) {
          eppList.innerHTML = '<li>No tiene EPP asignado</li>';
        } else {
          data.forEach(epp => {
            eppList.innerHTML += `<li>${epp.tipo}: ${epp.serie}</li>`;
          });
        }
        eppBox.classList.remove('d-none');
        validarChecklistContraAsignado(data);
      });
  });

  if (select.value) {
    select.dispatchEvent(new Event('change'));
  }

  // Validación antes de enviar
  document.getElementById('checklist-form').addEventListener('submit', function (e) {
  console.log('Interceptando submit…');


    let bloqueado = false;
    let incompleto = false;

    checklistItems.forEach(campo => {
      const tipoReal = aliasTipos[campo] || campo;
      const checkbox = document.getElementById(campo);
      const marcado = checkbox.checked;
      const tieneAsignado = asignados.includes(tipoReal);

      if (marcado && !tieneAsignado) {
        bloqueado = true;
        checkbox.classList.add('border-danger');
      }

      if (tieneAsignado && !marcado && campo !== 'arnes') {
        incompleto = true;
        checkbox.classList.add('border-warning');
      }
    });

    if (bloqueado) {
      e.preventDefault();
      alert('No se puede registrar el checklist: hay EPP marcados como usados pero no asignados.');
    } else if (incompleto) {
      e.preventDefault();
      alert('Este trabajador tiene EPP asignado que no fue marcado como usado hoy. Por favor revisá el checklist.');
    }
  });
});
</script>
@endpush

