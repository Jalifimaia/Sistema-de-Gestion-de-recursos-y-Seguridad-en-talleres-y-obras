@extends('layouts.app')

@section('title', 'Registro del checklist diario')

@section('content')
  <div class="container py-4">
  <!-- 🔶 Encabezado -->
<header class="mb-5 py-3 px-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap">

<a href="{{ request('from') === 'sinChecklist' 
              ? route('controlEPP.sinChecklist') 
              : route('controlEPP') }}" 
   class="btn btn-volver d-flex align-items-center">
  <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
  Volver
</a>

    <div class="text-center w-100 mt-3 d-flex justify-content-center align-items-center gap-2">
      <img src="{{ asset('images/check.svg') }}" alt="Checklist" class="icono-titulo">
      <h1 class="titulo-checklist mb-0">Registro del Checklist Diario</h1>
    </div>
  </div>
</header>

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

    <div id="aviso-checklist" class="text-danger mt-2 d-none"></div>


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
    <div class="mt-4 text-center">
      <button type="submit" class="btn btn-reg w-100">
        Registrar Checklist
      </button>
    </div>

  </form>
</div>

<!-- 🔶 Modal de advertencia -->
<div class="modal fade" id="modalChecklistIncompleto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-warning">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold">Checklist incompleto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="fs-5 mb-2">Faltan marcar los siguientes EPP asignados:</p>
        <ul id="listaEppFaltantes" class="mb-3 fs-5"></ul>
        <p class="text-danger fw-bold">Por favor revisá el checklist antes de continuar.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de error de checklist -->
<div class="modal fade" id="modalErrorChecklist" tabindex="-1" aria-labelledby="modalErrorChecklistLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalErrorChecklistLabel">Error al registrar checklist</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalErrorChecklistContenido">
        <!-- contenido dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de éxito al registrar checklist -->
<div class="modal fade" id="modalChecklistExito" tabindex="-1" aria-labelledby="modalChecklistExitoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalChecklistExitoLabel">Checklist registrado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalChecklistExitoContenido">
        <!-- contenido dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de advertencia por checklist crítico -->
<div class="modal fade" id="modalChecklistCritico" tabindex="-1" aria-labelledby="modalChecklistCriticoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalChecklistCriticoLabel">Advertencia de riesgo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p>El trabajador realiza tareas en altura pero no se marcó el uso de arnés.</p>
        <p>Este checklist será registrado como <strong>crítico</strong>. ¿Desea continuar?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarChecklistCritico">Registrar igual</button>
      </div>
    </div>
  </div>
</div>


@endsection

@push('styles')
<link href="{{ asset('css/registroCheck.css') }}" rel="stylesheet">
@endpush

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

  const eppPromise = fetch(`/epp/asignados/${userId}`).then(res => res.json());
  const checklistPromise = fetch(`/checklist/validar-hoy/${userId}`).then(res => res.json());

  Promise.all([eppPromise, checklistPromise]).then(([eppData, checklistData]) => {
    // Mostrar EPP asignado
    eppList.innerHTML = '';
    if (eppData.length === 0) {
      eppList.innerHTML = '<li>No tiene EPP asignado</li>';
    } else {
      eppData.forEach(epp => {
        eppList.innerHTML += `<li>${epp.tipo}: ${epp.serie}</li>`;
      });
    }
    eppBox.classList.remove('d-none');
    validarChecklistContraAsignado(eppData);

    // Mostrar aviso checklist
    const aviso = document.getElementById('aviso-checklist');
    if (checklistData.existe) {
      aviso.textContent = `Al trabajador ya se le realizó el checklist hoy.`;
      aviso.classList.remove('d-none');
    } else {
      aviso.textContent = '';
      aviso.classList.add('d-none');
    }
  });
});



  if (select.value) {
    select.dispatchEvent(new Event('change'));
  }

  // Validación antes de enviar
  document.getElementById('checklist-form').addEventListener('submit', function (e) {
    let bloqueado = false;
    let incompleto = false;
    let critico = false;

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

    const trabajaAltura = document.getElementById('es_en_altura')?.checked;
    const marcoArnes = document.getElementById('arnes')?.checked;

    if (trabajaAltura && !marcoArnes) {
      critico = true;
    }

    if (bloqueado) {
      e.preventDefault();
      const modal = new bootstrap.Modal(document.getElementById('modalErrorChecklist'));
      document.getElementById('modalErrorChecklistContenido').textContent =
        'No se puede registrar el checklist: hay EPP marcados como usados pero no asignados.';
      modal.show();
    } else if (incompleto) {
      e.preventDefault();
      const lista = document.getElementById('listaEppFaltantes');
      lista.innerHTML = '';

      checklistItems.forEach(campo => {
        const tipoReal = aliasTipos[campo] || campo;
        const checkbox = document.getElementById(campo);
        const marcado = checkbox.checked;
        const tieneAsignado = asignados.includes(tipoReal);

        if (tieneAsignado && !marcado && campo !== 'arnes') {
          lista.innerHTML += `<li>${tipoReal.charAt(0).toUpperCase() + tipoReal.slice(1)}</li>`;
        }
      });

      const modal = new bootstrap.Modal(document.getElementById('modalChecklistIncompleto'));
      modal.show();
    } else if (critico) {
      e.preventDefault();
      const modal = new bootstrap.Modal(document.getElementById('modalChecklistCritico'));
      modal.show();

      document.getElementById('btnConfirmarChecklistCritico').onclick = () => {
        modal.hide();
        document.getElementById('checklist-form').submit();
      };
    }
  });

  // Mostrar modal si el backend devolvió error de EPP no asignado
  @if($errors->has('epp_asignacion'))
    const modalError = new bootstrap.Modal(document.getElementById('modalErrorChecklist'));
    document.getElementById('modalErrorChecklistContenido').textContent = @json($errors->first('epp_asignacion'));
    modalError.show();
  @endif

  // Mostrar modal si el backend devolvió éxito
  @if(session('success'))
    const modalExito = new bootstrap.Modal(document.getElementById('modalChecklistExito'));
    document.getElementById('modalChecklistExitoContenido').textContent = @json(session('success'));
    modalExito.show();
  @endif
});
</script>
@endpush

