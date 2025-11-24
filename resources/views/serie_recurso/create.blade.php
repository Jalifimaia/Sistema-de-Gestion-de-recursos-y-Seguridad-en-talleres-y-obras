@extends('layouts.app')

@section('title', 'Agregar series a ' . $recurso->nombre . ' [' . ($recurso->subcategoria->nombre ?? '') . ']')

@section('content')
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-start mb-4 gap-3 flex-wrap">
    <a href="{{ route('inventario.index') }}" class="btn btn-volver d-flex align-items-center">
      <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
      Volver
    </a>

    <div class="d-flex align-items-center">
      <img src="{{ asset('images/herradd.svg') }}" alt="Herramienta" style="width: 40px; height: 40px;" class="me-2">
      <h4 class="fw-bold mb-0">
        Agregar series para: {{ $recurso->nombre }} [{{ $recurso->subcategoria->nombre ?? '' }}]
      </h4>
    </div>
  </div>

  @php
    // Requiere talle si la SUBCATEGORÍA es Chaleco o Botas
    $sub = strtolower($recurso->subcategoria->nombre ?? '');
    $requiereTalle = in_array($sub, ['chaleco', 'botas']);
  @endphp

  <!-- Para que el JS local pueda leer la subcategoría -->
  <span id="subcategoriaNombre" class="d-none">{{ $recurso->subcategoria->nombre ?? '' }}</span>

  <form method="POST" action="{{ route('serie_recurso.storeMultiple') }}" id="formSeries" novalidate>
    @csrf
    <input type="hidden" name="id_recurso" value="{{ $recurso->id }}">
    <input type="hidden" name="combinaciones" id="combinaciones">
    <input type="hidden" name="id_estado" value="{{ $estadoDisponible->id }}">

    <!-- Dos columnas: inputs lado a lado -->
    <div class="row g-4">
      <!-- Descripción (solo lectura) -->
      <div class="col-12">
        <label for="descripcion" class="form-label mb-1">Descripción del recurso</label>
        <input type="text" id="descripcion" class="form-control" value="{{ $recurso->descripcion }}" disabled>
      </div>

      <!-- Versión -->
      <div class="col-12 col-md-6">
        <label for="version" class="form-label mb-1">Versión <span class="required-asterisk">*</span></label>
        <select name="version" id="version" class="form-select" required>
          <option value="" disabled selected>Seleccione la versión</option>
          @for($i = 1; $i <= 10; $i++)
            <option value="{{ $i }}">{{ $i }}</option>
          @endfor
        </select>
        <div id="error-version" class="text-danger small mt-1 d-none  no-asterisk">Este campo es obligatorio.</div>
      </div>

      <!-- Año -->
      <div class="col-12 col-md-6">
        <label for="anio" class="form-label mb-1">Año <span class="required-asterisk">*</span></label>
        <select name="anio" id="anio" class="form-select" required>
          <option value="" disabled selected>Seleccione el año</option>
          @for($y = 2000; $y <= now()->year; $y++)
            <option value="{{ $y }}">{{ $y }}</option>
          @endfor
        </select>
        <div id="error-anio" class="text-danger small mt-1 d-none  no-asterisk">Este campo es obligatorio.</div>
      </div>

      <!-- Lote -->
      <div class="col-12 col-md-6">
        <label for="lote" class="form-label mb-1">Lote <span class="required-asterisk">*</span></label>
        <input type="number"
               name="lote"
               id="lote"
               class="form-control"
               placeholder="Ingrese el número de lote"
               min="1"
               required>
        <div id="error-lote" class="text-danger small mt-1 d-none  no-asterisk">Este campo es obligatorio.</div>
      </div>

      <!-- Fecha de adquisición (date, bloqueada hasta hoy, click en toda el área) -->
      <div class="col-12 col-md-6">
        <label for="fecha_adquisicion" class="form-label mb-1">
          Fecha de adquisición <span class="required-asterisk">*</span>
        </label>

        <div class="input-group date-click-wrap" onclick="this.querySelector('input').showPicker()">
          <input
            type="date"
            name="fecha_adquisicion"
            id="fecha_adquisicion"
            class="form-control @error('fecha_adquisicion') is-invalid @enderror"
            value="{{ old('fecha_adquisicion') }}"
            required
            aria-describedby="error-fecha_adquisicion"
            aria-invalid="{{ $errors->has('fecha_adquisicion') ? 'true' : 'false' }}"
            max="{{ now()->format('Y-m-d') }}"
          >
        </div>

        <div id="error-fecha_adquisicion" class="text-danger small mt-1 d-none  no-asterisk">Este campo es obligatorio.</div>
        @error('fecha_adquisicion')
          <div class="invalid-feedback" id="error-fecha_adquisicion">{{ $message }}</div>
        @enderror
      </div>

      <!-- Fecha de vencimiento -->
      <div class="col-12 col-md-6">
        <label for="fecha_vencimiento" class="form-label mb-1">Fecha de vencimiento (opcional)</label>
        <div class="input-group" onclick="this.querySelector('input').showPicker()">
          <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control">
        </div>
      </div>
    </div>

    <div class="mb-4 mt-4">
     
      <h5>Series por {{ $requiereTalle ? 'talle y color' : 'color' }}<span class="required-asterisk">*</span></h5>
      <table class="table table-bordered text-center">
        <thead>
          <tr>
            @if($requiereTalle)
              <th>Tipo de talle</th>
              <th>Talle</th>
            @endif
            <th>Color</th>
            <th>Cantidad</th>
            <th>Código</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="combinacionesBody">
          <!-- Filas dinámicas -->
        </tbody>
      </table>

      <!-- Mensaje de error de combinaciones -->
      <div id="error-combinaciones" class="alert alert-danger d-none mt-2" role="alert" ></div>

      <div class="d-flex justify-content-start gap-3 mt-3 flex-wrap">
        <button type="button" class="btn btn-combinacion" onclick="agregarFila()">+ Agregar combinación</button>
        <button type="submit" class="btn btn-guardar" id="btnGuardar">Guardar series</button>
      </div>
    </div>
  </form>
