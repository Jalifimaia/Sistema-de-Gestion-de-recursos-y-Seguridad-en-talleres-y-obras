@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-start mb-3">
  <a href="{{ route('incidente.index') }}" class="btn btn-outline-secondary">
    ⬅️ Volver
  </a>
</div>

<div class="container">
    <h2>Editar incidente</h2>

    @if(isset($readonly) && $readonly)
      <div class="alert alert-info">
        Este incidente está resuelto y no puede editarse. Los campos están en modo solo lectura.
      </div>
    @endif

    <form method="POST" action="{{ route('incidente.update', $incidente->id) }}">
        @csrf
        @method('PUT')

        <!-- 🧍 Trabajador -->
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

        <!-- 🧰 Recursos asociados -->
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
                              {{-- modo solo lectura: mostrar estado como texto y enviar hidden --}}
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

        <!-- Estado del incidente -->
        @php
        $resueltoEstado = \App\Models\EstadoIncidente::where('nombre_estado', 'Resuelto')->first();
        @endphp

        <div class="mb-3">
        <label for="id_estado_incidente" class="form-label">Estado del incidente</label>

        @if(!empty($readonly))
            <input type="text" class="form-control" 
            value="{{ $incidente->estadoIncidente?->nombre_estado ?? '-' }}" readonly>
            <input type="hidden" name="id_estado_incidente" value="{{ $incidente->id_estado_incidente }}">
        @else
            <select name="id_estado_incidente" id="id_estado_incidente" class="form-select" required>
            @foreach($estados as $estado)
                @if($estado->nombre_estado !== 'Resuelto')
                <option value="{{ $estado->id }}" {{ $incidente->id_estado_incidente == $estado->id ? 'selected' : '' }}>
                    {{ $estado->nombre_estado }}
                </option>
                @endif
            @endforeach
            </select>
        @endif
        </div>


        <!-- Motivo / Descripción -->
        <div class="mb-3">
            <label for="descripcion" class="form-label">Motivo del incidente</label>

            @if(!empty($readonly))
              <input type="text" name="descripcion" id="descripcion" class="form-control" 
                     value="{{ $incidente->descripcion }}" readonly>
              <input type="hidden" name="descripcion" value="{{ $incidente->descripcion }}">
            @else
              <input type="text" name="descripcion" id="descripcion" class="form-control" 
                     value="{{ old('descripcion', $incidente->descripcion) }}" required>
            @endif
        </div>

        <!-- Resolución -->
        <div class="mb-3" id="resolucion-container" style="display: none;">
            <label for="resolucion" class="form-label" >Resolución</label>
            @if(!empty($readonly))
            <input type="text" name="resolucion" id="resolucion" class="form-control" value="{{ $incidente->resolucion }}" readonly >
            <input type="hidden" name="resolucion" value="{{ $incidente->resolucion }}">
            @else
            <input type="text" name="resolucion" id="resolucion" class="form-control" placeholder="Ingrese aquí la resolución del incidente" value="{{ old('resolucion', $incidente->resolucion) }}" required>
            @endif
        </div>


        <!-- Fecha del incidente (no editable) -->
        <div class="mb-3">
          <label class="form-label">Fecha del incidente</label>

          {{-- input visual no editable --}}
          <input type="text" class="form-control"
            value="{{ $incidente->fecha_incidente
                ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $incidente->fecha_incidente, 'UTC')
                    ->setTimezone(config('app.timezone'))
                    ->format('d/m/Y H:i')
                : '-' }}"
            readonly>

          {{-- hidden que efectivamente se envía en el form (UTC, formato Y-m-d H:i:s) --}}
          <input type="hidden" name="fecha_incidente"
            value="{{ $incidente->fecha_incidente
                ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $incidente->fecha_incidente, 'UTC')
                    ->format('Y-m-d H:i:s')
                : '' }}">
        </div>


        <div class="d-flex justify-content-between">
            @if(empty($readonly))
              <button type="submit" class="btn btn-success">Actualizar incidente</button>
            @endif
            <a href="{{ route('incidente.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

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

