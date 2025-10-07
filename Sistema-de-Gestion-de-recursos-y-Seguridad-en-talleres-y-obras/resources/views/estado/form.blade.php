<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="nombre_estado" class="form-label">{{ __('Nombre Estado') }}</label>
            <input type="text" name="nombre_estado" class="form-control @error('nombre_estado') is-invalid @enderror" value="{{ old('nombre_estado', $estado?->nombre_estado) }}" id="nombre_estado" placeholder="Nombre Estado">
            {!! $errors->first('nombre_estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="descripcion_estado" class="form-label">{{ __('Descripcion Estado') }}</label>
            <input type="text" name="descripcion_estado" class="form-control @error('descripcion_estado') is-invalid @enderror" value="{{ old('descripcion_estado', $estado?->descripcion_estado) }}" id="descripcion_estado" placeholder="Descripcion Estado">
            {!! $errors->first('descripcion_estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>