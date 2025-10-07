<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="nombre_categoria" class="form-label">{{ __('Nombre Categoria') }}</label>
            <input type="text" name="nombre_categoria" class="form-control @error('nombre_categoria') is-invalid @enderror" value="{{ old('nombre_categoria', $categoria?->nombre_categoria) }}" id="nombre_categoria" placeholder="Nombre Categoria">
            {!! $errors->first('nombre_categoria', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>