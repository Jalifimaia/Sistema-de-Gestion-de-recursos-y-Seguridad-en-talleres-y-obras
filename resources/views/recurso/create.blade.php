@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <form id="recursoForm" class="row g-3 mb-5"
          method="POST"
          action="{{ isset($recurso) ? route('recursos.update', $recurso->id) : route('recursos.store') }}">
        @csrf
        @if(isset($recurso))
            @method('PUT')
        @endif

        <!-- Categoría -->
        <div class="col-md-6">
            <label for="categoria" class="form-label">Categoría</label>
            <select id="categoria" name="categoria_id" class="form-select">
                <option value="">Seleccione una categoría</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}"
                        {{ old('categoria_id', isset($recurso) && isset($recurso->id_subcategoria) ? (\App\Models\Subcategoria::find($recurso->id_subcategoria)->categoria_id ?? '') : '') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre_categoria }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Subcategoría -->
        <div class="col-md-6">
            <label for="subcategoria" class="form-label">Subcategoría</label>
            <select id="subcategoria" name="subcategoria_id" class="form-select" {{ isset($recurso) ? '' : 'disabled' }}>
                <option value="">Seleccione una subcategoría</option>
                @if(isset($subcategorias))
                    @foreach($subcategorias as $subcategoria)
                        <option value="{{ $subcategoria->id }}"
                            {{ old('subcategoria_id', $recurso->id_subcategoria ?? '') == $subcategoria->id ? 'selected' : '' }}>
                            {{ $subcategoria->nombre }}
                        </option>
                    @endforeach
                @endif
            </select>

            <div class="col-md-6 mt-3">
                <label for="nuevaSubcategoria" class="form-label">¿Falta una subcategoría?</label>
                <div class="input-group">
                    <input type="text" id="nuevaSubcategoria" class="form-control" placeholder="Nueva subcategoría">
                    <button type="button" id="agregarSubcategoria" class="btn btn-outline-secondary">Agregar</button>
                </div>
            </div>
        </div>

        <!-- Nombre -->
        <div class="col-md-6">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-control"
                   value="{{ old('nombre', $recurso->nombre ?? '') }}" required>
        </div>

        <!-- Descripción -->
        <div class="col-12">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="3">{{ old('descripcion', $recurso->descripcion ?? '') }}</textarea>
        </div>

        <!-- Costo unitario -->
        <div class="col-md-6">
            <label for="costo_unitario" class="form-label">Costo unitario</label>
            <input type="number" id="costo_unitario" name="costo_unitario" class="form-control" min="0" step="0.01"
                   value="{{ old('costo_unitario', $recurso->costo_unitario ?? '') }}">
        </div>

        <!-- Botón -->
        <div class="col-12">
            <button type="submit" class="btn btn-primary w-100">
                {{ isset($recurso) ? 'Guardar cambios' : 'Agregar' }}
            </button>
        </div>
    </form>

    <div id="mensaje" class="mt-3"></div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/recurso.js') }}"></script>
@endsection
