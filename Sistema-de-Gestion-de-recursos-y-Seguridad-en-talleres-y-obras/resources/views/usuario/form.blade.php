<div class="row padding-1 p-1">
    <div class="col-md-12">

        <div class="form-group mb-2 mb20">
            <label for="id_rol" class="form-label">{{ __('Rol') }}</label>
            <input type="text" name="id_rol" class="form-control @error('id_rol') is-invalid @enderror" value="{{ old('id_rol', $usuario?->id_rol) }}" id="id_rol" placeholder="Rol">
            {!! $errors->first('id_rol', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <div class="form-group mb-2 mb20">
            <label for="name" class="form-label">{{ __('Nombre') }}</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $usuario?->name) }}" id="name" placeholder="Nombre">
            {!! $errors->first('name', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <div class="form-group mb-2 mb20">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $usuario?->email) }}" id="email" placeholder="Email">
            {!! $errors->first('email', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <div class="form-group mb-2 mb20">
            <label for="password" class="form-label">{{ __('Contraseña') }}</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Contraseña">
            {!! $errors->first('password', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <div class="form-group mb-2 mb20">
            <label for="password_confirmation" class="form-label">{{ __('Confirmar Contraseña') }}</label>
            <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" placeholder="Confirmar Contraseña">
            {!! $errors->first('password_confirmation', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <div class="form-group mb-2 mb20">
            <label for="usuario_creacion" class="form-label">{{ __('Usuario Creación') }}</label>
            <input type="text" name="usuario_creacion" class="form-control @error('usuario_creacion') is-invalid @enderror" value="{{ old('usuario_creacion', $usuario?->usuario_creacion) }}" id="usuario_creacion" placeholder="Usuario Creación">
            {!! $errors->first('usuario_creacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <div class="form-group mb-2 mb20">
            <label for="usuario_modificacion" class="form-label">{{ __('Usuario Modificación') }}</label>
            <input type="text" name="usuario_modificacion" class="form-control @error('usuario_modificacion') is-invalid @enderror" value="{{ old('usuario_modificacion', $usuario?->usuario_modificacion) }}" id="usuario_modificacion" placeholder="Usuario Modificación">
            {!! $errors->first('usuario_modificacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Guardar Usuario') }}</button>
    </div>
</div>
