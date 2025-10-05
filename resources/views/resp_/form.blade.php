<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="mb-3">
        <label for="id_categoria" class="form-label">Categoría</label>
        <select name="id_categoria" id="id_categoria" class="form-select" required>
            <option value="">Seleccionar categoría</option>
            @foreach ($categorias as $categoria)
            <option value="{{ $categoria->id }}" {{ old('id_categoria', $recurso->id_categoria) == $categoria->id ? 'selected' : '' }}>
                {{ $categoria->nombre_categoria }}
            </option>
            @endforeach
        </select>
        </div>

        <div class="mb-3">
        <label for="id_estado" class="form-label">Estado</label>
        <select name="id_estado" id="id_estado" class="form-select" required>
            <option value="">Seleccionar estado</option>
            @foreach ($estados as $estado)
            <option value="{{ $estado->id }}" {{ old('id_estado', $recurso->id_estado) == $estado->id ? 'selected' : '' }}>
                {{ $estado->nombre_estado }}
            </option>
            @endforeach
        </select>
        </div>

        <div class="form-group mb-2 mb20">
            <label for="nombre" class="form-label">{{ __('Nombre') }}</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $recurso?->nombre) }}" id="nombre" placeholder="Nombre">
            {!! $errors->first('nombre', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="descripcion" class="form-label">{{ __('Descripcion') }}</label>
            <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $recurso?->descripcion) }}" id="descripcion" placeholder="Descripcion">
            {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="costo_unitario" class="form-label">{{ __('Costo Unitario') }}</label>
            <input type="text" name="costo_unitario" class="form-control @error('costo_unitario') is-invalid @enderror" value="{{ old('costo_unitario', $recurso?->costo_unitario) }}" id="costo_unitario" placeholder="Costo Unitario">
            {!! $errors->first('costo_unitario', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
</div>