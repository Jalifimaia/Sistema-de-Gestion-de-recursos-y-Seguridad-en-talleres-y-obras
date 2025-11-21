@extends('layouts.app')

@section('title', 'Editar incidente')

@section('content')
<div class="container py-4">

  <!-- Encabezado -->
  <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="{{ route('incidente.index') }}" class="btn btn-volver d-inline-flex align-items-center">
      <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
      Volver
    </a>

    <h4 class="fw-bold text-orange mb-0 d-flex align-items-center">
      <img src="{{ asset('images/lapiz.svg') }}" alt="Editar" class="me-2 icono-volver">
      Editar incidente
    </h4>
  </div>


    @if(isset($readonly) && $readonly)
      <div class="alert alert-info">
        Este incidente está resuelto y no puede editarse. Los campos están en modo solo lectura.
      </div>
    @endif

    <form method="POST" action="{{ route('incidente.update', $incidente->id) }}">
        @csrf
        @method('PUT')

        <!-- Trabajador -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">Datos del Trabajador</div>
            <div class="card-body">
                <div class="row mb-3">
                  <div class="col-md-4">
                      <label class="form-label">Trabajador</label>
                      <input type="text" class="form-control"
                          value="{{ $incidente->trabajador?->name ?? '-' }}" readonly>
                      <input type="hidden" name="id_trabajador" value="{{ $incidente->trabajador?->id ?? '' }}">
                  </div>

                  <div class="col-md-4">
                      <label class="form-label">DNI</label>
                      <input type="text" class="form-control"
                          value="{{ $incidente->trabajador?->dni ?? '-' }}" readonly>
                      <input type="hidden" name="dni_trabajador" value="{{ $incidente->trabajador?->dni ?? '' }}">
                  </div>
                </div>
            </div>
        </div>

        <!-- Recursos asociados -->
        <div id="recursos-container">
            @foreach($incidente->recursos as $i => $recurso)
            <div class="card mb-3 recurso-block">
                <div class="card-header bg-success text-white">Recurso asociado</div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Categoría</label>
                            <input type="text" class="form-control" 
                                value="{{ $recurso->subcategoria->categoria->nombre_categoria ?? '-' }}" readonly>
                            <input type="hidden" name="recursos[{{ $i }}][id_categoria]" 
                                value="{{ $recurso->subcategoria->categoria->id ?? '' }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Subcategoría</label>
                            <input type="text" class="form-control" 
                                value="{{ $recurso->subcategoria->nombre ?? '-' }}" readonly>
                            <input type="hidden" name="recursos[{{ $i }}][id_subcategoria]" 
                                value="{{ $recurso->subcategoria->id ?? '' }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Recurso</label>
                            <input type="text" class="form-control" 
                                value="{{ $recurso->nombre ?? '-' }}" readonly>
                            <input type="hidden" name="recursos[{{ $i }}][id_recurso]" 
                                value="{{ $recurso->id ?? '' }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Serie del recurso</label>
                            <input type="text" class="form-control" 
                                value="{{ $recurso->serieRecursos->firstWhere('id', $recurso->pivot->id_serie_recurso)?->nro_serie ?? '-' }}" readonly>
                            <input type="hidden" name="recursos[{{ $i }}][id_serie_recurso]" 
                                value="{{ $recurso->pivot->id_serie_recurso ?? '' }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Estado</label>

                            @if(!empty($readonly))
                              <input type="text" class="form-control" 
                                     value="{{ $estadosRecurso->firstWhere('id', $recurso->pivot->id_estado)?->nombre_estado ?? ($estados[$recurso->pivot->id_estado] ?? 'Sin estado') }}" readonly>
                              <input type="hidden" name="recursos[{{ $i }}][id_estado]" value="{{ $recurso->pivot->id_estado ?? '' }}">
                            @else
                              <select name="recursos[{{ $i }}][id_estado]" class="form-select recurso-estado-select" required>
                                  <option value="">Seleccione</option>
                                  @foreach($estadosRecurso as $estado)
                                      <option value="{{ $estado->id }}"
                                          {{ (string)($recurso->pivot->id_estado ?? '') === (string)$estado->id ? 'selected' : '' }}>
                                          {{ $estado->nombre_estado }}
                                      </option>
                                  @endforeach
                              </select>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @php
          $resueltoEstado = \App\Models\EstadoIncidente::where('nombre_estado', 'Resuelto')->first();
        @endphp

        <!-- Card agrupada -->
        <div class="card mb-4">
          <div class="card-header bg-orange text-white fw-bold">
            Información del incidente
          </div>
          <div class="card-body">

            <!-- Estado del incidente -->
            <div class="mb-3">
              <label for="id_estado_incidente" class="form-label">Estado del incidente</label>
              @if(!empty($readonly))
                <input type="text" class="form-control" 
                      value="{{ $incidente->estadoIncidente?->nombre_estado ?? '-' }}" readonly>
                <input type="hidden" name="id_estado_incidente" value="{{ $incidente->id_estado_incidente }}">
              @else
                <select name="id_estado_incidente" id="id_estado_incidente" class="form-select" required>
                  @foreach($estados as $estado)
                    <option value="{{ $estado->id }}" {{ $incidente->id_estado_incidente == $estado->id ? 'selected' : '' }}>
                      {{ $estado->nombre_estado }}
                    </option>
                  @endforeach
                  @if(!$estados->firstWhere('nombre_estado', 'Resuelto'))
                    <option value="{{ optional($resueltoEstado)->id }}" {{ $incidente->id_estado_incidente == optional($resueltoEstado)->id ? 'selected' : '' }}>Resuelto</option>
                  @endif
                </select>
              @endif
            </div>

            <!-- Motivo / Descripción -->
            <div class="mb-3">
              <label for="descripcion" class="form-label">Motivo del incidente</label>
              @if(!empty($readonly))
                <textarea name="descripcion" id="descripcion" class="form-control" readonly>{{ $incidente->descripcion }}</textarea>
                <input type="hidden" name="descripcion" value="{{ $incidente->descripcion }}">
              @else
                <textarea name="descripcion" id="descripcion" class="form-control"
                          required maxlength="255"
                          placeholder="Ingrese aquí cuál fue el motivo del incidente (máx. 255 caracteres).">{{ old('descripcion', $incidente->descripcion) }}</textarea>
              @endif
            </div>


            <!-- Fecha del incidente -->
            <div class="mb-3">
              <label class="form-label">Fecha del incidente</label>
              <input type="text" class="form-control"
                value="{{ $incidente->fecha_incidente
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $incidente->fecha_incidente, 'UTC')
                        ->setTimezone(config('app.timezone'))
                        ->format('d/m/Y H:i')
                    : '-' }}"
                readonly>
              <input type="hidden" name="fecha_incidente"
                value="{{ $incidente->fecha_incidente
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $incidente->fecha_incidente, 'UTC')
                        ->format('Y-m-d H:i:s')
                    : '' }}">
            </div>

          </div>
        </div>

        <!-- Resolución -->
        <div class="mb-3" id="resolucion-container" style="display: none;">
          <label for="resolucion" class="form-label">Resolución</label>
          @if(!empty($readonly))
            <input type="text" name="resolucion" id="resolucion" class="form-control" value="{{ $incidente->resolucion }}" readonly>
            <input type="hidden" name="resolucion" value="{{ $incidente->resolucion }}">
          @else
            <input type="text" name="resolucion" id="resolucion" class="form-control" placeholder="Ingrese aquí la resolución del incidente" value="{{ old('resolucion', $incidente->resolucion) }}">
          @endif
        </div>

        <!-- Botones -->
            <div class="text-center mt-4">
              @if(empty($readonly))
                <button type="submit" class="btn btn-actualizar w-100 mb-3">
                  Actualizar incidente
                </button>
              @endif

              <a href="{{ route('incidente.index') }}" class="btn btn-cancelar w-100">
                Cancelar
              </a>
            </div>


            </form>
        </div>

