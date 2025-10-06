<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="id_recurso" class="form-label">{{ __('Id Recurso') }}</label>
            <input type="text" name="id_recurso" class="form-control @error('id_recurso') is-invalid @enderror" value="{{ old('id_recurso', $incidente?->id_recurso) }}" id="id_recurso" placeholder="Id Recurso">
            {!! $errors->first('id_recurso', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_supervisor" class="form-label">{{ __('Id Supervisor') }}</label>
            <input type="text" name="id_supervisor" class="form-control @error('id_supervisor') is-invalid @enderror" value="{{ old('id_supervisor', $incidente?->id_supervisor) }}" id="id_supervisor" placeholder="Id Supervisor">
            {!! $errors->first('id_supervisor', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_incidente_detalle" class="form-label">{{ __('Id Incidente Detalle') }}</label>
            <input type="text" name="id_incidente_detalle" class="form-control @error('id_incidente_detalle') is-invalid @enderror" value="{{ old('id_incidente_detalle', $incidente?->id_incidente_detalle) }}" id="id_incidente_detalle" placeholder="Id Incidente Detalle">
            {!! $errors->first('id_incidente_detalle', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_usuario_creacion" class="form-label">{{ __('Id Usuario Creacion') }}</label>
            <input type="text" name="id_usuario_creacion" class="form-control @error('id_usuario_creacion') is-invalid @enderror" value="{{ old('id_usuario_creacion', $incidente?->id_usuario_creacion) }}" id="id_usuario_creacion" placeholder="Id Usuario Creacion">
            {!! $errors->first('id_usuario_creacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_usuario_modificacion" class="form-label">{{ __('Id Usuario Modificacion') }}</label>
            <input type="text" name="id_usuario_modificacion" class="form-control @error('id_usuario_modificacion') is-invalid @enderror" value="{{ old('id_usuario_modificacion', $incidente?->id_usuario_modificacion) }}" id="id_usuario_modificacion" placeholder="Id Usuario Modificacion">
            {!! $errors->first('id_usuario_modificacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="descripcion" class="form-label">{{ __('Descripcion') }}</label>
            <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $incidente?->descripcion) }}" id="descripcion" placeholder="Descripcion">
            {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_incidente" class="form-label">{{ __('Fecha Incidente') }}</label>
            <input type="text" name="fecha_incidente" class="form-control @error('fecha_incidente') is-invalid @enderror" value="{{ old('fecha_incidente', $incidente?->fecha_incidente) }}" id="fecha_incidente" placeholder="Fecha Incidente">
            {!! $errors->first('fecha_incidente', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_creacion" class="form-label">{{ __('Fecha Creacion') }}</label>
            <input type="text" name="fecha_creacion" class="form-control @error('fecha_creacion') is-invalid @enderror" value="{{ old('fecha_creacion', $incidente?->fecha_creacion) }}" id="fecha_creacion" placeholder="Fecha Creacion">
            {!! $errors->first('fecha_creacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_modificacion" class="form-label">{{ __('Fecha Modificacion') }}</label>
            <input type="text" name="fecha_modificacion" class="form-control @error('fecha_modificacion') is-invalid @enderror" value="{{ old('fecha_modificacion', $incidente?->fecha_modificacion) }}" id="fecha_modificacion" placeholder="Fecha Modificacion">
            {!! $errors->first('fecha_modificacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_cierre_incidente" class="form-label">{{ __('Fecha Cierre Incidente') }}</label>
            <input type="text" name="fecha_cierre_incidente" class="form-control @error('fecha_cierre_incidente') is-invalid @enderror" value="{{ old('fecha_cierre_incidente', $incidente?->fecha_cierre_incidente) }}" id="fecha_cierre_incidente" placeholder="Fecha Cierre Incidente">
            {!! $errors->first('fecha_cierre_incidente', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="resolucion" class="form-label">{{ __('Resolucion') }}</label>
            <input type="text" name="resolucion" class="form-control @error('resolucion') is-invalid @enderror" value="{{ old('resolucion', $incidente?->resolucion) }}" id="resolucion" placeholder="Resolucion">
            {!! $errors->first('resolucion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>