<div class="container mt-5">
    <h2>Agregar Recurso</h2>

    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="save" class="row g-3 mb-5">
        <!-- Categoría -->
        <select id="categoria" name="categoria_id" class="form-select">
    <option value="">Seleccione una categoría</option>
    @foreach($categorias as $categoria)
        <option value="{{ $categoria->id }}">{{ $categoria->nombre_categoria }}</option>
    @endforeach
</select>

<select id="subcategoria" name="subcategoria_id" class="form-select" disabled>
    <option value="">Seleccione una subcategoría</option>
</select>


        <!-- Serie -->
        <div class="col-md-2">
            <label class="form-label">Nro Serie</label>
            <input type="text" wire:model="serie" class="form-control" required>
        </div>

        <!-- Estado -->
        <div class="col-md-2">
            <label class="form-label">Estado</label>
            <select wire:model="id_estado" class="form-select">
                <option value="">Seleccione estado</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
        </div>

        <!-- Botón -->
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Agregar</button>
        </div>
    </form>
    <script src="{{ asset('js/recurso.js') }}"></script>

</div>
