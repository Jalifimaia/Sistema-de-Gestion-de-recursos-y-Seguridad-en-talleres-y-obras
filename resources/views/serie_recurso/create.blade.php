@extends('layouts.app')

@section('template_title')
    Agregar Serie a {{ $recurso->nombre }}
@endsection

@section('content')
<div class="container">
    <h3>Agregar Serie para: {{ $recurso->nombre }}</h3>

    <form method="POST" action="{{ route('serie_recurso.storeMultiple') }}">
        @csrf

        <!-- Campo oculto para enviar el id del recurso -->
        <input type="hidden" name="id_recurso" value="{{ $recurso->id }}">

        <div class="mb-3">
            <label for="nro_serie" class="form-label">Prefijo de Serie</label>
            <input type="text" name="nro_serie" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad de series</label>
            <input type="number" name="cantidad" class="form-control" min="1" required>
        </div>


        <div id="campoTalle" class="mb-3">
        <label for="talle" class="form-label" >Talle </label>
        <input type="text" name="talle" id="talle" class="form-control" placeholder="Ej: M, L, XL">
        </div>


        <div class="mb-3">
            <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label>
            <input type="date" name="fecha_adquisicion" class="form-control" required>
        </div>

    
        <div class="mb-3">
            <select name="id_estado" class="form-select">
        @foreach($estados as $estado)
            <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
        @endforeach
    </select>
    </div>


        <button type="submit" class="btn btn-success">Guardar Serie</button>
        <a href="{{ route('inventario') }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
<script>
  const categoriaRecurso = "{{ strtolower($recurso->categoria->nombre_categoria ?? '') }}";
</script>
<script src="{{ asset('js/serieRecurso.js') }}"></script>

@endsection
