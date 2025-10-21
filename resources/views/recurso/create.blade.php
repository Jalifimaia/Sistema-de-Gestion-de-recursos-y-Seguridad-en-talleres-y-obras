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
          <button type="submit" class="btn btn-primary">Guardar Recurso</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
  <script src="{{ asset('js/recurso.js') }}?v={{ time() }}"></script>
@endsection
