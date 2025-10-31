@extends('layouts.app')

@section('template_title')
    Agregar Series a {{ $recurso->nombre }} [{{ $recurso->subcategoria->nombre ?? '' }}]
@endsection

@section('content')
<div class="container py-4">
    <h3 class="mb-4">
        Agregar Series para: {{ $recurso->nombre }} [{{ $recurso->subcategoria->nombre ?? '' }}]
    </h3>

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
            <input type="number" name="lote" id="lote" class="form-control" placeholder="Ingrese el N° de lote" min="1" required>
        </div>

        <div class="mb-3">
        <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label>
        <div class="input-group" onclick="this.querySelector('input').showPicker()">
            <input type="date" name="fecha_adquisicion" id="fecha_adquisicion" class="form-control" required>
        </div>
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
                <thead class="table-light">
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
            <button type="button" class="btn btn-outline-primary" onclick="agregarFila()">+ Agregar combinación</button>
        </div>

        <button type="submit" class="btn btn-success" id="btnGuardar" disabled>Guardar Series</button>
        <a href="{{ route('inventario') }}" class="btn btn-secondary ms-2">Volver</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
    window.colores = @json($colores->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre]));
    window.nombreRecurso = @json($recurso->nombre);
    window.descripcionRecurso = @json($recurso->descripcion);
    window.requiereTalle = @json($requiereTalle);
    window.tallesPorTipo = @json($talles); // 👈 ahora viene de la BD
</script>
<script src="{{ asset('js/serieRecurso.js') }}"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
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
