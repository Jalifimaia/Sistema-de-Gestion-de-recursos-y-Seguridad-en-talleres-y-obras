@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <form id="recursoForm" class="row g-3 mb-5">
            <!-- Categoría -->
            <div class="col-md-6">
                <label for="categoria" class="form-label">Categoría</label>
                <select id="categoria" name="categoria_id" class="form-select">
                    <option value="">Seleccione una categoría</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre_categoria }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Subcategoría -->
            <div class="col-md-6">
                
                <label for="subcategoria" class="form-label">Subcategoría</label>
                <select id="subcategoria" name="subcategoria_id" class="form-select" disabled>
                    <option value="">Seleccione una subcategoría</option>
                </select>

                <div class="col-md-6">
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
                <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>

            <!-- Serie -->
            <div class="col-md-6">
                <label for="serie" class="form-label">Serie</label>
                <input type="text" id="serie" name="serie" class="form-control" required>
            </div>

            <!-- Descripción -->
            <div class="col-12">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3"></textarea>
            </div>

            <!-- Costo unitario -->
            <div class="col-md-6">
                <label for="costo_unitario" class="form-label">Costo unitario</label>
                <input type="number" id="costo_unitario" name="costo_unitario" class="form-control" min="0" step="0.01">
            </div>

            <!-- Estado -->
            <div class="col-md-6">
                <label for="estado" class="form-label">Estado</label>
                <select id="estado" name="id_estado" class="form-select">
                    <option value="">Seleccione estado</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Botón -->
            <div class="col-12">
                <button type="submit" class="btn btn-primary w-100">Agregar</button>
            </div>
        </form>

        <div id="mensaje" class="mt-3"></div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/recurso.js') }}"></script>
@endsection
