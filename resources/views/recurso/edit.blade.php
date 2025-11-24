@extends('layouts.app')

@section('title', 'Editar recurso')

@section('content')
<div class="container mt-4">
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
      <a href="{{ route('inventario.index') }}" class="btn btn-volver d-flex align-items-center">
        <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
        Volver
      </a>

      <div class="d-flex align-items-center">
        <img src="{{ asset('images/lapiz.svg') }}" alt="Editar" style="width: 36px; height: 36px;" class="me-2">
        <h4 class="fw-bold mb-0">Editar recurso</h4>
      </div>
    </div>

    <div id="mensaje"></div>

    <form id="recursoForm" class="row g-3 mb-3" method="POST" action="{{ route('recursos.update', $recurso->id) }}">
        @csrf
        @method('PUT')

        <!-- Categoría (editable) -->
        <div class="col-md-6 mb-3">
            <label for="categoria" class="form-label">Categoría <span class="required-asterisk">*</span></label>
            <select id="categoria" name="categoria_id" class="form-select" required>
                <option value="">Seleccione una categoría</option>
                @php
                    $categoriaId = \App\Models\Subcategoria::find($recurso->id_subcategoria)->categoria_id ?? '';
                @endphp
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ $categoriaId == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre_categoria }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Subcategoría (editable) -->
        <div class="col-md-6 mb-3">
            <label for="id_subcategoria" class="form-label">Subcategoría <span class="required-asterisk">*</span></label>
            <select id="id_subcategoria" name="id_subcategoria" class="form-select" required>
                @foreach($subcategorias as $subcategoria)
                    <option value="{{ $subcategoria->id }}" {{ $recurso->id_subcategoria == $subcategoria->id ? 'selected' : '' }}>
                        {{ $subcategoria->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Nombre -->
        <div class="col-md-6 mb-3">
            <label for="nombre" class="form-label">Nombre <span class="required-asterisk">*</span></label>
            <input type="text" id="nombre" name="nombre"
              class="form-control"
              placeholder = "Ingrese un nombre"
              maxlength="60"
              value="{{ old('nombre', $recurso->nombre) }}" required>
        </div>

        <!-- Costo unitario -->
        <div class="col-md-6 mb-3">
            <label for="costo_unitario" class="form-label">Costo unitario <span class="required-asterisk">*</span></label>
            <input type="text"
              id="costo_unitario"
              name="costo_unitario"
              class="form-control"
              placeholder="Ingrese el Costo"
              value="{{ old('costo_unitario', number_format($recurso->costo_unitario, 0, ',', '.')) }}"
              required>
        </div>

        <!-- Descripción -->
        <div class="col-12 mb-3">
          <label for="descripcion" class="form-label">Descripción <span class="required-asterisk">*</span></label>
          <textarea id="descripcion"
                    name="descripcion"
                    class="form-control @error('descripcion') is-invalid @enderror"
                    placeholder="Ingrese una descripción (máximo 4 palabras)"
                    rows="3"
                    maxlength="250"
                    required>{{ old('descripcion', $recurso->descripcion) }}</textarea>
          <small id="contadorPalabras" class="text-muted">0/4 palabras</small>
          @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <!-- Guardar cambios -->
        <div class="col-12">
            <button type="submit" class="btn btn-guardar w-100">Guardar cambios</button>
        </div>
    </form>

    <div class="d-none d-flex justify-content-start gap-3 flex-wrap botones-inferiores">
      <a href="{{ route('recursos.create') }}" class="btn btn-nuevo">
        + Registrar nuevo recurso
      </a>

      <form id="deleteRecursoForm" action="{{ route('recursos.destroy', $recurso->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="button" class="d-none btn btn-eliminar d-flex align-items-center gap-2" id="btnOpenEliminar">
          <img src="{{ asset('images/delete.svg') }}" alt="Eliminar" style="width: 20px; height: 20px;">
          Eliminar recurso
        </button>
      </form>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-labelledby="modalConfirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalConfirmDeleteLabel">¿Seguro que querés eliminar este recurso?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Esta acción eliminará el recurso permanentemente. ¿Deseás continuar?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Sí, eliminar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Error Campos -->
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

@if(session('success'))
<!-- Modal Guardado con opción volver al inventario -->
<div class="modal fade" id="modalGuardadoExitoso" tabindex="-1" aria-labelledby="modalGuardadoExitosoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalGuardadoExitosoLabel">Cambios guardados</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        {{ session('success') }}
      </div>
      <div class="modal-footer">
        <a href="{{ route('inventario.index') }}" class="btn btn-outline-success">Volver al inventario</a>
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continuar editando</button>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('recursoForm');
  const categoriaSelect = document.getElementById('categoria');
  const subcategoriaSelect = document.getElementById('id_subcategoria');
  const descripcion = document.getElementById('descripcion');
  const contador = document.getElementById('contadorPalabras');
  const costoInput = document.getElementById('costo_unitario');

  // 🔹 Desactivar validación nativa del navegador
  form.setAttribute('novalidate', true);

  // 🔹 Bloquear subcategoría si no hay categoría seleccionada
  function actualizarEstadoSubcategoria() {
    if (!categoriaSelect.value) {
      subcategoriaSelect.disabled = true;
      subcategoriaSelect.innerHTML = '<option value="">Primero seleccione una categoría</option>';
    }
  }
  actualizarEstadoSubcategoria();

  // 🔹 Función para contar palabras
  function contarPalabras(texto) {
    const palabras = texto.trim().split(/\s+/).filter(p => p.length > 0);
    return palabras.length;
  }

  // 🔹 Actualizar contador de palabras
  function actualizarContador() {
    const cantidad = descripcion.value.trim() === '' ? 0 : contarPalabras(descripcion.value);
    contador.textContent = `${cantidad}/4 palabras`;
    contador.classList.toggle('text-danger', cantidad > 4);
    contador.classList.toggle('text-muted', cantidad <= 4);
  }

  // 🔹 Limitar a 4 palabras en descripción (permitiendo espacio después de la 4ta)
  descripcion.addEventListener('input', function() {
    const texto = this.value;
    const palabras = texto.trim().split(/\s+/).filter(p => p.length > 0);
    
    if (palabras.length > 4) {
      // Mantener solo las primeras 4 palabras + espacio si lo había
      const cuatroPalabras = palabras.slice(0, 4).join(' ');
      this.value = cuatroPalabras;
    }
    
    actualizarContador();
    limpiarErrorCampo(this);
  });

  // Bloquear espacio si ya hay 4 palabras completas
  descripcion.addEventListener('keydown', function(e) {
    if (e.key === ' ') {
      const palabras = this.value.trim().split(/\s+/).filter(p => p.length > 0);
      if (palabras.length >= 4) {
        e.preventDefault();
      }
    }
  });

  // Inicializar contador
  actualizarContador();

  // 🔹 Cargar subcategorías al cambiar categoría
  categoriaSelect.addEventListener('change', function () {
    const categoriaId = this.value;
    subcategoriaSelect.innerHTML = '<option>Cargando...</option>';
    subcategoriaSelect.disabled = true;

    if (!categoriaId) {
      subcategoriaSelect.innerHTML = '<option value="">Primero seleccione una categoría</option>';
      subcategoriaSelect.disabled = true;
      return;
    }

    fetch(`/inventario/ajax/subcategorias/${encodeURIComponent(categoriaId)}`)
      .then(res => {
        if (!res.ok) throw new Error(`Error ${res.status}`);
        return res.json();
      })
      .then(data => {
        let options = '<option value="">Seleccione una subcategoría</option>';
        data.forEach(sub => {
          options += `<option value="${sub.id}">${sub.nombre}</option>`;
        });
        subcategoriaSelect.innerHTML = options;
        subcategoriaSelect.disabled = false;
      })
      .catch(error => {
        subcategoriaSelect.innerHTML = '<option>Error al cargar</option>';
        subcategoriaSelect.disabled = false;
      });

    limpiarErrorCampo(this);
  });

  // 🔹 Función para limpiar error de un campo
  function limpiarErrorCampo(campo) {
    const container = campo.closest('.mb-3') || campo.closest('.col-md-6') || campo.closest('.col-12') || campo.parentElement;
    const errorExistente = container.querySelector('.text-danger.small.mt-1');
    if (errorExistente) {
      errorExistente.remove();
    }
  }

  // 🔹 Limpiar errores al interactuar
  [document.getElementById('nombre'), costoInput].forEach(el => {
    if (el) {
      el.addEventListener('input', function() {
        limpiarErrorCampo(this);
      });
    }
  });

  subcategoriaSelect.addEventListener('change', function() {
    limpiarErrorCampo(this);
  });

  // 🔹 Validación del formulario
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // Limpiar errores previos
    form.querySelectorAll('.text-danger.small.mt-1').forEach(el => el.remove());

    let firstInvalid = null;
    let hayErrores = false;

    // Validar campos requeridos
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
      const container = field.closest('.mb-3') || field.closest('.col-md-6') || field.closest('.col-12') || field.parentElement;

      if (!field.value.trim()) {
        const error = document.createElement('div');
        error.className = 'text-danger small mt-1';
        error.textContent = 'Este campo es obligatorio.';
        container.appendChild(error);
        if (!firstInvalid) firstInvalid = field;
        hayErrores = true;
      }
    });

    // Validar que haya subcategoría seleccionada (si está habilitado)
    if (!subcategoriaSelect.disabled && !subcategoriaSelect.value) {
      const container = subcategoriaSelect.closest('.mb-3') || subcategoriaSelect.closest('.col-md-6') || subcategoriaSelect.parentElement;
      if (!container.querySelector('.text-danger.small.mt-1')) {
        const error = document.createElement('div');
        error.className = 'text-danger small mt-1';
        error.textContent = 'Este campo es obligatorio.';
        container.appendChild(error);
        if (!firstInvalid) firstInvalid = subcategoriaSelect;
        hayErrores = true;
      }
    }

    // Validar descripción: máximo 4 palabras
    const palabras = contarPalabras(descripcion.value);
    if (palabras > 4) {
      const container = descripcion.closest('.col-12') || descripcion.parentElement;
      const error = document.createElement('div');
      error.className = 'text-danger small mt-1';
      error.textContent = '⚠️ La descripción debe tener máximo 4 palabras.';
      container.appendChild(error);
      if (!firstInvalid) firstInvalid = descripcion;
      hayErrores = true;
    }

    if (hayErrores) {
      firstInvalid.focus();
      const modalErrorEl = document.getElementById('modalErrorCampos');
      if (modalErrorEl && typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(modalErrorEl).show();
      }
      return;
    }

    // Limpiar formato del costo antes de enviar
    costoInput.value = costoInput.value.replace(/\./g, '').replace(',', '.');

    // Enviar formulario
    form.submit();
  });

  // 🔹 Formateo del costo unitario
  if (costoInput) {
    costoInput.addEventListener('input', () => {
      let value = costoInput.value.replace(/\D/g, '');
      if (value) {
        costoInput.value = new Intl.NumberFormat('es-AR').format(value);
      }
    });
  }

  // 🔹 Modal de eliminación
  const btnOpenEliminar = document.getElementById('btnOpenEliminar');
  const modalConfirmEl = document.getElementById('modalConfirmDelete');
  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
  const deleteForm = document.getElementById('deleteRecursoForm');

  if (btnOpenEliminar && modalConfirmEl && confirmDeleteBtn && deleteForm) {
    const bsModal = new bootstrap.Modal(modalConfirmEl);
    btnOpenEliminar.addEventListener('click', () => bsModal.show());
    confirmDeleteBtn.addEventListener('click', () => deleteForm.submit());
  }

  // 🔹 Mostrar modal de guardado exitoso
  const modalGuardado = document.getElementById('modalGuardadoExitoso');
  if (modalGuardado && typeof bootstrap !== 'undefined') {
    new bootstrap.Modal(modalGuardado).show();
  }
});
</script>
@endpush

@push('styles')
  <link href="{{ asset('css/editarRecurso.css') }}" rel="stylesheet">

<style>
  .required-asterisk {
    margin-left: 4px;
    color: red; /*asterisco*/
    font-weight: 600;
  }
  </style>
@endpush