{{-- Modales --}}
@if(session('success'))
<!-- Modal de éxito -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">✅ Incidente actualizado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        {{ session('success') }}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir editando</button>
        <a href="{{ route('incidente.index') }}" class="btn btn-success">Ver incidentes</a>
      </div>
    </div>
  </div>
</div>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
  });
</script>
@endif

<!-- Modal error resolución -->
<div class="modal fade" id="modalFaltaResolucion" tabindex="-1" aria-labelledby="modalFaltaResolucionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-orange text-white">
        <h5 class="modal-title" id="modalFaltaResolucionLabel">Falta resolución</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Para marcar el incidente como <strong>Resuelto</strong>, debe ingresar una resolución.
      </div>
      <div class="modal-footer">
        <button type="button" id="btnCompletarResolucion" class="btn btn-outline-orange" data-bs-dismiss="modal">Completar resolución</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // estados originales (sin procesar) y id seleccionado al cargar
  const estadosOriginales = @json($estados->map(fn($e) => ['id' => $e->id, 'nombre' => $e->nombre_estado]));
  const estadoOriginalSeleccionado = '{{ $incidente->id_estado_incidente }}';
  const resueltoId = @json(optional($resueltoEstado)->id);
  const permitidosIds = @json($estadosPermitidos ?? \App\Models\Estado::whereIn('nombre_estado', ['Disponible','Baja'])->pluck('id')->toArray());
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const incidenteSelect = document.getElementById('id_estado_incidente');
  const ayudaContainerId = 'mensaje-puede-resolver';
  let ayudaNode = document.getElementById(ayudaContainerId);
  if (!ayudaNode && incidenteSelect) {
    const cont = incidenteSelect.parentNode;
    ayudaNode = document.createElement('div');
    ayudaNode.id = ayudaContainerId;
    ayudaNode.className = 'mt-2';
    cont.appendChild(ayudaNode);
  }

  // selects de estado de recursos
  function getEstadoSelects() {
    return Array.from(document.querySelectorAll('select[name^="recursos"][name$="[id_estado]"].recurso-estado-select'));
  }

  function nombreRecursoParaSelect(sel) {
    const card = sel.closest('.recurso-block') || sel.closest('.card') || sel.parentElement;
    if (!card) return 'Recurso';
    const nameInput = card.querySelector('input[type="text"][readonly]');
    if (nameInput && nameInput.value) return nameInput.value;
    const header = card.querySelector('.card-header');
    if (header) return header.textContent.trim();
    return 'Recurso';
  }

  function textEstadoSel(sel) {
    return sel.options[sel.selectedIndex]?.text?.trim() || 'Sin estado';
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"'`=\/]/g, function (s) {
      return ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
      })[s];
    });
  }

  function calcularBloqueadores() {
    const bloqueadores = [];
    const selects = getEstadoSelects();
    selects.forEach(sel => {
      const val = sel.value ? String(sel.value) : null;
      if (!val) {
        bloqueadores.push({ nombre: nombreRecursoParaSelect(sel), estadoText: textEstadoSel(sel) });
        return;
      }
      if (!permitidosIds.map(String).includes(String(val))) {
        bloqueadores.push({ nombre: nombreRecursoParaSelect(sel), estadoText: textEstadoSel(sel) });
      }
    });
    return bloqueadores;
  }

  function mostrarAyudaYGestionarResuelto() {
    const bloqueadores = calcularBloqueadores();

    if (bloqueadores.length === 0) {
      ayudaNode.innerHTML = '<div class="text-success"><strong>Puede seleccionar resuelto.</strong> Todos los recursos están en estado Disponible o Baja.</div>';
      enableResueltoOption(true);
      // mostrar resolucion
      const resolucionContainer = document.getElementById('resolucion-container');
      if (resolucionContainer) resolucionContainer.style.display = '';
    } else {
      let html = `
        <div class="d-flex align-items-center gap-2 text-warning fw-semibold">
          <img src="/images/precaucion.svg" alt="Precaución" class="icono-precaucion">
          <span>Para marcar el incidente como <strong>Resuelto</strong>, todos los recursos deben estar en estado <strong>Disponible</strong> o <strong>Baja</strong>.</span>
        </div>
        <ul class="mt-1 mb-0 small text-danger fw-semibold">
      `;
      bloqueadores.forEach(b => {
        html += `<li>${escapeHtml(b.nombre)} — estado actual: ${escapeHtml(b.estadoText)}</li>`;
      });
      html += '</ul>';
      ayudaNode.innerHTML = html;
      enableResueltoOption(false);
      const resolucionContainer = document.getElementById('resolucion-container');
      if (resolucionContainer) resolucionContainer.style.display = 'none';
    }
  }

  function enableResueltoOption(allow) {
    if (!incidenteSelect) return;
    // buscar opción Resuelto (si existe)
    const optRes = Array.from(incidenteSelect.options).find(o => String(o.value) === String(resueltoId));
    if (optRes) {
      if (allow) {
        optRes.disabled = false;
        optRes.style.display = '';
      } else {
        // si está seleccionado actualmente y no permitimos, cambiar a valor original o al primero visible
        if (incidenteSelect.value == String(resueltoId)) {
          // intentar reestablecer selección previa
          if (estadoOriginalSeleccionado) incidenteSelect.value = estadoOriginalSeleccionado;
          else {
            const firstOpt = Array.from(incidenteSelect.options).find(o => !o.disabled && o.style.display !== 'none' && String(o.value) !== String(resueltoId));
            if (firstOpt) incidenteSelect.value = firstOpt.value;
          }
        }
        optRes.disabled = true;
        optRes.style.display = 'none';
      }
    } else {
      // si no existe, y allow = true, crearla
      if (allow && resueltoId) {
        const newOpt = document.createElement('option');
        newOpt.value = String(resueltoId);
        newOpt.textContent = 'Resuelto';
        newOpt.selected = false;
        incidenteSelect.appendChild(newOpt);
      }
    }
  }

  // attach listeners
  function attachListeners() {
    getEstadoSelects().forEach(s => {
      s.removeEventListener('change', mostrarAyudaYGestionarResuelto);
      s.addEventListener('change', mostrarAyudaYGestionarResuelto);
    });
    if (incidenteSelect) {
      incidenteSelect.removeEventListener('change', onIncidenteChange);
      incidenteSelect.addEventListener('change', onIncidenteChange);
    }
  }

  function onIncidenteChange() {
    const resolucionContainer = document.getElementById('resolucion-container');
    if (!resolucionContainer) return;
    const seleccionado = incidenteSelect.value;
    if (String(seleccionado) === String(resueltoId)) {
      resolucionContainer.style.display = '';
    } else {
      resolucionContainer.style.display = 'none';
    }
  }

  // submit handler robusto
  (function () {
    const form = document.querySelector('form[action*="/incidente/"]') || document.querySelector('form');
    if (!form) {
      console.warn('No se encontró el formulario en la página.');
      return;
    }

    form.addEventListener('submit', function (e) {
      try {
        if (!incidenteSelect || typeof resueltoId === 'undefined' || resueltoId === null) return;

        const seleccionado = String(incidenteSelect.value);
        if (seleccionado === String(resueltoId)) {
          const resolucionInput = document.getElementById('resolucion');
          const texto = resolucionInput ? resolucionInput.value.trim() : '';

          if (!texto) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const modalEl = document.getElementById('modalFaltaResolucion');
            if (modalEl && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
              const m = new bootstrap.Modal(modalEl);
              m.show();
              modalEl.addEventListener('hidden.bs.modal', function handler() {
                modalEl.removeEventListener('hidden.bs.modal', handler);
                if (resolucionInput) {
                  resolucionInput.focus();
                  resolucionInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
              });
            } else {
              alert('Debe ingresar una resolución para cerrar el incidente.');
              if (resolucionInput) {
                resolucionInput.focus();
                resolucionInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
              }
            }
          }
        }
      } catch (err) {
        console.error('Error en validación submit:', err);
      }
    }, { passive: false });
  })();

  // inicialización
  attachListeners();
  mostrarAyudaYGestionarResuelto();
  onIncidenteChange();

  // MutationObserver para detectar cambios en recursos dinámicos
  const recursosContainer = document.getElementById('recursos-container');
  if (recursosContainer) {
    const mo = new MutationObserver(() => {
      attachListeners();
      mostrarAyudaYGestionarResuelto();
    });
    mo.observe(recursosContainer, { childList: true, subtree: true });
  }

  // botón del modal de completar resolución
  const btnCompletar = document.getElementById('btnCompletarResolucion');
  if (btnCompletar) {
    btnCompletar.addEventListener('click', () => {
      setTimeout(() => {
        const resol = document.getElementById('resolucion');
        if (resol) {
          resol.focus();
          resol.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }, 200);
    });
  }
});
</script>
@endpush

@endsection

@push('styles')
<link href="{{ asset('css/editarIncidente.css') }}" rel="stylesheet">
@endpush

