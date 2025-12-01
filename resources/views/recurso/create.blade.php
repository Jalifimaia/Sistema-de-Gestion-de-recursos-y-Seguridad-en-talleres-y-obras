@extends('layouts.app')

@section('title', 'Registrar recurso')

@section('content')
<div class="container py-4">
      <div class="d-flex align-items-center justify-content-start mb-4 gap-3 flex-wrap">
        <a href="{{ route('inventario.index') }}" class="btn btn-volver d-flex align-items-center">
          <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
          Volver
        </a>

        <div class="d-flex align-items-center">
          <h4 class="fw-bold mb-0">Registrar recurso</h4>
        </div>
      </div>

      <div id="mensaje"></div>

      <form id="recursoForm" method="POST" action="{{ route('recursos.store') }}" novalidate>
        @csrf

        <div class="row g-3">
          <div class="col-md-6 mb-3">
            <label for="categoria" class="form-label">Categoría <span class="required-asterisk">*</span></label>
            <select id="categoria" name="categoria" class="form-select" required>
              <option value="">Seleccione una categoría</option>
              @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" {{ old('categoria') == $categoria->id ? 'selected' : '' }}>
                  {{ $categoria->nombre_categoria }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label for="id_subcategoria" class="form-label">Subcategoría <span class="required-asterisk">*</span></label>
            <div class="input-group">
                <select id="id_subcategoria" name="id_subcategoria" class="form-select form-sub" required disabled>
                    <option value="">Primero seleccione una categoría</option>
                </select>
                <button type="button" 
                        class="btn btn-mas" 
                        id="btnAbrirModalSubcategoria"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar Subcategoría">
                    <i class="bi bi-plus-circle"></i>
                </button>
            </div>
              <small class="text-muted d-block mt-1">¿Necesita agregar una nueva subcategoría? Use el botón <span class="fs-5 fw-bold">+</span></small>          </div>
          
          {{-- EL BLOQUE ANTERIOR QUE ESTABA AQUÍ SE ELIMINÓ --}}
          {{--
          <div class="col-12 mb-3">
            <label class="form-label text-muted small">¿Necesita agregar una nueva subcategoría?</label>
            <button type="button" 
                    class="btn btn-outline-primary btn-sm w-100" 
                    id="btnAbrirModalSubcategoria">
              <i class="bi bi-plus-circle me-2"></i>Agregar nueva subcategoría
            </button>
          </div>
          --}}

          <div class="col-md-6 mb-3">
            <label for="nombre" class="form-label">Nombre <span class="required-asterisk">*</span></label>
            <input type="text"
                  id="nombre"
                  name="nombre"
                  class="form-control @error('nombre') is-invalid @enderror"
                  maxlength="60"
                  placeholder="Ingrese un nombre"
                  required>
            @error('nombre')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label for="costo_unitario" class="form-label">Costo unitario <span class="required-asterisk">*</span></label>
            <input type="text"
              id="costo_unitario"
              name="costo_unitario"
              class="form-control"
              placeholder="Ingrese el costo"
              inputmode="numeric"
              required>
            @error('costo_unitario')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12 mb-1">
            <label for="descripcion" class="form-label">Descripción <span class="required-asterisk">*</span></label>
            <textarea id="descripcion"
          name="descripcion"
          class="form-control @error('descripcion') is-invalid @enderror"
          rows="3"
          maxlength="250"
          placeholder="Ingrese una descripción (máximo 4 palabras)"
          required></textarea>

            <small id="contadorPalabras" class="text-muted">0/4 palabras</small>
            @error('descripcion')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          
          <div class="d-flex justify-content-end mt-1">
            <button type="submit" class="btn btn-guardar-recurso px-4">
              Guardar recurso
            </button>
          </div>
        </div>
      </form>
</div>

<div class="modal fade" id="modalAgregarSubcategoria" tabindex="-1" aria-labelledby="modalAgregarSubcategoriaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header header-subcat">
        <h5 class="modal-title" id="modalAgregarSubcategoriaLabel">Agregar nueva subcategoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="mensajeModalSubcategoria"></div>
        
        <div class="mb-3">
          <label for="categoriaModal" class="form-label">Categoría <span class="required-asterisk">*</span></label>
          <select id="categoriaModal" class="form-select">
            <option value="">Seleccione una categoría</option>
            @foreach($categorias as $categoria)
              <option value="{{ $categoria->id }}">{{ $categoria->nombre_categoria }}</option>
            @endforeach
          </select>
          <small class="text-muted">Seleccione la categoría para la nueva subcategoría</small>
        </div>
        
        <div class="mb-3">
          <label for="nombreSubcategoriaModal" class="form-label">Nombre de la subcategoría <span class="required-asterisk">*</span></label>
          <input type="text" 
                 class="form-control" 
                 id="nombreSubcategoriaModal" 
                 placeholder="Ingrese el nombre"
                 maxlength="100">
          <small class="text-muted">Ingrese al menos 2 caracteres</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-guardar-subcat" id="btnGuardarSubcategoria">
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerGuardar"></span>
          Guardar subcategoría
        </button>
      </div>
    </div>
  </div>
</div>

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

<div class="modal fade" id="modalRecursoCreado" tabindex="-1" aria-labelledby="modalRecursoCreadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalRecursoCreadoLabel">Recurso agregado correctamente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalRecursoBody">
        El recurso fue creado correctamente.
      </div>
      <div class="modal-footer">
        <a href="{{ route('inventario.index') }}" class="btn btn-outline-success">Volver al inventario</a>
        <a href="{{ route('recursos.create') }}" class="btn btn-success">Seguir agregando</a>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
  <script src="{{ asset('js/recurso.js') }}?v={{ time() }}"></script>
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const categoriaSelect = document.getElementById('categoria');
    const subcategoriaSelect = document.getElementById('id_subcategoria'); // DESCOMENTADO
    const btnAbrirModal = document.getElementById('btnAbrirModalSubcategoria');
    const modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregarSubcategoria'));
    const categoriaModal = document.getElementById('categoriaModal');
    const nombreSubcategoriaModal = document.getElementById('nombreSubcategoriaModal');
    const btnGuardarSubcategoria = document.getElementById('btnGuardarSubcategoria');
    const mensajeModalSubcategoria = document.getElementById('mensajeModalSubcategoria');
    const spinnerGuardar = document.getElementById('spinnerGuardar');
    
    // Abrir modal
    btnAbrirModal.addEventListener('click', function() {
      mensajeModalSubcategoria.innerHTML = '';
      nombreSubcategoriaModal.value = '';
      
      // Si hay una categoría seleccionada en el formulario, pre-seleccionarla en el modal
      if (categoriaSelect.value) {
        categoriaModal.value = categoriaSelect.value;
      } else {
        categoriaModal.value = '';
      }
      
      modalAgregar.show();
    });
    
    // Limpiar mensajes al escribir o cambiar categoría
    nombreSubcategoriaModal.addEventListener('input', function() {
      mensajeModalSubcategoria.innerHTML = '';
    });
    
    categoriaModal.addEventListener('change', function() {
      mensajeModalSubcategoria.innerHTML = '';
    });
    
    // Guardar subcategoría
    btnGuardarSubcategoria.addEventListener('click', async function() {
      const nombre = nombreSubcategoriaModal.value.trim();
      const categoriaId = categoriaModal.value;
      
      // Validar categoría
      if (!categoriaId) {
        mensajeModalSubcategoria.innerHTML = '<div class="alert alert-warning">Por favor, seleccione una categoría.</div>';
        return;
      }
      
      // Validar nombre
      if (!nombre) {
        mensajeModalSubcategoria.innerHTML = '<div class="alert alert-warning">Por favor, ingrese un nombre para la subcategoría.</div>';
        return;
      }
      
      if (nombre.length < 2) {
        mensajeModalSubcategoria.innerHTML = '<div class="alert alert-warning">El nombre debe tener al menos 2 caracteres.</div>';
        return;
      }
      
      // Mostrar spinner
      spinnerGuardar.classList.remove('d-none');
      btnGuardarSubcategoria.disabled = true;
      
      try {
        const response = await fetch('/subcategorias', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          },
          body: JSON.stringify({ 
            nombre: nombre, 
            categoria_id: categoriaId 
          })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
          // Error del servidor
          if (response.status === 409) {
            mensajeModalSubcategoria.innerHTML = '<div class="alert alert-danger">Esta subcategoría ya existe en esta categoría.</div>';
          } else if (response.status === 422) {
            const errores = Object.values(data.errors).flat().join('<br>');
            mensajeModalSubcategoria.innerHTML = `<div class="alert alert-danger">${errores}</div>`;
          } else {
            mensajeModalSubcategoria.innerHTML = `<div class="alert alert-danger">${data.error || 'Error al crear la subcategoría.'}</div>`;
          }
          return;
        }
        
        // Éxito - Mostrar mensaje
        mensajeModalSubcategoria.innerHTML = '<div class="alert alert-success">Subcategoría agregada correctamente.</div>';
        
        // Si la categoría del modal coincide con la del formulario, recargar el select
        if (categoriaId === categoriaSelect.value) {
          const valorActual = subcategoriaSelect.value; // Referencia al valor actual
          
          // Recargar las subcategorías
          const responseSubcategorias = await fetch(`/inventario/ajax/subcategorias/${encodeURIComponent(categoriaId)}`);
          const subcategorias = await responseSubcategorias.json();
          
          // Reconstruir el select
          let options = '<option value="">Seleccione una subcategoría</option>';
          subcategorias.forEach(sub => {
            options += `<option value="${sub.id}">${sub.nombre}</option>`;
          });
          subcategoriaSelect.innerHTML = options; // DESCOMENTADO
          subcategoriaSelect.disabled = false; // Asegurar que se desbloquee
          
          // Restaurar el valor anterior si existía, sino seleccionar la nueva
          if (valorActual) {
            subcategoriaSelect.value = valorActual;
          } else {
            subcategoriaSelect.value = data.id; // DESCOMENTADO
          }
        }
        
        // Cerrar el modal después de 1 segundo
       /* setTimeout(() => {
          modalAgregar.hide();
          nombreSubcategoriaModal.value = '';
          categoriaModal.value = '';
          mensajeModalSubcategoria.innerHTML = '';
        }, 1000);*/
        
      } catch (error) {
        mensajeModalSubcategoria.innerHTML = `<div class="alert alert-danger">Error de conexión: ${error.message}</div>`;
      } finally {
        // Ocultar spinner
        spinnerGuardar.classList.add('d-none');
        btnGuardarSubcategoria.disabled = false;
      }
    });
    
    // Permitir guardar con Enter
    nombreSubcategoriaModal.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        btnGuardarSubcategoria.click();
      }
    });
  });
  </script>
@endpush

@push('styles')
  <link href="{{ asset('css/agregarRecurso.css') }}" rel="stylesheet">

  <style>
  .required-asterisk {
    margin-left: 4px;
    color: red; /*asterisco*/
    font-weight: 600;
  }
  
  #btnAbrirModalSubcategoria:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  </style>
@endpush