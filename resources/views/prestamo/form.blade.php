<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="id_usuario" class="form-label">{{ __('Id Usuario') }}</label>
            <input type="text" name="id_usuario" class="form-control @error('id_usuario') is-invalid @enderror" value="{{ old('id_usuario', $prestamo?->id_usuario) }}" id="id_usuario" placeholder="Id Usuario">
            {!! $errors->first('id_usuario', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_usuario_creacion" class="form-label">{{ __('Id Usuario Creacion') }}</label>
            <input type="text" name="id_usuario_creacion" class="form-control @error('id_usuario_creacion') is-invalid @enderror" value="{{ old('id_usuario_creacion', $prestamo?->id_usuario_creacion) }}" id="id_usuario_creacion" placeholder="Id Usuario Creacion">
            {!! $errors->first('id_usuario_creacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="id_usuario_modificacion" class="form-label">{{ __('Id Usuario Modificacion') }}</label>
            <input type="text" name="id_usuario_modificacion" class="form-control @error('id_usuario_modificacion') is-invalid @enderror" value="{{ old('id_usuario_modificacion', $prestamo?->id_usuario_modificacion) }}" id="id_usuario_modificacion" placeholder="Id Usuario Modificacion">
            {!! $errors->first('id_usuario_modificacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_prestamo" class="form-label">{{ __('Fecha Prestamo') }}</label>
            <input type="text" name="fecha_prestamo" class="form-control @error('fecha_prestamo') is-invalid @enderror" value="{{ old('fecha_prestamo', $prestamo?->fecha_prestamo) }}" id="fecha_prestamo" placeholder="Fecha Prestamo">
            {!! $errors->first('fecha_prestamo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_devolucion" class="form-label">{{ __('Fecha Devolucion') }}</label>
            <input type="text" name="fecha_devolucion" class="form-control @error('fecha_devolucion') is-invalid @enderror" value="{{ old('fecha_devolucion', $prestamo?->fecha_devolucion) }}" id="fecha_devolucion" placeholder="Fecha Devolucion">
            {!! $errors->first('fecha_devolucion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="estado" class="form-label">{{ __('Estado') }}</label>
            <input type="text" name="estado" class="form-control @error('estado') is-invalid @enderror" value="{{ old('estado', $prestamo?->estado) }}" id="estado" placeholder="Estado">
            {!! $errors->first('estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_creacion" class="form-label">{{ __('Fecha Creacion') }}</label>
            <input type="text" name="fecha_creacion" class="form-control @error('fecha_creacion') is-invalid @enderror" value="{{ old('fecha_creacion', $prestamo?->fecha_creacion) }}" id="fecha_creacion" placeholder="Fecha Creacion">
            {!! $errors->first('fecha_creacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_modificacion" class="form-label">{{ __('Fecha Modificacion') }}</label>
            <input type="text" name="fecha_modificacion" class="form-control @error('fecha_modificacion') is-invalid @enderror" value="{{ old('fecha_modificacion', $prestamo?->fecha_modificacion) }}" id="fecha_modificacion" placeholder="Fecha Modificacion">
            {!! $errors->first('fecha_modificacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>