@extends('layouts.app')

@section('title', 'Agregar Recurso')

@section('content')
<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title fw-bold mb-1">Agregar Nuevo Recurso</h5>
      <p class="text-muted small mb-4">Complete los campos para registrar un nuevo recurso.</p>

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- Contenedor para mensajes JS -->
      <div id="mensaje"></div>

      <form id="recursoForm" method="POST" action="{{ route('recursos.store') }}">
        @csrf

        <!-- Categoría -->
        <div class="mb-3">
          <label for="categoria" class="form-label">Categoría</label>
          <select id="categoria" class="form-select" required>
            <option value="">Seleccione una categoría</option>
            @foreach($categorias as $categoria)
              <option value="{{ $categoria->id }}">{{ $categoria->nombre_categoria }}</option>
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
        <div class="input-group mt-2">
          <input type="text" id="nuevaSubcategoria" class="form-control" placeholder="Nueva subcategoría">
          <button type="button" class="btn btn-outline-success" id="agregarSubcategoria">Agregar</button>
        </div>

        <!-- Nombre -->
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre</label>
          <input type="text" id="nombre" name="nombre" class="form-control" required>
        </div>

        <!-- Descripción -->
        <div class="mb-3">
          <label for="descripcion" class="form-label">Descripción</label>
          <textarea id="descripcion" name="descripcion" class="form-control" rows="3"></textarea>
        </div>

        <!-- Costo unitario -->
        <div class="mb-3">
          <label for="costo_unitario" class="form-label">Costo Unitario</label>
          <input type="number" id="costo_unitario" name="costo_unitario" class="form-control" step="0.01" min="0">
        </div>

        <div class="text-end">
          <a href="{{ route('inventario') }}" class="btn btn-outline-secondary">⬅️ Volver</a>
          <button type="submit" class="btn btn-primary">Guardar Recurso</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal que aparece al crear -->
@if(session('success'))
<div class="modal fade" id="modalRecursoCreado" tabindex="-1" aria-labelledby="modalRecursoCreadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalRecursoCreadoLabel">Nuevo recurso agregado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        {{ session('success') }}
      </div>
      <div class="modal-footer">
        <a href="{{ route('inventario') }}" class="btn btn-outline-success">Volver al inventario</a>
        <a href="{{ route('recursos.create') }}" class="btn btn-success">Seguir agregando</a>
      </div>
    </div>
  </div>
</div>
@endif

@endsection

@section('scripts')
  <script src="{{ asset('js/recurso.js') }}?v={{ time() }}"></script>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Poblado dinámico de subcategorías al cambiar categoría
  const categoriaSelect = document.getElementById('categoria');
  const subcategoriaSelect = document.getElementById('id_subcategoria');

  if (categoriaSelect) {
    categoriaSelect.addEventListener('change', function () {
      const categoriaId = this.value;
      if (!categoriaId) {
        subcategoriaSelect.innerHTML = '<option value="">Seleccione una subcategoría</option>';
        subcategoriaSelect.disabled = true;
        return;
      }
      subcategoriaSelect.innerHTML = '<option value="">Cargando...</option>';
      subcategoriaSelect.disabled = true;

      fetch(`/api/subcategorias/${encodeURIComponent(categoriaId)}`)
        .then(res => {
          if (!res.ok) throw new Error('HTTP ' + res.status);
          return res.json();
        })
        .then(data => {
          if (!Array.isArray(data) || data.length === 0) {
            subcategoriaSelect.innerHTML = '<option value="">Sin subcategorías</option>';
            subcategoriaSelect.disabled = true;
            return;
          }
          let html = '<option value="">Seleccione una subcategoría</option>';
          data.forEach(s => {
            html += `<option value="${s.id}">${s.nombre}</option>`;
          });
          subcategoriaSelect.innerHTML = html;
          subcategoriaSelect.disabled = false;
        })
        .catch(err => {
          console.error('Error al cargar subcategorías:', err);
          subcategoriaSelect.innerHTML = '<option value="">Error al cargar</option>';
          subcategoriaSelect.disabled = true;
        });
    });
  }

  // Mostrar modal si existe en DOM
  const modalEl = document.getElementById('modalRecursoCreado');
  if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    new bootstrap.Modal(modalEl).show();
  }
});
</script>
@endpush
