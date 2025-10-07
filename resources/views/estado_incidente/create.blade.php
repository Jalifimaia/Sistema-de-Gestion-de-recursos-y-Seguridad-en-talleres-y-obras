<form action="{{ route('estado_incidente.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="nombre_estado" class="form-label">Nombre del estado</label>
        <input type="text" name="nombre_estado" id="nombre_estado" class="form-control"
               value="{{ old('nombre_estado') }}" required>
    </div>

    <button type="submit" class="btn btn-success">Crear</button>
</form>
