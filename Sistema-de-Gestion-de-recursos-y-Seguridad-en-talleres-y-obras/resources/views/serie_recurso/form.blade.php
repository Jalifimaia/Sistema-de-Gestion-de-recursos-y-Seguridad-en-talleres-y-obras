<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="id_recurso" class="form-label">{{ __('Id Recurso') }}</label>
            <input type="text" name="id_recurso" class="form-control @error('id_recurso') is-invalid @enderror" value="{{ old('id_recurso', $serieRecurso?->id_recurso) }}" id="id_recurso" placeholder="Id Recurso">
            {!! $errors->first('id_recurso', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_incidente_detalle" class="form-label">{{ __('Id Incidente Detalle') }}</label>
            <input type="text" name="id_incidente_detalle" class="form-control @error('id_incidente_detalle') is-invalid @enderror" value="{{ old('id_incidente_detalle', $serieRecurso?->id_incidente_detalle) }}" id="id_incidente_detalle" placeholder="Id Incidente Detalle">
            {!! $errors->first('id_incidente_detalle', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="nro_serie" class="form-label">{{ __('Nro Serie') }}</label>
            <input type="text" name="nro_serie" class="form-control @error('nro_serie') is-invalid @enderror" value="{{ old('nro_serie', $serieRecurso?->nro_serie) }}" id="nro_serie" placeholder="Nro Serie">
            {!! $errors->first('nro_serie', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="talle" class="form-label">{{ __('Talle') }}</label>
            <input type="text" name="talle" class="form-control @error('talle') is-invalid @enderror" value="{{ old('talle', $serieRecurso?->talle) }}" id="talle" placeholder="Talle">
            {!! $errors->first('talle', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_adquisicion" class="form-label">{{ __('Fecha Adquisicion') }}</label>
            <input type="text" name="fecha_adquisicion" class="form-control @error('fecha_adquisicion') is-invalid @enderror" value="{{ old('fecha_adquisicion', $serieRecurso?->fecha_adquisicion) }}" id="fecha_adquisicion" placeholder="Fecha Adquisicion">
            {!! $errors->first('fecha_adquisicion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_vencimiento" class="form-label">{{ __('Fecha Vencimiento') }}</label>
            <input type="text" name="fecha_vencimiento" class="form-control @error('fecha_vencimiento') is-invalid @enderror" value="{{ old('fecha_vencimiento', $serieRecurso?->fecha_vencimiento) }}" id="fecha_vencimiento" placeholder="Fecha Vencimiento">
            {!! $errors->first('fecha_vencimiento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>