<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="id_incidente" class="form-label">{{ __('Id Incidente') }}</label>
            <input type="text" name="id_incidente" class="form-control @error('id_incidente') is-invalid @enderror" value="{{ old('id_incidente', $incidenteDetalle?->id_incidente) }}" id="id_incidente" placeholder="Id Incidente">
            {!! $errors->first('id_incidente', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_serie" class="form-label">{{ __('Id Serie') }}</label>
            <input type="text" name="id_serie" class="form-control @error('id_serie') is-invalid @enderror" value="{{ old('id_serie', $incidenteDetalle?->id_serie) }}" id="id_serie" placeholder="Id Serie">
            {!! $errors->first('id_serie', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="descripcion" class="form-label">{{ __('Descripcion') }}</label>
            <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $incidenteDetalle?->descripcion) }}" id="descripcion" placeholder="Descripcion">
            {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>