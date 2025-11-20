@extends('layouts.app')

@section('title', 'Editar Recurso')

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


    <div class="alert alert-warning d-flex align-items-start gap-2">
      <img src="{{ asset('images/precaucion.svg') }}" alt="Precaución" class="icono-precaucion mt-1">
      <div>
        <strong>Importante:</strong> La categoría y subcategoría no pueden modificarse una vez creado el recurso.
        <br>Si necesitas cambiar la categoría (por ejemplo, de EPP a Herramienta), debes eliminar el recurso y volver a registrarlo.
      </div>
    </div>


    <form id="recursoForm" class="row g-3 mb-3" method="POST" action="{{ route('recursos.update', $recurso->id) }}">
        @csrf
        @method('PUT')

        <!-- Categoría (bloqueada) -->
        <div class="col-md-6">
            <label for="categoria" class="form-label">Categoría</label>
            <select id="categoria" name="categoria_id" class="form-select" disabled>
                @php
                    $categoriaId = \App\Models\Subcategoria::find($recurso->id_subcategoria)->categoria_id ?? '';
                @endphp
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ $categoriaId == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre_categoria }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="categoria_id" value="{{ $categoriaId }}">
        </div>

        <!-- Subcategoría (bloqueada) -->
        <div class="col-md-6">
            <label for="subcategoria" class="form-label">Subcategoría</label>
            <select id="subcategoria" name="subcategoria_id" class="form-select" disabled>
                @foreach($subcategorias as $subcategoria)
                    <option value="{{ $subcategoria->id }}" {{ $recurso->id_subcategoria == $subcategoria->id ? 'selected' : '' }}>
                        {{ $subcategoria->nombre }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="id_subcategoria" value="{{ $recurso->id_subcategoria }}">
        </div>

        <!-- Nombre -->
        <div class="col-md-6">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" id="nombre" name="nombre"
              class="form-control"
              maxlength="60"
              value="{{ old('nombre', $recurso->nombre) }}" required>
        </div>

        <!-- Descripción -->
        <div class="col-12">
          <label for="descripcion" class="form-label">Descripción</label>
          <textarea id="descripcion"
                    name="descripcion"
                    class="form-control @error('descripcion') is-invalid @enderror"
                    placeholder="Descripción..."
                    rows="3"
                    maxlength="250">{{ old('descripcion', $recurso->descripcion) }}</textarea>
          @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>


        <!-- Costo unitario -->
        <div class="col-md-6">
            <label for="costo_unitario" class="form-label">Costo unitario</label>
            <input type="number" id="costo_unitario" name="costo_unitario" class="form-control" placeholder="Costo unitario" min="0" step="0.01" value="{{ old('costo_unitario', $recurso->costo_unitario) }}">
        </div>

        <!-- Guardar cambios -->
        <div class="col-12">
            <button type="submit" class="btn btn-guardar w-100">Guardar cambios</button>
        </div>
    </form>

    <div class="d-flex justify-content-start gap-3 flex-wrap botones-inferiores">
      <a href="{{ route('recursos.create') }}" class="btn btn-nuevo">
        + Registrar nuevo recurso
      </a>

      <form id="deleteRecursoForm" action="{{ route('recursos.destroy', $recurso->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-eliminar d-flex align-items-center gap-2" id="btnOpenEliminar">
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
  // Eliminar: abrir modal al hacer click en el botón
  const btnOpenEliminar = document.getElementById('btnOpenEliminar');
  const modalConfirmEl = document.getElementById('modalConfirmDelete');
  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
  const deleteForm = document.getElementById('deleteRecursoForm');

  if (btnOpenEliminar && modalConfirmEl && confirmDeleteBtn && deleteForm) {
    const bsModal = new bootstrap.Modal(modalConfirmEl);
    btnOpenEliminar.addEventListener('click', () => bsModal.show());

    confirmDeleteBtn.addEventListener('click', () => {
      // enviar el form de eliminación
      deleteForm.submit();
    });
  }

  // Mostrar modal de guardado si existe
  const modalGuardado = document.getElementById('modalGuardadoExitoso');
  if (modalGuardado && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    new bootstrap.Modal(modalGuardado).show();
  }
});
</script>
@endpush

@push('styles')
  <link href="{{ asset('css/editarRecurso.css') }}" rel="stylesheet">
@endpush

