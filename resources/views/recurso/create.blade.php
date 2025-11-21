@extends('layouts.app')

@section('title', 'Agregar recurso')

@section('content')
<div class="container py-4">
      <div class="d-flex align-items-center justify-content-start mb-4 gap-3 flex-wrap">
        <a href="{{ route('inventario.index') }}" class="btn btn-volver d-flex align-items-center">
          <img src="{{ asset('images/volver1.svg') }}" alt="Volver" class="icono-volver me-2">
          Volver
        </a>

        <div class="d-flex align-items-center">
          <img src="{{ asset('images/herradd.svg') }}" alt="Herramienta" style="width: 40px; height: 40px;" class="me-2">
          <h4 class="fw-bold mb-0">Agregar recurso</h4>
        </div>
      </div>


      <form id="recursoForm" method="POST" action="{{ route('recursos.store') }}" novalidate>
        @csrf

        <!-- Categoría -->
        <div class="mb-3">
          <label for="categoria" class="form-label">Categoría</label>
          <select id="categoria" name="categoria" class="form-select" required>
            <option value="">Seleccione una categoría</option>
            @foreach($categorias as $categoria)
              <option value="{{ $categoria->id }}" {{ old('categoria') == $categoria->id ? 'selected' : '' }}>
                {{ $categoria->nombre_categoria }}
              </option>
            @endforeach
          </select>
        </div>

        <!-- Subcategoría -->
        <div class="mb-3">
          <label for="id_subcategoria" class="form-label">Subcategoría</label>
          <select id="id_subcategoria" name="id_subcategoria" class="form-select" required disabled>
            <option value="">Seleccione una subcategoría</option>
          </select>
        </div>

        <!-- Nueva Subcategoría -->
        <div class="mt-2 mb-3">
          <label for="nuevaSubcategoria" class="form-label">
            ¿No encontrás la subcategoría? Agregá una nueva
          </label>
          <div class="input-group">
            <input type="text"
                  id="nuevaSubcategoria"
                  name="nueva_subcategoria"
                  class="form-control"
                  placeholder="Nueva subcategoría"
                  value="{{ old('nueva_subcategoria') }}">
            <button type="button"
                    class="btn btn-agregar-subcategoria"
                    id="agregarSubcategoria">
              Agregar
            </button>
          </div>
        </div>

        <!-- Nombre -->
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre</label>
          <input type="text"
                id="nombre"
                name="nombre"
                class="form-control @error('nombre') is-invalid @enderror"
                maxlength="60" placeholder="Ingrese un nombre"
                required>
          @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <!-- Descripción -->
        <div class="mb-3">
          <label for="descripcion" class="form-label">Descripción</label>
          <textarea id="descripcion"
                    name="descripcion"
                    class="form-control @error('descripcion') is-invalid @enderror"
                    rows="3"
                    maxlength="250" placeholder="Ingrese una descripción (máx. 4 palabras)"></textarea>
          @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <!-- Costo unitario -->
        <div class="mb-3">
          <label for="costo_unitario" class="form-label">Costo Unitario</label>
          <input type="text"
            id="costo_unitario"
            name="costo_unitario"
            class="form-control"
            required>
          @error('costo_unitario')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>


        <div class="text-end">
          <button type="submit" class="btn btn-guardar-recurso w-100">
            Guardar Recurso
          </button>
        </div>

      </form>



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
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modalRecursoCreado" tabindex="-1" aria-labelledby="modalRecursoCreadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalRecursoCreadoLabel">Nuevo recurso agregado</h5>
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
@endpush

@push('styles')
  <link href="{{ asset('css/agregarRecurso.css') }}" rel="stylesheet">
@endpush

