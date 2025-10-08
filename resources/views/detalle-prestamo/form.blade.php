<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="id_prestamo" class="form-label">{{ __('Id Prestamo') }}</label>
            <input type="text" name="id_prestamo" class="form-control @error('id_prestamo') is-invalid @enderror" value="{{ old('id_prestamo', $detallePrestamo?->id_prestamo) }}" id="id_prestamo" placeholder="Id Prestamo">
            {!! $errors->first('id_prestamo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_serie" class="form-label">{{ __('Id Serie') }}</label>
            <input type="text" name="id_serie" class="form-control @error('id_serie') is-invalid @enderror" value="{{ old('id_serie', $detallePrestamo?->id_serie) }}" id="id_serie" placeholder="Id Serie">
            {!! $errors->first('id_serie', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_recurso" class="form-label">{{ __('Id Recurso') }}</label>
            <input type="text" name="id_recurso" class="form-control @error('id_recurso') is-invalid @enderror" value="{{ old('id_recurso', $detallePrestamo?->id_recurso) }}" id="id_recurso" placeholder="Id Recurso">
            {!! $errors->first('id_recurso', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <div class="form-group mb-2 mb20">
            <label for="id_estado_prestamo" class="form-label">{{ __('Estado del Préstamo') }}</label>
            <select name="id_estado_prestamo" id="id_estado_prestamo" class="form-control @error('id_estado_prestamo') is-invalid @enderror">
                <option value="">Seleccione estado</option>
                @foreach(App\Models\EstadoPrestamo::all() as $estado)
                    <option value="{{ $estado->id }}" {{ old('id_estado_prestamo', $detallePrestamo?->id_estado_prestamo) == $estado->id ? 'selected' : '' }}>{{ $estado->nombre }}</option>
                @endforeach
            </select>
            {!! $errors->first('id_estado_prestamo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>