@extends('layouts.app')

@section('template_title')
    Agregar Series a {{ $recurso->nombre }} [{{ $recurso->subcategoria->nombre ?? '' }}]
@endsection

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
            Agregar Series para: {{ $recurso->nombre }} [{{ $recurso->subcategoria->nombre ?? '' }}]
          </h4>
        </div>
      </div>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if (session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif

    @php
        // Requiere talle si la SUBCATEGORÍA es Chaleco o Botas
        $sub = strtolower($recurso->subcategoria->nombre ?? '');
        $requiereTalle = in_array($sub, ['chaleco', 'botas']);
    @endphp

    <!-- Añadir para que el JS local pueda leer la subcategoría -->
<span id="subcategoriaNombre" class="d-none">{{ $recurso->subcategoria->nombre ?? '' }}</span>


    <form method="POST" action="{{ route('serie_recurso.storeMultiple') }}" id="formSeries">
        @csrf
        <input type="hidden" name="id_recurso" value="{{ $recurso->id }}">
        <input type="hidden" name="combinaciones" id="combinaciones">

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción del recurso</label>
            <input type="text" id="descripcion" class="form-control" value="{{ $recurso->descripcion }}" disabled>
        </div>

        <div class="mb-3">
            <label for="version" class="form-label">Versión</label>
            <select name="version" id="version" class="form-select" required>
                <option value="" disabled selected>Seleccione versión</option>
                @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="mb-3">
            <label for="anio" class="form-label">Año</label>
            <select name="anio" id="anio" class="form-select" required>
                <option value="" disabled selected>Seleccione año</option>
                @for($y = 2000; $y <= now()->year; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="mb-3">
          <label for="lote" class="form-label">Lote</label>
          <input type="number"
                name="lote"
                id="lote"
                class="form-control"
                placeholder="Ingrese el N° de lote"
                min="1"
                required>
        </div>

        <div class="mb-3">
          <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label>
          <div class="input-group" onclick="this.querySelector('input').showPicker()">
            <input type="date" name="fecha_adquisicion" id="fecha_adquisicion" class="form-control" required>
          </div>
          <div id="fecha_adquisicion_error" class="invalid-feedback" style="display:none;"></div>
        </div>



        <div class="mb-3">
        <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento (opcional)</label>
        <div class="input-group" onclick="this.querySelector('input').showPicker()">
            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control">
        </div>
        </div>


            <input type="hidden" name="id_estado" value="{{ $estadoDisponible->id }}">


        <div class="mb-4">
            <h5>Series por {{ $requiereTalle ? 'Talle y Color' : 'Color' }}</h5>
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        @if($requiereTalle)
                            <th>Tipo de Talle</th>
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
            <div class="d-flex justify-content-start gap-3 mt-3 flex-wrap">
              <button type="button" class="btn btn-combinacion" onclick="agregarFila()">+ Agregar combinación</button>

              <button type="submit" class="btn btn-guardar" id="btnGuardar" disabled>Guardar Series</button>
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

@endsection

@push('scripts')
<script>
    window.colores = @json($colores->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre]));
    window.nombreRecurso = @json($recurso->nombre);
    window.descripcionRecurso = @json($recurso->descripcion);
    window.requiereTalle = @json($requiereTalle);
    window.tallesPorTipo = @json($talles); 
</script>
<script src="{{ asset('js/serieRecurso.js') }}"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  
  const loteInput = document.getElementById('lote');
  loteInput.addEventListener('input', () => {
    if (loteInput.value.length > 5) {
      loteInput.value = loteInput.value.slice(0, 5); // 🔹 corta a 5 dígitos
    }
  });
  const form = document.getElementById('formSeries');

  // 🔹 Validación al presionar Enter
  form.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();

      const requiredFields = form.querySelectorAll('[required]');
      let firstInvalid = null;

      requiredFields.forEach(field => {
        const container = field.closest('.mb-3') || field.parentElement;
        const errorId = 'error-' + field.id;

        // Eliminar errores previos
        const prevError = document.getElementById(errorId);
        if (prevError) prevError.remove();

        if (!field.value.trim()) {
          if (!firstInvalid) firstInvalid = field;

          const error = document.createElement('div');
          error.className = 'text-danger small mt-1';
          error.id = errorId;
          error.textContent = 'Este campo es obligatorio.';
          container.appendChild(error);
        }
      });

      if (firstInvalid) {
        firstInvalid.focus();
      } else {
        form.submit();
      }
    }
  });

  // 🔹 Validación visual al enviar el formulario
  form.addEventListener('submit', function (e) {
    const requiredFields = form.querySelectorAll('[required]');
    let firstInvalid = null;
    let hasErrors = false;

    // Limpiar errores previos
    form.querySelectorAll('.text-danger.small.mt-1').forEach(el => el.remove());

    requiredFields.forEach(field => {
      const container = field.closest('.mb-3') || field.parentElement;
      const errorId = 'error-' + field.id;

      if (!field.value.trim()) {
        hasErrors = true;
        if (!firstInvalid) firstInvalid = field;

        const error = document.createElement('div');
        error.className = 'text-danger small mt-1';
        error.id = errorId;
        error.textContent = 'Este campo es obligatorio.';
        container.appendChild(error);
      }
    });

    if (hasErrors) {
      e.preventDefault();
      if (firstInvalid) firstInvalid.focus();
    }
  });
});
</script>
@endpush
@push('styles')
  <link href="{{ asset('css/agregarSerie.css') }}" rel="stylesheet">
  <style>
    /* Oculta el input de búsqueda solo en el select2 de tipo de talle */
    #tipoTalle + .select2 .select2-search__field {
      display: none !important;
    }
  </style>
@endpush

