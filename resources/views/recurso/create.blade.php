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

      <form method="POST" action="{{ route('recursos.store') }}">
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

        <!-- Nombre -->
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>

        <!-- Descripción -->
        <div class="mb-3">
          <label for="descripcion" class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" rows="3"></textarea>
        </div>

        <!-- Costo unitario -->
        <div class="mb-3">
          <label for="costo_unitario" class="form-label">Costo Unitario</label>
          <input type="number" name="costo_unitario" class="form-control" step="0.01" min="0">
        </div>

        <div class="text-end">
          <a href="{{ route('inventario') }}" class="btn btn-outline-secondary">
              ⬅️ Volver
          </a>


          <button type="submit" class="btn btn-primary">Guardar Recurso</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const categoriaSelect = document.getElementById('categoria');
    const subcategoriaSelect = document.getElementById('id_subcategoria');

    categoriaSelect.addEventListener('change', function () {
      const categoriaId = this.value;
      subcategoriaSelect.innerHTML = '<option>Cargando...</option>';
      subcategoriaSelect.disabled = true;

      if (!categoriaId) {
        subcategoriaSelect.innerHTML = '<option value="">Seleccione una subcategoría</option>';
        return;
      }

      fetch(`/api/subcategorias/${categoriaId}`)
        .then(res => res.json())
        .then(data => {
          let options = '<option value="">Seleccione una subcategoría</option>';
          data.forEach(sub => {
            options += `<option value="${sub.id}">${sub.nombre}</option>`;
          });
          subcategoriaSelect.innerHTML = options;
          subcategoriaSelect.disabled = false;
        })
        .catch(error => {
          console.error('Error al cargar subcategorías:', error);
          subcategoriaSelect.innerHTML = '<option>Error al cargar</option>';
        });
    });
  });
</script>
@endsection