@push('scripts')
<script>
  const estadosOriginales = @json(
    $estados->filter(fn($e) => $e->nombre_estado !== 'Resuelto')
             ->map(fn($e) => ['id' => $e->id, 'nombre' => $e->nombre_estado])
             ->values()
  );
  const estadoOriginalSeleccionado = '{{ $incidente->id_estado_incidente }}';
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // IDs permitidos para cerrar (Disponible, Baja) — se expone desde controller o se calcula aquí
  const permitidosIds = @json($estadosPermitidos ?? \App\Models\Estado::whereIn('nombre_estado', ['Disponible','Baja'])->pluck('id')->toArray());
  const resueltoId = @json(optional($resueltoEstado)->id);

  const incidenteSelect = document.getElementById('id_estado_incidente');
  const ayudaContainerId = 'mensaje-puede-resolver';
  let ayudaNode = document.getElementById(ayudaContainerId);

  if (!ayudaNode) {
    // crear contenedor justo después del select (si existe)
    const cont = incidenteSelect ? incidenteSelect.parentNode : null;
    ayudaNode = document.createElement('div');
    ayudaNode.id = ayudaContainerId;
    ayudaNode.className = 'mt-2';
    if (cont) cont.appendChild(ayudaNode);
  }

  // selects de estado de recursos
  const estadoSelects = Array.from(document.querySelectorAll('select[name^="recursos"][name$="[id_estado]"].recurso-estado-select'));

  // helper para obtener nombre del recurso desde la tarjeta .recurso-block
  function nombreRecursoParaSelect(sel) {
    const card = sel.closest('.recurso-block') || sel.closest('.card') || sel.parentElement;
    if (!card) return 'Recurso';
    // buscamos el primer input text readonly que no sea hidden
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

  function recalcular() {
    const bloqueadores = [];

    estadoSelects.forEach(sel => {
      const val = sel.value ? String(sel.value) : null;
      if (!val) {
        bloqueadores.push({ nombre: nombreRecursoParaSelect(sel), estadoText: textEstadoSel(sel) });
        return;
      }
      if (!permitidosIds.map(String).includes(String(val))) {
        bloqueadores.push({ nombre: nombreRecursoParaSelect(sel), estadoText: textEstadoSel(sel) });
      }
    });

    
    if (bloqueadores.length === 0) {
  ayudaNode.innerHTML = '<div class="text-success"><strong>Resuelto.</strong> Todos los recursos están en estado Disponible o Baja.</div>';

  // Forzar selección de Resuelto
  if (resueltoId && incidenteSelect) {
  // Reemplazar todas las opciones por solo "Resuelto"
  incidenteSelect.innerHTML = '';
  const opt = document.createElement('option');
  opt.value = String(resueltoId);
  opt.textContent = 'Resuelto';
  opt.selected = true;
  incidenteSelect.appendChild(opt);
  incidenteSelect.dispatchEvent(new Event('change', { bubbles: true }));
}


  // Mostrar campo de resolución
  const resolucionContainer = document.getElementById('resolucion-container');
  if (resolucionContainer) {
    resolucionContainer.style.display = '';
  }

} else {
  // Mostrar advertencia
  let html = '<div class="text-warning"><strong>Para marcar el incidente como Resuelto, todos los recursos deben estar en estado Disponible o Baja.</strong></div>';
  html += '<ul class="mt-1 mb-0 small text-danger">';
  bloqueadores.forEach(b => {
    html += `<li>${escapeHtml(b.nombre)} — estado actual: ${escapeHtml(b.estadoText)}</li>`;
  });
  html += '</ul>';
  ayudaNode.innerHTML = html;

  // Ocultar opción Resuelto
  // Restaurar opciones originales (sin "Resuelto")
if (incidenteSelect) {
  incidenteSelect.innerHTML = '';
  estadosOriginales.forEach(e => {
    const opt = document.createElement('option');
    opt.value = e.id;
    opt.textContent = e.nombre;
    opt.selected = (String(e.id) === incidenteSelect.value);
    incidenteSelect.appendChild(opt);
  });
  incidenteSelect.dispatchEvent(new Event('change', { bubbles: true }));
}


  // Ocultar campo de resolución
  const resolucionContainer = document.getElementById('resolucion-container');
  if (resolucionContainer) {
    resolucionContainer.style.display = 'none';
  }
}


    if (incidenteSelect && typeof resueltoId !== 'undefined' && resueltoId !== null) {
      const optionRes = Array.from(incidenteSelect.options).find(o => o.value == String(resueltoId));
      if (optionRes) {
        if (bloqueadores.length === 0) {
          optionRes.disabled = false;
          optionRes.style.display = '';
        } else {
          // ocultar la opción Resuelto para que no sea seleccionable
          optionRes.style.display = 'none';
          optionRes.disabled = true;
          if (incidenteSelect.value == String(resueltoId)) {
            const firstValid = Array.from(incidenteSelect.options).find(o => o.value != String(resueltoId) && !o.disabled && o.style.display !== 'none');
            if (firstValid) incidenteSelect.value = firstValid.value;
          }
        }
      }
    }
  }

  // listeners dinámicos: si se agregan bloques dinámicamente, re-scanear
  function attachListeners() {
    const current = Array.from(document.querySelectorAll('select[name^="recursos"][name$="[id_estado]"].recurso-estado-select'));
    // attach change only to those without listener (cheap approach: remove all and re-add)
    estadoSelects.length = 0;
    current.forEach(s => {
      estadoSelects.push(s);
      s.removeEventListener('change', recalcular);
      s.addEventListener('change', recalcular);
    });
  }

  // inicial
  attachListeners();
  recalcular();

  // observar DOM por cambios en recursos (si el usuario añade/quita recursos)
  const recursosContainer = document.getElementById('recursos-container');
  if (recursosContainer) {
    const mo = new MutationObserver(() => {
      attachListeners();
      recalcular();
    });
    mo.observe(recursosContainer, { childList: true, subtree: true });
  }
});
</script>
@endpush

@endsection