</div>

<!-- Modal de éxito -->
<div class="modal fade" id="modalSeriesAgregadas" tabindex="-1" aria-labelledby="modalSeriesAgregadasLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalSeriesAgregadasLabel">Series agregadas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Las series fueron agregadas correctamente.
      </div>
      <div class="modal-footer">
        <a href="{{ route('inventario.index') }}" class="btn btn-outline-success">Volver al inventario</a>
        <a href="{{ url()->current() }}" class="btn btn-success">Agregar más series</a>
      </div>
    </div>
  </div>
</div>

<!-- Modal de error de tipo de talle -->
<div class="modal fade" id="modalErrorTipoTalle" tabindex="-1" aria-labelledby="modalErrorTipoTalleLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalErrorTipoTalleLabel">Error en tipo de talle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        El tipo de talle debe ser <strong>"{{ $requiereTalle ? ($recurso->subcategoria->nombre === 'Botas' ? 'Calzado' : 'Ropa') : '' }}"</strong> o <strong>"Otro"</strong> para el recurso seleccionado.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal faltan campos -->
<div class="modal fade" id="modalErrorCampos" tabindex="-1" aria-labelledby="modalErrorCamposLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalErrorCamposLabel">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Faltan campos por completar. Por favor, revisá el formulario.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger text-white" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  window.colores = @json($colores->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre]));
  window.nombreRecurso = @json($recurso->nombre);
  window.descripcionRecurso = @json($recurso->descripcion);
  window.requiereTalle = @json($requiereTalle);
  window.tallesPorTipo = @json($talles);
</script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('formSeries');

  // Helper: mostrar/ocultar error para un campo
  function setFieldError(field, show) {
    const errorEl = document.getElementById('error-' + field.id);
    if (!errorEl) return;

    if (show) {
      errorEl.classList.remove('d-none');
      field.classList.add('is-invalid');
      field.setAttribute('aria-invalid', 'true');
    } else {
      errorEl.classList.add('d-none');
      field.classList.remove('is-invalid');
      field.removeAttribute('aria-invalid');
    }
  }

  // Validación en tiempo real
  form.querySelectorAll('[required]').forEach(field => {
    field.addEventListener('input', () => {
      if (String(field.value).trim()) setFieldError(field, false);
    });
    field.addEventListener('change', () => {
      if (String(field.value).trim()) setFieldError(field, false);
    });
  });

  // Validación al presionar Enter
  form.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      validarYEnviar();
    }
  });

  function validarYEnviar() {
    const requiredFields = Array.from(form.querySelectorAll('[required]'));
    let firstInvalid = null;
    let hasErrors = false;

    requiredFields.forEach(field => {
      const empty = !String(field.value).trim();
      setFieldError(field, empty);
      if (empty && !firstInvalid) firstInvalid = field;
      if (empty) hasErrors = true;
    });

    if (hasErrors) {
      if (firstInvalid) firstInvalid.focus();
      return false;
    }
    return true;
  }
});

</script>
<script src="{{ asset('js/crearSeries.js') }}"></script>

@endpush

@push('styles')
<link href="{{ asset('css/agregarSerie.css') }}" rel="stylesheet">
<style>

  
  /* Consistencia visual de errores */
  .is-invalid {
    /*border-color: #dc3545 !important;*/
    box-shadow: none;
  }
  
  /* Asegurar que los select también muestren el borde rojo */
  select.is-invalid,
  select.form-select.is-invalid {
    /*border-color: #dc3545 !important;*/
    box-shadow: none;
  }
  
  /* Fondo rojo para td con campos inválidos */
  td.td-invalid {
    background-color: #f8d7da !important;
  }
  
  /* Borde rojo para Select2 cuando está inválido */
  .select2-invalid {
    /*border-color: #dc3545 !important;*/
    box-shadow: none !important;
  }
  
  .required-asterisk {
    margin-left: 4px;
    color: red; /*asterisco*/
    font-weight: 600;
  }

  /* Separación suave entre controles en dos columnas */
  .row.g-4 .form-control,
  .row.g-4 .form-select {
    min-height: 38px;
  }

  /* Oculta el input de búsqueda solo en el select2 de tipo de talle (si existiera) */
  #tipoTalle + .select2 .select2-search__field {
    display: none !important;
  }

  /* Borde rojo para Select2 cuando el td está inválido */
td.td-invalid .select2-selection {
  /*border-color: #dc3545 !important;*/
  box-shadow: none !important;
}

#error-combinaciones {
  display: none !important;
  visibility: hidden !important;
}

</style>
@endpush