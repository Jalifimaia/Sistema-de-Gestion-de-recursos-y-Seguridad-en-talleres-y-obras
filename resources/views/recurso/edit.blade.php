@extends('layouts.app')

@section('template_title')
    Editar Recurso
@endsection

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Editar recurso</h2>

    <!-- Advertencia -->
    <div class="alert alert-warning">
        <strong>Importante:</strong> La categoría y subcategoría no pueden modificarse una vez creado el recurso.
        <br>Si necesitas cambiar la categoría (por ejemplo, de EPP a Herramienta), debes eliminar el recurso y volver a registrarlo.
    </div>

    <form id="recursoForm" class="row g-3 mb-5"
          method="POST"
          action="{{ route('recursos.update', $recurso->id) }}">
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
                    <option value="{{ $categoria->id }}"
                        {{ $categoriaId == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre_categoria }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="categoria_id" value="{{ \App\Models\Subcategoria::find($recurso->id_subcategoria)->categoria_id ?? '' }}">
        </div>

        <!-- Subcategoría (bloqueada) -->
        <div class="col-md-6">
            <label for="subcategoria" class="form-label">Subcategoría</label>
            <select id="subcategoria" name="subcategoria_id" class="form-select" disabled>
                @foreach($subcategorias as $subcategoria)
                    <option value="{{ $subcategoria->id }}"
                        {{ $recurso->id_subcategoria == $subcategoria->id ? 'selected' : '' }}>
                        {{ $subcategoria->nombre }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="id_subcategoria" value="{{ $recurso->id_subcategoria }}">
        </div>

        <!-- Nombre -->
        <div class="col-md-6">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-control"
                   value="{{ old('nombre', $recurso->nombre) }}" required>
        </div>

        <!-- Descripción -->
        <div class="col-12">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="3">{{ old('descripcion', $recurso->descripcion) }}</textarea>
        </div>

        <!-- Costo unitario -->
        <div class="col-md-6">
            <label for="costo_unitario" class="form-label">Costo unitario</label>
            <input type="number" id="costo_unitario" name="costo_unitario" class="form-control" min="0" step="0.01"
                   value="{{ old('costo_unitario', $recurso->costo_unitario) }}">
        </div>

        <!-- Botón -->
        <div class="col-12">
            <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
        </div>
    </form>

    <!-- Opcional: botón para eliminar y volver a registrar -->
    <div class="d-flex justify-content-between">
        <form action="{{ route('recursos.destroy', $recurso->id) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este recurso?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger">Eliminar recurso</button>
        </form>
        <a href="{{ route('recursos.create') }}" class="btn btn-outline-primary">Registrar nuevo recurso</a>
    </div>
</div>
@endsection
